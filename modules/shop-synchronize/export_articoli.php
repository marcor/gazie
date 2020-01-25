<?php
/* ------------------------------------------------------------------------
  INTERFACCIA esporta articoli da GAzie a e-commerce
  ------------------------------------------------------------------------
  @Author    Antonio Germani
  @Website   http://www.lacasettabio.it
  @Copyright Copyright (C) 2018 - 2019 Antonio Germani All Rights Reserved.
  versione 1.0
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
require("../../library/include/datlib.inc.php");
require ("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();
$gForm = new magazzForm;
$resserver = gaz_dbi_get_row($gTables['company_config'], "var", "server");
$ftp_host= $resserver['val'];
$resftp_path = gaz_dbi_get_row($gTables['company_config'], "var", "ftp_path");
$ftp_path_upload=$resftp_path['val'];
$resuser = gaz_dbi_get_row($gTables['company_config'], "var", "user");
$ftp_user = $resuser['val'];
$respass = gaz_dbi_get_row($gTables['company_config'], "var", "pass");
$ftp_pass= $respass['val'];
$respath = gaz_dbi_get_row($gTables['company_config'], "var", "path");
$web_site_path= $respath['val'];
$test = gaz_dbi_query("SHOW COLUMNS FROM `" . $gTables['admin'] . "` LIKE 'enterprise_id'");
$exists = (gaz_dbi_num_rows($test)) ? TRUE : FALSE;
if ($exists) {
    $c_e = 'enterprise_id';
} else {
    $c_e = 'company_id';
}
$admin_aziend = gaz_dbi_get_row($gTables['admin'] . ' LEFT JOIN ' . $gTables['aziend'] . ' ON ' . $gTables['admin'] . '.' . $c_e . '= ' . $gTables['aziend'] . '.codice', "user_name", $_SESSION["user_name"]);
$path = gaz_dbi_get_row($gTables['company_config'], 'var', 'path');
$urlinterf = $path['val']."articoli-gazie.php";// nome del file interfaccia presente nella root dell'e-commerce. Per evitare intrusioni indesiderate Il file dovrà gestire anche una password. Per comodità viene usata la stessa FTP.
// il percorso per raggiungere questo file va impostato in configurazione avanzata azienda alla voce "Website root directory

//ob_flush();
//flush();
//ob_start();

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Return'])) { 
        header("Location: " . $_POST['ritorno']);
        exit;
    }
 
if (isset($_POST['conferma'])) { // se confermato
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
	if ($_GET['img']=="updimg"){ // se si devono aggiornare le immagini
		ftp_rmdir($conn_id, $web_site_path."images"); // cancello la cartella images con i vecchi files
		ftp_mkdir($conn_id, $web_site_path."images"); // creo nuovamente la cartella images per i nuovi files
	}
	//turn passive mode on
	ftp_pasv($conn_id, true);
	
		// creo il file xml
	$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
	<GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">';
	$xml_output .= "\n<Products>\n";
	for ($ord=0 ; $ord<=$_POST['num_products']; $ord++){// ciclo gli articoli e creo il file xml
		if (isset($_POST['download'.$ord])){ // se selezionato	
			if (intval($_POST['barcode'.$ord])==0) {
				$_POST['barcode'.$ord]="NULL";
			}
			$xml_output .= "\t<Product>\n";
			$xml_output .= "\t<Code>".$_POST['codice'.$ord]."</Code>\n";
			$xml_output .= "\t<BarCode>".$_POST['barcode'.$ord]."</BarCode>\n";
			if ($_GET['qta']=="updqty"){
				$xml_output .= "\t<AvailableQty>".$_POST['quanti'.$ord]."</AvailableQty>\n";
			}
			if ($_GET['prezzo']=="updprice" AND $_POST['web_price'.$ord]>0){
				$xml_output .= "\t<WebPrice>".$_POST['web_price'.$ord]."</WebPrice>\n";
			}
			if ($_GET['name']=="updnam" AND strlen($_POST['descri'.$ord])>0){
				$xml_output .= "\t<Name>".$_POST['descri'.$ord]."</Name>\n";
			}
			if ($_GET['descri']=="upddes" AND strlen($_POST['body_text'.$ord])>0){
				$xml_output .= "\t<Description>".$_POST['body_text'.$ord]."</Description>\n";
			}
			if ($_GET['img']=="updimg" AND strlen($_POST['imgurl'.$ord])>0){
				if (ftp_put($conn_id, $ftp_path_upload."images/".$_POST['imgname'.$ord], $_POST['imgurl'.$ord],  FTP_BINARY)){
					// scrivo l'immagine web HQ nella cartella e-commerce
					$xml_output .= "\t<ImgUrl>".$web_site_path."images/".$_POST['imgname'.$ord]."</ImgUrl>\n"; // ne scrivo l'url nel file xml
				} else {
					// ERRORE chiudo la connessione FTP 
					ftp_quit($conn_id);
					header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=5");
					exit;
				}
				
			}
			$xml_output .= "\t</Product>\n";
		}
	}
	$xml_output .="\n</Products>\n</GAzieDocuments>";
	$xmlFile = "prodotti.xml";
	$xmlHandle = fopen($xmlFile, "w");
	fwrite($xmlHandle, $xml_output);
	fclose($xmlHandle);
	
	// upload file xml
	if (ftp_put($conn_id, $ftp_path_upload."prodotti.xml", $xmlFile, FTP_ASCII)){
		// è OK
	} else{
		// ERRORE chiudo la connessione FTP 
		ftp_quit($conn_id);
		header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=4");
		exit;
	}

	$access=base64_encode($ftp_pass);

	// avvio il file di interfaccia presente nel sito web remoto
	$headers = @get_headers($urlinterf.'?access='.$access);
	
	if ( intval(substr($headers[0], 9, 3))==200){ // controllo se il file esiste o mi dà accesso
		
		$file = fopen ($urlinterf.'?access='.$access, "r");
		if (!$file) {
			// chiudo la connessione FTP 
			ftp_quit($conn_id);
			header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=3");
			exit;
			
		} else {
			// chiudo la connessione FTP 
			ftp_quit($conn_id);
						
		}
	} else { // IL FILE INTERFACCIA NON ESISTE > ESCO
		// chiudo la connessione FTP 
		ftp_quit($conn_id);
		header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=2");
		exit;
	}

	// chiudo la connessione FTP 
	ftp_quit($conn_id);
	header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=1");
    exit;
} else {
	require('../../library/include/header.php');
	$script_transl = HeadMain();
}

if (!isset($_GET['success'])){	
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
</script>
		<form method="POST" name="download" enctype="multipart/form-data">
			<input type="hidden" name="ritorno" value="<?php echo $_POST['ritorno'];?>" >
			<div class="container-fluid" style="max-width:90%;">
				<div class="row bg-primary" >
					<div class="col-sm-12" align="center"><h4>Esportazione di articoli da GAzie</h4>
						<p align="justify">Gli articoli selezionati verranno aggiornati nell'e-commerce se esistenti, altrimenti verranno ignorati. </p>
						<?php
						if ($_GET['img']=="updimg") {?>
							<b> Hai selezionato di trasferire le immagini: questa operazione potrebbe richiedere molti minuti di attesa!</b>
							<?php
						}
						?>
					</div>
				</div>
				<div class="row bg-info">
					<div class="col-sm-4">
						<input type="submit" name="Return"  value="Indietro">
					</div>
					<div class="col-sm-4" style="background-color:lightgreen;">
						<?php echo "E-commerce sincronizzato: " . $ftp_host;?>
					</div>
					<div class="col-sm-4" align="right">
						<!-- Trigger the modal with a button -->
						<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#downloader">Aggiorna i prodotti nell'e-commerce</button>
						<!-- Modal content-->
						<div id="downloader" class="modal fade" role="dialog">    
							<div class="modal-dialog modal-content">
								<div class="modal-header" align="left">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">ATTENZIONE!</h4>
								</div>
								<div class="modal-body">
									<p>Stai per aggiornare definitivamente i prodotti nell'e-commerce. <br>Questa operazione &egrave irreversibile. <br>Sei sicuro di volerlo fare?</p>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annulla</button>
									<input type="submit" class="btn btn-danger pull-right" name="conferma"  value="Aggiorna l'e-commerce">
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
				// carico in $artico gli articoli che sono presenti in GAzie
				$artico = gaz_dbi_query ('SELECT codice, barcode, web_price, descri FROM '.$gTables['artico'].' WHERE web_public = \'1\' and good_or_service <> \'1\' ORDER BY codice');
				$n=0;
				while ($item = gaz_dbi_fetch_array($artico)){ // li ciclo
					$avqty = 0;
					$ordinatic = $gForm->get_magazz_ordinati($item['codice'], "VOR");
					$mv = $gForm->getStockValue(false, $item['codice']);
					$magval = array_pop($mv);
					$avqty=$magval['q_g']-$ordinatic;
					if ($avqty<0 or $avqty==""){
						$avqty="0";
					}
					?>
					<div class="row bg-success" style="border-bottom: 1px solid;">
							<div class="col-sm-2">
								<?php echo $n;?>
							</div>
							<div class="col-sm-2">
								<?php echo $item['codice'];
								echo '<input type="hidden" name="codice'. $n .'" value="'. $item['codice'] . '">';
								?>
							</div>
							<div class="col-sm-6">
								<?php echo $item['descri'];
								echo '<input type="hidden" name="descri'. $n .'" value="'. $item['descri'] . '">';
								?>
							</div>
							<div class="col-sm-1">
								<?php 
								if ($_GET['descri']=="upddes"){ // se devo aggiornare il body_text
									$body = gaz_dbi_get_row($gTables['body_text'], "table_name_ref", "artico_". $item['codice']);
									echo '<input type="hidden" name="body_text'. $n .'" value="'. preg_replace('/[\x00-\x1f]/','',htmlspecialchars($body['body_text'])) . '">';
								}
								echo '<input type="hidden" name="quanti'. $n .'" value="'. $avqty .'">';
								echo '<input type="hidden" name="web_price'. $n .'" value="'. $item['web_price'] .'">';
								echo '<input type="hidden" name="barcode'. $n .'" value="'. $item['barcode'] .'">';
								if ($_GET['img']=="updimg"){ // se devo aggiornare l'immagine ne trovo l'url di GAzie
									$imgres = gaz_dbi_get_row($gTables['files'], "table_name_ref", "artico", "AND id_ref ='1' AND item_ref = '". $item['codice']."'");
									if ($imgres['id_doc']>0){ // se c'è un'immagine
										$imgurl="../../data/files/".$admin_aziend['company_id']."/images/". $imgres['id_doc'] . "." . $imgres['extension'];
									} else {
										$imgurl="";
									}
									echo '<input type="hidden" name="imgurl'. $n .'" value="'. $imgurl .'">';
									echo '<input type="hidden" name="imgname'. $n .'" value="'. $imgres['id_doc'] . "." . $imgres['extension'] .'">';
								}
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
						<?php echo "E-commerce sincronizzato: " . $ftp_host;?>
					</div>
					<div class="col-sm-4" align="right">
						<!-- Trigger the modal with a button -->
						<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#downloader">Aggiorna i prodotti nell'e-commerce</button>
						<!-- Modal content-->
						<div id="downloader" class="modal fade" role="dialog">    
							<div class="modal-dialog modal-content">
								<div class="modal-header" align="left">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">ATTENZIONE!</h4>
								</div>
								<div class="modal-body">
									<p>Stai per aggiornare definitivamente i prodotti nell'e-commerce. <br>Questa operazione &egrave irreversibile. <br>Sei sicuro di volerlo fare?</p>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annulla</button>
									<input type="submit" class="btn btn-danger pull-right" name="conferma"  value="Aggiorna l'e-commerce">
								</div>
							</div>
						</div>
					</div>						
				</div>
				
				
			</div> <!-- container fluid -->
		</form>

<?php
} elseif ($_GET['success']==1){
	?>
	<div class="alert alert-success alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>Fatto!</strong> Operazione conclusa con successo.
	</div>
<?php
} elseif ($_GET['success']==2){
	?>
	<div class="alert alert-danger alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>ERRORE!</strong> Manca il file di interfaccia nell'e-commerce o non è stato possibile accedervi!.
	</div>
<?php
} elseif ($_GET['success']==3){
	?>
	<div class="alert alert-danger alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>ERRORE!</strong> Il file di interfaccia nell'e-commerce non parte!.
	</div>
<?php
} elseif ($_GET['success']==4){
	?>
	<div class="alert alert-danger alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>ERRORE!</strong> L'upload del file xml non è riuscito!.
	</div>
<?php
} elseif ($_GET['success']==5){
	?>
	<div class="alert alert-danger alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>ERRORE!</strong> L'upload dell'immagine dell'articolo non è riuscito!.
	</div>
<?php
}
require("../../library/include/footer.php");
?>
                            