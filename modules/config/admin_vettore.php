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
$msg = array('err' => array(), 'war' => array());
if ( !isset($_POST['hidden_req']) && isset($_GET['codice']) && intval($_GET['codice']) >= 1 ) { //al primo accesso allo script per update
    $vettor = gaz_dbi_get_row($gTables['vettor'], 'codice', intval($_GET['codice']));
    $anagra = gaz_dbi_get_row($gTables['anagra'], 'id', $vettor['id_anagra']);
    $form=array_merge($vettor,$anagra);
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
} elseif ( !isset($_POST['hidden_req']) && !isset($_GET['codice'])) { //al primo accesso allo script per insert
    $form = array_merge(gaz_dbi_fields('vettor'), gaz_dbi_fields('anagra'));
    $form['codice'] = substr($last[0]['codice'], 3) + 1;
    $toDo = 'insert';
    $form['country'] = $admin_aziend['country'];
    $form['id_language'] = $admin_aziend['id_language'];
    $form['id_currency'] = $admin_aziend['id_currency'];
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
} elseif (isset($_POST['codice'])) { // accessi successivi
    $form = array_merge(gaz_dbi_parse_post('vettor'), gaz_dbi_parse_post('anagra'));
    $form['ritorno'] = $_POST['ritorno'];
    $form['hidden_req'] = $_POST['hidden_req'];
    $form['pec_email'] = trim($form['pec_email']);
    $form['e_mail'] = trim($form['e_mail']);
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }
    $toDo = 'update';
    if (isset($_POST['Insert'])) {
        $toDo = 'insert';
    }
    if ($form['hidden_req'] == 'toggle') { // e' stato accettato il link ad una anagrafica esistente
        $rs_a = gaz_dbi_get_row($gTables['anagra'], 'id', $form['id_anagra']);
        $form = array_merge($form, $rs_a);
    }

    if (isset($_POST['Submit'])) { // conferma tutto
        // inizio controllo campi
        $real_code = $admin_aziend['mascli'] * 1000000 + $form['codice'];
        $rs_same_code = gaz_dbi_dyn_query('*', $gTables['vettor'], " codice = " . $real_code, "codice", 0, 1);
        $same_code = gaz_dbi_fetch_array($rs_same_code);
        if ($same_code && ($toDo == 'insert')) { // c'� gi� uno stesso codice ed e' un inserimento
            $form['codice'] ++; // lo aumento di 1
            $msg .= "18+";
        }
        require("../../library/include/check.inc.php");
        if (strlen($form["ragso1"]) < 3) {
            if (!empty($form["legrap_pf_nome"]) && !empty($form["legrap_pf_cognome"]) && $form["sexper"] != 'G') {// setto la ragione sociale con l'eventuale legale rappresentante
                $form["ragso1"] = strtoupper($form["legrap_pf_cognome"] . ' ' . $form["legrap_pf_nome"]);
            } else { // altrimenti do errore                
                $msg .= '0+';
            }
        }
        if (empty($form["indspe"])) {
            $msg .= '1+';
        }
        // faccio i controlli sul codice postale
        $rs_pc = gaz_dbi_get_row($gTables['country'], 'iso', $form["country"]);
        $cap = new postal_code;
        if ( gaz_dbi_get_row($gTables['company_config'], 'var', 'check_cust_address')==1 ) {
            if ($cap->check_postal_code($form["capspe"], $form["country"], $rs_pc['postal_code_length'])) {
                $msg .= '2+';
            }
            if (empty($form["citspe"])) {
                $msg .= '3+';
            }
            if (empty($form["prospe"])) {
                $msg .= '4+';
            }
        }

        if (empty($form["sexper"])) {
            $msg .= '5+';
        }
        $iban = new IBAN;
        if (!empty($form['iban']) && !$iban->checkIBAN($form['iban'])) {
            $msg .= '6+';
        }
        if (!empty($form['iban']) && (substr($form['iban'], 0, 2) <> $form['country'])) {
            $msg .= '7+';
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
        if (!empty($r_pi) || ( $form['sexper']=='G' && intval(substr($form['codfis'],0,1)) < 8 && $form['country']=='IT' && strlen(trim($form['pariva'])) < 11 )) {
			// se la partita iva è sbagliata o un cliente persona giuridica senza partita iva e non ha un codice fiscale di una associazione 
            $msg .= "9+";
        }
        if ($form['codpag'] < 1) {
            $msg .= "17+";
        }
        $anagrafica = new Anagrafica();
        if (!empty($form['pariva']) && !($form['pariva'] == '00000000000')) {
            $partner_with_same_pi = $anagrafica->queryPartners('*', "codice <> " . $real_code . " AND codice BETWEEN " . $admin_aziend['mascli'] . "000000 AND " . $admin_aziend['mascli'] . "999999 AND pariva = '" . $form['pariva'] . "'", "pariva DESC", 0, 1);
            if ($partner_with_same_pi) {
                if ($partner_with_same_pi[0]['fe_cod_univoco'] == $form['fe_cod_univoco']) { // c'� gi� un cliente sul piano dei conti ed � anche lo stesso ufficio ( amministrativo della PA )
                    $msg .= "10+";
                }
            } elseif ($form['id_anagra'] == 0) { // � un nuovo cliente senza anagrafica
                $rs_anagra_with_same_pi = gaz_dbi_query_anagra(array("*"), $gTables['anagra'], array("pariva" => "='" . $form['pariva'] . "'"), array("pariva" => "DESC"), 0, 1);
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
        if (!empty($form['codfis']) && !($form['codfis'] == '00000000000')) {
            $partner_with_same_cf = $anagrafica->queryPartners('*', "codice <> " . $real_code . " AND codice BETWEEN " . $admin_aziend['mascli'] . "000000 AND " . $admin_aziend['mascli'] . "999999 AND codfis = '" . $form['codfis'] . "'", "codfis DESC", 0, 1);
            if ($partner_with_same_cf) { // c'� gi� un cliente sul piano dei conti
                if ($partner_with_same_cf[0]['fe_cod_univoco'] == $form['fe_cod_univoco']) { // c'� gi� un cliente sul piano dei conti ed � anche lo stesso ufficio ( amministrativo della PA )
                    $msg .= "12+";
                }
            } elseif ($form['id_anagra'] == 0) { // � un nuovo cliente senza anagrafica
                $rs_anagra_with_same_cf = gaz_dbi_query_anagra(array("*"), $gTables['anagra'], array("codfis" => "='" . $form['codfis'] . "'"), array("codfis" => "DESC"), 0, 1);
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

        if (!filter_var($form['pec_email'], FILTER_VALIDATE_EMAIL) && !empty($form['pec_email'])) {
            $msg .= "20+";
        }

        if (!filter_var($form['e_mail'], FILTER_VALIDATE_EMAIL) && !empty($form['e_mail'])) {
            $msg .= "20+";
        }
		// il codice SIAN deve essere univoco nell'ambito clienti e fornitori
		if (intval($form['id_SIAN'])>0){
			$rs_same_code = gaz_dbi_dyn_query('*', $gTables['anagra'], " id_SIAN = " . $form['id_SIAN']);
			$rows=mysqli_num_rows($rs_same_code);
			if ($rows>0 && ($toDo == 'insert')) { // c'� gi� uno stesso codice
				$form['id_SIAN'] ++; // lo aumento di 1
				$msg .= "21+";
			} 
			if ($toDo == 'update') {		 
				foreach ($rs_same_code as $row){
					if ($row['ragso1']!==$form['ragso1'] AND $row['id_SIAN']==$form['id_SIAN']){
						$form['id_SIAN'] ++; // c'� gi� uno stesso codice lo aumento di 1
						$msg .= "21+";
					}
				}
			}
		}

        if (empty($msg)) { // nessun errore
            $form['codice'] = $real_code;
            $form['datnas'] = date("Ymd", $uts_datnas);
            if ($toDo == 'insert') {
                if (!empty($form['fe_cod_univoco']) && $form['fatt_email'] <= 1) { // qui forzo all'utilizzo della PEC i nuovi clienti dalla PA 
                    $form['fatt_email'] = 2;
                }
                if ($form['id_anagra'] > 0) {
                    gaz_dbi_table_insert('vettor', $form);
                } else {
                    $anagrafica->insertPartner($form);
                }
            } elseif ($toDo == 'update') {
                $anagrafica->updatePartners($form['codice'], $form);
            }
            header("Location: report_client.php?codice=".intval(substr($real_code,-6))."&privacy=" . $form['codice']);
            exit;
        }
    } elseif (isset($_POST['Return'])) { // torno indietro
        header("Location: " . $form['ritorno']);
        exit;
    }
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/autocomplete'));
$gForm = new configForm();
$upd=($form['codice']>0)?'_upd':'';
?>
<SCRIPT type="text/javascript">
function toggleContent(currentContent) {
        var thisContent = document.getElementById(currentContent);
        if ( thisContent.style.display == 'none') {
           thisContent.style.display = '';
           return;
        }
        thisContent.style.display = 'none';
}

function selectValue(currentValue) {
   document.form.id_anagra.value=currentValue;
   document.form.hidden_req.value='toggle';
   document.form.submit();
}

?>
</script>
<form role="form" method="post" name="pay_riba" enctype="multipart/form-data" >
    <input type="hidden" value="<?php echo $form['codice'] ?>" name="codice" />
    <input type="hidden" value="<?php echo $form['hidden_req'] ?>" name="hidden_req" />
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
<div class="text-center">
   <p><b><?php echo $script_transl['title'.$upd]; ?></b></p>
</div>
<?php
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
if (count($msg['war']) > 0) { // ho un alert
    $gForm->gazHeadMessage($msg['war'], $script_transl['war'], 'war');
}

if (!empty($msg)) {
   if (isset($anagrafica)) {
        echo "<tr>\n";
        echo "\t <td>\n";
        echo "\t </td>\n";
        echo "<td colspan=\"2\"><div onmousedown=\"toggleContent('id_anagra')\" class=\"FacetDataTDred\" style=\"cursor:pointer;\">";
        echo ' &dArr; ' . $script_transl['link_anagra'] . " &dArr;</div>\n";
        echo "<div style=\"display: ;\" class=\"selectContainer\" id=\"id_anagra\" onclick=\"selectValue('" . $anagra['id'] . "');\" >\n";
        echo "<div class=\"selectHeader\"> ID = " . $anagra['id'] . "</div>\n";
        echo '<table cellspacing="0" cellpadding="0" width="100%" class="selectTable">';
        echo "\n<tr class=\"odd\"><td>" . $script_transl['ragso1'] . " </td><td> " . $anagra['ragso1'] . "</td></tr>\n";
        echo "<tr class=\"even\"><td>" . $script_transl['ragso2'] . " </td><td> " . $anagra['ragso2'] . "</td></tr>\n";
        echo "<tr class=\"odd\"><td>" . $script_transl['sexper'] . " </td><td> " . $anagra['sexper'] . "</td></tr>\n";
        echo "<tr class=\"even\"><td>" . $script_transl['indspe'] . " </td><td> " . $anagra['indspe'] . "</td></tr>\n";
        echo "<tr class=\"odd\"><td>" . $script_transl['capspe'] . " </td><td> " . $anagra['capspe'] . "</td></tr>\n";
        echo "<tr class=\"even\"><td>" . $script_transl['citspe'] . " </td><td> " . $anagra['citspe'] . " (" . $anagra['prospe'] . ")</td></tr>\n";
        echo "<tr class=\"odd\"><td>" . $script_transl['telefo'] . " </td><td> " . $anagra['telefo'] . "</td></tr>\n";
        echo "<tr class=\"even\"><td>" . $script_transl['cell'] . " </td><td> " . $anagra['cell'] . "</td></tr>\n";
        echo "<tr class=\"odd\"><td>" . $script_transl['fax'] . " </td><td> " . $anagra['fax'] . "</td></tr>\n";
        echo "</div></table></div>\n";
        echo "\t </td>\n";
        echo "</tr>\n";
    }
}
?>
<div class="panel panel-default gaz-table-form div-bordered">
  <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="ragso1" class="col-sm-4 control-label"><?php echo $script_transl['ragso1']; ?> *</label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['ragso1']; ?>" name="ragso1" minlenght="4" maxlength="50"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="ragso2" class="col-sm-4 control-label"><?php echo $script_transl['ragso2']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['ragso2']; ?>" name="ragso2" maxlength="50"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="sexper" class="col-sm-4 control-label"><?php echo $script_transl['sexper']; ?> </label>
    <?php
$gForm->variousSelect('sexper', $script_transl['sexper_value'], $form['sexper']);
    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="indspe" class="col-sm-4 control-label"><?php echo $script_transl['indspe']; ?></label>
                    <input class="col-sm-8" type="text" value="<?php echo $form['indspe']; ?>" name="indspe" maxlength="50"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="capspe" class="col-sm-4 control-label"><?php echo $script_transl['capspe']; ?></label>
                    <input class="col-sm-4" type="text" id="search_location-capspe" value="<?php echo $form['capspe']; ?>" name="capspe" maxlength="10"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="citspe" class="col-sm-4 control-label"><?php echo $script_transl['citspe']; ?></label>
                    <input class="col-sm-4" type="text" id="search_location" value="<?php echo $form['citspe']; ?>" name="citspe" maxlength="60"/> 
                    <div class="text-right"><input class="col-sm-1" type="text" id="search_location-prospe" value="<?php echo $form['prospe']; ?>" name="prospe" maxlength="2"/></div>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="country" class="col-sm-4 control-label"><?php echo $script_transl['country']; ?></label>
    <?php
$gForm->selectFromDB('country', 'country', 'iso', $form['country'], 'iso', 0, ' - ', 'name');
    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="id_language" class="col-sm-4 control-label"><?php echo $script_transl['id_language']; ?></label>
    <?php
$gForm->selectFromDB('languages', 'id_language', 'lang_id', $form['id_language'], 'lang_id', 1, ' - ', 'title_native');
    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="id_currency" class="col-sm-4 control-label"><?php echo $script_transl['id_currency']; ?></label>
    <?php
$gForm->selectFromDB('currencies', 'id_currency', 'id', $form['id_currency'], 'id', 1, ' - ', 'curr_name');
    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="conducente" class="col-sm-4 control-label"><?php echo $script_transl['conducente']; ?> </label>
                    <input class="col-sm-4" type="text" value="<?php echo $form['conducente']; ?>" name="conducente" maxlength="100"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="telefo" class="col-sm-4 control-label"><?php echo $script_transl['telefo']; ?> </label>
                    <input class="col-sm-4" type="text" value="<?php echo $form['telefo']; ?>" name="telefo" maxlength="50"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="targa" class="col-sm-4 control-label"><?php echo $script_transl['targa']; ?> </label>
                    <input class="col-sm-4" type="text" value="<?php echo $form['targa']; ?>" name="targa" maxlength="20"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="pec_email" class="col-sm-4 control-label"><?php echo $script_transl['pec_email']; ?></label>
                    <input class="col-sm-4" type="text" value="<?php echo $form['pec_email']; ?>" name="pec_email" id="pec_email" maxlength="60"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="e_mail" class="col-sm-4 control-label"><?php echo $script_transl['e_mail']; ?></label>
                    <input class="col-sm-4" type="text" value="<?php echo $form['e_mail']; ?>" name="e_mail" id="email" maxlength="60"/>
                </div>
            </div>
        </div><!-- chiude row  -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="annota" class="col-sm-4 control-label"><?php echo $script_transl['annota']; ?> </label>
                    <textarea name="annota" rows="2" cols="50" maxlength="3000"><?php echo $form['annota']; ?></textarea>
                </div>
            </div>
        </div><!-- chiude row  -->
  </div>
</div>
</div>
</form>
<?php
print_r($form);
require("../../library/include/footer.php");
?>