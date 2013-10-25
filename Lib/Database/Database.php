<?php

/**
* Database
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Database instantiation and retrieval interface. Creates MySQL PDO interfaces.
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

// Import Core Objects
use Exception;
use PDO;

class Database extends Component
{
	/**
	* Supported Database Types
	*/
	const DB_PDO 		= 'PDO';
	const DB_MYSQLI 	= 'MYSQLI';

	/**
    * @var String   Database name
    */
    public $name = null;

	/**
    * @var Array    Database Configuration 
    */
    private $_dbConfig = null;

    /**
    * @var PDO      Database Instance
    */
    private $_dbInstance = null;

    /**
    * Initialize new database object
    *
    * @param String 	Database Type
    */
    public function __construct()
    {
        // Load configuration
        if( !$this->_dbConfig = Lib\Config::get( 'database', Iceberg::getApplicationPath() . "/Configs/Database.ini" ) )
            throw new Exception('Database->__construct(): Unable to load database configuration');
        
        // Determine correct configuration to use
        if(!isset($this->_dbConfig[Iceberg::$ENV]) || empty($this->_dbConfig[Iceberg::$ENV]))
            throw new Exception('Database->__construct(): Unable to find configuration for environment "'.Iceberg::$ENV.'"');
        
        // Save configuration based on environment
        $this->_dbConfig = $this->_dbConfig[Iceberg::$ENV];

        // Check database name
        if (!isset($this->_dbConfig['database']))
            throw new Exception('Database->__construct(): No database name defined in configuration file.');

        // Set database name
        $this->name = $this->_dbConfig['database'];

        return $this;
    }

    /**
    * Create database instance
    */
    public function getInstance()
    {
        if( is_resource($this->_dbInstance) )
            return $this->_dbInstance;


        // Create new connection
        $dsn = "mysql:dbname=" . $this->_dbConfig['database'] . ";host=" . $this->_dbConfig['host'];
        try {
            $this->_dbInstance = new PDO($dsn, $this->_dbConfig['user'], $this->_dbConfig['pass']);
        }
        catch(Exception $e) {
            Iceberg::logError('Database::getPDOInstance() => ' . $e->getMessage()) ;
        }

        return $this->_dbInstance;
    }

    /**
    * Retrieve Database Configuration
    *
    * @return Array    Configuration parameters loaded from .INI file
    */
    public function getConfig()
    {
        if( is_array($this->_dbConfig) )
        {
            return $this->_dbConfig;
        }

        return null;
    }
}