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

namespace syncro;

if (isset($_SERVER['SCRIPT_FILENAME']) && (str_replace('\\', '/', __FILE__) == $_SERVER['SCRIPT_FILENAME'])) {
    exit('Accesso diretto non consentito');
}


// Classe indirizzo sincronizzazione
class SyncronizeOc {
	private $_table;
	private $id;
	private $table_oc;
	private $table_gz;
	private $id_oc;
	private $id_gz;
	private $date_created;
	private $data_update;

	public function __construct( ) {
		global $gTables;
		$this->_table = $gTables['syncronize_oc'];
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
		return $this->date_update;
	}

	public function setData( $table_oc, $table_gz, $id_oc, $id_gz ) {
		$this->table_oc = $table_oc;
		$this->table_gz = $table_gz;
		$this->id_oc = $id_oc;
		$this->id_gz = $id_gz;
	}

	public function add( ) {
		global $gTables;
		$this->date_created = time();
		$this->date_update = time();

	}

	public function update( ) {
		global $gTables;
		$this->date_update = time();
		

	}


}

