<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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
if (!$isAjax && !isset($_GET['duplicate'])) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}

// *****************************************************************************/

if (isset($_GET['id_tes'])) { //	Evitiamo errori se lo script viene chiamato direttamente
	require("../../library/include/datlib.inc.php");
	$admin_aziend = checkAdmin();

	//duplico la testata
   $tabella = $gTables['tesbro'];
   $id_testata = intval($_GET['id_tes']);
   $fornitore = intval($_GET['duplicate']);
   $numdoc = trovaNuovoNumero($gTables);  // numero nuovo documento
   $today = gaz_today();
   $sql = "INSERT INTO $tabella (`id_tes`, `seziva`, `tipdoc`, `template`, `print_total`, `delivery_time`, `day_of_validity`, `datemi`, `protoc`, `numdoc`, `numfat`, `datfat`, `clfoco`, `pagame`, `banapp`, `vettor`, `listin`, `destin`, `id_des`, `id_des_same_company`, `spediz`, `portos`, `imball`, `traspo`, `speban`, `spevar`, `round_stamp`, `cauven`, `caucon`, `caumag`, `id_agente`, `id_pro`, `sconto`, `expense_vat`, `stamp`, `taxstamp`, `virtual_taxstamp`, `net_weight`, `gross_weight`, `units`, `volume`, `initra`, `geneff`, `id_contract`, `id_con`, `status`, `adminid`, `last_modified`) "
           . "SELECT null, `seziva`, `tipdoc`, `template`, `print_total`, `delivery_time`, `day_of_validity`, '$today', `protoc`, $numdoc, '', '', $fornitore, `pagame`, `banapp`, `vettor`, `listin`, `destin`, `id_des`, `id_des_same_company`, `spediz`, `portos`, `imball`, `traspo`, `speban`, `spevar`, `round_stamp`, `cauven`, `caucon`, `caumag`, `id_agente`, `id_pro`, `sconto`, `expense_vat`, `stamp`, `taxstamp`, `virtual_taxstamp`, `net_weight`, `gross_weight`, `units`, `volume`,  '$today', `geneff`, `id_contract`, `id_con`, `status`, `adminid`, CURRENT_TIMESTAMP FROM $tabella WHERE id_tes = $id_testata;";
   mysqli_query($link, $sql);
   $nuovaChiave = gaz_dbi_last_id();
//    gaz_dbi_del_row($gTables['tesbro'], "id_tes", intval($_POST['id_tes']));
   //... e i righi
   $tabella = $gTables['rigbro'];
   $sql = "INSERT INTO $tabella (`id_rig`, `id_tes`, `tiprig`, `codart`, `descri`, `id_body_text`, `unimis`, `quanti`, `prelis`, `sconto`, `codvat`, `pervat`, `codric`, `provvigione`, `ritenuta`, `delivery_date`, `id_doc`, `id_mag`, `status`) "
           . "SELECT null, $nuovaChiave, `tiprig`, `codart`, `descri`, `id_body_text`, `unimis`, `quanti`, `prelis`, `sconto`, `codvat`, `pervat`, `codric`, `provvigione`, `ritenuta`, `delivery_date`, 0, 0, 'INSERT' FROM $tabella WHERE id_tes = $id_testata;";
   mysqli_query($link, $sql);
   header("Location: report_broacq.php");
   exit;
}

function trovaNuovoNumero($gTables) {
	// modifica di Antonio Espasiano come da post :
	// https://sourceforge.net/p/gazie/discussion/468173/thread/572dcb76/
	//
	$orderBy = "datemi desc, numdoc desc";
	parse_str(parse_url($_POST['ritorno'],PHP_URL_QUERY),$output);
	$rs_ultimo_documento = gaz_dbi_dyn_query("numdoc", $gTables['tesbro'], $gTables['tesbro'].".tipdoc="."'".$output['auxil']."'", $orderBy, 0, 1);
	$ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
	// se e' il primo documento dell'anno, resetto il contatore
	if ($ultimo_documento) {
	/*$orderBy = "datemi desc, numdoc desc";
	$rs_ultimo_documento = gaz_dbi_dyn_query("numdoc", $gTables['tesbro'], 1, $orderBy, 0, 1);
	$ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
	se e' il primo documento dell'anno, resetto il contatore
	if ($ultimo_documento) {
	*/
      $numdoc = $ultimo_documento['numdoc'] + 1;
   } else {
      $numdoc = 1;
   }
   return $numdoc;
}
?>