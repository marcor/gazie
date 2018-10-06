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
$admin_aziend=checkAdmin();

$message = "";
$anno = date("Y");
require("../../library/include/header.php");
$script_transl=HeadMain(0,array('custom/modal_form'));

$where = "tipdoc like 'A_R'";
$passo = 99999;
$orderby = "id_tes DESC";
$all = $where;

if (isset($_GET['flt_tipo']) && $_GET['flt_tipo']!= "All") {
    if ($_GET['flt_tipo'] != "") {
        $tipdoc = $_GET['flt_tipo'];
        $auxil .= "&flt_tipo=" . $tipdoc;
        $where = " tipdoc like '%$tipdoc%'";
        $passo = 9999;
    }
} else {
    $tipdoc = '';
}
if (isset($_GET['protoc'])) {
    if ($_GET['protoc'] != "") {
        $protocollo = $_GET['protoc'];
        $where .= " and id_tes = $protocollo";
        $passo = 9999;
    }
} else {
    $tipdoc = '';
}
if (isset($_GET['numdoc'])) {
    if ($_GET['numdoc'] != "") {
        $numdoc = $_GET['numdoc'];
        $where .= " AND numdoc like '%$numdoc%'";
        $passo = 9999;
    }
} else {
    $numfat = '';
}
if (isset($_GET['flt_year'])) {
	if ($_GET['flt_year'] != "" && $_GET['flt_year']!= "All") {
        $year = $_GET['flt_year'];
		$where .= " and datemi >= \"".$year."/01/01\" and datemi <= \"".$year."/12/31\"";
        $passo = 9999;
    } else {
		$year = 'All';
	}
} else {
	$year = 'All';    
}
if (isset($_GET['flt_ragso1'])) {
    if ($_GET['flt_ragso1'] != "") {
        $ragso1 = $_GET['flt_ragso1'];
		if ($ragso1!="All") {
			$where .= " and ".$gTables["tesbro"].".clfoco = ".$ragso1;
		}
        $passo = 9999;
    }
} else {
    $ragso1 = '';
}
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl["title2"]; ?></div>
<?php
$recordnav = new recordnav($gTables['tesbro'], $where, $limit, $passo);
$recordnav -> output();
?>
<form method="GET" >
<div id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
      <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
      <p class="ui-state-highlight" id="mail_adrs"></p>
      <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
      <p class="ui-state-highlight" id="mail_attc"></p>
</div>
    <div class="box-primary table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
<script>
$(function() {
   $( "#dialog" ).dialog({
      autoOpen: false
   });
});
function confirMail(link){
   tes_id = link.id.replace("doc", "");
   $.fx.speeds._default = 500;
   targetUrl = $("#doc"+tes_id).attr("url");
   //alert (targetUrl);
   $("p#mail_adrs").html($("#doc"+tes_id).attr("mail"));
   $("p#mail_attc").html($("#doc"+tes_id).attr("namedoc"));
   $( "#dialog" ).dialog({
         modal: "true",
      show: "blind",
      hide: "explode",
         buttons: {
                      " <?php echo $script_transl['submit']; ?> ": function() {
                         window.location.href = targetUrl;
                      },
                      " <?php echo $script_transl['cancel']; ?>": function() {
                        $(this).dialog("close");
                      }
                  }
         });
   $("#dialog" ).dialog( "open" );
}
</script>

<!-- inizio il filtro ricerca -->
<tr>
    <td colspan="1" class="FacetFieldCaptionTD">
        <input type="text" placeholder="Cerca Prot." class="input-sm form-control" name="protoc" value="<?php if (isset($protocollo)) print $protocollo; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
    </td>
	<td colspan="1" class="FacetFieldCaptionTD">
		<select class="form-control input-sm" name="flt_tipo" onchange="this.form.submit()">
			<option value="All"><?php echo $script_transl['tuttitipi']; ?></option>
			<?php 
				$res = gaz_dbi_dyn_query("distinct tipdoc", $gTables["tesbro"], $all, $orderby, 0, 999);
				while ( $val = gaz_dbi_fetch_array($res) ) {
					if ( $tipdoc == $val["tipdoc"] ) $selected = "selected";
					else $selected = "";
					echo "<option value=\"".$val["tipdoc"]."\" ".$selected.">".$val["tipdoc"]."</option>";
				}
			?>
		</select>
	</td>
    <td colspan="1" class="FacetFieldCaptionTD">
		<input type="text" placeholder="Cerca Num." class="input-sm form-control" name="numdoc" value="<?php if (isset($numdoc)) print $numdoc; ?>" size="3" tabindex="3" class="FacetInput">			
	</td>
	<td colspan="1" class="FacetFieldCaptionTD">
		<select class="form-control input-sm" name="flt_year" onchange="this.form.submit()">
		<option value="All"><?php echo $script_transl['tuttianni']; ?></option>
		<?php $res = gaz_dbi_dyn_query("distinct YEAR(datemi) as year", $gTables["tesbro"], $all, $orderby, 0, 999);
			while ( $val = gaz_dbi_fetch_array($res) ) {
				if ( $year == $val["year"] ) $selected = "selected";
				else $selected = "";
				echo "<option value=\"".$val["year"]."\" ".$selected.">".$val["year"]."</option>";
			} ?>
		</select>
	</td>
	<td colspan="1" class="FacetFieldCaptionTD">
		<select class="form-control input-sm" name="flt_ragso1" onchange="this.form.submit()">
		<option value="All"><?php echo $script_transl['tuttiforni']; ?></option>
		<?php $res = gaz_dbi_dyn_query("distinct ".$gTables['anagra'].".ragso1,".$gTables["tesbro"].".clfoco", $gTables['tesbro'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesbro'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $all, $orderby, 0, 999);
			while ( $val = gaz_dbi_fetch_array($res) ) {
				if ( $ragso1 == $val["clfoco"] ) $selected = "selected";
				else $selected = "";
				echo "<option value=\"".$val["clfoco"]."\" ".$selected.">".$val["ragso1"]."</option>";
			} ?>
		</select>
	</td>
	<td colspan="1" class="FacetFieldCaptionTD">
		&nbsp;
		<?php //gaz_filtro( "status", $_GET["status"], $gTables["tesbro"], $all, $orderby, "select" ); ?>
	</td>
	<td colspan="1" class="FacetFieldCaptionTD">
		&nbsp;
	</td>
	<td colspan="1" class="FacetFieldCaptionTD">
        <input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
    </td>
    <td colspan="1" class="FacetFieldCaptionTD">
        <input type="submit" class="btn btn-sm btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;">
    </td>
</tr>

<tr>
<?php
$headers_tesdoc = array  (
              "ID" => "id_tes",
              "Tipo" => "tipdoc",
              "Numero" => "numdoc",
              "Data" => "datemi",
              "Fornitore" => "clfoco",
              "Status" => "",
              "Stampa" => "",
              "Mail" => "",
              "Cancella" => ""
              );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
if (!isset($_GET['flag_order']))
       $orderby = "id_tes desc";
$result = gaz_dbi_dyn_query ($gTables['tesbro'].".*, ".$gTables['clfoco'].".codice", $gTables['tesbro']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesbro'].".clfoco = ".$gTables['clfoco'].".codice", $where, $orderby, $limit, $passo);
$ctrlprotoc = "";
$anagrafica = new Anagrafica();
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
    echo "<tr class=\"FacetDataTD\">";
    if (! empty ($modifi)) {
       echo "<td>
	   			<a class=\"btn btn-xs btn-default\" href=\"".$modifi."\">
					<i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$r["id_tes"]."
				</a>
			 </td>";
    } else {
       echo "<td>
	   			<button class=\"btn btn-xs btn-default disabled\">".$r["id_tes"]." &nbsp;</button>
			</td>";
    }
    echo "	<td>".$tipodoc." &nbsp;</td>
			<td>".$r["numdoc"]." &nbsp;</td>
			<td>".gaz_format_date($r["datemi"])." &nbsp;</td>
			<td>".$fornitore["ragso1"]."&nbsp;</td>";
			//<td>".$r["status"]." &nbsp;</td>
            
            

            // colonna stato ordine
            $remains_atleastone = false; // Almeno un rigo e' rimasto da evadere.
            $processed_atleastone = false; // Almeno un rigo e' gia' stato evaso.  
            $rigbro_result = gaz_dbi_dyn_query('*', $gTables['rigbro'], "id_tes = " . $r["id_tes"] . " AND tiprig <=1 ", 'id_tes DESC');
            while ( $rigbro_r = gaz_dbi_fetch_array($rigbro_result) ) {           
                $totale_da_evadere = $rigbro_r['quanti'];
                $totale_evaso = 0;
                $tesdoc_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "id_order='".$r['id_tes']."'", 'id_tes DESC');
                while ( $tesdoc_r = gaz_dbi_fetch_array($tesdoc_result) ) {
                    $rigdoc_result = gaz_dbi_dyn_query('*', $gTables['rigdoc'], "id_tes=" . $tesdoc_r['id_tes'] . " AND codart='".$rigbro_r['codart']."' AND tiprig <=1 ", 'id_tes DESC');
                    while ($rigdoc_r = gaz_dbi_fetch_array($rigdoc_result)) {
                        $totale_evaso += $rigdoc_r['quanti'];
                        $processed_atleastone = true;
                    }
                }
                if ( $totale_evaso != $totale_da_evadere ) {
                    //echo $totale_evaso ." -> ". $totale_da_evadere."<br>";
                    $remains_atleastone = true;
                }
            }
            //
            // Se l'ordine e' da evadere completamente, verifica lo status ed
            // eventualmente lo aggiorna.
            //
            if ($remains_atleastone && !$processed_atleastone) {
                //
                // L'ordine e' completamente da evadere.
                //
                if ($r["status"] != "GENERATO") {
                    gaz_dbi_put_row($gTables['tesbro'], "id_tes", $r["id_tes"], "status", "RIGENERATO");
                }
                echo "<td><a class=\"btn btn-xs btn-warning\" href=\"select_evaord.php?id_tes=" . $r['id_tes'] . "\">evadi</a></td>";

            } elseif ($remains_atleastone) {
                echo "<td> ";

                $ultimo_documento = 0;
                //
                // Interroga la tabella gaz_XXXrigbro per le righe corrispondenti
                // a questa testata.
                //
                $rigbro_result = gaz_dbi_dyn_query('*', $gTables['rigbro'], "id_tes = " . $r["id_tes"], 'id_tes DESC');
                //
                while ($rigbro_r = gaz_dbi_fetch_array($rigbro_result)) {
                    if ($rigbro_r["id_doc"] == 0) {
                        continue;
                    } else {
                        if ($ultimo_documento == $rigbro_r["id_doc"]) {
                            continue;
                        } else {
                            //
                            // Individua il documento.
                            //
                    $tesdoc_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "id_tes = " . $rigbro_r["id_doc"], 'id_tes DESC');
                            #
                            $tesdoc_r = gaz_dbi_fetch_array($tesdoc_result);
                            #
                            if ($tesdoc_r["tipdoc"] == "FAI") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza la fattura immediata\" href=\"stampa_docven.php?id_tes=" . $rigbro_r["id_doc"] . "\">";
                                echo "fatt. " . $tesdoc_r["numfat"];
                                echo "</a> ";
                            } elseif ($tesdoc_r["tipdoc"] == "DDT") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza il documento di trasporto\" href=\"stampa_docven.php?id_tes=" . $rigbro_r["id_doc"] . "&template=DDT\">";
                                echo "ddt " . $tesdoc_r["numdoc"];
                                echo "</a> ";
                            } else {
                                echo $tesdoc_r["tipdoc"] . $rigbro_r["id_doc"] . " ";
                            }
                            $ultimo_documento = $rigbro_r["id_doc"];
                        }
                    }
                }
                echo "<a class=\"btn btn-xs btn-default\" href=\"select_evaord.php?id_tes=" . $r['id_tes'] . "\">evadi il rimanente</a></td>";
            } else {
                echo "<td> evaso";
                //
                $ultimo_documento = 0;
                //
                // Interroga la tabella gaz_XXXrigbro per le righe corrispondenti
                // a questa testata.
                //
        $rigbro_result = gaz_dbi_dyn_query('*', $gTables['rigbro'], "id_tes = " . $r["id_tes"], 'id_tes DESC');
                //
                while ($rigbro_r = gaz_dbi_fetch_array($rigbro_result)) {
                    if ($rigbro_r["id_doc"] == 0) {
                        continue;
                    } else {
                        if ($ultimo_documento == $rigbro_r["id_doc"]) {
                            continue;
                        } else {
                            //
                            // Individua il documento.
                            //
                    $tesdoc_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "id_tes = " . $rigbro_r["id_doc"], 'id_tes DESC');
                            $tesdoc_r = gaz_dbi_fetch_array($tesdoc_result);
                            if ($tesdoc_r["tipdoc"] == "AFA") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza la fattura immediata\" href=\"stampa_docacq.php?id_tes=" . $rigbro_r["id_doc"] . "\">";
                                echo "fatt. " . $tesdoc_r["numfat"];
                                echo "</a> ";
                            } elseif ($tesdoc_r["tipdoc"] == "DDT" || $tesdoc_r["tipdoc"] == "FAD") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza il documento di trasporto\" href=\"stampa_docacq.php?id_tes=" . $rigbro_r["id_doc"] . "&template=DDT\">";
                                echo "ddt " . $tesdoc_r["numdoc"];
                                echo "</a> ";
                            } elseif ($tesdoc_r["tipdoc"] == "VCO") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza lo scontrino come fattura\" href=\"stampa_docacq.php?id_tes=" . $rigbro_r["id_doc"] . "&template=FatturaAllegata\">";
                                echo "scontrino n." . $tesdoc_r["numdoc"] . "<br />del " . gaz_format_date($tesdoc_r["datemi"]);
                                echo "</a> ";
                            } else {
                                echo $tesdoc_r["tipdoc"] . $rigbro_r["id_doc"] . " ";
                            }
                            $ultimo_documento = $rigbro_r["id_doc"];
                        }
                    }
                }
                echo "</td>";
            }



            
            echo "<td align=\"center\">
				<a class=\"btn btn-xs btn-default\" href=\"".$modulo."\" target=\"_blank\">
					<i class=\"glyphicon glyphicon-print\"></i>
				</a>
			</td>
			<td align=\"center\">";
    if (!empty($fornitore["e_mail"])) {
        echo '<a class="btn btn-xs btn-default btn-email" onclick="confirMail(this);return false;" id="doc'.$r["id_tes"].'" url="'.$modulo.'&dest=E" href="#" title="mailto: '.$fornitore["e_mail"].'"
        mail="'.$fornitore["e_mail"].'" namedoc="'.$tipodoc.' n.'.$r["numdoc"].' del '.gaz_format_date($r["datemi"]).'"><i class="glyphicon glyphicon-envelope"></i></a>';
    } else {
		echo '<a title="Non hai memorizzato l\'email per questo fornitore, inseriscila ora" target="_blank" href="admin_fornit.php?codice='.substr($r["codice"],3).'&Update"><i class="glyphicon glyphicon-edit"></i></a>';
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
 <tr><th class="FacetFieldCaptionTD" colspan="10"></th></tr>
</table>
    </div>
</form>
<?php
require("../../library/include/footer.php");
?>