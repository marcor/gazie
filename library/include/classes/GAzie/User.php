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

namespace GAzie;

/**
 * Class for user admin management
 */
class User extends \Database\Table {
	
	private $_logged;

	public function __construct( $id = NULL ) {
		parent::__construct('admin');
		if ( !is_null($id) && is_int($id)  > 0 )
			$this->load($id);
	}

	/**
	 * Load user logged
	 */
	public function loadLogged() {
 		if( $this->isLogged() ) 
			$this->searchByName( $_SESSION['user_name'] );
	}

	public function login( $user, $password, $remember = FALSE) {
		if ( ! $user || ! $password )
			return FALSE;
		
		$this->searchByNameOrEmail($user);
		
		if ( ! $this->loaded() ) 
			return FALSE;

		// Verify exist old field Password (upgrade GAzie)
		if ( $this->Password )
			return FALSE;

		// Function of PHP for verify password
		if ( ! password_verify( $password, $this->user_password_hash) ) {
			// Verifica failed login
			if ( $this->user_failed_logins > 3 && $this->user_last_failed_login > (time() - 30 ) )
				return FALSE;

			// Increment failed login
			$this->user_failed_logins = $this->user_failed_logins + 1;
			$this->user_last_failed_login = time();
			$this->save();
			return FALSE;
		} else {
			// Verify user active
			if ( $this->user_active != 1 )
		    		return FALSE;
			
			// Increment logins
			$this->Access = $this->Access + 1;
			$this->last_ip = $this->getUserIP(); 
			$this->user_failed_logins = 0;
			$this->save();
			return $this;		
		}
	}


	public function searchByNameOrEmail( $user ) {
		$user = $this->query->select()->escape($user);
		$sql = $this->query
			->select( $this->getTableName() )
			->where ( "user_name = '$user' OR user_email ='$user' ");
		$user = $this->execute($sql); echo $sql;
		return $this;
	}

	public function searchByName( $user ) {
		$user = $this->query->select()->escape($user);
		$sql = $this->query
			->select( $this->getTableName() )
			->where ( "user_name = '$user' ");
		$user = $this->execute($sql);
		return $this;
	}

	public function searchByEmail( $user_email ) {
		$user = $this->query->select()->escape($user_email);
		$sql = $this->query
			->select( $this->getTableName() )
			->where ( "user_email = '$user_email' ");
		$user = $this->execute($sql);
		return $this;
	}

	/**
	 * If user is logged
	 */
	private function loginWithSessionData() {
                $this->user_name = $_SESSION['user_name'];
                $this->user_email = $_SESSION['user_email'];

                // set logged in status to true, because we just checked for this:
                // !empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)
                // when we called this method (in the constructor)
                $this->user_is_logged_in = true;
        }

	/**
	 *
	 * @return \GAzie\Database\User\Config
	 */
	public function getConfig() {
		$config = new \GAzie\Database\User\Config;
		return $config->setUser($this);
	}

	/**
	 * Verify if user is logged
	 *
	 * @return boolean
	 */
	public function isLogged() {
		return  (!empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1));
	}

        // recupero ip chiamante
        private function getUserIP() {
                $client = @$_SERVER['HTTP_CLIENT_IP'];
                $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
                $remote = $_SERVER['REMOTE_ADDR'];
                if (filter_var($client, FILTER_VALIDATE_IP)) {
                        $ip = $client;
                } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
                        $ip = $forward;
                } else {
                        $ip = $remote;
                }
                return $ip;
        }
}


