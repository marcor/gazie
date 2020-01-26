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
if (isset($_POST['Delete'])){
        gaz_dbi_del_row($gTables['caucon'], "codice", substr($_POST['codice'],0,3));
		//cancello anche i righi 
		gaz_dbi_del_row($gTables['caucon_rows'], "caucon_cod", substr($_POST['codice'],0,3));
        header("Location: report_caucon.php");
        exit;
} else {
    $form = gaz_dbi_get_row($gTables['caucon'], "codice", substr($_GET['codice'],0,3));
}

if (isset($_POST['Return'])){
        header("Location: report_caucon.php");
        exit;
}
require("../../library/include/header.php");
$script_transl=HeadMain('','','admin_caucon');
?>
<form method="POST">
<input type="hidden" name="codice" value="<?php echo $form['codice']; ?>">
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['warning'].'!!! '.$script_transl['delete'].$script_transl['del_this']." '".substr($_GET['codice'],0,3)."'" ; ?></font></div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['descri']; ?></td>
    <td class="FacetDataTD"><?php echo $form["descri"]; ?> &nbsp;</td>
</tr>
<tr>
    <td class="FacetFieldCaptionTD" ><?php echo $script_transl['insdoc']; ?></td>
    <td class="FacetDataTD"><?php echo $script_transl['insdoc_value'][$form["insdoc"]]; ?></td>
</tr>
<tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['regiva']; ?></td>
    <td class="FacetDataTD"><?php echo $script_transl['regiva_value'][$form["regiva"]]; ?> &nbsp;</td>

</tr>
<tr>
<td colspan="2" class="FacetFormHeaderFont" align="center"><?php echo $script_transl['head']; ?></td>
</tr>
<?php
    $result = gaz_dbi_dyn_query("*", $gTables['caucon_rows'],"caucon_cod = '".$form['codice']."'");
    while ($a_row = gaz_dbi_fetch_array($result)) {
		$descri_acc = gaz_dbi_get_row($gTables['clfoco'], "codice", $a_row["clfoco_ref"]);
		echo '<tr><td class="FacetDataTD">'.$descri_acc["descri"].'</td><td class="FacetDataTD">';
        echo $script_transl['daav_value'][$a_row["dare_avere"]];
        echo "</td></tr>\n";
    }

for( $i = 1; $i <= 6; $i++ ) {
}
?>
<tr>
<td align="right">
<?php
echo '<input type="submit" accesskey="r" name="Return" value="'.$script_transl['return'].'"></td><td>
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