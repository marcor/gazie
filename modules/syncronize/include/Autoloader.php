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
	include_once($file);
     #   $class = preg_replace('/_controller$/ui','',$class);
       
     #   set_include_path(get_include_path().PATH_SEPARATOR.'/controller/');
     #   spl_autoload_extensions('.controller.php');
     #   spl_autoload($class);
    }

    public function gazie($class)
    {
	$file = str_replace("\\","/",$class).".php";
	include_once($file);
      #  $class = preg_replace('/_model$/ui','',$class);
       	
       # set_include_path(get_include_path().PATH_SEPARATOR.'/model/');
     #   spl_autoload_extensions($file);
     #   spl_autoload($class);
    }

    public function database($class)
    { 
	$file = str_replace("\\","/",$class).".php";
	include_once($file);
    #    $class = preg_replace('/_helper$/ui','',$class);

    #    set_include_path(get_include_path().PATH_SEPARATOR.'/helper/');
    #    spl_autoload_extensions('.helper.php');
    #    spl_autoload($class);
    }

    public function syncro($class)
    { 
	$file = str_replace("\\","/",$class).".php";
	include_once($file);
    #    $class = preg_replace('/_helper$/ui','',$class);

    #    set_include_path(get_include_path().PATH_SEPARATOR.'/helper/');
    #    spl_autoload_extensions('.helper.php');
    #    spl_autoload($class);
    }

}

//call
Autoloader::init();
?>

