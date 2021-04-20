<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend=checkAdmin();
require("../../library/include/header.php");
// campi ammissibili per la ricerca
$search_fields = [
    'sea_codice' => "{$gTables['artico']}.codice LIKE '%%%s%%'",
	'des_artico' => "{$gTables['artico']}.descri LIKE '%%%s%%'",
    'gos' => "{$gTables['artico']}.good_or_service = %d",
	'unimis' => "{$gTables['artico']}.unimis LIKE '%%%s%%'",
    'asset' => "id_assets = %d",
    'codcat' => "{$gTables['catmer']}.codice = %d",
];

// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array  (
            "Codice" => 'codice',
            "Descrizione"=>'descri',
            "Merce<br/>Servizio" => 'good_or_service',
            "Categoria" => 'catmer',
            'U.M.' => 'unimis',
            'Prezzo vendita<br/>listino 1' => 'preve1',
            'Acquisti' => '',
            'Giacenza' => '',
            '% IVA' => 'aliiva',
            'Lotti' => '',
            'Duplica' => '',
            'Elimina' => ''
);

$tablejoin = $gTables['artico']. " LEFT JOIN " . $gTables['catmer'] . " ON " . $gTables['artico'] . ".catmer = " . $gTables['catmer'] . ".codice";

$ts = new TableSorter(
    $tablejoin, 
    $passo, 
    ['last_modified'=>'desc'],
    ['asset' => 0]);
?>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("artico"));
		var id = $(this).attr('ref');
		$( "#dialog_delete" ).dialog({
			minHeight: 1,
			width: "auto",
			modal: "true",
			show: "blind",
			hide: "explode",
			buttons: {
				delete:{ 
					text:'Elimina', 
					'class':'btn btn-danger delete-button',
					click:function (event, ui) {
					$.ajax({
						data: {'type':'artico',ref:id},
						type: 'POST',
						url: '../magazz/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_artico.php");
						}
					});
				}},
				"Non eliminare": function() {
					$(this).dialog("close");
				}
			}
		});
		$("#dialog_delete" ).dialog( "open" );  
	});
	$( "#suggest_codice_artico" ).autocomplete({
		source: "../../modules/root/search.php?opt=suggest_codice_artico",
		minLength: 3,
        html: true, // optional (jquery.ui.autocomplete.html.js required)
      	// optional (if other layers overlap autocomplete list)
        open: function(event, ui) {
            $(".ui-autocomplete").css("z-index", 1000);
        },
		select: function(event, ui) {
			$("#suggest_codice_artico").val(ui.item.value);
			$(this).closest("form").submit();
		}
	});
    
});
</script>
<?php
$script_transl = HeadMain(0, array('custom/autocomplete'));
?>
<div class="text-center"><h3><?php echo $script_transl['title'];?></h3></div>
<?php
$ts->output_navbar();

?>
<form method="GET">
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
        <p><b>articolo:</b></p>
        <p>codice:</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Descrizione</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
	<div class="table-responsive">
	<table class="Tlarge table table-striped table-bordered table-condensed">
	<tr>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="sea_codice" placeholder="codice" id="suggest_codice_artico" class="input-sm form-control" value="<?php echo (isset($sea_codice))? htmlentities($sea_codice, ENT_QUOTES) : ""; ?>" maxlength="15">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="des_artico" placeholder="descrizione"  id="suggest_descri_artico" class="input-sm form-control" value="<?php echo (isset($des_artico))? htmlentities($des_artico, ENT_QUOTES) : ""; ?>" maxlength="30">
        </td>
		<td class="FacetFieldCaptionTD">
        <?php gaz_flt_disp_select("gos", $gTables['artico'].".good_or_service AS gos", $tablejoin, 1,'good_or_service ASC', $script_transl['good_or_service_value']); ?>
        </td>
		<td class="FacetFieldCaptionTD">
        <?php gaz_flt_disp_select("codcat", $gTables['catmer'].".codice AS codcat, ". $gTables['catmer'].".descri AS descat", $tablejoin, 1,'codcat ASC','descat'); ?>
        </td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="unimis" placeholder="U.M." class="input-sm form-control" value="<?php echo (isset($unimis))? $unimis : ""; ?>" maxlength="3">
        </td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD" colspan="7">
			<input type="submit" class="btn btn-sm btn-default" name="search" value="<?php echo $script_transl['search'];?>" onClick="javascript:document.report.all.value=1;">
			<a class="btn btn-sm btn-default" href="?">Reset</a>
			<?php  $ts->output_order_form(); ?>
		</td>
	</tr>

<?php
$gForm = new magazzForm();

$result = gaz_dbi_dyn_query ( $gTables['artico']. ".*, ".$gTables['catmer']. ".descri AS descat, ".$gTables['catmer']. ".codice AS codcat",$tablejoin, $ts->where, $ts->orderby, $ts->getOffset(), $ts->getLimit());

echo '<tr>';
$ts->output_headers();
echo '</tr>';
while ($r = gaz_dbi_fetch_array($result)) {
  // da configurazione azienda
	$show_artico_composit = gaz_dbi_get_row($gTables['company_config'], 'var', 'show_artico_composit');
	$tipo_composti = gaz_dbi_get_row($gTables['company_config'], 'var', 'tipo_composti');
  // acquisti

    // giacenza
    $mv = $gForm->getStockValue(false, $r['codice']);
    $magval = array_pop($mv);
	 if (isset($magval['q_g']) && round($magval['q_g'],6) == "-0"){
		 $magval['q_g']=0;
	 }
	$class = 'success';
    if (is_numeric($magval)) { // giacenza = 0
        $class = 'danger';
        $magval=[];
        $magval['q_g']=0;
    } elseif ($magval['q_g'] < 0) { // giacenza inferiore a 0
        $class = 'danger';
    } elseif ($magval['q_g'] > 0) { //
      if ($magval['q_g']<=$r['scorta']){
        $class = 'warning';
      }
    } else { // giacenza = 0
        $class = 'danger';
    }
    // contabilizzazione magazzino
    $com = '';
    if ($admin_aziend['conmag'] > 0 && $r["good_or_service"] != 1 && $tipo_composti['val']=="STD") {
        $com = '<a class="btn btn-xs btn-'.$class.'" href="../magazz/select_schart.php?di=0101' . date('Y') . '&df=' . date('dmY') . '&id=' . $r['codice'] . '" target="_blank">
	  <i class="glyphicon glyphicon-check"></i><i class="glyphicon glyphicon-print"></i>
	  </a>';
    }
    // IVA
    $iva = gaz_dbi_get_row($gTables['aliiva'], 'codice', $r['aliiva']);
    echo "<tr>\n";
    echo '<td>
    <a class="btn btn-xs btn-'.$class.'" href="../magazz/admin_artico.php?Update&codice='.$r['codice'].'" ><i class="glyphicon glyphicon-edit"></i> '.$r['codice'].'</a>';
    if ( $r["good_or_service"] == 2 ) {
        echo '<a class="btn btn-xs btn-default" href="../magazz/admin_artico_compost.php?Update&codice='.$r['codice'].'" ><i class="glyphicon glyphicon-plus"></i></a>';
        $des_bom ='<a target="_blank" title="Stampa l\'albero della distinta base" class="btn btn-xs btn-info" href="stampa_bom.php?ri=' . $r["codice"] . '"><i class="glyphicon glyphicon-tasks"> <b>'.$script_transl['good_or_service_value'][$r['good_or_service']].'</b></i></a>';
    } else {
        $des_bom = $script_transl['good_or_service_value'][$r['good_or_service']];
    }
    echo '</td>';
    echo '<td><span class="gazie-tooltip" data-type="product-thumb" data-id="'. $r['codice'] .'" data-title="'. $r['annota'].'" >'.$r['descri'].'</span>';
    echo "</td>\n";
    echo '<td class="text-center">'.$des_bom;
    echo "</td>\n";
    echo '<td class="text-center">'.$r['catmer'].'-'.$r['descat'];
    echo "</td>\n";
    echo '<td class="text-center">'.$r['unimis'];
	echo "</td>\n";
    echo '<td class="text-right">'.number_format($r['preve1'], $admin_aziend['decimal_price'], ',', '.');
	echo "</td>\n";
    echo '<td class="text-right">'.number_format($r['preacq'], $admin_aziend['decimal_price'], ',', '.');
	echo "</td>\n";
    echo '<td class="text-right">'.gaz_format_quantity(floatval($magval['q_g']),1,$admin_aziend['decimal_quantity']).' '.$com;
	echo "</td>\n";
    echo '<td class="text-center">'.floatval($iva['aliquo']);
	echo "</td>\n";
    echo '<td class="text-center">';
	if (intval($r['lot_or_serial'])>0) {
		$classcol=(intval($r['lot_or_serial'])==1)?'btn-info':'btn-success';
		$lor=(intval($r['lot_or_serial'])==1)?'Lot':'Ser';
	?>
	<a class="btn <?php echo $classcol; ?> btn-xs" href="javascript:;" onclick="window.open('<?php echo "../../modules/magazz/mostra_lotti.php?codice=".$r['codice'];?>', 'titolo', 'menubar=no, toolbar=no, width=800, height=400, left=80%, top=80%, resizable, status, scrollbars=1, location');"><?php echo $lor; ?> <i class="glyphicon glyphicon-tag"></i></a>
	<?php 
    }	
    echo "</td>\n";
    echo '<td class="text-center"><a class="btn btn-xs btn-default" href="clone_artico.php?codice='.$r["codice"].'"> <i class="glyphicon glyphicon-export"></i></a>';
	echo "</td>\n";
    echo '<td class="text-center"><a class="btn btn-xs btn-default btn-elimina dialog_delete" ref="'. $r['codice'].'" artico="'. $r['descri'].'"> <i class="glyphicon glyphicon-remove"></i></a>';
	echo "</td>\n";
    echo "</tr>\n";
}
?>
     </table>
	</div>
</form>
<?php
require("../../library/include/footer.php");
?>
