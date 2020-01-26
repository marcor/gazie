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
    $result = gaz_dbi_del_row($gTables['imball'], "codice", intval($_POST['codice']));
    header("Location: report_imball.php");
    exit;
}
if (isset($_POST['Return'])) {
    header("Location: report_imball.php");
    exit;
}
if (!isset($_POST['Delete'])) {
    $form = gaz_dbi_get_row($gTables['imball'], "codice", intval($_GET['codice']));
}
require("../../library/include/header.php");
$script_transl=HeadMain('','','admin_imball');
?>
<form method="POST">
<input type="hidden" name="codice" value="<?php print intval($_GET['codice'])?>">
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['warning'].'!!! '.$script_transl['delete'].$script_transl['del_this'].' n.'.intval($_GET['codice']); ?> </div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['descri']; ?></td>
    <td class="FacetDataTD"><?php print $form["descri"] ?>&nbsp;</td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['annota']; ?></td>
    <td class="FacetDataTD"><?php print $form["annota"] ?>&nbsp;</td>
  </tr>
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