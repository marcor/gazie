<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
/* ------------------------------------------------------------------------
  INTERFACCIA sincronizzazione e-commerce <-> GAzie
  ------------------------------------------------------------------------
  @Author    Antonio Germani 340-5011912
  @Website   http://www.programmisitiweb.lacasettabio.it
  @Copyright Copyright (C) 2018 - 2021 Antonio Germani All Rights Reserved.
  versione 3.0
  ------------------------------------------------------------------------ 
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

$file_download = "dowload_ordini.php";
$file_upload = "upload_prodotti.php";
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
	
	if (file_exists($file_downloader)){ // importazione 
		if (!isset($_POST['scarprezzo'])){
			$_POST['scarprezzo']="";
		}
		if (!isset($_POST['scardescrizione'])){
			$_POST['scardescrizione']="";
		}
		header("Location: " . $file_downloader."?upd=".$_POST['upd']."&updpre=".$_POST['updpre']."&updname=".$_POST['updname']."&upddes=".$_POST['upddes']."&updimm=".$_POST['updimm']."&imp=".$_POST['imp']."&imppre=".$_POST['imppre']."&impdes=".$_POST['impdes']."&impimm=".$_POST['impimm']);
		exit;
	} else {
		header("Location: " . $_POST['ritorno']);
        exit;
		}
} elseif (isset ($_POST['uploader'])) { 
	
	if (file_exists($file_uploader)){ // esportazione/aggiornamento
		header("Location: " . $file_uploader."?prezzo=".$_POST['prezzo']."&qta=".$_POST['quantita']."&descri=".$_POST['descrizione']."&img=".$_POST['immagine']."&name=".$_POST['name']);
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
							<h3 class="text-primary">ESPORTAZIONE - aggiornamento articoli nell'e-commerce</h3>
						</div>
					<div class="col-sm-6  bg-warning" align="left" style="font-size: 18;">
						<input type="checkbox" name="quantita" value="updqty" checked> Quantit&agrave &nbsp 
						<input type="checkbox" name="prezzo" value="updprice"> Prezzo &nbsp
						<input type="checkbox" name="name" value="updnam" > Nome &nbsp
						<input type="checkbox" name="descrizione" value="upddes" > Descrizione estesa &nbsp
						<input type="checkbox" name="immagine" value="updimg" > immagine &nbsp
					</div>
					
						<div class="col-sm-12  bg-warning">
							<input type="submit" class="btn btn-danger btn-sm pull-right" name="uploader"  value="Seleziona i prodotti da aggiornare">
						</div>
			
				</div>
				<div class="row bg-success" style="border-top: 1px solid;">
						<div class="col-sm-12 bg-success" align="center" >
							<h3 class="text-primary">IMPORTAZIONE - inserimento o aggiornamento articoli in GAzie</h3> 
						</div>
						
						<div class="col-sm-6  bg-success" align="left" style="font-size: 18;">
							<input type="checkbox" name="upd" value="updval" > Attiva modifica articolo<br><br>
							<p> Nell'articolo variare anche:</p>
							<!-- <input type="checkbox" name="impquantita" value="dwldqty"> quantit&agrave &nbsp -->
							<input type="checkbox" name="updpre" value="updpre"> Prezzo web &nbsp
							<input type="checkbox" name="updname" value="updnam" > Nome &nbsp
							<input type="checkbox" name="upddes" value="upddes" > Descrizione estesa &nbsp
							<input type="checkbox" name="updimm" value="updimg" > Immagine &nbsp
						</div>
						<div class="col-sm-6  bg-success" align="left" style="font-size: 18;">
							<input type="checkbox" name="imp" value="impval" > Attiva inserimento articolo<br><br>
							<p> Nell'articolo inserire anche:</p>
							<!-- <input type="checkbox" name="scarquantita" value="dwldqty"> quantit&agrave &nbsp -->
							<input type="checkbox" name="imppre" value="dwlprice"> Prezzo web &nbsp
							<input type="checkbox" name="impdes" value="dwldes" > Descrizione estesa &nbsp 
							<input type="checkbox" name="impimm" value="dwlimg" > Immagine &nbsp
						</div>
				
				
						<div class="col-sm-12  bg-success">
							<input type="submit" class="btn btn-danger btn-sm pull-right" name="downloader"  value="Seleziona i prodotti da importare o aggiornare">
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