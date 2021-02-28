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
     <td class="FacetDataTD"><input type="text" name="giorni_app" value="<?php echo (isset($_POST['giorni_app']) ? $_POST['giorni_app']:'120');?>" maxlength="5" onblur="Ricerca(1,this);"></td>
     <td class="FacetFieldCaptionTD"><?php echo $script_transl['calcola_giorni'];?>&nbsp;</td>
     <td class="FacetDataTD"><input type="text" name="gio_ven" value="<?php  echo (isset($_POST['gio_ven']) ? $_POST['gio_ven']:'365');?>" maxlength="5" onblur="Ricerca(1,this);"> <?php echo $script_transl['giorni'];?></td>
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
     $query_riga = "Select codice,".$gTables['artico'].".codice_fornitore,".$gTables['artico'].".descri,uniacq,".
           "scorta,sum(quanti) as venduti,tipdoc,preacq,last_cost,pack_units ".
           "from ".$gTables['artico']." left join ".
           "(".$gTables['rigdoc']." join ".$gTables['tesdoc']." on ((".$gTables['rigdoc'].".id_tes = ".$gTables['tesdoc'].".id_tes) ".
           " and (datemi > DATE_SUB(CURDATE(),INTERVAL ".$_POST['gio_ven'].
           " DAY)) and (tipdoc in('VCO','DDT','FAI','FAD','FAA','FAF','FAP') or tipdoc is NULL)))".
           "on (codice = codart) ".
           "where (((".$gTables['artico'].".clfoco = ".$form['id_anagra'].") and (image = '".addslashes($a_row_img['image'])."') ".
           ((isset($_POST['tutti']) && ($_POST['tutti']=='on'))?"))":" and (ordinabile <> 'N'))) ").          
           "group by ".$gTables['artico'].".codice order by codice_fornitore;";

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
          "<input type='text' style='text-align: center;' name='prezzo[".$righe."]' value='".(($a_row['last_cost']==0)?$a_row['preacq']:$a_row['last_cost'])."'> (".$a_row['preacq'].")</td>\n";
     echo "<td align=\"center\" ".(($magval['q_g'] > 0)?"class=\"FacetFieldCaptionTD\"":"Style=\"background-color : #FFA500; color : #000000; font-size : 13px;\"").">".$a_row['uniacq']." ".(($magval['q_g']=='')?'0':$magval['q_g'])."</td>\n";
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\">".$a_row['pack_units']."</td>\n";
     echo "<td align=\"center\" ".(($app == 0)?"class=\"FacetFieldCaptionTD\"":"Style=\"background-color : #FFA500; color : #000000; font-size : 13px;\"")."\"  >".
     "<input type='text' style='text-align: center;' name='acquista[".$righe."]' value='".gaz_format_number($app)."' title='".gaz_format_number($app)."'></td>\n";
     echo "<td align=\"center\" class=\"FacetFieldCaptionTD\">".gaz_format_number($rotazione*30)."</td>\n";
     $prez_acquisto = ($a_row['last_cost']==0)?$a_row['preacq']:$a_row['last_cost'];
     $totale = $totale + ($app * $prez_acquisto );
	 echo "</tr>\n";
     }}
   
    echo "</table><br><table align='center' border='0'><tr><td><h3 align=\"center\" >Totale <input readonly type='text' name='totale' value='".gaz_format_number($totale)."'></td><tr>";
    echo "<tr align='center'><td><input type='button' name='resetta' value='Azzera ordine' onclick='azzera(".$righe.")'>";
    echo "<input type='button' name='Ricalcola' value='Ricalcola' onclick='ricalcola(".$righe.")'>";
    echo "<input type='submit' name='ins' value='INSERISCI'></td></tr><tr align='center'><td>(totale righe n.".$righe.")";
    echo "<input type='hidden' name='righe' value='".$righe."'></td></tr></table>";
 }
?>
  <input type="hidden" name="clfo_pas" value="<?php echo $form['id_anagra'];?>">
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
<input type="hidden" name="clfoco" value="<?php echo $_POST['clfo_pas'];?>">
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
<input type="hidden" name="in_codvat" value="">

<input type="hidden" value="10" name="delivery_time">
<input type="hidden" value="15" name="day_of_validity">
<input type="hidden" value="0" name="speban" />
<input type="hidden" value="1" name="numrat" />
<input type="hidden" value="0" name="spevar" />
<input type="hidden" value="0" name="cauven" />
<input type="hidden" value="" name="caucon" />
<input type="hidden" value="5" name="caumag" />
<input type="hidden" value="0" name="id_agente" />
<input type="hidden" value="0" name="id_parent_doc" /> 
<input type="hidden" value="" name="pagame" />
<input type="hidden" value="" name="coseprod" />
<input type="hidden" value="1" name="print_total" />
<input type="hidden" value="" name="banapp" />
<input type="hidden" value="<?php echo date('d');?>" name="giocon" />
<input type="hidden" value="<?php echo date('m');?>" name="mescon" />
<input type="hidden" value="<?php echo date('Y');?>" name="anncon" />
<input type="hidden" value="" name="spediz" />
<input type="hidden" value="" name="portos" />
<input type="hidden" value="0" name="sconto" />

<input type="hidden" value="" name="in_codice_fornitore" />
<input type="hidden" value="" name="in_quality" id="in_quality" />
<input type="hidden" value="" name="in_descri" />
<input type="hidden" value="" name="in_pervat" />
<input type="hidden" value="" name="in_unimis" />
<input type="hidden" value="0" name="in_prelis" />
<input type="hidden" value="0" name="in_extdoc" />
<input type="hidden" value="0" name="in_id_mag" />
<input type="hidden" value="" name="in_annota" />
<input type="hidden" value="0" name="in_larghezza" />
<input type="hidden" value="0" name="in_lunghezza" />
<input type="hidden" value="0" name="in_spessore" />
<input type="hidden" value="0" name="in_peso_specifico" />
<input type="hidden" value="0" name="in_pezzi" />
<input type="hidden" value="INSERT" name="in_status" />
<input type="hidden" name="in_id_orderman" value="" />
<input type="hidden" name="sconto" value="0" />



<br>
<h1>Generazione ordine in corso
<?php 
$i = 1;
for ($k=1;$k<=$_POST['righe'];$k++)
{
if ($_POST['acquista'][$k] > 0)
{
$result = gaz_dbi_query("Select codice_fornitore,".$gTables['artico'].".descri,uniacq,aliquo from ".$gTables['artico']." join ".$gTables['aliiva']." on (".$gTables['aliiva'].".codice = ".$gTables['artico'].".aliiva ) where (".$gTables['artico'].".codice = '".$_POST['codice'][$k]."') limit 1;");
$a_row = gaz_dbi_fetch_array($result);
echo'.';
echo'.<input type="hidden" value="'.$_POST['codice'][$k].'" name="rows['.$i.'][codart]">';
echo'<input type="hidden" value="INSERT" name="rows['.$i.'][status]">';
echo'<input type="hidden" value="0" name="rows['.$i.'][tiprig]">';
echo'<input type="hidden" value="1" name="rows['.$i.'][codvat]">';
echo'<input type="hidden" value="'.$a_row['aliquo'].'" name="rows['.$i.'][pervat]">';
echo'<input type="hidden" value="330000004" name="rows['.$i.'][codric]">';
echo'<input type="hidden" name="rows['.$i.'][codice_fornitore]" value="'.$a_row['codice_fornitore'].'" />';
echo'<input type="hidden" name="rows['.$i.'][descri]" value="'.$a_row['descri'].'" />';
echo'<input type="hidden" name="rows['.$i.'][unimis]" value="'.$a_row['uniacq'].'" />';
echo'<input type="hidden" name="rows['.$i.'][quanti]" value="'.$_POST['acquista'][$k].'" />';
echo'<input type="hidden" name="rows['.$i.'][prelis]" value="'.$_POST['prezzo'][$k].'"  />';
echo'<input type="hidden" name="rows['.$i.'][sconto]" value="0"  />';
echo'<input type="hidden" name="rows['.$i.'][quality]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][id_orderman]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][id_mag]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][annota]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][larghezza]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][lunghezza]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][spessore]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][peso_specifico]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][pezzi]" value=""  />';
echo'<input type="hidden" name="rows['.$i.'][extdoc]" value=""  />';
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