<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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
// ANTONIO GERMANI       >>> gestione uso fitofarmaci <<<

require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
$msg = "";

// Antonio Germani questo serve per la ricerca articolo
if (isset($_POST['artico'])){
		$form['cod_art'] = $_POST['cod_art'];
		
		
	}
	
// Antonio Germani questo serve per la ricerca coltura
if (isset($_POST['nome_colt'])){
		$form['nome_colt'] = $_POST['nome_colt'];
		$form['id_colt'] = intval ($form['nome_colt']);
	}

// Antonio Germani questo serve per la ricerca avversità
if (isset($_POST['nome_avv'])){
		$form['nome_avv'] = $_POST['nome_avv'];
		$form['id_avv'] = intval ($form['nome_avv']);
	}

if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

if ((isset($_GET['Update']) and  !isset($_GET['id'])) or isset($_POST['Return'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
}

if ((isset($_POST['Insert'])) or (isset($_POST['Update']))) {   //se non e' il primo accesso
    $form=gaz_dbi_parse_post('camp_uso_fitofarmaci');
    // Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
      
		if ($toDo == 'insert') { // controllo se il codice esiste se e' un inserimento 
          $rs_ctrl = gaz_dbi_get_row($gTables['camp_uso_fitofarmaci'],'id',$form['id']); 
			if ($rs_ctrl){
             $msg .= "6+";
			}
		}
		if (isset ($form['id_colt'])){ // controllo coltivazione vuota
			if (intval ($form['id_colt'])== 0) {
			$msg .= "7+";
			} else {
				$rs_ctrl = gaz_dbi_get_row($gTables['camp_colture'],'id_colt',$form['id_colt']);
					if (empty ($rs_ctrl)){
				$msg .= "7+";
					}
				}
		} else {
			$msg .= "7+";
		}
       if (empty($form['cod_art'])){  // controllo nome articolo vuoto
             $msg .= "8+";
       } else {
			$rs_ctrl = gaz_dbi_get_row($gTables['artico'],'codice',$form['cod_art']);
				if (empty($rs_ctrl)){
				$msg .= "8+";
				}
			}
	   if (isset ($form['id_avv'])){ // controllo avversità vuota
			if (intval ($form['id_avv'])== 0) {
			$msg .= "9+";
			} else {
				$rs_ctrl = gaz_dbi_get_row($gTables['camp_avversita'],'id_avv',$form['id_avv']);
					if (empty($rs_ctrl)){
				$msg .= "9+";
					}
				}
		} else {
			$msg .= "9+";
		}
	   
       if ($msg == "") {// nessun errore        
          
          if ($toDo == 'update') { // e' una modifica
		 
		  $query="UPDATE " . $gTables['camp_uso_fitofarmaci'] . " SET cod_art ='"  .$form['cod_art']. "', id_colt ='" . $form['id_colt'] . "', id_avv =' ".$form['id_avv']. "', dose = '".$form['dose']. "', tempo_sosp = '".$form['tempo_sosp']."' WHERE id ='". $form['id'] ."'";
			gaz_dbi_query ($query) ;
		  
          } else { // e' un'inserimento
				gaz_dbi_table_insert('camp_uso_fitofarmaci',$form);
          }
          header("Location: ".$_POST['ritorno']);
          exit;
       }
  }
} elseif ((!isset($_POST['Update'])) and (isset($_GET['Update']))) { //se e' il primo accesso per update
    $camp_uso_fitofarmaci = gaz_dbi_get_row($gTables['camp_uso_fitofarmaci'],"id",$_GET['id']);
    $form['ritorno'] = $_POST['ritorno'];
    $form['id'] = $camp_uso_fitofarmaci['id'];
    $form['cod_art'] = $camp_uso_fitofarmaci['cod_art'];
	$form['id_colt'] = $camp_uso_fitofarmaci['id_colt'];
	$form['id_avv'] = $camp_uso_fitofarmaci['id_avv'];
	$form['dose'] = $camp_uso_fitofarmaci['dose'];
	$form['tempo_sosp'] = $camp_uso_fitofarmaci['tempo_sosp'];
	$colt = gaz_dbi_get_row($gTables['camp_colture'],"id_colt",$form['id_colt']);
	$form['nome_colt'] = $form['id_colt']." - ".$colt['nome_colt'];
	$avv = gaz_dbi_get_row($gTables['camp_avversita'],"id_avv",$form['id_avv']);
	$form['nome_avv'] = $form['id_avv']." - ".$avv['nome_avv'];
    
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $rs_ultimo_id = gaz_dbi_dyn_query("*", $gTables['camp_uso_fitofarmaci'], 1 ,'id desc',0,1);
    $ultimo_id = gaz_dbi_fetch_array($rs_ultimo_id);
    $form['id'] = $ultimo_id['id']+1;
    $form['cod_art'] = "";
    $form['id_colt'] = "";
	$form['nome_colt'] = "";
	$form['id_avv'] = "";
	$form['nome_avv'] = "";
	$form['dose'] = 0;
	$form['tempo_sosp'] = 0;
}
if (isset($_POST['Insert']) or isset($_POST['Update'])) {   //se non e' il primo accesso

 //ricarico i registri per il form
	$form['id'] = $_POST['id'];
    $form['cod_art'] = $_POST['cod_art'];
    $form['id_colt'] = $_POST['id_colt'];
	$form['nome_colt'] = $_POST['nome_colt'];
	$form['id_avv'] = $_POST['id_avv'];
	$form['nome_avv'] = $_POST['nome_avv'];
	if ($_POST['dose']==0) {
		$form['dose']=0;
	} else {
		$form['dose'] = number_format ($_POST['dose'],$admin_aziend['decimal_price'], '.', '');
	}
	$form['tempo_sosp'] = $_POST['tempo_sosp'];
}

require("../../library/include/header.php");
$script_transl = HeadMain();
if ($toDo == "update") {
   $title = ucwords($script_transl[$toDo].$script_transl[0])." n.".$form['id'];
} else {
   $title = ucwords($script_transl[$toDo].$script_transl[0]);
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
   print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td><td class=\"FacetDataTD\"><input type=\"hidden\" name=\"id\" value=\"".$form['id']."\" />".$form['id']."</td></tr>\n";
} else {
   print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"id\" value=\"".$form['id']."\" maxlength=\"3\" size=\"3\" /></td></tr>\n";
}
?>
<!-- inizio inserisci articolo   -->
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql artico	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['artico'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['codice']."\", ";			
	}
	$stringa=substr($stringa,0,-2);
	echo $stringa;
	?>],
		minLength:2,
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
 <?php
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[2]."</td><td class=\"FacetDataTD\"\n>";
?>
     <input id="autocomplete" type="text" value="<?php echo $form['cod_art']; ?>" name="cod_art" maxlength="50" size="50"/>

	 </td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete -->
	 

<!-- inizio inserisci coltura  -->
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql camp_coltura	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete2").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['camp_colture'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['id_colt']." - ".$row['nome_colt']."\", ";			
	}
	$stringa=substr($stringa,0,-2);
	echo $stringa;
	?>],
		minLength:2,
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
 <?php
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[3]."</td><td class=\"FacetDataTD\"\n>";
?>
     <input id="autocomplete2" type="text" value="<?php echo $form['nome_colt']; ?>" name="nome_colt" maxlength="50" size="50"/>
	 <input type="hidden" value="<?php echo intval ($form['nome_colt']); ?>" name="id_colt"/>
	 </td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete2 -->


<!-- inizio inserisci avversita  -->
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql camp_avversita	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete3").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['camp_avversita'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['id_avv']." - ".$row['nome_avv']."\", ";			
	}
	$stringa=substr($stringa,0,-2);
	echo $stringa;
	?>],
		minLength:2,
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
 <?php
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[4]."</td><td class=\"FacetDataTD\"\n>";
?>
     <input id="autocomplete3" type="text" value="<?php echo $form['nome_avv']; ?>" name="nome_avv" maxlength="50" size="50"/>
	 <input type="hidden" value="<?php echo intval ($form['nome_avv']); ?>" name="id_avv"/>
	 </td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete3 -->
	 
<?php

print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[5]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"dose\" value=\"".number_format ($form['dose'],$admin_aziend['decimal_price'], ',', '')."\" maxlength=\"8\" size=\"10\" />";
$res2 = gaz_dbi_get_row($gTables['artico'], 'codice', $form['cod_art']);
echo $res2['uniacq']."/ha</td></tr>\n";
print "<tr>";
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[10]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"tempo_sosp\" value=\"".$form['tempo_sosp']."\" maxlength=\"2\" size=\"2\" /> gg </td></tr>\n";
if ($toDo !== 'update') {
	print "<td class=\"FacetFieldCaptionTD\"><input type=\"reset\" name=\"Cancel\" value=\"".$script_transl['cancel']."\">\n</td>";
}
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