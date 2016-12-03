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

function suggestAmm($fixed, $found, $valamm, $no_deduct_cost_rate, $days = 365) {
    $trunk = false;
    $vy = $fixed / 365 * $days;
    $vy = $vy * $valamm / 100;
    if ($vy >= ($fixed - $found)) {
        // se l'ammortamento supera il resido lo tronco
        $vy = $fixed - $found;
        $trunk = round($vy / $fixed * 100, 2);
    }
    $vn = $vy; //$vn contiene la quota annua 
    $vy = round($vy - ($vy * $no_deduct_cost_rate / 100), 2);
    $vn = round($vn - $vy, 2);
    return array($vy, $vn, $trunk);
}

function getAssets($date) {
    /*  funzione per riprendere dal database tutti i beni ammortizzabili 
      e proporre una anteprima di ammortamenti */
    global $gTables, $admin_aziend;
    $ctrl_fix = 0;
    // riprendo i righi da assets
    $from = $gTables['assets'] . ' AS assets ' .
            'LEFT JOIN ' . $gTables['tesmov'] . ' AS tesmov ON assets.id_movcon=tesmov.id_tes ' .
            'LEFT JOIN ' . $gTables['clfoco'] . ' AS fornit ON tesmov.clfoco=fornit.codice ';
    $field = ' assets.*, tesmov.datreg AS dtrtes, tesmov.numdoc AS nudtes, tesmov.datdoc AS dtdtes, tesmov.descri AS destes, fornit.descri as desfor';
    $where = " datreg <= '" . $date . "'";
    $orderby = "acc_fixed_assets ASC, datreg ASC, type_mov ASC, id ASC";
    $result = gaz_dbi_dyn_query($field, $from, $where, $orderby);
    $acc = array();
    while ($row = gaz_dbi_fetch_array($result)) {
        // ad ogni cambio di bene creo un array e sulla radice metto tutti i dati che mi servono sulla intestazione del bene stesso
        if ($ctrl_fix <> $row['acc_fixed_assets']) {
            // azzero i totali delle colonne
            // in ordine di data necessariamente il primo rigo dev'essere l'acquisto
            $acc[$row['acc_fixed_assets']][1] = $row;
            // prendo il valore della immobilizzazione dal rigo contabile
            $f = gaz_dbi_get_row($gTables['rigmoc'], 'codcon', $row['acc_fixed_assets'] . ' AND id_tes = ' . $row['id_movcon']);
            $acc[$row['acc_fixed_assets']][1]['fixed_val'] = $f['import'];
            $acc[$row['acc_fixed_assets']][1]['found_val'] = 0;
            $acc[$row['acc_fixed_assets']][1]['cost_val'] = 0;
            $acc[$row['acc_fixed_assets']][1]['noded_val'] = 0;
            $acc[$row['acc_fixed_assets']][1]['lost_cost'] = 0; // non è più fiscalmente una quota persa ma da segnalare sul libro
            // questi sono i totali
            $acc[$row['acc_fixed_assets']][1]['fixed_tot'] = $f['import'];
            $acc[$row['acc_fixed_assets']][1]['found_tot'] = 0;
            $acc[$row['acc_fixed_assets']][1]['cost_tot'] = 0;
            $acc[$row['acc_fixed_assets']][1]['noded_tot'] = 0;
            // e i subtotali
            $acc[$row['acc_fixed_assets']][1]['fixed_subtot'] = $f['import'];
            $acc[$row['acc_fixed_assets']][1]['found_subtot'] = 0;
            $acc[$row['acc_fixed_assets']][1]['cost_subtot'] = 0;
            $acc[$row['acc_fixed_assets']][1]['noded_subtot'] = 0;

            // ricavo il gruppo e la specie dalla tabella ammortamenti ministeriali 
            $xml = simplexml_load_file('../../library/include/ammortamenti_ministeriali.xml') or die("Error: Cannot create object");
            preg_match("/^([0-9 ]+)([a-zA-Z ]+)$/", $admin_aziend['amm_min'], $m);
            foreach ($xml->gruppo as $vg) {
                if ($vg->gn[0] == $m[1]) {
                    foreach ($vg->specie as $v) {
                        if ($v->ns[0] == $m[2]) {
                            $acc[$row['acc_fixed_assets']][1]['ammmin_gruppo'] = $vg->gn[0] . '-' . $vg->gd[0];
                            $acc[$row['acc_fixed_assets']][1]['ammmin_specie'] = $v->ns[0] . '-' . $v->ds[0];
                            $acc[$row['acc_fixed_assets']][1]['ammmin_ssd'] = $v->ssd[intval($row['ss_amm_min'])] . ' ';
                            $acc[$row['acc_fixed_assets']][1]['ammmin_ssrate'] = $v->ssrate[intval($row['ss_amm_min'])] * 1;
                        }
                    }
                }
            }
        } else {
            //nei movimenti successivi a seconda del tipo di rigo agisco in maniera differente
            switch ($row['type_mov']) {
                case '10' : // incremento valore del bene (accessorio/ampliamento/ammodernamento/manutenzione)
                    break;
                case '50' : // decremento valore del bene per ammortamento
                    // prendo il valore del fondo ammortamento dal rigo contabile
                    $f = gaz_dbi_get_row($gTables['rigmoc'], 'codcon', $row['acc_found_assets'] . "' AND id_tes = '" . $row['id_movcon']);
                    $row['fixed_val'] = 0;
                    $row['fixed_subtot'] = $acc[$row['acc_fixed_assets']][1]['fixed_tot'];
                    $row['found_val'] = $f['import'];
                    $acc[$row['acc_fixed_assets']][1]['found_tot'] += $f['import'];
                    $row['found_subtot'] = $acc[$row['acc_fixed_assets']][1]['found_tot'];
                    // prendo il valore dell'ammortamento dal rigo contabile
                    $c = gaz_dbi_get_row($gTables['rigmoc'], 'codcon', $row['acc_cost_assets'] . "' AND id_tes = '" . $row['id_movcon']);
                    $row['cost_val'] = $c['import'];
                    $acc[$row['acc_fixed_assets']][1]['cost_tot'] += $c['import'];
                    $row['cost_subtot'] = $acc[$row['acc_fixed_assets']][1]['cost_tot'];
                    // prendo il valore della quota indeducibile dal rigo contabile
                    $n = gaz_dbi_get_row($gTables['rigmoc'], 'codcon', $row['acc_no_deduct_cost'] . "' AND id_tes = '" . $row['id_movcon']);
                    $row['noded_val'] = $n['import'];
                    $acc[$row['acc_fixed_assets']][1]['noded_tot'] += $n['import'];
                    $row['noded_subtot'] = $acc[$row['acc_fixed_assets']][1]['noded_tot'];
                    /* non è più fiscalmente una quota persa ma da segnalare sul libro
                     * quindi qui la dovrò calcolare 
                     */
                    $row['lost_cost'] = 0;
                    // aggiungo all'array del bene
                    $acc[$row['acc_fixed_assets']][] = $row;
                    break;
                case '80' : // alienazione parziale
                    break;
                case '90' : // alienazione del bene 
                    break;
            }
        }
        $ctrl_fix = $row['acc_fixed_assets'];
    }
    return $acc;
}

if (isset($_POST['ritorno'])) {
    $form['ritorno'] = filter_input(INPUT_POST, 'ritorno');
    $form['datreg'] = filter_input(INPUT_POST, 'datreg');
    if (!gaz_format_date($form["datreg"], 'chk')) {
        $msg['err'][] = "datreg";
    }
    if (isset($_POST['assets'])) {
        $form['assets'] = filter_input(INPUT_POST, 'assets');
    }
    $form['datreg'] = gaz_format_date($form['datreg'], true);
    $form['assets'] = getAssets($form['datreg']);
    // eventualmente sostituisco le quote con quelle postate
    if (isset($_POST['assets']) && count($form['assets']) > 0) {
        $ctrl_first = true;
        foreach ($_POST['assets'] as $k => $v) {
            $form['assets'][$k]['cost_suggest'] = floatval($v['cost_suggest']);
            $form['assets'][$k]['noded_suggest'] = floatval($v['noded_suggest']);
            $form['assets'][$k]['valamm_suggest'] = floatval($v['valamm_suggest']);
            if (isset($_POST['insert'])) {
                if ($ctrl_first) {
                    // inserisco la testata del movimento contabile unica per tutti i righi
                    $form['caucon'] = 'AMM';
                    $form['descri'] = 'RILEVATE QUOTE AMMORTAMENTO ANNO ' . substr($form['datreg'], 0, 4) . ')';
                    gaz_dbi_table_insert('tesmov', $form);
                    $id_tesmov = gaz_dbi_last_id();
                    $form['id_tes'] = $id_tesmov;
                    $ctrl_first = false;
                }
                // inserisco i righi del movimento contabile
                $form['codcon'] = $form['assets'][$k][1]['acc_found_assets'];
                $form['darave'] = 'A';
                $form['import'] = round($form['assets'][$k]['cost_suggest'] + $form['assets'][$k]['noded_suggest'], 2);
                gaz_dbi_table_insert('rigmoc', $form);
                $form['codcon'] = $form['assets'][$k][1]['acc_cost_assets'];
                $form['darave'] = 'D';
                $form['import'] = $form['assets'][$k]['cost_suggest'];
                gaz_dbi_table_insert('rigmoc', $form);
                if ($form['assets'][$k]['noded_suggest'] >= 0.01) { // se ho valorizzato un costo indeducibile 
                    $form['codcon'] = $form['assets'][$k][1]['acc_no_deduct_cost'];
                    $form['darave'] = 'D';
                    $form['import'] = $form['assets'][$k]['noded_suggest'];
                    gaz_dbi_table_insert('rigmoc', $form);
                }
                // inserisco il movimento sul libro cespiti 
                $form['id_movcon'] = $id_tesmov;
                $form['type_mov'] = 50;
                $form['descri'] = 'AMMORTAMENTO (QUOTA ANNO ' . substr($form['datreg'], 0, 4) . ')';
                $form['a_value'] = $form['import'];
                $form['valamm'] = $form['assets'][$k]['valamm_suggest'];
                $form['acc_fixed_assets'] = $form['assets'][$k][1]['acc_fixed_assets'];
                $form['acc_found_assets'] = $form['assets'][$k][1]['acc_found_assets'];
                $form['acc_cost_assets'] = $form['assets'][$k][1]['acc_cost_assets'];
                $form['acc_no_deduct_cost'] = $form['assets'][$k][1]['acc_no_deduct_cost'];
                $form['no_deduct_cost_rate'] = $form['assets'][$k][1]['no_deduct_cost_rate'];
                gaz_dbi_table_insert('assets', $form);
            }
        }
    }
    if (isset($_POST['insert'])) {
        header("Location: ./report_assets.php");
        exit;
    }
    // riporto datreg al valore postato
    $form['datreg'] = filter_input(INPUT_POST, 'datreg');
} else { // al primo accesso
    $form['ritorno'] = filter_input(INPUT_SERVER, 'HTTP_REFERER');
    $dt = new DateTime();
    $dt->modify('previous year');
    $form['datreg'] = $dt->format('31/12/Y');
    $form['assets'] = getAssets(gaz_format_date($form['datreg'], true));
}

require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<script>
    $(function () {
        $("#datreg").datepicker();
        $("#datreg").change(function () {
            this.form.submit();
        });
        $('.gaz-tooltip').tooltip({html: true, placement: 'auto bottom', delay: {show: 50}});
    });
</script>
<?php
$gForm = new GAzieForm();
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
?>
<form class="form-horizontal" role="form" method="post" id="gaz-form" name="form">
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
    <div class="panel panel-default">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="datreg" class="col-sm-6 control-label"><?php echo $script_transl['title'] . $script_transl['datreg']; ?></label>
                        <input type="text" class="col-sm-2" id="datreg" name="datreg" value="<?php echo $form['datreg']; ?>">
                    </div>
                </div>
            </div><!-- chiude row  -->
            <?php
            $head = true;
            foreach ($form['assets'] as $ka => $va) {
                $r = array();
                // ogni assets ha più righi-movimenti
                foreach ($va as $k => $v) {
                    if ($head) {
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="ammmin_gruppo" class="col-sm-6 control-label"><?php echo $v['ammmin_gruppo'] . $v['ammmin_specie']; ?></label>
                                    <span class="col-sm-6" > <?php echo $v['ammmin_specie']; ?></span>
                                </div>
                            </div>
                        </div><!-- chiude row  -->
                    </div>
                </div><!-- chiude panel panel-default  -->
                <?php
                $head = false;
            }
            if ($v['type_mov'] == 1) {
                $r[0] = [array('head' => $script_transl["asset_des"], 'class' => '', 'value' => '<b>' . $v['descri'] . $script_transl["clfoco"] . $v["desfor"] . $script_transl["movdes"] . $v["nudtes"] . ' - ' . gaz_format_date($v['dtdtes'], false, true) . '</b><br>' . $script_transl['ammmin_ssd'] . ': ' . $v['ammmin_ssd'] . '<br /> Ammortamento normale = ' . $v['ammmin_ssrate'] . '%'),
                    array('head' => '%', 'class' => 'text-center', 'value' => gaz_format_number($v['valamm'])),
                    array('head' => $script_transl["fixed_val"], 'class' => 'text-right',
                        'value' => gaz_format_number($v['fixed_val'])),
                    array('head' => $script_transl["found_val"], 'class' => 'text-right', 'value' => ''),
                    array('head' => $script_transl["cost_val"], 'class' => 'text-right', 'value' => ''),
                    array('head' => $script_transl["noded_val"], 'class' => 'text-right', 'value' => ''),
                    array('head' => $script_transl["rest_val"], 'class' => 'text-right', 'value' => gaz_format_number($v['fixed_val'])),
                    array('head' => $script_transl["lost_cost"], 'class' => 'text-center', 'value' => ''),
                ];
            } else {
                $r[] = [array('head' => $script_transl["asset_des"], 'class' => '',
                'value' => $v['descri'] . $script_transl["clfoco"] . $v["desfor"]),
                    array('head' => '%', 'class' => 'text-center', 'value' => gaz_format_number($v['valamm'])),
                    array('head' => $script_transl["fixed_val"], 'class' => 'text-right',
                        'value' => gaz_format_number($v['fixed_subtot'])),
                    array('head' => $script_transl["found_val"], 'class' => 'text-right', 'value' => gaz_format_number($v['found_subtot'])),
                    array('head' => $script_transl["cost_val"], 'class' => 'text-right', 'value' => gaz_format_number($v['cost_val'])),
                    array('head' => $script_transl["noded_val"], 'class' => 'text-right', 'value' => gaz_format_number($v['noded_val'])),
                    array('head' => $script_transl["rest_val"], 'class' => 'text-right', 'value' => gaz_format_number($v['fixed_subtot'] - $v['found_subtot'])),
                    array('head' => $script_transl["lost_cost"], 'class' => 'text-center', 'value' => gaz_format_number($v['lost_cost'])),
                ];
            }
        }
        // questo è il rigo di input alla fine della tabella di ogni cespite
        // calcolo una proposta d'ammortamento
        $suggest = suggestAmm($v['fixed_subtot'], $v['found_subtot'], $va[1]['valamm'], $va[1]['no_deduct_cost_rate']);
        $disabl = '';
        if ($suggest[2]) {
            // se è stata troncata la percentuale...
            $v['valamm'] = $suggest[2];
        } elseif ($suggest[0] < 0.01) {
            $v['valamm'] = 0.00;
            $disabl = ' disabled ';
        }
        $r[] = [array('head' => $script_transl["suggest_amm"] . ' %', 'class' => 'text-right bg-warning',
        'value' => $script_transl["suggest_amm"] . ' %'),
            array('head' => '%', 'class' => 'text-right numeric bg-warning',
                'value' => '<input ' . $disabl . ' type="number" step="0.01" name="assets[' . $ka . '][valamm_suggest]" value="' . $v['valamm'] . '" maxlength="5" size="4" />'),
            array('head' => $script_transl["fixed_val"], 'class' => 'text-right bg-warning',
                'value' => ''),
            array('head' => '', 'class' => 'text-center bg-warning', 'value' => ''),
            array('head' => $script_transl["cost_val"], 'class' => 'text-right numeric bg-warning',
                'value' => '<input ' . $disabl . ' type="number" step="any" name="assets[' . $ka . '][cost_suggest]" value="' . $suggest[0] . '" maxlength="15" size="4" />'),
            array('head' => $script_transl["noded_val"], 'class' => 'text-right numeric bg-warning',
                'value' => '<input ' . $disabl . ' type="number" step="any" name="assets[' . $ka . '][noded_suggest]" value="' . $suggest[1] . '" maxlength="15" size="4" />'),
            array('head' => '', 'class' => 'text-right bg-warning', 'value' => ''),
            array('head' => '', 'class' => 'text-center bg-warning', 'value' => ''),
        ];
        // fine rigo proposta ammortamento

        $gForm->gazResponsiveTable($r, 'gaz-responsive-table');
    }
    if ($head) {
        ?>
    </div>
    </div><!-- chiude panel panel-default  -->
<?php }
?>
<div class="panel panel-info">
    <div class="container-fluid">
        <div class="col-sm-12 text-right alert-success">
            <div class="form-group">
                <div>
                    <input class="btn-danger" name="insert" type="submit" value="<?php echo strtoupper($script_transl['submit']); ?>!">
                </div>
            </div>
        </div> <!-- chiude row  -->
    </div><!-- chiude container  -->
</div><!-- chiude panel  -->
</form>
<?php
require("../../library/include/footer.php");
?>