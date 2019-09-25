<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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

require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$send_fae_zip_package = gaz_dbi_get_row($gTables['company_config'], 'var', 'send_fae_zip_package');

if (!empty($send_fae_zip_package['val']) ) {
	$where1 = " id_SDI!=0 AND (flux_status = '@' OR flux_status = '@@' OR (filename_ori LIKE '%.xml.p7m' AND flux_status = 'RC')) ";
	$risultati = gaz_dbi_dyn_query("*", $gTables['fae_flux'], $where1);
	if (!$risultati) {
		die('<p align="center"><a href="./report_fae_sdi.php">Ritorna a report Fatture elettroniche</a></p>');
	}
	$IdentificativiSdI = array();
	while ($r = gaz_dbi_fetch_array($risultati)) {
		$IdentificativiSdI[] = $r['id_SDI'];
	}
	require('../../library/' . $send_fae_zip_package['val'] . '/SendFaE.php');
	$notifiche = ReceiveNotifiche(array($admin_aziend['country'].$admin_aziend['codfis'] => $IdentificativiSdI));
	if (!empty($notifiche)) {
		if (is_array($notifiche)) {
			foreach ($notifiche as $id_SDI=>$notifica) {
				gaz_dbi_put_query($gTables['fae_flux'], "id_SDI='" . $id_SDI . "'", "flux_status", $notifica['esito']);
				if (!empty($notifica['motivo'])) {
					if (is_array($notifica['motivo'])) {
						$descri_notifiche = '';
						foreach ($notifica['motivo'] as $descri_notifica) {
							if (!empty($descri_notifiche)) $descri_notifiche.= '<br />';
							$descri_notifiche.= $descri_notifica;
						}
					} else {
						$descri_notifiche = $notifica['motivo'];
					}
					gaz_dbi_put_query($gTables['fae_flux'], "id_SDI='" . $id_SDI . "'", "flux_descri", addslashes($descri_notifiche));
				}
			}
			echo 'Completato';
		} else {
			echo '<p>' . print_r($notifiche, true) . '</p>';
		}
	}
	echo '<p align="center"><a href="./report_fae_sdi.php">Ritorna a report Fatture elettroniche</a></p>';
	exit();
}

// controllo su PEC SDI

require_once('../../library/php-imap/ImapMailbox.php');


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


set_time_limit(3600);
global $gTables;
// IMAP
$cemail = gaz_dbi_get_row($gTables['company_config'],'var','cemail');
$cpassword = gaz_dbi_get_row($gTables['company_config'],'var','cpassword');
$cfiltro = gaz_dbi_get_row($gTables['company_config'],'var','cfiltro');
$cpopimap = gaz_dbi_get_row($gTables['company_config'],'var','cpopimap');
$last_fae_email = gaz_dbi_get_row($gTables['company_config'],'var','last_fae_email');
$pathricevute = '../../data/files/'.$admin_aziend['codice'].'/ricevutesdi' ;
if (! is_dir($pathricevute)) {
  if (mkdir($pathricevute,0777)) {
   echo ' Creata cartella ' . $pathricevute . ' <br/>';
  }
}


define('CATTACHMENTS_DIR',  '../../data/files/'.$admin_aziend['codice'].'/ricevutesdi');

try {
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

echo "I file Ricevute vengono salvati in: " .  CATTACHMENTS_DIR . "<br/>";

$identif_iva_az_lavoro = "IT".$admin_aziend['codfis'] ;
$cmailSDI = gaz_dbi_get_row($gTables['company_config'],'var','dest_fae_zip_package');
foreach($mailsIds as $mailId) {
  $mail = $mailbox->getMail($mailId);
  $data_mail =  $mail->date;
  $mittente = substr($mail->fromName,-strlen($cmailSDI['val']),strlen($cmailSDI['val']));
  $aaa= $mail->getAttachments();
  $ccc = array_values($aaa);
  $bbb = $ccc[0];
  $nome_file_ret = $bbb->name;
  $nome_info=explode( '_', $nome_file_ret );
  if ($mittente != $cmailSDI['val'] )
  {
    // non proviene da PEC SDI: non la considero elimino gli allegati, segno la mail come non letta  e riprendo il ciclo
    foreach ($ccc as $allegato)  {
      $bbb = $allegato ;
      $nome_file_ret = $bbb->name;
      unlink(CATTACHMENTS_DIR.'/'.$nome_file_ret) ;
    }
    $mailbox->markMailAsUnread($mailId) ;
    continue ;
  }
  if ($nome_info[0] != $identif_iva_az_lavoro) {
    // trattasi di fattura di nostro fornitore: avrà 2 allegati uno la fattura e due
    echo "Arrivata fattura Acquisto di: " . $nome_info[0] . "<br/>";
		foreach ($ccc as $allegato)  {
      $bbb = $allegato ;
      $nome_file_ret = $bbb->name;
      unlink(CATTACHMENTS_DIR.'/'.$nome_file_ret) ;
    }
    $mailbox->markMailAsUnread($mailId) ;
    continue ;

     }

  echo "Arrivata Ricevuta: " . $nome_info[2] ." per " . $nome_info[1] . "<br/>";
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
        $flag="ric" ;
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
       $flag="ric" ;

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
      $flag="ric" ;

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
      $flag="ric" ;

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
       $flag="ric" ;

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
     $data_exec = $data_mail ;
   } else {
      $id_flux = $verifica['id'] ;
      $data_exec = $verifica['exec_date'] ;
      $id_tes = $verifica['id_tes_ref'];
   }

   //non dovrebbero esserci ma verifica eventuali doppioni causa errori sulla casella di posta elettronica
   $verifica = gaz_dbi_get_row($gTables['fae_flux'], 'mail_id', $message_id);
   if ($verifica == false || $flag == "acq") {
     $valori=array('filename_ori'=>$nome_file,
         'id_tes_ref'=>$id_tes,
				 'exec_date'=>$data_exec,
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
    if ($id_tes == 0 && $flag == "ric") {
      echo " Attenzione ricevuta senza invio, inserisco in fae_flux ". $idsidi . " " . $nome_file . " " . $status . " ". $progressivo_status."<br/>";
      fae_fluxInsert($valori);
    } elseif ($id_tes > 0 && $flag == "ric") {
      // voglio che le ricevute aggiornino lo stesso record dell'invio fattura così da chiudere il ciclo
      echo "Aggiorno fae_flux ". $idsidi . " " . $nome_file . " " . $status . " ". $progressivo_status."<br/>";
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "received_date", $data_ora_ricezione);
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "delivery_date", $data_ora_consegna);
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "id_SDI", $idsidi);
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "filename_ret", $nome_file_ret);
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "mail_id", $message_id);
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "flux_status", $status);
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "progr_ret", $progressivo_status);
      gaz_dbi_put_query($gTables['fae_flux'], "id = '" . $id_flux."'", "flux_descri", $errore);
    }

    echo  $idsidi . " " . $nome_file . " " . $status . " ". $progressivo_status."<br/>";
    } else {
    echo " presente ". $idsidi . " " . $nome_file . " " . $status . " ". $progressivo_status."<br/>";
    }

}

    gaz_dbi_put_row($gTables['company_config'],'var','last_fae_email','val',$n_email);

    echo "Completato";
    echo "<p align=\"center\"><a href=\"./report_fae_sdi.php\">Ritorna a report Fatture elettroniche</a></p>";
} catch ( \Exception $e ) {
?>
	<div style="color:red">
		<center>
			Error: <b><?= $e->getMessage(); ?></b>
		<br>
			Codice: <?= $e->getCode(); ?>
		</center>
	</div>
<?php
}
?>
