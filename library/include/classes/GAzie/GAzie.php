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

use \Database\Database;

/**
 * Class call all subproject and configuration
 *
 */
class GAzie {

	private static $instance = null;

	private $_database;
	
	private $_config;

	private $_admin_aziend;

	private $_module_loaded;

	private $_level_access_module;
	
	private $_azienda;

	private $_user;

	public function __construct() {
		$this->_config = Config::factory();
		$config = $this->_config->get('database');
		$this->_database = Database::connect(
			$config['host'],
                        $config['user'],
                        $config['password'],
                        $config['dbname'],
			$config['port']
		);
		$this->_level_access_module = 0;
		$this->_module_loaded = FALSE;
	}

	/**
	 * Return configuration of GAzie
	 *
	 * @return \GAzie\Config
	 */
	public function getConfig() {
		return $this->_config;
	}

	/**
	 * Return database class
	 *
	 * @return \Database\Database
	 */
	public function getDatabase() {
		return $this->_database;
	}


	/**
	 * Return version of GAzie
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->getConfig()->get('GAZIE_VERSION');
	}

	public function getCurrentAzienda() {
		$azienda = new Azienda;
		return $azienda->getCurrent();
	}

	/**
	 * Method for load module 
	 *
	 * 
	 */
	public function loadModule() {
		$this->_admin_aziend = \checkAdmin( $this->_level_access_module);
		$this->_azienda = $this->getCurrentAzienda();
		$this->_user = new User;
		$this->_user->loadLogged();
		$this->_module_loaded = TRUE;
		$this->_template = new \View\Template( $this->getConfig(), $this->_user );
	}

	public function getTemplate() {
		return $this->_template;
	}

	public function moduleLoaded() {
		return $this->_module_loaded;
	}

	public function getCheckAdmin() {
		return $this->_admin_aziend;
	}

	public function checkAccess( $level = 0 ) {
		$this->_level_access_module = intval($level);
	}


	/**
	 * Get the user logged
	 * 
	 * @return \GAzie\User
	 */
	public function getUser() {
		return $this->_user;
	}

	/**
	 * Load debug kint 
	 *
	 */
	public function debug() {
		
	}	

	/**
	 * Return GAzie class
	 *
	 * @return \GAzie\GAzie
	 */
	public static function factory() {
                if (  self::$instance == null ) {
                        self::$instance = new GAzie();
                }
                return self::$instance;
        }


        /**
         * Return Json class
         *
         * @return \GAzie\Json
         */
        public function Json() {
                return new Json();
        }

}

?>
