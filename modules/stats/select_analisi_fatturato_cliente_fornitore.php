<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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
      $where = "tesdoc.tipdoc like 'F%' and rigdoc.quanti>0 " .
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
$statsForm = new statsForm();

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
$statsForm->selectCustomer('partner', $form['partner'], $form['search']['partner'], $form['hidden_req'], $script_transl['mesg']);
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
echo "<table class=\"Tlarge table table-striped table-bordered table-condensed table-responsive\">";
if (isset($resultFatturato)) {
   $linkHeaders = new linkHeaders($script_transl['header']);
   $linkHeaders->output();
   $totFatturato = 0;
   $totCosti = 0;
   
   //*+ DC - 14/03/2018
   // array da usare per grafici
   $emparray = array();
   //*- DC - 14/03/2018
	  
   while ($mv = gaz_dbi_fetch_array($resultFatturato)) {
      $nFatturato = $mv['imp_ven'];
      if ($nFatturato > 0) {

		 //*+ DC - 14/03/2018
		 $emparray[] = $mv;
		 //*- DC - 14/03/2018
		
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

<!--+ DC - 14/03/2018 -->
<!-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> -->
<!-- Lo script che segue è utile quando i dati vengono caricati con AJAX -->
<!--script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script-->

<br/>

<script type="text/javascript">
      
    // Load the Visualization API for corechart/piechart/bar package.
    google.charts.load('current', {'packages':['corechart']});
	google.charts.load('current', {'packages':['bar']});
	
	// Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(drawChart);
      
	function drawChart(data) {
    /*var jsonData = $.ajax({
          url: "getData.php",
          dataType: "json",
          async: false
          }).responseText;*/ // OK con getData e file_get_contents
          
	// Create our data table out of JSON data loaded from server.
    //var data = new google.visualization.DataTable(jsonData); // OK con getData e file_get_contents
      
	//var data = new google.visualization.DataTable(<?php $jsonData ?>); // non va bene sullo SQL puro restiuito e trasformato in JSON

	// -----------------------------
	// Add rows + data at the same time
	// -----------------------------
	// Pie Chart Margine %
	var data = new google.visualization.DataTable();

	// Declare columns
	data.addColumn('string', 'Fornitore');
	data.addColumn('number', 'Margine');
	// Add data.
	<?php
	if( $emparray ) {
		foreach ($emparray as $mvf)	{
			$margine = ($mvf[2] - $mvf[3]) * 100 / $mvf[2];
	?>
		data.addRows([
					 ['<?php echo $mvf[1]?>', {v:<?php echo $margine?>}]
					 ]);
	<?php
		}
	}
	?>

	var options = {
					title: 'MARGINE in % tra Fatturato/Costo per Fornitore',
					titleTextStyle: { color: '#757575',
									  fontName: 'Roboto',
									  fontSize: 14,
									  bold: false,
									},
					legend: {/*position: 'right',*/ textStyle: {color: '#757575', fontSize: 12}, alignment: 'center', /*position: 'labeled'*/},
					fontSize: 12,
					fontName: 'Roboto',
					/*fontSmoothing: 'antialiased',*/
					is3D: true,
					pieStartAngle: 7,
					pieResidueSliceLabel: 'Altro < 3%',
					sliceVisibilityThreshold: .03,
					pieSliceText: 'value',
					pieSliceTextStyle: {
										 color: 'black',
										 bold: 'true',
									   },
					tooltip: {showColorCode: true},
					/*width: $('.cols_chart').width(),
					height: $('.cols_chart').width()*/
				  };
	
	// Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('pie_chart_div'));
    chart.draw(data, options); //, {width: 400, height: 240}
	  
	// -----------------------------
	// Add rows + data at the same time
	// -----------------------------
	// Bar Chart Fatturato/Costi/Margine %
	// Declare series
	var dataBars = new google.visualization.DataTable();

	// Add legends with data type
	dataBars.addColumn('string', 'Fornitore');
	dataBars.addColumn('number', 'Fatturato');
	dataBars.addColumn('number', 'Costi');
	dataBars.addColumn('number', 'Margine %');

	// Add data.

	<?php
	if( $emparray ) {
		foreach ($emparray as $mvf)	{
			$margine = ($mvf[2] - $mvf[3]) * 100 / $mvf[2];
	?>
		dataBars.addRow(['<?php echo $mvf[1]?>', <?php echo $mvf[2]?>, <?php echo $mvf[3]?>, <?php echo $margine?>]);
	<?php
		}
	}
	?>

	var bar_options = {
						chart: {
								 title: 'Vendite per fornitore',
								 subtitle: 'Fatturato, Costi e Margine'
							   },
						legend: { position: 'right', maxLines: 2 },
						axes: {
								x: {
									 0: {side: 'bottom'}
								   }
							  },
						bars: 'horizontal',
						/*width: $('.cols_chart').width(),
						height: $('.cols_chart').width()*/
					  };
	  
		// Instantiate and draw our chart, passing in some options.
		var bar_chart = new google.charts.Bar(document.getElementById('bar_chart_div'));
		bar_chart.draw(dataBars, bar_options);
	}
	  
	window.addEventListener('resize', function () {
			drawChart();
    }/*, false*/);
</script>

<style>
.chart {
  width: 100%; 
  min-height: 450px;
  border: 1px solid #ccc;
  padding: 3px;
}
.row {
  margin:0 !important;
}
</style>
  
<!--+ not used -->
<div id="chart_area" style="width:100%">
	<!--Div that will hold the pie chart-->
	<column cols="6" class="cols_chart">
	<div id="pie_chart_divx" style="/*text-align: -webkit-center; width:35%; border: 1px solid #ccc; padding: 3px; float:right*/"></div>
    <!--Div that will hold the bar chart-->
    <div id="bar_chart_divx" style="/*text-align: -webkit-center; width:65%; border: 1px solid #ccc; padding: 3px;*/"></div>
	</column>
	<!--text-align: -webkit-center;
    display: inline;-->
</div>
<!--- not used -->

<div class="row">
  <!-- Titolo aggiuntivo (opzioneale, per ora disattivato)
  <div class="col-md-12 text-center">
    <h3>Rappresentazione grafica dati estrapolati</h3>
  </div>
  -->
  <div class="col-md-4 col-md-offset-4">
    <!--hr /-->
  </div>
  <div class="clearfix"></div>
  <div class="col-md-4">
    <div id="pie_chart_div" class="chart"></div>
  </div>
  <div class="col-md-8">
    <div id="bar_chart_div" class="chart"></div>
  </div>
</div>
<!--- DC - 14/03/2018 -->

<?php
require("../../library/include/footer.php");
?>