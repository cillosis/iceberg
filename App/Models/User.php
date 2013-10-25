<?php

/**
* User Model
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Models
* @author       Jeremy Harris 
*/

namespace Iceberg\App\Models;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

class User extends Lib\Model
{
    /**
    * Model Properties
    */
    protected $_name = "User";
    protected $_table = "User";
    protected $_primaryKey = "UserId";
    protected $_childForeignKeys = array();	
    protected $_parentForeignKeys = array();
}

?>