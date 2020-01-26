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
$admin_aziend = checkAdmin();
$importo = abs($_GET['importo']);
$numpar = $_GET['numpar'];
$partner = $_GET['partner'];
$datdoc = $_GET['datdoc'];
$clfr = $_GET['clfr'];
if (!isset($_GET['hidden_req'])) { //al primo accesso allo script
   $descrizione = ($clfr == "C")? "incasso fattura" : "pagamento fattura";
   $form['date_reg_D'] = date("d");
   $form['date_reg_M'] = date("m");
   $form['date_reg_Y'] = date("Y");
   $form['target_account'] = 0;
} else { // accessi successivi
   $descrizione = $_GET['descrizione'];
   $form['date_reg_D'] = intval($_GET['date_reg_D']);
   $form['date_reg_M'] = intval($_GET['date_reg_M']);
   $form['date_reg_Y'] = intval($_GET['date_reg_Y']);
   $form['target_account'] = intval($_GET['target_account']);
}
$gForm = new GAzieForm();
if (isset($_GET['salva'])) {
   $adminid = $admin_aziend["user_name"];
//   $dataRegistrazione = mktime(0, 0, 0, $_GET['date_reg_M'], $_GET['date_reg_D'], $_GET['date_reg_Y']);
   $dataRegistrazione = gaz_create_date($_GET['date_reg_D'],$_GET['date_reg_M'],$_GET['date_reg_Y']);
   $target_account = $_GET['target_account'];
   $dataRegistrazioneUS = gaz_format_date($dataRegistrazione, true);
   $datdocUS = gaz_format_date($datdoc, true);
   if (controllo($dataRegistrazioneUS, $datdocUS, $target_account)) {
      salvaMovimento($_GET['descrizione'], $_GET['importo'], $target_account, $dataRegistrazioneUS, $numpar, $partner, $datdocUS, $adminid, $clfr);
   }
}
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup'));
echo "<script type=\"text/javascript\">
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

function controllo($dataRegistrazione, $datdoc, $target_account) {
   $retval = true;
   if (empty($target_account)) {
      alert("Errore: conto non selezionato");
      return false;
   }
   if ($dataRegistrazione < $datdoc) {
      alert("Errore: data di registrazione antecedente alla data di apertura della partita");
      return false;
   }
   return $retval;
}

function salvaMovimento($descrizione, $importo, $target_account, $dataRegistrazione, $numpar, $partner, $datdoc, $adminid, $clfr) {
   $valore = array(
       "caucon" => "",
       "descri" => $descrizione,
       "datreg" => $dataRegistrazione,
       "seziva" => "1", // eventualmente da modificare
       "id_doc" => 0,
       "protoc" => 0,
       "numdoc" => $numpar,
       "datdoc" => $datdoc,
       "clfoco" => $partner,
       "regiva" => "",
       "operat" => 0,
       "libgio" => "",
       "adminid" => $adminid,
       "last_modified" => null
   );
   gaz_dbi_table_insert("tesmov", $valore);
   $idInserito = gaz_dbi_last_id();
   if ($clfr == "C") {
      $contoDare = $target_account;
      $contoAvere = $partner;
   } else {
      $contoAvere = $target_account;
      $contoDare = $partner;
   }

   $valore = array(
       "id_rig" => null,
       "id_tes" => $idInserito,
       "darave" => "D",
       "codcon" => $contoDare,
       "import" => $importo);
   gaz_dbi_table_insert("rigmoc", $valore);
   $codiceOp1 = gaz_dbi_last_id();
   $valore = array(
       "id_rig" => null,
       "id_tes" => $idInserito,
       "darave" => "A",
       "codcon" => $contoAvere,
       "import" => $importo);
   gaz_dbi_table_insert("rigmoc", $valore);
   $codiceOp2 = gaz_dbi_last_id();
   $valore = array(
       "id" => null,
       "id_tesdoc_ref" => $numpar,
       "id_rigmoc_pay" => ($clfr != "C" ? $codiceOp1 : 0),
       "id_rigmoc_doc" => ($clfr != "C" ? 0 : $codiceOp2),
       "amount" => $importo,
       "expiry" => $dataRegistrazione);
   gaz_dbi_table_insert("paymov", $valore);
   fine();
}

function fine() {
   alert("operazione completata con successo");
   windowsClose();
}

function creaListaConti() {
   global $admin_aziend, $gTables;
   $conto = "<select name=\"target_account\" tabindex=\"4\"   class=\"FacetSelect\">\n"; //impropriamente usato per il numero di conto d'accredito
   $masban = $admin_aziend['masban'] * 1000000;
   $casse = substr($admin_aziend['cassa_'], 0, 3);
   $mascas = $casse * 1000000;
   $res = gaz_dbi_dyn_query('*', $gTables['clfoco'], "(codice LIKE '$casse%' AND codice > '$mascas') or (codice LIKE '" . $admin_aziend['masban'] . "%' AND codice > '$masban')", "codice ASC"); //recupero i c/c
   $conto = $conto . "\t\t <option value=\"0\">--------------------------</option>\n";
   while ($a = gaz_dbi_fetch_array($res)) {
      $sel = "";
//   if ($a["codice"] == $form['target_account']) {
//      $sel = "selected";
//      if (substr($a["codice"], 0, 3) == $casse) { // è un pagamento in contanti/assegno
//         $_GET['print_ticket'] = " checked";
//      } else {
//         $_GET['print_ticket'] = "";
//      }
//   }
      $conto = $conto . "\t\t <option value=\"" . $a["codice"] . "\" $sel >" . $a["codice"] . " - " . $a["descri"] . "</option>\n";
   }
   $conto = $conto . "\t </select>";
//$conto = $conto . "<td class=\"FacetDataTD\">" . $script_transl['print_ticket'] . "<input type=\"checkbox\" title=\"Per stampare la ricevuta seleziona questa checkbox\" name=\"print_ticket\" " . $_GET['print_ticket'] . " ></td>\n";
//   $conto = $conto . "</tr>";
   return $conto;
}
?>

<div class="FacetFormHeaderFont" align="center">Registrazione pagamento relativo a partita <?= $numpar ?></div>

<form action="payment.php" method="get">
    <input value="true" name="hidden_req" type="hidden">
    <input type="hidden" name="numpar" value="<?= $numpar ?>">
    <input type="hidden" name="partner" value="<?= $partner ?>">
    <input type="hidden" name="datdoc" value="<?= $datdoc ?>">
    <input type="hidden" name="clfr" value="<?= $clfr ?>">
    <table class="Tmiddle">
        <tbody>
            <tr>
                <td class="FacetFieldCaptionTD">Data di registrazione</td>
                <td colspan="5" class="FacetDataTD">
                    <?php $gForm->CalendarPopup('date_reg', $form['date_reg_D'], $form['date_reg_M'], $form['date_reg_Y'], 'FacetSelect', 1); ?>
                </td>
            </tr>
            <tr>
                <td class="FacetFieldCaptionTD">Descrizione</td>
                <td class="FacetDataTD" colspan="2">
                    <input id="descrizione" name="descrizione" value="<?= $descrizione ?>" maxlength="100" size="80" required="true" type="text">
                </td>
            </tr>
            <tr>
                <td class="FacetFieldCaptionTD">Importo</td>
                <td colspan="2" class="FacetDataTD">
                    <input id="prezzoUnitario" name="importo" value="<?= $importo ?>" maxlength="10" size="10" required="true" step="any" type="number"> 
                </td>
            </tr>
            <tr>
                <td class="FacetFieldCaptionTD">Conto</td>
                <td class="FacetDataTD" colspan="2">
                    <?= creaListaConti(); ?>
                </td>
            </tr>
            <tr class="FacetFieldCaptionTD">
                <td align="left"></td>
                <td colspan="2" align="right"> 
                    <input type="button" name="annulla" value="Indietro" onclick="window.close()"> <input type="submit" name="salva" value="Ok">
                </td>	 
            </tr>

        </tbody>
    </table>        
</form>
<?php
require("../../library/include/footer.php");
?>