<?php

/**
* Record
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Record retreived from data model.
*/

namespace Iceberg\Lib\Model;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

// Import Core Objects
use Exception;
use PDO;

class Record extends Lib\Component
{
	/**
	* @var Array 	Array of custom values from record data
	*/
	private $_data = array();

	/**
	* @var String 	Model which data was derived from
	*/
    private $_model = null;

    /**
    * @var Array 	Names of database columns
    */
    private $_fields = array();

    /**
    * @var Array 	Values which have been changed
    */
    private $_dirty = array();

    /**
    * @var Boolean	Flag indicating initial data loading complete
    */
    private $_loaded = false;

    /**
    * @var Boolean	Flag indicating if this is a new record
    */
    private $_new = false;

    /**
    * @var Array	Fields which were joined in an not part of the original model
    */
    private $_joinedFields = array();

    /**
    * @var Integer	If this is a new record and gets saved, store the insert ID
    */
    private $_insertId = null;

	/**
    * Constructor: Initialize Data Object using array of data properties
    *
    * @param String 	Name of model
    * @param Array 		Array of data
    */
    public function __construct( $model = null, $data = null )
    {
    	// Verify model name
        if( !is_null($model) && strlen(trim($model)) > 0 )
        {
            $this->_model = $model;
        }

        // Verify data
        if( is_array($data) )
        {
        	// Assign data
            foreach($data as $key => $value)
            {
                $this->_data[$key] = is_null($value) ? '' : $value;
                $this->_fields[] = $key;
                $this->_dirty[$key] = false;
            }
        } else {
            // No data passed in, assume this is a new record
            $this->_new = true;
        }

        // Mark loading as complete
        $this->_loaded = true;
    }

    /**
    * Set record data
    *
    * @param String     Key/name of value
    * @param Mixed      Value of data
    */
    public function __set( $key = null, $value = null )
    {
        // If value different than before, mark dirty
        if (isset($this->_data[$key]) && $this->_data[$key] != $value)
            $this->_dirty[$key] = true;

        // If new value mark dirty and add to fields array
        if (isset($this->_data[$key]) == false && $this->_loaded == true)
        {
            $this->_dirty[$key] = true;
            if(!isset($this->_fields[$key]))
                $this->_fields[] = $key;
        }
        
        $this->_data[$key] = $value;
    }

    /**
    * Get custom data
    *
    * @param String     Key/name of value
    */
    public function __get( $key = null )
    {
        if (isset($this->_data[$key]))
            return ($this->_data[$key]);

        return null;
    }

    /**
    * Check if custom data is set
    *
    * @param String     Key/name of value
    */
    public function __isset( $key = null )
    {
        return isset($this->_data[$key]);
    }

    /**
    * Unset custom data
    *
    * @param String     Key/name of value
    */
    public function __unset( $key = null )
    {
        unset($this->_data[$key]);
    }

    /**
    * Check if field has been marked dirty
    *
    * @param String     Field name
    */
    public function isDirty( $field = null )
    {
        if (isset($this->_dirty[$field]))  
            return $this->_dirty[$field];
    }

    /**
    * Unmark dirty field
    *
    * @param String     Field name
    */
    public function setClean( $field = null )
    {
        if (isset($this->_dirty[$field]))  
            $this->_dirty[$field] = false;
    }

    /**
    * Get table fields
    */ 
    public function getFields()
    {
        return $this->_fields;
    }
    
    /**
    * Set a joined field
    *
    * @param String     Field Name
    */
    public function setJoinedField( $field = null )
    {
        $this->_joinedFields[] = $field;
    }

    /**
    * Save Record to database using save method of its originating model
    */
    public function save()
    {
        if( !empty($this->_model) )
        {
            // Extract any joined fields from record
            $this->_fields = array_diff($this->_fields, $this->_joinedFields);
            
            // Check if new record, if so, call create method of model
            if( $this->_new == true )
            {
                $this->_insertId = Iceberg::getModel($this->_model)->create($this); 
                if( is_numeric($this->_insertId) && $this->_insertId > 0 )
                {
                    $pkField = Iceberg::getModel($this->_model)->getPrimaryKey();
                    $this->$pkField = $this->_insertId;
                    return true;
                }
            }
            else if( in_array(true, $this->_dirty) )
            {
                return Iceberg::getModel($this->_model)->save($this);
            }
        }

        return false;
    }

    /**
    * Delete Record from Database using delete method of its originating model
    */
    public function delete()
    {
        if( !empty($this->_model) )
        {
            $model = Iceberg::getModel($this->_model);
            $primaryKey = $model->getPrimaryKey();
            $model->delete($primaryKey, $this->_data[$primaryKey]);
            return true;
        }

        return false;
    }
}