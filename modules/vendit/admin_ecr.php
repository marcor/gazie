<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend = checkAdmin(9);
$msg = array('err' => array(), 'war' => array());

if (isset($_GET['id_cash'])||isset($_POST['id_cash'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (isset($_POST['id_cash'])) {   //se non e' il primo accesso
  if ($_POST['id_cash']>0) {
      $toDo = 'update';
  } else {
      $toDo = 'insert';
  }
  $form = gaz_dbi_parse_post('cash_register');
  $form['reparti'] = array();
  $nreparto = 0;
  if (isset($_POST['reparti'])) {
    foreach ($_POST['reparti'] as $nreparto => $val) {
      $form['reparti'][$nreparto]['aliiva_codice'] = intval($val['aliiva_codice']);
      $form['reparti'][$nreparto]['reparto'] = substr($val['reparto'], 0, 8);
      $form['reparti'][$nreparto]['descrizione'] = substr($val['descrizione'], 0, 50);
      $nreparto++;
    }
  }
  $ntender = 0;
  if (isset($_POST['tenders'])) {
    foreach ($_POST['tenders'] as $ntender => $val) {
      $form['tenders'][$ntender]['pagame_codice'] = intval($val['pagame_codice']);
      $form['tenders'][$ntender]['tender'] = substr($val['extension'], 0, 8);
      $form['tenders'][$ntender]['descrizione'] = substr($val['descrizione'], 0, 50);
      $ntender++;
    }
  }
  if (isset($_POST['Submit'])) { // conferma tutto
    if ($toDo == 'update') {  // controlli in caso di modifica
    } else { // controlli inserimento
    }
    if (empty($form["descri"])) {
        $msg['err'][] = 'descri';
    }
  }
  if (count($msg['err']) == 0) { // nessun errore
    if ($toDo == 'update') {
    } else {
    }
    header("Location: ../../modules/vendit/report_ecr.php");
    exit;
  }
} elseif (!isset($_POST['id_cash']) && isset($_GET['id_cash'])) { //se e' il primo accesso per UPDATE
    $toDo = 'update';
    $form = gaz_dbi_get_row($gTables['cash_register'], 'id_cash', intval($_GET['id_cash']));
    $form['reparti'] = array();
    // inizio reparti
    $nreparto = 0;
    $rs_row = gaz_dbi_dyn_query("*", $gTables['cash_register_reparto'], "cash_register_id_cash = " . $form['id_cash'], "id_cash_register_reparto DESC");
    while ($row = gaz_dbi_fetch_array($rs_row)) {
        $form['reparti'][$nreparto] = $row;
        $nreparto++;
    }
    // fine reparti
	// inizio tender
    $ntender = 0;
    $rs_row = gaz_dbi_dyn_query("*", $gTables['cash_register_tender'], "cash_register_id_cash = " . $form['id_cash'] , "id_cash_register_tender DESC");
    while ($row = gaz_dbi_fetch_array($rs_row)) {
        $form['tenders'][$ntender] = $row;
        $ntender++;
    }
    // fine tender
} else { //se e' il primo accesso per INSERT
    $toDo = 'insert';
    $form = gaz_dbi_fields('cash_register');
    $form['reparti']=[];
    $form['tenders']=[];
}

require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new venditForm();
// ultimo utente 
$last_urs = gaz_dbi_get_row ($gTables['admin'], 'user_name', $form['adminid'] );
$lu=$last_urs?$last_urs['user_name']:'Mai utilizzato';
?>
<form method="POST" name="formecr">
<?php
    echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    if ($toDo == 'insert') {
        echo '<div class="text-center"><h3>' . $script_transl['ins_this'] . '</h3></div>';
    } else {
        echo '<div class="text-center"><h3>' . $script_transl['upd_this'] . ' ' . $form['id_cash'] . '</h3></div>';
    }
    ?>
            <ul class="nav nav-pills">
                <li class="active"><a data-toggle="pill" href="#home">Dati principali</a></li>
                <li><a data-toggle="pill" href="#reparti">Reparti IVA</a></li>
                <li><a data-toggle="pill" href="#tenders">Tender</a></li>
                <li><a data-toggle="pill" href="#utenti">Utenti</a></li>
                <li style="float: right;"><?php echo '<input name="Submit" type="submit" class="btn btn-warning" value="' . ucfirst($script_transl[$toDo]) . '" />'; ?></li>
            </ul>  

        <div class="panel panel-default gaz-table-form div-bordered">
            <div class="container-fluid">
          
            <div class="tab-content">
              <div id="home" class="tab-pane fade in active">
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="id_cash" class="col-sm-4 control-label"><?php echo $script_transl['id_cash']; ?></label>
                            <input class="col-sm-4" type="text" value="<?php echo $form["id_cash"]; ?>" name="id_cash" maxlength="2" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descri" class="col-sm-4 control-label"><?php echo $script_transl['descri']; ?></label>
                            <input class="col-sm-4" type="text" value="<?php echo $form["descri"]; ?>" name="descri" maxlength="32" />
                        </div>
                    </div>
                </div><!-- chiude row  -->
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="seziva" class="col-sm-4 control-label"><?php echo $script_transl['seziva']; ?></label>
                            <?php $gForm->selectNumber('seziva', $form['seziva'], 0, 1, 9, 'col-sm-1'); ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="adminid" class="col-sm-4 control-label"><?php echo $script_transl['adminid']; ?></label>
                            <?php echo $lu; ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                        <label for="driver" class="col-sm-4 control-label"><?php echo $script_transl['driver']; ?></label>
                        <?php
                        echo '<select name="driver">';
                        $relativePath = '../../library/cash_register';
                        if ($handle = opendir($relativePath)) {
                            echo '<option value=""></option>';
                            while ($file = readdir($handle)) {
                                if (substr($file, 0, 1) == ".") continue;
                                $selected = "";
                                if ($form["driver"] == $file) {
                                    $selected = " selected ";
                                }
                                echo "<option value=\"" . $file . "\"" . $selected . ">" . $file . "</option>";
                            }
                            closedir($handle);
                        }
                        echo "</select>\n";
                        ?>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="path_data" class="col-sm-4 control-label"><?php echo $script_transl['path_data']; ?></label>
                            <input class="col-sm-4" type="text" value="<?php echo $form["path_data"]; ?>" name="path_data" maxlength="1000"/>
                        </div>
                    </div>
                </div><!-- chiude row  -->
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="lotteria_scontrini" class="col-sm-4 control-label"><?php echo $script_transl['lotteria_scontrini']; ?></label>
                            <input class="col-sm-4" type="lotteria_scontrini" value="<?php echo $form["lotteria_scontrini"]; ?>" name="lotteria_scontrini" maxlength="32"/>
                        </div>
                    </div>
                </div><!-- chiude row  -->
              </div><!-- chiude tab-pane  -->
              <div id="reparti" class="tab-pane fade">
              </div><!-- chiude tab-pane  -->
              <div id="tenders" class="tab-pane fade">
              </div><!-- chiude tab-pane  -->
              <div id="utenti" class="tab-pane fade">
              </div><!-- chiude tab-pane  -->
          </div>
        <div class="col-sm-12">
    <?php
    echo '<div class="col-sm-8 text-center"><input name="Submit" type="submit" class="btn btn-warning" value="' . ucfirst($script_transl[$toDo]) . '" /></div>';

?>
            </div>
        </div> <!-- chiude container -->
    </div><!-- chiude panel -->
</form>
<?php
require("../../library/include/footer.php");
?>
