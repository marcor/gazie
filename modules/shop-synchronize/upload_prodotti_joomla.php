<?php
/* ------------------------------------------------------------------------
  INTERFACCIA Upload q.tà articoli da GAzie a Joomla 
  ------------------------------------------------------------------------
  @Author    Antonio Germani
  @Website   http://www.lacasettabio.it
  @Copyright Copyright (C) 2018 - 2019 Antonio Germani All Rights Reserved.
  versione 1.0
  ------------------------------------------------------------------------ */
  
/* impostazioni da fare prima di avviare il file
inserire i dati dentro alle virgolette non toccare il resto */

$urlinterf="https://www.lacasettabio.it/*******/articoli-gazie.php"; // url completa del file interfaccia presente nella root del sito con negozio online. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.


 // ---------------------------da qui in poi non modificare nulla---------------------------------
 
 
require ("../../modules/magazz/lib.function.php");
$gForm = new magazzForm;
$resserver = gaz_dbi_get_row($gTables['company_config'], "var", "server");
$ftp_host= $resserver['val'];
$resuser = gaz_dbi_get_row($gTables['company_config'], "var", "user");
$ftp_user = $resuser['val'];
$respass = gaz_dbi_get_row($gTables['company_config'], "var", "pass");
$ftp_pass= $respass['val'];
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

// controllo se la connessione è OK...
if ((!$conn_id) or (!$mylogin)){ 
	echo "Connessione fallita a " . $ftp_host . "!";
}

// creo il file xml
$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
$xml_output .= "\n<Products>\n";
$artico = gaz_dbi_query ('SELECT codice, barcode FROM '.$gTables['artico'].' WHERE web_public = \'1\' and good_or_service <> \'1\' ORDER BY codice');
 while ($item = gaz_dbi_fetch_array($artico)){
		 $mv = $gForm->getStockValue(false, $item['codice']);
         $magval = array_pop($mv);
		 $avqty=$magval['q_g'];
		 if ($avqty<0 or $avqty==""){
			 $avqty="0";
		 }
		 if (intval($item['barcode'])==0) {
			 $item['barcode']="NULL";
		 }
		 $xml_output .= "\t<Product>\n";
		 $xml_output .= "\t<Code>".$item['codice']."</Code>\n";
		 $xml_output .= "\t<BarCode>".$item['barcode']."</BarCode>\n";
		 $xml_output .= "\t<AvailableQty>".$avqty."</AvailableQty>\n";
		 $xml_output .= "\t</Product>\n";	 
 }
$xml_output .="\n</Products>\n</GAzieDocuments>";
$xmlFile = "prodotti.xml";
$xmlHandle = fopen($xmlFile, "w");
fwrite($xmlHandle, $xml_output);
fclose($xmlHandle);

// upload file xml
if (ftp_put($conn_id, "public_html/easyfatt/prodotti.xml", $xmlFile, FTP_ASCII)){
	echo "Successfully uploaded $xmlFile.";
} else{
  echo "Error uploading $xmlFile.";
}

// avvio il file di interfaccia presente nel sito web remoto
$headers = @get_headers($urlinterf.'?password='.$ftp_pass);
if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file esiste o mi dà accesso
	$file = fopen ($urlinterf.'?password='.$ftp_pass, "r");
	if (!$file) {
		echo "<p>Unable to open remote file.\n";
	} else {
		echo"Connessione interfaccia OK";
		header("Location: " . $_POST['ritorno']);
		exit;
	}
} else { // IL FILE INTERFACCIA NON ESISTE > ESCO
	echo "errore connessione interfaccia web",intval(substr($headers[0], 9, 3));
	header("Location: " . "../../modules/magazz/report_artico.php");
	ftp_quit($conn_id);
	exit;
}

// chiudo la connessione FTP 
ftp_quit($conn_id);

?>
                            