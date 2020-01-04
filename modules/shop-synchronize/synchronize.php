<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
/*    >>> LEGGIMI <<<
------------------------------------------------------------------------------------------
	** Antonio Germani - www.lacasettabio.it **
	Questo codice serve per avviare l'interfaccia per la sincronizzazione di GAzie con un negozio online.
	Per ogni caso specifico, sono necessari due file intefaccia, uno da mettere nella cartella "shop-synchronize" di GAzie e l'altro da mettere nella root del negozio online.
	I file interfaccia sono specifici per ciascun CMS e/o componente utilizzato dal negozio online.
	La cartella synchronize di GAzie potrà contenere tutti i file interfaccia che a mano a mano verranno crati dagli sviluppatori.
	L'utente deve scegliere quali interfacce utilizzare (sulla base delle caratteristiche del suo negozio online) e scrivere i relativi nomi dei file nella sottostante "Impostazione".
	-Caso download: L'interfaccia presente nella root del negozio online elabora i dati del database del negozio e crea un file xml. In GAzie, la seconda interfaccia elabora il file xml scrivendone i dati sul database di GAzie.
	-Caso Upload: L'interfaccia di GAzie crea un file xml che viene letto dall'interfaccia presente nella root del negozio online. Con i dati presenti nel file xml viene scritto il data base dell'e-commerce.
------------------------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();

// Prendo l'id_currency 
$test = gaz_dbi_query("SHOW COLUMNS FROM `" . $gTables['admin'] . "` LIKE 'enterprise_id'");
$exists = (gaz_dbi_num_rows($test)) ? TRUE : FALSE;
if ($exists) {
    $c_e = 'enterprise_id';
} else {
    $c_e = 'company_id';
}

$file_download = "dowload_ordini_joomla.php";
$file_upload = "upload_prodotti_joomla.php";
$file_downloader = "import_articoli.php";
$file_uploader = "export_articoli.php";

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Return'])) { 
        header("Location: " . $_POST['ritorno']);
        exit;
    }
if (isset ($_POST['download'])) {
	if (file_exists($file_download)) {
		include $file_download;
	} else {
		header("Location: " . $_POST['ritorno']);
        exit;
	}
} elseif (isset ($_POST['upload'])) {
	
	if (file_exists($file_upload)){
		include $file_upload;
	} else {
		header("Location: " . $_POST['ritorno']);
        exit;
		}
} elseif (isset ($_POST['downloader'])) { 
	
	if (file_exists($file_downloader)){
		if (!isset($_POST['scarprezzo'])){
			$_POST['scarprezzo']="";
		}
		if (!isset($_POST['scardescrizione'])){
			$_POST['scardescrizione']="";
		}
		header("Location: " . $file_downloader."?updpre=".$_POST['updpre']."&upddes=".$_POST['upddes']."&updimm=".$_POST['updimm']."&imppre=".$_POST['imppre']."&impdes=".$_POST['impdes']."&impimm=".$_POST['impimm']);
		exit;
	} else {
		header("Location: " . $_POST['ritorno']);
        exit;
		}
} elseif (isset ($_POST['uploader'])) { 
	
	if (file_exists($file_uploader)){
		header("Location: " . $file_uploader."?prezzo=".$_POST['prezzo']."&qta=".$_POST['quantita']."&descri=".$_POST['descrizione']);
		exit;
	} else {
		header("Location: " . $_POST['ritorno']);
        exit;
		}
} else {
	require('../../library/include/header.php');
	$script_transl = HeadMain();
	?>
<form method="POST" name="chouse" enctype="multipart/form-data">
	<input type="hidden" name="ritorno" value="<?php echo $_POST['ritorno'];?>" >
	<div class="container-fluid" style="max-width:90%;">
		<div class="row bg-primary" >
			<div class="col-sm-12" align="center"><h4>Sincronizzazione di GAzie con sito e-commerce</h4>
				<p align="justify">Scarica ordini: importa ordini dal sito a GAzie</p>
				<p align="justify">Aggiorna prodotti: aggiorna le quantità disponibili da Gazie al sito</p>
			</div>
		</div>
		<div>
			<div class="row bg-info">
				<div class="col-sm-4  bg-warning" align="center">
					<input type="submit" class="btn btn-primary" name="Return"  onClick="chkSubmit();" value="Indietro">
				</div>
				<div class="col-sm-4  bg-success" align="center">
					<input type="submit" class="btn btn-primary" name="download"  onClick="chkSubmit();" value="Scarica ordini">
				</div>
				<div class="col-sm-4 bg-warning" align="center">
					<input type="submit" class="btn btn-primary" name="upload"  onClick="chkSubmit();" value="Aggiorna q.t&agrave; prodotti">
				</div>
			</div>
			<div class="row bg-info">
				<div class="col-sm-12  bg-info" align="center">
					<input type="button" name="button1" id="nextbt" rel="more" value="&#9660" onclick="buttonToggle(this,'&#9650','&#9660')">
				</div>						
			</div>
			<div id="more">
				<div class="row bg-warning" style="border-top: 1px solid;">
					<div class="col-sm-12 bg-warning" align="center" >
							<h3 class="text-primary">ESPORTAZIONE</h3>
						</div>
					<div class="col-sm-6  bg-warning" align="left" style="font-size: 18;">
						<input type="checkbox" name="quantita" value="updqty" checked> Quantit&agrave &nbsp 
						<input type="checkbox" name="prezzo" value="updprice"> Prezzo &nbsp
						<input type="checkbox" name="descrizione" value="upddes" > Descrizione estesa &nbsp
						<!-- <input type="checkbox" name="carimg" value="updimg" > immagine &nbsp -->
					</div>
					
						<div class="col-sm-12  bg-warning">
							<input type="submit" class="btn btn-danger btn-sm pull-right" name="uploader"  value="Aggiorna prodotti nell'e-commerce">
						</div>
			
				</div>
				<div class="row bg-success" style="border-top: 1px solid;">
						<div class="col-sm-12 bg-success" align="center" >
							<h3 class="text-primary">IMPORTAZIONE</h3>
						</div>
						<div class="col-sm-6  bg-success" align="left" style="font-size: 18;">
							<p> In aggiornamento variare anche:</p>
							<!-- <input type="checkbox" name="impquantita" value="dwldqty"> quantit&agrave &nbsp -->
							<input type="checkbox" name="updpre" value="updprice"> Prezzo &nbsp
							<input type="checkbox" name="upddes" value="upddes" > Descrizione estesa &nbsp
							<input type="checkbox" name="updimm" value="updimg" > Immagine &nbsp
						</div>
						<div class="col-sm-6  bg-success" align="left" style="font-size: 18;">
							<p> In nuovo inserimento inserire anche:</p>
							<!-- <input type="checkbox" name="scarquantita" value="dwldqty"> quantit&agrave &nbsp -->
							<input type="checkbox" name="imppre" value="dwlprice"> Prezzo &nbsp
							<input type="checkbox" name="impdes" value="dwldes" > Descrizione estesa &nbsp 
							<input type="checkbox" name="impimm" value="dwlimg" > Immagine &nbsp
						</div>
				
				
						<div class="col-sm-12  bg-success">
							<input type="submit" class="btn btn-danger btn-sm pull-right" name="downloader"  value="Carica prodotti in GAzie">
						</div>
				</div>
					
				
			</div>
		</div>
	</div>		
</form>		
	
	<style>#more { display:none; }</style>				
	<script>
		function buttonToggle(where, pval, nval) {
			var table = document.getElementById(where.attributes.rel.value);
			where.value = (where.value == pval) ? nval : pval;
			table.style.display = (table.style.display == 'block') ? 'none' : 'block';
		}
	</script>
	<?php
}
require("../../library/include/footer.php");
?>