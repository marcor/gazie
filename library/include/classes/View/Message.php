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

namespace View;

class Message {

	private $errors;

	public function __construct( ) {
		$this->errors = array();
	}

	public function setError( $message ) {
		$this->setMessage($message,'error');
	}

	public function setWarning( $message ) {
		$this->setMessage($message,'warning');
	}

	public function setSuccess( $message ) {
		$this->setMessage($message,'success');
	}

	public function setNotice( $message ) {
		$this->setMessage($message,'notice');
	}

	private function setMessage($message, $type = 'error' ) {
		if ( is_null($message) || trim($message) == '' )
		        return;	
		if ( $type !== 'success' && $type !== 'error' && $type !== 'warning' && $type !== 'notice' )
			return;
		$this->errors[] = [
			'message'	=> $message,
			'type'		=> $type,
		];
	}

	public function render() {
		if ( empty($this->errors) )
			return '';

		$result = '<div class="container">';
		foreach ( $this->errors as $err ) {
			switch ( $err['type'] ) {
				case 'error': 
					$c = 'alert-danger';
					$icon = '-alert';
					break;
				case 'warning':
					$c = 'alert-warning';
					$icon = '-alert';
					break;
				case 'success':
					$c = 'alert-success';
					$icon = '-exclamation-sign';
					break;
				default:
					$c = 'alert-info';
					$icon = '-exclamation-sign';
					break;

			}
  			$result .= ' 
      <div class="row alert ' . $c . ' " >
         <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
           <span aria-hidden="true">&times;</span>
         </button>
         <span class="glyphicon glyphicon'.$icon.'" aria-hidden="true"></span>
          ' .$err['message'] .' 
      </div>
';

		} 
		return $result.'</div>';
  	}

	public function __toString() {
		return $this->render();		
	}

}

?>
