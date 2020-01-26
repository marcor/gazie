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
require("../../library/include/datlib.inc.php");

$admin_aziend = checkAdmin();
if (!ini_get('safe_mode')) { //se me lo posso permettere...
   ini_set('memory_limit', '128M');
   gaz_set_time_limit(0);
}
//if (!isset($_GET['id_agente'])) {
//   header("Location: " . $_SERVER['HTTP_REFERER']);
//   exit;
//}
require("../../config/templates/report_template.php");
$form['id_agente'] = (isset($_GET['id_agente']) ? $_GET['id_agente'] : '');
$form['clifor'] = (isset($_GET['clifor']) ? $_GET['clifor'] : '');
if (empty($form['id_agente']) && empty($form['clifor'])) { // mancano i dati per la selezione
   alert("Niente da stampare");
   tornaPaginaPrecedente();
} elseif (!empty($form['id_agente'])) {  // vogliamo la stampa dei clienti di un agente
   $where = "id_agente=" . $form['id_agente'] . " and clfoco.codice like '" . $admin_aziend['mascli'] . "%'";
   $titolo = "CLIENTI DELL'AGENTE: " . queryNomeAgente($form['id_agente'], $gTables);
} else {   // vogliamo la stampa dell'anagrafica
   $mastro = ($form['clifor'] == 'C' ? $admin_aziend['mascli'] : $admin_aziend['masfor']);
   $where = "clfoco.codice like '$mastro%'";
   $titolo = ($form['clifor'] == 'C' ? 'Elenco Clienti' : 'Elenco Fornitori');
}
$luogo_data = $admin_aziend['citspe'] . ", lì " . ucwords(strftime("%d %B %Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))));

$title = array('luogo_data' => $luogo_data,
    'title' => $titolo,
    'hile' => array(/* array('lun' => 45, 'nam' => 'Cliente'), */
        array('lun' => 60, 'nam' => 'Ragione Sociale'),
        array('lun' => 60, 'nam' => 'Sede Legale'),
        array('lun' => 20, 'nam' => 'Telefono'),
        array('lun' => 20, 'nam' => 'Cellulare'),
//        array('lun' => 20, 'nam' => 'Fax'),
        array('lun' => 40, 'nam' => 'Email'),
    )
);
$pdf = new Report_template();
$pdf->setVars($admin_aziend, $title);
$pdf->setFooterMargin(22);
$pdf->setTopMargin(43);
$pdf->SetLeftMargin(5);
$pdf->SetFillColor(160, 255, 220);
$pdf->AddPage();
//$config = new Config;
$rs = gaz_dbi_dyn_query("concat(ragso1,space(1),ragso2) as ragioneSociale, sedleg, telefo, cell, fax, e_mail", $gTables['clfoco'] . " clfoco LEFT JOIN " . $gTables['anagra'] . " anagra ON anagra.id = clfoco.id_anagra ", $where, "ragioneSociale");

while ($cliente = gaz_dbi_fetch_array($rs)) {
   $pdf->SetFont('helvetica', '', 10);
   $pdf->Cell(60, 0, $cliente['ragioneSociale'], 1, 0, 'L', false, '', 1);
   $pdf->Cell(60, 0, $cliente['sedleg'], 1, 0, 'L', false, '', 1);
   $pdf->Cell(20, 0, $cliente["telefo"], 1, 0, 'L', false, '', 1);
   $pdf->Cell(20, 0, $cliente["cell"], 1, 0, 'L', false, '', 1);
//   $pdf->Cell(20, 0, $cliente["fax"], 1, 0, 'L', false, '', 1);
   $pdf->Cell(40, 0, $cliente["e_mail"], 1, 1, 'L', false, '', 1);
}
$pdf->Output();

function queryNomeAgente($id_agente, $gTables) {
   $retVal = "";
   $rs = gaz_dbi_dyn_query("anagra.ragso1,anagra.ragso2", $gTables['agenti'] . " agenti LEFT JOIN " . $gTables['clfoco'] . " clfoco on agenti.id_fornitore = clfoco.codice "
           . "LEFT JOIN " . $gTables['anagra'] . ' anagra ON clfoco.id_anagra = anagra.id', "agenti.id_agente=$id_agente");
//   $anagrafiche = array();
   if ($r = gaz_dbi_fetch_array($rs)) {
      $retVal = $r["ragso1"] . " " . $r["ragso2"];
   }
   return $retVal;
}

?>