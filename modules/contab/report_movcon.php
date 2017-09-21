<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/header.php");

function getPaymov($id_tes, $clfoco) { // restituisce l'id_rig se c'� un movimento di scadenzario
    global $gTables;
    $rig_res = gaz_dbi_dyn_query('*', $gTables['rigmoc'], "id_tes = " . $id_tes . " AND codcon=" . $clfoco, 'id_rig ASC', 0, 1);
    $rig_r = gaz_dbi_fetch_array($rig_res);
    if ($rig_r) {
        $pay_res = gaz_dbi_dyn_query('*', $gTables['paymov'], "id_rigmoc_pay = " . $rig_r['id_rig'], 'expiry ASC', 0, 1);
        $pay_r = gaz_dbi_fetch_array($pay_res);
        if ($pay_r) {
            return $rig_r['id_rig'];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function getDocRef($data) {
    global $gTables;
    $r = '';
    switch ($data['caucon']) {
        case "FAI":
        case "FND":
        case "FNC":
            $tesdoc_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "id_con = " . $data["id_tes"], 'id_tes DESC', 0, 1);
            $tesdoc_r = gaz_dbi_fetch_array($tesdoc_result);
            if ($tesdoc_r) {
                $r = "../vendit/stampa_docven.php?id_tes=" . $tesdoc_r["id_tes"];
            }
            break;
        case "FAD":
            $tesdoc_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "tipdoc = \"" . $data["caucon"] . "\" AND seziva = " . $data["seziva"] . " AND protoc = " . $data["protoc"] . " AND numfat = '" . $data["numdoc"] . "' AND datfat = \"" . $data["datdoc"] . "\"", 'id_tes DESC');
            $tesdoc_r = gaz_dbi_fetch_array($tesdoc_result);
            if ($tesdoc_r) {
                $r = "../vendit/stampa_docven.php?td=2&si=" . $tesdoc_r["seziva"] . "&pi=" . $tesdoc_r['protoc'] . "&pf=" . $tesdoc_r['protoc'] . "&di=" . $tesdoc_r["datfat"] . "&df=" . $tesdoc_r["datfat"];
            }
            break;
        case "RIB":
        case "TRA":
            $effett_result = gaz_dbi_dyn_query('*', $gTables['effett'], "id_con = " . $data["id_tes"], 'id_tes', 0, 1);
            $effett_r = gaz_dbi_fetch_array($effett_result);
            if ($effett_r) {
                $r = "../vendit/stampa_effett.php?id_tes=" . $effett_r["id_tes"];
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
        $where = "caucon like '" . addslashes($_GET['auxil']) . "%'";
    }
}
if (isset($_GET['mov'])) {
    if ($_GET['mov'] > 0) {
        $numero = $_GET['mov'];
        $where = $gTables['tesmov'] . ".id_tes =" . $numero;
        $passo = 1;
    } else {
        $numero = '';
    }
}
if (!isset($_GET['flag_order'])) {
    $orderby = " id_tes desc";
}

if (!isset($_GET['auxil'])) {
    $auxil = "";
    $where = "caucon like '$auxil%'";
}
$script_transl = HeadMain('', '', 'admin_movcon');
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['report']; ?></div>
<form method="GET">
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
        <tr>
            <td class="FacetFieldCaptionTD">
                <input type="text" placeholder="Movimento" class="input-xs form-control" name="mov"
                       value="<?php if (isset($numero)) echo $numero; ?>" maxlength ="6" size="3" tabindex="1" class="FacetInput">
            </td>
            <td class="FacetFieldCaptionTD"></td>
            <td align="right" class="FacetFieldCaptionTD">
                <input type="text" placeholder="<?php echo $script_transl['caucon']; ?>" class="input-xs form-control" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
            </td>
            <td class="FacetFieldCaptionTD"></td>
            <td class="FacetFieldCaptionTD"></td>
            <td class="FacetFieldCaptionTD"></td>
            <td class="FacetFieldCaptionTD"></td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" name="search" class="btn btn-xs btn-default" value="<?php echo $script_transl['search']; ?>" tabindex="1" onClick="javascript:document.report.all.value = 1;">
            </td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" name="all" class="btn btn-xs btn-default" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value = 1;">
            </td>
        </tr>
        <?php
        $result = gaz_dbi_dyn_query("id_tes, datreg, clfoco, caucon, descri, protoc, numdoc, seziva, datdoc", $gTables['tesmov'], $where, $orderby, $limit, $passo);
        $headers_tesmov = array(
            "N." => "id_tes",
            $script_transl['date_reg'] => "datreg",
            $script_transl['caucon'] => "caucon",
            $script_transl['descri'] => "descri",
            $script_transl['protoc'] => "",
            $script_transl['numdoc'] => "",
            $script_transl['amount'] => "",
            $script_transl['source'] => "",
            $script_transl['delete'] => ""
        );
        $linkHeaders = new linkHeaders($headers_tesmov);
        $linkHeaders->output();
        $recordnav = new recordnav($gTables['tesmov'], $where, $limit, $passo);
        $recordnav->output();
        $anagrafica = new Anagrafica();
        while ($a_row = gaz_dbi_fetch_array($result)) {
            $paymov = false;
            if (substr($a_row["clfoco"], 0, 3) == $admin_aziend['mascli'] or substr($a_row["clfoco"], 0, 3) == $admin_aziend['masfor']) {
                if (substr($a_row["clfoco"], 0, 3) == $admin_aziend['mascli']) {
                    $paymov = getPaymov($a_row["id_tes"], $a_row["clfoco"]);
                }
            }

            // INIZIO crezione tabella per la visualizzazione sul tootip di tutto il movimento e facccio la somma del totale movimento 
            $res_rig = gaz_dbi_dyn_query("*", $gTables['rigmoc'], 'id_tes=' . $a_row["id_tes"], 'id_rig');
            $tt = '<table><th colspan=3 >' . $a_row['descri'] . '</th>';
            $tot = 0.00;
            while ($rr = gaz_dbi_fetch_array($res_rig)) {
                $account = $anagrafica->getPartner($rr["codcon"]);
                $tt .= '<tr><td>' . htmlspecialchars( $account['descri'] ) . '</td><td align=right>' . $rr['import'] . '</td><td align=right>' . $rr['darave'] . '</td></tr>';
                if ($rr['darave'] == 'D') {
                    $tot += $rr['import'];
                }
            }
            $tt .= '</table>';
            // FINE creazione tabella per il tooltip

            echo "<tr class=\"FacetDataTD\">";
            echo "<td align=\"right\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"admin_movcon.php?id_tes=" . $a_row["id_tes"] . "&Update\" title=\"Modifica\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . $a_row["id_tes"] . "</a> &nbsp</td>";
            echo "<td align=\"center\">" . gaz_format_date($a_row["datreg"]) . " &nbsp;</td>";
            echo '<td align="center"><div class="gazie-tooltip" data-type="movcon-thumb" data-id="' . $a_row["id_tes"] . '" data-title="' . $tt . '" >' . $a_row["caucon"] . "</div></td>";
            echo '<td><div class="gazie-tooltip" data-type="movcon-thumb" data-id="' . $a_row["id_tes"] . '" data-title="' . $tt . '" >' . $a_row["descri"] . '</div></td>';
            if ($a_row["protoc"] > 0) {
                echo "<td align=\"center\">" . $a_row["protoc"] . "/" . $a_row["seziva"] . "";
                echo "</td>";
            } else {
                echo "<td></td>";
            }
            echo "<td align=\"center\">" . $a_row["numdoc"] . "</td>";
            echo '<td align="right">' . gaz_format_number($tot) . '</td>';
            echo "<td align=\"center\">";
            $docref = getDocRef($a_row);
            if (!empty($docref)) {
                echo "<a class=\"btn btn-xs btn-default btn-default\" title=\"" . $script_transl['sourcedoc'] . "\" href=\"$docref\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a>";
            } elseif ($paymov) {
                echo "<a class=\"btn btn-xs btn-default btn-default\" title=\"" . $script_transl['customer_receipt'] . "\" href=\"../vendit/print_customer_payment_receipt.php?id_rig=" . $paymov . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-check\"></i>&nbsp;<i class=\"glyphicon glyphicon-euro\"></i>&nbsp;<i class=\"glyphicon glyphicon-print\"></i></a>";
            }
            echo "</td>";
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_movcon.php?id_tes=" . $a_row["id_tes"] . "\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
            echo "</tr>\n";
        }
        ?>
    </table>
    <?php
    require("../../library/include/footer.php");
    ?>