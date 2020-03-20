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
		/* token opencart
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
		}*/
		$this->api_token=TRUE; //Joomla non ha bisogno di TOKEN, quindi è TRUE	
				
	}
	function SetupStore() {
		// aggiorno i dati comuni a tutto lo store: Anagrafica Azienda, Aliquote IVA, dati richiesti ai nuovi clienti (CF,PI,indirizzo,ecc) in custom_field e tutto ciò che necessita per evitare di digitarlo a mano su ecommerce-admin 
	}
	function UpsertCategory($d) {
		// usando il token precedentemente avuto si dovranno eseguire tutte le operazioni necessarie ad aggiornare la categorie merceologica quindi:
		// in base alle API messe a disposizione dallo specifico store (Opencart,Prestashop,Magento,ecc) si passeranno i dati in maniera opportuna...
	}
	function UpsertProduct($d) {
		// aggiorno l'articolo di magazzino (product)
		session_start();
			global $gTables,$admin_aziend;			 
			$ftp_host = gaz_dbi_get_row($gTables['company_config'], "var", "server")['val'];			
			$ftp_path_upload = gaz_dbi_get_row($gTables['company_config'], "var", "ftp_path")['val'];			
			$ftp_user = gaz_dbi_get_row($gTables['company_config'], "var", "user")['val'];			
			$ftp_pass = gaz_dbi_get_row($gTables['company_config'], "var", "pass")['val'];			
			$urlinterf = gaz_dbi_get_row($gTables['company_config'], 'var', 'path')['val']."articoli-gazie.php";
			// "dwnlArticoli-gazie.php" è il nome del file interfaccia presente nella root del sito Joomla. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
			// il percorso per raggiungere questo file va impostato in configurazione avanzata azienda alla voce "Website root directory
			
			if (intval($d['barcode'])==0) {// se non c'è barcode allora è nullo
				$d['barcode']="NULL";
			}
			// imposto la connessione al server
			$conn_id = ftp_connect($ftp_host);
			// effettuo login con user e pass
			$mylogin = ftp_login($conn_id, $ftp_user, $ftp_pass);
			// controllo la connessione e il login
			if ((!$conn_id) OR (!$mylogin)){ 
				// non si connette FALSE
				$_SESSION['errmsg'] = "Problemi con le impostazioni FTP in configurazione avanzata azienda. AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				echo "Hai attivato la sincronizzazione e-commerce automatica per il modulo shop-syncronize.
				<br>Purtroppo ci sono problemi con la connessione FTP: controlla le impostazioni FTP in configurazione avanzata azienda.";
				//die;
			}
			// Calcolo il prezzo IVA compresa
			$aliquo=gaz_dbi_get_row($gTables['aliiva'], "codice", intval($d['aliiva']))['aliquo'];
			$web_price_vat_incl=$d['web_price']+(($d['web_price']*$aliquo)/100);			 
	 		// creo il file xml			
			$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
			<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
			$xml_output .= "\n<Products>\n";						
				$xml_output .= "\t<Product>\n";
				$xml_output .= "\t<Code>".$d['codice']."</Code>\n";
				$xml_output .= "\t<BarCode>".$d['barcode']."</BarCode>\n";				
				$xml_output .= "\t<Name>".$d['descri']."</Name>\n";
				$xml_output .= "\t<Description>".$d['body_text']."</Description>\n";
				$xml_output .= "\t<Price>".$d['web_price']."</Price>\n";
				$xml_output .= "\t<PriceVATincl>".$web_price_vat_incl."</PriceVATincl>\n";
				$xml_output .= "\t<VAT>".$aliquo."</VAT>\n";
				$xml_output .= "\t<Unimis>".$d['unimis']."</Unimis>\n";
				$xml_output .= "\t<ProductVat>".$d['aliva']."</ProductVat>\n";
				$xml_output .= "\t<ProductCategory>".$d['catmer']."</ProductCategory>\n";
				$xml_output .= "\t</Product>\n";			
			$xml_output .="\n</Products>\n</GAzieDocuments>";
			$xmlFile = "prodotti.xml";
			$xmlHandle = fopen($xmlFile, "w");
			fwrite($xmlHandle, $xml_output);
			fclose($xmlHandle);
			//turn passive mode on
			ftp_pasv($conn_id, true);
			// upload file xml
			if (ftp_put($conn_id, $ftp_path_upload."prodotti.xml", $xmlFile, FTP_ASCII)){			
			} else{
				$_SESSION['errmsg'] = "Upload del file xml non riuscito. AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				echo "Errore di upload del file xml";//die;			
			}
			// chiudo la connessione FTP 
			ftp_quit($conn_id);
			$access=base64_encode($ftp_pass);
			// avvio il file di interfaccia presente nel sito web remoto
			$headers = @get_headers($urlinterf.'?access='.$access);
			if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file mi ha dato accesso regolare
				$file = fopen ($urlinterf.'?access='.$access, "r");
				if (!$file) {
					$_SESSION['errmsg'] = "Il file di interfaccia non si apre. AGGIORNARE L'E-COMMERCE MANUALMENTE!";
					 echo "Errore: il file di interfaccia web non si apre!";//die;				
				}
			} else { // Riporto il codice di errore
				$_SESSION['errmsg'] = "Impossibile connettersi al file di interfaccia: ".intval(substr($headers[0], 9, 3)).". AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				echo "Errore di connessione al file di interfaccia dell'e-commerce = ",intval(substr($headers[0], 9, 3));//die;
			}
	}
	function SetProductQuantity($d) {
		// aggiornamento quantità disponibile di un articolo
			session_start();
			global $gTables,$admin_aziend;			 
			$ftp_host = gaz_dbi_get_row($gTables['company_config'], "var", "server")['val'];			
			$ftp_path_upload = gaz_dbi_get_row($gTables['company_config'], "var", "ftp_path")['val'];			
			$ftp_user = gaz_dbi_get_row($gTables['company_config'], "var", "user")['val'];			
			$ftp_pass = gaz_dbi_get_row($gTables['company_config'], "var", "pass")['val'];			
			$urlinterf = gaz_dbi_get_row($gTables['company_config'], 'var', 'path')['val']."articoli-gazie.php";
			// "articoli-gazie.php" è il nome del file interfaccia presente nella root del sito Joomla. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
			// il percorso per raggiungere questo file va impostato in configurazione avanzata azienda alla voce "Website root directory
			$gForm = new magazzForm();
			$mv = $gForm->getStockValue(false, $d);
			$magval = array_pop($mv);
			// trovo l'ID di riferimento e calcolo la disponibilità
			$id = gaz_dbi_get_row($gTables['artico'],"codice",$d);
			$fields = array ('product_id' => intval($id),'quantity'=>intval($magval['q_g']));
			$ordinati = $gForm->get_magazz_ordinati($d, "VOR");
			$avqty=$fields['quantity']-$ordinati;
			if ($avqty<0 or $avqty==""){ // per l'e-commerce la disponibilità non può essere nulla o negativa
				$avqty="0";
			}
			if (intval($id['barcode'])==0) {// se non c'è barcode allora è nullo
				$id['barcode']="NULL";
			}
			// imposto la connessione al server
			$conn_id = ftp_connect($ftp_host);
			// effettuo login con user e pass
			$mylogin = ftp_login($conn_id, $ftp_user, $ftp_pass);
			// controllo la connessione e il login
			if ((!$conn_id) OR (!$mylogin)){ 
				// non si connette FALSE
				$_SESSION['errmsg'] = "Problemi con le impostazioni FTP in configurazione avanzata azienda. AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				echo "Hai attivato la sincronizzazione e-commerce automatica per il modulo shop-syncronize.
				<br>Purtroppo ci sono problemi con la connessione FTP: controlla le impostazioni FTP in configurazione avanzata azienda.";
				//die;
			}	 
	 		// creo il file xml			
			$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
			<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
			$xml_output .= "\n<Products>\n";						
				$xml_output .= "\t<Product>\n";
				$xml_output .= "\t<Code>".$id['codice']."</Code>\n";
				$xml_output .= "\t<BarCode>".$id['barcode']."</BarCode>\n";
				$xml_output .= "\t<AvailableQty>".$avqty."</AvailableQty>\n";
				$xml_output .= "\t</Product>\n";			
			$xml_output .="\n</Products>\n</GAzieDocuments>";
			$xmlFile = "prodotti.xml";
			$xmlHandle = fopen($xmlFile, "w");
			fwrite($xmlHandle, $xml_output);
			fclose($xmlHandle);
			//turn passive mode on
			ftp_pasv($conn_id, true);
			// upload file xml
			if (ftp_put($conn_id, $ftp_path_upload."prodotti.xml", $xmlFile, FTP_ASCII)){			
			} else{
				$_SESSION['errmsg'] = "Upload del file xml non riuscito. AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				echo "Errore di upload del file xml";//die;			
			}
			// chiudo la connessione FTP 
			ftp_quit($conn_id);
			$access=base64_encode($ftp_pass);
			// avvio il file di interfaccia presente nel sito web remoto
			$headers = @get_headers($urlinterf.'?access='.$access);
			if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file mi ha dato accesso regolare
				$file = fopen ($urlinterf.'?access='.$access, "r");
				if (!$file) {
					$_SESSION['errmsg'] = "Il file di interfaccia non si apre. AGGIORNARE L'E-COMMERCE MANUALMENTE!";
					 echo "Errore: il file di interfaccia web non si apre!";//die;				
				}
			} else { // Riporto il codice di errore
				$_SESSION['errmsg'] = "Impossibile connettersi al file di interfaccia: ".intval(substr($headers[0], 9, 3)).". AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				echo "Errore di connessione al file di interfaccia dell'e-commerce = ",intval(substr($headers[0], 9, 3));//die;
			}
	}
	function GetOrder($last_id) {
		// prendo gli eventuali ordini arrivati assieme ai dati del cliente, se nuovo lo importo (order+customer), 
		// in $last_id si deve passare l'ultimo ordine già importato al fine di non importare tutto ma solo i nuovi 
	}
}
?>