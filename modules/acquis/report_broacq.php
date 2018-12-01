<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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
} else {
	$flt_tipo='APR';
}

if (isset($_GET['auxil'])) {
    $auxil = filter_input(INPUT_GET, 'auxil');
} else {
    $auxil = 1;
}
$where = "tipdoc = '".$flt_tipo."' AND seziva = '$auxil'";
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
?>
<script>
$(function() {
   $( "#dialog" ).dialog({
      autoOpen: false
   });
});

function confirmemail(cod_partner,id_tes) {
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
		buttons: {
			Invia: function() {
				if ( !( emailRegex.test( $("#mailaddress").val() ) ) ) {
					alert('Mail formalmente errata');
				} else {
					$("#mailbutt div").remove();
					var dest=$("#mailaddress").val();
                    window.location.href = 'stampa_prefor.php?id_tes='+id_tes+'&dest='+dest;
				}

				}
		},
		close: function(){
				$("#mailbutt div").remove();
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
					Duplica: function() {
						window.location.href = 'duplicate_broacq.php?id_tes='+row+'&duplicate='+ui.item.codice;
						}
					},
				close: function(){}
				});
			}		
	});
}
</script>

<form method="GET">
    <input type="hidden" name="flt_tipo" value="<?php echo $flt_tipo;?>">
    <div align="center" class="FacetFormHeaderFont"> <?php echo $script_transl['title_dist'][$flt_tipo]; ?>
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
                    <?php gaz_flt_disp_int("id_tes", "ID"); ?>
                    <!--<input placeholder="Cerca Numero" class="input-xs form-control" type="text" name="numdoc" value="<?php if (isset($documento) && $documento > 0) print $documento; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">-->
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("id_orderman", "Produzione"); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("numdoc", "numdoc", $what, $all, $orderby); ?>
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
                if ($r["tipdoc"] == 'APR') {
                    $tipodoc="Preventivo";
                    $modulo="stampa_prefor.php?id_tes=".$r['id_tes'];
                    $modifi="admin_broacq.php?id_tes=".$r['id_tes']."&Update";
                }
                if ($r["tipdoc"] == 'AOR') {
                    $tipodoc="Ordine";
                    $modulo="stampa_ordfor.php?id_tes=".$r['id_tes'];
                    $modifi="admin_broacq.php?id_tes=".$r['id_tes']."&Update";
                }
                $fornitore = $anagrafica->getPartner($r['clfoco']);
                echo '<tr class="FacetDataTD text-center">';
                if (! empty ($modifi)) {
                   echo "<td>
				   			<a class=\"btn btn-xs btn-default\" href=\"".$modifi."\">
								<i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$r["id_tes"]."
							</a>
						 </td>";
                } else {
                   echo "<td>
				   			<button class=\"btn btn-xs btn-default disabled\">".$r["id_tes"]." ".$tipodoc." &nbsp;</button>
						</td>";
                }
                        // per colonna stato ordine e produzione
						$orderman_descr='';
                        $rigbro_result = gaz_dbi_dyn_query('*', $gTables['rigbro']." LEFT JOIN ".$gTables['orderman']." ON ".$gTables['rigbro'].".id_orderman = ".$gTables['orderman'].".id", "id_tes = " . $r["id_tes"] . " AND tiprig <=1 ", 'id_tes DESC');
                        while ( $rigbro_r = gaz_dbi_fetch_array($rigbro_result) ) {
							if ($rigbro_r['id_orderman']>0){
								$orderman_descr=$rigbro_r['id_orderman'].'-'.$rigbro_r['description'];
							}
                        }
                echo '			<td>'.$orderman_descr." &nbsp;</td>
						<td><a class=\"btn btn-xs btn-success\" href=\"".$modifi."\"><i class=\"glyphicon glyphicon-edit\"> ".$tipodoc." n.".$r["numdoc"]."</i> &nbsp;</a></td>
						<td>".gaz_format_date($r["datemi"])." &nbsp;</td>
						<td><a title=\"Dettagli fornitore\" href=\"report_fornit.php?auxil=" . htmlspecialchars($fornitore["ragso1"]) . "&search=Cerca\">".$fornitore["ragso1"]."&nbsp;</a></td>";
                        echo '<td><a class="btn btn-xs btn-warning" onclick="confirmorder(\''.$r['id_tes'].'\');">Ordina</a></td>';
                        echo "<td align=\"center\">
							<a class=\"btn btn-xs btn-default\" href=\"".$modulo."\" target=\"_blank\">
								<i class=\"glyphicon glyphicon-print\"></i>
							</a>
						</td>";
						echo '<td align="center" title="Stesso preventivo per altro fornitore"><button class="btn btn-default btn-sm" type="button" data-toggle="collapse" data-target="#duplicate_'.$r['id_tes'].'" aria-expanded="false" aria-controls="duplicate_'.$r['id_tes'].'"><i class="glyphicon glyphicon-tags"></i></button>';
                        echo '<div class="collapse" id="duplicate_'.$r['id_tes'].'">Fornitore: <input id="search_partner'.$r['id_tes'].'" onClick="choicePartner(\''.$r['id_tes'].'\');"  value="" rigo="'. $r['id_tes'] .'" type="text" /></div></td><td align="center">';
                if (!empty($fornitore["e_mail"])) {
                    echo ' <a class="btn btn-xs btn-default btn-email" onclick="confirmemail(\''.$r["clfoco"].'\',\''.$r['id_tes'].'\');" id="doc'.$r["id_tes"].'"><i class="glyphicon glyphicon-envelope"></i></a>';
                } else {
					echo '<a title="Non hai memorizzato l\'email per questo fornitore, inseriscila ora" target="_blank" href="admin_fornit.php?codice='.substr($r["clfoco"],3).'&Update"><i class="glyphicon glyphicon-edit"></i></a>';
				 }		  
                echo "	</td>
						<td align=\"center\">
							<a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_broacq.php?id_tes=".$r['id_tes']."\">
								<i class=\"glyphicon glyphicon-remove\"></i>
							</a>
						</td>
					  </tr>";
            }
            ?>
            <tr><th class="FacetFieldCaptionTD" colspan="12"></th></tr>
        </table>
    </div>
</form>
<div class="modal" id="confirm_email" title="Invia mail...">
    <fieldset>
        <div>
            <label for="mailaddress">all'indirizzo:</label>
            <input type="text"  placeholder="seleziona sotto oppure digita" value="" id="mailaddress" name="mailaddress" maxlength="50" />
        </div>
        <div id="mailbutt">
		</div>
    </fieldset>
</div>
<div class="modal" id="confirm_duplicate" title="Duplica preventivo">
    <fieldset>
        <div>
            <label for="duplicate">a:</label>
            <div class="supplier_name"><div/>
        </div>
    </fieldset>
</div>
<?php
require("../../library/include/footer.php");
?>