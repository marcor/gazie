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

$form = gaz_dbi_get_row($gTables['campi'], "codice", $_GET['codice']);

if (isset($_POST['Delete'])) {
    gaz_dbi_del_row($gTables['campi'], "codice", $_GET['codice']);
	if ($form['id_rif']==0){
		header("Location: report_campi.php");
		exit;
	}else {
		header("Location: stabilim.php");
        exit;
	}
}

if (isset($_POST['Return'])){
	if ($form['id_rif']==0){
        header("Location: report_campi.php");
        exit;
	} else {
		header("Location: stabilim.php");
        exit;
	}
}
$ctrl = gaz_dbi_get_row($gTables['movmag'], "campo_coltivazione", $_GET['codice']);

require("../../library/include/header.php");
$script_transl=HeadMain();
require("./lang.".$admin_aziend['lang'].".php");
if ($form['id_rif']==0){
	$title = ucwords($script_transl['delete'].$strScript["admin_campi.php"][0]);
} else {
	$title = ucwords($script_transl['delete'].$strScript["admin_stabilim.php"][0]);
}
print "<form method=\"POST\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">$title</div>\n";
print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
If (isset($ctrl)) {
	print "<tr><td colspan=\"2\" class=\"FacetFieldCaptionTD\">".$strScript["admin_campi.php"][11]."</td></tr>\n";
}
print "<tr><td class=\"FacetFieldCaptionTD\">".$strScript["admin_campi.php"][1]."</td><td class=\"FacetDataTD\">".$form["codice"]."</td></tr>";
print "<tr><td class=\"FacetFieldCaptionTD\">".$strScript["admin_campi.php"][2]."</td><td class=\"FacetDataTD\">".$form["descri"]."</td></tr>\n";
if ($form['id_rif']==0){
print "<tr><td class=\"FacetFieldCaptionTD\">".$strScript["admin_campi.php"][4]."</td><td class=\"FacetDataTD\">".$form["ricarico"]."</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">".$strScript["admin_campi.php"][5]."</td><td class=\"FacetDataTD\">".$form["annota"]."</td></tr>\n";
}
print "<td align=\"right\"><input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\"></td>";
If (!isset($ctrl)) {
print "<td align=\"right\"><input type=\"submit\" name=\"Delete\" value=\"".strtoupper($script_transl['delete'])."!\"></td></tr>";
} else {
	print "<td></td></tr>";
}
?>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>