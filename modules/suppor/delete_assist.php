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

require("../../modules/magazz/lib.function.php");
$admin_aziend=checkAdmin();

$upd_mm = new magazzForm;
$docOperat = $upd_mm->getOperators();
$message = "Sei sicuro di voler rimuovere ?";
if (!isset($_POST['ritorno'])) {
   $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

if (isset($_GET['id'])){ //sto eliminando un singolo documento
   $result = gaz_dbi_dyn_query("*", $gTables['assist'], "id = ".intval($_GET['id']));
   $row = gaz_dbi_fetch_array($result);
} else {
   header("Location: ".$_POST['ritorno']);
   exit;
}

if (!$row) {
    header("Location: ".$_POST['ritorno']);
    exit;
}

if (isset($_POST['Delete'])){
	gaz_dbi_del_row($gTables['assist'], "id", $row['id']);
   header("Location: ".$_POST['ritorno']);
   exit;   
}

if (isset($_POST['Return'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
}

$numddt = gaz_dbi_num_rows($result);
$anagrafica = new Anagrafica();
$cliente = $anagrafica->getPartner($row['clfoco']);
$titolo="Elimina Documento ";
require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<form method="POST">
<input type="hidden" name="ritorno" value="<?php print $_POST['ritorno'];?>">
<div align="center"><font class="FacetFormHeaderFont">Attenzione!!! Stai eliminando il Documento n.<?php print $row['numdoc']."/".$row['seziva']." dell'anno ".substr($row['datemi'],0,4); ?> </font></div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<!-- BEGIN Error -->
<tr>
<td colspan="2" class="FacetDataTDred">
<?php
if (! $message == "") {
    print "$message";
}
?>
</td>
</tr>
<!-- END Error -->
<tr>
<td class="FacetFieldCaptionTD">ID &nbsp;</td><td class="FacetDataTD"><?php print $row["id"] ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Numero Documento &nbsp;</td><td class="FacetDataTD"><?php print $row["codice"] ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Oggetto &nbsp;</td><td class="FacetDataTD"><?php print $row["oggetto"] ?>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Cliente &nbsp;</td><td class="FacetDataTD"><?php print $cliente["ragso1"] ?>&nbsp;</td>
</tr>
<tr>
<td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
<!-- BEGIN Button Return --><input type="submit" name="Return" value="Indietro"><!-- END Button Return -->&nbsp;
<!-- BEGIN Button Insert --><input type="submit" name="Delete" value="ELIMINA !"><!-- END Button Insert -->&nbsp;
</td>
</tr>
</table>
</form>
</div><!-- chiude div container role main --></body>
</html>