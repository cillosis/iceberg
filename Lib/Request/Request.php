<?php

/**
* HTTP Request Component
*
* This component manages incoming requests.
*
* @category     Component 
* @package      Request
* @author       Jeremy Harris <contact@jeremyharris.me>
* @license      http://www.opensource.org/licenses/mit-license.html  MIT License
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

class Request extends Component
{
    /**
    * @var String       Unmodified request string
    */
    public $rawResource = false;

    /**
    * Flags indicating existence of populated superglobals
    * @var Boolean      Has $_POST data?
    * @var Boolean      Has $_GET data?
    * @var Boolean      Has $_FILES data?
    * @var Boolean      Has $_COOKIES data?
    */
    public $hasPost     = false;
    public $hasGet      = false;
    public $hasFiles    = false;
    public $hasCookies  = false;

    /**
    * @var Boolean      Flags indicating request type
    */
    public $isAjax      = false;

    /**
    * Parsed request destination
    * @var String       Module name
    * @var String       Controller name
    * @var String       Action name
    */
    public $module      = false;
    public $controller  = false;
    public $action      = false;

    /**
    * @var Boolean      Flag indicating request has been parsed
    */
    public $isParsed    = false;

    /**
    * @var Array        Cache result of getParams() when retrieving all parameters
    */
    private $_params    = false;

    /**
    * Constructor: Initialize Request Component
    */
    public function __construct()
    {
        // Initialize Base Component. This loads JSON configuration file for Component.
        parent::__construct($this);

        // Set superglobal flags
        if( count($_POST) > 0 )
            $this->hasPost = true;
        if( count($_GET) > 0 )
            $this->hasGet = true;
        if( count($_FILES) > 0 )
            $this->hasFiles = true;
        if( count($_COOKIE) > 0 )
            $this->hasCookies = true;

        // Set AJAX flag if matches XML HTTP Request pattern. This is not a completely reliable method and should not be
        // the sole method of validating an AJAX request.
        if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) 
            $this->isAjax = true;

        // Parse GET requests passed via HTACCESS routing mechanism
        if( isset($_SERVER['REQUEST_URI']) && stristr($_SERVER['REQUEST_URI'],"?") )
        {
            $requestUri = $_SERVER['REQUEST_URI'];
            $requestUri = substr( $requestUri, strpos($requestUri,"?")+1 );
            $requestUri = explode("&", $requestUri);
            foreach($requestUri as $uriParam)
            {
                if( stristr($uriParam,"=") )
                {
                    $uriParam = explode("=", $uriParam);
                    $uriParam[0] = trim($uriParam[0]);
                    $uriParam[1] = trim($uriParam[1]);
                    if( !isset($_GET[$uriParam[0]]) )
                    {
                        $_GET[$uriParam[0]] = $uriParam[1];
                        $_REQUEST[$uriParam[0]] = $uriParam[1];
                    }
                }
            }
        }

        // Parse REQUEST
        $this->_parseRequest();
    }

    /**
    * Retreive superglobal parameters.
    *
    * @param     String     [Optional] Superglobal name
    * @return     Array     Values contained in superglobals
    */
    public function getParams( $type = '' )
    {
        switch( strtoupper($type) )
        {
            // Only POST data
            case 'POST':
                return $_POST;
                break;

            // Only GET data
            case 'GET':
                return $_GET;
                break;

            // Only FILES data
            case 'FILES':
                return $_FILES;
                break;

            // Only COOKIE data
            case 'COOKIE':
                return $_COOKIE;
                break;

            // Only ENV data
            case 'ENV':
                return $_ENV;
                break;

            // Only SERVER data
            case 'SERVER':
                return $_SERVER;
                break;

            // All data except $_SERVER and $ENV to avoid
            // overwrites of that data from malicious users.
            default:
                // Retrieve cached if exists
                if( $this->_params ) 
                    return $this->_params();

                // Build result set
                $result = array();

                // Add GET, POST, and COOKIE
                foreach( $_REQUEST as $key => $value ) {
                    $result[$key] = $value;
                }

                // Add FILES
                foreach( $_FILES as $key => $value ) {
                    $result[$key] = $value;
                }

                // Add PUT and DELETE parameters if passsed
                parse_str( file_get_contents("php://input"), $_OTHER );
                foreach( $_OTHER as $key => $value )
                {
                    if( !isset($result[$key]) )
                        $result[$key] = $value;
                }
                
                // Cache result
                $this->_params = $result;

                return $result;
                break;
        }

        return false;
    }

    /**
    * Retreive superglobal parameters.
    *
    * @param     String     Parameter name
    * @param     String     [Optional] Superglobal name
    * @return     Mixed     Value contained in superglobals param
    */
    public function getParam( $param = false, $type = '' )
    {
        if( $param )
        {
            $params = $this->getParams($type);
            if( isset($params[$param]) )
                return $params[$param];
        }
        
        return null;
    }

    /**
    * Set parameter
    *
    * @param    String     Parameter name
    * @param    Mixed      Parameter value
    * @return   Boolean    Success or failure of method call
    */
    public function setParam( $param = false, $value = false )
    {
        // Filter parameter
        $param = trim($param);

        // Verify parameter
        if( strlen($param) > 0 )
        {
            // Set parameter
            $this->_params[$param] = $value;
            return true;
        }
        
        return false;
    }

    /**
    * Parse Request
    *
    * Process request query string from HTACCESS file which as this
    * format: resource=:module/:controller/:action/:paramKey/:paramValue.
    * The goal is to determine the correct module, controller, and action
    * from the request data.
    */
    private function _parseRequest()
    {
        // If request has already been parsed, return values
        if( $this->isParsed == true )
            return true;

        // Get URL request parts. 
        if($_SERVER['REQUEST_URI'] == "/")
            $query = "resource=index";
        else if(isset($_SERVER['REDIRECT_QUERY_STRING']))
            $query = urldecode($_SERVER['REDIRECT_QUERY_STRING']);
        else
            $query = urldecode($_SERVER['QUERY_STRING']);

        /**
        * Separate sections. The first should be "resource" and be the first
        * element in the array index following this pattern:
        * 
        * resource=:module/:controller/:action/:paramKey/:paramValue
        *
        * Additional items in array are variables appended in the URL with the "&" symbol.
        */
        $query_parts = explode("&", $query);
        foreach ($query_parts as $part)
            $parts[] = explode("=", $part);

        foreach ($parts as $part)
        {
            if($part[0] == "resource" && isset($part[1]))
            {
                // Break resource into components
                $part[1] = rtrim( $part[1], "/" );
                $components = explode("/", $part[1]);
                $this->rawResource = $part[1];

                // If we have all components for full resource path (:module/:controller/:action)
                if( count($components) >= 3 )
                {
                    // Get Module or default to Index module
                    if(isset($components[0]))
                        $this->module = strtolower(trim(strtolower($components[0])));
                    else 
                        $this->module = "index";

                    // Get Controller  or default to Index controller
                    if(isset($components[1]))
                        $this->controller = strtolower(trim(strtolower($components[1])));
                    else 
                        $this->controller = "index";

                    // Get Action  or default to Index action
                    if(isset($components[2]))
                        $this->action = trim(strtolower($components[2]));
                    else 
                        $this->action = "index";

                    // Get URL Parameters
                    if( !is_array( $this->_params ) )
                        $this->_params = array();
                    if(count($components) > 3)
                    {
                        for($x=3; $x<=count($components); $x++)
                        {
                            // If we have a key/value parameter pair
                            if(isset($components[$x]) && isset($components[$x+1]))
                                $this->_params[strval($components[$x])] = htmlspecialchars($components[$x+1], ENT_COMPAT, 'UTF-8');
                            // If key but no value, set as empty
                            elseif(isset($components[$x]))
                                $this->_params[$components[$x]] = "";
                            // Skip past parameter value
                            $x++;
                        }
                    }

                    // Mark flag
                    $this->isParsed = true;

                    return true;
                }
                // Does not follow standard format, may be a predefined route.
                else
                {
                    return false;
                }
            }
        }
    }
}

?>