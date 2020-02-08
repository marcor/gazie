<?php
 /*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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
    scriva   alla   Free  Software Foundation, 51 Franklin Street,
    Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
if (isset($_GET['change_co'])){
    changeEnterprise(intval($_GET['change_co']));
    header("Location: ../root/admin.php");
    exit;
}
$admin_aziend=checkAdmin(7);
require("../../library/include/header.php");
$script_transl = HeadMain();
$table=$gTables['aziend'].' LEFT JOIN '. $gTables['admin_module'].' ON '.$gTables['admin_module'].'.company_id = '.$gTables['aziend'].'.codice';
$where=$gTables['admin_module'].'.adminid=\''.$admin_aziend["user_name"].'\' GROUP BY company_id';
$rs = gaz_dbi_dyn_query ('*',$table,$where, $orderby, $limit, $passo);
//echo '<div align="center" class="FacetFormHeaderFont"><a href="create_new_company.php">'.$script_transl['ins_this']."</a></div>\n";
echo '<div align="center" class="FacetFormHeaderFont">'.$script_transl['title']."</div>\n";
echo '<div class="table-responsive"><table class="Tlarge table table-striped table-bordered table-condensed table-responsive">';
// creo l'array (header => campi) per l'ordinamento dei record
$headers_co = array  (
            $script_transl['codice'] => "codice",
            $script_transl['ragso1'] => "ragso1",
            $script_transl['e_mail'] => "e_mail",
            $script_transl['telefo'] => "telefo",
            $script_transl['regime'] => "regime",
            $script_transl['ivam_t'] => "ivam_t"/*,
            "Avanzate" => ""*/
            );
$linkHeaders = new linkHeaders($headers_co);
$linkHeaders -> output();
$recordnav = new recordnav($table,$where, $limit, $passo);
$recordnav -> output();
echo "<form method=\"GET\" name=\"myform\">\n";
echo "<input type=\"hidden\" name=\"change_co\" value=\"\">\n";
while ($r = gaz_dbi_fetch_array($rs)) {
    $style=" class=\"FacetDataTD\" ";
    if ($r['codice']==$_SESSION['company_id']) {
       $style=" style=\"background:#FF9999;\" ";
       echo "<tr $style>";
       echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-default\" href=\"admin_aziend.php\" title=\"".$script_transl['update']."\" ><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$r["codice"]."</a></td>\n";
       echo "<td title=\"".$r["indspe"].' '.$r["citspe"].' ('.$r["prospe"].")\"><a href=\"admin_aziend.php\" title=\"".$script_transl['update']."\" >".
             $r["ragso1"].' '.$r["ragso2"]." </a></td>\n";
    } else {
       echo "<tr>";
       echo "<td class=\"FacetDataTD\" align=\"center\"><div class=\"clickarea\" style=\"cursor:pointer;\" onclick=\"myform.change_co.value='".$r['codice']."'; myform.submit();\" >".$r["codice"]."</div></td>\n";
       echo "<td $style title=\"".$r["indspe"].' '.$r["citspe"].' ('.$r["prospe"].")\"><div class=\"clickarea\" style=\"cursor:pointer;\" onclick=\"myform.change_co.value='".$r['codice']."'; myform.submit();\" >".
             $r["ragso1"].' '.$r["ragso2"]." </div></td>\n";
    }
    echo "<td $style align=\"center\">".$r['e_mail']." &nbsp;</td>\n";
    echo "<td $style align=\"center\">".$r['telefo']." &nbsp;</td>\n";
    echo "<td $style align=\"center\">".$script_transl['regime_value'][$r["regime"]]." &nbsp;</td>\n";
    echo "<td $style align=\"center\">".$script_transl['ivam_t_value'][$r["ivam_t"]]." &nbsp;</td>\n";
    echo "</tr>\n";
}
?>
</form>
</table></div>
<?php
require("../../library/include/footer.php");
?>