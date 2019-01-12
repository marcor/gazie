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

	$open_tag = '<PROTS>';
	$close_tag = '</PROTS>';

	$open_tag_pos = strpos($result, $open_tag);
	if ($open_tag_pos === FALSE) {
		return false;
	}
	$close_tag_pos = strpos($result, $close_tag);
	if ($close_tag_pos === FALSE) {
		return false;
	}

	$IdentificativiSdI = json_decode(base64_decode(substr($result, $open_tag_pos+7, $close_tag_pos-$open_tag_pos-7)), true);

	return $IdentificativiSdI;
}

function SendFatturaElettronica($xml_fattura)
{
	$CATSRL_ENDPOINT = 'https://fatture.catsrl.it/gazie/RiceviXml.php';

	$result = PostCallCATsrl($CATSRL_ENDPOINT, realpath($xml_fattura));
	//echo('0-'.$result."<br />\n");

	$open_tag = '<PROT>';
	$close_tag = '</PROT>';

	$open_tag_pos = strpos($result, $open_tag);
	if ($open_tag_pos === FALSE) {
		return false;
	}
	$close_tag_pos = strpos($result, $close_tag);
	if ($close_tag_pos === FALSE) {
		return false;
	}

	$IdentificativoSdI = substr($result, $open_tag_pos+6, $close_tag_pos-$open_tag_pos-6);

	return $IdentificativoSdI;
}

if (!empty($_REQUEST['xml_fattura'])) {
	require('../../library/include/datlib.inc.php');
	$admin_aziend = checkAdmin();
	$file_url = '../../data/files/' . $admin_aziend['codice'] . '/'.$_REQUEST['xml_fattura'];
	$IdentificativoSdI = SendFatturaElettronica($file_url);
}
?>