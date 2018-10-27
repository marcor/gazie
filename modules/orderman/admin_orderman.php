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
	$form['lot_or_serial']=$_POST['lot_or_serial'];
	$form['datreg']=$_POST['datreg'];
	$form['quanti']=$_POST['quanti'];

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

    // Se viene inviata la richiesta di conferma totale ...CONTROLLO ERRORI
	$form['datemi'] = $form['anninp'] . "-" . $form['mesinp'] . "-" . $form['gioinp'];
    if (isset($_POST['ins'])) {
       
       
       if (empty($form['description'])){  //descrizione vuota
             $msg .= "4+";
       } 
	   
	   if (strlen($form['order_type'])<3){  //tipo produzione vuota
             $msg .= "12+";
       } 
	   
		if ($msg == "") {// nessun errore
	   
 // Antonio Germani >>>> inizio SCRITTURA dei database    §§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§
 
			if ($form['order_type']=="IND") { // >>> !!!! DA FARE !!!! <<< distingure se IND o AGR. Se AGR deve interagire con movmag di camp!!!!!
			}
			// ricarico i dati dell'articolo che non sono nel form; li avrò in array $resartico
			$resartico = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
		
			if ($toDo=="update"){ // se è un update cancello eventuali precedenti file temporanei nella cartella tmp
						foreach (glob("../../modules/orderman/tmp/*") as $fn) { 
							unlink($fn);
						}
			} else { // se è insert
				if ($form['order_type']=="IND") { // se produzione industriale 
					$query="SHOW TABLE STATUS LIKE '".$gTables['movmag']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_movmag = $row['Auto_increment']; // trovo l'ID che avrà il movimento di magazzino MOVMAG
				}
					$query="SHOW TABLE STATUS LIKE '".$gTables['orderman']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_orderman = $row['Auto_increment']; // trovo l'ID che avrà il movimento di produzione ORDERMAN
					$query="SHOW TABLE STATUS LIKE '".$gTables['lotmag']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_lotmag = $row['Auto_increment']; // trovo l'ID che avrà il movimento di magazzino
					$query="SHOW TABLE STATUS LIKE '".$gTables['tesbro']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_tesbro = $row['Auto_increment']; // trovo l'ID che avrà TESBRO testata documento
					$query="SHOW TABLE STATUS LIKE '".$gTables['rigbro']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_rigbro = $row['Auto_increment']; // trovo l'ID che avrà RIGBRO rigo documento	
			}	
				
// scrittura movimento di magazzino MOVMAG
			if ($toDo=="update"){ // se è update, aggiorno in ogni caso
				$query="UPDATE " . $gTables['movmag'] . " SET tipdoc = 'MAG' , campo_coltivazione = '"  .$form['campo_impianto']. "' , id_avversita = '"."' , id_colture = '"."' , id_orderman = '"  .$id_orderman. "' , id_lotmag = '" .$id_lotmag. "' WHERE id_mov ='". $id_movmag."'"; 
				gaz_dbi_query ($query) ;
			}
			if ($toDo=="insert" && $form['order_type']=="IND"){ // se è insert, creo il movimento di magazzino solo se produzione industriale
				$query="INSERT INTO " . $gTables['movmag'] . "(type_mov,datreg,tipdoc,desdoc,datdoc,artico,campo_coltivazione,quanti,id_orderman,id_lotmag,adminid) VALUES ('0', '".$form['datreg']."', 'MAG', 'Produzione', '".$form['datemi']."', '".$form['artico']."', '".$form['campo_impianto']."', '".$form['quanti']."', '".$id_orderman."', '".$id_lotmag."', '".$admin_aziend['adminid']."')"; 
				gaz_dbi_query ($query) ;
			}
		 
//Antonio Germani - > inizio salvo LOTTO, se c'è lotto e se il prodotto li richiede
		
		if ($form['lot_or_serial']> 0 ) { // se l'articolo prevede un lotto 
		// ripulisco il numero lotto inserito da caratteri dannosi
			$form['identifier'] = (empty($form['identifier'])) ? '' : filter_var($form['identifier'], FILTER_SANITIZE_STRING);
			if (strlen ($form['identifier']) ==0) { // se non c'è il lotto lo inserisco con data e ora in automatico
			$form['identifier']=date("Ymd Hms");
			}
			if (strlen ($form['expiry']) ==0) { // se non c'è la scadenza la inserisco a zero in automatico
			$form['expiry']="0000-00-00 00:00:00";
			} 
		 // è un nuovo INSERT
			if (strlen ($form['identifier']) >0 && $toDo=="insert") {
			
				gaz_dbi_query("INSERT INTO " . $gTables['lotmag'] . "(codart,id_movmag,identifier,expiry) VALUES ('". $form[	'artico'] . "','" . $id_movmag . "','" . $form['identifier'] . "','" . $form['expiry'] . "')");
					
			}		 
		 //  è un UPDATE 
		 
			if (strlen ($form['identifier']) >0  && $toDo=="update"){	die;//da controllare il valore di id_lotmag			
				gaz_dbi_query("UPDATE " . $gTables['lotmag'] . " SET codart = '" . $form['artico'] . "' , id_movmag = '" . $id_movmag . "' , identifier = '" . $form['identifier'] . "' , expiry = '" . $form['expiry'] . "' WHERE id = '" . $form['id_lotmag'] . "'");
			}		
		}
		
// Antonio Germani - inizio salvo documento/CERTIFICATO lotto
		if ($toDo=="update") { // imposto $form id_lotmag
			$form['id_lotmag'];
		} else {
			$form['id_lotmag']=$id_lotmag;
		}
		if (substr($form['filename'], 0, 7) <> 'lotmag_') { // se è stato cambiato il file, cioè il nome non inizia con lotmag e, quindi, anche se è un nuovo insert
			if (!empty($form['filename'])) { // e se ha un nome impostato nel form
				$tmp_file = "../../data/files/tmp/" . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $form['filename'];
				// sposto il file nella cartella definitiva, rinominandolo e cancellandolo dalla temporanea    
				$fd = pathinfo($form['filename']);
				rename($tmp_file, "../../data/files/" . $admin_aziend['company_id'] . "/lotmag_" . $form['id_lotmag'] . '.' . $fd['extension']);
			}
		} 
// <<< fine salvo lotti	
			
// Scrittura produzione ORDERMAN e, se non già creati da un ordine, scrittura di TESBRO E RIGBRO
			if ($toDo == 'update') { // DA CONTROLLARE ??????????? Antonio Germani e' una modifica quindi aggiorno orderman e tesbro
				$query="UPDATE ".$gTables['orderman']." SET ".'order_type'." = '".$form['order_type']."', ".'description'." = '".$form['description']."', ".'campo_impianto'." = '".$form["campo_impianto"]."', ".'add_info'." = '".$form['add_info']."' WHERE id = '".$form['id']."'";
		 	   $res = gaz_dbi_query($query);
				$query="UPDATE ".$gTables['tesbro']." SET ".'datemi'." = '".$form['datemi']."', ".'day_of_validity'." = '".$form['day_of_validity']."' WHERE id_tes = '".$form['id_tesbro']."'";
				$res = gaz_dbi_query($query);    
			} else { // e' un'inserimento
												// creo e salvo ORDERMAN
				gaz_dbi_query("INSERT INTO " . $gTables['orderman'] . "(order_type,description,add_info,id_tesbro,id_rigbro,campo_impianto,id_lotmag,adminid) VALUES ('". $form[	'order_type'] . "','" . $form['description'] . "','" . $form['add_info'] . "','" . $id_tesbro . "', '" . $id_rigbro . "', '" . $form['campo_impianto'] . "', '" . $id_lotmag . "', '". $admin_aziend['adminid'] ."')");
				
				if (strlen($form['order'])<1){ // se non c'è un ordine ne creo uno fittizio in TESBRO e RIGBRO
					gaz_dbi_query("INSERT INTO " . $gTables['tesbro'] . "(tipdoc,day_of_validity,datemi,id_orderman,adminid) VALUES ('PRO','" . $form['day_of_validity'] . "','" . $form['datemi'] . "','" . $id_orderman ."', '".$admin_aziend['adminid']."')");
					gaz_dbi_query("INSERT INTO " . $gTables['rigbro'] . "(id_tes,codart,descri,unimis,quanti) VALUES ('".$id_tesbro."','" . $form['artico'] . "','" . $resartico['descri'] . "','" . $resartico['unimis'] ."', '".$form['quanti']."')");
				}
			}
		// se sono in un popup lo chiudo dopo aver salvato tutto	
			if($popup==1){
				echo "<script> 
				window.opener.location.reload(true);
				window.close();</script>";
			}else {
				header("Location: ".$_POST['ritorno']);
				}
				exit;
			
		}
	}
//  fine scrittura database §§§§§§§§§§§§§§§§§§§§§§§§§§§§

} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE

$result = gaz_dbi_get_row($gTables['orderman'],"id",$_GET['codice']);
	$form['ritorno'] = $_POST['ritorno'];
	$form['id']=$_GET['codice'];
    $form['order_type']=$result['order_type'];
    $form['description']=$result['description'];
    $form['id_tesbro']=$result['id_tesbro'];
	$form['id_rigbro']=$result['id_rigbro'];
	$form['add_info']=$result['add_info'];
$result2 = gaz_dbi_get_row($gTables['tesbro'],"id_tes",$result['id_tesbro']);
	$form['gioinp'] = substr($result2['datemi'], 8, 2);
	$form['mesinp'] = substr($result2['datemi'], 5, 2);
	$form['anninp'] = substr($result2['datemi'], 0, 4);	
	$form['datemi']=$result2['datemi'];
	$form['day_of_validity']=$result2['day_of_validity'];
	$form["campo_impianto"]=$result['campo_impianto'];
	$form["id_colture"]=$result['id_colture'];
	$form["id_lotmag"]=$result['id_lotmag'];
	$form['order']=$result2['numdoc'];
$result3 = gaz_dbi_get_row($gTables['rigbro'],"id_rig",$result['id_rigbro']);
	$form['artico']=$result3['codart'];
$result4 = gaz_dbi_get_row($gTables['movmag'],"id_orderman",$_GET['codice']);	
	$form['datreg']=$result4['datreg'];
	$form['quanti']=$result4['quanti'];
$result5 = gaz_dbi_get_row($gTables['lotmag'],"id",$result['id_lotmag']);		
	$form['identifier']=$result5['identifier'];
	$form['expiry']=$result5['expiry'];
// Antonio Germani - se è presente, recupero il file documento lotto
	$form['filename'] = "";
	If (file_exists('../../data/files/' . $admin_aziend['company_id'])>0) {		
			// recupero il filename dal filesystem 
			$dh = opendir('../../data/files/' . $admin_aziend['company_id']);
			while (false !== ($filename = readdir($dh))) {
				$fd = pathinfo($filename); 
				$r = explode('_', $fd['filename']); 
				if ($r[0] == 'lotmag' && $r[1] == $result['id_lotmag']) {
					// riassegno il nome file 
					$form['filename'] = $fd['basename'];
				} 
			}
		} 	
// se presenti, prendo gli operai
		$query="SELECT ".'*'." FROM ".$gTables['staff_worked_hours']. " WHERE id_orderman ='". $_GET['codice']."'";
	$result6 = gaz_dbi_query($query);$form['mov']=0;$form['nmov']=0;$form['staff'][$form['mov']]="";
	if ($result6->num_rows >0){
	while($row = $result->fetch_assoc()){
		$form['staff'][$form['mov']]=$result6['id_staff'];
		$form['mov']++;
	}
	$form['nmov']=$form['mov']-1;
	}
	

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
	$form['lot_or_serial']="";
	$form['datreg']=""; // meglio se today >>>> modificare <<<<<<<<
	$form['quanti']="";
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
	$form['quanti']="";
}
if (!empty ($_FILES['docfile_']['name'])) { // Antonio Germani - se c'è un nome in $_FILES
	$prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'];
	foreach (glob("../../data/files/tmp/" . $prefix . "_*.*") as $fn) {// prima cancello eventuali precedenti file temporanei
             unlink($fn);
    }
	
	$mt = substr($_FILES['docfile_']['name'], -3);
	if (($mt == "png" || $mt == "odt" || $mt == "peg" || $mt == "jpg" || $mt == "pdf") && $_FILES['docfile_']['size'] > 1000){ // se rispetta limiti e parametri lo salvo nella cartella tmp
		move_uploaded_file($_FILES['docfile_']['tmp_name'], '../../data/files/tmp/' . $prefix . '_' . $_FILES['docfile_']['name']);                
		$form['filename']=$_FILES['docfile_']['name'];
	} else {
		$msg .= "14+";
	}
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
<script>
  $(function() {
    $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
});
</script>
<select name="order_type" onchange="this.form.submit()" >
<option <?php if ($form['order_type'] == "" ) echo 'selected' ; ?> value="">--</option>
<option <?php if ($form['order_type'] == "AGR" ) echo 'selected' ; ?> value="AGR">Agricola</option>
<option <?php if ($form['order_type'] == "IND" ) echo 'selected' ; ?> value="IND">Industriale</option>
</select>
<?php
echo '<label>' . 'Data registrazione: ' . ' </label><input class="datepicker" type="text" onchange="this.form.submit();" name="datreg"  value="' . $form['datreg']. '">';
?>
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
<!-- Antonio Germani inizio script autocompletamento ARTICOLO dalla tabella mysql artico	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete2").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['artico'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['codice']."\", ";		
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
	<?php // prendo i dati dall'articolo
		$resartico = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
		echo $resartico['descri'];// visualizzo la descrizione
		echo '<input type="hidden" name="lot_or_serial" value="' . $resartico['lot_or_serial'] . '"';$form['lot_or_serial']=$resartico['lot_or_serial'];
		
	?>	
	</td>
</tr>

<!--- Antonio Germani - inserimento quantità  -->
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['15']; ?> </td>
	<td colspan="2" class="FacetDataTD">
		<input type="text" name="quanti" value="<?php echo $form['quanti']; ?>" />
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
				echo "<input type=\"submit\" title=\"Togli ultimo operaio\" name=\"Del_mov\" value=\"X\">\n";
				}
				
			} $form['mov']=$form['nmov'];
	echo "<input type=\"hidden\" name=\"nmov\" value=\"" . $form['nmov'] . "\">\n</td></tr>";

// Antonio Germani > Inizio LOTTO in entrata o creazione nuovo	
if (intval($form['lot_or_serial']) == 1) { // se l'articolo prevede il lotto apro la gestione lotti
?>	  

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
						foreach (glob("../../modules/orderman/tmp/*") as $fn) {// prima cancello eventuali precedenti file temporanei
							unlink($fn);
						} 
						if (strlen($form['filename'])>0) {
							$tmp_file = "../../data/files/".$admin_aziend['company_id']."/".$form['filename'];
							// sposto nella cartella di lettura il relativo file temporaneo            
							copy($tmp_file, "../../modules/orderman/tmp/".$form['filename']);
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
	/*		$query="SELECT ".'*'." FROM ".$gTables['lotmag']. " WHERE codart ='". $form['artico']."'";
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
			}          */
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
} else { 
	echo '<tr><td><input type="hidden" name="filename" value="' . $form['filename'] . '">';
	echo '<input type="hidden" name="identifier" value="' . $form['identifier'] . '">';
	echo '<input type="hidden" name="expiry" value="' . $form['expiry'] . '"></td></tr>';
	
}   
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