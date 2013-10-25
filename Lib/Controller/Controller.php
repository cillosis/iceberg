<?php

/**
* Controller Component
*
* This component defines controller implementation in MVC paradigm.
*
* @category     Component 
* @package      Controller
* @author       Jeremy Harris <contact@jeremyharris.me>
* @license      http://www.opensource.org/licenses/mit-license.html  MIT License
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

abstract class Controller extends Component
{
    /**
    * @var Iceberg\Lib\View     View object associated with controller.
    */
    public $view = null;

    /**
    * Initialize Controller
    *
    * @param Iceberg\Lib\Request     Request object
    */ 
    public function __construct(){}
    
    /**
    * Abstract methods that child must implement
    */
    abstract function index();

    /**
    * Hook for pre-processing before calling action.
    */
    public function preHook() {}

    /**
    * Hook for post-processing after calling action.
    */
    public function postHook() {}
    
    /**
    * Get a single parameter
    *
    * @param String     Parameter name
    * @return String    Value stored in request
    */
    public function getParam( $key )
    {   
        return Iceberg::getComponent('Request')->getParam($key);
    }

    /**
    * Get all parameters
    */
    public function getParams()
    {
        return Iceberg::getComponent('Request')->getParams();
    }
}

?>