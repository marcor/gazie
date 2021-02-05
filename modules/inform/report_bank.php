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
    'sea_bank' => " CONCAT(".$gTables['bank']. ".codcab,' ',".$gTables['bank']. ".descricab,' ',".$gTables['bank']. ".indiri,' ',".$gTables['municipalities']. ".name) LIKE '%%%s%%'",
    'abi'  => "codabi = %d",
];
// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array  (
            "ID" => 'id',
            "ABI"=>'codabi',
            "Banca"=>'descriabi',
            "CAB"=>'codcab',
            "Sportello"=>'descricab',
            'Indirizzo' => 'indiri',
            'Comune' => 'descomune',
            'Elimina' => ''
);
$tablejoin = $gTables['bank']. " LEFT JOIN " . $gTables['municipalities'] . " ON " . $gTables['bank'] . ".id_municipalities = " . $gTables['municipalities'] . ".id";
$ts = new TableSorter(
    $tablejoin, 
    $passo, 
    ['codabi'=>'asc','codcab'=>'asc']
    );
?>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idabicab").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("bank"));
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
						data: {'type':'bank',ref:id},
						type: 'POST',
						url: './delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_bank.php");
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
	$( "#suggest_search" ).autocomplete({
		source: "./search.php?opt=suggest_search",
		minLength: 5,
        html: true, // optional (jquery.ui.autocomplete.html.js required)
      	// optional (if other layers overlap autocomplete list)
        open: function(event, ui) {
            $(".ui-autocomplete").css("z-index", 1000);
        },
		select: function(event, ui) {
			$("#suggest_search").val(ui.item.value);
			$(this).closest("form").submit();
		}
	});
});
</script>
<?php
$script_transl = HeadMain(0, array('custom/autocomplete'));
?>
<div class="text-center"><h3>Sportelli bancari</h3>
</div>
<div class="col-xs-12 text-center"><div class="col-xs-6"></div><div class="col-xs-6 text-center"><a href="./admin_bank.php" class="btn btn-success">Inserisci Nuovo</a></div>
</div>
<?php
$ts->output_navbar();
?>
<form method="GET">
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
        <p><b>Banca:</b></p>
        <p>ABI CAB:</p>
        <p class="ui-state-highlight" id="idbank"></p>
        <p>Descrizione</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
	<div class="table-responsive">
	<table class="Tlarge table table-striped table-bordered table-condensed">
	<tr>
		<td class="FacetFieldCaptionTD">
        </td>
		<td class="FacetFieldCaptionTD">
        <?php  gaz_flt_disp_select("abi", "codabi AS abi",$tablejoin, $ts->where, "codabi ASC"); ?>
		</td>
		<td class="FacetFieldCaptionTD">
        </td>
		<td class="FacetFieldCaptionTD" colspan="3">
			<input type="text" name="sea_bank" placeholder="ricerca sportello ( min.5 caratteri )"  id="suggest_search" class="input-sm form-control" value="<?php echo (isset($sea_bank))? htmlentities($sea_bank, ENT_QUOTES) : ""; ?>" maxlength="20">
        </td>
		<td class="FacetFieldCaptionTD" colspan="2">
			<input type="submit" class="btn btn-sm btn-default" name="search" value="<?php echo $script_transl['search'];?>" onClick="javascript:document.report.all.value=1;">
			<a class="btn btn-sm btn-default" href="?">Reset</a>
			<?php  $ts->output_order_form(); ?>
		</td>
	</tr>
<?php

$result = gaz_dbi_dyn_query ( $gTables['bank']. ".*, ".$gTables['municipalities']. ".name AS descomune ",$tablejoin, $ts->where, $ts->orderby, $ts->getOffset(), $ts->getLimit());
echo '<tr>';
$ts->output_headers();
echo '</tr>';
while ($r = gaz_dbi_fetch_array($result)) {
    echo "<tr>\n";
    echo '<td>
    <a class="btn btn-xs btn-default" href="./admin_bank.php?id='.$r['id'].'" ><i class="glyphicon glyphicon-edit"></i> '.$r['id'].'</a>';
    echo '</td>';
    echo '<td class="text-center">'.$r['codabi'];
    echo "</td>\n";
    echo '<td>'.$r['descriabi'];
	echo "</td>\n";
    echo '<td>'.$r['codcab'];
	echo "</td>\n";
    echo '<td>'.$r['descricab'];
	echo "</td>\n";
    echo '<td>'.$r['indiri'];
	echo "</td>\n";
    echo '<td>'.strtoupper($r['descomune']);
	echo "</td>\n";
    echo '<td class="text-center"><a class="btn btn-xs btn-default btn-elimina dialog_delete" ref="'. $r['id'].'" bankdes="'. $r['descriabi'].' '.$r['descricab'].'"> <i class="glyphicon glyphicon-remove"></i></a>';
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
