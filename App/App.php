<?php

/**
* Application
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.App
* @author       Jeremy Harris
*
* The App object is the instantiation point for the application. It is designed to be
* lightweight and only perform minimal initialization and routing as this is loaded in every request.
*/

namespace Iceberg\App;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App\Plugins as Plugins;
use \Iceberg\App\Modules as Modules;

// Import Core Objects
use Exception;
use ReflectionClass;

class App {

    /**
    * Constants
    */
    const ENV_DEVELOPMENT       = 'development';
    const ENV_STAGING           = 'staging';
    const ENV_PRODUCTION        = 'production';

    /**
    * Public Static Properties
    */
    public static $ENV          = App::ENV_PRODUCTION; // DEFAULT TO PRODUCTION
    public static $CONFIG       = array();
    public static $THEME        = "default";

    /**
    * Private Static Properties
    */
    private static $_components = null;
    private static $_models     = null;
    private static $_basePath   = "";
    private static $_appPath    = "";
    private static $_libPath    = "";
    private static $_pubPath    = "";

    /**
    * Component caches
    */
    private static $models = null;

    /**
    * Main application entry point. This function sets up autoloading, route configuration, 
    * and loads application settings.
    */
    public static function run() 
    {
        // Initialize Application Framework
        self::initialize();

        // Start Router and define routes
        $router = self::getComponent('Router');

        // Site Routes
        $router->addRoute('search', array('module' => 'site', 'controller' => 'shop', 'action' => 'search'));
        $router->addRoute('terms', array('module' => 'site', 'controller' => 'index', 'action' => 'tos'));
        $router->addRoute('tos', array('module' => 'site', 'controller' => 'index', 'action' => 'tos'));
        $router->addRoute('privacy', array('module' => 'site', 'controller' => 'index', 'action' => 'privacy'));
        $router->addRoute('rss', array('module' => 'site', 'controller' => 'index', 'action' => 'sitemap'));
        $router->addRoute('sitemap', array('module' => 'site', 'controller' => 'index', 'action' => 'sitemap'));
        $router->addRoute('about', array('module' => 'site', 'controller' => 'index', 'action' => 'about'));
        $router->addRoute('contact', array('module' => 'site', 'controller' => 'index', 'action' => 'contact'));

        // Update component cache
        self::setComponent( 'Router', $router );

        // Dispatch request
        $router->dispatch( self::getComponent('Request') );
    } 

    /**
    * Framework Initialization
    */
    public static function initialize() 
    {
        // Allow HTACCESS override of environment
        if( getenv('APP_ENV') )
            App::$ENV = getenv('APP_ENV');

        // Define application and system paths
        self::$_basePath    = dirname(dirname(__FILE__));
        self::$_appPath     = self::$_basePath . "/App";
        self::$_libPath     = self::$_basePath . "/Lib";
        self::$_pubPath     = self::$_basePath . "/Public";

        // Define Library Auto Loader
        spl_autoload_register(array('Iceberg\App\App', 'libraryAutoload'));

        // Define Application Auto Loader
        spl_autoload_register(array('Iceberg\App\App', 'applicationAutoload'));
        
        // Start Session
        session_start();

        // Turn off errors for production environment
        if( App::$ENV == App::ENV_PRODUCTION )
        {
            error_reporting(0);
            ini_set('display_errors', 0);
            if(function_exists('xdebug_disable'))
            {
                xdebug_disable();
            }
        }
        else
        {
            // Show all errors 
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }

        // Initialize Application Specific Configuration
        self::_loadConfiguration();
    } 

    /**
    * Application Auto Loading
    */
    protected static function applicationAutoload($className = false)
    {
        //echo($className . " ==> ");
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $fileName = str_replace('Iceberg/', '', $fileName);
        //echo(self::$_basePath . "/" . $fileName . "<br>");
        if( file_exists(self::$_basePath . "/" . $fileName) )
            include_once(self::$_basePath . "/" . $fileName);
    } 

    /**
    * Library Auto Loading
    */
    protected static function libraryAutoload($className = false)
    {
        //echo($className . " ==> ");
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . $className . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $fileName = str_replace('Iceberg/', '', $fileName);
        //echo(self::$_basePath . "/" . $fileName . "<br>");
        if( file_exists(self::$_basePath . "/" . $fileName) )
            include_once(self::$_basePath . "/" . $fileName);
    }

    /**
    * Get App URL
    */
    public static function getUrl()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
            $pageURL .= "://";
        if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"];
        }
        return $pageURL;
    }

    /**
    * Retrieve a model by initializing a new one, or retrieving a cached copy
    *
    * @param String     Name of model without the "Model_" prefix
    */
    public static function getModel( $name = false )
    {
        if( $name )
        {   
            // Check for cached copy
            if( isset(self::$_models[$name]) )
                return self::$_models[$name];

            // New Instance
            $model = "Iceberg\\App\\Models\\" . trim($name);
            if( class_exists($model) )
            {
                App::$models[$name] = new $model();
            }
            else
            {
                // Create model class following framework model pattern
                $newModel = Iceberg::getComponent('Model');
                $newModel->setName($name);
                $newModel->setTable($name);
                $newModel->setPrimaryKey($name . "Id");
                self::$models[$name] = $newModel;
            }

            return App::$models[$name];
        }

        return false;
    }

    /**
    * Retrieve a loaded configuration setting
    *
    * @param String     Setting Key
    * @return String    Value assigned to Key
    */
    public static function getSetting($key = null, $mode = "normal")
    {
        switch(strtolower($mode))
        {
            case "lowercase":
                return strtolower(Iceberg::getComponent('Registry')->get($key));
                break;
            case "uppercase":
                return strtoupper(Iceberg::getComponent('Registry')->get($key));
                break;
            case "camelcase":
                return ucwords(Iceberg::getComponent('Registry')->get($key));
                break;
            case "url":
                $url = strtolower(Iceberg::getComponent('Registry')->get($key));
                $url = str_replace(" ", "-", $url);
                return $url;
                break;
            default:
                return Iceberg::getComponent('Registry')->get($key);
                break;
        }
    }

    /**
    * Get library component
    */
    public static function getComponent( $componentKey = false )
    {
        // Standardize Component Key
        $componentKey = ucfirst(strtolower(trim($componentKey)));

        if( strlen($componentKey) > 0 )
        {
            // Check for additional arguments to pass to component. Components with additional arguments are not cached.
            if( func_num_args() > 1 )
            {
                // Get all arguments
                $componentArgs = func_get_args();

                // Remove component name argument
                unset($componentArgs[0]);

                // Define component
                $componentNamespace = "\\Iceberg\Lib\\" . $componentKey;

                // Create instance while passing arguments to constructor
                $reflect = new ReflectionClass($componentNamespace);
                return $reflect->newInstanceArgs($componentArgs);
            }
            else
            {
                if ( !isset(self::$_components[ $componentKey ]) || self::$_components[ $componentKey ] == null)
                {
                    $componentNamespace = "\\Iceberg\Lib\\" . $componentKey;
                    self::$_components[ $componentKey ] = new $componentNamespace();
                }

                return self::$_components[ $componentKey ];
            }
        }

        return null;
    }

    /**
    * Set library component
    */
    public static function setComponent( $componentKey = false, \Iceberg\Lib\Component $component )
    {
        // Standardize Component Key
        $componentKey = ucfirst(strtolower(trim($componentKey)));

        if( strlen($componentKey) > 0 )
            self::$_components[ $componentKey ] = $component;
    }

    /**
    * Get base framework path
    *
    * @return String    Absolute path to root folder
    */
    public static function getBasePath()
    {
        return self::$_basePath;
    }

    /**
    * Get application path
    *
    * @return String    Absolute path to "App" folder
    */
    public static function getApplicationPath()
    {
        return self::$_appPath;
    }

    /**
    * Get library path
    *
    * @return String    Absolute path to "Lib" folder
    */
    public static function getLibraryPath()
    {
        return self::$_libPath;
    }

    /**
    * Get public path
    *
    * @return String    Absolute path to "Public" folder
    */
    public static function getPublicPath()
    {
        return self::$_pubPath;
    }

    /**
    * Load Application Configuration Data
    */
    private static function _loadConfiguration()
    {
        Iceberg::getComponent('Registry')->set('App', \Iceberg\Lib\Config::get('App'));
    } 
}   

?>