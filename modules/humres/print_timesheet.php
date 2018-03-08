<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
         (http://www.devincentiis.it)
           <http://gazie.sourceforge.net>
 --------------------------------------------------------------------------
    Questo programma e` free software;   e` lecito redistribuirlo  e/o
    modificarlo secondo i  termini della Licenza Pubblica Generica GNU
    come e` pubblicata dalla Free Software Foundation; o la versione 2
    della licenza o (a propria scelta) una versione successiva.

    Questo programma  e` distribuito nella speranza  che sia utile, ma
    SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
    NEGOZIABILITA` o di  APPLICABILITA` PER UN  PWorkerLARE SCOPO.  Si
    veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

    Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
    Generica GNU insieme a   questo programma; in caso  contrario,  si
    scriva   alla   Free  Software Foundation, 51 Franklin Street,
    Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
 --------------------------------------------------------------------------
*/

require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();

if (!ini_get('safe_mode')){ //se me lo posso permettere...
    ini_set('memory_limit','128M');
}

if (!isset($_GET['year']) || !isset($_GET['week'])) {
    header("Location: docume_humres.php");
    exit;
}

$dto = new DateTime();
$dto->setISODate(intval($_GET['year']), intval($_GET['week']));
$dto->modify('first day of this month');
$first_day = $dto->format('Y-m-d');
$dto->modify('last day of this month');
$last_day = $dto->format('Y-m-d');

$luogo_data=$admin_aziend['citspe'].", lì " . ucwords(strftime("%d %B %Y", mktime (0,0,0,date("m"),date("d"),date("Y"))));

require("../../config/templates/report_template.php");
require("lang.".$admin_aziend['lang'].".php");
$script_transl=$strScript['employee_timesheet.php'];

$where=" work_day BETWEEN '".$first_day."' AND '".$last_day."'";
$what="*";
$tables=$gTables['staff_worked_hours'].' AS wh LEFT JOIN '.$gTables['staff'] . ' AS st ON wh.id_staff=st.id_staff ' . 'LEFT JOIN ' . $gTables['clfoco'] . ' AS wo ON st.id_clfoco=wo.codice ' . 'LEFT JOIN ' . $gTables['anagra'] . ' AS an ON wo.id_anagra=an.id ';
$result = gaz_dbi_dyn_query ($what, $tables,$where,'wh.id_staff ASC, wh.work_day ASC');

$item_head = array('top'=>array(array('lun' => 21,'nam'=>$script_transl['item_head'][0]),
                                array('lun' => 18,'nam'=>$script_transl['item_head'][1]),
                                array('lun' => 60,'nam'=>$script_transl['item_head'][2]),
                                array('lun' => 10,'nam'=>$script_transl['item_head'][3]),
                                array('lun' => 18,'nam'=>$script_transl['item_head'][4])
                               )
                   );

$title = array('luogo_data'=>$luogo_data,
               'title'=>$script_transl['title'],
               'hile'=>array(array('lun' => 16,'nam'=>$script_transl['header'][0]),
                             array('lun' => 30,'nam'=>$script_transl['header'][1]),
                             array('lun' => 100,'nam'=>$script_transl['header'][2]),
                             array('lun' => 17,'nam'=>$script_transl['header'][3]),
                             array('lun' => 8,'nam'=>$script_transl['header'][4]),
                             array('lun' => 17,'nam'=>$script_transl['header'][5]),
                             array('lun' => 17,'nam'=>$script_transl['header'][6]),
                             array('lun' => 17,'nam'=>$script_transl['header'][7]),
                             array('lun' => 20,'nam'=>$script_transl['header'][8]),
                             array('lun' => 20,'nam'=>$script_transl['header'][9])
                            )
              );
$aRiportare = array('top'=>array(array('lun' => 222,'nam'=>$script_transl['top']),
                           array('lun' => 20,'nam'=>''),
                           array('lun' => 20,'nam'=>'')
                           ),
                    'bot'=>array(array('lun' => 222,'nam'=>$script_transl['bot']),
                           array('lun' => 20,'nam'=>''),
                           array('lun' => 20,'nam'=>'')
                           )
                    );
$pdf = new Report_template();
$pdf->setVars($admin_aziend,$title);
$pdf->SetTopMargin(51);
$config = new Config;
$pdf->SetFont('helvetica','',7);
$pdf->AddPage('L',$config->getValue('page_format'));
$ctrlWorker='';
$ctrl_id=0;
while ($mv = gaz_dbi_fetch_array($result)) {
      $pdf->setRiporti($aRiportare);
      if ($ctrlWorker!=$mv['id_staff'] && $ctrlWorker!='') {
         if (!empty($ctrlWorker)) {
                   $pdf->StartPageGroup();
                   $pdf->SetFont('helvetica','B',8);
                   $pdf->Cell($aRiportare['top'][0]['lun'],4,$script_transl['tot'].strftime("%d-%m-%Y",$utsrf).' : ',1,0,'R');
                   $pdf->Cell($aRiportare['top'][1]['lun'],4,$aRiportare['top'][1]['nam'],1,0,'R');
                   $pdf->Cell($aRiportare['top'][2]['lun'],4,$aRiportare['top'][2]['nam'],1,0,'R');
                   $pdf->SetFont('helvetica','',7);
         }
         $aRiportare['top'][1]['nam'] = 0;
         $aRiportare['bot'][1]['nam'] = 0;
         $aRiportare['top'][2]['nam'] = 0;
         $aRiportare['bot'][2]['nam'] = 0;
         $item_head['bot']= array(array('lun' => 21,'nam'=>$mv['id_staff']),
                                  array('lun' => 18,'nam'=>$mv['catmer']),
                                  array('lun' => 60,'nam'=>$mv['desart']),
                                  array('lun' => 10,'nam'=>$mv['unimis']),
                                  array('lun' => 18,'nam'=>number_format($mv['scorta'],1,',',''))
                                  );
        $pdf->setRiporti('');
      }
      $pdf->Cell(20,4,$mv['id_staff'],1,0,'R');
      $pdf->Cell(120,4,$mv['descri'],1,1,'R');
      $ctrlWorker = $mv['id_staff'];
}
$pdf->SetFont('helvetica','B',8);
$pdf->Cell($aRiportare['top'][0]['lun'],4,' : ',1,0,'R');
$pdf->Cell($aRiportare['top'][1]['lun'],4,$aRiportare['top'][1]['nam'],1,0,'R');
$pdf->Cell($aRiportare['top'][2]['lun'],4,$aRiportare['top'][2]['nam'],1,0,'R');
$pdf->SetFont('helvetica','',7);
$pdf->setRiporti('');
$pdf->Output();
?>