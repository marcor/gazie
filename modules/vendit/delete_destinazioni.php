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
require("../../library/include/datlib.inc.php");

$admin_aziend = checkAdmin();
$titolo = "Eliminazione destinazione";
$message = "Sei sicuro di voler rimuovere ?";
if (isset($_POST['Delete'])) {
   $result = gaz_dbi_del_row($gTables['destina'], "codice", substr($_POST['codice'], 0, 30));
   header("Location: report_destinazioni.php");
   exit;
} else {
   $form = gaz_dbi_get_row($gTables['destina'], "codice", substr($_GET['codice'], 0, 30));
}

if (isset($_POST['Return'])) {
   header("Location: report_destinazioni.php");
   exit;
}

$codice = $_GET['codice'];
require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<form method="POST" >
    <input type="hidden" name="codice" value="<?php echo $codice ?>">
    <div align="center"><font class="FacetFormHeaderFont">Attenzione!!! Eliminazione Destinazione Codice: <?php echo $codice; ?> </font></div>
    <table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
        <tr>
            <td colspan="2" class="FacetDataTD"  style="color: red;">
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
            <td class="FacetFieldCaptionTD">Codice Destinazione &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["codice"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Ragione sociale 1 &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["unita_locale1"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Ragione sociale 2 &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["unita_locale2"]; ?>&nbsp;</td>
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
            <td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
                <input title="Torna indietro"  type="submit" name="Return" value="Indietro">&nbsp;
                <input title="Elimina definitivamente dall'archivio"  type="submit" name="Delete" value="ELIMINA !">&nbsp;
            </td>
        </tr></table>
</form>
<?php
require("../../library/include/footer.php");
?>