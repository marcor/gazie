<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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
$msg = "";

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and ! isset($_GET['id_tes'])) and ! isset($_GET['tipdoc'])) {
    header("Location: " . $form['ritorno']);
    exit;
}

if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or ( isset($_POST['Update']))) {   //se non e' il primo accesso
//qui si dovrebbe fare un parsing di quanto arriva dal browser...
    $form['id_tes'] = intval($_POST['id_tes']);
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner(intval($_POST['clfoco']));
    $form['hidden_req'] = $_POST['hidden_req'];
// ...e della testata
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }
    $form['seziva'] = $_POST['seziva'];
    $form['tipdoc'] = $_POST['tipdoc'];
    $form['gioemi'] = $_POST['gioemi'];
    $form['mesemi'] = $_POST['mesemi'];
    $form['annemi'] = $_POST['annemi'];
    $form['gioreg'] = $_POST['gioreg'];
    $form['mesreg'] = $_POST['mesreg'];
    $form['annreg'] = $_POST['annreg'];
    $form['protoc'] = $_POST['protoc'];
    $form['numdoc'] = $_POST['numdoc'];
    if (isset($_POST['numfat']))
        $form['numfat'] = $_POST['numfat'];
    $form['datfat'] = $_POST['datfat'];
    $form['clfoco'] = $_POST['clfoco'];
//tutti i controlli su  tipo di pagamento e rate
    $form['speban'] = $_POST['speban'];
    $form['numrat'] = $_POST['numrat'];
    $form['pagame'] = $_POST['pagame'];
    $form['change_pag'] = $_POST['change_pag'];
    if ($form['change_pag'] != $form['pagame']) {  //se è stato cambiato il pagamento
        $new_pag = gaz_dbi_get_row($gTables['pagame'], "codice", $form['pagame']);
        $old_pag = gaz_dbi_get_row($gTables['pagame'], "codice", $form['change_pag']);
        if (($new_pag['tippag'] == 'B' or $new_pag['tippag'] == 'T' or $new_pag['tippag'] == 'V')
                and ( $old_pag['tippag'] == 'C' or $old_pag['tippag'] == 'D')) { // se adesso devo mettere le spese e prima no
            $form['numrat'] = $new_pag['numrat'];
            if ($toDo == 'update') {  //se è una modifica mi baso sulle vecchie spese
                $old_header = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", $form['id_tes']);
                if ($old_header['speban'] > 0 and $fornitore['speban'] == "S") {
                    $form['speban'] = 0;
                } elseif ($old_header['speban'] == 0 and $fornitore['speban'] == "S") {
                    $form['speban'] = 0;
                } else {
                    $form['speban'] = 0.00;
                }
            } else { //altrimenti mi avvalgo delle nuove dell'azienda
                $form['speban'] = 0;
            }
        } elseif (($new_pag['tippag'] == 'C' or $new_pag['tippag'] == 'D')
                and ( $old_pag['tippag'] == 'B' or $old_pag['tippag'] == 'T' or $old_pag['tippag'] == 'V')) { // se devo togliere le spese
            $form['speban'] = 0.00;
            $form['numrat'] = 1;
        }
        $form['pagame'] = $_POST['pagame'];
        $form['change_pag'] = $_POST['pagame'];
    }
    $form['banapp'] = $_POST['banapp'];
    $form['traspo'] = $_POST['traspo'];
    $form['spevar'] = $_POST['spevar'];
    $form['ivaspe'] = $_POST['ivaspe'];
    $form['pervat'] = $_POST['pervat'];
    $form['cauven'] = $_POST['cauven'];
    $form['caucon'] = $_POST['caucon'];
    $form['id_pro'] = $_POST['id_pro'];
// Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
        $sezione = $form['seziva'];
        $datfat = $form['annemi'] . "-" . $form['mesemi'] . "-" . $form['gioemi'];
        $utsemi = mktime(0, 0, 0, $form['mesemi'], $form['gioemi'], $form['annemi']);
        $initra = $form['annreg'] . "-" . $form['mesreg'] . "-" . $form['gioreg'];
        $utstra = mktime(0, 0, 0, $form['mesreg'], $form['gioreg'], $form['annreg']);
        if ($form['tipdoc'] == 'DDR' or $form['tipdoc'] == 'DDL') {  //se è un DDT vs Fattura differita
            if ($utstra < $utsemi) {
                $msg .= "38+";
            }
            if (!checkdate($form['mesreg'], $form['gioreg'], $form['annreg'])) {
                $msg .= "37+";
            }
        } else {
            if ($utstra > $utsemi) {
                $msg .= "53+";
            }
            if (!checkdate($form['mesreg'], $form['gioreg'], $form['annreg'])) {
                $msg .= "54+";
            }
            if (empty($form['numfat'])) {
                $msg .= "55+";
            }
        }
        if (!isset($_POST['rows'])) {
            $msg .= "39+";
        }
// --- inizio controllo coerenza date-numerazione
        if ($toDo == 'update') {  // controlli in caso di modifica
            if ($form['tipdoc'] == 'DDR' or $form['tipdoc'] == 'DDL') {  //se è un DDT vs Fattura differita
                $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = " . $form['annemi'] . " and datfat < '$datfat' and ( tipdoc like 'DD_' or tipdoc = 'FAD') and seziva = $sezione", "numdoc desc", 0, 1);
                $result = gaz_dbi_fetch_array($rs_query); //giorni precedenti
                if ($result and ( $form['numdoc'] < $result['numdoc'])) {
                    $msg .= "40+";
                }
                $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = " . $form['annemi'] . " and datfat > '$datfat' and ( tipdoc like 'DD_' or tipdoc = 'FAD') and seziva = $sezione", "numdoc asc", 0, 1);
                $result = gaz_dbi_fetch_array($rs_query); //giorni successivi
                if ($result and ( $form['numdoc'] > $result['numdoc'])) {
                    $msg .= "41+";
                }
            } elseif ($form['tipdoc'] == 'ADT') { //se è un DDT acquisto non faccio controlli
            } else { //se sono altri documenti
                $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = " . $form['annemi'] . " and datfat < '$datfat' and tipdoc like '" . substr($form['tipdoc'], 0, 1) . "__' and seziva = $sezione", "protoc desc", 0, 1);
                $result = gaz_dbi_fetch_array($rs_query); //giorni precedenti
                if ($result && ($form['protoc'] < $result['protoc'])) {
                    $msg .= "42+";
                }
                $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = " . $form['annemi'] . " and datfat > '$datfat' and tipdoc like '" . substr($form['tipdoc'], 0, 1) . "__' and seziva = $sezione", "protoc asc", 0, 1);
                $result = gaz_dbi_fetch_array($rs_query); //giorni successivi
                if ($result && ($form['protoc'] > $result['protoc'])) {
                    $msg .= "43+";
                }
            }
        } else {    //controlli in caso di inserimento
            if ($form['tipdoc'] == 'DDR' or $form['tipdoc'] == 'DDL') {  //se è un DDT
                $rs_ultimo_ddt = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = " . $form['annemi'] . " and tipdoc like 'DD_' and seziva = $sezione", "numdoc desc, datfat desc", 0, 1);
                $ultimo_ddt = gaz_dbi_fetch_array($rs_ultimo_ddt);
                $utsUltimoDdT = mktime(0, 0, 0, substr($ultimo_ddt['datfat'], 5, 2), substr($ultimo_ddt['datfat'], 8, 2), substr($ultimo_ddt['datfat'], 0, 4));
                if ($ultimo_ddt and ( $utsUltimoDdT > $utsemi)) {
                    $msg .= "44+";
                }
            } else { //se sono altri documenti
                $rs_ultimo_tipo = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = " . $form['annemi'] . " and tipdoc like '" . substr($form['tipdoc'], 0, 1) . "%' and seziva = $sezione", "protoc desc, datfat desc, datfat desc", 0, 1);
                $ultimo_tipo = gaz_dbi_fetch_array($rs_ultimo_tipo);
                $utsUltimoProtocollo = mktime(0, 0, 0, substr($ultimo_tipo['datfat'], 5, 2), substr($ultimo_tipo['datfat'], 8, 2), substr($ultimo_tipo['datfat'], 0, 4));
                if ($ultimo_tipo and ( $utsUltimoProtocollo > $utsemi)) {
                    $msg .= "45+";
                }
            }
        }
// --- fine controllo coerenza date-numeri
        if (!checkdate($form['mesemi'], $form['gioemi'], $form['annemi']))
            $msg .= "46+";
        if (empty($form["clfoco"]))
            $msg .= "47+";
        if (empty($form["pagame"]))
            $msg .= "48+";
//controllo che i righi non abbiano descrizioni  e unita' di misura vuote in presenza di quantita diverse da 0
        foreach ($form['rows'] as $i => $value) {
            if ($value['descri'] == '' &&
                    $value['quanti']) {
                $msgrigo = $i + 1;
                $msg .= "49+";
            }
            if ($value['unimis'] == '' &&
                    $value['quanti'] &&
                    $value['tiprig'] == 0) {
                $msgrigo = $i + 1;
                $msg .= "50+";
            }
        }
        if ($msg == "") {// nessun errore
            if (preg_match("/^id_([0-9]+)$/", $form['clfoco'], $match)) {
                $new_clfoco = $anagrafica->getPartnerData($match[1], 1);
                $form['clfoco'] = $anagrafica->anagra_to_clfoco($new_clfoco, $admin_aziend['masfor']);
            }

            function getProtocol($type, $year, $sezione) {  // questa funzione trova l'ultimo numero di protocollo                                           // controllando sia l'archivio documenti che il
                global $gTables;                      // registro IVA acquisti
                $rs_ultimo_tesdoc = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = $year AND tipdoc LIKE '" . substr($type, 0, 2) . "_' AND seziva = $sezione", "protoc DESC", 0, 1);
                $ultimo_tesdoc = gaz_dbi_fetch_array($rs_ultimo_tesdoc);
                $rs_ultimo_tesmov = gaz_dbi_dyn_query("*", $gTables['tesmov'], "YEAR(datreg) = $year AND regiva = 6 AND seziva = $sezione", "protoc DESC", 0, 1);
                $ultimo_tesmov = gaz_dbi_fetch_array($rs_ultimo_tesmov);
                $lastProtocol = 0;
                if ($ultimo_tesdoc) {
                    $lastProtocol = $ultimo_tesdoc['protoc'];
                }
                if ($ultimo_tesmov) {
                    if ($ultimo_tesmov['protoc'] > $lastProtocol) {
                        $lastProtocol = $ultimo_tesmov['protoc'];
                    }
                }
                return $lastProtocol + 1;
            }

            $initra .= " " . $form['oratra'] . ":" . $form['mintra'] . ":00";
            $form['spediz'] = addslashes($form['spediz']);
            $form['portos'] = addslashes($form['portos']);
            $form['imball'] = addslashes($form['imball']);
            $form['destin'] = addslashes($form['destin']);
            if ($toDo == 'update') { // e' una modifica
                $old_rows = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = " . $form['id_tes'], "id_rig asc");
                $i = 0;
                $count = count($form['rows']) - 1;
                while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
                    if (substr($form['tipdoc'], 0, 2) <> 'DD') {
                        $form['numdoc'] = $form['numfat'];
                    }
                    if ($i <= $count) { //se il vecchio rigo e' ancora presente nel nuovo lo modifico
                        $form['rows'][$i]['id_tes'] = $form['id_tes'];
                        $codice = array('id_rig', $val_old_row['id_rig']);
                        rigdocUpdate($codice, $form['rows'][$i]);
                        if (isset($form["row_$i"]) && $val_old_row['id_body_text'] > 0) { //se è un rigo testo già presente lo modifico
                            bodytextUpdate(array('id_body', $val_old_row['id_body_text']), array('table_name_ref' => 'rigdoc', 'id_ref' => $val_old_row['id_rig'], 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                            gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $val_old_row['id_rig'], 'id_body_text', $val_old_row['id_body_text']);
                        } elseif (isset($form["row_$i"]) && $val_old_row['id_body_text'] == 0) { //prima era un rigo diverso da testo
                            bodytextInsert(array('table_name_ref' => 'rigdoc', 'id_ref' => $val_old_row['id_rig'], 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                            gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $val_old_row['id_rig'], 'id_body_text', gaz_dbi_last_id());
                        } elseif (!isset($form["row_$i"]) && $val_old_row['id_body_text'] > 0) { //un rigo che prima era testo adesso non lo è più
                            gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigdoc' AND id_ref", $val_old_row['id_rig']);
                        }
                        if ($form['rows'][$i]['id_mag'] > 0) {
// se il rigo ha un movimento di magazzino associato lo aggiorno
                            $upd_mm->uploadMag($val_old_row['id_rig'], $form['tipdoc'], $form['numdoc'], $form['seziva'], $datfat, $form['clfoco'], $form['sconto'], $form['caumag'], $form['rows'][$i]['codart'], $form['rows'][$i]['quanti'], $form['rows'][$i]['prelis'], $form['rows'][$i]['sconto'], $val_old_row['id_mag'], $admin_aziend['stock_eval_method'], false, $form['protoc']);
// aggiorno pure i documenti relativi ai lotti
                            $old_lm = gaz_dbi_get_row($gTables['lotmag'], 'id_rigdoc', $val_old_row['id_rig']);
                            if ($old_lm && substr($form['rows'][$i]['filename'], 0, 7) <> 'lotmag_') {
// se a questo rigo corrispondeva un certificato controllo che però è stato aggiornato lo cambio
                                $dh = opendir('../../data/files/' . $admin_aziend['company_id']);
                                while (false !== ($filename = readdir($dh))) {
                                    $fd = pathinfo($filename);
                                    if ($fd['filename'] == 'lotmag_' . $old_lm['id']) {
// cancello il file precedente indipendentemente dall'estensione
                                        $frep = glob('../../data/files/' . $admin_aziend['company_id'] . "/lotmag_" . $old_lm['id'] . ".*");
                                        foreach ($frep as $fdel) {// prima cancello eventuali precedenti file temporanei
                                            unlink($fdel);
                                        }
                                    }
                                }
                                $tmp_file = "../../data/files/tmp/" . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $i . '_' . $form['rows'][$i]['filename'];
// sposto e rinomino il relativo file temporaneo    
                                $fn = pathinfo($form['rows'][$i]['filename']);
                                rename($tmp_file, "../../data/files/" . $admin_aziend['company_id'] . "/lotmag_" . $old_lm['id'] . '.' . $fn['extension']);
                            }
                        }
                    } else { //altrimenti lo elimino
                        if ($val_old_row['id_mag'] > 0) {  //se c'è stato un movimento di magazzino lo azzero
                            $upd_mm->uploadMag('DEL', $form['tipdoc'], '', '', '', '', '', '', '', '', '', '', $val_old_row['id_mag'], $admin_aziend['stock_eval_method']);
                        }
                        gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $val_old_row['id_rig']);
                    }
                    $i++;
                }
//qualora i nuovi righi fossero di più dei vecchi inserisco l'eccedenza
                for ($i = $i; $i <= $count; $i++) {
                    $form['rows'][$i]['id_tes'] = $form['id_tes'];
                    rigdocInsert($form['rows'][$i]);
                    $last_rigdoc_id = gaz_dbi_last_id();
                    if ($admin_aziend['conmag'] == 2 &&
                            $form['rows'][$i]['tiprig'] == 0 &&
                            $form['rows'][$i]['gooser'] == 0 &&
                            !empty($form['rows'][$i]['codart'])) { //se l'impostazione in azienda prevede l'aggiornamento automatico dei movimenti di magazzino
                        $last_movmag_id = $upd_mm->uploadMag(gaz_dbi_last_id(), $form['tipdoc'], $form['numdoc'], $form['seziva'], $datfat, $form['clfoco'], $form['sconto'], $form['caumag'], $form['rows'][$i]['codart'], $form['rows'][$i]['quanti'], $form['rows'][$i]['prelis'], $form['rows'][$i]['sconto'], 0, $admin_aziend['stock_eval_method'], false, $form['protoc']
                        );
                    }
// se l'articolo prevede la gestione dei  lotti o della matricola/numero seriale creo un rigo in lotmag 
// ed eventualmente sposto e rinomino il relativo documento dalla dir temporanea a quella definitiva 
                    if ($form['rows'][$i]['lot_or_serial'] > 0) {
                        $form['rows'][$i]['id_rigdoc'] = $last_rigdoc_id;
                        $form['rows'][$i]['id_movmag'] = $last_movmag_id;
                        $form['rows'][$i]['expiry'] = gaz_format_date($form['rows'][$i]['expiry'], true);
                        if (empty($form['rows'][$i]['identifier'])) {
// creo un identificativo del lotto/matricola interno                            
                            $form['rows'][$i]['identifier'] = $form['datfat'] . '_' . $form['rows'][$i]['id_rigdoc'];
                        }
                        $last_lotmag_id = lotmagInsert($form['rows'][$i]);
// inserisco il rifermineto anche sul relativo movimento di magazzino
                        gaz_dbi_put_row($gTables['movmag'], 'id_mov', $last_movmag_id, 'id_lotmag', $last_lotmag_id);
                        if (!empty($form['rows'][$i]['filename'])) {
                            $tmp_file = "../../data/files/tmp/" . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $i . '_' . $form['rows'][$i]['filename'];
// sposto e rinomino il relativo file temporaneo    
                            $fd = pathinfo($form['rows'][$i]['filename']);
                            rename($tmp_file, "../../data/files/" . $admin_aziend['company_id'] . "/lotmag_" . $last_lotmag_id . '.' . $fd['extension']);
                        }
                    }
                }
//modifico la testata con i nuovi dati...
                $old_head = gaz_dbi_get_row($gTables['tesdoc'], 'id_tes', $form['id_tes']);
                if (substr($form['tipdoc'], 0, 2) == 'DD') { //se è un DDT non fatturato
                    $form['datfat'] = '';
                    $form['numfat'] = 0;
                } else {
                    $form['datfat'] = $initra;
                    $form['numdoc'] = $form['numfat']; // coincidono se il doc è emesso dal fornitore
                }
                $form['geneff'] = $old_head['geneff'];
                $form['id_contract'] = $old_head['id_contract'];
                $form['id_con'] = $old_head['id_con'];
                $form['status'] = $old_head['status'];
                $form['initra'] = $initra;
                $form['datfat'] = $datfat;
                $codice = array('id_tes', $form['id_tes']);
                tesdocUpdate($codice, $form);
                $prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'];
// prima di uscire cancello eventuali precedenti file temporanei
                foreach (glob("../../data/files/tmp/" . $prefix . "_*.*") as $fn) {
                    unlink($fn);
                }
                header("Location: " . $form['ritorno']);
                exit;
            } else { // e' un'inserimento
// ricavo i progressivi in base al tipo di documento
                $where = "numdoc desc";
                switch ($form['tipdoc']) {
                    case "DDR":
                        $sql_documento = "YEAR(datfat) = " . $form['annemi'] . " and ( tipdoc like 'DD_' or tipdoc = 'FAD') and seziva = $sezione";
                        break;
                    case "DDL":
                        $sql_documento = "YEAR(datfat) = " . $form['annemi'] . " and ( tipdoc like 'DD_' or tipdoc = 'FAD') and seziva = $sezione";
                        break;
                    case "AFA":
                        $sql_documento = "YEAR(datfat) = " . $form['annemi'] . " and tipdoc like 'AFA' and seziva = $sezione";
                        $where = "numfat desc";
                        break;
                    case "ADT":
                        $sql_documento = "YEAR(datfat) = " . $form['annemi'] . " and tipdoc like 'ADT' and seziva = $sezione";
                        break;
                    case "AFC":
                        $sql_documento = "YEAR(datfat) = " . $form['annemi'] . " and tipdoc = 'AFC' and seziva = $sezione";
                        $where = "numfat desc";
                        break;
                }
                $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $sql_documento, $where, 0, 1);
                $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
// se e' il primo documento dell'anno, resetto il contatore
                if ($ultimo_documento) {
                    $form['numdoc'] = $ultimo_documento['numdoc'] + 1;
                } else {
                    $form['numdoc'] = 1;
                }
                if (substr($form['tipdoc'], 0, 2) == 'DD') {  //ma se e' un ddt a fornitore il protocollo è 0 così come il numero e data fattura
                    $form['protoc'] = 0;
                    $form['numfat'] = 0;
                    $form['datfat'] = 0;
                } else { //in tutti gli altri casi si deve prendere quanto inserito nel form
                    $form['datfat'] = $initra;
                    $form['protoc'] = getProtocol($form['tipdoc'], $form['annemi'], $sezione);
                    $form['numdoc'] = $form['numfat'];
                }
//inserisco la testata
                $form['status'] = '';
                $form['initra'] = $initra;
                $form['datfat'] = $datfat;
                tesdocInsert($form);
//recupero l'id assegnato dall'inserimento
                $ultimo_id = gaz_dbi_last_id();
//inserisco i righi
                foreach ($form['rows'] as $i => $value) {
                    $form['rows'][$i]['id_tes'] = $ultimo_id;
                    rigdocInsert($form['rows'][$i]);
                    $last_rigdoc_id = gaz_dbi_last_id();
                    if (isset($form["row_$i"])) { //se è un rigo testo lo inserisco il contenuto in body_text
                        bodytextInsert(array('table_name_ref' => 'rigdoc', 'id_ref' => $last_rigdoc_id, 'body_text' => $form["row_$i"], 'lang_id' => $admin_aziend['id_language']));
                        gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $last_rigdoc_id, 'id_body_text', gaz_dbi_last_id());
                    }
                    if ($admin_aziend['conmag'] == 2 &&
                            $form['rows'][$i]['tiprig'] == 0 &&
                            $form['rows'][$i]['gooser'] == 0 &&
                            !empty($form['rows'][$i]['codart'])) { //se l'impostazione in azienda prevede l'aggiornamento automatico dei movimenti di magazzino
                        $last_movmag_id = $upd_mm->uploadMag(gaz_dbi_last_id(), $form['tipdoc'], $form['numdoc'], $form['seziva'], $datfat, $form['clfoco'], $form['sconto'], $form['caumag'], $form['rows'][$i]['codart'], $form['rows'][$i]['quanti'], $form['rows'][$i]['prelis'], $form['rows'][$i]['sconto'], 0, $admin_aziend['stock_eval_method'], false, $form['protoc']);
                    }
// se l'articolo prevede la gestione dei  lotti o della matricola/numero seriale creo un rigo in lotmag 
// ed eventualmente sposto e rinomino il relativo documento dalla dir temporanea a quella definitiva 
                    if ($form['rows'][$i]['lot_or_serial'] > 0) {
                        $form['rows'][$i]['id_rigdoc'] = $last_rigdoc_id;
                        $form['rows'][$i]['id_movmag'] = $last_movmag_id;
                        $form['rows'][$i]['expiry'] = gaz_format_date($form['rows'][$i]['expiry'], true);
                        if (empty($form['rows'][$i]['identifier'])) {
// creo un identificativo del lotto/matricola interno                            
                            $form['rows'][$i]['identifier'] = $form['datfat'] . '_' . $form['rows'][$i]['id_rigdoc'];
                        }
                        $last_lotmag_id = lotmagInsert($form['rows'][$i]);
// inserisco il rifermineto anche sul relativo movimento di magazzino
                        gaz_dbi_put_row($gTables['movmag'], 'id_mov', $last_movmag_id, 'id_lotmag', $last_lotmag_id);
                        if (!empty($form['rows'][$i]['filename'])) {
                            $tmp_file = "../../data/files/tmp/" . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $i . '_' . $form['rows'][$i]['filename'];
// sposto e rinomino il relativo file temporaneo    
                            $fd = pathinfo($form['rows'][$i]['filename']);
                            rename($tmp_file, "../../data/files/" . $admin_aziend['company_id'] . "/lotmag_" . $last_lotmag_id . '.' . $fd['extension']);
                        }
                    }
                }
                $prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'];
// prima di uscire cancello eventuali precedenti file temporanei
                foreach (glob("../../data/files/tmp/" . $prefix . "_*.*") as $fn) {
                    unlink($fn);
                }
                $_SESSION['print_request'] = $ultimo_id;
                header("Location: invsta_docacq.php");
                exit;
            }
        }
    }
// Se viene inviata la richiesta di conferma fornitore
    if ($_POST['hidden_req'] == 'clfoco') {
        $anagrafica = new Anagrafica();
        if (preg_match("/^id_([0-9]+)$/", $form['clfoco'], $match)) {
            $fornitore = $anagrafica->getPartnerData($match[1], 1);
        } else {
            $fornitore = $anagrafica->getPartner($form['clfoco']);
        }
        if (substr($form['tipdoc'], 0, 1) != 'A') {
            $result = gaz_dbi_get_row($gTables['imball'], "codice", $fornitore['imball']);
            $form['imball'] = $result['descri'];
        }
        $result = gaz_dbi_get_row($gTables['portos'], "codice", $fornitore['portos']);
        $form['portos'] = $result['descri'];
        $result = gaz_dbi_get_row($gTables['spediz'], "codice", $fornitore['spediz']);
        $form['spediz'] = $result['descri'];
        $form['destin'] = $fornitore['destin'];
        $form['id_des'] = $fornitore['id_des'];
        $id_des = $anagrafica->getPartner($form['id_des']);
        $form['search']['id_des'] = substr($id_des['ragso1'], 0, 10);
        if ($fornitore['aliiva'] > 0) {
            $form['ivaspe'] = $fornitore['aliiva'];
            $result = gaz_dbi_get_row($gTables['aliiva'], 'codice', $fornitore['aliiva']);
            $form['pervat'] = $result['aliquo'];
        }
        $form['in_codvat'] = $fornitore['aliiva'];
        $form['sconto'] = $fornitore['sconto'];
        $form['pagame'] = $fornitore['codpag'];
        $form['change_pag'] = $fornitore['codpag'];
        $form['banapp'] = $fornitore['banapp'];
        $form['listin'] = $fornitore['listin'];
        $pagame = gaz_dbi_get_row($gTables['pagame'], "codice", $form['pagame']);
        if (($pagame['tippag'] == 'B' or $pagame['tippag'] == 'T' or $pagame['tippag'] == 'V')
                and $fornitore['speban'] == 'S') {
            $form['speban'] = 0;
            $form['numrat'] = $pagame['numrat'];
        } else {
            $form['speban'] = 0.00;
            $form['numrat'] = 1;
        }
        if ($fornitore['cosric'] > 0) {
            $form['in_codric'] = $fornitore['cosric'];
        }
        $form['hidden_req'] = '';
    }

// Se viene inviata la richiesta di conferma rigo
//if (isset($_POST['in_submit_x'])) {
    /** ENRICO FEDELE */
    /* con button non funziona _x */
    if (isset($_POST['in_submit'])) {
        /** ENRICO FEDELE */
        $artico = gaz_dbi_get_row($gTables['artico'], "codice", $form['in_codart']);

        /** inizio modifica FP 09/01/2016
         * modifica piede ddt
         */
// addizione ai totali peso,pezzi,volume
        $form['net_weight'] += $form['in_quanti'] * $artico['peso_specifico'];
        $form['gross_weight'] += $form['in_quanti'] * $artico['peso_specifico'];
        if ($artico['pack_units'] > 0) {
            $form['units'] += intval(round($form['in_quanti'] / $artico['pack_units']));
        }
        $form['volume'] += $form['in_quanti'] * $artico['volume_specifico'];
// fine addizione peso,pezzi,volume
        /** fine modifica FP */
        /** inizio modifica FP 27/10/2015
         * carico gli indirizzi di destinazione dalla tabella gaz_destina
         */
        $idAnagrafe = $fornitore['id_anagra'];
        $rs_query_destinazioni = gaz_dbi_dyn_query("*", $gTables['destina'], "id_anagra='$idAnagrafe'");
        $array_destinazioni = gaz_dbi_fetch_all($rs_query_destinazioni);
        /** fine modifica FP */
    }
} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $tesdoc = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", intval($_GET['id_tes']));
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner($tesdoc['clfoco']);
    $form['id_tes'] = $tesdoc['id_tes'];
    $form['hidden_req'] = '';
    $form['search']['clfoco'] = substr($fornitore['ragso1'], 0, 10);
    $form['seziva'] = $tesdoc['seziva'];
    $form['tipdoc'] = $tesdoc['tipdoc'];
    if ($tesdoc['tipdoc'] == 'FAD') {
        $msg .= "Vuoi modificare un D.d.T. gi&agrave; fatturato!<br />";
    }
    if ($tesdoc['id_con'] > 0) {
        $msg .= "Questo documento &egrave; gi&agrave; stato contabilizzato!<br />";
    }
    $form['gioemi'] = substr($tesdoc['datfat'], 8, 2);
    $form['mesemi'] = substr($tesdoc['datfat'], 5, 2);
    $form['annemi'] = substr($tesdoc['datfat'], 0, 4);
    $form['gioreg'] = substr($tesdoc['initra'], 8, 2);
    $form['mesreg'] = substr($tesdoc['initra'], 5, 2);
    $form['annreg'] = substr($tesdoc['initra'], 0, 4);
    $form['protoc'] = $tesdoc['protoc'];
    $form['numdoc'] = $tesdoc['numdoc'];
    $form['numfat'] = $tesdoc['numfat'];
    $form['datfat'] = $tesdoc['datfat'];
    $form['clfoco'] = $tesdoc['clfoco'];
    $form['pagame'] = $tesdoc['pagame'];
    $form['change_pag'] = $tesdoc['pagame'];
    $form['speban'] = 0;
    $pagame = gaz_dbi_get_row($gTables['pagame'], "codice", $form['pagame']);
    if (($pagame['tippag'] == 'B' or $pagame['tippag'] == 'T' or $pagame['tippag'] == 'V') and $fornitore['speban'] == 'S') {
        $form['numrat'] = $pagame['numrat'];
    } else {
        $form['speban'] = 0.00;
        $form['numrat'] = 1;
    }
    $form['banapp'] = $tesdoc['banapp'];
    $form['vettor'] = $tesdoc['vettor'];
    $form['spevar'] = $tesdoc['spevar'];
    $form['ivaspe'] = 0;
    $form['pervat'] = 0;
    $form['cauven'] = $tesdoc['cauven'];
    $form['caucon'] = $tesdoc['caucon'];
    $form['caumag'] = $tesdoc['caumag'];
    $form['caucon'] = $tesdoc['caucon'];
    $form['id_pro'] = $tesdoc['id_pro'];
    $form['sconto'] = $tesdoc['sconto'];
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['tipdoc'] = $_GET['tipdoc'];
    $form['hidden_req'] = '';
    $form['id_tes'] = "";
    $form['datreg'] = date("d/m/Y");
//un documento d'acquisto ricevuto (non fiscale) imposto l'ultimo giorno del mese in modo da evidenziare un eventuale errore di mancata introduzione manuale del dato    
    $utsemi = mktime(0, 0, 0, date("m") + 1, date("d"), date("Y"));
    $form['datfat'] = date("d/m/Y", $utsemi);
    $form['search']['clfoco'] = '';
    $form['cosear'] = "";
    if (isset($_GET['seziva'])) {
        $form['seziva'] = intval($_GET['seziva']);
    } else {
        $form['seziva'] = 1;
    }
    $form['protoc'] = "";
    $form['numdoc'] = "";
    $form['numfat'] = "";
    $form['clfoco'] = "";
    $form['pagame'] = "";
    $form['change_pag'] = "";
    $form['banapp'] = "";
    $form['acc-fondo'] =0;
    $form['numrat'] = 1;
    $form['speban'] = 0;
    $form['spevar'] = 0;
    if ($admin_aziend['preeminent_vat'] > 0) {
        $form['ivaspe'] = $admin_aziend['preeminent_vat'];
    } else {
        $form['ivaspe'] = 1;
    }
    $result = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['ivaspe']);
    $form['pervat'] = $result['aliquo'];
    $form['cauven'] = 0;
    $form['caucon'] = '';
    if ($form['tipdoc'] == 'DDR') {
        $form['caumag'] = 4; //causale: 4 	SCARICO PER RESO A FORNITORE
    } else if ($form['tipdoc'] == 'DDL') {
        $form['caumag'] = 3; //causale: 3 	SCARICO PER C/LAVORAZIONE
    } else {
        $form['caumag'] = 5; //causale: 5 	CARICO PER ACQUISTO
    }
    $form['id_pro'] = 0;
    $form['sconto'] = 0;
    $fornitore['indspe'] = "";
    $fornitore['citspe'] = "";
}
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup', 'custom/autocomplete'));
?>

<script>
    $(function () {
        $("#datreg").datepicker();
        $("#datfat").datepicker();
        $('#seziva').selectmenu();
        $('#pagame').selectmenu();
        $('#banapp').selectmenu();
        $("#acc-fondo").selectmenu();
    });
</script>
<?php
$gForm = new gazieForm();
if (!empty($msg)) {
    $message = '<div class="col-sm-12">';
    $rsmsg = array_slice(explode('+', chop($msg)), 0, -1);
    foreach ($rsmsg as $value) {
        $message .= $script_transl['error'] . "! -> ";
        $rsval = explode('-', chop($value));
        foreach ($rsval as $valmsg) {
            $message .= $script_transl[$valmsg] . " ";
        }
        $message .= "<br>";
    }
    $message .= '</div>';
} else {
    $message = '<label for="msg_ind" class="col-sm-4 control-label">' . $script_transl['indspe'] . ':</label><div class="col-sm-8 text-left">' . $fornitore['indspe'] . ' ' . $fornitore['citspe'] . '</div>';
}
?>
<form class="form-horizontal" role="form" method="post" name="docacq" enctype="multipart/form-data" >
    <input type="hidden" name="<?php echo ucfirst($toDo); ?>" value="">
    <input type="hidden" value="<?php echo $form['hidden_req'] ?>" name="hidden_req" />
    <input type="hidden" value="<?php echo $form['id_tes']; ?>" name="id_tes">
    <input type="hidden" value="<?php echo $form['tipdoc']; ?>" name="tipdoc">
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
    <input type="hidden" value="<?php echo $form['change_pag']; ?>" name="change_pag">
    <input type="hidden" value="<?php echo $form['protoc']; ?>" name="protoc">
    <input type="hidden" value="<?php echo $form['numdoc']; ?>" name="numdoc">
    <div class="text-center">
        <p>
            <b>
                <?php
                echo $script_transl['title'];
                $select_fornitore = new selectPartner("clfoco");
                $select_fornitore->selectDocPartner('clfoco', $form['clfoco'], $form['search']['clfoco'], 'clfoco', $script_transl['mesg'], $admin_aziend['masfor']);
                ?>
            </b>
        </p>
    </div>
    <div class="panel panel-default">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
<?php echo $message; ?>                
                    </div>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label for="datreg" class="col-sm-4 control-label"><?php echo $script_transl['datreg']; ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="datreg" type="text" name="datreg" value="<?php echo $form['datreg']; ?>">
                        </div>
                    </div>
                </div>                    
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label for="numfat" class="col-sm-4 control-label"><?php echo $script_transl['numfat']; ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="numfat" placeholder="<?php echo $script_transl['numfat']; ?>" type="text" value="<?php echo $form['numfat']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label for="datfat" class="col-sm-4 control-label"><?php echo $script_transl['datfat']; ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="datfat" type="text" name="datfat" value="<?php echo $form['datfat']; ?>">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label for="seziva" class="col-sm-4 control-label"><?php echo $script_transl['seziva']; ?></label>
                        <div class="col-sm-8">
<?php $gForm->selectNumber('seziva', $form['seziva'], 0, 1, 3); ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label for="pagame" class="col-sm-4 control-label" ><?php echo $script_transl['pagame']; ?>:</label>
                        <div class="col-sm-8">
                            <?php
                            $select_pagame = new selectpagame("pagame");
                            $select_pagame->addSelected($form["pagame"]);
                            $select_pagame->output();
                            ?>                
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label for="banapp" class="col-sm-4 control-label"><?php echo $script_transl['banapp']; ?>:</label>
                        <div class="col-sm-8">
                            <?php
                            $select_banapp = new selectbanapp("banapp");
                            $select_banapp->addSelected($form["banapp"]);
                            $select_banapp->output();
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label for="acc-fondo" class="col-sm-4 control-label"><?php echo $script_transl['acc-fondo']; ?>:</label>
                        <div class="col-sm-8">
                            <?php
$gForm->selectAccount('acc-fondo', $form['acc-fondo']. '000000', array(1,9));
                            ?>
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
    <?php /*
      $gForm->selectNumber('seziva', $form['seziva'], 0, 1, 3);

      if (!empty($msg)) {
      $message = "";
      $rsmsg = array_slice(explode('+', chop($msg)), 0, -1);
      foreach ($rsmsg as $value) {
      $message .= $script_transl['error'] . "! -> ";
      $rsval = explode('-', chop($value));
      foreach ($rsval as $valmsg) {
      $message .= $script_transl[$valmsg] . " ";
      }
      $message .= "<br />";
      }
      echo '<div colspan="2" class="FacetDataTDred">' . $message . "</div>\n";
      } else {
      echo '<div  class="col-md-1">' . $script_transl['indspe'] . "</div><div>" . $fornitore['indspe'];
      echo "</div>\n";
      }
      echo '<div class="col-md-1">' . $script_transl['datfat'] . '</div><div class="col-md-1">';
      // select del giorno
      echo "\t <select name=\"gioemi\" class=\"FacetSelect\" >\n";
      for ($counter = 1; $counter <= 31; $counter++) {
      $selected = "";
      if ($counter == $form['gioemi'])
      $selected = "selected";
      echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
      }
      echo "\t </select>\n";
      // select del mese
      echo "\t <select name=\"mesemi\" class=\"FacetSelect\" >\n";
      for ($counter = 1; $counter <= 12; $counter++) {
      $selected = "";
      if ($counter == $form['mesemi'])
      $selected = "selected";
      $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
      echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
      }
      echo "\t </select>\n";
      // select del anno
      echo "\t <select name=\"annemi\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
      for ($counter = $form['annemi'] - 10; $counter <= $form['annemi'] + 10; $counter++) {
      $selected = "";
      if ($counter == $form['annemi'])
      $selected = "selected";
      echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
      }
      echo "\t </select></div></div>\n";
      echo "<div><div>libero</div><div>\n";
      echo "<a href=\"#\" onClick=\"calemi.showCalendar('anchor','" . $form['mesemi'] . "/" . $form['gioemi'] . "/" . $form['annemi'] . "'); return false;\" title=\" cambia la data! \" name=\"anchor\" id=\"anchor\" class=\"btn btn-default btn-sm\">\n";
      echo '<i class="glyphicon glyphicon-calendar"></i></a></div>';
      echo "<div>" . $script_transl['pagame'] . "</div><div>\n";
      $select_pagame = new selectpagame("pagame");
      $select_pagame->addSelected($form["pagame"]);
      $select_pagame->output();
      echo "</div><div>" . $script_transl['banapp'] . "</div><div>\n";
      $select_banapp = new selectbanapp("banapp");
      $select_banapp->addSelected($form["banapp"]);
      $select_banapp->output();
      echo "</div></div>\n";
      echo "<div><div> </div>\n";
      echo "<div><input type=\"text\" name=\"numfat\" value=\"" . $form['numfat'] . "\" maxlength=\"20\" size=\"20\"></div>\n";
      echo "<div>$script_transl[6]</div>";
      echo "<div><input TYPE=\"text\" name=\"gioreg\" value=\"" . $form['gioreg'] . "\" size=\"2\">\n";
      echo "<input TYPE=\"text\" name=\"mesreg\" value=\"" . $form['mesreg'] . "\" size=\"2\">\n";
      echo "<input TYPE=\"text\" id=\"datepicker\" class=\"hasDatepicker\" name=\"annreg\" value=\"" . $form['annreg'] . "\" size=\"2\">\n";
      echo "<a href=\"#\" onClick=\"cal.showCalendar('anchor','" . $form['mesreg'] . "/" . $form['gioreg'] . "/" . $form['annreg'] . "'); return false;\" title=\" cambia la data! \" name=\"anchor\" id=\"anchor\" class=\"btn btn-default btn-sm\">\n";
      echo '<i class="glyphicon glyphicon-calendar"></i></a></div>';

      echo "</div></div>\n";

      echo '	 <div class="FacetSeparatorTD" align="center">' . $script_transl[2] . '</div>
      <div class="Tlarge table table-striped table-bordered table-condensed table-responsive">
      <input type="hidden" value="' . $form['speban'] . '" name="speban" />
      <input type="hidden" value="' . $form['traspo'] . '" name="traspo" />
      <input type="hidden" value="' . $form['numrat'] . '" name="numrat" />
      <input type="hidden" value="' . $form['spevar'] . '" name="spevar" />
      <input type="hidden" value="' . $form['ivaspe'] . '" name="ivaspe" />
      <input type="hidden" value="' . $form['pervat'] . '" name="pervat" />
      <input type="hidden" value="' . $form['cauven'] . '" name="cauven" />
      <input type="hidden" value="' . $form['caucon'] . '" name="caucon" />
      <input type="hidden" value="' . $form['id_pro'] . '" name="id_pro" />';
      //inizio piede
      //fine piede
      echo "<div><div class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[32]</div><div class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[33]</div><div class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[34]</div><div class=\"FacetFieldCaptionTD\" align=\"right\">%$script_transl[24]<input type=\"text\" name=\"sconto\" value=\"" . $form["sconto"] . "\" maxlength=\"6\" size=\"1\" onchange=\"this.form.submit()\"></div><div class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[32]</div><div class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[19]</div><div class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[35]</div><div class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[36] " . $admin_aziend['symbol'] . "</div>\n";
      $chk_add_iva_tes = 0;
      $i = 1;
      $totivafat = 0.00;
      $totimpfat = 0.00;

      $castel = array();

      foreach ($castel as $key => $value) {
      $result = gaz_dbi_get_row($gTables['aliiva'], "codice", $key);
      $impcast = CalcolaImportoRigo(1, $value, $form['sconto']);
      if ($key == $form['ivaspe']) {
      $impcast += $form['traspo'] + $form['speban'] * $form['numrat'] + $form['spevar'];
      $chk_add_iva_tes = 1;
      }
      $ivacast = round($impcast * $result['aliquo']) / 100;
      $totimpfat += $impcast;
      $totivafat += $ivacast;
      if ($i > 0) {
      echo "<div><div align=\"right\">" . number_format($impcast, 2, '.', '') . "</div><div align=\"right\">" . $result['descri'] . " " . number_format($ivacast, 2, '.', '') . "</div>\n";
      }
      }

      if ($chk_add_iva_tes == 0) {// se le spese della testata non sono state aggiunte perchè non si è incontrato uno stesso codice IVA
      $result = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['ivaspe']);
      $impcast = $form['traspo'] + $form['speban'] * $form['numrat'] + $form['spevar'];
      $ivacast = round($impcast * $result['aliquo']) / 100;
      $totimpfat += $impcast;
      $totivafat += $ivacast;
      if ($i > 0) {
      echo "<div><div align=\"right\">" . number_format($impcast, 2, '.', '') . "</div><div align=\"right\">" . $result['descri'] . " " . number_format($ivacast, 2, '.', '') . "</div>\n";
      }
      $chk_add_iva_tes = 1;
      }

      if ($i > 0) {
      echo "	<div align=\"right\"></div>
      <div align=\"right\">" . gaz_format_number(($totimpfat - $form['traspo'] - ($form['speban'] * $form['numrat']) - $form['spevar']), 2, '.', '') . "</div>
      <div align=\"right\">" . number_format($totimpfat, 2, '.', '') . "</div>
      <div align=\"right\">" . number_format($totivafat, 2, '.', '') . "</div>
      <div align=\"right\"></div>
      <div align=\"right\">" . number_format(($totimpfat + $totivafat), 2, '.', '') . "</div>
      </div>\n";

      if ($toDo == 'update') {
      echo '<div>
      <div colspan="8" class="text-right alert alert-success">
      <input type="submit" accesskey="m" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="MODIFICA !" />
      </div>
      </div>';
      } else {
      echo '<div>
      <div colspan="8" class="text-right alert alert-success">
      <input type="submit" accesskey="i" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="INSERISCI !" />
      </div>
      </div>';
      }
      }
      echo '</div>'; */
    ?>
</form>
</div><!-- chiude div container role main -->
</body>
</html>