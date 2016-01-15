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
if (!isset($_GET['orderby'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
require("../../config/templates/report_template.php");
/* ENRICO FEDELE */
/* strftime effettivamente formatta sulla base della lingua del server, ma se l'italiano non è installato, comunque la data sarà in inglese
  stessa cosa dicasi per il fuso orario (sul mio NAS non so perchè se stampo l'ora, mi rendo conto che il fuso orario è quello cinese!!!
  mi chiedo perchè è stato usato mktime invete di lasciare che sia il sistema a prendere data/ora correnti con time(), forse per tentare di
  bypassare il problema del fuso orario?
  Per avere sicuramente data e ora nella lingua impostata dall'utente, occorrerebbe predisporre degli apposity array di localizzazione
  $date = array("month" => array(1=> "Gennaio", 2 => "Febbraio", ...., 12 => "Dicembre"),
  "day"	 => array(1=> "Lunedì",  2 => "Martedì", ...., 7 => "Domenica"));
  da richiamare poi con $date["month"][date("n")]
  $date["day"][date("N")]
 */
$luogo_data = $admin_aziend['citspe'] . ", lì " . ucwords(strftime("%d %B %Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))));
/* ENRICO FEDELE */
$item_head = array('top' => array(array('lun' => 80, 'nam' => 'Descrizione'),
        array('lun' => 25, 'nam' => 'Numero Conto')
    )
);
/* ENRICO FEDELE */
/* Modifico larghezza e intestazione delle colonne */
$title = array('luogo_data' => $luogo_data,
    'title' => "LISTA DELLE PARTITE APERTE ",
    'hile' => array(array('lun' => 45, 'nam' => 'Cliente'),
        array('lun' => 20, 'nam' => 'ID Partita'),
        array('lun' => 41, 'nam' => 'Descrizione'),
        array('lun' => 11, 'nam' => 'N.Doc.'),
        array('lun' => 13, 'nam' => 'D. Doc.'),
        array('lun' => 13, 'nam' => 'D. Reg.'),
        array('lun' => 15, 'nam' => 'Dare'),
        array('lun' => 15, 'nam' => 'Avere'),
        array('lun' => 13, 'nam' => 'Scad.')
    )
);
/* ENRICO FEDELE */
$aRiportare = array('top' => array(array('lun' => 166, 'nam' => 'da riporto : '),
        array('lun' => 20, 'nam' => '')
    ),
    'bot' => array(array('lun' => 166, 'nam' => 'a riportare : '),
        array('lun' => 20, 'nam' => '')
    )
);
$pdf = new Report_template();
$pdf->setVars($admin_aziend, $title);
$pdf->setFooterMargin(22);
$pdf->setTopMargin(43);
$pdf->SetFillColor(238, 238, 238);
$pdf->setRiporti('');
$pdf->AddPage();
$config = new Config;
$scdl = new Schedule;
$m = $scdl->getScheduleEntries(intval($_GET['orderby']), $admin_aziend['mascli']);
if (sizeof($scdl->Entries) > 0) {
    $ctrl_partner = 0;
    $ctrl_id_tes = 0;
    $ctrl_paymov = 0;

    /* ENRICO FEDELE */
    /* Inizializzo la variabili per il totale */
    $tot_dare = 0;
    $tot_avere = 0;
    /* ENRICO FEDELE */

    while (list($key, $mv) = each($scdl->Entries)) {
        $pdf->SetFont('helvetica', '', 6);
        $class_partner = '';
        $class_paymov = '';
        $class_id_tes = '';
        $partner = '';
        $id_tes = '';
        $paymov = '';
        if ($mv["clfoco"] <> $ctrl_partner) {
            $class_partner = 'FacetDataTDred';
            $partner = $mv["ragsoc"];
        }
        if ($mv["id_tes"] <> $ctrl_id_tes) {
            $class_id_tes = 'FacetFieldCaptionTD';
            $id_tes = $mv["id_tes"];
            $mv["datdoc"] = gaz_format_date($mv["datdoc"]);
        } else {
            $mv['descri'] = '';
            $mv['numdoc'] = '';
            $mv['seziva'] = '';
            $mv['datdoc'] = '';
            $class_partner = '';
            $partner = '';
        }
        if ($mv["id_tesdoc_ref"] <> $ctrl_paymov) {
            $paymov = $mv["id_tesdoc_ref"];
            $scdl->getStatus($paymov);
            if ($scdl->Status['diff_paydoc'] <> 0) {
                $status_cl = false;
            } else {
                $status_cl = true;
            }
        }
        if (empty($mv["numdoc"])) {
            $mv["datdoc"] = '';
            $mv['seziva'] = '';
        }
        $pdf->Cell(45, 4, $partner, 'LTB', 0, '', $status_cl, '', 1);
        $pdf->Cell(20, 4, $paymov, 1, 0, 'R', $status_cl, '', 2);
        $pdf->Cell(41, 4, $mv['descri'], 1, 0, 'C', $status_cl, '', 1);
        $pdf->Cell(11, 4, $mv["numdoc"] . '/' . $mv['seziva'], 1, 0, 'R', $status_cl);

        /* ENRICO FEDELE */
        /* Modifico la larghezza delle celle */
        $pdf->Cell(13, 4, $mv["datdoc"], 1, 0, 'C', $status_cl);
        $pdf->Cell(13, 4, gaz_format_date($mv["datreg"]), 1, 0, 'C', $status_cl);
        if ($mv['id_rigmoc_pay'] == 0) {
            /* Incremento il totale del dare */
            $tot_dare += $mv['amount'];
            /* Modifico la larghezza delle celle */
            $pdf->Cell(15, 4, gaz_format_number($mv['amount']), 1, 0, 'R', $status_cl);
            $pdf->Cell(15, 4, '', 1, 0, 'R', $status_cl);
        } else {
            /* Incremento il totale dell'avere, e decremento quello del dare */
            $tot_avere += $mv['amount'];
            $tot_dare -= $mv['amount'];
            /* Modifico la larghezza delle celle */
            $pdf->Cell(15, 4, '', 1, 0, 'R', $status_cl);
            $pdf->Cell(15, 4, gaz_format_number($mv['amount']), 1, 0, 'R', $status_cl);
        }
        /* Modifico la larghezza della cella */
        $pdf->Cell(13, 4, gaz_format_date($mv["expiry"]), 1, 1, 'C', $status_cl);
        /* ENRICO FEDELE */
        $ctrl_partner = $mv["clfoco"];
        $ctrl_id_tes = $mv["id_tes"];
        $ctrl_paymov = $mv["id_tesdoc_ref"];
    }
    /* ENRICO FEDELE */
    /* Stampo una riga vuota sottile per separare leggermente il totale e metterlo in evidenza */
    $pdf->SetFont('helvetica', '', 1);
    $pdf->Cell(186, 1, '', 1, 1, 'C', true);

    /* Stampo la riga del totale, in grassetto italico "BI" */
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->Cell(143, 4, 'TOTALE', 1, 0, 'R', false);

    $pdf->Cell(15, 4, gaz_format_number($tot_dare), 1, 0, 'R', false);
    $pdf->Cell(15, 4, gaz_format_number($tot_avere), 1, 0, 'R', true);
    /* Aggiunta la percentuale dell'avere rispetto al totale dare+avere, colorata come la cella avere per renderla intuitiva */
    $pdf->Cell(13, 4, gaz_format_number(100 * $tot_avere / ($tot_dare + $tot_avere)) . " %", 1, 1, 'C', true);
    /* ENRICO FEDELE */
}
$pdf->setRiporti('');
$pdf->Output();
?>