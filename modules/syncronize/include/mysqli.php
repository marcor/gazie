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
	
	public function __construct( $table ) {
	  	global $gTables;
		$this->_table = $gTables[$table];
	}
	
	/**
	 * Update or add a single row of data
	 */
	abstract public function save() {
	}


	/**
	 * Getting all
	 *
	 * @return array of Syncro\Anagr
	 */
	public static function getAll() {
	  global $gTables;
	  $orderby="ragso1 DESC";
	  $where = NULL;
	  $rs = gaz_dbi_dyn_query('*', $gTables['anagra'] , $where, $orderby);
	  $rs_all = array();
	  while ( $r = gaz_dbi_fetch_array($rs) ) { 
			$anagr = new Anagr($r['ragso1'], $r['sexper'],$r['codfis'],$r['pariva']);
			$anagr->setId($r['id']);
			$anagr->setSedleg($r['sedleg']);
			$anagr->setLegrap($r['legrap_pf_nome']);
			$anagr->setNascita($r['datnas'],$r['luonas'],$r['luonas'],$r['pronas'],$r['counas']);
			$anagr->setAddress($r['indspe'],$r['capspe'],$r['citspe'],$r['prospe']);
			$anagr->setCountry($r['country']);
			$anagr->setIdCurrency($r['id_currency']);
			$anagr->setIdLanguage($r['id_language']);
			$anagr->setCoordinate($r['latitude'],$r['longitude']);
			$anagr->setTelefono($r['telef']);
			$anagr->setFax($r['fax']);
			$anagr->setCell($r['cell']);
			$anagr->setFeCodUnivoco($r['fe_cod_univoco']);
			$anagr->setEmail($r['email']);
			$anagr->setEmailPec($r['pec_email']);
			$anagr->setFattureByEmail($r['fatt_email']);
			$rs_all[]=$anagr;
		}
	  return $rs_all;	
	}

	/**
	 * Get name of table
	 */
	public function getTableName() {
	  return $this->_table;
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

