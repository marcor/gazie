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

namespace \GAzie;

use \Gazie\Interfacce\Fattura as IFattura;

/**
 * Fattura implementazione classe Astratta 
 */
abstract class Fattura implements IFattura {

	private $numdoc;
	
	private $azienda;

	private $rows;

	private $anagr_destination;
	
	private $date_registration;

	private $date_invoice;	
	
	private $sconto;	

	private $spese_accessorie;

	public function __construct() {
		$azienda = new Azienda();
		$this->azienda = $azienda->getCurrent();
		$this->rows = [];
		$this->sconto = 0;
		$this->date_registration = time();
		$this->date_invoice = time();
		$this->spese_accessorie = [];
	}

	public function getNum() {
		return $this->numdoc;
	}

	public function setNum($num_fatt){
		$this->numdoc = $num_fatt;
	}

	/**
	 * Numero di righe
	 *
	 * @return integer
	 */
	public function getNumRows(){
		return count($this->rows);
	}

	public function getRow( int $num_row ){
		return $this->rows;
	}

	public function setRow( FatturaRow $row ) {
		if ( !isset( $this->rows[ $row->getNum() ] ) ) {
			$this->rows[ $row->getNum() ] = $row;
		}
	}

	public function getData() {
		return $this->date_invoice;	
	}

	public function setData( int $data ) {
		$this->date_invoice = $data;
	}
	
	public function setDataRegistrazione( int $data ) {
		$this->date_registration = $data;
	}

	public function getDataRegistrazione() {
		return $this->date_registration;
	}

	/**
	 *
	 * @return array (imponibile, iva)
	 */
	public function getTotals();

	public function getSconto() {
		return $this->sconto;
	}

	public function setSconto( float $sconto ) {
		$this->sconto = $sconto;
	}

	/**
	 * Spese come trasporto, incasso, fatturazione, ecc.. (imponibile e tipo iva)
	 * @return array of SpeseAccessorie
	 */
	public function getSpeseAccessorie() {
		return $this->spese_accessorie;
	}

	public function addSpeseAccessorie( SpeseAccessorie $spese ) {
		$this->spese_accessorie[] = $spese;
	}

}

