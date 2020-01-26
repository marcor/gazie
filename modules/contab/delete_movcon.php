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
if (isset($_POST['Delete'])) {
    $calc = new Schedule;
    //cancello i righi contabili
    $result = gaz_dbi_dyn_query("*", $gTables['rigmoc'],"id_tes = ".intval($_POST['id_tes']),"id_tes asc");
    while ($a_row = gaz_dbi_fetch_array($result)) {
        gaz_dbi_del_row($gTables['rigmoc'], "id_rig", $a_row['id_rig']);
        // elimino le eventuali partite aperte
        $calc->updatePaymov($a_row['id_rig']);
    }
    //cancello i righi iva
    $result = gaz_dbi_dyn_query("*", $gTables['rigmoi'],"id_tes = ".intval($_POST['id_tes']),"id_tes asc");
    while ($a_row = gaz_dbi_fetch_array($result)) {
        gaz_dbi_del_row($gTables['rigmoi'], "id_rig", $a_row['id_rig']);
    }
    //cancello la testata
    gaz_dbi_del_row($gTables['tesmov'], "id_tes", intval($_POST['id_tes']));
    // se si riferisce ad un documento contabilizzato annullo il riferimento al movimento
    gaz_dbi_put_query($gTables['tesdoc'], 'id_con ='.intval($_POST['id_tes']),'id_con',0);
    // se si riferisce ad un effetto contabilizzato annullo il riferimento al movimento
    gaz_dbi_put_query($gTables['effett'], 'id_con ='.intval($_POST['id_tes']),'id_con',0);
    //cancello anche l'eventuale rigo sul registro beni ammortizzabili
    gaz_dbi_del_row($gTables['assets'], "id_movcon", intval($_POST['id_tes']));
    header("Location: report_movcon.php");
    exit;
}

if (isset($_POST['Return'])) {
    header("Location: report_movcon.php");
    exit;
}

if (!isset($_POST['Delete'])) {
    $id_tes= intval($_GET['id_tes']);
    $form = gaz_dbi_get_row($gTables['tesmov'], "id_tes", $id_tes);
    //recupero i righi contabili
    $rs_righcon = gaz_dbi_dyn_query("*", $gTables['rigmoc'], "id_tes = ".intval($form['id_tes']),"id_rig asc");
    //recupero i righi iva
    $rs_righiva = gaz_dbi_dyn_query("*", $gTables['rigmoi'], "id_tes = ".intval($form['id_tes']),"id_rig asc");
    $righiva = gaz_dbi_fetch_array($rs_righiva);
}
require("../../library/include/header.php");
$script_transl=HeadMain('','','admin_movcon');
?>
<form method="POST">
<input type="hidden" name="id_tes" value="<?php print intval($_GET['id_tes'])?>">
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['warning'].'!!! '.$script_transl['delete'].$script_transl['del_this'].' n.'.intval($_GET['id_tes']); ?> </div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['date_reg']; ?></td>
        <td class="FacetDataTD" colspan=2> <?php print $form["datreg"]; ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['caucon']; ?></td>
    <td class="FacetDataTD" colspan=2><?php print $form["caucon"] ?>&nbsp;</td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['descri']; ?></td>
    <td class="FacetDataTD" colspan=2><?php print $form["descri"] ?>&nbsp;</td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['insdoc']; ?></td>
    <td class="FacetDataTD" colspan=2> <?php if ($form["numdoc"] > 0) echo $form["numdoc"]."/".$form["seziva"]." del ".$form["datdoc"]." prot.".$form["protoc"]; else echo "No"; ?>&nbsp;</td>
  </tr>
<tr><td colspan=3><hr></td></tr>
    <?php
    while ($a_row = gaz_dbi_fetch_array($rs_righcon)) {
        $descricon = gaz_dbi_get_row($gTables['clfoco'],"codice",$a_row['codcon']);
        echo "<tr><td class=\"FacetFieldCaptionTD\">".$descricon['descri']." &nbsp; </td>";
        echo "<td class=\"FacetDataTD\" align=\"right\">".$a_row["import"]." &nbsp; </td>";
        echo "<td class=\"FacetDataTD\" align=\"right\">".$a_row["darave"]." &nbsp; </td></tr>";
    }
    ?>
<tr>
 <td align="right">
<?php
echo '<input type="submit" accesskey="r" name="Return" value="'.$script_transl['return'].'"></td><td colspan="2">
     '.ucfirst($script_transl['safe']);
echo ' <input type="submit" accesskey="d" name="Delete" value="'.$script_transl['delete'].'">';
?>
 </td>
</tr>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>