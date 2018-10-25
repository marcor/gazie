<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend = checkAdmin();$msg="";

if (isset ($_GET['popup'])){ //controllo se proviene da una richiesta apertura popup
		$popup=$_GET['popup'];
	}
	else {
		$popup="";
	}

if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

if ((isset($_GET['Update']) and  !isset($_GET['codice'])) or isset($_POST['Return'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
}


if ((isset($_POST['Insert'])) or ( isset($_POST['Update']))) {    // Antonio Germani se non e' il primo accesso
	$form=gaz_dbi_parse_post('orderman');
	$form['description'] = $_POST['description'];
	$form['id_tesbro']= $_POST['id_tesbro'];
	$form['gioinp'] = $_POST['gioinp'];
	$form['mesinp'] = $_POST['mesinp'];
	$form['anninp'] = $_POST['anninp'];
	$form['day_of_validity'] = $_POST['day_of_validity'];
	$form["campo_impianto"] = $_POST["campo_impianto"];
	$form['order']=$_POST['order'];
	$form['artico']=$_POST['artico'];
	$form['nmov']=$_POST['nmov'];
	for ($m = 0; $m <= $form['nmov']; ++$m){
		$form['staff'][$m] = $_POST['staff'.$m];	
	}
	$form['filename']=$_POST['filename'];
	$form['identifier']=$_POST['identifier'];
	$form['expiry']=$_POST['expiry'];

 

// Antonio Germani > questo serve per aggiungere o togliere un operaio
if (isset($_POST['add_staff'])){ 
	$form['nmov']=$_POST['nmov'];
	for ($m = 0; $m <= $form['nmov']; ++$m){
		$form['staff'][$m] = $_POST['staff'.$m];	
	}
	$form['nmov']=$form['nmov']+1;
	$form['staff'][$form['nmov']] = "";
}
if (isset($_POST['Del_mov'])) {
	$form['staff'][$form['nmov']] = "";
	If ($_POST['nmov']>0) {
		$form['nmov']=$form['nmov']-1;
	}
}

    // Se viene inviata la richiesta di conferma totale ...
	$form['datemi'] = $form['anninp'] . "-" . $form['mesinp'] . "-" . $form['gioinp'];
    if (isset($_POST['ins'])) {
       
       
       if (empty($form['description'])){  //descrizione vuota
             $msg .= "4+";
       } 
	   
	   if (strlen($form['order_type'])<3){  //tipo produzione vuota
             $msg .= "12+";
       } 
	   
       if ($msg == "") {// nessun errore
 // Antonio Germani  qui si scrive il database       
          if ($toDo == 'update') { // Antonio Germani e' una modifica quindi aggiorno orderman e tesbro
            $query="UPDATE ".$gTables['orderman']." SET ".'order_type'." = '".$form['order_type']."', ".'description'." = '".$form['description']."', ".'campo_impianto'." = '".$form["campo_impianto"]."', ".'add_info'." = '".$form['add_info']."' WHERE id = '".$form['id']."'";
		 	   $res = gaz_dbi_query($query);
			$query="UPDATE ".$gTables['tesbro']." SET ".'datemi'." = '".$form['datemi']."', ".'day_of_validity'." = '".$form['day_of_validity']."' WHERE id_tes = '".$form['id_tesbro']."'";
			  $res = gaz_dbi_query($query);    
          } else { // e' un'inserimento
		  gaz_dbi_table_insert('tesbro',$form);
		  $query="SHOW TABLE STATUS LIKE '".$gTables['tesbro']."'"; // vedo dove lo ha scritto
				$result = gaz_dbi_query($query);
				$row = $result->fetch_assoc();
				$id_movmag = $row['Auto_increment'];
				// siccome ha già registrato la produzione devo togliere 1
				$form['id_tesbro']=$id_movmag-1; //Antonio Germani connetto tesbro a orderman
            gaz_dbi_table_insert('orderman',$form);
			
			
          }
		  if($popup==1){
		  echo "<script> 
        window.opener.location.reload(true);
        window.close();</script>";
		
		  }else {
          header("Location: ".$_POST['ritorno']);}
          exit;
       }
  }


} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE

$result = gaz_dbi_get_row($gTables['orderman'],"id",$_GET['codice']);
	$form['ritorno'] = $_POST['ritorno'];
	$form['id']=$_GET['codice'];
    $form['order_type']=$result['order_type'];
    $form['description']=$result['description'];
    $form['id_tesbro']=$result['id_tesbro'];
	$form['add_info']=$result['add_info'];
	$result2 = gaz_dbi_get_row($gTables['tesbro'],"id_tes",$result['id_tesbro']);
	$form['gioinp'] = substr($result2['datemi'], 8, 2);
	$form['mesinp'] = substr($result2['datemi'], 5, 2);
	$form['anninp'] = substr($result2['datemi'], 0, 4);	
	$form['datemi']=$result2['datemi'];
	$form['day_of_validity']=$result2['day_of_validity'];
	$form["campo_impianto"]=$result['campo_impianto'];
	$form['order']=$_POST['order'];
	$form['artico']=$_POST['artico'];
	$form['mov']=$_POST['mov'];
	$form['staff'][$form['mov']]=$_POST['staff'.$m]; 

} else { //se e' il primo accesso per INSERT
    
	$form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['order_type']="";
    $form['description']="";
    $form['id_tesbro']="";
	$form['add_info']="";
	$form['gioinp'] = date("d");
    $form['mesinp'] = date("m");
    $form['anninp'] = date("Y");
	$form['day_of_validity']="";
	$form["campo_impianto"] ="";
	$form['order']="";
	$form['artico']="";
	$form['mov']=0;
	$form['nmov']=0;
	$form['staff'][$form['mov']]="";
	$form['filename']="";
	$form['identifier']="";
	$form['expiry']="";
}
If (isset($_POST['Cancel'])){ // se è stato premuto ANNULLA
	$form['hidden_req'] = ''; 
	$form['order_type']="";
    $form['description']="";
    $form['id_tesbro']="";
	$form['add_info']="";
	$form['gioinp'] = date("d");
    $form['mesinp'] = date("m");
    $form['anninp'] = date("Y");
	$form['day_of_validity']="";
	$form["campo_impianto"] ="";
	$form['order']="";
	$form['artico']="";
	$form['mov']=0;
	$form['nmov']=0;
	$form['staff'][$form['mov']]="";
	$form['filename']="";
	$form['identifier']="";
	$form['expiry']="";	
}
require("../../library/include/header.php");
$script_transl = HeadMain();
if ($toDo == 'update') {
   $title = ucwords($script_transl['upd_this'])." n.".$form['id'];
} else {
   $title = ucwords($script_transl['ins_this']);
}

print "<form method=\"POST\" enctype=\"multipart/form-data\">\n";
print "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">\n";
print "<input type=\"hidden\" name=\"id_tesbro\" value=\"".$form['id_tesbro']."\">\n";
print "<input type=\"hidden\" value=\"".$_POST['ritorno']."\" name=\"ritorno\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">$title</div>";
print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice( explode('+',chop($msg)),0,-1);
    foreach ($rsmsg as $value){
            $message .= $script_transl['error']."! -> ";
            $rsval = explode('-',chop($value));
            foreach ($rsval as $valmsg){
                    $message .= $script_transl[$valmsg]." ";
            }
            $message .= "<br />";
    }
    echo '<tr><td colspan="5" class="FacetDataTDred">'.$message."</td></tr>\n";
}
if ($toDo == 'update') {
   print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[0]</td><td class=\"FacetDataTD\"><input type=\"hidden\" name=\"id\" value=\"".$form['id']."\" />".$form['id']."</td></tr>\n";
}

// Antonio Germani > inserimento tipo di produzione 
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td><td class=\"FacetDataTD\">";
	if(isset($_POST['order_type'])){
		$form['order_type'] = $_POST["order_type"]; // memorizzo il valore selezionato
	}
?>
<select name="order_type" onchange="this.form.submit()" >
<option <?php if ($form['order_type'] == "" ) echo 'selected' ; ?> value="">--</option>
<option <?php if ($form['order_type'] == "AGR" ) echo 'selected' ; ?> value="AGR">Agricola</option>
<option <?php if ($form['order_type'] == "IND" ) echo 'selected' ; ?> value="IND">Industriale</option>
</select>
</td></tr>
<?php


// Antonio Germani > inserimento ordine	
?>
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['8']; ?> </td>
<!-- Antonio Germani inizio script autocompletamento ORDINI dalla tabella mysql tesbro	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['tesbro'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['numdoc']." - ".$row['clfoco']."\", ";			
	}
	$stringa=substr($stringa,0,-1);
	echo $stringa;
	?>],
		minLength:1,
	select: function(event, ui) {
        //assign value back to the form element
        if(ui.item){
            $(event.target).val(ui.item.value);
        }
        //submit the form
        $(event.target.form).submit();
    }
	});
	});
  </script>
 <!-- fine autocompletamento -->	
	
	<td colspan="2" class="FacetDataTD">
		<input id="autocomplete" type="text" name="order" Value="<?php echo $form['order']; ?>"/>
	</td>
</tr>

<!-- Antonio Germani > inserimento articolo	-->
<tr> 
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['9']; ?> </td>
<!-- Antonio Germani inizio script autocompletamento ARTICOLO dalla tabella mysql tesbro	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete2").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['artico'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['codice']." - ".$row['descri']."\", ";			
	}
	$stringa=substr($stringa,0,-1);
	echo $stringa;
	?>],
		minLength:1,
	select: function(event, ui) {
        //assign value back to the form element
        if(ui.item){
            $(event.target).val(ui.item.value);
        }
        //submit the form
        $(event.target.form).submit();
    }
	});
	});
  </script>
 <!-- fine autocompletamento -->	
	
	<td colspan="2" class="FacetDataTD">
		<input id="autocomplete2" type="text" name="artico" Value="<?php echo $form['artico']; ?>"/>
	</td>
</tr>

<!--- Antonio Germani - inserimento descrizione  -->
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['2']; ?> </td>
	<td colspan="2" class="FacetDataTD">
		<textarea type="text" name="description" align="right" maxlength="255" cols="67" rows="3"><?php echo $form['description']; ?></textarea>
	</td>
</tr>
<?php
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[3]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"add_info\" value=\"".$form['add_info']."\" maxlength=\"80\" size=\"80\" /></td></tr>\n";
// DATA inizio produzione
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[5] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"gioinp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
    $selected = "";
    if ($counter == $form['gioinp'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesinp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
    $selected = "";
    if ($counter == $form['mesinp'])
        $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"anninp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
    $selected = "";
    if ($counter == $form['anninp'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td>\n";
// end data inizio produzione

// Antonio Germani > DURATA produzione
if ($form['order_type'] == "AGR") {
	print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[6]</td>";
} else {
	if ($form['order_type'] == "IND") {
		print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[11]</td>";
	} else {
	print"<tr><td>";
	}
}
 

	print "<td class=\"FacetDataTD\"><input type=\"number\" name=\"day_of_validity\" min=\"0\" maxlength=\"3\" step=\"any\" size=\"3\" value=\"".$form['day_of_validity']."\"  /></td></tr>\n";


		
/*Antonio Germani LUOGO di produzione  */
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"campo_impianto\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
echo "<option value=\"\">-------------</option>\n";
$result = gaz_dbi_dyn_query("*", $gTables['campi']);
while ($row = gaz_dbi_fetch_array($result)) {
    $selected = "";
    if ($form["campo_impianto"] == $row['codice']) {
        $selected = " selected ";
    }
    echo "<option value=\"" . $row['codice'] . "\"" . $selected . ">" . $row['codice'] . " - " . $row['descri'] . "</option>\n";
} 
echo "</select></td></tr>";

// Antonio Germani selezione operai
			for ($form['mov'] = 0; $form['mov'] <= $form['nmov']; ++$form['mov']){
				echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[10] . "</td><td class=\"FacetDataTD\">\n";
				echo '<select name="staff'.$form['mov'].'" class="FacetSelect" onchange="this.form.submit()">';	echo "<option value=\"\">-------------</option>\n";
				$result = gaz_dbi_dyn_query("*", $gTables['staff']);
				while ($row = gaz_dbi_fetch_array($result)) {
					$selected = ""; 
					if ($form['staff'][$form['mov']] == $row['id_staff']) {
						$selected = " selected ";
					}
					$anagra = gaz_dbi_get_row($gTables['clfoco'], "codice", $row['id_clfoco']); 
					echo "<option value=\"" . $row['id_staff'] . "\"" . $selected . ">" . $row['id_staff'] . " - " . $anagra['descri'] . "</option>\n"; 
				}
				echo "</select>";
				;
				
				If ($form['staff'][$form['mov']] > 0) {
					echo "<input type=\"submit\" name=\"add_staff\" value=\"" . $script_transl['add_staff'] . "\">\n";
				}
				If ($form['mov']>0){
				echo "<input type=\"submit\" title=\"Togli ultimo movimento\" name=\"Del_mov\" value=\"X\">\n";
				}
				
			} $form['mov']=$form['nmov'];
	echo "<input type=\"hidden\" name=\"nmov\" value=\"" . $form['nmov'] . "\">\n</td></tr>";

	
// Antonio Germani > Inizio LOTTO in entrata
	
		
		?>
		  
<script>
  $(function() {
    $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
});
</script>

		<tr><td class="FacetFieldCaptionTD"><?php echo $script_transl[13];?></td>
		<td class="FacetDataTD" >
			  <input type="hidden" name="filename" value="<?php echo $form['filename']; ?>">			  
		<?php 
		
		
              if (strlen($form['filename'])==0) {
                    echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog">'. 'Inserire nuovo certificato' . ' '.'<i class="glyphicon glyphicon-tag"></i>'
                    . '</button></div>';
			  } else {
				  echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog">'. $form['filename'] . ' '.'<i class="glyphicon glyphicon-tag"></i>'
                    . '</button>';
					if ($toDo=="update"){
						foreach (glob("../../modules/camp/tmp/*") as $fn) {// prima cancello eventuali precedenti file temporanei
							unlink($fn);
						} 
						if (strlen($form['filename'])>0) {
							$tmp_file = "../../data/files/".$admin_aziend['company_id']."/".$form['filename'];
							// sposto nella cartella di lettura il relativo file temporaneo            
							copy($tmp_file, "../../modules/camp/tmp/".$form['filename']);
						}
					?>
						<a  class="btn btn-info btn-md" href="javascript:;" onclick="window.open('<?php echo"../../modules/camp/tmp/".($form['filename'])?>', 'titolo', 'width=800, height=400, left=80%, top=80%, resizable, status, scrollbars=1, location');">
						<span class="glyphicon glyphicon-eye-open"></span></a></div>
					<?php
					} else {
						echo '</div>';
					}
			  }		
/*Antonio Germani scelta lotto fra quelli esistenti  DA RIVEDERE PERCHE IN PRODUZIONE INDUSTRIALE AD OGNI PRODUZIONE CORRISPONDE UN NUOVO LOTTO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
			$query="SELECT ".'*'." FROM ".$gTables['lotmag']. " WHERE codart ='". $form['artico']."'";
			$result = gaz_dbi_query($query);
			if ($result->num_rows >0) { // se ci sono lotti attivo la selezione
				echo '<select name="id_lotmag" class="FacetSelect" onchange="this.form.submit()">\n';
				echo "<option value=\"\">-seleziona fra lotti esistenti-</option>\n";	
				$sel=0;
				while ($rowlot = gaz_dbi_fetch_array($result)) {
					$selected = "";
					if ($form['id_lotmag'] == $rowlot['id']) {
						$selected = " selected ";$sel=1;
					}
					echo "<option value=\"" . $rowlot['id'] . "\"" . $selected . ">" . $rowlot['id'] . " - " . $rowlot['identifier'] . " - " . gaz_format_date($rowlot['expiry']) . "</option>\n";
				} 
				echo "</select>&nbsp;";
				If ((intval($form['id_lotmag'])>0) && (intval($sel)==1)){ // se è stato selezionato un lotto
					$rowlot = gaz_dbi_get_row($gTables['lotmag'], "id", $form['id_lotmag']);	
					$form['identifier']=$rowlot['identifier']; 
					$form['expiry']=$rowlot['expiry'];
				} 
			}
			// fine scelta lotto fra esistenti 					
              if (strlen($form['identifier'])==0){
                    echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog_lot">' . 'Inserire nuovo Lotto' . ' ' . '<i class="glyphicon glyphicon-tag"></i></button></div>';
			  } else {
				  if(intval($form['expiry'])>0){
					echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot">' . $form['identifier']. ' ' . gaz_format_date($form['expiry']) . '<i class="glyphicon glyphicon-tag"></i></button></div>';
				  } else{
					 echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot" >' . $form['identifier']. '<i class="glyphicon glyphicon-tag" ></i></button></div>'; 
				  }
			  }		  
                echo '<div id="lm_dialog" class="collapse" >
                        <div class="form-group">
                          <div>';						
				?>
               <input type="file" onchange="this.form.submit();" name="docfile_">
			 </div>
		     </div>
             </div>
				<?php
			  
				 echo '<div id="lm_dialog_lot" class="collapse" >
                        <div class="form-group">
                          <div>';
				echo '<label>' . "Numero: " . '</label><input type="text" name="identifier" value="'.$form['identifier'].'" >';
				echo "<br>";			
                echo '<label>' . 'Scadenza: ' . ' </label><input class="datepicker" type="text" onchange="this.form.submit();" name="expiry"  value="' . $form['expiry']. '"></div></div></div>';
    
// fine LOTTI in entrata	
	
	if ($popup<>1){
		//ANNULLA/RESET NON FUNZIONA DA RIVEDERE > print "<tr><td class=\"FacetFieldCaptionTD\"><input type=\"reset\" name=\"Cancel\" value=\"".$script_transl['cancel']."\">\n";
		print "<tr><td class=\"FacetDataTD\" align=\"right\">\n";
		print "<input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">\n";
	} 
	else {
		print "<tr><td>";
	}
if ($toDo == 'update') {
   print '<input type="submit" accesskey="m" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.strtoupper($script_transl['update']).'!">';
} else {
   print '<input type="submit" accesskey="i" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.strtoupper($script_transl['insert']).'!">';
}
print "</td></tr></table>\n";
?>
</form>

<?php

require("../../library/include/footer.php");
?>