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
$admin_aziend = checkAdmin(9);
$msg = '';
$rs_azienda = gaz_dbi_dyn_query('*', $gTables['aziend'], intval($_SESSION['company_id']), 'codice DESC', 0, 1);
$exist_true = gaz_dbi_fetch_array($rs_azienda);

if ($exist_true) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    $form = gaz_dbi_parse_post('aziend');
    $form['ritorno'] = $_POST['ritorno'];
    $form['e_mail'] = trim($form['e_mail']);
    $form['web_url'] = trim($form['web_url']);
    $form['mascli'] = intval(substr($_POST['mascli'], 0, 3));
    $form['masfor'] = intval(substr($_POST['masfor'], 0, 3));
    $form['mas_staff'] = intval(substr($_POST['mas_staff'], 0, 3));
    $form['masban'] = intval(substr($_POST['masban'], 0, 3));
    $form['virtual_stamp_auth_date_Y'] = intval($_POST['virtual_stamp_auth_date_Y']);
    $form['virtual_stamp_auth_date_M'] = intval($_POST['virtual_stamp_auth_date_M']);
    $form['virtual_stamp_auth_date_D'] = intval($_POST['virtual_stamp_auth_date_D']);
    $form['datnas_Y'] = intval($_POST['datnas_Y']);
    $form['datnas_M'] = intval($_POST['datnas_M']);
    $form['datnas_D'] = intval($_POST['datnas_D']);
    $form['intermediary_code'] = intval($_POST['intermediary_code']);
    $form['intermediary_descr'] = substr($_POST['intermediary_descr'], 0, 50);
    $form['amm_min'] = filter_input(INPUT_POST, 'amm_min');
    if (isset($_POST['Submit'])) { // conferma tutto
        require("../../library/include/check.inc.php");
        $chk = new check_VATno_TAXcode();
        $cf = trim($form['codfis']);
        if (!empty($_FILES['userfile']['name'])) {
            if (!( $_FILES['userfile']['type'] == "image/png" ||
                    $_FILES['userfile']['type'] == "image/x-png" ||
                    $_FILES['userfile']['type'] == "image/jpeg" ||
                    $_FILES['userfile']['type'] == "image/jpg" ||
                    $_FILES['userfile']['type'] == "image/gif" ||
                    $_FILES['userfile']['type'] == "image/x-gif"))
                $msg .= "11+";
            if ($_FILES['userfile']['size'] > 63999)
                $msg .= "12+";
        }
        if ($toDo == 'insert' && $_FILES['userfile']['size'] < 1) {
            $msg .= "14+";
        }
        if (strlen($cf) == 11) {
            $rs_cf = $chk->check_VAT_reg_no($cf, $form['country']);
            if ($form['sexper'] != 'G') {
                $msg .= "7+";
            }
        } elseif (empty($cf)) {
            $msg .= "10+";
        } else {
            $rs_cf = $chk->check_TAXcode($cf, $form['country']);
            if ($form['sexper'] == 'G') {
                $msg .= "9+";
            }
        }
        if (!empty($rs_cf)) {
            $msg .= "6+";
        }
        if (!empty($form['pariva'])) {
            $rs_pi = $chk->check_VAT_reg_no($form['pariva'], $form['country']);
            if (!empty($rs_pi)) {
                $msg .= "8+";
            }
        }

        /** ENRICO FEDELE */
        /* Compatibilità con il nuovo simple pick color */
        $form["colore"] = substr($form["colore"], 1);
        /** ENRICO FEDELE */
        $lumix = hexdec(substr($form["colore"], 0, 2)) + hexdec(substr($form["colore"], 0, 2)) + hexdec(substr($form["colore"], 0, 2));
        if ($lumix < 408) {
            $msg .= "13+";
        }

        //eseguo i controlli formali
        $uts_datnas = mktime(0, 0, 0, $form['datnas_M'], $form['datnas_D'], $form['datnas_Y']);
        $form['datnas'] = date("Y-m-d", $uts_datnas);
        if (empty($form['ragso1'])) {
            $msg .= "0+";
        }
        if (empty($form['sexper'])) {
            $msg .= "1+";
        }
        if (!checkdate($form['datnas_M'], $form['datnas_D'], $form['datnas_Y'])) {
            $msg .= "2+";
        }
        if (empty($form['indspe'])) {
            $msg .= "3+";
        }
        if (empty($form['citspe'])) {
            $msg .= "4+";
        }
        if (empty($form['prospe'])) {
            $msg .= "5+";
        }
        $cap = new postal_code;
        if ($cap->check_postal_code($form["capspe"], $form["country"])) {
            $msg.='15+';
        }
        if (!filter_var($form['e_mail'], FILTER_VALIDATE_EMAIL) && !empty($form['e_mail'])) {
            $msg .= "16+";
        }
        if (!filter_var($form['web_url'], FILTER_VALIDATE_URL) && !empty($form['e_mail']) && $form['web_url'] != "") {
            $msg .= "17+";
        }
        if ($form['cod_ateco'] < 10000) {
            $msg .= "18+";
        }
        if (empty($msg)) { // nessun errore
            if ($_FILES['userfile']['size'] > 0) { //se c'e' una nuova immagine nel buffer
                $form['image'] = file_get_contents($_FILES['userfile']['tmp_name']);
            }
            // aggiorno il db
            if ($toDo == 'insert') {
                gaz_dbi_table_insert('aziend', $form);
            } elseif ($toDo == 'update') {
                gaz_dbi_table_update('aziend', $form['codice'], $form);
            }
            // in ogni caso se è stata scelta come azienda intermediatrice verso l'AdE aggiorno la configurazione
            if (( $form['codice'] == $form['intermediary_code'] || $form['intermediary_code'] == 0 ) && isset($_POST['intermediary_check'])) {
                if ($_POST['intermediary_check'] == 'y') {
                    gaz_dbi_put_row($gTables['config'], 'variable', 'intermediary', 'cvalue', $form['codice']);
                } else { // no intermediario
                    gaz_dbi_put_row($gTables['config'], 'variable', 'intermediary', 'cvalue', 0);
                }
            }
            header("Location: docume_config.php");
            exit;
        }
    } elseif (isset($_POST['Return'])) { // torno indietro
        header("Location: " . $form['ritorno']);
        exit;
    }
} elseif ($exist_true) { //se e' il primo accesso per UPDATE
    $form = gaz_dbi_get_row($gTables['aziend'], 'codice', intval($_SESSION['company_id']));
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['datnas_Y'] = substr($form['datnas'], 0, 4);
    $form['datnas_M'] = substr($form['datnas'], 5, 2);
    $form['datnas_D'] = substr($form['datnas'], 8, 2);
    $form['virtual_stamp_auth_date_Y'] = substr($form['virtual_stamp_auth_date'], 0, 4);
    $form['virtual_stamp_auth_date_M'] = substr($form['virtual_stamp_auth_date'], 5, 2);
    $form['virtual_stamp_auth_date_D'] = substr($form['virtual_stamp_auth_date'], 8, 2);
    // rilevo l'eventuale intermediario
    $intermediary = gaz_dbi_get_row($gTables['config'], 'variable', 'intermediary');
    $form['intermediary_code'] = $intermediary['cvalue'];
    if ($intermediary['cvalue'] > 0) {
        $intermediary_descr = gaz_dbi_get_row($gTables['aziend'], 'codice', $intermediary['cvalue']);
        $form['intermediary_descr'] = $intermediary_descr['ragso1'] . ' ' . $intermediary_descr['ragso2'];
    } else {
        $form['intermediary_descr'] = '';
    }
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form = gaz_dbi_fields('aziend');
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['datnas_Y'] = date("Y");
    $form['datnas_M'] = date("m");
    $form['datnas_D'] = date("d");
    $form['virtual_stamp_auth_date_Y'] = 1970;
    $form['virtual_stamp_auth_date_M'] = 1;
    $form['virtual_stamp_auth_date_D'] = 1;
    $form['country'] = 'IT';
    $form['id_language'] = 1;
    $form['id_currency'] = 1;
    $form['decimal_price'] = 3;
    $form['ivaera'] = 5;
    $form['web_url'] = 'http://';
    // rilevo l'eventuale intermediario
    $intermediary = gaz_dbi_get_row($gTables['config'], 'variable', 'intermediary');
    $form['intermediary_code'] = $intermediary['cvalue'];
    if ($intermediary['cvalue'] > 0) {
        $intermediary_descr = gaz_dbi_get_row($gTables['aziend'], 'codice', $intermediary['cvalue']);
        $form['intermediary_descr'] = $intermediary_descr['ragso1'] . ' ' . $intermediary_descr['ragso2'];
    } else {
        $form['intermediary_descr'] = '';
    }
    $form['amm_min'] = '';
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup',
    'custom/autocomplete',
    'custom/jquery.simple-color'));
//simplecolordisplay
?>
<script>
    $(function () {
        $('#amm_min').selectmenu();
    });
</script>
<?php
echo "<script type=\"text/javascript\">
$(document).ready(function(){
	$('.simple_color_custom').simpleColor({
		columns: 37,
		border: '1px solid #333333',
		buttonClass: 'button',
		displayColorCode: true,
                livePreview: true,
                colors:['888888','8888AD','8888C1','8888D6','8888EA','8888FF','AD8888','AD88AD','AD88C1','AD88D6','AD88EA','AD88FF','C18888','C188AD','C188C1','C188D6','C188EA','C188FF','D68888','D688AD','D688C1','D688D6','D688EA','D688FF','EA8888','EA88AD','EA88C1','EA88D6','EA88EA','EA88FF','FF8888','FF88AD','FF88C1','FF88D6','FF88EA','FF88FF',
                '88AD88','88ADAD','88ADC1','88ADD6','88ADEA','88ADFF','ADAD88','ADADAD','ADADC1','ADADD6','ADADEA','ADADFF','C1AD88','C1ADAD','C1ADC1','C1ADD6','C1ADEA','C1ADFF','D6AD88','D6ADAD','D6ADC1','D6ADD6','D6ADEA','D6ADFF','EAAD88','EAADAD','EAADC1','EAADD6','EAADEA','EAADFF','FFAD88','FFADAD','FFADC1','FFADD6','FFADEA','FFADFF',
                '88C188','88C1AD','88C1C1','88C1D6','88C1EA','88C1FF','ADC188','ADC1AD','ADC1C1','ADC1D6','ADC1EA','ADC1FF','C1C188','C1C1AD','C1C1C1','C1C1D6','C1C1EA','C1C1FF','D6C188','D6C1AD','D6C1C1','D6C1D6','D6C1EA','D6C1FF','EAC188','EAC1AD','EAC1C1','EAC1D6','EAC1EA','EAC1FF','FFC188','FFC1AD','FFC1C1','FFC1D6','FFC1EA','FFC1FF',
                '88D688','88D6AD','88D6C1','88D6D6','88D6EA','88D6FF','ADD688','ADD6AD','ADD6C1','ADD6D6','ADD6EA','ADD6FF','C1D688','C1D6AD','C1D6C1','C1D6D6','C1D6EA','C1D6FF','D6D688','D6D6AD','D6D6C1','D6D6D6','D6D6EA','D6D6FF','EAD688','EAD6AD','EAD6C1','EAD6D6','EAD6EA','EAD6FF','FFD688','FFD6AD','FFD6C1','FFD6D6','FFD6EA','FFD6FF',
                '88EA88','88EAAD','88EAC1','88EAD6','88EAEA','88EAFF','ADEA88','ADEAAD','ADEAC1','ADEAD6','ADEAEA','ADEAFF','C1EA88','C1EAAD','C1EAC1','C1EAD6','C1EAEA','C1EAFF','D6EA88','D6EAAD','D6EAC1','D6EAD6','D6EAEA','D6EAFF','EAEA88','EAEAAD','EAEAC1','EAEAD6','EAEAEA','EAEAFF','FFEA88','FFEAAD','FFEAC1','FFEAD6','FFEAEA','FFEAFF',
                '88FF88','88FFAD','88FFC1','88FFD6','88FFEA','88FFFF','ADFF88','ADFFAD','ADFFC1','ADFFD6','ADFFEA','ADFFFF','C1FF88','C1FFAD','C1FFC1','C1FFD6','C1FFEA','C1FFFF','D6FF88','D6FFAD','D6FFC1','D6FFD6','D6FFEA','D6FFFF','EAFF88','EAFFAD','EAFFC1','EAFFD6','EAFFEA','EAFFFF','FFFF88','FFFFAD','FFFFC1','FFFFD6','FFFFEA','FFFFFF'],
                colorCodeColor: '#000'
	});
	
	
});
$( '#check').button();

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
";
echo "<form method=\"POST\" name=\"form\" enctype=\"multipart/form-data\">\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"" . $form['ritorno'] . "\">\n";
echo "<input type=\"hidden\" name=\"" . ucfirst($toDo) . "\" value=\"\">";
$gForm = new configForm();
if ($toDo == 'insert') {
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['ins_this'] . "</div>\n";
} else {
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['upd_this'] . " '" . $form['codice'] . "'</div>\n";
    echo "<input type=\"hidden\" value=\"" . $form['codice'] . "\" name=\"codice\" />\n";
}
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="3" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
}
if ($toDo == 'insert') {
    echo "<tr>\n";
    echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['codice'] . "* </td>\n";
    echo "\t<td colspan=\"2\" class=\"FacetDataTD\"><input type=\"hidden\" name=\"codice\" value=\"1\" align=\"right\" maxlength=\"3\" size=\"3\" />1</td>\n";
    echo "</tr>\n";
}
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['ragso1'] . "* </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"ragso1\" value=\"" . $form['ragso1'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" />&nbsp;\n";

echo "<a class=\"btn btn-xs btn-default btn-default\" href=\"config_aziend.php\"><i class=\"glyphicon glyphicon-lock\"></i>&nbsp;config</a></td>\n";

echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['ragso2'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"ragso2\" value=\"" . $form['ragso2'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\"><img src=\"../root/view.php?table=aziend&value=" . $form['codice'] . "\" width=\"100\">*</td>\n";
echo "<td colspan=\"2\" class=\"FacetFieldCaptionTD\">" . $script_transl['image'] . " * <input name=\"userfile\" type=\"file\">";
echo "</td></tr>";
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
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['datnas'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('datnas', $form['datnas_D'], $form['datnas_M'], $form['datnas_Y']);
echo "\t</td>\n";
echo "</tr>\n";
echo "<tr>\n";

/** ENRICO FEDELE */
/* Aggiunto id per autocompletamento */
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['luonas'] . " </td>\n";
echo "\t<td class=\"FacetDataTD\">
      <input type=\"text\" id=\"search_luonas\" name=\"luonas\" value=\"" . $form['luonas'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "\t<td class=\"FacetDataTD\">
      <input type=\"text\" id=\"search_pronas\" name=\"pronas\" value=\"" . $form['pronas'] . "\" align=\"right\" maxlength=\"2\" size=\"2\" /></td>\n";
echo "</tr>\n";
/** ENRICO FEDELE */
/* Cambiato l'ordine dei campi per renderlo più coerente con l'autocompletamento (prima il campo comune che ha la funzione attiva) */
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
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['latitude'] . " - " . $script_transl['longitude'] . " </td>\n";
echo "\t<td  colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"latitude\" value=\"" . $form['latitude'] . "\" align=\"right\" maxlength=\"10\" size=\"10\" />
       - <input type=\"text\" name=\"longitude\" value=\"" . $form['longitude'] . "\" align=\"right\" maxlength=\"10\" size=\"10\" />"
 . " <a class=\"btn btn-xs btn-default btn-default\" href=\"http://maps.google.com/maps?q=" . $form['latitude'] . "," . $form['longitude'] . "\"> maps -> <i class=\"glyphicon glyphicon-map-marker\"></i></a></td>\n";
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
/** ENRICO FEDELE */
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
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['sedleg'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <textarea name=\"sedleg\" rows=\"2\" cols=\"30\" maxlength=\"100\" size=\"50\">" . $form['sedleg'] . "</textarea></td>\n";
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
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['rea'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"rea\" value=\"" . $form['rea'] . "\" align=\"right\" maxlength=\"32\" size=\"32\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['e_mail'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"e_mail\" value=\"" . $form['e_mail'] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['web_url'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"web_url\" value=\"" . $form['web_url'] . "\" maxlength=\"255\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['intermediary'] . ":</td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
        <input type=\"hidden\" name=\"intermediary_code\" value=\"" . $form['intermediary_code'] . "\" />
        <input type=\"hidden\" name=\"intermediary_descr\" value=\"" . $form['intermediary_descr'] . "\" />";
if ($form['intermediary_code'] == $form['codice']) {
    echo "<input type=\"radio\" checked value=\"y\" name=\"intermediary_check\">Si - No<input type=\"radio\" value=\"n\" name=\"intermediary_check\">";
} elseif ($form['intermediary_code'] == 0) {
    echo "<input type=\"radio\" value=\"y\" name=\"intermediary_check\">" . $script_transl['yes'] . " - " . $script_transl['no'] . "<input type=\"radio\" checked value=\"n\" name=\"intermediary_check\">";
} else {
    echo $form['intermediary_descr'];
}
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['cod_ateco'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"cod_ateco\" value=\"" . $form['cod_ateco'] . "\" align=\"right\" maxlength=\"6\" size=\"6\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['regime'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('regime', $script_transl['regime_value'], $form['regime']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['fiscal_reg'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('fiscal_reg', $script_transl['fiscal_reg_value'], $form['fiscal_reg']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['amm_min'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selSpecieAmmortamentoMin( 'ammortamenti_ministeriali.xml' , 'amm_min',$form["amm_min"] );
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['decimal_quantity'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('decimal_quantity', $script_transl['decimal_quantity_value'], $form['decimal_quantity'], 'FacetSelect', 0);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">\n" . $script_transl['decimal_price'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectNumber('decimal_price', $form['decimal_price'], 0, 0, 5);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['stock_eval_method'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('stock_eval_method', $script_transl['stock_eval_method_value'], $form['stock_eval_method'], 'FacetSelect', 0);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['mascli'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('mascli', $form['mascli'], array(1));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['masfor'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('masfor', $form['masfor'], array(2));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['masban'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('masban', $form['masban'] . '000000', array(1, 9));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['mas_staff'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('mas_staff', $form['mas_staff'] . '000000', array(2, 9));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['cassa_'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('cassa_', $form['cassa_'], 1);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['ivaacq'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('ivaacq', $form['ivaacq'], 1);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['ivaven'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('ivaven', $form['ivaven'], 2);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['ivacor'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('ivacor', $form['ivacor'], 2);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['ivaera'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('ivaera', $form['ivaera'], substr($form['ivaera'], 0, 1));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['split_payment'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('split_payment', $form['split_payment'], substr($form['split_payment'], 0, 1));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['impven'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('impven', $form['impven'], 4);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['imptra'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('imptra', $form['imptra'], 4);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['impimb'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('impimb', $form['impimb'], 4);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['impspe'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('impspe', $form['impspe'], 4);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['impvar'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('impvar', $form['impvar'], 4);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['preeminent_vat'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('aliiva', 'preeminent_vat', 'codice', $form['preeminent_vat'], 'codice', 0, ' - ', 'descri');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['boleff'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('boleff', $form['boleff'], 4);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['omaggi'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('omaggi', $form['omaggi'], 3);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['sales_return'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('sales_return', $form['sales_return'], array('sub', 3, 4));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['impacq'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('impacq', $form['impacq'], 3);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['cost_tra'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('cost_tra', $form['cost_tra'], 3);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['cost_imb'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('cost_imb', $form['cost_imb'], 3);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['cost_var'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('cost_var', $form['cost_var'], 3);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['purchases_return'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('purchases_return', $form['purchases_return'], array('sub', 3, 4));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['coriba'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('coriba', $form['coriba'], array('sub', 1, 2, 5));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['cotrat'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('cotrat', $form['cotrat'], array('sub', 1, 2, 5));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['cocamb'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('cocamb', $form['cocamb'], array('sub', 1, 2, 5));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['c_ritenute'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('c_ritenute', $form['c_ritenute'], 1);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['ritenuta'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"ritenuta\" value=\"" . $form['ritenuta'] . "\" align=\"right\" maxlength=\"4\" size=\"4\" />%</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['c_payroll_tax'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('c_payroll_tax', $form['c_payroll_tax'], array('sub', 2, 4));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['payroll_tax'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"payroll_tax\" value=\"" . $form['payroll_tax'] . "\" align=\"right\" maxlength=\"4\" size=\"4\" />%</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['causale_pagam_770'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('causale_pagam_770', $script_transl['causale_pagam_770_value'], $form['causale_pagam_770'], 'FacetSelect', true, '', 100);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['upgrie'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"upgrie\" value=\"" . $form['upgrie'] . "\" align=\"right\" maxlength=\"4\" size=\"4\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['upggio'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"upggio\" value=\"" . $form['upggio'] . "\" align=\"right\" maxlength=\"4\" size=\"4\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['upginv'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"upginv\" value=\"" . $form['upginv'] . "\" align=\"right\" maxlength=\"4\" size=\"4\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['upgve'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">";
for ($i = 1; $i <= 3; $i++) {
    echo ' ' . $script_transl['sezione'] . $i . ": <input type=\"text\" name=\"upgve$i\" value=\"" . $form["upgve$i"] . "\" align=\"right\" maxlength=\"4\" size=\"4\" />";
}
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['upgac'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">";
for ($i = 1; $i <= 3; $i++) {
    echo ' ' . $script_transl['sezione'] . $i . ": <input type=\"text\" name=\"upgac$i\" value=\"" . $form["upgac$i"] . "\" align=\"right\" maxlength=\"4\" size=\"4\" />";
}
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['upgco'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">";
for ($i = 1; $i <= 3; $i++) {
    echo ' ' . $script_transl['sezione'] . $i . ": <input type=\"text\" name=\"upgco$i\" value=\"" . $form["upgco$i"] . "\" align=\"right\" maxlength=\"4\" size=\"4\" />";
}
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['acciva'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"acciva\" value=\"" . $form['acciva'] . "\" align=\"right\" maxlength=\"5\" size=\"5\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['taxstamp_limit'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"taxstamp_limit\" value=\"" . $form['taxstamp_limit'] . "\" align=\"right\" maxlength=\"6\" size=\"6\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['taxstamp'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"taxstamp\" value=\"" . $form['taxstamp'] . "\" align=\"right\" maxlength=\"6\" size=\"6\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['taxstamp_vat'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('aliiva', 'taxstamp_vat', 'codice', $form['taxstamp_vat'], 'codice', 0, ' - ', 'descri');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['perbol'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"perbol\" value=\"" . $form['perbol'] . "\" align=\"right\" maxlength=\"6\" size=\"6\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['round_bol'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('round_bol', $script_transl['round_bol_value'], $form['round_bol']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['virtual_taxstamp'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('virtual_taxstamp', $script_transl['virtual_taxstamp_value'], $form['virtual_taxstamp']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['virtual_stamp_auth_prot'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"virtual_stamp_auth_prot\" value=\"" . $form['virtual_stamp_auth_prot'] . "\" align=\"right\" maxlength=\"14\" size=\"14\" />\n";
echo $script_transl['virtual_stamp_auth_date'];
$gForm->CalendarPopup('virtual_stamp_auth_date', $form['virtual_stamp_auth_date_D'], $form['virtual_stamp_auth_date_M'], $form['virtual_stamp_auth_date_Y']);
echo "</td></tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['sperib'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"sperib\" value=\"" . $form['sperib'] . "\" align=\"right\" maxlength=\"6\" size=\"6\" /></td>\n";
echo "</tr>\n";
for ($i = 1; $i <= 3; $i++) {
    echo "<tr>\n";
    echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['desez'] . $script_transl['sezione'] . $i . " </td>\n";
    echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
       <input type=\"text\" name=\"desez$i\" value=\"" . $form['desez' . $i] . "\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
    echo "</tr>\n";
}
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['fatimm'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('fatimm', $script_transl['fatimm_value'], $form['fatimm']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['artsea'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('artsea', $script_transl['artsea_value'], $form['artsea']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl['templ_set'] . "</td>\n";
echo '<td class="FacetDataTD" colspan="2">';
echo '<select name="template" class="FacetSelect">';
$relativePath = '../../config';
if ($handle = opendir($relativePath)) {
    while ($file = readdir($handle)) {
        if (substr($file, 0, 9) != "templates")
            continue;
        $selected = "";
        if ($form["template"] == substr($file, 10)) {
            $selected = " selected ";
        }
        echo "<option value=\"" . substr($file, 10) . "\"" . $selected . ">" . ucfirst($file) . "</option>";
    }
    closedir($handle);
}
echo "</select></td></tr>\n";
/** ENRICO FEDELE */
/* Compatibilità con il nuovo simple pick color */
echo '<tr>
		<td class="FacetFieldCaptionTD">' . $script_transl['colore'] . '</td>
		<td colspan="2" style="color:white; background-color:#' . $form['colore'] . ';">
			<input class="simple_color_custom" type="text" name="colore" size="6" value="#' . $form['colore'] . '" />
	    </td>
	  </tr>';
/** ENRICO FEDELE */
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['conmag'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('conmag', $script_transl['conmag_value'], $form['conmag']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['ivam_t'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('ivam_t', $script_transl['ivam_t_value'], $form['ivam_t']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['interessi'] . " </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"interessi\" value=\"" . $form['interessi'] . "\" align=\"right\" maxlength=\"4\" size=\"4\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['sqn'] . "</td>";
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\">\n";
echo '<input name="Return" type="submit" value="' . $script_transl['return'] . '!">';
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\" align=\"right\">\n";
echo '<input name="Submit" type="submit" value="' . strtoupper($script_transl[$toDo]) . '!">';
echo "\t </td>\n";
echo "</tr>\n";
?>
</table>
</form>
</div><!-- chiude div container role main --></body>
</html>