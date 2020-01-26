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


$luogo_data = $admin_aziend['citspe'] . ", lì " . ucwords(strftime("%d %B %Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))));

$form['id_agente'] = (isset($_GET['id_agente']) ? intval($_GET['id_agente']) : '');
$form['clifor'] = (isset($_GET['clifor']) ? substr($_GET['clifor'],-1) : '');
$orderby="ragioneSociale";
if (empty($form['id_agente']) && empty($form['clifor'])) { // mancano i dati per la selezione
   alert("Niente da stampare");
   tornaPaginaPrecedente();
} elseif (!empty($form['id_agente'])) {  // vogliamo la stampa dei clienti di un agente
   $where = "id_agente=" . $form['id_agente'] . " and clfoco.codice like '" . $admin_aziend['mascli'] . "%'";
   $titolo = "CLIENTI DELL'AGENTE: " . queryNomeAgente($form['id_agente'], $gTables);
} elseif (isset($_GET['order'])&&$_GET['order']=='ZONE') {  // vogliamo la stampa dei clienti in ordine di zona
   $mastro = ($form['clifor'] == 'C' ? $admin_aziend['mascli'] : $admin_aziend['masfor']);
   $where = "clfoco.codice LIKE '$mastro%'";
   $titolo = "CLIENTI IN ORDINE DI ZONA ";
   $orderby="regions.id, provinces.id";
} else {   // vogliamo la stampa dell'anagrafica
   $mastro = ($form['clifor'] == 'C' ? $admin_aziend['mascli'] : $admin_aziend['masfor']);
   $where = "clfoco.codice like '$mastro%'";
   $titolo = ($form['clifor'] == 'C' ? 'Elenco Clienti' : 'Elenco Fornitori');
}

require("../../config/templates/report_template.php");
$title = array('luogo_data'=>$luogo_data,
               'title'=>$titolo,
               'hile'=>array(        
					array('lun' => 70, 'nam' => 'Ragione Sociale'),
					array('lun' => 20, 'nam' => 'Partitia IVA'),
					array('lun' => 90, 'nam' => 'Indirizzo'),
					array('lun' => 50, 'nam' => 'Telefono'),
					array('lun' => 40, 'nam' => 'Email'),
				)
              );
$pdf = new Report_template('L','mm','A4',true,'UTF-8',false,true);
$pdf->setVars($admin_aziend,$title);
$pdf->SetTopMargin(39);
$pdf->SetFooterMargin(20);
$config = new Config;
$pdf->AddPage('L',$config->getValue('page_format'));
$pdf->SetFont('helvetica','',9);

$rs = gaz_dbi_dyn_query("concat(ragso1,space(1),ragso2) as ragioneSociale, concat (indspe, space(1), citspe, ' (',prospe,')') as sede, concat(telefo, space(1), cell, space(1), fax) as telefono, e_mail, pariva", $gTables['clfoco'] . " clfoco 
LEFT JOIN " . $gTables['anagra'] . " anagra ON anagra.id = clfoco.id_anagra 
LEFT JOIN " . $gTables['provinces'] . " provinces ON anagra.prospe = provinces.abbreviation 
LEFT JOIN " . $gTables['regions'] . " regions ON provinces.id_region = regions.id", $where, $orderby);

while ($cliente = gaz_dbi_fetch_array($rs)) {
   $pdf->Cell(70, 0, $cliente['ragioneSociale'], 1, 0, 'L', false, '', 1);
   $pdf->Cell(20, 0, $cliente['pariva'], 1, 0, 'L', false, '', 1);
   $pdf->Cell(90, 0, $cliente['sede'], 1, 0, 'L', false, '', 1);
   $pdf->Cell(50, 0, $cliente["telefono"], 1, 0, 'L', false, '', 1);
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