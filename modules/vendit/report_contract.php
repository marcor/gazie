<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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

$anagrafica = new Anagrafica();

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
   $where = "vat_section = '$auxil'";
} else {
   $auxil = 1;
   $where = "vat_section = '$auxil'";
}
if (isset($_GET['all'])) {
   $where = "vat_section = '$auxil' ";
   $auxil = $_GET['auxil']."&all=yes";
   $passo = 100000;
   $protocollo ='';
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new GAzieForm();
echo "<form method=\"GET\" name=\"report\">\n";
echo "<input type=\"hidden\" name=\"hidden_req\">\n";
echo "<table align=\"center\" width=\"70%\">\n";
echo "<tr class=\"FacetFormHeaderFont\">\n";
echo "<td><a href=\"admin_contract.php?Insert&vat_section=".substr($auxil,0,1)."\">".$script_transl['link1']."</a>
      </td>\n";
echo "<td><a href=\"invoice_from_contract.php?vat_section=".substr($auxil,0,1)."\">".$script_transl['link2']."</a>
      </td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title'].$script_transl['vat_section'];
$gForm->selectNumber('auxil',$auxil,0,1,3,'FacetSelect','auxil');
echo "</div>\n";
if (!isset($_GET['field']) or ($_GET['field'] == 2) or(empty($_GET['field'])))
        $orderby = "conclusion_date DESC, doc_number DESC";
$recordnav = new recordnav($gTables['contract'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<td colspan="2" class="FacetFieldCaptionTD"><?php echo $script_transl['number']; ?> :
<input type="text" name="doc_number" value="<?php if (isset($doc_number)) print $doc_number; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td>
<input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
            $script_transl['id'] => "id_customer",
            $script_transl['date'] => "conclusion_date",
            $script_transl['number'] => "doc_number",
            $script_transl['customer'] => "id_customer",
            $script_transl['current_fee'] => "current_fee",
            $script_transl['print'] => "",
            $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query('*',$gTables['contract'], $where, $orderby,$limit, $passo);
while ($row = gaz_dbi_fetch_array($result)) {
        $cliente = $anagrafica->getPartner($row['id_customer']);
        print "<tr>";
        print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"admin_contract.php?Update&id_contract=".$row['id_contract']."\">".$row["id_contract"]."</a></td>";
        print "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($row["conclusion_date"])."</td>";
        print "<td class=\"FacetDataTD\" align=\"center\">".$row["doc_number"]." &nbsp;</td>";
        print "<td class=\"FacetDataTD\" align=\"center\">".$cliente['ragso1']."&nbsp;</td>";
        print "<td class=\"FacetDataTD\" align=\"center\">".$row["current_fee"]." &nbsp;</td>";
        print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"print_contract.php?id_contract=".$row['id_contract']."\"><img src=\"../../library/images/stampa.gif\" border=\"0\"></a></td>";
        print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_contract.php?id_contract=".$row['id_contract']."\"><img src=\"../../library/images/x.gif\" border=\"0\"></a></td>";
        print "</tr>\n";
}
?>
</form>
</table>
</body>
</html>