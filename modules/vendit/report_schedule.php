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
$script_transl=HeadMain();
echo '<div align="center" class="FacetFormHeaderFont">'.$script_transl['title'].'</div>';
$recordnav = new recordnav($gTables['paymov'], $where, $limit, $passo);
$recordnav -> output();
echo '<div class="box-primary table-responsive">';
echo '<table class="Tlarge table table-striped table-bordered table-condensed">';
$linkHeaders = new linkHeaders($script_transl['header']);
$linkHeaders->setAlign(array('left','center','center','center','right','center'));
$linkHeaders->output();

$result = gaz_dbi_dyn_query ('*', $gTables['paymov'], 1, 'id DESC');
while ($a_row = gaz_dbi_fetch_array($result))
    {
    // faccio una subquery che sembra essere più veloce di LEFT JOIN per ricavare l'id_tes
    $rigmoc = gaz_dbi_get_row($gTables['rigmoc'],'id_rig = '.$a_row["id_rigmoc_pay"].' OR id_rig',$a_row["id_rigmoc_doc"]);
    $tesmov = gaz_dbi_get_row($gTables['tesmov'],'id_tes',$rigmoc['id_tes']);
	if (substr($rigmoc['codcon'],0,3)==$admin_aziend['mascli']){ // stampo solo le partite relative ai clienti
    echo "<tr class=\"FacetDataTD\">";
    echo "<td>".$a_row["id"]." &nbsp;</td>";
    echo "<td align=\"center\">".$a_row["id_tesdoc_ref"]."</td>";
    if ($a_row["id_rigmoc_doc"]>0){
        echo "<td><a class=\"btn btn-xs btn-default btn-warning\"  style=\"font-size:10px;\" href=\"../contab/admin_movcon.php?id_tes=".$rigmoc["id_tes"]."&Update\">".$rigmoc["id_tes"]." </a>&nbsp; ".$tesmov["descri"]." </td>";
    } else {
        echo "<td></td>";
    }
    if ($a_row["id_rigmoc_pay"]>0){
        echo "<td><a class=\"btn btn-xs btn-default btn-success\"  style=\"font-size:10px;\" href=\"../contab/admin_movcon.php?id_tes=".$rigmoc["id_tes"]."&Update\">".$rigmoc["id_tes"]." </a>&nbsp; ".$tesmov["descri"]." </td>";
    } else {
        echo "<td></td>";
    }
    echo "<td align=\"right\">".$a_row["amount"]." &nbsp;</td>";
    echo "<td align=\"center\">".gaz_format_date($a_row["expiry"])." &nbsp;</td>";
    echo "</tr>";
	}
    }
?>
</table>
</div>
<?php
require("../../library/include/footer.php");
?>