<?php

require_once('../../library/php-imap/ImapMailbox.php');
require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
set_time_limit(3600);
global $gTables;
// IMAP  
$cemail = gaz_dbi_get_row($gTables['company_config'],'var','cemail');
$cpassword = gaz_dbi_get_row($gTables['company_config'],'var','cpassword');
$cfiltro = gaz_dbi_get_row($gTables['company_config'],'var','cfiltro');
$cpopimap = gaz_dbi_get_row($gTables['company_config'],'var','cpopimap');
define('CATTACHMENTS_DIR',  '../../data/files/ricevutesdi');

$mailbox = new ImapMailbox($cpopimap['val'], $cemail['val'], $cpassword['val'], CATTACHMENTS_DIR, 'utf-8');
$mails = array();

// Get some mail
$mailsIds = $mailbox->searchMailBox($cfiltro['val'] );
if(!$mailsIds) {
	die('Casella di posta elettronica vuota');
}

//$mailId = reset($mailsIds);
//$mail = $mailbox->getMail($mailId);

//var_dump($mailbox->getMailsInfo($mailsIds));
//var_dump($mailId);
//var_dump($mail);

//$aaa= $mail->getAttachments();
//var_dump($aaa);

$bbb = new IncomingMailAttachment();
$domDoc = new DOMDocument;


foreach($mailsIds as $mailId) {
  $mail = $mailbox->getMail($mailId);
    
    $aaa= $mail->getAttachments();
    $ccc = array_values($aaa);  
    $bbb = $ccc[0];
    
    $domDoc->load($bbb->filePath);

    $xpath = new DOMXPath($domDoc);	


	
	$result = $xpath->query("//IdentificativoSdI")->item(0);
	$idsidi = $result->textContent;  
	
    $result = $xpath->query("//NomeFile")->item(0);
    $nome_file = $result->textContent;
	
	$result = $xpath->query("//DataOraRicezione")->item(0);
    
	if ($result) {
	     $data_ora_ricezione = $result->textContent; }
	else {
	     $data_ora_ricezione = "";
	}
	
	
    $result = $xpath->query("//MessageId")->item(0);
    $message_id = $result->textContent;    
   

	$result = $xpath->query("//ListaErrori/Errore/Descrizione")->item(0);
	if ($result) {
     	$errore = $result->textContent; }
	else {
	    $errore = ""; }
    
	$result = $xpath->query("//Esito")->item(0);
    if ($result) {
     	$errore = $result->textContent; }
	else {
	    $errore = ""; }

  
    $status=""; 
    if (strpos($bbb->name, '_MC_') >0) {
       $status = "Mancata consegna"; 
    } elseif (strpos($bbb->name, '_NS_') >0) {
       $status = "Notifica di scarto";
    } elseif (strpos($bbb->name, '_RC_') >0) {
       $status = "Consegnata";   
    }  elseif (strpos($bbb->name, '_NE_') >0) {
       $status = "Notifica esito";   
    }  
  
   $valori=array('filename_ori'=>$nome_file,
         'id_tes_ref'=>11,
				 'exec_date'=>$data_ora_ricezione,
				 'filename_son'=>'',
				 'id_SDI'=>$idsidi,
         'filename_ret'=>$bbb->name,
         'mail_id'=>$message_id,
				 'data'=>'',
				 'status'=>$status,
				 'descri'=>$errore);
    
    fae_fluxInsert($valori);
    print $idsidi . " " . $nome_file . " " . $status ."<br/>";
  
}



?>