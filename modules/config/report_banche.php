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
$script_transl = HeadMain();
$where = "codice BETWEEN ".$admin_aziend['masban']."000001 AND ".$admin_aziend['masban']."999999";
$anagrafica = new Anagrafica();
$result=$anagrafica->queryPartners('*', $where, $orderby, $limit, $passo);
echo '<div align="center" class="FacetFormHeaderFont"><a href="admin_bank_account.php?Insert">'.$script_transl['ins_this'].'</a></div>';
echo '<div align="center" class="FacetFormHeaderFont">'.$script_transl['title'].'</div>';
$recordnav = new recordnav($gTables['clfoco'], $where, $limit, $passo);
$recordnav -> output();
echo '<table class="Tlarge">';
$headers = array  (
            $script_transl['codice']=>'codice',
            $script_transl['ragso1']=>'ragso1',
            $script_transl['iban']=>'iban',
            $script_transl['citspe']=>'citspe',
            $script_transl['prospe']=>'prospe',
            $script_transl['telefo']=>'telefo',
            $script_transl['view']=>'',
            $script_transl['delete']=>''
            );
$linkHeaders = new linkHeaders($headers);
$linkHeaders -> output();
foreach($result as $r) {
    echo "<tr>";
    echo "<td class=\"FacetDataTD\"><a href=\"admin_bank_account.php?Update&codice=".substr($r["codice"],3)."\" title=\"Modifica\">".substr($r["codice"],3)."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTD\">".$r["ragso1"]." &nbsp;</td>";
    if (!empty($r['iban'])) {
       echo "<td class=\"FacetDataTD\">".$r["iban"]." &nbsp;</td>";
       echo "<td class=\"FacetDataTD\">".$r["citspe"]." &nbsp;</td>";
       echo "<td class=\"FacetDataTD\">".$r["prospe"]." &nbsp;</td>";
       echo "<td class=\"FacetDataTD\">".$r["telefo"]." &nbsp;</td>";
    } else {
       echo "<td class=\"FacetDataTD\" colspan=\"4\">".$script_transl['msg'][0]."</td>\n";
    }
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"../contab/select_partit.php?id=".$r["codice"]."\"><img src=\"../../library/images/vis.gif\" title=\"".$script_transl['msg'][1]."\" border=\"0\"><img src=\"../../library/images/stampa.gif\" alt=\"".$script_transl['msg'][1]."\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"../contab/delete_piacon.php?codice=".$r["codice"]."\"><img src=\"../../library/images/x.gif\" title=\"".$script_transl['delete']."\" border=\"0\"></a></td>";
    echo "</tr>";
}
?>
</table>
</body>
</html>



