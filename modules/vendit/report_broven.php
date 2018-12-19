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

function getDayNameFromDayNumber($day_number) {
    return ucfirst(utf8_encode(strftime('%A', mktime(0, 0, 0, 3, 19+$day_number, 2017))));
}

function getDocRef($data) {
    global $gTables;
    $r = array();
    return $r;
}

// visualizza i bottoni dei documenti di evasione associati all'ordine
function mostra_documenti_associati($ordine) {
    global $gTables;
    // seleziono i documenti evasi che contengono gli articoli di questo ordine
    $rigdoc_result = gaz_dbi_dyn_query('DISTINCT id_tes', $gTables['rigdoc'], "id_order = " . $ordine, 'id_tes ASC');
    while ( $rigdoc = gaz_dbi_fetch_array($rigdoc_result) ) {
        // per ogni documento vado a leggere il numero documento
        $tesdoc_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "id_tes = " . $rigdoc["id_tes"], 'id_tes DESC');
        $tesdoc_r = gaz_dbi_fetch_array($tesdoc_result);
        // a seconda del tipo di documento visualizzo il bottone corrispondente
        if ($tesdoc_r["tipdoc"] == "FAI") {
            // fattura immediata
            echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza la fattura immediata\" href=\"stampa_docven.php?id_tes=" . $tesdoc_r["id_tes"] . "\">";
            echo "fatt. " . $tesdoc_r["numfat"];
            echo "</a> ";
        } elseif ($tesdoc_r["tipdoc"] == "DDT" || ($tesdoc_r["tipdoc"] == "FAD" && $tesdoc_r["ddt_type"]!='R')) {
            // documento di trasporto
            echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza il documento di trasporto\" href=\"stampa_docven.php?id_tes=" . $tesdoc_r["id_tes"] . "&template=DDT\">";
            echo "ddt " . $tesdoc_r["numdoc"];
            echo "</a> ";
        } elseif ($tesdoc_r["tipdoc"] == "CMR" || ($tesdoc_r["tipdoc"] == "FAD" && $tesdoc_r["ddt_type"]='R')) {
            // documento cmr
            echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza il cmr\" href=\"stampa_docven.php?id_tes=" . $tesdoc_r["id_tes"] . "&template=CMR\">";
            echo "cmr " . $tesdoc_r["numdoc"];
            echo "</a> ";
        } elseif ($tesdoc_r["tipdoc"] == "VCO") {
            // scontrino
            echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza lo scontrino come fattura\" href=\"stampa_docven.php?id_tes=" . $tesdoc_r["id_tes"] . "&template=FatturaAllegata\">";
            echo "scontr. " . $tesdoc_r["numdoc"] . "<br /> " . gaz_format_date($tesdoc_r["datemi"]);
            echo "</a> ";
        } else {
            echo $tesdoc_r["tipdoc"] . $rigbro_r["id_doc"] . " ";
        }
    }
}

if (isset($_GET['auxil'])) {
    $auxil = $_GET['auxil'];
    if ($_GET['auxil'] == 'VPR') {
        $what = 'VPR';
    } else if ($_GET['auxil'] == 'VOG') {
        $what = 'VOG'; 
    } else {
        $what = 'VOR';
    }
    $where = "tipdoc LIKE '$what'";
} else {
    $auxil = 'VOR';
    $_GET['auxil'] = 'VOR';
    $where = "tipdoc LIKE '$auxil'";
}

$all = $where;

gaz_flt_var_assign('id_tes', 'i');
gaz_flt_var_assign('numdoc', 'i');
if ( $what == "VOG" ) { 
    gaz_flt_var_assign('weekday_repeat', 'i');
} else {
    gaz_flt_var_assign('datemi', 'd');
}
gaz_flt_var_assign('clfoco', 'v');
gaz_flt_var_assign("unita_locale1", 'v');

if (isset($_GET['all'])) {
    $_GET['id_tes'] = "";
    $_GET['numdoc'] = "";
    $_GET['datemi'] = "";
    $_GET['clfoco'] = "";
    $_GET['unita_locale1'] = "";
    $auxil = $_GET['auxil'] . "&all=yes";
    if ($_GET['auxil'] == 'VPR') {
        $what = 'VPR';
    } else {
        $what = substr($auxil, 0, 2) . "_";
    }
    $where = "tipdoc LIKE '$what'";
    $passo = 100000;
    $numero = '';
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/modal_form'));

echo '<script>
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
                      " ' . $script_transl['submit'] . ' ": function() {
                         window.location.href = targetUrl;
                      },
                      " ' . $script_transl['cancel'] . ' ": function() {
                        $(this).dialog("close");
                      }
                  }
         });
   $("#dialog" ).dialog( "open" );
}
</script>';

if (!isset($_GET['flag_order']))
    $orderby = "datemi DESC, numdoc DESC";
$a = substr($auxil, 0, 3);
?>

<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title_value'][$_GET['auxil']]; ?></div>
<?php
$recordnav = new recordnav($gTables['tesbro'], $where, $limit, $passo);
$recordnav->output();
?>
<form method="GET" >
    <input type="hidden" name="auxil" value="<?php echo substr($_GET['auxil'], 0, 3); ?>">
    <div style="display:none" id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
        <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
        <p class="ui-state-highlight" id="mail_adrs"></p>
        <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
        <p class="ui-state-highlight" id="mail_attc"></p>
    </div>
    <div class="box-primary table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
        <tr>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_int("id_tes", "Numero Prot."); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_int("numdoc", "Numero Doc."); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <?php 
                    if ( $what=="VOG" ) {
                        ?>
                            <select class="form-control input-sm" onchange="this.form.submit()" name="weekday_repeat">
			                <?php
			                   if ( isset($_GET['weekday_repeat']) ) $gg = $_GET['weekday_repeat'];
			                   else $gg = 'All';
			                ?>
			                <option value="All" <?php if ($gg=='All') echo "selected"; ?>>Tutti</option>
			                <option value="0" <?php if ($gg=='0') echo "selected"; ?>>Domenica</option>
			                <option value="1" <?php if ($gg=='1') echo "selected"; ?>>Lunedi</option>
			                <option value="2" <?php if ($gg=='2') echo "selected"; ?>>Martedi</option>
			                <option value="3" <?php if ($gg=='3') echo "selected"; ?>>Mercoledi</option>
			                <option value="4" <?php if ($gg=='4') echo "selected"; ?>>Giovedi</option>
			                <option value="5" <?php if ($gg=='5') echo "selected"; ?>>Venerdi</option>
			                <option value="6" <?php if ($gg=='6') echo "selected"; ?>>Sabato</option>
			                </select>
                        <?php
                    } else {
                        gaz_flt_disp_select("datemi", "YEAR(datemi) as datemi", $gTables["tesbro"], $all, $orderby); 
                    }
                ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_select("clfoco", $gTables['anagra'] . ".ragso1," . $gTables["tesbro"] . ".clfoco", $gTables['tesbro'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesbro'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['clfoco'] . ".id_anagra = " . $gTables['anagra'] . ".id", $all, $orderby, "ragso1"); ?>
            </td>
            <td class=FacetFieldCaptionTD>
                <?php gaz_flt_disp_select("unita_locale1", $gTables['destina'].".unita_locale1", $gTables['tesbro'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesbro'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['clfoco'] . ".id_anagra = " . $gTables['anagra'] . ".id left join ". $gTables['destina']." on " .$gTables['tesbro'].".id_des_same_company = " . $gTables['destina'] . ".codice" , $all, $orderby, "unita_locale1"); ?>
            </td>
            <td class=FacetFieldCaptionTD>
                &nbsp;
            </td>
            <td class=FacetFieldCaptionTD>
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="search" value="<?php echo $script_transl['search']; ?>" tabindex="1" onClick="javascript:document.report.all.value = 1;">
            </td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value = 1;">
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
        </tr>
        <tr>
            <?php
            // creo l'array (header => campi) per l'ordinamento dei record
            if ( $what=="VOG" ) {
                $headers_tesbro = array(
                "ID" => "id_tes",
                //$script_transl['type'] => "tipdoc",
                $script_transl['number'] => "numdoc",
                $script_transl['weekday_repeat'] => "weekday_repeat",
                "Cliente" => "clfoco",
                "Destinazione" => "unita_locale1",
                $script_transl['status'] => "status",
                $script_transl['print'] => "",
                "Mail" => "",
                $script_transl['duplicate'] => "",
                $script_transl['delete'] => ""
                );
            } else {
                $headers_tesbro = array(
                "ID" => "id_tes",
                //$script_transl['type'] => "tipdoc",
                $script_transl['number'] => "numdoc",
                $script_transl['date'] => "datemi",
                "Cliente" => "clfoco",
                "Destinazione" => "unita_locale1",
                $script_transl['status'] => "status",
                $script_transl['print'] => "",
                "Mail" => "",
                $script_transl['duplicate'] => "",
                $script_transl['delete'] => ""
                );
            }
            $linkHeaders = new linkHeaders($headers_tesbro);
            $linkHeaders->output();
            ?>
        </tr>
        <?php
        //recupero le testate in base alle scelte impostate
        $result = gaz_dbi_dyn_query($gTables['tesbro'] . ".*," . $gTables['anagra'] . ".ragso1," . $gTables['anagra'] . ".e_mail AS base_mail," . $gTables["clfoco"] . ".codice, ".$gTables["destina"].".*", $gTables['tesbro'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesbro'] . ".clfoco = " . $gTables['clfoco'] . ".codice  LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id  left join '. $gTables['destina'].' on ' .$gTables['tesbro'].'.id_des_same_company = ' . $gTables['destina'] . '.codice', $where, $orderby, $limit, $passo);
        if ($result == false) {
            die(mysql_error());
        }
        $ctrlprotoc = "";
        while ($r = gaz_dbi_fetch_array($result)) {
            if ($r["tipdoc"] == 'VPR') {
                $modulo = "stampa_precli.php?id_tes=" . $r['id_tes'];
                $modifi = "admin_broven.php?Update&id_tes=" . $r['id_tes'];
            }
            if (substr($r["tipdoc"], 1, 1) == 'O') {
                $modulo = "stampa_ordcli.php?id_tes=" . $r['id_tes'];
                $modifi = "admin_broven.php?Update&id_tes=" . $r['id_tes'];
            }
            echo "<tr class=\"FacetDataTD\">";

            if (!empty($modifi)) {
                echo "<td><a class=\"btn btn-xs btn-default btn-edit\" title=\"" . $script_transl['type_value'][$r["tipdoc"]] . "\" href=\"" . $modifi . "\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . substr($r["tipdoc"], 1, 2) . "&nbsp;" . $r["id_tes"] . "</td>";
            } else {
                echo "<td><button class=\"btn btn-xs btn-default disabled\">&nbsp;" . substr($r["tipdoc"], 1, 2) . "&nbsp;" . $r["id_tes"] . " </button></td>";
            }
            echo "<td>" . $r["numdoc"] . " &nbsp;</td>";
            if ( $what=="VOG" ) {
                echo "<td>". getDayNameFromDayNumber($r["weekday_repeat"]). " &nbsp;</td>";
            } else {
                echo "<td>" . gaz_format_date($r["datemi"]) . " &nbsp;</td>";
            }
            echo "<td><a title=\"Dettagli cliente\" href=\"report_client.php?auxil=" . $r["ragso1"] . "&search=Cerca\">" . $r["ragso1"] . "</a> &nbsp;</td>";
            echo "<td><a href=\"admin_destinazioni.php?codice=".$r["codice"]."&Update\">".$r["unita_locale1"]."</a></td>";
            
            // colonna stato ordine
            $remains_atleastone = false; // Almeno un rigo e' rimasto da evadere.
            $processed_atleastone = false; // Almeno un rigo e' gia' stato evaso.  
            $rigbro_result = gaz_dbi_dyn_query('*', $gTables['rigbro'], "id_tes = " . $r["id_tes"] . " AND tiprig <=1 ", 'id_tes DESC');
            while ( $rigbro_r = gaz_dbi_fetch_array($rigbro_result) ) {
                $totale_da_evadere = $rigbro_r['quanti'];
                $totale_evaso = 0;
                $rigdoc_result = gaz_dbi_dyn_query('*', $gTables['rigdoc'], "id_order=" . $r['id_tes'] . " AND codart='".$rigbro_r['codart']."' AND tiprig <=1 ", 'id_tes DESC');
                while ($rigdoc_r = gaz_dbi_fetch_array($rigdoc_result)) {
                    $totale_evaso += $rigdoc_r['quanti'];
                    $processed_atleastone = true;
                }               
                if ( $totale_evaso != $totale_da_evadere ) {
                    $remains_atleastone = true;
                }
            }
            //
            // Se l'ordine e' da evadere completamente, verifica lo status ed
            // eventualmente lo aggiorna.
            //
            if ($remains_atleastone && !$processed_atleastone) {
                // L'ordine e' completamente da evadere.
                if ($r["status"] != "GENERATO") {
                    gaz_dbi_put_row($gTables['tesbro'], "id_tes", $r["id_tes"], "status", "RIGENERATO");
                }
                if ( $what=="VOG" ) {
                    echo "<td><a class=\"btn btn-xs btn-warning\" href=\"select_evaord_gio.php?weekday=".$r['weekday_repeat']."\">evadi</a></td>";
                } else {
                    echo "<td><a class=\"btn btn-xs btn-warning\" href=\"select_evaord.php?id_tes=" . $r['id_tes'] . "\">evadi</a>&nbsp;";
                    echo "<a class=\"btn btn-xs btn-warning\" href=\"select_evaord.php?clfoco=" . $r['clfoco'] . "\">evadi cliente</a></td>";
                }
            } elseif ($remains_atleastone) {
                // l'ordine è parzialmente evaso, mostro lista documenti e tasto per evadere rimanenze
                echo "<td>";
                $ultimo_documento = 0;
                mostra_documenti_associati ( $r["id_tes"]);
                if ( $what=="VOG" ) {
                    echo "<a class=\"btn btn-xs btn-default\" href=\"select_evaord_gio.php\">evadi il rimanente</a>";
                } else {
                    echo "<a class=\"btn btn-xs btn-warning\" href=\"select_evaord.php?id_tes=" . $r['id_tes'] . "\">evadi il rimanente</a>&nbsp;";
                    echo "<a class=\"btn btn-xs btn-warning\" href=\"select_evaord.php?clfoco=" . $r['clfoco'] . "\">evadi cliente</a>";
                }
                echo "</td>";
            } else {
                // l'ordine è completamente evaso, mostro i riferimenti ai documenti che lo hanno evaso
                echo "<td> ";
                $ultimo_documento = 0;
                mostra_documenti_associati($r["id_tes"]);
                echo "</td>";
            }
            
            // stampa
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-stampa\" href=\"" . $modulo . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a>";
            echo "</td>";
            // Colonna "Mail"
            echo "<td align=\"center\">";
            if (!empty($r["e_mail"])){ // ho una mail sulla destinazione
                echo '<a class="btn btn-xs btn-default btn-email" onclick="confirMail(this);return false;" id="doc' . $r["id_tes"] . '" url="' . $modulo . '&dest=E" href="#" title="mailto: ' . $r["e_mail"] . '"
        mail="' . $r["e_mail"] . '" namedoc="' . $script_transl['type_value'][$r["tipdoc"]] . ' n.' . $r["numdoc"] . ' del ' . gaz_format_date($r["datemi"]) . '"><i class="glyphicon glyphicon-envelope"></i></a>';
            } elseif (!empty($r["base_mail"])) { // ho una mail sul cliente
                echo '<a class="btn btn-xs btn-default btn-email" onclick="confirMail(this);return false;" id="doc' . $r["id_tes"] . '" url="' . $modulo . '&dest=E" href="#" title="mailto: ' . $r["base_mail"] . '"
        mail="' . $r["base_mail"] . '" namedoc="' . $script_transl['type_value'][$r["tipdoc"]] . ' n.' . $r["numdoc"] . ' del ' . gaz_format_date($r["datemi"]) . '"><i class="glyphicon glyphicon-envelope"></i></a>';
            } else { // non ho mail
                echo '<a title="Non hai memorizzato l\'email per questo cliente, inseriscila ora" href="admin_client.php?codice=' . substr($r["codice"], 3) . '&Update"><i class="glyphicon glyphicon-edit"></i></a>';
            }
            echo "</td>";

            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-duplica\" href=\"duplicate_broven.php?id_tes=" . $r['id_tes'] . "\"><i class=\"glyphicon glyphicon-duplicate\"></i></a>";
            echo "</td>";

            echo "<td align=\"center\">";
            if (!$remains_atleastone || !$processed_atleastone) {
                //possono essere cancellati solo gli ordini inevasi o completamente evasi
                echo "<a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_broven.php?id_tes=" . $r['id_tes'] . "\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
            }
            echo "</td>";
            echo "</tr>\n";
        }
        ?>
        <tr><th class="FacetFieldCaptionTD" colspan="10"></th></tr>
    </table>
    </div>
</form>
<?php
require("../../library/include/footer.php");
?>