<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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

function getDocRef($data) {
    global $gTables;
    $r = array();
    return $r;
}

if (isset($_GET['auxil'])) {
    $auxil = $_GET['auxil'];
    if ($_GET['auxil'] == 'VPR') {
        $what = 'VPR';
    } else {
        $what = substr($auxil, 0, 2) . "_";
    }
    $where = "tipdoc LIKE '$what'";
} else {
    $auxil = 'VO_';
    $_GET['auxil'] = 'VOR';
    $where = "tipdoc LIKE '$auxil'";
}
$all = $where;

gaz_flt_var_assign('id_tes', 'i');
gaz_flt_var_assign('numdoc', 'i');
gaz_flt_var_assign('datemi', 'd');
gaz_flt_var_assign('clfoco', 'v');

if (isset($_GET['all'])) {
    $_GET['id_tes'] = "";
    $_GET['numdoc'] = "";
    $_GET['datemi'] = "";
    $_GET['clfoco'] = "";
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
$script_transl = HeadMain(0, array(/** ENRICO FEDELE */
    /* 'jquery/jquery-1.7.1.min',
      'jquery/ui/jquery.ui.core',
      'jquery/ui/jquery.ui.widget',
      'jquery/ui/jquery.ui.mouse',
      'jquery/ui/jquery.ui.button',
      'jquery/ui/jquery.ui.dialog',
      'jquery/ui/jquery.ui.position',
      'jquery/ui/jquery.ui.draggable',
      'jquery/ui/jquery.ui.resizable',
      'jquery/ui/jquery.effects.core',
      'jquery/ui/jquery.effects.scale', */
    /** ENRICO FEDELE */
    'custom/modal_form'));
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

    <div id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
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
                <?php gaz_flt_disp_select("datemi", "YEAR(datemi) as datemi", $gTables["tesbro"], $all, $orderby); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_select("clfoco", $gTables['anagra'] . ".ragso1," . $gTables["tesbro"] . ".clfoco", $gTables['tesbro'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesbro'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['clfoco'] . ".id_anagra = " . $gTables['anagra'] . ".id", $all, $orderby, "ragso1"); ?>
            </td>
            <td class=FacetFieldCaptionTD>
                &nbsp;
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
        </tr>
        <tr>
            <?php
            echo '<input type="hidden" name="auxil" value="' . substr($_GET['auxil'], 0, 3) . '">';
// creo l'array (header => campi) per l'ordinamento dei record
            $headers_tesbro = array(
                "ID" => "id_tes",
                //$script_transl['type'] => "tipdoc",
                $script_transl['number'] => "numdoc",
                $script_transl['date'] => "datemi",
                "Cliente" => "clfoco",
                $script_transl['status'] => "status",
                $script_transl['print'] => "",
                "Mail" => "",
                $script_transl['duplicate'] => "",
                $script_transl['delete'] => ""
            );
            $linkHeaders = new linkHeaders($headers_tesbro);
            $linkHeaders->output();
            ?>
        </tr>
        <?php
//recupero le testate in base alle scelte impostate
        $result = gaz_dbi_dyn_query($gTables['tesbro'] . ".*," . $gTables['anagra'] . ".ragso1," . $gTables['anagra'] . ".e_mail," . $gTables["clfoco"] . ".codice", $gTables['tesbro'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesbro'] . ".clfoco = " . $gTables['clfoco'] . ".codice  LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $where, $orderby, $limit, $passo);
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
            //echo "<td>".$script_transl['type_value'][$r["tipdoc"]]." &nbsp;</td>";
            echo "<td>" . $r["numdoc"] . " &nbsp;</td>";
            echo "<td>" . gaz_format_date($r["datemi"]) . " &nbsp;</td>";
            echo "<td><a title=\"Dettagli cliente\" href=\"report_client.php?auxil=" . $r["ragso1"] . "&search=Cerca\">" . $r["ragso1"] . "</a> &nbsp;</td>";
            $remains_atleastone = false; // Almeno un rigo e' rimasto da evadere.
            $processed_atleastone = false; // Almeno un rigo e' gia' stato evaso.
            $rigbro_result = gaz_dbi_dyn_query('*', $gTables['rigbro'], "id_tes = " . $r["id_tes"] . " AND tiprig <=1 ", 'id_tes DESC');
            while ($rigbro_r = gaz_dbi_fetch_array($rigbro_result)) {
                if ($rigbro_r["id_doc"] == 0) {
                    $remains_atleastone = true;
                    continue;
                }
                $tesdoc_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "id_tes = " . $rigbro_r["id_doc"], 'id_tes DESC');
                $tesdoc_r = gaz_dbi_fetch_array($tesdoc_result);
                if ($rigbro_r["id_doc"] != $tesdoc_r["id_tes"]) {
                    //
                    // Azzera il numero documento nel rigo dell'ordine, dato
                    // che non e' piu' valido.
                    //
            gaz_dbi_put_row($gTables['rigbro'], "id_tes", $rigbro_r["id_tes"], "id_doc", 0);
                    //
                    // Il rigo e' da evadere.
                    //
            $remains_atleastone = true;
                } else {
                    //
                    // L'ordine sembra evaso.
                    //
            $processed_atleastone = true;
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
                echo "<td>";

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
                echo "<td>";
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
                            if ($tesdoc_r["tipdoc"] == "FAI") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza la fattura immediata\" href=\"stampa_docven.php?id_tes=" . $rigbro_r["id_doc"] . "\">";
                                echo "fatt. " . $tesdoc_r["numfat"];
                                echo "</a> ";
                            } elseif ($tesdoc_r["tipdoc"] == "DDT" || $tesdoc_r["tipdoc"] == "FAD") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza il documento di trasporto\" href=\"stampa_docven.php?id_tes=" . $rigbro_r["id_doc"] . "&template=DDT\">";
                                echo "ddt " . $tesdoc_r["numdoc"];
                                echo "</a> ";
                            } elseif ($tesdoc_r["tipdoc"] == "VCO") {
                                echo "<a class=\"btn btn-xs btn-default\" title=\"visualizza lo scontrino come fattura\" href=\"stampa_docven.php?id_tes=" . $rigbro_r["id_doc"] . "&template=FatturaAllegata\">";
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
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-stampa\" href=\"" . $modulo . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a>";
            echo "</td>";
            // Colonna "Mail"
            echo "<td align=\"center\">";
            if (!empty($r["e_mail"])) {
                echo '<a class="btn btn-xs btn-default btn-email" onclick="confirMail(this);return false;" id="doc' . $r["id_tes"] . '" url="' . $modulo . '&dest=E" href="#" title="mailto: ' . $r["e_mail"] . '"
        mail="' . $r["e_mail"] . '" namedoc="' . $script_transl['type_value'][$r["tipdoc"]] . ' n.' . $r["numdoc"] . ' del ' . gaz_format_date($r["datemi"]) . '"><i class="glyphicon glyphicon-envelope"></i></a>';
            } else {
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
    </table>
    </div>
</form>
<?php
require("../../library/include/footer.php");
?>