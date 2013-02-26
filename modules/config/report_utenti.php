<?php
/*
--------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend=checkAdmin(9);
require("../../library/include/header.php");
$script_transl = HeadMain('','','admin_utente');
?>
<div align="center" class="FacetFormHeaderFont"><a href="admin_utente.php?Insert"><?php echo $script_transl['ins_this']; ?></a></div>
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
    print "<tr>";
    print "<td class=\"FacetDataTD\" align=\"center\" title=\"".$script_transl['update']."\"><a href=\"admin_utente.php?Login=".$a_row["Login"]."&Update\">".$a_row["Login"]." </a> &nbsp</td>";
    print "<td class=\"FacetDataTD\">".$a_row["Cognome"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\">".$a_row["Nome"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["Abilit"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\">".$a_row["Access"]." &nbsp;</td>";
    print "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_utente.php?Login=".$a_row["Login"]."\"><img src=\"../../library/images/x.gif\" title=\"".$script_transl['delete']."\" border=\"0\"></a></td>";
    print "</tr>";
}
?>
</table>
</body>
</html>