<?php

/**
* Registry
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Container for storage and retrieval of variables.
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

class Registry extends Component
{
    /**
    * @var Array    Array of registry variables
    */
    private static $_data = array();

    /**
    * Get variable from registry using dot syntax
    *
    * @param String     Dot syntax variable name.
    */
    public static function get( $name = false )
    {
        if( strlen($name) > 0 )
        {
            $data = &self::$_data;
            foreach(explode('.', $name) as $part)
            {
                $data = &$data[$part];
            }

            if($data == null)
                $data = "";

            return $data;
        }

        return null;
    }

    /**
    * Set data in registry.
    * @param String     Namespace key for data set.
    * @param Array      Data to store in namespace.
    */
    public static function set( $key, $data )
    {
        // Validate key and data
        $key = trim($key);
        if( $key )
        {   
            self::$_data[$key] = $data;
            return true;
        }

        return false;
    }

    public static function getAll()
    {
        return self::$_data;
    }
}