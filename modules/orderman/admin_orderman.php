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

    // Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
       
       
       if (empty($form['description'])){  //descrizione vuota
             $msg .= "4+";
       } 
       if ($msg == "") {// nessun errore
          
          if ($toDo == 'update') { // e' una modifica
            gaz_dbi_table_update('orderman',$form["id"],$form);
          } else { // e' un'inserimento
            gaz_dbi_table_insert('orderman',$form);
          }
          header("Location: ".$_POST['ritorno']);
          exit;
       }
  }


} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
echo "PASSATO primo accesso UPDATE";die;
/*DA FARE <<<<<<<<<<<*/

} else { //se e' il primo accesso per INSERT
    
	$form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['order_type']='';
    $form['description']='';
    $form['id_tesbro']='';
	$form['add_info']='';
	
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
print "</select></td></tr><tr><td class=\"FacetFieldCaptionTD\"><input type=\"reset\" name=\"Cancel\" value=\"".$script_transl['cancel']."\">\n";
print "</td><td class=\"FacetDataTD\" align=\"right\">\n";
print "<input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">\n";
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