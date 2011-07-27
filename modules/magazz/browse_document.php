<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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
$script_transl = HeadMain(0,array('tiny_mce/tiny_mce',
                                  'boxover/boxover'));

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
} else {
   $auxil = "";
   $where = "table_name_ref LIKE '$auxil%' ";
}

if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "1";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "table_name_ref LIKE '".addslashes($auxil)."%'";
   }
}

if (!isset($_GET['flag_order'])) {
   $orderby = " table_name_ref DESC";
}

print "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title']."</div>\n";
print "<form method=\"GET\">";
print '<table class="Tlarge">';
$result = gaz_dbi_dyn_query ('*',$gTables['files']." LEFT JOIN ".$gTables['artico']." ON ".$gTables['files'].".item_ref = ".$gTables['artico'].".codice", $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_mov = array  (
            "ID" => "id_doc",
            $script_transl['item'] => "item_ref",
            $script_transl['table_name_ref'] => "table_name_ref",
            $script_transl['note'] => "title",
            $script_transl['ext'] => "extension",
            'Download' => "",
            $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_mov);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['files'], $where, $limit, $passo);
$recordnav -> output();
while ($a_row = gaz_dbi_fetch_array($result)) {
    if(!isset($_GET['all']) and !empty($a_row["image"])){
         $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$a_rowr['annota']."] body=[<center><img src='../root/view.php?table=artico&value=".$r['codice']."'>] fade=[on] fadespeed=[0.03] \"";
    } else {
         $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$a_row['annota']."]  fade=[on] fadespeed=[0.03] \"";
    }
    print "<tr>\n";
    print "<td class=\"FacetDataTD\" align=\"right\"><a href=\"admin_document.php?id_doc=".$a_row["id_doc"]."&Update\" title=\"".ucfirst($script_transl['update'])."!\">".$a_row["id_doc"]."</a> &nbsp</td>";
    print "<td class=\"FacetDataTD\" align=\"center\" $boxover >".$a_row["item_ref"]."</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["table_name_ref"]."</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["title"]."</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["extension"]."</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"../root/retrieve.php?id_doc=".$a_row["id_doc"]."\"><img src=\"../../library/images/vis.gif\" title=\"".$script_transl['view']."!\" border=\"0\"></a></td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_document.php?id_doc=".$a_row["id_doc"]."\"><img src=\"../../library/images/x.gif\" title=\"".$script_transl['delete']."!\" border=\"0\"></a></td>\n";
    print "</tr>\n";
}
?>
</table>
</body>
</html>