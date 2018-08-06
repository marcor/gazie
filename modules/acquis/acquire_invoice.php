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
$msg = array('err' => array(), 'war' => array());

function removeSignature($string, $filename) {
    $string = substr($string, strpos($string, '<?xml '));
    preg_match_all('/<\/.+?>/', $string, $matches, PREG_OFFSET_CAPTURE);
    $lastMatch = end($matches[0]);
	// trovo l'ultimo carattere del tag di chiusura per eliminare la coda
	$f_end = $lastMatch[1]+strlen($lastMatch[0]);
    $string = substr($string, 0, $f_end);
	// elimino le sequenze di caratteri aggiunti dalla firma (ancora da testare approfonditamente)
	$string = preg_replace ('/[\x{0004}]{1}[\x{0082}]{1}[\x{0002}\x{0003}\x{0004}]{1}[\s\S]{1}/i', '', $string);
	return preg_replace ('/[\x{0004}]{1}[\x{0081}]{1}[\s\S]{1}/i', '', $string);
}


if (!isset($_POST['fattura_elettronica_original_name'])) { // primo accesso
	$toDo = 'upload';
	$form['fattura_elettronica_original_name'] = '';
} else { // accessi successivi  
	$form['fattura_elettronica_original_name'] = filter_var($_POST['fattura_elettronica_original_name'], FILTER_SANITIZE_STRING);
	if (isset($_POST['Submit_file'])) { // conferma invio
        if (!empty($_FILES['userfile']['name'])) {
            if (!( $_FILES['userfile']['type'] == "application/pkcs7-mime" || $_FILES['userfile']['type'] == "text/xml")) {
				$msg['err'][] = 'filmim';
			} else {
                if (file_exists('../../data/files/' . $admin_aziend['codice'] . '/' . $_FILES['userfile']['name'])) { 
					$form['fattura_elettronica_original_name'] = $_FILES['userfile']['name'];
					$msg['war'][] = 'file_exists';
				} else if (move_uploaded_file($_FILES['userfile']['tmp_name'], '../../data/files/' . $admin_aziend['codice'] . '/' . $_FILES['userfile']['name'])) { // nessun errore
					$form['fattura_elettronica_original_name'] = $_FILES['userfile']['name'];
				} else { // no upload
					$msg['err'][] = 'no_upload';
				}
			}
		}
	} else if (isset($_POST['Download'])) {
		$name = filter_var($_POST['Download'], FILTER_SANITIZE_STRING);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;  filename="'.$name.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header("Content-Length: " . filesize('../../data/files/tmp/'.$name));
		readfile('../../data/files/tmp/'.$name);
		exit;
	}
	if (empty($form['fattura_elettronica_original_name'])) {  // non ho ancora fatto l'upload del file
		$toDo = 'upload';
	} else { // l'upload è stato fatto
		// controllo se il file è stato anche registrato sul database
		$tesdoc = gaz_dbi_get_row($gTables['tesdoc'], 'fattura_elettronica_original_name', $form["fattura_elettronica_original_name"]);
		if ($tesdoc){ // c'è anche sul database, è una modifica
			$toDo = 'update';
			$form['datreg'] = gaz_format_date($tesdoc['datreg'], false, false);
		} else { // non c'è sul database
			$toDo = 'insert';
			$msg['war'][] = 'no_db';			
			$form['datreg'] = date("d/m/Y");
		}

		// definisco l'array del form 
		$form['partner_cod'] = 0;
		$form['rows'] = array();

		// INIZIO acquisizione e pulizia file xml o p7m
		$file_name = '../../data/files/' . $admin_aziend['codice'] . '/' . $form['fattura_elettronica_original_name'];
		$p7mContent = file_get_contents($file_name);
		$invoiceContent = removeSignature($p7mContent,$file_name);
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML(utf8_encode($invoiceContent));
		$xpath = new DOMXpath($doc);
		
		// INIZIO CONTROLLI CORRETTEZZA FILE
		$val_err = libxml_get_errors(); // se l'xml è valido restituisce 1
		libxml_clear_errors();
		if (empty($val_err)){
			if ($doc->getElementsByTagName("FatturaElettronicaHeader")->length < 1) { // non esiste il nodo <FatturaElettronicaHeader>
				$msg['err'][] = 'invalid_fae';
			} else if (@$xpath->query("//FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0)->nodeValue <> $admin_aziend['pariva'] ) { // la partita IVA del cliente non coincide con la mia 
			$msg['err'][] = 'not_mine';
			} else {
				// controllo se ho il fornitore in archivio
				$form['partner_cost']=$admin_aziend['impacq']; 
				$form['partner_vat']=$admin_aziend['preeminent_vat']; 
				$form['partner_pag']=1; 
				$form['pariva'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0)->nodeValue;
				$anagrafica = new Anagrafica();
                $partner_with_same_pi = $anagrafica->queryPartners('*', "codice BETWEEN " . $admin_aziend['masfor'] . "000000 AND " . $admin_aziend['masfor'] . "999999 AND pariva = '" . $form['pariva']. "'", "pariva DESC", 0, 1);
                if ($partner_with_same_pi) { // ho già il fornitore sul piano dei conti
					$form['partner_cost'] = $partner_with_same_pi[0]['cosric']; // costo legato al fornitore 
					$form['partner_cod'] = $partner_with_same_pi[0]['codice'];
					$form['partner_pag'] = $partner_with_same_pi[0]['codpag']; // condizione di pagamento
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
		}
		// FINE CONTROLLI

		if (count($msg['err'])==0) { // non ho errori
			// INIZIO creazione array dei righi con la stessa nomenclatura usata in admin_docacq.php
			$DettaglioLinee = $doc->getElementsByTagName('DettaglioLinee');
			foreach ($DettaglioLinee as $item) {
				$nl=$item->getElementsByTagName('NumeroLinea')->item(0)->nodeValue;
				if ($item->getElementsByTagName("CodiceTipo")->length >= 1) {
					$form['rows'][$nl]['codart'] = trim($item->getElementsByTagName('CodiceTipo')->item(0)->nodeValue).'_'.trim($item->getElementsByTagName('CodiceValore')->item(0)->nodeValue); 
				} else {
					$form['rows'][$nl]['codart'] = ($item->getElementsByTagName("CodiceArticolo")->length >= 1 ? $item->getElementsByTagName('CodiceArticolo')->item(0)->nodeValue : '' );
				}
				$form['rows'][$nl]['descri'] = $item->getElementsByTagName('Descrizione')->item(0)->nodeValue; 
				if ($item->getElementsByTagName("Quantita")->length >= 1) {
					$form['rows'][$nl]['quanti'] = $item->getElementsByTagName('Quantita')->item(0)->nodeValue; 
					$form['rows'][$nl]['tiprig'] = 0;
				} else {
					$form['rows'][$nl]['quanti'] = '';
					$form['rows'][$nl]['tiprig'] = 1;
				}
				$form['rows'][$nl]['unimis'] =  ($item->getElementsByTagName("UnitaMisura")->length >= 1 ? $item->getElementsByTagName('UnitaMisura')->item(0)->nodeValue :	'');
				$form['rows'][$nl]['prelis'] = $item->getElementsByTagName('PrezzoUnitario')->item(0)->nodeValue; 
				if ($item->getElementsByTagName("Tipo")->length >= 1) { // ho uno sconto/maggiorazione
					$form['rows'][$nl]['sconto'] = ($item->getElementsByTagName('Percentuale')->item(0)->nodeValue == 'S' ? -$item->getElementsByTagName('Percentuale')->item(0)->nodeValue : $item->getElementsByTagName('Percentuale')->item(0)->nodeValue); 
				} else {
					$form['rows'][$nl]['sconto'] = '';
				}
				$form['rows'][$nl]['pervat'] = $item->getElementsByTagName('AliquotaIVA')->item(0)->nodeValue;
				$post_nl = $nl-1;
				if (empty($_FILES['userfile']['name'])) { // l'upload del file è già avvenuto e sono nei refresh successivi quindi riprendo i valori scelti e postati dall'utente
					$form['contor_'.$post_nl] = intval($_POST['contor_'.$post_nl]);
					$form['codvat_'.$post_nl] = intval($_POST['codvat_'.$post_nl]);
				} else { // al primo accesso dopo l'upload del file propongo dei costi e delle aliquote in base a quanto trovato sul database 
					$form['contor_'.$post_nl] = $form['partner_cost'];
					$expect_vat = gaz_dbi_get_row($gTables['aliiva'], 'codice', $form['partner_vat']);
					// analizzo le possibilità 
					if ( $expect_vat['aliquo'] == $form['rows'][$nl]['pervat']) { // coincide con le aspettative
						$form['codvat_'.$post_nl] = $expect_vat['codice'];
					} else { // non è quella che mi aspettavo allora provo a trovarne una tra quelle con la stessa aliquota
						$form['codvat_'.$post_nl] = 'non trovata';
					}
				}
			}
			// ricavo l'allegato, e se presente metterò un bottone per permettere il download
			$nf = $doc->getElementsByTagName('NomeAttachment')->item(0);
			if ($nf){
				$name_file = $nf->textContent;
				$att = $doc->getElementsByTagName('Attachment')->item(0);
				$base64 = $att->textContent;
				$bin = base64_decode($base64);
				file_put_contents('../../data/files/tmp/'.$name_file, $bin);
			}
		}
	}
}

require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new acquisForm();
?>
<script type="text/javascript">
    $(function () {
        $("#datreg").datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
        $("#datreg").change(function () {
            this.form.submit();
        });
    });
</script>
<form method="POST" name="form" enctype="multipart/form-data" id="add-invoice">
    <input type="hidden" name="fattura_elettronica_original_name" value="<?php echo $form['fattura_elettronica_original_name']; ?>">
<?php
if ($toDo=='insert' || $toDo=='update' ) {
	// INIZIO form che permetterà all'utente di interagire per (es.) imputare i vari costi al piano dei conti (contabilità) ed anche le eventuali merci al magazzino
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    if (count($msg['war']) > 0) { // ho un alert
        $gForm->gazHeadMessage($msg['war'], $script_transl['war'], 'war');
    }
	if (count($msg['err'])==0){
 ?>    
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12"><?php echo $script_transl['preview_text']; ?>
            </div>                    
        </div> <!-- chiude row  -->
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <div class="form-group">
                        <label for="datreg" class="col-sm-4 control-label"><?php echo $script_transl['datreg']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="datreg" name="datreg" value="<?php echo $form['datreg']; ?>">
                        </div>
                    </div>
                </div>                    
<?php		
		foreach ($form['rows'] as $k => $v) {
			$k--;
            $contor_dropdown = $gForm->selectAccount('contor_'.$k, $form['contor_'.$k], array('sub',1,3), '', false, "col-sm-8 small",'style="max-width: 350px;"', false, true);
			$codvat_dropdown = $gForm->selectFromDB('aliiva', 'codvat_'.$k, 'codice', $form['codvat_'.$k], 'aliquo', true, '-', 'descri', '', 'col-sm-8 small', null, '', false, true);            
			// creo l'array da passare alla funzione per la creazione della tabella responsive
            $resprow[$k] = array(
                array('head' => $script_transl["nrow"], 'class' => '',
                    'value' => $k+1),
                array('head' => $script_transl["codart"], 'class' => '',
                    'value' => $v['codart']),
                array('head' => $script_transl["descri"], 'class' => '',
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
					'value' => '', 'type' => ''),
                array('head' => $script_transl["tax"], 'class' => 'text-center numeric', 
					'value' => $codvat_dropdown, 'type' => ''),
                array('head' => '%', 'class' => 'text-center numeric', 
					'value' => $v['pervat'], 'type' => ''),
                array('head' => $script_transl["conto"], 'class' => 'text-center numeric', 
					'value' => $contor_dropdown, 'type' => '')
            );
		}
		$gForm->gazResponsiveTable($resprow, 'gaz-responsive-table');
?>	   <div class="col-sm-6">
<?php			
		if ($nf){
?>		
		Allegato: <input name="Download" type="submit" class="btn btn-default" value="<?php echo $name_file; ?>" />
<?php 
		} 
?>
	   </div>		   
	   <div class="col-sm-6 text-right">
		<input name="Submit" type="submit" class="btn btn-warning" value="<?php echo $script_transl['submit']; ?>" />
	   </div>		   
</form>
<?php			
	}
	// visualizzo la fattura fattura elettronica in calce
	$xslDoc = new DOMDocument();
	$xslDoc->load("../../library/include/fatturaordinaria_v1.2.1.xsl");
	$xslt = new XSLTProcessor();
	$xslt->importStylesheet($xslDoc);
	require("../../library/include/footer.php");
	echo $xslt->transformToXML($doc);
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
	   <div class="col-sm-12 text-right"><input name="Submit_file" type="submit" class="btn btn-warning" value="<?php echo $script_transl['btn_acquire']; ?>" />
	   </div>		   
	</div> <!-- chiude container -->
</div><!-- chiude panel -->
<?php
	require("../../library/include/footer.php");
}
?>
