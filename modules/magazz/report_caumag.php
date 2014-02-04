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


if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "descri like '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "descri like '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['flag_order'])) {
   $orderby = " codice desc";
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "codice like '$auxil%'";
}
require("../../library/include/header.php");
$script_transl = HeadMain();
require("./lang.".$admin_aziend['lang'].".php");
$script_transl += $strScript["admin_caumag.php"];
print "<div align=\"center\" class=\"FacetFormHeaderFont\"><a href=\"admin_caumag.php?Insert\">".$script_transl['insert'].$script_transl[0]."</a></div>\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl[1].$script_transl[0]."</div>\n";
print "<form method=\"GET\">";
print "<table class=\"Tlarge\">\n";
print "<tr><td></td><td class=\"FacetFieldCaptionTD\">".$script_transl[2].":\n";
print "<input type=\"text\" name=\"auxil\" value=\"";
if ($auxil != "&all=yes"){
    print $auxil;
}
print "\" maxlength=\"6\" size=\"3\" tabindex=\"1\" class=\"FacetInput\"></td>\n";
print "<td><input type=\"submit\" name=\"search\" value=\"".$script_transl['search']."\" tabindex=\"1\" onClick=\"javascript:document.report.all.value=1;\"></td>\n";
print "<td><input type=\"submit\" name=\"all\" value=\"".$script_transl['vall']."\" onClick=\"javascript:document.report.all.value=1;\"></td></tr>\n";
$result = gaz_dbi_dyn_query ("*",$gTables['caumag'], $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_mov = array  (
            $strScript["admin_caumag.php"][1] => "codice",
            $script_transl[2] => "descri",
            $script_transl[11] => "clifor",
            $script_transl[4] => "operat",
            $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_mov);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['caumag'], $where, $limit, $passo);
$recordnav -> output();
while ($a_row = gaz_dbi_fetch_array($result)) {
    print "<tr>\n";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"admin_caumag.php?codice=".$a_row["codice"]."&Update\" title=\"".ucfirst($script_transl['update'])."!\">".$a_row["codice"]."</a> &nbsp</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["descri"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$script_transl[$a_row['clifor']+13]."</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$script_transl[$a_row['operat']+9]."</td>";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_caumag.php?codice=".$a_row["codice"]."\"><img src=\"../../library/images/x.gif\" title=\"".$script_transl['delete']."!\" border=\"0\"></a></td>";
    print "</tr>\n";
}
?>
</table>
</body>
</html>