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
$msg = '';
$anagrafica = new Anagrafica();

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['paymov'] = array();
    $form['entry_date'] = date("d/m/Y");
    $form['expiry_ini'] = date("d/m/Y");
    $form['expiry_fin'] = date("t/m/Y");
    $form['target_account'] = 0;
    $form['transfer_fees_acc'] = 0;
    $form['transfer_fees'] = 0.00;
} else { // accessi successivi
    $first = false;
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    $form['ritorno'] = $_POST['ritorno'];
    if (isset($_POST['paymov'])) {
        $desmov = '';
        $acc_tot = 0.00;
        foreach ($_POST['paymov'] as $k => $v) {
            $form['paymov'][$k] = $v;  // qui dovrei fare il parsing
            $add_desc[$k] = 0.00;
            foreach ($v as $ki => $vi) { // calcolo il totale 
                $acc_tot += $vi['amount'];
                $add_desc[$k] += $vi['amount'];
            }
        }
        if ($acc_tot <= 0) {
            $msg .= '4+';
        }
    } else if (isset($_POST['ins'])) { // non ho movimenti ma ho chiesto di inserirli
        $msg .= '6+';
    }
    $form['entry_date'] = substr($_POST['entry_date'], 0, 10);
    $form['expiry_ini'] = substr($_POST['expiry_ini'], 0, 10);
    $form['expiry_fin'] = substr($_POST['expiry_fin'], 0, 10);
    $form['target_account'] = intval($_POST['target_account']);
    $bank_data = gaz_dbi_get_row($gTables['clfoco'], 'codice', $form['target_account']);
    if (!isset($_POST['ins'])) {
        if ($bank_data['maxrat'] >= 0.01 && $_POST['transfer_fees'] < 0.01) { // se il conto corrente bancccario prevede un addebito per bonifici allora lo propongo
            $form['transfer_fees_acc'] = $bank_data['cosric'];
            $form['transfer_fees'] = $bank_data['maxrat'];
        } elseif (substr($form['target_account'], 0, 3) == substr($admin_aziend['cassa_'], 0, 3)) {
            $form['transfer_fees_acc'] = 0;
            $form['transfer_fees'] = 0.00;
        } else {
            $form['transfer_fees_acc'] = intval($_POST['transfer_fees_acc']);
            $form['transfer_fees'] = floatval($_POST['transfer_fees']);
        }
    }
    if (isset($_POST['return'])) {
        header("Location: " . $form['ritorno']);
        exit;
    }
    if (isset($_POST['ins']) && $form['target_account'] < 100000001) {
        $msg = '5+';
    }
    // fine controlli
    if (isset($_POST['ins']) && $msg == '') {
        $tes_val = array('caucon' => '',
            'descri' => $desmov,
            'datreg' => $date,
            'datdoc' => $date,
        );
        tesmovInsert($tes_val);
        $tes_id = gaz_dbi_last_id();
        $tot_avere = $acc_tot;
        if ($form['transfer_fees'] >= 0.01 && $form['transfer_fees_acc'] > 100000000) {
            $tot_avere += $form['transfer_fees'];
        }
        rigmocInsert(array('id_tes' => $tes_id, 'darave' => 'A', 'codcon' => $form['target_account'], 'import' => $tot_avere));
        rigmocInsert(array('id_tes' => $tes_id, 'darave' => 'D', 'codcon' => $form['partner'], 'import' => $acc_tot));
        $rig_id = gaz_dbi_last_id();
        if ($form['transfer_fees'] >= 0.01 && $form['transfer_fees_acc'] > 100000000) {
            rigmocInsert(array('id_tes' => $tes_id, 'darave' => 'D', 'codcon' => $form['transfer_fees_acc'], 'import' => $form['transfer_fees']));
        }
        foreach ($form['paymov'] as $k => $v) { //attraverso l'array delle partite
            $acc = 0.00;
            foreach ($v as $ki => $vi) {
                $acc += $vi['amount'];
            }
            if ($acc >= 0.01) {
                paymovInsert(array('id_tesdoc_ref' => $k, 'id_rigmoc_pay' => $rig_id, 'amount' => $acc, 'expiry' => $date));
            }
        }
        header("Location: report_schedule_acq.php");
        exit;
    }
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new acquisForm();
?>
<SCRIPT type="text/javascript">
    $(function () {
        $("#entry_date,#expiry_ini, #expiry_fin").datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
        $("#expiry_ini, #expiry_fin").change(function () {
            this.form.submit();
        });
        $('input:checkbox').on('change', function () {
            var sum = 0;
            $('.check').each(function () {
                if (this.checked)
                    sum = sum + parseFloat($(this).val());
            });
            $('#total').text(Math.round(sum * 100) / 100)
        }).trigger("change");
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    });
</script>
<form role="form" method="post" name="pay_riba" enctype="multipart/form-data" >
    <input type="hidden" value="<?php echo $form['hidden_req'] ?>" name="hidden_req" />
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
    <div class="text-center">
        <p><b><?php echo $script_transl['title']; ?></b></p>
    </div>
    <div class="panel panel-default gaz-table-form">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="entry_date" class="col-sm-4 control-label"><?php echo $script_transl['entry_date']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="entry_date" name="entry_date" value="<?php echo $form['entry_date']; ?>">
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="target_account" class="col-sm-4 control-label"><?php echo $script_transl['target_account']; ?></label>
                        <div class="col-sm-8">
                            <?php
                            $select_bank = new selectconven("target_account");
                            $select_bank->addSelected($form['target_account']);
                            $select_bank->output($admin_aziend['masban'], false, true);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="expiry_ini" class="col-sm-4 control-label"><?php echo $script_transl['expiry_ini']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="expiry_ini" name="expiry_ini" tabindex=1 value="<?php echo $form['expiry_ini']; ?>">
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="expiry_fin" class="col-sm-4 control-label"><?php echo $script_transl['expiry_fin']; ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="expiry_fin" name="expiry_fin" tabindex=2 value="<?php echo $form['expiry_fin']; ?>">
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="transfer_fees_acc" class="col-sm-4 control-label"><?php echo $script_transl['transfer_fees_acc']; ?></label>
                        <div class="col-sm-8">
                            <?php
                            $gForm->selectAccount('transfer_fees_acc', $form['transfer_fees_acc'], 3, '', false, "col-sm-6 small");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="transfer_fees" class="col-sm-4 control-label"><?php echo $script_transl['transfer_fees']; ?></label>
                        <div class="col-sm-4">
                            <input type="number" step="0.01" min="0.00" max="100" class="form-control" id="transfer_fees" name="transfer_fees" placeholder="<?php echo $script_transl['transfer_fees']; ?>" value="<?php echo $form['transfer_fees']; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- chiude container -->
    </div><!-- chiude panel -->
    <div class="panel panel-default gaz-table-form">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="entry_date" class="col-sm-11 control-label"><?php ?></label>
                        <div class="col-sm-1">
                            Seleziona tutto <input type="checkbox" id="checkAll">
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="entry_date" class="col-sm-11 control-label"><?php ?></label>
                        <div class="col-sm-1" id="total">
                            0
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <?php
            $paymov = new Schedule;
            $rs = gaz_dbi_dyn_query("*", $gTables['paymov'], "expiry BETWEEN '" . gaz_format_date($form['expiry_ini'], true) . "' AND '" . gaz_format_date($form['expiry_fin'], true) . "' AND id_rigmoc_doc >= 1", "expiry");
            while ($r = gaz_dbi_fetch_array($rs)) {
                $doc_data = $paymov->getDocumentData($r['id_tesdoc_ref']);
                $status = $paymov->getAmount($r['id_tesdoc_ref'], gaz_format_date($form['expiry_fin'], true));
                if (substr($doc_data['clfoco'], 0, 3) == $admin_aziend['masfor'] &&
                        $status >= 0.01) { // considero solo i fornitori non saldati 
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="entry_date" class="col-sm-11 control-label"><?php echo 'Per ' . $doc_data['descri'] . ' ' . $doc_data['numdoc'] . ' del ' . gaz_format_date($doc_data['datdoc']) . '  € ' . gaz_format_number($r['amount']). '  scad.' . gaz_format_date($r['expiry']); ?></label>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="check" value="<?php echo $r['amount']; ?>" id="<?php echo $r['id_tesdoc_ref']; ?>">
                                </div>
                            </div>
                        </div>
                    </div><!-- chiude row  -->
                    <?php
                }
            }
            ?>
        </div> <!-- chiude container -->
    </div><!-- chiude panel -->
</form>

<?php
require("../../library/include/footer.php");
?>
