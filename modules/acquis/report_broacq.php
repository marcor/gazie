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
$partner_select_mode = gaz_dbi_get_row($gTables['company_config'], 'var', 'partner_select_mode');
$message = "";
$anno = date("Y");

if (isset($_GET['flt_tipo'])) {
    $flt_tipo = filter_input(INPUT_GET,'flt_tipo');
} elseif (isset($_GET['datfat'])) { // vengo da una richiesta fatta con recordnav
    $flt_tipo = filter_input(INPUT_GET,'datfat');
} else {
	$flt_tipo='APR';
}
$datfat = substr($flt_tipo,0,3);

if (isset($_GET['auxil'])) {
    $auxil = filter_input(INPUT_GET, 'auxil');
} else {
    $auxil = 1;
}
$where = "tipdoc = '".$datfat."' AND seziva = '$auxil'";
$all = $where;

$documento = '';
$fornitore = '';

gaz_flt_var_assign('id_tes', 'i');
gaz_flt_var_assign('numdoc', 'i');
gaz_flt_var_assign('id_orderman', 'i');
gaz_flt_var_assign('datemi', 'd');
gaz_flt_var_assign('clfoco', 'v');


if (isset($_GET['datemi'])) {
    $datemi = filter_input(INPUT_GET,'datemi');
}


if (isset($_GET['fornitore'])) {
    if ($_GET['fornitore'] <> '') {
        $fornitore = filter_input(INPUT_GET,'fornitore');
        $where = "tipdoc = '".$flt_tipo."' AND ragso1 LIKE \"%" . $fornitore.'%"';
        $limit = 0;
        $passo = 2000000;
        unset($documento);
    }
}

if (isset($_GET['all'])) {
    $_GET['id_tes'] = "";
    $_GET['numdoc'] = "";
    $_GET['id_orderman'] = "";
    $_GET['datemi'] = "";
    $_GET['clfoco'] = "";
    gaz_set_time_limit(0);
    $auxil = filter_input(INPUT_GET, 'auxil') . "&all=yes";
    $passo = 100000;
    $where = "tipdoc = '".$flt_tipo."' AND seziva = '$auxil' ";
    unset($documento);
    $fornitore = '';
}

// prendo i dati facendo il join con le anagrafiche
$what=$gTables['tesbro'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesbro'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['clfoco'] . ".id_anagra = " . $gTables['anagra'] . ".id";
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/modal_form'));
$gForm = new acquisForm();
?>
<script>
function confirmemail(cod_partner,id_tes,genorder=false) {
	var fornitore=$("#fornitore_"+id_tes).attr('value');
	var tipdoc=$("#tipdoc_"+id_tes).attr('value');
	if (tipdoc=='AOR') {
			$("#confirm_email").attr('title', 'Invia ORDINE a '+fornitore);
	} else if (tipdoc=='APR' && genorder ) {
			$("#confirm_email").attr('title', 'Genera ORDINE a '+fornitore);
			$("#mailaddress").remove();
			$("#mailbutt").remove();
			$("#maillabel").remove();
	} else {
			$("#confirm_email").attr('title', 'Invia Preventivo a '+fornitore);
	}
	$.get("search_email_address.php",
		  {clfoco: cod_partner},
		  function (data) {
			var j=0;
			$.each(data, function (i, value) {
				if (j==0){
					$("#mailbutt").append("<div>Indirizzi archiviati:</div>");
				}
				$("#mailbutt").append("<div align='center'><button id='fillmail_" + j+"'>" + value.email + "</button></div>");
                $("#fillmail_" + j).click(function () {
					$("#mailaddress").val(value.email);
				});
				j++;
			});
		  }, "json"
         );
		 
	$( function() {
    var dialog
	,	 
    emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
	dialog = $("#confirm_email").dialog({
		modal: true,
		show: "blind",
		hide: "explode",
		width: "auto",
		buttons: {
			Annulla: function() {
				$(this).dialog('close');
			},
			Conferma: function() {
				if ( !( emailRegex.test( $("#mailaddress").val() ) ) && !genorder ) {
					alert('Mail formalmente errata');
				} else {
					$("#mailbutt div").remove();
					var dest=$("#mailaddress").val();
					if (tipdoc=='AOR') { // è già un ordine lo reinvio
						window.location.href = 'stampa_ordfor.php?id_tes='+id_tes+'&dest='+dest;
					} else if (tipdoc=='APR' && genorder ) { // in caso di generazione ordine vado sull'apposito script php per la generazione ma non lo invio tramite email 
						window.location.href = 'duplicate_broacq.php?id_tes='+id_tes+'&dest='+dest;
					} else { // il preventivo lo invio solamente
						window.location.href = 'stampa_prefor.php?id_tes='+id_tes+'&dest='+dest;
					}
				}
			}
		},
		close: function(){
				$("#mailbutt div").remove();
				$(this).dialog('destroy');
		}
	});
	});
}

function choicePartner(row)
{
	$( "#search_partner"+row ).autocomplete({
		source: "../../modules/root/search.php?opt=supplier",
		minLength: 2,
        html: true, // optional (jquery.ui.autocomplete.html.js required)
      	// optional (if other layers overlap autocomplete list)
        open: function(event, ui) {
            $(".ui-autocomplete").css("z-index", 1000);
        },
		select: function(event, ui) {
			$(".supplier_name").replaceWith(ui.item.value);
			$("#confirm_duplicate").dialog({
				modal: true,
				show: "blind",
				hide: "explode",
				buttons: {
					Annulla: function() {
						$(this).dialog('destroy');
						}
					,
					Duplica: function() {
						window.location.href = 'duplicate_broacq.php?id_tes='+row+'&duplicate='+ui.item.codice;
						}
					},
				close: function(){}
				});
			}		
	});
}

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
						data: {'type':'broacq',id_tes:id},
						type: 'POST',
						url: '../acquis/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_broacq.php?flt_tipo=<?php echo $flt_tipo; ?> ");
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
        <p><b>ordine/preventivo:</b></p>
        <p>Codice:</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Fornitore</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
    <div align="center" class="FacetFormHeaderFont"> <?php echo $script_transl['title_dist'][$flt_tipo]; ?>
	<input type="hidden" name="flt_tipo" value="<?php echo $flt_tipo; ?>" />
	<select name="auxil" class="FacetSelect" onchange="this.form.submit()">
            <?php
            for ($sez = 1; $sez <= 9; $sez++) {
                $selected = "";
                if (substr($auxil, 0, 1) == $sez)
                    $selected = " selected ";
                echo "<option value=\"" . $sez . "\"" . $selected . ">" . $sez . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
    if (!isset($_GET['field']) or ( $_GET['field'] == 2) or ( empty($_GET['field'])))
        $orderby = "datemi desc, numdoc desc";
    $recordnav = new recordnav($what, $where, $limit, $passo);
    $recordnav->output();
    ?>
    <div class="box-primary table-responsive">
        <table class="Tlarge table table-striped table-bordered table-condensed">
            <tr>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("numdoc", "numdoc", $what, $all, $orderby); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("id_orderman", "Produzione"); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_select("datemi", "YEAR(datemi) as datemi", $gTables["tesbro"], $all, $orderby); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php
                    if ($partner_select_mode['val'] == null or $partner_select_mode['val'] == "0") {
                        gaz_flt_disp_select("clfoco", $gTables['anagra'] . ".ragso1," . $gTables["tesbro"] . ".clfoco", $what, $all, "ragso1", "ragso1");
                    } else {
                        gaz_flt_disp_int("fornitore", "fornitore");
                    }
                    ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    &nbsp;
                </td>
                <td class="FacetFieldCaptionTD">
                    &nbsp;
                </td>
                <td class="FacetFieldCaptionTD">
                    <input class="btn btn-sm btn-default" type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
                </td>
                <td class="FacetFieldCaptionTD">
                    <input class="btn btn-sm btn-default" type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;">
                </td>
            </tr>

            <tr>
                <?php
                $linkHeaders = new linkHeaders($script_transl['header']);
                $linkHeaders->setAlign(array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'));
                $linkHeaders->output();
                ?>
            </tr>
            <?php
            $rs_ultimo_documento = gaz_dbi_dyn_query("*",  $what, $where, "datemi desc, numdoc desc", 0, 1);
            $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
            if ($ultimo_documento)
                $ultimoddt = $ultimo_documento['numdoc'];
            else
                $ultimoddt = 1;
			$anagrafica = new Anagrafica();
			//recupero le testate in base alle scelte impostate
            $result = gaz_dbi_dyn_query("*", $what, $where, $orderby, $limit, $passo);
            while ($r = gaz_dbi_fetch_array($result)) {
				$linkstatus=false;	
				if ($r["tipdoc"] == 'APR') { // preventivo
					$rs_parent = gaz_dbi_get_row($gTables["tesbro"],'id_parent_doc',$r['id_tes']);
					$clastatus='info';	
					$status='Ordina';	
					if (strlen($r['email'])<8){
						$clastatus='warning';	
						$status='da inviare';	
					}
					if ($rs_parent && $rs_parent["tipdoc"] == 'APR') { // il genitore è pure un preventivo
					} elseif ($rs_parent && $rs_parent["tipdoc"] == 'AOR') { // è stato generato un ordine  
						$clastatus='success';	
						$status='Ordinato con n.'.$rs_parent["numdoc"];
						$linkstatus='stampa_ordfor.php?id_tes='.$rs_parent["id_tes"];	
					}				
                    $tipodoc="Preventivo";
                    $modulo="stampa_prefor.php?id_tes=".$r['id_tes'];
                    $modifi="admin_broacq.php?id_tes=".$r['id_tes']."&Update";
                } elseif ($r["tipdoc"] == 'AOR') {
					$linkstatus='stampa_ordfor.php?id_tes='.$r['id_tes'];	
					$rs_parent = gaz_dbi_get_row($gTables["tesbro"],'id_tes',$r['id_parent_doc']);
					if (strlen($r['email'])>8){
						$clastatus='success';	
						$status='Inviato';	
					} else {
						$clastatus='warning';	
						$status='Inserito';	
					}
					if ($rs_parent && $rs_parent["tipdoc"] == 'APR') { // il genitore è un preventivo
						$status .= '( da prev.n.'.$rs_parent["numdoc"].')';
					}				
                    $tipodoc="Ordine";
                    $modulo="stampa_ordfor.php?id_tes=".$r['id_tes'];
                    $modifi="admin_broacq.php?id_tes=".$r['id_tes']."&Update";
                }
				
				
                $fornitore = $anagrafica->getPartner($r['clfoco']);
                echo '<tr class="FacetDataTD text-center">';

				// colonna numero documento
				echo "<td><a class=\"btn btn-xs btn-default\" id=\"tipdoc_".$r['id_tes']."\"  value=\"".$r["tipdoc"]."\" href=\"".$modifi."\"><i class=\"glyphicon glyphicon-edit\"></i> ".$tipodoc." n.".$r["numdoc"]." &nbsp;</a></td>\n";


				// colonna produzione
				$orderman_descr='';
                $rigbro_result = gaz_dbi_dyn_query('*', $gTables['rigbro']." LEFT JOIN ".$gTables['orderman']." ON ".$gTables['rigbro'].".id_orderman = ".$gTables['orderman'].".id", "id_tes = " . $r["id_tes"] , 'id_tes DESC');

				// INIZIO crezione tabella per la visualizzazione sul tootip di tutto il documento 
				$tt = '<table><th colspan=4 >' . $tipodoc." n.".$r["numdoc"].' del '. gaz_format_date($r["datemi"]).'</th>';
                while ( $rigbro_r = gaz_dbi_fetch_array($rigbro_result) ) {
					if ($rigbro_r['id_orderman']>0){
						$orderman_descr=$rigbro_r['id_orderman'].'-'.$rigbro_r['description'];
					}
					$tt .= '<tr><td>' . $rigbro_r['codart'] . '</td><td>' . htmlspecialchars( $rigbro_r['descri'] ) . '</td><td>' . $rigbro_r['unimis'] . '</td><td align=right>' . $rigbro_r['quanti'] . '</td></tr>';
				}
				$tt .= '</table>';
				// FINE creazione tabella per il tooltip dei righi

                echo '<td>'.$orderman_descr." &nbsp;</td>\n";

				
				// colonna data documento
				echo "<td>".gaz_format_date($r["datemi"])." &nbsp;</td>\n";

				// colonna fornitore
				echo '<td><div class="gazie-tooltip" data-type="movcon-thumb" data-id="' . $r["id_tes"] . '" data-title="' . str_replace("\"", "'", $tt) . '" >'."<a title=\"Dettagli fornitore\" id=\"fornitore_".$r['id_tes']."\"  value=\"".$fornitore["ragso1"]."\" href=\"report_fornit.php?nome=" . htmlspecialchars($fornitore["ragso1"]) . "\">".$fornitore["ragso1"]."&nbsp;</a></div></td>";

				// colonna bottone cambia stato	
				echo '<td><a class="btn btn-xs btn-'.$clastatus.'"';
				if ($clastatus=='warning'){ // Ordine non confermato
					echo ' onclick="confirmemail(\''.$r["clfoco"].'\',\''.$r['id_tes'].'\',true);" title="Invia mail di conferma"';
				}elseif($clastatus=='info'){ // Preventivo: chiedo generazione ordine 
					echo ' onclick="confirmemail(\''.$r["clfoco"].'\',\''.$r['id_tes'].'\',true);" title="Genera un ordine da questo preventivo"';
				}else{ // Ordine confermato o preventivo che ha già generato ordine, visualizzo il pdf
					echo ' href="'.$linkstatus.'" title="Visualizza PDF"'; 
				}
                echo '>'.$status.'</a>';
				if ($r['tipdoc']=='AOR'){
					echo '<br><a class="btn btn-xs btn-default" title="Data consegna">'; 
					echo '<small> cons: '.gaz_format_date($r["initra"]).'</small></a>';
				}
				echo '</td>';

                // colonna stampa
				echo "<td align=\"center\">";
				echo "<a class=\"btn btn-xs btn-default\" href=\"".$modulo."\" title=\"Stampa per fornitore\" target=\"_blank\">
								<i class=\"glyphicon glyphicon-print\"></i>
							</a>\n";
				if($r["tipdoc"] == 'AOR') {
					echo " - <a class=\"btn btn-xs btn-default\" href=\"stampa_ordfor.php?id_tes=".$r['id_tes']."&production\" title=\"Stampa per reparto produzioni\" target=\"_blank\">
								<i class=\"glyphicon glyphicon-fire\"></i>
							</a>\n";
					
				}			
				echo "</td>";

				// colonna operazioni
				echo '<td align="center">';
				if ($r["tipdoc"] == 'APR'){
					echo '<button title="Stesso preventivo per altro fornitore" class="btn btn-default btn-xs" type="button" data-toggle="collapse" data-target="#duplicate_'.$r['id_tes'].'" aria-expanded="false" aria-controls="duplicate_'.$r['id_tes'].'"><i class="glyphicon glyphicon-tags">Duplica</i></button>&nbsp;';
                echo '<div class="collapse" id="duplicate_'.$r['id_tes'].'">Fornitore: <input id="search_partner'.$r['id_tes'].'" onClick="choicePartner(\''.$r['id_tes'].'\');"  value="" rigo="'. $r['id_tes'] .'" type="text" /></div>';
				}
				$st=$gForm->getOrderStatus($r['id_tes']);
				if ($r["tipdoc"] == 'AOR') {
					echo '<div><button title="Duplica questo ordine come preventivo per fornitore" class="btn btn-default btn-xs" type="button" data-toggle="collapse" data-target="#duplicate_'.$r['id_tes'].'" aria-expanded="false" aria-controls="duplicate_'.$r['id_tes'].'"><i class="glyphicon glyphicon-tags">crea Preventivo</i></button></div>';
                echo '<div class="collapse" id="duplicate_'.$r['id_tes'].'">Fornitore: <input id="search_partner'.$r['id_tes'].'" onClick="choicePartner(\''.$r['id_tes'].'\');"  value="" rigo="'. $r['id_tes'] .'" type="text" /></div>';
				echo '<div>';
				if ($st[0]==0){ // tutto da ricevere
					echo '<a title="Il fornitore consegna la merce ordinata" class="btn btn-xs btn-danger" href="order_delivered.php?id_tes=' . $r['id_tes'] . '"><i class="glyphicon glyphicon-save-file">Ricevi</i></a>';
				}elseif ($st[0]==1){ //  da ricevere in parte
					foreach($st[2]as$kd=>$vd){
						echo '<a title="Modifica il documento di acconto" class="btn btn-xs btn-default" href="admin_docacq.php?id_tes=' . $kd . '&Update"><i class="glyphicon glyphicon-edit">IdDoc.'.$kd.'</i></a> - ';
					}
					echo '<a title="Il fornitore consegna il saldo della merce" class="btn btn-xs btn-warning pull-right" href="order_delivered.php?id_tes=' . $r['id_tes'] . '"><i class="glyphicon glyphicon-save-file pull-right">Salda</i></a>';
				}elseif(is_array($st[2])){ // completamente ricevuto
					foreach($st[2]as$kd=>$vd){
						echo '<a title="Modifica il documento di acconto" class="btn btn-xs btn-default" href="admin_docacq.php?id_tes=' . $kd . '&Update"><i class="glyphicon glyphicon-edit">IdDoc.'.$kd.'</i></a> - ';
					}
					echo '<a title="Il fornitore ha consegnato tutta la merce ordinata" disabled class="btn btn-xs btn-success pull-right" href=""><i class="glyphicon glyphicon-save-file">Saldato</i></a>';
				} else {
					echo '<a title="Ordine senza righi normali, es: solo decrittivi" disabled class="btn btn-xs btn-default pull-right" href=""><i class="glyphicon glyphicon-save-file">Descrittivo</i></a>';
				}
				echo '</div>';
				}
                echo "	</td>\n";
				// colonna mail
				echo '<td align="center">';
                if (!empty($fornitore["e_mail"])) {
                    echo ' <a class="btn btn-xs btn-default btn-email" onclick="confirmemail(\''.$r["clfoco"].'\',\''.$r['id_tes'].'\',false);" id="doc'.$r["id_tes"].'"><i class="glyphicon glyphicon-envelope"></i></a>';
                } else {
					echo '<a title="Non hai memorizzato l\'email per questo fornitore, inseriscila ora" target="_blank" href="admin_fornit.php?codice='.substr($r["clfoco"],3).'&Update"><i class="glyphicon glyphicon-edit"></i></a>';
				 }		  
                echo "	</td>\n";
				
				// colonna elimina
				echo "<td align=\"center\">";
				?>			
				<a class="btn btn-xs btn-default btn-elimina dialog_delete" ref="<?php echo $r['id_tes'];?>" catdes="<?php echo $fornitore['ragso1']; ?>">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
				<?php				
				echo "</td></tr>";
            }
            ?>
            <tr><th class="FacetFieldCaptionTD" colspan="12"></th></tr>
        </table>
    </div>
</form>
<div class="modal" id="confirm_email" title="Invia mail...">
    <fieldset>
        <div>
            <label id="maillabel" for="mailaddress">all'indirizzo:</label>
            <input type="text"  placeholder="seleziona sotto oppure digita" value="" id="mailaddress" name="mailaddress" maxlength="100" />
        </div>
        <div id="mailbutt">
		</div>
    </fieldset>
</div>
<div class="modal" id="confirm_duplicate" title="Duplica su nuovo preventivo">
    <fieldset>
        <div>
            <label for="duplicate">a:</label>
            <div class="supplier_name"></div>
        </div>
    </fieldset>
</div>
<?php
require("../../library/include/footer.php");
?>
