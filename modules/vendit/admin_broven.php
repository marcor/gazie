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
$msgtoast = "";
$msg = "";
$show_artico_composit = gaz_dbi_get_row($gTables['company_config'], 'var', 'show_artico_composit');
$tipo_composti = gaz_dbi_get_row($gTables['company_config'], 'var', 'tipo_composti');
function getDayNameFromDayNumber($day_number) {
    return ucfirst(utf8_encode(strftime('%A', mktime(0, 0, 0, 3, 19 + $day_number, 2017))));
}

$upd_mm = new magazzForm;
$docOperat = $upd_mm->getOperators();
if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and ! isset($_GET['id_tes'])) and ! isset($_GET['tipdoc'])) {
    header("Location: " . $form['ritorno']);
    exit;
}


if (isset($_POST['newdestin'])) {
    $_POST['id_des'] = 0;
    $_POST['destin'] = "";
}

if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
    $toDo = 'update';
	$class_btn_confirm='btn-warning';
} else {
    $toDo = 'insert';
	$class_btn_confirm='btn-success';
}

if ((isset($_POST['Insert'])) or ( isset($_POST['Update']))) {   //se non e' il primo accesso
    //qui si dovrebbe fare un parsing di quanto arriva dal browser...
    $form['id_tes'] = $_POST['id_tes'];
    $anagrafica = new Anagrafica();
    $cliente = $anagrafica->getPartner($_POST['clfoco']);
    $form['hidden_req'] = $_POST['hidden_req'];
    // ...e della testata
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }
    $form['print_total'] = intval($_POST['print_total']);
    $form['delivery_time'] = intval($_POST['delivery_time']);
    $form['day_of_validity'] = intval($_POST['day_of_validity']);
    $form['cosear'] = $_POST['cosear'];
    $form['seziva'] = $_POST['seziva'];
    $form['tipdoc'] = $_POST['tipdoc'];
    $form['gioemi'] = $_POST['gioemi'];
    $form['mesemi'] = $_POST['mesemi'];
    $form['annemi'] = $_POST['annemi'];
    $form['weekday_repeat'] = $_POST['weekday_repeat'];
    $form['giotra'] = $_POST['giotra'];
    $form['mestra'] = $_POST['mestra'];
    $form['anntra'] = $_POST['anntra'];
    $form['oratra'] = $_POST['oratra'];
    $form['mintra'] = $_POST['mintra'];
    $form['protoc'] = $_POST['protoc'];
    $form['numdoc'] = $_POST['numdoc'];
    $form['numfat'] = $_POST['numfat'];
    $form['datfat'] = $_POST['datfat'];
    $form['clfoco'] = substr($_POST['clfoco'], 0, 13);
    //tutti i controlli su  tipo di pagamento e rate
    $form['speban'] = $_POST['speban'];
    $form['numrat'] = $_POST['numrat'];
    $form['expense_vat'] = intval($_POST['expense_vat']);
    $form['virtual_taxstamp'] = intval($_POST['virtual_taxstamp']);
    $form['taxstamp'] = floatval($_POST['taxstamp']);
    $form['stamp'] = floatval($_POST['stamp']);
    $form['round_stamp'] = intval($_POST['round_stamp']);
    $form['pagame'] = $_POST['pagame'];
    $form['change_pag'] = $_POST['change_pag'];
    if ($form['change_pag'] != $form['pagame']) {  //se e' stato cambiato il pagamento
        $new_pag = gaz_dbi_get_row($gTables['pagame'], "codice", $form['pagame']);
        if ($toDo == 'update') {  //se � una modifica mi baso sulle vecchie spese
            $old_header = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", $form['id_tes']);
            if ($cliente['speban'] == "S" && ($new_pag['tippag'] == 'T' || $new_pag['tippag'] == 'B')) {
                if ($old_header['speban'] > 0) {
                    $form['speban'] = $old_header['speban'];
                } else {
                    $form['speban'] = $admin_aziend['sperib'];
                }
            } else {
                $form['speban'] = 0.00;
            }
        } else { //altrimenti, se previste, mi avvalgo delle nuove dell'azienda
            if ($cliente['speban'] == "S" && ($new_pag['tippag'] == 'B' || $new_pag['tippag'] == 'T')) {
                $form['speban'] = $admin_aziend['sperib'];
            } else {
                $form['speban'] = 0;
            }
        }
        if ($new_pag['tippag'] == 'T' && $form['stamp'] == 0) {  //se il pagamento prevede il bollo
            $form['stamp'] = $admin_aziend['perbol'];
            $form['round_stamp'] = $admin_aziend['round_bol'];
        } elseif ($new_pag['tippag'] != 'T') {
            $form['stamp'] = 0;
            $form['round_stamp'] = 0;
        }
        $form['numrat'] = $new_pag['numrat'];
        $form['pagame'] = $_POST['pagame'];
        $form['change_pag'] = $_POST['pagame'];
    }
    $form['banapp'] = $_POST['banapp'];
    $form['vettor'] = $_POST['vettor'];
    $form['id_agente'] = intval($_POST['id_agente']);
    $form['net_weight'] = floatval($_POST['net_weight']);
    $form['gross_weight'] = floatval($_POST['gross_weight']);
    $form['units'] = intval($_POST['units']);
    $form['volume'] = floatval($_POST['volume']);
    $form['listin'] = $_POST['listin'];
    $form['spediz'] = $_POST['spediz'];
    $form['portos'] = $_POST['portos'];
    $form['imball'] = $_POST['imball'];
    $form['destin'] = $_POST['destin'];
    $form['id_des'] = $_POST['id_des'];
    $form['id_des_same_company'] = intval($_POST['id_des_same_company']);
    $form['traspo'] = $_POST['traspo'];
    $form['spevar'] = $_POST['spevar'];
    $form['cauven'] = $_POST['cauven'];
    $form['caucon'] = $_POST['caucon'];
    $form['caumag'] = $_POST['caumag'];
    $form['id_agente'] = $_POST['id_agente'];
    $form['sconto'] = $_POST['sconto'];
    // inizio rigo di input
    $form['in_descri'] = $_POST['in_descri'];
    $form['in_tiprig'] = $_POST['in_tiprig'];
    $form['in_id_doc'] = $_POST['in_id_doc'];
    $form['in_codart'] = $_POST['in_codart'];
	/*   $form['in_artsea'] = $_POST['in_artsea']; */
    $form['in_pervat'] = $_POST['in_pervat'];
    $form['in_tipiva'] = $_POST['in_tipiva'];
    $form['in_ritenuta'] = $_POST['in_ritenuta'];
    $form['in_unimis'] = $_POST['in_unimis'];
    $form['in_prelis'] = $_POST['in_prelis'];
    $form['in_sconto'] = $_POST['in_sconto'];
    $form['in_quanti'] = gaz_format_quantity($_POST['in_quanti'], 0, $admin_aziend['decimal_quantity']);
    $form['in_codvat'] = $_POST['in_codvat'];
    $form['in_codric'] = $_POST['in_codric'];
    $form['in_provvigione'] = $_POST['in_provvigione'];
    $form['in_id_mag'] = $_POST['in_id_mag'];
    $form['in_annota'] = $_POST['in_annota'];
    $form['in_scorta'] = $_POST['in_scorta'];
    $form['in_quamag'] = $_POST['in_quamag'];
    $form['in_pesosp'] = $_POST['in_pesosp'];
    $form['in_extdoc'] = $_POST['in_extdoc'];
    $form['in_status'] = $_POST['in_status'];
    // fine rigo input

	$ultimoprezzo=''; //info sugli ultimi prezzi
    if ($form['in_codart']<>$form['cosear']) { // ho cambiato articolo, cerco le 3 ultime vendite

		$what = $gTables['tesdoc'] . ".datfat, " .
				$gTables['tesdoc'] . ".numfat, " .
				$gTables['rigdoc'] . ".codart, " .
				$gTables['rigdoc'] . ".quanti, " .
				$gTables['rigdoc'] . ".prelis, " .
				$gTables['rigdoc'] . ".sconto, " .
				$gTables['rigdoc'] . ".provvigione";

		$table = $gTables['rigdoc'] . " LEFT JOIN " . $gTables['tesdoc'] . " ON "
        		.$gTables['tesdoc'] . ".id_tes = " . $gTables['rigdoc'] . ".id_tes";

        $where = $gTables['tesdoc'].".clfoco = '".$form['clfoco']."' AND ".$gTables['tesdoc'].".tipdoc LIKE 'FA_' AND ".$gTables['rigdoc'].".tiprig = 0 AND ".$gTables['rigdoc'].".codart = '".$form['cosear']."'";
		$result = gaz_dbi_dyn_query($what, $table, $where, "datfat DESC",0,3);
		while ($prezzi = gaz_dbi_fetch_array($result)) {
			$ultimoprezzo.="<br />Fattura n. ".$prezzi['numfat']." del ".gaz_format_date($prezzi['datfat'])." ____ quantit&agrave; ".gaz_format_quantity($prezzi['quanti'], 0, $admin_aziend['decimal_quantity'])." ____ prezzo ".gaz_format_number($prezzi['prelis'])." ____ sconto ".gaz_format_number($prezzi['sconto'])."% ____ provvigione ".gaz_format_number($prezzi['provvigione'])."%";
		}
	}

    $form['rows'] = array();
    $next_row = 0;
    if (isset($_POST['rows'])) {
        foreach ($_POST['rows'] as $next_row => $v) {
            if (isset($_POST["row_$next_row"])) { //se ho un rigo testo
                $form["row_$next_row"] = $_POST["row_$next_row"];
            }
            $form['rows'][$next_row]['descri'] = substr($v['descri'], 0, 100);
            $form['rows'][$next_row]['tiprig'] = intval($v['tiprig']);
            $form['rows'][$next_row]['id_doc'] = intval($v['id_doc']);
            $form['rows'][$next_row]['codart'] = substr($v['codart'], 0, 15);
			$form['rows'][$next_row]['good_or_service'] = intval($v['good_or_service']);
            $form['rows'][$next_row]['pervat'] = preg_replace("/\,/", '.', $v['pervat']);
            $form['rows'][$next_row]['tipiva'] = strtoupper(substr($v['tipiva'], 0, 1));
            $form['rows'][$next_row]['ritenuta'] = preg_replace("/\,/", '.', $v['ritenuta']);
            $form['rows'][$next_row]['unimis'] = substr($v['unimis'], 0, 3);
            $form['rows'][$next_row]['prelis'] = number_format(floatval(preg_replace("/\,/", '.', $v['prelis'])), $admin_aziend['decimal_price'], '.', '');
            $form['rows'][$next_row]['sconto'] = floatval(preg_replace("/\,/", '.', $v['sconto']));
            $form['rows'][$next_row]['quanti'] = gaz_format_quantity($v['quanti'], 0, $admin_aziend['decimal_quantity']);
            $form['rows'][$next_row]['codvat'] = intval($v['codvat']);
            $form['rows'][$next_row]['codric'] = intval($v['codric']);
            if (isset($v['provvigione'])) {
                $form['rows'][$next_row]['provvigione'] = intval($v['provvigione']);
            }
            $form['rows'][$next_row]['id_mag'] = intval($v['id_mag']);
            $form['rows'][$next_row]['annota'] = substr($v['annota'], 0, 50);
            $form['rows'][$next_row]['scorta'] = floatval($v['scorta']);
            $form['rows'][$next_row]['quamag'] = floatval($v['quamag']);
            $form['rows'][$next_row]['pesosp'] = floatval($v['pesosp']);
            $form['rows'][$next_row]['extdoc'] = filter_var($_POST['rows'][$next_row]['extdoc'], FILTER_SANITIZE_STRING);//die(print_r($_POST['rows'],TRUE));
            if (!empty($_FILES['docfile_' . $next_row]['name'])) {//die(print_r($_FILES,TRUE));
                $move = false;
                $mt = substr($_FILES['docfile_' . $next_row]['name'], -3);
                $prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $next_row;
                if (($mt == 'png' || $mt == 'peg' || $mt == 'jpg' || $mt == 'pdf') && $_FILES['docfile_' . $next_row]['size'] > 1000) { //se c'e' un nuovo documento nel buffer
                    foreach (glob( DATA_DIR . 'files/tmp/' . $prefix . '_*.*') as $fn) {// prima cancello eventuali precedenti file temporanei
                        unlink($fn);
                    }
                    $move = move_uploaded_file($_FILES['docfile_' . $next_row]['tmp_name'], DATA_DIR . 'files/tmp/' . $prefix . '_' . $_FILES['docfile_' . $next_row]['name']);
                    $form['rows'][$next_row]['extdoc'] = $_FILES['docfile_' . $next_row]['name'];
                }
                if (!$move) {
                    $msg .= '56+';
                }
            }
            $form['rows'][$next_row]['status'] = substr($v['status'], 0, 10);
            if (isset($_POST['upd_row'])) {
                $k_row = key($_POST['upd_row']);
                if ($k_row == $next_row) {
                    // sottrazione ai totali peso,pezzi,volume
                    $artico = gaz_dbi_get_row($gTables['artico'], "codice", $form['rows'][$k_row]['codart']);
                    $form['net_weight'] -= $form['rows'][$k_row]['quanti'] * $artico['peso_specifico'];
                    $form['gross_weight'] -= $form['rows'][$k_row]['quanti'] * $artico['peso_specifico'];
                    if ($artico['pack_units'] > 0) {
                        $form['units'] -= intval(round($form['rows'][$k_row]['quanti'] / $artico['pack_units']));
                    }
                    $form['volume'] -= $form['rows'][$k_row]['quanti'] * $artico['volume_specifico'];
                    // fine sottrazione peso,pezzi,volume
                    $form['in_descri'] = $form['rows'][$k_row]['descri'];
                    $form['in_tiprig'] = $form['rows'][$k_row]['tiprig'];
                    $form['in_codart'] = $form['rows'][$k_row]['codart'];
					$form['in_good_or_service'] = $form['rows'][$k_row]['good_or_service'];
                    $form['in_pervat'] = $form['rows'][$k_row]['pervat'];
                    $form['in_tipiva'] = $form['rows'][$k_row]['tipiva'];
                    $form['in_ritenuta'] = $form['rows'][$k_row]['ritenuta'];
                    $form['in_unimis'] = $form['rows'][$k_row]['unimis'];
                    $form['in_prelis'] = $form['rows'][$k_row]['prelis'];
                    $form['in_sconto'] = $form['rows'][$k_row]['sconto'];
                    $form['in_quanti'] = $form['rows'][$k_row]['quanti'];
                    //$form['in_codvat'] = $form['rows'][$k_row]['codvat'];
                    $form['in_codric'] = $form['rows'][$k_row]['codric'];
                    $form['in_provvigione'] = $form['rows'][$k_row]['provvigione'];
                    $form['in_id_mag'] = $form['rows'][$k_row]['id_mag'];
                    $form['in_annota'] = $form['rows'][$k_row]['annota'];
                    $form['in_scorta'] = $form['rows'][$k_row]['scorta'];
                    $form['in_quamag'] = $form['rows'][$k_row]['quamag'];
                    $form['in_pesosp'] = $form['rows'][$k_row]['pesosp'];
                    $form['in_extdoc'] = $form['rows'][$k_row]['extdoc'];
                    $form['in_status'] = "UPDROW" . $k_row;
                    /* if ($form['in_artsea'] == 'D') {
                      $artico_u = gaz_dbi_get_row($gTables['artico'], 'codice', $form['rows'][$k_row]['codart']);
                      $form['cosear'] = $artico_u['descri'];
                      } elseif ($form['in_artsea'] == 'B') {
                      $artico_u = gaz_dbi_get_row($gTables['artico'], 'codice', $form['rows'][$k_row]['codart']);
                      $form['cosear'] = $artico_u['barcode'];
                      } else { */
                    $form['cosear'] = $form['rows'][$k_row]['codart'];
                    //}
                    array_splice($form['rows'], $k_row, 1);
                    $next_row--;
                }
            } elseif ($_POST['hidden_req'] == 'ROW') {
                if (!empty($form['hidden_req'])) { // al primo ciclo azzero ma ripristino il lordo
                    $form['gross_weight'] -= $form['net_weight'];
                    $form['net_weight'] = 0;
                    $form['units'] = 0;
                    $form['volume'] = 0;
                    $form['hidden_req'] = '';
                }
                $artico = gaz_dbi_get_row($gTables['artico'], "codice", $form['rows'][$next_row]['codart']);
                $form['net_weight'] += $form['rows'][$next_row]['quanti'] * $artico['peso_specifico'];
                $form['gross_weight'] += $form['rows'][$next_row]['quanti'] * $artico['peso_specifico'];
                if ($artico['pack_units'] > 0) {
                    $form['units'] += intval(round($form['rows'][$next_row]['quanti'] / $artico['pack_units']));
                }
                $form['volume'] += $form['rows'][$next_row]['quanti'] * $artico['volume_specifico'];
            }
            $next_row++;
        }
    }
		// Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
        $sezione = $form['seziva'];
        $datemi = $form['annemi'] . "-" . $form['mesemi'] . "-" . $form['gioemi'];
        $utsemi = mktime(0, 0, 0, $form['mesemi'], $form['gioemi'], $form['annemi']);
        $initra = $form['anntra'] . "-" . $form['mestra'] . "-" . $form['giotra'];
        $utstra = mktime(0, 0, 0, $form['mestra'], $form['giotra'], $form['anntra']);
        if (!checkdate($form['mestra'], $form['giotra'], $form['anntra']))
            $msg .= "37+";
        if ($utstra < $utsemi) {
            $msg .= "38+";
        }
        if (!isset($_POST['rows'])) {
            $msg .= "39+";
        }
        // --- inizio controllo coerenza date-numerazione
        if ($toDo == 'update') {  // controlli in caso di modifica
            $rs_query = gaz_dbi_dyn_query("numdoc", $gTables['tesbro'], "YEAR(datemi) = " . $form['annemi'] . " and datemi < '$datemi' and tipdoc = '" . $form['tipdoc'] . "' and seziva = $sezione", "datemi DESC, numdoc DESC", 0, 1);
            $result = gaz_dbi_fetch_array($rs_query); //giorni precedenti
            if ($result and ( $form['numdoc'] < $result['numdoc'])) {
                $msg .= "42+";
            }
        } else {    //controlli in caso di inserimento
            $rs_ultimo_tipo = gaz_dbi_dyn_query("*", $gTables['tesbro'], "YEAR(datemi) = " . $form['annemi'] . " and tipdoc = '" . $form['tipdoc'] . "' and seziva = $sezione", "numdoc desc, datemi desc", 0, 1);
            $ultimo_tipo = gaz_dbi_fetch_array($rs_ultimo_tipo);
            $utsUltimoDocumento = mktime(0, 0, 0, substr($ultimo_tipo['datemi'], 5, 2), substr($ultimo_tipo['datemi'], 8, 2), substr($ultimo_tipo['datemi'], 0, 4));
            if ($ultimo_tipo and ( $utsUltimoDocumento > $utsemi)) {
                $msg .= "45+";
            }
        }
        // --- fine controllo coerenza date-numeri
        if (!checkdate($form['mesemi'], $form['gioemi'], $form['annemi']))
            $msg .= "46+";
        if (empty($form['clfoco'])&& $toDo == 'insert')
            $msg .= "47+";
        if (empty($form['pagame']))
            $msg .= "48+";
        //controllo che i rows non abbiano descrizioni  e unita' di misura vuote in presenza di quantita diverse da 0
        foreach ($form['rows'] as $i => $v) {
            if ($v['descri'] == '' && ($v['quanti'] > 0 || $v['quanti'] < 0)) {
                $msgrigo = $i + 1;
                $msg .= "49+";
            }
            if ($v['unimis'] == '' && ($v['quanti'] > 0 || $v['quanti'] < 0)) {
                $msgrigo = $i + 1;
                $msg .= "50+";
            }
        }
		if ($msg == "") {// nessun errore
             $initra .= " " . $form['oratra'] . ":" . $form['mintra'] . ":00";
            if (preg_match("/^id_([0-9]+)$/", $form['clfoco'], $match)) {
                $new_clfoco = $anagrafica->getPartnerData($match[1], 1);
                $form['clfoco'] = $anagrafica->anagra_to_clfoco($new_clfoco, $admin_aziend['mascli'],$form['pagame']);
            }
            if ($toDo == 'update') { // e' una modifica
                $old_rows = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = " . $form['id_tes'], "id_rig asc");
                $i = 0;$syncarticols=array();
                $count = count($form['rows']) - 1;
                while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
                  array_push($syncarticols,$val_old_row['codart']);// Antonio Germani - aggiungo il codice articolo all'array per la sincronizzazione e-commerce
                    if ($i <= $count) { //se il vecchio rigo e' ancora presente nel nuovo lo modifico
                        $form['rows'][$i]['id_tes'] = $form['id_tes'];
                        $codice = array('id_rig', $val_old_row['id_rig']);
                        rigbroUpdate($codice, $form['rows'][$i]);
                        if (isset($form["row_$i"]) && $val_old_row['id_body_text'] > 0) { //se è un rigo testo già presente lo modifico
                            bodytextUpdate(array('id_body', $val_old_row['id_body_text']), array('table_name_ref' => 'rigbro', 'id_ref' => $val_old_row['id_rig'], 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                            gaz_dbi_put_row($gTables['rigbro'], 'id_rig', $val_old_row['id_rig'], 'id_body_text', $val_old_row['id_body_text']);
                        } elseif (isset($form["row_$i"]) && $val_old_row['id_body_text'] == 0) { //prima era un rigo diverso da testo
                            bodytextInsert(array('table_name_ref' => 'rigbro', 'id_ref' => $val_old_row['id_rig'], 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                            gaz_dbi_put_row($gTables['rigbro'], 'id_rig', $val_old_row['id_rig'], 'id_body_text', gaz_dbi_last_id());
                        } elseif (!isset($form["row_$i"]) && $val_old_row['id_body_text'] > 0) { //un rigo che prima era testo adesso non lo � pi�
                            gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigbro' AND id_ref", $val_old_row['id_rig']);
                        }
                        if ($form['rows'][$i]['tiprig']==50 && !empty($form['rows'][$i]['extdoc']) && substr($form['rows'][$i]['extdoc'],0,10)!='rigbrodoc_') {
                            // se a questo rigo corrispondeva un certificato controllo che non sia stato aggiornato, altrimenti lo cambio
                            $dh = opendir( DATA_DIR . 'files/' . $admin_aziend['company_id'] );
                            while (false !== ($filename = readdir($dh))) {
                                $fd = pathinfo($filename);
                                if ($fd['filename'] == 'rigbrodoc_' . $val_old_row['id_rig']) {
                                    // cancello il file precedente indipendentemente dall'estensione
                                    $frep = glob( DATA_DIR . 'files/' . $admin_aziend['company_id'] . '/rigbrodoc_' . $val_old_row['id_rig'] . '.*');
                                    foreach ($frep as $fdel) {// prima cancello eventuali precedenti file temporanei
                                        unlink($fdel);
                                    }
                                }
                            }
                            $tmp_file = DATA_DIR . 'files/tmp/' . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $i . '_' . $form['rows'][$i]['extdoc'];
							// sposto e rinomino il relativo file temporaneo
                            $fn = pathinfo($form['rows'][$i]['extdoc']);
                            rename($tmp_file, DATA_DIR . 'files/' . $admin_aziend['company_id'] . '/rigbrodoc_' . $val_old_row['id_rig'] . '.' . $fn['extension']);
						}
                    } else { //altrimenti lo elimino
                        if (intval($val_old_row['id_body_text']) > 0) {  //se c'� un testo allegato al rigo elimino anch'esso
                            gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigbro' AND id_ref", $val_old_row['id_rig']);
                        }
                        gaz_dbi_del_row($gTables['rigbro'], "id_rig", $val_old_row['id_rig']);
                    }
                    $i++;
                }
                //qualora i nuovi rows fossero di pi� dei vecchi inserisco l'eccedenza
                for ($i = $i; $i <= $count; $i++) {
					array_push($syncarticols,$form['rows'][$i]['codart']);// Antonio Germani - aggiungo il codice articolo all'array per la sincronizzazione e-commerce
                    $form['rows'][$i]['id_tes'] = $form['id_tes'];
                    $last_rigbro_id = rigbroInsert($form['rows'][$i]);
                    if (!empty($form['rows'][$i]['extdoc'])) {
                        $tmp_file = DATA_DIR . 'files/tmp/' . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $i . '_' . $form['rows'][$i]['extdoc'];
// sposto e rinomino il relativo file temporaneo
                        $fd = pathinfo($form['rows'][$i]['extdoc']);
                        rename($tmp_file, DATA_DIR . 'files/' . $admin_aziend['company_id'] . '/rigbrodoc_' . $last_rigbro_id . '.' . $fd['extension']);
                    }
                    if (isset($form["row_$i"])) { //se � un rigo testo lo inserisco il contenuto in body_text
                        bodytextInsert(array('table_name_ref' => 'rigbro', 'id_ref' => $last_rigbro_id, 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                        gaz_dbi_put_row($gTables['rigbro'], 'id_rig', $last_rigbro_id, 'id_body_text', gaz_dbi_last_id());
                    }
                }
                //modifico la testata con i nuovi dati...
                $old_head = gaz_dbi_get_row($gTables['tesbro'], 'id_tes', $form['id_tes']);
                if (substr($form['tipdoc'], 0, 2) == 'DD') { //se � un DDT non fatturato
                    $form['datfat'] = '';
                    $form['numfat'] = 0;
                } else {
                    $form['datfat'] = $datemi;
                    $form['numfat'] = $old_head['numfat'];
                }
                $form['geneff'] = $old_head['geneff'];
                $form['id_contract'] = $old_head['id_contract'];
                $form['id_con'] = $old_head['id_con'];
                $form['status'] = $old_head['status'];
                $form['initra'] = $initra;
                $form['datemi'] = $datemi;
                $codice = array('id_tes', $form['id_tes']);
                tesbroUpdate($codice, $form);
                if (!empty($admin_aziend['synccommerce_classname']) && class_exists($admin_aziend['synccommerce_classname'])){
                    // aggiorno l'e-commerce ove presente
                    $gs=$admin_aziend['synccommerce_classname'];
                    $gSync = new $gs();
					if($gSync->api_token){
						foreach ($syncarticols as $syncarticol){
							$gSync->SetProductQuantity($syncarticol);
						}
					}
				}
                header("Location: " . $form['ritorno']);
                exit;
            } else { // e' un'inserimento
                // ricavo i progressivi in base al tipo di documento
                $where = "numdoc desc";
                $sql_documento = "YEAR(datemi) = " . $form['annemi'] . " and tipdoc = '" . $form['tipdoc'] . "'";
                $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesbro'], $sql_documento, $where, 0, 1);
                $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
                // se e' il primo documento dell'anno, resetto il contatore
                if ($ultimo_documento) {
                    $form['numdoc'] = $ultimo_documento['numdoc'] + 1;
                } else {
                    $form['numdoc'] = 1;
                }
                $form['protoc'] = 0;
                $form['numfat'] = 0;
                $form['datfat'] = 0;
                //inserisco la testata
                $form['status'] = 'GENERATO';
                $form['initra'] = $initra;
                $form['datemi'] = $datemi;
                tesbroInsert($form);
                //recupero l'id assegnato dall'inserimento
                $ultimo_id = gaz_dbi_last_id();
                //inserisco i rows
                foreach ($form['rows'] as $i => $v) {
                    $form['rows'][$i]['id_tes'] = $ultimo_id;
                    $last_rigbro_id = rigbroInsert($form['rows'][$i]);
					// INIZIO INSERIMENTO DOCUMENTI ALLEGATI
                    if (!empty($form['rows'][$i]['extdoc'])) {
                        $tmp_file = DATA_DIR . 'files/tmp/' . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $i . '_' . $form['rows'][$i]['extdoc'];
						// sposto e rinomino il relativo file temporaneo
                        $fd = pathinfo($form['rows'][$i]['extdoc']);
                        rename($tmp_file, DATA_DIR . 'files/' . $admin_aziend['company_id'] . '/rigbrodoc_' . $last_rigbro_id . '.' . $fd['extension']);
                    }
					// FINE INSERIMENTO DOCUMENTI ALLEGATI
                    if (isset($form["row_$i"])) { //se � un rigo testo lo inserisco il contenuto in body_text
                        bodytextInsert(array('table_name_ref' => 'rigbro', 'id_ref' => $last_rigbro_id, 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                        gaz_dbi_put_row($gTables['rigbro'], 'id_rig', $last_rigbro_id, 'id_body_text', gaz_dbi_last_id());
                    }

					if (!empty($admin_aziend['synccommerce_classname']) && class_exists($admin_aziend['synccommerce_classname'])){
						// aggiorno l'e-commerce ove presente
						$gs=$admin_aziend['synccommerce_classname'];
						$gSync = new $gs();
						if($gSync->api_token){
							$gSync->SetProductQuantity($form['rows'][$i]['codart']);
						}
					}

                }
                $_SESSION['print_request'] = $ultimo_id;
                header("Location: invsta_broven.php");
                exit;
            }
        }
    } elseif (isset($_POST['ord']) and $toDo == 'update') {  // si vuole generare un'ordine
        $sezione = $form['seziva'];
        $datemi = $form['annemi'] . "-" . $form['mesemi'] . "-" . $form['gioemi'];
        $utsemi = mktime(0, 0, 0, $form['mesemi'], $form['gioemi'], $form['annemi']);
        $initra = $form['anntra'] . "-" . $form['mestra'] . "-" . $form['giotra'];
        $utstra = mktime(0, 0, 0, $form['mestra'], $form['giotra'], $form['anntra']);
        if (!checkdate($form['mestra'], $form['giotra'], $form['anntra']))
            $msg .= "37+";
        if ($utstra < $utsemi) {
            $msg .= "38+";
        }
        if (!isset($_POST['rows'])) {
            $msg .= "39+";
        }
        if (!checkdate($form['mesemi'], $form['gioemi'], $form['annemi']))
            $msg .= "46+";
        if (empty($form['clfoco']))
            $msg .= "47+";
        if (empty($form['pagame']))
            $msg .= "48+";
        //controllo che i rows non abbiano descrizioni  e unita' di misura vuote in presenza di quantita diverse da 0
        foreach ($form['rows'] as $i => $v) {
            if ($v['descri'] == '' && ($v['quanti'] >= 0.00001 || $v['quanti'] <= -0.00001)) {
                $msgrigo = $i + 1;
                $msg .= "49+";
            }
            if ($v['unimis'] == '' && ($v['quanti'] >= 0.00001 || $v['quanti'] <= -0.00001)) {
                $msgrigo = $i + 1;
                $msg .= "50+";
            }
        }
        if ($msg == "") {// nessun errore
			// creo la descrizione del preventivo di origine
            require("lang." . $admin_aziend['lang'] . ".php");
            $descripreventivo = "rif. " . $strScript['admin_broven.php'][0]['VPR'] . " n." . $form['numdoc'] . " del " . $form['gioemi'] . "." . $form['mesemi'] . "." . $form['annemi'];
			// fine creazione descrizione preventivo di origine
			$sql_documento = "YEAR(datemi) = " . date("Y") . " and tipdoc = 'VOR'";
            $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesbro'], $sql_documento, "numdoc desc", 0, 1);
            $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
            if ($ultimo_documento) {
                $form['numdoc'] = $ultimo_documento['numdoc'] + 1;
            } else {
                $form['numdoc'] = 1;
            }
            //inserisco la testata
            $form['initra'] = $initra;
            $form['datemi'] = date("Y-m-d");
            $form['tipdoc'] = 'VOR';
            $form['status'] = 'GENERATO';
            tesbroInsert($form);
            //recupero l'id assegnato dall'inserimento
            $ultimo_id = gaz_dbi_last_id();
            //inserisco un rigo descrittivo per il riferimento al preventivo sull'ordine
            $descrirow = array('id_tes' => $ultimo_id, 'tiprig' => 2, 'descri' => $descripreventivo);
            rigbroInsert($descrirow);
            //inserisco i rows
            $count = count($form['rows']);
            for ($i = 0; $i < $count; $i++) {
                $form['rows'][$i]['id_tes'] = $ultimo_id;
                rigbroInsert($form['rows'][$i]);
                $last_rigbro_id = gaz_dbi_last_id();
                if (isset($form["row_$i"])) { //se è un rigo testo lo inserisco il contenuto in body_text
                    bodytextInsert(array('table_name_ref' => 'rigbro', 'id_ref' => $last_rigbro_id, 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                    gaz_dbi_put_row($gTables['rigbro'], 'id_rig', $last_rigbro_id, 'id_body_text', gaz_dbi_last_id());
                }
            }
            $_SESSION['print_request'] = $ultimo_id;
            header("Location: invsta_broven.php");
            exit;
        }
    }
    // Se viene inviata la richiesta di conferma cliente
    if ($_POST['hidden_req'] == 'clfoco') {
        $anagrafica = new Anagrafica();
        if (preg_match("/^id_([0-9]+)$/", $form['clfoco'], $match)) {
            $cliente = $anagrafica->getPartnerData($match[1], 1);
        } else {
            $cliente = $anagrafica->getPartner($form['clfoco']);
        }
        $result = gaz_dbi_get_row($gTables['imball'], "codice", $cliente['imball']);
        $form['imball'] = $result['descri'];
        if (($form['net_weight'] - $form['gross_weight']) >= 0) {
            $form['gross_weight'] += $result['weight'];
        }
        $result = gaz_dbi_get_row($gTables['portos'], "codice", $cliente['portos']);
        $form['portos'] = $result['descri'];
        $result = gaz_dbi_get_row($gTables['spediz'], "codice", $cliente['spediz']);
        $form['spediz'] = $result['descri'];
        $form['destin'] = $cliente['destin'];
        $form['id_agente'] = $cliente['id_agente'];
        if ($form['id_agente'] > 0) { // carico la provvigione standard
            $provvigione = new Agenti;
            $form['in_provvigione'] = $provvigione->getPercent($form['id_agente']);
            if (isset($_POST['rows'])) {  // aggiorno le provvigioni sui rows
                foreach ($_POST['rows'] as $k => $val) {
                    $form['rows'][$k]['provvigione'] = $provvigione->getPercent($form['id_agente'], $val['codart']);
                }
            }
        }
        $form['id_des'] = $cliente['id_des'];
        $id_des = $anagrafica->getPartner($form['id_des']);
        $form['search']['id_des'] = substr($id_des['ragso1'], 0, 10);
        $des_same = gaz_dbi_get_row($gTables['destina'], "id_anagra", $cliente['id_anagra']);
        $form['id_des_same_company'] = $des_same['codice'];
        $form['in_codvat'] = $cliente['aliiva'];
        if ($cliente['cosric'] >= 100000000) {
            $form['in_codric'] = $cliente['cosric'];
        }
		if ($cliente['sconto_rigo']>=0.01){
			$form['in_sconto'] = $cliente['sconto_rigo'];
		} else {
			$form['in_sconto'] = '#';
		}
        $form['expense_vat'] = $admin_aziend['preeminent_vat'];
        if ($cliente['aliiva'] > 0) {
            $form['expense_vat'] = $cliente['aliiva'];
        }
        $form['sconto'] = $cliente['sconto'];
        $form['pagame'] = $cliente['codpag'];
        $form['change_pag'] = $cliente['codpag'];
        $form['banapp'] = $cliente['banapp'];
        $form['listin'] = $cliente['listin'];
        $pagame = gaz_dbi_get_row($gTables['pagame'], "codice", $form['pagame']);
        if (($pagame['tippag'] == 'B' or $pagame['tippag'] == 'T' or $pagame['tippag'] == 'V')
                and $cliente['speban'] == 'S') {
            $form['speban'] = $admin_aziend['sperib'];
            $form['numrat'] = $pagame['numrat'];
        } else {
            $form['speban'] = 0.00;
            $form['numrat'] = 1;
        }
        if ($pagame['tippag'] == 'T' && $form['stamp'] == 0) {  //se il pagamento prevede il bollo
            $form['stamp'] = $admin_aziend['perbol'];
            $form['round_stamp'] = $admin_aziend['round_bol'];
        } elseif ($pagame['tippag'] != 'T') {
            $form['stamp'] = 0;
            $form['round_stamp'] = 0;
        }
        $form['hidden_req'] = '';
    }

    // Se viene modificato l'agente
    if ($_POST['hidden_req'] == 'AGENTE') {
        if ($form['id_agente'] > 0) { // carico la provvigione standard
            $provvigione = new Agenti;
            $form['in_provvigione'] = $provvigione->getPercent($form['id_agente']);
            if (isset($_POST['rows'])) {  // aggiorno le provvigioni sui rows
                foreach ($_POST['rows'] as $k => $val) {
                    $form['rows'][$k]['provvigione'] = $form['in_provvigione'];
                    $form['rows'][$k]['provvigione'] = $provvigione->getPercent($form['id_agente'], $val['codart']);
                }
            }
        }
        $form['hidden_req'] = '';
    }

    // Se viene inviata la richiesta di conferma rigo
    /** ENRICO FEDELE */
    /* Con button non funziona _x */
    //if (isset($_POST['in_submit_x'])) {
    /** ENRICO FEDELE */
	if (isset($_POST['in_submit_desc'])) { //rigo Descrittivo rapido
        $form['rows'][$next_row]['codart'] = '';
        $form['rows'][$next_row]['annota'] = '';
        $form['rows'][$next_row]['pesosp'] = '';
        $form['rows'][$next_row]['good_or_service'] = 0;
        $form['rows'][$next_row]['unimis'] = '';
        $form['rows'][$next_row]['quanti'] = 0;
        $form['rows'][$next_row]['prelis'] = 0;
        $form['rows'][$next_row]['codric'] = 0;
        $form['rows'][$next_row]['sconto'] = 0;
        $form['rows'][$next_row]['pervat'] = 0;
        $form['rows'][$next_row]['tipiva'] = 0;
        $form['rows'][$next_row]['ritenuta'] = 0;
        $form['rows'][$next_row]['codvat'] = 0;
        $form['rows'][$next_row]['id_doc'] = '';
        $form['rows'][$next_row]['descri'] = '';
        $form['rows'][$next_row]['id_mag'] = 0;
        $form['rows'][$next_row]['status'] = 'INSERT';
        $form['rows'][$next_row]['scorta'] = 0;
        $form['rows'][$next_row]['quamag'] = 0;
        $form['rows'][$next_row]['tiprig'] = 2;
        $next_row++;
    } else if (isset($_POST['in_submit_text'])) { //rigo Testo rapido
        $form["row_$next_row"] = '';
        $form['rows'][$next_row]['codart'] = '';
        $form['rows'][$next_row]['annota'] = '';
        $form['rows'][$next_row]['pesosp'] = '';
        $form['rows'][$next_row]['good_or_service'] = 0;
        $form['rows'][$next_row]['unimis'] = '';
        $form['rows'][$next_row]['quanti'] = 0;
        $form['rows'][$next_row]['prelis'] = 0;
        $form['rows'][$next_row]['codric'] = 0;
        $form['rows'][$next_row]['sconto'] = 0;
        $form['rows'][$next_row]['pervat'] = 0;
        $form['rows'][$next_row]['tipiva'] = 0;
        $form['rows'][$next_row]['ritenuta'] = 0;
        $form['rows'][$next_row]['codvat'] = 0;
        $form['rows'][$next_row]['id_doc'] = '';
        $form['rows'][$next_row]['descri'] = '';
        $form['rows'][$next_row]['id_mag'] = 0;
        $form['rows'][$next_row]['status'] = 'INSERT';
        $form['rows'][$next_row]['scorta'] = 0;
        $form['rows'][$next_row]['quamag'] = 0;
        $form['rows'][$next_row]['tiprig'] = 6;
        $next_row++;
    } else if (isset($_POST['in_submit'])) {
        $artico = gaz_dbi_get_row($gTables['artico'], "codice", $form['in_codart']);
        gaz_dbi_query ("UPDATE ".$gTables['artico']." SET `last_used`='".date("Y-m-d")."' WHERE codice='".$form['in_codart']."';");
        // addizione ai totali peso,pezzi,volume
        $form['net_weight'] += $form['in_quanti'] * $artico['peso_specifico'];
        $form['gross_weight'] += $form['in_quanti'] * $artico['peso_specifico'];
        if ($artico['pack_units'] > 0) {
            $form['units'] += intval(round($form['in_quanti'] / $artico['pack_units']));
        }
        $form['volume'] += $form['in_quanti'] * $artico['volume_specifico'];
		$form['in_good_or_service']=$artico['good_or_service'];
        // fine addizione peso,pezzi,volume
        if (substr($form['in_status'], 0, 6) == "UPDROW") { //se e' un rigo da modificare
            $old_key = intval(substr($form['in_status'], 6));
            $form['rows'][$old_key]['tiprig'] = $form['in_tiprig'];
            $form['rows'][$old_key]['id_doc'] = $form['in_id_doc'];
            $form['rows'][$old_key]['descri'] = $form['in_descri'];
            $form['rows'][$old_key]['id_mag'] = $form['in_id_mag'];
            $form['rows'][$old_key]['status'] = "UPDATE";
            $form['rows'][$old_key]['unimis'] = $form['in_unimis'];
            $form['rows'][$old_key]['quanti'] = $form['in_quanti'];
            $form['rows'][$old_key]['codart'] = $form['in_codart'];
			$form['rows'][$old_key]['good_or_service'] = $form['in_good_or_service'];
            $form['rows'][$old_key]['codric'] = $form['in_codric'];
            $form['rows'][$old_key]['ritenuta'] = $form['in_ritenuta'];
            $form['rows'][$old_key]['provvigione'] = $form['in_provvigione'];
            $form['rows'][$old_key]['prelis'] = number_format($form['in_prelis'], $admin_aziend['decimal_price'], '.', '');
            $form['rows'][$old_key]['sconto'] = $form['in_sconto'];
            if ($artico['aliiva'] > 0) {
                $form['rows'][$old_key]['codvat'] = $artico['aliiva'];
                $iva_row = gaz_dbi_get_row($gTables['aliiva'], "codice", $artico['aliiva']);
                $form['rows'][$old_key]['pervat'] = $iva_row['aliquo'];
                $form['rows'][$old_key]['tipiva'] = $iva_row['tipiva'];
            }
            if ($form['in_codvat'] > 0) {
                $form['rows'][$old_key]['codvat'] = $form['in_codvat'];
                $iva_row = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['in_codvat']);
                $form['rows'][$old_key]['pervat'] = $iva_row['aliquo'];
                $form['rows'][$old_key]['tipiva'] = $iva_row['tipiva'];
            }
            /* $form['rows'][$old_key]['codvat'] = $form['in_codvat'];
              $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
              $form['rows'][$old_key]['pervat'] = $iva_row['aliquo'];
              $form['rows'][$old_key]['tipiva'] = $iva_row['tipiva']; */
            $form['rows'][$old_key]['scorta'] = '';
            $form['rows'][$old_key]['quamag'] = 0;
            $form['rows'][$old_key]['annota'] = '';
            $form['rows'][$old_key]['pesosp'] = '';
            $form['rows'][$old_key]['extdoc'] = $form['in_extdoc'];
            if ($form['in_tiprig'] == 0 and ! empty($form['in_codart'])) {  //rigo normale
                $form['rows'][$old_key]['annota'] = $artico['annota'];
                $form['rows'][$old_key]['pesosp'] = $artico['peso_specifico'];
                $form['rows'][$old_key]['unimis'] = $artico['unimis'];
                $form['rows'][$old_key]['descri'] = $artico['descri'];
                if ($form['listin'] == 2) {
                    $form['rows'][$old_key]['prelis'] = number_format($artico['preve2'], $admin_aziend['decimal_price'], '.', '');
                } elseif ($form['listin'] == 3) {
                    $form['rows'][$old_key]['prelis'] = number_format($artico['preve3'], $admin_aziend['decimal_price'], '.', '');
                } elseif ($form['listin'] == 4) {
                    $form['rows'][$old_key]['prelis'] = number_format($artico['preve4'], $admin_aziend['decimal_price'], '.', '');
                } elseif ($form['listin'] == 5) {
                    $form['rows'][$old_key]['prelis'] = number_format($artico['web_price'], $admin_aziend['decimal_price'], '.', '');
                } else {
                    $form['rows'][$old_key]['prelis'] = number_format($artico['preve1'], $admin_aziend['decimal_price'], '.', '');
                }
                $mv = $upd_mm->getStockValue(false, $form['in_codart'], $form['annemi'] . '-' . $form['mesemi'] . '-' . $form['gioemi'], $admin_aziend['stock_eval_method']);
                $magval = array_pop($mv);
                $magval=(is_numeric($magval))?['q_g'=>0,'v_g'=>0]:$magval;
                $form['rows'][$old_key]['scorta'] = $artico['scorta'];
                $form['rows'][$old_key]['quamag'] = $magval['q_g'];
            } elseif ($form['in_tiprig'] == 1 || $form['in_tiprig'] == 50) { //rigo forfait o normale con allegato
                $form['rows'][$old_key]['codart'] = "";
				$form['rows'][$old_key]['good_or_service'] = "";
                $form['rows'][$old_key]['unimis'] = "";
                $form['rows'][$old_key]['quanti'] = 0;
                $form['rows'][$old_key]['sconto'] = 0;
            } elseif ($form['in_tiprig'] == 2 || $form['in_tiprig'] == 51) { //descrittivo o descrittivo con allegato
                $form['rows'][$old_key]['codart'] = "";
				$form['rows'][$old_key]['good_or_service'] = "";
                $form['rows'][$old_key]['annota'] = "";
                $form['rows'][$old_key]['pesosp'] = "";
                $form['rows'][$old_key]['unimis'] = "";
                $form['rows'][$old_key]['quanti'] = 0;
                $form['rows'][$old_key]['prelis'] = 0;
                $form['rows'][$old_key]['codric'] = 0;
                $form['rows'][$old_key]['sconto'] = 0;
                $form['rows'][$old_key]['pervat'] = 0;
                $form['rows'][$old_key]['tipiva'] = 0;
                $form['rows'][$old_key]['ritenuta'] = 0;
                $form['rows'][$old_key]['codvat'] = 0;
            } elseif ($form['in_tiprig'] == 3) {   //var.tot.fatt.
                $form['rows'][$old_key]['codart'] = "";
				$form['rows'][$old_key]['good_or_service'] = "";
                $form['rows'][$old_key]['quanti'] = "";
                $form['rows'][$old_key]['unimis'] = "";
                $form['rows'][$old_key]['sconto'] = 0;
            } elseif ($form['in_tiprig'] == 11 or $form['in_tiprig'] == 12 or $form['in_tiprig'] == 13) { //rigo fattura elettronica
                $form['rows'][$old_key]['codart'] = "";
				$form['rows'][$old_key]['good_or_service'] = "";
                $form['rows'][$old_key]['annota'] = "";
                $form['rows'][$old_key]['pesosp'] = "";
                $form['rows'][$old_key]['gooser'] = 0;
                $form['rows'][$old_key]['unimis'] = "";
                $form['rows'][$old_key]['quanti'] = 0;
                $form['rows'][$old_key]['prelis'] = 0;
                $form['rows'][$old_key]['codric'] = 0;
                $form['rows'][$old_key]['sconto'] = 0;
                $form['rows'][$old_key]['pervat'] = 0;
                $form['rows'][$old_key]['tipiva'] = 0;
                $form['rows'][$old_key]['ritenuta'] = 0;
                $form['rows'][$old_key]['codvat'] = 0;
            }
            ksort($form['rows']);
        } else { //se è un rigo da inserire
            if ($form['in_tiprig'] == 0) {   // è un rigo normale controllo se l'articolo prevede un rigo testuale che lo precede
				$article_text = gaz_dbi_get_row($gTables['company_config'], 'var', 'article_text');
				if ($article_text['val'] < 2){
					$bodytext = gaz_dbi_get_row($gTables['body_text'], "table_name_ref", 'artico_' . $form['in_codart']);
				} else {
					$bodytext = '';
				}
                if (!empty($bodytext) && !empty($bodytext['body_text'])) { // il testo aggiuntivo c'è (e non è vuoto)
                    $form["row_$next_row"] = $bodytext['body_text'];
                    $form['rows'][$next_row]['tiprig'] = 6;
                    $form['rows'][$next_row]['descri'] = '';
                    $form['rows'][$next_row]['id_mag'] = 0;
                    $form['rows'][$next_row]['id_lotmag'] = 0;
					$form['rows'][$next_row]['identifier'] = '';
					$form['rows'][$next_row]['cod_operazione'] = 11;
					$form['rows'][$next_row]['recip_stocc'] = '';
					$form['rows'][$next_row]['recip_stocc_destin'] = '';
                    $form['rows'][$next_row]['lot_or_serial'] = 0;
					$form['rows'][$next_row]['SIAN'] = 0;
                    $form['rows'][$next_row]['status'] = '';
                    $form['rows'][$next_row]['scorta'] = 0;
                    $form['rows'][$next_row]['quamag'] = 0;
                    $form['rows'][$next_row]['codart'] = '';
                    $form['rows'][$next_row]['annota'] = '';
                    $form['rows'][$next_row]['pesosp'] = '';
                    $form['rows'][$next_row]['gooser'] = 0;
                    $form['rows'][$next_row]['unimis'] = '';
                    $form['rows'][$next_row]['quanti'] = 0;
                    $form['rows'][$next_row]['prelis'] = 0;
                    $form['rows'][$next_row]['codric'] = 0;
                    $form['rows'][$next_row]['sconto'] = 0;
                    $form['rows'][$next_row]['pervat'] = 0;
                    $form['rows'][$next_row]['tipiva'] = 0;
                    $form['rows'][$next_row]['ritenuta'] = 0;
                    $form['rows'][$next_row]['codvat'] = 0;
                    $next_row++;
                }
			}
            $form['rows'][$next_row]['tiprig'] = $form['in_tiprig'];
            $form['rows'][$next_row]['id_doc'] = $form['in_id_doc'];
            $form['rows'][$next_row]['descri'] = $form['in_descri'];
            $form['rows'][$next_row]['id_mag'] = $form['in_id_mag'];
            $form['rows'][$next_row]['extdoc'] = 0;
            $form['rows'][$next_row]['status'] = "INSERT";
            $form['rows'][$next_row]['scorta'] = 0;
            $form['rows'][$next_row]['quamag'] = 0;
            if ($form['in_tiprig'] == 0) {  //rigo normale
                $form['rows'][$next_row]['codart'] = $form['in_codart'];
				$form['rows'][$next_row]['good_or_service'] = $form['in_good_or_service'];
                $form['rows'][$next_row]['annota'] = $artico['annota'];
                $form['rows'][$next_row]['pesosp'] = $artico['peso_specifico'];
                $form['rows'][$next_row]['descri'] = $artico['descri'];
                $form['rows'][$next_row]['unimis'] = $artico['unimis'];
                $form['rows'][$next_row]['prelis'] = number_format($form['in_prelis'], $admin_aziend['decimal_price'], '.', '');
                $form['rows'][$next_row]['codric'] = $form['in_codric'];
                $form['rows'][$next_row]['quanti'] = $form['in_quanti'];
                $form['rows'][$next_row]['sconto'] = $form['in_sconto'];
                /** inizio modifica FP 09/10/2015
                 * se non ho inserito uno sconto nella maschera prendo quello standard registrato nell'articolo
                 */
                //rimossa            $form['rows'][$next_row]['sconto'] = $form['in_sconto'];
                $in_sconto = $form['in_sconto'];
                if ($in_sconto != "#") {
                    $form['rows'][$next_row]['sconto'] = $in_sconto;
                } else {
					if ($form["sconto"] > 0) { // gestione sconto cliente sul totale merce o sul rigo
                        $form['rows'][$next_row]['sconto'] = 0;
					} else {
						$comp = new venditCalc();
						$tmpPrezzoNetto_Sconto = $comp->trovaPrezzoNetto_Sconto($cliente['codice'], $form['rows'][$next_row]['codart'], $artico['sconto']);
						if ($tmpPrezzoNetto_Sconto < 0) { // è un prezzo netto
							$form['rows'][$next_row]['prelis'] = -$tmpPrezzoNetto_Sconto;
							$form['rows'][$next_row]['sconto'] = 0;
						} else {
							$form['rows'][$next_row]['sconto'] = $tmpPrezzoNetto_Sconto;
						}
					}
				}
                /* fine modifica FP */
                $form['rows'][$next_row]['ritenuta'] = $form['in_ritenuta'];
                $provvigione = new Agenti;
                $form['rows'][$next_row]['provvigione'] = $provvigione->getPercent($form['id_agente'], $form['in_codart']);
                if (!isset($tmpPrezzoNetto_Sconto) or ( $tmpPrezzoNetto_Sconto >= 0)) { // non ho trovato un prezzo netto per il cliente/articolo
                    if ($form['listin'] == 2) {
                        $form['rows'][$next_row]['prelis'] = number_format($artico['preve2'], $admin_aziend['decimal_price'], '.', '');
                    } elseif ($form['listin'] == 3) {
                        $form['rows'][$next_row]['prelis'] = number_format($artico['preve3'], $admin_aziend['decimal_price'], '.', '');
                    } elseif ($form['listin'] == 4) {
                        $form['rows'][$next_row]['prelis'] = number_format($artico['preve4'], $admin_aziend['decimal_price'], '.', '');
                    } elseif ($form['listin'] == 5) {
                        $form['rows'][$next_row]['prelis'] = number_format($artico['web_price'], $admin_aziend['decimal_price'], '.', '');
                    } else {
                        $form['rows'][$next_row]['prelis'] = number_format($artico['preve1'], $admin_aziend['decimal_price'], '.', '');
                    }
                }
                $form['rows'][$next_row]['codvat'] = $admin_aziend['preeminent_vat'];
                $iva_azi = gaz_dbi_get_row($gTables['aliiva'], "codice", $admin_aziend['preeminent_vat']);
                $form['rows'][$next_row]['pervat'] = $iva_azi['aliquo'];
                $form['rows'][$next_row]['tipiva'] = $iva_azi['tipiva'];
                if ($artico['aliiva'] > 0) {
                    $form['rows'][$next_row]['codvat'] = $artico['aliiva'];
                    $iva_row = gaz_dbi_get_row($gTables['aliiva'], "codice", $artico['aliiva']);
                    $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
                    $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
                }
                if ($form['in_codvat'] > 0) {
                    $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
                    $iva_row = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['in_codvat']);
                    $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
                    $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
                }
                if ($artico['codcon'] > 0) {
                    $form['rows'][$next_row]['codric'] = $artico['codcon'];
                    $form['in_codric'] = $artico['codcon'];
                }
                $mv = $upd_mm->getStockValue(false, $form['in_codart'], $form['annemi'] . '-' . $form['mesemi'] . '-' . $form['gioemi'], $admin_aziend['stock_eval_method']);
                $magval = array_pop($mv);
                $magval=(is_numeric($magval))?['q_g'=>0,'v_g'=>0]:$magval;
                $form['rows'][$next_row]['scorta'] = $artico['scorta'];
                $form['rows'][$next_row]['quamag'] = $magval['q_g'];
                if ($artico['good_or_service']==2 and $tipo_composti['val']=="KIT") {
                    $whe_dis = "codice_composizione = '".$form['in_codart']."'";
                    $res_dis = gaz_dbi_dyn_query('*', $gTables['distinta_base'], $whe_dis, 'id', 0, PER_PAGE);
                    while ($row_dis = gaz_dbi_fetch_array($res_dis)) {
                        $next_row++;
                        $result2 = gaz_dbi_dyn_query('*', $gTables['artico'], " codice = '".$row_dis['codice_artico_base']."'", 'codice', 0, PER_PAGE);
                        $row2 = gaz_dbi_fetch_array($result2);
                        $form['rows'][$next_row]['lot_or_serial'] = 0;
                        $form['rows'][$next_row]['id_lotmag'] = 0;
                        $form['rows'][$next_row]['tiprig'] = 210;
                        $form['rows'][$next_row]['id_mag'] = "";
                        $form['rows'][$next_row]['status'] = "INSERT";
                        $form['rows'][$next_row]['scorta'] = 0;
                        $form['rows'][$next_row]['codart'] = $row2['codice'];
						$form['rows'][$next_row]['good_or_service'] = $row2['good_or_service'];
                        $form['rows'][$next_row]['descri'] = $row2['descri'];
                        $form['rows'][$next_row]['unimis'] = $row2['unimis'];
                        $form['rows'][$next_row]['prelis'] = 0;
                        $form['rows'][$next_row]['quanti'] = $row_dis['quantita_artico_base'];
                        $form['rows'][$next_row]['id_doc'] = "";
                        $form['rows'][$next_row]['prelis'] = 0;
                        $form['rows'][$next_row]['codric'] = 0;
                        $form['rows'][$next_row]['sconto'] = 0;
                        $form['rows'][$next_row]['pervat'] = 0;
                        $form['rows'][$next_row]['tipiva'] = 0;
                        $form['rows'][$next_row]['ritenuta'] = 0;
                        $form['rows'][$next_row]['codvat'] = 0;
                        $form['rows'][$next_row]['annota'] = "";
                        $form['rows'][$next_row]['pesosp'] = 0;
                    }
                }

            } elseif ($form['in_tiprig'] == 1 || $form['in_tiprig'] == 50) { //rigo forfait o normale con allegato
                $form['rows'][$next_row]['codart'] = "";
				$form['rows'][$next_row]['good_or_service'] = "";
                $form['rows'][$next_row]['annota'] = "";
                $form['rows'][$next_row]['pesosp'] = "";
                $form['rows'][$next_row]['unimis'] = "";
                $form['rows'][$next_row]['quanti'] = 0;
                $form['rows'][$next_row]['prelis'] = 0;
                $form['rows'][$next_row]['codric'] = $form['in_codric'];
                $form['rows'][$next_row]['sconto'] = 0;
                $form['rows'][$next_row]['codvat'] = $admin_aziend['preeminent_vat'];
                $iva_azi = gaz_dbi_get_row($gTables['aliiva'], "codice", $admin_aziend['preeminent_vat']);
                $form['rows'][$next_row]['pervat'] = $iva_azi['aliquo'];
                $form['rows'][$next_row]['tipiva'] = $iva_azi['tipiva'];
                if ($form['in_codvat'] > 0) {
                    $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
                    $iva_row = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['in_codvat']);
                    $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
                    $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
                }
                $form['rows'][$next_row]['ritenuta'] = $form['in_ritenuta'];
            } elseif ($form['in_tiprig'] == 2 || $form['in_tiprig'] == 51) { //descrittivo o descrittivo con allegato
                $form['rows'][$next_row]['codart'] = "";
				$form['rows'][$next_row]['good_or_service'] = "";
                $form['rows'][$next_row]['annota'] = "";
                $form['rows'][$next_row]['pesosp'] = "";
                $form['rows'][$next_row]['unimis'] = "";
                $form['rows'][$next_row]['quanti'] = 0;
                $form['rows'][$next_row]['prelis'] = 0;
                $form['rows'][$next_row]['codric'] = 0;
                $form['rows'][$next_row]['sconto'] = 0;
                $form['rows'][$next_row]['pervat'] = 0;
                $form['rows'][$next_row]['tipiva'] = 0;
                $form['rows'][$next_row]['ritenuta'] = 0;
                $form['rows'][$next_row]['codvat'] = 0;
            } elseif ($form['in_tiprig'] == 3) {
                $form['rows'][$next_row]['codart'] = "";
				$form['rows'][$next_row]['good_or_service'] = "";
                $form['rows'][$next_row]['annota'] = "";
                $form['rows'][$next_row]['pesosp'] = "";
                $form['rows'][$next_row]['quanti'] = 0;
                $form['rows'][$next_row]['prelis'] = number_format($form['in_prelis'], $admin_aziend['decimal_price'], '.', '');
                $form['rows'][$next_row]['codric'] = $form['in_codric'];
                $form['rows'][$next_row]['sconto'] = 0;
                $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
                $iva_row = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['in_codvat']);
                $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
                $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
                $form['rows'][$next_row]['ritenuta'] = 0;
            } elseif ($form['in_tiprig'] > 5 && $form['in_tiprig'] < 9) { //testo
                $form["row_$next_row"] = "";
                $form['rows'][$next_row]['codart'] = "";
				$form['rows'][$next_row]['good_or_service'] = "";
                $form['rows'][$next_row]['annota'] = "";
                $form['rows'][$next_row]['pesosp'] = "";
                $form['rows'][$next_row]['unimis'] = "";
                $form['rows'][$next_row]['quanti'] = 0;
                $form['rows'][$next_row]['prelis'] = 0;
                $form['rows'][$next_row]['codric'] = 0;
                $form['rows'][$next_row]['sconto'] = 0;
                $form['rows'][$next_row]['pervat'] = 0;
                $form['rows'][$next_row]['tipiva'] = 0;
                $form['rows'][$next_row]['codvat'] = 0;
                $form['rows'][$next_row]['ritenuta'] = 0;
            } elseif ($form['in_tiprig'] == 11 or $form['in_tiprig'] == 12 or $form['in_tiprig'] == 13) { //dati fattura elettronica
                $form['rows'][$next_row]['codart'] = "";
				$form['rows'][$next_row]['good_or_service'] = "";
                $form['rows'][$next_row]['annota'] = "";
                $form['rows'][$next_row]['pesosp'] = "";
                $form['rows'][$next_row]['gooser'] = 0;
                $form['rows'][$next_row]['unimis'] = "";
                $form['rows'][$next_row]['quanti'] = 0;
                $form['rows'][$next_row]['prelis'] = 0;
                $form['rows'][$next_row]['codric'] = 0;
                $form['rows'][$next_row]['sconto'] = 0;
                $form['rows'][$next_row]['pervat'] = 0;
                $form['rows'][$next_row]['tipiva'] = 0;
                $form['rows'][$next_row]['ritenuta'] = 0;
                $form['rows'][$next_row]['codvat'] = 0;
            }
        }
        // reinizializzo rigo di input tranne che per il tipo rigo e aliquota iva
        $form['in_descri'] = "";
        $form['in_codart'] = "";
		$form['in_good_or_service'] = "";
        $form['in_unimis'] = "";
        $form['in_prelis'] = 0;
//      $form['in_sconto'] = '#';  non azzero il campo in_sconto (sconto rigo)
        $form['in_quanti'] = 0;
        $form['in_codric'] = substr($admin_aziend['impven'], 0, 3);
        $form['in_id_mag'] = 0;
        $form['in_annota'] = "";
        $form['in_scorta'] = 0;
        $form['in_quamag'] = 0;
        $form['in_pesosp'] = 0;
        $form['in_status'] = "INSERT";
        // fine reinizializzo rigo input
        $form['cosear'] = "";
        $next_row++;
    }
    // Se viene inviata la richiesta di spostamento verso l'alto del rigo
    if (isset($_POST['upper_row'])) {
        $upp_key = key($_POST['upper_row']);
        if ($upp_key > 0) {
            $new_key = $upp_key - 1;
        } else {
            $new_key = $next_row - 1;
        }
        if (isset($form["row_$upp_key"])) { //se sto spostando un rigo testo
            $form["row_$new_key"] = $form["row_$upp_key"];
            unset($form["row_$upp_key"]);
        } elseif(isset($form["row_$new_key"]))  { //se lo sto spostando dove prima c'era un rigo testo
            $form["row_$upp_key"] = $form["row_$new_key"];
            unset($form["row_$new_key"]);
        }
        $updated_row = $form['rows'][$new_key];
        $form['rows'][$new_key] = $form['rows'][$upp_key];
        $form['rows'][$upp_key] = $updated_row;
        ksort($form['rows']);
        unset($updated_row);
    }
    // Se viene inviata la richiesta elimina il rigo corrispondente
    if (isset($_POST['del'])) {
        $delri = key($_POST['del']);
        // sottrazione ai totali peso,pezzi,volume
        $artico = gaz_dbi_get_row($gTables['artico'], "codice", $form['rows'][$delri]['codart']);
        $form['net_weight'] -= $form['rows'][$delri]['quanti'] * $artico['peso_specifico'];
        $form['gross_weight'] -= $form['rows'][$delri]['quanti'] * $artico['peso_specifico'];
        if ($artico['pack_units'] > 0) {
            $form['units'] -= intval(round($form['rows'][$delri]['quanti'] / $artico['pack_units']));
        }
        $form['volume'] -= $form['rows'][$delri]['quanti'] * $artico['volume_specifico'];
        // fine sottrazione peso,pezzi,volume
        // diminuisco o lascio inalterati gli index dei testi
        foreach ($form['rows'] as $k => $val) {
            if (isset($form["row_$k"])) { //se ho un rigo testo
                if ($k > $delri) { //se ho un rigo testo dopo
                    $new_k = $k - 1;
                    $form["row_$new_k"] = $form["row_$k"];
                    unset($form["row_$k"]);
                }
            }
        }
        array_splice($form['rows'], $delri, 1);
        $next_row--;
    }
} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $tesbro = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $_GET['id_tes']);
    $anagrafica = new Anagrafica();
    $cliente = $anagrafica->getPartner($tesbro['clfoco']);
    $rs_rig = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = " . intval($_GET['id_tes']), "id_rig asc");
    $id_des = $anagrafica->getPartner($tesbro['id_des']);
    $form['id_tes'] = intval($_GET['id_tes']);
    $form['hidden_req'] = '';
    // inizio rigo di input
    $form['in_descri'] = "";
    $form['in_tiprig'] = 0;
    $form['in_id_doc'] = 0;
    /*   $form['in_artsea'] = $admin_aziend['artsea']; */
    $form['in_codart'] = "";
	$form['in_good_or_service'] = "";
    $form['in_pervat'] = 0;
    $form['in_tipiva'] = 0;
    $form['in_ritenuta'] = 0;
    $form['in_unimis'] = "";
    $form['in_prelis'] = 0;
    /** inizio modifica FP 09/10/2015
     * inizializzo il campo con '#' per indicare che voglio lo sconto standard dell'articolo
     */
//rimossa    $form['in_sconto'] = 0;
    $form['in_sconto'] = '#';
    /* fine modifica FP */
    $form['in_quanti'] = 0;
    $form['in_codvat'] = 0;
    $form['in_codric'] = substr($admin_aziend['impven'], 0, 3);
    $form['in_id_mag'] = 0;
    $form['in_annota'] = "";
    $form['in_pesosp'] = 0;
    $form['in_scorta'] = 0;
    $form['in_quamag'] = 0;
    $form['in_extdoc'] = 0;
    $form['in_status'] = "INSERT";
    $form['in_codric'] = $admin_aziend['impven'];

    // fine rigo input
    $form['rows'] = array();
    // ...e della testata
    $form['search']['clfoco'] = substr($cliente['ragso1'], 0, 10);
    $form['print_total'] = $tesbro['print_total'];
    $form['delivery_time'] = $tesbro['delivery_time'];
    $form['day_of_validity'] = $tesbro['day_of_validity'];
    $form['cosear'] = "";
    $form['seziva'] = $tesbro['seziva'];
    $form['tipdoc'] = $tesbro['tipdoc'];
    $form['gioemi'] = substr($tesbro['datemi'], 8, 2);
    $form['mesemi'] = substr($tesbro['datemi'], 5, 2);
    $form['annemi'] = substr($tesbro['datemi'], 0, 4);
    $form['giotra'] = substr($tesbro['initra'], 8, 2);
    $form['mestra'] = substr($tesbro['initra'], 5, 2);
    $form['anntra'] = substr($tesbro['initra'], 0, 4);
    $form['oratra'] = substr($tesbro['initra'], 11, 2);
    $form['mintra'] = substr($tesbro['initra'], 14, 2);
    $form['protoc'] = $tesbro['protoc'];
    $form['numdoc'] = $tesbro['numdoc'];
    $form['numfat'] = $tesbro['numfat'];
    $form['datfat'] = $tesbro['datfat'];
    $form['clfoco'] = $tesbro['clfoco'];
    $form['pagame'] = $tesbro['pagame'];
    $form['change_pag'] = $tesbro['pagame'];
    $form['speban'] = $tesbro['speban'];
    $pagame = gaz_dbi_get_row($gTables['pagame'], "codice", $form['pagame']);
    if (($pagame['tippag'] == 'B' or $pagame['tippag'] == 'T' or $pagame['tippag'] == 'V' or $pagame['tippag'] == 'K') and $cliente['speban'] == 'S') {
        $form['numrat'] = $pagame['numrat'];
    } else {
        $form['speban'] = 0.00;
        $form['numrat'] = 1;
    }
    $form['banapp'] = $tesbro['banapp'];
    $form['weekday_repeat'] = $tesbro['weekday_repeat'];
    $form['vettor'] = $tesbro['vettor'];
    $form['id_agente'] = $tesbro['id_agente'];
    $provvigione = new Agenti;
    $form['in_provvigione'] = $provvigione->getPercent($form['id_agente']);
    $form['net_weight'] = $tesbro['net_weight'];
    $form['gross_weight'] = $tesbro['gross_weight'];
    $form['units'] = $tesbro['units'];
    $form['volume'] = $tesbro['volume'];
    $form['listin'] = $tesbro['listin'];
    $form['spediz'] = $tesbro['spediz'];
    $form['portos'] = $tesbro['portos'];
    $form['imball'] = $tesbro['imball'];
    $form['destin'] = $tesbro['destin'];
    $form['id_des'] = $tesbro['id_des'];
    $form['id_des_same_company'] = $tesbro['id_des_same_company'];
    $form['search']['id_des'] = substr($id_des['ragso1'], 0, 10);
    $form['traspo'] = $tesbro['traspo'];
    $form['spevar'] = $tesbro['spevar'];
    $form['expense_vat'] = $tesbro['expense_vat'];
    $form['virtual_taxstamp'] = $tesbro['virtual_taxstamp'];
    $form['taxstamp'] = $tesbro['taxstamp'];
    $form['stamp'] = $tesbro['stamp'];
    $form['round_stamp'] = $tesbro['round_stamp'];
    $form['cauven'] = $tesbro['cauven'];
    $form['caucon'] = $tesbro['caucon'];
    $form['caumag'] = $tesbro['caumag'];
    $form['caucon'] = $tesbro['caucon'];
    $form['sconto'] = $tesbro['sconto'];
    $next_row = 0;
    while ($rigo = gaz_dbi_fetch_array($rs_rig)) {
        $articolo = gaz_dbi_get_row($gTables['artico'], "codice", $rigo['codart']);
        if ($rigo['id_body_text'] > 0) { //se ho un rigo testo
            $text = gaz_dbi_get_row($gTables['body_text'], "id_body", $rigo['id_body_text']);
            $form["row_$next_row"] = $text['body_text'];
        }
        $form['rows'][$next_row]['descri'] = $rigo['descri'];
        $form['rows'][$next_row]['tiprig'] = $rigo['tiprig'];
        $form['rows'][$next_row]['id_doc'] = $rigo['id_doc'];
        $form['rows'][$next_row]['codart'] = $rigo['codart'];
		$form['rows'][$next_row]['good_or_service'] = $articolo['good_or_service'];
        $form['rows'][$next_row]['pervat'] = $rigo['pervat'];
        $iva_row = gaz_dbi_get_row($gTables['aliiva'], 'codice', $rigo['codvat']);
        $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
        $form['rows'][$next_row]['ritenuta'] = $rigo['ritenuta'];
        $form['rows'][$next_row]['unimis'] = $rigo['unimis'];
        $form['rows'][$next_row]['prelis'] = number_format($rigo['prelis'], $admin_aziend['decimal_price'], '.', '');
        $form['rows'][$next_row]['sconto'] = $rigo['sconto'];
        $form['rows'][$next_row]['quanti'] = gaz_format_quantity($rigo['quanti'], 0, $admin_aziend['decimal_quantity']);
        $form['rows'][$next_row]['codvat'] = $rigo['codvat'];
        $form['rows'][$next_row]['codric'] = $rigo['codric'];
        $form['rows'][$next_row]['provvigione'] = $rigo['provvigione'];
        $form['rows'][$next_row]['id_mag'] = $rigo['id_mag'];
        $form['rows'][$next_row]['annota'] = $articolo['annota'];
        $mv = $upd_mm->getStockValue(false, $rigo['codart'], $form['annemi'] . '-' . $form['mesemi'] . '-' . $form['gioemi'], $admin_aziend['stock_eval_method']);
        $magval = array_pop($mv);
        $magval=(is_numeric($magval))?['q_g'=>0,'v_g'=>0]:$magval;
        $form['rows'][$next_row]['scorta'] = $articolo['scorta'];
        $form['rows'][$next_row]['quamag'] = $magval['q_g'];
        $form['rows'][$next_row]['pesosp'] = $articolo['peso_specifico'];
        $form['rows'][$next_row]['extdoc'] = '';
        $form['rows'][$next_row]['status'] = "UPDATE";
		// recupero il filename dal filesystem e lo sposto sul tmp
		$dh = opendir( DATA_DIR . 'files/' . $admin_aziend['company_id'] );
		while (false !== ($filename = readdir($dh))) {
				$fd = pathinfo($filename);
				$r = explode('_', $fd['filename']);
				if ($r[0] == 'rigbrodoc' && $r[1] == $rigo['id_rig']) {
					/* 	uso id_body_text per mantenere il riferimento riferimento al file del documento esterno
					* 	e riassegno il nome file
					*/
					$form['rows'][$next_row]['extdoc'] = $fd['basename'];
				}
		}
        $next_row++;
    }
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    if (!isset($_GET['tipdoc'])) {
        $form['tipdoc'] = "VPR";
    } else {
        $form['tipdoc'] = $_GET['tipdoc'];
    }
    $form['id_tes'] = "";
    $form['weekday_repeat'] = date("N") - 1;
    $form['gioemi'] = date("d");
    $form['mesemi'] = date("m");
    $form['annemi'] = date("Y");
    $form['giotra'] = date("d");
    $form['mestra'] = date("m");
    $form['anntra'] = date("Y");
    $form['oratra'] = date("H");
    $form['mintra'] = date("i");
    $form['rows'] = array();
    $next_row = 0;
    $form['hidden_req'] = '';
    // inizio rigo di input
    $form['in_descri'] = "";
    $form['in_tiprig'] = 0;
    $form['in_id_doc'] = 0;
    /*   $form['in_artsea'] = $admin_aziend['artsea']; */
    $form['in_codart'] = "";
	$form['in_good_or_service'] = "";
    $form['in_pervat'] = "";
    $form['in_tipiva'] = "";
    $form['in_ritenuta'] = 0;
    $form['in_unimis'] = "";
    $form['in_prelis'] = 0.000;
    /** inizio modifica FP 09/10/2015
     * inizializzo il campo con '#' per indicare che voglio lo sconto standard dell'articolo
     */
//rimossa    $form['in_sconto'] = 0;
    $form['in_sconto'] = '#';
    /* fine modifica FP */
    $form['in_quanti'] = 0;
    $form['in_codvat'] = 0;
    $form['in_codric'] = substr($admin_aziend['impven'], 0, 3);
    $form['in_provvigione'] = 0;
    $form['in_id_mag'] = 0;
    $form['in_annota'] = "";
    $form['in_scorta'] = 0;
    $form['in_quamag'] = 0;
    $form['in_pesosp'] = 0;
    $form['in_extdoc'] = 0;
    $form['in_status'] = "INSERT";
    $form['in_codric'] = $admin_aziend['impven'];

    // fine rigo input
    $form['search']['clfoco'] = '';
	$print_total = gaz_dbi_get_row($gTables['company_config'], 'var', 'print_total');
    $form['print_total'] = intval($print_total['val']);
	$delivery_time = gaz_dbi_get_row($gTables['company_config'], 'var', 'delivery_time');
    $form['delivery_time'] = intval($delivery_time['val']);
	$day_of_validity = gaz_dbi_get_row($gTables['company_config'], 'var', 'day_of_validity');
    $form['day_of_validity'] = intval($day_of_validity['val']);
    $form['cosear'] = "";
    if (isset($_GET['seziva'])) {
        $form['seziva'] = intval($_GET['seziva']);
    } else {
        $form['seziva'] = 1;
    }
    $form['protoc'] = "";
    $form['numdoc'] = "";
    $form['numfat'] = "";
    $form['datfat'] = "";
    $form['clfoco'] = "";
    $form['pagame'] = "";
    $form['change_pag'] = "";
    $form['banapp'] = "";
    $form['vettor'] = "";
    $form['id_agente'] = 0;
    $form['net_weight'] = 0;
    $form['gross_weight'] = 0;
    $form['units'] = 0;
    $form['volume'] = 0;
    $form['listin'] = "";
    $form['destin'] = "";
    $form['id_des'] = 0;
    $form['id_des_same_company'] = 0;
    $form['search']['id_des'] = '';
    $form['spediz'] = "";
    $form['portos'] = "";
    $form['imball'] = "";
    $form['traspo'] = 0.00;
    $form['numrat'] = 1;
    $form['speban'] = 0;
    $form['spevar'] = 0;
    $form['expense_vat'] = $admin_aziend['preeminent_vat'];
    $form['stamp'] = 0;
    $form['round_stamp'] = $admin_aziend['round_bol'];
    $form['virtual_taxstamp'] = $admin_aziend['virtual_taxstamp'];
    $form['taxstamp'] = 0;
    $form['cauven'] = 0;
    $form['caucon'] = '';
    $form['caumag'] = 0;
    $form['sconto'] = 0;
    $cliente['indspe'] = "";
	$ultimoprezzo=''; //info sugli ultimi prezzi
}
require("../../library/include/header.php");
require("./lang." . $admin_aziend['lang'] . ".php");
$script_transl = $strScript["admin_broven.php"] + HeadMain(0, array(/* 'tiny_mce/tiny_mce', */
            /* 'boxover/boxover', */
            'calendarpopup/CalendarPopup',
            'custom/autocomplete',
            /* 'jquery/toast/javascript/jquery.toastmessage', */
            'custom/miojs'
                /** ENRICO FEDELE */
                /* 'jquery/jquery-1.7.1.min',
                  'jquery/ui/jquery.ui.core',
                  'jquery/ui/jquery.ui.widget',
                  'jquery/ui/jquery.ui.position',
                  'jquery/ui/jquery.ui.autocomplete', */
                /**/
                /** ENRICO FEDELE */                ));
if ($form['id_tes'] > 0) {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0][$form['tipdoc']]) . " n." . $form['numdoc'];
} else {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0][$form['tipdoc']]);
}
echo '<script type="text/javascript">';
if (empty($msg)) { // se ho un errore non scrollo
	if (!empty($_POST['last_focus'])){
		$idlf='#'.$_POST['last_focus'];
		$_POST['last_focus']='';
	} else {
		$idlf="#search_cosear";
	}
	echo '
	$( function() {
				$("html, body").delay(100).animate({scrollTop: $("'.$idlf.'").offset().top-100}, 1000);
				}); ';
}

echo "
function pulldown_menu(selectName, destField)
{
    // Create a variable url to contain the value of the
    // selected option from the the form named broven and variable selectName
    var url = document.broven[selectName].options[document.broven[selectName].selectedIndex].value;
    document.broven[destField].value = url;
}";
echo "
   function preStampa() // stampa il dettaglio del preventivo senza salvarlo
    {
        var mywindow = window.open('', 'my div', 'height=400,width=600');
        mywindow.document.write('<html><head><title>Stampa</title>');

        mywindow.document.write('</head><body >');
        //alert($('[name=\"change\"]').val());
        mywindow.document.write('<h1>CLIENTE: '+$('[name=\"change\"]').val()+'</h1>');
        mywindow.document.write('<table name=lista border=1> ');
        mywindow.document.write($('[name=\"elenco\"]').html());
        mywindow.document.write('</table> ');
        mywindow.document.write('<h2>TOTALE: &#8364; '+$('[name=\"totale\"]').html()+'</h2>');
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10

        mywindow.print();
        mywindow.close();

        return true;
    }
";
echo "</script>\n";

echo '
<SCRIPT LANGUAGE="JavaScript" ID="datapopup">
    var cal = new CalendarPopup();
    cal.setReturnFunction("setMultipleValues");
    function setMultipleValues(y, m, d) {
        document.broven.anntra.value = y;
        document.broven.mestra.value = LZ(m);
        document.broven.giotra.value = LZ(d);
    }
</SCRIPT>';

echo "\n";

/******************************************************

             I N I Z I O   P A G I N A

******************************************************/


echo "<form method=\"POST\" name=\"broven\" enctype=\"multipart/form-data\">\n";
$gForm = new venditForm();
echo '	<input type="hidden" name="' . ucfirst($toDo) . '" value="" />
		<input type="hidden" value="' . $form['id_tes'] . '" name="id_tes" />
		<input type="hidden" value="' . $form['seziva'] . '" name="seziva" />
		<input type="hidden" value="' . $form['tipdoc'] . '" name="tipdoc" />
		<input type="hidden" value="' . $form['ritorno'] . '" name="ritorno" />
		<input type="hidden" value="' . $form['change_pag'] . '" name="change_pag" />
		<input type="hidden" value="' . $form['protoc'] . '" name="protoc" />
		<input type="hidden" value="' . $form['numdoc'] . '" name="numdoc" />
		<input type="hidden" value="' . $form['numfat'] . '" name="numfat" />
		<input type="hidden" value="' . $form['datfat'] . '" name="datfat" />
		<input type="hidden" value="' . (isset($_POST['last_focus']) ? $_POST['last_focus'] : "") . '" name="last_focus" />
		<div align="center" class="FacetFormHeaderFont">' . $title . '  a :';
$select_cliente = new selectPartner('clfoco');
$select_cliente->selectDocPartner('clfoco', $form['clfoco'], $form['search']['clfoco'], 'clfoco', $script_transl['mesg'], $admin_aziend['mascli']);
echo '	</div><div class="table-responsive">
		<table class="Tlarge table table-striped table-bordered table-condensed">
			<tr>
				<td class="FacetFieldCaptionTD">' . $script_transl[4] . '</td>
				<td class="FacetDataTD">
					<select name="seziva" class="FacetSelect">';
for ($counter = 1; $counter <= 5; $counter++) {
    $selected = "";
    if ($form['seziva'] == $counter) {
        $selected = ' selected=""';
    }
    echo '				<option value="' . $counter . '"' . $selected . '>' . $counter . '</option>';
}
echo '				</select>
				</td>';
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice(explode('+', chop($msg)), 0, -1);
    foreach ($rsmsg as $v) {
        $message .= $script_transl['error'] . "! -> ";
        $rsval = explode('-', chop($v));
        foreach ($rsval as $valmsg) {
            $message .= $script_transl[$valmsg] . " ";
        }
        $message .= "<br />";
    }
    echo '			<td colspan="2" class="FacetDataTDred">' . $message . '</td>';
} else {
    echo '			<td class="FacetFieldCaptionTD">' . $script_transl[5] . '</td>
   					<td class="FacetDataTD">' . $cliente['indspe'] . '<br /></td>';
}
echo '			<td class="FacetFieldCaptionTD">' . $script_transl[6] . '</td>
				<td class="FacetDataTD">';
if ($form['tipdoc'] == 'VOG') {
    echo "<input name='gioemi' type='hidden' value=" . $form['gioemi'] . ">";
    echo "<input name='mesemi' type='hidden' value=" . $form['mesemi'] . ">";
    echo "<input name='annemi' type='hidden' value=" . $form['annemi'] . ">";

    echo '<select name="weekday_repeat" class="FacetSelect">';
    for ($t = 0; $t != 7; $t++) {
        if ($t == $form['weekday_repeat'])
            $selected = " selected";
        else
            $selected = "";
        echo "<option value='" . $t . "' " . $selected . ">" . getDayNameFromDayNumber($t) . "</option>";
    }
    echo '</select>';
} else {
    echo "<input name='weekday_repeat' type='hidden' value=" . $form['weekday_repeat'] . ">";

    echo '<select name="gioemi" class="FacetSelect">';
    for ($counter = 1; $counter <= 31; $counter++) {
        $selected = "";
        if ($counter == $form['gioemi']) {
            $selected = ' selected=""';
        }
        echo '					<option value="' . $counter . '"' . $selected . '>' . $counter . '</option>';
    }
    echo '				</select>';
    // select del mese
    echo '				<select name="mesemi" class="FacetSelect">';
    for ($counter = 1; $counter <= 12; $counter++) {
        $selected = "";
        if ($counter == $form['mesemi']) {
            $selected = ' selected=""';
        }
        $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
        echo '					<option value="' . $counter . '"' . $selected . '>' . $nome_mese . '</option>';
    }
    echo '				</select>';
    // select del anno
    echo '				<select name="annemi" class="FacetSelect" onchange="this.form.submit()">';
    for ($counter = $form['annemi'] - 10; $counter <= $form['annemi'] + 10; $counter++) {
        $selected = "";
        if ($counter == $form['annemi']) {
            $selected = ' selected=""';
        }
        echo '					<option value="' . $counter . '"' . $selected . '>' . $counter . '</option>';
    }
    echo '				</select>';
}
echo '
                </td>
			</tr>
			<tr>
				<td class="FacetFieldCaptionTD">' . $script_transl[7] . '</td>
				<td class="FacetDataTD">
					<select name="listin" class="FacetSelect">';
for ($lis = 1; $lis <= 5; $lis++) {
    $selected = "";
    if ($form['listin'] == $lis) {
        $selected = ' selected=""';
    }
    echo '					<option value="' . $lis . '"' . $selected . '>' . $lis . '</option>';
}
echo '				</select>
				</td>
				<td class="FacetFieldCaptionTD">' . $script_transl[8] . '</td>
				<td class="FacetDataTD">';
$select_pagame = new selectpagame("pagame");
$select_pagame->addSelected($form['pagame']);
$select_pagame->output();
echo '			</td>
				<td class="FacetFieldCaptionTD">' . $script_transl[9] . '</td>
				<td class="FacetDataTD">';
$select_banapp = new selectbanapp("banapp");
$select_banapp->addSelected($form['banapp']);
$select_banapp->output();
echo '			</td>
			</tr>
			<tr>
				<td class="FacetFieldCaptionTD">' . $script_transl['print_total'] . '</td>
				<td class="FacetDataTD">';
$gForm->variousSelect('print_total', $script_transl['print_total_value'], $form['print_total']);
echo '			</td>
				<td class="FacetFieldCaptionTD" title="' . $script_transl['day_of_validity'] . '">' . $script_transl['day_of_validity'] . '</td>
				<td class="FacetDataTD" title="' . $script_transl['day_of_validity'] . '">
					<input type="text" value="' . $form['day_of_validity'] . '" name="day_of_validity" maxlength="3" />
				</td>
				<td class="FacetFieldCaptionTD" title="' . $script_transl['delivery_time'] . '">' . $script_transl['delivery_time'] . '</td>
				<td class="FacetDataTD" title="' . $script_transl['delivery_time'] . '">
					<input type="text" value="' . $form['delivery_time'] . '" name="delivery_time" maxlength="3" />
				</td>
			</tr>
			<tr>
			<td class="FacetFieldCaptionTD" title="' . $script_transl['speban_title'] . '">' . $script_transl['speban'] . '</td>
			<td class="FacetDataTD" title="' . $script_transl['speban_title'] . '">
				<input type="text" value="' . $form['speban'] . '" name="speban" maxlength="6" onchange="this.form.submit()" /> x ' . $form['numrat'] . '
			</td>
			<td class="FacetFieldCaptionTD">' . $script_transl[10] . '</td>';
if ($form['id_des_same_company'] > 0) { //  è una destinazione legata all'anagrafica
        echo "<td class=\"FacetDataTD\">\n";
        $gForm->selectFromDB('destina', 'id_des_same_company', 'codice', $form['id_des_same_company'], 'codice', true, '-', 'unita_locale1', '', 'FacetSelect', null, '', "id_anagra = '" . $cliente['id_anagra'] . "'");
        echo "	<input type=\"hidden\" name=\"id_des\" value=\"" . $form['id_des'] . "\">
                <input type=\"hidden\" name=\"destin\" value=\"" . $form['destin'] . "\" /></td>\n";
    } elseif ($form['id_des'] > 0) { // la destinazione è un'altra anagrafica
        echo "<td class=\"FacetDataTD\">\n";
        $select_id_des = new selectPartner('id_des');
        $select_id_des->selectDocPartner('id_des', 'id_' . $form['id_des'], $form['search']['id_des'], 'id_des', $script_transl['mesg'], $admin_aziend['mascli']);
        echo "			<input type=\"hidden\" name=\"id_des_same_company\" value=\"" . $form['id_des_same_company'] . "\">
                                <input type=\"hidden\" name=\"destin\" value=\"" . $form['destin'] . "\" />
						</td>\n";
    } else {
        echo "			<td class=\"FacetDataTD\">";
        echo "				<textarea rows=\"1\" cols=\"30\" name=\"destin\" class=\"FacetInput\">" . $form["destin"] . "</textarea>
						</td>
						<input type=\"hidden\" name=\"id_des_same_company\" value=\"" . $form['id_des_same_company'] . "\">
						<input type=\"hidden\" name=\"id_des\" value=\"" . $form['id_des'] . "\">
						<input type=\"hidden\" name=\"search[id_des]\" value=\"" . $form['search']['id_des'] . "\"></td>\n";
    }
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_agente'] . "</td>";
echo "<td  class=\"FacetDataTD\">\n";
$select_agente = new selectAgente("id_agente");
$select_agente->addSelected($form["id_agente"]);
$select_agente->output();
echo '		</td>
		</tr>
	  </table></div>';
echo '<div class="table-responsive">
	  <table name="elenco" class="Tlarge table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<td class="FacetFieldCaptionTD">' . $script_transl[20] . '</td>
				<td class="FacetFieldCaptionTD" colspan="2">' . $script_transl[21] . '</td>
				<td class="FacetFieldCaptionTD">' . $script_transl[22] . '</td>
                <td class="FacetFieldCaptionTD">' . $script_transl[16] . '</td>
                <td class="FacetFieldCaptionTD">' . $script_transl[23] . '</td>
				<td class="FacetFieldCaptionTD">%' . substr($script_transl[24], 0, 2) . '.</td>
				<td class="FacetFieldCaptionTD">%' . substr($script_transl[56], 0, 5) . '.</td>
				<td class="FacetFieldCaptionTD text-right">' . $script_transl[25] . '</td>
				<td class="FacetFieldCaptionTD">' . $script_transl[19] . '</td>
				<td class="FacetFieldCaptionTD">' . $script_transl[18] . '</td>
				<td class="FacetFieldCaptionTD"></td>
			</tr>
		</thead>
		<tbody>';
$totimp_body = 0.00;
$totivafat = 0.00;
$totimpfat = 0.00;
$castle = array();
$rit = 0;
$carry = 0;
$last_row = array();
$vp = gaz_dbi_get_row($gTables['company_config'], 'var', 'vat_price')['val'];
foreach ($form['rows'] as $k => $v) {
    //creo il castelletto IVA
    $imprig = 0;
    if ($v['tiprig'] <= 1) {
        $imprig = CalcolaImportoRigo($v['quanti'], $v['prelis'], $v['sconto']);
        $v_for_castle = CalcolaImportoRigo($v['quanti'], $v['prelis'], array($v['sconto'], $form['sconto']));
        if ($v['tiprig'] == 1) {//ma se del tipo forfait
            $imprig = CalcolaImportoRigo(1, $v['prelis'], 0);
            $v_for_castle = CalcolaImportoRigo(1, $v['prelis'], $form['sconto']);
        }
        if (!isset($castle[$v['codvat']])) {
            $castle[$v['codvat']]['impcast'] = 0.00;
        }
        $totimp_body += $imprig;
        $castle[$v['codvat']]['impcast'] += $v_for_castle;
        $rit += round($imprig * $v['ritenuta'] / 100, 2);
    } elseif ($v['tiprig'] == 3) {
        $carry += $v['prelis'];
    }
    $descrizione = htmlentities($v['descri'], ENT_QUOTES);
    ;

    echo "<input type=\"hidden\" value=\"" . $v['codart'] . "\" name=\"rows[$k][codart]\">\n";
	echo "<input type=\"hidden\" value=\"" . $v['good_or_service'] . "\" name=\"rows[$k][good_or_service]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['status'] . "\" name=\"rows[$k][status]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['tiprig'] . "\" name=\"rows[$k][tiprig]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['id_doc'] . "\" name=\"rows[$k][id_doc]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['codvat'] . "\" name=\"rows[$k][codvat]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['pervat'] . "\" name=\"rows[$k][pervat]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['tipiva'] . "\" name=\"rows[$k][tipiva]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['ritenuta'] . "\" name=\"rows[$k][ritenuta]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['codric'] . "\" name=\"rows[$k][codric]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['id_mag'] . "\" name=\"rows[$k][id_mag]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['annota'] . "\" name=\"rows[$k][annota]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['scorta'] . "\" name=\"rows[$k][scorta]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['quamag'] . "\" name=\"rows[$k][quamag]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['pesosp'] . "\" name=\"rows[$k][pesosp]\">\n";
    echo "<input type=\"hidden\" value=\"" . $v['extdoc'] . "\" name=\"rows[$k][extdoc]\">\n";
    //stampo i rows in modo diverso a secondo del tipo
    echo "<tr>";
    switch ($v['tiprig']) {
        case "0":
			if ($v['good_or_service']<>1){
				if ($v['quamag'] < 0.00001 && $admin_aziend['conmag']==2) { // se gestisco la contabilità di magazzino controllo presenza articolo
					$btn_class = 'btn-danger';
					$btn_title = ' ARTICOLO NON DISPONIBILE';
				} elseif ($v['quamag'] <= $v['scorta'] && $admin_aziend['conmag']==2) { // se gestisco la contabilità di magazzino controllo il sottoscorta
					$btn_class = 'btn-warning';
					$btn_title = ' Articolo sottoscorta: disponibili '.$v['quamag'].'/'.floatval($v['scorta']);
				} else {
					$btn_class = 'btn-success';
					$btn_title = " ".$v['quamag'].' '.$v['unimis'].' disponibili';
				}
			} else {
				$btn_class = 'btn-info';
				$btn_title = " Senza magazzino";
			}
            /* Peso */
            $peso = 0;
            if ($v['pesosp'] <> 0) {
                $peso = gaz_format_number($v['quanti'] / $v['pesosp']);
            }
            echo '	<td>
					<button type="image" name="upper_row[' . $k . ']" class="btn btn-default btn-xs" title="' . $script_transl['3'] . '!">
						<i class="glyphicon glyphicon-arrow-up"></i>
					</button>
			  	</td>
                                <td title="' . $script_transl['update'] . $script_transl['thisrow'] . '!' . $btn_title . '">
					<button name="upd_row[' . $k . ']" class="btn btn-xs ' . $btn_class . ' btn-block" type="submit">
						<i class="glyphicon glyphicon-refresh"></i>&nbsp;' . $v['codart'] . '
					</button>
			 	</td>
				<td>
		 			<input class="gazie-tooltip" data-type="product-thumb" data-id="' . $v["codart"] . '" data-title="' . $v['annota'] . '" type="text" name="rows[' . $k . '][descri]" value="' . $descrizione . '" maxlength="100" />
			   	</td>
			    <td>
					<input class="gazie-tooltip" data-type="weight" data-id="' . $peso . '" data-title="' . $script_transl['weight'] . '" type="text" name="rows[' . $k . '][unimis]" value="' . $v['unimis'] . '" maxlength="3" />
				</td>
				<td>
					<input class="gazie-tooltip" data-type="weight" data-id="' . $peso . '" data-title="' . $script_transl['weight'] . '" type="text" name="rows[' . $k . '][quanti]" value="' . $v['quanti'] . '" align="right" maxlength="11" id="righi_' . $k . '_quanti" onchange="document.broven.last_focus.value=\'righi_' . $k . '_prelis\'; this.form.hidden_req.value=\'ROW\'; this.form.submit();" />
                </td>';
            echo "<td><input type=\"text\" name=\"rows[$k][prelis]\" value=\"" . $v['prelis'] . "\" align=\"right\" maxlength=\"11\" ";
			if ($vp>0) { // solo se scelto in configurazione avanzata azienda si vedrà il dialog per mettere il prezzo iva compresa
				echo ' onclick="vatPrice(\''.$k.'\',\''.$v['pervat'].'\');"';
			}
            echo " id=\"righi_" . $k . "_prelis\" onchange=\"document.broven.last_focus.value='righi_" . $k . "_sconto'; this.form.submit()\" /></td>\n";
            echo "<td><input type=\"text\" name=\"rows[$k][sconto]\" value=\"" . $v['sconto'] . "\" maxlength=\"6\"  id=\"righi_" . $k . "_sconto\" onchange=\"document.broven.last_focus.value=this.id; this.form.submit()\" /></td>\n";
            echo "<td><input type=\"text\" name=\"rows[$k][provvigione]\" value=\"" . $v['provvigione'] . "\" maxlength=\"6\"  /></td>\n";
            echo "<td class=\"text-right\">" . gaz_format_number($imprig) . "</td>\n";
            echo "<td class=\"text-right\">" . $v['pervat'] . "%</td>\n";
            echo "<td class=\"text-right codricTooltip\" title=\"Contropartita\">" . $v['codric'] . "</td>\n";

            $last_row[] = array_unshift($last_row, '<strong>' . $v['codart'] . '</strong>, ' . $v['descri'] . ', ' . $v['quanti'] . $v['unimis'] . ', <strong>' . $script_transl[23] . '</strong>: ' . gaz_format_number($v['prelis']) . ', %<strong>' . substr($script_transl[24], 0, 2) . '</strong>: ' . gaz_format_number($v['sconto']) . ', <strong>' . $script_transl[25] . '</strong>: ' . gaz_format_number($imprig) . ', <strong>' . $script_transl[19] . '</strong>: ' . $v['pervat'] . '%, <strong>' . $script_transl[18] . '</strong>: ' . $v['codric']);
            break;
        case "1":
            echo '		<td>
						<button type="image" name="upper_row[' . $k . ']" class="btn btn-default btn-xs" title="' . $script_transl['3'] . '!">
							<i class="glyphicon glyphicon-arrow-up"></i>
						</button>
					</td>'
            . '<td title="' . $script_transl['update'] . $script_transl['thisrow'] . '!">
              			<input class="FacetDataTDsmall" type="submit" name="upd_row[' . $k . ']" value="' . $script_transl['typerow'][$v['tiprig']] . '" />
					</td>
			  		<td>
		 				<input type="text" name="rows[' . $k . '][descri]" value="' . $descrizione . '" maxlength="100" />
					</td>

					<td>
						<input type="hidden" name="rows[' . $k . '][unimis]" value="" />
                    </td>
					<td>
                        <input type="hidden" name="rows[' . $k . '][quanti]" value="" />
                    </td>
					<td>
						<input type="hidden" name="rows[' . $k . '][sconto]" value="" />
					</td>
					<td>
						<input type="hidden" name="rows[' . $k . '][provvigione]" value="" />
					</td>
					<td></td>
					<td class="text-right">
						<input style="text-align:right" type="text" name="rows[' . $k . '][prelis]" value="' . number_format($v['prelis'], 2, '.', '') . '" align="right" maxlength="11" ';
						if ($vp>0) { // solo se scelto in configurazione avanzata azienda si vedrà il dialog per mettere il prezzo iva compresa
							echo ' onclick="vatPrice(\''.$k.'\',\''.$v['pervat'].'\');"';
						}
						echo ' id="righi_' . $k . '_prelis" onchange="document.broven.last_focus.value=this.id; this.form.submit()" />
					</td>
					<td class="text-right">' . $v['pervat'] . '%</td>
					<td class="text-right codricTooltip" title="Contropartita">' . $v['codric'] . '</td>';
            $last_row[] = array_unshift($last_row, $script_transl['typerow'][$v['tiprig']]);
            break;
        case "2": // descrittivo
            echo "	<td>
				<button type=\"image\" name=\"upper_row[" . $k . "]\" class=\"btn btn-default btn-xs\" title=\"" . $script_transl['3'] . "!\">
						<i class=\"glyphicon glyphicon-arrow-up\"></i>
				</button>
			</td>
                        <td title=\"" . $script_transl['update'] . $script_transl['thisrow'] . "!\">
                                <input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"" . $script_transl['typerow'][$v['tiprig']] . "\" />
			</td>
			<td>
				<input type=\"text\" name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"100\"  />
			</td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" /></td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>\n";
            echo "<td></td>\n";
            echo "<td></td>\n";
            echo "<td></td>\n";
            echo "<td></td>\n";
            $last_row[] = array_unshift($last_row, $script_transl['typerow'][$v['tiprig']]);
            break;
        case "3":
            echo "	<td title=\"" . $script_transl['update'] . $script_transl['thisrow'] . "!\">
              		<input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"" . $script_transl['typerow'][$v['tiprig']] . "\" />
				</td>
			  	<td>
		 			<input type=\"text\" name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"100\" >
				</td>
				<td>
					<button type=\"image\" name=\"upper_row[" . $k . "]\" class=\"btn btn-default btn-xs\" title=\"" . $script_transl['3'] . "!\">
						<i class=\"glyphicon glyphicon-arrow-up\"></i>
					</button>
				</td>
				<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>
                <td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>
				<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>
				<td></td>
				<td></td>
				<td class=\"text-right\"><input type=\"text\" name=\"rows[$k][prelis]\" value=\"" . $v['prelis'] . "\" align=\"right\" maxlength=\"11\"  /></td>
				<td></td>
				<td></td>\n";
            $last_row[] = array_unshift($last_row, $script_transl['typerow'][$v['tiprig']]);
            break;
        case "6":
        case "7":
        case "8":
            /**
              <textarea id="row_'.$k.'" name="row_'.$k.'" class="mceClass'.$k.'" style="width:100%;height:100px;">'.$form["row_$k"].'</textarea>
             */
            echo '	<td title="' . $script_transl['update'] . $script_transl['thisrow'] . '!">
					<button type="image" name="upper_row[' . $k . ']" class="btn btn-default btn-xs" title="' . $script_transl['3'] . '!">
						<i class="glyphicon glyphicon-arrow-up"></i>
					</button>
		 			<input class="FacetDataTDsmall" type="submit" name="upd_row[' . $k . ']" value="' . $script_transl['typerow'][$v['tiprig']] . '" />
				</td>
				<td colspan="10">
					<textarea id="row_' . $k .
                '" name="row_' . $k .
                '" class="mceClass" style="width:100%;height:100px;">'
                . $form["row_$k"] . '</textarea>
				</td>
				<input type="hidden" value="" name="rows[' . $k . '][descri]" />
				<input type="hidden" value="" name="rows[' . $k . '][unimis]" />
                <input type="hidden" value="" name="rows[' . $k . '][quanti]" />
				<input type="hidden" value="" name="rows[' . $k . '][prelis]" />
				<input type="hidden" value="" name="rows[' . $k . '][sconto]" />
				<input type="hidden" value="" name="rows[' . $k . '][provvigione]" />';
            $last_row[] = array_unshift($last_row, $script_transl['typerow'][$v['tiprig']]);
            break;
        case "11": // CIG fattura PA
        case "12": // CUP fattura PA
            echo "	<td>
						<button type=\"image\" name=\"upper_row[" . $k . "]\" class=\"btn btn-default btn-xs\" title=\"" . $script_transl['3'] . "!\">
							<i class=\"glyphicon glyphicon-arrow-up\"></i>
						</button>
					</td>
					<td title=\"" . $script_transl['update'] . $script_transl['thisrow'] . "!\">
						<input class=\"btn btn-xs btn-success btn-block\" type=\"submit\" name=\"upd_row[$k]\" value=\"" . $script_transl['typerow'][$v['tiprig']] . "\" />
					</td>
					<td>
						<input type=\"text\"   name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"100\"  />
					</td>
					<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>
                    <td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>
					<td><input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" /></td>
					<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>
					<td><input type=\"hidden\" name=\"rows[$k][provvigione]\" value=\"\" /></td>
					<td></td>
					<td></td>
					<td></td>\n";
            $last_row[] = array_unshift($last_row, $script_transl['typerow'][$v['tiprig']]);
            break;

        case "13": // ID documento fattura PA
            echo "	<td>
						<button type=\"image\" name=\"upper_row[" . $k . "]\" class=\"btn btn-default btn-xs\" title=\"" . $script_transl['3'] . "!\">
							<i class=\"glyphicon glyphicon-arrow-up\"></i>
						</button>
					</td>
                                        <td title=\"" . $script_transl['update'] . $script_transl['thisrow'] . "!\">
              			<input class=\"btn btn-xs btn-success btn-block\" type=\"submit\" name=\"upd_row[$k]\" value=\"" . $script_transl['typerow'][$v['tiprig']] . "\" />
					</td>
					<td>
						<input type=\"text\"   name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"100\"  />
					</td>
					<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>
                    <td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>
					<td><input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" /></td>
					<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>
					<td><input type=\"hidden\" name=\"rows[$k][provvigione]\" value=\"\" /></td>
					<td></td>
					<td></td>
					<td></td>\n";
            $last_row[] = array_unshift($last_row, $script_transl['typerow'][$v['tiprig']]);
            break;
        case "50":
			echo "<td><button type=\"image\" name=\"upper_row[" . $k . "]\" class=\"btn btn-default btn-sm\" title=\"" . $script_transl['3'] . "!\"><i class=\"glyphicon glyphicon-arrow-up\"></i></button></td>";
            echo "<td title=\"" . $script_transl['update'] . $script_transl['thisrow'] . "!\"><input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[{$k}]\" value=\"* documento allegato *\" />\n";
                if (empty($form['rows'][$k]['extdoc'])) {
                    echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#extdoc_dialog' . $k . '">'
                    . $script_transl['insert'] . ' documento esterno <i class="glyphicon glyphicon-tag"></i>'
                    . '</button></div>';
                } else {
                    echo '<div>documento esterno:<button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#extdoc_dialog' . $k . '">'
                    . $form['rows'][$k]['extdoc'] . ' <i class="glyphicon glyphicon-tag"></i>'
                    . '</button></div>';
                }
				echo '<div id="extdoc_dialog' . $k . '" class="collapse" >
                        <div class="form-group">
                          <div>';

                echo '<input type="file" onchange="this.form.submit();" name="docfile_' . $k . '">
                            <label>File: </label><input type="text" name="rows[' . $k . '][extdoc]" value="' . $form['rows'][$k]['extdoc'] . '" >
			</div>
		     </div>
              </div>' . "</td>\n";
            echo '
			   	<td>
		 			<input class="gazie-tooltip" data-type="product-thumb" data-id="' . $v["codart"] . '" data-title="' . $v['annota'] . '" type="text" name="rows[' . $k . '][descri]" value="' . $descrizione . '" maxlength="100" />
			   	</td>
			    <td>
					<input class="gazie-tooltip" data-type="weight" data-id="' . $peso . '" data-title="' . $script_transl['weight'] . '" type="text" name="rows[' . $k . '][unimis]" value="' . $v['unimis'] . '" maxlength="3" />
				</td>
				<td>
					<input class="gazie-tooltip" data-type="weight" data-id="' . $peso . '" data-title="' . $script_transl['weight'] . '" type="text" name="rows[' . $k . '][quanti]" value="' . $v['quanti'] . '" align="right" maxlength="11" id="righi_' . $k . '_quanti" onchange="document.broven.last_focus.value=\'righi_' . $k . '_prelis\'; this.form.hidden_req.value=\'ROW\'; this.form.submit();" />
                </td>';
            echo "<td><input type=\"text\" name=\"rows[$k][prelis]\" value=\"" . $v['prelis'] . "\" align=\"right\" maxlength=\"11\" ";
			if ($vp>0) { // solo se scelto in configurazione avanzata azienda si vedrà il dialog per mettere il prezzo iva compresa
				echo ' onclick="vatPrice(\''.$k.'\',\''.$v['pervat'].'\');"';
			}
            echo " id=\"righi_" . $k . "_prelis\" onchange=\"document.broven.last_focus.value='righi_" . $k . "_sconto'; this.form.submit()\" /></td>\n";
            echo "<td><input type=\"text\" name=\"rows[$k][sconto]\" value=\"" . $v['sconto'] . "\" maxlength=\"6\"  id=\"righi_" . $k . "_sconto\" onchange=\"document.broven.last_focus.value=this.id; this.form.submit()\" /></td>\n";
            echo "<td><input type=\"text\" name=\"rows[$k][provvigione]\" value=\"" . $v['provvigione'] . "\" maxlength=\"6\"  /></td>\n";
            echo "<td class=\"text-right\">" . gaz_format_number($imprig) . "</td>\n";
            echo "<td class=\"text-right\">" . $v['pervat'] . "%</td>\n";
            echo "<td class=\"text-right codricTooltip\" title=\"Contropartita\">" . $v['codric'] . "</td>\n";

            $last_row[] = array_unshift($last_row, '<strong>' . $v['codart'] . '</strong>, ' . $v['descri'] . ', ' . $v['quanti'] . $v['unimis'] . ', <strong>' . $script_transl[23] . '</strong>: ' . gaz_format_number($v['prelis']) . ', %<strong>' . substr($script_transl[24], 0, 2) . '</strong>: ' . gaz_format_number($v['sconto']) . ', <strong>' . $script_transl[25] . '</strong>: ' . gaz_format_number($imprig) . ', <strong>' . $script_transl[19] . '</strong>: ' . $v['pervat'] . '%, <strong>' . $script_transl[18] . '</strong>: ' . $v['codric']);
            break;
        case "51":
			echo "<td><button type=\"image\" name=\"upper_row[" . $k . "]\" class=\"btn btn-default btn-sm\" title=\"" . $script_transl['3'] . "!\"><i class=\"glyphicon glyphicon-arrow-up\"></i></button></td>";
            echo "<td title=\"" . $script_transl['update'] . $script_transl['thisrow'] . "!\"><input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[{$k}]\" value=\"* documento allegato *\" />\n";
                if (empty($form['rows'][$k]['extdoc'])) {
                    echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#extdoc_dialog' . $k . '">'
                    . $script_transl['insert'] . ' documento esterno <i class="glyphicon glyphicon-tag"></i>'
                    . '</button></div>';
                } else {
                    echo '<div>documento esterno:<button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#extdoc_dialog' . $k . '">'
                    . $form['rows'][$k]['extdoc'] . ' <i class="glyphicon glyphicon-tag"></i>'
                    . '</button></div>';
                }
				echo '<div id="extdoc_dialog' . $k . '" class="collapse" >
                        <div class="form-group">
                          <div>';

                echo '<input type="file" onchange="this.form.submit();" name="docfile_' . $k . '">
                            <label>File: </label><input type="text" name="rows[' . $k . '][extdoc]" value="' . $form['rows'][$k]['extdoc'] . '" >
			</div>
		     </div>
              </div>' . "</td>\n";
            echo "<td><input type=\"text\" name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"100\"  /></td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" /></td>\n";
            echo "<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>\n";
            echo "<td></td>\n";
            echo "<td></td>\n";
            echo "<td></td>\n";
            echo "<td></td>\n";
            $last_row[] = array_unshift($last_row, $script_transl['typerow'][$v['tiprig']]);
            break;
        case "210":  // serve per gli articoli composti contattare andrea
            if ( $show_artico_composit['val']=="1" && $tipo_composti['val']=="KIT") {
                if ($v['scorta'] < 0) {
                    //$scorta_col = 'FacetDataTDsmallRed';
                    $btn_class = 'btn-danger';
                } else {
                    //$scorta_col = 'FacetDataTDsmall';
                    $btn_class = 'btn-default';
                }
                echo '	<td></td>
                                    <td title="' . $script_transl['update'] . $script_transl['thisrow'] . '!' . $btn_title . '">
                        <button name="upd_row[' . $k . ']" class="btn btn-xs ' . $btn_class . ' btn-block" type="submit">
                            <i class="glyphicon glyphicon-refresh"></i>&nbsp;' . $v['codart'] . '
                        </button>
                    </td>
                    <td>
                        <input class="gazie-tooltip" data-type="product-thumb" data-id="' . $v["codart"] . '" data-title="' . $v['annota'] . '" type="text" name="rows[' . $k . '][descri]" value="' . $descrizione . '" maxlength="100" />
                    </td>
                    <td>
                        <input class="gazie-tooltip" data-type="weight" data-id="' . $peso . '" data-title="' . $script_transl['weight'] . '" type="text" name="rows[' . $k . '][unimis]" value="' . $v['unimis'] . '" maxlength="3" />
                    </td>
                    <td>
                        <input class="gazie-tooltip" data-type="weight" data-id="' . $peso . '" data-title="' . $script_transl['weight'] . '" type="text" name="rows[' . $k . '][quanti]" value="' . $v['quanti'] . '" align="right" maxlength="11" id="righi_' . $k . '_quanti" onchange="document.broven.last_focus.value=this.id; this.form.hidden_req.value=\'ROW\'; this.form.submit();" />
                    </td>';
                echo "<td></td>\n";
                echo "<td></td>\n";
                echo "<td></td>\n";
                echo "<td class=\"text-right\"></td>\n";
                echo "<td class=\"text-right\"></td>\n";
                echo "<td class=\"text-right\"></td>\n";
                $last_row[] = array_unshift($last_row, '<strong>' . $v['codart'] . '</strong>, ' . $v['descri'] . ', ' . $v['quanti'] . $v['unimis'] . ', <strong>' . $script_transl[23] . '</strong>: ' . gaz_format_number($v['prelis']) . ', %<strong>' . substr($script_transl[24], 0, 2) . '</strong>: ' . gaz_format_number($v['sconto']) . ', <strong>' . $script_transl[25] . '</strong>: ' . gaz_format_number($imprig) . ', <strong>' . $script_transl[19] . '</strong>: ' . $v['pervat'] . '%, <strong>' . $script_transl[18] . '</strong>: ' . $v['codric']);
            } else {
                echo "<input type=\"hidden\" name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"20\"  />
                    <input type=\"hidden\" class=\"gazie-tooltip\" data-type=\"weight\" data-id=\"" . $peso . "\" data-title=\"" . $script_transl["weight"] . "\" type=\"text\" name=\"rows[" . $k . "][unimis]\" value=\"" . $v["unimis"] . "\" maxlength=\"3\"  />
                    <input type=\"hidden\" class=\"gazie-tooltip\" data-type=\"weight\" data-id=\"" . $peso . "\" data-title=\"" . $script_transl['weight'] . "\" type=\"text\" name=\"rows[" . $k . "][quanti]\" value=\"" . $v["quanti"] . "\" align=\"right\" maxlength=\"11\"  id=\"righi_" . $k . "_quanti\" onchange=\"document.broven.last_focus.value=\"righi_" . $k . "_prelis\"; this.form.hidden_req.value=\"ROW\"; this.form.submit();\" />
                    <input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" />
                    <input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" />
                    <input type=\"hidden\" name=\"rows[$k][provvigione]\" value=\"\" />";
            }
            break;
    }
    if ( $v['tiprig']!="210" ) {
        echo '<td class="text-right">
		    <button type="submit" class="btn btn-default btn-xs" name="del[' . $k . ']" title="' . $script_transl['delete'] . $script_transl['thisrow'] . '"><i class="glyphicon glyphicon-remove"></i></button>
		    </td>';
    }
    echo "</tr>";
}

if ($ultimoprezzo<>'') {
    $msgtoast = $upd_mm->toast(" <strong>Ultime vendite:</strong>".$ultimoprezzo, 'alert-last-row', 'alert-success');
}

/* Nuovo alert per scontistica, da visualizzare rigorosamente dopo l'ultima riga inserita */
if (count($form['rows']) > 0) {
    $msgtoast = $upd_mm->toast($msgtoast);  //lo mostriamo

    if (isset($_POST['in_submit']) && count($form['rows']) > 5) {
        /* for($i=0;$i<3;$i++) {	//	Predisposizione per mostrare gli ultimi n articoli inseriti (in ordine inverso ovviamente)
          $msgtoast .= $last_row[$i].'<br />';
          } */
        //$msgtoast .= $last_row[0];
        $msgtoast = $upd_mm->toast($script_transl['last_row'] . ': ' . $last_row[0], 'alert-last-row', 'alert-success');  //lo mostriamo
    }
} else {
    echo '<tr id="alert-zerorows">
			<td colspan="12" class="alert alert-danger">' . $script_transl['zero_rows'] . '</td>
		  </tr>';
}

echo '</tbody></table></div>';

echo '<div class="FacetSeparatorTD" align="center">' . $script_transl[1] . '</div>
	  <input type="hidden" value="' . $form['in_descri'] . '" name="in_descri" />
	  <input type="hidden" value="' . $form['in_pervat'] . '" name="in_pervat" />
	  <input type="hidden" value="' . $form['in_tipiva'] . '" name="in_tipiva" />
	  <input type="hidden" value="' . $form['in_ritenuta'] . '" name="in_ritenuta" />
      <input type="hidden" value="' . $form['in_unimis'] . '" name="in_unimis" />
	  <input type="hidden" value="' . $form['in_prelis'] . '" name="in_prelis" />
	  <input type="hidden" value="' . $form['in_id_mag'] . '" name="in_id_mag" />
	  <input type="hidden" value="' . $form['in_id_doc'] . '" name="in_id_doc" />
	  <input type="hidden" value="' . $form['in_annota'] . '" name="in_annota" />
	  <input type="hidden" value="' . $form['in_scorta'] . '" name="in_scorta" />
	  <input type="hidden" value="' . $form['in_quamag'] . '" name="in_quamag" />
	  <input type="hidden" value="' . $form['in_pesosp'] . '" name="in_pesosp" />
	  <input type="hidden" value="' . $form['in_extdoc'] . '" name="in_extdoc" />
	  <input type="hidden" value="' . $form['in_status'] . '" name="in_status" />
	  <input type="hidden" value="' . $form['hidden_req'] . '" name="hidden_req" />
	  <div class="table-responsive"><table class="Tlarge table table-striped table-bordered table-condensed">
	  	<tr>
			<td class="FacetColumnTD">' . $script_transl[17] . ':';
/** ENRICO FEDELE */
$gForm->selTypeRow('in_tiprig', $form['in_tiprig']);
echo $script_transl[15] . ':&nbsp;';
$select_artico = new selectartico("in_codart");
$select_artico->addSelected($form['in_codart']);
//$select_artico->output($form['cosear'], $form['in_artsea']);
$select_artico->output($form['cosear']);

/** ENRICO FEDELE */
/* glyph-icon */
?>
			</td>
			<td class="FacetColumnTD">
				<?php echo $script_transl[16] ?>:&nbsp;<input type="text" value="<?php echo $form['in_quanti'] ?>" maxlength="11" name="in_quanti" tabindex="5" accesskey="q" />
			</td>
			<td class="FacetColumnTD" align="right">
<?php
if (substr($form['in_status'], 0, 6) != "UPDROW") { //se non è un rigo da modificare
?>
			<button type="submit" class="btn btn-default btn-sm" name="in_submit_desc" title="Aggiungi rigo Descrittivo"><i class="glyphicon glyphicon-pencil"></i></button>
			<button type="submit" class="btn btn-default btn-sm" name="in_submit_text" title="Aggiungi rigo Testo"><i class="glyphicon glyphicon-list"></i></button>
			<span>||</span>
<?php
}
?>
				<button type="submit" class="btn btn-default btn-xs" name="in_submit" title="<?php echo $script_transl['submit'] . $script_transl['thisrow'] ?>" tabindex="6">
					<i class="glyphicon glyphicon-ok"></i>
				</button>
			</td>
		</tr>
		<tr>
			<td class="FacetColumnTD">
<?php
echo $script_transl[18] . ": ";
$select_codric = new selectconven("in_codric");
$select_codric->addSelected($form['in_codric']);
$select_codric->output(substr($form['in_codric'], 0, 1));
echo '			%' . $script_transl[24] . ': <input type="text" value="' . $form['in_sconto'] . '" maxlength="4" name="in_sconto">
	  			 %' . $script_transl[56] . ': <input type="text" value="' . $form['in_provvigione'] . '" maxlength="6" name="in_provvigione">'
 . ' %' . $script_transl['ritenuta'] . ': <input type="text" value="' . $form['in_ritenuta'] . '" maxlength="6" name="in_ritenuta">
	   			</td>
				<td class="FacetColumnTD">' . $script_transl['vat_constrain'];
$select_in_codvat = new selectaliiva("in_codvat");
$select_in_codvat->addSelected($form['in_codvat']);
$select_in_codvat->output();
echo '</td><td class="FacetColumnTD"></td></tr></table></div>';


echo '<div class="FacetSeparatorTD text-center">' . $script_transl[2] . '</div><div>
		<table class="Tlarge table table-striped table-bordered table-condensed">
			<input type="hidden" value="' . $form['numrat'] . '" name="numrat" />
			<input type="hidden" value="' . $form['expense_vat'] . '" name="expense_vat" />
			<input type="hidden" value="' . $form['spevar'] . '" name="spevar" />
			<input type="hidden" value="' . $form['stamp'] . '" name="stamp" />
			<input type="hidden" value="' . $form['round_stamp'] . '" name="round_stamp" />
			<input type="hidden" value="' . $form['cauven'] . '" name="cauven" />
			<input type="hidden" value="' . $form['caucon'] . '" name="caucon" />
			<input type="hidden" value="' . $form['caumag'] . '" name="caumag" />';

$somma_spese = $form['traspo'] + $form['speban'] * $form['numrat'] + $form['spevar'];
$calc = new Compute;
$calc->add_value_to_VAT_castle($castle, $somma_spese, $form['expense_vat']);
if ($calc->total_exc_with_duty > $admin_aziend['taxstamp_limit'] && $form['virtual_taxstamp'] > 0) {
    $form['taxstamp'] = $admin_aziend['taxstamp'];
}

echo "	<tr>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[26]</td>
			<td class=\"FacetDataTD\">
				<input type=\"text\" name=\"imball\" value=\"" . $form['imball'] . "\" maxlength=\"50\"  class=\"FacetInput\">\n";
$select_spediz = new SelectValue("imballo");
$select_spediz->output('imball', 'imball');
echo "		</td>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[27]</td>
			<td class=\"FacetDataTD\">
				<input type=\"text\" name=\"spediz\" value=\"" . $form["spediz"] . "\" maxlength=\"50\"  class=\"FacetInput\">\n";
$select_spediz = new SelectValue("spedizione");
$select_spediz->output('spediz', 'spediz');
/** ENRICO FEDELE */
/* td non chiuso */
echo "		</td>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[14]</td>
			<td class=\"FacetDataTD\">\n";
$select_vettor = new selectvettor("vettor");
$select_vettor->addSelected($form["vettor"]);
$select_vettor->output();
echo "		</td>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[29]</td>
			<td class=\"FacetDataTD\">
				<input type=\"text\" name=\"portos\" value=\"" . $form["portos"] . "\" maxlength=\"50\"  class=\"FacetInput\">\n";
$select_spediz = new SelectValue("portoresa");
$select_spediz->output('portos', 'portos');

echo "			</td>
			</tr>
			<tr>
				<td class=\"FacetFieldCaptionTD text-right\">$script_transl[28] " . $admin_aziend['html_symbol'] . "</td>
				<td class=\"FacetDataTD\">
					<input type=\"text\" value=\"" . $form['traspo'] . "\" name=\"traspo\" maxlength=\"6\"  onchange=\"this.form.submit()\" />
				</td>
				<td class=\"FacetFieldCaptionTD text-right\">$script_transl[30]</td>
				<td class=\"FacetDataTD\">
					<input class=\"FacetText\" type=\"text\" name=\"giotra\" VALUE=\"" . $form['giotra'] . "\" >
					<input class=\"FacetText\" type=\"text\" name=\"mestra\" VALUE=\"" . $form['mestra'] . "\" >
					<input class=\"FacetText\" type=\"text\" name=\"anntra\" VALUE=\"" . $form['anntra'] . "\" >
					<a href=\"#\" onClick=\"cal.showCalendar('anchor','" . $form['mestra'] . "/" . $form['giotra'] . "/" . $form['anntra'] . "'); return false;\" title=\" cambia la data! \" name=\"anchor\" id=\"anchor\" class=\"btn btn-default btn-xs\">\n";
//echo "<img border=\"0\" src=\"../../library/images/cal.png\"></A>$script_transl[31]";
echo '<i class="glyphicon glyphicon-calendar"></i></a>' . $script_transl[31];
// select dell'ora
echo "\t <select name=\"oratra\" class=\"FacetText\" >\n";
for ($counter = 0; $counter <= 23; $counter++) {
    $selected = "";
    if ($counter == $form['oratra']) {
        $selected = 'selected=""';
    }
    echo "\t\t <option value=\"" . sprintf('%02d', $counter) . "\" $selected >" . sprintf('%02d', $counter) . "</option>\n";
}
echo "\t </select>\n ";
// select dell'ora
echo "\t <select name=\"mintra\" class=\"FacetText\" >\n";
for ($counter = 0; $counter <= 59; $counter++) {
    $selected = "";
    if ($counter == $form['mintra']) {
        $selected = ' selected=""';
    }
    echo "\t\t <option value=\"" . sprintf('%02d', $counter) . "\"$selected >" . sprintf('%02d', $counter) . "</option>\n";
}
echo "			</select>
			</td>
			<td class=\"FacetFieldCaptionTD text-right\">" . $script_transl[51] . "</td>
			<td class=\"FacetDataTD\">
				<select name=\"caumag\" class=\"FacetSelect\">\n";
$result = gaz_dbi_dyn_query("*", $gTables['caumag'], " clifor = -1 AND operat = " . $docOperat[$form['tipdoc']], "codice, descri");
while ($row = gaz_dbi_fetch_array($result)) {
    $selected = "";
    if ($form["caumag"] == $row['codice']) {
        $selected = ' selected=""';
    }
    echo "				<option value=\"" . $row['codice'] . "\"" . $selected . ">" . $row['codice'] . "-" . substr($row['descri'], 0, 20) . "</option>\n";
}
/** ENRICO FEDELE */
/* td non chiuso */
echo "			</select>
			</td>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[55]</td>
			<td class=\"FacetDataTD\">
				<input type=\"text\" value=\"" . $form['volume'] . "\" name=\"volume\" maxlength=\"9\"  />
			</td>
		</tr>
		<tr>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[52]</td>
			<td class=\"FacetDataTD\">
				<input type=\"text\" value=\"" . $form['net_weight'] . "\" name=\"net_weight\" maxlength=\"9\"  />
			</td>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[53]</td>
			<td class=\"FacetDataTD\">
				<input type=\"text\" value=\"" . $form['gross_weight'] . "\" name=\"gross_weight\" maxlength=\"9\"  />
			</td>
			<td class=\"FacetFieldCaptionTD text-right\">$script_transl[54]</td>
			<td class=\"FacetDataTD\">
				<input type=\"text\" value=\"" . $form['units'] . "\" name=\"units\" maxlength=\"6\"  />
			</td>
			<td class=\"FacetFieldCaptionTD\" colspan=\"2\">
				" . $script_transl['taxstamp'] . "<input type=\"text\" value=\"" . $form['taxstamp'] . "\" name=\"taxstamp\" maxlength=\"6\"  /> " . $script_transl['virtual_taxstamp'];
$gForm->variousSelect('virtual_taxstamp', $script_transl['virtual_taxstamp_value'], $form['virtual_taxstamp']);
echo '			</td>
			</tr>
			<tr>
				<td class="FacetFieldCaptionTD text-right">' . $script_transl[32] . '</td>
				<td class="FacetFieldCaptionTD text-right">' . $script_transl[33] . '</td>
				<td class="FacetFieldCaptionTD text-right">' . $script_transl[34] . '</td>
				<td class="FacetFieldCaptionTD text-right">
					%' . $script_transl[24] . '<input type="text" name="sconto" value="' . $form["sconto"] . '" maxlength="6" onchange="this.form.submit()">
				</td>
				<td class="FacetFieldCaptionTD text-right">' . $script_transl[32] . '</td>
				<td class="FacetFieldCaptionTD text-right">' . $script_transl[19] . '</td>
				<td class="FacetFieldCaptionTD text-right">' . $script_transl['stamp'] . '</td>
				<td class="FacetFieldCaptionTD text-right">' . $admin_aziend['html_symbol'] . $script_transl[36] . '</td>
			</tr>';

foreach ($calc->castle as $k => $v) {
    echo '		<tr>
   					<td class="text-right">' . gaz_format_number($v['impcast']) . '</td>
					<td class="text-right">' . $v['descriz'] . ' ' . gaz_format_number($v['ivacast']) . '</td>
					<td colspan="6"></td>
				</tr>';
}

if ($next_row > 0) {
    if ($form['stamp'] > 0) {
        $calc->payment_taxstamp($calc->total_imp + $calc->total_vat + $carry - $rit + $form['taxstamp'], $form['stamp'], $form['round_stamp'] * $form['numrat']);
        $stamp = $calc->pay_taxstamp;
    } else {
        $stamp = 0;
    }
    echo '		<tr>
   					<td colspan="2"></td>
   					<td class="text-right">' . gaz_format_number($totimp_body) . '</td>
   					<td class="text-right">' . gaz_format_number(($totimp_body - $totimpfat + $somma_spese), 2, '.', '') . '</td>
					<td class="text-right">' . gaz_format_number($calc->total_imp) . '</td>
					<td class="text-right">' . gaz_format_number($calc->total_vat) . '</td>
					<td class="text-right">' . gaz_format_number($stamp) . '</td>
					<td name="totale" class="text-right" style="font-weight:bold;">' . gaz_format_number($calc->total_imp + $calc->total_vat + $stamp + $form['taxstamp']) . '</td>
				</tr>';

    if ($rit >= 0.01) {
        echo '		<tr>
	  					<td colspan="7" class="text-right">' . $script_transl['ritenuta'] . '</td>
						<td class="text-right">' . gaz_format_number($rit) . '</td>
					</tr>
					<tr>
						<td colspan="7" class="text-right">' . $script_transl['netpay'] . '</td>
						<td class="text-right">' . gaz_format_number($totimpfat + $totivafat + $stamp - $rit + $form['taxstamp']) . '</td>
					</tr>';
    }
    echo '		<tr>
					<td colspan="2" class="text-right">
						<input name="prestampa" class="btn btn-default" onClick="preStampa();" type="button" value="Prestampa">
					</td>
					<td colspan="4" class="text-center">
						<input name="ins" class="btn '.$class_btn_confirm.'" id="preventDuplicate" onClick="chkSubmit();" type="submit" value="' . ucfirst($script_transl[$toDo]) . '">
					</td>
				';
}
if ($toDo == 'update' and $form['tipdoc'] == 'VPR') {
    echo '<td colspan="2"><input type="submit" class="btn btn-default" accesskey="o" name="ord" value="Genera ordine" /></td></tr>';
}else{
	echo '<td colspan="2"></td>';
}
echo "</tr>	</table></div>";
?>
</form>
<div class="modal" id="vat-price" title="IMPORTO IVA COMPRESA">
	<input type="text" id="cat_prevat" style="text-align: right;" maxlength="11" onkeyup="vatPriceCalc();" />
	<br /><br />
	<!--select id="codvat" name="cat_codvat" class="FacetSelect"></select-->
	<input type="text" id="cat_pervat" style="text-align: center;" maxlength="5" disabled="disabled" />
	<br /><br />
	<input type="text" id="cat_prelis" style="text-align: right;" maxlength="11" disabled="disabled" />
</div>
<script type="text/javascript">
	//var $options = $("#in_codvat > option").clone();
	//$('#cat_codvat').append($options);
	function vatPrice(row,pervat) {
		var prelis = $("[name='rows["+row+"][prelis]']").val();
		var prevat = parseFloat(prelis)*(1+parseFloat(pervat)/100);
    $("#cat_prevat").val(prevat.toFixed(<?php echo $admin_aziend['decimal_price'] ?>));
		$("#cat_pervat").val(pervat);
		$("#cat_prelis").val(prelis);
		$("#vat-price").dialog({
			modal: true,
			buttons: {
				Ok: function() {
					$("[name='rows["+row+"][prelis]']").val($("#cat_prelis").val());
					document.broven.last_focus.value="righi_" + row + "_sconto";
					$("[name='rows["+row+"][prelis]']").parents("form:first").submit();
					$(this).dialog("close");
				}
			}
		});
	};
	function vatPriceCalc() {
		var prevat = $("#cat_prevat").val();
		var pervat = $("#cat_pervat").val();
		if (prevat!="" && pervat!="") {
			var prelis = parseFloat(prevat)/(1+parseFloat(pervat)/100);
			$("#cat_prelis").val(prelis.toFixed(<?php echo $admin_aziend['decimal_price'] ?>));
		} else {
			$("#cat_prelis").val("0");
		}
	}
</script>
<script language="JavaScript">
var last_focus_value;
var last_focus;
last_focus_value = document.broven.last_focus.value;
if (last_focus_value != "") {
    last_focus = document.getElementById(last_focus_value);
    if (last_focus != undefined) {
        last_focus.focus();
}
}
last_focus_value = "";

$( document ).ready(function() {
	$(".codricTooltip").each(function(index){$(this).attr('title', $("#in_codric option[value='"+$( this ).text().trim()+"']").text());});
});

</script>
<?php
require("../../library/include/footer.php");
?>