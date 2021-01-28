<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend = checkAdmin();
require("../../library/include/header.php");
$script_transl = HeadMain();
$where = " SUBSTR(codcon,1,3) = '".$admin_aziend['masfor']."'";
echo '<div align="center" class="FacetFormHeaderFont">' . $script_transl['title'] . '</div>';
$recordnav = new recordnav($gTables['paymov']." LEFT JOIN ".$gTables['rigmoc']." ON (".$gTables['paymov'].".id_rigmoc_pay = ".$gTables['rigmoc'].".id_rig OR ".$gTables['paymov'].".id_rigmoc_doc = ".$gTables['rigmoc'].".id_rig)", $where, $limit, $passo);
$recordnav->output();
echo '<div class="table-responsive"><table class="Tlarge table table-striped table-bordered table-condensed">';
$linkHeaders = new linkHeaders($script_transl['header']);
$linkHeaders->setAlign(array('left', 'center', 'center', 'center', 'right', 'center'));
$linkHeaders->output();

$result = gaz_dbi_dyn_query('*', $gTables['paymov']." LEFT JOIN ".$gTables['rigmoc']." ON (".$gTables['paymov'].".id_rigmoc_pay = ".$gTables['rigmoc'].".id_rig OR ".$gTables['paymov'].".id_rigmoc_doc = ".$gTables['rigmoc'].".id_rig)" , $where, $orderby, $limit, $passo);
while ($r = gaz_dbi_fetch_array($result)) {
    // faccio una subquery che sembra più veloce di LEFT JOIN per ricavare l'id_tes
    $tesmov = gaz_dbi_get_row($gTables['tesmov'],'id_tes',$r['id_tes']);
    echo "<tr class=\"FacetDataTD\">";
    echo "<td>" . $r["id"] . " &nbsp;</td>";
    echo "<td>" . $r["id_tesdoc_ref"] . "</td>";
    if ($r["id_rigmoc_doc"] > 0) {
        echo "<td><a class=\"btn btn-xs btn-default btn-warning\"  style=\"font-size:10px;\" href=\"../contab/admin_movcon.php?id_tes=" . $r["id_tes"] . "&Update\">" . $r["id_tes"] . "</a>&nbsp; ".$tesmov["descri"]." &nbsp;</td>";
    } else {
        echo "<td></td>";
    }
    if ($r["id_rigmoc_pay"] > 0) {
        echo "<td><a class=\"btn btn-xs btn-default btn-success\"  style=\"font-size:10px;\" href=\"../contab/admin_movcon.php?id_tes=" . $r["id_tes"] . "&Update\">" . $r["id_tes"] . "</a>&nbsp; ".$tesmov["descri"]." &nbsp;</td>";
    } else {
        echo "<td></td>";
    }
    echo "<td align=\"right\">" . $r["amount"] . " &nbsp;</td>";
    echo "<td align=\"center\">" . $r["expiry"] . " &nbsp;</td>";
    echo "</tr>";
}
?>
</table></div>
<?php
if ( isset($_GET['xml']) && isset($_GET['id_rig']) && $_GET['id_rig'] > 0 ){
 echo '<script>
	$( window ).load(function() {
		window.location.href = "CBIPaymentRequest.php?id_rig='.intval($_GET['id_rig']).'";
	});
	</script>';
}
require("../../library/include/footer.php");
?>