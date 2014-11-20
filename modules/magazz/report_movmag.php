<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
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
$msg = "";
require("../../library/include/header.php");
$script_transl = HeadMain();
require("lang.".$admin_aziend['lang'].".php");

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "caumag LIKE '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "caumag LIKE '".$_GET['auxil']."%'";
   }
}

if (!isset($_GET['flag_order'])) {
   $orderby = " id_mov desc";
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "caumag LIKE '$auxil%'";
}
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">$script_transl[3]$script_transl[0]</div>\n";
echo "<form method=\"GET\">";
echo "<table class=\"Tlarge\">\n";
echo "<tr><td></td><td></td><td class=\"FacetFieldCaptionTD\">".$strScript["admin_movmag.php"][2].":\n";
echo "<input type=\"text\" name=\"auxil\" value=\"";
if ($auxil != "&all=yes"){
    echo $auxil;
}
echo "\" maxlength=\"6\" size=\"3\" tabindex=\"1\" class=\"FacetInput\"></td>\n";
echo "<td><input type=\"submit\" name=\"search\" value=\"".$script_transl['search']."\" tabindex=\"1\" onClick=\"javascript:document.report.all.value=1;\"></td>\n";
echo "<td><input type=\"submit\" name=\"all\" value=\"".$script_transl['vall']."\" onClick=\"javascript:document.report.all.value=1;\"></td></tr>\n";
$table = $gTables['movmag']." LEFT JOIN ".$gTables['caumag']." on (".$gTables['movmag'].".caumag = ".$gTables['caumag'].".codice)
         LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['movmag'].".clfoco = ".$gTables['clfoco'].".codice)
         LEFT JOIN ".$gTables['rigdoc']." ON (".$gTables['movmag'].".id_rif = ".$gTables['rigdoc'].".id_rig)";
$result = gaz_dbi_dyn_query ($gTables['movmag'].".*, ".$gTables['caumag'].".descri AS descau, ".$gTables['rigdoc'].".id_tes AS testata", $table, $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_mov = array  (
            "n.ID" => "id_mov",
            $script_transl[4] => "datreg",
            $strScript["admin_movmag.php"][2] => "caumag",
            $script_transl[8] => "",
            $script_transl[5] => "artico",
            $script_transl[6] => "",
            $script_transl[7] => "",
            $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_mov);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['movmag'], $where, $limit, $passo);
$recordnav -> output();
$anagrafica = new Anagrafica();
while ($a_row = gaz_dbi_fetch_array($result)) {
    $partner = $anagrafica->getPartner($a_row["clfoco"]);
    $title =  $partner['ragso1']." ".$partner['ragso2'];
    $valore = CalcolaImportoRigo($a_row['quanti'], $a_row['prezzo'], $a_row['scorig']) ;
    $valore = CalcolaImportoRigo(1, $valore, $a_row['scochi']) ;
    echo "<tr>\n";
    echo "<td class=\"FacetDataTD\" align=\"right\"><a href=\"admin_movmag.php?id_mov=".$a_row["id_mov"]."&Update\" title=\"".ucfirst($script_transl['update'])."!\">".$a_row["id_mov"]."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($a_row["datreg"])." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["caumag"]." - ".$a_row["descau"]."</td>\n";
    if ($a_row['id_rif'] == 0) {
        echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</td>\n";
    } else {
        if ($a_row['tipdoc'] == "ADT"
         || $a_row['tipdoc'] == "AFA"
         || $a_row['tipdoc'] == "AFC") {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../acquis/admin_docacq.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        } else {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../vendit/admin_docven.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        }
    }
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["artico"]." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_quantity($a_row["quanti"],1,$admin_aziend['decimal_quantity'])."</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($valore)." </td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_movmag.php?id_mov=".$a_row["id_mov"]."\"><img src=\"../../library/images/x.gif\" title=\"".$script_transl['delete']."!\" border=\"0\"></a></td>\n";
    echo "</tr>\n";
}
?>
</table>
</body>
</html>