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
	-Caso Upload: L'interfaccia di GAzie crea un file xml che viene letto dall'interfaccia presente nella root del negozio online. Con i dati presenti nel file xml viene scritto il data base di Joomla.
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
} else {
	require('../../library/include/header.php');
	$script_transl = HeadMain();
	?>
	<form method="POST" name="chouse" enctype="multipart/form-data">
	<input type="hidden" name="ritorno" value="<?php echo $_POST['ritorno'];?>" >
	<div class="container">
	<div class="table-responsive">
	
	
	<table class="table table-striped table-bordered" style="margin: 0 auto; max-width: 40%; margin-top:100px;">
	<tr style="background-color: PaleTurquoise;"><td colspan="3" style="text-align: center;">
	<h4>Sincronizzazione di GAzie con siti internet</h4>
	</td></tr>
	<tr><td colspan="3" style="text-align: left;">
	<p>Scarica ordini: importa ordini dal sito a GAzie</p>
	<p>Aggiorna prodotti: aggiorna le quantità disponibili da Gazie al sito</p>
	</td></tr>
	<tr>
	<td colspan="1" style="width:33%; text-align: left;">
	<input type="submit" name="Return"  onClick="chkSubmit();" value="Indietro">
	</td>
	<td colspan="1" style="width:33%; text-align: left;">
	<input type="submit" name="download"  onClick="chkSubmit();" value="Scarica ordini">
	</td>
	<td colspan="1" style="width:33%; text-align: right;" >
	<input type="submit" name="upload"  onClick="chkSubmit();" value="Aggiorna prodotti">
	</td></tr>
	</table>
	</div>
	</div>
	</form>
	</div>
	<?php
}
?>