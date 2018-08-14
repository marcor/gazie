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
$admin_aziend=checkAdmin();

if (isset($_GET['id_tes'])){
    $id=intval($_GET['id_tes']);
    $fat = gaz_dbi_get_row($gTables['tesdoc'],'id_tes',$id);
	$doc = new DOMDocument;
	$doc->preserveWhiteSpace = false;
	$doc->formatOutput = true;
 	$doc->loadXML($fat['fattura_elettronica_original_content']);
	$xpath = new DOMXpath($doc);
	$xslDoc = new DOMDocument();
	$xslDoc->load("../../library/include/fatturaordinaria_v1.2.1.xsl");
	$xslt = new XSLTProcessor();
	$xslt->importStylesheet($xslDoc);
	echo $xslt->transformToXML($doc);
}
?>