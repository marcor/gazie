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

function getMovimentiPeriodo($trimestre_liquidabile) {
    global $gTables, $admin_aziend;
    // ricavo le date dei periodi da liquidare in base all'ultimo trimestre e alle impostazioni aziendali
    $y = substr($trimestre_liquidabile, 0, 4);
    $trimestre = substr($trimestre_liquidabile, 4, 1);
    $m = $trimestre * 3 - 2;
    $date_ini = new DateTime($y . '-' . $m . '-1');
    $di = $date_ini->format('Y-m-d');
    if ($admin_aziend['ivam_t'] == 'T') { // un unico modulo per tutto il TRIMESTRE
        $date_ini->modify('+2 month');
        $df = $date_ini->format('Y-m-t');
        $mod_periodi = array(0 => array('ini' => $di, 'fin' => $df, 'mese_trimestre' => $trimestre));
    } else { // moduli MENSILI
        $df = $date_ini->format('Y-m-t');
        $mod_periodi = array(0 => array('ini' => $di, 'fin' => $df, 'mese_trimestre' => $date_ini->format('m')));
        $date_ini->modify('+1 month');
        $di = $date_ini->format('Y-m-d');
        $df = $date_ini->format('Y-m-t');
        $mod_periodi[] = array('ini' => $di, 'fin' => $df, 'mese_trimestre' => $date_ini->format('m'));
        $date_ini->modify('+1 month');
        $di = $date_ini->format('Y-m-d');
        $df = $date_ini->format('Y-m-t');
        $mod_periodi[] = array('ini' => $di, 'fin' => $df, 'mese_trimestre' => $date_ini->format('m'));
    }
    foreach ($mod_periodi as $date) {
        if ($admin_aziend['ivam_t'] == "T") {
            $np = null;
        } else {
            $np = str_pad(" " . strftime('%B', mktime(0, 0, 0, $date['mese_trimestre'])) . " " . $y . " ", 20, "*", STR_PAD_BOTH);
        }
        $acc[$date['mese_trimestre']] = array(
            'periodicità' => $admin_aziend['ivam_t'], 'anno' => $y, 'nome_periodo' => $np,
            'vp2' => 0, 'vp3' => 0, 'vp4' => 0, 'vp5' => 0, 'vp7' => 0, 'vp8' => 0, 'vp9' => 0, 'vp10' => 0, 'vp11' => 0, 'vp12' => 0, 'vp13' => 0
        );
        //recupero tutti i movimenti iva dei periodi
        $sqlquery = "SELECT seziva,regiva,codiva,aliquo," . $gTables['aliiva'] . ".tipiva," . $gTables['aliiva'] . ".descri,
                SUM(imponi*(operat = 1) - imponi*(operat = 2)) AS imponibile,
                SUM(impost*(operat = 1) - impost*(operat = 2)) AS iva
                FROM " . $gTables['rigmoi'] . "
                LEFT JOIN " . $gTables['tesmov'] . " ON " . $gTables['rigmoi'] . ".id_tes = " . $gTables['tesmov'] . ".id_tes
                LEFT JOIN " . $gTables['aliiva'] . " ON " . $gTables['rigmoi'] . ".codiva = " . $gTables['aliiva'] . ".codice
                WHERE datreg BETWEEN '" . $date['ini'] . "' AND '" . $date['fin'] . "' GROUP BY seziva,regiva,codiva ORDER BY seziva,regiva,aliquo DESC";
        $rs = gaz_dbi_query($sqlquery);
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
            if ($r['regiva'] > 5) { // acquisti
                $acc[$date['mese_trimestre']]['vp3'] += $r['imponibile'];
                $acc[$date['mese_trimestre']]['vp5'] += $r['iva'];
            } else { // vendite
                $acc[$date['mese_trimestre']]['vp2'] += $r['imponibile'];
                $acc[$date['mese_trimestre']]['vp4'] += $r['iva'];
            }
        }
    }
    return $acc; // nell'accumulatore gli array con i dati per riempire il form
}

if (!isset($_POST['ritorno'])) {
    // al primo accesso allo script
    $form['mods'] = array();
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    if ((isset($_GET['Update']) && isset($_GET['id']))) { // è una modifica
    } else { // è un inserimento
        // controllo se ad oggi è possibile fare una liquidazione
        $y = date('Y');
        $form['y'] = $y;
        $m = date('m') % 3 - 1;
        if ($m == 0) {
            $y--;
            $m = 4;
        }
        $trimestre_liquidabile = $y . $m;
        $form['trimestre_liquidabile'] = $trimestre_liquidabile;
        // cerco l'ultimo file xml generato
        $rs_query = gaz_dbi_dyn_query("*", $gTables['liquidazioni_iva'], 1, "anno DESC, mese_trimestre DESC", 0, 1);
        $ultima_liquidazione = gaz_dbi_fetch_array($rs_query);
        if ($ultima_liquidazione) {
            if ($ultima_liquidazione['periodicità'] == 'T') { // ho fatto una liquidazione trimestrale
                $ultimo_trimestre_liquidato = $ultima_liquidazione['anno'] . $ultima_liquidazione['mese'];
            } else {
                $m = $ultima_liquidazione['mese'] % 3 - 1;
                if ($m == 0) {
                    $ultima_liquidazione['anno'] --;
                    $m = 4;
                }
                $ultimo_trimestre_liquidato = $ultima_liquidazione['anno'] . $m;
            }
        } else { // non ho mai fatto liquidazioni, propongo la prima da fare
            $ultimo_trimestre_liquidato = 0;
        }
        if ($ultimo_trimestre_liquidato >= $trimestre_liquidabile) {
            $msg['err'][] = "eseguita";
        } else {
            // propongo una liquidazione in base ai dati che trovo sui movimenti IVA
            $d = getMovimentiPeriodo($trimestre_liquidabile);
            $form['mods'] = $d;
        }
    }
} else { // nei post successivi (submit)
    $form = $_POST; // dovrò fare il parsing per la sicurezza
    if (isset($_POST['Submit'])) {
        require("../../library/include/agenzia_entrate.inc.php");
        creaFileIVP17($admin_aziend, $form);
        exit;
    }
}

if ((isset($_GET['Update']) && !isset($_GET['id']))) {
    header("Location: " . $form['ritorno']);
    exit;
}

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

// Se viene inviata la richiesta di conferma totale ...
if (isset($_POST['ins'])) {
    if (count($msg['err']) < 1) { // nessun errore
        if ($toDo == 'update') { // e' una modifica
            tesdocUpdate(array('id_tes', $form['id_tes']), $form);
            header("Location: " . $form['ritorno']);
            exit;
        } else { // e' un'inserimento
            header("Location: comunicazione_liquidazioni_report.php");
            exit;
        }
    }
}

require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new contabForm();
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
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
        <input type="hidden" name="<?php echo ucfirst($toDo); ?>" value="">
        <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
        <input type="hidden" value="<?php echo $form['y']; ?>" name="y">
        <input type="hidden" value="<?php echo $form['trimestre_liquidabile']; ?>" name="trimestre_liquidabile">
        <div class="text-center"><b><?php echo $script_transl['title'] . ' ' . $script_transl['periodo_val'][substr($form['trimestre_liquidabile'], 4, 1)] . ' ' . $script_transl['ivam_t_val']['T'] . ' ' . $form['y']; ?></b></div>
        <?php
        foreach ($form['mods'] as $k => $v) {
            if (($v['vp4'] - $v['vp5']) >= 0.01) { // debito
                $vp6c = 0.00;
                $vp6d = round($v['vp4'] - $v['vp5'], 2);
            } elseif (($v['vp4'] - $v['vp5']) <= -0.01) { // credito
                $vp6d = 0.00;
                $vp6c = round($v['vp5'] - $v['vp4'], 2);
            } else {
                $vp6d = 0.00;
                $vp6c = 0.00;
            }
            $ImportoDaVersare = round($v['vp4'] - $v['vp5'] + $v['vp7'] - $v['vp8'] - $v['vp9'] - $v['vp10'] - $v['vp11'] + $v['vp12'] - $v['vp13'], 2);
            if ($ImportoDaVersare >= 0.00) { // versamento debito
                $vp14c = 0.00;
                $vp14d = $ImportoDaVersare;
            } else { // da riportare a credito
                $vp14c = -$ImportoDaVersare;
                $vp14d = 0.00;
            }
            ?>
            <input type="hidden" value="<?php echo $v['nome_periodo']; ?>" name="mods[<?php echo $k; ?>][nome_periodo]">
            <div class="panel panel-default gaz-table-form">
                <div class="verticaltext">
                    <div class="verticaltext_content"><?php echo $v['nome_periodo']; ?></div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group">
                                <label for="vp2" class="col-sm-1 col-md-1 col-lg-1 control-label">VP2</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <?php echo $script_transl['vp2']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp2" name="mods[<?php echo $k; ?>][vp2]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp2']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp3" class="col-sm-1 col-md-1 col-lg-1 control-label">VP3</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <?php echo $script_transl['vp3']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp3" name="mods[<?php echo $k; ?>][vp3]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp3']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp4" class="col-sm-1 col-md-1 col-lg-1 control-label">VP4</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <?php echo $script_transl['vp4']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp4" name="mods[<?php echo $k; ?>][vp4]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp4']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp5" class="col-sm-1 col-md-1 col-lg-1 control-label">VP5</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <?php echo $script_transl['vp5']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp5" name="mods[<?php echo $k; ?>][vp5]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp5']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp6" class="col-sm-1 col-md-1 col-lg-1 control-label">VP6</label>
                                <div class="col-sm-6 col-md-6 col-lg-6 bg-warning">
                                    <?php echo $script_transl['vp6']; ?>
                                    <div class="form-control text-center" id="vp6d" name="vp6d" >
                                        <?php echo $vp6d; ?>
                                    </div>
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5 bg-warning">
                                    <?php echo $script_transl['vp6c']; ?>
                                    <div class="form-control text-center" id="vp6c" name="vp6c" >
                                        <?php echo $vp6c; ?>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp7" class="col-sm-1 col-md-1 col-lg-1 control-label">VP7</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <?php echo $script_transl['vp7']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp7" name="mods[<?php echo $k; ?>][vp7]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp7']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp8" class="col-sm-1 col-md-1 col-lg-1 control-label">VP8</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <?php echo $script_transl['vp8']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp8" name="mods[<?php echo $k; ?>][vp8]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp8']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp9" class="col-sm-1 col-md-1 col-lg-1 control-label">VP9</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <?php echo $script_transl['vp9']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp5" name="mods[<?php echo $k; ?>][vp9]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp9']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp10" class="col-sm-1 col-md-1 col-lg-1 control-label">VP10</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <?php echo $script_transl['vp10']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp10" name="mods[<?php echo $k; ?>][vp10]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp10']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp11" class="col-sm-1 col-md-1 col-lg-1 control-label">VP11</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <?php echo $script_transl['vp11']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp10" name="mods[<?php echo $k; ?>][vp11]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp11']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp12" class="col-sm-1 col-md-1 col-lg-1 control-label">VP12</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <?php echo $script_transl['vp12']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp12" name="mods[<?php echo $k; ?>][vp12]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp12']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp13" class="col-sm-1 col-md-1 col-lg-1 control-label">VP13</label>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <?php echo $script_transl['vp13']; ?>
                                    <input type="number" step="0.01" min="0.00" class="form-control" id="vp13" name="mods[<?php echo $k; ?>][vp13]" placeholder="<?php echo ''; ?>" value="<?php echo $v['vp13']; ?>">
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                        <div class="row">
                            <div class="form-group">
                                <label for="vp14" class="col-sm-1 col-md-1 col-lg-1 control-label">VP14</label>
                                <div class="col-sm-6 col-md-6 col-lg-6 bg-warning">
                                    <?php echo $script_transl['vp14']; ?>
                                    <div class="form-control text-center" id="vp14d" name="vp14d" >
                                        <?php echo $vp14d; ?>
                                    </div>
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5 bg-warning">
                                    <?php echo $script_transl['vp14c']; ?>
                                    <div class="form-control text-center" id="vp14c" name="vp14c" >
                                        <?php echo $vp14c; ?>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- chiude row  -->
                    </div><!-- chiude container  -->
                </div><!-- chiude vertical text -->
            </div><!-- chiude panel  -->
        <?php } ?>
        <div class="col-sm-12 text-center"><input name="Submit" type="submit" class="btn btn-warning" value="Genera il file XML per la comunicazione trimestrale dell'IVA" /></div>
    </form>
    <?php
    require("../../library/include/footer.php");
    ?>