<?php

/**
 * Class for autoloader the databases
 *
 */
class Autoloader {

    public static $loader;

    public static function init()
    {
        if (self::$loader == NULL)
            self::$loader = new self();

        return self::$loader;
    }

    public function __construct()
    {
        spl_autoload_register(array($this,'opencart'));
        spl_autoload_register(array($this,'gazie'));
        spl_autoload_register(array($this,'database'));
        spl_autoload_register(array($this,'syncro'));
    }

    public function opencart($class)
    {
	$file = str_replace("\\","/",$class).".php";
	if ( ! is_null($file) )
		$this->include_file($file);
    }

    public function gazie($class)
    {
	$file = str_replace("\\","/",$class).".php"; 
	if ( ! is_null($file) )
		$this->include_file($file);
    }

    public function database($class)
    { 
	$file = str_replace("\\","/",$class).".php";
	if ( ! is_null($file) )
		$this->include_file($file);
    }

    public function syncro($class)
    { 
	$file = str_replace("\\","/",$class).".php";
	if ( ! is_null($file) )
		$this->include_file($file);
    }

    public function include_file($file) {
	include_once($file);
    }
}

//call
Autoloader::init();
?>
