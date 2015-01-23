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
$msg='';
$paymov = new Schedule;
$anagrafica = new Anagrafica();

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
   $form['hidden_req'] = '';
   $form['ritorno'] = $_SERVER['HTTP_REFERER'];
   $form['paymov']=array();
   $form['date_ini_D']=date("d");
   $form['date_ini_M']=date("m");
   $form['date_ini_Y']=date("Y");
   $date=$form['date_ini_Y'].'-'.$form['date_ini_M'].'-'.$form['date_ini_D'];
   $form['search']['partner']='';
   if (isset($_GET['partner'])) {
      $form['partner']=intval($_GET['partner']);
   } else {
      $form['partner']=0;
   }
   $form['target_account']=0;
   $_POST['print_ticket'] = "";

} else { // accessi successivi
   $first=false;
   if (isset($_POST['print_ticket'])) {
     $_POST['print_ticket'] = " checked";
   } else {
     $_POST['print_ticket'] = "";
   }
   $form['hidden_req']=htmlentities($_POST['hidden_req']);
   $form['ritorno']=$_POST['ritorno'];
   if (isset($_POST['paymov'])){
      $desmov='';
      $acc_tot=0.00;
      foreach($_POST['paymov'] as $k=>$v) {
         $form['paymov'][$k] = $v;  // qui dovrei fare il parsing
         $add_desc[$k]=0.00;
         foreach($v as $ki=>$vi) { // calcolo il totale 
            $acc_tot +=$vi['amount'];
            $add_desc[$k]+=$vi['amount'];
         }
         if ($add_desc[$k]>=0.01){ // posso mettere una descrizione perchè il pagamento interessa pure questa partita
            $dd=$paymov->getDocumentData($k);
            $desmov .= ' n.'.$dd['numdoc'].'/'.$dd['seziva'];
         }
     }
     if (strlen($desmov)<=85){ // la descrizione entra in 50 caratteri
         $desmov = 'RISCOSSO x FAT.'.$desmov; 
     } else { // la descrizione è troppo lunga
         $desmov = 'RISCOSSO FINO A FAT.n.'.$dd['numdoc'].'/'.$dd['seziva']; 
     }
     if ($acc_tot<=0){
         $msg .='4+';
     }
   } else if (isset($_POST['ins'])) { // non ho movimenti ma ho chiesto di inserirli
         $msg .='6+';
   }
   $form['date_ini_D']=intval($_POST['date_ini_D']);
   $form['date_ini_M']=intval($_POST['date_ini_M']);
   $form['date_ini_Y']=intval($_POST['date_ini_Y']);
   $date=$form['date_ini_Y'].'-'.$form['date_ini_M'].'-'.$form['date_ini_D'];
   $form['search']['partner']=substr($_POST['search']['partner'],0,20);
   $form['partner']=intval($_POST['partner']);
   $form['target_account']=intval($_POST['target_account']);
   if (isset($_POST['return'])) {
       header("Location: ".$form['ritorno']);
       exit;
   }
   //controllo i campi
   if (!checkdate( $form['date_ini_M'], $form['date_ini_D'], $form['date_ini_Y'])) {
      $msg .='0+';
   }
   if (isset($_POST['ins']) && $form['target_account']<100000001) {
      $msg='5+';
   }
   // fine controlli
   if (isset($_POST['ins']) && $msg=='') {
      $tes_val=array('caucon'=>'',
               'descri'=>$desmov,
               'datreg'=>$date,
               'clfoco'=>$form['partner']
               );
      tesmovInsert($tes_val);
      $tes_id = gaz_dbi_last_id();
      rigmocInsert(array('id_tes'=>$tes_id,'darave'=>'D','codcon'=>$form['target_account'],'import'=>$acc_tot));
      rigmocInsert(array('id_tes'=>$tes_id,'darave'=>'A','codcon'=>$form['partner'],'import'=>$acc_tot));
      $rig_id = gaz_dbi_last_id();
      foreach($form['paymov'] as $k=>$v) { //attraverso l'array delle partite
         $acc=0.00;
         foreach($v as $ki=>$vi) {
            $acc +=$vi['amount'];
         }
         if ($acc>=0.01){
            paymovInsert(array('id_tesdoc_ref'=>$k,'id_rigmoc_pay'=>$rig_id,'amount'=>$acc,'expiry'=>$date));
         }
      }
      if ($_POST['print_ticket']==" checked" ) {
         $_SESSION['print_request']=array('script_name'=>'print_customer_payment_receipt','id_rig'=>$rig_id);
         header("Location: sent_print.php");
         exit;
      }
      header("Location: report_schedule.php");
      exit;
   }
}
require("../../library/include/header.php");
$script_transl = HeadMain(0,array('jquery/jquery-1.7.1.min','calendarpopup/CalendarPopup',
                                  'jquery/ui/jquery.ui.core',
                                  'jquery/ui/jquery.ui.widget',
                                  'jquery/ui/jquery.ui.position',
                                  'jquery/ui/jquery.ui.autocomplete'));
echo '<SCRIPT type="text/javascript">
      $(function() {
           $( "#search_partner" ).autocomplete({
           source: "../../modules/root/search.php",
           minLength: 2,
           });})';
echo "           
var cal = new CalendarPopup();
var calName = '';
function setMultipleValues(y,m,d) {
     document.getElementById(calName+'_Y').value=y;
     document.getElementById(calName+'_M').selectedIndex=m*1-1;
     document.getElementById(calName+'_D').selectedIndex=d*1-1;
}
function setDate(name) {
  calName = name.toString();
  var year = document.getElementById(calName+'_Y').value.toString();
  var month = document.getElementById(calName+'_M').value.toString();
  var day = document.getElementById(calName+'_D').value.toString();
  var mdy = month+'/'+day+'/'+year;
  cal.setReturnFunction('setMultipleValues');
  cal.showCalendar('anchor', mdy);
}
</script>
";
echo "<form method=\"POST\" name=\"select\">\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\" />\n";
$gForm = new venditForm();
echo "<br /><div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title'];
echo "</div>\n";
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['mesg'])."</td></tr>\n";
}
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['date_ini']."</td><td colspan=\"3\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('date_ini',$form['date_ini_D'],$form['date_ini_M'],$form['date_ini_Y'],'FacetSelect',1);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['partner']."</td><td colspan=\"3\" class=\"FacetDataTD\">\n";
$gForm->selectCustomer('partner',$form['partner'],$form['search']['partner'],$form['hidden_req'],$script_transl['mesg']);
echo "</td>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['target_account']."</td>\n ";
echo "<td class=\"FacetFieldCaptionTD\">";
echo "\t <select name=\"target_account\" tabindex=\"4\"   class=\"FacetSelect\" onchange=\"this.form.submit()\">\n"; //impropriamente usato per il numero di conto d'accredito

$masban = $admin_aziend['masban']*1000000;
$casse = substr($admin_aziend['cassa_'],0,3);
$mascas = $casse*1000000;
$res = gaz_dbi_dyn_query ('*', $gTables['clfoco'], "(codice LIKE '$casse%' AND codice > '$mascas') or (codice LIKE '".$admin_aziend['masban']."%' AND codice > '$masban')", "codice ASC");//recupero i c/c
echo "\t\t <option value=\"0\">--------------------------</option>\n";
while ($a = gaz_dbi_fetch_array($res)) {
    $sel = "";
    if($a["codice"] == $form['target_account']) {
       $sel = "selected";
       if(substr($a["codice"],0,3) == $casse) { // è un pagamento in contanti/assegno
          $_POST['print_ticket'] = " checked";
       } else {
          $_POST['print_ticket'] = "";
       }
    }
    echo "\t\t <option value=\"".$a["codice"]."\" $sel >".$a["codice"]." - ".$a["descri"]."</option>\n";
}
echo "\t </select></td>\n";
echo "<td class=\"FacetDataTD\">".$script_transl['print_ticket']."<input type=\"checkbox\" title=\"Per stampare la ricevuta seleziona questa checkbox\" name=\"print_ticket\" ".$_POST['print_ticket']." ></td>\n";
echo "</tr>";
echo "</table>\n";
if ($form['partner']>100000000) { // partner selezionato
   // ottengo il valore del saldo contabile per confrontarlo con quello dello scedenziario
   $acc_bal=$paymov->getPartnerAccountingBalance($form['partner'],$date);
   $paymov->getPartnerStatus($form['partner'],$date);
   $kd_paymov = 0;
   $date_ctrl = new DateTime($date);
   $saldo=0.00;
   echo "<table class=\"Tlarge\">\n";
   echo "<tr>";
   echo "<td colspan='8'>".$script_transl['accbal'].gaz_format_number($acc_bal)."</td>";
   echo "<tr>";
   $linkHeaders = new linkHeaders($script_transl['header']);
   $linkHeaders -> output();
   echo "</tr>\n";
   $paymov_bal=0.00;
   foreach ($paymov->PartnerStatus as $k=>$v){
      $amount=0.00;
      echo "<tr>";
      echo "<td class=\"FacetDataTD\" colspan='8'><a class=\"btn btn-xs btn-default btn-edit\" href=\"../contab/admin_movcon.php?Update&id_tes=".$paymov->docData[$k]['id_tes']."\"><i class=\"glyphicon glyphicon-edit\"></i>".
      $paymov->docData[$k]['descri'].' n.'.
      $paymov->docData[$k]['numdoc'].'/'.
      $paymov->docData[$k]['seziva'].' '.
      $paymov->docData[$k]['datdoc']."</a> REF: $k</td>";
      echo "</tr>\n";
      foreach ($v as $ki=>$vi){
         $class_paymov='FacetDataTDevidenziaCL';
         $v_op='';
         $cl_exp='';
         if ($vi['op_val']>=0.01){
            $v_op=gaz_format_number($vi['op_val']);
            $paymov_bal+=$vi['op_val'];
         }
         $v_cl='';
         if ($vi['cl_val']>=0.01){
            $v_cl=gaz_format_number($vi['cl_val']);
            $cl_exp=gaz_format_date($vi['cl_exp']);
            $paymov_bal-=$vi['cl_val'];
         }
         $expo='';
         if ($vi['expo_day']>=1){ 
            $expo=$vi['expo_day'];
            if ($vi['cl_val']==$vi['op_val']){
               $vi['status']=2; // la partita è chiusa ma è esposta a rischio insolvenza 
               $class_paymov='FacetDataTDevidenziaOK';
            }	
         } else {
            if ($vi['cl_val']==$vi['op_val']){ // chiusa e non esposta
               $cl_exp='';
               $class_paymov='FacetDataTD';
            } elseif($vi['status']==3){ // SCADUTA
               $cl_exp='';
               $class_paymov='FacetDataTDevidenziaKO';
            } elseif($vi['status']==9){ // PAGAMENTO ANTICIPATO
               $class_paymov='FacetDataTDevidenziaBL';
               $vi['expiry']=$vi['cl_exp'];
            }
         }
         echo "<tr class='".$class_paymov."'>";
         echo "<td align=\"right\">".$vi['id']."</td>";
         echo "<td align=\"right\">".$v_op."</td>";
         echo "<td align=\"center\">".gaz_format_date($vi['expiry'])."</td>";
         echo "<td align=\"right\">\n";
         foreach($vi['cl_rig_data'] as $vj){
            echo "<a class=\"btn btn-xs btn-default btn-edit\"  href=\"../contab/admin_movcon.php?id_tes=".$vj['id_tes']."&Update\" title=\"".$script_transl['update'].': '.$vj['descri']." € ".gaz_format_number($vj['import'])."\"><i class=\"glyphicon glyphicon-edit\"></i>".$vj['id_tes']."</a>\n ";
         }
         echo $v_cl."</td>";
         echo "<td align=\"center\">".$cl_exp."</td>";
         echo "<td align=\"center\">".$expo."</td>";
         echo "<td align=\"center\">".$script_transl['status_value'][$vi['status']]." &nbsp;</td>";
         if($vi['status']<>1 || $vi['status']<9 ) { // accumulo solo se non è chiusa
                $amount+=round($vi['op_val']-$vi['cl_val'],2);
         }
         echo "</tr>\n";
      }
      if(!isset($_POST['paymov'])){ 
         $form['paymov'][$k][$ki]['amount']=$amount;
         $form['paymov'][$k][$ki]['id_tesdoc_ref']=$k;
      }
      echo '<input type="hidden" id="post_'.$k.'_'.$ki.'_id_tesdoc_ref" name="paymov['.$k.']['.$ki.'][id_tesdoc_ref]" value="'.$k."\" />";
      echo "<tr><td colspan='7'></td><td align='right'><input style=\"text-align: right;\" type=\"text\" name=\"paymov[$k][$ki][amount]\" value=\"".$form['paymov'][$k][$ki]['amount']."\"></td></tr>\n";

   }
   echo "<tr>";
   echo "<td colspan='3'>".$script_transl['paymovbal'].gaz_format_number($paymov_bal)."</td>";
   if ($paymov_bal<$acc_bal){
      echo "<td class=\"FacetDataTDred\" colspan='4'>".$script_transl['mesg'][3]." <a class=\"btn btn-xs btn-default btn-edit\" href=\"../contab/admin_movcon.php?Insert\"><i class=\"glyphicon glyphicon-edit\"> </i></td>";
   }
   echo '<td class="FacetFieldCaptionTD" align="center"><input name="ins" id="preventDuplicate" onClick="chkSubmit();" onClick="chkSubmit();" type="submit" value="'.strtoupper($script_transl['insert']).'!"></td>';
   echo "<tr>";
   echo "</table></form>";
}
?>
</body>
</html>