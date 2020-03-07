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
	$calc = new Schedule;
	$admin_aziend = checkAdmin();
	switch ($_POST['type']) {
        case "movcon":
			$i=intval($_POST['id_tes']);
			//cancello i righi contabili
			$result = gaz_dbi_dyn_query("*", $gTables['rigmoc'],"id_tes = ".$i,"id_tes asc");
			while ($a_row = gaz_dbi_fetch_array($result)) {
				gaz_dbi_del_row($gTables['rigmoc'], "id_rig", $a_row['id_rig']);
				// elimino le eventuali partite aperte
				$calc->updatePaymov($a_row['id_rig']);
			}
			//cancello i righi iva
			$result = gaz_dbi_dyn_query("*", $gTables['rigmoi'],"id_tes = ".$i,"id_tes asc");
			while ($a_row = gaz_dbi_fetch_array($result)) {
				gaz_dbi_del_row($gTables['rigmoi'], "id_rig", $a_row['id_rig']);
			}
			//cancello la testata
			gaz_dbi_del_row($gTables['tesmov'], "id_tes", $i);
			// se si riferisce ad un documento contabilizzato annullo il riferimento al movimento
			gaz_dbi_put_query($gTables['tesdoc'], 'id_con ='.$i,'id_con',0);
			// se si riferisce ad un effetto contabilizzato annullo il riferimento al movimento
			gaz_dbi_put_query($gTables['effett'], 'id_con ='.$i,'id_con',0);
			//cancello anche l'eventuale rigo sul registro beni ammortizzabili
			gaz_dbi_del_row($gTables['assets'], "id_movcon", $i);
		break;
	}
}
?>