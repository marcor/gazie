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

	if (isset($_POST['Cancel'])){
		$_POST['cod_art'] = "";
		$_POST['codart'] = "";
		$_POST['nomefito'] = "";
		$_POST['nome_fito'] = "";
		$_POST['id_colt'] = 0;
		$_POST['nome_colt'] = "";
		$_POST['id_avv'] = 0;
		$_POST['nome_avv'] = "";
		$_POST['dose'] = 0;
		$_POST['tempo_sosp'] = 0;
	}
	$_POST['id_colt'] = intval ($_POST['nome_colt']);
	$_POST['id_avv'] = intval ($_POST['nome_avv']);
	$_POST['cod_art'] = $_POST['codart'];
    $form=gaz_dbi_parse_post('camp_uso_fitofarmaci');
	
	//ricarico i registri per il form	
	$form['nome_colt'] = $_POST['nome_colt'];	
	$form['nome_avv'] = $_POST['nome_avv'];
	$form['nome_fito'] = $_POST['nomefito'];
	
	if ($form['nome_fito']){
		$form['id_reg'] = gaz_dbi_get_row($gTables['camp_fitofarmaci'], "PRODOTTO", $form['nome_fito'])['NUMERO_REGISTRAZIONE'];
		if (intval($form['id_reg'])>0){
			$form['cod_art'] = gaz_dbi_get_row($gTables['artico'], "id_reg", $form['id_reg'])['codice'];
		} else {
			$form['nome_fito']="";
		}
	} elseif ($form['cod_art']){
		$form['id_reg'] = gaz_dbi_get_row($gTables['artico'], "codice", $form['cod_art'])['id_reg'];
		if (intval($form['id_reg'])>0){
			$form['nome_fito'] = gaz_dbi_get_row($gTables['camp_fitofarmaci'], "NUMERO_REGISTRAZIONE", $form['id_reg'])['PRODOTTO'];
		} else {
			$form['cod_art']=$_POST['codart'];
		}
	} 
	if (($form['cod_art'] AND $form['nome_fito']) OR($form['cod_art']=="" AND $form['nome_fito']=="" )) {
		
	} else {
		$warning="NoGazie";
	}
	
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
				$msg .= "11+";
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
		if ($form['dose']==0){
			$msg .= "12+";
		}		
	   
		if ($msg == "") {// nessun errore        
          
			if ($toDo == 'update') { // e' una modifica

			$query="UPDATE " . $gTables['camp_uso_fitofarmaci'] . " SET cod_art ='"  .$form['cod_art']. "', id_colt ='" . $form['id_colt'] . "', id_avv =' ".$form['id_avv']. "', dose = '".$form['dose']. "', tempo_sosp = '".$form['tempo_sosp']."' WHERE id ='". $form['id'] ."'";
			gaz_dbi_query ($query) ;
			header("Location: ".$_POST['ritorno']);
			exit;

			} else { // e' un'inserimento
				gaz_dbi_table_insert('camp_uso_fitofarmaci',$form);
				$form['id_colt'] = 0;
				$form['nome_colt'] = "";
				$form['id_avv'] = 0;
				$form['nome_avv'] = "";
				$form['dose'] = 0;
				$form['tempo_sosp'] = 0;
				$form['id']++;
				$warning="inserito";
			}
			//header("Location: ".$_POST['ritorno']);
			//exit;
			
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
	$form['id_reg'] = gaz_dbi_get_row($gTables['artico'], "codice", $form['cod_art'])['id_reg'];
	
	$form['nome_fito'] = gaz_dbi_get_row($gTables['camp_fitofarmaci'], "NUMERO_REGISTRAZIONE", $form['id_reg'])['PRODOTTO'];
	
    
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
	// controllo se la tabella DB fitofarmaci è popolata
	$query="SELECT * FROM ".$gTables['camp_fitofarmaci']. " LIMIT 1";
	$checkdbfito = gaz_dbi_query($query);
	if ($checkdbfito -> num_rows ==0) {
		$warning="NoFito";
	}
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $rs_ultimo_id = gaz_dbi_dyn_query("*", $gTables['camp_uso_fitofarmaci'], 1 ,'id desc',0,1);
    $ultimo_id = gaz_dbi_fetch_array($rs_ultimo_id);
    $form['id'] = $ultimo_id['id']+1;
    $form['cod_art'] = "";
    $form['id_colt'] = 0;
	$form['nome_colt'] = "";
	$form['id_avv'] = 0;
	$form['nome_avv'] = "";
	$form['dose'] = 0;
	$form['tempo_sosp'] = 0;
}

require("../../library/include/header.php");
$script_transl = HeadMain();
if ($toDo == "update") {
   $title = ucwords($script_transl[$toDo].$script_transl[0])." n.".$form['id'];
} else {
   $title = ucwords($script_transl[$toDo].$script_transl[0]);
}
print "<form method=\"POST\" enctype=\"multipart/form-data\" id=\"add-product\">\n";
if ($warning == "NoFito"){ // se non c'è, bisogna creare il data base fitofarmaci
	?>
	<div class="alert alert-warning alert-dismissible" style="max-width: 70%; margin-left: 15%;">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>Warning!</strong> Il database Fitofarmaci non esiste. E' necessario crearlo <a  href="javascript:Popup('../../modules/camp/update_fitofarmaci.php')"> Crea database Fitofarmaci <i class="glyphicon glyphicon-import" style="color:green" ></i></a>
	</div>
	<?php
}
if ($warning == "NoGazie"){ // Articolo non presente in GAzie
	?>
	<div class="alert alert-warning alert-dismissible" style="max-width: 70%; margin-left: 15%;">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>Warning!</strong> Questo fitofarmaco non esiste in GAzie. Per utilizzarlo è necessario inserirlo <a  href="javascript:Popup('../../modules/camp/camp_admin_artico.php?Insert')"> Inserisci Fitofarmaco <i class="glyphicon glyphicon-import" style="color:green" ></i></a>
	</div>
	<?php
}
if ($warning == "inserito"){ // Dose fitofarmaco correttamente inserita
	?>
	<div class="autodism alert alert-success alert-dismissible" style="max-width: 70%; margin-left: 15%;">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>OK!</strong> Inserimento avvenuto correttamente 
	</div>
	<?php
}
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
   print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"id\" value=\"".$form['id']."\" maxlength=\"3\"  /></td></tr>\n";
}
?>
<!-- inizio inserisci articolo   -->
	
  <script>
<!-- Antonio Germani - chiude automaticamente tutti gli alert autodism -->
$(document).ready(function () { 
	window.setTimeout(function() {
		$(".autodism").fadeTo(1000, 0).slideUp(500, function(){
			$(this).remove(); 
		});
	}, 3000); 
});

<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql fitofarmaci	-->
	$(document).ready(function(){
	//Autocomplete search using PHP, MySQLi, Ajax and jQuery
	//generate suggestion on keyup
		$('#nomefito').keyup(function(e){
			e.preventDefault();
			var form = $('#add-product').serialize();
			$.ajax({
				type: 'GET',
				url: 'do_search.php',
				data: form,
				dataType: 'json',
				success: function(response){
					if(response.error){
						$('#product_search').hide();
					}
					else{
						$('#product_search').show().html(response.data);
					}
				}
			});
		});
		//fill the input
		$(document).on('click', '.dropdown-item', function(e){
			e.preventDefault();
			$('#product_search').hide();
			var fullname = $(this).data('fullname');
			$('#nomefito').val(fullname);
			$('#add-product').submit();
		});
	});
<!-- fine autocompletamento -->
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql artico	-->
	$(document).ready(function(){
	//Autocomplete search using PHP, MySQLi, Ajax and jQuery
	//generate suggestion on keyup
		$('#codart').keyup(function(e){
			e.preventDefault();
			var form = $('#add-product').serialize();
			$.ajax({
				type: 'POST',
				url: 'do_search.php',
				data: form,
				dataType: 'json',
				success: function(response){
					if(response.error){
						$('#codart_search').hide();
					}
					else{
						$('#codart_search').show().html(response.data);
					}
				}
			});
		});
		//fill the input
		$(document).on('click', '.dropdown-item2', function(e){
			e.preventDefault();
			$('#codart_search').hide();
			var fullname = $(this).data('fullname');
			$('#codart').val(fullname);
			$('#add-product').submit();
		});
	});
<!-- fine autocompletamento -->
<!-- script per popup -->	
	var stile = "top=10, left=10, width=600, height=800 status=no, menubar=no, toolbar=no scrollbar=no";
	   function Popup(apri) {
	      window.open(apri, "", stile);
	   }
  </script>

<tr>
	<td class="FacetFieldCaptionTD"> 
		<?php 
		echo $script_transl[2];
		?>
	</td>
	<td class="FacetDataTD">	 
		<div class="col-md-12">				
			<input class="col-md-12" type="text" id="nomefito" name="nomefito" value="<?php echo $form['nome_fito']; ?>" placeholder="Ricerca nome fitofarmaco" autocomplete="off" tabindex="1">
			<ul class="dropdown-menu" style="left: 20%; padding: 0px;" id="product_search"></ul>									
		</div>	
	</td>
</tr>
<tr>
	<td class="FacetFieldCaptionTD"> 
		<?php 
		echo "Codice articolo";
		?>
	</td>
	<td class="FacetDataTD">	 
		<div class="col-md-12">				
			<input class="col-md-12" type="text" id="codart" name="codart" value="<?php echo $form['cod_art']; ?>" placeholder="Ricerca codice articolo" autocomplete="off" tabindex="1">
			<ul class="dropdown-menu" style="left: 20%; padding: 0px;" id="codart_search"></ul>									
		</div>	
	</td>
</tr>


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
        //$(event.target.form).submit();
    }
	});
	});
  </script>
 <!-- fine autocompletamento -->
<?php
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[3]."</td><td class=\"FacetDataTD\"\n>";
?>
     <input id="autocomplete2" type="text" value="<?php echo $form['nome_colt']; ?>" name="nome_colt" maxlength="50"/>
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
        //$(event.target.form).submit();
    }
	});
	});
  </script>
 <!-- fine autocompletamento -->
 <?php
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[4]."</td><td class=\"FacetDataTD\"\n>";
?>
     <input id="autocomplete3" type="text" value="<?php echo $form['nome_avv']; ?>" name="nome_avv" maxlength="50"/>
	 <input type="hidden" value="<?php echo intval ($form['nome_avv']); ?>" name="id_avv"/>
	 </td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete3 -->
	 
<?php

print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[5]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"dose\" value=\"".number_format ($form['dose'],$admin_aziend['decimal_price'], ',', '')."\" maxlength=\"8\"  />";
$res2 = gaz_dbi_get_row($gTables['artico'], 'codice', $form['cod_art']);
echo $res2['uniacq']."/ha</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[10]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"tempo_sosp\" value=\"".$form['tempo_sosp']."\" maxlength=\"2\"  /> gg </td></tr>\n";
print "<tr>";
if ($toDo !== 'update') {
	print "<td class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"Cancel\" value=\"".$script_transl['cancel']."\">\n</td>";
}
print "<td class=\"FacetDataTD\" align=\"right\">\n";
print "<input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">\n";
if ($toDo == 'update') {
   print '<input type="submit" accesskey="m" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.ucfirst($script_transl['update']).'!"></td></tr><tr></tr>';
} else {
   print '<input type="submit" accesskey="i" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.ucfirst($script_transl['insert']).'!"></td></tr><tr></tr>';
}
print "</table>\n";
?>
</form>

<?php    
require("../../library/include/footer.php");
?>