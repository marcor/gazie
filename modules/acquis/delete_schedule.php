<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$rs = gaz_dbi_dyn_query($gTables['paymov'].".id_tesdoc_ref,".$gTables['tesmov'].".descri, ".$gTables['clfoco'].".descri AS ragsoc",
                        $gTables['paymov']." LEFT JOIN ".$gTables['rigmoc']." ON ".$gTables['paymov'].".id_rigmoc_doc = ".$gTables['rigmoc'].".id_rig
                        LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['tesmov'].".id_tes = ".$gTables['rigmoc'].".id_tes
                        LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['clfoco'].".codice = ".$gTables['tesmov'].".clfoco",
                        $gTables['paymov'].".id_tesdoc_ref = ".substr($_GET['id_tesdoc_ref'],0,15));
$form = gaz_dbi_fetch_array($rs);
if (!isset($_POST['ritorno'])) {
        $form['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Delete'])) {
    gaz_dbi_del_row($gTables['paymov'], 'id_tesdoc_ref', substr($_POST['id_tesdoc_ref'],0,15));
    header("Location: ".$_POST['ritorno']);
    exit;
    }

if (isset($_POST['Return'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
}

require("../../library/include/header.php");
$script_transl = HeadMain('delete_schedule');
?>
<form method="POST">
<input type="hidden" name="ritorno" value="<?php print $form['ritorno']; ?>">
<input type="hidden" name="id_tesdoc_ref" value="<?php print $form['id_tesdoc_ref']; ?>">
<div align="center" class="FacetFormHeaderFont">
<?php echo $script_transl['warning'].'!!! '.$script_transl['title'] ; ?></div>
<table class="Tsmall">
<tr>
<?php
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['id_tesdoc_ref']." : </td>
     <td class=\"FacetDataTD\">\n".$form['id_tesdoc_ref']."</td>";
?>
</tr>
<tr>
<?php
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['descri']." : </td>
     <td class=\"FacetDataTD\">\n".$form['descri']."</td>";
?>
</tr>
<tr>
<?php
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['ragsoc']." : </td>
     <td class=\"FacetDataTD\">\n".$form['ragsoc']."</td>";
?>
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
</div><!-- chiude div container role main --></body>
</html>