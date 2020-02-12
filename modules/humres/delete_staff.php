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
$message='';
if (isset($_POST['Delete'])) {
    $rs_check_mov = gaz_dbi_dyn_query("codcon", $gTables['rigmoc'], "codcon = '" . intval($_POST['codice']) . "'");
    $check_mov = gaz_dbi_num_rows($rs_check_mov);
    if ($check_mov > 0) {
        $message .= "Collaboratore non cancellabile perche' ha " . $check_mov . " movimenti contabili!<br>";
    }
    if ($message == "") {
        gaz_dbi_del_row($gTables['clfoco'], "codice", intval($_POST['codice']));
        gaz_dbi_del_row($gTables['staff'], "id_clfoco", intval($_POST['codice']));
        header("Location: staff_report.php");
        exit;
    }
}

if (isset($_POST['Return'])) {
    header("Location: staff_report.php");
    exit;
}


if (!isset($_POST['codice'])) {
    $codice = intval($_GET['codice']);
} else {
    $codice = intval($_POST['codice']);
}
$anagrafica = new Anagrafica();
$form = $anagrafica->getPartner($codice);
$form += gaz_dbi_get_row($gTables['staff'], 'id_clfoco', $form['codice']);

require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<form method="POST" >
    <input type="hidden" name="codice" value="<?php echo $codice ?>">
<?php        
echo '<div class="alert alert-danger text-center" role="alert">' . $script_transl['title'] . '</div>';
?>
    <table class="GazFormDeleteTable">
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
            <td class="FacetFieldCaptionTD"><?php echo $script_transl["codice"]; ?> &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["codice"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD"><?php echo $script_transl["ragso1"]; ?> &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["ragso1"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD"><?php echo $script_transl["ragso2"]; ?> &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["ragso2"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD"><?php echo $script_transl["sexper"]; ?> &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["sexper"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD"><?php echo $script_transl["citspe"]; ?> &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["citspe"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD"><?php echo $script_transl["job_title"]; ?> &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["job_title"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD"><?php echo $script_transl["telefo"]; ?> &nbsp;</td>
            <td class="FacetDataTD"><?php echo $form["telefo"]; ?>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
                <input title="Torna indietro"  type="submit" name="Return" value="Indietro">&nbsp;
                <input title="Elimina definitivamente dall'archivio"  type="submit" name="Delete" class="btn btn-danger" value="Elimina">&nbsp;
            </td>
        </tr></table>
</form>
<?php
require("../../library/include/footer.php");
?>