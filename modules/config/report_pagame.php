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
require("../../library/include/header.php");
$script_transl = HeadMain('','','admin_pagame');
?>
<div align="center" class="FacetFormHeaderFont"><a href="admin_pagame.php?Insert">
<?php echo $script_transl['ins_this']; ?></a></div>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl[0]; ?></div>
<?php
$recordnav = new recordnav($gTables['pagame'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<?php
$headers_pagame = array  (
              $script_transl[1] => "codice",
              $script_transl[2] => "descri",
              $script_transl[6] => "giodec",
              $script_transl[10] => "numrat",
              $script_transl[11] => "tiprat",
              $script_transl['delete'] => ""
              );
$linkHeaders = new linkHeaders($headers_pagame);
$linkHeaders -> output();
$result = gaz_dbi_dyn_query ('*', $gTables['pagame'], $where, $orderby, $limit, $passo);
while ($a_row = gaz_dbi_fetch_array($result)) {
    print "<tr>\n";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"admin_pagame.php?codice=".$a_row["codice"]."&Update\">".$a_row["codice"]."</a> &nbsp</td>\n";
    print "<td class=\"FacetDataTD\">".$a_row["descri"]." &nbsp;</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["giodec"]." &nbsp;</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["numrat"]." &nbsp;</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["tiprat"]." &nbsp;</td>\n";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_pagame.php?codice=".$a_row["codice"]."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>\n";
    print "</tr>\n";
}
?>
</table>
</body>
</html>