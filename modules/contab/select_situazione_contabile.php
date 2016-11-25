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
$msg = "";

$form['orderby'] = 2;
if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
   $form['hidden_req'] = '';
   $form['ritorno'] = $_SERVER['HTTP_REFERER'];
//   $form['this_date_Y'] = date("Y");
//   $form['this_date_M'] = date("m");
//   $form['this_date_D'] = date("d");
   $form['id_anagra'] = '';
   $form['search']['id_anagra'] = '';
   $form['id_agente'] = 0;
   $check_tutte_aperte0 = "checked";
   $check_tutte_aperte1 = "";
   $check_clfr0 = "checked";
   $check_clfr1 = "";
   $form['clfr'] = 0;
} else { // accessi successivi
   $form['hidden_req'] = htmlentities($_POST['hidden_req']);
   $form['ritorno'] = $_POST['ritorno'];
//   $form['this_date_Y'] = intval($_POST['this_date_Y']);
//   $form['this_date_M'] = intval($_POST['this_date_M']);
//   $form['this_date_D'] = intval($_POST['this_date_D']);
   $form['id_anagra'] = $_POST['id_anagra'];
   $form['id_agente'] = intval($_POST['id_agente']);
   $form['clfr'] = $_POST['clfr'];
   $check_tutte_aperte0 = ($_POST['aperte_tutte'] == 0 ? "checked" : "");
   $check_tutte_aperte1 = ($_POST['aperte_tutte'] == 1 ? "checked" : "");
   $check_clfr0 = ($form['clfr'] == 0 ? "checked" : "");
   $check_clfr1 = ($form['clfr'] == 1 ? "checked" : "");
//   if (isset($_POST['search'])) {
   foreach ($_POST['search'] as $k => $v) {
      $form['search'][$k] = $v;
   }
//   } else {
//      $form['search']['id_anagra'] = '';
//   }
   if (isset($_POST['return'])) {
      header("Location: " . $form['ritorno']);
      exit;
   }
}

//controllo i campi
//if (!checkdate($form['this_date_M'], $form['this_date_D'], $form['this_date_Y'])) {
//   $msg .='0+';
//}
//$utsexe = mktime(0, 0, 0, $form['this_date_M'], $form['this_date_D'], $form['this_date_Y']);
// fine controlli

if (isset($_POST['print']) && $msg == '') {
   $_SESSION['print_request'] = array('script_name' => 'print_situazione_contabile',
       'orderby' => $form['orderby'],
       'id_anagra' => $form['id_anagra'],
       'clfr' => $form['clfr'],
       'id_agente' => $form['id_agente'],
       'aperte_tutte' => $_POST['aperte_tutte'],
   );
   header("Location: sent_print.php");
   exit;
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
function doConfirm() {
   retVal=true;
   id_anagra=document.getElementById('id_anagra');
   id_agente=document.getElementById('id_agente');
   val_id_anagra=(id_anagra?id_anagra.value:0);
   val_id_agente=(id_agente?id_agente.value:0);
//   alert(val_id_anagra+' - '+val_id_agente);
   
//   if(val_id_anagra==0 && val_id_agente==0){
//      retVal=confirm('Se non si seleziona un\'anagrafica o un agente, l\'operazione potrebbe impiegare molto tempo. Vuoi continuare?');
//   }
   return retVal;
}
function clickAndDisable(link) {
   // disable subsequent clicks
   link.onclick = function(event) {
      event.preventDefault();
   }
 }  
</script>
";
echo "<form method=\"POST\" name=\"select\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['ritorno'] . "\" name=\"ritorno\" />\n";
//echo "<input type=\"hidden\" value=\"" . $form['search'] . "\" name=\"search\" />\n";
$gForm = new venditForm();
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'];
echo "</div>\n";
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
   echo '<tr><td colspan="2" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
}

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['clfr'] . "</td><td  colspan=\"2\" class=\"FacetDataTD\">\n";
echo "\t\t <input type=\"radio\" name=\"clfr\" value=0 $check_clfr0> clienti \n";
echo "\t\t <input type=\"radio\" name=\"clfr\" value=1 $check_clfr1> fornitori \n";
echo "</td>\n";

echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_anagra'] . " </td><td class=\"FacetDataTD\" colspan=\"2\">\n";
$select_id_anagra = new selectPartner("id_anagra");
//$select_id_anagra->selectAnagra('id_anagra', $form['id_anagra'], $form['search']['id_anagra'], 'id_anagra', $script_transl['mesg'], false, "codice like '" . ($form['clfr'] == 0 ? $admin_aziend['mascli'] : $admin_aziend['masfor']) . "%'");
$select_id_anagra->selectDocPartner('id_anagra', $form['id_anagra'], $form['search']['id_anagra'], 'id_anagra', $script_transl['mesg'], ($form['clfr'] == 0 ? $admin_aziend['mascli'] : $admin_aziend['masfor']), -1, 1, true);
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['aperte_tutte'] . "</td><td  colspan=\"2\" class=\"FacetDataTD\">\n";
echo "\t\t <input type=\"radio\" name=\"aperte_tutte\" value=0 $check_tutte_aperte0> solo partite aperte \n";
echo "\t\t <input type=\"radio\" name=\"aperte_tutte\" value=1 $check_tutte_aperte1> estratto conto \n";
echo "</td>\n";

echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_agente'] . "</td>";
echo "<td  class=\"FacetDataTD\">\n";
$select_agente = new selectAgente("id_agente");
$select_agente->addSelected($form["id_agente"]);
$select_agente->output();
echo "</td></tr>\n";

//echo "<tr>\n";
//echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['date'] . "</td><td  colspan=\"2\" class=\"FacetDataTD\">\n";
//$gForm->CalendarPopup('this_date', $form['this_date_D'], $form['this_date_M'], $form['this_date_Y'], 'FacetSelect', 1);
//echo "</td>\n";
//echo "</tr>\n";

echo "\t<tr class=\"FacetFieldCaptionTD\">\n";
echo "<td align=\"left\"><input type=\"submit\" name=\"return\" value=\"" . $script_transl['return'] . "\">\n";
echo '<td align="right" colspan="2"> <input type="submit" accesskey="i" name="preview" value="';
echo $script_transl['view'];
echo '" tabindex="100" onclick="return doConfirm()">';
//echo "\t </td>\n";
//echo '<td colspan="12" align="right">';
echo '<input type="submit" name="print" value="' . $script_transl['print'] . '"  onclick="return doConfirm()"></td>';
echo "\t </tr>\n";
echo "</table>\n";

if ($form['clfr'] == 0) {
   $cosaStampare = $admin_aziend['mascli']; // clienti
//   $linkPagamento = "vendit/customer_payment.php";
   $clfr = "C";
} else {
   $cosaStampare = $admin_aziend['masfor']; // fornitori
//   $linkPagamento = "acquis/supplier_payment.php";
   $clfr = "F";
}
$linkPagamento = "contab/payment.php?clfr=$clfr";

if (isset($_POST['preview']) and $msg == '') {
   $scdl = new Schedule;
   $select_id_anagra = new selectPartner("id_anagra");
   if (!empty($form['id_anagra'])) {
//      $cosaStampare = $select_id_anagra->queryClfoco($form['id_anagra'], ($form['clfr'] == 0 ? $admin_aziend['mascli'] : $admin_aziend['masfor'])); // anagrafe selezionata
      $cosaStampare = $form['id_anagra']; // anagrafe selezionata
   }

   ini_set('memory_limit', '128M'); // mi occorre tanta memoria
   gaz_set_time_limit(0);  // e tanto tempo
//   $cosaStampare = "103000974";
   $soloAperte = ($_POST['aperte_tutte'] == 0);
   $rs = $scdl->getPartite($form['orderby'], $cosaStampare, $form['id_agente'], $soloAperte);
   echo "<table class=\"Tlarge\">";
//   if (sizeof($scdl->Entries) > 0) {
   if ($rs->num_rows > 0) {
      $ctrl_partner = 0;
      $ctrl_id_tes = 0;
      $ctrl_paymov = 0;

      /* ENRICO FEDELE */
      /* Inizializzo le variabili per il totale */
      $tot_dare = 0;
      $tot_avere = 0;
      /* ENRICO FEDELE */

      $tot_diff_anagrafe = 0;

      echo "<tr>";
      $linkHeaders = new linkHeaders($script_transl['header']);
      $linkHeaders->output();
      echo "</tr>";
      $mv = gaz_dbi_fetch_array($rs);
      calcNumPartitaAperta($mv);
//      while (list($key, $mv) = each($scdl->Entries)) {
      while ($mv) {
         $class_partner = '';
         $class_paymov = '';
         $class_id_tes = '';
         $partner = '';
         $id_tes = '';
         $paymov = '';
         $status_del = false;
         $dati_partite = array();
         $ctrl_partner = $mv["clfoco"];
         $ctrl_id_tesdoc_ref = $mv["id_tesdoc_ref"];
         $tot_diff_tmp = 0;
         $tot_avere_tmp = 0;
         do {
            $dati_partite[] = $mv;
//            if ($mv['id_rigmoc_pay'] == 0) {
            if ($mv['darave'] == 'D') {
               /* Incremento il totale del dare */
               $tot_diff_tmp += $mv['amount'];
            } else {
               $tot_diff_tmp -= $mv['amount'];
            }
            $mv = gaz_dbi_fetch_array($rs);
            calcNumPartitaAperta($mv);
         } while ($mv && ($mv["clfoco"] == $ctrl_partner) && ($mv["id_tesdoc_ref"] == $ctrl_id_tesdoc_ref));
//         if ($tot_diff_tmp == 0 && $_POST['aperte_tutte'] == 0) {// la partita è chiusa ed io voglio solo le partite aperte
         if (abs($tot_diff_tmp) < 0.01 /* meno di 1 centesimo contabilmente è uguale a zero */ && $soloAperte) {// la partita è chiusa ed io voglio solo le partite aperte
            continue;
         }
         $tot_diff_anagrafe+=$tot_diff_tmp;

         $primo = true;
         foreach ($dati_partite as $mv_tmp) {
            if ($primo) {
               $class_partner = 'FacetDataTDred';
               $partner = $mv_tmp["ragsoc"];
               $class_id_tes = 'FacetFieldCaptionTD';
               $id_tes = $mv_tmp["id_tes"];
               $mv_tmp["datdoc"] = gaz_format_date($mv_tmp["datdoc"]);
               $paymov = $mv_tmp["id_tesdoc_ref"];
//               $scdl->getStatus($paymov);
               if (abs($tot_diff_tmp) > 0.01) {
                  $class_paymov = 'FacetDataTDevidenziaOK';
                  $status_descr = $script_transl['status_value'][1] .
                          " &nbsp;<a target=\"_blank\" onclick=\"clickAndDisable(this);\" title=\"Riscuoti\" class=\"btn btn-xs btn-default btn-pagamento\" href=\"../" . $linkPagamento . "&partner=" . $mv_tmp["clfoco"] . "&numdoc=" . $mv_tmp["numdoc"] . "&datdoc=" . $mv_tmp["datdoc"] . "&numpar=" . $mv_tmp["id_tesdoc_ref"] . "&importo=" . (-$tot_diff_tmp) . "\"><i class=\"glyphicon glyphicon-euro\"></i></a>";
               } else {
                  $class_paymov = 'FacetDataTDevidenziaCL';
                  $status_descr = $script_transl['status_value'][0];
               }
               $primo = false;
            } else {
//               $mv_tmp['descri'] = '';
               $mv_tmp['numdoc'] = '';
               $mv_tmp['datdoc'] = '';
               $id_tes = '';
               $class_partner = '';
               $partner = '';
               $status_descr = '';
               $status_del = true;
            }
            echo "<tr>";
            echo "<td class=\"$class_partner\">" . $partner . " &nbsp;</td>";
            echo "<td align=\"center\" class=\"$class_paymov\">" . $paymov . " &nbsp;</td>";
            echo "<td align=\"center\" class=\"$class_paymov\">" . $status_descr . " &nbsp;</td>";
            echo "<td align=\"center\" class=\"$class_id_tes\"><a href=\"../contab/admin_movcon.php?id_tes=" . $mv_tmp["id_tes"] . "&Update\">" . $id_tes . "</a> &nbsp</td>";
            echo "<td class=\"$class_id_tes\"><a href=\"../contab/admin_movcon.php?id_tes=" . $mv_tmp["id_tes"] . "&Update\">" . $mv_tmp['descri'] . "</a> &nbsp;</td>";
            echo "<td align=\"center\" class=\"FacetDataTD\">" . $mv_tmp["numdoc"] . " &nbsp;</td>";
            echo "<td align=\"center\" class=\"FacetDataTD\">" . $mv_tmp["datdoc"] . " &nbsp;</td>";
            echo "<td align=\"center\" class=\"FacetDataTD\">" . gaz_format_date($mv_tmp["datreg"]) . " &nbsp;</td>";
            /* ENRICO FEDELE */
            if ($mv_tmp['darave'] == 'D') {
               /* Incremento il totale del dare */
               $tot_dare += $mv_tmp['amount'];
               /* Allineo a destra il testo, i numeri sono così più leggibili e ordinati, li formatto con apposita funzione */
               echo "<td class=\"FacetDataTD\" align=\"right\">" . gaz_format_number($mv_tmp["amount"]) . " &nbsp;</td>";
               echo "<td class=\"FacetDataTD\"></td>";
            } else {
               /* Incremento il totale dell'avere, e decremento quello del dare */
               $tot_avere += $mv_tmp['amount'];
//               $tot_dare -= $mv_tmp['amount'];
               echo "<td class=\"FacetDataTD\"></td>";
               echo "<td class=\"FacetDataTD\" align=\"right\">" . gaz_format_number($mv_tmp["import"]) . " &nbsp;</td>";
            }
            /* ENRICO FEDELE */
            echo "<td align=\"center\" class=\"FacetDataTD\">" . gaz_format_date($mv_tmp["expiry"]) . " &nbsp;</td>";
            echo "<td align=\"center\" class=\"FacetDataTD\"> ";
            // Permette di cancellare il documento.
            if ($status_del) {
               echo "<a class=\"btn btn-xs btn-default btn-elimina\" title=\"Cancella tutti i movimenti relativi a questa partita oramai chiusa (rimarranno comunque i movimenti contabili)\" href=\"../vendit/delete_schedule.php?id_tesdoc_ref=" . $paymov . "\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
            } else {
               echo "<button title=\"Non &egrave; possibile cancellare una partita ancora aperta\" class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button>";
            }
            echo "</td></tr>\n";
         }
         $ctrl_id_tes = $mv["id_tes"];
         $ctrl_paymov = $mv["id_tesdoc_ref"];

         /* TOTALI PARTITA */
         echo '<tr">
			<td colspan="10" align="right" class="' . $class_paymov . '">SALDO PARTITA</td>
			<td colspan="2" title="saldo" align="right" class="' . $class_paymov . '">' . gaz_format_number(-$tot_diff_tmp) . '</td>
		  </tr>';
         /* TOTALI ANAGRAFE */
         if (!$mv || $mv["clfoco"] != $ctrl_partner) { // si cambia anagrafe alla prossima iterazione
            echo '<tr class="FacetDataTRTotAnagrafe">'
            . '<td colspan="10" align="right">'
            . 'SALDO ANAGRAFE</td><td colspan="2" title="saldo" align="right">'
            . gaz_format_number(-$tot_diff_anagrafe)
            . '</td></tr>';
            $tot_diff_anagrafe = 0;
         }
      }
      /** ENRICO FEDELE */
      /* Stampo il totale del dare, dell'avere, e la percentuale dell'avere rispetto al totale dare+avere */
      /* Aumento il colspan nell'ultima riga per ricomprendere anche l'ultima colonna, il pulsante stampa ora va sotto opzioni */
//			<td class="FacetFormHeaderFont" title="% dare-avere">' . gaz_format_number(100 * $tot_avere / ($tot_dare + $tot_avere)) . ' %</td>

      echo '<tr>
			<td colspan="8" class="FacetFormHeaderFont" align="right">TOTALE</td>
			<td class="FacetFormHeaderFont" align="right">' . gaz_format_number($tot_dare) . '</td>
			<td class="FacetFormHeaderFont" align="right">' . gaz_format_number($tot_avere) . '</td>
			<td class="FacetFormHeaderFont" title="% avere/dare"></td>
			<td class="FacetFormHeaderFont" title="saldo">' . gaz_format_number(-$tot_dare + $tot_avere) . '</td>
			<td class="FacetFormHeaderFont">&nbsp;</td>
		  </tr>
		  <tr class="FacetFieldCaptionTD">
	 			<td colspan="12" align="right"><input type="submit" name="print" value="' . $script_transl['print'] . '"></td>
	 	  </tr>';

      /** ENRICO FEDELE */
   } else {
      echo "<tr><td class=\"FacetDataTDred\" align=\"center\">" . $script_transl['errors'][1] . "</TD></TR>\n";
   }
   echo "</table>";
}
?>
<?php
require("../../library/include/footer.php");
?>