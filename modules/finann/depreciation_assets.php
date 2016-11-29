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

function getAssets($date) {
    /*  funzione per riprendere dal database tutti i beni ammortizzabili 
      e proporre una anteprima di ammortamenti */
    global $gTables;
    $from = $gTables['assets'] . ' AS assets ' .
            'LEFT JOIN ' . $gTables['tesmov'] . ' AS tesmov ON assets.id_movcon=tesmov.id_tes ' .
            'LEFT JOIN ' . $gTables['clfoco'] . ' AS fornit ON tesmov.clfoco=fornit.codice ';
    $field = ' assets.*, tesmov.numdoc AS nudtes, tesmov.datdoc AS dtdtes, tesmov.descri AS destes, fornit.descri as desfor';
    $where = " type_mov = 1 AND datreg <= '" . $date . "'";
    $orderby = "datreg ASC";
    //recupero i dati dal DB 
    $result = gaz_dbi_dyn_query($field, $from, $where, $orderby);

    $acc = array();
    while ($row = gaz_dbi_fetch_array($result)) {
        $fixval = gaz_dbi_dyn_query('*', $gTables['rigmoc'], 'codcon = ' . $row['acc_fixed_assets']);
        $fi = 0.00;
        while ($f = gaz_dbi_fetch_array($fixval)) {
            $fi +=$f['import'];
        }
        $found = gaz_dbi_dyn_query('*', $gTables['rigmoc'], 'codcon = ' . $row['acc_found_assets']);
        $fo = 0.00;
        while ($f = gaz_dbi_fetch_array($found)) {
            $fo +=$f['import'];
        }
        $row['movdes'] = $row['destes'] . ' n.' . $row['nudtes'] . ' ' . gaz_format_date($row['dtdtes']);
        $row['fixval'] = $fi;
        $row['fouval'] = $fo;
        $carry = $fi - $fo;
        // calcolo la proposta d'ammortamento annuo
        $row['rate'] = round($fi * $row['valamm'] / 100, 2);
        if ($row['rate'] > $carry) {
            $row['rate'] = $carry;
        }
        $acc[] = $row;
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
    $form['assets'] = getAssets(gaz_format_date($form['datreg'], true));
    // eventualmente sostituisco le quote con quelle postate
    if (isset($_POST['assets']) && count($form['assets']) > 0) {
        foreach ($_POST['assets'] as $k => $v) {
            $form['assets'][$k]['rate'] = floatval($v['rate']);
            if (isset($_POST['insert'])) {
                // inserisco la testata del movimento contabile
                $form['caucon'] = 'AMM';
                $form['datreg'] = gaz_format_date($form['datreg'], true);
                $form['descri'] = 'AMMORTAMENTO (QUOTA ANNO ' . substr($form['datreg'], 0, 4) . ')';
                gaz_dbi_table_insert('tesmov', $form);
                $id_tesmov = gaz_dbi_last_id();
                $form['id_tes'] = $id_tesmov;
                // inserisco i due righi del movimento contabile
                $form['codcon'] = $form['assets'][$k]['acc_found_assets'];
                $form['darave'] = 'A';
                $form['import'] = $form['assets'][$k]['rate'];
                gaz_dbi_table_insert('rigmoc', $form);
                $form['codcon'] = $form['assets'][$k]['acc_cost_assets'];
                $form['darave'] = 'D';
                $form['import'] = $form['assets'][$k]['rate'];
                gaz_dbi_table_insert('rigmoc', $form);
                // inserisco il movimento sul libro cespiti 
                $form['id_movcon'] = $id_tesmov;
                $form['type_mov'] = 50;
                $form['descri'] = 'AMMORTAMENTO (QUOTA ANNO ' . substr($form['datreg'], 0, 4) . ')';
                $form['a_value'] = $form['import'];
                $form['acc_fixed_assets'] = $form['assets'][$k]['acc_fixed_assets'];
                $form['acc_found_assets'] = $form['assets'][$k]['acc_found_assets'];
                $form['acc_cost_assets'] = $form['assets'][$k]['acc_cost_assets'];
                gaz_dbi_table_insert('assets', $form);
            }
        }
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
    <div class="col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
        <div class="col-sm-8 text-right"><b><?php echo $script_transl['title'] . $script_transl['datreg']; ?></b></div>
        <input type="text" class="col-sm-4" id="datreg" name="datreg" value="<?php echo $form['datreg']; ?>">
    </div>
    <?php
    $r = array(0 => array());
    foreach ($form['assets'] as $k => $v) {
        $r[$k] = [array('head' => $script_transl["descri"], 'class' => '',
        'value' => '<span class="gaz-tooltip" title="' . $v['unimis'] . ' ' . floatval($v['quantity']) . ' x ' . $admin_aziend["symbol"] . $v['a_value'] . '" >' . $v['descri'] . $script_transl["clfoco"] . $v["desfor"] . $script_transl["movdes"] . $v['movdes'] . ' </span>'),
            array('head' => '%', 'class' => 'text-center', 'value' => gaz_format_number($v['valamm'])),
            array('head' => $script_transl["fixval"], 'class' => 'text-right',
                'value' => gaz_format_number($v['fixval'])),
            array('head' => $script_transl["accdep"], 'class' => 'text-right', 'value' => gaz_format_number($v['fouval'])),
            array('head' => $script_transl["carry"], 'class' => 'text-right', 'value' => gaz_format_number($v['fixval'] - $v['fouval'])),
            array('head' => $script_transl["rate"], 'class' => 'text-center numeric',
                'value' => '<input type="number" step="0.01" min="0.00" name="assets[' . $k . '][rate]" value="' . number_format($v['rate'], 2, '.', '') . '" maxlength="15" size="4" />'),
            array('head' => $script_transl["lostrate"], 'class' => 'text-center', 'value' => ''),
        ];
    }
    $gForm->gazResponsiveTable($r, 'gaz-responsive-table');
    ?>
    <div class="panel panel-info">
        <div class="container-fluid">
            <div class="row">
                <div class="form-group">
                    <div class="col-sm-12 text-center alert-success">
                        <input name="insert" id="preventDuplicate" onClick="chkSubmit();" type="submit" value="<?php echo strtoupper($script_transl['view']); ?>!">
                    </div>
                </div>
            </div> <!-- chiude row  -->
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
</form>
<?php
require("../../library/include/footer.php");
?>