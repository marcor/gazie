<?php
/*
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
if ((isset($_POST['type']) && isset($_POST['ref'])) || (isset($_POST['type'])&&isset($_POST['id_tes']))) {
	require("../../library/include/datlib.inc.php");
	$admin_aziend = checkAdmin();
	switch ($_POST['type']) {
		case "module":
      $i=substr($_POST['ref'],0,30);
      // preparo l'update di custom_field che potrebbe contenere altri dati
      $module = gaz_dbi_get_row($gTables['module'], "name",substr($_POST['ref'],0,30));
      $admin_module = gaz_dbi_get_row($gTables['admin_module'], "moduleid",$module['id']," AND adminid='".substr($_POST['adminid'],0,30)."' AND company_id=" . $admin_aziend['company_id']);
      $custom_field=is_string($admin_module['custom_field'])?json_decode($admin_module['custom_field'],true):[];
      $custom_field['excluded_script']=[]; // azzero l'array prima di ripopolarlo
      foreach($_POST['del_script'] as $v) {
        if ($v['chk_script']==1) $custom_field['excluded_script'][] = substr($v['script_name'],0,-4);
      }
      //print_r($custom_field);
      // aggiorno la colonna custom_field
      $custom_field=json_encode($custom_field);
      $query="UPDATE ".$gTables['admin_module']." SET custom_field='".$custom_field."' WHERE moduleid=".$module['id']." AND adminid='".substr($_POST['adminid'],0,30)."' AND company_id=" . $admin_aziend['company_id'];
      gaz_dbi_query($query);
		break;
	}
}
?>
