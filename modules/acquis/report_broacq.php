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

$message = "";
$anno = date("Y");
require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<table align="center" width="80%">
<tr>
<td><font class="FacetFormHeaderFont"><a href="admin_broacq.php?tipdoc=APR" accesskey="p">Nuovo Preventivo</a></td>
<td><font class="FacetFormHeaderFont"><a href="admin_broacq.php?tipdoc=AOR" accesskey="o">Nuovo Ordine</a></td>
<br>
</tr>
</table>
<div align="center" class="FacetFormHeaderFont">Preventivi e ordini a fornitori</div>
<?php
$where = "tipdoc = 'APR' or tipdoc = 'AOR'";
$recordnav = new recordnav($gTables['tesbro'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<?php
$headers_tesdoc = array  (
              "ID" => "id_tes",
              "Tipo" => "tipdoc",
              "Numero" => "numdoc",
              "Data" => "datemi",
              "Cliente" => "clfoco",
              "Status" => "",
              "Stampa" => "",
              "Cancella" => ""
              );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
if (!isset($_GET['flag_order']))
       $orderby = "id_tes desc";
$result = gaz_dbi_dyn_query ('*', $gTables['tesbro'], $where, $orderby, $limit, $passo);
$ctrlprotoc = "";
$anagrafica = new Anagrafica();
while ($a_row = gaz_dbi_fetch_array($result)) {
    if ($a_row["tipdoc"] == 'APR') {
        $tipodoc="Preventivo";
        $modulo="stampa_prefor.php?id_tes=".$a_row['id_tes'];
        $modifi="admin_broacq.php?id_tes=".$a_row['id_tes']."&Update";
    }
    if ($a_row["tipdoc"] == 'AOR') {
        $tipodoc="Ordine";
        $modulo="stampa_ordfor.php?id_tes=".$a_row['id_tes'];
        $modifi="admin_broacq.php?id_tes=".$a_row['id_tes']."&Update";
    }
    $cliente = $anagrafica->getPartner($a_row['clfoco']);
    print "<tr>";
    if (! empty ($modifi)) {
       print "<td class=\"FacetDataTD\"><a href=\"".$modifi."\">".$a_row["id_tes"]."</td>";
    } else {
       print "<td class=\"FacetDataTD\">".$a_row["id_tes"]." &nbsp;</td>";
    }
    print "<td class=\"FacetDataTD\">".$tipodoc." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$a_row["numdoc"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">".gaz_format_date($a_row["datemi"])." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$cliente["ragso1"]."&nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$a_row["status"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\"><a href=\"".$modulo."\"><center><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
    print "<td class=\"FacetDataTD\"><a href=\"delete_broacq.php?id_tes=".$a_row['id_tes']."\"><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
    print "</tr>";
}
?>
</table>
</body>
</html>