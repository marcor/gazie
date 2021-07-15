<?php
/* ------------------------------------------------------------------------
  Download articoli da e-commerce a GAzie
  ------------------------------------------------------------------------
  @Author    Antonio Germani 340-5011912
  @Website   http://www.programmisitiweb.lacasettabio.it
  @Copyright Copyright (C) Antonio Germani All Rights Reserved.
  versione 3.1
  ------------------------------------------------------------------------ 
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
// Antonio Germani - Importazione articoli da e-commerce a GAzie con creazione articolo in GAzie se non esiste o aggiornamento se esiste

require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$resserver = gaz_dbi_get_row($gTables['company_config'], "var", "server");
$ftp_host= $resserver['val'];
$resuser = gaz_dbi_get_row($gTables['company_config'], "var", "user");
$accpass = gaz_dbi_get_row($gTables['company_config'], "var", "accpass")['val'];
$respass = gaz_dbi_get_row($gTables['company_config'], "var", "pass");
$ftp_pass= $respass['val'];
$path = gaz_dbi_get_row($gTables['company_config'], 'var', 'path');
$urlinterf = $path['val']."dwnlArticoli-gazie.php";//nome del file interfaccia presente nella root del sito e-commerce. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
// il percorso per raggiungere questo file va impostato in configurazione avanzata azienda alla voce "Website root directory"
$test = gaz_dbi_query("SHOW COLUMNS FROM `" . $gTables['admin'] . "` LIKE 'enterprise_id'");
$exists = (gaz_dbi_num_rows($test)) ? TRUE : FALSE;

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Return'])) { 
        header("Location: " . $_POST['ritorno']);
        exit;
    }

if ($exists) {
    $c_e = 'enterprise_id';
} else {
    $c_e = 'company_id';
}
$admin_aziend = gaz_dbi_get_row($gTables['admin'] . ' LEFT JOIN ' . $gTables['aziend'] . ' ON ' . $gTables['admin'] . '.' . $c_e . '= ' . $gTables['aziend'] . '.codice', "user_name", $_SESSION["user_name"]);
	
if (isset($_POST['conferma'])) { // se confermato
    // scrittura articoli su database di GAzie
	for ($ord=0 ; $ord<=$_POST['num_products']; $ord++){ // ciclo gli articoli e scrivo i database
		unset($form);
		if (isset($_POST['download'.$ord])){ // se selezionato
		
			unset($esiste);
			$_POST['codice'.$ord]=addslashes(substr($_POST['codice'.$ord],0,15)); // Il codice articolo di GAzie è max 15 caratteri
			
			// ricongiungo la categoria dell'e-commerce con quella di GAzie, se esiste
			$category="";
			if (intval($_POST['category_id'.$ord])>0){
				$cat = gaz_dbi_get_row($gTables['catmer'], "ref_ecommerce_id_category", $_POST['category_id'.$ord]);
				if ($cat){// controllo se esiste in GAzie
					$category=$cat['codice'];
				}
			}
			
			$web_public=1;
			// se l'e-commerce ha mandato la priorità di pubblicazione la imposto
			if (intval($_POST['web_public'.$ord])>0){
				$web_public=intval($_POST['web_public'.$ord]);					
			}
			
			if ($_POST['product_type'.$ord]=="parent"){ // se è un parent
				$esiste = gaz_dbi_get_row($gTables['artico_group'], "ref_ecommerce_id_main_product", $_POST['product_id'.$ord]);// controllo se esiste in GAzie
				$tablefile="artico_group";
				$itemref=($esiste)?$esiste['id_artico_group']:'';
			} else {
				$esiste = gaz_dbi_get_row($gTables['artico'], "ref_ecommerce_id_product", $_POST['product_id'.$ord]);// controllo se esiste in GAzie come id e-commerce
				$vat = gaz_dbi_get_row($gTables['aliiva'], "aliquo", $_POST['aliquo'.$ord], " AND tipiva = 'I'"); // prendo il codice IVA
				$tablefile="artico";
				$itemref=$_POST['codice'.$ord];
			}
			
			if ($esiste AND strlen($_POST['imgurl'.$ord])>0 AND $_GET['updimm']=="updimg" AND $_GET['upd']=="updval"){ // se è aggiornamento, se c'è un'immagine, se selezionato e se è attivo l'aggiornamento
				// cancello l'immagine presente nella cartella 
				$imgres = gaz_dbi_get_row($gTables['files'], "table_name_ref", $tablefile, "AND id_ref ='1' AND item_ref = '". $_POST['codice'.$ord]."'");
				if (isset($imgres)){
					gaz_dbi_del_row($gTables['files'], 'id_doc',$imgres['id_doc']);
					unlink (DATA_DIR."files/".$admin_aziend['company_id']."/images/". $imgres['id_doc'] . "." . $imgres['extension']);
				}
			}
			
			// se è inserimento o se è update e c'è un'immagine e se è selezionato
			if ((!$esiste AND strlen($_POST['imgurl'.$ord])>0 AND $_GET['impimm']=="dwlimg" AND $_GET['imp']=="impval") OR ($esiste AND strlen( $_POST['imgurl'.$ord])>0 AND $_GET['updimm']=="updimg" AND $_GET['upd']=="updval")){
				$url = $_POST['imgurl'.$ord];
				$expl= explode ("/", $_POST['imgurl'.$ord]);
				$form['table_name_ref']= $tablefile;
				$form['id_ref']= '1';
				$form['item_ref']= $itemref;
				$ext= explode (".",$expl[count($expl)-1]);
				$form['extension']= $ext[count($ext)-1];
				$form['title']= "Immagine web articolo: ".$_POST['codice'.$ord];
				
				gaz_dbi_table_insert('files',$form);// inserisco i dati dell'immagine nella tabella files
				$form['id_doc']= gaz_dbi_last_id();//recupero l'id assegnato dall'inserimento
				$imgweb=DATA_DIR.'files/'.$admin_aziend['company_id'].'/images/'.$form['id_doc'].'.'.$form['extension'];
				if (intval(file_put_contents($imgweb, file_get_contents($url))) == 0){ // scrivo l'immagine web HQ nella cartella files
					echo "ERRORE nella scrittura in GAzie dell'immagine: ",$url, " <br>Riprovare in quanto potrebbe trattarsi di un Errore momentaneo. Se persiste, controllare che le immagine dell'e-commerce abbiano il permesso per essere lette oppure che sia presente in GAzie la cartella images in data/files/nrAzienda/";die;
				}
				$img = DATA_DIR.'files/tmp/'.$expl[count($expl)-1]; 
				// scrivo l'immagine nella cartella tmp temporanea
				file_put_contents($img, file_get_contents($url));
				// ridimensiono l'immagine per rientrare nei 64k
				$maxDim = 190;				
				list($width, $height, $type, $attr) = getimagesize( $img );
				if ( $width > $maxDim || $height > $maxDim ) {
					$target_filename = $img;
					$ratio = $width/$height;
					if( $ratio > 1) {
						$new_width = $maxDim;
						$new_height = $maxDim/$ratio;
					} else {
							$new_width = $maxDim*$ratio;
							$new_height = $maxDim;
					}
					$src = imagecreatefromstring( file_get_contents( $img ) );
					$dst = imagecreatetruecolor( $new_width, $new_height );
					imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
					imagedestroy( $src );
					imagepng( $dst, $target_filename); // adjust format as needed
					imagedestroy( $dst );
				} 
				//Carico l'immagine ridimensionata
				$immagine= addslashes (file_get_contents($target_filename));
				unlink ($img);// cancello l'immagine temporanea
				if ($_POST['product_type'.$ord]=="parent"){ // se è un parent
					gaz_dbi_query("UPDATE ". $gTables['artico_group'] . " SET image = '".$immagine."' WHERE ref_ecommerce_id_main_product = '".$_POST['product_id'.$ord]."'");	

				}else {
					gaz_dbi_query("UPDATE ". $gTables['artico'] . " SET image = '".$immagine."' WHERE codice = '".$_POST['codice'.$ord]."'");	
				}
			} else {
				$immagine="";
			}
			
			$id_artico_group="";
			if ($_POST['product_parent_id'.$ord] > 0){ // se è una variante
			
				$parent = gaz_dbi_get_row($gTables['artico_group'], "ref_ecommerce_id_main_product", $_POST['product_parent_id'.$ord]);// trovo il padre in GAzie
				
				if (!isset($parent)){
					header("Location: " . "../../modules/shop-synchronize/import_articoli.php?success=2&parent=".$_POST['product_parent_id'.$ord]."&code=".$_POST['codice'.$ord]);
					exit;
				}
				$id_artico_group=$parent['id_artico_group']; // imposto il riferimento al padre
				if (strlen($_POST['descri'.$ord])<2){ // se non c'è la descrizione della variante 
					$_POST['descri'.$ord]=$parent['descri']."-".$_POST['characteristic'.$ord];// ci metto quella del padre accodandoci la variante
				}
			}
			if ($_POST['product_type'.$ord]=="variant"){ // se una variante
				// creo un json array per la variante
				$arrayvar= array("var_id" => floatval($_POST['characteristic_id'.$ord]), "var_name" => $_POST['characteristic'.$ord]);
				$arrayvar = json_encode ($arrayvar);
			}
			
			
			
			if ($esiste AND $_GET['upd']=="updval"){ // se esiste l'articolo ed è attivo l'update, aggiorno l'articolo
				
					// Body text
					if (strlen($_POST['body_text'.$ord])>0 AND $_GET['upddes']=="upddes"){ // se c'è una descrizione estesa body_text ed è selezionata
						if ($_POST['product_type'.$ord]=="parent"){ // se è un parent					
							gaz_dbi_query("UPDATE ". $gTables['artico_group'] . " SET large_descri = '". htmlspecialchars_decode (addslashes($_POST['body_text'.$ord])) ."' WHERE ref_ecommerce_id_main_product = '".$_POST['product_id'.$ord]."'");
						} else {					
							$esist = gaz_dbi_get_row($gTables['body_text'], "table_name_ref", "artico_".$_POST['codice'.$ord]);
							$form['body_text'] = htmlspecialchars_decode ($_POST['body_text'.$ord]);
							$form['table_name_ref']="artico_".$_POST['codice'.$ord];
							$form['lang_id']=1;
							if ($esist) { // se c'è già	
								$where = array("0" => "table_name_ref", "1" => "artico_".$_POST['codice'.$ord]); 
								gaz_dbi_table_update("body_text",$where, $form); // la aggiorno nel DB
							} else { // altrimenti 
								gaz_dbi_table_insert('body_text', $form); // la scrivo nel DB
							}
						}
					}
					
					if (intval($category)>0){
						$updcat="catmer = '". $category ."',";
					} else {
						$updcat="";
					}
					
					if ($_GET['updpre']=="updpre" AND $_GET['updname']=="updnam") { // se devo aggiornare prezzo e nome
						
						if ($_POST['product_type'.$ord]=="parent"){ // se è un parent					
							gaz_dbi_query("UPDATE ". $gTables['artico_group'] . " SET descri = '". htmlspecialchars_decode (addslashes($_POST['descri'.$ord])) ."', web_public = '".$web_public."' WHERE ref_ecommerce_id_main_product = '".$_POST['product_id'.$ord]."'");
						} else {
							gaz_dbi_query("UPDATE ". $gTables['artico'] . " SET ecomm_option_attribute = '".$arrayvar."', ". $updcat ." peso_specifico = '".$_POST['weight'.$ord]."', descri = '".addslashes($_POST['descri'.$ord])."', web_price = '".addslashes($_POST['web_price'.$ord])."' , id_artico_group ='". $id_artico_group ."', web_public = '".$web_public."' WHERE ref_ecommerce_id_product = '". $_POST['product_id'.$ord] ."'");
						}
					} elseif ($_GET['updpre']!=="updpre" AND $_GET['updname']=="updnam") { // altrimenti non aggiorno il prezzo ma aggiorno il nome
						if ($_POST['product_type'.$ord]=="parent"){ // se è un parent					
							gaz_dbi_query("UPDATE ". $gTables['artico_group'] . " SET descri = '". htmlspecialchars_decode (addslashes($_POST['descri'.$ord])) ."', web_public = '".$web_public."' WHERE ref_ecommerce_id_main_product = '".$_POST['product_id'.$ord]."'");
						} else {
							gaz_dbi_query("UPDATE ". $gTables['artico'] . " SET ecomm_option_attribute = '".$arrayvar."', ". $updcat ." peso_specifico = '".$_POST['weight'.$ord]."', descri = '".addslashes($_POST['descri'.$ord])."', id_artico_group ='". $id_artico_group ."', web_public = '".$web_public."' WHERE ref_ecommerce_id_product = '". $_POST['product_id'.$ord] ."'");
						}
					} elseif ($_GET['updpre']=="updpre" AND $_GET['updname']!=="updnam" AND $_POST['product_type'.$ord]!=="parent") { // altrimenti aggiorno il prezzo ma non aggiorno il nome
						gaz_dbi_query("UPDATE ". $gTables['artico'] . " SET ecomm_option_attribute = '".$arrayvar."', ". $updcat ." peso_specifico = '".$_POST['weight'.$ord]."', web_price = '".addslashes($_POST['web_price'.$ord])."', id_artico_group ='". $id_artico_group ."', web_public = '".$web_public."' WHERE ref_ecommerce_id_product = '". $_POST['product_id'.$ord] ."'");
					} else {// oppure aggiorno i dati default ma no nome e no prezzo
						if ($_POST['product_type'.$ord]=="parent"){ // se è un parent					
							gaz_dbi_query("UPDATE ". $gTables['artico_group'] . " SET descri = '". htmlspecialchars_decode (addslashes($_POST['descri'.$ord])) ."', web_public = '".$web_public."' WHERE ref_ecommerce_id_main_product = '".$_POST['product_id'.$ord]."'");
						} else {
							gaz_dbi_query("UPDATE ". $gTables['artico'] . " SET ecomm_option_attribute = '".$arrayvar."', ". $updcat ." peso_specifico = '".$_POST['weight'.$ord]."', id_artico_group ='". $id_artico_group ."', web_public = '".$web_public."' WHERE ref_ecommerce_id_product = '". $_POST['product_id'.$ord] ."'");
						}
					}
				
			} elseif (!$esiste AND $_GET['imp']=="impval"){ // altrimenti, se è attivo l'inserimento, inserisco un nuovo articolo
			
				// prima di inserire il nuovo controllo se l'e-commerce ha mandato il codice articolo e se è già in uso in GAzie	
				
				if (strlen($_POST['codice'.$ord])<1){// se l'e-commerce non ha inviato un codice me lo creo
					$_POST['codice'.$ord] = substr($_POST['descri'.$ord],0,10)."-".substr($_POST['product_id'.$ord],-4);
				}
				
				unset($usato);
				$usato = gaz_dbi_get_row($gTables['artico'], "codice", $_POST['codice'.$ord]);// controllo se il codice è già stato usato in GAzie	
				if ($usato){ // se il codice è già in uso lo modifico
					$_POST['codice'.$ord]=substr($_POST['codice'.$ord],0,10)."-".substr($_POST['product_id'.$ord],-4);
				}								
				
				if ($_POST['product_type'.$ord]=="parent"){// se è un parent ***<<<<<
					if (strlen($_POST['body_text'.$ord])>0 AND $_GET['impdes']!=="dwldes"){ // se non è stata selezionata la descrizione estesa
						$_POST['body_text'.$ord]=""; // la annullo
					}
					gaz_dbi_query("INSERT INTO " . $gTables['artico_group'] . "(descri,large_descri,image,web_url,ref_ecommerce_id_main_product,web_public,depli_public,adminid) VALUES ('" . addslashes($_POST['descri'.$ord]) . "', '" . htmlspecialchars_decode (addslashes($_POST['body_text'.$ord])). "', '" . $immagine . "', '". $_POST['web_url'.$ord] . "', '". $_POST['product_id'.$ord] . "', '".$web_public."', '1', '". $admin_aziend['adminid'] ."')");
				} else {
					
					if ($_GET['imppre']=="dwlprice") { // se devo inserire anche il prezzo web
						gaz_dbi_query("INSERT INTO " . $gTables['artico'] . "(web_url,ecomm_option_attribute,catmer,barcode,peso_specifico,codice,ref_ecommerce_id_product,descri,web_mu,web_price,unimis,image,web_public,depli_public,aliiva,id_artico_group) VALUES ('". $_POST['web_url'.$ord] ."', '". $arrayvar ."', '" . $category . "', '" . $_POST['barcode'.$ord] . "', '" . $_POST['weight'.$ord] . "', '" . $_POST['codice'.$ord] . "', '" . $_POST['product_id'.$ord]. "', '" . addslashes($_POST['descri'.$ord]). "', '".$_POST['unimis'.$ord] . "', '". addslashes($_POST['web_price'.$ord]). "', '".$_POST['unimis'.$ord]."', '".$immagine."', '".$web_public."', '1', '".$vat['codice']."', '". $id_artico_group ."')");
					} else { // altrimenti lo inserisco senza prezzo web
						gaz_dbi_query("INSERT INTO " . $gTables['artico'] . "(web_url,ecomm_option_attribute,catmer,barcode,peso_specifico,codice,ref_ecommerce_id_product,descri,web_mu,unimis,image,web_public,depli_public,aliiva,id_artico_group) VALUES ('". $_POST['web_url'.$ord] ."', '". $arrayvar ."', '" . $category . "', '" . $_POST['barcode'.$ord] . "', '" . $_POST['weight'.$ord] . "', '" . $_POST['codice'.$ord] . "', '" . $_POST['product_id'.$ord]. "', '" . addslashes($_POST['descri'.$ord]). "', '".$_POST['unimis'.$ord] . "', '".$_POST['unimis'.$ord]."', '".$immagine."', '".$web_public."', '1', '".$vat['codice']."', '". $id_artico_group ."')");
					}
					if (strlen($_POST['body_text'.$ord])>0 AND $_GET['impdes']=="dwldes"){ // se c'è una descrizione estesa - body_text ed è selezionata
						$form['body_text'] = htmlspecialchars_decode ($_POST['body_text'.$ord]);
						$form['table_name_ref']="artico_".$_POST['codice'.$ord];
						$form['lang_id']=1;
						gaz_dbi_table_insert('body_text', $form); // la scrivo nel DB
					}
				}
			}			
		}		
	}
	
	header("Location: " . "../../modules/shop-synchronize/import_articoli.php?success=1");
    exit;
} else {
	require('../../library/include/header.php');
	$script_transl = HeadMain();
}

$access=base64_encode($accpass);
if (!isset($_GET['success'])){
	// avvio il file di interfaccia presente nel sito web remoto
	$headers = @get_headers($urlinterf.'?access='.$access);	
	
	if ( intval(substr($headers[0], 9, 3))==200){ // controllo se ho avuto accesso al file interfaccia
		$xml=simplexml_load_file($urlinterf.'?access='.$access) ; // carico il file xml appena creato
		if (!$xml){ // se non è stato creato o non ho accesso
			?>
			<script>
			alert("<?php echo "Errore! Il file xml non è stato creato oppure non è possibile accedervi"; ?>");
			location.replace("<?php echo $_POST['ritorno']; ?>");
			</script>
			<?php
		}		
		// Apro il form per la selezione degli articoli
		?>
		<script>
    function selectCheckbox() {
        var inputs = document.getElementsByTagName('input');
        var checkboxes = [];
        for (var i = 0; i < inputs.length; i++){
            var input = inputs[i];
            if (input.getAttribute('type') == 'checkbox'){
                checkboxes.push(input);
            }
        } 
        return checkboxes;
    }    
    function check(checks){
      var checkboxes = selectCheckbox();
      for(var i=0; i < checkboxes.length; i++){
        checkboxes[i].checked = checks.checked;
      }
    }    
    function submit() {
        var checks = document.getElementsByClassName('check');
        var str = '';
        for ( i = 0; i < checks.length; i++) {
            if ( checks[i].checked === true ) {
                str += checks[i].value + " ";
            }
        }
        alert(str);
    }
</script>
		<form method="POST" name="download" enctype="multipart/form-data">
			<input type="hidden" name="ritorno" value="<?php echo $_POST['ritorno'];?>" >
			<input type="hidden" name="download" value="download" >
			<div class="container-fluid" style="max-width:90%;">
				<div class="row bg-primary" >
					<div class="col-sm-12" align="center"><h4>Importazione di articoli dall'e-commerce in GAzie</h4>
						<p align="justify">Gli articoli selezionati verranno aggiornati o, se inesistenti, verranno creati. </p>
					</div>
				</div>
				<div class="row bg-info">
					<div class="col-sm-4">
						<input type="submit" name="Return"  value="Indietro">
					</div>
					<div class="col-sm-4" style="background-color:lightgreen;">
						<?php echo "Connesso a " . $ftp_host;?>
					</div>
					<div class="col-sm-4" align="right">
						<!-- Trigger the modal with a button -->
						<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#downloader">Carica prodotti in GAzie</button>
						<!-- Modal content-->
						<div id="downloader" class="modal fade" role="dialog">    
							<div class="modal-dialog modal-content">
								<div class="modal-header" align="left">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">ATTENZIONE !</h4>
								</div>
								<div class="modal-body">
									<p>Stai per caricare/aggiornare definitivamente i prodotti in GAzie. <br>Questa operazione &egrave irreversibile. <br>Sei sicuro di volerlo fare?</p>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annulla</button>
									<input type="submit" class="btn btn-danger pull-right" name="conferma"  value="Carica prodotti in GAzie">
								</div>
							</div>
						</div>
					</div>						
				</div>
				<div class="row bg-info" style="border-bottom: 1px solid;">
					<div class="col-sm-2">
						<h4>Progressivo</h4>
					</div>
					<div class="col-sm-2">
						<h4>Codice</h4>
					</div>
					<div class="col-sm-5">	
						<h4>Nome</h4>
					</div>
					<div class="col-sm-2" align="right">	
						<h4>Seleziona</h4>
					</div>
					<div class="col-sm-1">	
						TUTTI <input type="checkbox" onClick="check(this)">
					</div>
				</div>
					
					<?php
					$n=0;
					foreach($xml->Documents->children() as $product) { 
						$nr=0;
						$rowclass="bg-success";
						if ($product->Type == "parent"){
							$rowclass="bg-warning";
						}
						?>
						<div class="row <?php echo $rowclass ?>" style="border-bottom: 1px solid;">
							<div class="col-sm-2">
								<?php echo $n;?>
							</div>
							<div class="col-sm-3">
								<?php echo $product->Code;
								echo '<input type="hidden" name="codice'. $n .'" value="'. $product->Code . '">';
								?>
							</div>
							<div class="col-sm-5">
								<?php echo $product->Name;
								echo '<input type="hidden" name="descri'. $n .'" value="'. $product->Name . '">';
								?>
							</div>
							<div class="col-sm-1">
								<?php echo '<input type="hidden" name="body_text'. $n .'" value="'. preg_replace('/[\x00-\x1f]/','',htmlspecialchars($product->Description)) . '">';
								echo '<input type="hidden" name="quanti'. $n .'" value="'. $product->AvailableQty .'">';
								echo '<input type="hidden" name="web_price'. $n .'" value="'. $product->Price .'">';
								echo '<input type="hidden" name="unimis'. $n .'" value="'. $product->Unimis .'">';
								echo '<input type="hidden" name="aliquo'. $n .'" value="'. $product->VAT .'">';
								echo '<input type="hidden" name="barcode'. $n .'" value="'. $product->BarCode .'">';
								echo '<input type="hidden" name="imgurl'. $n .'" value="'. $product->ProductImgUrl .'">';
								echo '<input type="hidden" name="product_id'. $n .'" value="'. $product->Id .'">';
								echo '<input type="hidden" name="web_url'. $n .'" value="'. $product->WebUrl .'">';
								echo '<input type="hidden" name="product_parent_id'. $n .'" value="'. $product->ParentId .'">';// se ci sono varianti questo è l'id del padre
								echo '<input type="hidden" name="product_type'. $n .'" value="'. $product->Type .'">';// se è padre questo è 'parent' altrimenti  null
								echo '<input type="hidden" name="weight'. $n .'" value="'. $product->Weight .'">';
								echo '<input type="hidden" name="category_id'. $n .'" value="'. $product->ProductCategoryId .'">';
								echo '<input type="hidden" name="category'. $n .'" value="'. $product->ProductCategory .'">';
								echo '<input type="hidden" name="characteristic_id'. $n .'" value="'. $product->CharacteristicId .'">';
								echo '<input type="hidden" name="characteristic'. $n .'" value="'. $product->Characteristic .'">';
								echo '<input type="hidden" name="web_public'. $n .'" value="'. $product->WebPublish .'">';
								?>
							</div>
							<div class="col-sm-1" align="right">
								<input type="checkbox" name="download<?php echo $n; ?>" value="download">
								<input type="hidden" name="num_products" value="<?php echo $n; ?>">
							</div>
						</div>
						<?php					
						$n++;
					} 
					?>					
					<div class="row bg-info">
						<div class="col-sm-4">
							<input type="submit" name="Return"  value="Indietro">
						</div>
						<div class="col-sm-4" style="background-color:lightgreen;">
							<?php echo "Connesso a " . $ftp_host;?>
						</div>
						<div class="col-sm-4" align="right">
							<!-- Trigger the modal with a button -->
							<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#downloader">Carica prodotti in GAzie</button>
							
						</div>						
					</div>				
			</div>
		</form>
		<?php
	
	} else { // ERRORE FILE INTERFACCIA > ESCO
		
		?>
		<script>
		alert("<?php echo " Errore di connessione al file di interfaccia web = ",intval(substr($headers[0], 9, 3)),"<br> Controllare codice errore o riprovare fra qualche minuto!"; ?>");
		location.replace("<?php echo $_POST['ritorno']; ?>");
		</script>
		<?php
		exit;
	}
} else {
	if ($_GET['success']==1){
	?>
	<div class="alert alert-success alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>Fatto!</strong> Operazione conclusa con successo.
	</div>
	<?php
	} else {
		?>
	<div class="alert alert-danger alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>Errore, importazione interrotta!</strong> Si è tentato di importare una variante senza aver prima importato/creato un articolo padre in artico_group.
		<p>ParentID mancante: <?php echo $_GET['parent']; ?> Codice variante: <?php echo $_GET['code']; ?></p>
	</div>
	<?php
	}
}
require("../../library/include/footer.php");
?>