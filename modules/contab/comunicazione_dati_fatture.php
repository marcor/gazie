<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/check.inc.php");

function createRowsAndErrors($anno, $periodicita, $trimestre_semestre) {
    global $gTables, $admin_aziend, $script_transl;
    $nuw = new check_VATno_TAXcode();
    if ($periodicita == 'T') { // trimestrale
        if ($trimestre_semestre == 1) {
            $date_ini = new DateTime($anno . '-1-1');
        } elseif ($trimestre_semestre == 2) {
            $date_ini = new DateTime($anno . '-4-1');
        } elseif ($trimestre_semestre == 3) {
            $date_ini = new DateTime($anno . '-7-1');
        } else {
            $date_ini = new DateTime($anno . '-10-1');
        }
        $di = $date_ini->format('Y-m-t');
        $date_ini->modify('+2 month');
        $df = $date_ini->format('Y-m-t');
    } else { // semestrale
        if ($trimestre_semestre == 1) {
            $date_ini = new DateTime($anno . '-1-1');
        } else {
            $date_ini = new DateTime($anno . '-7-1');
        }
        $di = $date_ini->format('Y-m-t');
        $date_ini->modify('+5 month');
        $df = $date_ini->format('Y-m-t');
    }
    $sqlquery = "SELECT " . $gTables['rigmoi'] . ".*, ragso1,ragso2,sedleg,sexper,indspe,regiva,allegato,
               citspe,prospe,country,codfis,pariva," . $gTables['tesmov'] . ".clfoco," . $gTables['tesmov'] . ".protoc,
               " . $gTables['tesmov'] . ".numdoc," . $gTables['tesmov'] . ".datdoc," . $gTables['tesmov'] . ".seziva,
               " . $gTables['tesmov'] . ".caucon," . $gTables['tesdoc'] . ".numfat AS n_fatt,id_anagra,
			   datreg,datnas,luonas,pronas,counas,id_doc,iso,black_list,cod_agenzia_entrate,
               operat, impost AS imposta," . $gTables['rigmoi'] . ".id_tes AS idtes,
               imponi AS imponibile FROM " . $gTables['rigmoi'] . "
               LEFT JOIN " . $gTables['tesmov'] . " ON " . $gTables['rigmoi'] . ".id_tes = " . $gTables['tesmov'] . ".id_tes
               LEFT JOIN " . $gTables['tesdoc'] . " ON " . $gTables['tesmov'] . ".id_doc = " . $gTables['tesdoc'] . ".id_tes
               LEFT JOIN " . $gTables['aliiva'] . " ON " . $gTables['rigmoi'] . ".codiva = " . $gTables['aliiva'] . ".codice
               LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesmov'] . ".clfoco = " . $gTables['clfoco'] . ".codice
               LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['anagra'] . ".id = " . $gTables['clfoco'] . ".id_anagra
               LEFT JOIN " . $gTables['country'] . " ON " . $gTables['anagra'] . ".country = " . $gTables['country'] . ".iso
               WHERE datreg BETWEEN '" . $di . "' AND '" . $df . "'
                 AND ( " . $gTables['tesmov'] . ".clfoco LIKE '" . $admin_aziend['masfor'] . "%' OR " . $gTables['tesmov'] . ".clfoco LIKE '" . $admin_aziend['mascli'] . "%')
                 AND " . $gTables['clfoco'] . ".allegato > 0 AND " . $gTables['tesmov'] . ".seziva <> " . $admin_aziend['reverse_charge_sez'] . "
               ORDER BY regiva,operat,clfoco,datreg,protoc";
    $result = gaz_dbi_query($sqlquery);
    $castel_transact = array();
    $error_transact = array();
    if (gaz_dbi_num_rows($result) > 0) {
        // inizio creazione array righi ed errori
        $progressivo = 0;
        $ctrl_id = 0;
        $value_imponi = 0.00;
        $value_impost = 0.00;
        while ($row = gaz_dbi_fetch_array($result)) {
            if ($row['operat'] >= 1) {
                $value_imponi = $row['imponibile'];
                $value_impost = $row['imposta'];
            } else {
                $value_imponi = 0;
                $value_impost = 0;
            }
            if ($ctrl_id <> $row['idtes']) {
                // se il precedente movimento non ha raggiunto l'importo lo elimino
                if (isset($castel_transact[$ctrl_id]) && $castel_transact[$ctrl_id]['operazioni_imponibili'] < 0.5 && $castel_transact[$ctrl_id]['operazioni_esente'] < 0.5 && $castel_transact[$ctrl_id]['operazioni_nonimp'] < 0.5 && $castel_transact[$ctrl_id]['contract'] < 0.5) {
                    unset($castel_transact[$ctrl_id]);
                    unset($error_transact[$ctrl_id]);
                }
                if (isset($castel_transact[$ctrl_id]) && $castel_transact[$ctrl_id]['quadro'] == 'DF' && $castel_transact[$ctrl_id]['operazioni_imponibili'] < $min_limit && $castel_transact[$ctrl_id]['contract'] < $min_limit) {
                    unset($castel_transact[$ctrl_id]);
                    unset($error_transact[$ctrl_id]);
                }
                // inizio controlli su CF e PI
                $resultpi = $nuw->check_VAT_reg_no($row['pariva']);
                $resultcf = $nuw->check_VAT_reg_no($row['codfis']);
                if ($admin_aziend['country'] != $row['country']) {
                    // È uno non residente 
                    if ($row['country'] == 'SM') {
                        // SAN MARINO 
                    } else {
                        if (!empty($row['datnas'])) { // È un persona fisica straniera
                            if (empty($row['pronas']) || empty($row['luonas']) || empty($row['counas'])) {
                                $error_transact[$row['idtes']][] = $script_transl['errors'][9];
                            }
                        }
                    }
                } elseif (empty($resultpi) && !empty($row['pariva'])) {
                    // ha la partita IVA ed è giusta 
                    if (strlen(trim($row['codfis'])) == 11) {
                        // È una persona giuridica

                        if (intval($row['codfis']) == 0 && $row['allegato'] < 2) { // se non è un riepilogativo 
                            $error_transact[$row['idtes']][] = $script_transl['errors'][1];
                        } elseif ($row['sexper'] != 'G') {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][2];
                        }
                    } else {
                        // È una una persona fisica
                        $resultcf = $nuw->check_TAXcode($row['codfis']);
                        if (empty($row['codfis'])) {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][3];
                        } elseif ($row['sexper'] == 'G' and empty($resultcf)) {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][4];
                        } elseif ($row['sexper'] == 'M' and empty($resultcf) and ( intval(substr($row['codfis'], 9, 2)) > 31 or
                                intval(substr($row['codfis'], 9, 2)) < 1)) {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][5];
                        } elseif ($row['sexper'] == 'F' and empty($resultcf) and ( intval(substr($row['codfis'], 9, 2)) > 71 or
                                intval(substr($row['codfis'], 9, 2)) < 41)) {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][6];
                        } elseif (!empty($resultcf)) {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][7];
                        }
                    }
                } else {
                    // È un soggetto con codice fiscale senza partita IVA 
                    $resultcf = $nuw->check_TAXcode($row['codfis']);
                    if (strlen(trim($row['codfis'])) == 11) { // È una persona giuridica
                        $resultcf = $nuw->check_VAT_reg_no($row['codfis']);
                    }
                    if (empty($row['codfis'])) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][3];
                    } elseif ($row['sexper'] == 'G' and ! empty($resultcf)) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][4];
                    } elseif ($row['sexper'] == 'M' and empty($resultcf) and ( intval(substr($row['codfis'], 9, 2)) > 31 or
                            intval(substr($row['codfis'], 9, 2)) < 1)) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][5];
                    } elseif ($row['sexper'] == 'F' and empty($resultcf) and ( intval(substr($row['codfis'], 9, 2)) > 71 or
                            intval(substr($row['codfis'], 9, 2)) < 41)) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][6];
                    } elseif (!empty($resultcf)) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][7];
                    }
                }
                // fine controlli su CF e PI
                $castel_transact[$row['idtes']] = $row;
                $castel_transact[$row['idtes']]['riepil'] = 0;
                // determino il tipo di soggetto residente all'estero
                $castel_transact[$row['idtes']]['istat_country'] = 0;
                // --------- TIPIZZAZIONE DEI MOVIMENTI -----------------
                $castel_transact[$row['idtes']]['quadro'] = 'ZZ';
                $castel_transact[$row['idtes']]['tipo_documento'] = 'TD99'; // NON TIPIZZATO
                if ($row['country'] <> $admin_aziend['country']) { // ESTERO
                    $castel_transact[$row['idtes']]['istat_country'] = $row['country'];
                    $castel_transact[$row['idtes']]['cod_ade'] = $row['cod_agenzia_entrate'];
                    if ($row['country'] == 'SM') {
                        // SAN MARINO 
                        $castel_transact[$row['idtes']]['quadro'] = 'SE';
                    } else {
                        // NON RESIDENTE
                        $castel_transact[$row['idtes']]['quadro'] = 'FN';
                    }
                    if ($row['regiva'] >= 6) { // ACQUISTI ESTERO
                        if ($row['operation_type'] == 'SERVIZ' ||
                                $row['operation_type'] == 'ASNRES') {  // acquisto di SERVIZI
                            $castel_transact[$row['idtes']]['tipo_documento'] = 'TD11';
                        } else {                                        // acquisto di BENI
                            $castel_transact[$row['idtes']]['tipo_documento'] = 'TD10';
                        }
                    }
                } else { //ITALIA
                    if ($row['regiva'] == 4 && (!empty($row['n_fatt']))) { // se è un documento allegato ad uno scontrino utilizzo il numero fattura in tesdoc
                        $castel_transact[$row['idtes']]['numdoc'] = $row['n_fatt'] . ' scontr.n.' . $row['numdoc'];
                        $castel_transact[$row['idtes']]['seziva'] = '';
                    }
                    if ($row['pariva'] > 0) {
                        // RESIDENTE con partita IVA
                        if ($row['regiva'] < 6) { // VENDITE - Fatture Emesse o Note Emesse
                            if ($row['operat'] == 1) { // Fattura
                                $castel_transact[$row['idtes']]['quadro'] = 'FE';
                                $castel_transact[$row['idtes']]['tipo_documento'] = 'TD01';
                            } else {                // Note
                                $castel_transact[$row['idtes']]['quadro'] = 'NE';
                                $castel_transact[$row['idtes']]['tipo_documento'] = 'TD04';
                            }
                            // aggiungo la sezione al numero documento
                            $castel_transact[$row['idtes']]['numdoc'] .= '/' . $castel_transact[$row['idtes']]['seziva'];
                        } else {                // ACQUISTI - Fatture Ricevute o Note Ricevute
                            if ($row['operat'] == 1) { // Fattura
                                $castel_transact[$row['idtes']]['quadro'] = 'FR';
                                $castel_transact[$row['idtes']]['tipo_documento'] = 'TD01';
                            } else {                // Note
                                $castel_transact[$row['idtes']]['quadro'] = 'NR';
                                $castel_transact[$row['idtes']]['tipo_documento'] = 'TD04';
                            }
                        }
                    } else { // senza partita iva
                        if ($row['allegato'] == 2) { // riepilogativo es.scheda carburante
                            $castel_transact[$row['idtes']]['quadro'] = 'FR';
                            $castel_transact[$row['idtes']]['riepil'] = 1;
                        } elseif (empty($resultcf) && strlen($row['codfis']) == 11) { // associazioni/noprofit
                            // imposto il codice fiscale come partita iva
                            if ($row['regiva'] < 6) { // VENDITE - Fatture Emesse o Note Emesse
                                if ($row['operat'] == 1) { // Fattura
                                    $castel_transact[$row['idtes']]['quadro'] = 'FE';
                                    $castel_transact[$row['idtes']]['tipo_documento'] = 'TD01';
                                } else {                // Note
                                    $castel_transact[$row['idtes']]['quadro'] = 'NE';
                                    $castel_transact[$row['idtes']]['tipo_documento'] = 'TD04';
                                }
                                // aggiungo la sezione al numero documento
                                $castel_transact[$row['idtes']]['numdoc'] .= '/' . $castel_transact[$row['idtes']]['seziva'];
                            } else {                // ACQUISTI - Fatture Ricevute o Note Ricevute
                                // nei quadri FR NR è possibile indicare la sola partita iva
                                $castel_transact[$row['idtes']]['pariva'] = $castel_transact[$row['idtes']]['codfis'];
                                $castel_transact[$row['idtes']]['codfis'] = 0;
                                if ($row['operat'] == 1) { // Fattura
                                    $castel_transact[$row['idtes']]['quadro'] = 'FR';
                                    $castel_transact[$row['idtes']]['tipo_documento'] = 'TD01';
                                } else {                // Note
                                    $castel_transact[$row['idtes']]['quadro'] = 'NR';
                                    $castel_transact[$row['idtes']]['tipo_documento'] = 'TD04';
                                }
                            }
                        } elseif (empty($resultcf) && strlen($row['codfis']) == 16) { // privato servito con fattura
                            if ($row['operat'] == 1) { // Fattura
                                $castel_transact[$row['idtes']]['quadro'] = 'FE';
                                $castel_transact[$row['idtes']]['tipo_documento'] = 'TD01';
                            } else {                // Note
                                $castel_transact[$row['idtes']]['quadro'] = 'NE';
                                $castel_transact[$row['idtes']]['tipo_documento'] = 'TD04';
                            }
                            // aggiungo la sezione al numero documento
                            $castel_transact[$row['idtes']]['numdoc'] .= '/' . $castel_transact[$row['idtes']]['seziva'];
                        } else {                // privati con scontrino
                            $castel_transact[$row['idtes']]['quadro'] = 'DF';
                        }
                    }
                }

                // ricerco gli eventuali contratti che hanno generato la transazione
                $castel_transact[$row['idtes']]['n_rate'] = 1;
                $castel_transact[$row['idtes']]['contract'] = 0;
                if ($row['id_doc'] > 0) {
                    $contr_query = "SELECT " . $gTables['tesdoc'] . ".*," . $gTables['contract'] . ".* FROM " . $gTables['tesdoc'] . "
                            LEFT JOIN " . $gTables['contract'] . " ON " . $gTables['tesdoc'] . ".id_contract = " . $gTables['contract'] . ".id_contract 
                            WHERE id_tes = " . $row['id_doc'] . " AND (" . $gTables['tesdoc'] . ".id_contract > 0 AND tipdoc NOT LIKE 'VCO')";
                    $result_contr = gaz_dbi_query($contr_query);

                    if (gaz_dbi_num_rows($result_contr) > 0) {
                        $contr_r = gaz_dbi_fetch_array($result_contr);
                        // devo ottenere l'importo totale del contratto
                        $castel_transact[$row['idtes']]['contract'] = $contr_r['current_fee'] * $contr_r['months_duration'];
                        $castel_transact[$row['idtes']]['n_rate'] = 2;
                    }
                }
                // fine ricerca contratti
                if (!empty($row['sedleg'])) {
                    if (preg_match("/([\w\,\.\s]+)([0-9]{5})[\s]+([\w\s\']+)\(([\w]{2})\)/", $row['sedleg'], $regs)) {
                        $castel_transact[$row['idtes']]['Indirizzo'] = $regs[1];
                        $castel_transact[$row['idtes']]['Comune'] = $regs[3];
                        $castel_transact[$row['idtes']]['Provincia'] = $regs[4];
                    } else {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][10];
                    }
                }
                // inizio valorizzazione imponibile,imposta,senza_iva,art8
                $castel_transact[$row['idtes']]['operazioni_imponibili'] = 0;
                $castel_transact[$row['idtes']]['imposte_addebitate'] = 0;
                $castel_transact[$row['idtes']]['operazioni_esente'] = 0;
                $castel_transact[$row['idtes']]['operazioni_nonimp'] = 0;
                $castel_transact[$row['idtes']]['tipiva'] = 1;
                switch ($row['tipiva']) {
                    case 'I':
                    case 'D':
                        $castel_transact[$row['idtes']]['operazioni_imponibili'] = $value_imponi;
                        $castel_transact[$row['idtes']]['imposte_addebitate'] = $value_impost;
                        if ($value_impost == 0) {  //se non c'è imposta il movimento è sbagliato
                            $error_transact[$row['idtes']][] = $script_transl['errors'][11];
                        }
                        break;
                    case 'E':

                        $castel_transact[$row['idtes']]['tipiva'] = 3;
                        $castel_transact[$row['idtes']]['operazioni_esente'] = $value_imponi;
                        if ($value_impost != 0) {  //se c'è imposta il movimento è sbagliato
                            $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                        }
                        break;
                    case 'N':
                        $castel_transact[$row['idtes']]['tipiva'] = 2;
                        $castel_transact[$row['idtes']]['operazioni_nonimp'] = $value_imponi;
                        if ($value_impost != 0) {  //se c'è imposta il movimento è sbagliato
                            $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                        }
                        break;
                }
            } else { //movimenti successivi al primo ma dello stesso id
                // inizio addiziona valori imponibile,imposta,esente,non imponibile
                switch ($row['tipiva']) {
                    case 'I':
                    case 'D':
                        $castel_transact[$row['idtes']]['operazioni_imponibili'] += $value_imponi;
                        $castel_transact[$row['idtes']]['imposte_addebitate'] += $value_impost;
                        if ($value_impost == 0) {  //se non c'è imposta il movimento è sbagliato
                            $error_transact[$row['idtes']][] = $script_transl['errors'][11];
                        }
                        break;
                    case 'E':
                        $castel_transact[$row['idtes']]['operazioni_esente'] += $value_imponi;
                        if ($value_impost != 0) {  //se c'è imposta il movimento è sbagliato
                            $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                        }
                        break;
                    case 'N':
                        $castel_transact[$row['idtes']]['operazioni_nonimp'] += $value_imponi;
                        if ($value_impost != 0) {  //se c'è imposta il movimento è sbagliato
                            $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                        }
                        break;
                }
                // fine addiziona valori imponibile,imposta,esente,non imponibile
            }


            // fine valorizzazione imponibile,imposta,esente,non imponibile
            $ctrl_id = $row['idtes'];
        }
        // se il precedente movimento non ha raggiunto l'importo lo elimino
        if (isset($castel_transact[$ctrl_id]) && $castel_transact[$ctrl_id]['operazioni_imponibili'] < 0.5 && $castel_transact[$ctrl_id]['operazioni_esente'] < 0.5 && $castel_transact[$ctrl_id]['operazioni_nonimp'] < 0.5 && $castel_transact[$ctrl_id]['contract'] < 0.5) {
            unset($castel_transact[$ctrl_id]);
            unset($error_transact[$ctrl_id]);
        }
        if (isset($castel_transact[$ctrl_id]) && $castel_transact[$ctrl_id]['quadro'] == 'DF' && $castel_transact[$ctrl_id]['operazioni_imponibili'] < $min_limit && $castel_transact[$ctrl_id]['contract'] < $min_limit) {
            unset($castel_transact[$ctrl_id]);
            unset($error_transact[$ctrl_id]);
        }
    } else {
        $error_transact[0] = $script_transl['errors'][15];
    }
    // fine creazione array righi ed errori
    return array($castel_transact, $error_transact);
}

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (!isset($_POST['ritorno'])) {
// al primo accesso allo script
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
    if ((isset($_GET['Update']) && isset($_GET['id']))) { // è una modifica
    } else { // è un inserimento
// controllo se ad oggi è possibile fare una liquidazione
        $y = date('Y');
        $m = floor((date('m') - 1) / 3);
        if ($m == 0) {
            $y--;
            $m = 4;
        }
        $trimestre_semestre = $y . $m;
        $form['trimestre_semestre'] = $m;
        $form['anno'] = $y;
        $form['periodicita'] = 'T';
// cerco l'ultimo file xml generato
        $rs_query = gaz_dbi_dyn_query("*", $gTables['comunicazioni_dati_fatture'], 1, "anno DESC, trimestre_semestre DESC", 0, 1);
        $ultima_comunicazione = gaz_dbi_fetch_array($rs_query);
        if ($ultima_comunicazione) {
            if ($ultima_comunicazione['periodicita'] == 'T') { // ho fatto una liquidazione trimestrale
                $ultimo_trimestre_liquidato = $ultima_comunicazione['anno'] . $ultima_comunicazione['mese_trimestre'];
            } else {
                $ultimo_trimestre_liquidato = $ultima_comunicazione['anno'] . floor($ultima_comunicazione['mese_trimestre'] / 3);
            }
        } else { // non ho mai fatto liquidazioni, propongo la prima da fare
            $ultimo_trimestre_liquidato = 0;
        }
        if ($ultimo_trimestre_liquidato >= $trimestre_semestre) {
            $msg['err'][] = "eseguita";
        } else {
// propongo una liquidazione in base ai dati che trovo sui movimenti IVA
        }
    }
} else { // nei post successivi (submit)
    $form['anno'] = intval($_POST['anno']);
    $form['trimestre_semestre'] = intval($_POST['trimestre_semestre']);
    $form['periodicita'] = substr($_POST['periodicita'], 0, 1);
    $form['ritorno'] = $_POST['ritorno'];
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    if (isset($_POST['Submit'])) {
        if ($toDo == 'update') { // e' una modifica
// aggiorno il database
            $id = array('anno', "'" . $vi['anno'] . "' AND mese_trimestre = '" . $ki . "'");
            gaz_dbi_table_update('liquidazioni_iva', $id, $vi);
            require("../../library/include/agenzia_entrate.inc.php");
            creaFileDAT10($admin_aziend, $form);
            $msg['war'][] = "download";
        } else { // e' un'inserimento
            gaz_dbi_table_insert('liquidazioni_iva', $vi);
            require("../../library/include/agenzia_entrate.inc.php");
            creaFileDAT10($admin_aziend, $form);
            $msg['war'][] = "download";
        }
    } elseif (isset($_POST['Download'])) {
        $file = '../../data/files/' . $admin_aziend['codice'] . '/' . $admin_aziend['country'] . $admin_aziend['codfis'] . "_DF_" . $form['trimestre_semestre'] . ".xml";
        header("Pragma: public", true);
        header("Expires: 0"); // set expiration time
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename=" . basename($file));
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($file));
        die(file_get_contents($file));
        exit;
    }
}

if ((isset($_GET['Update']) && !isset($_GET['id']))) {
    header("Location: " . $form['ritorno']);
    exit;
}

require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new contabForm();
?>
<STYLE>
    .verticaltext {
        position: relative; 
        padding-left:50px;
        margin:1em 0;
        min-height:120px;
    }

    .verticaltext_content {
        -webkit-transform: rotate(-90deg);
        -moz-transform: rotate(-90deg);
        -ms-transform: rotate(-90deg);
        -o-transform: rotate(-90deg);
        filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
        position: absolute;
        left: -130px;
        top: 300px;
        color: #000;
        text-transform: uppercase;
        font-size:30px;

    </STYLE>
    <form method="POST" name="form" enctype="multipart/form-data">
        <input type="hidden" name="hidden_req" value="<?php echo $form['hidden_req']; ?>">
        <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
        <input type="hidden" name="<?php echo ucfirst($toDo) ?>" value="">
        <input type="hidden" value="<?php echo $form['trimestre_semestre']; ?>" name="trimestre_semestre">
        <div class="text-center"><b><?php echo $script_transl['title']; ?></b></div>
        <div class="panel panel-default gaz-table-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="anno" class="col-sm-4 control-label"><?php echo $script_transl['anno_imposta']; ?></label>
                            <?php
                            $gForm->selectNumber('anno', $form['anno'], 0, $form['anno'] - 5, $form['anno'] + 5, "col-sm-8", 'anno_imposta', 'style="max-width: 100px;"');
                            ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="periodicita" class="col-sm-4 control-label"><?php echo $script_transl['periodicita']; ?></label>
                            <?php
                            $gForm->variousSelect('periodicita', $script_transl['periodicita_value'], $form['periodicita'], "col-sm-8", false, 'periodicita', false, 'style="max-width: 300px;"');
                            ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="trimestre_semestre" class="col-sm-4 control-label"><?php echo $script_transl['trimestre_semestre']; ?></label>
                            <?php
                            $gForm->variousSelect('trimestre_semestre', $script_transl['trimestre_semestre_value'][$form['periodicita']], $form['trimestre_semestre'], "col-sm-8", false, 'trimestre_semestre', false, 'style="max-width: 300px;"');
                            ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
            </div><!-- chiude container  -->
        </div><!-- chiude panel  -->
        <?php
        $queryData = createRowsAndErrors($form['anno'], $form['periodicita'], $form['trimestre_semestre']);
        if (count($queryData[1]) >= 1) { // ho degli errori
            echo '<div class="container">';
            foreach ($queryData[1] as $k => $v) {
                echo '<div class="row alert alert-warning fade in" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
					<span aria-hidden="true">&times;</span>
				</button>
				';
                echo '<span class="glyphicon glyphicon-alert" aria-hidden="true"></span><a class="btn btn-xs btn-default" href="../inform/admin_anagra.php?id=' . $queryData[0][$k]['id_anagra'] . '&Update" > ' . $queryData[0][$k]['ragso1'] . '</a> ERROR! => ' . $v[0] . ' <a class="btn btn-xs btn-default" href="admin_movcon.php?Update&id_tes=' . $k . '">' . $queryData[0][$k]['numdoc'] . '</a><br>';
                echo "</div>\n";
            }
            echo "</div>\n";
        } else {
            ?> 
            <div class="panel panel-default">
                <div id="gaz-responsive-table"  class="container-fluid">
                    <table class="table table-responsive table-striped table-condensed cf">
                        <thead>
                            <tr class="bg-success">              
                                <th>
                                    <?php echo $script_transl["TipoDocumento"]; ?>
                                </th>
                                <th>
                                    <?php echo $script_transl["Numero"]; ?>
                                </th>
                                <th>
                                    <?php echo $script_transl["Data"]; ?>
                                </th>
                                <th>
                                    <?php echo $script_transl["DataRegistrazione"]; ?>
                                </th>
                                <th>
                                    <?php echo $script_transl["ImponibileImporto"]; ?>
                                </th>
                                <th class="text-right">
                                    <?php echo $script_transl["Imposta"]; ?>
                                </th>
                                <th class="text-right">
                                    <?php echo $script_transl["Aliquota"]; ?>
                                </th>
                            </tr>      
                        </thead>    
                        <tbody id="all_rows">

                            <?php
                            // CREO L'ARRAY ASSOCIATIVO DEI TIPI DOCUMENTI
                            $xml = simplexml_load_file('../../library/include/tipi_documenti.xml');
                            foreach ($xml as $d) {
                                $v_td = get_object_vars($d);
                                $td[$v_td['field'][0]] = $v_td['field'][1];
                            }
                            $td['TD99'] = 'NON INSERIBILE';
                            // FINE CREAZIONE
                            $ctrl_partner = 0;
                            foreach ($queryData[0] as $k => $v) {
                                if ($ctrl_partner <> $v['clfoco']) {
                                    ?>
                                    <tr>              
                                        <td colspan=7 data-title="<?php echo $script_transl["CessionarioCommittente"]; ?>" class="text-info">
                                            <b>   <?php echo $v["ragso1"] . ' ' . $v["ragso2"]; ?> </b> <?php echo $script_transl["partita_iva"] . ' ' . $v["pariva"] . ' ' . $script_transl["codice_fiscale"] . ' ' . $v["codfis"]; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td data-title="<?php echo $script_transl["TipoDocumento"]; ?>">
                                        <a class="btn btn-xs btn-default" href="admin_movcon.php?Update&id_tes=<?php echo $k; ?>" title="<?php echo $v["caucon"]; ?>"><i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo ucfirst($td[$v["tipo_documento"]]) . ' prot.' . $v["protoc"]; ?></a>
                                    </td>
                                    <td data-title="<?php echo $script_transl["Numero"]; ?>" class="text-center">
                                        <?php echo $v["numdoc"]; ?>
                                    </td>
                                    <td data-title="<?php echo $script_transl["Data"]; ?>" class="text-center">
                                        <?php echo gaz_format_date($v["datdoc"]); ?>
                                    </td>
                                    <td data-title="<?php echo $script_transl["DataRegistrazione"]; ?>" class="text-center">
                                        <?php echo gaz_format_date($v["datreg"]); ?>
                                    </td>
                                    <td data-title="<?php echo $script_transl["ImponibileImporto"]; ?>" class="text-right">
                                        <?php echo gaz_format_number($v['imponibile']); ?>
                                    </td>
                                    <td data-title="<?php echo $script_transl["Imposta"]; ?>"  class="text-right">
                                        <?php echo gaz_format_number($v['imposta']); ?>
                                    </td>
                                    <td data-title="<?php echo $script_transl["Aliquota"]; ?>"  class="text-right">
                                        <?php echo floatval($v['periva']); ?>%
                                    </td>
                                </tr> 
                                <?php
                                $ctrl_partner = $v['clfoco'];
                            }
                            ?>
                        </tbody>     
                    </table>
                </div>  
            </div>
            <div class="col-sm-12 text-center"><input name="Submit" type="submit" class="btn btn-warning" value="<?php echo $script_transl["ok"]; ?>" /></div>
                <?php
            }
            ?>   
    </form>
    <?php
    require("../../library/include/footer.php");
    ?>