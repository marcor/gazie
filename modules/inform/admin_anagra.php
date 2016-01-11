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
$msg = '';

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and !isset($_GET['id'])) ) {
    header("Location: " . $POST['ritorno']);
    exit;
}

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
   $form = array_merge(gaz_dbi_parse_post('clfoco'), gaz_dbi_parse_post('anagra'));
   $form['ritorno'] = $_POST['ritorno'];
   $form['hidden_req'] = $_POST['hidden_req'];
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
      $rs_same_code = gaz_dbi_dyn_query('*', $gTables['clfoco'], " codice = " . $real_code, "codice", 0, 1);
      $same_code = gaz_dbi_fetch_array($rs_same_code);
      if ($same_code && ($toDo == 'insert')) { // c'� gi� uno stesso codice ed e' un inserimento
         $form['codice'] ++; // lo aumento di 1
         $msg .= "18+";
      }
      require("../../library/include/check.inc.php");
      if (strlen($form["ragso1"]) < 4) {
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
      $anagrafica = new Anagrafica();
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
         $form['codice'] = $real_code;
         $form['datnas'] = date("Ymd", $uts_datnas);
         if ($toDo == 'insert') {
            if ($form['id_anagra'] > 0) {
               gaz_dbi_table_insert('clfoco', $form);
            } else {
               $anagrafica->insertPartner($form);
            }
         } elseif ($toDo == 'update') {
            $anagrafica->updatePartners($form['codice'], $form);
         }
         header("Location: " . $form['ritorno']);
         exit;
      }
   } elseif (isset($_POST['Return'])) { // torno indietro
      header("Location: " . $form['ritorno']);
      exit;
   }
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
   $form = gaz_dbi_get_row($gTables['anagra'],'id',intval($_GET['id']));
   $toDo = 'update';
   $form['search']['id_des'] = '';
   $form['ritorno'] = $_SERVER['HTTP_REFERER'];
   $form['hidden_req'] = '';
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
   $anagrafica = new Anagrafica();
   $last = $anagrafica->queryPartners('*', "codice BETWEEN " . $admin_aziend['mascli'] . "000000 AND " . $admin_aziend['mascli'] . "999999", "codice DESC", 0, 1);
   $form = array_merge(gaz_dbi_fields('clfoco'), gaz_dbi_fields('anagra'));
   $form['codice'] = substr($last[0]['codice'], 3) + 1;
   $toDo = 'insert';
   $form['search']['id_des'] = '';
   $form['country'] = $admin_aziend['country'];
   $form['id_language'] = $admin_aziend['id_language'];
   $form['id_currency'] = $admin_aziend['id_currency'];
   $form['datnas_Y'] = 1900;
   $form['datnas_M'] = 1;
   $form['datnas_D'] = 1;
   $form['counas'] = $admin_aziend['country'];
   $form['codpag'] = 1;
   $form['spefat'] = 'N';
   $form['stapre'] = 'N';
   $form['allegato'] = 1;
   $form['ritorno'] = $_SERVER['HTTP_REFERER'];
   $form['hidden_req'] = '';
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup','custom/autocomplete'));

$gForm = new venditForm();
echo "<form method=\"POST\" name=\"form\">\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"" . $form['ritorno'] . "\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
//echo "<input type=\"hidden\" value=\"" . $form['id_anagra'] . "\" name=\"id_anagra\" />\n";
echo "<input type=\"hidden\" name=\"" . ucfirst($toDo) . "\" value=\"\">";

if ($toDo == 'insert') {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['ins_this'] . "</div>\n";
} else {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['upd_this'] . " '" . $form['id'] . "'</div>\n";
   echo "<input type=\"hidden\" value=\"" . $form['id'] . "\" name=\"id\" />\n";
}
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
   echo '<tr><td colspan="3" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
   if (isset($anagra)) {
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
if ($toDo == 'insert') {
   echo "<tr>\n";
   echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['codice'] . "* </td>\n";
   echo "\t<td colspan=\"2\" class=\"FacetDataTD\"><input type=\"text\" name=\"codice\" value=\"" . $form['codice'] . "\" align=\"right\" maxlength=\"6\" size=\"8\" /></td>\n";
   echo "</tr>\n";
}
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['ragso1'] . "* </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"ragso1\" value=\"" . $form['ragso1'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['ragso2'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"ragso2\" value=\"" . $form['ragso2'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['legrap'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"legrap\" value=\"" . $form['legrap'] . "\" align=\"right\" maxlength=\"100\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['sexper'] . "*</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('sexper', $script_transl['sexper_value'], $form['sexper']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['citspe'] . " *  </td>\n";
echo "\t<td class=\"FacetDataTD\">
      <input type=\"text\" name=\"citspe\" id=\"search_location\" value=\"" . $form['citspe'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "\t<td class=\"FacetDataTD\">
      <input type=\"text\" name=\"prospe\" id=\"search_location-prospe\" value=\"" . $form['prospe'] . "\" align=\"right\" maxlength=\"2\" size=\"2\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['indspe'] . " * </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"indspe\" value=\"" . $form['indspe'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['capspe'] . " * </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"capspe\" id=\"search_location-capspe\" value=\"" . $form['capspe'] . "\" align=\"right\" maxlength=\"10\" size=\"5\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['country'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('country', 'country', 'iso', $form['country'], 'iso', 0, ' - ', 'name');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_language'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('languages', 'id_language', 'lang_id', $form['id_language'], 'lang_id', 1, ' - ', 'title_native');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_currency'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('currencies', 'id_currency', 'id', $form['id_currency'], 'id', 1, ' - ', 'curr_name');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['counas'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('country', 'counas', 'iso', $form['cell'], 'iso', 1, ' - ', 'name');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['telefo'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"telefo\" value=\"" . $form['telefo'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['fax'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"fax\" value=\"" . $form['fax'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['cell'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"cell\" value=\"" . $form['cell'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['codfis'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"codfis\" value=\"" . $form['codfis'] . "\" align=\"right\" maxlength=\"16\" size=\"20\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['pariva'] . " </td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"pariva\" value=\"" . $form['pariva'] . "\" align=\"right\" maxlength=\"11\" size=\"11\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['e_mail'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" id=\"email\" name=\"e_mail\" value=\"" . $form['e_mail'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\"><a href=\"http://www.indicepa.gov.it/documentale/ricerca.php\" target=\"blank\">" . $script_transl['fe_cod_univoco'] . "</a></td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"fe_cod_univoco\" value=\"" . $form['fe_cod_univoco'] . "\" align=\"right\" maxlength=\"6\" size=\"7\" /></td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['sqn'] . "</td>";
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\">\n";
echo '<input name="Return" type="submit" value="' . $script_transl['return'] . '!">';
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\" align=\"right\">\n";
echo '<input disabled name="Submit" type="submit" value="' . strtoupper($script_transl[$toDo]) . '!">';
echo "\t </td>\n";
echo "</tr>\n";
?>
</table>
</form>
</div><!-- chiude div container role main --></body>
</html>

<SCRIPT type="text javascript">
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
</script>