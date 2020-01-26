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

namespace Opencart;

class Api {
	
  private $url;	
  private $username;
  private $password;  
  private $token;
  private $result;
  private $errors;


  public function __construct( $url, $username, $password) {
	$this->url = $url;
	$this->username = $username;
	$this->password = $password;
	$this->get_token();
  }

  // Funzione per richiedere json
  private function do_curl_request($url, $params=array()) {
	  $ch = curl_init(); 
	  curl_setopt($ch,CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	  curl_setopt($ch, CURLOPT_PORT, 8081 );
	  curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/apicookie.txt');
	  curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/apicookie.txt');
	  $params_string = '';
	  if (is_array($params) && count($params)) {
	    foreach($params as $key=>$value) {
	      $params_string .= $key.'='.$value.'&'; 
	    }
	    rtrim($params_string, '&');
	 
	    curl_setopt($ch,CURLOPT_POST, count($params));
	    curl_setopt($ch,CURLOPT_POSTFIELDS, $params_string);
	  }
	   
	  //execute post
	  $this->result = curl_exec($ch);
	  $error = curl_error($ch); 
	  if ( $error ) {
		$this->errors[] = $error;
	  }
	  //close connection
	  curl_close($ch);
	  return $this->result;
  }

  private function get_token( ) {
	  $fs = array(
		'username'=>$this->username,
		'password'=>$this->password
	  );
	  $url = $this->url . '/index.php?route=api/login';
	  $json = $this->do_curl_request( $url, $fs ) ;
          $this->result = $json;
	  $data = $this->json_to_array();  
	  $this->token = $data['cookie'];
  }

  public function json_to_array( ) {
  	return json_decode( $this->result, TRUE); 
  }

  public function getToken() {
	return $this->token;
  }

  public function getCustomers() {
	  $url = $this->url . '/index.php?route=sync/customer';
	  $params = array(
		'api_token'	=>	$this->getToken(),
	  );
	  $json = $this->do_curl_request( $url, $params ); 
	  return  $this->json_to_array();  
  }

  public function getCustomer($id) {
	  $url = $this->url . '/index.php?route=sync/customer';
	  $params = array(
		'api_token'	=>	$this->getToken(),
		'customer_id'	=>	$id,
	  );
	  $json = $this->do_curl_request( $url, $params ); 
	  return  $this->json_to_array();  

  }

  public function getProducts() {
	  $url = $this->url . '/index.php?route=sync/products';
	  $params = array(
		'api_token'	=>	$this->getToken(),
	  );
	  $json = $this->do_curl_request( $url, $params ); 
	  return  $this->json_to_array();  
  }
}
?>
