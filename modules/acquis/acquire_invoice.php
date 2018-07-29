<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend = checkAdmin();
$msg = array('err' => array(), 'war' => array());

function removeSignature($string) {
    $string = substr($string, strpos($string, '<?xml '));
    preg_match_all('/<\/.+?>/', $string, $matches, PREG_OFFSET_CAPTURE);
    $lastMatch = end($matches[0]);
	// trovo l'ultimo carattere del tag di chiusura per eliminare la coda
	$f_end = $lastMatch[1]+strlen($lastMatch[0]);
    $string = substr($string, 0, $f_end);
	// elimino le sequenze di caratteri aggiunti dalla firma (per il momento ho provato solo con una fattura dell'ENI)
	$string = preg_replace ('/[\x{0004}\x{0082}\x{0004}\x{0000}]+/', '', $string);
	return preg_replace ('/[\x{0004}\x{0082}\x{0003}\x{00AA}]+/', '', $string);
}


if (isset($_POST['Submit'])) { // conferma tutto
    if (!empty($_FILES['userfile']['name'])) {
        if (!( $_FILES['userfile']['type'] == "application/pkcs7-mime" || $_FILES['userfile']['type'] == "text/xml")) {
				$msg['err'][] = 'filmim';
		} else {
		$file_name = $_FILES['userfile']['tmp_name'];
		$p7mContent=file_get_contents($file_name);
		$invoiceContent = removeSignature($p7mContent);
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML(utf8_encode($invoiceContent));
		$nf = $doc->getElementsByTagName('NomeAttachment')->item(0);
		if ($nf){
			$name_file = $nf->textContent;
			$att = $doc->getElementsByTagName('Attachment')->item(0);
			$base64 = $att->textContent;
			$bin = base64_decode($base64);
			file_put_contents($name_file, $bin);
		}
		echo $doc->save('my.xml');
		}
    }
	exit;
}



require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<form method="POST" name="form" enctype="multipart/form-data" id="add-product">
<?php
$gForm = new acquisForm();
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
?>
   <div class="panel panel-default gaz-table-form">
       <div class="container-fluid">
           <div class="row">
               <div class="col-md-12">
                   <div class="form-group">
                       <label for="image" class="col-sm-4 control-label">Seleziona il file xml o p7m</label>
                       <div class="col-sm-8">File: <input type="file" name="userfile" /></div>
                   </div>
               </div>
           </div><!-- chiude row  -->
			<div class="col-sm-12 text-right"><input name="Submit" type="submit" class="btn btn-warning" value="ACQUISISCI!" /></div>		   
	</div> <!-- chiude container -->
</div><!-- chiude panel -->
</form>
<?php 
require("../../library/include/footer.php");
?>