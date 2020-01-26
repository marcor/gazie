<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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

require_once "../../library/phpmailer/class.phpmailer.php";
require_once "../../library/phpmailer/class.smtp.php";


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
	
	private $sender_email;

	private $send_errors;
	
	private $country;

	private $sender_ragso;

	private $dest_email;

	private $subject;

	private $body_text;

	private $attachment;

	public function __construct() {
		$config = new \GAzie\Database\Company\Config();
		$c = GAzie::factory()->getConfig()->getAzienda();
		$this->country = $c['country'];
		$this->sender_ragso = $c['ragso1']." ".$c['ragso2'];
		$this->notification 	= (bool)($config->get('return_notification')->val);

		$this->mailer 		= $config->get('mailer')->val;
		$this->host_smtp 	= $config->get('smtp_server')->val;
		$this->host_port 	= intval($config->get('smtp_port')->val);
		$this->smtp_secure 	= $config->get('smtp_secure')->val;
		$this->smtp_user 	= $config->get('smtp_user')->val;
		$this->smtp_password 	= $config->get('smtp_password')->val;
		$reply_to = $config->get('reply_to');
		if ( $reply_to ) {
			$this->reply_to = $reply_to->val;
			$this->sender_email = $reply_to->val;
		} else {
			$this->reply_to 	= $c['e_mail'];
			$this->sender_email 	= $c['e_mail'];
		}
		$this->attachment = [];
	}

	
	public function send() {
	     $mail = new \PHPMailer();
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
	     
	     // Imposto eventuale richiesta di notifica
             if ( $this->notification  ) {
		     $mail->AddCustomHeader($mail->HeaderLine("Disposition-notification-to", $this->sender_email ));
	     }
             $mail->setLanguage(strtolower($this->country ));
	     // Imposto email del mittente
	     $mail->SetFrom($this->sender_email , $this->sender_ragso);
	     // Imposto email del destinatario
	     $mail->Hostname = $this->host_smtp;
	     $mail->AddAddress( $this->dest_email );
	    /* 
	     // Se ho una mail utente lo utilizzo come mittente tra i destinatari in cc
	     if (strlen($user['user_email'])>=10) { // quando l'utente che ha inviato la mail ha un suo indirizzo il reply avviene su di lui
		   $mittente = $user['user_email'];
	     }
	     
	     $mail->AddCC($mittente, $admin_data['ragso1'] . " " . $admin_data['ragso2']);
	     */
	     // Imposto l'oggetto dell'email
	     $mail->Subject = $this->subject;
	     
	     // Imposto il testo HTML dell'email
	     $mail->MsgHTML($this->body_text);

	     // Aggiungo la fatture in allegato
	     foreach ( $this->attachment as $attach ) { 
	    	if ($attach->urlfile){ // se devo trasmettere un file allegato passo il suo url
			$mail->AddAttachment( $attach->urlfile, $attach->name );
	     	} else { // altrimenti metto il contenuto del pdf che presumibilmente mi arriva da document.php
		     	$mail->AddStringAttachment($attach->string, $attach->name, $attach->encoding, $attach->mimeType);
	     	}
	     }
	     if ( $mail->Send() )
		     return true;
	     else {
		     $this->send_errors = $mail->ErrorInfo;
		     return false;
	     }
	}

	public function subject(string $subject) {
	     $this->subject = $subject;
	}	

	public function body(string $body ) {
	     $this->body_text = $body;
	}	

	public function attachment( stdClass $content ) {
		$this->attachment[] = $content;
	}

	public function destination( string $email ) {
		$this->dest_email = $email; 
	}

	public function getError() {
		return $this->send_errors;
	}

	public function testing() {
		$this->subject( "Testing email");
		$this->body("Ho spedito mail da ".$this->sender_email." a alla stessa email GAzie");
		$this->destination( $this->sender_email );
		return $this->send();
	}

	public function getSender() {
		return $this->sender_email;
	}
}

?>
