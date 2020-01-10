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

require("../../library/include/classes/Autoloader.php");
use GAzie\GAzie as GAzie;
use GAzie\Anagra as Anagra;

# Message error token
$error_token = array(
	'error'=> true,
        'message' => 'Token not valid',
);
# Message error parse json 
$error_json = array(
	'error'=> true,
        'message' => 'Data is not array or failed encode json',
);



# Verify token
function verify_token( $token = NULL ) {
	if ( ! isset( $_SESSION['user_name'] )) {
		return False;
	}
	if ( ! isset( $_COOKIE[_SESSION_NAME] ) ) {
		return False;
	}
	if ( $token != NULL && $token != $_COOKIE[_SESSION_NAME] ) {
		return False;
	}
	if ( $token === $_COOKIE[_SESSION_NAME] )
		return True;
	else
		return False;
}		

function toJson( $data ) {
	if ( is_array($data ) ) {
	  return json_encode($data);
	}
	return json_encode( $error_json ); 
}

if ( ! isset($_GET['token']) || ! verify_token( $_GET['token'] ) ) {
	http_response_code(201);
	echo  json_encode( $error_token );
	exit;
}


$gazie = GAzie::factory();


$anagra = new Anagra();
$suppliers = $anagra->getSuppliers();

/*
 Il singolo fornitore il seguente risutalto
{
	"id":"18",
	"ragso1":"",
	"ragso2":"",
	"sedleg":"",
	"legrap_pf_nome":"",
	"legrap_pf_cognome":"",
	"sexper":"G",
	"datnas":"2000-01-01",
	"luonas":"",
	"pronas":"",
	"counas":"",
	"indspe":"",
	"capspe":"",
	"citspe":"",
	"prospe":"",
	"country":"",
	"id_currency":"1",
	"id_language":"1",
	"latitude":"0.00000",
	"longitude":"0.00000",
	"telefo":"",
	"fax":"",
	"cell":"",
	"codfis":"",
	"pariva":"",
	"fe_cod_univoco":"",
	"e_mail":"",
	"pec_email":"",
	"fatt_email":"0",
	"id_SIAN":null,
	"codice":"",
	"id_anagra":"18",
	"descri":" ",
	"print_map":"0",
	"external_resp":"0",
	"external_service_descri":"",
	"id_agente":"0",
	"banapp":"0",
	"portos":"0",
	"spediz":"0",
	"imball":"0",
	"listin":"1",
	"destin":"",
	"id_des":"0",
	"iban":"",
	"sia_code":"",
	"maxrat":"0.00",
	"ragdoc":"S",
	"addbol":"N",
	"speban":"N",
	"spefat":"N",
	"stapre":"N",
	"codpag":"2",
	"sconto":"0.00",
	"sconto_rigo":"0.00",
	"aliiva":"0",
	"ritenuta":"20.0",
	"allegato":"1",
	"cosric":"0",
	"ceedar":"",
	"ceeave":"",
	"paymov":"",
	"operation_type":"",
	"id_assets":"0",
	"sel4esp_art":"0",
	"status":"",
	"status_SIAN":"0",
	"annota":"",
	"adminid":"admin",
	"last_modified":"2012-06-18 20:31:59"
}
 */
$json = array (
	'total'	=> count($suppliers),
	'fornitori' => $suppliers,
);
echo toJson( $json );


