<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

$nome_file="";
$senza_esito=0;


if (isset($_GET['all'])) {
   $where ="";
   $status="";
   $form['ritorno'] = ""; 
} elseif (isset($_GET['id_record'])) {
   //da migliorare l'interazione
   $numero_record = $_GET['id_record'];
   gaz_dbi_put_row($gTables['fae_flux'], "id", $numero_record, "flux_status", "@" );
   $status="";
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
         
         if ($status == "NO") {
           // $status="@";           
           $where = " flux_status LIKE '%@%'";
           $senza_esito=1;
         } else {
                                 
           $where = " flux_status LIKE '%".$status."%'";
         }  
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
echo "<p align=\"center\"><a href=\"./check_fae_sdi.php\">Verifica email (...)</a></p>";

$recordnav = new recordnav($gTables['fae_flux'], $where, $limit, $passo);
$recordnav -> output();
?>

<p>&nbsp;</p>

<table id ="tableId" name="tableId" class="Tlarge">
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<tr style="margin-bottom: 20px !important;">
<td></td>
<td class="FacetFieldCaptionTD">
<input type="text" name="nome_file" id="nome_file" value="<?php echo $nome_file ?>" maxlength="30" size="30" tabindex="1" class="FacetInput">
</td>
<td class="FacetFieldCaptionTD" colspan="2">


<select name="status">
  <option value=""></option>
  <option value="#" <?php if($status =="@") echo "selected";?> ># - Non inviata</option>
  <option value="@" <?php if($status =="@") echo "selected";?> >@ - Inviata</option>
  <option value="NS" <?php if($status =="NS") echo "selected";?> >NS - Notifica scarto</option>
  <option value="MC" <?php if($status =="MC") echo "selected";?> >MC - Mancata consegna</option>
  <option value="RC" <?php if($status =="RC") echo "selected";?> >RC - Ricevuta consegna</option>
  <option value="DT" <?php if($status =="DT") echo "selected";?> >DT - Decorrenza termini</option>
  <option value="NE" <?php if($status =="NE") echo "selected";?> >NE - Notifica esito</option>  
  <option value="NO" <?php if($status =="NO") echo "selected";?> >NO - Senza esiti oltre RC</option>
</select> 
</td>
<td>
<input type="submit" name="search" colspan="11" value="Cerca" tabindex="1" >
</td>
<td colspan="1">
<input type="submit" name="all" value="Mostra tutti" >
</td>
</tr>
</form>


<?php



$headers = array  ($script_transl['id']=>'id',
                   $script_transl['filename_ori']=>'',
                   $script_transl['numfat']=>'',
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



if ( $status <> "" and $status <> "#" and $status <> "@" and $status <> "NO" ) {
    $linkHeaders -> output();
}


$orderby = $gTables['fae_flux'].'.filename_ori, '. $gTables['fae_flux'].'.progr_ret'   ;


$result = gaz_dbi_dyn_query ($gTables['fae_flux'].".*,".$gTables['tesdoc'].".numfat,".$gTables['clfoco'].".codice,".$gTables['clfoco'].".descri", $gTables['fae_flux'].
                             ' LEFT JOIN '.$gTables['tesdoc'].' ON '.$gTables['fae_flux'].'.id_tes_ref = '.$gTables['tesdoc'].'.id_tes'.
                             ' LEFT JOIN '.$gTables['clfoco'].' ON '.$gTables['tesdoc'].'.clfoco = '.$gTables['clfoco'].'.codice',
                             $where, $orderby, $limit, $passo);


    
while ($r = gaz_dbi_fetch_array($result)) {
    
    
    if ($senza_esito == 1) {
    
       
       $where1 = " filename_ori = '" . $r['filename_ori'] . ".p7m' and flux_status <> 'RC' ";    
       $risultati = gaz_dbi_dyn_query ("*", $gTables['fae_flux'], $where1, $orderby, $limit, $passo);
       $rr = gaz_dbi_fetch_array($risultati);
        
       if ($rr == false) {
          //   echo "<tr><td>-------- FALSO " . $where1 . "</td></tr>";
        } else {
          //   echo "<tr><td>-------- VERO "  . $where1 . " " . $rr['filename_ori'] . "</td></tr>";
          continue;
        }
      
    }
    
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
    } elseif ($r['flux_status'] == "@") {
      $class="FacetDataTD";
      $class1="";
    } elseif ($r['flux_status'] == "#") {
      $class="FacetDataTD";
      $class1="";  
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
    echo "<td class=\"$class\" align=\"center\">".$r['numfat']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['codice']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['descri']."</td>";
    echo "<td style=\"white-space:nowrap;\" class=\"$class\" align=\"center\">".gaz_format_date($r['exec_date'])."</td>";
    echo "<td style=\"white-space:nowrap;\" class=\"$class\" align=\"center\">".gaz_format_date($r['received_date'])."</td>";
    echo "<td style=\"white-space:nowrap;\" class=\"$class\" align=\"center\">".gaz_format_date($r['delivery_date'])."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['filename_son']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['id_SDI']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['filename_ret']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['mail_id']."</td>";
    
    //aggiungere una icona invece del cancelletto 
    if ($r['flux_status'] == "#") {
        $modulo_fae_report="report_fae_sdi.php?id_record=".$r['id'];
        echo "<td class=\"$class  $class2\" align=\"center\" title=\"".$script_transl['flux_status_value'][$r['flux_status']]."\">". "<a href=\"".$modulo_fae_report."\">#</a>" . "</td>";
    } else {
        echo "<td class=\"$class  $class2\" align=\"center\" title=\"".$script_transl['flux_status_value'][$r['flux_status']]."\">".$r['flux_status']."</td>";
    }
    echo "<td class=\"$class\" align=\"center\">".$r['progr_ret']."</td>";
    echo "<td class=\"$class\" align=\"center\">".$r['flux_descri']."</td>";
    echo "</tr>";

   }    


echo "</table>\n";
echo "</form>\n";

?>
</body>
</html>

