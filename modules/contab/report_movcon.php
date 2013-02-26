<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/header.php");

function getDocRef($data){
    global $gTables;
    $r='';
    switch ($data['caucon']) {
        case "FAI":
        case "FND":
        case "FNC":
            $tesdoc_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],
                                                "id_con = ".$data["id_tes"],
                                                'id_tes DESC',0,1);
            $tesdoc_r = gaz_dbi_fetch_array ($tesdoc_result);
            if ($tesdoc_r) {
                $r="../vendit/stampa_docven.php?id_tes=".$tesdoc_r["id_tes"];
            }
        break;
        case "FAD":
            $tesdoc_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],
                                                "tipdoc = \"".$data["caucon"]."\" AND seziva = ".$data["seziva"]." AND protoc = ".$data["protoc"]." AND numfat = '".$data["numdoc"]."' AND datfat = \"".$data["datdoc"]."\"",
                                                'id_tes DESC');
            $tesdoc_r = gaz_dbi_fetch_array ($tesdoc_result);
            if ($tesdoc_r) {
                $r="../vendit/stampa_docven.php?td=2&si=".$tesdoc_r["seziva"]."&pi=".$tesdoc_r['protoc']."&pf=".$tesdoc_r['protoc']."&di=".$tesdoc_r["datfat"]."&df=".$tesdoc_r["datfat"] ;
            }
        break;
        case "RIB":
        case "TRA":
            $effett_result = gaz_dbi_dyn_query ('*',$gTables['effett'],"id_con = ".$data["id_tes"],'id_tes',0,1);
            $effett_r = gaz_dbi_fetch_array ($effett_result);
            if ($effett_r) {
                $r="../vendit/stampa_effett.php?id_tes=".$effett_r["id_tes"];
            }
        break;
    }
    return $r;
}

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "caucon like '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "caucon like '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['flag_order'])) {
   $orderby = " id_tes desc";
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "caucon like '$auxil%'";
}
$script_transl=HeadMain('','','admin_movcon');
?>
<div align="center" class="FacetFormHeaderFont"><a href="admin_movcon.php?Insert"><?php echo $script_transl['ins_this']; ?></a></div>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['report']; ?></div>
<form method="GET">
<table class="Tlarge">
<tr>
<td></td>
<td colspan="2" align="right" class="FacetFieldCaptionTD"><?php echo $script_transl['caucon']; ?>:
<input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") print $auxil; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" value="<?php echo $script_transl['search']; ?>" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td>
<input type="submit" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<?php
$table = $gTables['rigmoc']." LEFT JOIN ".$gTables['tesmov']." ON (".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes) ";
$result = gaz_dbi_dyn_query ($gTables['rigmoc'].".id_tes, datreg, clfoco, caucon, descri, protoc, numdoc, seziva, datdoc, sum(import*(darave='D')) as dare,sum(import*(darave='A')) as avere", $table, $where." group by id_tes", $orderby, $limit, $passo);
$headers_tesmov = array  (
            "N." => "id_tes",
            $script_transl['date_reg']=>"datreg",
            $script_transl['caucon']=>"caucon",
            $script_transl['descri']=>"descri",
            $script_transl['protoc']=>"",
            $script_transl['numdoc']=>"",
            $script_transl['amount']=>"",
            $script_transl['source']=> "",
            $script_transl['delete']=>""
            );
$linkHeaders = new linkHeaders($headers_tesmov);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['tesmov'], $where, $limit, $passo);
$recordnav -> output();
$anagrafica = new Anagrafica();
while ($a_row = gaz_dbi_fetch_array($result)) {
    if (substr($a_row["clfoco"],0,3) == $admin_aziend['mascli'] or substr($a_row["clfoco"],0,3) == $admin_aziend['masfor']) {
       $partner = $anagrafica->getPartner($a_row["clfoco"]);
       $title =  $partner['ragso1']." ".$partner['ragso2'];
    } else {
       $title = "";
    }
    print "<tr>";
    print "<td class=\"FacetDataTD\" align=\"right\"><a href=\"admin_movcon.php?id_tes=".$a_row["id_tes"]."&Update\" title=\"Modifica\">".$a_row["id_tes"]."</a> &nbsp</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($a_row["datreg"])." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" title= \"$title\" align=\"center\">".$a_row["caucon"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" title= \"$title\">".$a_row["descri"]." &nbsp;</td>";
    if ($a_row["protoc"] > 0) {
       print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["protoc"]."/".$a_row["seziva"]."";
       print "</td>";
    } else {
       print "<td class=\"FacetDataTD\"></td>";
    }
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["numdoc"]."</td>";
    print "<td class=\"FacetDataTD\" title= \"$title\" align=\"right\">".gaz_format_number($a_row['dare'])." </td>";
    print "<td class=\"FacetDataTD\" align=\"center\">";
    $docref=getDocRef($a_row);
    if (!empty($docref)){
      echo "<a title=\"".$script_transl['sourcedoc']."\" href=\"$docref\"><img src=\"../../library/images/stampa.gif\" border=\"0\"></a>";
    }
    print "</td>";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_movcon.php?id_tes=".$a_row["id_tes"]."\"><img src=\"../../library/images/x.gif\" title=\"".$script_transl['delete']."!\" border=\"0\"></a></td>";
    print "</tr>\n";
}
?>
</table>
</body>
</html>