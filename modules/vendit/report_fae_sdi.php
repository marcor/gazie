<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

$nome_file="";

  

if (isset($_GET['all'])) {
   $where ="";
   $status="";
   $form['ritorno'] = ""; 
} else {

  if (isset($_GET['nome_file'])) {
     $nome_file = $_GET['nome_file'];
     $status="";
     $where = " filename_ori LIKE '%".$nome_file."%'";
     
  }
  
  if ($nome_file=="") {
     $status="";
     if (isset($_GET['id_tes'])) {
         $id_tes = $_GET['id_tes'];
         $where = " id_tes_ref = ".$id_tes."";
     }

     if (isset($_GET['status'])) {
         $passo=1000000;
         $status = $_GET['status'];         
         $where = " flux_status LIKE '%".$status."%'";
     }     
     
     
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
                                  'jquery/modal_form',
                                  'jquery/varie'));
$gForm = new GAzieForm();
echo '<form method="GET">';
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\" />\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title'];
echo "</div>\n";

$recordnav = new recordnav($gTables['fae_flux'], $where, $limit, $passo);
$recordnav -> output();


?>


<p align="center"><a href="./check_fae_sdi.php">Verifica email (...)</a></p>

<table id ="tableId" name="tableId" class="Tlarge">
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<tr style="margin-bottom: 20px !important;">
<td></td>
<td class="FacetFieldCaptionTD">
<input type="text" name="nome_file" id="nome_file" value="<?php echo $nome_file ?>" maxlength="30" size="30" tabindex="1" class="FacetInput">
</td>
<td class="FacetFieldCaptionTD">
<input type="text" name="status" id="status" value="<?php echo $status ?>" maxlength="3" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" colspan="12" value="Cerca" tabindex="1" >
</td>
<td colspan="1">
<input type="submit" name="all" value="Mostra tutti" >
</td>
</tr>
</form>


<?php

$headers = array  ($script_transl['id']=>'id',
                   $script_transl['filename_ori']=>'',
                   $script_transl['protoc']=>'',
                   $script_transl['codice']=>'',
                   $script_transl['ragso1']=>'',
                   $script_transl['exec_date']=>'',
                   $script_transl['received_date']=>'',
                   $script_transl['delivery_date']=>'',
                   $script_transl['filename_son']=>'',
                   $script_transl['id_SDI']=>'',
                   $script_transl['filename_ret']=>'',
                   $script_transl['mail_id']=>'',
                   $script_transl['flux_status']=>'',
                   $script_transl['progr_ret']=>'',
                   $script_transl['flux_descri']=>''
            );
$linkHeaders = new linkHeaders($headers);

if ($status <> "") {
    $linkHeaders -> output();
}


$orderby = $gTables['fae_flux'].'.filename_ori, '. $gTables['fae_flux'].'.progr_ret'   ;


$result = gaz_dbi_dyn_query ($gTables['fae_flux'].".*,".$gTables['tesdoc'].".protoc,".$gTables['clfoco'].".codice,".$gTables['clfoco'].".descri", $gTables['fae_flux'].
                             ' LEFT JOIN '.$gTables['tesdoc'].' ON '.$gTables['fae_flux'].'.id_tes_ref = '.$gTables['tesdoc'].'.id_tes'.
                             ' LEFT JOIN '.$gTables['clfoco'].' ON '.$gTables['tesdoc'].'.clfoco = '.$gTables['clfoco'].'.codice',
                             $where, $orderby, $limit, $passo);


    
while ($r = gaz_dbi_fetch_array($result)) {
    
    $class="";
    $class1="";
    $class2="";
    if ($r['flux_status'] == "RC") {
      $class="FacetDataTD";
     } elseif ($r['flux_status'] == "NS") {
      $class="FacetDataTD";  
      $class2="FacetDataTDevidenziaKO";
    } elseif ($r['flux_status'] == "DT") {
      $class="FacetDataTDred";
    } elseif ($r['flux_status'] == "MC") {
      $class="FacetDataTD";
      $class2="FacetDataTDred";
    } 
    
    if ($r['progr_ret'] == "000") {
      $class="FacetDataTD";
      $class1="";
      $linkHeaders -> output();
     }
     
    //Fattura accettata
    if ($r['flux_descri'] == "EC01") {
      $class="FacetDataTD";
      $class2="FacetDataTDevidenziaOK";
     } 
    
    //Fattura rifiutata
    if ($r['flux_descri'] == "EC02") {
      $class="FacetDataTD";
      $class2="FacetDataTDevidenziaKO";
    }
 
    echo "<tr class=\"$class1 $class2\" >";
    echo "<td class=\"$class\" align=\"center\">".$r['id']."</td>";
    echo "<td class=\"$class paper\" align=\"left\">".$r['filename_ori']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['protoc']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['codice']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['descri']."</td>";
    echo "<td class=\"$class\" align=\"center\">".gaz_format_date($r['exec_date'])."</td>";
    echo "<td class=\"$class\" align=\"center\">".gaz_format_date($r['received_date'])."</td>";
    echo "<td class=\"$class\" align=\"center\">".gaz_format_date($r['delivery_date'])."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['filename_son']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['id_SDI']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['filename_ret']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['mail_id']."</td>";
    echo "<td class=\"$class  $class2 paper1\" align=\"center\">".$r['flux_status']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['progr_ret']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['flux_descri']."</td>";
    echo "</tr>";
}
echo "</table>\n";
echo "</form>\n";

?>

</body>
</html>

