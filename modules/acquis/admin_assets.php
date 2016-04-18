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
    $form['acc-fondo'] = substr($_POST['acc-fondo'], 0, 3);
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
            }
        }
// --- fine controllo coerenza date-numeri
        if (!checkdate($form['mesemi'], $form['gioemi'], $form['annemi']))
            $msg .= "46+";
        if (empty($form["clfoco"]))
            $msg .= "47+";
        if (empty($form["pagame"]))
            $msg .= "48+";
        if ($msg == "") {// nessun errore
            if (preg_match("/^id_([0-9]+)$/", $form['clfoco'], $match)) {
                $new_clfoco = $anagrafica->getPartnerData($match[1], 1);
                $form['clfoco'] = $anagrafica->anagra_to_clfoco($new_clfoco, $admin_aziend['masfor']);
            }
            if ($toDo == 'update') { // e' una modifica
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
                if (substr($form['tipdoc'], 0, 2) == 'DD') {  //ma se e' un ddt a fornitore il protocollo è 0 così come il numero e data fattura
                    $form['protoc'] = 0;
                    $form['numfat'] = 0;
                    $form['datfat'] = 0;
                } else { //in tutti gli altri casi si deve prendere quanto inserito nel form
                    $form['datfat'] = $initra;
                    $form['protoc'] = getProtocol($form['tipdoc'], $form['annemi'], $sezione);
                    $form['numfat'] = $form['numfat'];
                }
//inserisco la testata
                $form['status'] = '';
                $form['initra'] = $initra;
                $form['datfat'] = $datfat;
                tesdocInsert($form);
//recupero l'id assegnato dall'inserimento
                $ultimo_id = gaz_dbi_last_id();
                $prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'];
// prima di uscire cancello eventuali precedenti file temporanei
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
    $form['codvat'] = $admin_aziend['preeminent_vat'];
    $form['protoc'] = 0;
    $form['numfat'] = "";
    $form['clfoco'] = "";
    $form['pagame'] = "";
    $form['change_pag'] = "";
    $form['valamm'] = 0;
    $form['acc-fondo'] = $admin_aziend['mas_amm'];
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
/* prove di formattazione date per tutte le esigenze
echo 'alla fine dell\'anno mancano '.365-date("z", gaz_format_date($form['datreg'],2)).' gg';
echo '<br>'.gaz_format_date($form['datreg'],false,true);
*/
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/autocomplete'));
?>
<script>
    $(function () {
        $("#datreg").datepicker();
        $("#datfat").datepicker();
        // tutto questo sotto per far funzionare tabindex sui selectmenu :( 
        $.widget("ui.selectmenu", $.ui.selectmenu, {
            _create: function () {
                this._super();
                this._setTabIndex();
            },
            _setTabIndex: function () {
                this.button.attr("tabindex",
                        this.options.disabled ? -1 :
                        this.element.attr("tabindex") || 0);
            },
            _setOption: function (key, value) {
                this._super(key, value);
                if (key === "disabled") {
                    this._setTabIndex();
                }
            }
        });
        // finalmente adesso funziona tabindex :)
        $('#pagame').selectmenu();
        $('#acc-fondo').selectmenu();
        $('#codvat').selectmenu();
        $('#seziva').selectmenu();
        $('#ss_amm_min').selectmenu({
            change: function (event, ui) {
                this.form.hidden_req.value = 'ss_amm_min';
                this.form.submit();
            }
        });
        $('#price').change(function () {
            this.form.submit();
        });
        $('#quantity').change(function () {
            this.form.submit();
        });
    });
</script>
<?php
$gForm = new acquisForm();
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
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
    <input type="hidden" value="<?php echo $form['change_pag']; ?>" name="change_pag">
    <div class="text-center">
        <p>
            <b>
                <?php
                echo $script_transl[$toDo] . ' ' . $script_transl['title'];
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
                        <?php echo $message; ?>                
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="datreg" class="col-sm-4 control-label"><?php echo $script_transl['datreg']; ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="datreg" name="datreg" tabindex=10 value="<?php echo $form['datreg']; ?>">
                        </div>
                    </div>
                </div>                    
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="numfat" class="col-sm-4 control-label"><?php echo $script_transl['numfat']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="numfat" name="numfat" maxlength="20" tabindex=11 placeholder="<?php echo $script_transl['numfat']; ?>" type="text" value="<?php echo $form['numfat']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="datfat" class="col-sm-4 control-label"><?php echo $script_transl['datfat']; ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="datfat" name="datfat" tabindex=12 value="<?php echo $form['datfat']; ?>">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="acc-fondo" class="col-sm-4 control-label"><?php echo $script_transl['acc-fondo']; ?>:</label>
                        <div class="col-sm-8">
                            <?php
                            $gForm->selectAccount('acc-fondo', $form['acc-fondo'] . '000000', array(1, 9), '', 13, "col-lg-12 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="pagame" class="col-sm-4 control-label" ><?php echo $script_transl['pagame']; ?>:</label>
                        <div class="col-sm-8">
                            <?php
                            $select_pagame = new selectpagame("pagame");
                            $select_pagame->addSelected($form["pagame"]);
                            $select_pagame->output(false, "col-lg-12 small");
                            ?>                
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="codvat" class="col-sm-4 control-label"><?php echo $script_transl['codvat']; ?>:</label>
                        <div class="col-sm-8">
                            <?php
                            $sel_vat = new selectaliiva("codvat");
                            $sel_vat->addSelected($form["codvat"]);
                            $sel_vat->output(false, "col-lg-12 small");
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="seziva" class="col-sm-4 control-label"><?php echo $script_transl['seziva']; ?></label>
                        <div class="col-sm-8">
                            <?php $gForm->selectNumber('seziva', $form['seziva'], 0, 1, 3); ?>
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
                        <div class="col-sm-8">
                            <?php
                            $gForm->selAmmortamentoMin('ammortamenti_ministeriali.xml', 'ss_amm_min', $admin_aziend['amm_min'], $form["ss_amm_min"]);
                            ?>
                        </div>
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
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="form-group">
                        <label for="descri" class="col-sm-4 control-label"><?php echo $script_transl['descri']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="numfat" name="descri" maxlenght="100" tabindex=14 placeholder="<?php echo $script_transl['descri']; ?>" value="<?php echo $form['descri']; ?>">
                        </div>
                    </div>
                </div>
            </div> <!-- chiude row  -->
            <div class="row">
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="unimis" class="col-sm-4 control-label"><?php echo $script_transl['unimis']; ?>:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="unimis" name="unimis" maxlenght="10" tabindex=15 placeholder="<?php echo $script_transl['unimis']; ?>" value="<?php echo $form['unimis']; ?>">
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
                <div class="col-sm-6 col-md-3 col-lg-3">
                    <div class="form-group">
                        <label for="amount" class="col-sm-8 control-label"><?php echo $script_transl['amount']; ?>:</label>
                        <div class="col-sm-4 bg-success">
                            <?php echo gaz_format_number(CalcolaImportoRigo($form['quantity'], $form['price'], 0)); ?>
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