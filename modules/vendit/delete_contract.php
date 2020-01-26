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

if (!isset($_POST['ritorno'])) {
        $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
        $form['ritorno'] = $_POST['ritorno'];
}

if (isset($_GET['id_contract'])) {
   $row = gaz_dbi_get_row($gTables['contract'], 'id_contract', intval($_GET['id_contract']));
   $customer = gaz_dbi_get_row($gTables['clfoco'], 'codice', $row['id_customer']);
} else { //non ci sono dati sufficenti per stabilire cosa eliminare
    header("Location: ".$form['ritorno']);
    exit;
}

if (isset($_POST['del'])){
    gaz_dbi_del_row($gTables['contract'], 'id_contract', intval($_GET['id_contract']));
    gaz_dbi_del_row($gTables['contract_row'], 'id_contract', intval($_GET['id_contract']));
    header("Location: ".$form['ritorno']);
    exit;
}

if (isset($_POST['return'])) {
    header("Location: ".$form['ritorno']);
    exit;
}

require("../../library/include/header.php");
require("./lang.".$admin_aziend['lang'].".php");
$script_transl=HeadMain();
echo "<form method=\"POST\" name=\"contract\">\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderRed\">".$script_transl['alert']."</div>";
echo "<table class=\"Tsmall\">\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$strScript['admin_contract.php']['doc_number']."</td><td class=\"FacetDataTD\">\n";
echo $row['doc_number'];
echo "\t </td>\n";
echo "\t </tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$strScript['report_contract.php']['customer']."</td><td class=\"FacetDataTD\">\n";
echo $customer['descri'];
echo "\t </td>\n";
echo "\t </tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$strScript['admin_contract.php']['conclusion_date']."</td><td class=\"FacetDataTD\">\n";
echo $row['conclusion_date'];
echo "\t </td>\n";
echo "\t </tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$strScript['admin_contract.php']['start_date']."</td><td class=\"FacetDataTD\">\n";
echo $row['start_date'];
echo "\t </td>\n";
echo "\t </tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$strScript['admin_contract.php']['current_fee']."</td><td class=\"FacetDataTD\">\n";
echo $row['current_fee'];
echo "\t </td>\n";
echo "\t </tr>\n";
echo "<tr>\n";
echo "<td>";
echo "<input type=\"submit\" name=\"return\" value=\"".$script_transl['return']."\"></td>\n";
echo "<td align=\"right\">";
echo "<input type=\"submit\" name=\"del\" value=\"".$script_transl['submit']."\"></td>\n";
echo "\t </tr>\n";
?>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>