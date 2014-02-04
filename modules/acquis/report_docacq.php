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
$message = "";
if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
   $where = "tipdoc LIKE 'AF_' GROUP BY protoc, datfat";
} else {
   $auxil = 1;
   $where = "tipdoc LIKE 'AF_' GROUP BY protoc, datfat";
}
if (isset($_GET['protoc'])) {
   if ($_GET['protoc'] > 0) {
      $protocollo = $_GET['protoc'];
      $auxil = $_GET['auxil']."&protoc=".$protocollo;
      $where = "tipdoc LIKE 'AF_' AND protoc = $protocollo GROUP BY protoc, datfat";
      $passo = 1;
   }
}  else {
   $protocollo ='';
}
if (isset($_GET['all'])) {
   $where = "tipdoc LIKE 'AF_' GROUP BY protoc, datfat";
   $auxil = $_GET['auxil']."&all=yes";
   $passo = 100000;
   $protocollo ='';
}

$titolo="Documenti d'acquisto";
require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<table border="0" cellpadding="3" cellspacing="1" align="center" width="70%">
<tr>
<td class="FacetFormHeaderFont"><a href="admin_docacq.php?Insert&tipdoc=AFA" accesskey="F">Registra Fattura d'Acquisto Merce</a></td>
<td class="FacetFormHeaderFont"><a href="admin_docacq.php?Insert&tipdoc=AFC" accesskey="N">Registra Nota Credito per Merce</a></td>
<td class="FacetFormHeaderFont"><a href="accounting_documents.php?type=A" accesskey="C">Contabilizzazione fatture</a></td>
</tr>
</table>
<form method="GET" >
<div align="center" class="FacetFormHeaderFont"><?php echo $titolo; ?>
</div>
<?php
if (!isset($_GET['field']) || ($_GET['field'] == 2) || (empty($_GET['field'])))
        $orderby = "datfat DESC, protoc DESC";
$recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<td colspan="2" class="FacetFieldCaptionTD">Protocollo:
<input type="text" name="protoc" value="<?php if (isset($protocollo)) print $protocollo; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
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
            "Prot." => "protoc",
            "Tipo" => "tipdoc",
            "Numero" => "numfat",
            "Data" => "datfat",
            "Fornitore" => "ragso1",
            "Status" => "",
            "Stampa" => "",
            "Cancella" => ""
            );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
$rs_last_doc = gaz_dbi_dyn_query("MAX(protoc) AS maxpro, YEAR(datfat) AS y", $gTables['tesdoc'],"tipdoc LIKE 'AF_' GROUP BY y " ,'protoc DESC');
while ($last_doc = gaz_dbi_fetch_array($rs_last_doc)){
       $lt_doc[$last_doc['y']]=$last_doc['maxpro'];
}

//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesdoc'].".*,".$gTables['anagra'].".ragso1", $gTables['tesdoc']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesdoc'].".clfoco = ".$gTables['clfoco'].".codice LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $where, $orderby,$limit, $passo);
$ctrlprotoc = "";
while ($a_row = gaz_dbi_fetch_array($result)) {
    $y=substr($a_row['datfat'],0,4);
    if ($a_row["tipdoc"] == 'AFA') {
        $tipodoc="Fattura";
        $modulo="stampa_docacq.php?id_tes=".$a_row['id_tes'];
        $modifi="admin_docacq.php?Update&id_tes=".$a_row['id_tes'];
    } elseif ($a_row["tipdoc"] == 'AFC') {
        $tipodoc="Nota Credito";
        $modulo="stampa_docacq.php?id_tes=".$a_row['id_tes'];
        $modifi="admin_docacq.php?Update&id_tes=".$a_row['id_tes'];
    }

    if ($a_row["protoc"] <> $ctrlprotoc)    {
        print "<tr>";
        if (! empty ($modifi)) {
           print "<td class=\"FacetDataTD\"><a href=\"".$modifi."\">".$a_row["protoc"]."</td>";
        } else {
           print "<td class=\"FacetDataTD\">".$a_row["protoc"]." &nbsp;</td>";
        }
        print "<td class=\"FacetDataTD\">".$tipodoc." &nbsp;</td>";
        print "<td class=\"FacetDataTD\">".$a_row["numfat"]." &nbsp;</td>";
        print "<td class=\"FacetDataTD\">".$a_row["datfat"]." &nbsp;</td>";
        print "<td class=\"FacetDataTD\">".$a_row["ragso1"]."&nbsp;</td>";
        if ($a_row["id_con"] > 0) {
           echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"../contab/admin_movcon.php?id_tes=".$a_row["id_con"]."&Update\">Cont. n.".$a_row["id_con"]."</a></td>";
        } else {
           echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"accounting_documents.php?type=A&last=".$a_row["protoc"]."\">Contabilizza</a></td>";
        }
        print "<td class=\"FacetDataTD\"><a href=\"".$modulo."\"><center><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
        if (($lt_doc[$y]==$a_row['protoc']) && ($a_row["id_con"]==0)) {
           print "<td class=\"FacetDataTD\"><a href=\"delete_docacq.php?id_tes=".$a_row["id_tes"]."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
        } else {
           print "<td class=\"FacetDataTD\"></td>";
        }
        print "</tr>\n";
    }
    $ctrlprotoc = $a_row["protoc"];
}
?>
</form>
</table>
</body>
</html>