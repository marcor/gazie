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
$admin_aziend = checkAdmin();
require("../../library/include/header.php");
$script_transl = HeadMain();

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "description like '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "description like '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "description like '".addslashes($auxil)."%'";
}

?>
<div align="center" class="FacetFormHeaderFont">Elenco produzioni</div>
<?php
if (!isset($_GET['field'])||empty($_GET['field']))
   $orderby = "id DESC";

$recordnav = new recordnav($gTables['orderman'], $where, $limit, $passo);
$recordnav -> output();
?>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("orddes"));
		var id = $(this).attr('ref');
		var id2 = $(this).attr('ref2');
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
						data: {'type':'orderman',ref:id,ref2:id2},
						type: 'POST',
						url: '../orderman/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./orderman_report.php");
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
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
        <p><b>produzione:</b></p>
        <p>codice:</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Descrizione</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
	<div class="table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed ">
    	<thead>
            <tr>
                <td></td>
                <td class="FacetFieldCaptionTD">Descrizione:
                    <input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput" />
                </td>
                <td>
                    <input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;" />
                </td>
                <td>
                    <input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;" />
                </td>
            </tr>
            <tr>
<?php
	$result = gaz_dbi_dyn_query ('*', $gTables['orderman'], $where, $orderby, $limit, $passo);
	// creo l'array (header => campi) per l'ordinamento dei record
	$headers_orderman = array("Codice ID"      => "id",
							"Descrizione" => "description",
							"Tipo lavorazione"  => "order_type",
							"Informazioni aggiuntive" => "add_info",
							"Articolo" => "",
							"Q.tà prodotta" => "",
							"Lotto e scadenza" => "",
							"Ordine" => "",
							"Inizio produzione" => "",
							"Durata" => "",
							"Luogo di produzione" => "campo_impianto",
							"Distinta" => "",
							"Cancella"    => ""
							);
	$linkHeaders = new linkHeaders($headers_orderman);
	$linkHeaders -> output();
?>
        	</tr>
        </thead>
        <tbody>
<?php
while ($a_row = gaz_dbi_fetch_array($result)) {
?>		<tr class="FacetDataTD">
			<td>
				<a class="btn btn-xs btn-default btn-block" href="admin_orderman.php?Update&codice=<?php echo $a_row['id']; ?>">
					<i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $a_row['id'];?>
				</a>
			</td>
			<td>
				<span class="gazie-tooltip" data-type="catmer-thumb" data-id="<?php echo $a_row['id']; ?>" data-title="<?php echo $a_row['add_info']; ?>"><?php echo $a_row['description']; ?></span>
			</td>
			<td align="center"><?php echo $script_transl['order_type'][$a_row['order_type']];?></td>
			<td align="center"><?php echo $a_row['add_info'];?></td>
			<?php $d_row = gaz_dbi_get_row($gTables['rigbro'], "id_rig", $a_row['id_rigbro']);?>
			<td align="center"><?php echo $d_row['codart'];?></td>
			<td align="center"><?php echo gaz_format_quantity($d_row['quanti'], true, $admin_aziend['decimal_quantity']);?></td>
			<?php $e_row = gaz_dbi_get_row($gTables['movmag'], "id_orderman", $a_row['id'], "AND operat = 1");
			$f_row = gaz_dbi_get_row($gTables['lotmag'], "id_movmag", $e_row['id_mov']);?>
			<?php 
			if (strlen($f_row['identifier'])>0) {
				echo '<td align="center">'.$f_row['identifier'].' - '.gaz_format_date($f_row['expiry']).'</td>';
			} else {
				echo '<td></td>';
			}
			?>
			<!-- Antonio Germani Vado a leggere la tabella tesbro connessa alla produzione -->
			<?php $b_row = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $a_row['id_tesbro']);?>
			<td align="center"><?php echo $b_row['numdoc'];?></td>
			<td align="center"><?php echo gaz_format_date($b_row['datemi']);?></td>
			<td align="center"><?php echo $a_row['duration'];?></td>
			<!-- Antonio Germani Vado a leggere la descrizione del campo connesso alla produzione -->
			<?php $c_row = gaz_dbi_get_row($gTables['campi'], "codice", $a_row['campo_impianto']);?>
			<td align="center"><?php echo $a_row['campo_impianto'], " ", $c_row['descri'] ;?></td>
			<?php
			if ($a_row['order_type']=="IND" or $a_row['order_type']=="ART"){
			?>
			<td align="center">
				<a class="btn btn-info" href="stampa_produzione.php?id_orderman=<?php echo $a_row['id']; ?>">
					<i class="glyphicon glyphicon-list-alt"></i>
				</a>
			</td>
			<?php
			} else  {
				echo '<td></td>';
			}
			?>
			<td align="center">
				<a class="btn btn-xs btn-default btn-elimina dialog_delete" ref="<?php echo $a_row['id'];?>" ref2="<?php echo $a_row['id_tesbro'];?>" orddes="<?php echo $a_row['description']; ?>">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
			</td>
		</tr>
<?php
}
?>
    		</tbody>
        </table></div>
		</form>

<?php

require("../../library/include/footer.php");
?>