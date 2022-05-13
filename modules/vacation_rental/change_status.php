<?php
/*
 --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-20223 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)
  Ogni diritto è riservato.
  E' possibile usare questo modulo solo dietro autorizzazione dell'autore
  --------------------------------------------------------------------------
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
	require("../../modules/magazz/lib.function.php");
	$upd_mm = new magazzForm;
	$admin_aziend = checkAdmin();
	switch ($_POST['type']) {
		case "set_new_stato_lavorazione":
			$i=intval($_POST['ref']); // id_tesbro
      // ricarico il json custom field tesbro e controllo
      $custom_field=gaz_dbi_get_row($gTables['tesbro'], "id_tes", $i)['custom_field']; // carico il vecchio json custom_field di tesbro
      if ($data = json_decode($custom_field,true)){// se c'è un json
        if (is_array($data['vacation_rental'])){ // se c'è il modulo "vacation rental" lo aggiorno
          $data['vacation_rental']['status']=substr($_POST['new_status'],0,10);
          $custom_json = json_encode($data);
        } else { //se non c'è il modulo "vacation_rental" lo aggiungo
          $data['vacation_rental']= array('status' => substr($_POST['new_status'],0,10));
          $custom_json = json_encode($data);
        }
      }else { //se non c'è un json creo "vacation_rental"
          $data['vacation_rental']= array('status' => substr($_POST['new_status'],0,10));
          $custom_json = json_encode($data);
      }
      gaz_dbi_put_row($gTables['tesbro'], 'id_tes', $i, 'custom_field', $custom_json);
		break;
	}
}
?>
