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

namespace GAzie;

// Classe anagrafiche sincronizzazione
class Product extends \Database\TableMysqli {
	private $codice;
	private $descri;
	private $codice_fornitore;
	private $quality;
	private $ordinabile;
	private $movimentabile;
	private $good_or_service;
	private $lot_or_serial;
	private $image;
	private $barcode;
	private $unimis;
	private $larghezza;
	private $lunghezza;
	private $spessore;
	private $bending_moment;
	private $catmer;
	private $ragstat;
	private $preacq;
	private $preve1;
	private $preve2;
	private $preve3;
	private $preve4;
	private $sconto;
	private $web_mu;
	private $web_price;
	private $web_multiplier;
	private $web_public;
	private $depli_public;
	private $web_url;
	private $aliiva;
	private $retention_tax;
	private $last_cost;
	private $payroll_tax;
	private $scorta;
	private $riordino;
	private $uniacq;
	private $classif_amb;
	private $mostra_qdc;
	private $peso_specifico;
	private $volume_specifico;
	private $dose_massima;
	private $rame_metallico;
	private $tempo_sospensione;
	private $pack_units;
	private $codcon;
	private $id_cost;
	private $annota;
	private $adminid;
	private $last_modified;
	private $clfoco;
	private $last_used;

	
	public function __construct($ragso1, $sex, $codfis, $pariva) {
		parent::__construct( 'artico' );
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
		$fields = get_object_vars($this);
		$vals=[];
		foreach( $fields as $k =>$v) {
			if ( !is_null($v) ) {
				$vals[$k] = $v;
			}
		}	
		if ( !$this->setValues($vals) ) 
			return false;
		return parent::save();
	}

	/**
	 * Return anagr by id
	 *
	 */
	public static function getById( $id ) {
	  global $gTables;
	  $where = "`id` = $id "; 
	  $orderby = '';
	  $rs = gaz_dbi_dyn_query('*', $gTables['anagra'] , $where, $orderby);
	  if ( $r = gaz_dbi_fetch_array($rs) ) {
		$anagr = new Product($r['ragso1'], $r['sexper'],$r['codfis'],$r['pariva']);
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
	  global $gTables;
	  $orderby="codice DESC";
	  $where = NULL;
	  $rs = gaz_dbi_dyn_query('*', $this->getTable() , $where, $orderby);
	  $rs_all = array();
	  while ( $r = gaz_dbi_fetch_array($rs) ) { 
			$anagr = new Product();
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

}

