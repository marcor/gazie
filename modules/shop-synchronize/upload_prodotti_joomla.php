<?php
/* ------------------------------------------------------------------------
  INTERFACCIA Upload q.tà articoli da GAzie a Joomla 
  ------------------------------------------------------------------------
  @Author    Antonio Germani
  @Website   http://www.lacasettabio.it
  @Copyright Copyright (C) 2018 - 2019 Antonio Germani All Rights Reserved.
  versione 1.0
  ------------------------------------------------------------------------ */
  
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
$path = gaz_dbi_get_row($gTables['company_config'], 'var', 'path');
$urlinterf = $path['val']."articoli-gazie.php";// nome del file interfaccia presente nella root del sito Joomla. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
// il percorso per raggiungere questo file va impostato in configurazione avanzata azienda alla voce "Website root directory
ob_flush();
flush();
ob_start();
 
// imposto la connessione al server
$conn_id = ftp_connect($ftp_host);

// effettuo login con user e pass
$mylogin = ftp_login($conn_id, $ftp_user, $ftp_pass);

// controllo se la connessione è OK...
if ((!$conn_id) or (!$mylogin)){ 
	
	?>
	<script>
	alert("<?php echo "Errore: connessione FTP a " . $ftp_host . " non riuscita!"; ?>");
	location.replace("<?php echo $_POST['ritorno']; ?>");
    </script>
	<?php
} else {
	?>
	<div class="alert alert-success text-center" >
	<strong>ok</strong> Connessione FTP riuscita.
	</div>
	<?php
}

// creo il file xml
$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
$xml_output .= "\n<Products>\n";
$artico = gaz_dbi_query ('SELECT codice, barcode FROM '.$gTables['artico'].' WHERE web_public = \'1\' and good_or_service <> \'1\' ORDER BY codice');
 while ($item = gaz_dbi_fetch_array($artico)){
		$avqty = 0;
		$ordinatic = $gForm->get_magazz_ordinati($item['codice'], "VOR");
		$mv = $gForm->getStockValue(false, $item['codice']);
        $magval = array_pop($mv);
		$avqty=$magval['q_g']-$ordinatic;
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
//turn passive mode on
ftp_pasv($conn_id, true);
// upload file xml
if (ftp_put($conn_id, "public_html/easyfatt/prodotti.xml", $xmlFile, FTP_ASCII)){
	?>
	<div class="alert alert-success text-center" >
	<strong>ok</strong> il file xml è stato trasferito al sito web.
	</div>
	<?php
} else{
	// chiudo la connessione FTP 
	ftp_quit($conn_id);
  	?>
	<script>
	alert("<?php echo "Errore di upload del file xml"; ?>");
	location.replace("<?php echo $_POST['ritorno']; ?>");
    </script>
	<?php
}

$access=base64_encode($ftp_pass);

// avvio il file di interfaccia presente nel sito web remoto
$headers = @get_headers($urlinterf.'?access='.$access);
if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file esiste o mi dà accesso
	$file = fopen ($urlinterf.'?access='.$access, "r");
	if (!$file) {
		// chiudo la connessione FTP 
		ftp_quit($conn_id);
		?>
		<script>
		alert("<?php echo "Errore: il file di interfaccia web non si apre!"; ?>");
		location.replace("<?php echo $_POST['ritorno']; ?>");
		</script>
		<?php
		
	} else {
		// chiudo la connessione FTP 
		ftp_quit($conn_id);
		?>
		<div class="alert alert-success text-center" >
		<strong>ok</strong> Aggiornamento prodotti riuscito.
		</div>
		<script>
		location.replace("<?php echo $_POST['ritorno']; ?>");
		</script>
		<?php
		exit;
	}
} else { // IL FILE INTERFACCIA NON ESISTE > ESCO
	// chiudo la connessione FTP 
	ftp_quit($conn_id);
	?>
	<script>
		alert("<?php echo "Errore di connessione al file di interfaccia web = ",intval(substr($headers[0], 9, 3)); ?>");
		 location.replace("<?php echo $_POST['ritorno']; ?>");
    </script>
	<?php
		
	exit;
}

// chiudo la connessione FTP 
ftp_quit($conn_id);

?>
                            