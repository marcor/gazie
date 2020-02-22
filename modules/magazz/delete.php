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
if (isset($_POST['type'])&&isset($_POST['ref'])) { 
	require("../../library/include/datlib.inc.php");
	$admin_aziend = checkAdmin();
	switch ($_POST['type']) {
        case "catmer":
			$i=intval($_POST['ref']);
			gaz_dbi_del_row($gTables['catmer'],"codice",$i);
		break;
		case "caumag":
			if (intval($_POST['ref']) > 80) {
				break;
			}
			$i=intval($_POST['ref']);
			gaz_dbi_del_row($gTables['caumag'],"codice",$i);
		break;
		case "movmag":
			$i=intval($_POST['ref']);
			$form = gaz_dbi_get_row($gTables['movmag'], 'id_mov', $i);
			gaz_dbi_del_row($gTables['movmag'], 'id_mov', $i); // cancello il movimento di magazzino
			if ($form['id_rif'] > 0) {  //se il movimento di magazzino � stato generato da un rigo di documento lo azzero
				gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $form['id_rif'], 'id_mag', 0);
			}
			$item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
			if ($item['SIAN']>0){ // se è SIAN cancello anche il suo movimento
				gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", intval($_POST['id_mov']));
			}		
		break;
	}
}
?>