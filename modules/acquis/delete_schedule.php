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
$rs = gaz_dbi_dyn_query($gTables['paymov'] . ".id_tesdoc_ref," . $gTables['tesmov'] . ".descri, " . $gTables['clfoco'] . ".descri AS ragsoc", $gTables['paymov'] . " LEFT JOIN " . $gTables['rigmoc'] . " ON " . $gTables['paymov'] . ".id_rigmoc_doc = " . $gTables['rigmoc'] . ".id_rig
                        LEFT JOIN " . $gTables['tesmov'] . " ON " . $gTables['tesmov'] . ".id_tes = " . $gTables['rigmoc'] . ".id_tes
                        LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['clfoco'] . ".codice = " . $gTables['tesmov'] . ".clfoco", $gTables['paymov'] . ".id_tesdoc_ref = '" . substr($_GET['id_tesdoc_ref'], 0, 15) . "'");
$form = gaz_dbi_fetch_array($rs);
if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if (isset($_POST['Delete'])) {
    gaz_dbi_del_row($gTables['paymov'], 'id_tesdoc_ref', substr($_POST['id_tesdoc_ref'], 0, 15));
    header("Location: " . $_POST['ritorno']);
    exit;
}

if (isset($_POST['Return'])) {
    header("Location: " . $_POST['ritorno']);
    exit;
}

require("../../library/include/header.php");
$script_transl = HeadMain('delete_schedule');
?>
<form method="POST">
    <input type="hidden" name="ritorno" value="<?php print $form['ritorno']; ?>">
    <input type="hidden" name="id_tesdoc_ref" value="<?php print $form['id_tesdoc_ref']; ?>">
    <div class="text-center bg-danger">
        <p>
            <b>
                <?php echo $script_transl['warning'] . '!!! ' . $script_transl['title']; ?>
            </b> 
        </p>
    </div>
    <div class="panel panel-default gaz-table-form">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="id_tesdoc_ref" class="col-sm-4 control-label"><?php echo $script_transl['id_tesdoc_ref']; ?></label>
                        <div class="col-sm-8"><?php echo $form['id_tesdoc_ref']; ?></div>                
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="descri" class="col-sm-4 control-label"><?php echo $script_transl['descri']; ?></label>
                        <div class="col-sm-8"><?php echo $form['descri']; ?></div>                
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ragsoc" class="col-sm-4 control-label"><?php echo $script_transl['ragsoc']; ?></label>
                        <div class="col-sm-8"><?php echo $form['ragsoc']; ?></div>                
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-sm-4 text-danger text-right"><?php echo ucfirst($script_transl['safe']); ?></label>
                        <input type="submit" accesskey="d" name="Delete" class="col-sm-8 bg-danger" value="<?php echo $script_transl['delete']; ?>" >                
                    </div>
                </div>
            </div><!-- chiude row  -->
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
</form>
<?php
require("../../library/include/footer.php");
?>