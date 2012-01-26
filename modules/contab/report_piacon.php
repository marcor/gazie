<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2012 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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

if (!isset($_POST['annrip'])) {
    $_POST['annrip'] = date("Y");
}
// INIZIO determinazione limiti di date
$final_date = intval($_POST['annrip']).'1231';
$rs_last_opening = gaz_dbi_dyn_query("*", $gTables['tesmov'], "caucon = 'APE' AND datreg <= ".$final_date,"datreg DESC",0,1);
$last_opening = gaz_dbi_fetch_array($rs_last_opening);
if ($last_opening) {
   $date_ini = substr($last_opening['datreg'],0,4).substr($last_opening['datreg'],5,2).substr($last_opening['datreg'],8,2);
} else {
   $date_ini = '20040101';
}
// FINE determinazione limiti di date

require("../../library/include/header.php");
$strTransl=HeadMain();
?>
<table border="0" align="center" width="90%">
<tr>
<td class="FacetFormHeaderFont"><a href="admin_piacon.php?Insert"><?php echo $strTransl['ins_this']; ?> </a></td>
<td class="FacetFormHeaderFont"><a href="<?php echo "select_partit.php";?>"><?php echo $strTransl['view_this']; ?> </a></td>
<td class="FacetFormHeaderFont"><a href="stampa_piacon.php"><?php echo $strTransl['print_this']; ?> </a></td>
</tr>
<tr><td colspan="3" class="FacetFormHeaderFont" align="center"><?php echo $strTransl['title']; ?></td></tr>
<tr><td colspan="3" class="FacetDataTDred" ><?php echo $strTransl['msg1']; ?></td></tr>
</table>
<table class="Tlarge">
<tr>
<?php
foreach ($strTransl['header'] as $k=>$v) {
        echo '<th class="FacetFieldCaptionTD">'.$k."</th>\n";
}
?>
</tr>
<form method="POST">
<?php
echo "<tr><td colspan=\"6\" align=\"right\" class=\"FacetDataTD\">".$strTransl['msg2']." : ";
echo "\t <select name=\"annrip\" class=\"FacetSelect\" onchange=\"this.form.submit();\">\n";
for( $counter = date("Y")-3; $counter <= date("Y"); $counter++ ) {
     $selected = "";
     if($counter == $_POST['annrip']) {
        $selected = "selected";
     }
     echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td></tr>\n";
$where = "(codice < ".$admin_aziend['mascli']."000001 OR codice > ".$admin_aziend['mascli']."999999)
       AND (codice < ".$admin_aziend['masfor']."000001 OR codice > ".$admin_aziend['masfor']."999999)";

$select = "SUM(import*(darave='D')) AS dare, SUM(import*(darave='A')) AS avere";
$table = $gTables['rigmoc']." LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes ";
$where2 = " AND datreg BETWEEN $date_ini AND ".$final_date." GROUP BY codcon";

$rs=gaz_dbi_dyn_query ('codice,descri', $gTables['clfoco'], $where, 'codice');
while ($r = gaz_dbi_fetch_array($rs)) {
       $r2=array('dare'=>0,'avere'=>0);
       $rs2=gaz_dbi_dyn_query ($select, $table, 'codcon='.$r['codice'].$where2, 'codcon');
       if ($rs2) {
          $r2=gaz_dbi_fetch_array($rs2);
       }
       if (substr($r["codice"],3) == '000000') {
           echo "<td class=\"FacetDataTD\"><a href=\"admin_piacon.php?Update&codice=".$r["codice"]."\" title=\"Modifica il mastro\" >".substr($r["codice"],0,3)."</a> </td>";
           echo "<td class=\"FacetDataTD\">".substr($r["codice"],3)." </td><td class=\"FacetDataTDred\" colspan=\"5\" >".$r["descri"]." </td>";
           echo "<td class=\"FacetDataTD\"><a href=\"delete_piacon.php?codice=".$r["codice"]."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
           echo "</tr>\n";
       } else {
           echo "<td class=\"FacetDataTD\">".substr($r["codice"],0,3)." </td>";
           echo "<td class=\"FacetDataTD\"><a href=\"admin_piacon.php?Update&codice=".$r["codice"]."\" title=\"Modifica il conto\">".substr($r["codice"],3)."</a> ";
           echo "</td><td class=\"FacetDataTD\">".$r["descri"]." </td>";
           echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($r2["dare"])." </td>";
           echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($r2["avere"])." </td>";
           echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($r2["dare"]-$r2["avere"])." </td>";
           echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"select_partit.php?id=".$r["codice"]."\"><img src=\"../../library/images/vis.gif\" alt=\"Visualizza e stampa il partitario\" border=\"0\"><img src=\"../../library/images/stampa.gif\" alt=\"Visualizza e stampa il partitario\" border=\"0\"></a></td>";
           echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_piacon.php?codice=".$r["codice"]."\"><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
           echo "</tr>\n";
       }
}
?>
</table>
</form>
</body>
</html>