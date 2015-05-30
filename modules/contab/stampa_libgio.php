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
$admin_aziend=checkAdmin();
if (!ini_get('safe_mode')){ //se me lo posso permettere...
    ini_set('memory_limit','128M');
    gaz_set_time_limit (0);
}

if (!isset($_GET['regini']) or !isset($_GET['regfin'])) {
    header("Location: select_libgio.php");
    exit;
}
$gioini = substr($_GET['regini'],0,2);
$mesini = substr($_GET['regini'],3,2);
$annini = substr($_GET['regini'],6,4);
$utsini= mktime(0,0,0,$mesini,$gioini,$annini);
$giofin = substr($_GET['regfin'],0,2);
$mesfin = substr($_GET['regfin'],3,2);
$annfin = substr($_GET['regfin'],6,4);
$utsfin= mktime(0,0,0,$mesfin,$giofin,$annfin);
$datainizio = date("Ymd",$utsini);
$datafine = date("Ymd",$utsfin);
$title = 'Libro Giornale dal '.date("d-m-Y",$utsini).' al '.date("d-m-Y",$utsfin);
//recupero tutti i movimenti contabili insieme alle relative testate...
$result = mergeTable($gTables['rigmoc'],"*",$gTables['tesmov'],"*","id_tes","datreg between $datainizio and $datafine  ORDER BY datreg, ".$gTables['rigmoc'].".id_tes,id_rig,codcon");


$cover_descri = 'Libro Giornale dell\'anno '.date("Y",$utsfin);
$tot_avere=number_format($_GET['valave'],2, '.', '');
$tot_dare=number_format($_GET['valdar'],2, '.', '');

$topCarry = array(array('lenght' => 150,'name'=>'da riporto : ','frame' => 'B','fill'=>0,'font'=>8),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>0),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>0));
$botCarry = array(array('lenght' => 150,'name'=>'a riporto : ','frame' => 'T','fill'=>0,'font'=>8),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>1),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>1));
$top = array(array('lenght' => 10,'name'=>'Progr.','frame' => 1,'fill'=>1,'font'=>8),
             array('lenght' => 18,'name'=>'Data Reg.','frame' => 1,'fill'=>1),
             array('lenght' => 60,'name'=>'Descrizione Operazione / Num. e Data Doc.','frame' => 1,'fill'=>1),
             array('lenght' => 16,'name'=>'N.Conto','frame' => 1,'fill'=>1),
             array('lenght' => 46,'name'=>'Nome Conto','frame' => 1,'fill'=>1),
             array('lenght' => 20,'name'=>'Dare','frame' => 1,'fill'=>1),
             array('lenght' => 20,'name'=>'Avere','frame' => 1,'fill'=>1));

require("../../config/templates/standard_template.php");
$pdf = new Standard_template();
$n_page = intval($_GET['pagini']);
if (isset($_GET['copert'])) {
   $n_page--;
}
$pdf->setVars($admin_aziend,$title,0,array('ini_page'=>$n_page,'year'=>'Pagina '.$annfin));
if (isset($_GET['copert'])) {
   $pdf->setCover($cover_descri);
   $pdf->AddPage();
}
$pdf->setTopBar($top);
if (($tot_avere+$tot_dare)> 0.01){
    $topCarry[1]['name']= gaz_format_number($tot_dare);
    $topCarry[2]['name']= gaz_format_number($tot_avere);
    $pdf->setTopCarryBar($topCarry);
}
$pdf->AddPage();
$pdf->setFooterMargin(21);
$pdf->setTopMargin(44);
$pdf->SetFont('helvetica','',7);
$ctrlmopre = 0;
$numrig=1;

$anagrafica = new Anagrafica();
while ($mov = gaz_dbi_fetch_array($result)) {
    $giomov = substr($mov['datreg'],8,2);
    $mesmov = substr($mov['datreg'],5,2);
    $annmov = substr($mov['datreg'],0,4);
    $giodoc = substr($mov['datdoc'],8,2);
    $mesdoc = substr($mov['datdoc'],5,2);
    $anndoc = substr($mov['datdoc'],0,4);
    $clfoco = $anagrafica->getPartner($mov["codcon"]);
    $utsmov= mktime(0,0,0,$mesmov,$giomov,$annmov);
    $datamov = date("d-m-Y",$utsmov);
    $datadoc = $giodoc.'-'.$mesdoc.'-'.$anndoc;

    if($mov["darave"] == 'D') {
        $dare = gaz_format_number($mov["import"]);
        $tot_dare += $mov["import"];
        $avere = "";
    } else {
        $avere = gaz_format_number($mov["import"]);
        $tot_avere += $mov["import"];
        $dare = "";
    }

    if ($mov["id_tes"] != $ctrlmopre) {
        $pdf->Cell(10,4,$numrig,'LTR',0,'R');
        $pdf->Cell(18,4,$datamov,1,0,'L');
        $pdf->Cell(60,4,$mov['descri'],1,0,'L',0,'',1);
        $pdf->Cell(16,4,$mov['codcon'],'LT',0,'C');
        $pdf->Cell(46,4,$clfoco['descri'],'LT',0,'L',0,'',1);
        $pdf->Cell(20,4,$dare,'LT',0,'R');
        $pdf->Cell(20,4,$avere,'LRT',1,'R');
        if(!empty($mov["numdoc"])) {
            $pdf->Cell(28);
            $pdf->Cell(60,4,"n.".$mov['numdoc']."/".$mov['seziva']." del ".$datadoc,1,0,'L');
            $pdf->SetX(10);
        }
    } else {
        $pdf->Cell(10,4,$numrig,'LR',0,'R');
        $pdf->Cell(78,4,'',0,0,'L');
        $pdf->Cell(16,4,$mov['codcon'],'L',0,'C');
        $pdf->Cell(46,4,$clfoco['descri'],'L',0,'L',0,'',1);
        $pdf->Cell(20,4,$dare,'L',0,'R');
        $pdf->Cell(20,4,$avere,'LR',1,'R');
    }

    $topCarry[1]['name']= gaz_format_number($tot_dare);
    $botCarry[1]['name']= gaz_format_number($tot_dare);
    $topCarry[2]['name']= gaz_format_number($tot_avere);
    $botCarry[2]['name']= gaz_format_number($tot_avere);
    $pdf->setTopCarryBar($topCarry);
    $pdf->setBotCarryBar($botCarry);

    $ctrlmopre = $mov["id_tes"];
    $numrig ++;
}
$pdf->setBotCarryBar('');
$pdf->Cell(150,4,'TOTALI : ',1,0,'L');
$pdf->Cell(20,4,gaz_format_number($tot_dare),1,0,'R',1);
$pdf->Cell(20,4,gaz_format_number($tot_avere),1,0,'R',1);
if (isset($_GET['stadef'])) {
    gaz_dbi_put_row($gTables['aziend'],"codice",$admin_aziend['codice'],'upggio', $pdf->getGroupPageNo()+$n_page-1);
}
$pdf->Output($title.'.pdf');
?>
