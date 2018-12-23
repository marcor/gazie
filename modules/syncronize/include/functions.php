<?php

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
}
?>
