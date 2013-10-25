<?php

/**
* Config
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Interface for .INI configuration files.
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

class Config extends Component
{
    /**
    * @var Array    Array of loaded configurations
    */
    private static $_configs = array();

    /**
    * Get config file from disk or cache
    *
    * @param String     Name of config file without file extension
    * @param String     Absolute path to config file
    */
    public static function get( $configName = false, $configPath = false )
    {
        if( strlen($configName) > 0 && strlen($configPath) > 0 )
        {
            // Standardize config name
            $configName = strtolower($configName);

            // Attempt cached
            if( isset(self::$_configs[$configName]) )
                return self::$_configs[$configName];

            // Load config
            if( file_exists($configPath) && $config = parse_ini_file($configPath, true) )
            {
                self::$_configs[$configName] = $config;
                return $config;
            }
        }

        return false;
    }

    /**
    * Clear configuration cache
    */
    public static function clearCache()
    {
        self::$_configs = array();
    }
}