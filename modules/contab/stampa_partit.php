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
if (!isset($_GET['codice']) ||
    !isset($_GET['codfin']) ||
    !isset($_GET['regini']) ||
    !isset($_GET['regfin']) ) {
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit;
}
require("../../config/templates/report_template.php");
$gioini = substr($_GET['regini'],0,2);
$mesini = substr($_GET['regini'],2,2);
$annini = substr($_GET['regini'],4,4);
$utsini= mktime(0,0,0,$mesini,$gioini,$annini);
$giofin = substr($_GET['regfin'],0,2);
$mesfin = substr($_GET['regfin'],2,2);
$annfin = substr($_GET['regfin'],4,4);
$utsfin= mktime(0,0,0,$mesfin,$giofin,$annfin);
$dataini = date("Ymd",$utsini);
$datafin = date("Ymd",$utsfin);
$descrDataini = date("d-m-Y",$utsini);
$descrDatafin = date("d-m-Y",$utsfin);
$luogo_data=$admin_aziend['citspe'].", lì ";
if (isset($_GET['ds'])) {
   $giosta = substr($_GET['ds'],0,2);
   $messta = substr($_GET['ds'],2,2);
   $annsta = substr($_GET['ds'],4,4);
   $utssta= mktime(0,0,0,$messta,$giosta,$annsta);
   $luogo_data .= ucwords(strftime("%d %B %Y",$utssta));
} else {
   $luogo_data .=ucwords(strftime("%d %B %Y", mktime (0,0,0,date("m"),date("d"),date("Y"))));
}
$where = " codcon BETWEEN ".$_GET['codice']." AND ".$_GET['codfin']." AND".
         " datreg BETWEEN '".$dataini."' AND '".$datafin."'";
$what = $gTables['rigmoc'].".*, ".$gTables['tesmov'].".id_tes, ".
        $gTables['tesmov'].".descri AS tesdes, ".$gTables['tesmov'].".datreg, ".$gTables['tesmov'].".datreg, ".$gTables['tesmov'].".seziva, ".
        $gTables['tesmov'].".datdoc, ".$gTables['tesmov'].".numdoc, ".$gTables['tesmov'].".protoc, ".
        $gTables['clfoco'].".codice, ".$gTables['clfoco'].".descri, t_part.descri AS partner ";
$table = $gTables['rigmoc']." LEFT JOIN ".$gTables['tesmov']." ON (".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes)
                              LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['rigmoc'].".codcon = ".$gTables['clfoco'].".codice)
                              LEFT JOIN ".$gTables['clfoco']." AS t_part ON (".$gTables['tesmov'].".clfoco = t_part.codice)";
$result = gaz_dbi_dyn_query ($what, $table,$where,"codcon ASC, datreg ASC, ".$gTables['tesmov'].".id_tes");
$item_head = array('top'=>array(array('lun' => 80,'nam'=>'Descrizione'),
                                array('lun' => 25,'nam'=>'Numero Conto')
                               )
                   );
$title = array('luogo_data'=>$luogo_data,
               'title'=>"PARTITARIO  dal ".$descrDataini." al ".$descrDatafin,
               'hile'=>array(   array('lun' => 18,'nam'=>'Data Reg.'),
                                array('lun' =>108,'nam'=>'Descrizione (Dati del documento'),
                                array('lun' => 20,'nam'=>'Dare'),
                                array('lun' => 20,'nam'=>'Avere'),
                                array('lun' => 20,'nam'=>'SALDO')
                            )
              );
$aRiportare = array('top'=>array(array('lun' => 166,'nam'=>'da riporto : '),
                           array('lun' => 20,'nam'=>'')
                           ),
                    'bot'=>array(array('lun' => 166,'nam'=>'a riportare : '),
                           array('lun' => 20,'nam'=>'')
                           )
                    );
$pdf = new Report_template();
$pdf->setVars($admin_aziend,$title);
$pdf->SetTopMargin(51);
$pdf->SetFooterMargin(22);
$config = new Config;
$ctrlConto = '';
$movSaldo = 0.00;
while ($row = gaz_dbi_fetch_array($result)) {
      $datadoc = substr($row['datdoc'],8,2).'-'.substr($row['datdoc'],5,2).'-'.substr($row['datdoc'],0,4);
      $datareg = substr($row['datreg'],8,2).'-'.substr($row['datreg'],5,2).'-'.substr($row['datreg'],0,4);
      $pdf->setRiporti($aRiportare);
      if ($ctrlConto != $row['codcon']) {
         $movSaldo = 0.00;
         if (!empty($ctrlConto)) {
                   $pdf->SetFont('helvetica','B',7);
                   $pdf->Cell($aRiportare['top'][0]['lun'],4,'SALDO al '.$descrDatafin.' : ',1,0,'R');
                   $pdf->Cell($aRiportare['top'][1]['lun'],4,$aRiportare['top'][1]['nam'],1,0,'R');
         }
         $pdf->SetFont('helvetica','',7);
         $aRiportare['top'][1]['nam'] = 0;
         $aRiportare['bot'][1]['nam'] = 0;
         $item_head['bot']= array(array('lun' => 80,'nam'=>$row['descri']),
                                  array('lun' => 25,'nam'=>$row['codcon'])
                                  );
         $pdf->setItemGroup($item_head);
         $pdf->setRiporti('');
         $pdf->AddPage('P',$config->getValue('page_format'));
      }
      if ($row['darave'] == 'D'){
        $movSaldo += $row['import'];
        $dare = gaz_format_number($row['import']);
        $avere = '';
     } else {
        $movSaldo -= $row['import'];
        $avere = gaz_format_number($row['import']);
        $dare = '';
      }
      $aRiportare['top'][1]['nam'] = gaz_format_number($movSaldo);
      $aRiportare['bot'][1]['nam'] = gaz_format_number($movSaldo);
      $pdf->Cell(18,4,$datareg,1,0,'C');
      if (!empty($row['partner']) || !empty($row['numdoc'])){
          $pdf->SetFont('helvetica','',6);
          $row['tesdes'].=' ('.$row['partner'];
          if (!empty($row['numdoc'])){
             $row['tesdes'] .= ' n.'.$row['numdoc'].' del '.$datadoc;
             if ($row['protoc']>0) {
                $row['tesdes'] .= ' sez.'.$row['seziva'].' p.'.$row['protoc'];
             }
          }
          $row['tesdes'].=')';
      }
      $pdf->Cell(108,4,$row['tesdes'],'LTB',0,'L',0,'',1);
      $pdf->SetFont('helvetica','',7);
      $pdf->Cell(20,4,$dare,1,0,'R');
      $pdf->Cell(20,4,$avere,1,0,'R');
      $pdf->Cell(20,4,gaz_format_number($movSaldo),1,1,'R');
      $ctrlConto = $row['codcon'];
}
$pdf->SetFont('helvetica','B',8);
$pdf->Cell($aRiportare['top'][0]['lun'],4,'SALDO al '.$descrDatafin.' : ',1,0,'R');
$pdf->Cell($aRiportare['top'][1]['lun'],4,$aRiportare['top'][1]['nam'],1,0,'R');
$pdf->setRiporti('');
$pdf->Output();
?>