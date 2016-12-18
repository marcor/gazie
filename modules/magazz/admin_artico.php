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
$modal_ok_insert = false;
/** ENRICO FEDELE */
/* Inizializzo la variabile per aprire in finestra modale */
$modal = false;
if (isset($_POST['mode']) || isset($_GET['mode'])) {
    $modal = true;
    if (isset($_GET['ok_insert'])) {
        $modal_ok_insert = true;
    }
}
/** ENRICO FEDELE */
if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    $form = gaz_dbi_parse_post('artico');
    $form['codice'] = trim($form['codice']);
    $form['ritorno'] = $_POST['ritorno'];
    $form['ref_code'] = substr($_POST['ref_code'], 0, 15);
    // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
    $form["preacq"] = number_format($form['preacq'], $admin_aziend['decimal_price'], '.', '');
    $form["preve1"] = number_format($form['preve1'], $admin_aziend['decimal_price'], '.', '');
    $form["preve2"] = number_format($form['preve2'], $admin_aziend['decimal_price'], '.', '');
    $form["preve3"] = number_format($form['preve3'], $admin_aziend['decimal_price'], '.', '');
    $form["web_price"] = number_format($form['web_price'], $admin_aziend['decimal_price'], '.', '');
    $form['rows'] = array();
    /** inizio modifica FP 03/12/2015
     * fornitore
     */
    $form['id_anagra'] = filter_input(INPUT_POST, 'id_anagra');
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }
    /** fine modifica FP */
    // inizio documenti/certificati
    $ndoc = 0;
    if (isset($_POST['rows'])) {
        foreach ($_POST['rows'] as $ndoc => $value) {
            $form['rows'][$ndoc]['id_doc'] = intval($value['id_doc']);
            $form['rows'][$ndoc]['extension'] = substr($value['extension'], 0, 5);
            $form['rows'][$ndoc]['title'] = substr($value['title'], 0, 255);
            $ndoc++;
        }
    }
    // fine documenti/certificati
    $form['body_text'] = filter_input(INPUT_POST, 'body_text');

    /** ENRICO FEDELE */
    /* Controllo se il submit viene da una modale */
    if (isset($_POST['Submit']) || ($modal === true && isset($_POST['mode-act']))) { // conferma tutto
        /** ENRICO FEDELE */
        if ($toDo == 'update') {  // controlli in caso di modifica
            if ($form['codice'] != $form['ref_code']) { // se sto modificando il codice originario
                // controllo che l'articolo ci sia gia'
                $rs_articolo = gaz_dbi_dyn_query('codice', $gTables['artico'], "codice = '" . $form['codice'] . "'", "codice DESC", 0, 1);
                $rs = gaz_dbi_fetch_array($rs_articolo);
                if ($rs) {
                    $msg['err'][] = 'codice';
                }
                // controllo che il precedente non abbia movimenti di magazzino associati
                $rs_articolo = gaz_dbi_dyn_query('artico', $gTables['movmag'], "artico = '" . $form['ref_code'] . "'", "artico DESC", 0, 1);
                $rs = gaz_dbi_fetch_array($rs_articolo);
                if ($rs) {
                    $msg['err'][] = 'movmag';
                }
            }
        } else {
            // controllo che l'articolo ci sia gia'
            $rs_articolo = gaz_dbi_dyn_query('codice', $gTables['artico'], "codice = '" . $form['codice'] . "'", "codice DESC", 0, 1);
            $rs = gaz_dbi_fetch_array($rs_articolo);
            if ($rs) {
                $msg['err'][] = 'codice';
            }
        }
        if (!empty($_FILES['userfile']['name'])) {
            if (!( $_FILES['userfile']['type'] == "image/png" ||
                    $_FILES['userfile']['type'] == "image/x-png" ||
                    $_FILES['userfile']['type'] == "image/jpeg" ||
                    $_FILES['userfile']['type'] == "image/jpg" ||
                    $_FILES['userfile']['type'] == "image/gif" ||
                    $_FILES['userfile']['type'] == "image/x-gif"))
                $msg['err'][] = 'filmim';
            // controllo che il file non sia piu' grande di circa 10kb
            if ($_FILES['userfile']['size'] > 10999)
                $msg['err'][] = 'filsiz';
        }
        if (empty($form["codice"])) {
            $msg['err'][] = 'valcod';
        }
        if (empty($form["descri"])) {
            $msg['err'][] = 'descri';
        }
        if (empty($form["unimis"])) {
            $msg['err'][] = 'unimis';
        }
        if (empty($form["aliiva"])) {
            $msg['err'][] = 'aliiva';
        }
        // per poter avere la tracciabilità è necessario attivare la contabità di magazzino in configurazione azienda
        if ($form["lot_or_serial"] > 0 && $admin_aziend['conmag'] <= 1) {
            $msg['err'][] = 'lotmag';
        }
        if (count($msg['err']) == 0) { // nessun errore
            if ($_FILES['userfile']['size'] > 0) { //se c'e' una nuova immagine nel buffer
                $form['image'] = file_get_contents($_FILES['userfile']['tmp_name']);
            } elseif ($toDo == 'update') { // altrimenti riprendo la vecchia ma solo se è una modifica
                $oldimage = gaz_dbi_get_row($gTables['artico'], 'codice', $form['ref_code']);
                $form['image'] = $oldimage['image'];
            } else {
                $form['image'] = '';
            }
            /** inizio modifica FP 03/12/2015
             * aggiorno il campo con il codice fornitore
             */
            $form['clfoco'] = $form['id_anagra'];
            /** fine modifica FP */
            $tbt = trim($form['body_text']);
            // aggiorno il db
            if ($toDo == 'insert') {
                gaz_dbi_table_insert('artico', $form);
                if (!empty($tbt)) {
                    bodytextInsert(array('table_name_ref' => 'artico_' . $form['codice'], 'body_text' => $form['body_text'], 'lang_id' => $admin_aziend['id_language']));
                }
            } elseif ($toDo == 'update') {
                gaz_dbi_table_update('artico', $form['ref_code'], $form);
                $bodytext = gaz_dbi_get_row($gTables['body_text'], "table_name_ref", 'artico_' . $form['codice']);
                if (empty($tbt) && $bodytext) {
                    // è vuoto il nuovo ma non lo era prima, allora lo cancello 
                    gaz_dbi_del_row($gTables['body_text'], 'id_body', $bodytext['id_body']);
                } elseif (!empty($tbt) && $bodytext) {
                    // c'è e c'era quindi faccio l'update
                    bodytextUpdate(array('id_body', $bodytext['id_body']), array('table_name_ref' => 'artico_' . $form['codice'], 'body_text' => $form['body_text'], 'lang_id' => $admin_aziend['id_language']));
                } elseif (!empty($tbt)) {
                    // non c'era lo inserisco
                    bodytextInsert(array('table_name_ref' => 'artico_' . $form['codice'], 'body_text' => $form['body_text'], 'lang_id' => $admin_aziend['id_language']));
                }
            }
            /** ENRICO FEDELE */
            /* Niente redirect se sono in finestra modale */
            if ($modal === false) {
                header("Location: " . $form['ritorno']);
            } else {
                header("Location: ../../modules/magazz/admin_artico.php?mode=modal&ok_insert=1");
            }
            /** ENRICO FEDELE */
            exit;
        }
        /** ENRICO FEDELE */
    } elseif (isset($_POST['Return']) && $modal === false) { // torno indietro
        /* Solo se non sono in finestra modale */
        /** ENRICO FEDELE */
        header("Location: " . $form['ritorno']);
        exit;
    }
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    $form = gaz_dbi_get_row($gTables['artico'], 'codice', substr($_GET['codice'], 0, 15));
    /** ENRICO FEDELE */
    if ($modal === false) {
        $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    } else {
        $form['ritorno'] = 'admin_artico.php';
    }
    /** ENRICO FEDELE */
    $form['ref_code'] = $form['codice'];
    // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
    $form["preacq"] = number_format($form['preacq'], $admin_aziend['decimal_price'], '.', '');
    $form["preve1"] = number_format($form['preve1'], $admin_aziend['decimal_price'], '.', '');
    $form["preve2"] = number_format($form['preve2'], $admin_aziend['decimal_price'], '.', '');
    $form["preve3"] = number_format($form['preve3'], $admin_aziend['decimal_price'], '.', '');
    $form["web_price"] = number_format($form['web_price'], $admin_aziend['decimal_price'], '.', '');
    $form['rows'] = array();
    /** inizio modifica FP 03/12/2015
     * fornitore
     */
    $form['id_anagra'] = $form['clfoco'];
    $form['search']['id_anagra'] = '';
    /** fine modifica FP */
    // inizio documenti/certificati
    $ndoc = 0;
    $rs_row = gaz_dbi_dyn_query("*", $gTables['files'], "item_ref = '" . $form['codice'] . "'", "id_doc DESC");
    while ($row = gaz_dbi_fetch_array($rs_row)) {
        $form['rows'][$ndoc] = $row;
        $ndoc++;
    }
    // fine documenti/certificati
    $bodytext = gaz_dbi_get_row($gTables['body_text'], "table_name_ref", 'artico_' . $form['codice']);
    $form['body_text'] = $bodytext['body_text'];
} else { //se e' il primo accesso per INSERT
    $form = gaz_dbi_fields('artico');
    /** ENRICO FEDELE */
    if ($modal === false) {
        $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    } else {
        $form['ritorno'] = 'admin_artico.php';
    }
    /** ENRICO FEDELE */
    $form['ref_code'] = '';
    $form['aliiva'] = $admin_aziend['preeminent_vat'];
    // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
    $form["preacq"] = number_format($form['preacq'], $admin_aziend['decimal_price'], '.', '');
    $form["preve1"] = number_format($form['preve1'], $admin_aziend['decimal_price'], '.', '');
    $form["preve2"] = number_format($form['preve2'], $admin_aziend['decimal_price'], '.', '');
    $form["preve3"] = number_format($form['preve3'], $admin_aziend['decimal_price'], '.', '');
    $form["web_price"] = number_format($form['web_price'], $admin_aziend['decimal_price'], '.', '');
    $form['web_public'] = 1;
    $form['depli_public'] = 1;
    /** inizio modifica FP 03/12/2015
     * filtro per fornitore ed ordinamento
     */
    $form['id_anagra'] = '';
    $form['search']['id_anagra'] = '';
    /** fine modifica FP */
    // eventuale descrizione amplia
    $form['body_text'] = '';
}

/** ENRICO FEDELE */
/* Solo se non sono in finestra modale carico il file di lingua del modulo */
if ($modal === false) {
    require("../../library/include/header.php");
    $script_transl = HeadMain();
} else {
    $script = basename($_SERVER['PHP_SELF']);
    require("../../language/" . $admin_aziend['lang'] . "/menu.inc.php");
    require("../../modules/magazz/lang." . $admin_aziend['lang'] . ".php");
    if (isset($script)) { // se è stato tradotto lo script lo ritorno al chiamante
        $script_transl = $strScript[$script];
    }

    $script_transl = $strCommon + $script_transl;
}
/** ENRICO FEDELE */
/* Assegno un id al form, quindi distinguo tra modale e non 
 * in caso di finestra modale, aggiungo un campo nascosto che mi serve per salvare nel database
 */
?>
<script type="text/javascript">
    function calcDiscount() {
        var p1 = ($("#preve1").val() * (1 - $("#sconto").val() / 100)).toFixed(<?php echo $admin_aziend['decimal_price']; ?>);
        $("#preve1_sc").val(p1);
        var p2 = ($("#preve2").val() * (1 - $("#sconto").val() / 100)).toFixed(<?php echo $admin_aziend['decimal_price']; ?>);
        $("#preve2_sc").val(p2);
        var p3 = ($("#preve3").val() * (1 - $("#sconto").val() / 100)).toFixed(<?php echo $admin_aziend['decimal_price']; ?>);
        $("#preve3_sc").val(p3);
    }

    $(function () {
        $("#preve1,#preve2,#preve3,#sconto").change(function () {
            var v = $(this).val().replace(/,/, '.');
            $(this).val(v);
            calcDiscount();
        });
    });
</script>
<form method="POST" name="form" enctype="multipart/form-data" id="add-product">
<?php
if ($modal === true) {
    echo '<input type="hidden" name="mode" value="modal" />
          <input type="hidden" name="mode-act" value="submit" />';
}
echo '<input type="hidden" name="ritorno" value="' . $form['ritorno'] . '" />';
echo '<input type="hidden" name="ref_code" value="' . $form['ref_code'] . '" />';

if ($modal_ok_insert === true) {
    echo '<div class="alert alert-success" role="alert">' . $script_transl['modal_ok_insert'] . '</div>';
    echo '<div class=" text-center"><button class="btn btn-lg btn-default" type="submit" name="none">' . $script_transl['iterate_invitation'] . '</button></div>';
} else {
    $gForm = new magazzForm();
    $mv = $gForm->getStockValue(false, $form['codice']);
    $magval = array_pop($mv);

    /** ENRICO FEDELE */
    /* Se sono in finestra modale, non visualizzo questo titolo */
    $changesubmit = '';
    if ($modal === false) {
        if ($toDo == 'insert') {
            echo '<div class="text-center"><b>' . $script_transl['ins_this'] . '</b></div>';
        } else {
            echo '<div class="text-center"><b>' . $script_transl['upd_this'] . ' ' . $form['codice'] . '</b></div>';
        }
    }
    echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    ?>
        <div class="panel panel-default gaz-table-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="codice" class="col-sm-4 control-label"><?php echo $script_transl['codice']; ?></label>
                            <input class="col-sm-4" type="text" value="<?php echo $form['codice']; ?>" name="codice" maxlength="15" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descri" class="col-sm-4 control-label"><?php echo $script_transl['descri']; ?></label>
                            <input class="col-sm-8" type="text" value="<?php echo $form['descri']; ?>" name="descri" maxlength="255" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="good_or_service" class="col-sm-4 control-label"><?php echo $script_transl['good_or_service']; ?>*</label>
    <?php
    $gForm->variousSelect('good_or_service', $script_transl['good_or_service_value'], $form['good_or_service'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="body_text" class="col-sm-4 control-label"><?php echo $script_transl['body_text']; ?></label>
                            <div class="col-sm-8">
                                <textarea id="body_text" name="body_text" class="mceClass"><?php echo $form['body_text']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="barcode" class="col-sm-4 control-label"><?php echo $script_transl['barcode']; ?></label>
                            <input class="col-sm-4" type="text" value="<?php echo $form['barcode']; ?>" name="barcode" maxlength="13" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="lot_or_serial" class="col-sm-4 control-label"><?php echo $script_transl['lot_or_serial'] . ' (' . $admin_aziend['ritenuta'] . '%)'; ?></label>
    <?php
    $gForm->variousSelect('lot_or_serial', $script_transl['lot_or_serial_value'], $form['lot_or_serial'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="image" class="col-sm-4 control-label"><img src="../root/view.php?table=artico&value=<?php echo $form['codice']; ?>" width="100" >*</label>
                            <div class="col-sm-8"><?php echo $script_transl['image']; ?><input type="file" name="userfile" /></div>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="unimis" class="col-sm-4 control-label"><?php echo $script_transl['unimis']; ?></label>
                            <input class="col-sm-2" type="text" value="<?php echo $form['unimis']; ?>" name="unimis" maxlength="3" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="catmer" class="col-sm-4 control-label"><?php echo $script_transl['catmer']; ?></label>
    <?php
    $gForm->selectFromDB('catmer', 'catmer', 'codice', $form['catmer'], false, 1, ' - ', 'descri', '', 'col-sm-8', null, 'style="max-width: 250px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="ragstat" class="col-sm-4 control-label"><?php echo $script_transl['ragstat']; ?></label>
    <?php
    $gForm->selectFromDB('ragstat', 'ragstat', 'codice', $form['ragstat'], false, 1, ' - ', 'descri', '', 'col-sm-8', null, 'style="max-width: 250px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="preacq" class="col-sm-4 control-label"><?php echo $script_transl['preacq']; ?></label>
                            <input class="col-sm-4" type="number" step="any" min="0" value="<?php echo $form['preacq']; ?>" name="preacq" maxlength="15" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="sconto" class="col-sm-4 control-label"><?php echo $script_transl['sconto']; ?></label>
                            <input class="col-sm-2" name="sconto" id="sconto" type="number" step="0.01" min="0" max="100" value="<?php echo $form['sconto']; ?>" maxlength="6" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="preve1" class="col-sm-4 control-label"><?php echo $script_transl['preve1']; ?></label>
                            <input type="number" step="any" min="0" id="preve1" name="preve1" value="<?php echo $form['preve1']; ?>"  maxlength="15" />
    <?php echo $script_transl['preve1_sc']; ?>
                            <input type="text" readonly="true" id="preve1_sc" name="preve1_sc" value="<?php echo gaz_format_number($form['preve1'] * (1 - $form['sconto'] / 100)); ?>" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="preve2" class="col-sm-4 control-label"><?php echo $script_transl['preve2']; ?></label>
                            <input type="number" step="any" min="0" id="preve2" name="preve2" value="<?php echo $form['preve2']; ?>"  maxlength="15" />
    <?php echo $script_transl['preve2_sc']; ?>
                            <input type="text" readonly="true" id="preve2_sc" name="preve2_sc" value="<?php echo gaz_format_number($form['preve2'] * (1 - $form['sconto'] / 100)); ?>" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="preve3" class="col-sm-4 control-label"><?php echo $script_transl['preve3']; ?></label>
                            <input type="number" step="any" min="0" id="preve3" name="preve3" value="<?php echo $form['preve3']; ?>"  maxlength="15" />
    <?php echo $script_transl['preve3_sc']; ?>
                            <input type="text" readonly="true" id="preve3_sc" name="preve3_sc" value="<?php echo gaz_format_number($form['preve3'] * (1 - $form['sconto'] / 100)); ?>" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="aliiva" class="col-sm-4 control-label"><?php echo $script_transl['aliiva']; ?></label>
    <?php
    $gForm->selectFromDB('aliiva', 'aliiva', 'codice', $form['aliiva'], 'codice', 0, ' - ', 'descri', '', 'col-sm-8', null, 'style="max-width: 350px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="retention_tax" class="col-sm-4 control-label"><?php echo $script_transl['retention_tax'] . ' (' . $admin_aziend['ritenuta'] . '%)'; ?></label>
    <?php
    $gForm->variousSelect('retention_tax', $script_transl['retention_tax_value'], $form['retention_tax'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="payroll_tax" class="col-sm-4 control-label"><?php echo $script_transl['payroll_tax']; ?>*</label>
    <?php
    $gForm->variousSelect('payroll_tax', $script_transl['payroll_tax_value'], $form['payroll_tax'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="esiste" class="col-sm-4 control-label"><?php echo $script_transl['esiste']; ?></label>
                            <div class="col-sm-2"><?php echo $magval['q_g']; ?></div>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="valore" class="col-sm-4 control-label"><?php echo $script_transl['valore']; ?></label>
                            <div class="col-sm-2"><?php echo $admin_aziend['symbol'] . $magval['v_g']; ?></div>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="last_cost" class="col-sm-4 control-label"><?php echo $script_transl['last_cost']; ?></label>
                            <input class="col-sm-4" type="number" min="0" step="any" value="<?php echo $form['last_cost']; ?>" name="last_cost" maxlength="15" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="scorta" class="col-sm-4 control-label"><?php echo $script_transl['scorta']; ?></label>
                            <input class="col-sm-4" type="number" min="0" step="any" value="<?php echo $form['scorta']; ?>" name="scorta" maxlength="13" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="riordino" class="col-sm-4 control-label"><?php echo $script_transl['riordino']; ?></label>
                            <input type="number" min="0" step="any" class="col-sm-4" type="text"  value="<?php echo $form['riordino']; ?>" name="riordino" maxlength="13" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="uniacq" class="col-sm-4 control-label"><?php echo $script_transl['uniacq']; ?></label>
                            <input class="col-sm-2" type="text" value="<?php echo $form['uniacq']; ?>" name="uniacq" maxlength="3" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="peso_specifico" class="col-sm-4 control-label"><?php echo $script_transl['peso_specifico']; ?></label>
                            <input class="col-sm-4" type="number" min="0" step="any" value="<?php echo $form['peso_specifico']; ?>" name="peso_specifico" maxlength="13" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="volume_specifico" class="col-sm-4 control-label"><?php echo $script_transl['volume_specifico']; ?></label>
                            <input class="col-sm-4" type="number" min="0" step="any" value="<?php echo $form['volume_specifico']; ?>" name="volume_specifico" maxlength="13" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="pack_units" class="col-sm-4 control-label"><?php echo $script_transl['pack_units']; ?></label>
                            <input class="col-sm-4" type="number" min="0" step="any" value="<?php echo $form['pack_units']; ?>" name="pack_units" maxlength="6" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="codcon" class="col-sm-4 control-label"><?php echo $script_transl['codcon']; ?></label>
    <?php
    $gForm->selectAccount('codcon', $form['codcon'], 4, '', false, "col-sm-8");
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="id_cost" class="col-sm-4 control-label"><?php echo $script_transl['id_cost']; ?></label>
    <?php
    $gForm->selectAccount('id_cost', $form['id_cost'], 3, '', false, "col-sm-8");
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="annota" class="col-sm-4 control-label"><?php echo $script_transl['annota']; ?></label>
                            <input class="col-sm-8" type="text" value="<?php echo $form['annota']; ?>" name="annota" maxlength="50" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
    <?php if ($toDo == 'update') { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="annota" class="col-sm-4 control-label"><?php echo $script_transl['document']; ?></label>
        <?php if ($ndoc > 0) { // se ho dei documenti  ?> 
                                    <div>
                                    <?php foreach ($form['rows'] as $k => $val) { ?>
                                            <input type="hidden" value="<?php echo $val['id_doc']; ?>" name="rows[<?php echo $k; ?>][id_doc]">
                                            <input type="hidden" value="<?php echo $val['extension']; ?>" name="rows[<?php echo $k; ?>][extension]">
                                            <input type="hidden" value="<?php echo $val['title']; ?>" name="rows[<?php echo $k; ?>][title]">
                <?php echo DATA_DIR . 'files/' . $val['id_doc'] . '.' . $val['extension']; ?>
                                            <a href="../root/retrieve.php?id_doc=<?php echo $val["id_doc"]; ?>" title="<?php echo $script_transl['view']; ?>!" class="btn btn-default btn-sm">
                                                <i class="glyphicon glyphicon-file"></i>
                                            </a><?php echo $val['title']; ?>
                                            <input type="button" value="<?php echo ucfirst($script_transl['update']); ?>" onclick="location.href = 'admin_document.php?id_doc=<?php echo $val['id_doc']; ?>&Update'" />

            <?php } ?>
                                        <input type="button" value="<?php echo ucfirst($script_transl['insert']); ?>" onclick="location.href = 'admin_document.php?item_ref=<?php echo $form['codice']; ?>&Insert'" />
                                    </div>
                                    <?php } else { // non ho documenti  ?>
                                    <input type="button" value="<?php echo ucfirst($script_transl['insert']); ?>" onclick="location.href = 'admin_document.php?item_ref=<?php echo $form['codice']; ?>&Insert'">
                                <?php } ?>
                            </div>
                        </div>
                    </div>
    <?php } ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="id_cost" class="col-sm-4 control-label"><?php echo $script_transl['id_anagra']; ?></label>
    <?php
    $select_id_anagra = new selectPartner("id_anagra");
    $select_id_anagra->selectDocPartner('id_anagra', $form['id_anagra'], $form['search']['id_anagra'], 'id_anagra', $script_transl['mesg'], $admin_aziend['masfor'], -1, 1, true);
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="web_mu" class="col-sm-4 control-label"><?php echo $script_transl['web_mu']; ?></label>
                            <input class="col-sm-4" type="text" value="<?php echo $form['web_mu']; ?>" name="web_mu" maxlength="15" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="web_price" class="col-sm-4 control-label"><?php echo $script_transl['web_price']; ?></label>
                            <input class="col-sm-4" type="text"  value="<?php echo $form['web_price']; ?>" name="web_price" maxlength="15" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="web_multiplier" class="col-sm-4 control-label"><?php echo $script_transl['web_multiplier']; ?></label>
                            <input class="col-sm-4" type="text"  value="<?php echo $form['web_multiplier']; ?>" name="web_multiplier" maxlength="15" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="web_url" class="col-sm-4 control-label"><?php echo $script_transl['web_url']; ?></label>
                            <input class="col-sm-8" type="text" value="<?php echo $form['web_url']; ?>" name="web_url" maxlength="255" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="depli_public" class="col-sm-4 control-label"><?php echo $script_transl['depli_public']; ?></label>
    <?php
    $gForm->variousSelect('depli_public', $script_transl['depli_public_value'], $form['depli_public'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="web_public" class="col-sm-4 control-label"><?php echo $script_transl['web_public']; ?></label>
    <?php
    $gForm->variousSelect('web_public', $script_transl['web_public_value'], $form['web_public'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
    ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                <div class="col-sm-12">
    <?php
    /** ENRICO FEDELE */
    /* SOlo se non sono in finestra modale */
    if ($modal === false) {
        echo '<div class="col-sm-4 text-left"><input name="none" type="submit" value="" disabled></div>';
    }
    /** ENRICO FEDELE */
    echo '<div class="col-sm-8 text-center"><input name="Submit" type="submit" class="btn btn-warning" value="' . strtoupper($script_transl[$toDo]) . '!" /></div>';
}
?>
            </div>
        </div> <!-- chiude container --> 
    </div><!-- chiude panel -->
</form>
<script type="text/javascript">
    // Basato su: http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/
    $(document).on('change', '.btn-file :file', function () {
        var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);
    });
    $(document).ready(function () {
        $('.btn-file :file').on('fileselect', function (event, numFiles, label) {

            var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;
            if (input.length) {
                input.val(log);
            } else {
                if (log)
                    alert(log);
            }

        });
    });</script>


<?php
/** ENRICO FEDELE */
/* SOlo se non sono in finestra modale */
if ($modal === false) {
} else {
    ?>
    <script type="text/javascript">
        $("#add-product").submit(function (e) {
            $.ajax({
                type: "POST",
                url: "../../modules/magazz/admin_artico.php",
                data: $("#add-product").serialize(), // serializes the form's elements.
                success: function (data) {
                    $("#edit-modal .modal-sm").css('width', '100%');
                    $("#edit-modal .modal-body").html(data);
                }
            });
            e.preventDefault(); // avoid to execute the actual submit of the form.
        });
    </script>
    <?php
}
require("../../library/include/footer.php");
?>