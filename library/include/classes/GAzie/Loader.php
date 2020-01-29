<?php /*
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


/**
 * Class for load file php or return correct js path
 *
 */
class Loader {

	private $_path_root;

	private $_dir_home;

	public function __construct() {
		$this->_path_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
		$this->_dir_home = Config::factory()->getPathRoot();
	}

	/**
	 * Return relative path of js file
	 *
	 * @return string
	 */
	public function js( $path_js ) {	
		return $this->getFolderInstallation() . $path_js;
	}

	/**
	 * Return the path with folder into installation
	 *
	 * @return string
	 */
	private function getFolderInstallation() {
		$folder = str_replace($this->_path_root, '', $this->_dir_home);
		return $folder;
	}

}

?>
