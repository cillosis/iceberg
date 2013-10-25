<?php

/**
* Framework System Web Entry 
* 
* @copyright    Copyright (c) 2013  
* @package      Framework.System
* @author       Jeremy Harris
* 
* Primary entry point for Framework System.
*/

namespace Iceberg;
use Iceberg\App;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(dirname(__FILE__)) . '/App/App.php';

/** GLOBAL APPLICATION OBJECT **/
$App = new App\App();

$App::run();

?>