<?php

/**
* Query Component
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Build dynamic queries.
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

/*
$query = $this->makeQuery()
			  ->select( array('*') )
			  ->from('User')
			  ->where( array( 'UserId' => "=" ) ) )
			  ->limit(10)
*/

class Query extends Lib\Component
{
	/**
	* @var String 	Operation and it's values such as SELECT xyz and UPDATE abc
	*/
	private $_operation = "";

	/**
	* @var String 	Where to perform operation
	*/
	private $_from = "";

	/**
	* @var String 	WHERE clause to limit operation results
	*/
	private $_where = "";

	/**
	* @var String 	LIMIT clause to limit operation results
	*/
	private $_limit = "";

	/**
	* @var String 	Compiled query
	*/
	private $_query = "";

	/**
	* Create SELECT operation clause
	*
	* @param Array 	Array of columns
	*/
	public function select( $columns = null )
	{
		if( is_array($columns) )
		{
			$this->_operation .= "SELECT " . implode(",", $columns);
		} else {
			$this->_operation .= "SELECT " . $columns;
		}
		return $this;
	}

	/**
	* Create FROM clause
	*
	* @param Array 	Array of columns
	*/
	public function from( $table = null )
	{
		$this->_from = "FROM " . $table;
		return $this;
	}

	/**
	* Create WHERE clause
	*
	* @param Array 	Array of columns
	*/
	public function where( $where = null )
	{
		$this->_where = "WHERE ";
		if( is_array($where) )
		{
			foreach($where as $column => $condition)
			{
				$this->_where .= $column . " " . $condition . " ?"; 
			}
			
		} 
		return $this;
	}

	/**
	* Create LIMIT clause
	*
	* @param Array 	Array of columns
	*/
	public function limit( $limit = null )
	{
		if( !is_null($limit) )
			$this->_limit = "LIMIT " . $limit;

		return $this;
	}

	public function build()
	{
		// Assemble Query		
		$query = $this->_operation . " " . $this->_from . " " . $this->_where . " " . $this->_limit;
		return $query;
	}
}