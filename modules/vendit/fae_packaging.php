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
$admin_aziend = checkAdmin();
if (!ini_get('safe_mode')) { //se me lo posso permettere...
    ini_set('memory_limit', '128M');
    gaz_set_time_limit(0);
}
$msg = '';
require("../../library/include/electronic_invoice.inc.php");
$XMLdata = new invoiceXMLvars();
require("../../library/include/header.php");
$gForm = new venditForm();
$invoices = $gForm->getFAEunpacked();


if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
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
	$form['hidden_req'] = '';
	// imposto i limiti su tutti i documenti impacchettabili
	$form['packable']=(count($invoices)>1)?$invoices['head']:[];
} else {    // accessi successivi
	$form['filename'] = substr($_POST['filename'],0,37);
	$form['hidden_req'] = htmlentities($_POST['hidden_req']);
	if (isset($_POST['submit']) && empty($msg)) {   //confermo la contabilizzazione
		$rs = getFAEunpacked();
		if (count($rs) > 0) {
			$zip = new ZipArchive;
			$res = $zip->open(DATA_DIR.'files/'.$admin_aziend['codice'].'/'.$form['filename'], ZipArchive::CREATE);
			if ($res === TRUE) {
				// ho creato l'archivio e adesso lo riempio con i file xml delle singole fatture
				foreach ($rs as $k => $v) {
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
						$enc_data['protocollo']=$v['tes']['protoc'];
						$enc_data['fae_reinvii']=$v['tes']['fattura_elettronica_reinvii'];
					}
					// aggiorno anche il flusso SdI
					$fn_ori = 'IT'.$admin_aziend['codfis'].'_'.encodeSendingNumber($enc_data,36).'.xml';
					gaz_dbi_query("UPDATE " . $gTables['fae_flux'] . " SET filename_zip_package = '".$form['filename']."' WHERE filename_ori = '".$fn_ori."'");
					$file_content=create_XML_invoice($testate,$gTables,'rigdoc',false,$form['filename']);
					$zip->addFromString($fn_ori, $file_content);
				}
				$zip->close();

				header("Location: report_fae_sdi.php");
			} else {
				echo 'La creazione del pacchetto è fallita!';
			}
			exit;
		} else {
			$msg .= "1+";
		}
	}
}

$script_transl = HeadMain('','','fae_packaging');
?>
<form method="POST" name="pack">
<input type="hidden" value="<?php $form['hidden_req']; ?>" name="hidden_req" />
<input type="hidden" value="<?php $form['filename']; ?>" name="filename" />

<?php
?>
<table class="Tsmall" align="center">
<?php 
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
}
?>
</table>
<div align="center"><b><?php echo count($invoices['data'])>0?$script_transl['preview'].$form['filename']:'<span class="text-danger">'.$script_transl['errors'][1].'</span>'; ?> </b></div>
<div class="panel panel-success table-responsive">
<table class="table table-striped">
	<th class="FacetFieldCaptionTD"><?php echo $script_transl['protoc']; ?> </th>
	<th class="FacetFieldCaptionTD"><?php echo $script_transl['doc_type']; ?> </th>
    <th class="FacetFieldCaptionTD">N.</th>
    <th class="FacetFieldCaptionTD"><?php echo $script_transl['date_reg']; ?> </th>
    <th class="FacetFieldCaptionTD"><?php echo $script_transl['customer']; ?> </th>
    <th class="FacetFieldCaptionTD"><?php echo $script_transl['taxable']; ?> </th>
    <th class="FacetFieldCaptionTD"><?php echo $script_transl['vat']; ?> </th>
    <th class="FacetFieldCaptionTD"><?php echo $script_transl['tot']; ?> </th>
<?php
foreach ($invoices['data'] as $k => $v) {
	// se ho il codice univoco non utilizzo la pec
	$cl_sdi='bg-success';
	if ($v['tes']['ctrlreg']=='X'){
		$cl_sdi='bg-warning';
		$v['tes']['pec_email']= 'su Cassetto Fiscale aziendale (Reverse Charge '.$v['tes']['status'].')';
	} elseif (strlen($v['tes']['fe_cod_univoco'])>5){
		$v['tes']['pec_email']=$script_transl['sdi'].$v['tes']['fe_cod_univoco'];
	} else {
		if (strlen($v['tes']['pec_email'])<5){	// non ho nemmeno la pec
			$dest='&dest=E';
			if (strlen($v['tes']['e_mail']<5)){
				$dest='';
			}
			$cl_sdi='bg-danger';
			$v['tes']['pec_email']= 'su Cassetto Fiscale del cliente (non ho PEC o codice SDI)';
		} else{
			$v['tes']['pec_email']=$script_transl['pec'].$v['tes']['pec_email'];
		}
	}
    $tot = $gForm->computeTotFromVatCastle($v['vat']);
    //fine calcolo totali
	$enc_data['sezione']=$v['tes']['seziva'];
	$enc_data['anno']=substr($v['tes']['datfat'],0,4);
	if (substr($k,0,1)=='V'){
		// ATTENZIONE QUI!!!!se scelgo di generare l'xml di una fattura allegata allo scontrino per evitare di far coincidere il progressivo unico di invio file aggiungerò il valore 4 al numero di reinvio
		$v['tes']['fattura_elettronica_reinvii']=$v['tes']['fattura_elettronica_reinvii']+4;
		$v['tes']['protoc']=$v['tes']['numfat'];
	} elseif (substr($k,0,1)=='X'){
		$v['tes']['fattura_elettronica_reinvii']=$v['tes']['fattura_elettronica_reinvii']+5;
	}
	$enc_data['protocollo']=$v['tes']['protoc'];
	$enc_data['fae_reinvii']=$v['tes']['fattura_elettronica_reinvii'];
	
// INIZIO VIEW RIGHI
    echo '<tr class="FacetDataTD">
           <td>' . $v['tes']['protoc'] .'</td>
           <td>' . $script_transl['doc_type_value'][$v['tes']['tipdoc']] .' '.$v['tes']['flux_status']. '</td>
           <td>' . $v['tes']['numfat'] .'/'. $v['tes']['seziva'] .'</td>
           <td align="center">' . gaz_format_date($v['tes']['datfat']) . '</td>
           <td><a href="'.(($v['tes']['ctrlreg']=='X')?'../acquis/report_fornit':'report_client').'.php?nome=' . $v['tes']['ragsoc'] . '" target="_blank">' . $v['tes']['ragsoc'] . '</a></td>
           <td align="right">' . gaz_format_number($tot['taxable']) . '</td>
           <td align="right">' . gaz_format_number($tot['vat']) . '</td>
           <td align="right">' . gaz_format_number($tot['tot']) . "</td>
           </tr>\n";
	if ($v['tes']['country'] == 'IT') {
		$check_failed_message = '';
		if (strlen($v['tes']['citspe']) < 2) {
			$check_failed_message = 'Localit&agrave; non valida';
		}
		if (strlen($v['tes']['prospe']) != 2) {
			$check_failed_message = 'Sigla della provincia non valida';
		}
		if (strlen($v['tes']['capspe']) != 5 || !is_numeric($v['tes']['capspe'])) {
			$check_failed_message = 'CAP non valido';
		}
	}
	if (!empty($check_failed_message)) {
    echo '<tr class="FacetDataTD">
           <td colspan="5" class="bg-danger" align="right">' . $check_failed_message . '</td>
           <td colspan="3"></td>
           </tr>';
	}
    echo '<tr class="FacetDataTD">
           <td colspan="5" align="right">produrrà il file IT'.$admin_aziend['codfis'].'_'.encodeSendingNumber($enc_data,36).'.xml che dovrà essere inviato tramite SdI </td>
           <td colspan="3" class="'.$cl_sdi.'">'.$v['tes']['pec_email'] . '</td>
           </tr>';
// FINE VIEW RIGHI

}
if (count($invoices['data']) > 0) {
?>
<tr><td colspan="9" align="center"><input class="btn btn-warning" type="submit" name="submit" value="<?php echo $script_transl['submit']; ?>"></td></tr>
<?php
}
?>
</table>
</div>
</form>
<?php
require("../../library/include/footer.php");
?>
