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
$msg = array('err' => array(), 'ale' => array());

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

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) && !isset($_GET['id_tes'])) && !isset($_GET['tipdoc'])) {
    header("Location: " . $form['ritorno']);
    exit;
}

if ((isset($_POST['Update'])) || ( isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) || ( isset($_POST['Update']))) {   //se non e' il primo accesso
//qui si dovrebbe fare un parsing di quanto arriva dal browser...
    $form['id_tes'] = intval($_POST['id_tes']);
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
    $form['descri'] = filter_input(INPUT_POST, 'descri');
    $form['unimis'] = filter_input(INPUT_POST, 'unimis');
    $form['quantity'] = floatval($_POST['quantity']);
    $form['price'] = floatval($_POST['price']);
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
// --- fine controlli
        if (count($msg['err']) == 0) {// nessun errore
            if ($toDo == 'update') { // e' una modifica
                header("Location: " . $form['ritorno']);
                exit;
            } else { // e' un'inserimento
                $year = substr($form['datreg'], 6, 4);
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
                $form['regiva'] = 6;
                $form['operat'] = 1;
                $form['protoc'] = $lastProtocol;
                $form['numdoc'] = $form['numfat'];
                $form['datreg'] = gaz_format_date($form['datreg'], true);
                $form['datdoc'] = gaz_format_date($form['datfat'], true);
                gaz_dbi_table_insert('tesmov', $form);
                $id_tesmov = gaz_dbi_last_id();
                $form['id_tes'] = $id_tesmov;
                // trovo il conto immobilizzazione 
                $form['acc_fixed_assets'] = lastAccount($form['mas_fixed_assets'], $form['ss_amm_min']);
                // trovo il conto fondo ammortamento 
                $form['acc_found_assets'] = lastAccount($form['mas_found_assets'], $form['ss_amm_min']);
                // trovo il conto costo ammortamento 
                $form['acc_cost_assets'] = lastAccount($form['mas_cost_assets'], $form['ss_amm_min']);
                // inserisco i dati sulla tabella assets
                gaz_dbi_table_insert('assets', $form);
                $form['id_assets'] = gaz_dbi_last_id();
                // creo i tre conti relativi ai mastri scelti
                $form['codice'] = $form['acc_fixed_assets'];
                gaz_dbi_table_insert('clfoco', $form);
                $form['codice'] = $form['acc_found_assets'];
                gaz_dbi_table_insert('clfoco', $form);
                $form['codice'] = $form['acc_cost_assets'];
                gaz_dbi_table_insert('clfoco', $form);
                // rigo conto fornitore
                $form['codcon'] = $form['clfoco'];
                $form['darave'] = 'A';
                $iva = gaz_dbi_get_row($gTables['aliiva'], "codice", $form['codvat']);
                $form['imponi'] = round($form['quantity'] * $form['price'], 2);
                $form['impost'] = round($form['imponi'] * $iva['aliquo'] / 100, 2);
                $form['import'] = $form['imponi'] + $form['impost'];
                $import = $form['import'];
                gaz_dbi_table_insert('rigmoc', $form);
                // rigo conto immobilizzazione
                $form['codcon'] = $form['acc_fixed_assets'];
                $form['darave'] = 'D';
                $form['import'] = $form['imponi'];
                gaz_dbi_table_insert('rigmoc', $form);
                // rigo iva 
                $form['codiva'] = $form['codvat'];
                $form['periva'] = $iva['aliquo'];
                $form['tipiva'] = $iva['tipiva'];
                gaz_dbi_table_insert('rigmoi', $form);
                //e rigo conto imposta
                $form['codcon'] = $admin_aziend['ivaacq'];
                $form['import'] = $form['impost'];
                gaz_dbi_table_insert('rigmoc', $form);
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
    $tesdoc = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", intval($_GET['id_tes']));
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner($tesdoc['clfoco']);
    $form['id_tes'] = $tesdoc['id_tes'];
    $form['hidden_req'] = '';
    $form['search']['clfoco'] = substr($fornitore['ragso1'], 0, 10);
    $form['seziva'] = $tesdoc['seziva'];
    $form['tipdoc'] = $tesdoc['tipdoc'];
    if ($tesdoc['id_con'] > 0) {
        $msg .= "Questo documento &egrave; gi&agrave; stato contabilizzato!<br />";
    }
    $form['datfat'] = substr($tesdoc['datfat'], 8, 2);
    $form['datreg'] = substr($tesdoc['initra'], 8, 2);
    $form['protoc'] = $tesdoc['protoc'];
    $form['numfat'] = $tesdoc['numfat'];
    $form['datfat'] = $tesdoc['datfat'];
    $form['clfoco'] = $tesdoc['clfoco'];
    $form['pagame'] = $tesdoc['pagame'];
    $form['change_pag'] = $tesdoc['pagame'];
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['hidden_req'] = '';
    $form['id_tes'] = "";
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
    $form['cosear'] = "";
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
    $form['descri'] = '';
    $form['unimis'] = '';
    $form['quantity'] = 1;
    $form['price'] = 0;
    $form['ss_amm_min'] = 999;
    $fornitore['indspe'] = "";
    $fornitore['citspe'] = "";
}

// ricavo il gruppo e la specie dalla tabella ammortamenti ministeriali 
$xml = simplexml_load_file('../../library/include/ammortamenti_ministeriali.xml') or die("Error: Cannot create object");
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
$amount = CalcolaImportoRigo($form['quantity'], $form['price'], 0);
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
            var price = parseFloat($('#price').val());
            var amount = price * quantity;
            var amount_rate = amount * valamm * gg / 36500;
            $("#amount").text(amount.toFixed(2).toString());
            ;
            $("#amount_rate").text(amount_rate.toFixed(2).toString());
            ;
        }
        $("#datreg, #datfat").datepicker();
        $("#datreg").change(function () {
            this.form.submit();
        });
        $('#valamm, #price, #quantity').change(function () {
            sumVal();
        });
    });
</script>
<?php
$gForm = new acquisForm();
if (count($msg['err']) > 0) { // ho un errore
    $gForm->toast($msg['err'], $script_transl['err'], 'err-entry', 'alert-danger');
}
?>
<form class="form-horizontal" role="form" method="post" name="docacq" enctype="multipart/form-data" >
    <input type="hidden" name="<?php echo ucfirst($toDo); ?>" value="">
    <input type="hidden" value="<?php echo $form['hidden_req'] ?>" name="hidden_req" />
    <input type="hidden" value="<?php echo $form['id_tes']; ?>" name="id_tes">
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
    <input type="hidden" value="<?php echo $form['change_pag']; ?>" name="change_pag">
    <input type="hidden" value="<?php echo $gg; ?>" id="gg">
    <div class="text-center">
        <p>
            <b>
                <?php
                echo $script_transl[$toDo] . ' ' . $script_transl['title'] . ':';
                $select_fornitore = new selectPartner("clfoco");
                $select_fornitore->selectDocPartner('clfoco', $form['clfoco'], $form['search']['clfoco'], 'clfoco', $script_transl['mesg'], $admin_aziend['masfor']);
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
                        <label for="datreg" class="col-sm-4 control-label"><?php echo $script_transl['datreg']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" id="datreg" name="datreg" tabindex=10 value="<?php echo $form['datreg']; ?>">
                        </div>
                    </div>
                </div>                    
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="numfat" class="col-sm-4 control-label"><?php echo $script_transl['numfat']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="numfat" name="numfat" maxlength="20" tabindex=11 placeholder="<?php echo $script_transl['numfat']; ?>" value="<?php echo $form['numfat']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="datfat" class="col-sm-4 control-label"><?php echo $script_transl['datfat']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" id="datfat" name="datfat" placeholder="GG/MM/AAAA" tabindex=12 value="<?php echo $form['datfat']; ?>">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="pagame" class="col-sm-4 control-label" ><?php echo $script_transl['pagame']; ?>:</label>
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
                        <label for="codvat" class="col-sm-4 control-label"><?php echo $script_transl['codvat']; ?>:</label>
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
                        <label for="mas_fixed_assets" class="col-sm-4 control-label"><?php echo $script_transl['mas_fixed_assets']; ?>:</label>
                        <div>
                            <?php
                            $gForm->selectAccount('mas_fixed_assets', $form['mas_fixed_assets'] . '000000', array(1, 9), '', 13, "col-sm-8 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="seziva" class="col-sm-4 control-label"><?php echo $script_transl['seziva']; ?></label>
                        <div class="col-sm-8">
                            <?php $gForm->selectNumber('seziva', $form['seziva'], 0, 1, 3, 'col-sm-8 small'); ?>
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
                        <label for="ss_amm_min" class="col-sm-4 control-label"><?php echo $script_transl['ss_amm_min']; ?>:</label>
                        <?php
                        $gForm->selAmmortamentoMin('ammortamenti_ministeriali.xml', 'ss_amm_min', $admin_aziend['amm_min'], $form["ss_amm_min"]);
                        ?>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="valamm" class="col-sm-8 control-label"><?php echo $script_transl['valamm']; ?>:</label>
                        <div class="col-sm-4">
                            <input type="number" step="0.1" min="0.1" max="100" class="form-control" id="valamm" name="valamm" placeholder="<?php echo $script_transl['valamm']; ?>" value="<?php echo $form['valamm']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="mas_found_assets" class="col-sm-4 control-label"><?php echo $script_transl['mas_found_assets']; ?>:</label>
                        <div>
                            <?php
                            $gForm->selectAccount('mas_found_assets', $form['mas_found_assets'] . '000000', array(2, 9), '', 13, "col-sm-8 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="mas_cost_assets" class="col-sm-4 control-label"><?php echo $script_transl['mas_cost_assets']; ?>:</label>
                        <div>
                            <?php
                            $gForm->selectAccount('mas_cost_assets', $form['mas_cost_assets'] . '000000', array(3, 9), '', 13, "col-sm-8 small");
                            ?>
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="descri" class="col-sm-4 control-label"><?php echo $script_transl['descri']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="numfat" name="descri" maxlenght="100" tabindex=14 placeholder="<?php echo $script_transl['descri']; ?>" value="<?php echo $form['descri']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="unimis" class="col-sm-4 control-label"><?php echo $script_transl['unimis']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="unimis" name="unimis" maxlenght="3" tabindex=15 placeholder="<?php echo $script_transl['unimis']; ?>" value="<?php echo $form['unimis']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="quantity" class="col-sm-4 control-label"><?php echo $script_transl['quantity']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.1" min="1" class="form-control" id="quantity" name="quantity" tabindex=16 placeholder="<?php echo $script_transl['quantity']; ?>" value="<?php echo $form['quantity']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="price" class="col-sm-4 control-label"><?php echo $script_transl['price']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price" tabindex=17 placeholder="<?php echo $script_transl['price']; ?>" value="<?php echo $form['price']; ?>">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="amount" class="col-sm-8 control-label"><?php echo $script_transl['amount']; ?>:</label>
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
                            <?php
                            echo $gg . $script_transl['info']['gg_to_year_end_1'] . substr($form['datreg'], 6, 4).$script_transl['info']['gg_to_year_end_2'];
                            ?>
                            <span id="amount_rate">
                                <?php
                                echo gaz_format_number(round($amount*$form['valamm']*$gg/36500, 2));
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
                <div class="col-md-12 col-lg-6">
                    <div class="form-group">
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="form-group">
                        <div class="col-sm-12 text-right alert-success">
                            <input name="ins" id="preventDuplicate" onClick="chkSubmit();" type="submit" value="<?php echo strtoupper($script_transl[$toDo]); ?>!">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
</form>
</div><!-- chiude div container role main -->
</body>
</html>