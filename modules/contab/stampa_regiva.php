<?php
/*
--------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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

$mastroCassa = substr($admin_aziend['cassa_'],0,3);
if (!isset($_GET['vr']) ||
    !isset($_GET['vs']) ||
    !isset($_GET['ds']) ||
    !isset($_GET['pi']) ||
    !isset($_GET['sd']) ||
    !isset($_GET['mt']) ||
    !isset($_GET['cv']) ||
    !isset($_GET['ri']) ||
    !isset($_GET['rf'])) {
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit;
}

$gioini = substr($_GET['ri'],0,2);
$mesini = substr($_GET['ri'],2,2);
$annini = substr($_GET['ri'],4,4);
$utsini= mktime(0,0,0,$mesini,$gioini,$annini);
$giofin = substr($_GET['rf'],0,2);
$mesfin = substr($_GET['rf'],2,2);
$annfin = substr($_GET['rf'],4,4);
$utsfin= mktime(0,0,0,$mesfin,$giofin,$annfin);
$datainizio = date("Ymd",$utsini);
$datafine = date("Ymd",$utsfin);

//recupero tutti i movimenti IVA del conto insieme alle relative testate
$what = $gTables['tesmov'].".*, ".
        $gTables['rigmoi'].".*,
        CONCAT(".$gTables['anagra'].".ragso1, ' ',".$gTables['anagra'].".ragso2) AS ragsoc, ".
        $gTables['aliiva'].".descri AS desiva "
        ;
$table = $gTables['rigmoi']." LEFT JOIN ".$gTables['tesmov']." ON (".$gTables['rigmoi'].".id_tes = ".$gTables['tesmov'].".id_tes)
         LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['tesmov'].".clfoco = ".$gTables['clfoco'].".codice)
         LEFT JOIN ".$gTables['anagra']." ON (".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra)
         LEFT JOIN ".$gTables['aliiva']." ON (".$gTables['rigmoi'].".codiva = ".$gTables['aliiva'].".codice)";
$orderby = "datreg ASC , protoc ASC, id_rig ASC";
$where = "datreg BETWEEN $datainizio AND $datafine AND seziva = ".intval($_GET['vs'])." AND regiva = ".intval($_GET['vr']);
$result = gaz_dbi_dyn_query($what, $table, $where, $orderby);

switch($_GET['vr']) {
case 2:
    $title = 'Registro delle fatture di vendita '.$_GET['ds'];
    $cliforsco = 'Ragione Sociale Cliente';
    $cover_descri = 'Registro delle fatture di vendita dell\'anno '.date("Y",$utsfin)."\n sezione I.V.A. n.".$_GET['vs'];
    break;
case 4:
    $title = 'Registro dei corrispettivi '.$_GET['ds'];
    $cliforsco = 'Descrizione';
    $cover_descri = 'Registro dei corrispettivi dell\'anno '.date("Y",$utsfin)."\n sezione I.V.A. n.".$_GET['vs'];
    break;
case 6:
    $title = 'Registro degli acquisti '.$_GET['ds'];
    $cliforsco = 'Ragione Sociale Fornitore';
    $cover_descri = 'Registro degli acquisti dell\'anno '.date("Y",$utsfin)."\n sezione I.V.A. n.".$_GET['vs'];
    break;
}

$topCarry = array(array('lenght' => 118,'name'=>'da riporto : ','frame' => 'B','fill'=>0,'font'=>8),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>0),
                  array('lenght' => 32,'name'=>'','frame' => 1,'fill'=>0),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>0));
$botCarry = array(array('lenght' => 118,'name'=>'a riporto : ','frame' => 'T','fill'=>0,'font'=>8),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>1),
                  array('lenght' => 32,'name'=>'','frame' => 1,'fill'=>1),
                  array('lenght' => 20,'name'=>'','frame' => 1,'fill'=>1));
$top = array(array('lenght' => 10,'name'=>'N.Prot.','frame' => 1,'fill'=>1,'font'=>8),
             array('lenght' => 18,'name'=>'Data Reg.','frame' => 1,'fill'=>1),
             array('lenght' => 32,'name'=>'N.Documento/Descr.','frame' => 1,'fill'=>1),
             array('lenght' => 18,'name'=>'Data Doc.','frame' => 1,'fill'=>1),
             array('lenght' => 40,'name'=>$cliforsco,'frame' => 1,'fill'=>1),
             array('lenght' => 20,'name'=>'Imponibile','frame' => 1,'fill'=>1),
             array('lenght' => 14,'name'=>'Perc.','frame' => 1,'fill'=>1),
             array('lenght' => 18,'name'=>'Imposta','frame' => 1,'fill'=>1),
             array('lenght' => 20,'name'=>'Totale','frame' => 1,'fill'=>1));

require("../../config/templates/standard_template.php");
$pdf = new Standard_template();
$n_page = intval($_GET['pi']);
if ($_GET['cv']=='cover') {
   $n_page--;
}
$pdf->setVars($admin_aziend,$title,0,array('ini_page'=>$n_page,'year'=>'Sezione IVA n.'.intval($_GET['vs']).' Pagina '.$annfin));
if ($_GET['cv']=='cover') {
   $pdf->setCover($cover_descri);
   $pdf->AddPage();
}
$pdf->setTopBar($top);
$pdf->AddPage();
$pdf->setFooterMargin(21);
$pdf->setTopMargin(44);
$pdf->SetFont('helvetica','',8);
$castelconti = array();
$totimponi =0.00;
$totimpost =0.00;
$totmovpre =0.00;
$totmovsuc =0.00;
$ctrlmovco = 0;
$ctrlmoiva = 0;
$saldo = 0.00;
$key="";

while ($mov = gaz_dbi_fetch_array($result)) {
      $giomov = substr($mov['datreg'],8,2);
      $mesmov = substr($mov['datreg'],5,2);
      $annmov = substr($mov['datreg'],0,4);
      $giodoc = substr($mov['datdoc'],8,2);
      $mesdoc = substr($mov['datdoc'],5,2);
      $anndoc = substr($mov['datdoc'],0,4);
      $utsmov= mktime(0,0,0,$mesmov,$giomov,$annmov);
      $utsdoc= mktime(0,0,0,$mesdoc,$giodoc,$anndoc);
      $datamov = date("d-m-Y",$utsmov);
      $datadoc = date("d-m-Y",$utsdoc);
      $codiva = $mov['codiva'];
      switch ($mov['operat']) {
             case "1":
             $imponi = $mov['imponi'];
             $impost = $mov['impost'];
             $sezion = "/".$mov['seziva'];
             break;
             case "2":
             $imponi = number_format(-$mov['imponi'],2, '.', '');
             $impost = number_format(-$mov['impost'],2, '.', '');
             $sezion = "";
             break;
             default:
             $imponi = 0;
             $impost = 0;
             $sezion = "";
             break;
      }
      $totimponi += $imponi;
      if ($mov['tipiva'] != "D") {
         $totimpost += $impost;
      }
      if (!isset($castelimponi[$codiva])) {
         $castelimponi[$codiva]= 0;
      }
      $castelimponi[$codiva] = number_format(($castelimponi[$codiva]+ $imponi),2,'.','');
      if (!isset($castelimpost[$codiva])) {
         $castelimpost[$codiva]= 0;
      }
      $castelimpost[$codiva] = number_format(($castelimpost[$codiva]+ $impost),2,'.','');
      if ($ctrlmovco != $mov['id_tes']) {
         $pdf->Cell(10,4,$mov['protoc'],'LTB',0,'C');
         $pdf->Cell(18,4,$datamov,'LTB',0,'C');
         $pdf->Cell(32,4,$mov['numdoc'],'LTB',0,'C');
         $pdf->Cell(18,4,$datadoc,'LTB',0,'R');
         $pdf->Cell(112,4,$mov['ragsoc'],'LTR',1,'L');
         $topCarry[1]['name']= gaz_format_number($totimponi);
         $botCarry[1]['name']= gaz_format_number($totimponi);
         $topCarry[2]['name']= gaz_format_number($totimpost);
         $botCarry[2]['name']= gaz_format_number($totimpost);
         $topCarry[3]['name']= gaz_format_number($totimponi+$totimpost);
         $botCarry[3]['name']= gaz_format_number($totimponi+$totimpost);
         $pdf->setTopCarryBar($topCarry);
         $pdf->setBotCarryBar($botCarry);
         $pdf->Cell(66,4,$mov['descri'],'LTB',0,'R');
         $pdf->Cell(12,4,'cod. '.$mov['codiva'],1,0,'C');
         $pdf->Cell(40,4,$mov['desiva'],1,0,'L');
         $pdf->Cell(20,4,gaz_format_number($imponi),1,0,'R');
         $pdf->Cell(14,4,gaz_format_number($mov['periva']).'%',1,0,'C');
         $pdf->Cell(18,4,gaz_format_number($impost),1,0,'R');
         $pdf->Cell(20,4,gaz_format_number($impost + $imponi),1,1,'R');
         $topY = $pdf->GetY();
         $maxY = $pdf->GetY();
         //se e' una semplificata recupero anche i righi contabili
         if ($_GET['mt']==1) {
            $rs_righicontabili = gaz_dbi_dyn_query("*",
                               $gTables['rigmoc']." LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['rigmoc'].".codcon = ".$gTables['clfoco'].".codice)",
                                      "id_tes = '".$mov['id_tes']."'
                                      AND codcon NOT LIKE '".$admin_aziend['mascli']."%'
                                      AND codcon NOT LIKE '".$admin_aziend['masfor']."%'
                                      AND codcon NOT LIKE '$mastroCassa%'
                                      AND codcon NOT LIKE '".$admin_aziend['masban']."%'
                                      AND codcon <> ".$admin_aziend['ivaacq']."
                                      AND codcon <> ".$admin_aziend['ivaven']."
                                      AND codcon <> ".$admin_aziend['ivacor'],
                                      "id_rig asc");
            while ($righi_cont = gaz_dbi_fetch_array($rs_righicontabili)) {
               $codcon = $righi_cont['codcon'];
               $pdf->SetFont('helvetica','',7);
               $pdf->Cell(50,4,$righi_cont['codcon']."-".substr($righi_cont['descri'],0,23),'L');
               $pdf->Cell(1,4,$money[1]);
               $maxY = $pdf->GetY();
               $pdf->Cell(15,4,gaz_format_number($righi_cont['import']),'R',1,'R');
               $pdf->SetFont('helvetica','',8);
               if (!isset($castelconti[$codcon])) {
                   $castelconti[$codcon] = array('value'=>0,'ds'=>0);
                   $castelconti[$codcon]['ds'] = $righi_cont['descri'];
               }
               if (($righi_cont['darave'] == 'A' and $mov['regiva'] > 5) or ($righi_cont['darave'] == 'D' and $mov['regiva'] <= 5) ) {
                    $castelconti[$codcon]['value'] -= $righi_cont['import'];
               } else {
                    $castelconti[$codcon]['value'] += $righi_cont['import'];
               }
            }
         }
      } else {
         if ($maxY > $topY) {
            $topY = $maxY;
         }
         $topCarry[1]['name']= gaz_format_number($totimponi);
         $botCarry[1]['name']= gaz_format_number($totimponi);
         $topCarry[2]['name']= gaz_format_number($totimpost);
         $botCarry[2]['name']= gaz_format_number($totimpost);
         $topCarry[3]['name']= gaz_format_number($totimponi+$totimpost);
         $botCarry[3]['name']= gaz_format_number($totimponi+$totimpost);
         $pdf->setTopCarryBar($topCarry);
         $pdf->setBotCarryBar($botCarry);
         $pdf->SetXY(76,$topY);
         $pdf->Cell(12,4,'cod. '.$mov['codiva'],1,0,'C');
         $pdf->Cell(40,4,$mov['desiva'],1,0,'L');
         $pdf->Cell(20,4,gaz_format_number($imponi),1,0,'R');
         $pdf->Cell(14,4,gaz_format_number($mov['periva']).'%',1,0,'C');
         $pdf->Cell(18,4,gaz_format_number($impost),1,0,'R');
         $pdf->Cell(20,4,gaz_format_number($impost + $imponi),1,1,'R');
         $topY = $pdf->GetY();
      }
      $ctrlmovco = $mov['id_tes'];
}
$pdf->setTopCarryBar('');
$pdf->setBotCarryBar('');
$pdf->Cell(190,1,'','T');
$pdf->SetFont('helvetica','B',10);
$pdf->Ln(6);
$pdf->Cell(190,6,'RIEPILOGO TOTALI PER ALIQUOTE',1,1,'C',1);
$pdf->Cell(20,5,'cod'.$key,1,0,'C');
$pdf->Cell(60,5,'descrizione',1,0,'C');
$pdf->Cell(30,5,'imponibile',1,0,'R');
$pdf->Cell(20,5,'%',1,0,'C');
$pdf->Cell(30,5,'imposta',1,0,'R');
$pdf->Cell(30,5,'totale',1,1,'R');
$totale = number_format(($totimponi+$totimpost),2,'.','');
foreach ($castelimponi as $key => $value) {
     $iva = gaz_dbi_get_row($gTables['aliiva'],"codice",$key);
     $pdf->Cell(20,5,$key,1,0,'C');
     $pdf->Cell(60,5,$iva['descri'],1,0,'C');
     $pdf->Cell(30,5,gaz_format_number($value),1,0,'R');
     $pdf->Cell(20,5,$iva['aliquo'].'%',1,0,'C');
     $pdf->Cell(30,5,gaz_format_number($castelimpost[$key]),1,0,'R');
     $pdf->Cell(30,5,gaz_format_number($value + $castelimpost[$key]),1,1,'R');
}
$pdf->SetFont('helvetica','B',10);
$pdf->Cell(80,5,'TOTALE GENERALE',1,0,'C',1);
$pdf->Cell(30,5,gaz_format_number($totimponi),1,0,'R',1);
$pdf->Cell(20,5);
$pdf->Cell(30,5,gaz_format_number($totimpost),1,0,'R',1);
$pdf->Cell(30,5,gaz_format_number($totale),1,1,'R',1);

if ($_GET['mt']==1) {
   $pdf->Ln(6);
   $pdf->SetFont('helvetica','B',10);
   $pdf->Cell(35);
   $pdf->Cell(120,6,'RIEPILOGO TOTALI CONTI',1,2,'C',1);
   $pdf->Cell(20,5,'codice',1,0,'C');
   $pdf->Cell(75,5,'descrizione',1,0,'C');
   $pdf->Cell(25,5,'importo',1,1,'R');
   $pdf->SetFont('helvetica','',8);
   foreach ($castelconti as $key => $value) {
     $pdf->Cell(35);
     $pdf->Cell(20,5,$key,1,0,'C');
     $pdf->Cell(75,5,$value['ds'],1,0,'L');
     $pdf->Cell(25,5,gaz_format_number($value['value']),1,1,'R');
   }
}

switch($_GET['vr']) {
        case 2:
        $azireg='upgve'.intval($_GET['vs']);
        break;
        case 4:
        $azireg='upgco'.intval($_GET['vs']);
        break;
        case 6:
        $azireg='upgac'.intval($_GET['vs']);
        break;
}
if ($_GET['sd']=='sta_def') {
    gaz_dbi_put_row($gTables['aziend'],'codice',$admin_aziend['codice'],$azireg, $pdf->getGroupPageNo()+$n_page-1);
}
$pdf->Output();
?>