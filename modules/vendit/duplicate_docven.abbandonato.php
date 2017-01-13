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
require("../../library/include/datlib.inc.php");
require("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();
$upd_mm = new magazzForm;
$docOperat = $upd_mm->getOperators();
$message = "Sei sicuro di voler duplicare?";
if (!isset($_POST['ritorno'])) {
   $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

if (isset($_GET['id_tes'])) { //sto duplicando un singolo documento
   $result = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "id_tes = " . intval($_GET['id_tes']));
   $row = gaz_dbi_fetch_array($result);
   if ($row['tipdoc'] == 'FAD') {   // non si può duplicare una fattura differita per evitare di gestire i DDT multipli
      header("Location: " . $_POST['ritorno']);
      exit;
   } elseif (substr($row['tipdoc'], 0, 2) == 'DD') {
      $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = '" . substr($row['datemi'], 0, 4) . "' AND tipdoc LIKE '" . substr($row['tipdoc'], 0, 2) . "_' or tipdoc='FAD' AND seziva = " . $row['seziva'] . " ", "numdoc DESC", 0, 1);
   } elseif ($row['tipdoc'] == 'RDV') {
      $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "id_tes = " . intval($_GET['id_tes']));
   } elseif ($row['tipdoc'] == 'VCO') {
      $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "datemi = '" . $row['datemi'] . "' AND tipdoc = 'VCO' AND seziva = " . $row['seziva'], "datemi DESC, numdoc DESC", 0, 1);
   } else {
      $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = '" . substr($row['datemi'], 0, 4) . "' AND tipdoc LIKE '" . substr($row['tipdoc'], 0, 1) . "%' AND seziva = " . $row['seziva'] . " ", "protoc DESC, numdoc DESC", 0, 1);
   }
} else { //non ci sono dati sufficenti per stabilire cosa eliminare
   header("Location: " . $_POST['ritorno']);
   exit;
}

if (!$row) {
   header("Location: " . $_POST['ritorno']);
   exit;
}

if (isset($_POST['Duplicate'])) {
   // duplico le testate
   $today = gaz_today();
   $numdoc = trovaNuovoNumero($rs_ultimo_documento);  // numero nuovo documento
   $tabella = $gTables['tesdoc'];
   $chiave = $row['id_tes'];
   $sql = "INSERT INTO $tabella (`id_tes`, `seziva`, `tipdoc`, `ddt_type`, `id_doc_ritorno`, `template`, `datemi`, `data_ordine`, `protoc`, `numdoc`, `numfat`, `datfat`, `clfoco`, `pagame`, `ragbol`, `banapp`, `vettor`, `listin`, `destin`, `id_des`, `spediz`, `portos`, `imball`, `traspo`, `speban`, `spevar`, `round_stamp`, `cauven`, `caucon`, `caumag`, `id_agente`, `id_pro`, `sconto`, `expense_vat`, `stamp`, `taxstamp`, `virtual_taxstamp`, `net_weight`, `gross_weight`, `units`, `volume`, `initra`, `geneff`, `id_contract`, `id_con`, `status`, `adminid`, `last_modified`) "
           . "SELECT null, `seziva`, `tipdoc`, `ddt_type`, `id_doc_ritorno`, `template`, '$today', `data_ordine`, `protoc`, '$numdoc', `numfat`, `datfat`, `clfoco`, `pagame`, `ragbol`, `banapp`, `vettor`, `listin`, `destin`, `id_des`, `spediz`, `portos`, `imball`, `traspo`, `speban`, `spevar`, `round_stamp`, `cauven`, `caucon`, `caumag`, `id_agente`, `id_pro`, `sconto`, `expense_vat`, `stamp`, `taxstamp`, `virtual_taxstamp`, `net_weight`, `gross_weight`, `units`, `volume`, '', `geneff`, `id_contract`, `id_con`, `status`, `adminid`, CURRENT_TIMESTAMP FROM $tabella WHERE id_tes = $chiave;";
   mysqli_query($link, $sql);
   $nuovaChiave = gaz_dbi_last_id();

   $tabella = $gTables['tesmov'];
   $chiave = $row['id_con'];
   $sql = "INSERT INTO $tabella (`id_tes`, `caucon`, `descri`, `datreg`, `seziva`, `id_doc`, `protoc`, `numdoc`, `datdoc`, `clfoco`, `regiva`, `operat`, `libgio`, `adminid`, `last_modified`) "
           . "SELECT $nuovaChiave, `caucon`, `descri`, '$today', `seziva`, `id_doc`, `protoc`, '$numdoc', '$today', `clfoco`, `regiva`, `operat`, `libgio`, `adminid`, CURRENT_TIMESTAMP FROM $tabella WHERE id_tes = $chiave;";
   mysqli_query($link, $sql);

   $tabella = $gTables['rigmoc'];
   $chiave = $row['id_con'];
   $sql = "INSERT INTO $tabella (`id_rig`, `id_tes`, `darave`, `codcon`, `import`) "
           . "SELECT null, $nuovaChiave, `darave`, `codcon`, `import` FROM $tabella WHERE id_tes = $chiave;";
   mysqli_query($link, $sql);

   $tabella = $gTables['rigmoi'];
   $chiave = $row['id_con'];
   $sql = "INSERT INTO $tabella (`id_rig`, `id_tes`, `tipiva`, `codiva`, `periva`, `imponi`, `impost`) "
           . "SELECT null, $nuovaChiave, `tipiva`, `codiva`, `periva`, `imponi`, `impost` FROM $tabella WHERE id_tes = $chiave;";
   mysqli_query($link, $sql);

   $tabella = $gTables['rigdoc'];
   $chiave = $row['id_tes'];
   $sql = "INSERT INTO $tabella (`id_rig`, `id_tes`, `tiprig`, `codart`, `descri`, `id_body_text`, `unimis`, `quanti`, `prelis`, `sconto`, `codvat`, `pervat`, `codric`, `provvigione`, `ritenuta`, `id_order`, `id_mag`, `status`) "
           . "SELECT null, $nuovaChiave, `tiprig`, `codart`, `descri`, `id_body_text`, `unimis`, `quanti`, `prelis`, `sconto`, `codvat`, `pervat`, `codric`, `provvigione`, `ritenuta`, `id_order`, `id_mag`, `status` FROM $tabella WHERE id_tes = $chiave;";
   mysqli_query($link, $sql);

   header("Location: " . $_POST['ritorno']);
   exit;
}

if (isset($_POST['Return'])) {
   header("Location: " . $_POST['ritorno']);
   exit;
}
$numddt = gaz_dbi_num_rows($result);
$anagrafica = new Anagrafica();
$cliente = $anagrafica->getPartner($row['clfoco']);
$titolo = "Duplica Documento di Vendita";
require("../../library/include/header.php");
$script_transl = HeadMain();

function trovaNuovoNumero($rs_ultimo_documento) {
   $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
   // se e' il primo documento dell'anno, resetto il contatore
   if ($ultimo_documento) {
      $numdoc = $ultimo_documento['numdoc'] + 1;
   } else {
      $numdoc = 1;
   }
   return $numdoc;
}
?>
<form method="POST">
    <input type="hidden" name="ritorno" value="<?php print $_POST['ritorno']; ?>">
    <div align="center"><font class="FacetFormHeaderFont">Attenzione!!! Stai duplicando il Documento n.<?php print $row['numdoc'] . "/" . $row['seziva'] . " dell'anno " . substr($row['datemi'], 0, 4); ?> </font></div>
    <table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
        <!-- BEGIN Error -->
        <tr>
            <td colspan="2" class="FacetDataTDred">
                <?php
                if (!$message == "") {
                   print "$message";
                }
                ?>
            </td>
        </tr>
        <!-- END Error -->
        <tr>
            <td class="FacetFieldCaptionTD">Protocollo &nbsp;</td><td class="FacetDataTD"><?php print $row["protoc"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Tipo documento &nbsp;</td><td class="FacetDataTD"><?php print $row["tipdoc"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Numero Documento &nbsp;</td><td class="FacetDataTD"><?php print $row["numdoc"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Cliente &nbsp;</td><td class="FacetDataTD"><?php print $cliente["ragso1"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Num. di testate &nbsp;</td><td class="FacetDataTD"><?php print $numddt ?>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="right">Se sei sicuro conferma la duplicazione &nbsp;
                <!-- BEGIN Button Return --><input type="submit" name="Return" value="Indietro"><!-- END Button Return -->&nbsp;
                <!-- BEGIN Button Insert --><input type="submit" name="Duplicate" value="DUPLICA !"><!-- END Button Insert -->&nbsp;
            </td>
        </tr>
    </table>
</form>
<?php
require("../../library/include/footer.php");
?>