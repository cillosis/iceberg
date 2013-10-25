<?php

/**
* Plugin Abstract
*
* @category     Plugin 
* @package      Plugin
* @author       Jeremy Harris
* @license       http://www.opensource.org/licenses/mit-license.html  MIT License
*
* Core plugin framework extensible by plugins.
*/

namespace Iceberg\App\Plugins;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

// Import Core Objects
use stdClass;

class Plugin
{
    /**
    * Array of dynamic properties
    */
    protected $_properties  = array();

    /**
    * Plugin configuration object
    */
    protected $_config      = null;

    /**
    * Initialize Base Plugin
    *
    * @param Plugin     Child class
    */
    public function __construct( $plugin = false )
    {
        // Load JSON Plugin Configuration
        if( $plugin instanceof Plugin )
            $this->_loadPluginConfig( get_class($plugin) );   
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
    * Load JSON config file for plugin.
    *
    * @param     String         Name of plugin class.
    * @return     Boolean     Success or failure of method.
    *
    * @todo     Add logging for unreadable or invalid format config file.
    */
    protected function _loadPluginConfig( $pluginName = false )
    {
        if( $pluginName )
        {
            // Exclude plugin namespace to get class
            $pluginName = str_replace( "App\\Plugin\\", "", $pluginName );

            // Format for file path
            $pluginName = str_replace( "\\", DIRECTORY_SEPARATOR, $pluginName );

            // Create JSON configuration file name
            $configFile = dirname(realpath(__FILE__)) . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . $pluginName . ".json";
            
            // If file exists, load it into Plugin base class
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
    * Get plugin config object
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