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

namespace Database\Driver\Mysqli;

// Classe anagrafiche sincronizzazione
class Mysqli implements \Database\Driver\Driver  {
	
	private $_link;

	private $_resource;

	public function __construct( $host, $user, $password, $dbname, $port ) {
		$this->connect($host, $user, $password, $dbname, $port);
	}

	public function connect( $host, $user, $password, $dbname, $port ) {
		$this->_link = @mysqli_connect($host, $user, $password, $dbname);
		if ( mysqli_connect_errno() ) {
  			die ( 'Error connect database: ' . mysqli_connect_error());
		}
		$this->query( "/*!50701 SET SESSION sql_mode='' */");
                mysqli_set_charset( $this->_link, 'utf8' );
	}

	/**
	 * Execute a query
	 */
	public function query( $sql ) {
		$this->_resource = @mysqli_query($this->_link, $sql);
		if (  $this->_resource  === FALSE ) {
			$error = $this->error(); echo $error;
			if ( $error != '' ) {
				$this->close();
				die ('Errors query: ' . $sql . ' | '.$error);
			}
		}
		return is_object( $this->_resource ) ? $this->result() : $this->_resource;
	}

	/**
	 * Return a result of a query
	 */
	public function result( ) {
		return new Result( $this->_resource );	
	}

	/**
	 * Retun Last id Insert
	 *
	 * @return int
	 */
	public function lastInsertId() {
		return mysqli_insert_id($this->_link);
	}

	/**
	 * Escape string
	 *
	 * @param string
	 * @return string
	 */
	public function escape( $str ) {
		return mysqli_real_escape_string($this->_link, $str);
	}

	/**
	 * Return last error
	 *
	 * @return string
	 */
	public function error() {
		return	mysqli_error($this->_link);
	}

	public function close() {
		mysqli_close($this->_link);
	}
}

