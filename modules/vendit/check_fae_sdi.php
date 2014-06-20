<?php

require_once('../../library/php-imap/ImapMailbox.php');
require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();

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
	
    print $result->textContent . " ";
	$result = $xpath->query("//NomeFile")->item(0);
	print $result->textContent . " ";
	
	$result = $xpath->query("//ListaErrori/Errore/Descrizione")->item(0);
	if ($result)
     	echo $result->textContent . " ";
	
    echo "<br/>";	
  
  $valori=array('filename_ori'=>'aa','id_tes_ref'=>11,'exec_date'=>'2014-06-20 12:20:45','filename_son'=>'','id_SDI'=>10,'data'=>'','status'=>'','descri'=>'');
  var_dump($valori); 
  
  fae_fluxInsert($valori);
  
}


?>