<?php

/**
* Model
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Database model interface.
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

class Model extends Component
{
	/**
	* @var String 	Name of model
	*/
    protected $_name = "";

	/**
	* @var String 	Name of table
	*/
    protected $_table = "";

    /**
	* @var String 	Name of table's primary key
	*/
    protected $_primaryKey = "";

    /**
	* @var Array 	Array of foreign keys that depend on this model
	*/
    protected $_childForeignKeys = array();

    /**
	* @var Array 	Array of foreign keys that this model depends on
	*/	
    protected $_parentForeignKeys = array();

    /**
    * @var String 	Registry key containing database component
    */
    private $_dbKey = null;

	/**
    * Ensure model that extends this class defines correct properties
    *
    * @param String 	Key used to store database instance in Registry. Default: Database
    */
    public function __construct( $databaseKey = 'Database' )
    {
    	// Retrieve database if initialized
    	$database = Iceberg::getComponent('Registry')->get($databaseKey);

    	// Check if database component initialized
    	if( false == ($database instanceof \Iceberg\Lib\Database)  )
    	{
    		// Not initialized, attempt to initialize it
    		$registry = Iceberg::getComponent('Registry');
	        $database = Iceberg::getComponent('Database');
	        $registry->set($databaseKey, $database);
	        
    	}

    	// Store registry key
    	$this->_dbKey = $databaseKey;
    }	

    /**
    * Retrieve Model Name
    *
    * @return String
    */
    public function getName()
    {
        return $this->_name;
    }

    /**
    * Retrieve Table Name
    *
    * @return String
    */
    public function getTable()
    {
        return $this->_table;
    }

    /**
    * Retrieve Primary Key Name
    *
    * @return String 	Primary key name
    */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }

    /**
    * Set Model Name
    *
    * @param String 	Name of model
    */
    public function setName( $modelName = false )
    {
    	// Set value
    	if( strlen(trim($modelName)) > 0 )
        	$this->_name = $modelName;

       // Return component for method chaining
       return $this;
    }

    /**
    * Set Table Name
    *
    * @param String 	Name of table
    */
    public function setTable( $tableName = false )
    {
    	// Set value
    	if( strlen(trim($tableName)) > 0 )
        	$this->_table = $tableName;

       // Return component for method chaining
       return $this;
    }

    /**
    * Set Table Name
    *
    * @param String 	Name of primary key column
    */
    public function setPrimaryKey( $primaryKey = false )
    {
    	// Set value
    	if( strlen(trim($primaryKey)) > 0 )
        	$this->_primaryKey = $primaryKey;

       // Return component for method chaining
       return $this;
    }

    /**
    * Dynamic record creation. Performs a SQL Insert of data.
    *
    * @param Iceberg\Lib\Model\Record     Object containing record data
    */
    public function create( Lib\Model\Record $data )
    {
        // Get Database
        $database = Iceberg::getComponent('Registry')->get( $this->_dbKey );

        $columns = array();
        $values = array();
        $holders = array();
        
        foreach($data->getFields() as $field)
        {
            // Don't update primary key value, but save for WHERE clause
            if( $field == $this->_primaryKey )
            {
                $primaryKeyValue = $data->$field;
                continue;
            }
            
            $columns[] = $field;
            $holders[] = "?";
            $values[] = $data->$field;
        }

        $query = "INSERT INTO " . $database->name ."." . $this->_table . " (";
        $query.= implode(",", $columns) . ") VALUES (";
        $query.= implode(",", $holders) . ")";

        $dbh = $database->getInstance();
        if ($stmt = $dbh->prepare($query))
        {
            if($stmt->execute($values))
            {
                $insertId = $dbh->lastInsertId();

                // Cleanup
                $stmt = null;
                $dbh = null;

                return $insertId;
            } else {
                // Log error
                Iceberg::getComponent('Logger')->logError( $stmt->errorInfo() );
            }
        } else {
            // Log error
            Iceberg::getComponent('Logger')->logError( $dbh->errorInfo() );
        }

        // Cleanup
        $stmt = null;
        $dbh = null;
                    
        return false;
    }

    /**
    * Dynamic record saving. Performs a SQL Update of changed data.
    *
    * @param Iceberg\Lib\Model\Record     Object containing record data
    */
    public function save( Lib\Model\Record $data )
    {
    	// Get Database
    	$database = Iceberg::getComponent('Registry')->get( $this->_dbKey );

        $columns = array();
        $values = array();
        $primaryKeyValue = null;
        
        foreach($data->getFields() as $field)
        {
            // Don't update primary key value, but save for WHERE clause
            if ( $field == $this->_primaryKey)
            {
                $primaryKeyValue = $data->$field;
                continue;
            }
            
            // Only update dirty fields
            if ($data->isDirty($field))
            {
                $columns[] = $field . " = ?";
                $values[] = $data->$field;
            }
        }

        $query = "UPDATE " . $database->name ."." . $this->_table . " SET ";
        $query.= implode(",", $columns);
        $query.= " WHERE ".$this->_primaryKey." = ?";
            
        // Add primary key to end of values array
        array_push($values, $primaryKeyValue);

        $dbh = $database->getInstance();
        if ($stmt = $dbh->prepare($query))
        {
            if($stmt->execute($values))
            {
                // Cleanup
                $stmt = null;
                $dbh = null;

                return true;
            } else {
                // Log error
                Iceberg::getComponent('Logger')->logError( $stmt->errorInfo() );
            }
        } else {
            // Log error
            Iceberg::getComponent('Logger')->logError( $dbh->errorInfo() );
        }
        
        // Cleanup
        $stmt = null;
        $dbh = null;
                    
        return false;
    }

    /**
    * Perform delete by primary key
    *
    * @param String/Integer     Primary Key
    */
    public function delete( $primaryKey )
    {
        // Get Database
        $database = Iceberg::getComponent('Registry')->get( $this->_dbKey );

        $query = "DELETE FROM " . $database->name ."." . $this->_table . " WHERE " . $this->_primaryKey . " = ?";

        $dbh = $database->getInstance();
        if ($stmt = $dbh->prepare($query))
        {
            if($stmt->execute( array($primaryKey) ) )
            {   
                return true;
            }
        }

        return false;
    }

    /**
    * Get by variable column names. This method is wrapped by how records should be returned.
    *
    * @param Array      Key Value array of columns and values to match.
    * @param String     Value to match if first param is only a string
    * @param Integer 	Number of records to retrieve
    * @return Array     Array of results.
    * @return Boolean   False on failure
    *
    * This function alleviates the need for getByXXXX() functions within the models. It 
    * retrieves rows with a specific value in a specific column. For more dynamic searching,
    * use find().
    */
    private function _getBy( $params = null, $value = null, $limit = null )
    {
    	// Get Database
    	$database = Iceberg::getComponent('Registry')->get( $this->_dbKey );

        // Build WHERE clause
        $where = array();
        $whereValues = array();
        if( is_array($params) )
        {
            foreach($params as $column => $value)
            {
                $where[$column] = "=";
                $whereValues[] = $value;
            }
        }
        else
        {
            $where = array( $params => "=" );
            $whereValues = array($value);
        }
        
        // Build Query
        $query = new Lib\Model\Query;
        $query->select('*')
              ->from( $this->_table )
              ->where( $where )
              ->limit( $limit );

        // Execute Query
        $dbh = $database->getInstance();
        if( $stmt = $dbh->prepare( $query->build() ) )
        {
            if( $stmt->execute($whereValues) )
            {   
                $results = array();
                while( $result = $stmt->fetch(PDO::FETCH_ASSOC) )
                    $results[] = new Lib\Model\Record($this->_name, $result);
            
                // Cleanup
                $stmt = null;
                $dbh = null;
        
                return $results;
            }
            else
            {
                // Log error
                Iceberg::getComponent('Logger')->logError( $stmt->errorInfo() );
            }
        }
        else
        {
            // Log error
            Iceberg::getComponent('Logger')->logError( $dbh->errorInfo() );
        }
        
        // Cleanup
        $stmt = null;
        $dbh = null;
  

        return false;
    }

    /**
    * Get single Record object using variable column names. Returns first match
    * if result has many.
    *
    * @param Array      Key Value array of columns and values to match.
    * @param String     Value to match if first param is only a string
    * @return Array     Array of results
    * @return Null   	If no results found
    */
    public function getOne( $params = null, $value = null )
    {
    	$results = $this->_getBy( $params, $value );
    	if( is_array($results) )
    		return array_pop($results);

    	return null;
    }

    /**
    * Get multiple Record objects as a Recordset using variable column names.
    *
    * @param Array      Key Value array of columns and values to match
    * @param String     Value to match if first param is only a string
    * @param Integer    Number of records to limit recordset to
    * @return Array     Array of results
    * @return Null   	If no results found
    */
    public function getMany( $params = null, $value = null, $limit = null)
    {
    	$results = $this->_getBy( $params, $value, $limit );
    	if( is_array($results) )
    	{
    		$recordSet = new Lib\Model\Recordset( $results );
    		return $recordSet;
    	}

    	return null;
    }

    /**
    * Raw Query of Model
    *
    * Has placeholders to be filled in by this function for {db}, {table}, and {pk}
    */
    public function query($query, $params = array())
    {
        // Get Database
        $database = Iceberg::getComponent('Registry')->get( $this->_dbKey );

        // Process query placeholders
        $query = str_ireplace("{db}", $database->name, $query);
        $query = str_ireplace("{table}", $this->_table, $query);
        $query = str_ireplace("{pk}", $this->_primaryKey, $query);

        $dbh = $database->getInstance();

        if ($stmt = $dbh->prepare($query))
        {
            if($stmt->execute($params))
            {   
                $results = array();
                
                while($result = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $results[] = new Lib\Model\Record($this->_name, $result);
                }

                if( is_array($results) )
                {
                    $recordSet = new Lib\Model\Recordset( $results );
                    return $recordSet;
                }

                // Cleanup
                $stmt = null;
                $dbh = null;
        
                return $results;
            } else {
               var_dump($stmt->errorInfo());
            }
        }

        // Cleanup
        $stmt = null;
        $dbh = null;

        return false;
    }
}