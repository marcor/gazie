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
require("../../library/include/header.php");
$script_transl = HeadMain('','','admin_pagame');
// se non è impostata una modalità di ordinamento della tabella, verrà ordinata per codice desc
if ( !isset($_GET['field']) ) $orderby = "codice DESC";
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl[0]; ?></div>
<?php
$recordnav = new recordnav($gTables['pagame'], $where, $limit, $passo);
$recordnav -> output();
?>
<div class="table-responsive"><table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
<?php
$headers_pagame = array  (
              $script_transl[1] => "codice",
              $script_transl[2] => "descri",
              $script_transl[6] => "giodec",
              $script_transl[10] => "numrat",
              $script_transl[11] => "tiprat",
              $script_transl['fae_mode'] => "fae_mode",
              $script_transl['delete'] => ""
              );
$linkHeaders = new linkHeaders($headers_pagame);
$linkHeaders -> output();
$result = gaz_dbi_dyn_query ('*', $gTables['pagame'], $where, $orderby, $limit, $passo);
while ($a_row = gaz_dbi_fetch_array($result)) {
    echo "<tr class=\"FacetDataTD\">\n";
    echo "<td><a class=\"btn btn-xs btn-default\" href=\"admin_pagame.php?codice=".$a_row["codice"]."&Update\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$a_row["codice"]."</a></td>\n";
    echo "<td>".$a_row["descri"]." &nbsp;</td>\n";
    echo "<td align=\"center\">".$a_row["giodec"]." &nbsp;</td>\n";
    echo "<td align=\"center\">".$a_row["numrat"]." &nbsp;</td>\n";
    echo "<td align=\"center\">".$a_row["tiprat"]." &nbsp;</td>\n";
    echo "<td align=\"center\">".$a_row["fae_mode"]." &nbsp;</td>\n";
    echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_pagame.php?codice=".$a_row["codice"]."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>\n";
    echo "</tr>\n";
}
?>
</table></div>
<?php
require("../../library/include/footer.php");
?>