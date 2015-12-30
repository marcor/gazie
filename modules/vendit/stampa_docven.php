<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
if (!ini_get('safe_mode')) { //se me lo posso permettere...
   ini_set('memory_limit', '128M');
   gaz_set_time_limit(0);
}

require("../../library/include/document.php");
// recupero i dati
if (isset($_GET['id_tes'])) {   //se viene richiesta la stampa di un solo documento attraverso il suo id_tes
   $id_testata = intval($_GET['id_tes']);
   $testata = gaz_dbi_get_row($gTables['tesdoc'], 'id_tes', $id_testata);
   if (!empty($_GET['template'])) {
      $template = substr($_GET['template'], 0, 25);
   } elseif (!empty($testata['template'])) {
      $template = $testata['template'];
   } else {
      $template = 'FatturaImmediata';
   }
   if (isset($_GET['dest']) && $_GET['dest'] == 'E') { // se l'utente vuole inviare una mail
      createDocument($testata, $template, $gTables, 'rigdoc', 'E');
   } else {
      createDocument($testata, $template, $gTables);
   }
} elseif (isset($_GET['td']) and $_GET['td'] == 2) {  //se viene richiesta la stampa di fattura/e differita/e appartenenti ad un periodo
   if (!isset($_GET['pi'])) {
      header("Location: report_docven.php");
      exit;
   }
   if (!isset($_GET['pf'])) {
      $_GET['pf'] = intval($_GET['pi']);
   }
   if (!isset($_GET['ni'])) {
      $_GET['ni'] = 1;
   }
   if (!isset($_GET['nf'])) {
      $_GET['nf'] = 999999999;
   }
   if (!isset($_GET['di'])) {
      $_GET['di'] = 20050101;
   }
   if (!isset($_GET['df'])) {
      $_GET['df'] = 20991231;
   }
   if (!isset($_GET['cl']) or ( empty($_GET['cl']))) {
      $cliente = '';
   } else {
      $cliente = ' AND clfoco = ' . intval($_GET['cl']);
   }
   //recupero i documenti da stampare
   $testate = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "tipdoc = 'FAD' AND seziva = " . intval($_GET['si']) . " AND
                                                 datfat BETWEEN '" . substr($_GET['di'], 0, 10) . "' AND '" . substr($_GET['df'], 0, 10) . "' AND
                                                 numfat BETWEEN " . intval($_GET['ni']) . " AND " . intval($_GET['nf']) . " AND
                                                 protoc BETWEEN " . intval($_GET['pi']) . " AND " . intval($_GET['pf']) .
           $cliente, "datfat ASC, protoc ASC, id_tes ASC");
   if (isset($_GET['dest']) && $_GET['dest'] == 'E') { // se l'utente vuole inviare una mail
      createInvoiceFromDDT($testate, $gTables, 'E');
   } else {
      createInvoiceFromDDT($testate, $gTables);
   }
} else { // in tutti gli altri casi
   if (!isset($_GET['pi']) or ! isset($_GET['td'])) {
      header("Location: report_docven.php");
      exit;
   }
   if (!isset($_GET['pf'])) {
      $_GET['pf'] = intval($_GET['pi']);
   }
   $date_name = 'datfat';
   $num_name = 'numfat';
   $template = 'FatturaSemplice';
   $orderby = 'datfat ASC, protoc ASC, id_tes ASC';
   switch ($_GET['td']) {
      case 1:  //ddt
         $date_name = 'datemi';
         $num_name = 'numdoc';
         $_GET['pi'] = 0;
         $_GET['pf'] = 999999999;
//                $where = "(tipdoc = 'DDT' OR tipdoc = 'FAD') ";
         $where = "(tipdoc like 'DD%' OR tipdoc = 'FAD') ";
         $template = 'DDT';
         $orderby = 'datemi ASC, numdoc ASC, id_tes ASC';
         break;
      case 2:  //fattura differita
         $where = "tipdoc = 'FAD'";
         break;
      case 3:  //fattura immediata accompagnatoria
         $where = "tipdoc = 'FAI' AND template = 'FatturaImmediata'";
         $template = 'FatturaImmediata';
         break;
      case 4: //fattura immediata semplice
         $where = "tipdoc = 'FAI' AND template <> 'FatturaImmediata'";
         break;
      case 5: //nota di credito
         $where = "tipdoc = 'FNC'";
         break;
      case 6: //nota di debito
         $where = "tipdoc = 'FND'";
         break;
      case 7: //nota di debito
         $where = "tipdoc = 'VRI'";
         $template = 'Received';
         break;
   }
   if (!isset($_GET['ni'])) {
      $_GET['ni'] = 1;
   }
   if (!isset($_GET['nf'])) {
      $_GET['nf'] = 999999999;
   }
   if (!isset($_GET['di'])) {
      $_GET['di'] = 20050101;
   }
   if (!isset($_GET['df'])) {
      $_GET['df'] = 20991231;
   }
   if (!isset($_GET['cl']) or ( empty($_GET['cl']))) {
      $cliente = '';
   } else {
      $cliente = ' AND clfoco = ' . intval($_GET['cl']);
   }

   if (!isset($_GET['ag']) or ( empty($_GET['ag']))) {   // selezione agente
      $agente = '';
   } else {
      $agente = ' AND B.id_agente = ' . intval($_GET['ag']);
   }
   $invioPerEmail = 0;
   if (!isset($_GET['ts']) or ( empty($_GET['ts']))) {
      $fattEmail = '';
   } else {
      $invioPerEmail = ($_GET['ts'] == 1 ? 0 : 1);
      $fattEmail = " AND B.fatt_email = $invioPerEmail";
   }
   //recupero i documenti da stampare
   $where = $where
           . " AND seziva = "
           . intval($_GET['si'])
           . " AND $date_name BETWEEN '" . substr($_GET['di'], 0, 10) . "' AND '" . substr($_GET['df'], 0, 10)
           . "' AND $num_name BETWEEN " . intval($_GET['ni']) . " AND " . intval($_GET['nf'])
           . " AND protoc BETWEEN " . intval($_GET['pi']) . " AND " . intval($_GET['pf'])
           . $cliente . $agente . $fattEmail;
   $from = $gTables['tesdoc'] . " A left join " . $gTables['clfoco'] . " B on A.clfoco=B.codice ";
   //recupero i documenti da stampare
   $testate = gaz_dbi_dyn_query("A.*", $from, $where, $orderby);
   if ($invioPerEmail) {
      foreach ($testate as $doc) {
         $testata = gaz_dbi_get_row($gTables['tesdoc'], 'id_tes', $doc['id_tes']);
         createDocument($testata, $template, $gTables, 'rigdoc', 'E');
      }
   } else {
      createMultiDocument($testate, $template, $gTables);
   }
//   createMultiDocument($testate, $template, $gTables, ($invioPerEmail ? "E" : false)); non funziona, invia tutte le fatture allo stesso destinatario
}
?>