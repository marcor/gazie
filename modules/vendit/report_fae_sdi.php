<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}



if (isset($_GET['all'])) {
   $where ="";
   $nome_file ="";
   $form['ritorno'] = ""; 
} else {

  if (isset($_GET['nome_file'])) {
     $nome_file = $_GET['nome_file'];
     $where = " filename_ori LIKE '%".$nome_file."%'";
     
  } else {
     $nome_file = "";
  }

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

?>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<tr>
<td></td>
<td class="FacetFieldCaptionTD">
<input type="text" name="nome_file" value="<?php echo $nome_file ?>" maxlength="30" size="30" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" colspan="10" value="Cerca" tabindex="1" >
</td>
<td colspan="1">
<input type="submit" name="all" value="Mostra tutti" >
</td>
</tr>
</form>
<?php

$headers = array  ($script_transl['id']=>'id',
                   $script_transl['filename_ori']=>'',
                   $script_transl['ragso1']=>'',
                   $script_transl['exec_date']=>'',
                   $script_transl['received_date']=>'',
                   $script_transl['delivery_date']=>'',
                   $script_transl['filename_son']=>'',
                   $script_transl['id_SDI']=>'',
                   $script_transl['filename_ret']=>'',
                   $script_transl['mail_id']=>'',
                   $script_transl['status']=>'',
                   $script_transl['progr_ret']=>'',
                   $script_transl['flux_descri']=>''
            );
$linkHeaders = new linkHeaders($headers);
$linkHeaders -> output();
//$orderby = $gTables['fae_flux'].'.filename_ori, '. $gTables['fae_flux'].'.mail_id'   ; 
$orderby = $gTables['fae_flux'].'.filename_ori, '. $gTables['fae_flux'].'.progr_ret'   ;

$result = gaz_dbi_dyn_query ($gTables['fae_flux'].".*,".$gTables['clfoco'].".descri", $gTables['fae_flux'].
                             ' LEFT JOIN '.$gTables['tesmov'].' ON '.$gTables['fae_flux'].'.id_tes_ref = '.$gTables['tesmov'].'.id_tes'.
                             ' LEFT JOIN '.$gTables['clfoco'].' ON '.$gTables['tesmov'].'.clfoco = '.$gTables['clfoco'].'.codice',
                             $where, $orderby, $limit, $passo);


    
while ($r = gaz_dbi_fetch_array($result)) {
    
    if ($r['status'] == "RC") {
      $class="FacetDataTD";
      } else {
      $class="";
    } 
    echo "<tr>";
    echo "<td class=\"$class\" align=\"center\">".$r['id']."</td>";
    echo "<td class=\"$class\" align=\"left\">".$r['filename_ori']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['descri']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['exec_date']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['received_date']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['delivery_date']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['filename_son']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['id_SDI']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['filename_ret']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['mail_id']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['status']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['progr_ret']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['flux_descri']."</td>";
    echo "</tr>";
}
echo "</table>\n";
echo "</form>\n";

?>

</body>
</html>

