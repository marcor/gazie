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

$anno = date("Y");
if (!isset($_GET['auxil']))
    $_GET['auxil'] = 1;
$titolo = "D.d.T. a Fornitori";
require("../../library/include/header.php");
$script_transl=HeadMain();
?>

<form method="GET" action="<?php print $_SERVER['PHP_SELF']; ?>"  name="auxil">
    <p>
    <div align="center">
    <font class="FacetFormHeaderFont"> <?php print $titolo." della sezione "; ?>
    <select name="auxil" class="FacetSelect" onchange="this.form.submit()">
    <?php
    for ($sez = 1; $sez <= 3; $sez++) {
        $selected="";
        if($_GET["auxil"] == $sez)
            $selected = " selected ";
        echo "<option value=\"".$sez."\"".$selected.">".$sez."</option>";
    }
    ?>
    </select></p>
    </font>
    </div>
</form>
<?php
$sezione= $_GET["auxil"];
if (!isset($_GET['flag_order'])) {
    $orderby = "datemi desc, numdoc desc";
    }
$where = "(tipdoc = 'DDR' OR tipdoc = 'DDL') and seziva = $sezione";
$recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
            "ID" => "id_tes",
            "Numero" => "numdoc",
            "Data" => "datemi",
            "Fornitore (cod.)" => "clfoco",
            "Status" => "",
            "Stampa" => "",
            "Cancella" => ""
            );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
$rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where,"datemi desc, numdoc desc",0,1);
$ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
if ($ultimo_documento) {
    $ultimoddt = $ultimo_documento['numdoc'];
} else {
    $ultimoddt = 1;
}
$result = gaz_dbi_dyn_query ('*', $gTables['tesdoc'], $where, $orderby, $limit, $passo);
print "<tr><td class=\"FacetDataTDred\" colspan=\"6\">Attenzione, la numerazione comprende anche i D.d.T. di Vendita non riportati in questa lista!</td></tr>";

$anagrafica = new Anagrafica();
while ($a_row = gaz_dbi_fetch_array($result)) {
    $cliente = $anagrafica->getPartner($a_row['clfoco']);
    print "<tr>";
    print "<td class=\"FacetDataTD\"><a href=\"admin_docacq.php?id_tes=".$a_row["id_tes"]."&Update\">".$a_row["id_tes"]."</a> &nbsp</td>";
    print "<td class=\"FacetDataTD\">".$a_row["numdoc"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$a_row["datemi"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$cliente["ragso1"]."&nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$a_row["status"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">
			<a class=\"btn btn-xs btn-default\" href=\"stampa_docacq.php?id_tes=".$a_row["id_tes"]."&template=DDT\" title=\"Stampa\">
					<i class=\"glyphicon glyphicon-remove\"></i>
			</a>
		  </td>";
    if ($ultimoddt == $a_row["numdoc"] and $a_row['numfat'] == 0) {
        print "<td class=\"FacetDataTD\">
				<a class=\"btn btn-xs btn-default\" href=\"delete_docacq.php?id_tes=".$a_row["id_tes"]."\" title=\"Cancella\">
					<i class=\"glyphicon glyphicon-remove\"></i>
				</a>
			  </td>";
    } else {
        print "<td class=\"FacetDataTD\"></td>";
    }
    print "</tr>";
}
?>
</table>
</body>
</html>