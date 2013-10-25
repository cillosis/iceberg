<?php

/**
* Component Abstract
*
* @category     Component 
* @package      Component
* @author       Jeremy Harris
* @license       http://www.opensource.org/licenses/mit-license.html  MIT License
*
* Core component framework extensible by library components.
*/

namespace Iceberg\Lib;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

// Import Core Objects
use stdClass;

class Component
{
    /**
    * Array of dynamic properties
    */
    protected $_properties  = array();

    /**
    * Component configuration object
    */
    protected $_config      = null;

    /**
    * Initialize Base Component
    *
    * @param Component     Child class
    */
    public function __construct( $component = false )
    {
        // Load JSON Component Configuration
        if( $component instanceof Component )
            $this->_loadComponentConfig( get_class($component) );   
    }

    /**
    * Set dynamic property
    *
    * @param String     Key/name of value
    * @param Mixed      Value of property
    */
    public function __set($key, $value)
    {
        $this->_properties[$key] = $value;
    }

    /**
    * Get dynamic property
    *
    * @param String     Key/name of value
    */
    public function __get($key)
    {
        if (isset($this->_properties[$key]))
            return ($this->_properties[$key]);

        return null;
    }

    /**
    * Check if dynamic property is set
    *
    * @param String     Key/name of value
    */
    public function __isset($key)
    {
        return isset($this->_properties[$key]);
    }

    /**
    * Load JSON config file for component.
    *
    * @param     String         Name of component class.
    * @return     Boolean     Success or failure of method.
    *
    * @todo     Add logging for unreadable or invalid format config file.
    */
    protected function _loadComponentConfig( $componentName = false )
    {
        if( $componentName )
        {
            // Exclude component namespace to get class
            $componentName = str_replace( "App\\Component\\", "", $componentName );

            // Format for file path
            $componentName = str_replace( "\\", DIRECTORY_SEPARATOR, $componentName );

            // Create JSON configuration file name
            $configFile = dirname(realpath(__FILE__)) . DIRECTORY_SEPARATOR . $componentName . DIRECTORY_SEPARATOR . $componentName . ".json";
            
            // If file exists, load it into Component base class
            if( file_exists($configFile) && is_readable($configFile) ) {
                // Get contets and convert to PHP object
                $configData = file_get_contents($configFile);
                $configData = json_decode($configData);
                if( is_object($configData) ) {
                    $this->_config = $configData;
                    return true;
                } 
              }
        }

        return false;
    }

    /**
    * Get component config object
    *
    * @return     Object     JSON Configuration Object
    */
    public function getConfig()
    {
        if( !is_object($this->_config) )
            $this->_config = new stdClass();

        return $this->_config;
    }
}

?>