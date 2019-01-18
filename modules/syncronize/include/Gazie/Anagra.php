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

namespace Gazie;

if (isset($_SERVER['SCRIPT_FILENAME']) && (str_replace('\\', '/', __FILE__) == $_SERVER['SCRIPT_FILENAME'])) {
    exit('Accesso diretto non consentito');
}


// Classe anagrafiche sincronizzazione
class Anagra extends \Database\TableMysqli {
	private $id;
	private $ragso1;
	private $ragso2;
	private $sedleg;
	private $legrap_pf_nome;
	private $sexper;
	private $datnas;
	private $luonas;
	private $pronas;
	private $counas;
	private $indspe;
	private $capspe;
	private $citspe;
	private $prospe;
	private $country;
	private $id_currency;
	private $id_language;
	private $latitude;
	private $longitude;
	private $telefo;
	private $fax;
	private $cell;
	private $codfis;
	private $pariva;
	private $fe_cod_univoco;
	private $email;
	private $pec_email;
	private $fatt_email = 0;

	
	public function __construct($ragso1, $sex, $codfis, $pariva) {
		parent::__construct('anagra');
		$this->setRagso( $ragso1);
		$this->setSexper( $sex );
		$this->setCodfis( $codfis);
		$this->setPariva( $pariva );	
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
	  global $gTables;
	  $orderby="ragso1 DESC";
	  $where = NULL;
	  $rs = gaz_dbi_dyn_query('*', $gTables['anagra'] , $where, $orderby);
	  $rs_all = array();
	  while ( $r = gaz_dbi_fetch_array($rs) ) { 
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
			$rs_all[]=$anagr;
		}
	  return $rs_all;	
	}

	public function setId( $id ) {
		$this->id = $id;
	}

	public function setRagso( $ragso1, $ragso2 = NULL ) {
		$this->ragso1 = $ragso1;
		$this->ragso2 = $ragso2;
	}

	public function setSedleg( $sedleg ) {
		$this->sedleg = $sedleg;
	}

	public function setLegrap( $legrap_pf_nome ) {
		$this->legrap_pf_nome = $legrap_pf_nome;
	}

	public function setSexper( $sexper ) {
		$this->sexper = $sexper;
	}

	public function setNascita( $datnas, $luonas = NULL, $pronas = NULL, $counas = NULL ) {
		$this->datnas = $datnas;
		$this->pronas = $pronas;
		$this->luonas = $luonas;
		$this->counas = $counas;	
	}

	public function setAddress( $indspe, $capspe, $citspe, $prospe ) {
		$this->indspe = $indspe;
		$this->capspe = $capspe;
		$this->citspe = $citspe;
		$this->prospe = $prospe;
	}

	public function setCountry( $country ) {
		$this->country = $country;
	}

	public function setIdCurrency( $id_currency ) {
		$this->id_currency = $id_currency;
	}

	public function setIdLanguage( $id_language ) {
		$this->id_language = $id_language;
	}

	public function setCoordinate( $latitude, $longitude ) {
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}

	public function setTelefono( $telef ) {
		$this->telefo = $telefo;
	}

	public function setFax( $fax ) {
		$this->fax = $fax;
	}

	public function setCell( $cell ) {
		$this->cell = $cell;
	}

	public function setCodFis( $codfis ) {
		$this->codfis = $codfis;
	}

	public function setParIva( $pariva ) {
		$this->pariva = $pariva;
	}

	public function setFeCodUnivoco( $fe_cod_univoco ) {
		$this->fe_cod_univoco = $fe_cod_univoco;
	}

	public function setEmail( $email ) {
		$this->email = $email;
	}

	public function setEmailPec( $pec_email ) {
		$this->pec_email = $pec_email;
	}

	public function setFattureByEmail( $fatt_email ) {
		$this->fatt_email = $fatt_email;
	}

	public function getId( ) {
		return $this->id;
	}

	public function getRagso1( ) {
		return $this->ragso1;
	}

	public function getRagso2( ) {
		return $this->ragso2;
	}

	public function getSedleg( ) {
		return $this->sedleg;
	}

	public function getLegrap(  ) {
		return $this->legrap_pf_nome;
	}

	public function getSexper( ) {
		return $this->sexper;
	}

	public function getDataNascita( ) {
		return $this->datnas;	
	}

	public function getProvNascita( ) {
		return $this->pronas;	
	}

	public function getLuogoNascita( ) {
		return $this->luonas;	
	}

	public function getCittaNascita( ) {
		return $this->counas;	
	}

	public function getAddress( ) {
		return $this->indspe;
		$this->capspe = $capspe;
		$this->citspe = $citspe;
		$this->prospe = $prospe;
	}

	public function getCap( ) {
		return $this->capspe;
	}

	public function getCitta( ) {
		return $this->citspe;
	}

	public function getProvincia( ) {
		return $this->prospe;
	}

	public function getCountry( ) {
		return $this->country;
	}

	public function getIdCurrency( ) {
		return $this->id_currency;
	}

	public function getIdLanguage( ) {
		return $this->id_language;
	}

	public function getCoordinate( ) {
		return array( $this->latitude , $this->longitude);
	}

	public function getTelefono( ) {
		return $this->telefo;
	}

	public function getFax( ) {
		return $this->fax;
	}

	public function getCell( ) {
		return $this->cell;
	}

	public function getCodFis( ) {
		return $this->codfis;
	}

	public function getParIva( ) {
		return $this->pariva;
	}

	public function getFeCodUnivoco( ) {
		return $this->fe_cod_univoco;
	}

	public function getEmail( ) {
		return $this->email;
	}

	public function getEmailPec( ) {
		return $this->pec_email;
	}

	public function getFattureByEmail( ) {
		return $this->fatt_email;
	}

	public function exist() {
		// Controllo ragso1
		$query = new \Database\Query($this->getTableName());
		$where = "`ragso1` = '" . strtoupper(trim($this->getRagso1())) . "' " . 
			 "OR `cell` = '" . trim($this->getCell()) . "' " . 
			 "OR `e_mail` = '" . strtolower(trim($this->getEmail())) . "' ";
		$query->createSelect(NULL, $where );
		$result = $query->execute(); 
		return count($result) > 0;
	}

	public static function  syncCustomer( \Syncro\Interfaces\ICustomer $customer ) {
		// Verifica esistenza customer
		$sync = new \Syncro\SyncronizeOc; 
		if ( $sync->getFromOc('customer', $customer->getCustomerId() ) ) {
			// Gia sincronizzato
			// Ritorna id Gazie
			echo "Non posso sincronizzare perchè già inserito";

		} else {
			// Non sincronizzato
			// Aggiungi il customer
			$anagr = new Anagra($customer->getRagso1(), "G","00000000000","00000000000");
			$anagr->setEmail($customer->getEmail());
			$anagr->setCell($customer->getTelephone());
			// Inserisci indirizzo
			// CAP, SPEDIZIONE, CITTA, PROVINCIA
			$anagr->setAddress($r['indspe'],$r['capspe'],$r['citspe'],$r['prospe']);

			$exist = $anagr->exist();
			if ( ! $exist ) {
				// Salva ed aggiungi id customer

				return $anagr->save();
			} else {
				return FALSE;
			}
		}
	}
}

