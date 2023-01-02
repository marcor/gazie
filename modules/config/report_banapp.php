<?php
 /*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
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
$script_transl = HeadMain('','','admin_banapp');
?>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("ragso"));
		var id = $(this).attr('ref');
		$( "#dialog_delete" ).dialog({
			minHeight: 1,
			width: "auto",
			modal: "true",
			show: "blind",
			hide: "explode",
			buttons: {
   			close: {
					text:'Non eliminare',
					'class':'btn btn-default',
          click:function() {
            $(this).dialog("close");
          }
        },
				delete:{
					text:'Elimina',
					'class':'btn btn-danger',
					click:function (event, ui) {
					$.ajax({
						data: {'type':'banapp',ref:id},
						type: 'POST',
						url: '../config/delete.php',
						success: function(output){
							window.location.replace("./report_banapp.php");
						}
					});
				}}
			}
		});
		$("#dialog_delete" ).dialog( "open" );
	});
});
</script>
<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
	<p><b>Banca d'appoggio:</b></p>
	<p>Codice:</p>
	<p class="ui-state-highlight" id="idcodice"></p>
	<p>Descrizione:</p>
	<p class="ui-state-highlight" id="iddescri"></p>
</div>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['report']; ?></div>
<?php
$recordnav = new recordnav($gTables['banapp'], $where, $limit, $passo);
$recordnav -> output();
?>
<div class="table-responsive"><table class="Tlarge table table-striped table-bordered table-condensed">
<?php
$headers_banapp = array  (
              $script_transl['codice'] => "codice",
              $script_transl['descri'] => "descri",
              $script_transl['locali'] => "locali",
              $script_transl['codabi'] => "codabi",
              $script_transl['codcab'] => "codcab",
              $script_transl['delete'] => ""
              );
$linkHeaders = new linkHeaders($headers_banapp);
$linkHeaders -> output();
?>
   </tr>
<?php
$result = gaz_dbi_dyn_query ('*', $gTables['banapp'], $where, $orderby, $limit, $passo);
while ($a_row = gaz_dbi_fetch_array($result)) {
	$rs_check_doc = gaz_dbi_dyn_query("id_tes", $gTables['tesdoc'], "banapp = '{$a_row['codice']}'", "id_tes", 0, 1);
  $check_doc = gaz_dbi_num_rows($rs_check_doc);
	$rs_check_cli = gaz_dbi_dyn_query("codice", $gTables['clfoco'], "banapp = '{$a_row['codice']}'", "codice", 0, 1);
  $check_cli = gaz_dbi_num_rows($rs_check_cli);
  echo "<tr class=\"FacetDataTD\">";
  echo "<td align=\"center\"><a class=\"btn btn-xs btn-edit\" href=\"admin_banapp.php?Update&codice=".$a_row["codice"]."\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$a_row["codice"]."</a> &nbsp</td>";
  echo "<td>".$a_row["descri"]." &nbsp;</td>";
  echo "<td align=\"center\">".$a_row["locali"]." &nbsp;</td>";
  echo "<td align=\"center\">". sprintf("%'.05d\n", $a_row["codabi"]) ."</td>";
  echo "<td align=\"center\">". sprintf("%'.05d\n", $a_row["codcab"]) ."</td><td align=\"center\">";
  if ($check_doc > 0 || $check_cli > 0){
		?>
		<button title="Impossibile cancellare perché usata da clienti e/o documenti di vendita" class="btn btn-xs btn-default btn-elimina disabled"><i class="glyphicon glyphicon-remove"></i></button>
		<?php
	} else {
		?>
		<a class="btn btn-xs btn-default btn-elimina dialog_delete" title="Cancella la banca d'appoggio" ref="<?php echo $a_row['codice'];?>" ragso="<?php echo $a_row['descri'];?>">
			<i class="glyphicon glyphicon-remove"></i>
		</a>
		<?php
	}
  echo "</td></tr>";
}
?>
 </table></div>
<?php
require("../../library/include/footer.php");
?>
