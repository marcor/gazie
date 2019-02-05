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

namespace GAzie;

define('PATH_ROOT', realpath(__DIR__ .'/../../../..' ));

class Config {

	private static $instance = null;

	private $data;

	public function __construct() {
                global $gTables;
		$this->data = [];
		if( defined('PATH_ROOT') )
			$this->set('path_root', PATH_ROOT);
		$this->set('directories', [
			$this->getPathRoot()."/admin.php", 
			$this->getPathRoot()."/composer.json", 
			$this->getPathRoot()."/composer.lock.php", 
			$this->getPathRoot()."/config.php", 
			$this->getPathRoot()."/doc", 
			$this->getPathRoot()."/.htaccess", 
			$this->getPathRoot()."/INDEX.html", 
			$this->getPathRoot()."/index.php", 
			$this->getPathRoot()."/js", 
			$this->getPathRoot()."/language", 
			$this->getPathRoot()."/library", 
			$this->getPathRoot()."/modules", 
			$this->getPathRoot()."/README.html", 
			$this->getPathRoot()."/setup", 
			$this->getPathRoot()."/SETUP.html", 
		]);
                $data = gaz_dbi_get_row($gTables['company_config'], 'var', 'syncronize_oc');
                if ( ! $data ) {
                        $json_data = json_encode(array(
                                'user'=>'',
                                'pass'=>'',
                                'url'=>'http://...',
                        ));
                        gaz_dbi_table_insert('company_config', array( 'description'=>'Accesso ai dati shop opencart web','var'=>'syncronize_oc','val'=> $json_data));
                } else {
                        $json_data = $data['val'];
                }
                $tmp = json_decode($json_data,TRUE);
                $this->data['username'] = $tmp['user'];
                $this->data['password'] = $tmp['pass'];
                $this->data['url'] = $tmp['url'];

	}

	public function set($key,$value) {  
		$this->data[$key]=$value;
	}  

	public function get($key) {
		return $this->data[$key];
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
		global $gTables;
		$json_data = json_encode($data);
		$data = gaz_dbi_put_row($gTables['company_config'], 'var', 'syncronize_oc','val',$json_data);
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
