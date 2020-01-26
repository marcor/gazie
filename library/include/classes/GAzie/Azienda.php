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

use \GAzie\Azienda\Config as AziendaConfig;

/**
 * Class for manage azienda table
 */
class Azienda extends \Database\Table {
	
	public function __construct( $id = NULL ) {
		parent::__construct('aziend');
		$this->load( $id );
	}
	
	/**
	 * Return the config of company
	 *
	 * @return GAzie\Azienda\Config
	 */
	public function getConfig() {
		return new AziendaConfig();
	}

	public function getCurrent() {
	      if (isset($_SESSION['company_id'])) {
                        $id = $_SESSION['company_id'];
              } else {
                        $id = 1;
              }
	      $this->load( intval($id) );
	      return $this;
	}

	public function exist() {
		// Controllo se esiste l'azioneda
		return  $this->loaded();
	}

}

