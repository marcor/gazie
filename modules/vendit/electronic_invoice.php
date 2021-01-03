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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

require("../../library/include/electronic_invoice.inc.php");

// recupero i dati
if (isset($_GET['id_tes'])) {   //se viene richiesta la stampa di un solo documento attraverso il suo id_tes
    $id_testata = intval($_GET['id_tes']);
    $testata = gaz_dbi_get_row($gTables['tesdoc'], 'id_tes', $id_testata);
	$where="tipdoc = '". $testata['tipdoc'] ."' AND seziva = ".$testata['seziva']." AND YEAR(datfat) = ".substr($testata['datfat'],0,4)." AND protoc = ".$testata['protoc'];
	if ($testata['tipdoc']=='VCO'){ // in caso di fattura allegata a scontrino mi baso solo sull'id_tes
		$where="id_tes = ".$id_testata;
	}

} else { // in tutti gli altri casi devo passare i valori su $_GET
   if (!isset($_GET['protoc']) || !isset($_GET['year']) || !isset($_GET['seziva'])) {
      header("Location: report_docven.php");
      exit;
   } else {
	  $where="tipdoc LIKE 'F__' AND seziva = ".intval($_GET['seziva'])." AND YEAR(datfat) = ".intval($_GET['year'])." AND protoc = ".intval($_GET['protoc']);
   }
}
if (isset($_GET['reinvia'])) {   //se viene richiesto un reinvio con altro nome faccio avanzare il relativo contatore sulle testate delle fatture
   gaz_dbi_query ("UPDATE ".$gTables['tesdoc']." SET `fattura_elettronica_reinvii`=`fattura_elettronica_reinvii`+1 WHERE ".$where);
}
//recupero i dati
$testate = gaz_dbi_dyn_query("*", $gTables['tesdoc'],$where,'datemi ASC, numdoc ASC, id_tes ASC');
if (isset($_GET['viewxml'])) {   //se viene richiesta una visualizzazione all'interno del browser
	$file_content=create_XML_invoice($testate,$gTables,'rigdoc',false,'from_string.xml');
	$fae_xsl_file = gaz_dbi_get_row($gTables['company_config'], 'var', 'fae_style');
	$doc = new DOMDocument;
	$doc->preserveWhiteSpace = false;
	$doc->formatOutput = true;
 	$doc->loadXML($file_content);
	$xpath = new DOMXpath($doc);
	$xslDoc = new DOMDocument();
	$xslDoc->load("../../library/include/".$fae_xsl_file['val'].".xsl");
	$xslt = new XSLTProcessor();
	$xslt->importStylesheet($xslDoc);
	echo $xslt->transformToXML($doc);
} else { // .... altrimenti faccio il download diretto 
	create_XML_invoice($testate,$gTables);	
}
?>