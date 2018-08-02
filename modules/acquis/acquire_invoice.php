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
$preview=false;

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


if (isset($_POST['Submit'])) { // conferma tutto
    if (!empty($_FILES['userfile']['name'])) {
        if (!( $_FILES['userfile']['type'] == "application/pkcs7-mime" || $_FILES['userfile']['type'] == "text/xml")) {
			$msg['err'][] = 'filmim';
		} else {
			$form['rows'] = array();
			$preview=true;
			// INIZIO acquisizione e pulizia file xml o p7m
			$file_name = $_FILES['userfile']['tmp_name'];
			$p7mContent=file_get_contents($file_name);
			$invoiceContent = removeSignature($p7mContent);
			$doc = new DOMDocument;
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput = true;
			$doc->loadXML(utf8_encode($invoiceContent));
			$xpath = new DOMXpath($doc);
			
			// INIZIO CONTROLLI CORRETTEZZA FILE
			$val_err = libxml_get_errors(); // se l'xml è valido restituisce 1
			libxml_clear_errors();
			if (empty($val)){
				if ($doc->getElementsByTagName("FatturaElettronicaHeader")->length < 1) { // non esiste il nodo <FatturaElettronicaHeader>
					$msg['err'][] = 'invalid_fae';
				} else if (@$xpath->query("//FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0)->nodeValue <> $admin_aziend['pariva'] ) { // la partita IVA del cliente non coincide con la mia 
				$msg['err'][] = 'not_mine';
				} else {
					// controllo se ho il fornitore in archivio
					$form['pariva'] = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0)->nodeValue;
					$anagrafica = new Anagrafica();
                    $partner_with_same_pi = $anagrafica->queryPartners('*', "codice BETWEEN " . $admin_aziend['masfor'] . "000000 AND " . $admin_aziend['masfor'] . "999999 AND pariva = '" . $form['pariva']. "'", "pariva DESC", 0, 1);
                    if ($partner_with_same_pi) { // se non ho già un fornitore sul piano dei conti
						// $msg['war'][] = 'ok_suppl'; il fornitore è già in archivio, non allerto
                    } else { // provo a vedere nelle anagrafiche
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

			if (count($msg['err'])==0){ // non ho errori
			
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
					$form['rows'][$nl]['pervat'] = $item->getElementsByTagName('AliquotaIVA')->item(0)->nodeValue;;
				}
			
			}

			}
    }
}

require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new acquisForm();
?>
<form method="POST" name="form" enctype="multipart/form-data" id="add-invoice">
<?php
if ($preview) {
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
<?php			
			foreach ($form['rows'] as $k => $v) {
				$k--;
                // creo l'array da passare alla funzione per la creazione della tabella responsive
                $resprow[$k] = array(
                    array('head' => $script_transl["nrow"], 'class' => '',
                        'value' => $k),
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
						'value' => $v['pervat'], 'type' => '')
                );
			}
			$gForm->gazResponsiveTable($resprow, 'gaz-responsive-table');
?>
</form>
<?php			
	}
	// ricavo l'allegato, e se presente metterò un bottone per permettere il download
	$nf = $doc->getElementsByTagName('NomeAttachment')->item(0);
	if ($nf){
		$name_file = $nf->textContent;
		$att = $doc->getElementsByTagName('Attachment')->item(0);
		$base64 = $att->textContent;
		$bin = base64_decode($base64);
		file_put_contents($name_file, $bin);
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
	   <div class="col-sm-12 text-right"><input name="Submit" type="submit" class="btn btn-warning" value="<?php echo $script_transl['btn_acquire']; ?>" />
	   </div>		   
	</div> <!-- chiude container -->
</div><!-- chiude panel -->
<?php
	require("../../library/include/footer.php");
}
?>
