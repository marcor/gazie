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
$msg = array('err' => array(), 'war' => array());
require("../../library/include/check.inc.php");

if (!isset($_GET['anno'])) { //al primo accesso allo script suppongo che si debba produrre l'elenco per l'anno precedente
    $_GET['anno'] = date("Y") - 1;
    $_GET['min_limit'] = 2951;
    $Testa = getHeaderData();
    if ($admin_aziend['sexper'] != 'G') { // le persone fisiche hanno due campi separati
        $_GET['ragso1'] = $Testa['cognome'];
        $_GET['ragso2'] = $Testa['nome'];
    } else {
        $_GET['ragso1'] = strtoupper($admin_aziend['ragso1']);
        $_GET['ragso2'] = strtoupper($admin_aziend['ragso2']);
    }
}

function printTransact($transact, $error) {
    global $script_transl, $admin_aziend;
    $nrec = 0;
    echo "<td align=\"center\" class=\"FacetDataTD\" >N.Rec.</td>";
    echo "<td align=\"center\" class=\"FacetDataTD\" >N.Mov.</td>";
    echo "<td align=\"center\" class=\"FacetDataTD\" >" . $script_transl['sourcedoc'] . "</td>";
    echo "<td class=\"FacetDataTD\" >" . $script_transl['soggetto'] . "</td>";
    echo "<td align=\"center\" class=\"FacetDataTD\" >" . $script_transl['pariva'] . "</td>";
    echo "<td align=\"center\" class=\"FacetDataTD\" >" . $script_transl['codfis'] . "</td>";
    echo "<td align=\"center\" class=\"FacetDataTD\" >" . $script_transl['quadro'] . "</td>";
    echo "<td align=\"right\" class=\"FacetDataTD\" >" . $script_transl['amount'] . "</td>";
    echo "<td align=\"right\" class=\"FacetDataTD\" >" . $script_transl['tax'] . "</td>";
    echo "</tr>\n";
    foreach ($transact as $key => $value) {
        $nrec++;
        $totale = gaz_format_number($value['operazioni_imponibili'] + $value['operazioni_nonimp'] + $value['operazioni_esente']);
        $class = ' ';
        switch ($value['quadro']) {
            case 'FE':
                $class = 'style="color:#000000; background-color: #FFDDDD;"';
                break;
            case 'NE':
                $class = 'style="color:#000000; background-color: #DDFFDD;"';
                break;
            case 'FR':
                $class = 'style="color:#000000; background-color: #AFC8D8"';
                break;
            case 'NR':
                $class = 'style="color:#000000; background-color: #D3CFA8;"';
                break;
            case 'DF':
                $class = 'style="color:#000000; background-color: #DDDDFF;"';
                break;
        }
        if (isset($error[$key])) {
            $class = ' class="FacetDataTDred" ';
        }
        echo "<tr>";
        echo "<td align=\"right\" $class>$nrec</a></td>";
        echo "<td align=\"center\" $class><a href=\"../contab/admin_movcon.php?id_tes=" . $value['id_tes'] . "&Update\">n." . $value['id_tes'] . " - " . gaz_format_date($value['datreg']) . "</a></td>";
        echo "<td align=\"center\" $class> sez." . $value['seziva'] . " n." . $value['numdoc'] . ' del ' . gaz_format_date($value['datdoc']) . "</td>";
        echo "<td $class>" . $value['ragso1'] . ' ' . $value['ragso2'] . "</td>";
        if ($value['riepil'] == 1) { // è un riepilogativo quindi il tracciato dovrà prevedere l'apposito flag
            echo "<td align=\"center\" colspan=\"2\" style=\"color:#000000; background-color: #DDADAF;\">" . $script_transl['riepil'] . "</td>";
        } else {
            echo "<td align=\"center\" $class>" . $value['iso'] . " " . $value['pariva'] . "</td>";
            echo "<td align=\"center\" $class>" . $value['codfis'] . "</td>";
        }
        echo "<td align=\"center\" $class>" . $value['quadro'] . "</td>";
        echo "<td align=\"right\" $class>$totale</td>";
        echo "<td align=\"right\" $class>" . gaz_format_number($value['imposte_addebitate']) . "</td>";
        echo "</tr>\n";
        if (isset($error[$key])) {
            foreach ($error[$key] as $val_err) {
                echo "<tr>";
                echo "<td class=\"FacetDataTDred\" colspan=\"10\">" . $val_err;
                if (substr($value['clfoco'], 0, 3) == $admin_aziend['mascli']) {
                    echo ", <a href='../vendit/admin_client";
                } else {
                    echo ", <a href='../acquis/admin_fornit";
                }
                echo ".php?codice=" . substr($value['clfoco'], 3, 6) . "&Update' target='_NEW'>" . $script_transl['errors'][0] . "</a><br /></td>
                               </tr>\n";
            }
        }
    }
}

function getHeaderData() {
    global $admin_aziend, $gTables;
    // preparo il nome dell'azienda e faccio i controlli di errore
    $Testa['anno'] = intval($_GET['anno']);
    $Testa['pariva'] = $admin_aziend['pariva'];
    $Testa['codfis'] = $admin_aziend['codfis'];
    $Testa['ateco'] = $admin_aziend['cod_ateco'];
    $Testa['e_mail'] = $admin_aziend['e_mail'];
    $Testa['telefono'] = filter_var($admin_aziend['telefo'], FILTER_SANITIZE_NUMBER_INT);
    $Testa['fax'] = filter_var($admin_aziend['fax'], FILTER_SANITIZE_NUMBER_INT);
    // aggiungo l'eventuale intermediario in caso di installazione "da commercialista"
    $intermediary_code = gaz_dbi_get_row($gTables['config'], 'variable', 'intermediary');
    if ($intermediary_code['cvalue'] > 0) {
        $intermediary = gaz_dbi_get_row($gTables['aziend'], 'codice', $intermediary_code['cvalue']);
        $Testa['intermediario'] = $intermediary['codfis'];
    } else {
        $Testa['intermediario'] = '';
    }

    if ($admin_aziend['sexper'] == 'G') {
        // persona giuridica
        if (strlen($Testa['codfis']) <> 11) {
            $Testa['fatal_error'] = 'codfis';
        }
        if (empty($admin_aziend['ragso1']) and empty($admin_aziend['ragso2'])) {
            $Testa['fatal_error'] = 'ragsoc';
        } else {
            $Testa['ragsoc'] = strtoupper($admin_aziend['ragso1'] . " " . $admin_aziend['ragso2']);
        }
        if (empty($admin_aziend['citspe'])) {
            $Testa['fatal_error'] = 'citspe';
        } else {
            $Testa['sedleg'] = strtoupper($admin_aziend['citspe']);
        }
        if (strlen(trim($admin_aziend['prospe'])) < 2) {
            $Testa['fatal_error'] = 'prospe';
        } else {
            $Testa['proleg'] = strtoupper($admin_aziend['prospe']);
        }
    } elseif ($admin_aziend['sexper'] == 'F' or $admin_aziend['sexper'] == 'M') {
        // persona fisica
        $gn = substr($Testa['codfis'], 9, 2);
        if (($admin_aziend['sexper'] == 'M' and ( $gn < 1 or $gn > 31))
                or ( $admin_aziend['sexper'] == 'F' and ( $gn < 41 or $gn > 71))) {
            $Testa['fatal_error'] = 'sexper';
        }
        $Testa['sesso'] = strtoupper($admin_aziend['sexper']);
        if (!empty($admin_aziend['legrap'])) {
            // persona fisica con cognome e nome non separati nel campo legale rappresentante
            $Testa['cognome'] = '';
            $Testa['nome'] = '';
            $line = strtoupper($admin_aziend['legrap']);
            $nuova = explode(' ', chop($line));
            $lenght = count($nuova);
            $middle = intval(($lenght + 1) / 2);
            for ($i = 0; $i < $lenght; $i++) {
                if ($i < $middle) {
                    $Testa['cognome'] .= $nuova[$i] . " ";
                } else {
                    $Testa['nome'] .= $nuova[$i] . " ";
                }
            }
        } elseif (!empty($admin_aziend['ragso1']) and ! empty($admin_aziend['ragso2'])) {
            // persona fisica con cognome e nome separati tra ragso1 e ragso2
            $Testa['cognome'] = strtoupper($admin_aziend['ragso1']);
            $Testa['nome'] = strtoupper($admin_aziend['ragso2']);
        } else {
            $Testa['fatal_error'] = 'legrap';
        }
        if (empty($admin_aziend['luonas'])) {
            $Testa['fatal_error'] = 'luonas';
        } else {
            $Testa['luonas'] = strtoupper($admin_aziend['luonas']);
        }
        if (strlen(trim($admin_aziend['pronas'])) < 2) {
            $Testa['fatal_error'] = 'pronas';
        } else {
            $Testa['pronas'] = strtoupper($admin_aziend['pronas']);
        }
        $d = substr($admin_aziend['datnas'], 8, 2);
        $m = substr($admin_aziend['datnas'], 5, 2);
        $Y = substr($admin_aziend['datnas'], 0, 4);
        if (checkdate($m, $d, $Y)) {
            $Testa['datnas'] = $admin_aziend['datnas'];
        } else {
            $Testa['fatal_error'] = 'datnas';
        }
    } else {
        $Testa['fatal_error'] = 'nosexper';
    }
    return $Testa;
}

function createRowsAndErrors($min_limit) {
    global $gTables, $admin_aziend, $script_transl;
    $nuw = new check_VATno_TAXcode();
    $sqlquery = "SELECT " . $gTables['rigmoi'] . ".*, ragso1,ragso2,sedleg,sexper,indspe,regiva,allegato,
               citspe,prospe,country,codfis,pariva," . $gTables['tesmov'] . ".clfoco," . $gTables['tesmov'] . ".protoc,
               " . $gTables['tesmov'] . ".numdoc," . $gTables['tesmov'] . ".datdoc," . $gTables['tesmov'] . ".seziva,
               " . $gTables['tesmov'] . ".caucon," . $gTables['tesdoc'] . ".numfat AS n_fatt,
			   datreg,datnas,luonas,pronas,counas,id_doc,iso,black_list,cod_agenzia_entrate,
               operat, impost AS imposta," . $gTables['rigmoi'] . ".id_tes AS idtes,
               imponi AS imponibile FROM " . $gTables['rigmoi'] . "
               LEFT JOIN " . $gTables['tesmov'] . " ON " . $gTables['rigmoi'] . ".id_tes = " . $gTables['tesmov'] . ".id_tes
               LEFT JOIN " . $gTables['tesdoc'] . " ON " . $gTables['tesmov'] . ".id_doc = " . $gTables['tesdoc'] . ".id_tes
               LEFT JOIN " . $gTables['aliiva'] . " ON " . $gTables['rigmoi'] . ".codiva = " . $gTables['aliiva'] . ".codice
               LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesmov'] . ".clfoco = " . $gTables['clfoco'] . ".codice
               LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['anagra'] . ".id = " . $gTables['clfoco'] . ".id_anagra
               LEFT JOIN " . $gTables['country'] . " ON " . $gTables['anagra'] . ".country = " . $gTables['country'] . ".iso
               WHERE YEAR(datreg) = " . intval($_GET['anno']) . "
                 AND ( " . $gTables['tesmov'] . ".clfoco LIKE '" . $admin_aziend['masfor'] . "%' OR " . $gTables['tesmov'] . ".clfoco LIKE '" . $admin_aziend['mascli'] . "%')
                 AND " . $gTables['clfoco'] . ".allegato > 0 
               ORDER BY regiva,operat,country,datreg,seziva,protoc";
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
                    if (!empty($row['datnas'])) { // È un persona fisica straniera
                        if (empty($row['pronas']) || empty($row['luonas']) || empty($row['counas'])) {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][9];
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
                if ($row['country'] <> $admin_aziend['country']) {
                    // NON RESIDENTE
                    $castel_transact[$row['idtes']]['istat_country'] = $row['country'];
                    $castel_transact[$row['idtes']]['cod_ade'] = $row['cod_agenzia_entrate'];
                    $castel_transact[$row['idtes']]['quadro'] = 'FN';
                } else {
                    if ($row['regiva'] == 4 && (!empty($row['n_fatt']))) { // se è un documento allegato ad uno scontrino utilizzo il numero fattura in tesdoc
                        $castel_transact[$row['idtes']]['numdoc'] = $row['n_fatt'] . ' scontr.n.' . $row['numdoc'];
                        $castel_transact[$row['idtes']]['seziva'] = '';
                    }
                    if ($row['pariva'] > 0) {
                        // RESIDENTE con partita IVA
                        if ($row['regiva'] < 6) { // VENDITE - Fatture Emesse o Note Emesse
                            if ($row['operat'] == 1) { // Fattura
                                $castel_transact[$row['idtes']]['quadro'] = 'FE';
                            } else {                // Note
                                $castel_transact[$row['idtes']]['quadro'] = 'NE';
                            }
                        } else {                // ACQUISTI - Fatture Ricevute o Note Ricevute
                            if ($row['operat'] == 1) { // Fattura
                                $castel_transact[$row['idtes']]['quadro'] = 'FR';
                            } else {                // Note
                                $castel_transact[$row['idtes']]['quadro'] = 'NR';
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
                                } else {                // Note
                                    $castel_transact[$row['idtes']]['quadro'] = 'NE';
                                }
                            } else {                // ACQUISTI - Fatture Ricevute o Note Ricevute
                                // nei quadri FR NR è possibile indicare la sola partita iva
                                $castel_transact[$row['idtes']]['pariva'] = $castel_transact[$row['idtes']]['codfis'];
                                $castel_transact[$row['idtes']]['codfis'] = 0;
                                if ($row['operat'] == 1) { // Fattura
                                    $castel_transact[$row['idtes']]['quadro'] = 'FR';
                                } else {                // Note
                                    $castel_transact[$row['idtes']]['quadro'] = 'NR';
                                }
                            }
                        } elseif (empty($resultcf) && strlen($row['codfis']) == 16) { // privato servito con fattura
                            if ($row['operat'] == 1) { // Fattura
                                $castel_transact[$row['idtes']]['quadro'] = 'FE';
                            } else {                // Note
                                $castel_transact[$row['idtes']]['quadro'] = 'NE';
                            }
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

if (isset($_GET['file_agenzia'])) {
    $year = intval($_GET['anno']);
    $queryData = createRowsAndErrors(intval($_GET['min_limit']));
    require("../../library/include/agenzia_entrate.inc.php");
    $annofornitura = date("y");
    // --- preparo gli array da passare alla classe AgenziaEntrate a secondo della scelta effettuata
    $Testa = getHeaderData();
    $agenzia = new AgenziaEntrate;

    // Impostazione degli header per l'opozione "save as" dello standard input che verrà generato
    header('Content-Type: text/x-art21');
    header("Content-Disposition: attachment; filename=" . $admin_aziend['codfis'] . $year . "NSP00.nsp");
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // per poter ripetere l'operazione di back-up più volte.
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Pragma: no-cache');
    }
    if ($year > 2011) {
        $content = $agenzia->creaFileART21_poli($Testa, $queryData[0]);
    } else {
        $content = $agenzia->creaFileART21($Testa, $queryData[0]);
    }
    print $content;
    exit;
}

function getPreviousCredit($date) {
    global $gTables, $admin_aziend;
    $rs_last_opening = gaz_dbi_dyn_query("*", $gTables['tesmov'], "caucon = 'APE' AND datreg <= " . $date, "datreg DESC", 0, 1);
    $last_opening = gaz_dbi_fetch_array($rs_last_opening);
    if ($last_opening) {
        $date_ini = substr($last_opening['datreg'], 0, 4) . substr($last_opening['datreg'], 5, 2) . substr($last_opening['datreg'], 8, 2);
    } else {
        $date_ini = '20040101';
    }
    if ($date_ini > $date) {
        $date_ini = '20040101';
    }
    $utsdatera = mktime(0, 0, 0, substr($date, 4, 2) + 2, 0, substr($date, 0, 4));
    $date_era = date("Ymd", $utsdatera);
    $where = "(datreg BETWEEN $date_ini AND $date AND (codcon=" . $admin_aziend['ivaven'] . " OR codcon=" . $admin_aziend['ivacor'] . " OR codcon=" . $admin_aziend['ivaacq'] . "))
                 OR (datreg BETWEEN $date_ini AND $date_era AND codcon=" . $admin_aziend['ivaera'] . ") GROUP BY darave";
    $orderby = " datreg ";
    $select = "darave,SUM(import) AS value";
    $table = $gTables['tesmov'] . " LEFT JOIN " . $gTables['rigmoc'] . " ON " . $gTables['rigmoc'] . ".id_tes = " . $gTables['tesmov'] . ".id_tes ";
    $rs = gaz_dbi_dyn_query($select, $table, $where, $orderby);
    $m = 0;
    while ($r = gaz_dbi_fetch_array($rs)) {
        if ($r['darave'] == 'D') {
            $m+=$r['value'];
        } else {
            $m-=$r['value'];
        }
    }
    $m = round($m, 2);
    if ($m < 0) {
        $m = 0;
    }
    return $m;
}

function getMovements($date_ini, $date_fin) {
    global $gTables, $admin_aziend;
    $where = "datreg BETWEEN $date_ini AND $date_fin GROUP BY seziva,regiva,codiva";
    $orderby = "seziva, regiva, datreg, protoc";
    $rs = gaz_dbi_dyn_query("seziva,regiva,codiva,periva,operat,
                               SUM((imponi*(operat = 1) - imponi*(operat = 2))*(-2*(regiva > 5)+1)) AS imp,
                               SUM((impost*(operat = 1) - impost*(operat = 2))*(-2*(regiva > 5)+1)) AS iva,
                              " . $gTables['aliiva'] . ".descri AS desvat,
                              " . $gTables['aliiva'] . ".tipiva AS tipiva", $gTables['rigmoi'] . " LEFT JOIN " . $gTables['tesmov'] . " ON " . $gTables['rigmoi'] . ".id_tes = " . $gTables['tesmov'] . ".id_tes
        LEFT JOIN " . $gTables['aliiva'] . " ON " . $gTables['rigmoi'] . ".codiva = " . $gTables['aliiva'] . ".codice", $where, $orderby);
    $m = array();
    $m['tot'] = 0;
    while ($r = gaz_dbi_fetch_array($rs)) {
        if ($r['tipiva'] == 'D') { // iva indetraibile
            $r['isp'] = 0;
            $r['ind'] = $r['iva'];
            $r['iva'] = 0;
        } elseif ($r['tipiva'] == 'T') { // iva split payment
            $r['isp'] = $r['iva'];
            $r['ind'] = 0;
            $r['iva'] = 0;
        } else { // iva normale
            $r['ind'] = 0;
            $r['isp'] = 0;
        }
        $m['data'][] = $r;
        if (!isset($m['tot_rate'][$r['codiva']])) {
            $m['tot_rate'][$r['codiva']] = $r;
        } else {
            $m['tot_rate'][$r['codiva']]['imp']+=$r['imp'];
            $m['tot_rate'][$r['codiva']]['iva']+=$r['iva'];
            $m['tot_rate'][$r['codiva']]['ind']+=$r['ind'];
            $m['tot_rate'][$r['codiva']]['isp']+=$r['isp'];
        }
        $m['tot']+=$r['iva'];
    }
    return $m;
}

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    require("lang." . $admin_aziend['lang'] . ".php");
    $form['descri'] = $strScript[$scriptname]['descri_value'][$admin_aziend['ivam_t']];
    if (date("m") >= 1 and date("m") < 4) {
        $utsdatini = mktime(0, 0, 0, 10, 1, date("Y") - 1);
        $utsdatfin = mktime(0, 0, 0, 12, 31, date("Y") - 1);
    } elseif (date("m") >= 4 and date("m") < 7) {
        $utsdatini = mktime(0, 0, 0, 1, 1, date("Y"));
        $utsdatfin = mktime(0, 0, 0, 3, 31, date("Y"));
    } elseif (date("m") >= 7 and date("m") < 10) {
        $utsdatini = mktime(0, 0, 0, 4, 1, date("Y"));
        $utsdatfin = mktime(0, 0, 0, 6, 31, date("Y"));
    } else {  // <=10 e <=12
        $utsdatini = mktime(0, 0, 0, 7, 1, date("Y"));
        $utsdatfin = mktime(0, 0, 0, 9, 30, date("Y"));
    }
    if ($admin_aziend['ivam_t'] == 'M') {
        $form['descri'].=ucwords(strftime("%B %Y", $utsdatini));
    } else {
        $form['descri'].=ucwords(strftime("%B", $utsdatini)) . " - " . ucwords(strftime("%B %Y", $utsdatfin));
    }
    $Testa = getHeaderData();
    $form['min_limit'] = 2950.81;
    if ($admin_aziend['sexper'] != 'G') { // le persone fisiche hanno due campi separati
        $form['ragso1'] = $Testa['cognome'];
        $form['ragso2'] = $Testa['nome'];
    } else {
        $form['ragso1'] = strtoupper($admin_aziend['ragso1']);
        $form['ragso2'] = strtoupper($admin_aziend['ragso2']);
    }
    $form['datini'] = date("01/m/Y", $utsdatini);
    $form['datfin'] = date("d/m/Y", $utsdatfin);
} else { // accessi successivi
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    $form['ritorno'] = $_POST['ritorno'];
    $form['min_limit'] = floatval($_POST['min_limit']);
    $form['ragso1'] = strtoupper(substr($_POST['ragso1'], 0, 50));
    $form['ragso2'] = strtoupper(substr($_POST['ragso2'], 0, 50));
    $form['datini'] = substr($_POST['datini'], 0, 10);
    $form['datfin'] = substr($_POST['datfin'], 0, 10);
    if ($form['hidden_req'] == 'vat_reg' || $form['hidden_req'] == 'vat_section') {
        require("lang." . $admin_aziend['lang'] . ".php");
        $form['descri'] = $strScript[$scriptname]['descri_value'][$admin_aziend['ivam_t']];
        if ($admin_aziend['ivam_t'] == 'M') {
            $form['descri'].=ucwords(strftime("%B %Y", mktime(0, 0, 0, $form['date_ini_M'], $form['date_ini_D'], $form['date_ini_Y'])));
        } else {
            $form['descri'].=ucwords(strftime("%B", mktime(0, 0, 0, $form['date_ini_M'], $form['date_ini_D'], $form['date_ini_Y']))) . " - " . ucwords(strftime("%B %Y", mktime(0, 0, 0, $form['date_fin_M'], $form['date_fin_D'], $form['date_fin_Y'])));
        }
        $form['hidden_req'] = '';
    } elseif ($form['hidden_req'] == 'date_fin') {
        $form['hidden_req'] = '';
    } else {
        $form['hidden_req'] = '';
    }
    if (isset($_POST['return'])) {
        header("Location: " . $form['ritorno']);
        exit;
    }
}

//inizio controllo campi
$datini = DateTime::createFromFormat('d/m/Y', $form['datini']);
$datfin = DateTime::createFromFormat('d/m/Y', $form['datfin']);
if (empty($form['ragso1'])) {
    $msg['err'][] = 'ragso1';
}
if (empty($form['ragso2']) && $admin_aziend['sexper'] != 'G') {
    $msg['err'][] = 'ragso2';
}
if ($datini > $datfin) {
    $msg['err'][] = 'date';
}
// fine controlli


require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new contabForm();
?>
<script type="text/javascript">
    $(function () {
        $("#datini").datepicker();
        $("#datini").change(function () {
            this.form.submit();
        });
        $("#datfin").datepicker();
        $("#datfin").change(function () {
            this.form.submit();
        });
    });
</script>
<?php
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
?>

<form method="POST" name="form" enctype="multipart/form-data">
    <input type="hidden" name="ritorno" value="<?php echo $form['ritorno'] ?>">
    <input type="hidden" name="hidden_req" value="<?php echo $form['hidden_req'] ?>">
    <div class="text-center"><b><?php echo $script_transl['title']; ?></b></div>
    <div class="panel panel-default gaz-table-form">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="min_limit" class="col-sm-4 control-label"><?php echo $script_transl['min_limit']; ?></label>
                        <input class="col-sm-7"  step="0.01" type="number" value="<?php echo $form['min_limit']; ?>" name="min_limit" />
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ragso1" class="col-sm-4 control-label"><?php echo $script_transl['ragso1']; ?></label>
                        <input class="col-sm-8" type="text" value="<?php echo $form['ragso1']; ?>" name="ragso1" />
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ragso2" class="col-sm-4 control-label"><?php echo $script_transl['ragso2']; ?></label>
                        <input class="col-sm-8" type="text" value="<?php echo $form['ragso2']; ?>" name="ragso2" />
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="datini" class="col-sm-4 control-label"><?php echo $script_transl['datini']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="datini" name="datini" value="<?php echo $form['datini']; ?>">
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="datfin" class="col-sm-4 control-label"><?php echo $script_transl['datfin']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="datfin" name="datfin" value="<?php echo $form['datfin']; ?>">
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <?php
            if (isset($_POST['preview']) and $msg == '') {
                $date_ini = sprintf("%04d%02d%02d", $form['date_ini_Y'], $form['date_ini_M'], $form['date_ini_D']);
                $date_fin = sprintf("%04d%02d%02d", $form['date_fin_Y'], $form['date_fin_M'], $form['date_fin_D']);
                $m = getMovements($date_ini, $date_fin);
                echo "<table class=\"Tlarge\">";
                if (sizeof($m['data']) > 0) {
                    $err = 0;
                    echo "<tr>";
                    $linkHeaders = new linkHeaders($script_transl['header']);
                    $linkHeaders->output();
                    echo "</tr>\n";
                    foreach ($m['data'] as $k => $v) {
                        echo "<tr align=\"right\">\n";
                        echo "<td>" . $v['seziva'] . "</td><td align=\"center\">" . $script_transl['regiva_value'][$v['regiva']] . "</td><td>" . $v['desvat'] . "</td><td>" . gaz_format_number($v['imp']) . "</td>";
                        echo "<td>" . $v['periva'] . "% </td><td>" . gaz_format_number($v['iva']) . "</td><td>" . gaz_format_number($v['ind']) . "</td>\n";
                        echo "<td>" . gaz_format_number($v['ind'] + $v['imp'] + $v['iva'] + $v['isp']) . "</td>\n";
                        echo "</tr>\n";
                    }
                    echo "<tr><td colspan=8><HR></td></tr>";
                    foreach ($m['tot_rate'] as $k => $v) {
                        echo "<tr align=\"right\">\n";
                        echo "<td colspan=\"2\"></td><td>" . $v['desvat'] . "</td><td>" . gaz_format_number($v['imp']) . "</td>";
                        echo "<td>" . $v['periva'] . "% </td><td>" . gaz_format_number($v['iva']) . "</td><td>" . gaz_format_number($v['ind']) . "</td>\n";
                        echo "<td>" . gaz_format_number($v['ind'] + $v['imp'] + $v['iva'] + $v['isp']) . "</td>\n";
                        echo "</tr>\n";
                    }
                    echo "<tr><td colspan=2></td><td colspan=6><HR></td></tr>";
                    if ($m['tot'] < 0) {
                        echo "<tr><td colspan=2></td><td class=\"FacetDataTDred\" align=\"right\" colspan=3>" . $script_transl['tot'] . $script_transl['t_neg'] . "</td><td class=\"FacetDataTDred\" align=\"right\">" . gaz_format_number($m['tot']) . "</td></tr>";
                    } else {
                        echo "<tr><td colspan=2></td><td class=\"FacetDataTD\" align=\"right\" colspan=3>" . $script_transl['tot'] . $script_transl['t_pos'] . "</td><td class=\"FacetDataTD\" align=\"right\">" . gaz_format_number($m['tot']) . "</td></tr>";
                    }
                    if ($err == 0) {
                        echo "\t<tr class=\"FacetFieldCaptionTD\">\n";
                        echo '<td colspan="7" align="right"><input type="submit" name="print" value="';
                        echo $script_transl['print'];
                        echo '">';
                        echo "\t </td>\n";
                        echo "\t </tr>\n";
                    } else {
                        echo "<tr>";
                        echo "<td colspan=\"7\" align=\"right\" class=\"FacetDataTDred\">" . $script_transl['errors']['err'] . "</td>";
                        echo "</tr>\n";
                    }
                }
                echo "</table>\n";
            }
            ?>
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
</form>
</div><!-- chiude div container role main --></body>
</html>