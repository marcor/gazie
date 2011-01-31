<?php
/*$Id: inventory_stock.php,v 1.23 2011/01/01 11:07:46 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
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
$upd_mm = new magazzForm;
$msg='';

if (!isset($_POST['ritorno'])) { //al primo accesso allo script
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['date'] = date("d/m/Y");
    $rs_first = gaz_dbi_dyn_query("codice", $gTables['catmer'],1,"codice ASC",0,1);
    $cm_first = gaz_dbi_fetch_array($rs_first);
    $form['cm_ini'] = $cm_first['codice'];
    $form['cm_fin'] = $cm_first['codice'];
    $utsdate= mktime(0,0,0,intval(substr($form['date'],0,2)),intval(substr($form['date'],3,2)),intval(substr($form['date'],6,4)));
    $date = date("Y-m-d",$utsdate);
    $result = gaz_dbi_dyn_query($gTables['artico'].'.*, '.$gTables['catmer'].'.descri AS descat,'.$gTables['catmer'].'.annota AS anncat', $gTables['artico'].' LEFT JOIN '.$gTables['catmer'].' ON catmer = '.$gTables['catmer'].'.codice', "catmer BETWEEN ".$form["cm_ini"]." AND ".$form["cm_fin"],'catmer ASC, '.$gTables['artico'].'.codice ASC');
    if ($result) {
        while ($r = gaz_dbi_fetch_array($result)) {
              $magval=array_pop($upd_mm->getStockValue(false,$r['codice'],$date));
              $form['a'][$r['codice']]['i_d'] = $r['descri'];
              $form['a'][$r['codice']]['i_u'] = $r['unimis'];
              $form['a'][$r['codice']]['i_e'] = $magval['v'];
              $form['a'][$r['codice']]['i_a'] = $r['annota'];
              $form['a'][$r['codice']]['i_g'] = $r['catmer'];
              $form['a'][$r['codice']]['g_d'] = $r['descat'];
              $form['a'][$r['codice']]['g_a'] = $r['anncat'];
              $form['a'][$r['codice']]['p_e'] = $magval['q_g'];
              $form['a'][$r['codice']]['l_e'] = $magval['v_g'];
              $form['a'][$r['codice']]['t_e'] = $magval['q_g'];
              $form['check'.$r['codice']] = '';
              if ($magval['q_g'] < 0 ){
                 $form['check_'.$r['codice']] = ' checked ';
                 $form['a'][$r['codice']]['col'] = 'red';
                 $form['a'][$r['codice']]['val'] = $magval['q_g'];
              } elseif ($magval['q_g']>0) {
                 $form['check_'.$r['codice']] = ' checked ';
                 $form['a'][$r['codice']]['col'] = '';
                 $form['a'][$r['codice']]['val'] = $magval['q_g'];
              } else {
                 $form['a'][$r['codice']]['col'] = '';
                 $form['a'][$r['codice']]['val'] = $magval['q_g'];
              }
        }
    } else {
    }
} else { //nelle  successive entrate
    if (isset($_POST['Return'])) {
        header("Location: ".$_POST['ritorno']);
        exit;
    }
    $form['date'] = $_POST['date'];
    $form['cm_ini'] = intval($_POST['cm_ini']);
    $form['cm_fin'] = intval($_POST['cm_fin']);
    if ($form['cm_ini'] > $form['cm_fin']) {
         $msg .= "15+";
    }
    $stock = getStock($form['date']);
    $result = gaz_dbi_dyn_query($gTables['artico'].'.*, '.$gTables['catmer'].'.descri AS descat,'.$gTables['catmer'].'.annota AS anncat', $gTables['artico'].' LEFT JOIN '.$gTables['catmer'].' ON catmer = '.$gTables['catmer'].'.codice', "catmer BETWEEN ".$form["cm_ini"]." AND ".$form["cm_fin"],'catmer ASC, '.$gTables['artico'].'.codice ASC');
    if ($result) {
        while ($r = gaz_dbi_fetch_array($result)) {
              if (!isset($stock[$r['codice']])){
                 $stock[$r['codice']]=array(0,0,0);
              }
              $form['a'][$r['codice']]['i_d'] = $r['descri'];
              $form['a'][$r['codice']]['i_u'] = $r['unimis'];
              $form['a'][$r['codice']]['i_e'] = $r['esiste'];
              $form['a'][$r['codice']]['i_a'] = $r['annota'];
              $form['a'][$r['codice']]['i_g'] = $r['catmer'];
              $form['a'][$r['codice']]['g_d'] = $r['descat'];
              $form['a'][$r['codice']]['g_a'] = $r['anncat'];
              $form['a'][$r['codice']]['p_e'] = $stock[$r['codice']][0];
              $form['a'][$r['codice']]['l_e'] = $stock[$r['codice']][1];
              $form['a'][$r['codice']]['t_e'] = $stock[$r['codice']][2];
              $form['a'][$r['codice']]['val'] = floatval($_POST['a'][$r['codice']]['val']);
              if (isset($_POST['check_'.$r['codice']])) {
                  $form['check_'.$r['codice']] = 'checked';
              } else {                                 // se non è stato fatto il check ripristino il valore in base alla nuova data
                  $form['check_'.$r['codice']] = '';
                  $form['a'][$r['codice']]['val'] = floatval($stock[$r['codice']][0]);
              }
              if ($stock[$r['codice']][0] < 0 || $stock[$r['codice']][2] < 0 || $r['esiste'] < 0){
                 $form['a'][$r['codice']]['col'] = 'red';
              } elseif (floatval($stock[$r['codice']][2]) <> $r['esiste']) {
                 $form['a'][$r['codice']]['col'] = 'red';
              } else {
                 $form['a'][$r['codice']]['col'] = '';
              }
        }
        if (isset($_POST['insert'])) {  //in caso di conferma
           $val=0;
           foreach ($_POST as $k=>$v) { //controllo sui dati inseriti e flaggati
                   if (substr($k,0,6) == 'check_') {
                       if (floatval($form['a'][substr($k,6)]['val'])<0) { //se è stato introdotto un valore negativo
                           $msg .= "18-19+";
                       }
                       if (floatval($val + $stock[substr($k,6)][1])<0) { //se l'esistente totale diventa minore di 0 è un errore
                           $msg .= "18-20+";
                       }
                   } else continue;
           }
           if (empty($msg)) {  //se non ci sono errori
              //formatto la data
              $date = intval(substr($form['date'],-4))."-".intval(substr($form['date'],3,2))."-".intval(substr($form['date'],0,2));
              foreach ($_POST as $k=>$v) { //inserisco solo i dati con il flag sul check
                      if (substr($k,0,6) == 'check_') {
                         $val=floatval($form['a'][substr($k,6)]['val'] - $stock[substr($k,6)][0]); //detraggo al val inserito quello dei movimenti fino allla data
                         $operat=1;
                         if ($val<0) {
                            $operat=-1;
                            $val=-$val;
                         }
                         $upd_mm->uploadMag(0,
                                      'INV',
                                      0,
                                      0,
                                      $date,
                                      0,
                                      0,
                                      99, // è il codice per l'inventario
                                      substr($k,6),
                                      $val,
                                      0,
                                      0,
                                      0,
                                      $admin_aziend['stock_eval_method'],
                                      array('datreg'=>$date,'operat'=>$operat,'desdoc'=>'Inventario')
                              );
                       } else continue;
              }
              header("Location: report_movmag.php");
              exit;
           }
        }
    }
}
require("../../library/include/header.php");
echo "<script language=\"JavaScript\" src=\"../../js/boxover/boxover.js\"></script>\n";
echo "<script language=\"JavaScript\" src=\"../../js/calendarpopup/CalendarPopup.js\"></script>\n";
?>
<SCRIPT LANGUAGE="JavaScript" ID="datapopup">
var cal = new CalendarPopup();
</SCRIPT>
<?php
$script_transl=HeadMain();
echo "<form method=\"POST\" name=\"maschera\">\n";
echo "<input type=\"hidden\" name=\"hidden_req\" value=\"\" />";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"".$_POST['ritorno']."\" />";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".ucfirst($script_transl[0])."</div>\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\"> $script_transl[11] ";
echo "<input class=\"FacetFormHeaderFont\" type=\"text\" name=\"date\" value=\"".$form['date']."\" size=\"10\">\n";
echo "<a href=\"#\" onClick=\"cal.select(document.maschera.date,'anchor','dd/MM/yyyy'); return false;\"  title=\"  $script_transl[12]  \" name=\"anchor\" id=\"anchor\">\n";
echo "<img border=\"0\" src=\"../../library/images/cal.png\"></a></div>\n";
echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice( explode('+',chop($msg)),0,-1);
    foreach ($rsmsg as $value){
            $message .= $script_transl['error']."! -> ";
            $rsval = explode('-',chop($value));
            foreach ($rsval as $valmsg){
                    $message .= $script_transl[$valmsg]." ";
            }
            $message .= "<br />";
    }
    echo '<tr><td colspan="5" class="FacetDataTDred">'.$message."</td></tr>\n";
}
echo "<tr>
      <td colspan=\"2\" class=\"FacetDataTD\">".$script_transl[9]."</td>
      <td colspan=\"1\" class=\"FacetFieldCaptionTD\">";
$select_catmer = new selectcatmer("cm_ini");
$select_catmer->addSelected($form["cm_ini"]);
$select_catmer->output('cm');
echo "<td colspan=\"3\" class=\"FacetDataTD\">".$script_transl[10]."</td>
      <td colspan=\"2\" class=\"FacetFieldCaptionTD\">";
$select_catmer = new selectcatmer("cm_fin");
$select_catmer->addSelected($form["cm_fin"]);
$select_catmer->output('cm');
echo "</td></tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td>
         <td class=\"FacetFieldCaptionTD\">$script_transl[2]</td>
         <td class=\"FacetFieldCaptionTD\">$script_transl[3]</td>
         <td class=\"FacetFieldCaptionTD\">$script_transl[4]</td>
         <td class=\"FacetFieldCaptionTD\">$script_transl[5]</td>
         <td class=\"FacetFieldCaptionTD\">$script_transl[6]".$form['date']."</td>
         <td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[8]</td>
         <td class=\"FacetFieldCaptionTD\">$script_transl[7]".$form['date']."</td>
         </tr>\n";
$ctrl_cm=0;
if (isset($form['a'])) {
   foreach($form['a'] as $k=>$v) {
        //ini default value
        $title = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$v['i_a']."] body=[<center><img src='../root/view.php?table=artico&value=".$k."'>] fade=[on] fadespeed=[0.03] \"";
        $class= ' class="FacetDataTD'.$v['col'].'" ';
        // end default value
        if ($ctrl_cm <> $v['i_g']) {
            $cm_title = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$v['g_a']."] body=[<center><img src='../root/view.php?table=catmer&value=".$v['i_g']."'>] fade=[on] fadespeed=[0.03] \"";
            echo "<tr>\n";
            echo "<td></td>";
            echo "<td $cm_title class=\"FacetFieldCaptionTD\" colspan=\"4\" align=\"left\">".$script_transl[13].$v['i_g'].' - '.$v['g_d']."</td>\n";
            echo "</tr>\n";
        }
        echo "<tr>\n";
        if (!isset($form['check_'.$k])){
           $form['check_'.$k]='';
        }
        echo "<td class=\"FacetFieldCaptionTD\" align=\"center\">\n<input type=\"checkbox\" name=\"check_$k\" ".$form['check_'.$k]." ></td>\n";
        echo "<td $title $class align=\"left\">".$k."</td>\n";
        echo "<td $title $class align=\"left\">".$v['i_d']."</td>\n";
        echo "<td $class align=\"center\">".$v['i_u']."</td>\n";
        echo "<td $class align=\"center\" align=\"right\">".gaz_format_quantity($v['i_e'],0,$admin_aziend['decimal_quantity'])."</td>\n";
        echo "<td $class align=\"center\" align=\"right\">".gaz_format_quantity($v['p_e'],0,$admin_aziend['decimal_quantity'])."</td>\n";
        echo "<td $class align=\"right\"><input type=\"text\" style=\"text-align:right\" onchange=\"document.maschera.check_$k.checked=true\" name=\"a[$k][val]\" value=\"".$v['val']."\"></td>\n";
        echo "<td $class align=\"center\" align=\"right\">".gaz_format_quantity($v['l_e'],0,$admin_aziend['decimal_quantity'])."</td>\n";
        echo "</tr>\n";
        $ctrl_cm = $v['i_g'];
   }
   echo "<tr>
      <td  colspan=\"2\" class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">&nbsp;</td>
      <td align=\"right\" colspan=\"5\" class=\"FacetFooterTD\"><input type=\"submit\" name=\"insert\" value=\"".$script_transl['submit']."!\">&nbsp;</td>
      </tr>\n";
} else {
   echo "<tr>
      <td colspan=\"8\" class=\"FacetFormHeaderFont\">".$script_transl[17]."</td>
      </tr>\n";

}
echo "</table>\n";
?>
</form>
</body>
</html>