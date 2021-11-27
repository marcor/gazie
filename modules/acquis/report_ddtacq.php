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
$admin_aziend = checkAdmin();

$tipdoc=array('DDL', 'RDL', 'DDR','ADT', 'AFT');

$partner_select = !gaz_dbi_get_row($gTables['company_config'], 'var', 'partner_select_mode')['val'];
$pdf_to_modal = gaz_dbi_get_row($gTables['company_config'], 'var', 'pdf_reports_send_to_modal')['val'];

$tesdoc_e_partners = $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id';

function print_querytime($prev) {
    list($usec, $sec) = explode(" ", microtime());
    $this_time = ((float) $usec + (float) $sec);
    echo round($this_time - $prev, 3);
    return $this_time;
}

// funzione di utilità generale, adatta a mysqli.inc.php
function cols_from($table_name, ...$col_names) {
    $full_names = array_map(function ($col_name) use ($table_name) { return "$table_name.$col_name"; }, $col_names);
    return implode(", ", $full_names);
}

// campi ammissibili per la ricerca
$search_fields = [
    'sezione' => "seziva = %d",
    'numdoc'  => "numdoc = %d",
    'tipo'    => "tipdoc LIKE '%s'",
    'numero'  => "numfat LIKE '%%%s%%'",
    'anno'    => "YEAR(datemi) = %d",
    'fornitore'=> $partner_select ? "clfoco = '%s'" : "ragso1 LIKE '%%%s%%'"
];

// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array(
    "ID" => "id_tes",
    "Tipo" => "tipdoc",
    "Numero" => "numdoc",
    "Data" => "datemi",
    "Fornitore" => "",
    "Status" => "",
    "Stampa" => "",
    "Cancella" => ""
);

require("../../library/include/header.php");
$script_transl = HeadMain();

$ts = new TableSorter(
    !$partner_select && isset($_GET["fornitore"]) ? $tesdoc_e_partners : $gTables['tesdoc'],
    $passo, ['id_tes' => 'desc'], ['sezione'=>1],[], " (tipdoc = 'DDL' OR tipdoc = 'RDL' OR tipdoc = 'DDR' OR tipdoc = 'ADT' OR tipdoc = 'AFT')"
);

?>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("catdes"));
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
						data: {'type':'docacq',id_tes:id},
						type: 'POST',
						url: '../acquis/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_ddtacq.php");
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
function printPdf(urlPrintDoc){
	$(function(){
        var ctrlmodal = urlPrintDoc.match(/modal=0$/);
        if (ctrlmodal){
            window.open(urlPrintDoc, "_blank");
        } else {
            $('#framePdf').attr('src',urlPrintDoc);
            $('#framePdf').css({'height': '100%'});
            $('.framePdf').css({'display': 'block','width': '90%', 'height': '80%', 'z-index':'2000'});
       		$('#closePdf').on( "click", function() {
                $('.framePdf').css({'display': 'none'});
            });
        }
	});
};
</script>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>"  name="auxil">
	<div class="framePdf panel panel-success" style="display: none; position: absolute; left: 5%; top: 100px">
		<div class="col-lg-12">
			<div class="col-xs-11"><h4><?php echo $script_transl['print'];; ?></h4></div>
			<div class="col-xs-1"><h4><button type="button" id="closePdf"><i class="glyphicon glyphicon-remove"></i></button></h4></div>
		</div>
		<iframe id="framePdf"  style="height: 100%; width: 100%" src=""></iframe>
	</div>
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
        <p><b>documento di trasporto:</b></p>
        <p>ID:</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Fornitore</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
    <div align="center" class="FacetFormHeaderFont">D.d.T. acquisti della sezione
        <select name="sezione" class="FacetSelect" onchange="this.form.submit()">
	    <?php
            for ($i = 1; $i <= 9; $i++) {
                $selected = ($sezione == $i) ? "selected" : "";
                echo "<option value='$i' $selected > $i </option>\n";
            }
	    ?>

        </select>
    </div>
	<?php
        list ($usec, $sec) = explode(' ', microtime());
        $querytime = ((float) $usec + (float) $sec);
        $querytime_before = $querytime;
        $ts->output_navbar();
	?>
	<div class="table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
        <tr>
            <td class="FacetFieldCaptionTD">
            </td>
            <td class="FacetFieldCaptionTD">
                <?php  gaz_flt_disp_select("tipo", "tipdoc as tipo", $tesdoc_e_partners, $ts->where, "tipdoc ASC"); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("numdoc", "Numero"); ?>
            </td>
            <td  class="FacetFieldCaptionTD">
                <?php  gaz_flt_disp_select("anno", "YEAR(datemi) as anno", $tesdoc_e_partners, $ts->where, "anno DESC"); ?>
            </td>
            <td  class="FacetFieldCaptionTD">
		    <?php
                    if ($partner_select) {
                        gaz_flt_disp_select("fornitore", "clfoco AS fornitore, ragso1 as nome",
					    $tesdoc_e_partners,
					    $ts->where, "nome ASC", "nome");
                    } else {
                        gaz_flt_disp_int("fornitore", "Fornitore");
                    }
		    ?>

            </td>
            <td  class="FacetFieldCaptionTD">
            </td>
            <td  class="FacetFieldCaptionTD">
            </td>
            <td  class="FacetFieldCaptionTD">
			<input type="submit" class="btn btn-sm btn-default" name="search" value="<?php echo $script_transl['search'];?>" onClick="javascript:document.report.all.value=1;">
			<a class="btn btn-sm btn-default" href="?">Reset</a>
			<?php  $ts->output_order_form(); ?>
            </td>
        </tr>
        <tr>
            <?php
// creo l'array (header => campi) per l'ordinamento dei record
            $headers_tesdoc = array(
                "ID" => "id_tes",
                "Tipo" => "tipdoc",
                "Numero" => "numdoc",
                "Data" => "datemi",
                "Fornitore (cod.)" => "clfoco",
                "Status" => "",
                "Stampa" => "",
                "Cancella" => ""
            );
            ?>
        </tr>
		            <tr>
                <?php
                $ts->output_headers();
                ?>
            </tr>

        <?php

        $result = gaz_dbi_dyn_query(cols_from($gTables['tesdoc'],
						  "id_tes","tipdoc","ddt_type","seziva","datemi","numdoc","numfat","datfat","status") . ", " .
					cols_from($gTables['anagra'],
						  "fe_cod_univoco",
						  "pec_email",
						  "ragso1",
						  "ragso2",
						  "e_mail")
					,$tesdoc_e_partners,
					$ts->where,
					$ts->orderby,
                    $ts->getOffset(),
					$ts->getLimit());

        while ($r = gaz_dbi_fetch_array($result)) {
			// controllo ogni rigo se è ultimo movimento per quel tipdoc
            $ddtanomalo=($r['status']=='DdtAnomalo')?'<small class="text-warning" title="Il DdT è stato generato da una fattura elettronica con riferimenti ai righi errati o mancanti"> &nbsp; (<sup>*</sup>) &nbsp; </small>':'';
			$order='id_tes DESC';
			if  (substr($r['tipdoc'],0,2) == 'DD') {
				$where = "tipdoc LIKE 'DD_' AND seziva = ".$r['seziva']." AND numfat = 0" ;
				$order='numdoc DESC';
				$title="Modifica documento";
			} elseif  (substr($r['tipdoc'],0,2) == 'AF'){ // fattura o nota credito fornitore
				$where = "tipdoc LIKE 'AF_' AND seziva = ".$r['seziva']." AND YEAR(datreg) = '".substr($r['datfat'],0,4)."'";
				$order='protoc DESC';
				if ($r['ddt_type']=="T" OR $r['ddt_type']=="L"){
					//$update="disabled";
				}
				$title="Cancellare la fattura per modificare il DDT";
			} elseif  (substr($r['tipdoc'],0,2) == 'AD'){
				$where = "tipdoc LIKE 'AD_'";
				$order='id_tes DESC';
				$title="Modifica documento";
			} elseif  (substr($r['tipdoc'],0,2) == 'RD'){
				$where = "tipdoc LIKE 'RD_' AND seziva = ".$r['seziva'];
				$order='id_tes DESC';
				$title="Modifica documento";
			}

			if ($r['tipdoc']=="AFT" AND $r['ddt_type']=="T"){
				$addtip="ADT &#8594; ";
			} elseif ($r['tipdoc']=="AFT" AND $r['ddt_type']=="L"){
				$addtip="RDL &#8594; ";
			} else {
				$addtip="";
			}
            echo "<tr>";
            echo '<td class="text-center"><a class="btn btn-xs btn-default" href="admin_docacq.php?id_tes=' . $r["id_tes"] . '&Update&DDT" title="'. $title .'" >  <i class="glyphicon glyphicon-edit"></i>&nbsp;' . $r["id_tes"] . '</a></td>';
            echo '<td class="text-center">' . $addtip.$r["tipdoc"] . " &nbsp;</td>";
            echo '<td class="text-center">'. $r["numdoc"] . ' '.$ddtanomalo.'</td>';
            echo '<td class="text-center">'. gaz_format_date($r["datemi"]). " &nbsp;</td>";
            echo "<td>" . $r["ragso1"] . "&nbsp;</td>";
			if (intval(preg_replace("/[^0-9]/","",$r['numfat']))>=1){
				echo "<td align=\"center\"><a class=\"btn btn-xs btn-default\" style=\"cursor:pointer;\" onclick=\"printPdf('stampa_docacq.php?id_tes=" . $r["id_tes"]."&modal=".$pdf_to_modal ."')\"><i class=\"glyphicon glyphicon-print\" title=\"Stampa fattura n. " . $r["numfat"] . " PDF\"></i> fatt. n. " . $r["numfat"] . "</a></td>";
			} else {
				echo "<td>" . $r["status"] . " &nbsp;</td>";
			}

			echo "<td align=\"center\"><a class=\"btn btn-xs btn-default\" style=\"cursor:pointer;\" onclick=\"printPdf('stampa_docacq.php?id_tes=" . $r["id_tes"] . "&template=DDT&modal=".$pdf_to_modal."')\"><i class=\"glyphicon glyphicon-print\" title=\"Stampa documento PDF\"></i></a></td>";

            echo '<td class="text-center">';
			if (substr($r['tipdoc'], 0, 2)=="AF" ){
				?>
				<button title="Questo Ddt &egrave; stato fatturato. Per eliminarlo devi prima eliminare la relativa fattura" class="btn btn-xs btn-default btn-elimina disabled"><i class="glyphicon glyphicon-remove"></i></button>
				<?php
			} else {
					?>
			<a class="btn btn-xs btn-default btn-elimina dialog_delete" title="Elimina questo D.d.T." ref="<?php echo $r['id_tes'];?>" catdes="<?php echo $r['ragso1']; ?>">
				<i class="glyphicon glyphicon-remove"></i>
			</a>
			<?php
			}
			echo "</td></tr>";
        }
            echo '<tr><td class="FacetFieldCaptionTD" colspan="8" align="right">Querytime: ';
            print_querytime($querytime);
            echo ' sec.</td></tr>';
        ?>
</form>
</table></div>
<?php
require("../../library/include/footer.php");
?>
