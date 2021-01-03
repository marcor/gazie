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
// ANTONIO GERMANI       >>> gestione coltivazioni <<<

require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
$titolo = 'Campi';
require("../../library/include/header.php");
$script_transl = HeadMain();

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "nome_colt like '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "nome_colt like '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "nome_colt like '".addslashes($auxil)."%'";
}
?>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("coltdes"));
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
						data: {'type':'colture',ref:id},
						type: 'POST',
						url: '../camp/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./admin_colt.php");
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
<div align="center" class="FacetFormHeaderFont">Colture</div>
<?php
$recordnav = new recordnav($gTables['camp_colture'], $where, $limit, $passo);
$recordnav -> output();
?>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<div style="display:none" id="dialog_delete" title="CONFERMA ELIMINAZIONE">
		<p><b>COLTURA:</b></p>
		<p>Codice</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Descrizione</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
    	<thead>
            <tr>
                <td></td>
                <td class="FacetFieldCaptionTD">Nome coltura:
                    <input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="6" tabindex="1" class="FacetInput" />
					<input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;" />
                
                    <input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;" />
					
                </td>
                <td align="center">               
				<a class="btn btn-xs btn-default" href="admin_coltura.php?insert" title="Aggiungi nuova coltura">
					<i class="glyphicon glyphicon-plus-sign"></i> Aggiungi
				</a>
			</td>
            </tr>
            <tr>
<?php
	$result = gaz_dbi_dyn_query ('*', $gTables['camp_colture'], $where, $orderby, $limit, $passo);
	// creo l'array (header => campi) per l'ordinamento dei record
	$headers_colt = array("ID"      => "id_colt",
							"Nome coltura" => "nome_colt"							
							);
	$linkHeaders = new linkHeaders($headers_colt);
	$linkHeaders -> output();
?>
        	</tr>
        </thead>
        <tbody>
<?php
while ($a_row = gaz_dbi_fetch_array($result)) {
?>		<tr class="FacetDataTD">
			<td>
				<a class="btn btn-xs btn-success btn-block" title="Modifica" href="admin_coltura.php?Update&id_colt=<?php echo $a_row["id_colt"]; ?>">
					<i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $a_row["id_colt"];?>
				</a>
			</td>
			<td>
				<span class="gazie-tooltip" data-type="catmer-thumb" data-id="<?php echo $a_row['id_colt']; ?>" data-title="<?php echo $a_row['nome_colt']; ?>"><?php echo $a_row["nome_colt"]; ?></span>
			</td>
			<td align="center">
				<a class="btn btn-xs btn-default btn-elimina dialog_delete" ref="<?php echo $a_row['id_colt'];?>" coltdes="<?php echo $a_row['nome_colt']; ?>">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
			</td>
		</tr>
<?php
}
?>
    	</tbody>
    </table>
    <?php
?>
<?php    
require("../../library/include/footer.php");
?>