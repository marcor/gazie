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

namespace GAzie;

/**
 * Class for send mail with phpmailer
 *
 */
class Mailer {

	private $mailer;

	private $host_smtp;

	private $notification;

	private $host_port;

	private $smtp_secure;

	private $smtp_user;

	private $smtp_password;

	private $reply_to;

	private $send_errors;

	public function __construct() {
		$config = new \GAzie\Database\Company\Config();
		$this->notification 	= (bool)($config->get('return_notification')->val);
		$this->mailer 		= $config->get('mailer')->val;
		$this->host_smtp 	= $config->get('smtp_server')->val;
		$this->host_port 	= intval($config->get('smtp_port')->val);
		$this->smtp_secure 	= $config->get('smtp_secure')->val;
		$this->smtp_user 	= $config->get('smtp_user')->val;
		$this->smtp_password 	= $config->get('smtp_password')->val;
		$c = GAzie::factory()->getConfig()->getAzienda();
		$this->reply_to 	= $c['e_mail'];
	}


	public function send() {
	     $mail = new PHPMailer();
   	     $mail->Host = $this->host_smtp;
             $mail->IsHTML();                                // Modalita' HTML
  	     $mail->CharSet = 'UTF-8';
   	      // Imposto il server SMTP
    	     if (  $this->host_port != 0 ) {
            	$mail->Port = $this->host_port;             // Imposto la porta del servizio SMTP
             }
             switch ($this->mailer ) {
                case "smtp":
                	// Invio tramite protocollo SMTP
            		$mail->SMTPDebug = false;                           // Attivo il debug
                	$mail->IsSMTP();                                // Modalita' SMTP
                	if ( $this->smtp_secure != "" ) {
                    		$mail->SMTPSecure = $this->smtp_secure; // Invio tramite protocollo criptato
                	} else {
				$mail->SMTPOptions = array(
					'ssl' => array('verify_peer' => false,
                            		'verify_peer_name' => false,
					'allow_self_signed' => true)
				);
                	}
                	$mail->SMTPAuth =  $this->smtp_user != ""   ? TRUE : FALSE ;
                	if ($mail->SMTPAuth) {
                    		$mail->Username = $this->smtp_user;     // Imposto username per autenticazione SMTP
                    		$mail->Password = $this->smtp_password;     // Imposto password per autenticazione SMTP
                	}
                	break;
              	case "mail":
     
		default:
                	break;
         
	     }
/* DA completare
             $mittente = $config_replyTo['val'];
        } elseif (strlen($user['user_email'])>=10)  { // utilizzo quella dell'utente
            $mittente = $user['user_email'];
        } else { // utilizzo quella dell'azienda, la stessa che appare sui documenti
            $mittente = $admin_data['e_mail'];
        }
        // Imposto eventuale richiesta di notifica
        if ($config_notif['val'] == 'yes') {
            $mail->AddCustomHeader($mail->HeaderLine("Disposition-notification-to", $mittente));
        }
        $mail->setLanguage(strtolower($admin_data['country']));
        // Imposto email del mittente
        $mail->SetFrom($mittente, $admin_data['ragso1'] . " " . $admin_data['ragso2']);
        // Imposto email del destinatario
        $mail->Hostname = $config_host;
        $mail->AddAddress($mailto);
        // Se ho una mail utente lo utilizzo come mittente tra i destinatari in cc
                if (strlen($user['user_email'])>=10) { // quando l'utente che ha inviato la mail ha un suo indirizzo il reply avviene su di lui
            $mittente = $user['user_email'];
        }
        $mail->AddCC($mittente, $admin_data['ragso1'] . " " . $admin_data['ragso2']);
        // Imposto l'oggetto dell'email
        $mail->Subject = $subject;
        // Imposto il testo HTML dell'email
        $mail->MsgHTML($body_text);
        // Aggiungo la fattura in allegato
                if ($content->urlfile){ // se devo trasmettere un file allegato passo il suo url
                        $mail->AddAttachment( $content->urlfile, $content->name );
                } else { // altrimenti metto il contenuto del pdf che presumibilmente mi arriva da document.php
                        $mail->AddStringAttachment($content->string, $content->name, $content->encoding, $content->mimeType);
                }
 */
	}	
}

?>
