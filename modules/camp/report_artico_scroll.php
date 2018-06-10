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
        $where = "codice = '" . $ca . "'";
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
		<i class="glyphicon glyphicon-file" title="Scheda di sicurezza (ultima inserita)"></i>
		</a>';
        }
        if ($row["good_or_service"] > 0) {
            $gooser_i = 'wrench';
        } else {
            $gooser_i = 'shopping-cart';
        }
       
        $com = '';
        if ($admin_aziend['conmag'] > 0 && $row["good_or_service"] <= 0) {
            $com = '<a class="btn btn-xs btn-default" href="../camp/select_schart.php?di=0101' . date('Y') . '&df=' . date('dmY') . '&id=' . $row['codice'] . '" target="_blank">
		  <i class="glyphicon glyphicon-check"></i><i class="glyphicon glyphicon-print"></i>
		  </a>&nbsp;';
        }
		/*Antonio Germani creo array per le categorie merceologiche */
		$rescatmer = gaz_dbi_dyn_query('*', $gTables['catmer']);
		 while ($row2 = gaz_dbi_fetch_array($rescatmer)){
			 if ($row["catmer"]==$row2["codice"]){$descatmer=$row2["descri"];}
			 }
        ?>
        <tr>              
            <td data-title="<?php echo $script_transl["codice"]; ?>">
                <a class="btn btn-xs btn-default" href="../camp/admin_artico.php?Update&codice=<?php echo $row['codice']; ?>" ><i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $row['codice']; ?></a>
            </td>
            <td data-title="<?php echo $script_transl["descri"]; ?>">
                <span class="gazie-tooltip" data-type="product-thumb" data-id="<?php echo $row["codice"]; ?>" data-label="<?php echo $row['annota']; ?>"><?php echo $row["descri"]; ?></span>
            </td>
			<td data-title="">
			<?php if ($row["classif_amb"]==0){?>
			<img src="../camp/media/classe_0.gif" alt="Mia Immagine" width="50 px">
			<?php echo "Nc"; }?>
			<?php if ($row["classif_amb"]==1){?>
			<img src="../camp/media/classe_1.gif" alt="Mia Immagine" width="50 px">
			<?php echo "Xi"; }?>
			<?php if ($row["classif_amb"]==2){?>
			<img src="../camp/media/classe_2.gif" alt="Mia Immagine" width="50 px">
			<?php echo "Xn"; }?>
			<?php if ($row["classif_amb"]==3){?>
			<img src="../camp/media/classe_3.gif" alt="Mia Immagine" width="50 px">
			<?php echo "T"; }?>
			<?php if ($row["classif_amb"]==4){?>
			<img src="../camp/media/classe_4.gif" alt="Mia Immagine" width="50 px">
			<?php echo "T+"; }?>
            </td>
            <td data-title="<?php echo $script_transl["good_or_service"]; ?>" class="text-center">
                <?php echo $ldoc; ?> &nbsp;   <i class="glyphicon glyphicon-<?php echo $gooser_i; ?>"></i> 
            </td>
            <td data-title="<?php echo $script_transl["catmer"]; ?>" class="text-left">
                <?php echo $row["catmer"]," ",$descatmer; ?>
            </td>
            <td data-title="<?php echo $script_transl["unimis"]; ?>">
                <?php echo $row["unimis"]; ?>
            </td>
            
            <td data-title="<?php echo $script_transl["stock"]; ?>" title="Visualizza scheda prodotto">
               <?php echo floatval($magval['q_g']); echo "<p style='float:right;'>".$com."</p>"; ?>
            </td>
            
            <td data-title="<?php echo $script_transl["clone"] . ' in ' . $row["codice"]; ?>_2" title="Copia" class="text-center">
                <a class="btn btn-xs btn-default" href="clone_artico.php?codice=<?php echo $row["codice"]; ?>">
                    <i class="glyphicon glyphicon-export"></i>
                </a>
            </td>
            <td title="Elimina" class="text-center">
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