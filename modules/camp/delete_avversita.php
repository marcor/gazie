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
$titolo="Cancella l' avversità";
if (isset($_POST['Delete']))
    {
        $result = gaz_dbi_del_row($gTables['camp_avversita'], "id_avv", $_POST['id_avv']);
        header("Location: admin_avv.php");
        exit;
    }

if (isset($_POST['Return']))
        {
        header("Location: admin_avv.php");
        exit;
        }

if (!isset($_POST['Delete']))
    {
    $id_avv= $_GET['id_avv'];
    $form = gaz_dbi_get_row($gTables['camp_avversita'], "id_avv", $id_avv);
    }

require("../../library/include/header.php"); HeadMain();
?>
<form method="POST">
<input type="hidden" name="id_avv" value="<?php print $id_avv?>">
<div align="center"><font class="FacetFormHeaderFont">Attenzione!!! Eliminazione avversità  ID: <?php print $id_avv; ?> </font></div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<tr>
<td colspan="2" class="FacetDataTDred">
<?php
if (! $message == "")
    {
    print "$message";
    }
?>
</td>
</tr>
<tr>
<tr>
<td class="FacetFieldCaptionTD">ID avversità &nbsp;</td>
<td class="FacetDataTD"> <?php print $form["id_avv"]; ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Nome avversità &nbsp;</td>
<td class="FacetDataTD"><?php print $form["nome_avv"] ?>&nbsp;</td>
</tr>

<td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
<!-- BEGIN Button Return --><input type="submit" name="Return" value="Indietro"><!-- END Button Return -->&nbsp;
<!-- BEGIN Button Insert --><input type="submit" name="Delete" value="ELIMINA !"><!-- END Button Insert -->&nbsp;
</td>
</tr>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>