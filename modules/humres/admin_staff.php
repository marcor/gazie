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
if ($admin_aziend['mas_staff'] <= 199) { // non ho messo il mastro collaboratori in configurazione azienda
    $msg .= "21+";
}
if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    $form = array_merge(gaz_dbi_parse_post('clfoco'), gaz_dbi_parse_post('staff'), gaz_dbi_parse_post('anagra'));
    $form['ritorno'] = $_POST['ritorno'];
    $form['hidden_req'] = $_POST['hidden_req'];
    $form['e_mail'] = trim($form['e_mail']);
    $form['datnas_Y'] = intval($_POST['datnas_Y']);
    $form['datnas_M'] = intval($_POST['datnas_M']);
    $form['datnas_D'] = intval($_POST['datnas_D']);
	if (substr($form['end_date'],-4)<=1999) {
		$form['end_date'] = '';
	}
    $toDo = 'update';
    if (isset($_POST['Insert'])) {
        $toDo = 'insert';
    }

    if ($form['hidden_req'] == 'toggle') { // e' stato accettato il link ad una anagrafica esistente
        $rs_a = gaz_dbi_get_row($gTables['anagra'], 'id', $form['id_anagra']);
        $form = array_merge($form, $rs_a);
		$form['ragso1']=$rs_a['legrap_pf_cognome'];
		$form['ragso2']=$rs_a['legrap_pf_nome'];
    }

    if (isset($_POST['Submit'])) { // conferma tutto
        // inizio controllo campi
        $real_code = $admin_aziend['mas_staff'] * 1000000 + $form['codice'];
        $rs_same_code = gaz_dbi_dyn_query('*', $gTables['clfoco'], " codice = " . $real_code, "codice", 0, 1);
        $same_code = gaz_dbi_fetch_array($rs_same_code);
        if ($same_code && $toDo == 'insert') { // c'� gi� uno stesso codice ed e' un inserimento
            $form['codice'] ++; // lo aumento di 1
            $msg .= "18+";
        }
		print $real_code.'adflkasdhgkòsdfhgsdhghsdfhgiufdsgdsufg<br><br><hr><br><br>';
        $rs_same_id_contract = gaz_dbi_dyn_query('*', $gTables['staff'], " id_contract = " . $form['id_contract']." AND id_clfoco <> ".$real_code , "id_staff", 0, 1);
        $same_id_contract = gaz_dbi_fetch_array($rs_same_id_contract);
        if ($same_id_contract) { // matricola esistente
            $msg .= "22+";
        }
        require("../../library/include/check.inc.php");
        if (strlen($form["ragso1"]) < 2 || strlen($form["ragso2"]) < 2) {
            $msg.='0+';
        }
        if (empty($form["indspe"])) {
            $msg.='1+';
        }
        // faccio i controlli sul codice postale 
        $rs_pc = gaz_dbi_get_row($gTables['country'], 'iso', $form["country"]);
        $cap = new postal_code;
        if ($cap->check_postal_code($form["capspe"], $form["country"], $rs_pc['postal_code_length'])) {
            $msg.='2+';
        }
        if (empty($form["citspe"])) {
            $msg.='3+';
        }
        if (empty($form["prospe"])) {
            $msg.='4+';
        }
        if (empty($form["sexper"])) {
            $msg.='5+';
        }
        $iban = new IBAN;
        if (!empty($form['iban']) && !$iban->checkIBAN($form['iban'])) {
            $msg.='6+';
        }
        if (!empty($form['iban']) && (substr($form['iban'], 0, 2) <> $form['country'])) {
            $msg.='7+';
        }
        $cf_pi = new check_VATno_TAXcode();
        $r_cf = $cf_pi->check_TAXcode($form['codfis'], $form['country']);
        if (!empty($r_pi)) {
            $msg .= "9+";
        }
        $anagrafica = new Anagrafica();
        if (!empty($r_cf)) {
            $msg .= "11+";
        }
        if (!($form['codfis'] == "") && !($form['codfis'] == "00000000000") && $toDo == 'insert') {
            $partner_with_same_cf = $anagrafica->queryPartners('*', "codice <> " . $real_code . " AND codice BETWEEN " . $admin_aziend['mas_staff'] . "000000 AND " . $admin_aziend['mas_staff'] . "999999 AND codfis = '" . $form['codfis'] . "'", "codfis DESC", 0, 1);
            if ($partner_with_same_cf) { // c'� gi� un lavoratore sul piano dei conti
                $msg .= "12+";
            } elseif ($form['id_anagra'] == 0) { // � un nuovo lavoratore senza anagrafica
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
            $msg .= "14+";
        }

        $uts_datnas = mktime(0, 0, 0, $form['datnas_M'], $form['datnas_D'], $form['datnas_Y']);
        if (!checkdate($form['datnas_M'], $form['datnas_D'], $form['datnas_Y']) && ($admin_aziend['country'] != $form['country'] )) {
            $msg .= "19+";
        }
        if (!filter_var($form['e_mail'], FILTER_VALIDATE_EMAIL) && !empty($form['e_mail'])) {
            $msg .= "20+";
        }

        if (empty($msg)) { // nessun errore
            $form['codice'] = $real_code;
            $form['id_clfoco'] = $real_code;
            $form['datnas'] = date("Ymd", $uts_datnas);
            $form['legrap_pf_cognome'] = trim($form['ragso1']);
            $form['legrap_pf_nome'] = trim($form['ragso2']);
            $form['start_date'] = gaz_format_date($form['start_date'], true);
            $form['end_date'] = gaz_format_date($form['end_date'], true);
            if ($toDo == 'insert') {
                if ($form['id_anagra'] > 0) {
                    gaz_dbi_table_insert('clfoco', $form);
                    gaz_dbi_table_insert('staff', $form);
                } else {
                    $anagrafica->insertPartner($form);
                    gaz_dbi_table_insert('staff', $form);
                }
            } elseif ($toDo == 'update') {
                $anagrafica->updatePartners($form['codice'], $form);
                gaz_dbi_table_update('staff',array('id_clfoco',$form['codice']), $form);
            }
            header("Location: staff_report.php");
            exit;
        }
    } elseif (isset($_POST['Return'])) { // torno indietro
        header("Location: " . $form['ritorno']);
        exit;
    }
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    $anagrafica = new Anagrafica();
    $form = $anagrafica->getPartner(intval($admin_aziend['mas_staff'] * 1000000 + intval($_GET['codice'])));
    $staff = gaz_dbi_get_row($gTables['staff'], 'id_clfoco', $form['codice']);
    $form += $staff;
    $form['codice'] = intval(substr($form['codice'], 3));
    $toDo = 'update';
    $form['search']['id_des'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
    $form['datnas_Y'] = substr($form['datnas'], 0, 4);
    $form['datnas_M'] = substr($form['datnas'], 5, 2);
    $form['datnas_D'] = substr($form['datnas'], 8, 2);
    $form['start_date'] = gaz_format_date($staff['start_date'], false, false);
	if (substr($staff['end_date'],0,4)>1999) {
		$form['end_date'] = gaz_format_date($staff['end_date'], false, false);
	} else {
		$form['end_date'] = '';
	}
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $anagrafica = new Anagrafica();
    $last = $anagrafica->queryPartners('*', "codice BETWEEN " . $admin_aziend['mas_staff'] . "000000 AND " . $admin_aziend['mas_staff'] . "999999", "codice DESC", 0, 1);
    $form = array_merge(gaz_dbi_fields('clfoco'), gaz_dbi_fields('staff'), gaz_dbi_fields('anagra'));
    if (isset($last[0]['codice'])) {
        $form['codice'] = substr($last[0]['codice'], 3) + 1;
    } else {
        $form['codice'] = 1;
    }
    $toDo = 'insert';
    $form['search'] = '';
    $form['country'] = $admin_aziend['country'];
    $form['datnas_Y'] = 1900;
    $form['datnas_M'] = 1;
    $form['datnas_D'] = 1;
    $form['start_date'] = date("d/m/Y");
    $form['end_date'] = '';
    $form['counas'] = $admin_aziend['country'];
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup', 'custom/autocomplete'));
echo "<SCRIPT type=\"text/javascript\">\n";
echo "function toggleContent(currentContent) {
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
var cal = new CalendarPopup();
var calName = '';
function setMultipleValues(y,m,d) {
     document.getElementById(calName+'_Y').value=y;
     document.getElementById(calName+'_M').selectedIndex=m*1-1;
     document.getElementById(calName+'_D').selectedIndex=d*1-1;
}
function setDate(name) {
  calName = name.toString();
  var year = document.getElementById(calName+'_Y').value.toString();
  var month = document.getElementById(calName+'_M').value.toString();
  var day = document.getElementById(calName+'_D').value.toString();
  var mdy = month+'/'+day+'/'+year;
  cal.setReturnFunction('setMultipleValues');
  cal.showCalendar('anchor', mdy);
}
$(function () {
    $('#start_date').datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
    $('#end_date').datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
});

</script>
";
echo "<form method=\"POST\" name=\"form\">\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"" . $form['ritorno'] . "\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['id_anagra'] . "\" name=\"id_anagra\" />\n";
echo "<input type=\"hidden\" name=\"" . ucfirst($toDo) . "\" value=\"\">";
$gForm = new GAzieForm();
if ($toDo == 'insert') {
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['ins_this'] . "</div>\n";
} else {
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['upd_this'] . " '" . $form['codice'] . "'</div>\n";
    echo "<input type=\"hidden\" value=\"" . $form['codice'] . "\" name=\"codice\" />\n";
}
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="3" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
    if (isset($anagra)) {
        echo "<tr style=\"cursor:pointer;\">\n";
        echo "\t <td>\n";
        echo "\t </td>\n";
        echo "<td colspan=\"2\"><div onmousedown=\"toggleContent('id_anagra')\" class=\"FacetDataTDred\">";
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
        echo "</div></table></div>\n";
        echo "\t </td>\n";
        echo "</tr>\n";
    }
}
if ($toDo == 'insert') {
    echo "<tr>\n";
    echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['codice'] . "* </td>\n";
    echo "\t<td colspan=\"2\" class=\"FacetDataTD\"><input type=\"text\" name=\"codice\" value=\"" . $form['codice'] . "\" align=\"right\" maxlength=\"6\"  /></td>\n";
    echo "</tr>\n";
}
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['ragso1'] . "* </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"ragso1\" value=\"" . $form['ragso1'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['ragso2'] . "* </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"ragso2\" value=\"" . $form['ragso2'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_contract'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"id_contract\" value=\"" . $form['id_contract'] . "\" align=\"right\" maxlength=\4\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['job_title'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"job_title\" value=\"" . $form['job_title'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "</tr>\n";
echo "</tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['Codice_CCNL'] . " </td>\n";
echo "\t<td class=\"FacetDataTD\">
      <input type=\"text\" name=\"Codice_CCNL\" id=\"search_Codice_CCNL\" value=\"" . $form['Codice_CCNL'] . "\" align=\"right\" maxlength=\"30\"  /></td><td class=\"FacetDataTD\"></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['sexper'] . "*</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('sexper', $script_transl['sexper_value'], $form['sexper']);
echo "\t </td>\n";
echo "</tr>\n";
/** ENRICO FEDELE */
/* Cambiato l'ordine dei campi per renderlo pi� coerente con l'autocompletamento (prima il campo comune che ha la funzione attiva) */
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['citspe'] . " *  </td>\n";
echo "\t<td class=\"FacetDataTD\">
      <input type=\"text\" name=\"citspe\" id=\"search_location\" value=\"" . $form['citspe'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "\t<td class=\"FacetDataTD\">
      <input type=\"text\" name=\"prospe\" id=\"search_location-prospe\" value=\"" . $form['prospe'] . "\" align=\"right\" maxlength=\"2\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['indspe'] . " * </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"indspe\" value=\"" . $form['indspe'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['capspe'] . " * </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"capspe\" id=\"search_location-capspe\" value=\"" . $form['capspe'] . "\" align=\"right\" maxlength=\"10\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['country'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('country', 'country', 'iso', $form['country'], 'iso', 0, ' - ', 'name');
echo "</td>\n";
echo "</tr>\n";
/** ENRICO FEDELE */
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['datnas'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('datnas', $form['datnas_D'], $form['datnas_M'], $form['datnas_Y']);
echo "\t</td>\n";
echo "</tr>\n";
echo "<tr>\n";

/** ENRICO FEDELE */
/* Aggiunto id per autocompletamento */
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['luonas'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" id=\"search_luonas\" name=\"luonas\" value=\"" . $form['luonas'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['pronas'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" id=\"search_pronas\" name=\"pronas\" value=\"" . $form['pronas'] . "\" align=\"right\" maxlength=\"2\"  /></td>\n";
echo "</tr>\n";
/** ENRICO FEDELE */
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['counas'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('country', 'counas', 'iso', $form['counas'], 'iso', 1, ' - ', 'name');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['telefo'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"telefo\" value=\"" . $form['telefo'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['cell'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"cell\" value=\"" . $form['cell'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['codfis'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"codfis\" value=\"" . $form['codfis'] . "\" align=\"right\" maxlength=\"16\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['e_mail'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"e_mail\" value=\"" . $form['e_mail'] . "\" align=\"right\" maxlength=\"50\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['iban'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"iban\" value=\"" . $form['iban'] . "\" align=\"right\" maxlength=\"27\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['status'] . "</td><td class=\"FacetDataTD\" colspan=\"2\">\n";
$gForm->variousSelect('status', $script_transl['status_value'], $form['status'], 'FacetSelect', false);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['annota'] . "</td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"annota\" value=\"" . $form['annota'] . "\" align=\"right\" maxlength=\"100\"  /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['start_date'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
?>
<input type="text" class="form-control" id="start_date" name="start_date" value="<?php echo $form['start_date']; ?>">
<?php
echo "\t</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['end_date'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
?>
<input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo $form['end_date']; ?>">
<?php
echo "\t</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['last_hourly_cost'] . "</td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"number\" name=\"last_hourly_cost\" value=\"" . $form['last_hourly_cost'] . "\" min=\"0\" max=\"1000\" step=\"0.01\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['sqn'] . "</td>";
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\">\n";
echo '<input name="Return" type="submit" value="' . $script_transl['return'] . '">';
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\" align=\"right\">\n";
echo '<input name="Submit" type="submit" value="' . ucfirst($script_transl[$toDo]) . '">';
echo "\t </td>\n";
echo "</tr>\n";
?>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>