<?php

/* $
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

class CBIPaymentXMLvars {
    function setPaymentsVars($gTables,$bank) {
		// qui setto tutti i valori per l'intestazione 
        $this->azienda = gaz_dbi_get_row($gTables['aziend'], 'codice', $_SESSION['company_id']);
        $this->bank = gaz_dbi_get_row($gTables['clfoco'].' LEFT JOIN '.$gTables['banapp'].' ON '.$gTables['clfoco'].'.banapp = '.$gTables['banapp'].".codice", $gTables['clfoco'].'.codice',$bank);
        $this->OthrId = (intval($this->azienda['pariva'])>=100)?$this->azienda['pariva']:$this->azienda['codfis'];
		$this->CreDtTm = date('Y-m-d\TH:i:s');
        $this->MsgId = base64_encode($this->CreDtTm);
    }
}

function create_XML_CBIPayment($gTables,$bank,$data) {
	// in $data dovrò passare tutti i dati necessari per la creazione degli elementi <CdtTrfTxInf>
	// le chiavi sono: EndToEndId (id univoco) ,InstdAmt (importo), Nm (descrizione creditore), IBAN (iban accredito), Ustrd (descrizione debito pagato) ognuno creerà il relativo elemento dentro <CdtTrfTxInf>
	// EndToEndId non è indispensabile e se EndToEndId[0] non è valorizzato ne verrà creato uno random e non verrà considerata la presenza sugli eventuali altri indici in quanto ne verrà creato uno random
    $XMLvars = new CBIPaymentXMLvars();
    $domDoc = new DOMDocument;
	$domDoc->preserveWhiteSpace = false;
	$domDoc->formatOutput = true;
    $XMLvars->setPaymentsVars($gTables,$bank);
	$domDoc->load("../../library/include/template_CBIPaymentRequest.xml");
	$xpath = new DOMXPath($domDoc);
	$results = $xpath->query("//GrpHdr/MsgId")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->MsgId);
	$results->appendChild($attrVal);
	$results = $xpath->query("//GrpHdr/CreDtTm")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->CreDtTm);
	$results->appendChild($attrVal);
	$results = $xpath->query("//GrpHdr/InitgPty/Nm")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->bank['descri']);
	$results->appendChild($attrVal);
	$results = $xpath->query("//GrpHdr/InitgPty/Id/OrgId/Othr/Id")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->bank['cuc_code']);
	$results->appendChild($attrVal);
	$results = $xpath->query("//PmtInf/PmtInfId")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->MsgId);
	$results->appendChild($attrVal);
	$results = $xpath->query("//PmtInf/ReqdExctnDt")->item(0);
	$attrVal = $domDoc->createTextNode(substr($XMLvars->CreDtTm,0,10));
	$results->appendChild($attrVal);
	$results = $xpath->query("//PmtInf/Dbtr/Nm")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->bank['descri']);
	$results->appendChild($attrVal);
	$results = $xpath->query("//PmtInf/Dbtr/Id/OrgId/Othr/Id")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->OthrId);
	$results->appendChild($attrVal);
	$results = $xpath->query("//PmtInf/DbtrAcct/Id/IBAN")->item(0);
	$attrVal = $domDoc->createTextNode($XMLvars->bank['iban']);
	$results->appendChild($attrVal);
	$results = $xpath->query("//PmtInf/DbtrAgt/FinInstnId/ClrSysMmbId/MmbId")->item(0);
	$attrVal = $domDoc->createTextNode(str_pad($XMLvars->bank['codabi'],5,'0',STR_PAD_LEFT));
	$results->appendChild($attrVal);
	// creo gli elementi dei singoli bonifici
	$nbon=1;
	foreach($data as $v){
		$PmtInf = $xpath->query("//PmtInf")->item(0);
        $el = $domDoc->createElement("CdtTrfTxInf", "");
            $el1 = $domDoc->createElement("PmtId", "");
				$el2 = $domDoc->createElement("InstrId", $nbon);
				$el1->appendChild($el2);
				$el2 = $domDoc->createElement("EndToEndId", $XMLvars->MsgId.$nbon);
				$el1->appendChild($el2);
			$el->appendChild($el1);
            $el1 = $domDoc->createElement("PmtTpInf", "");
				$el2 = $domDoc->createElement("CtgyPurp", "");
					$el3 = $domDoc->createElement("Cd", "OTHR");
					$el2->appendChild($el3);
				$el1->appendChild($el2);
			$el->appendChild($el1);
            $el1 = $domDoc->createElement("Amt", "");
				$el2 = $domDoc->createElement("InstdAmt", $v['InstdAmt']);
				$newel2 = $el1->appendChild($el2);
				$newel2->setAttribute("Ccy", "EUR");
			$el->appendChild($el1);
            $el1 = $domDoc->createElement("Cdtr", "");
				$el2 = $domDoc->createElement("Nm", $v['Nm']);
				$el1->appendChild($el2);
			$el->appendChild($el1);
            $el1 = $domDoc->createElement("CdtrAcct", "");
				$el2 = $domDoc->createElement("Id", "");
					$el3 = $domDoc->createElement("IBAN", $v['IBAN']);
					$el2->appendChild($el3);
				$el1->appendChild($el2);
			$el->appendChild($el1);
            $el1 = $domDoc->createElement("RmtInf", "");
				$el2 = $domDoc->createElement("Ustrd", $v['Ustrd']);
				$el1->appendChild($el2);
			$el->appendChild($el1);
		$PmtInf->appendChild($el);
		$nbon++;	
	}
	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=provaCBI.xml");
	print $domDoc->saveXML();
}
?>
