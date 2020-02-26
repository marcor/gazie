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
 
/* 
QUESTA CLASSE CONTERRA' DELLE FUNZIONI DI NOME STANDARD PER INTERAGIRE CON LE API DEI VARI E-COMMERCE
SOTTO VEDETE UNA SOLA FUNZIONE DI COSTRUTTO DI ESEMPIO PER LA PRESA DEL TOKEN. 
GAzie userà dei nomi di funzione per eseguire le varie operazioni di sincronizzazione, con il proseguire 
dello sviluppo vedrete delle chiamate ad esse che però al momento saranno vuote e a discrezione dei 
singoli sviluppatori utilizzarle per passare O ricevere dati (d)allo store online, tramite le specifiche API.
I nomi standard di funzione saranno: 
"UpsertProduct","GetOrder","UpsertCategory","UpsertCustomer","UpdateStore",ecc 
e dovranno essere gli stessi anche su eventuali "moduli cloni" per la sincronizzazione di GAzie.
Con questo stratagemma basterà indicare in configurazione azienda  il nome del modulo che si vuole 
utilizzare per il sincronismo che tutti gli altri moduli di GAzie nel momento in cui effettueranno
un aggiornamento dei dati punteranno alle funzioni contenute nel modulo alternativo richiesto,
 pittosto che a questo. 
*/
class APIeCommerce {

	function __construct() {
		// Quando istanzio questa classe prendo il token, sempre.
		// Se $this->api_token ritorna FALSE vuol dire che le credenziali sono sbagliate
		global $gTables,$admin_aziend;
    $this->oc_api_url = gaz_dbi_get_row($gTables['company_data'], 'var','oc_api_url')['data'];
    $oc_api_username = gaz_dbi_get_row($gTables['company_data'], 'var','oc_api_username')['data'];
    $oc_api_key = gaz_dbi_get_row($gTables['company_data'], 'var','oc_api_key')['data'];
		// prendo il token
		$curl = curl_init($this->oc_api_url);
		$post = array('username' => $oc_api_username,'key'=>$oc_api_key); 
		curl_setopt_array($curl,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_POSTFIELDS=>$post));
		$raw_response = curl_exec($curl);
		if(!$raw_response){
			$this->api_token=false;
		}else{
			$res = json_decode($raw_response);
			$this->api_token=$res->api_token;
			curl_close($curl);		
    }
	}

	function UpsertCategory($d) {
		// usando il token precedentemente avuto si dovranno eseguire tutte le operazioni necessarie ad aggiornare la categorie merceologica quindi:
		// in base alle API messe a disposizione dallo specifico store (Opencart,Prestashop,Magento,ecc) si passeranno i dati in maniera opportuna...
	}
}
?>