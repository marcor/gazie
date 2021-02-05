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
$msg = '';
if ( !isset($_POST['hidden_req']) && isset($_GET['id']) && intval($_GET['id']) >= 1 ) { //al primo accesso allo script per update
	$form=gaz_dbi_get_row($gTables['bank'], 'id',intval($_GET['id']));
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
} elseif (isset($_POST['id_tes'])) { // accessi successivi
    $form = gaz_dbi_parse_post('bank');
    $form['ritorno'] = $_POST['ritorno'];
    $form['hidden_req'] = $_POST['hidden_req'];
} elseif ( !isset($_POST['hidden_req']) && !isset($_GET['id_tes'])) { //al primo accesso allo script per insert
    $form = gaz_dbi_fields('bank');
    $form['iso_country'] = $admin_aziend['country'];
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
	$form['id_municipalities']=0;
}
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/autocomplete'));
?>
<form method="POST" name="form">
<input type="hidden" name="ritorno" value="<?php echo $form['ritorno']; ?>">
<input type="hidden" name="hidden_req" value="<?php echo $form['hidden_req']; ?>">
<?php
$gForm = new informForm();
if ($form['id']>0) {
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['upd_this'] .  $form['id'];
} else {
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['ins_this'];
}
echo "<input type=\"hidden\" value=\"0\" name=\"id\" /></div>\n";
?>
<div class="panel panel-default gaz-table-form div-bordered">
  <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="iso_country" class="col-sm-4 control-label"><?php echo $script_transl['iso_country']; ?></label>
    <?php
$gForm->selectFromDB('country', 'country', 'iso', $form['iso_country'], 'iso', 0, ' - ', 'name');
    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="codabi" class="col-sm-4 control-label"><?php echo $script_transl['codabi']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['codabi']; ?>" name="codabi" maxlength="5"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="descriabi" class="col-sm-4 control-label"><?php echo $script_transl['descriabi']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['descriabi']; ?>" name="descriabi" maxlength="50"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="codcab" class="col-sm-4 control-label"><?php echo $script_transl['codcab']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['codcab']; ?>" name="codcab" maxlength="5"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="descricab" class="col-sm-4 control-label"><?php echo $script_transl['descricab']; ?> </label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['descricab']; ?>" name="descricab" maxlength="100"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="indiri" class="col-sm-4 control-label"><?php echo $script_transl['indiri']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['indiri']; ?>" name="indiri" maxlength="100"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="id_municipalities" class="col-sm-4 control-label"><?php echo $script_transl['id_municipalities']; ?></label>
    <?php
$gForm->selectFromDB('municipalities', 'id_municipalities', 'id', $form['id_municipalities'], 'name', 1, ' - ', 'name');
    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
</div>
</div>
</form>
<?php
require("../../library/include/footer.php");
?>