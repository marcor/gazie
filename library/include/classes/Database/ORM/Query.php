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

namespace Database\ORM;

use \Database\Driver\Driver;

/**
 *  Class for creation query
 *  from databasae
 *
 */
abstract class Query implements QueryInterface {

	private $_driver;
	private $_table;
	private $_columns;

	public function __construct( Driver $driver, $table=NULL, $columns = NULL ) {
		$this->_driver = $driver;
		$this->_table = $table;
		$this->columns( $columns);
	}

	public function setDriver( \Database\Driver\Driver $driver ) {
		$this->_driver = $driver;
	}

	public function escape( $str ) {
		if ( ! $str )
			return;
		return $this->_driver->escape($str);
	}

	public function getTable() {
		return $this->_table;
	}

	public function from($table) {
		$this->_table = $table;
		return $this;
	}

	public function columns( $columns ) {
		if ( is_array($columns) )
			$this->_columns = $columns;
		return $this;
	}

	public function getColumns( ) {
		return $this->_columns;
	}

	abstract function write();

	public function __toString() {
		return $this->write();
	}
}

