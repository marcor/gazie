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
if ((isset($_POST['type'])&&isset($_POST['ref'])) OR (isset($_POST['type'])&&isset($_POST['id_tes']))) { 
	require("../../library/include/datlib.inc.php");
	$admin_aziend = checkAdmin();
	switch ($_POST['type']) {
        case "broacq":
			$i=intval($_POST['id_tes']);
			//cancello la testata
			gaz_dbi_del_row($gTables['tesbro'], "id_tes", $i);
			//... e i righi
			$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = '{$i}'","id_tes desc");
			while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
				gaz_dbi_del_row($gTables['rigbro'], "id_rig", $a_row['id_rig']);
			}
		break;
		case "docacq":
			$i=intval($_POST['id_tes']);
			$form = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", $i);
			gaz_dbi_del_row($gTables['tesdoc'], "id_tes", $i);
			gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $form['id_con']);
			gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $form['id_con']);
			gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $form['id_con']);
			$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '".$i."'","id_tes desc");
			print_r($rs_righidel);
			while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
				gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $a_row['id_rig']);
				if (intval($a_row['id_mag']) > 0){  //se c'� stato un movimento di magazzino lo azzero
					gaz_dbi_del_row($gTables['movmag'], "id_mov", $a_row['id_mag']);					
					// cancello pure eventuale movimento sian 
					gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", $a_row['id_mag']);
				}				
			}
		break;
		case "pagdeb":
			$i=intval($_POST['id_tes']);
			//cancello la testata
			gaz_dbi_del_row($gTables['tesbro'], "id_tes", $i);
			//... e i righi
			$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = '{$i}'","id_tes desc");
			while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
				  gaz_dbi_del_row($gTables['rigbro'], "id_rig", $a_row['id_rig']);
			}
		break;
		case "fornit":
			$i=intval($_POST['ref']);
			gaz_dbi_del_row($gTables['clfoco'], 'codice', $i);
		break;
	}
}
?>