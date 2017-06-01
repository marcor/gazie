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
$msg = '';
$paymov = new Schedule;
$anagrafica = new Anagrafica();

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['paymov'] = array();
    $form['expiry_ini'] = date("d-m-Y");
    $form['expiry_fin'] = date("d-m-Y");
    $form['target_account'] = 0;
    $form['transfer_fees_acc'] = 0;
    $form['transfer_fees'] = 0.00;
    /* aggiunta descrizione modificabile */
    $form['descr_mov'] = '';
    /** fine modifica FP */
} else { // accessi successivi
    $first = false;
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    $form['ritorno'] = $_POST['ritorno'];
    if (isset($_POST['paymov'])) {
        $desmov = '';
        $acc_tot = 0.00;
        foreach ($_POST['paymov'] as $k => $v) {
            $form['paymov'][$k] = $v;  // qui dovrei fare il parsing
            $add_desc[$k] = 0.00;
            foreach ($v as $ki => $vi) { // calcolo il totale 
                $acc_tot += $vi['amount'];
                $add_desc[$k] += $vi['amount'];
            }
        }
        if (strlen($desmov) <= 85) { // la descrizione entra in 50 caratteri
            $desmov = 'PAGATO x FAT.' . $desmov;
        } else { // la descrizione è troppo lunga
            $desmov = 'PAGATO FINO A FAT.n.';
        }
        if ($acc_tot <= 0) {
            $msg .= '4+';
        }
    } else if (isset($_POST['ins'])) { // non ho movimenti ma ho chiesto di inserirli
        $msg .= '6+';
    }
    $form['descr_mov'] = $_POST['descr_mov'];
    /** fine modifica FP */
    $form['expiry_ini'] = substr($_POST['expiry_ini'], 0, 10);
    $form['expiry_fin'] = substr($_POST['expiry_fin'], 0, 10);
    $form['target_account'] = intval($_POST['target_account']);
    $bank_data = gaz_dbi_get_row($gTables['clfoco'], 'codice', $form['target_account']);
    if (!isset($_POST['ins'])) {
        if ($bank_data['maxrat'] >= 0.01 && $_POST['transfer_fees'] < 0.01) { // se il conto corrente bancccario prevede un addebito per bonifici allora lo propongo
            $form['transfer_fees_acc'] = $bank_data['cosric'];
            $form['transfer_fees'] = $bank_data['maxrat'];
        } elseif (substr($form['target_account'], 0, 3) == substr($admin_aziend['cassa_'], 0, 3)) {
            $form['transfer_fees_acc'] = 0;
            $form['transfer_fees'] = 0.00;
        } else {
            $form['transfer_fees_acc'] = intval($_POST['transfer_fees_acc']);
            $form['transfer_fees'] = floatval($_POST['transfer_fees']);
        }
    }
    if (isset($_POST['return'])) {
        header("Location: " . $form['ritorno']);
        exit;
    }
    //controllo i campi
    if (!checkdate($form['expiry_fin'], $form['expiry_ini'], $form['date_ini_Y'])) {
        $msg .= '0+';
    }
    if (isset($_POST['ins']) && $form['target_account'] < 100000001) {
        $msg = '5+';
    }
    // fine controlli
    if (isset($_POST['ins']) && $msg == '') {
        /** inizio modifica FP 09/01/2016
         * descrizione modificabile
         */
        if (!empty($form['descr_mov'])) {
            $desmov = $form['descr_mov'];
        }
        /** fine modifica FP */
        $tes_val = array('caucon' => '',
            'descri' => $desmov,
            'datreg' => $date,
            'datdoc' => $date,
        );
        tesmovInsert($tes_val);
        $tes_id = gaz_dbi_last_id();
        $tot_avere = $acc_tot;
        if ($form['transfer_fees'] >= 0.01 && $form['transfer_fees_acc'] > 100000000) {
            $tot_avere += $form['transfer_fees'];
        }
        rigmocInsert(array('id_tes' => $tes_id, 'darave' => 'A', 'codcon' => $form['target_account'], 'import' => $tot_avere));
        rigmocInsert(array('id_tes' => $tes_id, 'darave' => 'D', 'codcon' => $form['partner'], 'import' => $acc_tot));
        $rig_id = gaz_dbi_last_id();
        if ($form['transfer_fees'] >= 0.01 && $form['transfer_fees_acc'] > 100000000) {
            rigmocInsert(array('id_tes' => $tes_id, 'darave' => 'D', 'codcon' => $form['transfer_fees_acc'], 'import' => $form['transfer_fees']));
        }
        foreach ($form['paymov'] as $k => $v) { //attraverso l'array delle partite
            $acc = 0.00;
            foreach ($v as $ki => $vi) {
                $acc += $vi['amount'];
            }
            if ($acc >= 0.01) {
                paymovInsert(array('id_tesdoc_ref' => $k, 'id_rigmoc_pay' => $rig_id, 'amount' => $acc, 'expiry' => $date));
            }
        }
        header("Location: report_schedule_acq.php");
        exit;
    }
}
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup', /** ENRICO FEDELE */));
?>
<SCRIPT type="text/javascript">
    $(function () {
        $("#expiry_ini, #expiry_fin").datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
        $("#expiry_ini, #expiry_fin").change(function () {
            this.form.submit();
        });
    });
</script>
<?php
echo "<form method=\"POST\" name=\"select\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['ritorno'] . "\" name=\"ritorno\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['numdoc'] . "\" name=\"numdoc\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['datdoc'] . "\" name=\"datdoc\" />\n";
$gForm = new acquisForm();
echo "<br /><div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'];
echo "</div>\n";
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['mesg']) . "</td></tr>\n";
}
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['date_ini'] . "</td><td colspan=\"3\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('date_ini', $form['expiry_ini'], $form['expiry_fin'], $form['date_ini_Y'], 'FacetSelect', 1);
echo "</td>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl['target_account'] . "</td>\n ";
echo "<td class=\"FacetFieldCaptionTD\">";
echo "\t <select name=\"target_account\" tabindex=\"4\"   class=\"FacetSelect\" onchange=\"this.form.submit()\">\n"; //impropriamente usato per il numero di conto d'accredito
/** inizio modifica FP 28/11/2015 */
$isDocumentoSelezionato = !empty($form['numdoc']) && !empty($form['datdoc']);
/** fine modifica FP */
$masban = $admin_aziend['masban'] * 1000000;
$casse = substr($admin_aziend['cassa_'], 0, 3);
$mascas = $casse * 1000000;
$res = gaz_dbi_dyn_query('*', $gTables['clfoco'], "(codice LIKE '$casse%' AND codice > '$mascas') or (codice LIKE '" . $admin_aziend['masban'] . "%' AND codice > '$masban')", "codice ASC"); //recupero i c/c
echo "\t\t <option value=\"0\">--------------------------</option>\n";
while ($a = gaz_dbi_fetch_array($res)) {
    $sel = "";
    if ($a["codice"] == $form['target_account']) {
        $sel = "selected";
    }
    echo "\t\t <option value=\"" . $a["codice"] . "\" $sel >" . $a["codice"] . " - " . $a["descri"] . "</option>\n";
}
echo "\t </select></td>\n";
echo "</tr>";
/** inizio modifica FP 09/01/2016
 * descrizione modificabile
 */
echo "<tr>";
echo "<td class=\"FacetFieldCaptionTD\" colspan=\"2\">" . $script_transl['descr_mov'] . "</td>\n ";
echo "<td class=\"FacetDataTD\"> <input type=\"text\" name=\"descr_mov\" value=\"" . $form['descr_mov'] . "\" maxlength=\"85\" size=\"85\"></td>";
echo "</tr>";
/** fine modifica FP */
// qui aggiungo i dati necessari in fase di pagamento delle fatture di acquisto con bonifico bancario (sullo scadenzario) per poter proporre le eventuali spese per bonifico ed il relativo conto di costo di addebito 
echo "</tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\" colspan=\"2\">" . $script_transl['transfer_fees'] . "</td><td class=\"FacetDataTD\">
       <input type=\"text\" name=\"transfer_fees\" value=\"" . $form['transfer_fees'] . "\" maxlength=\"5\" size=\"5\" />
       </td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\" colspan=\"2\">" . $script_transl['transfer_fees_acc'] . "</td><td class=\"FacetDataTD\">";
$gForm->selectAccount('transfer_fees_acc', $form['transfer_fees_acc'], array('sub', 3), '', false, "col-sm-8");
echo "</td></tr>\n";
// fine campi per proposta dei costi di bonifico bancario

echo "</table>\n";
echo "</table></form>";
?>
<?php
require("../../library/include/footer.php");
?>
