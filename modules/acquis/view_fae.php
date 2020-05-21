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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

if (isset($_POST['Download'])) { // è stato richiesto il download dell'allegato
		$name = filter_var($_POST['Download'], FILTER_SANITIZE_STRING);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;  filename="'.$name.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize( DATA_DIR . 'files/tmp/' . $name ));
		readfile( DATA_DIR . 'files/tmp/' . $name );
		exit;
}

if (isset($_GET['id_tes'])){
    $id=intval($_GET['id_tes']);
    $fat = gaz_dbi_get_row($gTables['tesdoc'],'id_tes',$id);
	$doc = new DOMDocument;
	$doc->preserveWhiteSpace = false;
	$doc->formatOutput = true;
 	$doc->loadXML($fat['fattura_elettronica_original_content']);
	// ricavo l'allegato, e se presente metterò un bottone per permettere il download
	$nf = $doc->getElementsByTagName('NomeAttachment')->item(0);
	if ($nf){
		$name_file = $nf->textContent;
		$att = $doc->getElementsByTagName('Attachment')->item(0);
		$base64 = $att->textContent;
		$bin = base64_decode($base64);
		file_put_contents( DATA_DIR . 'files/tmp/' . $name_file, $bin );
		echo '<form method="POST"><div class="col-sm-6"> Allegato: <input name="Download" type="submit" class="btn btn-default" value="'.$name_file.'" /></div></form>';
	}
	$xpath = new DOMXpath($doc);
	$xslDoc = new DOMDocument();
	$fae_xsl_file = gaz_dbi_get_row($gTables['company_config'], 'var', 'fae_style');
	$xslDoc->load('../../library/include/' . $fae_xsl_file['val'] . '.xsl');
	$xslt = new XSLTProcessor();
	$xslt->importStylesheet($xslDoc);
	echo $xslt->transformToXML($doc);
}
?>