<?php

/**
* Recordset
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Container for multiple record objects
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
use Iterator;

class Recordset extends Lib\Component implements Iterator
{
	/**
	* @var Array 	Records in recordset
	*/
	private $_records = array();

	/**
	* @var Integer 	Iteration position
	*/
	private $_position = 0;

	/**
	* Constructor: Initialize recordset from array of records
	*
	* @param Array 		Array of record objects
	*/
	public function __construct( $records = null )
	{
		if( is_array($records) )
		{
			foreach( $records as $record )
			{
				if( $record instanceof Lib\Model\Record )
					$this->_records[] = $record;
			}
		}
	}

	/**
	* Count records in recordset
	*/
	public function count()
	{
		if( is_array($this->_records) )
		{
			return count($this->_records);
		}

		return 0;
	}

	/**
	* Iterator: Retrieve current record
	*/
	public function current()
	{
		return $this->_records[ $this->_position ];
	}

	/**
	* Iterator: Rewind recordset
	*/
	public function rewind()
	{
		$this->_position = 0;
	}

	/**
	* Iterator: Current record key
	*/
	public function key()
	{
		return $this->_position;
	}

	/**
	* Iterator: Next record
	*/
	public function next()
	{
		++$this->_position;
	}

	/**
	* Iterator: Valid record at current position
	*/
	public function valid()
	{
		return isset( $this->_records[ $this->_position ] );
	}
}