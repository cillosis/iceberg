<?php

/**
* Error Controller
*
* @copyright    Copyright (c) 2013 
* @package      Controller
* @author       Jeremy Harris
*
* Custom error controller
*/

namespace Iceberg\App\Modules\Site\Controllers;

use \Iceberg\App as App;
use \Iceberg\Lib as Lib;

class Error extends Lib\Controller
{
    public function index()
    {

    }

    /**
    * 404 Error
    */
    public function error404()
    {
    	header("HTTP/1.0 404 Not Found");
    	header("Status: 404 Not Found");
    }
}