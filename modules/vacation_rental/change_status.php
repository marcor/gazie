<?php
/*
 --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-2023 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)

  --------------------------------------------------------------------------
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}
use Ddeboer\Imap\Server;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require("../../library/include/datlib.inc.php");
require("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();

if (isset($_POST['type'])&&isset($_POST['ref'])) {
	switch ($_POST['type']) {
		case "set_new_stato_lavorazione":
			$i=intval($_POST['ref']); // id_tesbro
      // ricarico il json custom field tesbro e controllo
      $tesbro=gaz_dbi_get_row($gTables['tesbro'], "id_tes", $i); // carico la tesbro
      $clfoco=gaz_dbi_get_row($gTables['clfoco'], "codice", $tesbro['clfoco']);
      $anagra=gaz_dbi_get_row($gTables['anagra'], "id", $clfoco['id_anagra']); // carico la anagra
      $language=gaz_dbi_get_row($gTables['languages'], "lang_id", $anagra['id_language']); // carico la lingua
      $langarr = explode(" ",$language['title_native']);
      $lang = strtolower($langarr[0]);
      include "lang.".$lang.".php";
      $script_transl=$strScript['booking_form.php'];

      if ($data = json_decode($tesbro['custom_field'],true)){// se c'è un json

        if (is_array($data['vacation_rental'])){ // se c'è il modulo "vacation rental" lo aggiorno
          if (substr($_POST['new_status'],0,9)=="CANCELLED"){// se la prenotazione va cancellata azzero anche i reminder
            $data['vacation_rental']['rem_pag']="";
            $data['vacation_rental']['rem_checkin']="";
          }
          $data['vacation_rental']['status']=substr($_POST['new_status'],0,10);
          $custom_json = json_encode($data);
        } else { //se non c'è il modulo "vacation_rental" lo aggiungo
          $data['vacation_rental']= array('status' => substr($_POST['new_status'],0,10));
          $custom_json = json_encode($data);
        }
      }else { //se non c'è un json creo "vacation_rental"
          $data['vacation_rental']= array('status' => substr($_POST['new_status'],0,10));
          $custom_json = json_encode($data);
      }
      gaz_dbi_put_row($gTables['tesbro'], 'id_tes', $i, 'custom_field', $custom_json);
      if ($_POST['email']=='true' && strlen($_POST['cust_mail'])>4){// se richiesto invio mail
        // imposto PHP Mailer per invio email di cambio stato
        $host = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_server')['val'];
        $usr = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_user')['val'];
        $psw = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_password')['val'];
        $port = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_port')['val'];
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        //Server settings
        $mail->SMTPDebug  = 0;                           //Enable verbose debug output default: SMTP::DEBUG_SERVER;
        $mail->isSMTP();                                 //Send using SMTP
        $mail->Host       = $host;                       //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                        //Enable SMTP authentication
        $mail->Username   = $usr;                        //SMTP username
        $mail->Password   = $psw;                        //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
        $mail->Port       = $port;                       //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        // creo e invio email di conferma
        //Recipients
        $mail->setFrom($admin_aziend['e_mail']); // sender (e-mail dell'account che sta inviando)
        $mail->addReplyTo($admin_aziend['e_mail']); // reply to sender (e-mail dell'account che sta inviando)
        $mail->addAddress($_POST['cust_mail']);                  // email destinatario
        $mail->addAddress($admin_aziend['e_mail']);             //invio copia a mittente
        $mail->isHTML(true);
        $mail->Subject = $script_transl['booking']." ".$tesbro['numdoc'].' '.$script_transl['of'].' '.gaz_format_date($tesbro['datemi']);
        $mail->Body    = "<p>".$script_transl['change_status'].": ".$script_transl[$_POST['new_status']]."</p><p><b>".$admin_aziend['ragso1']." ".$admin_aziend['ragso2']."</b></p>";
        if($mail->send()) {
        }else {
          echo "Errore imprevisto nello spedire la mail di modifica status: " . $mail->ErrorInfo;
        }
      }
		break;
    case "set_new_status_check":
			$i=intval($_POST['ref']); // id_tesbro
      $datetime  = date ('Y-m-d H:i:s', strtotime($_POST['datetime']));
      // ricarico il json custom field tesbro e controllo
      $tesbro=gaz_dbi_get_row($gTables['tesbro'], "id_tes", $i); // carico la tesbro
      $clfoco=gaz_dbi_get_row($gTables['clfoco'], "codice", $tesbro['clfoco']);
      $anagra=gaz_dbi_get_row($gTables['anagra'], "id", $clfoco['id_anagra']); // carico la anagra
      $language=gaz_dbi_get_row($gTables['languages'], "lang_id", $anagra['id_language']); // carico la lingua specifica del cliente
      $langarr = explode(" ",$language['title_native']);
      $lang = strtolower($langarr[0]);
      include "lang.".$lang.".php";
      $script_transl=$strScript['booking_form.php'];
      $res=gaz_dbi_get_row($gTables['company_config'], "var", 'vacation_url_user');
      $vacation_url_user=$res['val'];// carico l'url per la pagina front-end utente

      if ($_POST['new_status']=="OUT"){
        $updt= "checked_out_date = '". $datetime."'";
      }elseif($_POST['new_status']=="IN"){
        $updt= "checked_in_date = '". $datetime."', checked_out_date = NULL";
      }else{
        $updt= "checked_in_date = NULL, checked_out_date = NULL";
      }

      gaz_dbi_query ("UPDATE " . $gTables['rental_events'] . " SET ".$updt." WHERE id_tesbro =".$i." AND type= 'ALLOGGIO'") ;

      if ($_POST['email']=='true' && strlen($_POST['cust_mail'])>4 && strlen($vacation_url_user)>4){// se richiesto invio mail di richiesta recensione

        $event=gaz_dbi_get_row($gTables['rental_events'], "id_tesbro", $i, " AND type = 'ALLOGGIO'"); // carico l'evento prenotazione

        // imposto PHP Mailer per invio email di cambio stato
        $host = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_server')['val'];
        $usr = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_user')['val'];
        $psw = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_password')['val'];
        $port = gaz_dbi_get_row($gTables['company_config'], 'var', 'smtp_port')['val'];
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        //Server settings
        $mail->SMTPDebug  = 0;                           //Enable verbose debug output default: SMTP::DEBUG_SERVER;
        $mail->isSMTP();                                 //Send using SMTP
        $mail->Host       = $host;                       //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                        //Enable SMTP authentication
        $mail->Username   = $usr;                        //SMTP username
        $mail->Password   = $psw;                        //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
        $mail->Port       = $port;                       //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        // creo e invio email di conferma
        //Recipients
        $mail->setFrom($admin_aziend['e_mail']); // sender (e-mail dell'account che sta inviando)
        $mail->addReplyTo($admin_aziend['e_mail']); // reply to sender (e-mail dell'account che sta inviando)
        $mail->addAddress($_POST['cust_mail']);                  // email destinatario
        $mail->addAddress($admin_aziend['e_mail']);             //invio copia a mittente
        $mail->isHTML(true);
        $mail->Subject = $script_transl['booking']." ".$tesbro['numdoc'].' '.$script_transl['of'].' '.gaz_format_date($tesbro['datemi']);
        $mail->Body    = "<p>".$script_transl['ask_feedback']."</p><p><a href=".$vacation_url_user.">".$vacation_url_user."</a></p>".$script_transl['use_access']."<br>Password: <b>".$event['access_code']."</b><br>ID: <b>".$event['id_tesbro']."</b><br>".$script_transl['booking_number'].": <b>".$tesbro['numdoc']."</b><p>".$script_transl['ask_feedback2']."</p><p><b>".$admin_aziend['ragso1']." ".$admin_aziend['ragso2']."</b></p>";
        if($mail->send()) {
        }else {
          echo "Errore imprevisto nello spedire la mail di modifica status: " . $mail->ErrorInfo;
        }
      }
		break;
	}
}
?>
