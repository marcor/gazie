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

namespace Database\Driver\Mysqli;

// Classe anagrafiche sincronizzazione
class Result  {
	
	private $_resource;

	public function __construct( $rs ) {
		$this->_resource = $rs;
	}

	/**
	 * Return number of rows
	 *
	 */
	public function count() {
		return mysqli_num_rows( $this->_resource );
	}


	public function asArray() {
		$result = [];
		while ($obj = mysqli_fetch_array($this->_resource, MYSQLI_ASSOC)) {
			$result[]=$obj;
		}
		return $result;
	}

	public function asObject() {
		$result = [];
		while ($obj = mysqli_fetch_object($this->_resource)) {
    			$result[]=$obj;
		}
		return $result;
	}

	public function free() {
		 mysqli_free_result($this->_resource);
	}
}

