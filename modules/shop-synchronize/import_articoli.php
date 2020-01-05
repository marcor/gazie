<?php
/* ------------------------------------------------------------------------
  INTERFACCIA Download articoli da online-shop a GAzie
  ------------------------------------------------------------------------
  @Author    Antonio Germani 340-5011912
  @Website   http://www.lacasettabio.it
  @Copyright Copyright (C) 2018 - 2019 Antonio Germani All Rights Reserved.
  versione 1.0
  ------------------------------------------------------------------------ */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$resserver = gaz_dbi_get_row($gTables['company_config'], "var", "server");
$ftp_host= $resserver['val'];
$resuser = gaz_dbi_get_row($gTables['company_config'], "var", "user");
$ftp_user = $resuser['val'];
$respass = gaz_dbi_get_row($gTables['company_config'], "var", "pass");
$ftp_pass= $respass['val'];
$path = gaz_dbi_get_row($gTables['company_config'], 'var', 'path');
$urlinterf = $path['val']."dwnlArticoli-gazie.php";//nome del file interfaccia presente nella root del sito Joomla. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
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
		if (isset($_POST['download'.$ord])){ // se selezionato
			$esiste = gaz_dbi_get_row($gTables['artico'], "codice", $_POST['codice'.$ord]);
			$vat = gaz_dbi_get_row($gTables['aliiva'], "aliquo", $_POST['aliquo'.$ord]); // prendo il codice IVA
			
			if (strlen($esiste AND $_POST['imgurl'.$ord])>0 AND $_GET['updimm']=="updimg"){ // se è aggiornamento, se c'è un'immagine e se selezionato
				// cancello l'immagine presente nella cartella 
				$imgres = gaz_dbi_get_row($gTables['files'], "table_name_ref", "artico", "AND id_ref ='1' AND item_ref = '". $_POST['codice'.$ord]."'");
				gaz_dbi_del_row($gTables['files'], 'id_doc',$imgres['id_doc']);
				unlink ("../../data/files/".$admin_aziend['company_id']."/images/". $imgres['id_doc'] . "." . $imgres['extension']);
			}
			
			if ((strlen(!$esiste AND $_POST['imgurl'.$ord])>0 AND $_GET['impimm']=="dwlimg") OR (strlen($esiste AND $_POST['imgurl'.$ord])>0 AND $_GET['updimm']=="updimg")){ // se è inserimento, se c'è un'immagine e se selezionato
				$url = $_POST['imgurl'.$ord];
				$expl= explode ("/", $_POST['imgurl'.$ord]);
				$form['table_name_ref']= 'artico';
				$form['id_ref']= '1';
				$form['item_ref']= $_POST['codice'.$ord];
				$ext= explode (".",$expl[count($expl)-1]);
				$form['extension']= $ext[count($ext)-1];
				$form['title']= "Immagine web articolo: ".$_POST['codice'.$ord];
				gaz_dbi_table_insert('files',$form);// inserisco i dati dell'immagine nella tabella files
				$form['id_doc']= gaz_dbi_last_id();//recupero l'id assegnato dall'inserimento
				$imgweb='../../data/files/'.$admin_aziend['company_id'].'/images/'.$form['id_doc'].'.'.$form['extension'];
				file_put_contents($imgweb, file_get_contents($url)); // scrivo l'immagine web HQ nella cartella files
				
				$img = '../../data/files/tmp/'.$expl[count($expl)-1]; 
				// scrivo l'immagine nella cartella temporanea
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
				unlink ($img);// cancello l'immagine della cartella temporanea
			} else {
				$immagine="";
			}			
			
			if ($esiste){ // se esiste l'articolo lo aggiorno
				if (strlen($_POST['body_text'.$ord])>0 AND $_GET['upddes']=="upddes"){ // se c'è una descrizione estesa - body_text ed è selezionata
					$esist = gaz_dbi_get_row($gTables['body_text'], "table_name_ref", "artico_".$_POST['codice'.$ord]);
					$form['body_text']=$_POST['body_text'.$ord];
					$form['table_name_ref']="artico_".$_POST['codice'.$ord];
					$form['lang_id']=1;
					if ($esist) { // se c'è già	
						$where = array("0" => "table_name_ref", "1" => "artico_".$_POST['codice'.$ord]);
						gaz_dbi_table_update("body_text",$where, $form); // la aggiorno nel DB
					} else { // altrimenti 
						gaz_dbi_table_insert('body_text', $form); // la scrivo nel DB
					}
				}
				if ($_GET['updpre']=="updpre") { // se devo aggiornare anche il prezzo
					gaz_dbi_query("UPDATE ". $gTables['artico'] . " SET descri = '".addslashes($_POST['descri'.$ord])."', web_price = '".addslashes($_POST['web_price'.$ord])."' , image = '".$immagine."' WHERE codice = '".addslashes($_POST['codice'.$ord])."'");
				} else { // altrimenti non aggiorno il prezzo
					gaz_dbi_query("UPDATE ". $gTables['artico'] . " SET descri = '".addslashes($_POST['descri'.$ord])."' , image = '".$immagine."' WHERE codice = '".addslashes($_POST['codice'.$ord])."'");
				}
			} else { // altrimenti inserisco nuovo articolo
				if ($_GET['imppre']=="dwlprice") { // se devo inserire anche il prezzo web
					gaz_dbi_query("INSERT INTO " . $gTables['artico'] . "(codice,descri,web_mu,web_price,unimis,image,web_public,depli_public,aliiva) VALUES ('" . addslashes($_POST['codice'.$ord]) . "', '" . addslashes($_POST['descri'.$ord]). "', '".$_POST['unimis'.$ord] . "', '". addslashes($_POST['web_price'.$ord]). "', '".$_POST['unimis'.$ord]."', '".$immagine."', '1', '1', '".$vat['codice']."')");
				} else { // altrimenti lo inserisco senza prezzo web
					gaz_dbi_query("INSERT INTO " . $gTables['artico'] . "(codice,descri,web_mu,unimis,image,web_public,depli_public,aliiva) VALUES ('" . addslashes($_POST['codice'.$ord]) . "', '" . addslashes($_POST['descri'.$ord]). "', '".$_POST['unimis'.$ord] . "', '".$_POST['unimis'.$ord]."', '".$immagine."', '1', '1', '".$vat['codice']."')");
				}
				if (strlen($_POST['body_text'.$ord])>0 AND $_GET['impdes']=="dwldes"){ // se c'è una descrizione estesa - body_text ed è selezionata
					$form['body_text']=$_POST['body_text'.$ord];
					$form['table_name_ref']="artico_".$_POST['codice'.$ord];
					$form['lang_id']=1;
					gaz_dbi_table_insert('body_text', $form); // la scrivo nel DB
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
 
// imposto la connessione al server
$conn_id = ftp_connect($ftp_host);

// effettuo login con user e pass
$mylogin = ftp_login($conn_id, $ftp_user, $ftp_pass);

// controllo se la connessione è OK...
if ((!$conn_id) or (!$mylogin)){ 
	?>
	<script>
	alert("<?php echo "Errore: connessione FTP a " . $ftp_host . " non riuscita!"; ?>");
	location.replace("<?php echo $_POST['ritorno']; ?>");
    </script>
	<?php
}
$access=base64_encode($ftp_pass);
if (!isset($_GET['success'])){
	// avvio il file di interfaccia presente nel sito web remoto
	$headers = @get_headers($urlinterf.'?access='.$access);
	if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file esiste o mi dà accesso
		$xml=simplexml_load_file($urlinterf.'?access='.$access) ; // lo carico
		if (!$xml){
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
					<div class="col-sm-12" align="center"><h4>Importazione di articoli dall'e-commerce a GAzie</h4>
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
									<p>Stai per scaricare definitivamente i prodotti in GAzie. <br>Questa operazione &egrave irreversibile. <br>Sei sicuro di volerlo fare?</p>
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
						?>
						<div class="row bg-success" style="border-bottom: 1px solid;">
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
								echo '<input type="hidden" name="descri'. $n .'" value="'. preg_replace('/[\x00-\x1f]/','',htmlspecialchars($product->Name)) . '">';
								?>
							</div>
							<div class="col-sm-1">
								<?php echo '<input type="hidden" name="body_text'. $n .'" value="'. preg_replace('/[\x00-\x1f]/','',htmlspecialchars($product->Description)) . '">';
								echo '<input type="hidden" name="quanti'. $n .'" value="'. $product->AvailableQty .'">';
								echo '<input type="hidden" name="web_price'. $n .'" value="'. $product->Price .'">';
								echo '<input type="hidden" name="unimis'. $n .'" value="'. $product->Unimis .'">';
								echo '<input type="hidden" name="aliquo'. $n .'" value="'. $product->ProductVat .'">';
								echo '<input type="hidden" name="imgurl'. $n .'" value="'. $product->ProductImgUrl .'">';
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
							<!-- Modal content-->
							<div id="downloader" class="modal fade" role="dialog">    
								<div class="modal-dialog modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">ATTENZIONE !</h4>
									</div>
									<div class="modal-body">
										<p>Stai per scaricare definitivamente i prodotti in GAzie. <br>Questa operazione &egrave irreversibile. <br>Sei sicuro di volerlo fare?</p>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annulla</button>
										<input type="submit" class="btn btn-danger pull-right" name="conferma"  value="Carica prodotti in GAzie">
									</div>
								</div>
							</div>
						</div>						
					</div>				
			</div>
		</form>
		<?php
	
	} else { // IL FILE INTERFACCIA NON ESISTE > ESCO
		ftp_quit($conn_id);
		?>
		<script>
		alert("<?php echo "Errore di connessione al file di interfaccia web = ",intval(substr($headers[0], 9, 3)),"<br> Riprovare fra qualche minuto!"; ?>");
		location.replace("<?php echo $_POST['ritorno']; ?>");
		</script>
		<?php
		exit;
	}
} else {
	?>
	<div class="alert alert-success alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>Fatto!</strong> Operazione conclusa con successo.
	</div>
<?php
}
// chiudo la connessione FTP 
ftp_quit($conn_id);
require("../../library/include/footer.php");
?>
                            