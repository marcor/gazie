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
$msg = array('err' => array(), 'war' => array());

function lastAccount($mas, $ss) {
    /* funzione per trovare i numeri dei nuovi sottoconto da creare sui mastri 
     * scelti per le immobilizzazioni, i fondi e i costi d'ammortamento dove i 
     * due numeri successivi indicano la sottospecie della tabella ministeriale 
     * degli ammortamenti e i restanti 4 (9999) sono attribuiti automaticamente 
     * al singolo bene da questa funzione                                     */
    global $gTables;
    $subacc = $mas * 1000000 + $ss * 10000;
    $rs_last_subacc = gaz_dbi_dyn_query("*", $gTables['clfoco'], "codice BETWEEN " . $subacc . " AND " . intval($subacc + 9999), "codice DESC", 0, 1);
    $last_subacc = gaz_dbi_fetch_array($rs_last_subacc);
    if ($last_subacc) {
        return $last_subacc['codice'] + 1;
    } else {
        return $subacc + 1;
    }
}

if (isset($_GET['Update']) && !isset($_GET['id'])) {
    header("Location: " . $form['ritorno']);
    exit;
}

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) || ( isset($_POST['Update']))) {   //se non e' il primo accesso
//qui si dovrebbe fare un parsing di quanto arriva dal browser...
    $form['id_movcon'] = intval($_POST['id_movcon']);
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner(intval($_POST['clfoco']));
    $form['hidden_req'] = filter_input(INPUT_POST, 'hidden_req');
// ...e della testata
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }
    $form['seziva'] = intval($_POST['seziva']);
    $form['codvat'] = intval($_POST['codvat']);
    $form['datfat'] = substr($_POST['datfat'], 0, 10);
    $form['datreg'] = substr($_POST['datreg'], 0, 10);
    $form['numfat'] = substr($_POST['numfat'], 0, 40);
    $form['clfoco'] = intval($_POST['clfoco']);
    $form['mas_fixed_assets'] = substr($_POST['mas_fixed_assets'], 0, 3);
    $form['mas_found_assets'] = substr($_POST['mas_found_assets'], 0, 3);
    $form['mas_cost_assets'] = substr($_POST['mas_cost_assets'], 0, 3);
    $form['id_no_deduct_vat'] = intval($_POST['id_no_deduct_vat']);
    $form['no_deduct_vat_rate'] = floatval($_POST['no_deduct_vat_rate']);
    $form['acc_no_deduct_cost'] = intval($_POST['acc_no_deduct_cost']);
    $form['no_deduct_cost_rate'] = floatval($_POST['no_deduct_cost_rate']);
    $form['super_ammort'] = floatval($_POST['super_ammort']);
    $form['type_mov'] = intval($_POST['type_mov']);
    $form['descri'] = filter_input(INPUT_POST, 'descri');
    $form['unimis'] = filter_input(INPUT_POST, 'unimis');
    $form['quantity'] = floatval($_POST['quantity']);
    $form['a_value'] = floatval($_POST['a_value']);
    $form['ss_amm_min'] = intval($_POST['ss_amm_min']);
    $form['pagame'] = intval($_POST['pagame']);
    $form['change_pag'] = $_POST['change_pag'];
    if ($form['change_pag'] != $form['pagame']) {  //se è stato cambiato il pagamento
        $new_pag = gaz_dbi_get_row($gTables['pagame'], "codice", $form['pagame']);
        $old_pag = gaz_dbi_get_row($gTables['pagame'], "codice", $form['change_pag']);
        if (($new_pag['tippag'] == 'B' or $new_pag['tippag'] == 'T' or $new_pag['tippag'] == 'V')
                and ( $old_pag['tippag'] == 'C' or $old_pag['tippag'] == 'D')) { // se adesso devo mettere le spese e prima no
            $form['numrat'] = $new_pag['numrat'];
            if ($toDo == 'update') {  //se è una modifica mi baso sulle vecchie spese
                $old_header = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", $form['id_movcon']);
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
    $form['valamm'] = floatval($_POST['valamm']);

    if ($form['valamm'] < 0.1 || $form['valamm'] > 100) {
        // limito a valori reali
        $form['valamm'] = 0.00;
    }
// Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
        $utsfat = gaz_format_date($form['datfat'], 3);
        $utsreg = gaz_format_date($form['datreg'], 3);
        if ($utsreg < $utsfat) {
            $msg['err'][] = 'regdat';
        }
        if (empty($form['numfat'])) {
            $msg['err'][] = 'numfat';
        }
// --- inizio controlli
        if ($toDo == 'update') {  // controlli in caso di modifica
        } else {                   //controlli in caso di inserimento
        }
        if ($form["clfoco"] < 100000001)
            $msg['err'][] = 'clfoco';
        if (!gaz_format_date($form["datreg"], 'chk'))
            $msg['err'][] = 'datreg';
        if (!gaz_format_date($form["datfat"], 'chk'))
            $msg['err'][] = 'datfat';
        if (empty($form["pagame"]))
            $msg['err'][] = 'pagame';
        if ($form["mas_fixed_assets"] < 100)
            $msg['err'][] = 'mas_fixed_assets';
        if ($form["mas_found_assets"] < 100)
            $msg['err'][] = 'mas_found_assets';
        if ($form["mas_cost_assets"] < 100)
            $msg['err'][] = 'mas_cost_assets';
        if (empty($form["descri"]))
            $msg['err'][] = 'descri';
        if ($form["no_deduct_cost_rate"] >= 0.01 && $form["acc_no_deduct_cost"] < 100000000)
            $msg['err'][] = 'deduct_cost';
        if ($form["no_deduct_vat_rate"] >= 0.01 && $form["id_no_deduct_vat"] < 1)
            $msg['err'][] = 'deduct_vat';
        if ($form["ss_amm_min"] >= 100)
            $msg['err'][] = 'ss_amm_min';
// --- fine controlli
        if (count($msg['err']) == 0) {// nessun errore
            if ($toDo == 'update') { // e' una modifica
                gaz_dbi_table_update('assets',array('id',intval($_GET['id'])), $form);
                header("Location: ../finann/report_assets.php");
                exit;
            } else { // e' un'inserimento
                $year = substr($form['datreg'], 6, 4);
                $descri = $form['descri'];
                // ricavo il protocollo da assegnare all'acquisto
                $rs_ultimo_tesdoc = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = $year AND tipdoc LIKE 'AF_' AND seziva = " . $form['seziva'], "protoc DESC", 0, 1);
                $ultimo_tesdoc = gaz_dbi_fetch_array($rs_ultimo_tesdoc);
                $rs_ultimo_tesmov = gaz_dbi_dyn_query("*", $gTables['tesmov'], "YEAR(datreg) = $year AND regiva = 6 AND seziva = " . $form['seziva'], "protoc DESC", 0, 1);
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
                $lastProtocol++;
                // testata movimento contabile
                $form['caucon'] = 'AFA';
                $form['descri'] = 'FATTURA DI ACQUISTO';
                $form['regiva'] = 6;
                $form['operat'] = 1;
                $form['protoc'] = $lastProtocol;
                $form['numdoc'] = $form['numfat'];

                $form['datreg'] = gaz_format_date($form['datreg'], true);
                $form['datdoc'] = gaz_format_date($form['datfat'], true);
                gaz_dbi_table_insert('tesmov', $form);
                $id_tesmov = gaz_dbi_last_id();
                $form['id_tes'] = $id_tesmov;
                $form['id_movcon'] = $id_tesmov;
                // trovo il conto immobilizzazione 
                $form['acc_fixed_assets'] = lastAccount($form['mas_fixed_assets'], $form['ss_amm_min']);
                // trovo il conto fondo ammortamento 
                $form['acc_found_assets'] = lastAccount($form['mas_found_assets'], $form['ss_amm_min']);
                // trovo il conto costo ammortamento 
                $form['acc_cost_assets'] = lastAccount($form['mas_cost_assets'], $form['ss_amm_min']);
                // inserisco i dati sulla tabella assets
                $form['descri'] = $descri;
                $form['type_mov'] = 1; // è un acquisto ,10 rivalutazione, 50 ammortamento, 90 alienazione
                $form['id_assets'] = gaz_dbi_table_insert('assets', $form);


                // ripreno i file di traduzione
                require("./lang." . $admin_aziend['lang'] . ".php");
                $transl = $strScript['admin_assets.php'];
                // creo i tre conti relativi ai mastri scelti
                $form['descri'] = $transl['des_fixed_assets'] . strtolower($descri);
                $form['codice'] = $form['acc_fixed_assets'];
                gaz_dbi_table_insert('clfoco', $form);
                $form['descri'] = $transl['des_found_assets'] . strtolower($descri);
                $form['codice'] = $form['acc_found_assets'];
                gaz_dbi_table_insert('clfoco', $form);
                $form['descri'] = $transl['des_cost_assets'] . strtolower($descri);
                $form['codice'] = $form['acc_cost_assets'];
                gaz_dbi_table_insert('clfoco', $form);
                // recupero i dati iva ed eseguo i calcoli
                $iva = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['codvat']);
                $form['no_imponi'] = 0;
                $form['no_impost'] = 0;
                if ($form['id_no_deduct_vat'] > 0) { // ho una parte di iva indetraibile che si andrà a sommare ai costi
                    // per i righi iva
                    $no_iva = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['id_no_deduct_vat']);
                    $form['no_imponi'] = round($form['quantity'] * $form['a_value'] * $form['no_deduct_vat_rate'] / 100, 2);
                    $form['no_impost'] = round($form['no_imponi'] * $no_iva['aliquo'] / 100, 2);
                    $form['imponi'] = round($form['quantity'] * $form['a_value'] - $form['no_imponi'], 2);
                    $form['impost'] = round($form['imponi'] * $iva['aliquo'] / 100, 2);
                    // per i righi contabili
                    $form['import'] = $form['imponi'] + $form['impost'] + $form['no_imponi'] + $form['no_impost'];
                } else {
                    $form['imponi'] = round($form['quantity'] * $form['a_value'], 2);
                    $form['impost'] = round($form['imponi'] * $iva['aliquo'] / 100, 2);
                    $form['import'] = $form['imponi'] + $form['impost'];
                }
                $import = $form['import'];
                // rigo conto fornitore con importo totale
                $form['codcon'] = $form['clfoco'];
                $form['darave'] = 'A';
                gaz_dbi_table_insert('rigmoc', $form);
                $last_id_rig = gaz_dbi_last_id();
                // inserisco lo scadenzario
                $pagame = gaz_dbi_get_row($gTables['pagame'], 'codice', $form['pagame']);
                require("../../library/include/expiry_calc.php");
                $ex = new Expiry;
                $rs_ex = $ex->CalcExpiry($import, gaz_format_date($form['datfat'], true), $pagame['tipdec'], $pagame['giodec'], $pagame['numrat'], $pagame['tiprat'], $pagame['mesesc'], $pagame['giosuc']);
                foreach ($rs_ex as $k => $v) {
                    $paymov_value = array('id_tesdoc_ref' => $year . '6' . $form['seziva'] . str_pad($form['protoc'], 9, 0, STR_PAD_LEFT),
                        'id_rigmoc_doc' => $last_id_rig,
                        'amount' => $v['amount'],
                        'expiry' => $v['date']);
                    paymovInsert($paymov_value);
                }
                // rigo conto immobilizzazione
                $form['codcon'] = $form['acc_fixed_assets'];
                $form['darave'] = 'D';
                // agli imponibili si dovrà sommare anche l'eventuale iva indetraibile (che diventa costo storico)
                $form['import'] = $form['imponi'] + $form['no_imponi'] + $form['no_impost'];
                gaz_dbi_table_insert('rigmoc', $form);
                // rigo iva 
                $form['codiva'] = $form['codvat'];
                $form['periva'] = $iva['aliquo'];
                $form['tipiva'] = $iva['tipiva'];
                $form['operation_type'] = 'BENAMM';
                gaz_dbi_table_insert('rigmoi', $form);
                //e rigo conto imposta
                $form['codcon'] = $admin_aziend['ivaacq'];
                $form['import'] = $form['impost'];
                gaz_dbi_table_insert('rigmoc', $form);
                if ($form['id_no_deduct_vat'] > 0) { // ho iva indetraibile che genererà un apposito rigo iva
                    // rigo iva indetraibile
                    $form['imponi'] = $form['no_imponi'];
                    $form['impost'] = $form['no_impost'];
                    $form['codiva'] = $form['id_no_deduct_vat'];
                    $form['periva'] = $no_iva['aliquo'];
                    $form['tipiva'] = $no_iva['tipiva'];
                    gaz_dbi_table_insert('rigmoi', $form);
                }
				// lo inserisco anche come articolo (in futuro ho intenzione di automatizzare la rivendita)
				$form['codice']='ASSET_'.$form['id_assets'];
				gaz_dbi_put_row($gTables['assets'], 'id', $form['id_assets'], 'codice_artico', $form['codice']);
				$form['descri'] = ucfirst($descri);
				$form['preacq'] = $form['import'];
                $form['aliiva'] = $form['codvat'];
                $form['uniacq'] = $form['unimis'];
				gaz_dbi_table_insert('artico', $form);

                // vado alla pagina del report sul modulo Fine Anno (finann)
                header("Location: ../finann/report_assets.php");
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
        $form['in_codvat'] = $fornitore['aliiva'];
        $form['pagame'] = $fornitore['codpag'];
        $form['change_pag'] = $fornitore['codpag'];
        $form['hidden_req'] = '';
    }
} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $form = gaz_dbi_get_row($gTables['assets'], "id", intval($_GET['id']));
    // recupero i dati iva ed eseguo i calcoli
    $tesmov = gaz_dbi_get_row($gTables['tesmov'], "id_tes", $form['id_movcon']);
    // è un acquisto (type_mov=1) quindi id_movcon contiene la testata del movimento contabile, in altri casi contiene il'id_rig
    $rigmoi = gaz_dbi_get_row($gTables['rigmoi'], "tipiva ='I' AND id_tes", $form['id_movcon']);
    $iva = gaz_dbi_get_row($gTables['aliiva'], "codice", $rigmoi['codiva']);
    $rigmoi_no = gaz_dbi_get_row($gTables['rigmoi'], "tipiva ='D' AND id_tes", $form['id_movcon']);
    $iva_no = gaz_dbi_get_row($gTables['aliiva'], "codice", $rigmoi_no['codiva']);
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner($tesmov['clfoco']);
    $form['hidden_req'] = '';
    $form['clfoco'] = $tesmov['clfoco'];
    $form['search']['clfoco'] = substr($fornitore['ragso1'], 0, 10);
    $form['seziva'] = $tesmov['seziva'];
    $form['codvat'] = $rigmoi['codiva'];
    $form['mas_fixed_assets'] = substr($form['acc_fixed_assets'], 0, 3);
    $form['mas_found_assets'] = substr($form['acc_found_assets'], 0, 3);
    $form['mas_cost_assets'] = substr($form['acc_cost_assets'], 0, 3);
    $form['id_no_deduct_vat'] = $rigmoi_no['codiva'];
    $form['datreg'] = gaz_format_date($tesmov['datreg'],false,false);
    $form['protoc'] = $tesmov['protoc'];
    $form['numfat'] = $tesmov['numdoc'];
    $form['datfat'] = gaz_format_date($tesmov['datdoc'],false,false);
    $form['change_pag'] = $form['pagame'];
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['hidden_req'] = '';
    $form['id_movcon'] = "";
    // ricerco l'ultimo inserimento per ricavarne la data
    $rs_last = gaz_dbi_dyn_query('datreg', $gTables['tesmov'], 1, "id_tes DESC", 0, 1);
    $last = gaz_dbi_fetch_array($rs_last);
    if ($last) {
        $form['datreg'] = gaz_format_date($last['datreg'], false, true);
    } else {
        $form['datreg'] = date("d/m/Y");
    }
    $form['datfat'] = '';
    $form['search']['clfoco'] = '';
    if (isset($_GET['seziva'])) {
        $form['seziva'] = intval($_GET['seziva']);
    } else {
        $form['seziva'] = 1;
    }
    $form['codvat'] = $admin_aziend['preeminent_vat'];
    $form['protoc'] = 0;
    $form['numfat'] = "";
    $form['clfoco'] = "";
    $form['pagame'] = "";
    $form['change_pag'] = "";
    $form['valamm'] = 0;
    $form['mas_fixed_assets'] = $admin_aziend['mas_fixed_assets'];
    $form['mas_found_assets'] = $admin_aziend['mas_found_assets'];
    $form['mas_cost_assets'] = $admin_aziend['mas_cost_assets'];
    $form['super_ammort'] = $admin_aziend['super_amm_rate'];
    $form['id_no_deduct_vat'] = 0;
    $form['no_deduct_vat_rate'] = 0;
    $form['acc_no_deduct_cost'] = 0;
    $form['no_deduct_cost_rate'] = 0;
    $form['type_mov'] = '';
    $form['descri'] = '';
    $form['unimis'] = 'n';
    $form['quantity'] = 1;
    $form['a_value'] = 0;
    $form['ss_amm_min'] = 999;
    $fornitore['indspe'] = "";
    $fornitore['citspe'] = "";
}
if (isset($_POST['ritorno'])) {
    $form['ritorno'] = $_POST['ritorno'];
} else {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'] . ' ';
}

// ricavo il gruppo e la specie dalla tabella ammortamenti ministeriali 
$xml = simplexml_load_file('../../library/include/ammortamenti_ministeriali.xml') or die("Error: Cannot create object for file ammortamenti ministeriali.xml");
preg_match("/^([0-9 ]+)([a-zA-Z ]+)$/", $admin_aziend['amm_min'], $m);
foreach ($xml->gruppo as $vg) {
    if ($vg->gn[0] == $m[1]) {
        foreach ($vg->specie as $v) {
            if ($v->ns[0] == $m[2]) {
                $amm_gr = $vg->gn[0] . '-' . $vg->gd[0];
                $amm_sp = $v->ns[0] . '-' . $v->ds[0];
                // Se viene scelta o cambiata la voce tabella ammortamenti carico il suo nuovo valore
                if ($form['hidden_req'] == 'ss_amm_min') {
                    $form['valamm'] = $v->ssrate[$form['ss_amm_min']][0];
                    $form['hidden_req'] = '';
                }
            }
        }
    }
}
$amount = CalcolaImportoRigo($form['quantity'], $form['a_value'], 0);
$gg = intval(365 - date("z", gaz_format_date($form['datreg'], 2)));
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/autocomplete'));
?>
<script>
    $(function () {
        function sumVal() {
            var quantity = parseFloat($('#quantity').val());
            var valamm = parseFloat($('#valamm').val());
            var gg = parseFloat($('#gg').val());
            var a_value = parseFloat($('#a_value').val());
            var amount = a_value * quantity;
            var amount_rate = amount * valamm * gg / 36500;
            $("#amount").text(amount.toFixed(2).toString());
            ;
            $("#amount_rate").text(amount_rate.toFixed(2).toString());
            ;
        }
        $("#datreg, #datfat").datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
        $("#datreg").change(function () {
            this.form.submit();
        });
        $('#valamm, #a_value, #quantity').change(function () {
            sumVal();
        });
<?php if ($toDo == 'update') {
    ?>
            $("#datreg,#numfat,#datfat,#mas_fixed_assets,#mas_found_assets,#mas_cost_assets,#codvat,#seziva,#clfoco").prop("disabled", true);
    <?php
}
?>
    });
</script>
<?php
$gForm = new acquisForm();
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
if ($toDo == 'update') { // allerto che le modifiche devono essere fatte anche sul movimento contabile
    $script_transl['war']['update'] .= ' n.<a class="btn btn-xs btn-default" href="../contab/admin_movcon.php?Update&id_tes='.$form['id_movcon'].'" >'.$form['id_movcon'].' <i class="glyphicon glyphicon-edit"></i></a>';
    $gForm->gazHeadMessage(array('update'), $script_transl['war'], 'war');
}
?>
<form class="form-horizontal" role="form" method="post" name="docacq" enctype="multipart/form-data" >
    <input type="hidden" name="<?php echo ucfirst($toDo); ?>" value="">
    <input type="hidden" value="<?php echo $form['hidden_req'] ?>" name="hidden_req" />
    <input type="hidden" value="<?php echo $form['id_movcon']; ?>" name="id_movcon">
    <input type="hidden" value="<?php echo $form['type_mov']; ?>" name="type_mov">
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
    <input type="hidden" value="<?php echo $form['change_pag']; ?>" name="change_pag">
    <input type="hidden" value="<?php echo $gg; ?>" id="gg">
    <div class="text-center">
        <p>
            <b>
                <?php
                echo $script_transl[$toDo] . ' ' . $script_transl['title'] . ':';
                if ($toDo == 'update') {
                    $anagrafica = new Anagrafica();
                    $fornitore= $anagrafica->getPartner($form['clfoco']);
                    echo $fornitore['ragso1'];
                ?>
    <input type="hidden" value="<?php echo $form['clfoco']; ?>" name="clfoco">
    <input type="hidden" value="<?php echo $form['seziva']; ?>" name="seziva">
    <input type="hidden" value="<?php echo $form['codvat']; ?>" name="codvat">
    <input type="hidden" value="<?php echo $form['datreg']; ?>" name="datreg">
    <input type="hidden" value="<?php echo $form['numfat']; ?>" name="numfat">
    <input type="hidden" value="<?php echo $form['datfat']; ?>" name="datfat">
    <input type="hidden" value="<?php echo $form['mas_fixed_assets']; ?>" name="mas_fixed_assets">
    <input type="hidden" value="<?php echo $form['mas_found_assets']; ?>" name="mas_found_assets">
    <input type="hidden" value="<?php echo $form['mas_cost_assets']; ?>" name="mas_cost_assets">
    <input type="hidden" value="<?php echo $form['search']['clfoco']; ?>" name="search[clfoco]">
    
                <?php
                } else {
                    $select_fornitore = new selectPartner("clfoco");
                    $select_fornitore->selectDocPartner('clfoco', $form['clfoco'], $form['search']['clfoco'], 'clfoco', $script_transl['mesg'], $admin_aziend['masfor']);
                }
                ?>
            </b>
        </p>
    </div>
    <div class="panel panel-default">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="indspe" class="col-sm-4 control-label"><?php echo $script_transl['indspe']; ?></label>
                        <div class="col-sm-8 text-left"><?php echo $fornitore['indspe'] . ' ' . $fornitore['citspe']; ?></div>                
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="datreg" class="col-sm-4 control-label"><?php echo $script_transl['datreg']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="datreg" name="datreg" tabindex=7 value="<?php echo $form['datreg']; ?>">
                        </div>
                    </div>
                </div>                    
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="numfat" class="col-sm-4 control-label"><?php echo $script_transl['numfat']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="numfat" name="numfat" maxlength="20" tabindex=8 placeholder="<?php echo $script_transl['numfat']; ?>" value="<?php echo $form['numfat']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="datfat" class="col-sm-4 control-label"><?php echo $script_transl['datfat']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="datfat" name="datfat" placeholder="GG/MM/AAAA" tabindex=9 value="<?php echo $form['datfat']; ?>">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="pagame" class="col-sm-4 control-label" ><?php echo $script_transl['pagame']; ?></label>
                        <div>
                            <?php
                            $select_pagame = new selectpagame("pagame");
                            $select_pagame->addSelected($form["pagame"]);
                            $select_pagame->output(false, "col-sm-8 small");
                            ?>                
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="codvat" class="col-sm-4 control-label"><?php echo $script_transl['codvat']; ?></label>
                        <div>
                            <?php
                            $sel_vat = new selectaliiva("codvat");
                            $sel_vat->addSelected($form["codvat"]);
                            $sel_vat->output("col-sm-8 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="mas_fixed_assets" class="col-sm-4 control-label"><?php echo $script_transl['mas_fixed_assets']; ?></label>
                        <div>
                            <?php
                            $gForm->selectAccount('mas_fixed_assets', $form['mas_fixed_assets'] . '000000', array(1, 9), '', 10, "col-sm-8 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="seziva" class="col-sm-4 control-label"><?php echo $script_transl['seziva']; ?></label>
                        <div class="col-sm-8">
                            <?php $gForm->selectNumber('seziva', $form['seziva'], 0, 1, 9, 'col-sm-8 small'); ?>
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <p class="col-sm-12 small bg-info">
                        <?php echo $amm_gr; ?>
                    </p>
                </div>
                <div class="col-md-12 col-lg-6">
                    <p class="col-sm-12 small bg-info">
                        <?php echo $amm_sp; ?>                  
                    </p>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="ss_amm_min" class="col-sm-4 control-label"><?php echo $script_transl['ss_amm_min']; ?></label>
                        <div>
                            <?php
                            $gForm->selAmmortamentoMin('ammortamenti_ministeriali.xml', 'ss_amm_min', $admin_aziend['amm_min'], $form["ss_amm_min"]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="valamm" class="col-sm-8 control-label"><?php echo $script_transl['valamm']; ?></label>
                        <div class="col-sm-4">
                            <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="valamm" name="valamm" placeholder="<?php echo $script_transl['valamm']; ?>" value="<?php echo $form['valamm']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="mas_found_assets" class="col-sm-4 control-label"><?php echo $script_transl['mas_found_assets']; ?></label>
                        <div>
                            <?php
                            $gForm->selectAccount('mas_found_assets', $form['mas_found_assets'] . '000000', array(2, 9), '', 11, "col-sm-8 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="mas_cost_assets" class="col-sm-4 control-label"><?php echo $script_transl['mas_cost_assets']; ?></label>
                        <div>
                            <?php
                            $gForm->selectAccount('mas_cost_assets', $form['mas_cost_assets'] . '000000', array(3, 9), '', 12, "col-sm-8 small");
                            ?>
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="no_deduct_cost_rate" class="col-sm-6 control-label"><?php echo $script_transl['no_deduct_cost_rate']; ?></label>
                        <div class="col-sm-6">
                            <input type="number" step="0.1" max="100" class="form-control" id="no_deduct_cost_rate" name="no_deduct_cost_rate" placeholder="<?php echo $script_transl['no_deduct_cost_rate']; ?>" value="<?php echo $form['no_deduct_cost_rate']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="acc_no_deduct_cost" class="col-sm-6 control-label"><?php echo $script_transl['acc_no_deduct_cost']; ?></label>
                        <div>
                            <?php
                            $gForm->selectAccount('acc_no_deduct_cost', $form['acc_no_deduct_cost'], 3, '',false, "col-sm-6 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="no_deduct_vat_rate" class="col-sm-8 control-label"><?php echo $script_transl['no_deduct_vat_rate']; ?></label>
                        <div class="col-sm-4">
                            <input type="number" step="0.1" max="100" class="form-control" id="valamm" name="no_deduct_vat_rate" placeholder="<?php echo $script_transl['no_deduct_vat_rate']; ?>" value="<?php echo $form['no_deduct_vat_rate']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="id_no_deduct_vat" class="col-sm-4 control-label"><?php echo $script_transl['id_no_deduct_vat']; ?></label>
                        <div>
                            <?php
                            $sel_vat = new selectaliiva("id_no_deduct_vat");
                            $sel_vat->addSelected($form["id_no_deduct_vat"]);
                            $sel_vat->output("col-sm-8 small", 'D');
                            ?>
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="descri" class="col-sm-4 control-label"><?php echo $script_transl['descri']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="descri" name="descri" maxlenght="100" tabindex=14 placeholder="<?php echo $script_transl['descri']; ?>" value="<?php echo $form['descri']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="unimis" class="col-sm-4 control-label"><?php echo $script_transl['unimis']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="unimis" name="unimis" maxlenght="3" tabindex=15 placeholder="<?php echo $script_transl['unimis']; ?>" value="<?php echo $form['unimis']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="quantity" class="col-sm-4 control-label"><?php echo $script_transl['quantity']; ?></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.1" min="1" class="form-control" id="quantity" name="quantity" tabindex=16 placeholder="<?php echo $script_transl['quantity']; ?>" value="<?php echo $form['quantity']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="a_value" class="col-sm-4 control-label"><?php echo $script_transl['a_value']; ?></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" min="0.01" class="form-control" id="a_value" name="a_value" tabindex=17 placeholder="<?php echo $script_transl['a_value']; ?>" value="<?php echo $form['a_value']; ?>">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="super_ammort" class="col-sm-8 control-label"><?php echo $script_transl['super_ammort']; ?></label>
                        <div class="col-sm-4">
                            <input type="number" step="0.1" min="0.1" max="500" class="form-control" id="super_ammort" name="super_ammort" placeholder="<?php echo $script_transl['super_ammort']; ?>" value="<?php echo $form['super_ammort']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="amount" class="col-sm-8 control-label"><?php echo $script_transl['amount']; ?></label>
                        <div class="col-sm-4 bg-success">
                            <span id="amount" class="text-right">
                                <?php echo round($amount, 2); ?>                  
                            </span>          
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <p class="col-sm-12 small">
                            <?php echo $gg . $script_transl['info']['gg_to_year_end_1']; ?>
                            <span id="yreg" class="text-right"><?php echo substr($form['datreg'], 6, 4) ?></span> 
                            <?php echo $script_transl['info']['gg_to_year_end_2'];
                            ?>
                            <span id="amount_rate">
                                <?php
                                echo gaz_format_number(round($amount * $form['valamm'] * $gg / 36500, 2));
                                ?></span>
                        </p>                
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                    </div>
                </div>
            </div> <!-- chiude row  -->
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
    <div class="panel panel-info">
        <div class="container-fluid">
            <div class="row">
                    <div class="form-group">
                        <div class="col-sm-12 text-center alert-success">
                            <input name="ins" id="preventDuplicate" onClick="chkSubmit();" type="submit" value="<?php echo ucfirst($script_transl[$toDo]); ?>">
                        </div>
                    </div>
            </div> <!-- chiude row  -->
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
</form>
<?php
require("../../library/include/footer.php");
?>