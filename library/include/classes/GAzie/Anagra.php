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


// Classe anagrafiche sincronizzazione
class Anagra extends \Database\Table {
	
	public function __construct( $id = NULL ) {
		parent::__construct('anagra');
		$this->load( $id );
	}

	public function exist() {
		// Controllo ragso1
		if ( ! strtoupper(trim($this->ragso1)) )
			return FALSE;

		$where = "`ragso1` = '" . strtoupper(trim($this->ragso1)) . "' " ; 
		$where .= trim($this->cell) ? "OR `cell` = '" . trim($this->cell) . "' " : ''; 
		$where .= strtolower(trim($this->email))  ? "OR `e_mail` = '" . strtolower(trim($this->email)) . "' ": '';
		$where .= strtoupper(trim($this->codfis)) != '00000000000'  ? "OR `codfis` = '" . strtoupper(trim($this->codfis)) . "' ": '';
		$where .= strtoupper(trim($this->pariva)) != '00000000000'  ? "OR `codfis` = '" . strtoupper(trim($this->pariva)) . "' ": '';
		$sql = $this->query->select()
			->from($this->getTableName())
			->where( $where ); 
		return $this->execute( $sql )->count() > 0;
	}

	public function getSuppliers() {
		$GAzie = GAzie::factory();
		$admin_aziend = $GAzie->getCurrentAzienda();
		$masfor = $admin_aziend->masfor . "000000";
		$masfor_final = $admin_aziend->masfor . "999999";
		$table_clfoco = $GAzie->getConfig()->getTabelle('clfoco');
		$sql = $this->query->sql("SELECT * FROM " . $this->getTableName() . " AS a INNER JOIN " . $table_clfoco . " AS cl ON cl.id_anagra = a.id WHERE cl.codice >= $masfor AND cl.codice <= $masfor_final;");

		$result = $this->execute( $sql );
		return $result->asArray();
	}

	public function searchSuppliers($term='') {
		if ( $term == '' ) {
			return $this->getSuppliers();
		} else {
			$GAzie = GAzie::factory();
	                $admin_aziend = $GAzie->getCurrentAzienda();
	                $masfor = $admin_aziend->masfor . "000000";
	                $masfor_final = $admin_aziend->masfor . "999999";
	                $table_clfoco = $GAzie->getConfig()->getTabelle('clfoco');
	                $sql = $this->query->sql("SELECT * FROM " . $this->getTableName() . " AS a INNER JOIN " . $table_clfoco . " AS cl ON cl.id_anagra = a.id WHERE cl.codice >= $masfor AND cl.codice <= $masfor_final AND (a.ragso1 LIKE '%$term%' OR a.ragso2 LIKE '%$term%');");

			$result = $this->execute( $sql );
			return $this->getResult()->asArray();
		}
	}

	public static function  syncCustomer( \Syncro\Interfaces\ICustomer $customer ) {
		// Verifica esistenza customer
		$sync = new SyncronizeOc; 
		if ( $sync->getFromOc('customer', $customer->getCustomerId() ) ) {
			// Gia sincronizzato
			// Ritorna id Gazie
			return FALSE;

		} else {
			// Non sincronizzato
			// Aggiungi il customer
			$anagr = new Anagra();
			$anagr->ragso1 = $customer->getRagso1();
			$anagr->sexper =  "G";
			$anagr->pariva ="00000000000";
			$anagr->codfis = "00000000000";
			$anagr->e_mail = $customer->getEmail();
			$anagr->cell = $customer->getTelephone();
			// Inserisci indirizzo
			// CAP, SPEDIZIONE, CITTA, PROVINCIA
			$anagr->indspe = $r['indspe'];
			$anagr->capspe = $r['capspe'];
			$anagr->citspe = $r['citspe'];
			$anagr->prospe = $r['prospe'];
			
			$exist = $anagr->exist();
			if ( ! $exist ) {
				// Salva ed aggiungi id customer
				
				return $anagr->save();
			} else {
				return FALSE;
			}
		}
	}
}

