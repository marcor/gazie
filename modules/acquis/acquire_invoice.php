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

/**
 * stripP7MData
 *
 * removes the PKCS#7 header and the signature info footer from a digitally-signed .xml.p7m file using CAdES format.
 *
 * @param ($string, string) the CAdES .xml.p7m file content (in string format).
 * @return (string) an arguably-valid XML string with the .p7m header and footer stripped away.
 */
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

$p7mContent=file_get_contents('myxml.p7m');
$xmlContent = removeSignature($p7mContent);
print_r($xmlContent);
$fp = fopen('my.xml', 'w');
fwrite($fp, $xmlContent);
fclose($fp);
?>