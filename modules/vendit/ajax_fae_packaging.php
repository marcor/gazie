<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();

require("../../library/include/electronic_invoice.inc.php");
$XMLdata = new invoiceXMLvars();
$gForm = new venditForm();
$id_tes=$_POST['id_tes'];
$invoices = $gForm->getFAEunpacked(TRUE,$id_tes);

$ultimo_progressivo_invio = $gForm->getLastPack();
$progressivo_decimale=substr((decodeFromSendingNumber($ultimo_progressivo_invio,36)+1),-2); // aggiungo 1 al numero in base dieci dell'ultimo progressivo
// inizio formattazione popolando l'array con valori adatti a quanto si aspetta la funzione encodeSendingNumber
$filename_data['sezione']=1;
$filename_data['anno']=date("Y");
$filename_data['fae_reinvii']=substr(date("m"),0,1);
$filename_data['protocollo']=substr(date("md"),1).$progressivo_decimale;
// fine formattazione array
$progressivo_attuale=encodeSendingNumber($filename_data,36);
$form['filename']='IT'.$admin_aziend['codfis'].'_'.$progressivo_attuale.'.zip';

$form['packable']=(count($invoices)>1)?$invoices['head']:[];

	$form['filename'] = substr($form['filename'],0,37);	
		 
	if (count($invoices['data']) > 0) {
		$zip = new ZipArchive;
		$res = $zip->open(DATA_DIR.'files/'.$admin_aziend['codice'].'/'.$form['filename'], ZipArchive::CREATE);
		if ($res === TRUE) {
			// ho creato l'archivio e adesso lo riempio con i file xml delle singole fatture
			foreach ($invoices['data'] as $k => $v) {
				if ($v['tes']['protoc']>$form['packable'][$v['tes']['seziva']][$v['tes']['ctrlreg']]['max']) { // non impacchetto i protocolli che superano i limiti scelti dall'utente
					continue;
				}
				if ($v['tes']['tipdoc']=='VCO'){ // in caso di fattura allegata allo scontrino
					//vado a modificare le testate valorizzando con il nome del file zip (pacchetto) in cui desidero siano contenuti i file xml delle fatture selezionate
					gaz_dbi_query("UPDATE " . $gTables['tesdoc'] . " SET fattura_elettronica_zip_package = '".$form['filename']."' WHERE seziva = " .$v['tes']['seziva']. " AND numfat = " .$v['tes']['numfat']. " AND YEAR(datfat)=".substr($v['tes']['datfat'],0,4)." AND tipdoc = 'VCO'");
					//recupero i dati
					$testate = gaz_dbi_dyn_query("*", $gTables['tesdoc']," tipdoc = 'VCO' AND seziva = " .$v['tes']['seziva']. " AND YEAR(datfat)=".substr($v['tes']['datfat'],0,4)." AND numfat = " .$v['tes']['numfat'],'datemi ASC, numdoc ASC, id_tes ASC');
					$enc_data['sezione']=$v['tes']['seziva'];
					$enc_data['anno']=substr($v['tes']['datfat'],0,4);
					$enc_data['protocollo']=$v['tes']['numfat'];
					$enc_data['fae_reinvii']=$v['tes']['fattura_elettronica_reinvii']+4;
				} else {
					//vado a modificare le testate valorizzando con il nome del file zip (pacchetto) in cui desidero siano contenuti i file xml delle fatture selezionate
					gaz_dbi_query("UPDATE " . $gTables['tesdoc'] . " SET fattura_elettronica_zip_package = '".$form['filename']."' WHERE seziva = " .$v['tes']['seziva']. " AND protoc = " .$v['tes']['protoc']. " AND YEAR(datfat)=".substr($v['tes']['datfat'],0,4)." AND tipdoc = '" .$v['tes']['tipdoc']. "';");
					//recupero i dati
					$testate = gaz_dbi_dyn_query("*", $gTables['tesdoc']," tipdoc LIKE '" .$v['tes']['tipdoc']. "' AND seziva = " .$v['tes']['seziva']. " AND YEAR(datfat)=".substr($v['tes']['datfat'],0,4)." AND protoc = " .$v['tes']['protoc'],'datemi ASC, numdoc ASC, id_tes ASC');
					$enc_data['sezione']=$v['tes']['seziva'];
					$enc_data['anno']=substr($v['tes']['datfat'],0,4);
					$enc_data['fae_reinvii']=$v['tes']['fattura_elettronica_reinvii'];
					$enc_data['protocollo']=$v['tes']['protoc'];
					if($v['tes']['ctrlreg']=='X'){ // è una autofattura reverse charge
						/* considerando che la funzione si attiene al seguente specchietto normalmente usato per le fatture di vendita
						  ------------------------- SCHEMA DEI DATI PER FATTURE NORMALI  ---------------
						  |   SEZIONE IVA   |  ANNO DOCUMENTO  | N.REINVII |    NUMERO PROTOCOLLO     |
						  |     INT (1)     |      INT(1)      |   INT(1)  |        INT(5)            |
						  |        3        |        9         |     9     |        99999             |
						  | $data[sezione]  |   $data[anno] $data[fae_reinvii]  $data[protocollo]     |
						  ------------------------------------------------------------------------------
						  dovrò modificare la matrice in questo con valore fisso "59" sulle prime due cifre, ovvero parto da un numero decimale 59000000
						  ------------------------- SCHEMA DEI DATI PER AUTOFATTURE  ------------------
						  |  VALORE FISSO   |  ANNO DOCUMENTO  | N.REINVII |    NUMERO PROTOCOLLO     |
						  |    INT (2 )     |      INT(1)      |   INT(1)  |        INT(4)            |
						  |       "59       |        9         |     9     |         9999             |
						  | $data[sezione]  |   $data[anno] $data[fae_reinvii]  $data[protocollo]     |
						  -------------------------------------------------------------------------------------------------------------------
						 */
						$enc_data['sezione']=5;
						$enc_data['anno']=2009; // la funzione considererà solo "9"
						$enc_data['fae_reinvii']=substr($v['tes']['datfat'],3,1);
						$enc_data['protocollo']= intval($v['tes']['fattura_elettronica_reinvii']*10000+$v['tes']['protoc']);
					}
				}
				// aggiorno anche il flusso SdI
				$fn_ori = 'IT'.$admin_aziend['codfis'].'_'.encodeSendingNumber($enc_data,36).'.xml';
				gaz_dbi_query("UPDATE " . $gTables['fae_flux'] . " SET filename_zip_package = '".$form['filename']."' WHERE filename_ori = '".$fn_ori."'");
				$file_content=create_XML_invoice($testate,$gTables,'rigdoc',false,$form['filename']);
				$zip->addFromString($fn_ori, $file_content);

			}
			$zip->close();
			echo "send_fae_package.php?fn=",$form['filename'];// restituisco l'url da chiamare composto con il nome del file zip appena salvato
			
		} else {
			echo 'Errore La creazione del pacchetto è fallita!';
		}		
	} else {
		echo "Errore non ci sono fatture";
	}