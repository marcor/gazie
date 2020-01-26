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
	$cat = filter_input(INPUT_POST, 'catmer');
	$show_artico_composit = gaz_dbi_get_row($gTables['company_config'], 'var', 'show_artico_composit');
	$tipo_composti = gaz_dbi_get_row($gTables['company_config'], 'var', 'tipo_composti');
   
    if (empty($ca) and empty($cat)) {
		$where = '1';
    } else {
		if (!empty($ca)){
			$where = "codice LIKE '" . $ca . "'";
		} else {
			if (!empty($cat)){
				$where = "catmer LIKE '" . $cat . "'";
			}
		}
	}
    
    $gForm = new magazzForm();
    $result = gaz_dbi_dyn_query('*', $gTables['artico'], $where, $ob . ' ' . $so, $no, PER_PAGE);
    while ($row = gaz_dbi_fetch_array($result)) {
        $lastdoc = getLastDoc($row["codice"]);
        $mv = $gForm->getStockValue(false, $row['codice']);
        $magval = array_pop($mv);
		 if (isset($magval['q_g']) && round($magval['q_g'],6) == "-0"){
			 $magval['q_g']=0;
		 }
		$class = 'default';
        if ($magval['q_g'] < 0) { // giacenza inferiore a 0
            $class = 'danger';
        } elseif ($magval['q_g'] > 0) { //
			if ($magval['q_g']<=$row['scorta']){
				$class = 'warning';
			}
        } else { // giacenza = 0
            $class = 'danger';
        }
        $iva = gaz_dbi_get_row($gTables['aliiva'], "codice", $row["aliiva"]);
		
		//*+ Recupero Ragione sociale Fornitore - DC - 02 feb 2018 
        $rsfor = gaz_dbi_get_row($gTables['clfoco'], "codice", $row["clfoco"]);
		//*- Recupero Ragione sociale Fornitore
		if ($rsfor['descri']==''){
			$rsfor['descri']='-';
		}	
        $ldoc = '';
        if ($lastdoc) {
            $ldoc = '<a href="../root/retrieve.php?id_doc=' . $lastdoc["id_doc"] . '">
		<i class="glyphicon glyphicon-file" title="Ultimo certificato e/o documentazione disponibile"></i>
		</a>';
        }
        if ($row["good_or_service"] == 1) {
            $ldoc = '<i class="glyphicon glyphicon-wrench" > </i>';
        } else if ($row["good_or_service"] == 0) {
            $ldoc = '<i class="glyphicon glyphicon-shopping-cart"> </i>';
        } else if ($row["good_or_service"] == 2) {
			$ldoc='<a target="_blank" title="Stampa l\'albero della distinta base" class="btn btn-xs btn-default" href="stampa_bom.php?ri=' . $row["codice"] . '"> <i class="glyphicon glyphicon-tasks"></i><b>
		BOM</b></a>';
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
            $brc = '<a target="_blank" class="btn btn-xs btn-default" href="stampa_barcode.php?code=' . $row["codice"] . '">
		<i class="glyphicon glyphicon-barcode"></i>
		</a>';
        }
        $com = '';
        if ($admin_aziend['conmag'] > 0 && $row["good_or_service"] != 1 && $tipo_composti['val']=="STD") {
            $com = '<a class="btn btn-xs btn-default" href="../magazz/select_schart.php?di=0101' . date('Y') . '&df=' . date('dmY') . '&id=' . $row['codice'] . '" target="_blank">
		  <i class="glyphicon glyphicon-check"></i><i class="glyphicon glyphicon-print"></i>
		  </a>&nbsp;';
        }
        ?>
        <tr>              
            <td data-title="<?php echo $script_transl["codice"]; ?>">
                <a class="btn btn-xs btn-default" href="../magazz/admin_artico.php?Update&codice=<?php echo $row['codice']; ?>" ><i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $row['codice']; ?></a>
                <?php
                    if ( $row["good_or_service"] == 2 ) {
                        echo '<a class="btn btn-xs btn-default" href="../magazz/admin_artico_compost.php?Update&codice='.$row['codice'].'" ><i class="glyphicon glyphicon-plus"></i></a>';
                    }
                ?>
            </td>
            <td data-title="<?php echo $script_transl["descri"]; ?>">
                <span class="gazie-tooltip" data-type="product-thumb" data-id="<?php echo $row["codice"]; ?>" data-label="<?php echo $row['annota']; ?>"><?php echo $row["descri"]; ?></span>
            </td>
            <td data-title="<?php echo $script_transl["good_or_service"]; ?>" class="text-center">
                <?php echo $ldoc; ?> 
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
            <!--+ nuova colonna fornitore - DC - 02 feb 2018  -->
			<td data-title="<?php echo $script_transl["clfoco"]; ?>">
				<?php echo $rsfor['descri']; ?>
            </td>
			<!--- nuova colonna fornitore -->
			<td data-title="<?php echo $script_transl["preacq"]; ?>" class="text-right">
                <?php echo number_format($row["preacq"], $admin_aziend['decimal_price'], ',', '.'); ?>
            </td>
            <td data-title="<?php echo $script_transl["stock"]; ?>" title="<?php echo $admin_aziend['symbol'] . ' ' . $magval['v_g']; ?>" class="text-center <?php echo $class; ?>">
               <?php echo $com.gaz_format_quantity(floatval($magval['q_g']),1,$admin_aziend['decimal_quantity']); ?>
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
			<td title="Visualizza lotti"> 
				<?php
			   if (intval($row['lot_or_serial'])>0) {
			   ?>
			   <a  class="btn btn-info btn-md" href="javascript:;" onclick="window.open('<?php echo"../../modules/magazz/mostra_lotti.php?codice=".$row['codice'];?>', 'titolo', 'menubar=no, toolbar=no, width=800, height=400, left=80%, top=80%, resizable, status, scrollbars=1, location');">
						<span class="glyphicon glyphicon-tag"></span></a>
			   <?php } ?>
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