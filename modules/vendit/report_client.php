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

$titolo = 'Clienti';

$clienti = $admin_aziend['mascli'];
$mascli = $clienti . "000000";

// campi ammissibili per la ricerca
$search_fields = [
    'codice'
    => "codice = $mascli + %d",
    'nome'
    => "CONCAT(ragso1, ragso2) LIKE '%%%s%%'",
    'codmin'
    => "codice >= $mascli + GREATEST(%d, 1)",
    'codmax'
    => "codice <= $mascli + LEAST(%d, 999999)"
];

// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array(
    "Codice" => "codice",
    "Ragione Sociale" => "ragso1",
    "Tipo" => "sexper",
    "Citt&agrave;" => "citspe",
    "Telefono" => "telefo",
    "P.IVA - C.F." => "",
    "Privacy" => "",
    "Riscuoti" => "",
    "Visualizza <br /> e/o stampa" => "",
    "Cancella" => ""
);

require("../../library/include/header.php");
if (isset($_GET['privacy'])) {
    echo '<script> window.onload = function() {
    window.open("stampa_privacy.php?codice='.intval($_GET['privacy']).'", "_blank"); // will open new tab on window.onload
} </script>';
}
$script_transl = HeadMain();

$partners = "{$gTables['clfoco']} LEFT JOIN {$gTables['anagra']} ON {$gTables['clfoco']}.id_anagra = {$gTables['anagra']}.id";
$ts = new TableSorter(
    $partners,
    $passo,
    ['codice' => 'desc'],
    ['codmin' => 1, 'codmax' => 999999]
);
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
				delete:{ 
					text:'Elimina', 
					'class':'btn btn-danger delete-button',
					click:function (event, ui) {
					$.ajax({
						data: {'type':'client',ref:id},
						type: 'POST',
						url: '../vendit/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_client.php");
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
<div align="center" class="FacetFormHeaderFont">Clienti</div>
<div align="center"><?php $ts->output_navbar(); ?></div>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
        <p><b>cliente:</b></p>
        <p>Codice:</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Ragione sociale:</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
    <div class="box-primary table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
        <tr>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_int("codice", "Codice cli."); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_int("nome", "Nome cliente"); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
                <?php // gaz_flt_disp_int("codmin", "Min"); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
                <?php // gaz_flt_disp_int("codmax", "Max"); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-xs btn-default" name="search" value="Cerca" tabindex="1" >
                <?php $ts->output_order_form();  ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <a class="btn btn-xs btn-default" href="?">Reset</a>
            </td>
        </tr>
        <?php
        $result = gaz_dbi_dyn_query('*', $partners, $ts->where, $ts->orderby, $ts->getOffset(), $ts->getLimit());
        ?>
        <tr>
            <?php $ts->output_headers(); ?>
        </tr>
        <?php
        while ($a_row = gaz_dbi_fetch_array($result)) {
			$rs_check_mov = gaz_dbi_dyn_query("clfoco", $gTables['tesmov'], "clfoco = '".$a_row['codice']."'","id_tes asc",0,1);
            $check_mov = gaz_dbi_num_rows($rs_check_mov);
			$rs_check_doc = gaz_dbi_dyn_query("clfoco", $gTables['tesdoc'], "clfoco = '".$a_row['codice']."'","id_tes asc",0,1);
			$check_doc = gaz_dbi_num_rows($rs_check_doc);
			$rs_check_bro = gaz_dbi_dyn_query("clfoco", $gTables['tesbro'], "clfoco = '".$a_row['codice']."'","id_tes asc",0,1);
			$check_bro = gaz_dbi_num_rows($rs_check_bro);
			echo "<tr class=\"FacetDataTD\">";
            // Colonna codice cliente
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"admin_client.php?codice=" . substr($a_row["codice"], 3) . "&Update\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . substr($a_row["codice"], 3) . "</a> &nbsp</td>";
            // Colonna ragione sociale
            echo "<td title=\"" . $a_row["ragso2"] . "\">" . $a_row["ragso1"] . " &nbsp;</td>";
            // colonna sesso
            echo "<td align=\"center\">" . $a_row["sexper"] . "</td>";
            // colonna indirizzo
            $google_string = str_replace(" ", "+", $a_row["indspe"]) . "," . str_replace(" ", "+", $a_row["capspe"]) . "," . str_replace(" ", "+", $a_row["citspe"]) . "," . str_replace(" ", "+", $a_row["prospe"]);
            echo "<td title=\"" . $a_row["capspe"] . " " . $a_row["indspe"] . "\">";
            echo "<a class=\"btn btn-xs btn-default\" target=\"_blank\" href=\"https://www.google.it/maps/place/" . $google_string . "\">" . $a_row["citspe"] . " (" . $a_row["prospe"] . ")&nbsp;<i class=\"glyphicon glyphicon-map-marker\"></i></a>";
            echo "<a class=\"btn btn-xs btn-default\" target=\"_blank\" href=\"https://www.google.it/maps/dir/" . $admin_aziend['latitude'] . "," . $admin_aziend['longitude'] . "/" . $google_string . "\">  <i class=\"glyphicon glyphicon-random\"></i></a>";
            echo "</td>";
            // composizione telefono
            $title = "";
            $telefono = "";
            if (!empty($a_row["telefo"])) {
                $telefono = $a_row["telefo"];
                if (!empty($a_row["cell"])) {
                    $title .= "cell:" . $a_row["cell"];
                }
                if (!empty($a_row["fax"])) {
                    $title .= " fax:" . $a_row["fax"];
                }
            } elseif (!empty($a_row["cell"])) {
                $telefono = $a_row["cell"];
                if (!empty($a_row["fax"])) {
                    $title .= " fax:" . $a_row["fax"];
                }
            } elseif (!empty($a_row["fax"])) {
                $telefono = "fax:" . $a_row["fax"];
            } else {
                $telefono = "_";
                $title = " nessun contatto telefonico memorizzato ";
            }
            // colonna telefono
            echo "<td title=\"$title\" align=\"center\">" . gaz_html_call_tel($telefono) . " &nbsp;</td>";
            // colonna fiscali
            if ($a_row['pariva'] > 0 and empty($a_row['codfis'])) {
                echo "<td align=\"center\">" . $a_row['country'] . " " . $a_row['pariva'] . "</td>";
            } elseif ($a_row['pariva'] == 0 and ! empty($a_row['codfis'])) {
                echo "<td align=\"center\">" . $a_row['codfis'] . "</td>";
            } elseif ($a_row['pariva'] > 0 and ! empty($a_row['codfis'])) {
                if ($a_row['pariva'] == $a_row['codfis']) {
                    echo "<td align=\"center\">";
                    echo gaz_html_ae_checkiva($a_row['country'], $a_row['pariva']);
                    echo "</td>";
                } else {
                    echo "<td align=\"center\">" . gaz_html_ae_checkiva($a_row['country'], $a_row['pariva']) . "<br>" . $a_row['codfis'] . "</td>";
                }
            } else {
                echo "<td class=\"FacetDataTDred\" align=\"center\"> * NO * </td>";
            }
            // colonna stampa privacy
            echo "<td align=\"center\"><a title=\"stampa informativa sulla privacy\" class=\"btn btn-xs btn-default\" href=\"stampa_privacy.php?codice=" . $a_row["codice"] . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-eye-close\"></i></a><a title=\"stampa richiesta codice sdi o pec\" class=\"btn btn-xs btn-default\" href=\"stampa_richiesta_pecsdi.php?codice=" . $a_row["codice"] . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-inbox\"></i></a></td>";
            echo "<td title=\"Effettuato un pagamento da " . $a_row["ragso1"] . "\" align=\"center\"><a class=\"btn btn-xs btn-default btn-pagamento\" href=\"customer_payment.php?partner=" . $a_row["codice"] . "\"><i class=\"glyphicon glyphicon-euro\"></i></a></td>";
            echo "<td title=\"Visualizza e stampa il partitario\" align=\"center\">  <a class=\"btn btn-xs btn-default\" href=\"report_contcli.php?id=".$a_row["codice"]."\"  target=\"_blank\"><i class=\"glyphicon glyphicon-list-alt\"></i></a> <a class=\"btn btn-xs btn-default\" href=\"../contab/select_partit.php?id=".$a_row["codice"]."\" target=\"_blank\"><i class=\"glyphicon glyphicon-check\"></i>&nbsp;<i class=\"glyphicon glyphicon-print\"></a></td>";
            echo "<td align=\"center\">";
			if ($check_mov > 0 or $check_doc > 0 or $check_bro > 0) {
				?>
				<a class="btn btn-xs btn-default btn-elimina" title="Impossibile cancellare perché ci sono dei movimenti associati" ref="<?php echo substr($a_row['codice'], 3);?>" >
					<i class="glyphicon glyphicon-ban-circle"></i>
				</a>
				<?php
				
			} else {
				?>
				<a class="btn btn-xs btn-default btn-elimina dialog_delete" title="Cancella il cliente" ref="<?php echo $a_row['codice'];?>" ragso="<?php echo $a_row['ragso2']," ",$a_row['ragso1'];?>">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
				<?php
			}
            echo "</td></tr>\n";
        }
        ?>
        <tr><th class="FacetFieldCaptionTD" colspan="10"></th></tr>
    </table>
    </div>
</form>

<script>
 $(document).ready(function(){
     var selects = $("select");
     // la funzione gaz_flt_dsp_select usa "All", qui usiamo invece valori vuoti
     // (in questo modo i campi non usati possono essere esclusi)        
     $("option", selects).filter(function(){ return this.value == "All"; }).val("");
     
     // la stessa funzione imposta onchange="this.form.submit()" sulle select: 
     // l'azione non lancia un evento "submit" e non può essere intercettata.
     // per non andare a modificare la funzione rimpiazziamo l'attributo onchange:
     selects.attr('onchange', null).change(function() { $(this.form).submit(); });
     
     // così ora possiamo intercettare tutti i submit e pulire la GET dal superfluo
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
