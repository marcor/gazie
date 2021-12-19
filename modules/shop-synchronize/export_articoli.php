<?php
/*
	  --------------------------------------------------------------------------
	  GAzie - Gestione Azienda
	  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
	  (http://www.devincentiis.it)
	  <http://gazie.sourceforge.net>
	  --------------------------------------------------------------------------
	  SHOP SYNCHRONIZE è un modulo creato per GAzie da Antonio Germani, Massignano AP
	  Copyright (C) 2018-2021 - Antonio Germani, Massignano (AP)
	  https://www.lacasettabio.it
	  https://www.programmisitiweb.lacasettabio.it
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
	  scriva   alla   Free  Software Foundation,  Inc.,   59
	  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
	  --------------------------------------------------------------------------
	  # free to use, Author name and references must be left untouched  #
	  --------------------------------------------------------------------------
*/
/* Antonio Germani - ESPORTAZIONE MANUALE (update) DEGLI ARTICOLI DA GAZIE ALL'E-COMMERCE -  GLI ARTICOLI DEVONO GIà ESISTERE NELL'E-COMMERCE ALTRIMENTI NON VERRANNO CONSIDERATI */

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
$accpass = gaz_dbi_get_row($gTables['company_config'], "var", "accpass")['val'];
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

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Return'])) {
        header("Location: " . $_POST['ritorno']);
        exit;
    }

if (isset($_POST['conferma'])) { // se confermato
	if (gaz_dbi_get_row($gTables['company_config'], 'var', 'Sftp')['val']=="SI"){

		// SFTP login with private key and password
		$ftp_port = gaz_dbi_get_row($gTables['company_config'], "var", "port")['val'];
		$ftp_key = gaz_dbi_get_row($gTables['company_config'], "var", "chiave")['val'];

		if (gaz_dbi_get_row($gTables['company_config'], "var", "keypass")['val']=="key"){ // SFTP log-in con KEY
			$key = PublicKeyLoader::load(file_get_contents('../../data/files/'.$admin_aziend['codice'].'/secret_key/'. $ftp_key .''),$ftp_pass);

			$sftp = new SFTP($ftp_host, $ftp_port);
			if (!$sftp->login($ftp_user, $key)) {
				// non si connette: key LOG-IN FALSE
				?>
				<script>
				alert("<?php echo "Mancata connessione Sftp con file chiave segreta: impossibile scaricare gli ordini dall\'e-commerce"; ?>");
				location.replace("<?php echo $_POST['ritorno']; ?>");
				</script>
				<?php
			} else {
				?>
				<!--
				<div class="alert alert-success text-center" >
				<strong>ok</strong> Connessione SFTP con chiave riuscita.
				</div>
				-->
				<?php
			}
		} else { // SFTP log-in con password

			$sftp = new SFTP($ftp_host, $ftp_port);
			if (!$sftp->login($ftp_user, $ftp_pass)) {
				// non si connette: password LOG-IN FALSE
				?>
				<script>
				alert("<?php echo "Mancata connessione Sftp con password: impossibile scaricare gli ordini dall\'e-commerce"; ?>");
				location.replace("<?php echo $_POST['ritorno']; ?>");
				</script>
				<?php
			} else {
				?>
				<div class="alert alert-success text-center" >
				<strong>ok</strong> Connessione SFTP con password riuscita.
				</div>
				<?php
			}
		}
	} else {
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
		//FTP turn passive mode on
		ftp_pasv($conn_id, true);
	}
	if ($_GET['img']=="updimg"){ // se si devono aggiornare le immagini
		if (!@ftp_mkdir($conn_id, $ftp_path_upload."images")){ // se non c'è la cartella images la creo
			// get contents of the current directory
			$files = ftp_nlist($conn_id, $ftp_path_upload."images");

			foreach ($files as $file){ // se c'era, cancello i files del precedente aggiornamento
				if (@ftp_delete($conn_id, $file)){
				} else {
					header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=6");
					exit;
				}
			}
		}
	}

		// creo il file xml
	$xml_output = '<?xml version="1.0" encoding="UTF-8"?>
	<GAzieDocuments AppVersion="1" Creator="Antonio Germani Copyright" CreatorUrl="https://www.lacasettabio.it">';
	$xml_output .= "\n<Products>\n";
	for ($ord=0 ; $ord<=$_POST['num_products']; $ord++){// ciclo gli articoli e creo il file xml
		if (isset($_POST['download'.$ord])){ // se selezionato
			if ((isset ($_POST['barcode'.$ord]) AND intval($_POST['barcode'.$ord])==0) OR !isset ($_POST['barcode'.$ord])) {
				$_POST['barcode'.$ord]="NULL";
			}

			$xml_output .= "\t<Product>\n";
			$xml_output .= "\t<Id>".$_POST['ref_ecommerce_id_product'.$ord]."</Id>\n";
			$xml_output .= "\t<IdMain>".$_POST['ref_ecommerce_id_main_product'.$ord]."</IdMain>\n";
			if (intval($_POST['ref_ecommerce_id_main_product'.$ord])>0){
				if ($_POST['ref_ecommerce_id_product'.$ord]<1){
					$xml_output .= "\t<Type>parent</Type>\n";
				} else {
					$xml_output .= "\t<Type>variant</Type>\n";
					if (json_decode($_POST['ecomm_option_attribute'.$ord]) != null){ // se esiste un json per attributo della variante dell'e-commerce
						$var = json_decode($_POST['ecomm_option_attribute'.$ord]);
						$xml_output .= "\t<Characteristic>".$var->var_name."</Characteristic>\n";
						$xml_output .= "\t<CharacteristicId>".$var->var_id."</CharacteristicId>\n";
					}
				}
			} else {
				$xml_output .= "\t<Type>product</Type>\n";
			}
			$xml_output .= "\t<Code>".$_POST['codice'.$ord]."</Code>\n";
			$xml_output .= "\t<BarCode>".$_POST['barcode'.$ord]."</BarCode>\n";
			if ($_GET['qta']=="updqty"){
				$xml_output .= "\t<AvailableQty>".$_POST['quanti'.$ord]."</AvailableQty>\n";
			}
			if ($_GET['prezzo']=="updprice" AND $_POST['web_price'.$ord]>0){
				// Calcolo il prezzo IVA compresa
				$aliquo=gaz_dbi_get_row($gTables['aliiva'], "codice", intval($_POST['aliiva'.$ord]))['aliquo'];
				$web_price_vat_incl= $_POST['web_price'.$ord] +(($_POST['web_price'.$ord]*$aliquo)/100);
				$web_price_vat_incl=number_format($web_price_vat_incl, $admin_aziend['decimal_price'], '.', '');
				$xml_output .= "\t<Price>".$_POST['web_price'.$ord]."</Price>\n";
				$xml_output .= "\t<PriceVATincl>".$web_price_vat_incl."</PriceVATincl>\n";
				$xml_output .= "\t<VAT>".$aliquo."</VAT>\n";
			}
			if ($_GET['name']=="updnam" AND strlen($_POST['descri'.$ord])>0){
				$xml_output .= "\t<Name>".$_POST['descri'.$ord]."</Name>\n";
			}
			if ($_GET['descri']=="upddes" AND strlen($_POST['body_text'.$ord])>0){
				$xml_output .= "\t<Description>".preg_replace('/[\x00-\x1f]/','',htmlspecialchars($_POST['body_text'.$ord]))."</Description>\n";
			}
			$xml_output .= "\t<WebPublish>".$_POST['web_public'.$ord]."</WebPublish>\n";// 1=attivo su web; 2=attivo e prestabilito; 3=attivo e pubblicato in home; 4=attivo, in home e prestabilito; 5=disattivato su web"
			if ($_GET['img']=="updimg" AND strlen($_POST['imgurl'.$ord])>0){ // se è da aggiornare e c'è un'immagine HQ
				if (ftp_put($conn_id, $ftp_path_upload."images/".$_POST['imgname'.$ord], $_POST['imgurl'.$ord],  FTP_BINARY)){
					// ho scritto l'immagine web HQ nella cartella e-commerce
          ftp_chmod($conn_id, 0664, $ftp_path_upload."images/".$_POST['imgname'.$ord]);// fornisco i permessi necessari all'immagine
					$xml_output .= "\t<ImgUrl>".$web_site_path."images/".$_POST['imgname'.$ord]."</ImgUrl>\n"; // ne scrivo l'url nel file xml
				} else {
					// ERRORE chiudo la connessione FTP
					ftp_quit($conn_id);
					header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=5");
					exit;
				}
			}
			if ($_GET['img']=="updimg" AND strlen($_POST['imgblob'.$ord])>0){// se è da aggiornare e c'è un'immagine blob
				file_put_contents("../../data/files/tmp/img.jpg", base64_decode($_POST['imgblob'.$ord])); // salvo immagine nella cartella temporanea
				if (ftp_put($conn_id, $ftp_path_upload."images/".$_POST['codice'.$ord].".jpg", "../../data/files/tmp/img.jpg",  FTP_BINARY)){
					// scrivo l'immagine web blob nella cartella images dell'e-commerce
          ftp_chmod($conn_id, 0664, $ftp_path_upload."images/".$_POST['codice'.$ord].".jpg");// fornisco i permessi necessari all'immagine
					$xml_output .= "\t<ImgUrl>".$web_site_path."images/".$_POST['codice'.$ord].".jpg</ImgUrl>\n"; // ne scrivo l'url nel file xml
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
	$xml_output .="</Products>\n</GAzieDocuments>";
	$xmlFile = "prodotti.xml";
	$xmlHandle = fopen($xmlFile, "w");
	fwrite($xmlHandle, $xml_output);
	fclose($xmlHandle);

	if (gaz_dbi_get_row($gTables['company_config'], 'var', 'Sftp')['val']=="SI"){

		if ($sftp->put($ftp_path_upload."prodotti.xml", $xmlFile, SFTP::SOURCE_LOCAL_FILE)){
			$sftp->disconnect();
			?>
			<!--
			<div class="alert alert-success text-center" >
			<strong>ok</strong> il file xml è stato trasferito al sito web tramite SFTP.
			</div>
			-->
			<?php
		}else {
			// chiudo la connessione FTP
			$sftp->disconnect();
			?>
			<script>
			alert("<?php echo "Errore di upload del file xml tramite SFTP"; ?>");
			location.replace("<?php echo $_POST['ritorno']; ?>");
			</script>
			<?php
		}
	} else { // FTP semplice
		// upload file xml
		if (ftp_put($conn_id, $ftp_path_upload."prodotti.xml", $xmlFile, FTP_ASCII)){
			// è OK
			//echo "xml trasferito";
		} else{
			// ERRORE chiudo la connessione FTP
			ftp_quit($conn_id);
			header("Location: " . "../../modules/shop-synchronize/export_articoli.php?success=4");
			exit;
		}
	}
	$access=base64_encode($accpass);

	// avvio il file di interfaccia presente nel sito web remoto
	$headers = get_headers ($urlinterf.'?access='.$access);

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
				$artico = gaz_dbi_query ('SELECT ecomm_option_attribute, codice, barcode, web_price, descri, aliiva, ref_ecommerce_id_product, id_artico_group, web_public, image FROM '.$gTables['artico'].' WHERE web_public = \'1\' and good_or_service <> \'1\' ORDER BY codice');
				$n=0;
				while ($item = gaz_dbi_fetch_array($artico)){ // li ciclo
					$ref_ecommerce_id_main_product="";
					if ($item['id_artico_group']>0){
						$artico_group = gaz_dbi_query ('SELECT ref_ecommerce_id_main_product FROM '.$gTables['artico_group'].' WHERE id_artico_group = \''.$item['id_artico_group'].'\'');
						$item_group = gaz_dbi_fetch_array($artico_group);
						$ref_ecommerce_id_main_product = $item_group['ref_ecommerce_id_main_product'];
					}
					$avqty = 0;
					$ordinatic = $gForm->get_magazz_ordinati($item['codice'], "VOR");
					$mv = $gForm->getStockValue(false, $item['codice']);

						$magval = array_pop($mv);
						if ($magval){
							$avqty=$magval['q_g']-$ordinatic;
						}
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
								echo '<input type="hidden" name="web_public'. $n .'" value="'. $item['web_public'] . '">';
								echo '<input type="hidden" name="quanti'. $n .'" value="'. $avqty .'">';
								echo '<input type="hidden" name="aliiva'. $n .'" value="'. $item['aliiva'] .'">';
								echo '<input type="hidden" name="web_price'. $n .'" value="'. $item['web_price'] .'">';
								echo '<input type="hidden" name="ref_ecommerce_id_main_product'. $n .'" value="'. $ref_ecommerce_id_main_product .'">';
								echo '<input type="hidden" name="ref_ecommerce_id_product'. $n .'" value="'. $item['ref_ecommerce_id_product'] .'">';
								echo '<input type="hidden" name="ecomm_option_attribute'. $n .'" value="'. htmlspecialchars($item['ecomm_option_attribute']) .'">';
								if ($_GET['img']=="updimg"){ // se devo aggiornare l'immagine ne trovo l'url di GAzie
									unset ($imgres);
                  $imgres = gaz_dbi_get_row($gTables['files'], "table_name_ref", "artico", "AND id_ref ='1' AND item_ref = '". $item['codice']."'");
									if (isset($imgres['id_doc']) AND $imgres['id_doc']>0){ // se c'è un'immagine
										$imgurl=DATA_DIR."files/".$admin_aziend['company_id']."/images/". $imgres['id_doc'] . "." . $imgres['extension'];
										$imgblob="";echo "Img HQ";
									} else {
										$imgurl="";
										$imgres['id_doc']="";
										$imgres['extension']="";
										$imgblob=$item['image'];echo "Img blob";
									}
									echo '<input type="hidden" name="imgurl'. $n .'" value="'. $imgurl .'">';
									echo '<input type="hidden" name="imgname'. $n .'" value="'. $imgres['id_doc'] . "." . $imgres['extension'] .'">';
									echo '<input type="hidden" name="imgblob'. $n .'" value="'. base64_encode($imgblob) .'">';
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


				// carico in $parent i gruppi che sono presenti in GAzie
				$parent = gaz_dbi_query ('SELECT * FROM '.$gTables['artico_group'].' WHERE web_public = \'1\' ORDER BY id_artico_group');

				while ($item = gaz_dbi_fetch_array($parent)){ // ciclo i PARENT / GRUPPI

					?>
					<div class="row bg-warning" style="border-bottom: 1px solid;">
							<div class="col-sm-2">
								<?php echo $n;?>
							</div>
							<div class="col-sm-2">
								<?php echo $item['id_artico_group'];
								echo '<input type="hidden" name="codice'. $n .'" value="'. $item['id_artico_group'] . '">';
								?>
							</div>
							<div class="col-sm-6">
								<?php echo $item['descri'];
								echo '<input type="hidden" name="descri'. $n .'" value="'. $item['descri'] . '">';
								?>
							</div>
							<div class="col-sm-1">
								<?php

								echo '<input type="hidden" name="body_text'. $n .'" value="'. preg_replace('/[\x00-\x1f]/','',htmlspecialchars($item['large_descri'])) . '">';

								echo '<input type="hidden" name="web_public'. $n .'" value="'. $item['web_public'] . '">';
								echo '<input type="hidden" name="quanti'. $n .'" value="">';
								echo '<input type="hidden" name="aliiva'. $n .'" value="">';
								echo '<input type="hidden" name="web_price'. $n .'" value="">';
								echo '<input type="hidden" name="ref_ecommerce_id_main_product'. $n .'" value="'. $item['ref_ecommerce_id_main_product'] .'">';
								echo '<input type="hidden" name="ref_ecommerce_id_product'. $n .'" value="">';

								if ($_GET['img']=="updimg"){ // se devo aggiornare l'immagine cerco l'url di quella HQ High Quality in GAzie
                  unset ($imgres);
                  // NB: al momento i gruppi/parent non gestiscono le immagini HQ
                  /*  quando verrà gestitata bata decommentare la riga seguente e tutto funziona già
									$imgres = gaz_dbi_get_row($gTables['files'], "table_name_ref", "artico_group", "AND id_ref ='1' AND item_ref = '". $item['id_artico_group']."'");
									*/
                  // Si preferisce l'immagine HQ, in mancanza si invia la blob
									if (isset($imgres['id_doc']) AND $imgres['id_doc']>0){ // se c'è un'immagine High Quality
										$imgurl=DATA_DIR."files/".$admin_aziend['company_id']."/images/". $imgres['id_doc'] . "." . $imgres['extension'];
										$imgblob="";echo "Img HQ";
									} else {
										$imgurl="";
										$imgres['id_doc']="";
										$imgres['extension']="";
										$imgblob=$item['image'];echo "img blob";

									}
									echo '<input type="hidden" name="imgurl'. $n .'" value="'. $imgurl .'">';
									echo '<input type="hidden" name="imgname'. $n .'" value="'. $imgres['id_doc'] . "." . $imgres['extension'] .'">';
									echo '<input type="hidden" name="imgblob'. $n .'" value="'. base64_encode($imgblob) .'">';
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
} elseif ($_GET['success']==6){
	?>
	<div class="alert alert-danger alert-dismissible">
		<a href="../../modules/shop-synchronize/synchronize.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<strong>ERRORE!</strong> Non è riuscita la cancellazione delle vecchie immagini temporanee della cartella images remota!.
	</div>
<?php
}
require("../../library/include/footer.php");
?>
