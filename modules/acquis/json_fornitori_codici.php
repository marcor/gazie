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

require("../../library/include/classes/Autoloader.php");
use GAzie\GAzie as GAzie;
use GAzie\Anagra as Anagra;
use GAzie\Database\FornitoreMagazzino as FornitoreMagazzino;

$gazie = GAzie::factory();
$client_json = $gazie->Json();

function addSupplierCode() { 
	$f = new FornitoreMagazzino();
	$f->id_anagr = 18;
	$f->codice_fornitore='ippo';
	$f->codice_magazzino='001000020001';
	$f->last_price = 1.3;
	$f->save();

	$result = $f->getAllSuppliers('00100002000');
	return $result;
}


switch( $client_json->method() ) {
	case 'POST':
		# Inserisce codice fornitore
		$data = $_POST;
		if ( ! $data['anagr_id'] || ! $data['codice_articolo'] ) {
			$json = array(
				'error' => 'true',
				'message' => 'Invalid request',
			);
			echo $client_json->response( $json, 402 );
			exit;
		}

		$f = new FornitoreMagazzino();
		if ( $f->exist($data['anagr_id'],$data['codice_articolo']) ) {
			$json = array(
				'error' => 'true',
				'message' => 'Exist code supplier',
			);
			echo $client_json->response( $json, 402 );
			exit;
		}
		$f->id_anagr = $data['anagr_id'];
		$f->codice_fornitore= $data['codice_fornitore'];
		$f->codice_magazzino = $data['codice_articolo'];
		$f->last_price = $data['last_price'];
		if ( $f->save() ) {
			$json = array(
				'insert' => 'true',
			);
		} else {
			$client_json->error(403, "Errore inserimento articolo");
		}
		break;
	case 'GET':
		if ( isset( $_GET['codice_magazzino']) ) {
			$codice = $_GET['codice_magazzino'];
			$f = new FornitoreMagazzino();
			$json =	$f->getAllFromCodice($codice);
		} else {
			$client_json->error(405, "Codice articolo non valido");
		}

		break;
	case 'PUT':

		break;
	default:
		
		break;
}


echo $client_json->response( $json );


