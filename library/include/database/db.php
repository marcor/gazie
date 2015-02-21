<?php
/**
 * This package is the library to manage Database
 *
 * @author Daniele Frulla
 * @package Library
 * @subpackage Database
 * @version 1.0
 */
class DB {
	private $db;

	public function __construct($driver, $hostname, $username, $password, $database) {
		$class =  ucfirst(strtolower($driver) );
		if ( !class_exists($class) )
		{
			$file = dirname(__FILE__)."/driver/".$driver.".php";
			if ( file_exists($file) )
			{
				include_once $file;
				if ( !class_exists($class) )
					die('Error: Could not load database driver ' . $driver . '!');
			}
			else
				die('Error: Could not load database driver ' . $driver . '!');
		}
		$this->db = new $class($hostname, $username, $password, $database);
	}

	public function query($sql) {
		return $this->db->query($sql);
	}

	public function escape( $value ) {
		return $this->db->escape($value);
	}

	public function countAffected() {
		return $this->db->countAffected();
	}

	public function getLastId() {
		return $this->db->getLastId();
	}
}
