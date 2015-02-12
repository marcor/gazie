<?php

include(GAZ_ROOT . 'library/include/function.inc.php');

class functionTest extends PHPUnit_Framework_TestCase {
	
	public function setup() {
		
		//var_dump($GLOBALS);
	}
	
	public function additionProvider() 
	{
		return array(
				array(10,20,array(2,4),188.16),
				array(10,200,10,1800.00 ),
				array(1,5,7,4.65)
		);
	}
	
	/**
	 * @dataProvider additionProvider
	 */
	public function testCalcolaImportoRigo($quantita, $prezzo, $sconto, $importo ) {
		$this->assertEquals($importo, CalcolaImportoRigo($quantita, $prezzo, $sconto) );
	}

	
}