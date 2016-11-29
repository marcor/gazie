<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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



$luogo_data = $admin_aziend['citspe'] . ", lì " . ucwords(strftime("%d %B %Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))));


require("../../config/templates/report_template.php");
$title = array('luogo_data' => $luogo_data,
    'title' => "!!! BOZZA INIZIALE INUTILIZZABILE del Libro dei BENI AMMORTIZZABILI !!!",
    'hile' => array(array('lun' => 40, 'nam' => 'ID'),
        array('lun' => 70, 'nam' => 'Descrizione bene'),
        array('lun' => 70, 'nam' => 'Fornitore'),
        array('lun' => 40, 'nam' => 'Valore'),
        array('lun' => 40, 'nam' => '% Ammortamento')
    )
);

$pdf = new Report_template();
$pdf->setVars($admin_aziend, $title);
$pdf->SetTopMargin(39);
$pdf->SetFooterMargin(20);
$pdf->AddPage('L');
$pdf->SetFont('helvetica', '', 7);
$result = gaz_dbi_dyn_query('*', $gTables['assets'], '1', 'id DESC');

while ($row = gaz_dbi_fetch_array($result)) {
    $tesmov = gaz_dbi_get_row($gTables['tesmov'], "id_tes", $row['id_movcon']);
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner($tesmov['clfoco']);
    $pdf->Cell(40, 3, $row['id'], 1);
    $pdf->Cell(70, 3, $row['descri'], 1);
    $pdf->Cell(70, 3, $fornitore["descri"], 1);
    $pdf->Cell(40, 3, gaz_format_number($row["a_value"] * $row["quantity"]), 1, 0, 'R');
    $pdf->Cell(40, 3, round($row["valamm"], 1), 1, 1, 'R');
}

$pdf->Output();
?>