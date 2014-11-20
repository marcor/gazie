<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/header.php");
$script_transl=HeadMain('','','admin_caucon');
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['report']; ?></div>
<?php
$recordnav = new recordnav($gTables['caucon'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<?php
$headers_caucon = array  (
            $script_transl['codice']=> "codice",
            $script_transl['descri'] => "descri",
            $script_transl['regiva']=> "regiva",
            $script_transl['operat']=> "operat",
            $script_transl['pay_schedule']=> "pay_schedule",
            $script_transl['delete']=> ""
            );
$linkHeaders = new linkHeaders($headers_caucon);
$linkHeaders -> output();
$result = gaz_dbi_dyn_query ('*', $gTables['caucon'], $where, $orderby);
while ($row = gaz_dbi_fetch_array($result)) {
    echo "<tr>";
    echo "<td class=\"FacetDataTD\"><a href=\"admin_caucon.php?codice=".$row["codice"]."&Update\">".$row["codice"]."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTD\">".$row["descri"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$script_transl['regiva_value'][$row["regiva"]]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$script_transl['operat_value'][$row["operat"]]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$script_transl['pay_schedule_value'][$row["pay_schedule"]]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_caucon.php?codice=".$row["codice"]."\"><img src=\"../../library/images/x.gif\" alt=\"".$script_transl['delete']."\" border=\"0\"></a></td>";
    echo "</tr>";
}
?>
</table>
</body>
</html>



