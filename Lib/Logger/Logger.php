<?php

/**
* Application Logger
*
* @copyright    Copyright (c) 2013 
* @package      Framework.System.Config
* @author       Jeremy Harris
*
* Handles logging of application messages.
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

// Import Core Objects
use Exception;

class Logger extends Lib\Component
{
	/**
	* @var String 	Log folder path
	*/
	private $_logName = null;

	/**
	* @var String 	Log folder path
	*/
	private $_logPath = null;

	/**
	* Constructor: Initialize logger
	*/
	public function __construct( $logPath = null )
	{
		// Initialize log path
		if( file_exists($logPath) )
			$this->_logPath = $logPath;
		else
			$this->_logPath = Iceberg::getApplicationPath() . "/Logs";

		// Check if log path writable
		if( !is_writable($this->_logPath) )
			throw new exception("Lib\Logger::__construct() -> Log path not writable.");
	}

	/**
	* Log message to log
	*/
	public function logError( $message )
	{
		// Get last caller
		$backtrace = debug_backtrace();
		$lastCaller = "";
		if( is_array($backtrace) && isset($backtrace[1]) )
		{
			$lastCaller = $backtrace[1];
			if( is_array($lastCaller) )
				$lastCaller = "[" . $lastCaller['class'] . $lastCaller['type'] . $lastCaller['function'] . "()]";
		}

		// Define file
        $filePath = $this->_logPath . "/Error.log";

        if( !file_exists($filePath) )
        {
        	// Create File
        	$fp = @fopen($filePath, 'w');
        	@fclose($fp);
        }

        if( file_exists($filePath) && is_writable($filePath) )
        {
            if( $fp = fopen($filePath, 'a+') )
            {
                fwrite($fp, "[" . date('Y-m-d H:i:s') . "]" . $lastCaller . " => " . $message . "\n");
                fclose($fp);
            } 
        }
	}

	/**
	* Log message to log
	*/
	public function logDebug( $message )
	{
		// Open file
        $filePath = $this->_logPath . "/Debug.log";
        if( file_exists($filePath) && is_writable($filePath) )
        {
            if( $fp = fopen($filePath, 'a+') )
            {
                fwrite($fp, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n");
                fclose($fp);
            } 
        }
	}

}