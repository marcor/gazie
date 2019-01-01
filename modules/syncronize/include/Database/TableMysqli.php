<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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

if (isset($_SERVER['SCRIPT_FILENAME']) && (str_replace('\\', '/', __FILE__) == $_SERVER['SCRIPT_FILENAME'])) {
    exit('Accesso diretto non consentito');
}


// Classe anagrafiche sincronizzazione
abstract class TableMysqli  {
	
	private $_table;
	private $_columns;
	private $_values;
	

	public function __construct( $table ) {
	  	global $gTables;
		$this->_table = $gTables[$table];
		$rs = gaz_dbi_query( "SHOW COLUMNS FROM " . $this->_table );
		while ( $row=gaz_dbi_fetch_array($rs)) {
			$this->_columns[$row[0]] = [
				'name'=>$row[0],
				'type'=>$row['Type']
			];
//			$this->_values[$row[0]] = NULL;
		}
	}
	
	/**
	 * Update or add a single row of data
	 */
	public function save() {
		// Control id exists
		$values = $this->getValues();
		if ( !isset($values['id']) ) {
			// inserimento
			$query = $this->createQueryInsert();
		} else {
			// update

		}
		return $this->execute($query);
	}

	public function setValues(array $values) {
		// Le colonne non corrispondono
		if ( count($values) > count($this->getColumns() ) )
			return FALSE;
		$cols = $this->getColumns();
		foreach( $values as $k=>$v ) {
			if ( isset($cols[$k]) ) {
				$this->_values[$k] = $v;
			}
		}
		return true;
	}

	/**
	 * Insert values to records
	 */
	public function createQueryInsert( ) {
		if ( ! $this->getValues() )
			return FALSE;
		foreach( $this->getValues() as $k=>$v ) {
				$fields[] = "`$k`";
				$vs[$k] = "'$v'";
			}
		$table = $this->getTableName();
		$query = "INSERT INTO `".$table."` ( " . implode(',',$fields) . " ) VALUES ( " . implode(',',$vs) . " );";
		return $query;
	}

	/**
	 * Getting all
	 *
	 * @return array of Syncro\Anagr
	 */
	public static function getAll() {
	  return $rs_all;	
	}

	public function getColumns() {
	  return $this->_columns;
	}

	public function getValues() {
	  return $this->_values;
	}

	/**
	 * Get name of table
	 */
	public function getTableName() {
	  return $this->_table;
	}

	public function execute( $query ) {
          global $link;
 	  $result = mysqli_query($link, $query);
  	  if (!$result)
		  die("Error sql query:<b> $query </b> " . mysqli_error($link));
	  return $result;
	}		

	/**
	 * Return a resource of data
	 */
	public function getResource( $where, $orderby ) {
		$rs = gaz_dbi_dyn_query('*', $this->getTableName() , $where, $orderby);
		return $r;
	}

	public function getRowsToArray($where, $orderby ) {
		$rs = $this->getResource( $where, $orderby);
	 	$rs_all = array();
	 	while ( $r = gaz_dbi_fetch_array($rs) ) { 
			$rs_all[] = $r;
		}
		return $rs_all;
	}
}

