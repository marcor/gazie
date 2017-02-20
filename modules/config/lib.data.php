<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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

function aliivaInsert($newValue) {
    $table = 'aliiva';
    $columns = array('codice', 'tipiva', 'aliquo', 'fae_natura', 'descri', 'status', 'annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function aliivaUpdate($codice, $newValue) {
    $table = 'aliiva';
    $columns = array('codice', 'tipiva', 'aliquo', 'taxstamp', 'fae_natura', 'descri', 'status', 'annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function pagameInsert($newValue) {
    $table = 'pagame';
    $columns = array('codice', 'descri', 'tippag', 'incaut', 'pagaut', 'tipdec', 'giodec', 'mesesc', 'messuc', 'giosuc', 'numrat', 'tiprat', 'fae_mode', 'id_bank', 'annota');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function pagameUpdate($codice, $newValue) {
    $table = 'pagame';
    $columns = array('codice', 'descri', 'tippag', 'incaut', 'pagaut', 'tipdec', 'giodec', 'mesesc', 'messuc', 'giosuc', 'numrat', 'tiprat', 'fae_mode', 'id_bank', 'annota');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function vettoreUpdate($codice, $newValue) {
    $table = 'vettor';
    $columns = array('codice', 'ragione_sociale', 'indirizzo', 'cap', 'citta', 'provincia', 'partita_iva', 'codice_fiscale', 'n_albo', 'descri', 'telefo', 'annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function vettoreInsert($newValue) {
    $table = 'vettor';
    $columns = array('codice', 'ragione_sociale', 'indirizzo', 'cap', 'citta', 'provincia', 'partita_iva', 'codice_fiscale', 'n_albo', 'descri', 'telefo', 'annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

?>