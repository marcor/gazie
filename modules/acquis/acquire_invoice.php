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
require("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();
$msg = array('err' => array(), 'war' => array());
$tipdoc_conv=array('TD01'=>'AFA','TD02'=>'AFA','TD03'=>'AFA','TD04'=>'AFC','TD05'=>'AFD','TD06'=>'AFA','TD08'=>'AFC','TD24'=>'AFT','TD25'=>'AFT','TD27'=>'AFA');

// ATTENZIONE TD01 deve indicizzare per AFT nel caso in cui ci sono DDT di riferimento all'interno del tracciato, quindi si dovrà gestire questa accezione. Comunque con la prossima versione della fattura elettronica (2.0) saranno da implementare anche altri tipi di doc

$magazz = new magazzForm;
$docOperat = $magazz->getOperators();
$toDo = 'upload';
$f_ex=false; // visualizza file

$send_fae_zip_package = gaz_dbi_get_row($gTables['company_config'], 'var', 'send_fae_zip_package');

function tryBase64Decode($s)
{
	// Check if there are valid base64 characters
	if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) {
		// Decode the string in strict mode and check the results
		try {
			$decoded = base64_decode($s, true);
			if ($decoded !== false) {
				// Encode the string again
				if(base64_encode($decoded) == $s) {
                	return $decoded;
                } else {
					error_log('Charset non gestito in tryBase64Decode ' . print_r($decoded, true), 0);
                	return $decoded;
                }
			}
		} catch (Exception $ex) {
			//$ex->getMessage();
		}
	}

	return $s;
}


function der2smime($file)
{
$to = <<<TXT
MIME-Version: 1.0
Content-Disposition: attachment; filename="smime.p7m"
Content-Type: application/x-pkcs7-mime; smime-type=signed-data; name="smime.p7m"
Content-Transfer-Encoding: base64
\n
TXT;
	$from = file_get_contents($file);
	$to.= chunk_split(base64_encode($from));
	return file_put_contents($file,$to);
}

function extractDER($file)
{
	$tmp = tempnam(DATA_DIR . 'files/tmp/', 'ricder');
	$txt = tempnam(DATA_DIR . 'files/tmp/', 'rictxt');
	$flags = PKCS7_BINARY|PKCS7_NOVERIFY|PKCS7_NOSIGS;
	openssl_pkcs7_verify($file, $flags, $tmp); // estrazione certificato
	@openssl_pkcs7_verify($file, $flags, '/dev/null', array(), $tmp, $txt); // estrazione contenuto - questo potrebbe fallire se il file non è ASN.1 clean
	unlink($tmp);
	$out = file_get_contents($txt);
	unlink($txt);
	return $out;
}

function removeSignature($s)
{
	$start_xml = strpos($s, '<?xml ');
	if ($start_xml !== FALSE) {
		$s = substr($s, $start_xml);
	} else {
		$start_xml = strpos($s, '<?xml-stylesheet ');
		if ($start_xml !== FALSE) {
			$s = substr($s, $start_xml);
		}
	}
	preg_match_all('/<\/.+?>/', $s, $matches, PREG_OFFSET_CAPTURE);
	$lastMatch = end($matches[0]);
	// trovo l'ultimo carattere del tag di chiusura per eliminare la coda
	$f_end = $lastMatch[1]+strlen($lastMatch[0]);
	$s = substr($s, 0, $f_end);
	// elimino le sequenze di caratteri aggiunti dalla firma (ancora da testare approfonditamente)
	$s = preg_replace('/[\x{0004}]{1}[\x{0082}]{1}[\x{0001}-\x{001F}]{1}[\s\S]{1}/i', '', $s);
	$s = preg_replace('/[\x{0004}]{1}[\x{0082}]{1}[\s\S]{1}[\x{0000}]{1}/i', '', $s);
	$s = preg_replace('/[\x{0004}]{1}[\x{0081}]{1}[\s\S]{1}/i', '', $s);
	$s = preg_replace('/[\x{0004}]{1}[\s\S]{1}/i', '', $s);
	$s = preg_replace('/[\x{0003}]{1}[\s\S]{1}/i', '', $s);
	//$s = preg_replace('/[\x{0004}]{1}[A-Za-z]{1}/i', '', $s); // per eliminare tag finale
	return $s;
}

function recoverCorruptedXML($s)
{
	libxml_use_internal_errors(true);
	$xml = @simplexml_load_string($s);
	$errors = libxml_get_errors();
	if (!empty($errors) && is_array($errors) && count($errors)>0) {
		$lines = explode("\n", $s);
		foreach ($errors as $error) {
			if (strpos($error->message, 'Opening and ending tag mismatch')!==false) {
				$tag   = trim(preg_replace('/Opening and ending tag mismatch: (.*) line.*/', '$1', $error->message));
				$line  = $error->line-1;
				$lines[$line] = substr($lines[$line], 0, strpos($lines[$line], '</')).'</'.$tag.'>';
			}
		}
		libxml_clear_errors();
		return implode("\n", $lines);
	} else {
		return $s;
	}
}

function getLastProtocol($type, $year, $sezione) {  
	/* 	questa funzione trova l'ultimo numero di protocollo 
	*	controllando sia l'archivio documenti che il registro IVA acquisti 
	*/
	global $gTables;
    $rs_ultimo_tesdoc = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datreg) = ".$year." AND tipdoc LIKE '" . substr($type, 0, 2) . "_' AND seziva = ".$sezione, "protoc DESC", 0, 1);
    $ultimo_tesdoc = gaz_dbi_fetch_array($rs_ultimo_tesdoc);
    $rs_ultimo_tesmov = gaz_dbi_dyn_query("*", $gTables['tesmov'], "YEAR(datreg) = ".$year." AND regiva = 6 AND seziva = ".$sezione, "protoc DESC", 0, 1);
    $ultimo_tesmov = gaz_dbi_fetch_array($rs_ultimo_tesmov);
    $lastProtocol = 0;
    $lastDatreg = date("Y-m-d");
    if ($ultimo_tesdoc) {
        $lastProtocol = $ultimo_tesdoc['protoc'];
        $lastDatreg = $ultimo_tesdoc['datreg'];
    }
    if ($ultimo_tesmov) {
        if ($ultimo_tesmov['protoc'] > $lastProtocol) {
            $lastProtocol = $ultimo_tesmov['protoc'];
            $lastDatreg = $ultimo_tesmov['datreg'];
        }
    }
    return array('last_protoc'=>$lastProtocol + 1,'last_datreg'=>$lastDatreg);
}

function encondeFornitorePrefix($clfoco,$b=36) {
    $num = intval(substr($clfoco,-6));
	/* con questa funzione ricavo un prefisso di codice articolo che dipende dal codice fornitore */
    $base = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $r = $num % $b;
    $res = $base[$r];
    $q = floor($num / $b);
    while ($q) {
        $r = $q % $b;
        $q = floor($q / $b);
        $res = $base[$r] . $res;
    }
    return $res;
	
}

function existDdT($numddt,$dataddt,$clfoco,$codart="%%") {
	global $gTables;
	/* Questa funzione serve per controllare se è già stato registrato in magazzino il rigo dell'eventuale DdT contenuto nella 
		fattura che stiamo acquisendo mi baso su fornitore, numero, data e, se lo passo, il codice articolo, quando passo $codart 
		faccio una ricerca puntuale sull'articolo specifico
	*/
    $result=gaz_dbi_dyn_query("*", $gTables['tesdoc']. " LEFT JOIN " . $gTables['rigdoc'] . " ON " . $gTables['tesdoc'] . ".id_tes = " . $gTables['rigdoc'] . ".id_tes", "(tipdoc='ADT' OR tipdoc='RDL') AND clfoco = ".$clfoco." AND datemi='".$dataddt."' AND numdoc='".$numddt."' AND codart LIKE '".$codart."'", "id_rig DESC", 0, 1);
    return gaz_dbi_fetch_array($result);
}

if (!isset($_POST['fattura_elettronica_original_name'])) { // primo accesso nessun upload
	$form['fattura_elettronica_original_name'] = '';
	$form['date_ini_D'] = '01';
	$form['date_ini_M'] = date('m', strtotime('last month'));
	$form['date_ini_Y'] = date('Y', strtotime('last month'));
	$form['date_fin_D'] = date('d');
	$form['date_fin_M'] = date('m');
	$form['date_fin_Y'] = date('Y');
	$form['curr_doc'] = 0;
} else { // accessi successivi  
	$form['fattura_elettronica_original_name'] = filter_var($_POST['fattura_elettronica_original_name'], FILTER_SANITIZE_STRING);
	$form['curr_doc'] = intval($_POST['curr_doc']);
	$form['date_ini_D'] = '01';
	$form['date_ini_M'] = date('m');
	$form['date_ini_Y'] = date('Y');
	$form['date_fin_D'] = date('d');
	$form['date_fin_M'] = date('m');
	$form['date_fin_Y'] = date('Y');
	if (!isset($_POST['datreg'])){
		$form['datreg'] = date("d/m/Y");
		$form['seziva'] = 1;
	} else {
		$form['datreg'] = substr($_POST['datreg'],0,10);
		$form['seziva'] = intval($_POST['seziva']);
	}
	if (isset($_POST['Submit_file'])) { // conferma invio upload file
        if (!empty($_FILES['userfile']['name'])) {
            if (!( $_FILES['userfile']['type'] == "application/pkcs7-mime" || $_FILES['userfile']['type'] == "application/pkcs7" || $_FILES['userfile']['type'] == "text/xml")) {
				$msg['err'][] = 'filmim';
			} else {
                if (move_uploaded_file($_FILES['userfile']['tmp_name'], DATA_DIR . 'files/' . $admin_aziend['codice'] . '/' . $_FILES['userfile']['name'])) { // nessun errore
					$form['fattura_elettronica_original_name'] = $_FILES['userfile']['name'];
				} else { // no upload
					$msg['err'][] = 'no_upload';
				}
			}
		} else if (!empty($_POST['selected_SdI'])) {
			require('../../library/' . $send_fae_zip_package['val'] . '/SendFaE.php');
			$FattF = DownloadFattF(array($admin_aziend['country'].$admin_aziend['codfis'] => array('id_SdI' => $_POST['selected_SdI'])));
			if (!empty($FattF) && is_array($FattF) && file_put_contents( DATA_DIR . 'files/' . $admin_aziend['codice'] . '/' . key($FattF), base64_decode($FattF[key($FattF)])) !== FALSE) { // nessun errore
				$form['fattura_elettronica_original_name'] = key($FattF);
			} else { // no upload
				$msg['err'][] = 'no_upload';
			}
		}
	} else if (isset($_POST['Submit_form'])) { // ho  confermato l'inserimento
		$form['pagame'] = intval($_POST['pagame']);
		$form['new_acconcile'] = intval($_POST['new_acconcile']);
        if ($form['pagame'] <= 0 ) {  // ma non ho selezionato il pagamento
			$msg['err'][] = 'no_pagame';
		}
		// faccio i controlli sui righi
		foreach($_POST as $kr=>$vr){
			if (substr($kr,0,7)=='codvat_' && $vr<=0 && $vr !='000000000') {
				$msg['err'][] = 'no_codvat';
			}	
			if (substr($kr,0,7)=='codric_' && $vr<=0 && $vr !='000000000') {
				$msg['err'][] = 'no_codric';
			}	
		}
	} else if (isset($_POST['Download'])) { // faccio il download dell'allegato
		$name = filter_var($_POST['Download'], FILTER_SANITIZE_STRING);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;  filename="'.$name.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize( DATA_DIR . 'files/tmp/' . $name ));
		readfile( DATA_DIR . 'files/tmp/' . $name );
		exit;
	} else if (isset($_POST['Submit_list'])) { // ho richiesto l'elenco delle fatture passive
		$form['date_ini_D'] = str_pad($_POST['date_ini_D'], 2, '0', STR_PAD_LEFT);
		$form['date_ini_M'] = str_pad($_POST['date_ini_M'], 2, '0', STR_PAD_LEFT);
		$form['date_ini_Y'] = $_POST['date_ini_Y'];
		$form['date_fin_D'] = str_pad($_POST['date_fin_D'], 2, '0', STR_PAD_LEFT);
		$form['date_fin_M'] = str_pad($_POST['date_fin_M'], 2, '0', STR_PAD_LEFT);
		$form['date_fin_Y'] = $_POST['date_fin_Y'];
		$FattF = array();
		$where1 = " tipdoc LIKE 'A%' AND fattura_elettronica_original_name!='' AND datreg BETWEEN '" . $form['date_ini_Y'] . '-' . $form['date_ini_M'] . '-' . $form['date_ini_D'] . "' AND '" . $form['date_fin_Y'] . '-' . $form['date_fin_M'] . '-' . $form['date_fin_D'] . "'";
		$risultati = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where1);
		if ($risultati) {
			while ($r = gaz_dbi_fetch_array($risultati)) {
				$FattF[] = $r['fattura_elettronica_original_name'];
			}
		}
		require('../../library/' . $send_fae_zip_package['val'] . '/SendFaE.php');
		$AltreFattF = ReceiveFattF(array($admin_aziend['country'].$admin_aziend['codfis'] => array('fattf' => $FattF, 'ini_date' => $form['date_ini_Y'] . '-' . $form['date_ini_M'] . '-' . $form['date_ini_D'], 'fin_date' => $form['date_fin_Y'] . '-' . $form['date_fin_M'] . '-' . $form['date_fin_D'])));
	}

	$tesdoc = gaz_dbi_get_row($gTables['tesdoc'], 'BINARY fattura_elettronica_original_name', $form["fattura_elettronica_original_name"]);
	if ($tesdoc && !empty($form['fattura_elettronica_original_name'])) { // c'è anche sul database, è una modifica
		$toDo = 'update';
		$form['datreg'] = gaz_format_date($tesdoc['datreg'], false, false);
		$form['seziva'] = $tesdoc['seziva'];
		$msg['err'][] = 'file_exists';
	} elseif (!empty($form['fattura_elettronica_original_name'])) { // non c'è sul database è un inserimento
		$toDo = 'insert';
		// INIZIO acquisizione e pulizia file xml o p7m
		$file_name = DATA_DIR . 'files/' . $admin_aziend['codice'] . '/' . $form['fattura_elettronica_original_name'];
		if (!isset($_POST['datreg'])){
			$form['datreg'] = date("d/m/Y",filemtime($file_name));
		}
		$p7mContent = @file_get_contents($file_name);
		$p7mContent = tryBase64Decode($p7mContent);


		$tmpfatt = tempnam(DATA_DIR . 'files/tmp/', 'ricfat');
		file_put_contents($tmpfatt, $p7mContent);

		if (FALSE !== der2smime($tmpfatt)) {
			$cert = tempnam(DATA_DIR . 'files/tmp/', 'ricpem');
			$retn = openssl_pkcs7_verify($tmpfatt, PKCS7_NOVERIFY, $cert);
			unlink($cert);
			if (!$retn) {
				//unlink($tmpfatt);
				//echo "Error verifying PKCS#7 signature in {$file_name}";
				error_log('errore in Verifica firma PKCS#7', 0);
				//echo 'errore in Verifica firma PKCS#7';
				//return false;
			}

			$isFatturaElettronicaSemplificata = false;
			$fatt = extractDER($tmpfatt);
			if (empty($fatt)) {
				$test = @base64_decode(file_get_contents($tmpfatt));
				// Salto lo header (INDISPENSABILE perché la regexp funzioni sempre)
				if (strpos($test, 'FatturaElettronicaSemplificata') !== FALSE) {
					$isFatturaElettronicaSemplificata = true;
					if (preg_match('#(<[^>]*FatturaElettronicaSemplificata.*</[^>]*FatturaElettronicaSemplificata>)#', substr($test, 54), $gregs)) {
						$fatt = '<'.'?'.'xml version="1.0"'.'?'.'>' . $gregs[1]; // RECUPERO INTESTAZIONE XML
					}
				} else {
					if (preg_match('#(<[^>]*FatturaElettronica.*</[^>]*FatturaElettronica>)#', substr($test, 54), $gregs)) {
						$fatt = '<'.'?'.'xml version="1.0"'.'?'.'>' . $gregs[1]; // RECUPERO INTESTAZIONE XML
					}
				}
			} else {
				if (strpos($p7mContent, 'FatturaElettronicaSemplificata') !== FALSE) {
					$isFatturaElettronicaSemplificata = true;
				}
			}
		}
		unlink($tmpfatt);


		if (!empty($fatt)) {
			$invoiceContent = $fatt;
		} else {
			$invoiceContent = removeSignature($p7mContent);
		}


		$xml = new DOMDocument;
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;
		if (FALSE === @$xml->loadXML(utf8_encode($invoiceContent))) {
			// elimino le sequenze di caratteri non stampabili aggiunti dalla firma (da testare approfonditamente)
			$invoiceContent = preg_replace('/[[:^print:]]/', "", $invoiceContent);
			if (FALSE === @$xml->loadXML(utf8_encode($invoiceContent))) {
				$xml->loadXML(recoverCorruptedXML($invoiceContent));
			}
		}
		$xpath = new DOMXpath($xml);
		$f_ex=true;
	} else {
		$toDo = 'upload';
	}

	// definisco l'array dei righi 
	$form['rows'] = array();

	$anagra_with_same_pi = false; // sarà true se è una anagrafica esistente ma non è un fornitore sul piano dei conti 

 
	if ($f_ex) { // non ho errori di file,  faccio altri controlli sul contenuto del file

	if (empty($form['curr_doc'])) {
		$docs = $xml->getElementsByTagName('FatturaElettronicaBody');
		if (count($docs) == 1) {
			$form['curr_doc'] = 1;
		}
	}

	if (!empty($form['curr_doc'])) {

		$ndoc = 0;
		$docs = $xml->getElementsByTagName('FatturaElettronicaBody');
		foreach ($docs as $doc) {
			$ndoc++;
			if ($ndoc == $form['curr_doc']) break;
		}

		// INIZIO CONTROLLI CORRETTEZZA FILE
		$val_err = libxml_get_errors(); // se l'xml è valido restituisce 1
		libxml_clear_errors();
		if (empty($val_err)){
			/* INIZIO CONTROLLO NUMERO DATA, ovvero se nonostante il nome del file sia diverso il suo contenuto è già stato importato e già c'è uno con lo stesso tipo_documento-numero_documento-anno-fornitore 
			*/ 
			$tipdoc=$tipdoc_conv[$xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/TipoDocumento")->item(0)->nodeValue];
			$datdoc=$xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/Data")->item(0)->nodeValue;
			$numdoc=$xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/Numero")->item(0)->nodeValue;
			if ($isFatturaElettronicaSemplificata) {
				$codiva=$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/IdFiscaleIVA/IdCodice")->item(0)->nodeValue;
				if ($xpath->query("//FatturaElettronicaHeader/CedentePrestatore/CodiceFiscale")->length>=1){
					$codfis=$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/CodiceFiscale")->item(0)->nodeValue;
				} else {
					$codfis=$codiva;
				}
			} else {
				$codiva=$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0)->nodeValue;
				if ($xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/CodiceFiscale")->length>=1){
					$codfis=$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/CodiceFiscale")->item(0)->nodeValue;
				} else {
					$codfis=$codiva;
				}
			}
			$r_invoice=gaz_dbi_dyn_query("*", $gTables['tesdoc']. " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['clfoco'] . ".id_anagra = " . $gTables['anagra'] . ".id", "tipdoc='".$tipdoc."' AND (pariva = '".$codiva."' OR codfis = '".$codfis."') AND datfat='".$datdoc."' AND numfat='".$numdoc."'", "id_tes", 0, 1);
			$exist_invoice=gaz_dbi_fetch_array($r_invoice);
			if ($exist_invoice) { // esiste un file che pur avendo un nome diverso è già stato acquisito ed ha lo stesso numero e data 
				$msg['err'][] = 'same_content';
				$f_ex=false; // non è visualizzabile
			}
			// FINE CONTROLLO NUMERO DATA	
			if ($xml->getElementsByTagName("FatturaElettronicaHeader")->length < 1) { // non esiste il nodo <FatturaElettronicaHeader>
				$msg['err'][] = 'invalid_fae';
				$f_ex=false; // non è visualizzabile
			} else if ( ( !$isFatturaElettronicaSemplificata && @$xpath->query("//FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0)->nodeValue <> $admin_aziend['pariva'] && @$xpath->query("//FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/CodiceFiscale")->item(0)->nodeValue <> $admin_aziend['codfis'] ) || 
						 ( $isFatturaElettronicaSemplificata && @$xpath->query("//FatturaElettronicaHeader/CessionarioCommittente/IdentificativiFiscali/IdFiscaleIVA/IdCodice")->item(0)->nodeValue <> $admin_aziend['pariva'] && @$xpath->query("//FatturaElettronicaHeader/CessionarioCommittente/IdentificativiFiscali/CodiceFiscale")->item(0)->nodeValue <> $admin_aziend['codfis'] ) ) { // ne partita IVA ne codice fiscale coincidono con quella della azienda che sta acquisendo la fattura 
				$msg['err'][] = 'not_mine';
				$f_ex=false; // non la visualizzo perché non è una mia fattura
			} else {
				// controllo se ho il fornitore in archivio
				$form['partner_cost']=$admin_aziend['impacq'];
				$form['partner_vat']=$admin_aziend['preeminent_vat'];
				$form['pariva'] = $codiva;
				$form['codfis'] = $codfis;
				$anagrafica = new Anagrafica();
                $partner_with_same_pi = $anagrafica->queryPartners('*', "codice BETWEEN " . $admin_aziend['masfor'] . "000000 AND " . $admin_aziend['masfor'] . "999999 AND pariva = '" . $form['pariva'] . "'", "CASE WHEN codfis LIKE '" . $form['codfis'] . "' THEN 1 ELSE 0 END DESC");
                if ($partner_with_same_pi) { // ho già il fornitore sul piano dei conti
					$form['clfoco'] = $partner_with_same_pi[0]['codice'];
					if ($partner_with_same_pi[0]['cosric']>100000000) { // ho un costo legato al fornitore 
						$form['partner_cost'] = $partner_with_same_pi[0]['cosric']; // costo legato al fornitore
					}				
					$form['pagame'] = $partner_with_same_pi[0]['codpag']; // condizione di pagamento
					$form['new_acconcile']=0;
					if ( $partner_with_same_pi[0]['aliiva'] > 0 ){
						$form['partner_vat'] = $partner_with_same_pi[0]['aliiva']; 
					}
                } else { // se non ho già un fornitore sul piano dei conti provo a vedere nelle anagrafiche
                    $rs_anagra_with_same_pi = gaz_dbi_query_anagra(array("*"), $gTables['anagra'], array("pariva" => "='" . $form['pariva'] . "'"), array("pariva" => "DESC"), 0, 1);
                    $anagra_with_same_pi = gaz_dbi_fetch_array($rs_anagra_with_same_pi);
                    if ($anagra_with_same_pi) { // c'è già un'anagrafica con la stessa PI non serve reinserirlo ma dovrò metterlo sul piano dei conti
						$msg['war'][] = 'no_suppl';
                    } else { // non c'è nemmeno nelle anagrafiche allora attingerò i dati da questa fattura
						$msg['war'][] = 'no_anagr';
						
					}
                }
			}
			
		} else {
			$msg['err'][] = 'invalid_xml';
			$f_ex=false; // non è visualizzabile
		}
		// FINE CONTROLLI SU FILE

		if ($f_ex) { // non ho errori  vincolanti sul file posso proporre la visualizzazione
			/*	Prendo i valori delle ritenute d'acconto che purtroppo sul tracciato ufficiale non viene distinto a livello di linee pertanto devo ricavarmele */
			$tot_ritenute = ($doc->getElementsByTagName("ImportoRitenuta")->length >= 1 ? $doc->getElementsByTagName('ImportoRitenuta')->item(0)->nodeValue : 0 );
			$ali_ritenute = ($doc->getElementsByTagName("AliquotaRitenuta")->length >= 1 ? $doc->getElementsByTagName('AliquotaRitenuta')->item(0)->nodeValue : 0 );
			// mi calcolo le eventuali ritenute relative alle casse previdenziali da annotare sotto quando aggiungerò i righi tipo 4 
			$ritenute_su_casse = 0;
			$DatiCassaPrevidenziale = $doc->getElementsByTagName('DatiCassaPrevidenziale');
			foreach ($DatiCassaPrevidenziale as $item) { // attraverso per trovare gli elementi cassa previdenziale 
				if ($item->getElementsByTagName("Ritenuta")->length >= 1 && $item->getElementsByTagName('Ritenuta')->item(0)->nodeValue=='SI'){
					// su questo contributo cassa ho la ritenuta
					$ritenute_su_casse += round($item->getElementsByTagName('ImportoContributoCassa')->item(0)->nodeValue*$ali_ritenute/100,2); 
				} 
			}
			// calcolo il residuo ritenute che sono costretto a mettere sulla prima linea questa è sicuramente una carenza strutturale del tracciato che non fa alcun riferimento alle linee 
			$res_ritenute=round($tot_ritenute-$ritenute_su_casse,2); 
			
			/* mi serve per tenere traccia della linea con l'importo più grosso in modo da poterci sommare gli eventuali errori di arrotondamento sul totale imponibile
			 dovuto alla diversità del metodo di calcolo usato in gazie*/
			$max_val_linea=1;
			$tot_imponi=0.00;
			/* 
			INIZIO creazione array dei righi con la stessa nomenclatura usata sulla tabella rigdoc
			a causa della mancanza di rigore del tracciato ufficiale siamo costretti a crearci un castelletto conti e iva 
			al fine contabilizzare direttamente qui senza passare per la contabilizzazione di GAzie e tentare di creare dei
			righi documenti la cui somma coincida con il totale imponibile riportato sul tracciato 
			*/
			$DettaglioLinee = $doc->getElementsByTagName('DettaglioLinee');
			$nl=0;
			$nl_NumeroLinea = []; // matrice che conterrà i riferimenti tra $nl e il NumeroLinea, da utilizzare per assegnare Numero/DataDDT se presenti
			foreach ($DettaglioLinee as $item) {
				$nl++;
				// assegno i riferimenti tra $nl e il NumeroLinea
				// succede di tutto: se NumeroLinea è doppio lo dobbiamo controllare...
				$NumLin='GAZ'.$nl;
				if ($item->getElementsByTagName("NumeroLinea")->length >= 1){ // c'è un riferimento al numero
					$NumLin=$item->getElementsByTagName('NumeroLinea')->item(0)->nodeValue;
					if (array_key_exists($NumLin,$nl_NumeroLinea)){ // controllo, e se c'è un numero duplicato :( ne invento uno pur di non perdere il riferimento
						$NumLin .= '-'.$nl;
					}
				}            
				$nl_NumeroLinea[$NumLin]=$nl;
				
				if ($item->getElementsByTagName("CodiceTipo")->length >= 1) {
					$form['rows'][$nl]['codice_fornitore'] = trim($item->getElementsByTagName('CodiceTipo')->item(0)->nodeValue).'_'.trim($item->getElementsByTagName('CodiceValore')->item(0)->nodeValue); 
				} else {
					$form['rows'][$nl]['codice_fornitore'] = ($item->getElementsByTagName("CodiceArticolo")->length >= 1 ? $item->getElementsByTagName('CodiceArticolo')->item(0)->nodeValue : '' );
				}
				// Elimino spazi dal codice fornitore creato
				$form['rows'][$nl]['codice_fornitore'] = preg_replace("/\s+/","_",$form['rows'][$nl]['codice_fornitore']);
				// vedo se ho un codice_fornitore in gaz_artico
				$artico = gaz_dbi_get_row($gTables['artico'], 'codice_fornitore', $form['rows'][$nl]['codice_fornitore']);
				$form['rows'][$nl]['codart'] = ($artico && !empty($form['rows'][$nl]['codice_fornitore']))?$artico['codice']:'';
				$form['rows'][$nl]['descri'] = $item->getElementsByTagName('Descrizione')->item(0)->nodeValue; 
				if ($item->getElementsByTagName("Quantita")->length >= 1) {
					$form['rows'][$nl]['quanti'] = $item->getElementsByTagName('Quantita')->item(0)->nodeValue; 
					$form['rows'][$nl]['tiprig'] = 0;
				} else {
					$form['rows'][$nl]['quanti'] = '';
					$form['rows'][$nl]['tiprig'] = 1; // rigo forfait
				}
				$form['rows'][$nl]['unimis'] =  ($item->getElementsByTagName('UnitaMisura')->length >= 1 ? $item->getElementsByTagName('UnitaMisura')->item(0)->nodeValue :	'');
				$form['rows'][$nl]['prelis'] = $item->getElementsByTagName('PrezzoUnitario')->item(0)->nodeValue;
				
				// Antonio Germani prendo il tipo di cessione prestazione che mi servirà per le eccezioni delle anomalie
				$form['rows'][$nl]['tipocessprest'] = $item->getElementsByTagName('TipoCessionePrestazione')->length >= 1 ? $item->getElementsByTagName('TipoCessionePrestazione')->item(0)->nodeValue : '';
				
				// inizio applicazione sconto su rigo
				$form['rows'][$nl]['sconto'] = 0;
				$acc_sconti=array();
				if ($item->getElementsByTagName("ScontoMaggiorazione")->length >= 1) { // ho uno sconto/maggiorazione
					$acc_sconti=array();
					$sconti_forfait=array();
					$sconto_maggiorazione=$item->getElementsByTagName("ScontoMaggiorazione");
					foreach ($sconto_maggiorazione as $sconti) { // potrei avere più elementi 2.2.1.10 <ScontoMaggiorazione>
						if ($form['rows'][$nl]['prelis'] < 0.00001) { // se trovo l'elemento 2.2.1.9 <PrezzoUnitario> a zero calcolo lo sconto a forfait
							$sconti_forfait[]=($sconti->getElementsByTagName('Tipo')->item(0)->nodeValue == 'SC' ? -$sconti->getElementsByTagName('Importo')->item(0)->nodeValue : $sconti->getElementsByTagName('Importo')->item(0)->nodeValue);
						} elseif ($sconti->getElementsByTagName("Importo")->length >= 1 && $item->getElementsByTagName('Importo')->item(0)->nodeValue >= 0.00001){ 
							// calcolo la percentuale di sconto partendo dall'importo del rigo e da quello dello sconto, il funzionamento di GAzie prevede la percentuale e non l'importo dello sconto 
							$tot_rig= (!empty($form['rows'][$nl]['quanti']) && $form['rows'][$nl]['quanti']!=0) ? $form['rows'][$nl]['quanti']*$form['rows'][$nl]['prelis'] : $form['rows'][$nl]['prelis'];
							$acc_sconti[]=(!empty($form['rows'][$nl]['quanti']) && intval($form['rows'][$nl]['quanti'])>1) ? $form['rows'][$nl]['quanti']*$item->getElementsByTagName('Importo')->item(0)->nodeValue*100/$tot_rig : $item->getElementsByTagName('Importo')->item(0)->nodeValue*100/$tot_rig;
							//$form['rows'][$nl]['sconto']=$item->getElementsByTagName('Importo')->item(0)->nodeValue*100/$tot_rig;  
						} elseif($sconti->getElementsByTagName("Percentuale")->length >= 1 && $sconti->getElementsByTagName('Percentuale')->item(0)->nodeValue>=0.00001){ // ho una percentuale accodo quella
							$acc_sconti[]=($sconti->getElementsByTagName('Tipo')->item(0)->nodeValue == 'SC' ? $sconti->getElementsByTagName('Percentuale')->item(0)->nodeValue : -$sconti->getElementsByTagName('Percentuale')->item(0)->nodeValue);
						}				
					}
					if (count($sconti_forfait) > 0) {
						$sf=0;
						foreach($sconti_forfait as $scf){ // attraverso l'accumulatore di sconti forfait per ottenerne il totale
							$sf += $scf;
						}
						$form['rows'][$nl]['prelis'] = $sf;
					} else {
						$is=1;
						foreach($acc_sconti as $vsc){ // attraverso l'accumulatore di sconti per ottenerne uno solo
							$is *=(1-$vsc/100);
						}
						$form['rows'][$nl]['sconto'] = 100*(1-$is);
					}
				}
				$form['rows'][$nl]['pervat'] = $item->getElementsByTagName('AliquotaIVA')->item(0)->nodeValue;
				// se ho un residuo di ritenuta d'acconto valorizzo con l'aliquota di cui sopra
				$form['rows'][$nl]['ritenuta'] = 0;
				// calcolo l'importo del rigo 
				if ($form['rows'][$nl]['tiprig']==0){
					$form['rows'][$nl]['amount']=CalcolaImportoRigo($form['rows'][$nl]['quanti'],$form['rows'][$nl]['prelis'],array($form['rows'][$nl]['sconto']));
				} else {
					$form['rows'][$nl]['amount']=CalcolaImportoRigo(1,$form['rows'][$nl]['prelis'],array($form['rows'][$nl]['sconto']));
				}

				// tengo traccia del NumeroLinea e se il rigo è descrittivo 
				$form['rows'][$nl]['numrig'] = $item->getElementsByTagName('NumeroLinea')->item(0)->nodeValue;
				$form['rows'][$nl]['is_descri'] = ($form['rows'][$nl]['prelis']<0.00001)?1:false;
				
				$tot_imponi += $form['rows'][$nl]['amount'];
				if (!empty($form['rows'][$nl]) && !empty($form['rows'][$max_val_linea]) && $form['rows'][$nl]['amount']>$form['rows'][$max_val_linea]['amount']){ // è una linea con valore più alto delle precedenti
					$max_val_linea=$nl;
				}
				if (round($res_ritenute,2)>=0.01){
					$res_ritenute -= $form['rows'][$nl]['amount']*$ali_ritenute/100;
					if (round($res_ritenute,2) >= 0) { // setto l'aliquota ritenuta ma solo se c'è stata capienza
						$form['rows'][$nl]['ritenuta'] = $ali_ritenute;
					}
				}
				$post_nl = $nl-1;
				if (empty($_POST['Submit_file'])) { // l'upload del file è già avvenuto e sono nei refresh successivi quindi riprendo i valori scelti e postati dall'utente
					$form['codart_'.$post_nl] = preg_replace("/[^A-Za-z0-9_]i/", '',substr($_POST['codart_'.$post_nl],0,15));
					$form['rows'][$nl]['codart']=$form['codart_'.$post_nl];
					$form['codric_'.$post_nl] = intval($_POST['codric_'.$post_nl]);
					$form['codvat_'.$post_nl] = intval($_POST['codvat_'.$post_nl]);
				} else { 
					if (isset( $form['rows'][$nl]['codart'])){
						$form['codart_'.$post_nl] = $form['rows'][$nl]['codart'];
					} else {
						$form['rows'][$nl]['codart'] = '';
						$form['codart_'.$post_nl] ='';
					}			
					/* al primo accesso dopo l'upload del file propongo:
					   - la prima data di registrazione utile considerando quella di questa fattura e l'ultima registrazione
					   - i costi sulle linee (righe) in base al fornitore
					   - le aliquote IVA in base a quanto trovato sul database e sul riepilogo del tracciato 
					*/
					/* NON SEMBRA ESSERE UTILIZZATA - DA RIVEDERE*/
					$df = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/Data")->item(0)->nodeValue;
					// trovo l'ultima data di registrazione
					$lr = getLastProtocol('AF_',substr($df,0,4),1)['last_datreg'];/**/
					if ($lr > $df) {
						$form['datreg'] = gaz_format_date($lr, false, true);
					}
					$form['codric_'.$post_nl] = $form['partner_cost'];
					if (preg_match('/TRASP/i',strtoupper($form['rows'][$nl]['descri']))) { // se sulla descrizione ho un trasporto lo propongo come costo d'acquisto
						$form['codric_'.$post_nl] = $admin_aziend['cost_tra'];
					}
					$expect_vat = gaz_dbi_get_row($gTables['aliiva'], 'codice', $form['partner_vat']); // analizzo le possibilità 
					// analizzo le possibilità 
					// controllo se ho uno split payment
					$yes_split = false;
					if ($xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiBeniServizi/DatiRiepilogo/EsigibilitaIVA")->length >= 1) {
						$yes_split = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiBeniServizi/DatiRiepilogo/EsigibilitaIVA")->item(0)->nodeValue;
					}
					if ($yes_split == 'S') {
						$rs_split_vat = gaz_dbi_dyn_query("*", $gTables['aliiva'], "aliquo=" . $form['rows'][$nl]['pervat'] . " AND tipiva='T'", "codice ASC", 0, 1);
						$split_vat = gaz_dbi_fetch_array($rs_split_vat);
						$form['codvat_'.$post_nl] = $split_vat['codice'];
					} elseif ( $expect_vat['aliquo'] == $form['rows'][$nl]['pervat']) { // coincide con le aspettative
						$form['codvat_'.$post_nl] = $expect_vat['codice'];
					} else { // non è quella che mi aspettavo allora provo a trovarne una tra quelle con la stessa aliquota
						$filter_vat = "aliquo=" . $form['rows'][$nl]['pervat'];
						@$Natura = $item->getElementsByTagName('Natura')->item(0)->nodeValue;
						if (!empty($Natura)) {
							$filter_vat.= " AND fae_natura='" . $Natura . "'";
						}
						$rs_last_codvat = gaz_dbi_dyn_query("*", $gTables['aliiva'], $filter_vat . " AND tipiva<>'T'", "codice ASC", 0, 1);
						$last_codvat = gaz_dbi_fetch_array($rs_last_codvat);
						if ($last_codvat) {
							$form['codvat_'.$post_nl] = $last_codvat['codice'];
						} else {
							$form['codvat_'.$post_nl] = 'non trovata';
						}
					}
				}
			}

			/* 
			Se la fattura è derivante da un DdT aggiungo i relativi  elementi  all'array dei righi  
			*/
			//print_r($nl_NumeroLinea);
			$anomalia="";
			if ($doc->getElementsByTagName('DatiDDT')->length>=1) { 
				// quando ci sono dei DdT capita che il rigo che precede sia la descrizione del seguente allora faccio un primo attraversamento dei riferimenti ai righi perchè capita che alcuni righi descrittivi che precedono siano comunque riferiti a ddt
				$ddt=$doc->getElementsByTagName('DatiDDT');
				$ctrl_NumeroDDT='';
				$acc_DataDDT='';
				
				foreach ($ddt as $vd) { // attraverso DatiDDT
					$vr=$vd->getElementsByTagName('RiferimentoNumeroLinea');
					$numddt=preg_replace('/\D/', '',$vd->getElementsByTagName('NumeroDDT')->item(0)->nodeValue);
					$dataddt=$vd->getElementsByTagName('DataDDT')->item(0)->nodeValue;
					foreach ($vr as $vdd) { // attraverso RiferimentoNumeroLinea
						$nl = $nl_NumeroLinea[$vdd->nodeValue];
						if ($numddt!=$ctrl_NumeroDDT){ // è cambiato controllo, se il rigo che precede questo è un descritto e non ha un riferimento a ddt lo assegno a questo  
							if (isset($form['rows'][$nl-1]['is_descri'])&&$form['rows'][$nl-1]['is_descri']){   
								$form['rows'][$nl-1]['NumeroDDT']=$numddt;
								$form['rows'][$nl-1]['DataDDT']=$dataddt;
								$form['rows'][$nl-1]['exist_ddt']=false;
								// è stato assegnato ad un DdT lo rimuovo dall'array $nl_NumeroLinea
								unset($nl_NumeroLinea[$form['rows'][$nl-1]['numrig']]);
							}
						}
						if (isset($form['clfoco'])&&existDdT($numddt,$dataddt,$form['clfoco'])){
							$form['rows'][$nl]['exist_ddt']=existDdT($numddt,$dataddt,$form['clfoco']);
							
						} else {
							$form['rows'][$nl]['exist_ddt']=false;
						}
						$form['rows'][$nl]['NumeroDDT']=$numddt;
						$form['rows'][$nl]['DataDDT']=$dataddt;
						// è stato assegnato ad un DdT lo rimuovo dall'array $nl_NumeroLinea in modo da poter, eventualmente trattare questi successivamente
						unset($nl_NumeroLinea[$form['rows'][$nl]['numrig']]); 
						$ctrl_NumeroDDT=$numddt;
					}
					$ctrl_NumeroDDT=$numddt;
					$ctrl_DataDDT=$dataddt;
				}
				
				foreach($nl_NumeroLinea as $k=>$v){ // in questo mi ritrovo i righi non assegnati ai ddt specifici (potrebbero essere anche tutti), alcune fatture malfatte non specificano i righi! 
					// in $v ho l'indice del rigo non assegnato questa è una anomalia e la segnalo
					$anomalia="Anomalia";
					if (isset($form['clfoco'])&&existDdT($numddt,$dataddt,$form['clfoco'])){
						$anomalia="AnomaliaExistDdt";
					}
					$form['rows'][$v]['NumeroDDT']=$numddt;
					$form['rows'][$v]['DataDDT']=$dataddt;
					if ($form['rows'][$v]['tipocessprest']=="AC"){ // Antonio Germani - le spese fanno eccezione e quindi tolgo l'anomalia
						$anomalia="";
					}
					if (isset($form['clfoco'])&&existDdT($numddt,$dataddt,$form['clfoco'])){
						$form['rows'][$v]['exist_ddt']=existDdT($numddt,$dataddt,$form['clfoco']);
					} else {
						$form['rows'][$v]['exist_ddt']=false;
					}
				}
			}
			$linekeys=array_keys($form['rows']);
			$nl=end($linekeys); // trovo l'ultima linea, mi servirà per accodare CassaPrevidenziale, sconti, ecc

			if ($numdoc==$numddt AND $datdoc==$dataddt){ // se fattura e ddt hanno stesso numero e data modifico l'anomalia
				$anomalia = "AnomaliaDDT=FAT";
			}
			/*
				QUI TRATTERO' gli elementi <DatiCassaPrevidenziale> come righi accodandoli ad essi su rigdoc (tipdoc=4) 
			*/
			foreach ($DatiCassaPrevidenziale as $item) { // attraverso per trovare gli elementi cassa previdenziale
				$nl++;
				$form['rows'][$nl]['codice_fornitore'] = $item->getElementsByTagName('TipoCassa')->item(0)->nodeValue;
				$form['rows'][$nl]['tiprig'] = 4;
				// carico anche la descrizione corrispondente dal file xml
				$xml = simplexml_load_file('../../library/include/fae_tipo_cassa.xml');
				foreach ($xml->record as $v) {
					$selected = '';
					if ($v->field[0] == $form['rows'][$nl]['codice_fornitore']) {
						$form['rows'][$nl]['descri']= 'Contributo '.strtolower($v->field[1]);
					}
				}
				$form['rows'][$nl]['unimis'] = '';
				$form['rows'][$nl]['quanti'] = '';
				$form['rows'][$nl]['sconto'] = 0;
				$form['rows'][$nl]['provvigione'] = $item->getElementsByTagName('AlCassa')->item(0)->nodeValue; // così come per le vendite uso il campo provvigioni per mettere l'aliquota della cassa previdenziale (evidenziato anche sui commenti del database)
				if ($item->getElementsByTagName('ImponibileCassa')->length>=1) {
					$form['rows'][$nl]['prelis'] = $item->getElementsByTagName('ImponibileCassa')->item(0)->nodeValue;
				} else {
					// non ho l'imponibile base di calcolo, allora lo ricavo dall'importo del contributo e dall'aliquota
					$form['rows'][$nl]['prelis'] = round($item->getElementsByTagName('ImportoContributoCassa')->item(0)->nodeValue*100/$form['rows'][$nl]['provvigione'],2);
				}
				$form['rows'][$nl]['amount'] = $form['rows'][$nl]['prelis'];
				$tot_imponi += round($form['rows'][$nl]['amount']*$form['rows'][$nl]['provvigione']/100,2);
				$form['rows'][$nl]['pervat'] = $item->getElementsByTagName('AliquotaIVA')->item(0)->nodeValue;
				$form['rows'][$nl]['ritenuta']='';
				if ($item->getElementsByTagName("Ritenuta")->length >= 1 && $item->getElementsByTagName('Ritenuta')->item(0)->nodeValue=='SI'){
					// su questo contributo cassa ho la ritenuta
					$form['rows'][$nl]['ritenuta']= $ali_ritenute; 
				} 
				$post_nl = $nl-1;
				if (empty($_POST['Submit_file'])) { // l'upload del file è già avvenuto e sono nei refresh successivi quindi riprendo i valori scelti e postati dall'utente
					$form['codart_'.$post_nl] = preg_replace("/[^A-Za-z0-9_]i/", '',substr($_POST['codart_'.$post_nl],0,15));
					$form['codric_'.$post_nl] = intval($_POST['codric_'.$post_nl]);
					$form['codvat_'.$post_nl] = intval($_POST['codvat_'.$post_nl]);
				} else {
					if (isset( $form['rows'][$nl]['codart'])){
						$form['codart_'.$post_nl] = $form['rows'][$nl]['codart'];
					} else {
						$form['rows'][$nl]['codart'] = '';
						$form['codart_'.$post_nl] ='';
					}			
					/* al primo accesso dopo l'upload del file propongo:
				   - i costi sulle linee (righe) in base al fornitore
				   - le aliquote IVA in base a quanto trovato sul database e sul riepilogo del tracciato 
					*/
					$form['codric_'.$post_nl] = $form['partner_cost'];
					$expect_vat = gaz_dbi_get_row($gTables['aliiva'], 'codice', $form['partner_vat']);
					// analizzo le possibilità 
					if ( $expect_vat['aliquo'] == $form['rows'][$nl]['pervat']) { // coincide con le aspettative
						$form['codvat_'.$post_nl] = $expect_vat['codice'];
					} else { // non è quella che mi aspettavo allora provo a trovarne una tra quelle con la stessa aliquota
						$filter_vat = "aliquo=" . $form['rows'][$nl]['pervat'];
						@$Natura = $item->getElementsByTagName('Natura')->item(0)->nodeValue;
						if (!empty($Natura)) {
							$filter_vat.= " AND fae_natura='" . $Natura . "'";
						}
						$rs_last_codvat = gaz_dbi_dyn_query("*", $gTables['aliiva'], $filter_vat . " AND tipiva<>'T'", "codice ASC", 0, 1);
						$last_codvat = gaz_dbi_fetch_array($rs_last_codvat);
						if ($last_codvat) {
							$form['codvat_'.$post_nl] = $last_codvat['codice'];
						} else {
							$form['codvat_'.$post_nl] = 'non trovata';
						}
					}
				}				
			}

			/*	Se presenti, trasformo gli sconti/maggiorazioni del campo 2.1.1.8 <ScontoMaggiorazione> in righe forfait */
			if ($xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/ScontoMaggiorazione")->length >= 1) {
				$sconto_totale_incondizionato = array();
				$sconto_maggiorazione = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/ScontoMaggiorazione");
				foreach ($sconto_maggiorazione as $sconti) { // potrei avere più elementi 2.2.1.10 <ScontoMaggiorazione>
					if ($sconti->getElementsByTagName('Percentuale')->length >= 1 && $sconti->getElementsByTagName('Percentuale')->item(0)->nodeValue>=0.00001) {
						$sconto_totale_incondizionato[] = $sconti->getElementsByTagName('Percentuale')->item(0)->nodeValue;
					} else {
						$nl++;
						$form['rows'][$nl]['tiprig'] = 1;
						$form['rows'][$nl]['codice_fornitore'] = '';
						$form['rows'][$nl]['descri'] = '';
						$form['rows'][$nl]['unimis'] = '';
						$form['rows'][$nl]['quanti'] = '';
						$form['rows'][$nl]['sconto'] = '';
						$form['rows'][$nl]['ritenuta'] = '';
						$form['rows'][$nl]['pervat'] = '';

						$form['codart_'.($nl-1)] = '';
						$form['codvat_'.($nl-1)] = '';
						$form['codric_'.($nl-1)] = '';

						$sconto_incondizionato = ($sconti->getElementsByTagName('Tipo')->item(0)->nodeValue == 'SC' ? -$sconti->getElementsByTagName('Importo')->item(0)->nodeValue : $sconti->getElementsByTagName('Importo')->item(0)->nodeValue);
						$form['rows'][$nl]['prelis'] = $sconto_incondizionato;
						$form['rows'][$nl]['amount'] = $sconto_incondizionato;
					}
				}
				if (count($sconto_totale_incondizionato) > 0) {
					$is=1;
					foreach($sconto_totale_incondizionato as $vsc){ // attraverso l'accumulatore di sconti per ottenerne uno solo
						$is *=(1-$vsc/100);
					}
					$sconto_totale_incondizionato = 100*(1-$is);
				}
			}

			$ImponibileImporto=0.00;
			$ImpostaDocumento=0.00;

			/* 
			Se la fattura è di tipo semplificata
			*/
			if ($isFatturaElettronicaSemplificata) {
				$DettaglioLineeSemplificate = $doc->getElementsByTagName('DatiBeniServizi');
				$nl = 0;
				foreach ($DettaglioLineeSemplificate as $item) {
					$nl++;
					$form['rows'][$nl]['tiprig'] = 1;
					$form['rows'][$nl]['codice_fornitore'] = '';
					$form['rows'][$nl]['descri'] = $item->getElementsByTagName('Descrizione')->item(0)->nodeValue;
					$form['rows'][$nl]['unimis'] = '';
					$form['rows'][$nl]['prelis'] = $item->getElementsByTagName('Importo')->item(0)->nodeValue;
					$form['rows'][$nl]['quanti'] = 1;
					$form['rows'][$nl]['amount'] = $form['rows'][$nl]['prelis'];
					$form['rows'][$nl]['sconto'] = '';
					$form['rows'][$nl]['ritenuta'] = '';
					if ($item->getElementsByTagName('Aliquota')->length > 0) {
						$form['rows'][$nl]['pervat'] = $item->getElementsByTagName('Aliquota')->item(0)->nodeValue;
                    } else {
						$form['rows'][$nl]['pervat'] = 0;
					}
					if ($item->getElementsByTagName('Imposta')->length > 0) {
						$ImpostaDocumento += $item->getElementsByTagName('Imposta')->item(0)->nodeValue;
                    }
					if ($item->getElementsByTagName('Natura')->length > 0) {
						$Natura = $item->getElementsByTagName('Natura')->item(0)->nodeValue;
						$rs_vat = gaz_dbi_dyn_query("codice", $gTables['aliiva'], "fae_natura='" . $Natura . "'", "codice DESC", 0, 1);
						$cod_vat = gaz_dbi_fetch_array($rs_vat)['codice'];
						$form['codvat_'.($nl-1)] = $cod_vat;
					}
				}
			} else { // non è una fattura semplificata
				$DatiRiepilogo = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiBeniServizi/DatiRiepilogo");
				$naturaN6 = false;
				foreach($DatiRiepilogo as $dr) {
					if ($dr->getElementsByTagName('Imposta')->length >= 1) {
						$ImpostaDocumento += $dr->getElementsByTagName('Imposta')->item(0)->nodeValue;
					}
					$ImponibileImporto+=$dr->getElementsByTagName('ImponibileImporto')->item(0)->nodeValue;
					if ($dr->getElementsByTagName('Natura')->length >= 1) { // se ho l'elemento Natura = 6.x dovrò ricercare l'aliquota per il reverse charge a tutto il documento ed attribuirla a tutti i righi del documento
						$Natura = $dr->getElementsByTagName('Natura')->item(0)->nodeValue;
						if ( substr($Natura,0,2) == 'N6' ) { // dovrò fare il reverse charge sostituisco con il codice iva relativo
							$naturaN6 = $Natura;
						}
					}
				}
				if (!isset($_POST['Submit_form']) && $naturaN6 ) { // al primo accesso se sopra ho trovato che è una natura da reverse charge
					$stdiva = gaz_dbi_get_row($gTables['aliiva'], 'codice', $admin_aziend['preeminent_vat'])['aliquo']; //la percentuale dell'aliquota standard (potrebbe cambiare negli anni)
					$rs_reverse = gaz_dbi_dyn_query("codice", $gTables['aliiva'], "aliquo=".$stdiva." AND fae_natura='" .$naturaN6."'", "codice DESC", 0, 1);
					$cod_reverse = gaz_dbi_fetch_array($rs_reverse)['codice'];
					// riattraverso i righi e ci metto il nuovo codice IVA
					foreach($form['rows'] as $kn => $vn) {
						$kp = $kn-1;
						//$form['codvat_'.$kp]=$cod_reverse;
					}
				}

				$totdiff=abs($ImponibileImporto-$tot_imponi);
				/* Infine aggiungo un eventuale differenza di centesimo di imponibile sul rigo di maggior valore, questo succede perché il tracciato non è rigoroso nei confronti dell'importo totale dell'elemento  */
				if ($totdiff>=0.01){ // qualora ci sia una differenza di almeno 1 cent la aggiunto (o lo sottraggo al rigo di maggior valore
					if ($form['rows'][$max_val_linea]['tiprig']==0){ //rigo normale con quantità variabile
						$form['rows'][$max_val_linea]['prelis']+= ($ImponibileImporto-$tot_imponi)/$form['rows'][$max_val_linea]['quanti'];
					} else {
						$form['rows'][$max_val_linea]['prelis']+= $ImponibileImporto-$tot_imponi;
					}
					$form['rows'][$max_val_linea]['amount'] += $ImponibileImporto-$tot_imponi;
				}
			}

            // qui eseguo un controllo per vedere se c'è l'elemento <Arrotondamento> dentro <DatiGeneraliDocumento> e se l'elemento <ImportoTotaleDocumento> non coincide con i righi procedo con l'aggiunta di un rigo fittizio in art.15 (natura esenzione N1)
            $ImportoTotaleDocumento=$xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/ImportoTotaleDocumento")->item(0)->nodeValue;
			if ($xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/Arrotondamento")->length >= 1) {
                $Arrotondamento=$xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/Arrotondamento")->item(0)->nodeValue;
                if (abs($ImportoTotaleDocumento-($ImponibileImporto + $ImpostaDocumento)) >= 0.01) { // ho una effettiva differenza tra i totali del castelletto IVA e il totale documennto allora aggiungo un rigo fuori campo IVA N1
  					$codvat_fc=gaz_dbi_get_row($gTables['aliiva'], "fae_natura", 'N1')['codice'];
                    $nl++;
					$form['rows'][$nl]['tiprig'] = 1;
					$form['rows'][$nl]['codice_fornitore'] = '';
					$form['rows'][$nl]['descri'] = 'Arrotondamento';
					$form['rows'][$nl]['unimis'] = '';
					$form['rows'][$nl]['quanti'] = '';
					$form['rows'][$nl]['sconto'] = '';
					$form['rows'][$nl]['ritenuta'] = '';
					$form['rows'][$nl]['pervat'] = '';
					$form['codart_'.($nl-1)] = '';
					$form['codvat_'.($nl-1)] = $codvat_fc;
					$form['codric_'.($nl-1)] = $form['codric_'.($nl-2)]; // attribuisco il costo del rigo che lo precede
					$form['rows'][$nl]['prelis'] = $Arrotondamento;
					$form['rows'][$nl]['amount'] = $Arrotondamento;
                }
            }
            
			// ricavo l'allegato, e se presente metterò un bottone per permettere il download
			$nf = $doc->getElementsByTagName('NomeAttachment')->item(0);
			if ($nf) {
				$name_file = $nf->textContent;
				$att = $doc->getElementsByTagName('Attachment')->item(0);
				$base64 = $att->textContent;
				$bin = base64_decode($base64);
				file_put_contents( DATA_DIR . 'files/tmp/' . $name_file, $bin);
			}
			if (empty($_POST['Submit_file'])) { // l'upload del file è già avvenuto e sono nei refresh successivi quindi riprendo i valori scelti e postati dall'utente
				//$form['datreg'] = substr($_POST['datreg'],0,10);
				$form['pagame'] = intval($_POST['pagame']);
				$form['new_acconcile'] = intval($_POST['new_acconcile']);
				$form['seziva'] = intval($_POST['seziva']);
			}

			if (isset($_POST['Submit_form']) && count($msg['err'])==0) { // confermo le scelte sul form, inserisco i dati sul db ma solo se non ho errori
				if (!$anagra_with_same_pi && !$partner_with_same_pi) { // non ho nulla: devo inserire tutto (anagrafica e fornitore) basandomi sul pagamento e sui conti di costo scelti dall'utente
					$new_partner = array_merge(gaz_dbi_fields('clfoco'), gaz_dbi_fields('anagra'));
					$new_partner['codpag'] = $form['pagame'];
					$new_partner['sexper'] = 'G';
					// setto le colonne in base ai dati di questa fattura elettronica
					$new_partner['pariva'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0)->nodeValue;
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/CodiceFiscale")->item(0)) {
						$new_partner['codfis'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/CodiceFiscale")->item(0)->nodeValue;
						// ho un codice fiscale posso vedere se è una persona fisica e di quale sesso
						preg_match('/^[a-z]{6}[0-9]{2}[a-z]([0-9]{2})[a-z][0-9]{3}[a-z]$/i',trim($new_partner['codfis']),$match);
						if (count($match)>1){
							if ($match[1] > 40 ){  // è un codice fiscale femminile
								$new_partner['sexper'] = 'F';
							} else {
								$new_partner['sexper'] = 'M';
							}
						} else { // giuridica
							$new_partner['sexper'] = 'G';
						}
					}
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Nome")->item(0)) {
						$new_partner['legrap_pf_nome'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Nome")->item(0)->nodeValue;
						$new_partner['legrap_pf_cognome'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Cognome")->item(0)->nodeValue;
						$new_partner['descri'] = $new_partner['legrap_pf_cognome']. ' '.$new_partner['legrap_pf_nome'];
						if (strlen($new_partner['descri'])>50){
							$new_partner['ragso1'] = $new_partner['legrap_pf_cognome'];
							$new_partner['ragso2'] = $new_partner['legrap_pf_nome'];
						} else {
							$new_partner['ragso1'] = $new_partner['descri'];							
						}
					}
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Denominazione")->item(0)) {
						$new_partner['descri'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Denominazione")->item(0)->nodeValue;
						if (strlen($new_partner['descri'])>50){
							$new_partner['ragso1'] = substr(str_replace(array("'",'"',"`"),"",$new_partner['descri']),0,50);
							$new_partner['ragso2'] = substr(str_replace(array("'",'"',"`"),"",$new_partner['descri']),50,100);
						} else {
							$new_partner['ragso1'] = str_replace(array("'",'"',"`"),"",$new_partner['descri']);							
						}
					}
					$new_partner['indspe'] = ucwords(strtolower($xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Indirizzo")->item(0)->nodeValue));
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/NumeroCivico")->item(0)){
						$new_partner['indspe'] .= ', '.$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/NumeroCivico")->item(0)->nodeValue;
					}
					$new_partner['capspe'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/CAP")->item(0)->nodeValue;
					$new_partner['citspe'] = strtoupper($xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Comune")->item(0)->nodeValue);
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Provincia")->item(0)){
						$new_partner['prospe'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Provincia")->item(0)->nodeValue;
					}
					$new_partner['country'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Nazione")->item(0)->nodeValue;
					$new_partner['counas'] = $new_partner['country'];
					$new_partner['id_currency'] =1;
					$new_partner['id_language'] =1;
					$new_partner['cosric']=intval($_POST['codric_0']);	 // prendo il primo valore di costo per valorizzare quello del fornitore			
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Contatti/Telefono")->item(0)) {
						$new_partner['telefo'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Contatti/Telefono")->item(0)->nodeValue;
					}
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Contatti/Fax")->item(0)) {
						$new_partner['fax'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Contatti/Fax")->item(0)->nodeValue;
					}
					if (@$xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Contatti/Email")->item(0)) {
						$new_partner['e_mail'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Contatti/Email")->item(0)->nodeValue;
					}
					if (@$xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiPagamento/DettaglioPagamento/IBAN")->item(0)) {
						$new_partner['iban'] = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiPagamento/DettaglioPagamento/IBAN")->item(0)->nodeValue;
					}
					// trovo l'ultimo codice disponibile sul piano dei conti
					$rs_last_partner = gaz_dbi_dyn_query("*", $gTables['clfoco'], 'codice BETWEEN ' . $admin_aziend['masfor'] . '000001 AND ' . $admin_aziend['masfor'] . '999999', "codice DESC", 0, 1);
					$last_partner = gaz_dbi_fetch_array($rs_last_partner);
					if (!$last_partner) {
						$new_partner['codice']=$admin_aziend['masfor'].'000001';
					} else {
						$new_partner['codice'] =$last_partner['codice']+1;
					}
					// inserisco il partner
					$anagrafica->insertPartner($new_partner);
					$form['clfoco']=$new_partner['codice'];
				} else if ($anagra_with_same_pi) { // devo inserire il fornitore, ho già l'anagrafica 
					$anagra_with_same_pi['id_anagra'] = $anagra_with_same_pi['id'];
					$anagra_with_same_pi['cosric'] = intval($_POST['codric_0']); // prendo il primo valore di costo per valorizzare quello del fornitore
					$form['clfoco'] = $anagrafica->anagra_to_clfoco($anagra_with_same_pi, $admin_aziend['masfor'], $form['pagame']);
				}
				$prefisso_codici_articoli_fornitore=encondeFornitorePrefix($form['clfoco']);// mi servirà eventualmente per attribuire ai nuovi articoli un pre-codice univoco e uguale per tutti gli articoli dello stesso fornitore
				$form['tipdoc'] = $tipdoc_conv[$xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/TipoDocumento")->item(0)->nodeValue]; 
				$form['protoc'] = getLastProtocol($form['tipdoc'],substr($form['datreg'],-4),$form['seziva'])['last_protoc'];
				$form['numfat'] = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/Numero")->item(0)->nodeValue;
				$form['numdoc'] = preg_replace ('/\D/', '', $form['numfat']);
				$form['datfat'] = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiGenerali/DatiGeneraliDocumento/Data")->item(0)->nodeValue;
				$form['datemi'] = $form['datfat'];
				$form['fattura_elettronica_original_content'] = utf8_encode($invoiceContent);
				$form['datreg'] = gaz_format_date($form['datreg'], true);
				$form['caumag'] = $magazz->get_codice_caumag(1, 1, $docOperat[$form['tipdoc']]);
				if (!empty($sconto_totale_incondizionato)) {
					$form['sconto'] = $sconto_totale_incondizionato;
				}
				$form['template']="FatturaAcquisto";

				$accexpdoc = array();
				if ($doc->getElementsByTagName('DettaglioPagamento')->length>=1) { 
					// se ho le date e gli importi delle scadenze creo un array da inserire sulla tabella gaz_NNNexpdoc al fine di poter aprire le partite in base a quanto riportato in fattura del fornitore e senza calcolarli dalla modalità di pagamento con si faceva sulle versioni <= 7.34
					$detpag=$doc->getElementsByTagName('DettaglioPagamento');
					foreach ($detpag as $vdp) { // attraverso
						if ($vdp->getElementsByTagName('DataScadenzaPagamento')->length>=1 && $vdp->getElementsByTagName('ImportoPagamento')->length>=1){
							$accexpdoc[] = array('ModalitaPagamento'=>$vdp->getElementsByTagName('ModalitaPagamento')->item(0)->nodeValue,'DataScadenzaPagamento'=>$vdp->getElementsByTagName('DataScadenzaPagamento')->item(0)->nodeValue,'ImportoPagamento'=>$vdp->getElementsByTagName('ImportoPagamento')->item(0)->nodeValue);
						}
						
					}
				}
				// Antonio Germani - inizio scrittura DB
				
				if ($doc->getElementsByTagName('DatiDDT')->length<1 OR $anomalia == "AnomaliaDDT=FAT" OR $form['tipdoc']=="AFC"){ // se non ci sono ddt vuol dire che è una fattura immediata AFA 
					//oppure se c'è anomalia è accompagnatoria e la trattiamo sempre come AFA 
					//oppure se è una nota credito AFC non devo considerare eventuali DDT a riferimento
					$ultimo_id=tesdocInsert($form); // Antonio Germani - creo fattura immediata senza ddt
                    $fn = DATA_DIR . 'files/' . $admin_aziend["codice"] . '/'.$ultimo_id.'.inv';
                    file_put_contents($fn,$form['fattura_elettronica_original_content']); 
				}
				if ($anomalia == "AnomaliaDDT=FAT"){ // se è da considerare accompagnatoria azzero la presenza del DdT
					$v['exist_ddt']="";
				}
				$ctrl_ddt='';
				foreach ($form['rows'] as $i => $v) { // inserisco i righi
				 //print_r($v); print '<br>';
				 $form['rows'][$i]['status']="INSERT";
					$post_nl=$i-1;
					
					if (abs($v['prelis'])<0.00001) { // siccome il prezzo è a zero mi trovo di fronte ad un rigo di tipo descrittivo 
						$form['rows'][$i]['tiprig']=2;
					}
					if ($form['tipdoc']=="AFC" && $ImportoTotaleDocumento <= -0.01 ) { // capita a volte che dei software malfatti sulle note credito indichino i valori in negativo... allora per renderli compatibili con la contabilizzazione di GAzie invertiamo il segno
                            $form['rows'][$i]['prelis']=-$v['prelis'];
					}
					// questo mi servirà sotto se è stata richiesta la creazione di un articolo nuovo
					if (empty(trim($v['codice_fornitore']))) { // non ho il codice del fornitore me lo invento accodando al precedente prefisso dipendente dal codice del fornitore un hash a 8 caratteri della descrizione
						$new_codart=$prefisso_codici_articoli_fornitore.'_'.crc32($v['descri']);						
					} else { // ho il codice articolo del fornitore sul tracciato ma potrei averlo cambiato
						$new_codart=$prefisso_codici_articoli_fornitore.'_'.substr($v['codice_fornitore'],-11);
					}				
									
					if (isset($v['exist_ddt']) AND $anomalia != "AnomaliaDDT=FAT" AND $form['tipdoc']!=="AFC") { // se ci sono DDT collegabili alla FAE e non è una nota credito AFC	
						if ($ctrl_ddt!=$v['NumeroDDT']) { 
							// Antonio Germani - controllo se esiste tesdoc di questo ddt usando la funzione existDdT
							$exist_artico_tesdoc=existDdT($v['NumeroDDT'],$v['DataDDT'],$form['clfoco'],$v['codart']);
						
							if ($exist_artico_tesdoc){// se esiste cancello tesdoc e ne cancello tutti i rigdoc e i relativi movmag
								$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '{$exist_artico_tesdoc['id_tes']}'","id_tes desc");
								
								gaz_dbi_del_row($gTables['tesdoc'], "id_tes", $exist_artico_tesdoc['id_tes']);
								while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
									  gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $a_row['id_rig']);
									  gaz_dbi_del_row($gTables['movmag'], "id_mov", $a_row['id_mag']);
								}
							}
							// creo un nuovo tesdoc AFT
							if ($exist_artico_tesdoc['tipdoc']=="RDL"){
								$ddt_type="L";
							} else {
								$ddt_type="T";
							}
							$form['tipdoc']="AFT";$form['ddt_type']=$ddt_type;$form['numdoc']=$v['NumeroDDT'];$form['datemi']=$v['DataDDT'];
							if ($anomalia=="Anomalia"){
								$form['status']="DdtAnomalo";
							}
							$ultimo_id =tesdocInsert($form); // Antonio Germani - creo fattura differita
                            $fn = DATA_DIR . 'files/' . $admin_aziend["codice"] . '/'.$ultimo_id.'.inv';
                            file_put_contents($fn,$form['fattura_elettronica_original_content']); 
						}
						$ctrl_ddt=$v['NumeroDDT'];
					}
					$form['rows'][$i]['id_tes'] = $ultimo_id;
					
					// per A.GERMANI: se decommento rigo 1007 acquisisce quella fattura anomala (ddt + fattura immediata...  GRRR... ) ma duplica i righi del ddt inserito manualmente
					// secondo me le segnalazioni di anomalie si devono spostare più in basso (a livello rigo), in particolare si dovrebbe fare qualcosa sopra (righi 621-675) 
					// dove vengono fatti i controlli in presenza di elementi DDT  
					
					//$form['rows'][$i]['id_tes'] = ( $form['rows'][$i]['exist_ddt']['id_tes'] >= 1 && $ultimo_id == 0 ) ? $form['rows'][$i]['exist_ddt']['id_tes'] : $ultimo_id;
					
					// i righi postati hanno un indice diverso
					$form['rows'][$i]['codart'] = preg_replace("/[^A-Za-z0-9_]i/",'',$_POST['codart_'.$post_nl]);
					$form['rows'][$i]['codric'] = intval($_POST['codric_'.$post_nl]);
					$form['rows'][$i]['codvat'] = intval($_POST['codvat_'.$post_nl]);
					$aliiva=$form['rows'][$i]['codvat'];
					$exist_new_codart=gaz_dbi_get_row($gTables['artico'], "codice", $new_codart);
					if ($exist_new_codart && substr($v['codart'],0,6)!='Insert') { // il codice esiste lo uso, ma prima controllo se l'ho volutamente cambiato sul form 
						if( $exist_new_codart['codice'] != $form['rows'][$i]['codart'] ){ // ho scelto un codice diverso 
							$other_artico=gaz_dbi_get_row($gTables['artico'], "codice", $form['rows'][$i]['codart']);
							$form['rows'][$i]['good_or_service']=$other_artico['good_or_service'];
							//aggiorno l'articolo con questo codice fornitore 
							gaz_dbi_put_row($gTables['artico'], 'codice', $other_artico['codice'], 'codice_fornitore', $v['codice_fornitore']);
						} else {
							$form['rows'][$i]['codart']=$exist_new_codart['codice'];
							$form['rows'][$i]['good_or_service']=$exist_new_codart['good_or_service'];
						}
					} else { // il codice nuovo ricavato non esiste creo l'articolo basandomi sui dati in fattura
						if ($exist_new_codart) { // il fornitore ha la pessima abitudine di usare lo stesso codice articolo per diversi articoli me lo invento con un hash a 8 caratteri della descrizione nella speranza che almeno questa sia cambiata...
							$new_codart=$prefisso_codici_articoli_fornitore.'_'.crc32($v['descri'].$form['datreg'].$form['protoc']);						
						}
						$v['catmer'] = 1; // di default utilizzo la prima categoria merceologica, sarebbe da farla selezionare all'operatore...
						$form['rows'][$i]['good_or_service']=0;
						switch ($v['codart']) {
							case 'Insert_New': // inserisco il nuovo articolo in gaz_XXXartico senza lotti o matricola
							$artico=array('codice'=>$new_codart,'descri'=>$v['descri'],'catmer'=>$v['catmer'],'codice_fornitore'=>$v['codice_fornitore'],'unimis'=>$v['unimis'],'web_mu'=>$v['unimis'],'uniacq'=>$v['unimis'],'aliiva'=>$aliiva);
							gaz_dbi_table_insert('artico', $artico);
							$form['rows'][$i]['codart'] = $new_codart;
							break;
							case 'Insert_W-lot': // inserisco il nuovo articolo in gaz_XXXartico con lotti
							$artico=array('codice'=>$new_codart,'descri'=>$v['descri'],'catmer'=>$v['catmer'],'codice_fornitore'=>$v['codice_fornitore'],'lot_or_serial'=>1,'unimis'=>$v['unimis'],'web_mu'=>$v['unimis'],'uniacq'=>$v['unimis'],'aliiva'=>$aliiva);
							gaz_dbi_table_insert('artico', $artico);
							$form['rows'][$i]['codart'] = $new_codart;
							break;
							case 'Insert_W-matr': //  inserisco il nuovo articolo in gaz_XXXartico con matricola
							$artico=array('codice'=>$new_codart,'descri'=>$v['descri'],'catmer'=>$v['catmer'],'codice_fornitore'=>$v['codice_fornitore'],'lot_or_serial'=>2,'unimis'=>$v['unimis'],'web_mu'=>$v['unimis'],'uniacq'=>$v['unimis'],'aliiva'=>$aliiva);
							gaz_dbi_table_insert('artico', $artico);
							$form['rows'][$i]['codart'] = $new_codart;
							break;
							default: //  negli altri casi controllo se devo inserire il riferimento ad una bolla
						}
					}
					// alla fine se ho un codice articolo e il tipo rigo è normale aggiorno l'articolo con il nuovo prezzo d'acquisto e con l'ultimo fornitore
					if (strlen($form['rows'][$i]['codart'])>2&&$form['rows'][$i]['tiprig']==0) {
						tableUpdate('artico',array('clfoco','preacq'),$form['rows'][$i]['codart'],array('preacq'=>CalcolaImportoRigo(1,$form['rows'][$i]['prelis'],array($form['rows'][$i]['sconto'])),'clfoco'=>$form['clfoco']));
					}
					
					// inserisco il rigo rigdoc
					$id_rif=rigdocInsert($form['rows'][$i]);	
									
					if ($form['rows'][$i]['good_or_service']==0 AND strlen($form['rows'][$i]['codart'])>0 AND $form['tipdoc']!=="AFC"){ // se l'articolo prevede di movimentare il magazzino e non è una nota credito
						// Antonio Germani - creo movimento di magazzino sempre perché, se c'erano, sono stati cancellati
						if ($v['NumeroDDT']>0){ // se c'è un ddt
							$rowmag=array("caumag"=>$form['caumag'],"type_mov"=>"0","operat"=>"1","datreg"=>$form['datreg'],"tipdoc"=>"ADT",
							"desdoc"=>"D.d.t. di acquisto n.".$v['NumeroDDT']."/".$form['seziva']." prot. ".$form['protoc']."/".$form['seziva'],
							"datdoc"=>$form['datemi'],"clfoco"=>$form['clfoco'],"id_rif"=>$id_rif,"artico"=>$form['rows'][$i]['codart'],"quanti"=>$form['rows'][$i]['quanti'],
							"prezzo"=>$form['rows'][$i]['prelis'],"scorig"=>$form['rows'][$i]['sconto'],'synccommerce_classname'=>$admin_aziend['synccommerce_classname']);
						} else { // se non c'è DDT
							$rowmag=array("caumag"=>$form['caumag'],"type_mov"=>"0","operat"=>"1","datreg"=>$form['datreg'],"tipdoc"=>"ADT",
							"desdoc"=>"Fattura di acquisto n.".$form['numfat']."/".$form['seziva']." prot. ".$form['protoc']."/".$form['seziva'],
							"datdoc"=>$form['datfat'],"clfoco"=>$form['clfoco'],"id_rif"=>$id_rif,"artico"=>$form['rows'][$i]['codart'],"quanti"=>$form['rows'][$i]['quanti'],
							"prezzo"=>$form['rows'][$i]['prelis'],"scorig"=>$form['rows'][$i]['sconto'],'synccommerce_classname'=>$admin_aziend['synccommerce_classname']);
						}
					
						$id_mag=movmagInsert($rowmag);
						
						// aggiorno idmag nel rigdoc 
						gaz_dbi_query("UPDATE " . $gTables['rigdoc'] . " SET id_mag = " . $id_mag . " WHERE `id_rig` = $id_rif ");
					}				
					
				}
				// se l'array delle scadenze ha dati li inserisco nell'apposita tabella facendo riferimento sempre all'ultimi id_tes inserito
				foreach ($accexpdoc as $ved) { // attraverso
					$ved['id_tes']=$ultimo_id;    
					expdocInsert($ved);
				}

				if ($anomalia=="AnomaliaExistDdt" AND isset($form['clfoco'])){ // se c'è una anomalia, cioè la FAE ha ddt senza i riferimenti ai prodotti, ma i ddt sono già presenti in GAzie
					$ddt=$doc->getElementsByTagName('DatiDDT');
					foreach ($ddt as $vd) { // Ciclo nuovamente i DDt della FAE
						
						$dataddt=$vd->getElementsByTagName('DataDDT')->item(0)->nodeValue;
						$numddt=preg_replace ('/\D/', '',$vd->getElementsByTagName('NumeroDDT')->item(0)->nodeValue);
						$exist=existDdT($numddt,$dataddt,$form['clfoco']);// controllo se esiste il tesdoc
						if ($exist){ // se esiste, modifico il tesdoc per trasformarlo in ddt connesso a fae
							$updt=array();
							if ($exist['tipdoc']=="RDL"){
								$ddt_type="L";
							} else {
								$ddt_type="T";
							}
							if ($exist['protoc']==0){ // se non ha già un protocollo ne ricavo il primo disponibile
								$updt['protoc']=getLastProtocol($form['tipdoc'],substr($form['datreg'],-4),$form['seziva'])['last_protoc'];
							}
							$updt['tipdoc']="AFT";$updt['ddt_type']=$ddt_type;$updt['numfat']=$form['numfat'];$updt['datfat']=$form['datfat'];
							$updt['fattura_elettronica_zip_package']=$form['fattura_elettronica_zip_package'];
							$updt['fattura_elettronica_original_name']=$form['fattura_elettronica_original_name'];
                            $fn = DATA_DIR . 'files/' . $admin_aziend["codice"] . '/'.$exist['id_tes'].'.inv';
                            file_put_contents($fn,$form['fattura_elettronica_original_content']); 
							//$updt['fattura_elettronica_original_content']=$form['fattura_elettronica_original_content'];
							tesdocUpdate(array('id_tes', $exist['id_tes']), $updt);					
						}
					}
				}
				header('Location: report_docacq.php?sezione='.$form['seziva']);
				exit;
			} else { // non ho confermato, sono alla prima entrata dopo l'upload del file
				if (!isset($form['pagame'])) {
					//$cond_pag = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiPagamento/CondizioniPagamento")->item(0)->nodeValue;
					//$dat_scad = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiPagamento/DettaglioPagamento/DataScadenzaPagamento")->item(0)->nodeValue;
					//$imp_scad = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiPagamento/DettaglioPagamento/ImportoPagamento")->item(0)->nodeValue;
					$fae_mode = $xpath->query("//FatturaElettronicaBody[".$form['curr_doc']."]/DatiPagamento/DettaglioPagamento/ModalitaPagamento")->item(0)->nodeValue;
					$pagame = gaz_dbi_get_row($gTables['pagame'], "fae_mode", $fae_mode);
					$form['pagame'] = $pagame['codice'];
					$form['new_acconcile']=0;
				}
			}
		}
	}
	}
}

require('../../library/include/header.php');
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup'));
$gForm = new acquisForm();
echo "<script type=\"text/javascript\">
var cal = new CalendarPopup();
var calName = '';
function setMultipleValues(y,m,d) {
     document.getElementById(calName+'_Y').value=y;
     document.getElementById(calName+'_M').selectedIndex=m*1-1;
     document.getElementById(calName+'_D').selectedIndex=d*1-1;
}
function setDate(name) {
  calName = name.toString();
  var year = document.getElementById(calName+'_Y').value.toString();
  var month = document.getElementById(calName+'_M').value.toString();
  var day = document.getElementById(calName+'_D').value.toString();
  var mdy = month+'/'+day+'/'+year;
  cal.setReturnFunction('setMultipleValues');
  cal.showCalendar('anchor', mdy);
}
</script>
";
?>
<script type="text/javascript">
    $(function () {
        $("#datreg").datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
        $("#datreg,#new_acconcile").change(function () {
            this.form.submit();
        });
    });
</script>
<div align="center" ><b><?php echo $script_transl['title'];?></b></div>
<form method="POST" name="form" enctype="multipart/form-data" id="add-invoice">
    <input type="hidden" name="fattura_elettronica_original_name" value="<?php echo $form['fattura_elettronica_original_name']; ?>">
    <input type="hidden" name="curr_doc" value="<?php echo $form['curr_doc']; ?>">
<?php
	// INIZIO form che permetterà all'utente di interagire per (es.) imputare i vari costi al piano dei conti (contabilità) ed anche le eventuali merci al magazzino
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    if (count($msg['war']) > 0) { // ho un alert
        $gForm->gazHeadMessage($msg['war'], $script_transl['war'], 'war');
    }
//echo "<pre>",print_r($form);
if ($toDo=='insert' || $toDo=='update' ) {
	if ($f_ex){
	if (empty($form['curr_doc']) && count($docs) > 1) {
?>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label for="image" class="col-sm-4 control-label">Scegli la fattura da acquisire</label>
					<div class="col-sm-12">
						<br />

<?php
			$ndoc = 0;
			echo "<table class=\"Tlarge table table-striped table-bordered table-condensed\">";
			echo '<tr><th>Seleziona</th><th>Tipo Doc.</th><th>Numero</th><th>Data</th></tr>';
			foreach ($docs as $doc) {
				$ndoc++;
				$tipdoc = $xpath->query("//FatturaElettronicaBody[".$ndoc."]/DatiGenerali/DatiGeneraliDocumento/TipoDocumento")->item(0)->nodeValue;
				$datdoc = $xpath->query("//FatturaElettronicaBody[".$ndoc."]/DatiGenerali/DatiGeneraliDocumento/Data")->item(0)->nodeValue;
				$numdoc = $xpath->query("//FatturaElettronicaBody[".$ndoc."]/DatiGenerali/DatiGeneraliDocumento/Numero")->item(0)->nodeValue;
				echo '<tr>';
				echo '<td align="center">' . $ndoc . ' <input type="radio" name="curr_doc" value="' . $ndoc . '" /></td>';
				echo '<td>' . $tipdoc . '</td>';
				echo '<td>' . gaz_format_date($datdoc, false) . '</td>';
				echo '<td>' . $numdoc . '</td>';
				echo '</tr>';
			}
			echo '</table><br />';
			echo '<div class="col-sm-12 text-right"><input name="Select_doc" type="submit" class="btn btn-warning" value="Seleziona documento" />';
?>
					</div>
				</div>
			</div>
		</div><!-- chiude row  -->
<?php
	} else {
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12"><?php echo $script_transl['head_text1']. '<span class="label label-success">'.$form['fattura_elettronica_original_name'] .'</span>'.$script_transl['head_text2']; ?>
            </div>
        </div> <!-- chiude row  -->
    </div>
    <div class="panel-body">
        <div class="form-group">
            <div class="form-group col-md-6 col-lg-3 nopadding">
                 <label for="seziva" class="col-form-label"><?php echo $script_transl['seziva']; ?></label>
                 <div>
                        <?php
                        $gForm->selectNumber('seziva', $form['seziva'], 0, 1, 9, "col-lg-12", '', 'style="max-width: 100px;"');
                        ?>
                </div>
            </div>
            <div class="form-group col-md-6 col-lg-3 nopadding">
                 <label for="datreg" class="col-form-label"><?php echo $script_transl['datreg']; ?></label>
                 <div>
                     <input type="text" id="datreg" name="datreg" value="<?php echo $form['datreg']; ?>">
                 </div>
            </div>
            <div class="form-group col-md-6 col-lg-3 nopadding">
                <label for="new_acconcile" class="col-form-label" ><?php echo $script_transl['new_acconcile']; ?></label>
                <div>
                <?php
				// new_acconcile lo riporto sempre a 0 dopo ogni post e solo quando viene cambiato cambieranno tutti i valori dei conti di costo di tutti i righi
				$gForm->selectAccount('new_acconcile', 0, array('sub',3),'', false, "col-sm-12 small",'style="max-width: 300px;"', false);
				?>
                </div>
            </div>
            <div class="form-group col-md-6 col-lg-3 nopadding">
                 <label for="pagame" class="col-form-label" ><?php echo $script_transl['pagame']; ?></label>
                 <div>
                        <?php
                        $select_pagame = new selectpagame("pagame");
                        $select_pagame->addSelected($form["pagame"]);
                        $select_pagame->output(false, "col-lg-12");
                        ?>
                </div>
            </div>
        </div> <!-- chiude row  -->
    </div>
</div>
<?php		
		$rowshead=array();
		$ctrl_ddt='';
		$exist_movmag=false;
		$new_acconcile=$form['new_acconcile'];
		foreach ($form['rows'] as $k => $v) { 
			$k--;
			if (!empty($v['NumeroDDT'])){
				if ($ctrl_ddt!=$v['NumeroDDT']){
					// qui valorizzo il rigo di riferimento al ddt
					$exist_ddt='';
					if ($v['exist_ddt']){ // ho un ddt d'acquisto già inserito 
						$exist_ddt='<span class="warning">- questo DdT &egrave; gi&agrave; stato inserito <a class="btn btn-xs btn-success" href="admin_docacq.php?id_tes='. $v['exist_ddt']['id_tes'] . '&Update"><i class="glyphicon glyphicon-edit"></i>&nbsp;'.$v['exist_ddt']['id_tes'].'</a></span>'; 
						$tipddt=$v['exist_ddt']['tipdoc'];
					} else {
						$tipddt="Ddt";
					}
					$ctrl_ddt=$v['NumeroDDT'];
					$rowshead[$k]='<td colspan=13><b> da '.$tipddt.' n.'.$v['NumeroDDT'].' del '.gaz_format_date($v['DataDDT']).' '.$exist_ddt.'</b></td>';
										
					if ($anomalia!=""){ // La FAE non ha i riferimenti linea nei ddt
						if ($anomalia == "AnomaliaDDT=FAT"){
							$rowshead[$k]='<td colspan=13><p class="text-warning"><b>> ANOMALIA FAE: questa fattura fa riferimento ad un DDT che ha il suo numero e stessa data. E\' da considerarsi come accompagnatoria. <</b></p><p>GAzie acquisirà questo documento come fattura semplice AFA.</p></td>';
						} else if ($anomalia=="AnomaliaExistDdt"){
						$rowshead[$k]='<td colspan=13><p class="text-warning"><b>> ANOMALIA FAE: questa fattura riporta DDT con i collegamenti ai righi degli articoli anomali o mancanti <</b></p><p>GAzie è in grado di rimediare a patto che i DDT già inseriti non differiscano dalla FAE.</p></td>';
						} else {
							$rowshead[$k]='<td colspan=13><p class="text-warning"><b>> ANOMALIA FAE: questa fattura riporta DDT senza però collegare gli articoli ai rispettivi DDT <</b></p><p>Se è presente più di un DDT, prima di inserire la FAE, si consiglia di inserire manualmente i DDT altrimenti verrà creato automaticamente un unico ddt di raggruppamento anomalo.</p></td>';
						}						
					}					
				}				
			} else if (!empty($ctrl_ddt)){
				$ctrl_ddt='';
				$rowshead[$k]='<td colspan=13> senza riferimento a DdT</td>';
			}
			
			if ($new_acconcile>100000000){
				$form['codric_'.$k]=$new_acconcile;
			}
            $codric_dropdown = $gForm->selectAccount('codric_'.$k, $form['codric_'.$k], array('sub',1,3), '', false, "col-sm-12 small",'style="max-width: 350px;"', false, true);
			$codvat_dropdown = $gForm->selectFromDB('aliiva', 'codvat_'.$k, 'codice', $form['codvat_'.$k], 'aliquo', true, '-', 'descri', '', 'col-sm-12 small', null, 'style="max-width: 350px;"', false, true);
			$codart_dropdown = $gForm->concileArtico('codart_'.$k,'codice',$form['codart_'.$k]);
			//forzo i valori diversi dalla descrizione a vuoti se è descrittivo
			if (abs($v['prelis'])<0.00001){ // siccome il prezzo è a zero mi trovo di fronte ad un rigo di tipo descrittivo 
				$v['codice_fornitore'] = '';
				$v['unimis'] = '';
				$v['quanti'] = '';
				$v['unimis'] = '';
				$v['prelis'] = '';
				$v['sconto'] = '';
				$v['amount'] = '';
				$v['ritenuta'] = '';
				$v['pervat'] = '';
				$codric_dropdown = '<input type="hidden" name="codric_'.$k.'" value="000000000" />';
				$codvat_dropdown = '<input type="hidden" name="codvat_'.$k.'" value="000000000" />';
				$codart_dropdown = '<input type="hidden" name="codart_'.$k.'" />';
			} else {
				//$v['prelis']=gaz_format_number($v['prelis']);
				$v['amount']=gaz_format_number($v['amount']);
				$v['ritenuta']=floatval($v['ritenuta']);
				$v['pervat']=floatval($v['pervat']);
			}		
			// creo l'array da passare alla funzione per la creazione della tabella responsive
            $resprow[$k] = array(
                array('head' => $script_transl["nrow"], 'class' => '',
                    'value' => $k+1),
                array('head' => $script_transl["codart"], 'class' => '',
                    'value' => $v['codice_fornitore']),
                array('head' => $script_transl["codart"], 'class' => '',
                    'value' => $codart_dropdown),
                array('head' => $script_transl["descri"], 'class' => 'col-sm-12 col-md-3 col-lg-3',
                    'value' => $v['descri']),
                array('head' => $script_transl["unimis"], 'class' => '',
                    'value' => $v['unimis']),
                array('head' => $script_transl["quanti"], 'class' => 'text-right numeric',
                    'value' => $v['quanti']),
                array('head' => $script_transl["prezzo"], 'class' => 'text-right numeric',
                    'value' => $v['prelis']),
                array('head' => $script_transl["sconto"], 'class' => 'text-right numeric',
                    'value' => $v['sconto']),
                array('head' => $script_transl["amount"], 'class' => 'text-right numeric', 
					'value' => $v['amount'], 'type' => ''),
                array('head' => $script_transl["conto"], 'class' => 'text-center numeric', 
					'value' => $codric_dropdown, 'type' => ''),
                array('head' => $script_transl["tax"], 'class' => 'text-center numeric', 
					'value' => $codvat_dropdown, 'type' => ''),
                array('head' => '%', 'class' => 'text-center numeric', 
					'value' => $v['pervat'], 'type' => ''),
                array('head' => 'Ritenuta', 'class' => 'text-center numeric', 
					'value' => $v['ritenuta'], 'type' => '')
            );

		}
		$gForm->gazResponsiveTable($resprow, 'gaz-responsive-table', $rowshead);
?>	   <div class="col-sm-6">
<?php			
		if ($nf){
?>		
		Allegato: <input name="Download" type="submit" class="btn btn-default" value="<?php echo $name_file; ?>" />
<?php 
		} 
?>
		</div>		   
		<div class="col-sm-6">
			<div class="col-sm-10 bg-warning">
				<?php
				if ($anomalia!=""){ // La FAE non ha i riferimenti linea nei ddt
					echo $rowshead[0];
				}
				?>
			</div>
			<div class="col-sm-2 text-left">
				<input name="Submit_form" type="submit" class="btn btn-warning" value="<?php echo $script_transl['submit']; ?>" />
			</div>
		</div>	   
</form>
<br>
<?php
	}
	}
	if (substr($_SESSION['theme'],-2)!='te'){ 
		/* se non ho "lte" come motore di interfaccia allora richiamo subito il footer
		 * della pagina e poi visualizzo l'xml altrimenti non mi fa il submit del form */
		require("../../library/include/footer.php");
	}
	if ($f_ex) {	// visualizzo la fattura elettronica in calce
		$fae_xsl_file = gaz_dbi_get_row($gTables['company_config'], 'var', 'fae_style');
		$xslDoc = new DOMDocument();
		$xslDoc->load('../../library/include/'.$fae_xsl_file['val'].'.xsl');
		$xslt = new XSLTProcessor();
		$xslt->importStylesheet($xslDoc);
		echo '<center>' . $xslt->transformToXML($xml) . '</center>';
	}
	if (substr($_SESSION['theme'],-3)=='lte'){ 
		// footer  richiamato alla fine in caso di utilizzo di lte 
		require("../../library/include/footer.php");
	}
} else { // all'inizio chiedo l'upload di un file xml o p7m 
?>
<div class="panel panel-default gaz-table-form">
	<div class="container-fluid">
       <div class="row">
           <div class="col-md-12">
               <div class="form-group">
                   <label for="image" class="col-sm-4 control-label">Seleziona il file xml o p7m</label>
                   <div class="col-sm-8">File: <input type="file" accept=".xml,.p7m" name="userfile" />
				   </div>
               </div>
           </div>
       </div><!-- chiude row  -->
<?php
if (!empty($send_fae_zip_package['val']) && $send_fae_zip_package['val']!='pec_SDI') {
?>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label for="image" class="col-sm-4 control-label">o consulta il canale telematico</label>
					<div class="col-sm-8">
						<br />
						<div class="col-sm-6">dal
<?php
						$gForm->CalendarPopup('date_ini', $form['date_ini_D'], $form['date_ini_M'], $form['date_ini_Y'], 'FacetSelect', 1);
?>
						</div>
						<div class="col-sm-6">al
<?php
						$gForm->CalendarPopup('date_fin', $form['date_fin_D'], $form['date_fin_M'], $form['date_fin_Y'], 'FacetSelect', 1);
?>
						</div>
						<div class="col-sm-12 text-center"><input name="Submit_list" type="submit" class="btn btn-success" value="VISUALIZZA" />
						</div>
					</div>
				</div>
			</div>
		</div><!-- chiude row  -->
<?php
	if (!empty($AltreFattF)) {
?>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label for="image" class="col-sm-4 control-label">Scegli la fattura da acquisire</label>
					<div class="col-sm-12">
						<br />
<?php
		if (is_array($AltreFattF)) {
			echo "<table class=\"Tlarge table table-striped table-bordered table-condensed\">";
			echo '<tr><th>Seleziona</th><th>Id SdI</th><th>Ricezione</th><th>Fornitore</th><th>Numero</th><th>Data</th></tr>';
			foreach ($AltreFattF as $AltraFattF) {
				echo '<tr>';
				echo '<td align="center"><input type="radio" name="selected_SdI" value="' . $AltraFattF[0] . '" /></td>';
				echo '<td>' . implode('</td><td>', $AltraFattF) . '</td>';
				echo '</tr>';
			}
			echo '</table><br />';
		} else {
			echo '<p>' . print_r($AltreFattF, true) . '</p>';
		}
?>
					</div>
				</div>
			</div>
		</div><!-- chiude row  -->
<?php
	}	
}
?>
		<div class="col-sm-12 text-right"><input name="Submit_file" type="submit" class="btn btn-warning" value="<?php echo $script_transl['btn_acquire']; ?>" />
		</div>
		<br /><br />
	</div> <!-- chiude container -->
</div><!-- chiude panel -->
<?php
	require("../../library/include/footer.php");
}
?>

