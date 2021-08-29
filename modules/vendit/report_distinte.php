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
$script_transl = HeadMain();

// campi ammissibili per la ricerca
$search_fields = [
    'id_doc' => $gTables['files'].".id_doc = %d",
    'anno' => "YEAR(".$gTables['files'].".last_modified) = %d"
];

// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array  (
            "ID" => 'id_doc',
            $script_transl['date'] => 'title',
            $script_transl['desbanacc'] => 'banacc',
            $script_transl['delete'] => ""
);

echo "<div align='center' class='FacetFormHeaderFont '>{$script_transl['title']}</div>\n";
$table = $gTables['effett']." LEFT JOIN ".$gTables['files']." ON (".$gTables['effett'].".id_distinta = ".$gTables['files'].".id_doc)
		 LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['effett'].".banacc = ".$gTables['clfoco'].".codice)";
 
$t = new TableSorter(
    $table, 
    $passo, 
    ['id_doc' => 'desc'],
    ['item_ref'=>'distinta'],
    ['id_distinta'],
    " table_name_ref='effett'");
$t->output_navbar();

?>
<script>
$(function() {
    $("#datareg").datepicker({ dateFormat: 'yy-mm-dd',showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("movdes"));
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
						data: {'type':'files',ref:id},
						type: 'POST',
						url: './delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_files.php");
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
});
</script>
<form method="GET">
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
		<p><b>Distinta effetti</b></p>
		<p>Codice</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Descrizione</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
	<div class="table-responsive">
	<table class="Tlarge table table-striped table-bordered table-condensed">
	<tr>
        <td class="FacetFieldCaptionTD">
            <?php gaz_flt_disp_int("id_doc", "ID"); ?>
        </td>
        <td class="FacetFieldCaptionTD">
            <?php gaz_flt_disp_select("anno", "YEAR(".$gTables['files'].".last_modified) AS anno", $table, $t->where, "anno DESC"); ?>
        </td>
		<td class="FacetFieldCaptionTD" colspan="3">
			<input type="submit" class="btn btn-xs btn-default" name="search" value="<?php echo $script_transl['search'];?>" tabindex="1" onClick="javascript:document.report.all.value=1;">
			<a class="btn btn-xs btn-default" href="?">Reset</a>
			<?php  $t->output_order_form(); ?>
		</td>
	</tr>

<?php
$rs=gaz_dbi_dyn_query ($gTables['files'].".*, ".$gTables['clfoco'].".descri AS desbanacc", $table, $t->where." ".$t->group_by, $t->orderby, $t->getOffset(), $t->getLimit());

echo '<tr>';
$t->output_headers();
echo '</tr>';
while ($r = gaz_dbi_fetch_array($rs)) {
    print_r($r);
}
?>
     </table>
	</div>
</form>
<?php
require("../../library/include/footer.php");
?>
