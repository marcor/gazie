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
// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
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

if (isset($_POST['rowno'])) { //	Evitiamo errori se lo script viene chiamato direttamente
    require("../../library/include/datlib.inc.php");
    $admin_aziend = checkAdmin();
    require("./lang." . $admin_aziend['lang'] . ".php");
    $script_transl = $strScript['report_artico.php'];
    $no = intval($_POST['rowno']);
    $ob = filter_input(INPUT_POST, 'orderby');
    $so = filter_input(INPUT_POST, 'sort');
    $ca = filter_input(INPUT_POST, 'codart');
    if (empty($ca)) {
        $where = '1';
    } else {
        $where = "codice LIKE '" . $ca . "'";
        $no = '0';
    }
    $gForm = new magazzForm();
    $result = gaz_dbi_dyn_query('*', $gTables['artico'], $where, $ob . ' ' . $so, $no, PER_PAGE);
    while ($row = gaz_dbi_fetch_array($result)) {
        $lastdoc = getLastDoc($row["codice"]);
        $mv = $gForm->getStockValue(false, $row['codice']);
        $magval = array_pop($mv);
        $iva = gaz_dbi_get_row($gTables['aliiva'], "codice", $row["aliiva"]);
        $ldoc = '';
        if ($lastdoc) {
            $ldoc = '<a href="../root/retrieve.php?id_doc=' . $lastdoc["id_doc"] . '">
		<i class="glyphicon glyphicon-file" title="Ultimo certificato e/o documentazione disponibile"></i>
		</a>';
        }
        if ($row["good_or_service"] > 0) {
            $gooser_i = 'wrench';
        } else {
            $gooser_i = 'shopping-cart';
        }
        $prt = '';
        if ($row["payroll_tax"] > 0) {
            $prt = floatval($admin_aziend['payroll_tax']) . '%';
        }
        $ret = '';
        if ($row["retention_tax"] > 0) {
            $ret = floatval($admin_aziend['ritenuta']) . '%';
        }
        $brc = '';
        if ($row["barcode"] > 0) {
            $brc = '<a class="btn btn-xs btn-default" href="stampa_barcode.php?code=' . $row["codice"] . '">
		<i class="glyphicon glyphicon-barcode"></i>
		</a>';
        }
        $com = '';
        if ($admin_aziend['conmag'] > 0 && $row["good_or_service"] <= 0) {
            $com = '<a class="btn btn-xs btn-default" href="../magazz/select_schart.php?di=0101' . date('Y') . '&df=' . date('dmY') . '&id=' . $row['codice'] . '" target="_blank">
		  <i class="glyphicon glyphicon-check"></i><i class="glyphicon glyphicon-print"></i>
		  </a>&nbsp;';
        }
        ?>
        <tr>              
            <td data-title="<?php echo $script_transl["codice"]; ?>">
                <a class="btn btn-xs btn-default" href="../magazz/admin_artico.php?Update&codice=<?php echo $row['codice']; ?>" ><i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $row['codice']; ?></a>
            </td>
            <td data-title="<?php echo $script_transl["descri"]; ?>">
                <span class="gazie-tooltip" data-type="product-thumb" data-id="<?php echo $row["codice"]; ?>" data-label="<?php echo $row['annota']; ?>"><?php echo $row["descri"]; ?></span>
            </td>
            <td data-title="<?php echo $script_transl["good_or_service"]; ?>" class="text-center">
                <?php echo $ldoc; ?> &nbsp;   <i class="glyphicon glyphicon-<?php echo $gooser_i; ?>"></i> 
            </td>
            <td data-title="<?php echo $script_transl["catmer"]; ?>" class="text-center">
                <?php echo $row["catmer"]; ?>
            </td>
            <td data-title="<?php echo $script_transl["unimis"]; ?>">
                <?php echo $row["unimis"]; ?>
            </td>
            <td data-title="<?php echo $script_transl["preve1"]; ?>" class="text-right">
                <?php echo number_format($row["preve1"], $admin_aziend['decimal_price'], ',', '.'); ?>
            </td>
            <td data-title="<?php echo $script_transl["preacq"]; ?>" class="text-right">
                <?php echo number_format($row["preacq"], $admin_aziend['decimal_price'], ',', '.'); ?>
            </td>
            <td data-title="<?php echo $script_transl["stock"]; ?>" title="<?php echo $admin_aziend['symbol'] . ' ' . $magval['v_g']; ?>" class="text-center">
               <?php echo $com.floatval($magval['q_g']); ?>
            </td>
            <td data-title="<?php echo $script_transl["aliiva"]; ?>">
                <?php echo floatval($iva['aliquo']) . '%'; ?>
            </td>
            <td data-title="<?php echo $script_transl["retention_tax"]; ?>" class="text-center" >
                <?php echo $ret; ?>&nbsp;
            </td>
            <td data-title="<?php echo $script_transl["payroll_tax"]; ?>" class="text-center">
                <?php echo $prt; ?>&nbsp;
            </td>
            <td data-title="<?php echo $script_transl["barcode"]; ?>" class="text-center">
                <?php echo $brc; ?>&nbsp;
            </td>
            <td data-title="<?php echo $script_transl["clone"] . ' in ' . $row["codice"]; ?>_2" class="text-center">
                <a class="btn btn-xs btn-default" href="clone_artico.php?codice=<?php echo $row["codice"]; ?>">
                    <i class="glyphicon glyphicon-export"></i>
                </a>
            </td>
            <td class="text-center">
                <a class="btn btn-xs btn-default btn-elimina" href="delete_artico.php?codice=<?php echo $row["codice"]; ?>">
                    <i class="glyphicon glyphicon-remove"></i>
                </a>
            </td>
        </tr>  
        <?php
    }
    exit();
}
?>