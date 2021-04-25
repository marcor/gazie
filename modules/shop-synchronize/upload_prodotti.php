<?php
/* ------------------------------------------------------------------------
  Upload q.tà articoli da GAzie a e-commerce via FTP
  ------------------------------------------------------------------------
  @Author    Antonio Germani
  @Website   http://www.programmisitiweb.lacasettabio.it
  @Copyright Copyright (C) 2018 - 2021 Antonio Germani All Rights Reserved.
  versione 3.0
  ------------------------------------------------------------------------ 
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
require ("../../modules/magazz/lib.function.php");
$gForm = new magazzForm;
$resserver = gaz_dbi_get_row($gTables['company_config'], "var", "server");
$ftp_host= $resserver['val'];
$resftp_path = gaz_dbi_get_row($gTables['company_config'], "var", "ftp_path");
$ftp_path_upload=$resftp_path['val'];
$resuser = gaz_dbi_get_row($gTables['company_config'], "var", "user");
$ftp_user = $resuser['val'];
$respass = gaz_dbi_get_row($gTables['company_config'], "var", "pass");
$ftp_pass= $respass['val'];
$accpass = gaz_dbi_get_row($gTables['company_config'], "var", "accpass")['val'];
$test = gaz_dbi_query("SHOW COLUMNS FROM `" . $gTables['admin'] . "` LIKE 'enterprise_id'");
$exists = (gaz_dbi_num_rows($test)) ? TRUE : FALSE;
if ($exists) {
    $c_e = 'enterprise_id';
} else {
    $c_e = 'company_id';
}
$admin_aziend = gaz_dbi_get_row($gTables['admin'] . ' LEFT JOIN ' . $gTables['aziend'] . ' ON ' . $gTables['admin'] . '.' . $c_e . '= ' . $gTables['aziend'] . '.codice', "user_name", $_SESSION["user_name"]);
$path = gaz_dbi_get_row($gTables['company_config'], 'var', 'path');
$urlinterf = $path['val']."articoli-gazie.php";// nome del file interfaccia presente nella root del sito e-commerce. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
// il percorso per raggiungere questo file va impostato in configurazione avanzata azienda alla voce "Website root directory
ob_flush();
flush();
ob_start();


use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
if (gaz_dbi_get_row($gTables['company_config'], 'var', 'Sftp')['val']=="SI"){

	// SFTP login with private key and password
	
	set_include_path(get_include_path() . PATH_SEPARATOR . '../../library');

	include '../../library/phpseclib/Net/SSH2.php';

	include '../../library/phpseclib/Net/SFTP.php';
	include '../../library/phpseclib/Net/SFTP/Stream.php';

	include '../../library/phpseclib/Crypt/AES.php';

	include '../../library/phpseclib/Crypt/RSA.php';	
	

	$ftp_port = gaz_dbi_get_row($gTables['company_config'], "var", "port")['val'];
	$ftp_key = gaz_dbi_get_row($gTables['company_config'], "var", "chiave")['val'];

	if (gaz_dbi_get_row($gTables['company_config'], "var", "keypass")['val']=="key"){ // SFTP log-in con KEY
		$key = new RSA();
		$key->setPassword($ftp_pass);
		$key -> loadKey(file_get_contents('../../data/files/'.$admin_aziend['codice'].'/secret_key/'. $ftp_key .''));

		$sftp = new SFTP($ftp_host, $ftp_port);
		if (!$sftp->login($ftp_user, $key)) {
			// non si connette: key LOG-IN FALSE
			?>
			<script>
			alert("<?php echo "Mancata connessione Sftp con file chiave segreta: impossibile scaricare gli ordini dall\'e-commerce"; ?>");
			location.replace("<?php echo $_POST['ritorno']; ?>");
			</script>
			<?php										
		} else {
			?>
			<div class="alert alert-success text-center" >
			<strong>ok</strong> Connessione SFTP con chiave riuscita.
			</div>
			<?php
		} 
	} else { // SFTP log-in con password
	
		$sftp = new SFTP($ftp_host, $ftp_port);
		if (!$sftp->login($ftp_user, $ftp_pass)) {
			// non si connette: password LOG-IN FALSE
			?>
			<script>
			alert("<?php echo "Mancata connessione Sftp con password: impossibile scaricare gli ordini dall\'e-commerce"; ?>");
			location.replace("<?php echo $_POST['ritorno']; ?>");
			</script>
			<?php			
		} else {
			?>
			<div class="alert alert-success text-center" >
			<strong>ok</strong> Connessione SFTP con password riuscita.
			</div>
			<?php
		}
	}				
} else {
 
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
} 

// creo il file xml
$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
$xml_output .= "\n<Products>\n";
$artico = gaz_dbi_query ('SELECT codice, barcode, ref_ecommerce_id_product FROM '.$gTables['artico'].' WHERE web_public = \'1\' and good_or_service <> \'1\' ORDER BY codice');
 while ($item = gaz_dbi_fetch_array($artico)){
		$avqty = 0;
		$ordinatic = $gForm->get_magazz_ordinati($item['codice'], "VOR");
		$ordinatic = $ordinatic + $gForm->get_magazz_ordinati($item['codice'], "VOW");
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
		$xml_output .= "\t<Id>".$item['ref_ecommerce_id_product']."</Id>\n";
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
if (gaz_dbi_get_row($gTables['company_config'], 'var', 'Sftp')['val']=="SI"){
		
		if ($sftp->put($ftp_path_upload."prodotti.xml", $xmlFile, SFTP::SOURCE_LOCAL_FILE)){
			$sftp->disconnect();
			?>
			<div class="alert alert-success text-center" >
			<strong>ok</strong> il file xml è stato trasferito al sito web tramite SFTP.
			</div>
			<?php
			
		}else {
			// chiudo la connessione FTP 
			$sftp->disconnect();
			?>
			<script>
			alert("<?php echo "Errore di upload del file xml tramite SFTP"; ?>");
			location.replace("<?php echo $_POST['ritorno']; ?>");
			</script>
			<?php			
		}	
} else { // FTP semplice
	//turn passive mode on
	ftp_pasv($conn_id, true);
	// upload file xml
	if (ftp_put($conn_id, $ftp_path_upload."prodotti.xml", $xmlFile, FTP_ASCII)){
		?>
		<div class="alert alert-success text-center" >
		<strong>ok</strong> il file xml è stato trasferito al sito web.
		</div>
		<?php
		// chiudo la connessione FTP 
		ftp_quit($conn_id);
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
}
$access=base64_encode($accpass);

// avvio il file di interfaccia presente nel sito web remoto
$headers = @get_headers($urlinterf.'?access='.$access);
if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file esiste o mi dà accesso
	$file = fopen ($urlinterf.'?access='.$access, "r");
	if (!$file) {
		
		?>
		<script>
		alert("<?php echo "Errore: il file di interfaccia web non si apre!"; ?>");
		location.replace("<?php echo $_POST['ritorno']; ?>");
		</script>
		<?php
		
	} else {
		
		?>
		<div class="alert alert-success text-center" >
		<strong>ok</strong> Aggiornamento prodotti riuscito.
		</div>
		<script>
		alert("<?php echo "Aggiornamento prodotti riuscito!"; ?>");
		location.replace("<?php echo $_POST['ritorno']; ?>");
		</script>
		<?php
		exit;
	}
} else { // IL FILE INTERFACCIA NON ESISTE > ESCO
	
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
                            