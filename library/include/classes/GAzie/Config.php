<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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

define('PATH_ROOT', realpath(__DIR__ .'/../../../..' ));

class Config {

	private static $instance = null;

	private $data;

	public function __construct() {
		$this->data = [];
		if (! defined('PATH_ROOT') )
			die ('Error setting path root');

		// Verifica file di configurazione	
	   	if ( ! is_file( PATH_ROOT . "/config/config/gconfig.myconf.php") )
			die ('Non hai creato il file di configurazione <b>config/config/gconfig.myconf.php</b>! <br> Copialo da <b>config/config/config.myconf.default.php</b>');

		$this->setConfigFile( PATH_ROOT . "/config/config/gconfig.php");
		$this->setTabelle();
		$this->set('path_root', PATH_ROOT );
		$this->setDirectories( PATH_ROOT );
		
                $data = \gaz_dbi_get_row( $this->getTabelle('company_config'), 'var', 'syncronize_oc');
                if ( ! $data ) {
                        $json_data = json_encode(array(
                                'user'=>'',
                                'pass'=>'',
                                'url'=>'http://...',
                        ));
                        \gaz_dbi_table_insert('company_config', array( 'description'=>'Accesso ai dati shop opencart web','var'=>'syncronize_oc','val'=> $json_data));
                } else {
                        $json_data = $data['val'];
                }
		$tmp = json_decode($json_data, TRUE);
                $this->data['username'] = $tmp['user'];
                $this->data['password'] = $tmp['pass'];
		$this->data['url'] = $tmp['url'];
		try {
			$this->data['config_azienda'] = \checkAdmin();
		} catch ( Exception $e ) {
			echo "Errore: " . $e->getMessage();
		}
	}

	public function set($key,$value) {  
		$this->data[$key]=$value;
	}  

	public function get($key) {
		return $this->data[$key];
	}

	private function setDirectories($path_root)  {
		$this->set('directories', [
                        $path_root."/admin.php",
                        $path_root."/composer.json",
                        $path_root."/composer.lock.php",
                        $path_root."/config.php",
                        $path_root."/config/gconfig.php",
                        $path_root."/doc",
                        $path_root."/.htaccess",
                        $path_root."/INDEX.html",
                        $path_root."/index.php",
                        $path_root."/js",
                        $path_root."/language",
                        $path_root."/library",
                        $path_root."/modules",
                        $path_root."/README.html",
                        $path_root."/setup",
                        $path_root."/SETUP.html",
                ]);

	}

	public function getMonth() {
		return [ "Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio","Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
	}
	
	/**
	 *  
	 */
	private function setConfigFile($file) {
		if ( ! is_file($file ) )
			die ('Configuration file '.$file.' not found');
		include_once($file);
		if ( isset($_SESSION['table_prefix'] ) ) {
			$table_prefix = substr($_SESSION['table_prefix'], 0, 12);
		} else {
			if ( defined('table_prefix') )
    				$table_prefix = filter_var(substr(table_prefix, 0, 12), FILTER_SANITIZE_MAGIC_QUOTES);
		}
		if ( ! defined('GAZIE_VERSION') )
			die('Version of GAzie not defined. Control configuration file!');
		$this->set('GAZIE_VERSION',defined('GAZIE_VERSION') ? GAZIE_VERSION: '');
		$this->set('database', [
			'host'		=>	defined('Host') ? Host : $Host,
			'dbname'	=>	defined('Database') ? Database : $Database,
			'user'		=>	defined('User') ? User : $User,
			'password'	=>	defined('Password') ? Password : $password,
			'port'		=>	defined('Port') ? Port : $Port,
			'table_prefix'	=>	defined('table_prefix') ? table_prefix : '',
		]);
		$this->set('default_user', defined('default_user') ? default_user : '');
		$this->set('timezone', defined('Timezone') ? Timezone : '');
		$this->set('email_footer', defined('EMAIL_FOOTER') ? EMAIL_FOOTER : '');
		$this->set('disable_set_time_limit', defined('disable_set_time_limit') ? disable_set_time_limit : false);
		$this->set('gazie_locale', defined('gazie_locale') ? gazie_locale : '');
		$this->set('PER_PAGE', defined('PER_PAGE') ? PER_PAGE : 30);
		$this->set('DATA_DIR', defined('DATA_DIR') ? DATA_DIR : PATH_ROOT . '/data/');
		$this->set('K_PATH_CACHE', defined('K_PATCH_CACHE') ? K_PATH_CACHE : PATH_ROOT . '/library/tcpdf/cache/');
		$this->set('_SESSION_NAME', defined('_SESSION_NAME') ? _SESSION_NAME : 'gazie');
		$this->set('SET_DYNAMIC_IP', defined('SET_DYNAMIC_IP') ? SET_DYNAMIC_IP : '');
		$this->set('update_URI_files', defined('update_URI_files') ? update_URI_files : 'https://sourceforge.net/projects/gazie');
		$this->set('debug_active', defined('debug_active') ? debug_active : FALSE);
		$this->set('modifica_fatture_ddt', defined('modifica_fatture_ddt') ? modifica_fatture_ddt : FALSE);
	}

	public function setTabelle() {
		$table_prefix = $this->get('database')['table_prefix'];
		$tb =  [
			'admin', 
			'admin_config', 
			'admin_module', 
			'anagra', 
			'aziend', 
			'classroom', 
			'config',
			'country', 
			'currencies', 
			'currency_history', 
			'destina', 
			'camp_avversita', 
			'camp_colture',
			'camp_fitofarmaci', 
			'camp_uso_fitofarmaci',
			'languages', 
			'menu_module', 
			'menu_script', 
			'menu_usage',
			'module', 
			'municipalities', 
			'provinces', 
			'regions', 
			'staff_absence_type', 
			'staff_work_type', 
			'students',
			'breadcrumb'
		];
		$result = [];
		foreach ($tb as $v) {
			$result[$v] = $table_prefix . "_" . $v;
		}		
		$tb =  [
			'aliiva', 
			'agenti', 
			'artico', 
			'assets', 
			'banapp', 
			'body_text', 
			'campi', 
			'cash_register',
			'catmer', 
			'caucon', 
			'caucon_rows', 
			'caumag', 
			'clfoco', 
			'company_config', 
			'company_data',
			'comunicazioni_dati_fatture', 
			'contract', 
			'effett', 
			'extcon', 
			'files', 
			'fornitore_magazzino',
			'imball', 
			'letter',
			'liquidazioni_iva',
			'lotmag', 
			'movmag', 
			'pagame', 
			'paymov', 
			'portos', 
			'provvigioni', 
			'rigbro',
			'rigdoc', 
			'rigmoc', 
			'rigmoi', 
			'spediz', 
			'staff', 
			'staff_skills', 
			'staff_worked_hours', 
			'tesbro',
			'tesdoc', 
			'tesmov', 
			'vettor', 
			'fae_flux', 
			'assist',
			'ragstat', 
			'agenti_forn',
			'movimenti',
			'sconti_articoli', 
			'sconti_raggruppamenti', 
			'instal', 
			'orderman', 
			'registro_trattamento_dati',
			'distinta_base', 
			'disbas', 
			'disbas_componente', 
			'tescmr', 
			'rigcmr', 
			'syncronize_oc'
                ];
                foreach ($tb as $v) {
                        $result[$v] = $table_prefix . "_" .  sprintf('%03d', $this->getIdAzienda()) . $v;
                }               
                $this->set('tabelle_database', $result);
        }

	/**
	 * Return all tables name or single table
	 * 
	 */
	public function getTabelle($table=NULL) {
		$tbs=$this->get('tabelle_database');
		if ( !$table )
			return $tbs;
		else
			if ( isset($tbs[$table]) )
				return  $tbs[$table];
			else
				die( "Table $table not exist");
	}

	/**
	 * Return l'id azienda
	 */
	public function getIdAzienda() {
		if (isset($_SESSION['company_id'])) {
			$id = $_SESSION['company_id'];
		} else {
			$id = 1;
		}
		return  intval($id);
	}

	/**
	 * Return the azienda config on database
	 *
	 * return array
	 */
	public function getAzienda() {
		return $this->get('config_azienda');
	}

	public function getPathRoot() {
		return $this->data['path_root'];
	}

	public function getDirectories() {
		return $this->data['directories'];
	}

	public function getUser() {
		return $this->data['username'];
	}
	public function getPassword() {
		return $this->data['password'];
	}
	public function getUrl() {
		return $this->data['url'];
	}

	public function putData( $data ) {
		$json_data = json_encode($data);
		$data = \gaz_dbi_put_row($this->getTabelle('company_config'), 'var', 'syncronize_oc','val',$json_data);
		return $data;
	}

	public static function factory() {
     	    	if (  self::$instance == null ) {
			self::$instance = new Config();
        	}
    		return self::$instance;
 	}
}

?>
