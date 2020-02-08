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

$admin_aziend = checkAdmin();
if (isset($_GET["auxil"]))
    $sezione = $_GET["auxil"];
else
    $sezione = 1;
$where = "(tipdoc = 'DDL' OR tipdoc = 'RDL' OR tipdoc LIKE 'DDR' OR tipdoc LIKE 'ADT' ) AND seziva = $sezione";
$all = $where;
$anno = date("Y");

if (!isset($_GET['auxil']))
    $_GET['auxil'] = 1;
require("../../library/include/header.php");
$script_transl = HeadMain();

if (isset($_GET['flt_tipo']) && $_GET['flt_tipo'] != "All") {
    if ($_GET['flt_tipo'] != "") {
        $tipdoc = $_GET['flt_tipo'];
        $where = " tipdoc like '%$tipdoc%'";
    }
} else {
    $tipdoc = '';
}
if (isset($_GET['protoc'])) {
    if ($_GET['protoc'] > 0) {
        $protocollo = $_GET['protoc'];
        $where = " id_tes = $protocollo";
    }
} else {
    $protocollo = '';
}
if (isset($_GET['numdoc'])) {
    if ($_GET['numdoc'] != "") {
        $numdoc = $_GET['numdoc'];
        $where .= " AND numdoc like '%$numdoc%'";
    }
} else {
    $numdoc = '';
}
if (isset($_GET['flt_year'])) {
    if ($_GET['flt_year'] != "" && $_GET['flt_year'] != "All") {
        $year = $_GET['flt_year'];
        $auxil = $_GET['auxil'] . "&datfat=" . $year;
        $where .= " and datemi >= \"" . $year . "/01/01\" and datemi <= \"" . $year . "/12/31\"";
    } else {
        $year = 'All';
    }
} else {
    $year = 'All';
}
if (isset($_GET['flt_ragso1'])) {
    if ($_GET['flt_ragso1'] != "") {
        $ragso1 = $_GET['flt_ragso1'];
        if ($ragso1 != "All") {
            $auxil = $_GET['auxil'] . "&ragso1=" . $ragso1;
            $where .= " and " . $gTables["tesdoc"] . ".clfoco = " . $ragso1;
        } else {
			$passo = 100000;			
		}
    }
} else {
    $ragso1 = '';
}
if (isset($_GET['all'])) {
    $year = "";
    $numdoc = "";
    $tipdoc = "";
    $ragso1 = "";
    $protocollo = "";
	$passo = 100000;
	$where = "(tipdoc = 'DDL' OR tipdoc LIKE 'DDR' OR tipdoc LIKE 'ADT' ) AND seziva = $sezione";
    $auxil = $_GET['auxil'] . "&all=yes";
}
?>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>"  name="auxil">
    <div align="center" class="FacetFormHeaderFont"> <?php echo $script_transl['title']; ?>
        <select name="auxil" class="FacetSelect" onchange="this.form.submit()">
            <?php
            for ($sez = 1; $sez <= 9; $sez++) {
                $selected = "";
                if ($_GET["auxil"] == $sez)
                    $selected = " selected ";
                echo "<option value=\"" . $sez . "\"" . $selected . ">" . $sez . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
    if (!isset($_GET['flag_order'])) {
        $orderby = "datemi desc, numdoc desc";
    }
    $recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
    $recordnav->output();
    ?>
	<div class="table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
        <tr>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="text" placeholder="Cerca Prot." class="input-sm form-control" name="protoc" value="<?php if (isset($protocollo)) echo $protocollo; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <select class="form-control input-sm" name="flt_tipo" onchange="this.form.submit()">
                    <option value="All"><?php echo $script_transl['tuttitipi']; ?></option>
                    <?php
                    $res = gaz_dbi_dyn_query("distinct tipdoc", $gTables["tesdoc"], $all, $orderby, 0, 999);
                    while ($val = gaz_dbi_fetch_array($res)) {
                        if ($tipdoc == $val["tipdoc"])
                            $selected = "selected";
                        else
                            $selected = "";
                        echo "<option value=\"" . $val["tipdoc"] . "\" " . $selected . ">" . $val["tipdoc"] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="text" placeholder="Cerca Num." class="input-sm form-control" name="numdoc" value="<?php if (isset($numdoc)) echo $numdoc; ?>" size="3" tabindex="3" class="FacetInput">			
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <select class="form-control input-sm" name="flt_year" onchange="this.form.submit()">
                    <option value="All"><?php echo $script_transl['tuttianni']; ?></option>
                    <?php
                    $res = gaz_dbi_dyn_query("distinct YEAR(datemi) as year", $gTables["tesdoc"], $all, $orderby, 0, 999);
                    while ($val = gaz_dbi_fetch_array($res)) {
                        if ($year == $val["year"])
                            $selected = "selected";
                        else
                            $selected = "";
                        echo "<option value=\"" . $val["year"] . "\" " . $selected . ">" . $val["year"] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <select class="form-control input-sm" name="flt_ragso1" onchange="this.form.submit()">
                    <option value="All"><?php echo $script_transl['tuttiforni']; ?></option>
<?php
$res = gaz_dbi_dyn_query("distinct " . $gTables['anagra'] . ".ragso1," . $gTables["tesdoc"] . ".clfoco", $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $all, $orderby, 0, 999);
while ($val = gaz_dbi_fetch_array($res)) {
    if ($ragso1 == $val["clfoco"])
        $selected = "selected";
    else
        $selected = "";
    echo "<option value=\"" . $val["clfoco"] . "\" " . $selected . ">" . $val["ragso1"] . "</option>";
}
?>
                </select>
            </td colspan="1" class="FacetFieldCaptionTD">
            &nbsp;
            <td colspan="1" class="FacetFieldCaptionTD">
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;">
            </td>
        </tr>
        <tr>
            <?php
// creo l'array (header => campi) per l'ordinamento dei record
            $headers_tesdoc = array(
                "ID" => "id_tes",
                "Tipo" => "Tipo",
                "Numero" => "numdoc",
                "Data" => "datemi",
                "Fornitore (cod.)" => "clfoco",
                "Status" => "",
                "Stampa" => "",
                "Cancella" => ""
            );
            $linkHeaders = new linkHeaders($headers_tesdoc);
            $linkHeaders->output();
            ?>
        </tr>
        <?php
        $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where, "datemi desc, numdoc desc", 0, 1);
        $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
        if ($ultimo_documento) {
            $ultimoddt = $ultimo_documento['numdoc'];
        } else {
            $ultimoddt = 1;
        }
        $result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], $where, $orderby, $limit, $passo);
        echo "<tr><td class=\"FacetDataTDred\" colspan=\"8\">Attenzione, la numerazione comprende anche i D.d.T. di Vendita non riportati in questa lista!</td></tr>";

        $anagrafica = new Anagrafica();
        while ($a_row = gaz_dbi_fetch_array($result)) {
            $cliente = $anagrafica->getPartner($a_row['clfoco']);
            echo "<tr class=\"FacetDataTD\">";
//       echo "<td class=\"FacetDataTD\"><a href=\"admin_docacq.php?id_tes=" . $a_row["id_tes"] . "&Update\">" . $a_row["id_tes"] . "</a> &nbsp</td>";
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"admin_docacq.php?id_tes=" . $a_row["id_tes"] . "&Update\">" . "<i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . $a_row["id_tes"] . "</a></td>";
            echo "<td>" . $a_row["tipdoc"] . " &nbsp;</td>";
            echo "<td>" . $a_row["numdoc"] . " &nbsp;</td>";
            echo "<td>" . $a_row["datemi"] . " &nbsp;</td>";
            echo "<td>" . $cliente["ragso1"] . "&nbsp;</td>";
            echo "<td>" . $a_row["status"] . " &nbsp;</td>";
            echo "<td>
			<a class=\"btn btn-xs btn-default\" href=\"stampa_docacq.php?id_tes=" . $a_row["id_tes"] . "&template=DDT\" title=\"Stampa\">
					<i class=\"glyphicon glyphicon-print\"></i>
			</a>
		  </td>";
            if ($ultimoddt == $a_row["numdoc"] and $a_row['numdoc'] == 0) {
                echo "<td>
				<a class=\"btn btn-xs btn-default\" href=\"delete_docacq.php?id_tes=" . $a_row["id_tes"] . "\" title=\"Cancella\">
					<i class=\"glyphicon glyphicon-remove\"></i>
				</a>
			  </td>";
            } else {
                echo "<td></td>";
            }
            echo "</tr>";
        }
        ?>
</form>
</table></div>
<?php
require("../../library/include/footer.php");
?>
