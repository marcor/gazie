<?php
function PostCallCATsrl($CATSRL_ENDPOINT, $file_to_send)
{
	$CA_FILE = 'CA_Agenzia_delle_Entrate.pem';

	// initialise the curl request
	$request = curl_init();

	// send a file
	curl_setopt($request, CURLOPT_CAINFO, dirname(__FILE__).'/'.$CA_FILE);
	curl_setopt($request, CURLOPT_POST, true);
	curl_setopt(
		$request,
		CURLOPT_POSTFIELDS,
		array(
		  'file_contents' => curl_file_create($file_to_send)
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

	return $result;
}

function SendFattureElettroniche($zip_fatture)
{
	$CATSRL_ENDPOINT = 'https://fatture.catsrl.it/gazie/RiceviZip.php';

	$result = PostCallCATsrl($CATSRL_ENDPOINT, realpath('../'.$zip_fatture));

	$open_tag = '<PROTS>';
	$close_tag = '</PROTS>';

	return substr($result, strpos($result, $open_tag), strpos($result, $close_tag));
}

function SendFatturaElettronica($xml_fattura)
{
	$CATSRL_ENDPOINT = 'https://fatture.catsrl.it/gazie/RiceviXml.php';

	$result = PostCallCATsrl($CATSRL_ENDPOINT, realpath('../'.$xml_fattura));

	$open_tag = '<PROT>';
	$close_tag = '</PROT>';

	return substr($result, strpos($result, $open_tag), strpos($result, $close_tag));
}

if (!empty($_REQUEST['xml_fattura'])) {
	require('../../library/include/datlib.inc.php');
	$admin_aziend = checkAdmin();
	$file_url = '../../data/files/'.$admin_aziend['codice'].'/'.$_REQUEST['xml_fattura'];
	$IdentificativoSdI = SendFatturaElettronica($file_url); //return IdentificativoSdI
}
?>