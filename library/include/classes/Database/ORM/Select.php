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

namespace Database\ORM;


/**
 *  Class for creation query
 *  from databasae
 *
 */
class Select extends Query {

	private $_where;
	private $_order_by;
	private $_group_by;
	private $_offset = 0;
	private $_limit = NULL;

	public function write() {
		if ( empty( $this->getColumns() ) || ! $this->getColumns() )
			$all_cols = true;
		else
			$all_cols = false;

		if ( ! $this->getTable() )
			return '';

                if ( ! $all_cols ) {
                        foreach( $this->getColumns() as $k=>$v ) {
                                $fields[] = "`$k`";
                        }
                        $query = "SELECT ".implode(",",$fields)." FROM " . $this->getTable();
		} else {
                	$query = "SELECT * FROM " . $this->getTable();
		}
                if ( $this->_where ) {
                        $query .= " WHERE $this->_where";
                }
                if ( $this->_group_by )
                        $query .= " GROUP BY $this->_group_by";
                if ( $this->_order_by )
                        $query .= " ORDER BY $this->_order_by";
                if ( $this->_limit )
                        $query .= " LIMIT $this->_limit OFFSET $this->_offset";
		return $query;	
	}
    
	public function where( $where ) {
		$this->_where = $where;
		return $this;
        }

	public function orderby( $orderby ) {
		$this->_order_by = $orderby;
		return $this;
	}

	public function groupby( $groupby ) {
		$this->_group_by = $groupby;
		return $this;
	}

	public function limit( $offset, $limit) {
		$this->_limit = $limit;
		$this->_offset = $offset;
		return $this;
	}

}

