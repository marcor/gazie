<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
  (http://www.devincentiis.it)
  <http://gazie.sourceforge.net>

  OSCAT - Gestione Centri Ass. Tecnica
  Copyright (C) 2005 - Maurizio Lapini Pistoia (PT)
  (maurizio@drime.it)
  <http://www.lapini.com>
  <http://www.assistenzanet.com>
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

$titolo = "Cancella Fornitore";

$masfor = $admin_aziend['masfor'] . "000000";
$fornit = $admin_aziend['masfor'];
$message = "";

$anagrafica = new Anagrafica();

if (isset($_POST['Delete'])) {
    $rs_check_mov = gaz_dbi_dyn_query("clfoco", $gTables['tesmov'], "clfoco = '{$_POST['codice']}'", "id_tes asc", 0, 1);
    $check_mov = gaz_dbi_num_rows($rs_check_mov);
    $rs_check_doc = gaz_dbi_dyn_query("clfoco", $gTables['tesdoc'], "clfoco = '{$_POST['codice']}'", "id_tes asc", 0, 1);
    $check_doc = gaz_dbi_num_rows($rs_check_doc);
    $rs_check_bro = gaz_dbi_dyn_query("clfoco", $gTables['tesbro'], "clfoco = '{$_POST['codice']}'", "id_tes asc", 0, 1);
    $check_bro = gaz_dbi_num_rows($rs_check_bro);
    if ($check_mov > 0) {
        $message .= "Fornitore non cancellabile perche' ha " . $check_mov . " movimenti contabili!<br>";
    }
    if ($check_doc > 0) {
        $message .= "Fornitore non cancellabile perche' ha " . $check_doc . " documenti fiscali!<br>";
    }
    if ($check_bro > 0) {
        $message .= "Fornitore non cancellabile perche' ha " . $check_bro . " documenti non fiscali!<br>";
    }
    if ($message == "") {
        $anagrafica->deletePartner($_POST['codice']);
        header("Location: report_fornit.php");
        exit;
    }
}

if (isset($_POST['Return'])) {
    header("Location: report_fornit.php");
    exit;
}

if (!isset($_POST['codice'])) {
    $codice = $masfor + $_GET['codice'];
} else {
    $codice = $_POST['codice'];
}

$form = $anagrafica->getPartner($codice);
require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<form method="POST">
    <input type="hidden" name="codice" value="<?php echo $codice; ?>">
    <div><font class="text-center text-danger">Attenzione!!! Eliminazione Fornitore Codice: <?php echo $codice; ?> </font></div>
    <table class="GazFormDeleteTable">
        <tr>
            <td colspan="2" class="FacetDataTD" style="color: red;">
                <?php
                if (!$message == "") {
                    echo $message;
                } else {
                    echo "Sei sicuro di voler rimuovere?";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Codice Fornitore &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["codice"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Ragione sociale 1 &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["ragso1"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Ragione sociale 2 &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["ragso2"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Indirizzo &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["indspe"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">CAP &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["capspe"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Citta' - Provincia &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["citspe"] ?>&nbsp;- <?php echo $form["prospe"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Telefono e/o fax &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["telefo"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Codice Fiscale - Partita IVA &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["codfis"] ?>&nbsp;- <?php echo $form["pariva"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
                <input title="Torna indietro"  type="submit" name="Return" value="Indietro"> &nbsp;
                <input title="Elimina definitivamente dall'archivio"  type="submit" name="Delete" value="Elimina"> &nbsp;
            </td>
        </tr></table>
</form>
<?php
require("../../library/include/footer.php");
?>