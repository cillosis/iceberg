<?php

/**
* HTTP Route Component
*
* This component defines the data structure of a route for request routing.
*
* @category     Component 
* @package      Router
* @author       Jeremy Harris <contact@jeremyharris.me>
* @license      http://www.opensource.org/licenses/mit-license.html  MIT License
*/

namespace Iceberg\Lib\Router;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

class Route extends Lib\Component
{
	/**
	* @var String 	Plaintext or psuedo-regex route.
	*/
	public $route = null;

	/**
	* @var Array 	Destination of route as module/controller/action.
	*/
    public $destination = null;

    /**
    * Flag indicating route string contains regular expressions.
    */
    public $isRegex = false;

    /**
    * @var String 	Actual regular expression generated from pseudo-regex.
    */
    private $_routeRegex = null;

    /**
    * @var String 	Regular expression variable replacements.
    */
    private $_regexVariables = array();

    /**
    * Constructor: Initialize route
    *
    * @param String     Pseudo-regex path to match
    * @param Array      Destination if path is matched      
    */
    public function __construct( $route = false, $destination = false )
    {
    	// Filter parameters
    	$route = trim($route);

    	// Verify parameters
    	if( strlen($route) > 0 && is_array($destination) )
    	{
	        // Check if route has psuedo-regex symbol variables (like ":myvar")
	        if( stristr($route, ":") )
	        {
	            // Build parameterized regex expression
	            $routeRegex = substr( $route, 0, stripos($route,":") );
	            $route = str_ireplace($routeRegex, "", $route);
	            $routeRegex = str_replace("/", "\/", $routeRegex);
	            $parts = preg_split('/:/', $route, null, PREG_SPLIT_NO_EMPTY && PREG_SPLIT_DELIM_CAPTURE);

	            foreach($parts as $part)
	            {
	                // Filter Trailing Slashes
	                $part = str_replace("/", "", $part);
	                // If Variable Parts
	                if( preg_match('/\[[0-9]{1,2}\]/', $part, $matches) )
	                {
	                    $repeat = intval( str_replace( array("[","]"), "", $matches[0] ) );
	                    $part = str_replace( array("[","]",$repeat), "", $part);
	                    for($x=0; $x < $repeat; $x++)
	                    {
	                        $this->_regexVariables[] = $part;
	                        $routeRegex .= "([a-zA-Z0-9_-]*)?\/?";
	                    }
	                } else {
	                    // Get Regex Piece
	                    $this->_regexVariables[] = $part;
	                    $routeRegex .= "([a-zA-Z0-9_-]*)?\/?";
	                }
	            }

	            $routeRegex = "/" . $routeRegex . "/";
	            $this->isRegex = true;
	            $this->route = $routeRegex;
	        } else {
	            $this->route = $route;
	        }
	        
	        $this->destination = $destination;

	        return $this;
	    }
    }

    /**
    * Check if request matches this route
    */
    public function matches($request)
    {
        // Match regular expressions
        if($this->isRegex)
        {
            if( preg_match($this->route, $request->rawResource, $matches) )
            {
                // Remove first match as this is the piece before the variables
                unset($matches[0]);
                $matches = array_values($matches);
                $this->destination['params'] = array();
                foreach($matches as $index => $match)
                {
                    // If param is already set, treat as an array
                    if( isset($this->destination['params'][$this->_regexVariables[$index]]) )
                    {
                        // Convert to array if not already
                        if( !is_array($this->destination['params'][$this->_regexVariables[$index]]) )
                        {
                            $pieceValue = $this->destination['params'][$this->_regexVariables[$index]];
                            $this->destination['params'][$this->_regexVariables[$index]] = array();
                            array_push($this->destination['params'][$this->_regexVariables[$index]], $pieceValue);
                        }
                        if( !empty($match) )
                            array_push($this->destination['params'][$this->_regexVariables[$index]], $match);
                    } 
                    else
                    {
                        // Assign parameters to GET
                        $this->destination['params'][$this->_regexVariables[$index]] = $match;
                    }
                }
                return $this->destination;
            }
        }
        else
        {
            // Match plaintext
            if ( strtolower($this->route) == strtolower($request->rawResource) )
            {
                return $this->destination;
            }
        }

        return false;
    }
}