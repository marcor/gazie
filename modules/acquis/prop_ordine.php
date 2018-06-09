<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin(9);
require("../../library/include/header.php");
$script_transl = HeadMain();

if(!isset($_POST['ins']))
{

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}
    $form['id_anagra'] = '';
    $form['search']['id_anagra'] = '';

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    $form['id_anagra'] = filter_input(INPUT_POST, 'id_anagra');
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }

}
?>

<script type="text/javascript" >
function ricalcola(righe)
  {
    var totale = 0;
    for (I = 1; I< (righe*3); I+=3)
     {
       totale += parseFloat(document.docacq[I].value) * parseFloat(document.docacq[I+1].value) ;
     }
   document.docacq['totale'].value = totale;
  }
 
function azzera(righe)  
    {

     if (confirm("Sei sicuro di voler azzerare l'ordine?"))
     for (I = 1; I< (righe*3); I+=3)
     {
       document.docacq[I+1].value = 0; 
     }
    }
</script>

<br>
<form action="prop_ordine.php" method="post" name="proposta_ordine">
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<tr>      
     <td class="FacetDataTD"><?php echo $script_transl['includi'];?>:&nbsp<input type="checkbox" name="tutti" <?php  echo ((isset($_POST['tutti'])) and $_POST['tutti']=='on')?"checked":""; ?>></td>
     <td class="FacetFieldCaptionTD"><?php echo $script_transl['fornitore'];?></td>
     <td class="FacetDataTD">
	     <form method="POST" name="form" enctype="multipart/form-data" id="add-product">
              <?php
                   echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
                   $select_id_anagra = new selectPartner("id_anagra");
                   $select_id_anagra->selectDocPartner('id_anagra', $form['id_anagra'], $form['search']['id_anagra'], 'id_anagra', $script_transl['mesg'], $admin_aziend['masfor'], -1, 1, true);
                   ?>
	     </form>
	 <td class="FacetFieldCaptionTD"><?php echo $script_transl['giorni_app'];?>&nbsp;</td>
     <td class="FacetDataTD"><input type="text" name="giorni_app" value="<?php echo (isset($_POST['giorni_app']) ? $_POST['giorni_app']:'120');?>" size="5" maxlength="5" onblur="Ricerca(1,this);"></td>
     <td class="FacetFieldCaptionTD"><?php echo $script_transl['calcola_giorni'];?>&nbsp;</td>
     <td class="FacetDataTD"><input type="text" name="gio_ven" value="<?php  echo (isset($_POST['gio_ven']) ? $_POST['gio_ven']:'365');?>" size="5" maxlength="5" onblur="Ricerca(1,this);"> <?php echo $script_transl['giorni'];?></td>
     <td class="FacetFieldCaptionTD"><input type="submit" name="Cerca" value="Cerca"></td>
</tr>
</form>
<br>
<?php 
 
 if (($form['id_anagra'] != '') && isset($_POST['giorni_app']) && isset($_POST['gio_ven']) && isset($_POST['Cerca']))
 {
   ?>
    <br>
  <form action="prop_ordine.php" method="post" name="docacq">  
  <table align="center" width="90%">
  <tr class="FacetFormHeaderFont">
    <td align="center" class="FacetDataTD">Immagine</td>
    <td align="center" class="FacetDataTD">Codice</td>
    <td align="center" class="FacetDataTD">Codice fornitore</td>
    <td align="center" class="FacetDataTD">Descrizione</td>
    <td align="center" class="FacetDataTD">U.P.A(P.A.)</td>
    <td align="center" class="FacetDataTD">giacenza</td>
    <td align="center" class="FacetDataTD">Pz x Cf</td>
    <td align="center" class="FacetDataTD">da ordinare</td>
    <td align="center" class="FacetDataTD">Vendite mensili</td>
  </tr>
   <?php 
   require("../../modules/magazz/lib.function.php");
   $gForm = new magazzForm();
   
  $query = "Select image, count(image) as righe from ".$gTables['artico'].
           " where (((clfoco = ".$form['id_anagra'].") ".
           ((isset($_POST['tutti']) && ($_POST['tutti']=='on'))?"))":" and (ordinabile <> 'N'))) ").          
           " group by image order by image;";
            
   //echo $query."<br><br>";
   
   $result_img = gaz_dbi_query($query);
   $righe = $totale = 0;
   while ($a_row_img = gaz_dbi_fetch_array($result_img)) 
    {
     $query_riga = "Select codice,codice_fornitore,gaz_001artico.descri,uniacq,".
           "scorta,sum(quanti) as venduti,tipdoc,preacq,last_cost,pack_units ".
           "from ".$gTables['artico']." left join ".
           "(gaz_001rigdoc join gaz_001tesdoc on ((gaz_001rigdoc.id_tes = gaz_001tesdoc.id_tes) ".
           " and (datemi > DATE_SUB(CURDATE(),INTERVAL ".$_POST['gio_ven'].
           " DAY)) and (tipdoc in('VCO','DDT','FAI','FAD') or tipdoc is NULL)))".
           "on (codice = codart) ".
           "where (((".$gTables['artico'].".clfoco = ".$form['id_anagra'].") and (image = '".addslashes($a_row_img['image'])."') ".
           ((isset($_POST['tutti']) && ($_POST['tutti']=='on'))?"))":" and (ordinabile <> 'N'))) ").          
           "group by gaz_001artico.codice order by codice_fornitore;";

    // echo $query_riga."<br><hr width='50'><br>";
	 
   $result = gaz_dbi_query($query_riga);

   $primo = 0;
   while ($a_row = gaz_dbi_fetch_array($result)) 
    {
    $mv = $gForm->getStockValue(false,$a_row['codice']); //q_g
    $magval = array_pop($mv);
	
     echo "<tr>\n";
     $righe++;
     $rotazione = ($a_row['venduti'] / $_POST['gio_ven']);
     $app = ($rotazione*$_POST['giorni_app']) - ($magval['q_g']);
     
     if ($app < 0) $app = 0; //Ci sono scorte a sufficienza
     if (($magval['q_g'] + $app) < $a_row['scorta']) $app = $a_row['scorta'];

     if($primo == 0) { $primo++;
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\" rowspan='".$a_row_img['righe']."'>".
     "<br><img src=\"../root/view.php?table=artico&value=".$a_row['codice']."\" width=\"100\"></td>\n";
     }   
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\">".
          "<a href='../magazz/admin_artico.php?codice=".$a_row['codice']."&Update' target='_blank'>".
          $a_row['codice']."</a><input type='hidden' name='codice[".$righe."]' value='".$a_row['codice']."'></td>\n";
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\">".$a_row['codice_fornitore']."</td>\n";
     echo "<td align=\"left\" class=\"FacetFieldCaptionTD\">".$a_row['descri']."</td>\n";
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\"> ".
          "<input type='text' size='10'style='text-align: center;' name='prezzo[".$righe."]' value='".(($a_row['last_cost']==0)?$a_row['preacq']:$a_row['last_cost'])."'> (".$a_row['preacq'].")</td>\n";
     echo "<td align=\"center\" ".(($magval['q_g'] > 0)?"class=\"FacetFieldCaptionTD\"":"Style=\"background-color : #FFA500; color : #000000; font-size : 13px;\"").">".$a_row['uniacq']." ".(($magval['q_g']=='')?'0':$magval['q_g'])."</td>\n";
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\">".$a_row['pack_units']."</td>\n";
     echo "<td align=\"center\" ".(($app == 0)?"class=\"FacetFieldCaptionTD\"":"Style=\"background-color : #FFA500; color : #000000; font-size : 13px;\"")."\"  >".
     "<input type='text'size='6' style='text-align: center;' name='acquista[".$righe."]' value='".gaz_format_number($app)."' title='".gaz_format_number($app)."'></td>\n";
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\">".gaz_format_number($rotazione*30)."</td>\n";
     $prez_acquisto = ($a_row['last_cost']==0)?$a_row['preacq']:$a_row['last_cost'];
     $totale = $totale + ($app * $prez_acquisto );
	 echo "</tr>\n";
     }}
   
    echo "</table><br><table align='center' border='0'><tr><td><h3 align=\"center\" >Totale <input readonly type='text' name='totale' value='".gaz_format_number($totale)."'></td><tr>";
    echo "<tr align='center'><td><input type='button' name='resetta' value='Azzera ordine' onclick='azzera(".$righe.")'>";
    echo "<input type='button' name='Ricalcola' value='Ricalcola' onclick='ricalcola(".$righe.")'>";
    echo "<input type='submit' name='ins' value='INSERISCI'></td></tr><tr align='center'><td>(totale righe n.".$righe.")";
    echo "<input type='hidden' name='righe' value='".$righe."'></td></tr></table></form>";
 }
?>
</form>
<?php
require("../../library/include/footer.php");
}
else {

?>
<form action="admin_broacq.php?tipdoc=AOR" method="post" name="docacq">  
<input type="hidden" name="Insert" value="">
<input type="hidden" value="" name="id_tes">
<input type="hidden" value="1" name="seziva">
<input type="hidden" value="AOR" name="tipdoc">
<input type="hidden" value="" name="ritorno">
<input type="hidden" value="0" name="change_pag">
<input type="hidden" value="" name="protoc">
<input type="hidden" value="" name="numdoc">
<input type="hidden" value="" name="numfat">
<input type="hidden" value="" name="datfat">
<input type="hidden" value="" name="hidden_req" />
<input type="hidden" name="clfoco" value="">
<input type="hidden" name="search[clfoco]" value="">
<input type="hidden" name="seziva" value="1">
<input type="hidden" name="gioemi" value="<?php echo date('d');?>"> <!-- giorno -->
<input type="hidden" name="mesemi" value="<?php echo date('m');?>"> <!-- mese -->
<input type="hidden" name="annemi" value="<?php echo date('Y');?>"><!-- anno -->
<input type="hidden" name="listin" value="1">
<input type="hidden" value="" name="in_descri">
<input type="hidden" value="" name="in_pervat">
<input type="hidden" value="" name="in_unimis">
<input type="hidden" value="0" name="in_prelis">
<input type="hidden" value="0" name="in_id_mag">
<input type="hidden" value="" name="in_annota">
<input type="hidden" value="0" name="in_pesosp">
<input type="hidden" value="INSERT" name="in_status">
<input type="hidden" name="in_codart" value="">
<input type="hidden" name="cosear" value="">
<input type="hidden" value="0"  name="in_quanti">
<input type="hidden" name="in_tiprig" value="0">
<input type="hidden" name="in_codric" value="330000004">
<input type="hidden" value="0"  name="in_sconto">
<input type="hidden" name="in_codvat" value=""><br>
<h1>Generazione ordine in corso
<?php 
$i = 1;
for ($k=1;$k<=$_POST['righe'];$k++)
{
if ($_POST['acquista'][$k] > 0)
{
$result = gaz_dbi_query("Select codice_fornitore,gaz_001artico.descri,uniacq,aliquo from gaz_001artico join gaz_001aliiva on (gaz_001aliiva.codice = gaz_001artico.aliiva ) where (gaz_001artico.codice = '".$_POST['codice'][$k]."') limit 1;");
$a_row = gaz_dbi_fetch_array($result);

echo'.<input type="hidden" value="'.$_POST['codice'][$k].'" name="righi['.$i.'][codart]">';
echo'<input type="hidden" value="INSERT" name="righi['.$i.'][status]">';
echo'<input type="hidden" value="0" name="righi['.$i.'][tiprig]">';
echo'<input type="hidden" value="1" name="righi['.$i.'][codvat]">';
echo'<input type="hidden" value="'.$a_row['aliquo'].'" name="righi['.$i.'][pervat]">';
echo'<input type="hidden" value="330000004" name="righi['.$i.'][codric]">';
echo'<input type="hidden" name="righi['.$i.'][codfor]" value="'.$a_row['codice_fornitore'].'" />';
echo'<input type="hidden" name="righi['.$i.'][descri]" value="'.$a_row['descri'].'" />';
echo'<input type="hidden" name="righi['.$i.'][unimis]" value="'.$a_row['uniacq'].'" />';
echo'<input type="hidden" name="righi['.$i.'][quanti]" value="'.$_POST['acquista'][$k].'" />';
echo'<input type="hidden" name="righi['.$i.'][prelis]" value="'.$_POST['prezzo'][$k].'"  />';
$i++;
}
}
?></h1>
<script type="text/javascript" >
 document.forms["docacq"].submit(); 
</script>
</form>
</body>
</html>
<?php  } ?>