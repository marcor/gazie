<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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
<div align="center" class="FacetFormHeaderFont">Bonifici e Ordini di Addebito</div>
<?php
$where = "tipdoc = 'AOA' or tipdoc = 'AOB'";
$recordnav = new recordnav($gTables['tesbro'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
              "ID" => "id_tes",
              "Tipo" => "tipdoc",
              "Num." => "numdoc",
              "Data" => "datemi",
              "Fornitore" => "clfoco",
              "Importo" => "portos",
              "Stampa" => "",
              "Cancella" => ""
              );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
if (!isset($_GET['flag_order'])) {
        $orderby = "id_tes desc";
}
$result = gaz_dbi_dyn_query ('*', $gTables['tesbro'], $where, $orderby, $limit, $passo);
$ctrlprotoc = "";
$anagrafica = new Anagrafica();
while ($a_row = gaz_dbi_fetch_array($result)) {
    if ($a_row["tipdoc"] == 'AOB') {
        $tipodoc="Bonifico";
        $modulo="stampa_ordban.php?id_tes=".$a_row['id_tes'];
        $modifi="update_pagdeb.php?id_tes=".$a_row['id_tes'];
    }
    if ($a_row["tipdoc"] == 'AOA') {
        $tipodoc="Ordine di Addebito";
        $modulo="stampa_ordban.php?id_tes=".$a_row['id_tes'];
        $modifi="update_pagdeb.php?id_tes=".$a_row['id_tes'];
    }

    $cliente = $anagrafica->getPartner($a_row['clfoco']);

    print "<tr>";
    if (! empty ($modifi)) {
       print "<td class=\"FacetDataTD\"><a href=\"".$modifi."\">".$a_row["id_tes"]."</td>";
    } else {
       print "<td class=\"FacetDataTD\">".$a_row["id_tes"]." &nbsp;</td>";
    }
    print "<td class=\"FacetDataTD\">".$tipodoc." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["numdoc"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["datemi"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$cliente["ragso1"]."&nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"right\">".$a_row["portos"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"".$modulo."\"><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_pagdeb.php?id_tes=".$a_row['id_tes']."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
    print "</tr>";
}
?>
</table>
</body>
</html>