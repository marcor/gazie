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
 *  Class for creation query
 *  from databasae
 *
 */
class Query {
	
	private $_table;
	private $_columns;
	private $_pk;
	private $_type; // SELECT, INSERT, UPDATE, DELETE	
	private $_query;

	public function __construct( $table, array $columns, $pk ) {
		$this->_query = '';
		$this->_table = $table;
		$this->_columns = $columns;
		$this->_pk = $pk;
	}

	public function getTable() {
		return $this->_table;
	}

	/**
	 * Controlla colonna esistenza
 	 */
	private function controlColumns( $column ) {
		if ( ! isset( $this->_columns[$column] ) )
			die ("La colonna '$column' non esiste nella tabella '" . $this->getTable() . "'");
		return true;
	}

	/**
	 * Insert values to records
	 */
	public function createInsert( array $values ) {
		if ( empty($values) )
			return FALSE;
		foreach( $values as $k=>$v ) {
			$this->controlColumns($k);
			$fields[] = "`$k`";
			$vs[$k] = "'$v'";
		}
		$query = "INSERT INTO `".$this->getTable()."` ( " . implode(',',$fields) . " ) VALUES ( " . implode(',',$vs) . " );";
		$this->_type = "INSERT";
		$this->_query = $query;
		return $query;
	}

	/**
	 * Update values
	 */
	public function createUpdate( array $values, string $where = NULL ) {
		if ( empty($values) )
			return FALSE;
		foreach( $values as $k=>$v ) {
			$this->controlColumns($k);	
			$f[] = "`$k` = '$v'";
		}
		$query = "UPDATE `".$this->getTable()."` SET  " . implode(',',$f);
		if ( $where )
			$query .= " WHERE $where";
		$this->_type = "UPDATE";
		$this->_query = $query;
		return $query;
	}
	
	/**
	 * Delete values
	 */
	public function createDelete( string $where = NULL ) {
		$query = "DELETE FROM `".$this->getTable()."` ";
		if ( $where )
			$query .= " WHERE $where";
		$this->_type = "DELETE";
		$this->_query = $query;
		return $query;
	}

	/**
	 * Select values
	 */
	public function createSelect( array $values = NULL, string $where = NULL, $orderby = NULL, $limit = 0, $passo = 2000000, $groupby = NULL ) {
		$this->_type = "SELECT";
		if ( empty($values) || ! $values ) {
			$query = "SELECT * FROM " . $this->getTable();
		} else {
			foreach( $values as $k=>$v ) {
				$this->controlColumns($k);	
				$fields[] = "`$k`";
			}
			$query = "SELECT ".implode(",",$fields)." FROM " . $this->getTable();
		}
		if ( $where ) {
			$query .= " WHERE $where";
		}
		if ( $groupby )
			$query .= " GROUP BY $groupby";
		if ( $orderby )
			$query .= " ORDER BY $orderby";
		if ( $limit && $passo )
			$query .= " LIMIT $limit, $passo";
		$this->_query = $query;
		return $query;
	}

	public function setQuery( $query ) {
	  if ( is_string( $query ) ) {
  	    $this->_query = $query;
	  }
	}

	public function execute( $query = NULL ) {
	  if ( ! $query )
 	    $query = $this->_query;
          global $link;
 	  $rs = mysqli_query($link, $query);
  	  if (!$rs) {
		  die("Error sql query:<b> $query </b> " . mysqli_error($link));
	  }
	  if ( $this->_type === 'INSERT' ) {
		return mysqli_insert_id($link);
	  }
	  if ( $this->_type === 'SELECT' ) {
	 	$rs_all = array();
	 	while ( $row = gaz_dbi_fetch_array($rs) ) { 
			$rs_all[] = $row;
		}
		return $rs_all;
	  }
	  return $rs;
	}

	public function __toString() {
		return $this->_query;
	}	
}

