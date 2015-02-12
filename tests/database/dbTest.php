<?php
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

	// Verifca  test database fallito
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

	// Verifca  test database fallito
	public function testQueryDbiSelect()
	{
		connectIsOk();
		$result = gaz_dbi_query('SELECT * FROM gaz_config;', true );
		
		$this->assertEquals( is_integer($result), true );
	}
	
	// Verifca  test database fallito
	public function testQueryDbiUpdate()
	{
		connectIsOk();
		$result = gaz_dbi_query("UPDATE  gaz_config SET cvalue = 95 WHERE variable = 'giornipass';", false );
	
		$this->assertEquals( is_bool($result ), true );
	}
	
	// Verifca  test database fallito
	public function testResourceDbi()
	{
		connectIsOk();
		$result = gaz_dbi_query("UPDATE  gaz_config SET cvalue = 95 WHERE variable = 'giornipass';", false );
	
		$this->assertEquals( is_bool($result ), true );
	}
	
	// Verifica la funzione testTable
	public function testRecordCount()
	{
		connectIsOk();
		$count= gaz_dbi_record_count( 'gaz_country','');
		$this->assertEquals( $count, 196 );
		
		$count= gaz_dbi_record_count( 'gaz_country',"iso ='IT'");
		$this->assertEquals( $count, 1);
	}
	
	// Verifica la funzione testTable 
// 	public function testTableUpdate()
// 	{
// 		connectIsOk();
// 		$table = 'vettor';
// 		$columns=array('codice','ragione_sociale','indirizzo','cap','citta','provincia','partita_iva','codice_fiscale','n_albo','descri','telefo','annota', 'adminid');
// 		$newValue['adminid'] = 'amministratore';
// 		$newValue['ragione sociale'] = 'SDA';
// 		$codice = array(12);
// 		tableUpdate($table, $columns, $codice, $newValue);

// 	}
}