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
if ($admin_aziend['decimal_quantity'] > 4) {
    $admin_aziend['decimal_quantity'] = 4;
}

function getLastDoc($item_code) {
    global $gTables;
    $rs = false;
    $rs_last_doc = gaz_dbi_dyn_query("*", $gTables['files'], " item_ref ='" . $item_code . "'", 'id_doc DESC', 0, 1);
    $last_doc = gaz_dbi_fetch_array($rs_last_doc);
    // se e' il primo documento dell'anno, resetto il contatore
    if ($last_doc) {
        $rs = $last_doc;
    }
    return $rs;
}

$search_field_Array = array('C' => array('codice', 'Codice'), 'D' => array('descri', 'Descrizione'), 'B' => array('barcode', 'Codice a barre'), 'T' => array('tutti', 'Campi principali'));

require("../../library/include/header.php");

$script_transl = HeadMain();

if (isset($_GET['auxil'])) {
    $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
    $auxil = "&all=yes";
    if ($admin_aziend['artsea'] == 'T') {
        $where = "codice LIKE '%%' or descri LIKE '%%' or barcode LIKE '%%'";
    } else {
        $where = $search_field_Array[$admin_aziend['artsea']][0] . " LIKE '%'";
    }
    $passo = 100000;
} else {
    if (isset($_GET['auxil'])) {
        if ($admin_aziend['artsea'] == 'T') {
            $where = "codice LIKE '%" . addslashes($_GET['auxil']) . "%' or descri LIKE '%" . addslashes($_GET['auxil']) . "%' or barcode LIKE '" . addslashes($_GET['auxil']) . "'";
        } else {
            $where = $search_field_Array[$admin_aziend['artsea']][0] . " LIKE '" . addslashes($_GET['auxil']) . "%'";
        }
    }
}

if (!isset($_GET['auxil'])) {
    $auxil = "";
    if ($admin_aziend['artsea'] == 'T') {
        $where = "codice LIKE '%" . addslashes($auxil) . "%' or descri LIKE '%" . addslashes($auxil) . "%' or barcode LIKE '" . addslashes($auxil) . "'";
    } else {
        $where = $search_field_Array[$admin_aziend['artsea']][0] . " LIKE '$auxil%'";
    }
}
?>
<div align="center" class="FacetFormHeaderFont">Merci e Servizi </div>
<?php
$recordnav = new recordnav($gTables['artico'], $where, $limit, $passo);
$recordnav->output();
/** ENRICO FEDELE */
/* pulizia del codice, eliminato boxover, aggiunte classi bootstrap alla tabella, convertite immagini in glyphicons */
?>
<form method="GET">
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
        <thead>
            <tr>
                <th class="FacetFieldCaptionTD" colspan="3"><?php echo $search_field_Array[$admin_aziend['artsea']][1]; ?>:
                    <input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="15" size="15" tabindex="1" class="FacetInput" />
                    <input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;" />
                </th>
                <th class="FacetFieldCaptionTD" colspan="3">
                    <input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;" />
                </th>
                <th class="FacetFieldCaptionTD" colspan="6">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = gaz_dbi_dyn_query('*', $gTables['artico'], $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
            $headers_artico = array("Codice" => "codice",
                "Descrizione" => "descri",
                "Doc." => "",
                "Merce<br />Servizio" => "good_or_service",
                "Cat.<br />merc." => "catmer",
                "U.M." => "unimis",
                "Prezzo 1" => "preve1",
                "Prezzo<br />acquisto" => "preacq",
                "Giacenza" => "",
                "IVA" => "aliiva",
                "Ritenuta" => "retention_tax",
                "Cassa<br />Prev." => "payroll_tax");
            if ($admin_aziend['conmag'] > 0) {
                $headers_artico = array_merge($headers_artico, array(
                    "Visualizza<br />Stampa" => '',
                    "Barcode" => "barcode",
                    "Duplica" => "",
                    "Cancella" => ""));
            } else {
                $headers_artico = array_merge($headers_artico, array("Barcode" => "barcode",
                    "Duplica" => "",
                    "Cancella" => ""));
            }
            $linkHeaders = new linkHeaders($headers_artico);
            $gForm = new magazzForm();

            echo '<tr>';
            $linkHeaders->output();
            echo '</tr>';
            while ($r = gaz_dbi_fetch_array($result)) {
                $lastdoc = getLastDoc($r["codice"]);
                $mv = $gForm->getStockValue(false, $r['codice']);
                $magval = array_pop($mv);
                $iva = gaz_dbi_get_row($gTables['aliiva'], "codice", $r["aliiva"]);
                echo '<tr class="FacetDataTD">
	   			<td>
					<a class="btn btn-xs btn-success btn-block" href="admin_artico.php?codice=' . $r["codice"] . '&amp;Update">
						<i class="glyphicon glyphicon-edit"></i>&nbsp;' . $r["codice"] . '
					</a>
				</td>';
                echo '	<td>
	   				<span class="gazie-tooltip" data-type="product-thumb" data-id="' . $r["codice"] . '" data-title="' . $r['annota'] . '">' . $r["descri"] . '</span>
				</td>
	   			<td align="center" title="">';
                if ($lastdoc) {
                    echo '		<a href="../root/retrieve.php?id_doc=' . $lastdoc["id_doc"] . '">
		 				<i class="glyphicon glyphicon-file" title="Ultimo certificato e/o documentazione disponibile"></i>
					</a>';
                }
                if ($r["good_or_service"] > 0) {
                    $gooser_i = 'wrench';
                } else {
                    $gooser_i = 'shopping-cart';
                }
                $prt = '';
                if ($r["payroll_tax"] > 0) {
                    $prt = floatval($admin_aziend['payroll_tax']) . '%';
                }
                $ret = '';
                if ($r["retention_tax"] > 0) {
                    $ret = floatval($admin_aziend['ritenuta']) . '%';
                }
                echo '	</td>
                                <td align="center"><i class="glyphicon glyphicon-' . $gooser_i . '"></i></td>
                                <td align="center">' . $r["catmer"] . ' </td>
				<td align="center">' . $r["unimis"] . ' </td>
				<td align="right">' . number_format($r["preve1"], $admin_aziend['decimal_price'], ',', '.') . ' </td>
				<td align="right">' . number_format($r["preacq"], $admin_aziend['decimal_price'], ',', '.') . ' </td>
				<td align="right" title="' . $admin_aziend['symbol'] . ' ' . $magval['v_g'] . '">' . floatval($magval['q_g']) . '</td>                                
                                <td align="right">' . floatval($iva['aliquo']) . '%</td>                                
                                <td align="right">' . $ret . '</td>                                
                                <td align="right">' . $prt . '</td>';
                if ($admin_aziend['conmag'] > 0) {
                    echo '	<td align="center" title="Visualizza e/o stampa la scheda di magazzino">
		  				<a class="btn btn-xs btn-default" href="../magazz/select_schart.php?di=0101' . date('Y') . '&df=' . date('dmY') . '&id=' . $r['codice'] . '" target="_blank">
							<i class="glyphicon glyphicon-check"></i><i class="glyphicon glyphicon-print"></i>
						</a>
					</td>';
                }
                echo '	<td align="center" title="Stampa Codici a Barre">
	   				<a class="btn btn-xs btn-default" href="stampa_barcode.php?code=' . $r["codice"] . '">
						<i class="glyphicon glyphicon-barcode"></i>
					</a>
				</td>
				<td align="center" title="Duplica articolo in (' . $r["codice"] . '_2)">
					<a class="btn btn-xs btn-default" href="clone_artico.php?codice=' . $r["codice"] . '">
						<i class="glyphicon glyphicon-export"></i>
					</a>
				</td>
				<td align="center">
					<a class="btn btn-xs btn-default btn-elimina" href="delete_artico.php?codice=' . $r["codice"] . '">
						<i class="glyphicon glyphicon-remove"></i>
					</a>
				</td>
			</tr>';
            }
            ?>
        </tbody>
    </table>
</form>
</div><!-- chiude div container role main --></body>
</html>