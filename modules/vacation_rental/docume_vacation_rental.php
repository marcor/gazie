<?php
/*
 --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-2023 - Antonio Germani, Massignano (AP) - telefono +39 340 50 11 912
  (http://www.programmisitiweb.lacasettabio.it)

  --------------------------------------------------------------------------
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend=checkAdmin(9);
?>
<div class="help">
	<div class="panel panel-info text-center">
		<h1>
		VACATION RENTAL - affitto alloggi per vacanze
		</h1>
		<h2>Case Vacanze, Agriturismi, Bed & Breakfast, Alberghi</h2>
		<div>
			<img src="../vacation_rental/vacation_rental.png" alt="Vacation rental logo" style="max-width:20%;">
		</div>
		<h4> Gestione di prenotazione alloggi con sincronizzazione diretta e generazione di Ical</h4>
		<h5>
			<p>Il modulo "VACATION RENTAL" per GAZIE (Gestione AZIEnda) è di proprietà di Antonio Germani, ogni diritto è riservato.</p>
			<p>Il suo utilizzo è possibile solo dietro autorizzazione dell'autore.</p>
			<p>Copyright (C) - Antonio Germani Massignano (AP) https://www.programmisitiweb.lacasettabio.it - telefono +39 340 50 11 912</p>
		</h5>
    <h5><b>
    -------->    Versione 2.3    <---------
    </b></h5>
    <div class="panel panel-info text-left" style="max-width:80%; margin-left:10%; padding:10px;">

        <p><b>Versione free: attivabile gratuitamente come modulo di GAzie</b></p>
        <p>Utilizzabile solo in GAzie</p>
        <ul>
          <li>Gestione degli alloggi e delle strutture</li>
          <li>Gestione delle prenotazioni</li>
          <li>Gestione degli extra (anche con quantità limitate), della tassa di soggiorno turistica, della caparra confirmatoria e del deposito cauzionale</li>
          <li>Calendario delle disponibilità degli alloggi e degli extra se con quantità limitate</li>
          <li>Creazione e stampa o invio via e-mail del PDF della prenotazione</li>
          <li>Creazione e stampa o invio via e-mail del PDF del contratto di locazione</li>
          <li>Gestione di alloggi con proprietario diverso dall'azienda di GAzie (contratto di locazione specifico)</li>
          <li>Gestione di un eventuale tour operator che ha venduto la prenotazione</li>
          <li>Gestione dell'accettazione (check-in/check-out) con data e ora e, al check-out, possibilità di inviare e-mail di richiesta recensione</li>
          <li>Creazione di statistiche generali, suddivise per strutture, anche ai fini della compilazione del mod ISA (indici sintetici affidabilità fiscale) e del pagamento della tassa di soggiorno turistica</li>
        </ul>

    </div>
    <br>
    <div class="panel panel-info text-left" style="max-width:80%; margin-left:10%; padding:10px;">
      <p><b>Versione PRO, a pagamento</b></p>
      <ul>
        <p>Utilizzabile in GAzie:</p>
        <li>Gestione degli alloggi e delle strutture</li>
        <li>Gestione delle prenotazioni</li>
        <li>Gestione degli extra (anche con quantità limitate), della tassa di soggiorno turistica, della caparra confirmatoria e del deposito cauzionale</li>
        <li>Calendario delle disponibilità degli alloggi e degli extra se con quantità limitate</li>
        <li>Creazione e stampa o invio via e-mail del PDF della prenotazione</li>
        <li>Creazione e stampa o invio via e-mail del PDF del contratto di locazione</li>
        <li>Gestione del Calendario dei prezzi giornalieri per ogni singolo alloggio</li>
        <li>Gestione di alloggi con proprietario diverso dall'azienda di GAzie (contratto di locazione specifico)</li>
        <li>Gestione di un eventuale tour operator che ha venduto la prenotazione</li>
        <li>Gestione dell'accettazione (check-in/check-out) con data e ora e, al check-out, possibilità di inviare e-mail di richiesta recensione</li>
        <li>Creazione di statistiche generali, suddivise per strutture, anche ai fini della compilazione del mod ISA (indici sintetici affidabilità fiscale) e del pagamento della tassa di soggiorno turistica</li>
        <li>Invio automatico di promemoria/benvenuto x giorni prima del check-in (richiede un cron job)</li>
        <li>Controllo automatico delle prenotazioni con caparra non pagata, invio di sollecito e successivo annullamento automatico (richiede un cron job)</li>


        <p><br>Utilizzabile in un qualsiasi sito web tramite apposito Iframe:</p>
        <li>Front-end per il cliente tramite iframe su qualsiasi sito internet</li>
        <li>L'Iframe, impostando giorno di check-in e check-out, fornirà al cliente la disponibilità e i prezzi e permetterà la prenotazione immediata online</li>
        <li>Calcolo sconti automatico per alloggio, struttura, numero di notti, periodo limitato e codice sconto</li>
        <li>Visualizzazione calendario delle disponibilità totali</li>
        <li>Visualizzazione delle recensioni nel front-end</li>
        <li>Pagamenti online con bonifico bancario, carta di credito off-line, PayPal e Stripe(richiede licenza d'uso)</li>
        <li>Possibilita di richiedere il pagamento della prenotazione a favore di soggetti diversi dall'azienda di GAzie (proprietario)</li>
        <li>Sincronizzazione bidirezionale delle disponibilità con altri portali di prenotazioni (Airbnb, Booking, Tripadvisor, etc) tramite ICal in entrata (necessita di cron-job) e in uscita</li>
        <li>Pagina front-end di accesso utente alla prenotazione con possibilità di effettuare pagamenti e lasciare la recensione</li>
        <li>Gestione dei feedback. Al check-out verrà inviata, al cliente, la richiesta di lasciare una recensione con voti su elementi personalizzabili e commento</li>
        <li>Avviso automatico, tramite un pop-up nel front-end, quando un cliente ha impostato una ricerca disponibilità e prezzi con il numero notti di poco inferiore a quello necessario per ottenere uno sconto.</li>

      </ul>
      <p>Per la versione PRO contattare lo sviluppatore: Antonio Germani Massignano (AP) https://www.programmisitiweb.lacasettabio.it - telefono +39 340 50 11 912</p>
      <p><h3>Sito demo per testare il lato cliente su un sito Joomla di esempio: https://tony.netsons.org/index.php/it/</h3></p>
      <p><h3>Per il sito demo gestionale (GAzie) contattare lo sviluppatore per avere senza impegno le chiavi di accesso test</h3></p>
      <p><h3>Questo è il primo sito di produzione (NON demo) che usa Vacation rental PRO: https://gmonamour.it/</h3></p>

    </div>
	</div>
</div>

