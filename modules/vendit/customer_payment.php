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

function getMovements($account)
    {
        global $gTables;
        $select = $gTables['tesmov'].".id_tes,".$gTables['tesmov'].".descri AS tesdes,id_rig,datreg,codice,seziva,numdoc,datdoc,".$gTables['clfoco'].".descri,import*(darave='D') AS dare,import*(darave='A') AS avere";
        $table = $gTables['clfoco']." LEFT JOIN ".$gTables['rigmoc']." ON ".$gTables['clfoco'].".codice = ".$gTables['rigmoc'].".codcon "
                    ."LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes ";

        $m=array();
        $rs=gaz_dbi_dyn_query ($select, $table, " codcon = $account "," datreg DESC, darave DESC ");
        while ($r = gaz_dbi_fetch_array($rs)) {
            $m[] = $r;
        }
        return $m;
}



$paymov = new Schedule;
$anagrafica = new Anagrafica();

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['date_ini_D']=date("d");
    $form['date_ini_M']=date("m");
    $form['date_ini_Y']=date("Y");
    $form['search']['partner']='';
    if (isset($_GET['partner'])) {
       $form['partner']=intval($_GET['partner']);
    } else {
       $form['partner']=0;
    }
	$form['target_account']=0;
} else { // accessi successivi
    $form['hidden_req']=htmlentities($_POST['hidden_req']);
    $form['ritorno']=$_POST['ritorno'];
    $form['date_ini_D']=intval($_POST['date_ini_D']);
    $form['date_ini_M']=intval($_POST['date_ini_M']);
    $form['date_ini_Y']=intval($_POST['date_ini_Y']);
    $form['search']['partner']=substr($_POST['search']['partner'],0,20);
    $form['partner']=intval($_POST['partner']);
	$form['target_account']=intval($_POST['target_account']);
    if (isset($_POST['return'])) {
        header("Location: ".$form['ritorno']);
        exit;
    }
}
//controllo i campi
if (!checkdate( $form['date_ini_M'], $form['date_ini_D'], $form['date_ini_Y'])) {
    $msg .='0+';
}
// fine controlli

if (isset($_POST['print']) && $msg=='') {
    $_SESSION['print_request']=array('script_name'=>'print_partner_status',
                                     'date'=>$form['date_ini_Y'].'-'.$form['date_ini_M'].'-'.$form['date_ini_D']
                                     );
    header("Location: sent_print.php");
    exit;
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
    echo '<tr><td colspan="2" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
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
echo "<tr><td class=\"FacetFieldCaptionTD\"> Conto per l'incasso </td>\n ";
echo "<td class=\"FacetFieldCaptionTD\">";
echo "\t <select name=\"target_account\" class=\"FacetSelect\">\n"; //impropriamente usato per il numero di conto d'accredito

$masban = $admin_aziend['masban']*1000000;
$casse = substr($admin_aziend['cassa_'],0,3);
$mascas = $casse*1000000;
$res = gaz_dbi_dyn_query ('*', $gTables['clfoco'], "(codice LIKE '$casse%' AND codice > '$mascas') or (codice LIKE '".$admin_aziend['masban']."%' AND codice > '$masban')", "codice ASC");//recupero i c/c
echo "\t\t <option value=\"0\">--------------------------</option>\n";
while ($a = gaz_dbi_fetch_array($res)) {
    $sel = "";
    if($a["codice"] == $form['target_account']) {
       $sel = "selected";
    }
    echo "\t\t <option value=\"".$a["codice"]."\" $sel >".$a["codice"]." - ".$a["descri"]."</option>\n";
}
echo "\t </select>\n";
echo "</td></tr>";
echo "</table>\n";
if ($form['partner']>100000000) { // partner selezionato
	$paymov->getPartnerAccountingBalance($form['partner']);
	$date=$form['date_ini_Y'].'-'.$form['date_ini_M'].'-'.$form['date_ini_D'];
	$paymov->getPartnerStatus($form['partner']);
	$id_paymov = 0;
	$date_ctrl = new DateTime($date);
	$saldo=0.00;
	echo "<table class=\"Tlarge\">\n";
	echo "<tr>";
	$linkHeaders = new linkHeaders($script_transl['header']);
	$linkHeaders -> output();
	echo "</tr>";
	foreach ($paymov->PartnerStatus as $k=>$v){
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
			}
			$v_cl='';
			if ($vi['cl_val']>=0.01){
				$v_cl=gaz_format_number($vi['cl_val']);
				$cl_exp=gaz_format_date($vi['cl_exp']);
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
			echo "<td align=\"right\">";
			foreach($vi['cl_rig_data'] as $vj){
				echo "<a class=\"btn btn-xs btn-default btn-edit\"  href=\"../contab/admin_movcon.php?id_tes=".$vj['id_tes']."&Update\" title=\"".$script_transl['update'].': '.$vj['descri']." € ".gaz_format_number($vj['import'])."\"><i class=\"glyphicon glyphicon-edit\"></i>".$vj['id_tes']."</a> ";
			}
			echo $v_cl."</td>";
			echo "<td align=\"center\">".$cl_exp."</td>";
			echo "<td align=\"center\">".$expo."</td>";
			echo "<td align=\"center\">".$script_transl['status_value'][$vi['status']]." &nbsp;</td>";
			echo "</tr>\n";
        }
    }
	echo "</table></form>";
}
?>
</body>
</html>