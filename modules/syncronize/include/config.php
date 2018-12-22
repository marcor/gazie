<?php

namespace Syncronize;

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
