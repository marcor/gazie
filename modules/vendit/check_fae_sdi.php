<?php

require_once('../../library/php-imap/ImapMailbox.php');
require("../../library/include/datlib.inc.php");

// Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
         
//Flush (send) the output buffer and turn off output buffering
//ob_end_flush();
while (@ob_end_flush());
         
// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);
 


//Alcuni browser non iniziano ad eseguire output fino a quando non viene superato un certo numero di byte 
for($i = 0; $i < 1300; $i++)
{
echo ' ';
}
 

$admin_aziend=checkAdmin();
set_time_limit(3600);
global $gTables;
// IMAP  
$cemail = gaz_dbi_get_row($gTables['company_config'],'var','cemail');
$cpassword = gaz_dbi_get_row($gTables['company_config'],'var','cpassword');
$cfiltro = gaz_dbi_get_row($gTables['company_config'],'var','cfiltro');
$cpopimap = gaz_dbi_get_row($gTables['company_config'],'var','cpopimap');
$last_fae_email = gaz_dbi_get_row($gTables['company_config'],'var','last_fae_email');
define('CATTACHMENTS_DIR',  '../../data/files/ricevutesdi');

$mailbox = new ImapMailbox($cpopimap['val'], $cemail['val'], $cpassword['val'], CATTACHMENTS_DIR, 'utf-8');
$mails = array();

//se passato checkall verranno riscaricate tutte le email senza tener conto dell'eventule filtro: UNSEEN (solo non lette) 
if (isset($_GET['checkall'])) {
   $cfiltro['val'] = str_replace("UNSEEN","", $cfiltro['val']);
}


// Get some mail
$mailsIds = $mailbox->searchMailBox($cfiltro['val'] );
if(!$mailsIds) {
	echo('Nessuna nuova email con questo filtro: ' . $cfiltro['val']);
  die("<p align=\"center\"><a href=\"./report_fae_sdi.php\">Ritorna a report Fatture elettroniche</a></p>");
}

//$mailId = reset($mailsIds);
//$mail = $mailbox->getMail($mailId);

echo "Attendere: Verifico la posta elettronica sulla casella " . $cemail['val'] ."<br />";
$n_email = count($mailbox->getMailsInfo($mailsIds));



// if ($n_email == $last_fae_email['val']) { 
//     echo "Nessuna variazione sul numero di email ($n_email) <br/>";
//     echo "<p align=\"center\"><a href=\"./report_fae_sdi.php\">Ritorna a report Fatture elettroniche</a></p>";
//     exit();
// }


echo "N. email: " . $n_email ."<br />";

   
 
//var_dump($mailId);
//var_dump($mail);

//$aaa= $mail->getAttachments();
//var_dump($aaa);

$bbb = new IncomingMailAttachment();
$domDoc = new DOMDocument;

echo "I file vengono salvati in: " .  CATTACHMENTS_DIR . "<br/>";


foreach($mailsIds as $mailId) {
    $mail = $mailbox->getMail($mailId);
    $data_mail =  $mail->date;
    $aaa= $mail->getAttachments();
    $ccc = array_values($aaa);  
    $bbb = $ccc[0];
    $nome_file_ret = $bbb->name;
    
    $domDoc->load($bbb->filePath);
    $xpath = new DOMXPath($domDoc);	
	
    $result = $xpath->query("//MessageId")->item(0);
    $message_id = $result->textContent;    
   	
    
    $data_ora_ricezione="";
	  $errore = "";  
    $status=""; 
    
    //aggiungere dei controlli
    $nome_info=explode( '_', $nome_file_ret );
    $nome_status = $nome_info[2];
    $progressivo_status = substr($nome_info[3],0,3);
    
    
    if ($nome_status == 'MC') {
       $status = "MC";
       $result = $xpath->query("//IdentificativoSdI")->item(0);
	     $idsidi = $result->textContent;  
	
       $result = $xpath->query("//NomeFile")->item(0);
       $nome_file = $result->textContent;
	
	     $result = $xpath->query("//DataOraRicezione")->item(0);
       $data_ora_ricezione = $result->textContent; 
       $data_ora_consegna =$data_ora_ricezione; 
        
    } elseif ($nome_status == 'NS') {
       $status = "NS";

       $result = $xpath->query("//IdentificativoSdI")->item(0);
	     $idsidi = $result->textContent;  
	
       $result = $xpath->query("//NomeFile")->item(0);
       $nome_file = $result->textContent;
	
	     $result = $xpath->query("//DataOraRicezione")->item(0);
	     $data_ora_ricezione = $result->textContent; 
       $data_ora_consegna =$data_ora_ricezione;

       $result = $xpath->query("//ListaErrori/Errore/Descrizione")->item(0);
	     $errore = $result->textContent; 
                   
    } elseif ($nome_status == 'RC') {
       $status = "RC";
	     $result = $xpath->query("//IdentificativoSdI")->item(0);
	     $idsidi = $result->textContent;  
	
       $result = $xpath->query("//NomeFile")->item(0);
       $nome_file = $result->textContent;
	
	     $result = $xpath->query("//DataOraRicezione")->item(0);
	     $data_ora_ricezione = $result->textContent; 
       $result = $xpath->query("//DataOraConsegna")->item(0);
	     $data_ora_consegna = $result->textContent;
                        
    }  elseif ($nome_status == 'NE') {
       $status = "NE";

       $result = $xpath->query("//IdentificativoSdI")->item(0);
       $idsidi = $result->textContent;  
	
       $result = $xpath->query("//NomeFile")->item(0);
       $nome_file = $result->textContent;

       $result = $xpath->query("//Esito")->item(0);
       $errore = $result->textContent;
       
       if ($errore == "EC02") {
	 $result = $xpath->query("//Descrizione")->item(0);
	 $errore = "EC02: " . $result->textContent;
       }
       
       
       $data_ora_ricezione =$data_mail;
       $data_ora_consegna =$data_mail;                
       
    }  elseif ($nome_status == 'DT') {
       $status = "DT";

	     $result = $xpath->query("//IdentificativoSdI")->item(0);
	     $idsidi = $result->textContent;  
	
       $result = $xpath->query("//NomeFile")->item(0);
       $nome_file = $result->textContent;

	     $result = $xpath->query("//Descrizione")->item(0);
       $errore = $result->textContent;  
       
       $data_ora_ricezione =$data_mail;
       $data_ora_consegna =$data_mail;                
       
    }  
  
  
   
   
   $nome_file_ori =str_replace('.xml.p7m','.xml', $nome_file);
   $verifica = gaz_dbi_get_row($gTables['fae_flux'], 'filename_ori ', $nome_file_ori);
   
   if ($verifica == false) {
     $id_tes = 0;
   } else {
      $id_tes = $verifica['id_tes_ref'];
   }
   
   //non dovrebbero esserci ma verifica eventuali doppioni causa errori sulla casella di posta elettronica
   $verifica = gaz_dbi_get_row($gTables['fae_flux'], 'mail_id', $message_id);   
   if ($verifica == false) {
   $valori=array('filename_ori'=>$nome_file,
         'id_tes_ref'=>$id_tes,
				 'exec_date'=>$data_mail,
         'received_date'=>$data_ora_ricezione,
         'delivery_date'=>$data_ora_consegna,
				 'filename_son'=>'',
				 'id_SDI'=>$idsidi,
         'filename_ret'=>$nome_file_ret,
         'mail_id'=>$message_id,
				 'data'=>'',
				 'flux_status'=>$status,
         'progr_ret'=>$progressivo_status,
				 'flux_descri'=>$errore);
    
    fae_fluxInsert($valori);

    echo  $idsidi . " " . $nome_file . " " . $status . " ". $progressivo_status."<br/>";
    } else {
    echo " presente ". $idsidi . " " . $nome_file . " " . $status . " ". $progressivo_status."<br/>";
    } 
        

    
    
}

    gaz_dbi_put_row($gTables['company_config'],'var','last_fae_email','val',$n_email);

    echo "Completato";
    echo "<p align=\"center\"><a href=\"./report_fae_sdi.php\">Ritorna a report Fatture elettroniche</a></p>";

?>