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
		@session_start();
			global $gTables,$admin_aziend;			 
			$ftp_host = gaz_dbi_get_row($gTables['company_config'], "var", "server")['val'];			
			$ftp_path_upload = gaz_dbi_get_row($gTables['company_config'], "var", "ftp_path")['val'];			
			$ftp_user = gaz_dbi_get_row($gTables['company_config'], "var", "user")['val'];			
			$ftp_pass = gaz_dbi_get_row($gTables['company_config'], "var", "pass")['val'];			
			$urlinterf = gaz_dbi_get_row($gTables['company_config'], 'var', 'path')['val']."articoli-gazie.php";
			// "articoli-gazie.php" è il nome del file interfaccia presente nella root del sito Joomla. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
			// il percorso per raggiungere questo file va impostato in configurazione avanzata azienda alla voce "Website root directory"
			
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
				$_SESSION['errref'] = "Aggiornamento dell'articolo: ". $d['codice'];
			}
			// Calcolo il prezzo IVA compresa
			$aliquo=gaz_dbi_get_row($gTables['aliiva'], "codice", intval($d['aliiva']))['aliquo'];
			$web_price_vat_incl=$d['web_price']+(($d['web_price']*$aliquo)/100);
			$web_price_vat_incl=number_format($web_price_vat_incl, $admin_aziend['decimal_price'], '.', '');
	 		// creo il file xml			
			$xml_output = '<?xml version="1.0" encoding="UTF-8"?>
			<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
			$xml_output .= "\n<Products>\n";						
				$xml_output .= "\t<Product>\n";
				$xml_output .= "\t<Id>".$d['ref_ecommerce_id_product']."</Id>\n";
				$xml_output .= "\t<Code>".$d['codice']."</Code>\n";
				$xml_output .= "\t<BarCode>".$d['barcode']."</BarCode>\n";				
				$xml_output .= "\t<Name>".$d['descri']."</Name>\n";
				$xml_output .= "\t<Description>".preg_replace('/[\x00-\x1f]/','',htmlspecialchars($d['body_text'], ENT_QUOTES, 'UTF-8'))."</Description>\n";
				$xml_output .= "\t<Price>".$d['web_price']."</Price>\n";
				$xml_output .= "\t<PriceVATincl>".$web_price_vat_incl."</PriceVATincl>\n";
				$xml_output .= "\t<VAT>".$aliquo."</VAT>\n";
				$xml_output .= "\t<Unimis>".$d['unimis']."</Unimis>\n";
				$xml_output .= "\t<ProductCategory>".$d['catmer']."</ProductCategory>\n";
				$xml_output .= "\t</Product>\n";			
			$xml_output .="</Products>\n</GAzieDocuments>";
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
				$_SESSION['errref'] = "Aggiornamento dati dell'articolo: ". $d['codice'];			
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
					$_SESSION['errref'] = "Aggiornamento dati dell'articolo: ". $d['codice'];				
				}
			} else { // Riporto il codice di errore
				$_SESSION['errmsg'] = "Impossibile connettersi al file di interfaccia: ".intval(substr($headers[0], 9, 3)).". AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				$_SESSION['errref'] = "Aggiornamento dati dell'articolo: ". $d['codice'];
			}
	}
	function SetProductQuantity($d) {
		// aggiornamento quantità disponibile di un articolo
			@session_start();
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
			$ordinati = $ordinati + $gForm->get_magazz_ordinati($d, "VOW");
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
				$_SESSION['errref'] = "Aggiornamento quantità dell'articolo: ". $d;
			}	 
	 		// creo il file xml			
			$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
			<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
			$xml_output .= "\n<Products>\n";						
				$xml_output .= "\t<Product>\n";
				$xml_output .= "\t<Id>".$id['ref_ecommerce_id_product']."</Id>\n";
				$xml_output .= "\t<Code>".$id['codice']."</Code>\n";
				$xml_output .= "\t<BarCode>".$id['barcode']."</BarCode>\n";
				$xml_output .= "\t<AvailableQty>".$avqty."</AvailableQty>\n";
				$xml_output .= "\t</Product>\n";			
			$xml_output .="</Products>\n</GAzieDocuments>";
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
				$_SESSION['errref'] = "Aggiornamento quantità dell'articolo: ". $d;			
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
					$_SESSION['errref'] = "Aggiornamento quantità dell'articolo: ". $d;				
				}
			} else { // Riporto il codice di errore
				$_SESSION['errmsg'] = "Impossibile connettersi al file di interfaccia: ".intval(substr($headers[0], 9, 3)).". AGGIORNARE L'E-COMMERCE MANUALMENTE!";
				$_SESSION['errref'] = "Aggiornamento quantità dell'articolo: ". $d;
			}
	}
	function GetOrder($last_id) { 
		// prendo gli eventuali ordini arrivati assieme ai dati del cliente, se nuovo lo importo (order+customer), 
		// in $last_id si deve passare l'ultimo ordine già importato al fine di non importare tutto ma solo i nuovi
		//Antonio Germani - $last_id non viene usato perché si controlla con una query se l'ordine è già stato importato
		@session_start();
		global $gTables,$admin_aziend;	
		$ftp_host = gaz_dbi_get_row($gTables['company_config'], "var", "server")['val'];
		$ftp_user = gaz_dbi_get_row($gTables['company_config'], "var", "user")['val'];
		$ftp_pass = gaz_dbi_get_row($gTables['company_config'], "var", "pass")['val'];
		$urlinterf = gaz_dbi_get_row($gTables['company_config'], 'var', 'path')['val']."ordini-gazie.php";
		$test = gaz_dbi_query("SHOW COLUMNS FROM `" . $gTables['admin'] . "` LIKE 'enterprise_id'");
		$exists = (gaz_dbi_num_rows($test)) ? TRUE : FALSE;
		if ($exists) {
			$c_e = 'enterprise_id';
		} else {
			$c_e = 'company_id';
		}
		$admin_aziend = gaz_dbi_get_row($gTables['admin'] . ' LEFT JOIN ' . $gTables['aziend'] . ' ON ' . $gTables['admin'] . '.' . $c_e . '= ' . $gTables['aziend'] . '.codice', "user_name", $_SESSION["user_name"]);
		// imposto la connessione al server
		$conn_id = ftp_connect($ftp_host);
		// effettuo login con user e pass
		$mylogin = ftp_login($conn_id, $ftp_user, $ftp_pass);
		if ((!$conn_id) or (!$mylogin)){// controllo se la connessione è OK...
			// non si connette FALSE
			$_SESSION['errmsg'] = "Problemi con le impostazioni FTP in configurazione avanzata azienda.";
			$_SESSION['errref'] = "Impossibile scaricare gli ordini dall'e-commerce";
		}
		$access=base64_encode($ftp_pass);
		// avvio il file di interfaccia presente nel sito web remoto
		$headers = @get_headers($urlinterf.'?access='.$access);
		if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file esiste o mi dà accesso
			$xml=simplexml_load_file($urlinterf.'?access='.$access) ;
			if (!$xml){
				$_SESSION['errmsg'] = "L'e-commerce non crea il file xml";
				$_SESSION['errref'] = "Impossibile scaricare gli ordini dall'e-commerce";
			}
			$count=0;$countDocument=0;
			foreach($xml->Documents->children() as $order) { // ciclo gli ordini
			
				if(!gaz_dbi_get_row($gTables['tesbro'], "numdoc", $order->Number)){ // se il numero d'ordine non esiste carico l'ordine in GAzie
					$query = "SHOW TABLE STATUS LIKE '" . $gTables['tesbro'] . "'";
					$result = gaz_dbi_query($query);
					$row = $result->fetch_assoc();
					$id_tesbro = $row['Auto_increment']; // questo è l'ID che avrà TESBRO: testata documento/ordine
					$query = "SHOW TABLE STATUS LIKE '" . $gTables['anagra'] . "'";
					$result = gaz_dbi_query($query);
					$row = $result->fetch_assoc();
					$id_anagra = $row['Auto_increment']; // questo è l'ID che avrà ANAGRA: Anagrafica cliente				
					$anagrafica = new Anagrafica(); 
					$last = $anagrafica->queryPartners('*', "codice BETWEEN " . $admin_aziend['mascli'] . "000000 AND " . $admin_aziend['mascli'] . "999999", "codice DESC", 0, 1);
					$codice = substr($last[0]['codice'], 3) + 1;
					$clfoco = $admin_aziend['mascli'] * 1000000 + $codice;// questo è il codice di CLFOCO da connettere all'anagrafica cliente se il cliente non esiste
	    			$esiste=0;
					if (strlen($order->CustomerFiscalCode)>0){ // controllo esistenza cliente per codice fiscale
						$query = "SELECT * FROM " . $gTables['anagra'] . " WHERE codfis ='" . $order->CustomerFiscalCode . "'";
						$check = gaz_dbi_query($query);
						if ($check->num_rows > 0){
							$esiste=1;
							$row = $check->fetch_assoc();
							$cl = gaz_dbi_get_row($gTables['clfoco'], "id_anagra", $row['id']);
							$clfoco=$cl['codice'];
						}
					}
					if (intval($order->CustomerVatCode)>0){ // controllo esistenza cliente per partita iva
						$query = "SELECT * FROM " . $gTables['anagra'] . " WHERE pariva ='" . $order->CustomerVatCode . "'";
						$check = gaz_dbi_query($query);
						if ($check->num_rows > 0){
							$esiste=1;
							$row = $check->fetch_assoc();
							$cl = gaz_dbi_get_row($gTables['clfoco'], "id_anagra", $row['id']);
							$clfoco=$cl['codice'];
						}
					}
					 // controllo esistenza cliente per cognome, nome e città
					$query = "SELECT * FROM " . $gTables['anagra'] . " WHERE ragso1 ='" . addslashes($order->CustomerName) . "' AND ragso2 ='". addslashes($order->CustomerSurname) . "'";
					$check = gaz_dbi_query($query);
					while ($row = $check->fetch_assoc()) {
						if (($check->num_rows > 0) && ($row['citspe']=$order->CustomerCity) && ($row['indspe']=$order->CustomerAddress)){
							$esiste=1;
							$cl = gaz_dbi_get_row($gTables['clfoco'], "id_anagra", $row['id']);
							$clfoco=$cl['codice'];
						}
					}
								
					If ($esiste==0) { //registro cliente se non esiste
							if ($order->CustomerCountry=="IT"){ // se la nazione è IT
								$lang="1";
							} else {
								$lang="0";
							}
							gaz_dbi_query("INSERT INTO " . $gTables['anagra'] . "(ragso1,ragso2,indspe,capspe,citspe,prospe,country,id_currency,id_language,telefo,codfis,pariva,fe_cod_univoco,e_mail,pec_email) VALUES ('" . addslashes($order->CustomerName) . "', '" . addslashes($order->CustomerSurname) . "', '". addslashes($order->CustomerAddress) ."', '".$order->CustomerPostCode."', '". addslashes($order->CustomerCity) ."', '". $order->CustomerProvince ."', '" . $order->CustomerCountry. "', '1', '".$lang."', '". $order->CustomerTel ."', '". $order->CustomerFiscalCode ."', '" . $order->CustomerVatCode . "', '" . $order->CustomerCodeFattEl . "', '". $order->CustomerEmail . "', '". $order->CustomerPecEmail . "')");
							
							gaz_dbi_query("INSERT INTO " . $gTables['clfoco'] . "(codice,id_anagra,descri,destin,speban) VALUES ('". $clfoco . "', '" . $id_anagra . "', '" .addslashes($order->CustomerName)." ".addslashes($order->CustomerSurname) . "', '". $order->CustomerShippingDestin ."', 'S')");
					}
					
					if ($order->TotalDiscount>0){ // se il sito ha mandato uno sconto totale a valore calcolo lo sconto in percentuale da dare ad ogni rigo
						$lordo=$order->Total+$order->TotalDiscount-$order->CostPaymentAmount-$order->CostShippingAmount;
						$netto=$lordo-$order->TotalDiscount;
						$percdisc= 100-(($netto/$lordo)*100);
					} else {
						$percdisc="";
					}
					
					if ($order->PricesIncludeVat=="true"){ // se il sito include l'iva la scorporo dalle spese banca e trasporto
						$CostPaymentAmount=floatval($order->CostPaymentAmount)/ 1.22; // floatval traforma da alfabetico a numerico
						$CostShippingAmount=floatval($order->CostShippingAmount) / 1.22;
					} else {
						$CostPaymentAmount=floatval($order->CostPaymentAmount);
						$CostShippingAmount=floatval($order->CostShippingAmount);
					}
											
					// registro testata ordine
					gaz_dbi_query("INSERT INTO " . $gTables['tesbro'] . "(tipdoc,seziva,print_total,datemi,numdoc,datfat,clfoco,pagame,listin,spediz,traspo,speban,caumag,expense_vat,initra,status,adminid) VALUES ('VOW', '1', '1', '" . $order->DateOrder . "', '" .$order->Number . "', '0000-00-00', '". $clfoco . "', '" .$order->PaymentName."', '". $order->PriceListNum . "', '".$order->Carrier."', '". $CostShippingAmount ."', '". $CostPaymentAmount ."', '1', '". $admin_aziend['preeminent_vat']."', '" . $order->DateOrder. "', 'ONLINE-SHOP', '" . $admin_aziend['adminid'] . "')");
					
					// Gestione righi ordine					
					foreach($xml->Documents->Document[$countDocument]->Rows->children() as $orderrow) { // carico le righe dell'ordine
					
						// controllo se esiste l'articolo in GAzie 
						$ckart = gaz_dbi_get_row($gTables['artico'], "ref_ecommerce_id_product", $orderrow->Id);
						$codart=$ckart['codice']; // se esiste ne prendo il codice come $codart
						$descri=$ckart['descri'];// se esiste ne prendo descri come $descri						
		 
						if (!$ckart){ // se non esiste creo un nuovo articolo su gazie come servizio in quanto non si sa se deve movimentare il magazzino					
							if ($orderrow->VatAli<1){ // se il sito non ha mandato l'aliquota IVA dell'articolo ci metto quella che ha mandato come base aziendale riservato alle spese
								$orderrow->VatCode=$order->CostVatCode;
								$orderrow->VatAli=$order->CostVatAli;
							} 
							$vat = gaz_dbi_get_row($gTables['aliiva'], "aliquo", $orderrow->VatAli, " AND tipiva = 'I'");
							gaz_dbi_query("INSERT INTO " . $gTables['artico'] . "(codice,descri,ref_ecommerce_id_product,good_or_service,unimis,catmer,preve2,web_price,web_public,aliiva,codcon,adminid) VALUES ('". substr($orderrow->Code,0,15) ."', '". addslashes($orderrow->Description) ."', '". $orderrow->Id ."', '1', '" . $orderrow->MeasureUnit . "', '" .$orderrow->Category . "', '". $orderrow->Price ."', '". $orderrow->Price ."', '1', '".$vat['codice']."', '420000006', '" . $admin_aziend['adminid'] . "')");
							$codart= substr($orderrow->Code,0,15);// dopo averlo creato ne prendo il codice come $codart
							$descri= $orderrow->Description; //prendo anche la descrizione
							$codvat=$vat['aliiva'];
							$aliiva=$vat['aliquo'];
						} else {
							$codvat=gaz_dbi_get_row($gTables['artico'], "codice", $codart)['aliiva'];
							$aliiva=$orderrow->VatAli;
						}
						
						// salvo rigo su database tabella rigbro 
						gaz_dbi_query("INSERT INTO " . $gTables['rigbro'] . "(id_tes,codart,descri,unimis,quanti,prelis,sconto,codvat,codric,pervat,status) VALUES ('" . intval($id_tesbro) . "','" . $codart . "','" . addslashes($descri) . "','". $orderrow->MeasureUnit . "','" . $orderrow->Qty . "','" . $orderrow->Price . "', '".$percdisc."', '". $codvat. "', '420000006', '". $aliiva. "', 'ONLINE-SHOP')");
					}
					$count++;//aggiorno contatore nuovi ordini
					$countDocument++;//aggiorno contatore Document
					$id_tesbro++;				
				} else {
					$countDocument++;//aggiorno contatore Document	
				}					
			}						
		} else { // IL FILE INTERFACCIA NON ESISTE > chiudo la connessione ftp
			ftp_quit($conn_id);
			$_SESSION['errmsg'] = "Il file xml degli ordini non si apre";
			$_SESSION['errref'] = "Codice errore = ".intval(substr($headers[0], 9, 3));
			if (intval(substr($headers[0], 9, 3))==0) {
				$_SESSION['errref'] = $_SESSION['errref']." controllare connessione internet";
			}
		}
		if ($count>0){
			if ($count==1){
				$_SESSION['errmsg'] = "E' arrivato ". $count ." ordine dall'e-commerce";
			} else {
				$_SESSION['errmsg'] = "Sono arrivati ". $count ." ordini dall'e-commerce";
			}
		}
	}
}