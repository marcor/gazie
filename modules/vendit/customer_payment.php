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
        $select = $gTables['tesmov'].".id_tes,".$gTables['tesmov'].".descri AS tesdes,id_rig,datreg,codice,protoc,numdoc,datdoc,".$gTables['clfoco'].".descri,import*(darave='D') AS dare,import*(darave='A') AS avere";
        $table = $gTables['clfoco']." LEFT JOIN ".$gTables['rigmoc']." ON ".$gTables['clfoco'].".codice = ".$gTables['rigmoc'].".codcon "
                    ."LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes ";

        $m=array();
        $rs=gaz_dbi_dyn_query ($select, $table, " codcon = $account "," datreg DESC, id_tes DESC ");
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
} else { // accessi successivi
    $form['hidden_req']=htmlentities($_POST['hidden_req']);
    $form['ritorno']=$_POST['ritorno'];
    $form['date_ini_D']=intval($_POST['date_ini_D']);
    $form['date_ini_M']=intval($_POST['date_ini_M']);
    $form['date_ini_Y']=intval($_POST['date_ini_Y']);
    $form['search']['partner']=substr($_POST['search']['partner'],0,20);
    $form['partner']=intval($_POST['partner']);
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
echo "<table class=\"Tlarge\">\n";
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
echo "</table>\n";
if ($form['partner']>100000000) { // partner selezionato
   $paymov->getPartnerStatus($form['partner'],$form['date_ini_Y'].'-'.$form['date_ini_M'].'-'.$form['date_ini_D']);
   $res=getMovements($form['partner']);
   $nummov = count($res);
   if ($nummov > 0) {
        $saldo=0.00;
        echo "<table class=\"Tlarge\">\n";
        echo "<tr>";
        $linkHeaders = new linkHeaders($script_transl['header']);
        $linkHeaders -> output();
        echo "</tr>";
        while (list($key, $mv) = each($res)) {
            $paymov->setRigmocEntries($mv["id_rig"]); 
            $saldo += $mv['dare'];
            $saldo -= $mv['avere'];
            echo "<tr><td class=\"FacetDataTD\">".gaz_format_date($mv["datreg"])." &nbsp;</td>";
            echo "<td align=\"center\" class=\"FacetDataTD\"><a href=\"admin_movcon.php?id_tes=".$mv["id_tes"]."&Update\">".$mv["id_tes"]."</a> &nbsp</td>";
            echo "<td class=\"FacetDataTD\">".$mv["tesdes"]." &nbsp;</td>";
            if (!empty($mv['numdoc'])){
                echo "<td align=\"center\" class=\"FacetDataTD\">".$mv["protoc"]." &nbsp;</td>";
                echo "<td align=\"center\" class=\"FacetDataTD\">".$mv["numdoc"]." &nbsp;</td>";
                echo "<td align=\"center\" class=\"FacetDataTD\">".gaz_format_date($mv["datdoc"])." &nbsp;</td>";
            } else {
                echo "<td class=\"FacetDataTD\" colspan=\"3\"></td>";
            }
            echo "<td align=\"right\" class=\"FacetDataTD\">".gaz_format_number($mv['dare'])."</td>";
            echo "<td align=\"right\" class=\"FacetDataTD\">".gaz_format_number($mv['avere'])."</td>";
            echo "<td align=\"right\" class=\"FacetDataTD\">".gaz_format_number($saldo)."</td></tr>\n";
            foreach($paymov->RigmocEntries as $e){
               echo "<tr><td colspan=\"3\"></td>\n";
               echo "<td colspan=\"3\" align=\"center\" class=\"FacetDataTDevidenziaBL\">".$e["id_tesdoc_ref"]."</td>";
               if ($e['id_rigmoc_pay']==0){
                   echo "<td align=\"right\" class=\"FacetDataTDevidenziaBL\">".gaz_format_number($e['amount'])." &nbsp;</td>";
                   echo "<td></td>";
               } else {
                   echo "<td></td>";
                   echo "<td align=\"right\" class=\"FacetDataTDevidenziaBL\">".gaz_format_number($e['amount'])." &nbsp;</td>";
               }
            }
        }
   } else {
       echo "<tr><td colspan=\"6\" class=\"FacetDataTDred\">Non ci sono movimenti contabili relativi al cliente !<td></tr>\n";
   }

}
?>
</body>
</html>