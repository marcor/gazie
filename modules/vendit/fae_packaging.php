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
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
if (!ini_get('safe_mode')) { //se me lo posso permettere...
    ini_set('memory_limit', '128M');
    gaz_set_time_limit(0);
}
$msg = '';
require("../../library/include/electronic_invoice.inc.php");
$XMLdata = new invoiceXMLvars();
function getExtremeDocs($vat_register = '_', $vat_section = 1) {
    global $gTables;
    $vat_register = substr($vat_register, 0, 1);
    $docs = array();
    $where = "LENGTH(fe_cod_univoco)<>6 AND (fattura_elettronica_zip_package IS NULL OR fattura_elettronica_zip_package = '') AND seziva = $vat_section AND ";
    $orderby = "datfat ASC, protoc ASC";
    if ($vat_register=='V') { // in caso di fattura allegata allo scontrino mi baso sul numero e non sul protocollo
        $where .= "tipdoc = 'VCO' AND numfat > 0 AND clfoco > 100000000 AND datfat > '2018-01-01'";
		$orderby = "datfat ASC, numfat ASC";
    } else {
        $where .= "tipdoc LIKE '$vat_register" . "__'";
	}
    $from = $gTables['tesdoc'] . ' AS tesdoc
             LEFT JOIN ' . $gTables['clfoco'] . ' AS customer
             ON tesdoc.clfoco=customer.codice
             LEFT JOIN ' . $gTables['anagra'] . ' AS anagraf
             ON customer.id_anagra=anagraf.id';
    $result = gaz_dbi_dyn_query('tesdoc.*', $from, $where, $orderby, 0, 1);
    $row = gaz_dbi_fetch_array($result);
    if ($vat_register=='V') { // in caso di fattura allegata allo scontrino mi baso sul numero e non sul protocollo
		$docs['ini'] = array('proini' => $row['numfat'], 'date' => $row['datfat']);
		$orderby = "datfat DESC, numfat DESC";
    } else {
		$docs['ini'] = array('proini' => $row['protoc'], 'date' => $row['datfat']);
		$orderby = "datfat DESC, protoc DESC";
	}
    $result = gaz_dbi_dyn_query('*', $from, $where, $orderby, 0, 1);
    $row = gaz_dbi_fetch_array($result);
    $docs['fin'] = array('profin' => $row['protoc'], 'date' => $row['datfat']);
    if ($vat_register=='V') { // in caso di fattura allegata allo scontrino mi baso sul numero e non sul protocollo
		$docs['fin'] = array('profin' => $row['numfat'], 'date' => $row['datfat']);

    }
    return $docs;
}

// AGGIUNTA FUNZIONE PER RECUPERARE ULTIMO PROGRESSIVO PACCHETTO IN fae_flux
function getLastPack() // RESTITUISCE IL NUMERO PROGRESSIVO DELL'ULTIMO PACCHETTO CREATO/INVIATO
{
    global $gTables;
//    $where = "(filename_zip_package IS NOT NULL OR filename_zip_package != '') ";
    $where = "(filename_zip_package != '') AND exec_date LIKE '" . date('Y') . "%' ";
    $orderby = "filename_zip_package DESC";
    $from = $gTables['fae_flux'];
    $result = gaz_dbi_dyn_query('*', $from, $where, $orderby, 0, 1);
    $row = gaz_dbi_fetch_array($result);
	if(!$row){$row['filename_zip_package']='00000.zip';}
    return substr($row['filename_zip_package'],-9,5);
}
/// FINE aggiunta FUNZIONE

function getFAEunpacked($vat_register = '___', $vat_section = 1, $date = false, $protoc = 999999999) {
    global $gTables, $admin_aziend;
    $calc = new Compute;
    $vat_register = substr($vat_register, 0, 1);
    if ($date) {
        $p = ' AND (YEAR(datfat)*1000000+protoc) <= ' . (substr($date, 0, 4) * 1000000 + $protoc);
		if ($vat_register=='V') { // in caso di fattura allegata allo scontrino mi baso sul numero e non sul protocollo
			$p = 'AND numfat > 0 AND (YEAR(datfat)*1000000+numfat) <= ' . (substr($date, 0, 4) * 1000000 + $protoc);
		}
		$d = ' AND datfat <= ' . $date;
    } else {
        $d = '';
        $p = '';
    }
    $from = $gTables['tesdoc'] . ' AS tesdoc
             LEFT JOIN ' . $gTables['pagame'] . ' AS pay
             ON tesdoc.pagame=pay.codice
             LEFT JOIN ' . $gTables['clfoco'] . ' AS customer
             ON tesdoc.clfoco=customer.codice
             LEFT JOIN ' . $gTables['anagra'] . ' AS anagraf
             ON customer.id_anagra=anagraf.id';
    $where = "LENGTH(fe_cod_univoco)<>6 AND (fattura_elettronica_zip_package IS NULL OR fattura_elettronica_zip_package = '') AND seziva = $vat_section AND tipdoc LIKE '$vat_register" . "__' $d $p";
    $orderby = "datfat ASC, protoc ASC";
    $result = gaz_dbi_dyn_query('tesdoc.*,
                        pay.tippag,pay.numrat,pay.incaut,pay.tipdec,pay.giodec,pay.tiprat,pay.mesesc,pay.giosuc,pay.id_bank,
                        customer.codice,
                        customer.speban AS addebitospese,
                        CONCAT(anagraf.ragso1,\' \',anagraf.ragso2) AS ragsoc, anagraf.citspe, anagraf.prospe, anagraf.capspe, anagraf.country, anagraf.fe_cod_univoco, anagraf.pec_email, anagraf.e_mail', $from, $where, $orderby);
    $doc = array();
    $ctrlp = 0;

    $carry = 0;
    $ivasplitpay = 0;
    $somma_spese = 0;
    $totimpdoc = 0;
	$taxstamp = 0;
    $rit = 0;

    while ($tes = gaz_dbi_fetch_array($result)) {
		if ($vat_register=='V') { // in caso di fattura allegata allo scontrino mi baso sul numero fattura e non sul protocollo
			$tes['protoc']=$tes['numfat'];
		}
        if ($tes['protoc'] <> $ctrlp) { // la prima testata della fattura
            if ($ctrlp > 0 && ($doc[$ctrlp]['tes']['stamp'] >= 0.01 || $doc[$ctrlp]['tes']['taxstamp'] >= 0.01 )) { // non è il primo ciclo faccio il calcolo dei bolli del pagamento e lo aggiungo ai castelletti
                $calc->payment_taxstamp($calc->total_imp + $calc->total_vat + $carry - $rit - $ivasplitpay + $taxstamp, $doc[$ctrlp]['tes']['stamp'], $doc[$ctrlp]['tes']['round_stamp'] * $doc[$ctrlp]['tes']['numrat']);
                $calc->add_value_to_VAT_castle($doc[$ctrlp]['vat'], $taxstamp + $calc->pay_taxstamp, $admin_aziend['taxstamp_vat']);
                $doc[$ctrlp]['vat'] = $calc->castle;
                // aggiungo il castelleto conti
                if (!isset($doc[$ctrlp]['acc'][$admin_aziend['boleff']])) {
                    $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] = 0;
                }
                $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] += $taxstamp + $calc->pay_taxstamp;
            }
            $carry = 0;
            $ivasplitpay = 0;
            $cast_vat = array();
            $cast_acc = array();
            $somma_spese = 0;
            $totimpdoc = 0;
            $totimp_decalc = 0.00;
            $n_vat_decalc = 0;
            $spese_incasso = $tes['numrat'] * $tes['speban'];
            $taxstamp = 0;
            $rit = 0;
        } else {
            $spese_incasso = 0;
        }
        // aggiungo il bollo sugli esenti/esclusi se nel DdT c'è ma non è ancora stato mai aggiunto
        if ($tes['taxstamp'] >= 0.01 && $taxstamp < 0.01) {
            $taxstamp = $tes['taxstamp'];
        }
        if ($tes['virtual_taxstamp'] == 0 || $tes['virtual_taxstamp'] == 3) { //  se è a carico dell'emittente non lo aggiungo al castelletto IVA
            $taxstamp = 0.00;
        }
        if ($tes['traspo'] >= 0.01) {
            if (!isset($cast_acc[$admin_aziend['imptra']]['import'])) {
                $cast_acc[$admin_aziend['imptra']]['import'] = $tes['traspo'];
            } else {
                $cast_acc[$admin_aziend['imptra']]['import'] += $tes['traspo'];
            }
        }
        if ($spese_incasso >= 0.01) {
            if (!isset($cast_acc[$admin_aziend['impspe']]['import'])) {
                $cast_acc[$admin_aziend['impspe']]['import'] = $spese_incasso;
            } else {
                $cast_acc[$admin_aziend['impspe']]['import'] += $spese_incasso;
            }
        }
        if ($tes['spevar'] >= 0.01) {
            if (!isset($cast_acc[$admin_aziend['impvar']]['import'])) {
                $cast_acc[$admin_aziend['impvar']]['import'] = $tes['spevar'];
            } else {
                $cast_acc[$admin_aziend['impvar']]['import'] += $tes['spevar'];
            }
        }
        //recupero i dati righi per creare il castelletto
        $from = $gTables['rigdoc'] . ' AS rs
                    LEFT JOIN ' . $gTables['aliiva'] . ' AS vat
                    ON rs.codvat=vat.codice';
        $rs_rig = gaz_dbi_dyn_query('rs.*,vat.tipiva AS tipiva', $from, "rs.id_tes = " . $tes['id_tes'], "id_tes DESC");
        while ($r = gaz_dbi_fetch_array($rs_rig)) {
            if ($r['tiprig'] <= 1 || $r['tiprig'] == 90) { //ma solo se del tipo normale, forfait, vendita cespite
                //calcolo importo rigo
                $importo = CalcolaImportoRigo($r['quanti'], $r['prelis'], array($r['sconto'], $tes['sconto']));
                if ($r['tiprig'] == 1 || $r['tiprig'] == 90) { // se di tipo forfait o vendita cespite
                    $importo = CalcolaImportoRigo(1, $r['prelis'], $tes['sconto']);
                }
                //creo il castelletto IVA
                if (!isset($cast_vat[$r['codvat']]['impcast'])) {
                    $cast_vat[$r['codvat']]['impcast'] = 0;
                    $cast_vat[$r['codvat']]['ivacast'] = 0;
                    $cast_vat[$r['codvat']]['periva'] = $r['pervat'];
                    $cast_vat[$r['codvat']]['tipiva'] = $r['tipiva'];
                }
                $cast_vat[$r['codvat']]['impcast'] += $importo;
                $cast_vat[$r['codvat']]['ivacast'] += round(($importo * $r['pervat']) / 100, 2);
                $totimpdoc += $importo;
                //creo il castelletto conti
                if (!isset($cast_acc[$r['codric']]['import'])) {
                    $cast_acc[$r['codric']]['import'] = 0;
                }
                $cast_acc[$r['codric']]['import'] += $importo;
                if ($r['tiprig'] == 90) { // se è una vendita cespite lo indico sull'array dei conti
                    $cast_acc[$r['codric']]['asset'] = 1;
                }
                $rit += round($importo * $r['ritenuta'] / 100, 2);
                // aggiungo all'accumulatore l'eventuale iva non esigibile (split payment)
                if ($r['tipiva'] == 'T') {
                    $ivasplitpay += round(($importo * $r['pervat']) / 100, 2);
                }
            } elseif ($r['tiprig'] == 3) {
                $carry += $r['prelis'];
            }
        }
        $doc[$tes['protoc']]['tes'] = $tes;
        $doc[$tes['protoc']]['acc'] = $cast_acc;
        $doc[$tes['protoc']]['car'] = $carry;
        $doc[$tes['protoc']]['isp'] = $ivasplitpay;
        $doc[$tes['protoc']]['rit'] = $rit;
        $somma_spese += $tes['traspo'] + $spese_incasso + $tes['spevar'];
        $calc->add_value_to_VAT_castle($cast_vat, $somma_spese, $tes['expense_vat']);
        $doc[$tes['protoc']]['vat'] = $calc->castle;
        $ctrlp = $tes['protoc'];
    }
    if ($ctrlp > 0 && ($doc[$ctrlp]['tes']['stamp'] >= 0.01 || $taxstamp >= 0.01)) { // a chiusura dei cicli faccio il calcolo dei bolli del pagamento e lo aggiungo ai castelletti
        $calc->payment_taxstamp($calc->total_imp + $calc->total_vat + $carry - $rit - $ivasplitpay + $taxstamp, $doc[$ctrlp]['tes']['stamp'], $doc[$ctrlp]['tes']['round_stamp'] * $doc[$ctrlp]['tes']['numrat']);
        // aggiungo al castelletto IVA
        $calc->add_value_to_VAT_castle($doc[$ctrlp]['vat'], $taxstamp + $calc->pay_taxstamp, $admin_aziend['taxstamp_vat']);
        $doc[$ctrlp]['vat'] = $calc->castle;
        // aggiungo il castelleto conti
        if (!isset($doc[$ctrlp]['acc'][$admin_aziend['boleff']])) {
            $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] = 0;
        }
        $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] += $taxstamp + $calc->pay_taxstamp;
    }
    return $doc;
}

function computeTot($data) {
    $tax = 0;
    $vat = 0;
    foreach ($data as $k => $v) {
        $tax += $v['impcast'];
        $vat += round($v['impcast'] * $v['periva']) / 100;
    }
    $tot = $vat + $tax;
    return array('taxable' => $tax, 'vat' => $vat, 'tot' => $tot);
}

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    if (isset($_GET['vat_register'])) {
        $form['vat_register'] = substr($_GET['vat_register'], 0, 1);
    } else {
        $form['vat_register'] = 'F';
    }
    if (isset($_GET['vat_section'])) {
        $form['vat_section'] = intval($_GET['vat_section']);
    } else {
        $form['vat_section'] = 1;
    }
    $extreme = getExtremeDocs($form['vat_register'], $form['vat_section']);
    if ($extreme['ini']['proini'] > 0) {
        $form['this_date_Y'] = substr($extreme['fin']['date'], 0, 4);
        $form['this_date_M'] = substr($extreme['fin']['date'], 5, 2);
        $form['this_date_D'] = substr($extreme['fin']['date'], 8, 2);
    } else {
        $form['this_date_Y'] = date("Y");
        $form['this_date_M'] = date("m");
        $form['this_date_D'] = date("d");
    }
    $form['proini'] = $extreme['ini']['proini'];
    $form['profin'] = $extreme['fin']['profin'];
    if (isset($_GET['last'])) {
        $form['profin'] = intval($_GET['last']);
    }
    $form['year_ini'] = substr($extreme['ini']['date'], 0, 4);
    $form['year_fin'] = substr($extreme['fin']['date'], 0, 4);
	$ultimo_progressivo_invio = getLastPack();
	$progressivo_decimale=substr((decodeFromSendingNumber($ultimo_progressivo_invio,36)+1),-2); // aggiungo 1 al numero in base dieci dell'ultimo progressivo
	// inizio formattazione popolando l'array con valori adatti a quanto si aspetta la funzione encodeSendingNumber
	$data['sezione']=$form['vat_section'];
	$data['anno']=date("Y");
	$data['fae_reinvii']=substr(date("m"),0,1);
	$data['protocollo']=substr(date("md"),1).$progressivo_decimale;
	// fine formattazione array
	$progressivo_attuale=encodeSendingNumber($data,36);
	$form['filename']='IT'.$admin_aziend['codfis'].'_'.$progressivo_attuale.'.zip';
    $form['hidden_req'] = '';
} else {    // accessi successivi
    $form['vat_register'] = substr($_POST['vat_register'], 0, 1);
    $form['vat_section'] = intval($_POST['vat_section']);
    $form['this_date_Y'] = intval($_POST['this_date_Y']);
    $form['this_date_M'] = intval($_POST['this_date_M']);
    $form['this_date_D'] = intval($_POST['this_date_D']);
    $form['proini'] = intval($_POST['proini']);
    $form['profin'] = intval($_POST['profin']);
    $form['year_ini'] = intval($_POST['year_ini']);
    $form['year_fin'] = intval($_POST['year_fin']);
    $form['filename'] = substr($_POST['filename'],0,37);
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    if (!checkdate($form['this_date_M'], $form['this_date_D'], $form['this_date_Y']))
        $msg .= "0+";
    if ($form['hidden_req'] == 'vat_register' || $form['hidden_req'] == 'vat_section') {   //se cambio il registro
        $extreme = getExtremeDocs($form['vat_register'], $form['vat_section']);
        if ($extreme['ini']['proini'] > 0) {
            $form['this_date_Y'] = substr($extreme['fin']['date'], 0, 4);
            $form['this_date_M'] = substr($extreme['fin']['date'], 5, 2);
            $form['this_date_D'] = substr($extreme['fin']['date'], 8, 2);
        } else {
            $form['this_date_Y'] = date("Y");
            $form['this_date_M'] = date("m");
            $form['this_date_D'] = date("d");
        }
        $form['proini'] = $extreme['ini']['proini'];
        $form['profin'] = $extreme['fin']['profin'];
        $form['year_ini'] = substr($extreme['ini']['date'], 0, 4);
        $form['year_fin'] = substr($extreme['fin']['date'], 0, 4);
    }
    $form['hidden_req'] = '';
    $uts_this_date = mktime(0, 0, 0, $form['this_date_M'], $form['this_date_D'], $form['this_date_Y']);
    if (isset($_POST['submit']) && empty($msg)) {   //confermo la contabilizzazione
        $rs = getFAEunpacked($form['vat_register'], $form['vat_section'], strftime("%Y%m%d", $uts_this_date), $form['profin']);
        if (count($rs) > 0) {
			$zip = new ZipArchive;
			$res = $zip->open('../../data/files/'.$admin_aziend['codice'].'/'.$form['filename'], ZipArchive::CREATE);
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
					$file_content=create_XML_invoice($testate,$gTables,'rigdoc',false,$form['filename']);
					$zip->addFromString('IT'.$admin_aziend['codfis'].'_'.encodeSendingNumber($enc_data,36).'.xml', $file_content);
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


require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup'));
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
".
'
$(function() {
   $( "#dialog" ).dialog({
      autoOpen: false
   });

});
function confirMail(link){
   codice = link.id.replace("doc", "");
   $.fx.speeds._default = 500;
   targetUrl = $("#doc"+codice).attr("url");
   //alert (targetUrl);
   $("p#mail_adrs").html($("#doc"+codice).attr("mail"));
   $("p#mail_attc").html($("#doc"+codice).attr("namedoc"));
   $( "#dialog" ).dialog({
         modal: "true",
      show: "blind",
      hide: "explode",
         buttons: {
                      " ' . $script_transl['submit'] . ' ": function() {
                         window.location.href = targetUrl;
                      },
                      " ' . $script_transl['cancel'] . ' ": function() {
                        $(this).dialog("close");
                      }
                  }
         });
   $("#dialog" ).dialog( "open" );
}
</script>
';

echo "<form method=\"POST\" name=\"accounting\">\n";
?>
    <div style="display:none" id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
        <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
        <p class="ui-state-highlight" id="mail_adrs"></p>
    </div>
<?php
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['filename'] . "\" name=\"filename\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['proini'] . "\" name=\"proini\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['year_ini'] . "\" name=\"year_ini\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['year_fin'] . "\" name=\"year_fin\" />\n";
$gForm = new GAzieForm();
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'] . $script_transl['vat_section'];
$gForm->selectNumber('vat_section', $form['vat_section'], 0, 1, 9, 'FacetSelect', 'vat_section');
echo "</div>\n";
echo "<table class=\"Tsmall\">\n";
if (date('Y')>=2028 && date('m')==12) {
	echo '<tr><td colspan="2" class="FacetDataTDred">Archiviare gli xml del ' . (date('Y')-9) . ' (9 anni fa) per evitarne la sovrascrittura, prima di cominciare ad emettere fatture l\'anno prossimo</td></tr>' . "\n";
}
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
}
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['date'] . "</td><td  class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('this_date', $form['this_date_D'], $form['this_date_M'], $form['this_date_Y'], 'FacetSelect', 1);
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\" align=\"right\">" . $script_transl['vat_register'] . " </td><td  class=\"FacetDataTD\">\n";
$gForm->variousSelect('vat_register', $script_transl['vat_register_value'], $form['vat_register'], 'FacetSelect', 0, 'vat_register');
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['proini'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\">" . $form['proini'] . " / " . $form['year_ini'] . "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['profin'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"profin\" value=\"" . $form['profin'] . "\" align=\"right\" maxlength=\"9\"  /> / " . $form['year_fin'] . "</td>\n";
echo "</tr>\n";
echo "\t<tr class=\"FacetDataTD\">\n";
echo "\t<td class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"return\" value=\"" .
 $script_transl['return'] . "\"></td>\n";
echo '<td align="right"><input type="submit" class="warning" name="preview" value="';
echo $script_transl['view'].' del file: '. $form['filename'];
echo '">';
echo "\t </td>\n";
echo "\t </tr>\n";
echo "</table>\n";

//mostro l'anteprima
if (isset($_POST['preview'])) {
    $rs = getFAEunpacked($form['vat_register'], $form['vat_section'], strftime("%Y%m%d", $uts_this_date), $form['profin']);
    echo "<div align=\"center\"><b>" . $script_transl['preview'] . $form['filename']. "</b></div>";
    echo "<div class=\"box-primary table-responsive\">";
    echo "<table class=\"Tlarge table table-striped table-bordered table-condensed\">";
    echo "<th class=\"FacetFieldCaptionTD\">" . $script_transl['protoc'] . "</th>
		 <th class=\"FacetFieldCaptionTD\">" . $script_transl['doc_type'] . "</th>
         <th class=\"FacetFieldCaptionTD\">N.</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['date_reg'] . "</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['customer'] . "</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['taxable'] . "</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['vat'] . "</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['tot'] . "</th>\n";
    foreach ($rs as $k => $v) {
		// se ho il codice univoco non utilizzo la pec
		$cl_sdi='bg-primary';
		if (strlen($v['tes']['fe_cod_univoco'])>5){
			$v['tes']['pec_email']=$script_transl['sdi'].$v['tes']['fe_cod_univoco'];
		} else {
			if (strlen($v['tes']['pec_email'])<5){	// non ho nemmeno la pec
				$dest='&dest=E';
				if (strlen($v['tes']['e_mail']<5)){
					$dest='';
				}
				$cl_sdi='bg-danger';
				$v['tes']['pec_email']= '<a onclick="confirMail(this);return false;" id="doc' . $v['tes']["clfoco"] . '" url="stampa_richiesta_pecsdi.php?codice='.$v['tes']['clfoco'].$dest.'" href="#" title="Mailto: ' . $v['tes']["e_mail"] . '"
            mail="' . $v['tes']["e_mail"] . '" namedoc="Richiesta codice SdI o indirizzo PEC"  class="btn btn-xs btn-default btn-elimina">Questa '.$script_transl['doc_type_value'][$v['tes']['tipdoc']] .' finirà sul cassetto fiscale del cliente, richiedi la PEC o il codice SDI per recapitarla</a>';
			} else{
				$v['tes']['pec_email']=$script_transl['pec'].$v['tes']['pec_email'];
			}
		}
        $tot = computeTot($v['vat']);
        //fine calcolo totali
		$enc_data['sezione']=$v['tes']['seziva'];
		$enc_data['anno']=substr($v['tes']['datfat'],0,4);
 		$enc_data['protocollo']=$v['tes']['protoc'];
		if ($form['vat_register']=='V'){
			/* ATTENZIONE QUI!!!!
				se scelgo di generare l'xml di una fattura allegata allo scontrino per evitare di far coincidere il progressivo unico di invio file aggiungerò il valore 4 al numero di reinvio
			*/
			$v['tes']['fattura_elettronica_reinvii']=$v['tes']['fattura_elettronica_reinvii']+4;
		}
 		$enc_data['fae_reinvii']=$v['tes']['fattura_elettronica_reinvii'];
        echo '<tr class="FacetDataTD">
               <td>' . $v['tes']['protoc'] .'</td>
               <td>' . $script_transl['doc_type_value'][$v['tes']['tipdoc']] . '</td>
               <td>' . $v['tes']['numfat'] .'/'. $v['tes']['seziva'] .'</td>
               <td align="center">' . gaz_format_date($v['tes']['datfat']) . '</td>
               <td><a href="report_client.php?nome=' . $v['tes']['ragsoc'] . '" target="_blank">' . $v['tes']['ragsoc'] . '</a></td>
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
    }
    if (count($rs) > 0) {
        echo "\t<tr class=\"FacetFieldCaptionTD\">\n";
        echo '<td colspan="9" align="right"><input type="submit" name="submit" value="';
        echo $script_transl['submit'];
        echo '">';
        echo "\t </td>\n";
        echo "\t </tr>\n";
    } else {
        echo "\t<tr>\n";
        echo '<td colspan="9" align="center" class="FacetDataTDred">';
        echo $script_transl['errors'][1];
        echo "\t </td>\n";
        echo "\t </tr>\n";
    }
}
?>
</table>
</div>
</form>
<?php
require("../../library/include/footer.php");
?>
