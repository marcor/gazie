<?php

function SendFattureElettroniche($zip_fatture) {
//require("../../library/include/datlib.inc.php");
global $gTables ;
$admin_aziend = checkAdmin();
if (! isset($zip_fatture)) {
	 echo "manca pacchetto fatture" ;
	 return false ;
 } else {
	 //	     $user = gaz_dbi_get_row($gTables['admin'], "user_name", $_SESSION["user_name"]);
//	$fn=substr($_GET['fn'],0,37);
	//$file_url = "../../data/files/".$admin_aziend['codice']."/".$xml_fattura;
    $content = new StdClass;
		$aurl=explode("/",$zip_fatture) ;
		//$content->name = substr($zip_fatture,-23,23);
		$content->name = $aurl[count($aurl)-1] ;
    $content->urlfile = $zip_fatture; // se passo l'url GAzieMail allega un file del file system e non da stringa
	$dest_fae_zip_package['e_mail'] = gaz_dbi_get_row($gTables['company_config'], 'var', 'dest_fae_zip_package')['val'];
	echo $content->urlfile . "<br/>" ;
	echo $content->name . "<br/>" ;
	 echo $zip_fatture . "<br/>";
	if (!empty($dest_fae_zip_package['e_mail'])) {
		$gMail = new GAzieMail();
		if ($gMail->sendMail($admin_aziend, $user, $content, $dest_fae_zip_package)){
			// se la mail è stata trasmessa con successo aggiorno lo stato sulla tabella dei flussi
			gaz_dbi_put_query($gTables['fae_flux'], "filename_zip_package = '" . $content->name."'", "flux_status", "@");
      $data_invio = date("Y-m-d") ;
      // metto la data odierna come data di invio exec_date
      gaz_dbi_put_query($gTables['fae_flux'], "filename_zip_package = '" . $content->name."'", "exec_date", $data_invio);

			echo "<p>INVIO PACCHETTO FATTURE ELETTRONICHE RIUSCITO!!!</p>";
		}
	}
	return 0 ;
}
}

function SendFatturaElettronica($xml_fattura) {
//require("../../library/include/datlib.inc.php");
global $gTables ;
$admin_aziend = checkAdmin();
if (! isset($xml_fattura)) {
	 echo "manca file fattura" ;
	 return false ;
 } else {
	 //	     $user = gaz_dbi_get_row($gTables['admin'], "user_name", $_SESSION["user_name"]);
//	$fn=substr($_GET['fn'],0,37);
	//$file_url = "../../data/files/".$admin_aziend['codice']."/".$xml_fattura;
    $content = new StdClass;
		$aurl=explode("/",$xml_fattura) ;
		//$content->name = substr($xml_fattura,-23,23);
		$content->name = $aurl[count($aurl)-1] ;  // l'ultima parte dell'url è il nome del file
    $content->urlfile = $xml_fattura; // se passo l'url GAzieMail allega un file del file system e non da stringa
	$dest_fae_zip_package['e_mail'] = gaz_dbi_get_row($gTables['company_config'], 'var', 'dest_fae_zip_package')['val'];
	echo $content->urlfile . "<br/>" ;
	echo $content->name . "<br/>" ;
	 echo $xml_fattura . "<br/>";
	if (!empty($dest_fae_zip_package['e_mail'])) {
		$gMail = new GAzieMail();
		if ($gMail->sendMail($admin_aziend, $user, $content, $dest_fae_zip_package)){
			// se la mail è stata trasmessa con successo aggiorno lo stato sulla tabella dei flussi
			gaz_dbi_put_query($gTables['fae_flux'], "filename_ori = '" . $content->name."'", "flux_status", "@");
      $data_invio = date("Y-m-d") ;
      // metto la data odierna come data di invio exec_date
      gaz_dbi_put_query($gTables['fae_flux'], "filename_ori = '" . $content->name."'", "exec_date", $data_invio);

			echo "<p>INVIO FATTURA ELETTRONICA RIUSCITO!!!</p>";
		}
	}
	return 0 ;
}
}


?>
