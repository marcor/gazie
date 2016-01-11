<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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

$admin_aziend = checkAdmin();
$message = "";
if (isset($_GET['auxil'])) {
    $seziva = $_GET['auxil'];
    $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva' GROUP BY protoc, datfat";
} else {
    $seziva = 1;
    $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva' GROUP BY protoc, datfat";
}
if (isset($_GET['protoc'])) {
    if ($_GET['protoc'] > 0) {
        $protocollo = $_GET['protoc'];
        $auxil = $_GET['auxil'] . "&protoc=" . $protocollo;
        $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'  AND protoc = $protocollo GROUP BY protoc, datfat";
        $passo = 1;
    }
} else {
    $protocollo = '';
}
if (isset($_GET['all'])) {
    $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'  GROUP BY protoc, datfat";
    $auxil = $_GET['auxil'] . "&all=yes";
    $passo = 100000;
    $protocollo = '';
}

$titolo = "Documenti d'acquisto";
require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<form method="GET" >
    <div align="center" class="FacetFormHeaderFont"><?php echo $titolo; ?>
        <select name="auxil" class="FacetSelect" onchange="this.form.submit()">
            <?php
            for ($sez = 1; $sez <= 3; $sez++) {
                $selected = "";
                if ($seziva == $sez) {
                    $selected = " selected ";
                }
                echo "<option value=\"" . $sez . "\"" . $selected . ">" . $sez . "</option>";
            }
            ?>
        </select></font>
    </div>
    <?php
    if (!isset($_GET['field']) || ($_GET['field'] == 2) || (empty($_GET['field'])))
        $orderby = "datfat DESC, protoc DESC";
    $recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
    $recordnav->output();
    ?>
    <table class="Tlarge">
        <tr>
            <td colspan="2" class="FacetFieldCaptionTD">
                <input type="text" placeholder="Cerca Prot." class="input-xs form-control" name="protoc" value="<?php if (isset($protocollo)) print $protocollo; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
            </td>
            <td>
                <input type="submit" class="btn btn-xs btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
            </td>
            <td>
                <input type="submit" class="btn btn-xs btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;">
            </td>
        </tr>
        <tr>
            <?php
// creo l'array (header => campi) per l'ordinamento dei record
            $headers_tesdoc = array(
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
            $linkHeaders->output();
            ?>
        </tr>
        <?php
        $rs_last_doc = gaz_dbi_dyn_query("MAX(protoc) AS maxpro, YEAR(datfat) AS y", $gTables['tesdoc'], "tipdoc LIKE 'AF_' AND seziva = '$seziva' GROUP BY y ", 'protoc DESC');
        while ($last_doc = gaz_dbi_fetch_array($rs_last_doc)) {
            $lt_doc[$last_doc['y']] = $last_doc['maxpro'];
        }

//recupero le testate in base alle scelte impostate
        $result = gaz_dbi_dyn_query($gTables['tesdoc'] . ".*," . $gTables['anagra'] . ".ragso1", $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $where, $orderby, $limit, $passo);
        $ctrlprotoc = "";
        while ($a_row = gaz_dbi_fetch_array($result)) {
            $y = substr($a_row['datfat'], 0, 4);
            if ($a_row["tipdoc"] == 'AFA') {
                $tipodoc = "Fattura";
                $modulo = "stampa_docacq.php?id_tes=" . $a_row['id_tes'];
                $modifi = "admin_docacq.php?Update&id_tes=" . $a_row['id_tes'];
            } elseif ($a_row["tipdoc"] == 'AFC') {
                $tipodoc = "Nota Credito";
                $modulo = "stampa_docacq.php?id_tes=" . $a_row['id_tes'];
                $modifi = "admin_docacq.php?Update&id_tes=" . $a_row['id_tes'];
            }

            if ($a_row["protoc"] <> $ctrlprotoc) {
                print "<tr>";
                if (!empty($modifi)) {
                    print "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"" . $modifi . "\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . $a_row["protoc"] . "</td>";
                } else {
                    print "<td class=\"FacetDataTD\"><button class=\"btn btn-xs btn-default btn-edit disabled\">" . $a_row["protoc"] . " &nbsp;</button></td>";
                }
                print "<td class=\"FacetDataTD\">" . $tipodoc . " &nbsp;</td>";
                print "<td class=\"FacetDataTD\">" . $a_row["numfat"] . " &nbsp;</td>";
                print "<td class=\"FacetDataTD\">" . $a_row["datfat"] . " &nbsp;</td>";
                print "<td class=\"FacetDataTD\">" . $a_row["ragso1"] . "&nbsp;</td>";
                if ($a_row["id_con"] > 0) {
                    echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-default\" href=\"../contab/admin_movcon.php?id_tes=" . $a_row["id_con"] . "&Update\">Cont. n." . $a_row["id_con"] . "</a></td>";
                } else {
                    echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-cont\" href=\"accounting_documents.php?type=A&last=" . $a_row["protoc"] . "\">Contabilizza</a></td>";
                }
                print "<td class=\"FacetDataTD\"><a class=\"btn btn-xs btn-default\" href=\"" . $modulo . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a></td>";
                if ($lt_doc[$y] == $a_row['protoc']) {
                    print "<td class=\"FacetDataTD\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_docacq.php?id_tes=" . $a_row["id_tes"] . "\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
                } else {
                    print "<td class=\"FacetDataTD\"><button title=\"Per garantire la sequenza corretta della numerazione, non &egrave; possibile cancellare un documento diverso dall'ultimo\" class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button></td>";
                }
                print "</tr>\n";
            }
            $ctrlprotoc = $a_row["protoc"];
        }
        ?>
</form>
</table>
</div><!-- chiude div container role main --></body>
</html>