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

namespace GAzie;

/**
 * Class for manage azienda table
 *
 */
class Azienda extends \Database\TableMysqli {
	
	public function __construct( $id = NULL ) {
		parent::__construct('aziend');
		if ( $id )
			$this->load( $id );
	}
	
	/**
	 * Update or add 
	 */
	public function add() {
		// Control exist
		
		// Insert or Update
	}
	
	/**
	 * Save anagr or update
	 */
	public function save() {
		return parent::save();
	}

	/**
	 * Return anagr by id
	 *
	 */
	public static function getById( $id ) {
	  if ( $r = gaz_dbi_fetch_array($rs) ) {
		$anagr = new Anagra($r['ragso1'], $r['sexper'],$r['codfis'],$r['pariva']);
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
	  	return $anagr;
	  } else {
		return false;
	  }
	}

	/**
	 * Getting all
	 *
	 * @return array of Syncro\Anagr
	 */
	public static function getAll() {
	}

	public function setCodice( $id ) {
		$this->set('codice', $id);
	}

	public function getCodice() {
		return $this->get('codice');
	}

	public function exist() {
		// Controllo se esiste l'azioneda
		$query = new \Database\Query( $this->getTableName() );
		$where = "`codice` = '" . $this->getCodice() . "'";  
		$query->createSelect( NULL, $where );
		$result = $query->execute(); 
		return count($result) > 0;
	}

}

