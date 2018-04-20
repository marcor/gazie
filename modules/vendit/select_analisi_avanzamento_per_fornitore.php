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

//*+ DC - 19/04/2018
// Nuova analisi statistica:
// Viene calcolato l'avanzamento delle vendite (in %) rispetto all'acquistato raggruppato per fornitore abituale presente su articolo
// L'analisi parte dai movimenti di magazzino (movmag) ed estrae per periodo (distinto tra acquistato e venduto) il totale acquistato/venduto
// I dati vengono raggruppati per fornitore impostato su anagrafica articolo
// Risulta quindi indispensabile la corretta imputazione del codice fornitore sull'anagrafica articolo
// Questa analisi mi fa capire tra i fornitori che tratto quelli che sono più remunerativi nei periodi indicati dandomi la possibilità di
// valutare quali fornitori tenere e quali escludere per il rifornimento di merce (settore abbigliamento al dettaglio)
//*- DC - 19/04/2018

require("../../library/include/datlib.inc.php");

$admin_aziend = checkAdmin();

$msg = ''; // anche se non sono previste situazioni di errori da gestire (lascio per uso futuro)

if (!isset($_POST['ritorno'])) { //al primo accesso allo script
   $msg = '';
   $form['ritorno'] = $_SERVER['HTTP_REFERER'];
   
   // Data inizio / fine per vendite
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
   
   // Data inizio / fine per acquisti
   if (isset($_POST['datiniA'])) {
      $form['giA'] = substr($_POST['datiniA'], 6, 2);
      $form['miA'] = substr($_POST['datiniA'], 4, 2);
      $form['aiA'] = substr($_POST['datiniA'], 0, 4);
   } else {
      $form['giA'] = 1;
      $form['miA'] = 1;
      $form['aiA'] = date("Y");
   }
   if (isset($_POST['datfinA'])) {
      $form['gfA'] = substr($_POST['datfinA'], 6, 2);
      $form['mfA'] = substr($_POST['datfinA'], 4, 2);
      $form['afA'] = substr($_POST['datfinA'], 0, 4);
   } else {
      $form['gfA'] = date("d");
      $form['mfA'] = date("m");
      $form['afA'] = date("Y");
   }
   
   unset($resultAnalisi);
   $form['hidden_req'] = '';
} else { // le richieste successive
   // Data inizio / fine per vendite
   $form['ritorno'] = $_POST['ritorno'];
   $form['gi'] = intval($_POST['gi']);
   $form['mi'] = intval($_POST['mi']);
   $form['ai'] = intval($_POST['ai']);
   $form['gf'] = intval($_POST['gf']);
   $form['mf'] = intval($_POST['mf']);
   $form['af'] = intval($_POST['af']);
   // Data inizio / fine per acquisti
   $form['giA'] = intval($_POST['giA']);
   $form['miA'] = intval($_POST['miA']);
   $form['aiA'] = intval($_POST['aiA']);
   $form['gfA'] = intval($_POST['gfA']);
   $form['mfA'] = intval($_POST['mfA']);
   $form['afA'] = intval($_POST['afA']);
   
   $form['hidden_req'] = $_POST['hidden_req'];
}


if (isset($_POST['preview'])) {
   // controllo situazioni di errore // per ora nessuna prevista
   if (empty($msg)) { //non ci sono errori
	  // Data inizio / fine per vendite
      $datini = sprintf("%04d%02d%02d", $form['ai'], $form['mi'], $form['gi']);
      $datfin = sprintf("%04d%02d%02d", $form['af'], $form['mf'], $form['gf']);
	  // Data inizio / fine per acquisti
      $datiniA = sprintf("%04d%02d%02d", $form['aiA'], $form['miA'], $form['giA']);
      $datfinA = sprintf("%04d%02d%02d", $form['afA'], $form['mfA'], $form['gfA']);
	  
	  $what = "fornitori.codice as codice_fornitore, dati_fornitori.ragso1 as nome_fornitore, 
sum(CASE
                WHEN (movmag.datreg between '$datini' and '$datfin' and movmag.tipdoc='FAI') THEN movmag.quanti*movmag.prezzo*(1-movmag.scorig/100) 
		        WHEN (movmag.datreg between '$datini' and '$datfin' and movmag.tipdoc='FNC') THEN (-1)*movmag.quanti*movmag.prezzo*(1-movmag.scorig/100) 
				ELSE 0 END) as totValVen,
sum(CASE
                WHEN (movmag.datreg between '$datiniA' and '$datfinA' and movmag.tipdoc='AFA') THEN movmag.quanti*movmag.prezzo*(1-movmag.scorig/100) 
		        WHEN (movmag.datreg between '$datiniA' and '$datfinA' and movmag.tipdoc='AFC') THEN (-1)*movmag.quanti*movmag.prezzo*(1-movmag.scorig/100)
				ELSE 0 END) as totValAcq";
      
	  $tab_movmag = $gTables['movmag'];
      $tab_artico = $gTables['artico'];
      $tab_anagra = $gTables['anagra'];
      $tab_clfoco = $gTables['clfoco'];
      
	  $table = "$tab_movmag movmag 
left join $tab_artico artico on artico.codice=movmag.artico 
left join $tab_clfoco fornitori on artico.clfoco=fornitori.codice 
left join $tab_anagra dati_fornitori on fornitori.id_anagra=dati_fornitori.id";
      
	  $where = "artico.clfoco>0 and movmag.quanti<>0 ";
      $order = "nome_fornitore, codice_fornitore";
      $group = "fornitori.codice"; // artico.clfoco
      $resultAnalisi = gaz_dbi_dyn_query($what, $table, $where, $order, 0, 20000, $group);
   }
}

if (isset($_POST['Return'])) {
   header("Location:../root/docume_root.php"); // richiamato script 'help' di base di GAzie
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

// Data inizio / fine per vendite
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
// select dell'anno
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
// select dell'anno
echo "\t <select name=\"af\" class=\"FacetSelect\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
   $selected = "";
   if ($counter == $form['af'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "</td></tr>";

// Data inizio / fine per acquisti
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[2]</td>";
echo "<td class=\"FacetDataTD\">";
// select del giorno
echo "\t <select name=\"giA\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
   $selected = "";
   if ($counter == $form['giA'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
}
echo "\t </select>\n";
// select del mese
echo "\t <select name=\"miA\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
   $selected = "";
   if ($counter == $form['miA'])
      $selected = "selected";
   $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
   echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
// select dell'anno
echo "\t <select name=\"aiA\" class=\"FacetSelect\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
   $selected = "";
   if ($counter == $form['aiA'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}

echo "\t </select>\n";
echo "</td></tr>";
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[3]</td>";
echo "<td class=\"FacetDataTD\">";
// select del giorno
echo "\t <select name=\"gfA\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
   $selected = "";
   if ($counter == $form['gfA'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
}
echo "\t </select>\n";
// select del mese
echo "\t <select name=\"mfA\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
   $selected = "";
   if ($counter == $form['mfA'])
      $selected = "selected";
   $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
   echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
// select dell'anno
echo "\t <select name=\"afA\" class=\"FacetSelect\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
   $selected = "";
   if ($counter == $form['afA'])
      $selected = "selected";
   echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "</td></tr>";

echo "<tr>\n
     <td class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"Return\" value=\"" . ucfirst($script_transl['return']) . "\"></td>\n
     <td align=\"right\" class=\"FacetFooterTD\"><input type=\"submit\" accesskey=\"i\" name=\"preview\" value=\"" . ucfirst($script_transl['preview']) . "\"></td>\n
     </tr>\n</table>";
echo "<table class=\"Tlarge table table-striped table-bordered table-condensed table-responsive\">";
if (isset($resultAnalisi)) {
   $linkHeaders = new linkHeaders($script_transl['header']);
   $linkHeaders->output();
   $totFatturato = 0;
   $totCosti = 0;
   
   // array da usare per grafici
   $GCarray = array();
	  
   while ($mv = gaz_dbi_fetch_array($resultAnalisi)) {
      $nAcquistato = $mv['totValAcq'];
      if ($nAcquistato > 0) {

		 $GCarray[] = $mv;
		
         $nVenduto = $mv['totValVen'];
         $avanzamento = ($nVenduto*100) / $nAcquistato;
         $totFatturato+=$nVenduto;
         $totCosti+=$nAcquistato;
         echo "<tr>";
         echo "<td class=\"FacetFieldCaptionTD\">" . substr($mv[0], 3) . " &nbsp;</td>";
         echo "<td align=\"left\" class=\"FacetDataTD\">" . $mv[1] . " &nbsp;</td>";
         echo "<td align=\"right\" class=\"FacetDataTD\">" . gaz_format_number($nAcquistato) . " &nbsp;</td>";
         echo "<td align=\"right\" class=\"FacetDataTD\">" . gaz_format_number($nVenduto) . " &nbsp;</td>";
         echo "<td align=\"right\" class=\"FacetDataTD\">" . gaz_format_number($avanzamento) . " &nbsp;</td>";
         echo "</tr>";
      }
   }
   
   $avanzamento = ($totCosti > 0 ? ($totFatturato*100) / $totCosti : 0);
   
   echo "<tr>";
   echo "<td class=\"FacetFieldCaptionTD\"> &nbsp;</td>";
   echo "<td align=\"left\" class=\"FacetDataTD\"><B>" . $script_transl['totale'] . "</B> &nbsp;</td>";
   echo "<td align=\"right\" class=\"FacetDataTD\"><B>" . gaz_format_number($totCosti) . "</B> &nbsp;</td>";
   echo "<td align=\"right\" class=\"FacetDataTD\"><B>" . gaz_format_number($totFatturato) . "</B> &nbsp;</td>";
   echo "<td align=\"right\" class=\"FacetDataTD\"><B>" . gaz_format_number($avanzamento) . "</B> &nbsp;</td>";
   echo "</tr>";
   echo '<tr class="FacetFieldCaptionTD">
	 			<td colspan="12" align="right"><input type="button" name="print" onclick="window.print();" value="' . $script_transl['print'] . '"></td>
	 	  </tr>';
}
?>
</table>
</form>

<!--+ Google Chart JS -->
<!-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> -->
<!-- Lo script che segue è utile quando i dati vengono caricati con AJAX -->
<!--script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script-->
<!--- Google Chart JS -->

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
	// Pie Chart Avanzamento %
	var data = new google.visualization.DataTable();

	// Declare columns
	data.addColumn('string', 'Fornitore');
	data.addColumn('number', 'Avanzamento');
	// Add data.
	<?php
	if( $GCarray ) {
		foreach ($GCarray as $mvf)	{
			$avanzamento = ($mvf[3] > 0 ? ($mvf[2]*100) / $mvf[3] : 0);
	?>
		data.addRows([
					 ['<?php echo $mvf[1]?>', {v:<?php echo $avanzamento?>}]
					 ]);
	<?php
		}
	}
	?>

	var options = {
					title: 'Avanzamento in % del Venduto su Acquistato (reale) per Fornitore',
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
	// Bar Chart Acquistato/Venduto/Avanzamento %
	// Declare series
	var dataBars = new google.visualization.DataTable();

	// Add legends with data type
	dataBars.addColumn('string', 'Fornitore');
	dataBars.addColumn('number', 'Acquistato');
	dataBars.addColumn('number', 'Venduto');
	dataBars.addColumn('number', 'Avanzamento %');

	// Add data.

	<?php
	if( $GCarray ) {
		foreach ($GCarray as $mvf)	{
			$avanzamento = ($mvf[3] > 0 ? ($mvf[2]*100) / $mvf[3] : 0);
	?>
		dataBars.addRow(['<?php echo $mvf[1]?>', <?php echo $mvf[3]?>, <?php echo $mvf[2]?>, <?php echo $avanzamento?>]);
	<?php
		}
	}
	?>

	var bar_options = {
						chart: {
								 title: 'Avanzamento venduto su acquistato per fornitore',
								 subtitle: 'Acquistato, Venduto ed Avanzamento (reali)'
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

<?php
require("../../library/include/footer.php");
?>