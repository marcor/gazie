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
$admin_aziend=checkAdmin();
$msg = "";
require("../../library/include/header.php");
$script_transl = HeadMain();
require("lang.".$admin_aziend['lang'].".php");

// campi ammissibili per la ricerca
$search_fields = [
	'movimento'
        => "{$gTables['movmag']}.id_mov = %d",
	'causale'
        => "caumag LIKE '%s%%'",
	'documento'
        => "desdoc LIKE '%%%s%%'",
	'articolo'
        => "artico LIKE '%%%s%%'",
	'lotto'
        => "id_lotmag LIKE '%%%s%%'"
];

// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array  (
            "n.ID" => 'id_mov',
            $script_transl[4] => 'datreg',
            $strScript["admin_movmag.php"][2] => 'caumag',
            $script_transl[8] => "",
            $script_transl[5] => 'artico',
            $script_transl[11] => 'identifier',
            $script_transl[6] => "",
            $script_transl[7] => "",
            $script_transl['delete'] => ""
);

echo "<div align='center' class='FacetFormHeaderFont '>{$script_transl[3]}{$script_transl[0]}</div>\n";
 
$t = new TableSorter($gTables['movmag'], $passo, ['id_mov' => 'desc']);
$t->output_navbar();

?>
<script>
$(function() {
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
						data: {'type':'movmag',ref:id},
						type: 'POST',
						url: '../magazz/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_movmag.php");
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
		<p><b>movimento magazzino:</b></p>
		<p>Codice</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Descrizione</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
	<div class="table-responsive">
	<table class="Tlarge table table-striped table-bordered table-condensed">
	<tr>
		<td class="FacetFieldCaptionTD">
		  <input type="text" name="movimento" placeholder="Movimento" class="input-sm form-control"  value="<?php echo (isset($movimento))? $movimento : ""; ?>" maxlength ="6" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="causale" placeholder="<?php echo $strScript['admin_movmag.php'][2];?>" class="input-sm form-control" value="<?php echo (isset($causale))? $causale : ""; ?>" maxlength="6" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="documento" placeholder="<?php echo $script_transl[8];?>" class="input-sm form-control" value="<?php echo (isset($documento))? $documento : ""; ?>" maxlength="15" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="articolo" placeholder="<?php echo $script_transl[5];?>" class="input-sm form-control" value="<?php echo (isset($articolo))? $articolo : ""; ?>" maxlength="15" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="lotto" placeholder="<?php echo "ID ",$script_transl[11];?>" class="input-sm form-control" value="<?php echo (isset($lotto))? $lotto : ""; ?>" maxlength="15" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD" colspan="3">
			<input type="submit" class="btn btn-xs btn-default" name="search" value="<?php echo $script_transl['search'];?>" tabindex="1" onClick="javascript:document.report.all.value=1;">
			<a class="btn btn-xs btn-default" href="?">Reset</a>
			<?php  $t->output_order_form(); ?>
		</td>
	</tr>

<?php
$table = $gTables['movmag']." LEFT JOIN ".$gTables['caumag']." on (".$gTables['movmag'].".caumag = ".$gTables['caumag'].".codice)
         LEFT JOIN ".$gTables['artico']." ON (".$gTables['movmag'].".artico = ".$gTables['artico'].".codice)
		 LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['movmag'].".clfoco = ".$gTables['clfoco'].".codice)
         LEFT JOIN ".$gTables['rigdoc']." ON (".$gTables['movmag'].".id_rif = ".$gTables['rigdoc'].".id_rig)
		 LEFT JOIN ".$gTables['lotmag']." ON (".$gTables['movmag'].".id_lotmag = ".$gTables['lotmag'].".id)";
		/* Antonio Germani - momentaneamente commentato, di comune accordo con Antonio de Vincentiis, perché causa un ambiguous column names con id_lotmag quando si utilizza l'ID lotto come filtro
		LEFT JOIN ".$gTables['orderman']." ON (".$gTables['movmag'].".id_orderman = ".$gTables['orderman'].".id)
		*/
$result = gaz_dbi_dyn_query ($gTables['movmag'].".*, ".$gTables['artico'].".descri AS descart, ".$gTables['caumag'].".descri AS descau, ".$gTables['lotmag'].".*, ".$gTables['rigdoc'].".id_tes AS testata", $table, $t->where, $t->orderby, $t->getOffset(), $t->getLimit());

echo '<tr>';
$t->output_headers();
echo '</tr>';
$anagrafica = new Anagrafica();

/** ENRICO FEDELE */
/* Inizializzo la variabile */
$tot_movimenti = 0;
/** ENRICO FEDELE */

while ($a_row = gaz_dbi_fetch_array($result)) {
    $partner = $anagrafica->getPartner($a_row['clfoco']);
    $title =  $partner['ragso1']." ".$partner['ragso2'];
	$descri=$a_row['descart'];
	if ($a_row['expiry']>0){
		$expiry="Scad.: ".gaz_format_date($a_row['expiry']);
	} else {
		$expiry="";
	}
    $valore = CalcolaImportoRigo($a_row['quanti'], $a_row['prezzo'], $a_row['scorig']) ;
    $valore = CalcolaImportoRigo(1, $valore, $a_row['scochi']) ;
    echo "<tr>\n";
	
    echo "<td class=\"FacetDataTD\">";
	if ($a_row['tipdoc'] == "MAG"){
		echo "<a class=\"btn btn-xs btn-default\" href=\"admin_movmag.php?id_mov=".$a_row["id_mov"]."&Update\" title=\"".ucfirst($script_transl['update'])."!\"><i class=\"glyphicon glyphicon-edit text-success\"></i>&nbsp;".$a_row["id_mov"]."</a> &nbsp</td>";
    } else {
		echo "<a class=\"btn btn-xs btn-default\" title=\"Questo movimento puo essere modificato solo nel documento che lo ha creato\"><i class=\"glyphicon glyphicon-ban-circle text-danger\"></i>&nbsp;".$a_row["id_mov"]."</a> &nbsp</td>";
	}
	echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($a_row["datreg"])." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["caumag"]." - ".$a_row["descau"]."</td>\n";
    if ($a_row['id_rif'] == 0) {
		if ($a_row['id_orderman']>0){
			echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../orderman/admin_orderman.php?Update&codice=".$a_row['id_orderman']."\">".$a_row['descau']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])." - ID: ".$a_row['id_orderman']."</a></td>\n";
		} else {
			echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</td>\n";
		}
    } else if ($a_row['tipdoc'] == "ADT"
         || $a_row['tipdoc'] == "AFA"
         || $a_row['tipdoc'] == "AFC") {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../acquis/admin_docacq.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
	} else if ($a_row['tipdoc'] == "CAM"){
		echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../camp/admin_movmag.php?id_mov=".$a_row['id_rif']."&Update\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";

	} else {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../vendit/admin_docven.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
    }
    
   	echo "<td class=\"FacetDataTD\"  align=\"center\"><p data-toggle=\"tooltip\" data-placement=\"auto\" title=\"$descri\">".$a_row["artico"]."</p></td>\n";
	if ($a_row['id']>0) {
		echo "<td class=\"FacetDataTD\" align=\"center\"><p data-toggle=\"tooltip\" data-placement=\"auto\" title=\"$expiry\">"."ID:".$a_row['id']." - ".$a_row['identifier']."</td>\n";
	} else {
		echo "<td class=\"FacetDataTD\"></td>";
	}
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_quantity($a_row["quanti"],1,$admin_aziend['decimal_quantity'])."</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($valore)." </td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">\n";
	if ($a_row['tipdoc'] == "MAG"){
		?>
		<a class="btn btn-xs btn-default btn-elimina dialog_delete" title="Elimina movimento" ref="<?php echo $a_row['id_mov'];?>" movdes="<?php echo $a_row['descau']; ?>">
		<i class="glyphicon glyphicon-remove"></i>
		</a>
		<?php
	} else {
		?>
		<a class="btn btn-xs btn-default btn-elimina" title="Questo movimento puo essere eliminato solo dal documento che lo ha creato">
		<i class="glyphicon glyphicon-ban-circle"></i>
		</a>
		<?php
	}
	echo "</td>\n";
    echo "</tr>\n";
	/** ENRICO FEDELE */
	/* Incremento il totale */
	$tot_movimenti += $valore;
	/** ENRICO FEDELE */
}
	/** ENRICO FEDELE */
	/* Stampo il totale */
	//if($tot_movimenti!=0) {	//	Inizialmente avevo pensato di stampare il totale solo se diverso da zero, ma la cosa risulta fuorviante in alcuni casi
								//	meglio stamparlo sempre
		echo "<tr>
				<td colspan=\"7\" class=\"FacetFieldCaptionTD\" align=\"right\"><strong>TOTALE</strong></td>
				<td class=\"FacetFieldCaptionTD\" align=\"right\"><strong>".gaz_format_number($tot_movimenti)."</strong></td>
				<td class=\"FacetFieldCaptionTD\">&nbsp;</td>
			  </tr>";
	//}
	/** ENRICO FEDELE */
?>
     </table>
	</div>
</form>
<script>
$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip();

	// Remove empty fields from GET forms
	// URL: http://www.billerickson.net/code/hide-empty-fields-get-form/

	$("form").submit(function() {
		$(this).find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
		return true; // ensure form still submits
	});

	// Un-disable form fields when page loads, in case they click back after submission
	$( "form" ).find( ":input" ).prop( "disabled", false );
});
</script>
<?php
require("../../library/include/footer.php");
?>
