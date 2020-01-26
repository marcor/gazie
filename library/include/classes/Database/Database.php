<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
  (http://www.devincentiis.it)
  <http://gazie.sourceforge.net>
  --------------------------------------------------------------------------
  Questo programma e` free software;   e` lecito redistribuirlo  e/o
  modificarlo secondo i  termini della Licenza Pubblica Generica GNU
  come e` pubblicata dalla Free Software Foundation; o la versione 2
  della licenza o (a propria scelta) una versione successiva.

  Questo programma  e` distribuito nella speranza  che sia utile, ma
  SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
  NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
  veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

  Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
  Generica GNU insieme a   questo programma; in caso  contrario,  si
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */

namespace Database;


/**
 *  Class DB for call query 
 */
class Database {

	private static $instance = null;	

	private $driver;
	
	/**
	 * 
	 */
	public function __construct( array $config, $driver ) {
		switch ( $driver ) {
			case "mysqli":
				$this->driver = new \Database\Driver\Mysqli\Mysqli( $config['host'], $config['user'], $config['password'], $config['database'], $config['port'] );
				break;
			default:
				die( 'Not defined driver database');
		}
	}

	/**
	 * Creation query
	 */
	public function query() {
		return new \Database\ORM\DB($this->driver);
	}	

	/**
	 * Execute query
	 *
	 * @return \Database\Result or Boolean 
	 */
	public function execute( \Database\ORM\QueryInterface $query ) {
		return $this->driver->query( $query->write() );
	}

	
	public function table($name) {
	
	}

	/**
	 * Return lastInsertId
	 *
	 * @return Integer
	 */
	public function lastInsertId( ) {
		return $this->driver->lastInsertId( );
	}

	public static function connect( $host, $user, $password, $dbname, $port, $driver = 'mysqli' ) {
		$config = [
			'host'	=>	$host,
			'user'	=>	$user,
			'password'	=>	$password,
			'database'	=>	$dbname,
			'port'		=>	$port,
		];
                if (  self::$instance == null ) {
                        self::$instance = new Database($config, $driver);
                }
                return self::$instance;
        }

}

