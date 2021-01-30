<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
$message = "Sei sicuro di voler duplicare?";
if (!isset($_POST['ritorno'])) {
	$_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Duplicate'])) {
	//procedo alla duplicazione della testata e dei righi...
	//duplico la testata
	$tabella = $gTables['tesbro'];
	$chiave = $_POST['id_tes'];
	$numdoc = trovaNuovoNumero($gTables);  // numero nuovo documento
	$today = gaz_today();
	$sql = "INSERT INTO $tabella (`id_tes`, `seziva`, `tipdoc`, `template`, `print_total`, `delivery_time`, `day_of_validity`, `datemi`, `protoc`, `numdoc`, `numfat`, `datfat`, `clfoco`, `pagame`, `banapp`, `vettor`, `listin`, `destin`, `id_des`, `id_des_same_company`, `spediz`, `portos`, `imball`, `traspo`, `speban`, `spevar`, `round_stamp`, `cauven`, `caucon`, `caumag`, `id_agente`, `sconto`, `expense_vat`, `stamp`, `taxstamp`, `virtual_taxstamp`, `net_weight`, `gross_weight`, `units`, `volume`, `initra`, `geneff`, `id_contract`, `id_con`, `status`, `adminid`, `last_modified`) "
	. "SELECT null, `seziva`, `tipdoc`, `template`, `print_total`, `delivery_time`, `day_of_validity`, '$today', `protoc`, $numdoc, '', '', `clfoco`, `pagame`, `banapp`, `vettor`, `listin`, `destin`, `id_des`, `id_des_same_company`, `spediz`, `portos`, `imball`, `traspo`, `speban`, `spevar`, `round_stamp`, `cauven`, `caucon`, `caumag`, `id_agente`, `sconto`, `expense_vat`, `stamp`, `taxstamp`, `virtual_taxstamp`, `net_weight`, `gross_weight`, `units`, `volume`,  '$today', `geneff`, `id_contract`, `id_con`, `status`, `adminid`, CURRENT_TIMESTAMP FROM $tabella WHERE id_tes = $chiave;";
	mysqli_query($link, $sql);
	$nuovaChiave = gaz_dbi_last_id();

	//... e i righi
	$tabella = $gTables['rigbro'];
	$sql = "INSERT INTO $tabella (`id_rig`, `id_tes`, `tiprig`, `codart`, `descri`, `id_body_text`, `unimis`, `quanti`, `prelis`, `sconto`, `codvat`, `pervat`, `codric`, `provvigione`, `ritenuta`, `delivery_date`, `id_doc`, `id_mag`, `status`) "
	. "SELECT null, $nuovaChiave, `tiprig`, `codart`, `descri`, `id_body_text`, `unimis`, `quanti`, `prelis`, `sconto`, `codvat`, `pervat`, `codric`, `provvigione`, `ritenuta`, `delivery_date`, 0, 0, 'INSERT' FROM $tabella WHERE id_tes = $chiave;";
	mysqli_query($link, $sql);

	header("Location: " . $_POST['ritorno']);
	exit;
}

if (isset($_POST['Return'])) {
	header("Location: " . $_POST['ritorno']);
	exit;
}

//recupero i documenti non contabilizzati
$result = gaz_dbi_dyn_query("*", $gTables['tesbro'], "id_tes = " . intval($_GET['id_tes']), "id_tes desc");
$rs_righi = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = " . intval($_GET['id_tes']), "id_tes desc");
$numrig = gaz_dbi_num_rows($rs_righi);
$form = gaz_dbi_fetch_array($result);
$tipobro="";
switch ($form['tipdoc']) {
	case "VPR":
		$tipobro = "il preventivo";
		break;
	case "VOR":
	case "VOW":
		$tipobro = "l'ordine";
		break;
	case "VCO":
		$tipobro = "lo scontrino";
		break;
}
$titolo = "Duplica " . $tipobro . " n." . $form['numdoc'];
require("../../library/include/header.php");
$script_transl = HeadMain();
$anagrafica = new Anagrafica();
$cliente = $anagrafica->getPartner($form['clfoco']);

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
<form method="POST">
	<input type="hidden" name="id_tes" value="<?php print $form['id_tes']; ?>">
	<input type="hidden" name="ritorno" value="<?php print $_POST['ritorno']; ?>">
	<div align="center"><font class="FacetFormHeaderFont">Attenzione!!! Stai duplicando <?php echo $tipobro . " n." . $form['numdoc']; ?> </font></div>
	<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
		<!-- BEGIN Error -->
		<tr>
			<td colspan="2" class="FacetDataTD" style="color: red;">
				<?php
				if (!$message == "") {
					print "$message";
				}
				?>
			</td>
		</tr>
		<!-- END Error -->
		<tr>
			<td class="FacetFieldCaptionTD">Numero di ID &nbsp;</td><td class="FacetDataTD"><?php print $form["id_tes"] ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD">Tipo documento &nbsp;</td><td class="FacetDataTD"><?php print $form["tipdoc"] ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD">Numero Documento &nbsp;</td><td class="FacetDataTD"><?php print $form["numdoc"] ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD">Cliente &nbsp;</td><td class="FacetDataTD"><?php print $cliente["ragso1"] ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD">Num. di righi &nbsp;</td><td class="FacetDataTD"><?php print $numrig ?>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" align="right">Se sei sicuro conferma la duplicazione &nbsp;
				<input type="submit" name="Duplicate" value="DUPLICA !">&nbsp;
			</td>
		</tr>
	</table>
</form>
<?php
require("../../library/include/footer.php");
?>