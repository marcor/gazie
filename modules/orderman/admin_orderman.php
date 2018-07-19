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


    // Se viene inviata la richiesta di conferma totale ...
	$form['datemi'] = $form['anninp'] . "-" . $form['mesinp'] . "-" . $form['gioinp'];
    if (isset($_POST['ins'])) {
       
       
       if (empty($form['description'])){  //descrizione vuota
             $msg .= "4+";
       } 
       if ($msg == "") {// nessun errore
 // Antonio Germani  qui si scrive il database       
          if ($toDo == 'update') { // Antonio Germani e' una modifica quindi aggiorno orderman e tesbro
            $query="UPDATE ".$gTables['orderman']." SET ".'order_type'." = '".$form['order_type']."', ".'description'." = '".$form['description']."', ".'add_info'." = '".$form['add_info']."' WHERE id = '".$form['id']."'";
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

/*DA FARE <<<<<<<<<<<*/
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

} else { //se e' il primo accesso per INSERT
    
	$form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['order_type']='';
    $form['description']='';
    $form['id_tesbro']='';
	$form['add_info']='';
	$form['gioinp'] = date("d");
    $form['mesinp'] = date("m");
    $form['anninp'] = date("Y");
	$form['day_of_validity']='';
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
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"order_type\" value=\"".$form['order_type']."\" maxlength=\"3\" size=\"3\" /></td></tr>\n";
?>
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['2']; ?> </td>
	<td colspan="2" class="FacetDataTD">
		<textarea type="text" name="description" align="right" maxlength="255" cols="67" rows="3"><?php echo $form['description']; ?></textarea>
	</td>
</tr>
<?php
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[3]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"add_info\" value=\"".$form['add_info']."\" maxlength=\"80\" size=\"80\" /></td></tr>\n";
// data inizio produzione
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
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[6]</td><td class=\"FacetDataTD\"><input type=\"number\" name=\"day_of_validity\" min=\"0\" maxlength=\"3\" step=\"any\" size=\"3\" value=\"".$form['day_of_validity']."\"  /></td></tr>\n";
	
		print "</select></td></tr>";
	if ($popup<>1){
		print "<tr><td class=\"FacetFieldCaptionTD\"><input type=\"reset\" name=\"Cancel\" value=\"".$script_transl['cancel']."\">\n";
		print "</td><td class=\"FacetDataTD\" align=\"right\">\n";
		print "<input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">\n";
	} 
	else {
		print "<tr><td>";
	}
if ($toDo == 'update') {
   print '<input type="submit" accesskey="m" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.strtoupper($script_transl['update']).'!"></td></tr><tr></tr>';
} else {
   print '<input type="submit" accesskey="i" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.strtoupper($script_transl['insert']).'!"></td></tr><tr></tr>';
}
print "</td></tr></table>\n";
?>
</form>

<?php

require("../../library/include/footer.php");
?>