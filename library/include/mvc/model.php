<?php
/**
 * Manager of model class
 * 
 * @author Daniele Frulla
 * @package Library
 * @subpackage Mvc
 * @subpackage Model
 * @version 1.0
 */
class ModelFactory
{
	private $path;
	
	private $nameClass;
	
	public function __construct( $path )
	{
		$this->path = PATH_ROOT . '/modules/models/'. $path . '.php';
		$this->nameClass = $this->nameClass( $path );
	}
	
	public function __destruct()
	{
		unset($this->path); 
		unset($this->nameClass);
	}
	
	
	public function nameClass( $path )
	{
		return 'Model'.str_replace('/','', $path );
	}
	
	/**
	 * Return the model class
	 * 
	 * 
	 */
	public function get()
	{
		if ( file_exists( $this->path ))
		{
			include $this->path;
			if (class_exists($this->nameClass ) )
				return new $this->nameClass;
			else
				die( 'Model '. $this->nameClass . ' not found!' );
		}
		else 
			die( 'Model path'. $this->path . ' not found!' );
	}
}



class Model
{	
	private $db;
	
	public function __construct()
	{
		global $database;
		$this->db = $database;
	}
	
	public function getModel( $path )
	{
		$modelFactory = new ModelFactory($path);
		return $modelFactory->get();
	}
	
}