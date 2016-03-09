<?php
/*
--------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend=checkAdmin(9);
require("../../library/include/header.php");
$script_transl = HeadMain('','','admin_utente');
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['report']; ?></div>
<?php
$recordnav = new recordnav($gTables['admin'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<?php
$headers_utenti = array  (
              $script_transl['Login'] => "Login",
              $script_transl['Cognome'] => "Cognome",
              $script_transl['Nome'] => "Nome",
              $script_transl['Abilit'] => "Abilit",
              $script_transl['Access'] => "Access",
              $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_utenti);
$linkHeaders -> output();
$result = gaz_dbi_dyn_query ('*', $gTables['admin'], $where, $orderby, $limit, $passo);
while ($a_row = gaz_dbi_fetch_array($result)) {
    echo "<tr class=\"FacetDataTD\">";
    echo "<td title=\"".$script_transl['update']."\"><a class=\"btn btn-xs btn-default\" href=\"admin_utente.php?Login=".$a_row["Login"]."&Update\">".$a_row["Login"]." </a> &nbsp</td>";
    echo "<td>".$a_row["Cognome"]." &nbsp;</td>";
    echo "<td>".$a_row["Nome"]." &nbsp;</td>";
    echo "<td align=\"center\">".$a_row["Abilit"]." &nbsp;</td>";
    echo "<td align=\"center\">".$a_row["Access"]." &nbsp;</td>";
    echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_utente.php?Login=".$a_row["Login"]."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
    echo "</tr>";
}
?>
</table>
</div><!-- chiude div container role main --></body>
</html>