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

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
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
        left: -105px;
        top: 300px;
        color: #000;
        text-transform: uppercase;
        font-size:36px;

    </STYLE>
    <form method="POST" name="form" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
        <div class="text-center"><b><?php echo $script_transl['title']; ?></b></div>
        <div class="panel panel-default gaz-table-form">
            <div class="verticaltext">
                <div class="verticaltext_content">IV° TRIMESTRE</div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="form-group">
                            <label for="vp2" class="col-sm-1 col-md-1 col-lg-1 control-label">VP2</label>
                            <div class="col-sm-6 col-md-6 col-lg-6">
                                <?php echo $script_transl['vp2']; ?>
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp2" name="vp2" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
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
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp3" name="vp3" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
                            </div>
                        </div>
                    </div> <!-- chiude row  -->
                    <div class="row">
                        <div class="form-group">
                            <label for="vp4" class="col-sm-1 col-md-1 col-lg-1 control-label">VP4</label>
                            <div class="col-sm-6 col-md-6 col-lg-6">
                                <?php echo $script_transl['vp4']; ?>
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp4" name="vp4" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
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
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp5" name="vp5" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
                            </div>
                        </div>
                    </div> <!-- chiude row  -->
                    <div class="row">
                        <div class="form-group">
                            <label for="vp6" class="col-sm-1 col-md-1 col-lg-1 control-label">VP6</label>
                            <div class="col-sm-6 col-md-6 col-lg-6 bg-warning">
                                <?php echo $script_transl['vp6']; ?>
                                <div class="form-control" id="vp6d" name="vp6d" >
                                </div>
                            </div>
                            <div class="col-sm-5 col-md-5 col-lg-5 bg-warning">
                                <?php echo $script_transl['vp6c']; ?>
                                <div class="form-control" id="vp6c" name="vp6c" >
                                </div>
                            </div>
                        </div>
                    </div> <!-- chiude row  -->
                    <div class="row">
                        <div class="form-group">
                            <label for="vp7" class="col-sm-1 col-md-1 col-lg-1 control-label">VP7</label>
                            <div class="col-sm-6 col-md-6 col-lg-6">
                                <?php echo $script_transl['vp7']; ?>
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp7" name="vp7" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
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
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp8" name="vp8" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
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
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp5" name="vp9" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
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
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp10" name="vp10" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
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
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp10" name="vp11" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
                            </div>
                        </div>
                    </div> <!-- chiude row  -->
                    <div class="row">
                        <div class="form-group">
                            <label for="vp12" class="col-sm-1 col-md-1 col-lg-1 control-label">VP12</label>
                            <div class="col-sm-6 col-md-6 col-lg-6">
                                <?php echo $script_transl['vp12']; ?>
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp12" name="vp12" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
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
                                <input type="number" step="0.01" min="0.1" max="100" class="form-control" id="vp13" name="vp13" placeholder="<?php echo ''; ?>" value="<?php echo ''; ?>">
                            </div>
                        </div>
                    </div> <!-- chiude row  -->
                    <div class="row">
                        <div class="form-group">
                            <label for="vp14" class="col-sm-1 col-md-1 col-lg-1 control-label">VP14</label>
                            <div class="col-sm-6 col-md-6 col-lg-6 bg-warning">
                                <?php echo $script_transl['vp14']; ?>
                                <div class="form-control" id="vp14d" name="vp14d" >
                                </div>
                            </div>
                            <div class="col-sm-5 col-md-5 col-lg-5 bg-warning">
                                <?php echo $script_transl['vp14c']; ?>
                                <div class="form-control" id="vp14c" name="vp14c" >
                                </div>
                            </div>
                        </div>
                    </div> <!-- chiude row  -->
                </div><!-- chiude container  -->
            </div><!-- chiude vertical text -->
        </div><!-- chiude panel  -->
    </form>
    <?php
    require("../../library/include/footer.php");
    ?>