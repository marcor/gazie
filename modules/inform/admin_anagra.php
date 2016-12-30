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
require("../../modules/vendit/lib.function.php");
$admin_aziend = checkAdmin();
$msg['err'] = array();

if (!isset($_GET['id'])) {
    header("Location: " . $POST['ritorno']);
    exit;
}

if (isset($_POST['ritorno'])) {   //se non e' il primo accesso
    $form = array_merge(gaz_dbi_parse_post('anagra'), gaz_dbi_parse_post('anagra'));
    $form['ritorno'] = $_POST['ritorno'];
    $form['hidden_req'] = $_POST['hidden_req'];
    $form['e_mail'] = trim($form['e_mail']);
    if (isset($_POST['Submit'])) { // conferma tutto
        require("../../library/include/check.inc.php");
        if (strlen($form["ragso1"]) < 4) {
            $msg .= '0+';
        }
        if (empty($form["indspe"])) {
            $msg .= '1+';
        }
        // faccio i controlli sul codice postale 
        $rs_pc = gaz_dbi_get_row($gTables['country'], 'iso', $form["country"]);
        $cap = new postal_code;
        if ($cap->check_postal_code($form["capspe"], $form["country"], $rs_pc['postal_code_length'])) {
            $msg .= '2+';
        }
        if (empty($form["citspe"])) {
            $msg .= '3+';
        }
        if (empty($form["prospe"])) {
            $msg .= '4+';
        }
        if (empty($form["sexper"])) {
            $msg .= '5+';
        }
        $cf_pi = new check_VATno_TAXcode();
        $r_pi = $cf_pi->check_VAT_reg_no($form['pariva'], $form['country']);
        if (strlen(trim($form['codfis'])) == 11) {
            $r_cf = $cf_pi->check_VAT_reg_no($form['codfis'], $form['country']);
            if ($form['sexper'] != 'G') {
                $r_cf = 'Codice fiscale sbagliato per una persona fisica';
                $msg .= '8+';
            }
        } else {
            $r_cf = $cf_pi->check_TAXcode($form['codfis'], $form['country']);
        }
        if (!empty($r_pi)) {
            $msg .= "9+";
        }
        if ($form['codpag'] < 1) {
            $msg .= "17+";
        }
        if (!($form['pariva'] == "") && !($form['pariva'] == "00000000000")) {
            $partner_with_same_pi = $anagrafica->queryPartners('*', "codice <> " . $real_code . " AND codice BETWEEN " . $admin_aziend['mascli'] . "000000 AND " . $admin_aziend['mascli'] . "999999 AND pariva = '" . $form['pariva'] . "'", "pariva DESC", 0, 1);
            if ($partner_with_same_pi) {
                if ($partner_with_same_pi[0]['fe_cod_univoco'] == $form['fe_cod_univoco']) { // c'� gi� un cliente sul piano dei conti ed � anche lo stesso ufficio ( amministrativo della PA )
                    $msg .= "10+";
                }
            } elseif ($form['id_anagra'] == 0) { // � un nuovo cliente senza anagrafica
                $rs_anagra_with_same_pi = gaz_dbi_dyn_query('*', $gTables['anagra'], " pariva = '" . $form['pariva'] . "'", "pariva DESC", 0, 1);
                $anagra_with_same_pi = gaz_dbi_fetch_array($rs_anagra_with_same_pi);
                if ($anagra_with_same_pi) { // c'� gi� un'anagrafica con la stessa PI non serve reinserirlo ma avverto
                    // devo attivare tutte le interfacce per la scelta!
                    $anagra = $anagra_with_same_pi;
                    $msg .= '15+';
                }
            }
        }
        if (!empty($r_cf)) {
            $msg .= "11+";
        }
        if (!($form['codfis'] == "") && !($form['codfis'] == "00000000000")) {
            $partner_with_same_cf = $anagrafica->queryPartners('*', "codice <> " . $real_code . " AND codice BETWEEN " . $admin_aziend['mascli'] . "000000 AND " . $admin_aziend['mascli'] . "999999 AND codfis = '" . $form['codfis'] . "'", "codfis DESC", 0, 1);
            if ($partner_with_same_cf) { // c'� gi� un cliente sul piano dei conti
                if ($partner_with_same_cf[0]['fe_cod_univoco'] == $form['fe_cod_univoco']) { // c'� gi� un cliente sul piano dei conti ed � anche lo stesso ufficio ( amministrativo della PA )
                    $msg .= "12+";
                }
            } elseif ($form['id_anagra'] == 0) { // � un nuovo cliente senza anagrafica
                $rs_anagra_with_same_cf = gaz_dbi_dyn_query('*', $gTables['anagra'], " codfis = '" . $form['codfis'] . "'", "codfis DESC", 0, 1);
                $anagra_with_same_cf = gaz_dbi_fetch_array($rs_anagra_with_same_cf);
                if ($anagra_with_same_cf) { // c'� gi� un'anagrafica con lo stesso CF non serve reinserirlo ma avverto
                    // devo attivare tutte le interfacce per la scelta!
                    $anagra = $anagra_with_same_cf;
                    $msg .= '16+';
                }
            }
        }

        if (empty($form['codfis'])) {
            if ($form['sexper'] == 'G') {
                $msg .= "13+";
                $form['codfis'] = $form['pariva'];
            } else {
                $msg .= "14+";
            }
        }

        $uts_datnas = mktime(0, 0, 0, $form['datnas_M'], $form['datnas_D'], $form['datnas_Y']);
        if (!checkdate($form['datnas_M'], $form['datnas_D'], $form['datnas_Y']) && ($admin_aziend['country'] != $form['country'] )) {
            $msg .= "19+";
        }
        if (!filter_var($form['e_mail'], FILTER_VALIDATE_EMAIL) && !empty($form['e_mail'])) {
            $msg .= "20+";
        }

        if (empty($msg)) { // nessun errore
            $form['datnas'] = date("Ymd", $uts_datnas);
            $anagrafica->updatePartners($form['codice'], $form);
            header("Location: " . $form['ritorno']);
            exit;
        }
    } elseif (isset($_POST['Return'])) { // torno indietro
        header("Location: " . $form['ritorno']);
        exit;
    }
} else { //se e' il primo accesso per UPDATE
    $form = gaz_dbi_get_row($gTables['anagra'], 'id', intval($_GET['id']));
    $form['datnas'] = gaz_format_date($form['datnas'], false);
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/autocomplete'));
$gForm = new venditForm();
?>
<script>
    $(function () {
        $("#datnas").datepicker();
    });
</script>

<?php
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
?>

<form method="POST" name="form" enctype="multipart/form-data" id="add-product" />
<input type="hidden" name="ritorno" value="<?php echo $form['ritorno'] ?>" />
<input type="hidden" name="id" value=" <?php echo $form['id']; ?> " />
<div class="FacetFormHeaderFont text-center"><?php echo $script_transl['title'] . " '" . $form['id'] . "'"; ?></div>
<div class="panel panel-default gaz-table-form">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="ragso1" class="col-sm-4 control-label"><?php echo $script_transl['ragso1']; ?> *</label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['ragso1']; ?>" name="ragso1" maxlength="50" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="ragso2" class="col-sm-4 control-label"><?php echo $script_transl['ragso2']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['ragso2']; ?>" name="ragso2" maxlength="50" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="sedleg" class="col-sm-4 control-label"><?php echo $script_transl['sedleg']; ?></label>
                    <textarea class="col-sm-4" name="sedleg" rows="2" cols="30" maxlength="100" size="50"><?php echo $form['sedleg']; ?></textarea>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="legrap" class="col-sm-4 control-label"><?php echo $script_transl['legrap']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['legrap']; ?>" name="legrap" maxlength="100" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="sexper" class="col-sm-4 control-label"><?php echo $script_transl['sexper']; ?> *</label>
                    <?php
                    $gForm->variousSelect('sexper', $script_transl['sexper_value'], $form['sexper'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
                    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="datnas" class="col-sm-4 control-label"><?php echo $script_transl['datnas']; ?></label>
                    <input type="text" class="col-sm-4" id="datnas" name="datnas" value="<?php echo $form['datnas']; ?>">
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="luonas" class="col-sm-4 control-label"><?php echo $script_transl['luonas']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['luonas']; ?>" name="luonas" maxlength="50" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="pronas" class="col-sm-4 control-label"><?php echo $script_transl['pronas']; ?></label>
                    <input class="col-sm-1" type="text" value="<?php echo $form['pronas']; ?>" name="pronas" maxlength="2" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="counas" class="col-sm-4 control-label"><?php echo $script_transl['counas']; ?> *</label>
                    <?php
                    $gForm->selectFromDB('country', 'counas', 'iso', $form['counas'], 'iso', 1, ' - ', 'name');
                    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="indspe" class="col-sm-4 control-label"><?php echo $script_transl['indspe']; ?> *</label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['indspe']; ?>" name="indspe" maxlength="50" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="capspe" class="col-sm-4 control-label"><?php echo $script_transl['capspe']; ?></label>
                    <input class="col-sm-1" type="text" id="search_location-capspe" value="<?php echo $form['capspe']; ?>" name="capspe" maxlength="10" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="citspe" class="col-sm-4 control-label"><?php echo $script_transl['citspe']; ?> *</label>
                    <input class="col-sm-8" type="text"  id="search_location"  value="<?php echo $form['citspe']; ?>" name="citspe" maxlength="50" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="prospe" class="col-sm-4 control-label"><?php echo $script_transl['prospe']; ?></label>
                    <input class="col-sm-1" type="text"  id="search_location-prospe" value="<?php echo $form['prospe']; ?>" name="prospe" maxlength="2" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="country" class="col-sm-4 control-label"><?php echo $script_transl['country']; ?> *</label>
                    <?php
                    $gForm->selectFromDB('country', 'country', 'iso', $form['country'], 'iso', 1, ' - ', 'name');
                    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="id_language" class="col-sm-4 control-label"><?php echo $script_transl['id_language']; ?> *</label>
                    <?php
                    $gForm->selectFromDB('languages', 'id_language', 'lang_id', $form['id_language'], 'lang_id', 1, ' - ', 'title_native');
                    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="pariva" class="col-sm-4 control-label"><?php echo $script_transl['pariva']; ?></label>
                    <input class="col-sm-3" type="text" value="<?php echo $form['pariva']; ?>" name="pariva" maxlength="11" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="codfis" class="col-sm-4 control-label"><?php echo $script_transl['codfis']; ?></label>
                    <input class="col-sm-3" type="text" value="<?php echo $form['codfis']; ?>" name="codfis" maxlength="16" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="latitude" class="col-sm-4 control-label"><?php echo $script_transl['latitude'] . " - " . $script_transl['longitude']; ?></label>
                    <div class="col-sm-8">
                        <input class="col-sm-3" type="text" name="latitude" value="<?php echo $form['latitude'] ?>" maxlength="10" /><input class="col-sm-3" type="text" name="longitude" value="<?php echo $form['longitude']; ?>" maxlength="10" /><a class="btn btn-xs btn-default btn-default col-sm-2" href="http://maps.google.com/maps?q=<?php echo $form['latitude'] . "," . $form['longitude']; ?>"> maps -> <i class="glyphicon glyphicon-map-marker"></i></a>
                    </div>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="telefo" class="col-sm-4 control-label"><?php echo $script_transl['telefo']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['telefo']; ?>" name="telefo" maxlength="50" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="fax" class="col-sm-4 control-label"><?php echo $script_transl['fax']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['fax']; ?>" name="fax" maxlength="32" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="cell" class="col-sm-4 control-label"><?php echo $script_transl['cell']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['cell']; ?>" name="cell" maxlength="32" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="e_mail" class="col-sm-4 control-label"><?php echo $script_transl['e_mail']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['e_mail']; ?>" name="e_mail" maxlength="50" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="fatt_email" class="col-sm-4 control-label"><?php echo $script_transl['fatt_email']; ?> </label>
                    <?php
                    $gForm->selectNumber('fatt_email', $form['fatt_email'], TRUE, 0, 1);
                    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="fe_cod_univoco" class="col-sm-4 control-label"><?php echo $script_transl['fe_cod_univoco']; ?></label>
                    <input class="col-sm-2" type="text" value="<?php echo $form['fe_cod_univoco']; ?>" name="fe_cod_univoco" maxlength="6" />
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group text-center">
                    <input class="btn btn-warning" name="Submit" type="submit" value="<?php echo strtoupper($script_transl['update']); ?>!">
                </div>
            </div>
        </div><!-- chiude row  -->
    </div> <!-- chiude container --> 
</div><!-- chiude panel -->
</form>
<?php
require("../../library/include/footer.php");
?>