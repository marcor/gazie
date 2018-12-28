<?php
function SendFatturaElettronica_CATsrl($fatturaxml, $codiceAzienda)
{
	$CA_FILE = 'CA_Agenzia_delle_Entrate.pem';
	$CATSRL_ENDPOINT = 'https://fatture.catsrl.it/gazie/RiceviXml.php';

	// initialise the curl request
	$request = curl_init();

	// send a file
	curl_setopt($request, CURLOPT_CAINFO, dirname(__FILE__).'/'.$CA_FILE);
	curl_setopt($request, CURLOPT_POST, true);
	curl_setopt(
		$request,
		CURLOPT_POSTFIELDS,
		array(
		  'file_contents' => curl_file_create(realpath('../../data/files/'.$codiceAzienda.'/'.$fatturaxml))
		)
	);
	/**/curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false); // DISATTIVARE AL PROSSIMO RILASCIO CERTIFICATO
	curl_setopt($request, CURLOPT_URL, $CATSRL_ENDPOINT);

	// output the response
	curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($request);
	//echo($result);
	//echo(curl_error($request));

	// close the session
	curl_close($request);

	return substr($result, strpos($result, $open_tag), strpos($result, $close_tag));
}

if (!empty($_REQUEST['fatturaxml'])) {
	require('../../library/include/datlib.inc.php');
	$admin_aziend = checkAdmin();
	SendFatturaElettronica_CATsrl($_REQUEST['fatturaxml'], $admin_aziend['codice']); //return IdentificativoSdI
}
?>