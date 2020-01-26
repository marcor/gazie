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

function getNewAgente($id) {
   global $gTables;
   $agente = gaz_dbi_get_row($gTables['agenti'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['agenti'] . ".id_fornitore = " . $gTables['clfoco'] . ".codice
                                                  LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $gTables['agenti'] . '.id_agente', $id);
   return $agente;
}

require("../../library/include/datlib.inc.php");
require("../../config/templates/report_template.php");
$admin_aziend = checkAdmin();
require("lang." . $admin_aziend['lang'] . ".php");

if (!isset($_GET['pi'])) {
   header("Location: select_docforlist.php");
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
if (!isset($_GET['ag']) or ( empty($_GET['ag']))) {   // selezione agente
   $agente = '';
} else {
   $agente = ' AND tesdoc.id_agente = ' . intval($_GET['ag']);
}
if (!isset($_GET['cm']) or ( empty($_GET['cm']))) {   // selezione agente
   $caumag = '';
} else {
   $caumag = ' AND tesdoc.caumag = ' . intval($_GET['cm']);
}
$titolo = $_GET['ti'];
$tipdoc = $_GET['td'];
$campoOrdinamento = "numfat";
$campoData = "datfat";
$campiCosto = "tesdoc.traspo, tesdoc.speban, tesdoc.spevar, tesdoc.expense_vat, tesdoc.stamp, tesdoc.round_stamp, ";

//recupero i documenti da stampare
switch ($tipdoc) {
   case 1:  //ddt
//         $date_name = 'datemi';
//         $num_name = 'numdoc';
//         $protocollo_inizio = 0;
//         $protocollo_fine = 999999999;
      $where = "(tipdoc like 'DD%' OR ( tipdoc = 'FAD' AND ddt_type!='R' ) ) ";
      $campoOrdinamento = "numdoc";
      $campoData = "datemi";
      break;
   case 2:  //fattura differita
      $where = "tipdoc = 'FAD'";
      $campiCosto = "(select sum(tesdocTmp.traspo) from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat and tipdoc = 'FAD') as traspo , "
              . "(select tesdocTmp.speban from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat and tipdoc = 'FAD' order by id_tes desc limit 1) as speban, " //le spese sono nell'ultimo record
              . "(select tesdocTmp.spevar from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat and tipdoc = 'FAD' order by id_tes desc limit 1) as spevar, " //le spese sono nell'ultimo record
              . "(select tesdocTmp.expense_vat from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat and tipdoc = 'FAD' order by id_tes desc limit 1) as expense_vat, " //l'iva per le spese è nell'ultimo record
              . "(select tesdocTmp.stamp from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat and tipdoc = 'FAD' order by id_tes desc limit 1) as stamp, " //l'aliquota bolli è nell'ultimo record
              . "(select tesdocTmp.round_stamp from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat and tipdoc = 'FAD' order by id_tes desc limit 1) as round_stamp, "; //l'aliquota bolli è nell'ultimo record
//      $campiCosto = "(select sum(tesdocTmp.traspo) from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat) as traspo , "
//              . "(select max(tesdocTmp.speban) from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat order by id_tes desc) as speban, " //le spese sono nell'ultimo record
//              . "(select max(tesdocTmp.spevar) from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat order by id_tes desc) as spevar, " //le spese sono nell'ultimo record
//              . "(select max(tesdocTmp.expense_vat) from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat order by id_tes desc) as expense_vat, " //l'iva per le spese è nell'ultimo record
//              . "(select max(tesdocTmp.stamp) from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat order by id_tes desc) as stamp, " //l'aliquota bolli è nell'ultimo record
//              . "(select max(tesdocTmp.round_stamp) from " . $gTables['tesdoc'] . " tesdocTmp where tesdocTmp.numfat=tesdoc.numfat order by id_tes desc) as round_stamp, "; //l'aliquota bolli è nell'ultimo record
      break;
   case 3:  //fattura immediata accompagnatoria
      $where = "tipdoc = 'FAI' AND template = 'FatturaImmediata'";
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
   case 7: //ricevuta
      $where = "tipdoc = 'VRI'";
      break;
   case 8: //ricevuta
      $where = "tipdoc = 'FAP'";
      break;
   case 9: //ricevuta
      $where = "( tipdoc = 'CMR' OR ( tipdoc = 'FAD' AND ddt_type='R' ) )";
      break;
}
if ( $tipdoc==0 ) {
   $result = gaz_dbi_query( "SELECT DISTINCT gaz_anagra.ragso1, gaz_anagra.ragso2,
      SUM(".$gTables['rigdoc'].".quanti * ".$gTables['rigdoc'].".prelis * (1 - ".$gTables['rigdoc'].".sconto / 100) * (1 - ".$gTables['tesdoc'].".sconto / 100)) AS imponibile,
      SUM(".$gTables['rigdoc'].".quanti * ".$gTables['rigdoc'].".prelis * (1 - ".$gTables['rigdoc'].".sconto / 100) * (1 - ".$gTables['tesdoc'].".sconto / 100) * ".$gTables['rigdoc'].".pervat / 100) AS iva,
      ".$gTables['tesdoc'].".*
         FROM ".$gTables['rigdoc']."
            LEFT JOIN ".$gTables['tesdoc']." ON ".$gTables['rigdoc'].".id_tes = ".$gTables['tesdoc'].".id_tes
            LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['clfoco'].".codice = ".$gTables['tesdoc'].".clfoco
            LEFT JOIN gaz_anagra ON ".$gTables['clfoco'].".id_anagra = gaz_anagra.id
         WHERE 
            tipdoc not like 'AF%' and tipdoc!='DDL' and seziva = "
        . intval($_GET['si'])
        . " AND datemi BETWEEN '"
        . substr($_GET['di'], 0, 10)
        . "' AND '"
        . substr($_GET['df'], 0, 10)
        . "'"
        . " AND (numdoc BETWEEN "
        . intval($_GET['ni'])
        . " AND "
        . intval($_GET['nf'])
        . " OR numfat BETWEEN "
        . intval($_GET['ni'])
        . " AND "
        . intval($_GET['nf'])
        . ") "
        . " AND protoc BETWEEN "
        . intval($_GET['pi'])
        . " AND "
        . intval($_GET['pf'])
        . $cliente
        ." GROUP BY gaz_anagra.ragso1,
               gaz_anagra.ragso2,
               ".$gTables['tesdoc'].".protoc,
               ".$gTables['tesdoc'].".numdoc,
               ".$gTables['tesdoc'].".numfat,
               ".$gTables['tesdoc'].".datfat
               ORDER BY CAST(numfat as unsigned), CAST(numdoc as unsigned), datfat, datemi, tipdoc" );
      $luogo_data = $admin_aziend['citspe'] . ", lì " . ucwords(strftime("%d %B %Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))));
      $title = array('luogo_data' => $luogo_data,
         'title' => "Lista documenti: " . $titolo,
         'hile' => array(
         array('lun' => 20, 'nam' => 'Data'),
         array('lun' => 10, 'nam' => 'Num.'),
         array('lun' => 12, 'nam' => 'Tipo'),
         array('lun' => 82, 'nam' => 'Destinatario'),
         array('lun' => 20, 'nam' => 'Impon.'),
         array('lun' => 16, 'nam' => 'Spese'),
         array('lun' => 16, 'nam' => 'Iva'),
         array('lun' => 20, 'nam' => 'Totale')
      )
   );

   $pdf = new Report_template();
   $pdf->setVars($admin_aziend, $title);
   $pdf->SetTopMargin(40);
   $pdf->SetFooterMargin(18);
   $config = new Config;
   $pdf->AddPage('P', $config->getValue('page_format'));
   $pdf->SetFont('helvetica', '', 8);

   $tot_imponibile = 0.00;
   $tot_iva = 0.00;
   $tot_spese = 0.00;
   $tot_importo = 0.00;
   while ($row = gaz_dbi_fetch_array($result)) {
      $spese = 0;
      $alivaSpese = 0;
      // numrat per adesso non è valorizzato devo correggere la query Andrea
      if ( !isset($row['numrat']) ) $row['numrat'] = 1;
      $spese = $row['traspo'] + $row['speban'] * $row['numrat'] + $row['spevar'];
      $alivaSpese = gaz_dbi_get_row($gTables['aliiva'], "codice", $row['expense_vat']);
      
      $row['iva'] = $row['iva'] + ($spese * $alivaSpese['aliquo'] / 100);
      $bolli = calcolaBolli($row, $spese);   // bolli per le tratte
      $spese+=$bolli;
      if ($row['tipdoc'] == 'FNC') {   // nota di credito
         $row['imponibile'] = -$row['imponibile'];
         $row['iva'] = -$row['iva'];
         $spese = -$spese;
      }
      // nei dd% non sono presenti la data del documento e il numero fattura, uso numdoc e datemi
      if ( substr($row['tipdoc'],0,2)=="DD" ) {
         $pdf->Cell(20, 4, gaz_format_date($row['datemi']), 1);
         $pdf->Cell(10, 4, $row['numdoc'], 1);  
      } else {
         $pdf->Cell(20, 4, gaz_format_date($row["$campoData"]), 1);
         $pdf->Cell(10, 4, $row["$campoOrdinamento"], 1);  
      }
      // se è una fattura differita aggiungo carattere ddt_type per individuare il tipo
      if ( $row['tipdoc']=="FAD") 
         $pdf->Cell(12, 4, $row['tipdoc'].$row['ddt_type'], 1);
      else 
         $pdf->Cell(12, 4, $row['tipdoc'], 1);
     
      $tot_imponibile += $row['imponibile'];
      $tot_iva += $row['iva'];
      $tot_spese += $spese;
      $pdf->Cell(82, 4, $row['ragso1'] . " " . $row['ragso2'], 1, 0, '', false, '', 1);  
      $pdf->Cell(20, 4, gaz_format_number($row['imponibile']), 1, 0, 'R');
      $pdf->Cell(16, 4, gaz_format_number($spese), 1, 0, 'R');
      $pdf->Cell(16, 4, gaz_format_number($row['iva']), 1, 0, 'R');
      $pdf->Cell(20, 4, gaz_format_number($row['imponibile'] + $row['iva'] + $spese), 1, 1, 'R');
   }
   $pdf->SetFont('helvetica', 'B', 8);
   $pdf->Cell(124, 4, 'Totali: ', 1, 0, 'R');
   $pdf->Cell(20, 4, gaz_format_number($tot_imponibile), 1, 0, 'R', false, '', 1);
   $pdf->Cell(16, 4, gaz_format_number($tot_spese), 1, 0, 'R', false, '', 1);
   $pdf->Cell(16, 4, gaz_format_number($tot_iva), 1, 0, 'R', false, '', 1);
   $pdf->Cell(20, 4, gaz_format_number($tot_imponibile + $tot_iva + $tot_spese), 1, 0, 'R', false, '', 1);
   $pdf->Output();
   
} else {

   $where = $where . " AND seziva = "
        . intval($_GET['si'])
        . " AND $campoData BETWEEN '"
        . substr($_GET['di'], 0, 10)
        . "' AND '"
        . substr($_GET['df'], 0, 10)
        . "' AND $campoOrdinamento BETWEEN "
        . intval($_GET['ni'])
        . " AND "
        . intval($_GET['nf'])
        . " AND protoc BETWEEN "
        . intval($_GET['pi'])
        . " AND "
        . intval($_GET['pf'])
        . $cliente
        . $agente
        . $caumag;
   $what = "tesdoc.id_agente, " .
        "tesdoc.id_tes, " .
        "tesdoc.datfat, " .
        "tesdoc.datemi, " .
        "tesdoc.clfoco, " .
        "tesdoc.tipdoc, " .
        "tesdoc.protoc, " .
        "tesdoc.numdoc, " .
        "tesdoc.numfat, " .
        "tesdoc.seziva, " .
        "tesdoc.sconto AS scochi, " .
        "anagra.ragso1, " .
        "anagra.ragso2, " .
        "anagra.citspe, " .
        "anagra.prospe, " .
        "rigdoc.id_tes, " .
        "pagame.descri as pagame, " .
        "pagame.numrat, " .
        "caumag.descri as caumag, " .
        "SUM(rigdoc.quanti*rigdoc.prelis*(1-rigdoc.sconto/100)*(1-tesdoc.sconto/100)) as imponibile, " .
        "SUM(rigdoc.quanti*rigdoc.prelis*(1-rigdoc.sconto/100)*(1-tesdoc.sconto/100)*rigdoc.pervat/100) as iva," .
       $campiCosto .
        "CONVERT($campoOrdinamento,UNSIGNED INTEGER) AS campoOrdinamento";
   $table = $gTables['tesdoc'] . " tesdoc "
        . "LEFT JOIN " . $gTables['rigdoc'] . " rigdoc ON tesdoc.id_tes = rigdoc.id_tes "
        . "LEFT JOIN " . $gTables['clfoco'] . " clfoco ON tesdoc.clfoco = clfoco.codice "
        . "LEFT JOIN " . $gTables['anagra'] . " anagra ON anagra.id = clfoco.id_anagra "
        . "LEFT JOIN " . $gTables['pagame'] . " pagame ON tesdoc.pagame = pagame.codice "
        . "LEFT JOIN " . $gTables['caumag'] . " caumag ON tesdoc.caumag = caumag.codice ";
        //echo $where;
   $result = gaz_dbi_dyn_query($what, $table, $where, 'campoOrdinamento', 0, 20000, 'campoOrdinamento');
   $luogo_data = $admin_aziend['citspe'] . ", lì " . ucwords(strftime("%d %B %Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))));
   $title = array('luogo_data' => $luogo_data,
    'title' => "Lista documenti: " . $titolo,
    'hile' => array(
        array('lun' => 16, 'nam' => 'Data'),
        array('lun' => 10, 'nam' => 'Num.'),
        array('lun' => 62, 'nam' => 'Destinatario'),
        array('lun' => 25, 'nam' => 'Agente'),
        array('lun' => 25, 'nam' => 'Causale'),
        array('lun' => 16, 'nam' => 'Impon.'),
        array('lun' => 12, 'nam' => 'Spese'),
        array('lun' => 14, 'nam' => 'Iva'),
        array('lun' => 16, 'nam' => 'Totale')
      )
   );
   $pdf = new Report_template();
   $pdf->setVars($admin_aziend, $title);
   $pdf->SetTopMargin(40);
   $pdf->SetFooterMargin(18);
   $config = new Config;
   $pdf->AddPage('P', $config->getValue('page_format'));
   $pdf->SetFont('helvetica', '', 8);

   $tot_imponibile = 0.00;
   $tot_iva = 0.00;
   $tot_spese = 0.00;
   $tot_importo = 0.00;
   while ($row = gaz_dbi_fetch_array($result)) {
      $spese = $row['traspo'] + $row['speban'] * $row['numrat'] + $row['spevar'];
      $alivaSpese = gaz_dbi_get_row($gTables['aliiva'], "codice", $row['expense_vat']);
      $row['iva'] = $row['iva'] + ($spese * $alivaSpese['aliquo'] / 100);
      //$bolli = $row['stamp'] * ($spese + $row['imponibile'] + $row['iva']) / 100;   // bolli per le tratte
      $bolli = calcolaBolli($row, $spese);   // bolli per le tratte
      $spese+=$bolli;
      if ($row['tipdoc'] == 'FNC') {   // nota di credito
         //$row['quanti'] = -$row['quanti'];
         $row['imponibile'] = -$row['imponibile'];
         $row['iva'] = -$row['iva'];
         $spese = -$spese;
      }
      $tot_imponibile += $row['imponibile'];
      $tot_iva += $row['iva'];
      $tot_spese += $spese;
      //$tot_importo += $row_imponibile + $row_iva;
      $pdf->Cell(16, 4, gaz_format_date($row["$campoData"]), 1);
      $pdf->Cell(10, 4, $row["$campoOrdinamento"], 1);
      $pdf->Cell(62, 4, $row['ragso1'] . " " . $row['ragso2'], 1, 0, '', false, '', 1);  
      $agente = getNewAgente($row['id_agente']);
      $pdf->Cell(25, 4, $agente['ragso1'] . ' ' . $agente['ragso2'], 1, 0, '', false, '', 1);
      $pdf->Cell(25, 4, $row['caumag'], 1, 0, '', false, '', 1);
      $pdf->Cell(16, 4, gaz_format_number($row['imponibile']), 1, 0, 'R');
      $pdf->Cell(12, 4, gaz_format_number($spese), 1, 0, 'R');
      $pdf->Cell(14, 4, gaz_format_number($row['iva']), 1, 0, 'R');
      $pdf->Cell(16, 4, gaz_format_number($row['imponibile'] + $row['iva'] + $spese), 1, 1, 'R');
   }
   $pdf->SetFont('helvetica', 'B', 8);
   $pdf->Cell(138, 4, 'Totali: ', 1, 0, 'R');
   $pdf->Cell(16, 4, gaz_format_number($tot_imponibile), 1, 0, 'R', false, '', 1);
   $pdf->Cell(12, 4, gaz_format_number($tot_spese), 1, 0, 'R', false, '', 1);
   $pdf->Cell(14, 4, gaz_format_number($tot_iva), 1, 0, 'R', false, '', 1);
   $pdf->Cell(16, 4, gaz_format_number($tot_imponibile + $tot_iva + $tot_spese), 1, 0, 'R', false, '', 1);
   $pdf->Output();
}

function calcolaBolli($row, $spese) {
   $calc = new Compute();
   $calc->pay_taxstamp = 0;
   if ($row['stamp'] > 0) {
      $calc->payment_taxstamp($row['imponibile'] + $row['iva'] + $spese, $row['stamp'], $row['round_stamp'] * $row['numrat']);
   }
   return $calc->pay_taxstamp;
}


/*az_anagra.ragso2,
               ".$gTables['tesdoc'].".seziva,
               ".$gTables['tesdoc'].".ddt_type,
               ".$gTables['tesdoc'].".id_doc_ritorno,
               ".$gTables['tesdoc'].".template,
               ".$gTables['tesdoc'].".email,
               ".$gTables['tesdoc'].".datemi,
               ".$gTables['tesdoc'].".weekday_repeat,
               ".$gTables['tesdoc'].".data_ordine,
               ".$gTables['tesdoc'].".clfoco,
               ".$gTables['tesdoc'].".pagame,
               ".$gTables['tesdoc'].".ragbol,
               ".$gTables['tesdoc'].".banapp,
               ".$gTables['tesdoc'].".vettor,
               ".$gTables['tesdoc'].".listin,
               ".$gTables['tesdoc'].".destin,
               ".$gTables['tesdoc'].".id_des,
               ".$gTables['tesdoc'].".id_des_same_company,
               ".$gTables['tesdoc'].".spediz,
               ".$gTables['tesdoc'].".portos,
               ".$gTables['tesdoc'].".imball,
               ".$gTables['tesdoc'].".round_stamp,
               ".$gTables['tesdoc'].".cauven,
               ".$gTables['tesdoc'].".caucon,
               ".$gTables['tesdoc'].".caumag,
               ".$gTables['tesdoc'].".id_agente,
               ".$gTables['tesdoc'].".id_parent_doc,
               ".$gTables['tesdoc'].".expense_vat,
               ".$gTables['tesdoc'].".stamp,
               ".$gTables['tesdoc'].".taxstamp,
               ".$gTables['tesdoc'].".virtual_taxstamp,
               ".$gTables['tesdoc'].".net_weight,
               ".$gTables['tesdoc'].".gross_weight,
               ".$gTables['tesdoc'].".units,
               ".$gTables['tesdoc'].".volume,
               ".$gTables['tesdoc'].".initra,
               ".$gTables['tesdoc'].".geneff,
               ".$gTables['tesdoc'].".id_contract,
               ".$gTables['tesdoc'].".id_con,
               ".$gTables['tesdoc'].".datreg,
               ".$gTables['tesdoc'].".fattura_elettronica_zip_package,
               ".$gTables['tesdoc'].".fattura_elettronica_original_name,
               ".$gTables['tesdoc'].".fattura_elettronica_original_content,
               ".$gTables['tesdoc'].".fattura_elettronica_reinvii,
               ".$gTables['tesdoc'].".status,
               ".$gTables['tesdoc'].".adminid,
               ".$gTables['tesdoc'].".last_modified,
               gaz_anagra.id,
               gaz_anagra.sedleg,
               gaz_anagra.legrap_pf_nome,
               gaz_anagra.legrap_pf_cognome,
               gaz_anagra.sexper,
               gaz_anagra.datnas,
               gaz_anagra.luonas,
               gaz_anagra.pronas,
               gaz_anagra.counas,
               gaz_anagra.indspe,
               gaz_anagra.capspe,
               gaz_anagra.citspe,
               gaz_anagra.prospe,
               gaz_anagra.country,
               gaz_anagra.id_currency,
               gaz_anagra.id_language,
               gaz_anagra.latitude,
               gaz_anagra.longitude,
               gaz_anagra.telefo,
               gaz_anagra.fax,
               gaz_anagra.cell,
               gaz_anagra.codfis,
               gaz_anagra.pariva,
               gaz_anagra.fe_cod_univoco,
               gaz_anagra.e_mail,
               gaz_anagra.pec_email,
               gaz_anagra.fatt_email,
               ".$gTables['tesdoc'].".id_tes,
               ".$gTables['tesdoc'].".protoc,
               ".$gTables['tesdoc'].".numdoc,
               ".$gTables['tesdoc'].".numfat,
               ".$gTables['tesdoc'].".datfat,
               ".$gTables['tesdoc'].".traspo,
               ".$gTables['tesdoc'].".speban,
               ".$gTables['tesdoc'].".spevar,*/
?>