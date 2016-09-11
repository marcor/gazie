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

$admin_aziend = checkAdmin();

$mastrofornitori = $admin_aziend['masfor'] . "000000";
$inifornitori = $admin_aziend['masfor'] . '000001';
$finfornitori = $admin_aziend['masfor'] . '999999';
$msg = '';

if (!isset($_POST['ritorno'])) { //al primo accesso allo script
   $msg = '';
   $form['ritorno'] = $_SERVER['HTTP_REFERER'];
//   if (isset($_GET['id_agente'])) { //se mi viene richiesto un agente specifico...
//      $form['id_agente'] = intval($_GET['id_agente']);
//   } else {
//      $form['id_agente'] = 0;
//   }
//   $form['cerca_agente'] = '';
   if (isset($_POST['datini'])) {
      $form['gi'] = substr($_POST['datini'], 6, 2);
      $form['mi'] = substr($_POST['datini'], 4, 2);
      $form['ai'] = substr($_POST['datini'], 0, 4);
   } else {
      $form['gi'] = 1;
      $form['mi'] = 1;
      $form['ai'] = date("Y");
   }
   if (isset($_POST['datfin'])) {
      $form['gf'] = substr($_POST['datfin'], 6, 2);
      $form['mf'] = substr($_POST['datfin'], 4, 2);
      $form['af'] = substr($_POST['datfin'], 0, 4);
   } else {
      $form['gf'] = date("d");
      $form['mf'] = date("m");
      $form['af'] = date("Y");
   }
   $form['search']['partner'] = '';
   $form['partner'] = 0;
   unset($resultFatturato);
   $form['hidden_req'] = '';
} else { // le richieste successive
   $form['ritorno'] = $_POST['ritorno'];
   $form['gi'] = intval($_POST['gi']);
   $form['mi'] = intval($_POST['mi']);
   $form['ai'] = intval($_POST['ai']);
   $form['gf'] = intval($_POST['gf']);
   $form['mf'] = intval($_POST['mf']);
   $form['af'] = intval($_POST['af']);
   $form['search']['partner'] = substr($_POST['search']['partner'], 0, 20);
   $form['partner'] = intval($_POST['partner']);
   $form['hidden_req'] = $_POST['hidden_req'];
}


if (isset($_POST['preview'])) {
   if (empty($form['partner'])) {
      $msg .= "0+";
   }
   if (empty($msg)) { //non ci sono errori
      $datini = sprintf("%04d%02d%02d", $form['ai'], $form['mi'], $form['gi']);
      $datfin = sprintf("%04d%02d%02d", $form['af'], $form['mf'], $form['gf']);
//       $_SESSION['print_request'] = array('livello'=>$form['livello'],'di'=>$datini,'df'=>$datfin);
//       header("Location: invsta_analisi_agenti.php");
      $what = "fornitori.codice as codice_fornitore, concat(dati_fornitori.ragso1,' ',dati_fornitori.ragso2) as nome_fornitore, 
sum(CASE WHEN (tesdoc.datfat between '$datini' and '$datfin' and tesdoc.tipdoc like 'FA%') THEN rigdoc.quanti*rigdoc.prelis*(1-rigdoc.sconto/100) ELSE 0 END) as imp_ven,
sum(CASE WHEN (tesdoc.datfat between '$datini' and '$datfin' and tesdoc.tipdoc like 'FA%') THEN rigdoc.quanti*artico.preacq ELSE 0 END) as imp_acq";
      $tab_rigdoc = $gTables['rigdoc'];
      $tab_tesdoc = $gTables['tesdoc'];
      $tab_artico = $gTables['artico'];
      $tab_anagra = $gTables['anagra'];
      $tab_clfoco = $gTables['clfoco'];
      $table = "$tab_rigdoc rigdoc 
left join $tab_tesdoc tesdoc on rigdoc.id_tes=tesdoc.id_tes 
left join $tab_artico artico on artico.codice=rigdoc.codart 
left join $tab_clfoco fornitori on artico.clfoco=fornitori.codice 
left join $tab_anagra dati_fornitori on fornitori.id_anagra=dati_fornitori.id 
left join $tab_clfoco clienti on tesdoc.clfoco=clienti.codice 
left join $tab_anagra dati_clienti on clienti.id_anagra=dati_clienti.id ";
      $codcli = $form['partner'];
      $where = "tesdoc.tipdoc like 'F%' and rigdoc.quanti>0 and artico.ragstat is not null and artico.ragstat!=''" .
              " and clienti.codice = '$codcli'";
      $order = "nome_fornitore";
      $group = "fornitori.codice";
      $resultFatturato = gaz_dbi_dyn_query($what, $table, $where, $order, 0, 20000, $group);
   }
}

if (isset($_POST['Return'])) {
   header("Location:docume_vendit.php");
   exit;
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$vendForm = new venditForm();

echo "<form method=\"POST\">";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"" . $form['ritorno'] . "\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'] . "</div>";
echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">";
if (!empty($msg)) {
   $message = "";
   $rsmsg = array_slice(explode('+', chop($msg)), 0, -1);
   foreach ($rsmsg as $value) {
      $message .= $script_transl['error'] . "! -> ";
      $rsval = explode('-', chop($value));
      foreach ($rsval as $valmsg) {
         $message .= $script_transl['errors'][$valmsg] . " ";
      }
      $message .= "<br>";
   }
   echo '<tr><td colspan="5" class="FacetDataTDred">' . $message . '</td></tr>';
}
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['partner'] . "</td><td colspan=\"3\" class=\"FacetDataTD\">\n";
$vendForm->selectCustomer('partner', $form['partner'], $form['search']['partner'], $form['hidden_req'], $script_transl['mesg']);
echo "</td>\n";
echo "</tr>\n";

echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[0]</td>";
echo "<td class=\"FacetDataTD\">";
// select del giorno
echo "\t <select name=\"gi\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
   $selected = "";
   if ($counter == $form['gi'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
}
echo "\t </select>\n";
// select del mese
echo "\t <select name=\"mi\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
   $selected = "";
   if ($counter == $form['mi'])
      $selected = "selected";
   $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
   echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
// select del anno
echo "\t <select name=\"ai\" class=\"FacetSelect\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
   $selected = "";
   if ($counter == $form['ai'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}

echo "\t </select>\n";
echo "</td></tr>";
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td>";
echo "<td class=\"FacetDataTD\">";
// select del giorno
echo "\t <select name=\"gf\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
   $selected = "";
   if ($counter == $form['gf'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
}
echo "\t </select>\n";
// select del mese
echo "\t <select name=\"mf\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
   $selected = "";
   if ($counter == $form['mf'])
      $selected = "selected";
   $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
   echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
// select del anno
echo "\t <select name=\"af\" class=\"FacetSelect\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
   $selected = "";
   if ($counter == $form['af'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "</td></tr>";

//echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[2]</td>";
//echo "<td class=\"FacetDataTD\">";
//echo "<input title=\"anno da analizzare\" type=\"text\" name=\"livello\" value=\"" .
// $form["livello"] . "\" maxlength=\"5\" size=\"5\" class=\"FacetInput\">";
//echo "</td></tr>";
//echo "<tr>\n";
//echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_agente'] . "</td>";
//echo "<td  class=\"FacetDataTD\">\n";
//$select_agente = new selectAgente("id_agente");
//$select_agente->addSelected($form["id_agente"]);
//$select_agente->output();
//echo "</td></tr>\n";

echo "<tr>\n
     <td class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"Return\" value=\"" . ucfirst($script_transl['return']) . "\"></td>\n
     <td align=\"right\" class=\"FacetFooterTD\"><input type=\"submit\" accesskey=\"i\" name=\"preview\" value=\"" . ucfirst($script_transl['preview']) . "\"></td>\n
     </tr>\n</table>";
echo "<table class=\"Tlarge\">";
if (isset($resultFatturato)) {
   $linkHeaders = new linkHeaders($script_transl['header']);
   $linkHeaders->output();
   $totFatturato = 0;
   $totCosti = 0;
   while ($mv = gaz_dbi_fetch_array($resultFatturato)) {
      $nFatturato = $mv['imp_ven'];
      if ($nFatturato > 0) {
         $nCosti = $mv['imp_acq'];
         $margine = ($nFatturato - $nCosti) * 100 / $nFatturato;
         $totFatturato+=$nFatturato;
         $totCosti+=$nCosti;
         echo "<tr>";
         echo "<td class=\"FacetFieldCaptionTD\">" . substr($mv[0], 3) . " &nbsp;</td>";
         echo "<td align=\"left\" class=\"FacetDataTD\">" . $mv[1] . " &nbsp;</td>";
         echo "<td align=\"right\" class=\"FacetDataTD\">" . gaz_format_number($nFatturato) . " &nbsp;</td>";
         echo "<td align=\"right\" class=\"FacetDataTD\">" . gaz_format_number($nCosti) . " &nbsp;</td>";
         echo "<td align=\"right\" class=\"FacetDataTD\">" . gaz_format_number($margine) . " &nbsp;</td>";
         echo "</tr>";
      }
   }
   $margine = ($totFatturato > 0 ? ($totFatturato - $totCosti) * 100 / $totFatturato : 0);
   echo "<tr>";
   echo "<td class=\"FacetFieldCaptionTD\"> &nbsp;</td>";
   echo "<td align=\"left\" class=\"FacetDataTD\"><B>" . $script_transl['totale'] . "</B> &nbsp;</td>";
   echo "<td align=\"right\" class=\"FacetDataTD\"><B>" . gaz_format_number($totFatturato) . "</B> &nbsp;</td>";
   echo "<td align=\"right\" class=\"FacetDataTD\"><B>" . gaz_format_number($totCosti) . "</B> &nbsp;</td>";
   echo "<td align=\"right\" class=\"FacetDataTD\"><B>" . gaz_format_number($margine) . "</B> &nbsp;</td>";
   echo "</tr>";
   echo '<tr class="FacetFieldCaptionTD">
	 			<td colspan="12" align="right"><input type="button" name="print" onclick="window.print();" value="' . $script_transl['print'] . '"></td>
	 	  </tr>';
}
?>
</table>
</form>
</div><!-- chiude div container role main --></body>
</html>