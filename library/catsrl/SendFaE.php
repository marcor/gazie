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
	//echo('1-'.print_r($request,true)."<br />\n");
	//echo('2-'.print_r($result,true)."<br />\n");
	//echo('3-'.print_r(curl_error($request),true)."<br />\n");

	// close the session
	curl_close($request);

	return $result;
}

function SendFattureElettroniche($zip_fatture)
{
	$CATSRL_ENDPOINT = 'https://fatture.catsrl.it/gazie/RiceviZip.php';

	$result = PostCallCATsrl($CATSRL_ENDPOINT, realpath($zip_fatture));
	//echo('0-'.$result."<br />\n");

	$IdentificativiSdI = explode(';', $result);

	return $IdentificativiSdI;
}

function SendFatturaElettronica($xml_fattura)
{
	$CATSRL_ENDPOINT = 'https://fatture.catsrl.it/gazie/RiceviXml.php';

	$IdentificativoSdI = PostCallCATsrl($CATSRL_ENDPOINT, realpath($xml_fattura));
	//echo('0-'.$IdentificativoSdI."<br />\n");

	return $IdentificativoSdI;
}

if (!empty($_REQUEST['xml_fattura'])) {
	require('../../library/include/datlib.inc.php');
	$admin_aziend = checkAdmin();
	$file_url = '../../data/files/'.$admin_aziend['codice'].'/'.$_REQUEST['xml_fattura'];
	$IdentificativoSdI = SendFatturaElettronica($file_url);
}
?>