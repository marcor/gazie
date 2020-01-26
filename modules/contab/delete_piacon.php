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
$admin_aziend=checkAdmin();
$message = "";
if (isset($_POST['Delete'])) {
    $conctrl = substr($_POST["codice"],3);
    $masctrl = substr($_POST["codice"],0,3);
    $mas0000 = $masctrl."000000";
    if ($conctrl == 0) {
        $result = gaz_dbi_dyn_query ('*', $gTables['clfoco'], "codice like '$masctrl%' and codice <> '$mas0000'");
        $conferma = gaz_dbi_fetch_array($result);
        if ($conferma)
           $message .= "Per eliminare un mastro questo dev'essere vuoto (senza conti)! <br>";
        }
    $rs_check_moc = gaz_dbi_dyn_query("codcon", $gTables['rigmoc'], "codcon = '{$_POST['codice']}'","id_rig asc",0,1);
    $check_moc = gaz_dbi_num_rows($rs_check_moc);
    if ($check_moc > 0) {
        $message .= "Conto non cancellabile perche' ha ".$check_moc." movimenti contabili!<br>";
        }
    if ( $message == "") {
        //aggiorno il db
        gaz_dbi_del_row($gTables['clfoco'], "codice", $_POST['codice']);
        header("Location: report_piacon.php");
        exit;
    }
}
if (isset($_POST['Return'])) {
    header("Location: report_piacon.php");
    exit;
}
if (!isset($_POST['Delete'])) {
    $codice= intval($_GET['codice']);
} else {
    $codice= intval($_POST['codice']);
}
$form = gaz_dbi_get_row($gTables['clfoco'], "codice", $codice);
$titolo="Cancella il Conto";
require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<form method="POST">
<input type="hidden" name="codice" value="<?php print $codice?>">
<div align="center"><font class="FacetFormHeaderFont">Attenzione!!! Eliminazione Conto N.<?php print $codice; ?> </font></div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<tr>
<td colspan="2" class="FacetDataTD" style="color: red;">
<?php
if (! $message == "") {
    echo $message;
} else {
    echo "Sei sicuro di voler rimuovere?";
}
?>
</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Mastro &nbsp;</td>
<td class="FacetDataTD"> <?php print substr($form["codice"],0,3); ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Conto &nbsp;</td>
<td class="FacetDataTD"> <?php print substr($form["codice"],3); ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Descrizione  &nbsp;</td>
<td class="FacetDataTD"> <?php print $form["descri"]; ?>&nbsp;</td>
</tr>
<tr>
<td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
<input title="Torna indietro" type="submit" name="Return" value="Indietro">&nbsp;
<input title="Conferma l'eliminazione" type="submit" name="Delete" value="ELIMINA !">&nbsp;
</td>
</tr></table>
</form>
<?php
require("../../library/include/footer.php");
?>