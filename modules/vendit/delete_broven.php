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
$message = "Sei sicuro di voler rimuovere ?";
if (!isset($_POST['ritorno'])) {
        $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Delete'])) {
    //procedo all'eliminazione della testata e dei righi...
    //cancello la testata
    gaz_dbi_del_row($gTables['tesbro'], "id_tes", intval($_POST['id_tes']));
    //... e i righi
    $rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes =". intval($_POST['id_tes']),"id_tes DESC");
    while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
           gaz_dbi_del_row($gTables['rigbro'], "id_rig", $a_row['id_rig']);
           gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigbro' AND id_ref ",$a_row['id_rig']);
           }
    header("Location: ".$_POST['ritorno']);
    exit;
    }

if (isset($_POST['Return'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
    }

//recupero i documenti non contabilizzati
$result = gaz_dbi_dyn_query("*", $gTables['tesbro'], "id_tes = ".intval($_GET['id_tes']) ,"id_tes desc");
$rs_righi = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = ".intval($_GET['id_tes']) ,"id_tes desc");
$numrig = gaz_dbi_num_rows($rs_righi);
$form = gaz_dbi_fetch_array($result);
    switch($form['tipdoc']) {
        case "VPR":
        $tipobro="il preventivo";
        break;
        case "VOR":
        case "VOW":
        $tipobro="l'ordine";
        break;
        case "VCO":
        $tipobro="lo scontrino";
        break;
        }
$titolo="Elimina ".$tipobro." n.".$form['numdoc'];
require("../../library/include/header.php");
$script_transl=HeadMain();
$anagrafica = new Anagrafica();
$cliente = $anagrafica->getPartner($form['clfoco']);
?>
<form method="POST">
<input type="hidden" name="id_tes" value="<?php print $form['id_tes']; ?>">
<input type="hidden" name="ritorno" value="<?php print $_POST['ritorno']; ?>">
<div><font class="text-center text-danger">Attenzione!!! Stai eliminando <?php echo $tipobro." n.".$form['numdoc']; ?> </font></div>
<table class="GazFormDeleteTable">
<!-- BEGIN Error -->
<tr>
    <td colspan="2" class="FacetDataTD" style="color: red;">
    <?php
    if (! $message == "") {
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
    <td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
<input type="submit" name="Delete" class="btn btn-danger" value="Elimina">&nbsp;
    </td>
</tr>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>