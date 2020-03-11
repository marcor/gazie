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
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
require("../../library/include/check.inc.php");
$admin_aziend = checkAdmin();
$msg = '';


if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and ! isset($_GET['codice']))) {
    header("Location: " . $form['ritorno']);
    exit;
}

if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or ( isset($_POST['Update']))) {   //se non e' il primo accesso
    //qui si deve fare un parsing di quanto arriva dal browser...
    $form['ragione_sociale'] = substr($_POST['ragione_sociale'], 0, 100);
    $form['indirizzo'] = substr($_POST['indirizzo'], 0, 100);
    $form['cap'] = substr($_POST['cap'], 0, 5);
    $form['citta'] = substr($_POST['citta'], 0, 100);
    $form['provincia'] = strtoupper(substr($_POST['provincia'], 0, 2));
    $form['partita_iva'] = substr($_POST['partita_iva'], 0, 12);
    $form['codice_fiscale'] = strtoupper(substr(trim($_POST['codice_fiscale']), 0, 16));
    $form['n_albo'] = substr($_POST['n_albo'], 0, 50);
    $form['descri'] = substr($_POST['descri'], 0, 100);
    $form['telefo'] = substr($_POST['telefo'], 0, 50);
    $form['annota'] = substr($_POST['annota'], 0, 50);
    $form['codice'] = intval($_POST['codice']);
    // Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
        if (empty($form['ragione_sociale'])) {
            $msg .= "0+";
        }
        if (empty($form['indirizzo'])) {
            $msg .= "1+";
        }
        if (empty($form['citta'])) {
            $msg .= "2+";
        }
        if (empty($form['cap'])) {
            $msg .= "3+";
        }
        if (!empty($form['codice_fiscale'])) {  // controllo codice fiscale
            $ctrl_cf = new check_VATno_TAXcode();
            $rs_cf = $ctrl_cf->check_TAXcode($form['codice_fiscale'], $admin_aziend['country']);
            if (!empty($rs_cf)) {
                $msg .= "4+";
            }
        }
        if (!empty($form['partita_iva'])) {
            $ctrl_pi = new check_VATno_TAXcode();
            $rs_pi = $ctrl_pi->check_VAT_reg_no($form['partita_iva'], $admin_aziend['country']);
            if (!empty($rs_pi)) {
                $msg .= "5+";
            }
        } else {
            $msg .= "6+";
        }
        if ($msg == "") { // nessun errore
            if ($toDo == 'update') { // e' una modifica
                vettoreUpdate($form['codice'], $form);
                header("Location: " . $form['ritorno']);
                exit;
            } else { // e' un'inserimento
                $rs_last_n = gaz_dbi_dyn_query("codice", $gTables['vettor'], 1, 'codice DESC', 0, 1);
                $last_n = gaz_dbi_fetch_array($rs_last_n);
                if ($last_n) {
                    $form['codice'] = $last_n['codice'] + 1;
                } else {
                    $form['codice'] = 1;
                }
                vettoreInsert($form);
                header("Location: report_vettor.php");
                exit;
            }
        }
    }
} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $vettore = gaz_dbi_get_row($gTables['vettor'], 'codice', intval($_GET['codice']));
    $form['codice'] = $vettore['codice'];
    $form['ragione_sociale'] = $vettore['ragione_sociale'];
    $form['indirizzo'] = $vettore['indirizzo'];
    $form['cap'] = $vettore['cap'];
    $form['citta'] = $vettore['citta'];
    $form['provincia'] = $vettore['provincia'];
    $form['partita_iva'] = $vettore['partita_iva'];
    $form['codice_fiscale'] = $vettore['codice_fiscale'];
    $form['n_albo'] = $vettore['n_albo'];
    $form['descri'] = $vettore['descri'];
    $form['telefo'] = $vettore['telefo'];
    $form['annota'] = $vettore['annota'];
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['codice'] = 0;
    $form['ragione_sociale'] = '';
    $form['indirizzo'] = '';
    $form['cap'] = '';
    $form['citta'] = '';
    $form['provincia'] = '';
    $form['partita_iva'] = '';
    $form['codice_fiscale'] = '';
    $form['n_albo'] = '';
    $form['descri'] = '';
    $form['telefo'] = '';
    $form['annota'] = '';
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array(/** ENRICO FEDELE */
    /* 'jquery/jquery-1.7.1.min',
      'jquery/ui/jquery.ui.core',
      'jquery/ui/jquery.ui.widget',
      'jquery/ui/jquery.ui.position',
      'jquery/ui/jquery.ui.autocomplete', */
    /** ENRICO FEDELE */
    'custom/autocomplete'));
echo "<form method=\"POST\" name=\"vettore\">\n";
$gForm = new GAzieForm();
echo "<input type=\"hidden\" name=\"" . ucfirst($toDo) . "\" value=\"\">\n";
echo "<input type=\"hidden\" value=\"" . $form['codice'] . "\" name=\"codice\">\n";
echo "<input type=\"hidden\" value=\"" . $form['ritorno'] . "\" name=\"ritorno\">\n";

if ($form['codice'] > 0) { // � una modifica
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['upd_this'] . " " . $form['codice'] . " </div>\n";
} else {
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['ins_this'] . "<font class=\"FacetDataTD\"></font></div>\n";
}
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="6" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
}

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['ragione_sociale'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"ragione_sociale\" value=\"" . $form['ragione_sociale'] . "\" maxlength=\"100\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['indirizzo'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"indirizzo\" value=\"" . $form['indirizzo'] . "\" maxlength=\"100\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['cap'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"cap\" id=\"search_location-capspe\" value=\"" . $form['cap'] . "\" maxlength=\"5\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['citta'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"citta\" id=\"search_location\" value=\"" . $form['citta'] . "\" maxlength=\"100\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['provincia'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" id=\"search_location-prospe\" name=\"provincia\" value=\"" . $form['provincia'] . "\" maxlength=\"2\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['partita_iva'] . " *</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"partita_iva\" value=\"" . $form['partita_iva'] . "\" maxlength=\"11\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['codice_fiscale'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"codice_fiscale\" value=\"" . $form['codice_fiscale'] . "\" maxlength=\"16\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['n_albo'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"n_albo\" value=\"" . $form['n_albo'] . "\" maxlength=\"50\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['telefo'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"telefo\" value=\"" . $form['telefo'] . "\" maxlength=\"50\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['descri'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"descri\" value=\"" . $form['descri'] . "\" maxlength=\"100\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['annota'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"annota\" value=\"" . $form['annota'] . "\" maxlength=\"100\"  /></td>\n";
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>" . $script_transl['sqn'] . "</td>\n";
echo "\t<td class=\"FacetDataTD\" align=\"right\">\n";
if ($toDo == 'update') {
    echo '<input title="' . $script_transl['upd'] . '" type="submit" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="' . ucfirst($script_transl['update']) . '">';
} else {
    echo '<input title="' . $script_transl['ins'] . '" type="submit" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="' . ucfirst($script_transl['insert']) . '">';
}
echo "\t </td>\n";
echo "</tr>\n";
echo "</table>\n";
?>
</form>
<?php
require("../../library/include/footer.php");
?>