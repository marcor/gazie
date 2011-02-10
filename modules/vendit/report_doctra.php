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
$message = "";
$anno = date("Y");
if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
} else {
   $auxil = 1;
}
$where = " (tipdoc = 'FAD' or tipdoc like 'DD_') and seziva = '$auxil'";
$documento ='';
if (isset($_GET['numdoc'])) {
   if ($_GET['numdoc'] > 0) {
      $documento = $_GET['numdoc'];
      $auxil = $_GET['auxil']."&numdoc=".$documento;
      $where = " (tipdoc = 'FAD' or tipdoc like 'DD_') and seziva = '$auxil' and numdoc = '$documento'";
      $passo = 1;
   }
}
if (isset($_GET['all'])) {
   set_time_limit (240);
   $auxil = $_GET['auxil']."&all=yes";
   $passo = 100000;
   $where = " (tipdoc = 'FAD' or tipdoc like 'DD_') and seziva = '$auxil'";
}

$titolo="Documenti di trasporto";
require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<div align="center" class="FacetFormHeaderFont"><a href="admin_docven.php?Insert&seziva=<?php echo substr($auxil,0,1); ?>&tipdoc=DDT" accesskey="d">Emetti Documento di Trasporto</a></div>
<form method="GET">
<div align="center" class="FacetFormHeaderFont"> D.d.T. della sezione
<select name="auxil" class="FacetSelect" onchange="this.form.submit()">
<?php
for ($sez = 1; $sez <= 3; $sez++) {
     $selected="";
     if(substr($auxil,0,1) == $sez)
        $selected = " selected ";
     echo "<option value=\"".$sez."\"".$selected.">".$sez."</option>";
}
?>
</select>
</div>
<?php
if (!isset($_GET['field']) or ($_GET['field'] == 2) or(empty($_GET['field'])))
        $orderby = "datemi desc, numdoc desc";
$recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<td colspan="2" class="FacetFieldCaptionTD">Numero:
<input type="text" name="numdoc" value="<?php if ($documento > 0) print $documento; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td>
<input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
              "Numero" => "numdoc",
              "Data" => "datemi",
              "Cliente" => "ragso1",
              "Status" => "",
              "Stampa" => "",
              "Origine" => "",
              "Cancella" => ""
              );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
$rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where,"datemi desc, numdoc desc",0,1);
$ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
if ($ultimo_documento)
    $ultimoddt = $ultimo_documento['numdoc'];
else
    $ultimoddt = 1;
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesdoc'].".*,".$gTables['anagra'].".ragso1", $gTables['tesdoc']."
                            LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesdoc'].".clfoco = ".$gTables['clfoco'].".codice
                            LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra",
                            $where, $orderby,$limit, $passo);
while ($r = gaz_dbi_fetch_array($result)) {
    switch($r['tipdoc']) {
    case "DDT":
    echo "<tr>";
    echo "<td class=\"FacetDataTD\" align=\"right\"><a href=\"admin_docven.php?Update&id_tes=".$r["id_tes"]."\">".$r["numdoc"]."</a> &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$r["ragso1"]."&nbsp;</td>";
    if ($r['numfat'] > 0) {
        echo "<td class=\"FacetDataTD\" align=\"center\"><a title=\"stampa la fattura differita n. ".$r["numfat"]."\" href=\"stampa_docven.php?td=2&si=".$r["seziva"]."&pi=".$r['protoc']."&pf=".$r['protoc']."&di=".$r['datfat']."&df=".$r['datfat']."\">fatt. n. ".$r["numfat"]."</a></td>";
        if ($r["id_con"] > 0) {
            echo ", <a title=\"visualizza la registrazione contabile della fattura differita\" href=\"../contab/admin_movcon.php?id_tes=".$r["id_con"]."&Update\">cont. n.".$r["id_con"]."</a>";
        }
    } else {
        echo "<td class=\"FacetDataTD\" align=\"center\"><a title=\"fattuazione da d.d.t.\" href=\"emissi_fatdif.php\">da fatturare</a></td>";
    }
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"stampa_docven.php?id_tes=".$r["id_tes"]."&template=DDT\"><center><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">";
    $rigbro_result = gaz_dbi_dyn_query ('*',$gTables['rigbro'],"id_doc = ".$r['id_tes']." GROUP BY id_doc",'id_tes');
    while ($rigbro_r = gaz_dbi_fetch_array ($rigbro_result)) {
          $r_d = gaz_dbi_get_row($gTables['tesbro'],"id_tes",$rigbro_r["id_tes"]);
          if ($r_d["id_tes"] > 0) {
             echo " <a title=\"visualizza l'Ordine\" href=\"stampa_ordcli.php?id_tes=".$r_d['id_tes']."\" style=\"font-size:10px;\">Ord.".$r_d['numdoc']."</a>\n";
          }
    }
    echo "</td>";
    if ($ultimoddt == $r["numdoc"] and $r['numfat'] == 0)
       echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_docven.php?id_tes=".$r["id_tes"]."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
    else
        echo "<td class=\"FacetDataTD\" align=\"center\"></td>";
    echo "</tr>\n";
    break;
    case "DDR":
    case "DDL":
    echo "<tr>";
    echo "<td class=\"FacetDataTD\" align=\"right\"><a href=\"../acquis/admin_docacq.php?id_tes=".$r["id_tes"]."&Update\">".$r["numdoc"]."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTDred\" align=\"center\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
    echo "<td class=\"FacetDataTDred\">".$r["ragso1"]."&nbsp;</td>";
    echo "<td class=\"FacetDataTDred\" align=\"center\">D.d.T. a Fornitore &nbsp;</td>";
    echo "<td class=\"FacetDataTDred\" align=\"center\"><a href=\"stampa_docven.php?id_tes=".$r["id_tes"]."&template=DDT\"><center><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\" align=\"center\"></td>";
    if ($ultimoddt == $r["numdoc"] and $r['numfat'] == 0)
       echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_docven.php?id_tes=".$r["id_tes"]."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
    else
        echo "<td class=\"FacetDataTD\" align=\"center\"></td>";
    echo "</tr>\n";
    break;
    case "FAD":
    echo "<tr>";
    echo "<td class=\"FacetDataTD\" align=\"right\"><a href=\"admin_docven.php?Update&id_tes=".$r["id_tes"]."\">".$r["numdoc"]."</a> &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$r["ragso1"]."&nbsp;</td>";

    echo "<td class=\"FacetDataTD\" align=\"center\"><a title=\"stampa la fattura differita n. ".$r["numfat"]."\" href=\"stampa_docven.php?td=2&si=".$r["seziva"]."&pi=".$r['protoc']."&pf=".$r['protoc']."&di=".$r['datfat']."&df=".$r['datfat']."\">Fat ".$r["numfat"]."</a>";
    if ($r["id_con"] > 0) {
        echo ", <a title=\"visualizza la registrazione contabile della fattura differita\" href=\"../contab/admin_movcon.php?id_tes=".$r["id_con"]."&Update\">Cont ".$r["id_con"]."</a>";
    }
    echo "</td>";

    echo "<td class=\"FacetDataTD\" align=\"center\"><a title=\"stampa il documento di trasporto n. ".$r["numdoc"]."\" href=\"stampa_docven.php?id_tes=".$r["id_tes"]."&template=DDT\"><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">";
    $rigbro_result = gaz_dbi_dyn_query ('*',$gTables['rigbro'],"id_doc = ".$r['id_tes']." GROUP BY id_doc",'id_tes');
    while ($rigbro_r = gaz_dbi_fetch_array ($rigbro_result)) {
          $r_d = gaz_dbi_get_row($gTables['tesbro'],"id_tes",$rigbro_r["id_tes"]);
          if ($r_d["id_tes"] > 0) {
             echo "<a title=\"visualizza l'Ordine\" href=\"stampa_ordcli.php?id_tes=".$r_d['id_tes']."\" style=\"font-size:10px;\">Ord.".$r_d['numdoc']."</a>\n";
          }
    }
    echo "</td>";
    echo "<td class=\"FacetDataTD\"></td>";
    echo "</tr>\n";
    break;
    }
}
?>
</form>
</table>
</body>
</html>