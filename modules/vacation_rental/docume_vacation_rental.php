<?php
/*
 --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-20223 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)
  Ogni diritto è riservato.
  E' possibile usare questo modulo solo dietro autorizzazione dell'autore
  --------------------------------------------------------------------------
  --------------------------------------------------------------------------

Copyright (C) - Antonio Germani Massignano (AP) - telefono +39 340 50 11 912
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
			<img src="../vacation_rental/vacation_rental.PNG" alt="Vacation rental logo" style="max-width:20%;">
		</div>
		<h4> Gestione di prenotazione alloggi con sincronizzazione diretta e generazione di Ical</h4>
		<h5>
			<p>Il modulo "VACATION RENTAL" per GAZIE (Gestione AZIEnda) è di proprietà di Antonio Germani, ogni diritto è riservato.</p>
			<p>Il suo utilizzo è possibile solo dietro autorizzazione dell'autore.</p>
			<p>Copyright (C) - Antonio Germani Massignano (AP) https://www.programmisitiweb.lacasettabio.it - telefono +39 340 50 11 912</p>
		</h5>
    <h6><b>
    -> Versione BETA <-
    </b></h6>
    <div class="panel panel-info text-left" style="max-width:80%; margin-left:10%; padding:10px;">

        <p><b>Versione free: attivabile gratuitamente come modulo di GAzie</b></p>
        <ul>
          <li>Gestione degli alloggi e delle strutture</li>
          <li>Gestione delle prenotazioni</li>
          <li>Gestione degli extra, della tassa di soggiorno turistica e della caparra confirmatoria</li>
          <li>Calendario delle disponibilità</li>
        </ul>

    </div>
    <br>
    <div class="panel panel-info text-left" style="max-width:80%; margin-left:10%; padding:10px;">
      <p><b>Versione PRO, a pagamento</b></p>
      <ul>
        <li>Gestione degli alloggi e delle strutture</li>
        <li>Gestione delle prenotazioni</li>
        <li>Gestione degli extra, della tassa di soggiorno turistica e della caparra confirmatoria</li>
        <li>Calendario delle disponibilità</li>
        <li>Gestione e Calendario dei prezzi giornalieri di ogni singolo alloggio</li>
        <li>Creazione di un contratto di locazione</li>
        <li>Front-end per il cliente tramite iframe su qualsiasi sito internet</li>
        <li>Uso nel Front-end degli sconti automatici per alloggio, struttura, numero di notti, periodo limitato e codice sconto</li>
        <li>Modulo Front-end di ricerca disponibilità alloggio</li>
        <li>Modulo Front-end per visualizzare il calendario delle disponibilità totali</li>
        <li>Modulo Front-end di prenotazione e pagamento online (bonifici bancari e carte di credito off-line)</li>
        <li>Possibilita di richiedere il pagamento della prenotazione a favore di soggetti diversi dall'amministratore di GAzie</li>
        <li>Sincronizzazione dei calendari delle disponibilità con altri portali di prenotazioni (Airbnb, Booking, Tripadvisor, etc) tramite ICal in entrata (necessita di cron-job) e in uscita</li>
      </ul>
      <p>Per la versione PRO contattare lo sviluppatore: Antonio Germani Massignano (AP) https://www.programmisitiweb.lacasettabio.it - telefono +39 340 50 11 912</p>
    </div>
	</div>
</div>

