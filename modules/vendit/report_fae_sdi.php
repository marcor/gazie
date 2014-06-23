<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}


require("../../library/include/header.php");
$script_transl=HeadMain(0,array('calendarpopup/CalendarPopup',
                                  'jquery/jquery-1.7.1.min',
                                  'jquery/ui/jquery.ui.core',
                                  'jquery/ui/jquery.ui.widget',
                                  'jquery/ui/jquery.ui.mouse',
                                  'jquery/ui/jquery.ui.button',
                                  'jquery/ui/jquery.ui.autocomplete',
                                  'jquery/ui/jquery.ui.dialog',
                                  'jquery/ui/jquery.ui.position',
                                  'jquery/ui/jquery.ui.draggable',
                                  'jquery/ui/jquery.ui.resizable',
                                  'jquery/ui/jquery.effects.core',
                                  'jquery/ui/jquery.effects.scale',
                                  'jquery/modal_form'));
$gForm = new GAzieForm();
echo '<form method="GET">';
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\" />\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title'];
echo "</div>\n";

$recordnav = new recordnav($gTables['fae_flux'], $where, $limit, $passo);
$recordnav -> output();
echo "<table class=\"Tlarge\">\n";
$headers = array  ($script_transl['id']=>'id',
                   $script_transl['filename_ori']=>'filename_ori',
                   $script_transl['ragso1']=>'',
                   $script_transl['exec_date']=>'exec_date',
                   $script_transl['received_date']=>'received_date',
                   $script_transl['delivery_date']=>'delivery_date',
                   $script_transl['filename_son']=>'filename_son',
                   $script_transl['id_SDI']=>'id_SDI',
                   $script_transl['filename_ret']=>'filename_ret',
                   $script_transl['mail_id']=>'mail_id',
                   $script_transl['status']=>'status',
                   $script_transl['flux_descri']=>'flux_descri'
            );
$linkHeaders = new linkHeaders($headers);
$linkHeaders -> output();
$orderby = $gTables['fae_flux'].'.filename_ori, '. $gTables['fae_flux'].'.mail_id'   ; 
$result = gaz_dbi_dyn_query ($gTables['fae_flux'].".*,".$gTables['clfoco'].".descri", $gTables['fae_flux'].
                             ' LEFT JOIN '.$gTables['tesmov'].' ON '.$gTables['fae_flux'].'.id_tes_ref = '.$gTables['tesmov'].'.id_tes'.
                             ' LEFT JOIN '.$gTables['clfoco'].' ON '.$gTables['tesmov'].'.clfoco = '.$gTables['clfoco'].'.codice',
                             $where, $orderby, $limit, $passo);
while ($r = gaz_dbi_fetch_array($result)) {
    echo "<tr>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['id']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['filename_ori']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['descri']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['exec_date']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['received_date']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['delivery_date']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['filename_son']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['id_SDI']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['filename_ret']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['mail_id']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['status']."</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$r['flux_descri']."</td>";
    echo "</tr>";
}
echo "</table>\n";
echo "</form>\n";

?>
</body>
</html>

