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
$aDates = array();
$st_date = strtotime($first_day);
$ed_date = strtotime($last_day);
for ($i = $st_date; $i <= $ed_date; $i += (60 * 60 * 24)) {
	$currDate = array('strdate'=>date('Y-m-d', $i),'daydate'=>date('w',$i),'tsdate'=>$i);
	// in $aDates accumulo i giorni del mese
    $aDates[] = $currDate;
}

$luogo_data=$admin_aziend['citspe'].", lì " . ucwords(strftime("%d %B %Y", mktime (0,0,0,date("m"),date("d"),date("Y"))));

require("../../config/templates/report_template.php");
require("lang.".$admin_aziend['lang'].".php");
$script_transl=$strScript['employee_timesheet.php'];

$where=" work_day BETWEEN '".$first_day."' AND '".$last_day."'";
$what="*";
$tables=$gTables['staff_worked_hours'].' AS wh LEFT JOIN '.$gTables['staff'] . ' AS st ON wh.id_staff=st.id_staff ' . 'LEFT JOIN ' . $gTables['clfoco'] . ' AS wo ON st.id_clfoco=wo.codice ' . 'LEFT JOIN ' . $gTables['anagra'] . ' AS an ON wo.id_anagra=an.id ';
$result = gaz_dbi_dyn_query ($what, $tables,$where,'wh.id_staff ASC, wh.work_day ASC');

$title = array('luogo_data'=>$luogo_data,
               'title'=>$script_transl['title'].' del mese di '.strftime("%B %Y", strtotime($first_day)),
               'hile'=>array(array('lun' => 5,'nam'=>$script_transl['header'][0]),
                             array('lun' => 45,'nam'=>$script_transl['header'][1])
                            )
              );
// accodo a $title l'array dei giorni segnando le domeniche in rosso
for ($i=0; $i<=30; $i++){
	if (isset($aDates[$i])){
		$col=array(255,255,255);
		if ($aDates[$i]['daydate']==0){
			$col=array(255,150,150);
		}
		if ($aDates[$i]['daydate']==6){
			$col=array(255,180,120);
		}
		$title['hile'][]=array('lun'=>7,'nam'=>substr($aDates[$i]['strdate'],-2)."\n". substr(strftime("%A", strtotime($aDates[$i]['strdate'])),0,3), 'col'=>$col);
	} else {
		$title['hile'][]=array('lun'=>7,'nam'=>" \n ");
	}
}
 
$pdf = new Report_template();
$pdf->setVars($admin_aziend,$title);
$pdf->SetTopMargin(51);
$config = new Config;
$pdf->SetFont('helvetica','',7);
$pdf->AddPage('L',$config->getValue('page_format'));
$ctrlWorker='';
$ctrl_id=0;
while ($mv = gaz_dbi_fetch_array($result)) {
	//print_r($mv);
    if ($ctrlWorker!=$mv['id_staff']) {
		if (!empty($ctrlWorker)) { // sono al primo ciclo
    
		}
		$pdf->Cell(5,5,$mv['id_staff'],1,0,'R');
		$pdf->Cell(45,5,$mv['descri'],1,1,'R');
    }
    $ctrlWorker = $mv['id_staff'];
}
$pdf->SetFont('helvetica','',7);
$pdf->setRiporti('');
$pdf->Output();
?>