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

class Config {

	private $username;
	private $password;
	private $url;

	public function __construct() {
		global $gTables; 
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
		$this->username = $tmp['user'];
		$this->password = $tmp['pass'];
		$this->url = $tmp['url'];
	}

	public function getUser() {
		return $this->username;
	}
	public function getPassword() {
		return $this->password;
	}
	public function getUrl() {
		return $this->url;
	}

	public function putData( $data ) {
		global $gTables;
		$json_data = json_encode($data);
		$data = gaz_dbi_put_row($gTables['company_config'], 'var', 'syncronize_oc','val',$json_data);
	}
}

?>
