<?php

include 'tests/startup.php';

class dbTest extends GazieTest {
	private $database;
	private $path;
	
	public function setup() {
		//var_dump($GLOBALS);

	}
	
	public function testConnectionDBOK() {
		$result = connectIsOk();
		$this->assertEquals($result, true);
	}
	
	// Verifca test fallito
	public function testConnectionDBFail()
	{
			global $User;
			$user_bck = $User;
			$User = 'Bad_user_test';
			
			$result = connectIsOk();
			$this->assertEquals($result, false);
		//	$User = $user_bck;
	}

	// Verifca test database
	public function testDatabaseOK()
	{		
		connectIsOk();
		$result = databaseIsOk();
		$this->assertEquals($result, true);
	}

	// Verifca test database fallito
	public function testDatabaseFail()
	{
		global $Database;
		$db_bck = $Database;
		$Database = 'BadDatabase';
		connectIsOk();
		$result = databaseIsOk();
		$this->assertEquals($result, false);
		$Database  = $db_bck;
	}
}