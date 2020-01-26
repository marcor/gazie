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

namespace \GAzie\Interfacce\;


/**
 * Interface for invoice 
 */
interface Fattura  {
	
	public function getNum();

	public function setNum($num_fatt);

	/**
	 * Numero di righe
	 *
	 * @return integer
	 */
	public function getNumRows();

	public function getRow( int $num_row );

	public function setRow( FatturaRow $row );

	public function getData();

	public function setData( int $data );
	
	public function setDataRegistrazione( int $data );

	public function getDataRegistrazione();

	/**
	 *
	 * @return array (imponibile, iva)
	 */
	public function getTotals();

	public function getSconto();

	public function setSconto( float $sconto );

	/**
	 * Spese come trasporto, incasso, fatturazione, ecc.. (imponibile e tipo iva)
	 * @return array of SpeseAccessorie
	 */
	public function getSpeseAccessorie();

	public function addSpeseAccessorie( SpeseAccessorie $spese );

}

