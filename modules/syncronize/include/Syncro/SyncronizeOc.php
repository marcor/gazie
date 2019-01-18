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

namespace Syncro;

if (isset($_SERVER['SCRIPT_FILENAME']) && (str_replace('\\', '/', __FILE__) == $_SERVER['SCRIPT_FILENAME'])) {
    exit('Accesso diretto non consentito');
}


// Classe indirizzo sincronizzazione
class SyncronizeOc extends \Database\TableMysqli {
	private $id;
	private $table_oc;
	private $table_gz;
	private $id_oc;
	private $id_gz;
	private $date_created;
	private $date_updated;

	public function __construct( ) {
		parent::__construct('syncronize_oc');
	}

	public function getId( ) {
		return $this->id;
	}

	public function getTableOc( ) {
		return $this->table_oc;
	}
	
	public function getTableGz( ) {
		return $this->table_gz;
	}

	public function getIdOc( ) {
		return $this->id_oc;
	}

	public function getIdGz( ) {
		return $this->id_gz;
	}

	public function getDateCreated( ) {
		return $this->date_created;
	}

	public function getDateUpdate( ) {
		return $this->date_updated;
	}

	public function setDateCreated( $date_created ) {
		$this->date_created=$date_created;
	}

	public function setDateUpdate( $date_upd ) {
		$this->date_updated=$date_upd;
	}

	public function setData( $table_oc, $table_gz, $id_oc, $id_gz ) {
		$this->table_oc = $table_oc;
		$this->table_gz = $table_gz;
		$this->id_oc = $id_oc;
		$this->id_gz = $id_gz;
	}

	public function add( ) {
		$this->date_created = date('Y-m-d H:i:s');
		$this->date_updated = date('Y-m-d H:i:s');
	}

	public function update( ) {
		$this->date_updated = date('Y-m-d H:i:s'); 
	}

	public function save() {
		$values = array(
			'id'		=>	$this->getId(),
			'table_oc'	=>	$this->getTableOc(),
			'table_gz'	=>	$this->getTableGz(),
			'id_oc'		=>	$this->getIdOc(),
			'id_gz'		=>	$this->getIdGz(),
			'date_created'	=>	$this->getDateCreated(),
			'date_update'	=>	$this->getDateUpdate(),
		);
		$this->setValues($values);
	 	return	parent::save();			
	}

	public function getFromOc( $table_oc, $id_oc ) {
		$query = new \Database\Query( $this->getTableName() );
		$where = "`table_oc` = '$table_oc' AND `id_oc` = $id_oc";
		$order_by = '`id_oc` DESC';
		$query->createSelect( NULL, $where, $order_by );
		$rs = $query->execute();
		if ( count($rs) !== 1 ) {
		//	$q= new \Database\Query( $this->getTableName() );
		//	$q->create
			return false;
		}
		foreach ($rs as $r) {
			$this->id = $r['id']; 
			$this->table_oc = $r['table_oc']; 
			$this->table_gz = $r['table_gz']; 
			$this->id_oc = $r['id_oc']; 
			$this->id_gz = $r['id_gz']; 
			$this->date_created = $r['date_created']; 
			$this->date_updated = $r['date_update'];
		}
		return true;
	}
}

