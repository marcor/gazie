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
require("./lang.".$admin_aziend['lang'].".php");
$script_transl = $strScript["select_partner_status.php"];


if (!ini_get('safe_mode')){ //se me lo posso permettere...
    ini_set('memory_limit','128M');
    gaz_set_time_limit (0);
}
if (!isset($_GET['date']) ) {
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit;
}
require("../../config/templates/report_template.php");
$luogo_data=$admin_aziend['citspe'].", lì ".ucwords(strftime("%d %B %Y", mktime (0,0,0,date("m"),date("d"),date("Y"))));
$item_head = array('top'=>array(array('lun' => 80,'nam'=>'Descrizione'),
                                array('lun' => 25,'nam'=>'Numero Conto')
                               )
                   );
$acc=array();
foreach($script_transl['header'] as $k=>$v){
    $acc[]=$k;
}
$title = array('luogo_data'=>$luogo_data,
               'title'=>"SCADENZARIO CLIENTI ",
               'hile'=>array(   array('lun' => 28,'nam'=>$acc[0]),
                                array('lun' => 28,'nam'=>$acc[1]),
                                array('lun' => 28,'nam'=>$acc[2]),
                                array('lun' => 28,'nam'=>$acc[3]),
                                array('lun' => 28,'nam'=>$acc[4]),
                                array('lun' => 28,'nam'=>$acc[5]),
                                array('lun' => 18,'nam'=>$acc[6])
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
$pdf->setRiporti('');
$pdf->AddPage();
$paymov = new Schedule;
$paymov->setScheduledPartner($admin_aziend['mascli']);
if (sizeof($paymov->Partners) > 0) {
      $pdf->SetFont('helvetica','',9);
      $anagrafica = new Anagrafica();
      foreach ($paymov->Partners as $p){
          $anagrafica = new Anagrafica();
          $prt = $anagrafica->getPartner($p);
          $pdf->SetFillColor(200,255,200);
          $pdf->Cell(186,5,$prt['ragso1']." ".$prt['ragso2']." tel:".$prt['telefo']." fax:".$prt['fax']." mob:".$prt['cell']." ",1,1,'',1,'',1);
          $paymov->getPartnerStatus($p,substr($_GET['date'],0,10));
          foreach ($paymov->PartnerStatus as $k=>$v){
             $pdf->SetTextColor(255,0,0);
             $pdf->SetFillColor(230,255,230);
             $pdf->Cell(56,5,"REF: ".$k,1,1,'',1,'',1);
             $pdf->SetTextColor(0);
             foreach ($v as $ki=>$vi){
                $pdf->SetFillColor(170,255,170);
                $v_op='';
                if ($vi['op_val']>=0.01){
                   $v_op=gaz_format_number($vi['op_val']);
                }
                $v_cl='';
                if ($vi['cl_val']>=0.01){
                   $v_cl=gaz_format_number($vi['cl_val']);
                }
                $expo='';
                $cl_exp=gaz_format_date($vi['cl_exp']);
                if ($vi['expo_day']>=1){ 
                   $expo=$vi['expo_day'];
                   if ($vi['cl_val']==$vi['op_val']){
                      $vi['status']=2; // la partita è chiusa ma è esposta a rischio insolvenza 
                      $pdf->SetFillColor(255,245,185);
                      $class_paymov='FacetDataTDevidenziaOK';
                   }
                } else {
                   if ($vi['cl_val']==$vi['op_val']){ // chiusa e non esposta
                      $cl_exp='';
                      $pdf->SetFillColor(230,255,230);
                   } elseif($vi['status']==3){ // SCADUTA
                      $cl_exp='';
                      $pdf->SetFillColor(255,160,160);
                   } elseif($vi['status']==9){ // PAGAMENTO ANTICIPATO
                      $pdf->SetFillColor(190,190,255);
                      $vi['expiry']=$vi['cl_exp'];
                   }
                }
                $pdf->Cell(28,4,$vi['id'],'LTB',0,'C',1,'',1);
                $pdf->Cell(28,4,$v_op,1,0,'R',1);
                $pdf->Cell(28,4,gaz_format_date($vi['expiry']),1,0,'C',1,'',1);
                $pdf->Cell(28,4,$v_cl,1,0,'R',1);
                $pdf->Cell(28,4,$cl_exp,1,0,'C',1);
                $pdf->Cell(28,4,$expo,1,0,'C',1);
                $pdf->Cell(18,4,$script_transl['status_value'][$vi['status']],1,1,'C',1);

/*                echo "<tr class='".$class_paymov."'>";
                echo "<td align=\"right\">".$vi['id']."</td>";
                echo "<td align=\"right\">".$v_op."</td>";
                echo "<td align=\"center\">".gaz_format_date($vi['expiry'])."</td>";
                echo "<td align=\"right\">".$v_cl."</td>";
                echo "<td align=\"center\">".$cl_exp."</td>";
                echo "<td align=\"center\">".$expo."</td>";
                echo "<td align=\"center\">".$script_transl['status_value'][$vi['status']]." &nbsp;</td>";
                echo "</tr>\n";*/
             }
          }
      }
}/* else {
   echo "<tr><td class=\"FacetDataTDred\" align=\"center\">".$script_transl['errors'][1]."</TD></TR>\n";
}
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
          $pdf->Cell(45,4,$partner,'LTB',0,'',$status_cl,'',1);
          $pdf->Cell(20,4,$paymov,1,0,'R',$status_cl,'',2);
          $pdf->Cell(41,4,$mv['descri'],1,0,'C',$status_cl,'',1);
          $pdf->Cell(11,4,$mv["numdoc"].'/'.$mv['seziva'],1,0,'R',$status_cl);
          $pdf->Cell(15,4,$mv["datdoc"],1,0,'C',$status_cl);
          $pdf->Cell(15,4,gaz_format_date($mv["datreg"]),1,0,'C',$status_cl);
          if ($mv['id_rigmoc_pay']==0){
              $pdf->Cell(12,4,$mv['amount'],1,0,'R',$status_cl);
              $pdf->Cell(12,4,'',1,0,'R',$status_cl);
          } else {
              $pdf->Cell(12,4,'',1,0,'R',$status_cl);
              $pdf->Cell(12,4,$mv['amount'],1,0,'R',$status_cl);
          }
          $pdf->Cell(15,4,gaz_format_date($mv["expiry"]),1,1,'C',$status_cl);
          $ctrl_partner=$mv["clfoco"];
          $ctrl_id_tes=$mv["id_tes"];
          $ctrl_paymov=$mv["id_tesdoc_ref"];

      }
}*/
$pdf->setRiporti('');
$pdf->Output();
?>