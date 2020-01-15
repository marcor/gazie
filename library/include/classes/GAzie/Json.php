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

/**
 * Class for manage json entities
 *
 */
class Json {

	private $messages = array(
		401	=> array(
			        'error'=> true,
			        'message' => 'Token not valid',
		  	   ),
                501     => array(
                                'error'=> true,
                                'message' => 'Data is not array or failed encode json',
                           ),
		);

	private $_method = NULL;

	public function __construct() {
		$this->control_token();
		$this->_method = strtoupper($_SERVER['REQUEST_METHOD']);
	}

	public function method() {
		return $this->_method;
	}

	private function control_token( ) {
		 if ( ! isset($_GET['token']) || ! $this->verify_token( $_GET['token'] ) ) {
                        echo  $this->response( $this->getMessage(401), 401 );
                        exit;
                }
	}

	/**
	 *  Verify token
	 */
	private function verify_token( $token = NULL ) {
	        if ( ! isset( $_SESSION['user_name'] )) {
        	        return False;
	        }
	        if ( ! isset( $_COOKIE[_SESSION_NAME] ) ) {
	                return False;
	        }
	        if ( $token != NULL && $token != $_COOKIE[_SESSION_NAME] ) {
	                return False;
	        }
	        if ( $token === $_COOKIE[_SESSION_NAME] )
	                return True;
	        else
        	        return False;
	}

	public function response( $data , $code = 200 ) {
		if ( $code <> 200 )
			http_response_code($code);

	        if ( is_array($data ) ) {
	          return json_encode($data);
	        }
		http_response_code(501);
	        return json_encode( $this->getMessage( 501 ) ); 
	}

	private function getMessage( $code ) {
		if ( isset( $this->messages[$code]  ))
			return $this->messages[$code];
		else
			return array( 'error' => 'Not message code $code founded' );
	}

	public function error( $code, $msg) {
		$json = array('error' => $msg);
		echo $this->response($json, $code);
		exit;
	}

}

?>
