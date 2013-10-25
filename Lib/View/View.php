<?php

/**
* View Component
*
* This component manages MVC views.
*
* @category     Component 
* @package      View
* @author       Jeremy Harris <contact@jeremyharris.me>
* @license      http://www.opensource.org/licenses/mit-license.html  MIT License
*/

namespace Iceberg\Lib;

// Expose Application as Iceberg Framework Object
use \Iceberg\App\App as Iceberg;

// Import Framework
use \Iceberg\Lib as Lib;
use \Iceberg\App as App;

class View extends Component
{
    /**
    * @var String   Layout to use when displaying View
    */
    public $layout = "default";

    /**
    * @var Boolean  Option to disable layout in special circumstances
    *               such as JSON or XML output
    */
    public $useLayout = true;

    /**
    * @var String   Content generated for injection into layout
    */
    public $content = null;

    /**
    * @var String   Title to inject into <title> tag of layout
    */
    public $layoutTitle = null;

    /**
    * @var String   Description to inject into meta description tag of layout
    */
    public $layoutDescription = null;

    /**
    * @var String   Path to View file (.phtml)
    */
    private $_viewFile = null;

    /**
    * @var Array   Stylesheets to inject into <head> tag
    */
    private $_styles = array();

    /**
    * @var Array   Scripts to inject into <head> tag
    */
    private $_scripts = array();

    /**
    * @var Array   Meta Tags to inject into <head> tag
    */
    private $_meta = array();

    /**
    * Constructor: Initialize routing
    *
    * @param String 	Array of routes to pre-initialize [optional]
    */
    public function __construct( $module = false, $controller = false, $action = false )
    {
        // Filter parameters
        $module = trim($module);
        $controller = trim($controller);
        $action = trim($action);

        // Set view file from parameters
        if( strlen($module) > 0 && strlen($controller) > 0 && strlen($action) > 0 ) {
            $this->_viewFile = Iceberg::getApplicationPath() . "/Modules/" . ucfirst(strtolower($module)) . "/Views/" . ucfirst(strtolower($controller)) . "/" . ucfirst(strtolower($action)) . ".phtml";
        }
    }

    /**
    * Render this view
    */
    public function render()
    {
        if(file_exists($this->_viewFile)) 
        {   
            ob_start();
            include_once $this->_viewFile;
            $this->content = ob_get_clean();
            
            if($this->useLayout == false)
            {
                echo($this->content);
                $this->useLayout = true;
            } else {
                if($this->layout != "default") {
                    include_once Iceberg::getApplicationPath() . "/Layouts/" . $this->layout . ".phtml";
                } else {
                    include_once Iceberg::getApplicationPath() . "/Layouts/default.phtml";
                }
            }
        }
    }

    /**
    * Add Style
    *
    * @param String     Path to stylesheet
    *
    * Queue a stylesheet for render
    */
    public function addStyle( $href="" )
    {
        if( strlen(trim($href)) > 0 )
        {
            $this->_styles[] = "<link href='$href' rel='stylesheet'>";
        }
    }

    /**
    * Add Script
    *
    * @param String     Path to script
    *
    * Queue a stylesheet for render
    */
    public function addScript( $src="" )
    {
        if( strlen(trim($src)) > 0 )
        {
            $this->_scripts[] = "<script src='$src'></script>";
        }
    }

    /**
    * Add Meta Data
    *
    * @param String     Meta tag to inject into <head>
    */
    public function addMeta( $meta )
    {
        $this->_meta[] = $meta;
    }

    
    /**
    * Inject Styles
    *
    * Takes array of styles set via controller and injects them into <head> during render.
    */
    public function injectStyles()
    {
        foreach ($this->_styles as $style)
        {
            echo($style . "\n");
        }
    }

    /**
    * Inject Scripts
    *
    * Takes array of scripts set via controller and injects them into <head> during render.
    */
    public function injectScripts()
    {
        foreach ($this->_scripts as $script)
        {
            echo($script . "\n");
        }
    }

    /**
    * Inject Meta Data into Header
    *
    * Takes array of meta tags set via controller and injects them into <head> during render.
    */
    public function injectMeta()
    {
        foreach ($this->_meta as $meta)
        {
            echo($meta . "\n");
        }
    }

    /**
    * Set View File
    *
    * Allows override of default view file
    */
    public function setViewFile($file)
    {
        $this->_viewFile = Iceberg::getApplicationPath() . "/Modules/" . $file . ".phtml";
    }

    /**
    * Set Page Title
    *
    * @param String     Title of page
    */
    public function setTitle( $title )
    {
        $this->layoutTitle = htmlentities( trim($title) );
    }

    /**
    * Get Page Title
    */
    public function getTitle()
    {
        return $this->layoutTitle;
    }
}