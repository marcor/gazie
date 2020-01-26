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

namespace GAzie\Database;

/**
 *  Classe per gestione delle righe dei 
 *  documenti
 *
 */
class FatturaRow extends \Database\Table {
	
	public function __construct( $id = NULL ) {
		parent::__construct('rigdoc');
		$this->load( $id );
	}

	/**
	 * Return array of FatturaRow
	 *
	 * @return array of FatturaRow
	 */
	public function getRowsOfFattura( Fattura $fattura ) {
		if ( ! $fattura->loaded() ) 
			return [];

		$where = "id_tes = '". $fattura->id_tes . "'";
		$sql = $this->query->select()
                	->from( $this->getTableName())
			->where( $where );
		echo $sql;
		return $this->execute($sql)->asObject();
	}
}

