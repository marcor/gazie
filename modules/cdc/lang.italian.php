<?php

/*
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

$strScript = array(
    "select_schedule.php" =>
    array('title' => 'Selezione delle fatture da mandare in compensazione',
        'mesg' => array('La ricerca non ha dato risultati!',
            'Inserire almeno 2 caratteri!',
            'Cambia cliente/fornitore'
        ),
        'errors' => array('La data  non &egrave; corretta!',
            'Non sono stati trovati movimenti!'
        ),
        'confirm_entry' => 'Invia a Camera di Compensazione',
        'tutti' => 'Seleziona tutte ',
        'precisazione' => 'Puoi anche usare direttamente <a href="https://fe.cameracompensazione.it" target="_blank">Camera di Compensazione</a>',
        'accettazione' => ' <b>Per continuare</b> devi accettare <a href="https://webapp.cameracompensazione.it/termini-condizioni.html" target="_blank">Termini e condizioni</a> e <a href="https://webapp.cameracompensazione.it/privacy-policy.html" target="_blank">Privacy Policy</a> ',
        'account' => 'Cliente ',
        'orderby' => 'Ordina per: ',
        'orderby_value' => array(0 => 'Scadenza crescente', 1 => 'Scadenza decrescente',
            2 => 'Controparte crescente', 3 => 'Controparte decrescente'
        ),
        /** ENRICO FEDELE */
        /* Aggiunto header per ultima colonna della tabella, per abbellire il layout */
        'header' => array('Controparte' => '', 'Seleziona'=> '', 'ID Partita' => '', 'Status' => '', 'Mov.Cont.' => '', 'Descrizione' => '',
            'N.Doc.' => '', 'Data Doc.' => '', 'Data Reg.' => '', 'Dare' => '', 'Avere' => '',
            'Scadenza' => ''
        ),
        /** ENRICO FEDELE */
        'status_value' => array(0 => 'APERTA', 1 => 'CHIUSA', 2 => 'ESPOSTA', 3 => 'SCADUTA', 9 => 'ANTICIPO'),
        'total_open' => 'Totale partite aperte'
    ),
    "admin_cdc.php" =>
    array('title' => 'Parametri Camera di Compensazione',
        'ins_this' => 'Inserisci una nuova causale contabile ',
        'upd_this' => 'Modifica Parametri Camera di Compensazione',
        'mesg' => array('La ricerca non ha dato risultati!',
            'Inserire almeno 2 caratteri!',
            'Cambia cliente/fornitore'
        ),
        'err' => array('codice_emp' => 'Inserire un codice valido!',
            'descri' => '&Egrave; necessario inserire una descrizione!',
            'codice_exi' => 'Codice causale esistente!',
            'clfoco_ref' => "C'&egrave; almeno un rigo con conto non definito!",
            'CHI' => 'Codice riservato alla CHIUSURA AUTOMATICA CONTI!',
            'APE' => 'Codice riservato alla APERTURA AUTOMATICA CONTI!',
            'AMM' => 'Codice riservato al RILEVAMENTO AMMORTAMENTI DI FINE ANNO!'
        ),
        'head' => 'Conti movimentati ',
        'cdc_id' => 'Codice partner *',
        'cdc_token' => 'Token di autenticazione *',
        'insdoc' => 'Inserimento dati documento di riferimento',
        'insdoc_value' => array(0 => 'No', 1 => 'Si'),
        'regiva' => 'Registro I.V.A.',
        'regiva_value' => array(0 => 'Nessuno', 2 => 'Fatture di Vendita', 4 => 'Corrispettivi', 6 => 'Fatture d\'Acquisto', 9 => 'Versamenti IVA'),
        'operat' => 'Operatore',
        'operat_value' => array(0 => 'Non opera', 1 => 'Somma', 2 => 'Sottrae'),
        'pay_schedule' => 'Partite aperte (scadenzario)',
        'pay_schedule_value' => array(0 => 'Non opera', 1 => 'Documento vendita/acquisto (apre)', 2 => 'Pagamento (chiude)'),
        'clfoco_mastro' => 'Mastro',
        'clfoco_sub' => 'Sottoconto',
        'tipim' => 'Tipo importo',
        'tipim_value' => array('' => '', 'A' => 'Totale', 'B' => 'Imponibile', 'C' => 'Imposta'),
        'daav' => 'DARE/AVERE',
        'daav_value' => array('D' => 'DARE', 'A' => 'AVERE'),
        'report' => 'Lista delle causali contabili',
        'del_this' => 'Causale contabile ',
        'add_row' => 'Salva i parametri'
    ),
);
?>