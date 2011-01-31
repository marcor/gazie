<?php
/*$Id: recalc_exist_value.php,v 1.14 2011/01/01 11:07:46 devincen Exp $
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
if (!isset($_POST['anno'])) { //al primo accesso allo script
    if (isset($_GET['anno'])){
       $form['anno'] = intval($_GET['anno']);
    } else {
       $form['anno'] = date("Y");
    }
} else {
    $form['anno'] = intval($_POST['anno']);
}

if (isset($_POST['insert']) and $msg == "") {  //in caso di conferma
    $result = gaz_dbi_dyn_query("*", $gTables['artico'], 1, " catmer ASC, codice ASC");
    while ($item = gaz_dbi_fetch_array($result)) {
          if (!empty($item['artico'])) {
             $upd_mm->uploadStockValue($item['codice'],$form['anno'],$admin_aziend['stock_eval_method']);
          }
    }
    header("Location:report_movmag.php");
    exit;
}
require("../../library/include/header.php");
$script_transl = HeadMain();
echo "<form method=\"POST\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".ucfirst($script_transl[0])."</div>\n";
echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
$selArray = array('0'=>'FIFO', '1'=>'Prezzo medio ponderato(WMA)', '2'=>'LIFO', '3'=>'FIFO');
echo "<tr><td colspan=\"2\">".$script_transl[2].': '.$selArray[$admin_aziend['stock_eval_method']]."</td></tr>";
echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl[1]."</td><td class=\"FacetDataTD\" colspan=\"3\">";
echo "\t <select name=\"anno\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter =  date("Y")-10; $counter <=  date("Y")+10; $counter++ ){
    $selected = "";
    if($counter == $form['anno'])
            $selected = "selected";
    echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td></tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\"></td><td align=\"right\" colspan=\"4\"  class=\"FacetFooterTD\">
         <input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">&nbsp;<input type=\"submit\" name=\"anteprima\" value=\"".$script_transl['submit']."!\">&nbsp;</td></tr>\n";
echo "</table>\n";
if (isset($_POST['anteprima'])) {
    $result = gaz_dbi_dyn_query("*, COUNT(*) AS num_mov", $gTables['movmag']." LEFT JOIN ".$gTables['artico']." ON ".$gTables['movmag'].".artico = ".$gTables['artico'].".codice" , " YEAR(datreg) = ".$form['anno']." GROUP BY artico", "catmer, artico");
    $numrow = gaz_dbi_num_rows($result);
    echo "<table class=\"Tlarge\">";
    if ($numrow > 0) {
       echo "<tr><td class=\"FacetFieldCaptionTD\" colspan=\"6\" >".$script_transl[3]." $numrow ".$script_transl[4].$form['anno'].":</td></tr>";
       echo "<tr>\n";
       echo "<td align=\"center\">$script_transl[5]</td>\n";
       echo "<td align=\"center\">$script_transl[6]</td>\n";
       echo "<td align=\"center\">$script_transl[7]</td>\n";
       echo "<td align=\"center\">$script_transl[8]/$script_transl[9]</td>\n";
       echo "<td align=\"center\">$script_transl[10]</td>\n";
       echo "<td align=\"center\">$script_transl[11]</td>\n";
       echo "</tr>\n";
       $note=array();
       while ($row = gaz_dbi_fetch_array($result)) {
             if ($upd_mm->ctrlMovYearsAfter($form['anno'],$row["artico"])){
                $new_value=$upd_mm->updateStockValue($row["artico"],$form['anno'],$admin_aziend['stock_eval_method']);
                $class = 'FacetDataTD';
                if (!$new_value){
                   $class = 'FacetDataTDred';
                   $note[2]='';
                   $new_value = $script_transl[12].'(2)';
                } else {
                   $new_value = gaz_format_number($new_value*$row['esiste']);
                }
             } else {
                $class = 'FacetDataTDred';
                $note[1]='';
                $new_value = $script_transl[12].'(1)';
             }
             echo "<tr>\n";
             echo "<td class=\"$class\" align=\"right\">".$row["num_mov"]."</td>\n";
             echo "<td class=\"$class\" align=\"center\">".$row["artico"]."</td>\n";
             echo "<td class=\"$class\">".$row["descri"]."</td>\n";
             echo "<td class=\"$class\" align=\"right\">".gaz_format_quantity($row["esiste"],1,$admin_aziend['decimal_quantity']).' '.$row["uniacq"]."</td>\n";
             echo "<td class=\"$class\" align=\"right\">".gaz_format_number($row["valore"])."</td>\n";
             echo "<td class=\"$class\" align=\"right\">".$new_value."</td>\n";
             echo "</tr>\n";
       }
       if (isset($note[1])) {
           echo "<tr><td colspan=\"6\">$script_transl[13]".$form['anno']."</TD></TR>";
       }
       if (isset($note[2])) {
           echo "<tr><td colspan=\"6\">$script_transl[14]".$form['anno']."</TD></TR>";
       }
    } else {
       echo "<tr><td class=\"FacetDataTDred\" align=\"center\">".$script_transl[15]."</td></tr>";
    }
}
?>
</form>
</body>
</html>