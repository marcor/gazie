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
 // IL REGISTRO DI CAMPAGNA E' UN MODULO DI ANTONIO GERMANI - MASSIGNANO AP
// >> Visualizza recipienti di stoccaggio <<

require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
$titolo = 'Recipienti di staccaggio e silos';
require("../../library/include/header.php");
$script_transl = HeadMain();

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "cod_silos like '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "cod_silos like '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "cod_silos like '".addslashes($auxil)."%'";
}

// controllo contenitori e silos
function getCont($codsil){
	global $gTables,$admin_aziend;
	$content=0;
	$orderby=2;
	$limit=0;
	$passo=2000000;
	$where="recip_stocc = '".$codsil."'";
	$what=	$gTables['movmag'].".operat, ".$gTables['movmag'].".quanti, ".$gTables['movmag'].".id_orderman, ".
			$gTables['camp_mov_sian'].".*, ".$gTables['camp_artico'].".confezione ";
	$groupby= "";
	$table=$gTables['camp_mov_sian']." LEFT JOIN ".$gTables['movmag']." ON ".$gTables['movmag'].".id_mov = ".$gTables['camp_mov_sian'].".id_movmag
										LEFT JOIN ".$gTables['camp_artico']." ON ".$gTables['camp_artico'].".codice = ".$gTables['movmag'].".artico
	";
	$ressilos=gaz_dbi_dyn_query ($what,$table,$where,$orderby,$limit,$passo,$groupby);
	while ($r = gaz_dbi_fetch_array($ressilos)) {
		if ($r['confezione']==0){
			$content=$content+($r['quanti']*$r['operat']);
		} 
	}
	$content=number_format ($content,3);
	
	return $content ;
}

?>
<style>
	.bar {
		max-width:100%;
		width:283px;
		height: 28px;
		overflow: hidden;		
		background: url(../../modules/camp/media/background_bar.jpg) no-repeat;
	}
</style>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("capacity"));
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
						data: {'type':'recstocc',ref:id},
						type: 'POST',
						url: '../root/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./rec_stocc.php");
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
<div align="center" class="FacetFormHeaderFont">Recipienti di stoccaggio</div>
<?php
$recordnav = new recordnav($gTables['camp_recip_stocc'], $where, $limit, $passo);
$recordnav -> output();
?>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
		<p><b>recipiente di stoccaggio:</b></p>
		<p>Codice</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Descrizione</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
    	<thead>
            <tr>
                <td></td>
                <td class="FacetFieldCaptionTD">Codice recipiente o silos:
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
	$result = gaz_dbi_dyn_query ('*', $gTables['camp_recip_stocc'], $where, $orderby, $limit, $passo);
	// creo l'array (header => campi) per l'ordinamento dei record
	$headers_silos = array("Codice SIAN del recipiente o silos"      => "cod_silos",
							"Capacità in Kg" => "capacita",
							"Stato" => "riempimento",
							"Titolo di possesso" => "affitto",
							"Destinato a DOP o IGP" => "dop_igp",
							"Cancella"    => ""
							);
	$linkHeaders = new linkHeaders($headers_silos);
	$linkHeaders -> output();
?>
        	</tr>
        </thead></form>
        <tbody>
<?php


while ($a_row = gaz_dbi_fetch_array($result)) {
	$content=getCont($a_row['cod_silos']); 
?>		
			<tr class="FacetDataTD">
			<td>
				<a class="btn btn-xs btn-success btn-block" href="admin_rec_stocc.php?Update&codice=<?php echo $a_row["cod_silos"]; ?>">
					<i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $a_row['cod_silos'];?>
				</a>
			</td>
			<td align="center"><?php echo gaz_format_quantity($a_row['capacita'], 1, 3);?></td>
			<td>
			<?php echo $content; 
			if ($content > $a_row['capacita']){
				echo " ERRORE!";
			}
			?>
			<div class="bar">
				<img src="../../modules/camp/media/white_bar.jpg" alt="Barra silos" title="Contenuto silos" style="padding-left:<?php echo ((($content/$a_row['capacita'])*100)* 280 )/100;?>px;">
			</div>
			</td>
			<td align="center">
			<?php
			if (intval($a_row['affitto'])==0){
				echo "Proprietà";
			} else{
				echo "Affitto";
			}
			?>
			</td>
			<td align="center">
			<?php
			if (intval($a_row['dop_igp'])==0){
				echo "NO";
			} else{
				echo "DOP IGP";
			}
			?>
			</td>
			<td align="center">
				<a class="btn btn-xs btn-default btn-elimina dialog_delete" ref="<?php echo $a_row['cod_silos'];?>" capacity="<?php echo $a_row['capacita']; ?>">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
			</td>
		</tr>
<?php
}
?>
		<tr class=\"FacetFieldCaptionTD\">
			<form method="post" action="admin_rec_stocc.php">
			<td colspan="7" align="right">
				<input class="btn btn-info" type="submit" name="aggiungi" value="<?php echo "Inserisci nuovo contenitore o silos";?>">
			</td>
		</tr>

<!-- Se servirà la STAMPA riattivare con le dovute modifiche		
<tr class=\"FacetFieldCaptionTD\">
	<form method="post" action="stampa_campi.php">
    <td colspan="7" align="right"><input type="submit" name="print" value="<?php echo $script_transl['print'];?>">
    </td>
</tr>
-->
    		</tbody>
    </table>
	</form>
    <?php
require("../../library/include/footer.php");
?>