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

function getRiepilogo($mastro, $anagrafe, $id_agente) {
   global $gTables;
//   $orderby = "ragsoc, id_tesdoc_ref, datreg, paymov.id ";
////   $select = "tesmov.clfoco, paymov.id_tesdoc_ref, rigmoc.darave, rigmoc.import, rigmoc.id_tes, "
////           . "tesmov.datdoc, tesmov.numdoc, tesmov.datreg, paymov.expiry, clfoco.descri AS ragsoc, "
////           . "tesmov.descri, tesmov.caucon, amount,"
////           . "anagra.sedleg, anagra.telefo, anagra.cell ";
//
//   $select = "tesmov.clfoco,clfoco.descri AS ragsoc,anagra.sedleg, anagra.telefo, anagra.cell,"
//           . "sum(CASE WHEN (darave='D') THEN amount ELSE 0 END) as amountDare,   "
//           . "sum(CASE WHEN (darave='A') THEN amount ELSE 0 END) as amountAvere ";
   if (empty($anagrafe)) {
      $where = "where clfoco.codice LIKE '$mastro%' ";
   } else {
      $where = "where clfoco.codice = " . $anagrafe;
   }
   if (!empty($id_agente)) {
      $where.=" and clfoco.id_agente =$id_agente";
   }
//   $table = $gTables['clfoco'] . " clfoco INNER JOIN " . $gTables['rigmoc'] . " rigmoc ON clfoco.codice = rigmoc.codcon "
//           . "LEFT JOIN " . $gTables['tesmov'] . " tesmov ON rigmoc.id_tes = tesmov.id_tes "
//           . "LEFT JOIN " . $gTables['anagra'] . " anagra ON anagra.id = clfoco.id_anagra "
//           . "LEFT JOIN " . $gTables['paymov'] . " paymov ON (paymov.id_rigmoc_pay = rigmoc.id_rig OR paymov.id_rigmoc_doc = rigmoc.id_rig )";
//   $groupby = "clfoco.id_anagra";

   $paymov = $gTables['paymov'];
   $rigmoc = $gTables['rigmoc'];
   $tesmov = $gTables['tesmov'];
   $clfoco = $gTables['clfoco'];
   $anagra = $gTables['anagra'];
   $movimenti = $gTables['movimenti'];
//   $query = "create OR REPLACE view movimenti as select * from $paymov paymov
//JOIN $rigmoc rigmoc ON (paymov.id_rigmoc_pay = rigmoc.id_rig)
//union all
//select * from $paymov paymov
//JOIN $rigmoc rigmoc ON (paymov.id_rigmoc_doc = rigmoc.id_rig );";

   $query = "SELECT tesmov.clfoco,clfoco.descri AS ragsoc,anagra.sedleg, anagra.telefo, anagra.cell,
sum(CASE WHEN (darave='D') THEN amount ELSE 0 END) as amountDare,   
sum(CASE WHEN (darave='A') THEN amount ELSE 0 END) as amountAvere  
FROM $movimenti movimenti
JOIN $tesmov tesmov ON movimenti.id_tes = tesmov.id_tes
JOIN $clfoco clfoco ON clfoco.codice = movimenti.codcon
JOIN $anagra anagra ON anagra.id = clfoco.id_anagra
$where 
group by tesmov.clfoco
having amountDare!=amountAvere
order by clfoco.descri;
";
//      $this->Entries = array();
   $rs = gaz_dbi_query($query);
//   gaz_dbi_dyn_query($select, $tabella, $where = 1, $orderby = 2, $limit = 0, $passo = 2000000, $groupby = '') 
//      while ($r = gaz_dbi_fetch_array($rs)) {
//         $this->Entries[] = $r;
//      }
   return $rs;
}

//$form['orderby'] = 2;
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
//   $check_tutte_aperte0 = ($_POST['aperte_tutte'] == 0 ? "checked" : "");
//   $check_tutte_aperte1 = ($_POST['aperte_tutte'] == 1 ? "checked" : "");
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
//       'orderby' => $form['orderby'],
       'id_anagra' => $form['id_anagra'],
       'clfr' => $form['clfr'],
       'id_agente' => $form['id_agente'],
//       'aperte_tutte' => $_POST['aperte_tutte'],
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
//echo "</td>\n";
//echo "</tr>\n";
//
//echo "<tr>\n";
//echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['aperte_tutte'] . "</td><td  colspan=\"2\" class=\"FacetDataTD\">\n";
//echo "\t\t <input type=\"radio\" name=\"aperte_tutte\" value=0 $check_tutte_aperte0> solo partite aperte \n";
//echo "\t\t <input type=\"radio\" name=\"aperte_tutte\" value=1 $check_tutte_aperte1> estratto conto \n";
//echo "</td>\n";

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
//echo '<input type="submit" name="print" value="' . $script_transl['print'] . '"  onclick="return doConfirm()"></td>';
//echo "\t </tr>\n";
echo "</table>\n";

$mastro = "";
if ($form['clfr'] == 0) {
   $mastro = $admin_aziend['mascli']; // clienti
//   $linkPagamento = "vendit/customer_payment.php";
   $clfr = "C";
} else {
   $mastro = $admin_aziend['masfor']; // fornitori
//   $linkPagamento = "acquis/supplier_payment.php";
   $clfr = "F";
}
//$linkPagamento = "contab/payment.php?clfr=$clfr";

$anagrafe = "";
if (isset($_POST['preview'])) {
   if ($msg == '') {
      $scdl = new Schedule;
      $select_id_anagra = new selectPartner("id_anagra");
      if (!empty($form['id_anagra'])) {
         $anagrafe = $form['id_anagra']; // anagrafe selezionata
      }

      ini_set('memory_limit', '128M'); // mi occorre tanta memoria
      gaz_set_time_limit(0);  // e tanto tempo
      $rs = getRiepilogo($mastro, $anagrafe, $form['id_agente']);
      echo "<table class=\"Tlarge\">";
//   if (sizeof($scdl->Entries) > 0) {
      if ($rs->num_rows > 0) {
         /* Inizializzo le variabili per il totale */
         $tot_dare = 0;
         $tot_avere = 0;
         echo "<tr>";
         $linkHeaders = new linkHeaders($script_transl['header']);
         $linkHeaders->output();
         echo "</tr>";
         while ($mv = gaz_dbi_fetch_array($rs)) {
            echo "<tr>";
            $partner = $mv['ragsoc'] . " - " . $mv['sedleg'] . " - " . $mv['telefo'];
            echo "<td class=\"FacetFieldCaptionTD\">" . $partner . " &nbsp;</td>";
            echo "<td class=\"FacetDataTD\" align=\"right\">" . gaz_format_number($mv['amountDare']) . " &nbsp;</td>";
            echo "<td class=\"FacetDataTD\" align=\"right\">" . gaz_format_number($mv['amountAvere']) . " &nbsp;</td>";
            echo "<td class=\"FacetDataTD\" align=\"right\">" . gaz_format_number($mv['amountDare'] - $mv['amountAvere']) . " &nbsp;</td>";
            echo "</tr>\n";
            $tot_dare+=$mv['amountDare'];
            $tot_avere+=$mv['amountAvere'];
         }
      }
      /* Stampo il totale del dare, dell'avere, e la percentuale dell'avere rispetto al totale dare+avere */
      echo '<tr>
			<td colspan="1" class="FacetFormHeaderFont" align="right">TOTALI</td>
			<td class="FacetFormHeaderFont" align="right">' . gaz_format_number($tot_dare) . '</td>
			<td class="FacetFormHeaderFont" align="right">' . gaz_format_number($tot_avere) . '</td>
			<td class="FacetFormHeaderFont" align="right" title="saldo">' . gaz_format_number(-$tot_dare + $tot_avere) . '</td>
		  </tr>
		  <tr class="FacetFieldCaptionTD">
	 			<td colspan="12" align="right"><input type="button" name="print" onclick="window.print();" value="' . $script_transl['print'] . '"></td>
	 	  </tr>';

      /** ENRICO FEDELE */
   } else {
      echo "<tr><td class=\"FacetDataTDred\" align=\"center\">" . $script_transl['errors'][1] . "</TD></TR>\n";
   }
}
echo "</table>";
?>
<?php
require("../../library/include/footer.php");
?>