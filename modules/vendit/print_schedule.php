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
if (!isset($_GET['orderby']) ) {
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit;
}
require("../../config/templates/report_template.php");
$luogo_data=$admin_aziend['citspe'].", lì ".ucwords(strftime("%d %B %Y", mktime (0,0,0,date("m"),date("d"),date("Y"))));
$item_head = array('top'=>array(array('lun' => 80,'nam'=>'Descrizione'),
                                array('lun' => 25,'nam'=>'Numero Conto')
                               )
                   );
$title = array('luogo_data'=>$luogo_data,
               'title'=>"LISTA DELLE PARTITE APERTE ",
               'hile'=>array(   array('lun' => 45,'nam'=>'Cliente'),
                                array('lun' => 20,'nam'=>'ID Partita'),
                                array('lun' => 41,'nam'=>'Descrizione'),
                                array('lun' => 11,'nam'=>'N.Doc.'),
                                array('lun' => 15,'nam'=>'Data Doc.'),
                                array('lun' => 15,'nam'=>'Data Reg.'),
                                array('lun' => 12,'nam'=>'Dare'),
                                array('lun' => 12,'nam'=>'Avere'),
                                array('lun' => 15,'nam'=>'Scadenza')
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
$pdf->setFooterMargin(22);
$pdf->setTopMargin(43);
$pdf->SetFillColor(160, 255,220 );
$pdf->setRiporti('');
$pdf->AddPage();
$config = new Config;
$scdl = new Schedule;
$m = $scdl->getScheduleEntries(intval($_GET['orderby']),$admin_aziend['mascli']);
if (sizeof($scdl->Entries) > 0) {
      $ctrl_partner=0;
      $ctrl_id_tes=0;
      $ctrl_paymov=0;
      while (list($key, $mv) = each($scdl->Entries)) {
          $pdf->SetFont('helvetica','',6);
          $class_partner='';
          $class_paymov='';
          $class_id_tes='';
          $partner='';
          $id_tes='';
          $paymov='';
          if ($mv["clfoco"]<>$ctrl_partner){
              $class_partner='FacetDataTDred';
              $partner=$mv["ragsoc"];
          }
          if ($mv["id_tes"]<>$ctrl_id_tes){
              $class_id_tes='FacetFieldCaptionTD';
              $id_tes=$mv["id_tes"];
              $mv["datdoc"]=gaz_format_date($mv["datdoc"]);
          } else {
              $mv['descri']='';
              $mv['numdoc']='';
              $mv['seziva']='';
              $mv['datdoc']='';
              $class_partner='';
              $partner='';
          }
          if ($mv["id_tesdoc_ref"]<>$ctrl_paymov){
              $paymov=$mv["id_tesdoc_ref"];
              $scdl->getStatus($paymov);
              if($scdl->Status['diff_paydoc']<>0){
                  $status_cl=false;
              } else {
                  $status_cl=true;
              }
          }
          if (empty($mv["numdoc"])){
              $mv["datdoc"]='';
              $mv['seziva']='';
          }
          $pdf->Cell(45,4,$partner,'LTB',0,'',$status_cl,'',1);
          $pdf->Cell(20,4,$paymov,1,0,'R',$status_cl,'',2);
          $pdf->Cell(41,4,$mv['descri'],1,0,'C',$status_cl,'',1);
          $pdf->Cell(11,4,$mv["numdoc"].'/'.$mv['seziva'],1,0,'R',$status_cl);
          $pdf->Cell(15,4,$mv["datdoc"],1,0,'C',$status_cl);
          $pdf->Cell(15,4,gaz_format_date($mv["datreg"]),1,0,'C',$status_cl);
          if ($mv['id_rigmoc_pay']==0){
              $pdf->Cell(12,4,gaz_format_number($mv['amount']),1,0,'R',$status_cl);
              $pdf->Cell(12,4,'',1,0,'R',$status_cl);
          } else {
              $pdf->Cell(12,4,'',1,0,'R',$status_cl);
              $pdf->Cell(12,4,gaz_format_number($mv['amount']),1,0,'R',$status_cl);
          }
          $pdf->Cell(15,4,gaz_format_date($mv["expiry"]),1,1,'C',$status_cl);
          $ctrl_partner=$mv["clfoco"];
          $ctrl_id_tes=$mv["id_tes"];
          $ctrl_paymov=$mv["id_tesdoc_ref"];

      }
}
$pdf->setRiporti('');
$pdf->Output();
?>