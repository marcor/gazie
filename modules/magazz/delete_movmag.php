<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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

$admin_aziend = checkAdmin();


if (isset($_POST['Delete'])) {
    $upd_mm = new magazzForm;
    $form = gaz_dbi_get_row($gTables['movmag'], 'id_mov', intval($_POST['id_mov']));
    $upd_mm->uploadMag('DEL', $form['tipdoc'], '', '', '', '', '', '', '', '', '', '', $form['id_mov'], $admin_aziend['stock_eval_method']);
    if ($form['id_rif'] > 0) {  //se il movimento di magazzino � stato generato da un rigo di documento lo azzero
        gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $form['id_rif'], 'id_mag', 0);
    }
	$item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
	if ($item['SIAN']>0){ // se è SIAN cancello anche il suo movimento
		gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", intval($_POST['id_mov']));
	}

    header("Location: report_movmag.php");
    exit;
} else {
    $form = gaz_dbi_get_row($gTables['movmag'], 'id_mov', $_GET['id_mov']);
    $causal = gaz_dbi_get_row($gTables['caumag'], 'codice', $form['caumag']);
}

if (isset($_POST['Return'])) {
    header("Location: report_movmag.php");
    exit;
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, 0, 'admin_movmag');
print "<form method=\"POST\">\n";
echo "<input type=\"hidden\" value=\"" . $form['id_mov'] . "\" name=\"id_mov\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['del_this'] . "</div>\n";
print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
$anagrafica = new Anagrafica();
$a_part = $anagrafica->getPartner($form['clfoco']);
$partner = $a_part['ragso1'] . " " . $a_part['ragso2'];
print "<tr><td class=\"FacetFieldCaptionTD\">n. ID </td><td class=\"FacetDataTD\">" . $form["id_mov"] . "</td></tr>";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[1] . "</td><td class=\"FacetDataTD\">" . $form["datreg"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[2] . "</td><td class=\"FacetDataTD\">" . $causal["descri"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl["operat"] . "</td><td class=\"FacetDataTD\">" . $script_transl["operat_value"][$form["operat"]] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl["partner"] . "</td><td class=\"FacetDataTD\">" . $partner . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[8] . "</td><td class=\"FacetDataTD\">" . $form["datdoc"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">" . $form["artico"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[12] . "</td><td class=\"FacetDataTD\">" . $form["quanti"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[13] . "</td><td class=\"FacetDataTD\">" . $form["prezzo"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[14] . "</td><td class=\"FacetDataTD\">" . $form["scorig"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[10] . "</td><td class=\"FacetDataTD\">" . $form["scochi"] . "</td></tr>\n";
$valore = CalcolaImportoRigo($form['quanti'], $form['prezzo'], $form['scorig']);
$valore = CalcolaImportoRigo(1, $valore, $form['scochi']);
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl["amount"] . "</td><td class=\"FacetDataTD\">" . gaz_format_number($valore) . "</td></tr>\n";
print "<td colspan=\"2\" align=\"right\"><input type=\"submit\" name=\"Return\" value=\"" . $script_transl['return'] . "\"><input type=\"submit\" name=\"Delete\" value=\"" . strtoupper($script_transl['delete']) . "!\"></td></tr>";
?>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>