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

use \GAzie\GAzie;
use \Database\ORM\DB;
use \Database\ORM\QueryInterface;

/**
 *  Abstract class for consult table of db
 *
 */
abstract class Table  {

	private $_loaded;	
	private $_name;
	private $_pk;
	private $_columns;
	private $_values;

	// var \Database\ORM\DB
	protected $query;
	private $_result;

	public function __construct( $table_name ) {
		$GAzie_config = GAzie::factory()->getConfig();
		$this->_name = $GAzie_config->getTabelle($table_name);
		$this->query = GAzie::factory()->getDatabase()->query();
		$this->_values = [];
		$this->_loaded = FALSE;
		$this->initColumns();		
	}

	/**
	 * Return \Database\Result object or boolean
	 *
	 * @return \Database\Result or Boolean
	 */
	public function execute( \Database\ORM\QueryInterface $query ) {
		$this->_result = GAzie::factory()->getDatabase()->execute($query);
		if ( is_bool($this->_result) ) {
			return $this->_result;
		} else { 
		  if ( $this->_result->count() === 1 ) {
			$this->_setValues($this->_result);
			$this->_loaded = TRUE;
			return TRUE;
		  } else {
			$this->_loaded = FALSE;
			return $this->_result;
		  }
		}
		return $this->_result;
	}

	# Create a query manual
	public function setQuery( $sql ) {
		$this->query->setQuery( $sql );
	}

	private function _setValues( $result ) {
		foreach ( $result->asArray()[0] as $k=>$v ) {
			$this->$k = $v;
		}
	}

	private function initColumns() {
		$sql = $this->query->show()->columns($this->_name);
		$result = $this->execute( $sql );
		foreach ( $result->asArray() as $row ) {
			$this->_columns[$row['Field']] = [
                                'name'=>$row['Field'],
                                'type'=>$row['Type']
			];
			if ( $row['Key'] == 'PRI' )
                                $this->_pk = $row['Field'];
		}
	}

	public function getPrimaryKeyName() {
		return $this->_pk;
	}

	/**
	 * Return a single record from 
	 * prymary key
	 */
	public function load( $key ) {
		$pk = $this->getPrimaryKeyName();
		$sql = $this->query->select()
			->from($this->_name)
			->where("$pk = '$key'");
			
		$this->execute( $sql );
		if ( $this->_result->count() == 1 ) {
			$this->_loaded = TRUE;
			foreach( $this->_result->asArray() as $row ) {
				foreach ( $row as $key => $value ) {
					$this->_values[$key] = $value;
				}	
			}
		} else {
			$this->_loaded = FALSE;
			$this->_values = [];
		}
		return $this->_loaded;
	}

	public function __get( $name ) {
       		if ( isset($this->_values[$name]) )
			return $this->_values[$name];
		return;
	}
	
	/**
	 * Setting a value of field
	 */
	public function __set( $name, $value ) {
		$columns = $this->getColumns();
		if ( isset( $columns[$name] ) )
			$this->_values[$name]=$value;
	}

	/**
	 * Verify loaded 
	 *
	 * @return boolean
	 */
	public function loaded() {
		return $this->_loaded;
	}

	/**
	 * Return the result of a load
	 * by prymary key
	 *
	 */
	public function getResult() {
	#	if ( $this->_loaded )
			return $this->_result;
	}


	/**
	 * Update or add a single row of data
	 */
	public function save() {
		// Control id exists
		$values = $this->getValues();
		if ( ! $this->loaded() )  {
			// inserimento
			$sql = $this->query->insert()
				->from($this->getTableName())
				->columns( $this->_values );
			$result = $this->execute($sql); 
		} else {
			// update
			$pk = $this->getPrimaryKeyName();
			$value = $this->$pk; var_dump($pk);
			$sql = $this->query->update()
				->from($this->getTableName())
				->columns( $this->_values )
				->where("$pk = '$value'");
			$result = $this->execute($sql); 
		}
		return $result;
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
	  return $this->_name;
	}

	/**
	 * Return all records from table
	 *
	 * @return \Database\Result
	 */
	public function getAll() {
		$pk = $this->getPrimaryKeyName();
		$sql = $this->query->select()
			->from($this->_name);
			
		return  $this->execute( $sql );
	}

}

