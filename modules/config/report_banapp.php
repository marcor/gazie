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
require("../../library/include/header.php");
$script_transl = HeadMain('','','admin_banapp');
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['report']; ?></div>
<?php
$recordnav = new recordnav($gTables['banapp'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<?php
$headers_banapp = array  (
              $script_transl['codice'] => "codice",
              $script_transl['descri'] => "descri",
              $script_transl['locali'] => "locali",
              $script_transl['codabi'] => "codabi",
              $script_transl['codcab'] => "codcab",
              $script_transl['delete'] => ""
              );
$linkHeaders = new linkHeaders($headers_banapp);
$linkHeaders -> output();
?>
   </tr>
<?php
$result = gaz_dbi_dyn_query ('*', $gTables['banapp'], $where, $orderby, $limit, $passo);
while ($a_row = gaz_dbi_fetch_array($result)) {
    print "<tr>";
    print "<td class=\"FacetDataTD\" ><a class=\"btn btn-xs btn-default\" href=\"admin_banapp.php?Update&codice=".$a_row["codice"]."\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$a_row["codice"]."</a> &nbsp</td>";
    print "<td class=\"FacetDataTD\">".$a_row["descri"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["locali"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["codabi"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["codcab"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_banapp.php?codice=".$a_row["codice"]."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
    print "</tr>";
}
?>
 </table>
</div><!-- chiude div container role main --></body>
</html>