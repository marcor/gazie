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
$admin_aziend=checkAdmin(9);
$message='';
if (isset($_POST['Delete'])) {
    $ricerca=$_POST["user_name"];
    $rs_utente = gaz_dbi_dyn_query("*", $gTables['admin'], "user_name <> '$ricerca' AND Abilit = 9 ", "user_name",0,1);
    $last_admin = gaz_dbi_fetch_array($rs_utente);
    if (!$last_admin) {
        $message = "del_err";
        $form = gaz_dbi_get_row($gTables['admin'], "user_name", substr($_GET["user_name"],0,15));
    }
    if ( $message == "") { // nessun errore
        gaz_dbi_del_row($gTables['admin'], "user_name",substr($_POST["user_name"],0,15));
        gaz_dbi_del_row($gTables['admin_module'], "adminid",substr($_POST["user_name"],0,15));
        gaz_dbi_del_row($gTables['admin_config'], "adminid",substr($_POST["user_name"],0,15));
        header("Location: report_utenti.php");
        exit;
    }
} else {
    $form = gaz_dbi_get_row($gTables['admin'], "user_name", substr($_GET["user_name"],0,15));
}
if (isset($_POST['Return'])) {
   header("Location: report_utenti.php");
   exit;
}
require("../../library/include/header.php");
$script_transl=HeadMain('','','admin_utente');
?>
<form method="POST">
<input type="hidden" name="user_name" value="<?php print $form["user_name"];?>">
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['warning'].'!!! '.$script_transl['delete'].$script_transl['del_this'].': '.$form["user_name"]; ?></div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<?php
if (!$message == "") {
    echo '<tr><td colspan="2" class="FacetDataTDred">'.$script_transl[$message]."</td></tr>\n";
}
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_lastname']; ?></td>
<td class="FacetDataTD"><?php print $form["user_lastname"] ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_firstname']; ?></td>
<td class="FacetDataTD"><?php print $form["user_firstname"] ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Abilit']; ?></td>
<td class="FacetDataTD"><?php print $form["Abilit"] ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Access']; ?></td>
<td class="FacetDataTD"><?php print $form["Access"] ?>&nbsp;</td>
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