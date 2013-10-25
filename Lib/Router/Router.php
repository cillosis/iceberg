<?php

/**
* HTTP Router Component
*
* This component routes requests.
*
* @category     Component 
* @package      Router
* @author       Jeremy Harris <contact@jeremyharris.me>
* @license      http://www.opensource.org/licenses/mit-license.html  MIT License
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

class Router extends Component
{
	/**
    * @var Array 	User defined routes
    */
    private $_routes = array();

    /**
    * Constructor: Initialize routing
    *
    * @param Array 	Array of routes to pre-initialize [optional]
    */
    public function __construct( $routes = false )
    {   
        // Load routes if passed
        if( is_array($routes) )
            $this->addRoutes($routes);
    }

    /**
    * Add route to router.
    *
    * @param String     Pseudo path to match
    * @param Array      Destination if path is matched
    * @return Boolean 	Success or failure of method call
    *
    * Example: 
    * 	addRoute( array( "/user/settings" => array('module' = 'site', controller' = 'user', 'action' => 'settings') ) );
    */
    public function addRoute( $route = false, $destination = false ) 
    {
    	// Filter parameters
    	$route = trim($route);

    	// Verify parameters
    	if( strlen($route) > 0 && is_array($destination) )
    	{
	        $newRoute = new Router\Route( $route, $destination );
	        if( is_object($newRoute) )
	        {
	        	$this->_routes[] = $newRoute;
	        	return true;
	        }
        }

        return false;
    }

    /**
    * Add multiple routes to router.
    *
    * @param Array      Array of values that would be passed to addRoute so we can add multiple at once.
    * @return Boolean 	Success or failure of method call. 
    */
    public function addRoutes( $routes = false ) 
    {
        if (!is_array($routes))
            return false;

        foreach ($routes as $routeItem)
        {
        	// Verify Data and add route
            $route = isset($routeItem['route']) ? $routeItem['route'] : false;
            $destination = isset($routeItem['destination']) ? $routeItem['destination'] : false;
            if( $route !== false && is_array($destination) )
                $this->_routes[] = new Router\Route( $route, $destination );
        }
    }

    /**
    * Dispatch request to controller based on routes or default routing scheme :module/:controller/:action/:params
    *
    * @param Request  Request object
    */
    public function dispatch( Request $request )
    {  
        // Attempt to match against predefined routes.
        foreach( $this->_routes as $route )
        {           
            if( $destination = $route->matches($request) )
            {
                // Route to destination
                $request->module = trim($destination['module']);
                $request->controller = trim($destination['controller']);
                $request->action = trim($destination['action']);
                if( isset($destination['params']) && is_array($destination['params']) )
                { 
                    foreach($destination['params'] as $paramKey => $paramValue)
                    {
                    	$request->setParam( $paramKey, $paramValue );
                    }
                }

                // Match found, prematurely exit loop
                break;
            } 
        }

        // Check values and use defaults if not set
        if( $request->rawResource == "index" )
        {
            if( $request->module == '' ) { $request->module = 'site'; }
            if( $request->controller == '' ) { $request->controller = 'index'; }
            if( $request->action == '' ) { $request->action = 'index'; }
        }

        // Define controller namespace
        $fqController = "Iceberg\\App\\Modules\\" . ucfirst($request->module) . "\\Controllers\\" . ucfirst($request->controller);

        // Verify class exists
        if (class_exists($fqController)) {

            // Instantiate controller
            $this->controller = new $fqController();

            // Verify method exists
            if( method_exists($this->controller, $request->action) ) {

                // Define module bootstrap namespace
                $moduleBootstrap = "Iceberg\\App\\Modules\\" . $request->module . "\\Bootstrap";
                // Check if optional module bootstrap exists
                if (class_exists($moduleBootstrap)) {
                    $bootstrap = new $moduleBootstrap();
                    $bootstrap->bootstrap();
                }

                // Initialize view for controller/action
                $this->controller->view = Iceberg::getComponent('View', $request->module, $request->controller, $request->action);

                // Call controller hook before action execution
                if( method_exists($this->controller, "preHook") ) 
                {
                    $this->controller->preHook();
                }

                // Call controller action
                $action = $request->action;
                $this->controller->$action();

                // Call controller hook after action execution
                if(method_exists($this->controller, "postHook")) 
                {
                    $this->controller->postHook();
                }

                // Render view
                $this->controller->view->render();

            } else {
                // Redirect to Error Page
                $this->throwHttpError( '404', $request );
            }
            
        } else {
            // Redirect to Error Page
            $this->throwHttpError( '404', $request );
        }
    }

    /**
    * Throw an HTTP Error
    *
    * @param 	String 		HTTP error code
    * @param 	Request 	Request object that caused error
    */
    public function throwHttpError( $errorCode = false, Request $request )
    {
    	// Pass to module if available
    	if( $request->module )
    	{
    		// Determine module error controller name
    		$errorControllerNamespace = "App\\Modules\\" . ucfirst($request->module) . "\\Controllers\\Error";

    		// Determine module error method name
    		$errorMethod = "error" . $errorCode;

    		// Error controller exist in module
    		if( class_exists($errorControllerNamespace) && method_exists($errorControllerNamespace, $errorMethod) )
    		{
    			// Call module error controller
    			$request->controller = "Error";
    			$request->action = $errorMethod;
    			$this->dispatch( $request );
    			exit;
    		}
    		else
    		{
    			// Throw generic error
    			switch( intval($errorCode) )
    			{
    				case 404:
    					header("HTTP/1.0 404 Not Found");
    					header("Status: 404 Not Found");
    					echo("<h1>Not Found.</h1>");
    					break;
    				default:
    					print_r("Something went wrong.");
    					break;
    			}

    			exit;
    		}
    	}
    }
}