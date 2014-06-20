<?php

require_once('../../library/php-imap/ImapMailbox.php');
require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
gaz_set_time_limit (0);
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
    $data_ora_ricezione = $result->textContent;
	
	$result = $xpath->query("//ListaErrori/Errore/Descrizione")->item(0);

	if ($result) {
     	$errore = $result->textContent; }
	else {
	    $errore = ""; }
    
	
  
    print $idsidi . " " . $nome_file . " " . $errore;
  
   $valori=array('filename_ori'=>$nome_file,
                 'id_tes_ref'=>11,
				 'exec_date'=>$data_ora_ricezione,
				 'filename_son'=>'',
				 'id_SDI'=>$idsidi,
				 'data'=>'',
				 'status'=>'',
				 'descri'=>'');
    
    fae_fluxInsert($valori);
  
}

gaz_set_time_limit (30);

?>