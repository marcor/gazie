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

namespace GAzie\Database\User;

/**
 * Class for user configuration
 */
class Config extends \Database\Table {
	
	private $_values;

	public function __construct( $id = NULL ) {
		parent::__construct('admin_config');
		if ( !is_null($id) && is_int($id)  > 0 )
			$this->load($id);
	}

	/**
	 * Return configuration of a single user
	 *
	 * @retur array of object
	 */
	public function setUser( \GAzie\User $user ) {
		if ( ! $user->loaded() )
			return [];
		
		$user_name = $this->query->select()->escape( $user->user_name );
                $sql = $this->query
                        ->select( $this->getTableName() )
                        ->where ( "adminid = '$user_name' ");
		$this->execute($sql);
		foreach ($this->getResult()->asObject() as $v ) {
			$this->_values[$v->var_name] = [
				'value'		=> $v->var_value,
				'descri'	=> $v->var_descri
			];
		}
		return $this;
	}

	public function get($k) {
		return isset($this->_values[$k]) ? $this->_values[$k]['value'] : null;
	}

	public function getDescri($k) {
		return isset($this->_values[$k]) ? $this->_values[$k]['descri'] : null;
	}
}


