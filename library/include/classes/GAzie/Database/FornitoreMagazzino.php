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

namespace GAzie\Database;


/**
 * Class for manage azienda table
 */
class FornitoreMagazzino extends \Database\Table {
	
	public function __construct( $id = NULL ) {
		parent::__construct('fornitore_magazzino');
		$this->load( $id );
	}

	public function getAllFromCodice( $codice_articolo ) {
		$where = "`codice_magazzino` = '$codice_articolo'";
		$sql = $this->query->select()
			->from( $this->getTableName())
			->where($where);

		$this->execute($sql);
		return $this->getResult()->asArray();
	}

	/**
	 * Return true if exist anagr_id and code article
	 * 
	 *
	 * @return boolean
	 */
	public function exist( $anagr_id, $codice_articolo ) {
		$where = "`id_anagr` = '$anagr_id' AND `codice_magazzino` = '$codice_articolo'";
		$sql = $this->query->select()
			->from( $this->getTableName())
			->where($where);

		$this->execute($sql);
		return $this->getResult()->count() >= 1;

	}


}

