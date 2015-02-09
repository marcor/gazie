<?php

define ( 'GAZ_ROOT', '/opt/lampp/htdocs/gazie6/' );

function shutdown()
{
	// This is our shutdown function, in
	// here we can do any last operations
	// before the script is complete.
//	echo "fine";
	try {
		if ( $error = error_get_last()) {

			throw  new Exception("fatal error");
		}
	} 
	 catch  (Exception $e) {
		//	throw  new Exception("fatal error");
	 }
}

class GazieTest extends PHPUnit_Framework_TestCase {
	
	
	public function __construct() {
		parent::__construct();
	}
	
	public function loadModule( $name )
	{
		
	}

}