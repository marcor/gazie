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
// se l'utente non ha alcun registratore di cassa associato nella tabella cash_register non pu� emettere scontrini
$gForm = new venditForm();
$ecr_user = gaz_dbi_get_row($gTables['cash_register'], 'adminid', $admin_aziend["user_name"]);
$ecr = $gForm->getECR_userData($admin_aziend["user_name"]);
if (!$ecr_user) {
	$ecr=array('id_cash'=>0,'seziva'=>1,'descri'=>'File XML');
}
$lot = new lotmag();

function getLastId($date, $seziva) {
    global $gTables;
    // ricavo l'ultimo id del giorno
    $rs_last = gaz_dbi_dyn_query("id_tes", $gTables['tesdoc'], "tipdoc = 'VCO' AND datemi = '" . $date . "' AND seziva = " . intval($seziva), 'numdoc DESC', 0, 1);
    $last = gaz_dbi_fetch_array($rs_last);
    $id = 0;
    if ($last) {
        $id = $last['id_tes'];
    }
    return $id;
}


$where = "tipdoc = 'VCO' AND seziva = " . $ecr['seziva'];
$all = $where;
if (isset($_GET['all'])) {
    gaz_set_time_limit(0);
    $passo = 100000;
}
require("../../library/include/header.php");
$script_transl = HeadMain();
echo '<script>
$(function() {
   $( "#dialog1" ).dialog({
      autoOpen: false
   });

});

function confirFae(link){
	tes_id = link.id.replace("doc1", "");
	$.fx.speeds._default = 500;
	var new_title = "Genera file XML per fattura n." + $("#doc1"+tes_id).attr("n_fatt");
	var n_reinvii = parseInt($("#doc1"+tes_id).attr("fae_n_reinvii"))+1;
	$("p#fae1").html("nome file: " + $("#doc1"+tes_id).attr("fae_attuale"));
	$("span#fae2").html("<a href=\'"+link.href+"&reinvia\'> " + $("#doc1"+tes_id).attr("fae_reinvio")+ " (" + n_reinvii.toString() + "° reinvio) </a>");
	$("#dialog1").dialog({
	  title: new_title,
      modal: "true",
      show: "blind",
      hide: "explode",
      buttons: {
                      " ' . $script_transl['submit'] . ' ": function() {
                         window.location.href = link.href;
                          $(this).dialog("close");
                      },
                      " ' . $script_transl['cancel'] . ' ": function() {
                        $(this).dialog("close");
                      }
               }
         });
	$("#dialog1").dialog( "open" );
}

</script>';

$gForm = new GAzieForm();
echo "<form method=\"GET\" name=\"report\">\n";
?>
    <div style="display:none" id="dialog1" title="<?php echo $script_transl['fae_alert0']; ?>">
        <p id="fae_alert1"><?php echo $script_transl['fae_alert1']; ?></p>
        <p class="ui-state-highlight" id="fae1"></p>
        <p id="fae_alert2"><?php echo $script_transl['fae_alert2']; ?><span id="fae2" class="bg-warning"></span></p>
    </div>
<?php
echo "<input type=\"hidden\" name=\"hidden_req\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'] . $script_transl['seziva'];
echo $ecr['seziva'];
echo "</div>\n";
if (!isset($_GET['field']) || $_GET['field'] == 2 || empty($_GET['field'])) {
    $orderby = "datemi DESC, id_con ASC, numdoc DESC";
}

gaz_flt_var_assign('id_tes', 'i');
gaz_flt_var_assign('datemi', 'd');
gaz_flt_var_assign('numdoc', 'i');
//gaz_flt_var_assign('clfoco','v' );

if (isset($_GET['all'])) {
    $_GET['id_tes'] = "";
    $_GET['datemi'] = "";
    $_GET['numdoc'] = "";
    //$_GET['clfoco']="";
    $where = $all;
    $auxil = "&all=yes";
}

$recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
$recordnav->output();
?>
<div class="box-primary table-responsive">
<table class="Tlarge table table-striped table-bordered">
    <tr>
        <td class="FacetFieldCaptionTD" colspan="1">
<?php gaz_flt_disp_int("id_tes", "Numero Id"); ?>
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
            <?php gaz_flt_disp_select("datemi", "YEAR(datemi) as datemi", $gTables["tesdoc"], $all, $orderby); ?>
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
            <?php gaz_flt_disp_int("numdoc", "Numero Doc."); ?>
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
            <input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
        </td>
        <td class="FacetFieldCaptionTD" colspan="1">
            <input type="submit" class="btn btn-default btn-sm" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value = 1;">
        </td>
    </tr>
    <tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array(
    $script_transl['id'] => "id_tes",
    $script_transl['date'] => "datemi",
    $script_transl['number'] => "numdoc",
    $script_transl['invoice'] => "clfoco",
    $script_transl['pagame'] => "",
    $script_transl['status'] => "",
    $script_transl['amount'] => "",
    'Cert.' => "",
    $script_transl['delete'] => "",
    '' => ""
);
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders->output();
?>
    </tr>
        <?php
//recupero le testate in base alle scelte impostate
        $result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], $where, $orderby, $limit, $passo);
        $anagrafica = new Anagrafica();
        $tot = 0;
        while ($row = gaz_dbi_fetch_array($result)) {
            $cast_vat = array();
            $cast_acc = array();
            $tot_tes = 0;
            $pagamento = gaz_dbi_get_row($gTables['pagame'], 'codice', $row['pagame']);

            //recupero i dati righi per creare i castelletti
            $rs_rig = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = " . $row['id_tes'], "id_rig");
            while ($v = gaz_dbi_fetch_array($rs_rig)) {
                if ($v['tiprig'] <= 1) {    //ma solo se del tipo normale o forfait
                    if ($v['tiprig'] == 0) { // tipo normale
                        $tot_row = CalcolaImportoRigo($v['quanti'], $v['prelis'], array($v['sconto'], $row['sconto'], -$v['pervat']));
                    } else {                 // tipo forfait
                        $tot_row = CalcolaImportoRigo(1, $v['prelis'], -$v['pervat']);
                    }
                    if (!isset($cast_vat[$v['codvat']])) {
                        $cast_vat[$v['codvat']]['totale'] = 0.00;
                        $cast_vat[$v['codvat']]['imponi'] = 0.00;
                        $cast_vat[$v['codvat']]['impost'] = 0.00;
                        $cast_vat[$v['codvat']]['periva'] = $v['pervat'];
                    }
                    $cast_vat[$v['codvat']]['totale']+=$tot_row;
                    // calcolo il totale del rigo stornato dell'iva
                    $imprig = round($tot_row / (1 + ($v['pervat'] / 100)), 2);
                    $cast_vat[$v['codvat']]['imponi']+=$imprig;
                    $cast_vat[$v['codvat']]['impost']+=$tot_row - $imprig;
                    $tot+=$tot_row;
                    $tot_tes+=$tot_row;
                    // inizio AVERE
                    if (!isset($cast_acc[$admin_aziend['ivacor']]['A'])) {
                        $cast_acc[$admin_aziend['ivacor']]['A'] = 0;
                    }
                    $cast_acc[$admin_aziend['ivacor']]['A']+=$tot_row - $imprig;
                    if (!isset($cast_acc[$v['codric']]['A'])) {
                        $cast_acc[$v['codric']]['A'] = 0;
                    }
                    $cast_acc[$v['codric']]['A']+=$imprig;
                    // inizio DARE
                    if ($row['clfoco'] > 100000000) { // c'� un cliente selezionato
                        if (!isset($cast_acc[$row['clfoco']]['D'])) {
                            $cast_acc[$row['clfoco']]['D'] = 0;
                        }
                        $cast_acc[$row['clfoco']]['D']+=$tot_row;
                    } else {  // il cliente � anonimo lo passo direttamente per cassa
                        if (!isset($cast_acc[$admin_aziend['cassa_']]['D'])) {
                            $cast_acc[$admin_aziend['cassa_']]['D'] = 0;
                        }
                        $cast_acc[$admin_aziend['cassa_']]['D']+=$tot_row;
                    }
                }
            }
            $doc['all'][] = array('tes' => $row,
                'vat' => $cast_vat,
                'acc' => $cast_acc,
                'tot' => $tot_tes);
            if ($row['clfoco'] > 100000000) {
                $doc['invoice'][] = array('tes' => $row,
                    'vat' => $cast_vat,
                    'acc' => $cast_acc,
                    'tot' => $tot_tes);
            } else {
                $doc['ticket'][] = array('tes' => $row,
                    'vat' => $cast_vat,
                    'acc' => $cast_acc,
                    'tot' => $tot_tes);
            }
            // ************* FINE CREAZIONE TOTALI SCONTRINO ***************
            if ($row['id_con'] > 0) {
                $status = $script_transl['status_value'][1];
            } else {
                $status = $script_transl['status_value'][0];
            }
            if ($row['numfat'] > 0) {
                $cliente = $anagrafica->getPartner($row['clfoco']);
                $modulo_fae = "electronic_invoice.php?id_tes=" . $row['id_tes'];
				$row['fae_attuale']="IT" . $admin_aziend['codfis'] . "_".encodeSendingNumber(array('azienda' => $admin_aziend['codice'],
								'anno' => $row["datfat"],
								'sezione' => $row["seziva"],
								'fae_reinvii'=> $row["fattura_elettronica_reinvii"]+4,
								'protocollo' => $row["numfat"]), 36).".xml";
				$row['fae_reinvio']="IT" . $admin_aziend['codfis'] . "_".encodeSendingNumber(array('azienda' => $admin_aziend['codice'],
								'anno' => $row["datfat"],
								'sezione' => $row["seziva"],
								'fae_reinvii'=> intval($row["fattura_elettronica_reinvii"]+5),
								'protocollo' => $row["numfat"]), 36).".xml";
                $invoice = "<a href=\"stampa_docven.php?id_tes=" . $row['id_tes'] . "&template=FatturaAllegata\" class=\"btn btn-xs btn-default\" title=\"Stampa\" target=\"_blank\">n." . $row['numfat'] . " del " . gaz_format_date($row['datfat']) . ' a ' . $cliente['ragso1'] . "&nbsp;<i class=\"glyphicon glyphicon-print\"></i></a>\n";
				$invoice .= '<a class="btn btn-xs btn-default btn-xml" onclick="confirFae(this);return false;" id="doc1" '.$row["id_tes"].'" fae_reinvio="'.$row["fae_reinvio"].'" fae_attuale="'.$row["fae_attuale"].'" fae_n_reinvii="'.$row["fattura_elettronica_reinvii"].'" n_fatt="'. $row["numfat"]."/". $row["seziva"].'/SCONTR" target="_blank" href="'.$modulo_fae.'" title="genera il file '.$row["fae_attuale"].' o fai il '.intval($row["fattura_elettronica_reinvii"]+1).'° reinvio ">xml</a><a class="btn btn-xs btn-default" title="Visualizza in stile www.fatturapa.gov.it" href="electronic_invoice.php?id_tes='.$row['id_tes'].'&viewxml"><i class="glyphicon glyphicon-eye-open"></i> </a>';
				if(strlen($row["fattura_elettronica_zip_package"])>10){
					$invoice.='<a class="btn btn-xs btn-edit" title="Pacchetto di fatture elettroniche in cui è contenuta questa fattura" href="download_zip_package.php?fn='.$row['fattura_elettronica_zip_package'].'">zip <i class="glyphicon glyphicon-compressed"></i> </a>';
				}
            } else {
                $invoice = '';
            }

            echo "<tr class=\"FacetDataTD\">";
            // Colonna ID scontrino
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"admin_scontr.php?Update&id_tes=" . $row['id_tes'] . "\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . $row["id_tes"] . "</a></td>";
            // Colonna data emissione
            echo "<td align=\"center\">" . gaz_format_date($row['datemi']) . "</td>";
            // Colonna numero documento
            echo "<td align=\"center\">" . $row["numdoc"] . " &nbsp;</td>";
            // Colonna fattura
            echo "<td align=\"center\">$invoice</td>";
            // Colonna pagamento
            echo "<td align=\"center\">" . $pagamento["descri"] . " &nbsp;</td>";
            // Colonna stato
            echo "<td align=\"center\">";
            if ($row["id_con"] > 0) {
                echo " <a class=\"btn btn-xs btn-default\" style=\"font-size:10px;\" title=\"Modifica il movimento contabile generato da questo documento\" href=\"../contab/admin_movcon.php?id_tes=" . $row["id_con"] . "&Update\">Cont." . $row["id_con"] . "</a> ";
				if(strlen($row["fattura_elettronica_original_name"])>10){
					echo " <a class=\"btn btn-xs btn-info\" title=\"Dato contenuto nel file\" href=\"download_zip_package.php?fn=" . $row["fattura_elettronica_original_name"] . "\"><small>" . $row["fattura_elettronica_original_name"] . "</small></a> ";
				}
            } else {
                echo " <a class=\"btn btn-xs btn-default btn-cont\" href=\"close_ecr.php\"><i class=\"glyphicon glyphicon-euro\"></i>&nbsp;Contabilizza</a>";
            }
            echo "&nbsp;</td>";
             // Colonna importo
           echo '<td align="right" style="font-weight=bolt;">';
            echo gaz_format_number($tot_tes);
            echo "\t </td>\n";
            // Colonna certificato
            echo "<td align=\"center\">";
            if ($lot->thereisLot($row['id_tes'])) {
                    echo "<a class=\"btn btn-xs btn-default\" title=\"" . $script_transl['print_lot'] . "\" href=\"lotmag_print_cert.php?id_tesdoc=" . $row['id_tes'] . "\" style=\"font-size:10px;\">Cert.<i class=\"glyphicon glyphicon-tags\"></i></a>\n";
            }            
            // Colonna Elimina
            echo "</td>";
            if ($row["id_con"] == 0) {
                if (getLastId($row['datemi'], $row['seziva']) == $row["id_tes"]) {
                    echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_docven.php?id_tes=" . $row['id_tes'] . "\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
                } else {
                    echo "<td align=\"center\"><button class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button></td>";
                }
            } else {
                echo "<td align=\"center\"><button class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button></td>";
            }
            // Colonna invia a ECR
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-primary btn-ecr\" href=\"resend_to_ecr.php?id_tes=" . $row['id_tes'] . "\" >" . $script_transl['send'] . "</a>";
            echo "</tr>\n";
        }
        ?>
    <th colspan="9" class="FacetFieldCaptionTD"></th>
</form>
</table>
</div>
<?php
require("../../library/include/footer.php");
?>