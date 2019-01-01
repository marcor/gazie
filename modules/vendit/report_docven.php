<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
$anno = date("Y");
$cliente = '';
$message = "";
$lot = new lotmag();
$partner_select_mode = gaz_dbi_get_row($gTables['company_config'], 'var', 'partner_select_mode');

function print_querytime($prev) {
    list($usec, $sec) = explode(" ", microtime());
    $this_time = ((float) $usec + (float) $sec);
    echo round($this_time - $prev, 8);
    return $this_time;
}

if (isset($_GET['auxil'])) {
    $seziva = intval($_GET['auxil']);
    $where = "tipdoc LIKE 'F%' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'";
} else {
    $seziva = "1";
    $where = "tipdoc LIKE 'F%' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'";
}

$all = $where;

gaz_flt_var_assign('protoc', 'i');
gaz_flt_var_assign('numfat', 'i');
gaz_flt_var_assign('datfat', 'd');
gaz_flt_var_assign('clfoco', 'v');


if (isset($_GET['all'])) {
    $_GET['protoc'] = "";
    $_GET['numfat'] = "";
    $_GET['datfat'] = "";
    $_GET['clfoco'] = "";
    $where = $all;
}

if (isset($_GET['datfat'])) {
    $datfat = $_GET['datfat'];
}

$where .= " GROUP BY protoc, datfat";


if (isset($_GET['cliente'])) {
    if ($_GET['cliente'] <> '') {
        $cliente = $_GET['cliente'];
        $where = " tipdoc LIKE 'F%' AND " . $gTables['tesdoc'] . ".seziva = '$seziva' GROUP BY protoc, datfat";
        $limit = 0;
        $passo = 2000000;
        unset($protocollo);
        unset($numerof);
    }
}

if (isset($_GET['all'])) {
    gaz_set_time_limit(0);
    $where = "tipdoc LIKE 'F%' AND " . $gTables['tesdoc'] . ".seziva = '$seziva' GROUP BY protoc, datfat";
    $passo = 100000;
    unset($cliente);
}


$titolo = "Documenti di vendita a clienti";
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/modal_form'));
echo '<script>
$(function() {
   $( "#dialog" ).dialog({
      autoOpen: false
   });
   
   $( "#dialog1" ).dialog({
      autoOpen: false
   });

   $( "#dialog2" ).dialog({
      autoOpen: false
   });
   $( "#dialog3" ).dialog({
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

function confirPecSdi(link){
   codice = link.id.replace("doc3", "");
   $.fx.speeds._default = 500;
   targetUrl = $("#doc3"+codice).attr("url");
   $("p#mailpecsdi").html($("#doc3"+codice).attr("mail"));
   $("p#mail_attc").html($("#doc3"+codice).attr("namedoc"));
   $( "#dialog3" ).dialog({
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
   $("#dialog3" ).dialog( "open" );
}


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

function confirTutti(link){
   $.fx.speeds._default = 500;
   $( "#dialog2" ).dialog({
      modal: "true",
      show: "blind",
      hide: "explode",
      buttons: {
                      " ' . $script_transl['submit'] . ' ": function() {
                          window.location.href = window.location.pathname + "?all=Mostra+tutti&auxil=' . $seziva . '";
                          $(this).dialog("close");
                      },
                      " ' . $script_transl['cancel'] . ' ": function() {
                        $(this).dialog("close");
                      }
               }
         });
   $("#dialog2" ).dialog( "open" );
}



</script>';
switch ($admin_aziend['fatimm']) {
    case "1":
        $sezfatimm = 1;
        break;
    case "2":
        $sezfatimm = 2;
        break;
    case "3":
        $sezfatimm = 3;
        break;
    case "4":
        $sezfatimm = 4;
        break;
    case "5":
        $sezfatimm = 5;
        break;
    case "6":
        $sezfatimm = 6;
        break;
    case "7":
        $sezfatimm = 7;
        break;
    case "8":
        $sezfatimm = 8;
        break;
    case "9":
        $sezfatimm = 9;
        break;
    case "R":
        $sezfatimm = $seziva;
        break;
    case "U":
        $rs_ultimo = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "datemi LIKE '$anno%' AND tipdoc = 'FAI'", "datfat desc", 0, 1);
        $ultimo = gaz_dbi_fetch_array($rs_ultimo);
        $sezfatimm = $ultimo['seziva'];
        break;
    default:
        $sezfatimm = $seziva;
}
?>
<form method="GET" >
    <div style="display:none" id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
        <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
        <p class="ui-state-highlight" id="mail_adrs"></p>
        <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
        <p class="ui-state-highlight" id="mail_attc"></p>
    </div>

    <div style="display:none" id="dialog1" title="<?php echo $script_transl['fae_alert0']; ?>">
        <p id="fae_alert1"><?php echo $script_transl['fae_alert1']; ?></p>
        <p class="ui-state-highlight" id="fae1"></p>
        <p id="fae_alert2"><?php echo $script_transl['fae_alert2']; ?><span id="fae2" class="bg-warning"></span></p>
    </div>

    <div style="display:none" id="dialog2" title="<?php echo $script_transl['report_alert0']; ?>">
        <p id="report_alert1"><?php echo $script_transl['report_alert1']; ?></p>
        <p class="ui-state-highlight" id="report1"></p>
    </div>
	
    <div style="display:none" id="dialog3" title="<?php echo $script_transl['faesdi_alert0']; ?>">
        <p id="faesdi_alert1"><?php echo $script_transl['faesdi_alert1']; ?></p>
        <p class="ui-state-highlight" id="mailpecsdi"></p>
    </div>
	
    <div align="center" class="FacetFormHeaderFont">Documenti di vendita della sezione
        <select name="auxil" class="FacetSelect" onchange="this.form.submit()">
            <?php
            for ($sez = 1; $sez <= 9; $sez++) {
                $selected = "";
                if ($seziva == $sez) {
                    $selected = " selected ";
                }
                echo "<option value=\"" . $sez . "\"" . $selected . ">" . $sez . "</option>";
            }
            ?>
        </select></div>

    <div align="center">
        <?php
        if (!isset($_GET['field']) or ( $_GET['field'] == 2) or ( empty($_GET['field'])))
            $orderby = "datfat desc, protoc desc";
        list ($usec, $sec) = explode(' ', microtime());
        $querytime = ((float) $usec + (float) $sec);
        $querytime_before = $querytime;
        $recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
        $recordnav->output();
        ?>
    </div>

    <div class="box-body table-responsive">

        <table class="Tlarge table table-bordered table-condensed table-striped">
            <tr>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("protoc", "Numero Prot."); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("numfat", "Numero Fatt."); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_select("datfat", "YEAR(datfat) as datfat", $gTables["tesdoc"], $all, $orderby); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php
                    if ($partner_select_mode['val'] == null or $partner_select_mode['val'] == "0") {
                        gaz_flt_disp_select("clfoco", $gTables['anagra'] . ".ragso1," . $gTables["tesdoc"] . ".clfoco", $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['clfoco'] . ".id_anagra = " . $gTables['anagra'] . ".id", $all, "ragso1", "ragso1");
                    } else {
                        gaz_flt_disp_int("cliente", "Cliente");
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
                    &nbsp;
                </td>
                <td class="FacetFieldCaptionTD">
                    &nbsp;
                </td>
                <td class="FacetFieldCaptionTD">
                    <input type="submit" class="btn btn-sm btn-default btn-50" name="search" value="Cerca" tabindex="1">
                </td>
                <td class="FacetFieldCaptionTD">
                    <input type="submit" class="btn btn-sm btn-default btn-50" name="all" value="Tutti" onClick="confirTutti();
                            return false;">
                </td>
            </tr>
            <tr>
                <?php
// creo l'array (header => campi) per l'ordinamento dei record
                $headers_tesdoc = array(
                    "Prot." => "protoc",
                    //"Tipo" => "tipdoc",
                    "Numero" => "numfat",
                    "Data" => "datfat",
                    "Cliente" => "",
                    "Status" => "",
                    "Stampa" => "",
                    "FAE" => "",
                    "Mail" => "",
                    "Origine" => "",
                    "Cancella" => ""
                );
                $linkHeaders = new linkHeaders($headers_tesdoc);
                $linkHeaders->output();
                ?>
            </tr>
            <?php
            $rs_ultimo_documento = gaz_dbi_dyn_query("id_tes,tipdoc,protoc", $gTables['tesdoc'], "tipdoc LIKE 'F%' AND seziva = '$seziva'", "datfat DESC, protoc DESC, id_tes DESC", 0, 1);
            $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
//recupero le testate in base alle scelte impostate
            $result = gaz_dbi_dyn_query($gTables['tesdoc'] . ".*, MAX(" . $gTables['tesdoc'] . ".id_tes) AS reftes", $gTables['tesdoc'], $where, $orderby, $limit, $passo);
            /*
             * $gTables['anagra'] . ".fe_cod_univoco," . 
             * $gTables['anagra'] . ".pec_email," .  
             * $gTables['anagra'] . ".ragso1," . 
             * $gTables['anagra'] . ".e_mail," . 
             * $gTables['clfoco'] . ".codice," . 
             * $gTables['pagame'] . ".tippag" 
             */
            $ctrl_doc = "";
            $ctrl_eff = 999999;
            while ($r = gaz_dbi_fetch_array($result)) {
                // customer data
                $match_cust = true;
                $clfoco = gaz_dbi_get_row($gTables['clfoco'], 'codice', $r['clfoco']);
                $pagame = gaz_dbi_get_row($gTables['pagame'], 'codice', $r['pagame']);
                $anagra = gaz_dbi_get_row($gTables['anagra'], 'id', $clfoco['id_anagra']);
                if (!empty($cliente) && stripos($anagra['ragso1'], $_GET['cliente']) === false) {
                    $match_cust = false;
                }
                $modulo_fae = "electronic_invoice.php?id_tes=" . $r['id_tes'];
                $modulo_fae_report = "report_fae_sdi.php?id_tes=" . $r['id_tes'];
                $classe_btn = "btn-default";
                if ($r["tipdoc"] == 'FAI') {
                    $tipodoc = "Fattura Immediata";
                    $modulo = "stampa_docven.php?id_tes=" . $r['id_tes'];
                    $modifi = "admin_docven.php?Update&id_tes=" . $r['id_tes'];
                } elseif ($r["tipdoc"] == 'FAD') {
                    $tipodoc = "Fattura Differita";
                    $classe_btn = "btn-inverse";
                    $modulo = "stampa_docven.php?td=2&si=" . $r["seziva"] . "&pi=" . $r['protoc'] . "&pf=" . $r['protoc'] . "&di=" . $r['datfat'] . "&df=" . $r['datfat'];
                    $modulo_fae = "electronic_invoice.php?seziva=" . $r["seziva"] . "&protoc=" . $r['protoc'] . "&year=" . substr($r['datfat'], 0, 4);
                    $modifi = "";
                } elseif ($r["tipdoc"] == 'FAP') {
                    $tipodoc = "Parcella";
                    $classe_btn = "btn-primary";
                    $modulo = "stampa_docven.php?id_tes=" . $r['id_tes'];
                    $modifi = "admin_docven.php?Update&id_tes=" . $r['id_tes'];
                } elseif ($r["tipdoc"] == 'FNC') {
                    $tipodoc = "Nota Credito";
                    $classe_btn = "btn-danger";
                    $modulo = "stampa_docven.php?id_tes=" . $r['id_tes'];
                    $modifi = "admin_docven.php?Update&id_tes=" . $r['id_tes'];
                } elseif ($r["tipdoc"] == 'FND') {
                    $tipodoc = "Nota Debito";
                    $classe_btn = "btn-success";
                    $modulo = "stampa_docven.php?id_tes=" . $r['id_tes'];
                    $modifi = "admin_docven.php?Update&id_tes=" . $r['id_tes'];
                } else {
                    $tipodoc = "DOC.SCONOSCIUTO";
                    $classe_btn = "btn-warning";
                    $modulo = "stampa_docven.php?id_tes=" . $r['id_tes'];
                    $modifi = "admin_docven.php?Update&id_tes=" . $r['id_tes'];
                }
                if ($match_cust && sprintf('%09d', $r['protoc']) . $r['datfat'] <> $ctrl_doc) {
                    $n_e = 0;
					/* trovo il nome dei file xml delle fatture elettroniche, sia quello attuale sia quello frutto di un eventuale reinviio 
					*/
					$r['fae_attuale']="IT" . $admin_aziend['codfis'] . "_".encodeSendingNumber(array('azienda' => $admin_aziend['codice'],
									'anno' => $r["datfat"],
									'sezione' => $r["seziva"],
									'fae_reinvii'=> $r["fattura_elettronica_reinvii"],
									'protocollo' => $r["protoc"]), 36).".xml";
 					$r['fae_reinvio']="IT" . $admin_aziend['codfis'] . "_".encodeSendingNumber(array('azienda' => $admin_aziend['codice'],
									'anno' => $r["datfat"],
									'sezione' => $r["seziva"],
									'fae_reinvii'=> intval($r["fattura_elettronica_reinvii"]+1),
									'protocollo' => $r["protoc"]), 36).".xml";
                    echo "<tr class=\"FacetDataTD\">";
                    // Colonna protocollo
                    if (!empty($modifi)) {
                        echo "<td><a href=\"" . $modifi . "\" class=\"btn btn-100 btn-xs " . $classe_btn . " btn-edit\" title=\"Modifica " . $tipodoc . " \">" . $r["protoc"] . "&nbsp;" . $r["tipdoc"] . "&nbsp;<i class=\"glyphicon glyphicon-edit\"></i></a></td>";
                    } else {
                        echo "<td><button class=\"btn btn-100 btn-xs " . $classe_btn . " btn-edit disabled\" title=\"Per poter modificare questa " . $tipodoc . " devi modificare i DdT in essa contenuti!\">" . $r["protoc"] . "&nbsp;" . $r["tipdoc"] . " &nbsp;<i class=\"glyphicon glyphicon-edit\"></i></button></td>";
                    }
                    // Colonna tipo documento
                    //echo "<td class=\"FacetDataTD\">".$tipodoc." &nbsp;</td>";
                    // Colonna numero documento
                    echo "<td align=\"center\">" . $r["numfat"] . " &nbsp;</td>";
                    // Colonna data documento
                    echo "<td align=\"center\">" . gaz_format_date($r["datfat"]) . " &nbsp;</td>";
                    // Colonna cliente
                    echo "<td><a title=\"Dettagli cliente\" href=\"report_client.php?auxil=" . htmlspecialchars($anagra["ragso1"]) . "&search=Cerca\">" . $anagra["ragso1"] . ((empty($anagra["ragso2"]))?"":" ".$anagra["ragso2"]) . "</a>&nbsp;</td>";
                    // Colonna movimenti contabili
                    echo "<td align=\"left\">";
                    if ($r["id_con"] > 0) {
                        echo " <a class=\"btn btn-xs btn-default btn-default\" style=\"font-size:10px;\" title=\"Modifica il movimento contabile generato da questo documento\" href=\"../contab/admin_movcon.php?id_tes=" . $r["id_con"] . "&Update\">Cont." . $r["id_con"] . "</a> ";
                    } else {
                        echo " <a class=\"btn btn-xs btn-default btn-cont\" href=\"accounting_documents.php?type=F&vat_section=" . $seziva . "&last=" . $r["protoc"] . "\"><i class=\"glyphicon glyphicon-euro\"></i>&nbsp;Contabilizza</a>";
                    }
                    $effett_result = gaz_dbi_dyn_query('*', $gTables['effett'], "id_doc = " . $r["reftes"], 'progre');
                    while ($r_e = gaz_dbi_fetch_array($effett_result)) {
                        // La fattura ha almeno un effetto emesso
                        $n_e++;
                        if ($r_e["tipeff"] == "B") {
                            echo " <a class=\"btn btn-xs btn-default btn-riba\" style=\"font-size:10px;\" title=\"Visualizza la ricevuta bancaria generata per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=" . $r_e["id_tes"] . "\">";
                            echo "RiBa" . $r_e["progre"];
                            echo "</a>";
                        } elseif ($r_e["tipeff"] == "T") {
                            echo " <a class=\"btn btn-xs btn-default btn-cambiale\" style=\"font-size:10px;\" title=\"Visualizza la cambiale tratta generata per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=" . $r_e["id_tes"] . "\">";
                            echo "Tratta" . $r_e["progre"];
                            echo "</a>";
                        } elseif ($r_e["tipeff"] == "V") {
                            echo " <a class=\"btn btn-xs btn-default btn-avviso\" style=\"font-size:10px;\" title=\"Visualizza il pagamento mediante avviso generato per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=" . $r_e["id_tes"] . "\">";
                            echo "MAV" . $r_e["progre"];
                            echo "</a>";
                        } else {
                            echo " <a class=\"btn btn-xs btn-default btn-effetto\" style=\"font-size:10px;\" title=\"Visualizza l'effetto\" href=\"stampa_effett.php?id_tes=" . $r_e["id_tes"] . "\">";
                            echo $r_e["tipeff"] . $r_e["progre"];
                            echo "</a>";
                        }
                    }
                    if ($n_e == 0 && ($pagame["tippag"] == 'B' || $pagame["tippag"] == 'T' || $pagame["tippag"] == 'V')) {
                        echo " <a class=\"btn btn-xs btn-effetti\" title=\"Genera gli effetti previsti per il regolamento delle fatture\" href=\"genera_effett.php\"> Genera effetti</a>";
                    }
                    echo "</td>";
                    // Colonna "Stampa"
                    echo "<td align=\"center\"><a accesskey=\"p\" class=\"btn btn-xs btn-50 btn-default\" href=\"" . $modulo . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a>";
                    echo "</td>";

                    // Colonna "Fattura elettronica"
                    if (substr($r["tipdoc"], 0, 1) == 'F') {
                        if(strlen($r["fattura_elettronica_zip_package"])>10){ // se è contenuto in un pacchetto di file permetterò sia il download del singolo XML che del pacchetto in cui è contenuto
                            echo "<td align=\"center\">".'<a class="btn btn-xs btn-edit" title="Pacchetto di fatture elettroniche in cui è contenuta questa fattura" href="download_zip_package.php?fn='.$r['fattura_elettronica_zip_package'].'">zip <i class="glyphicon glyphicon-compressed"></i> </a>';							
						} elseif (strlen($anagra['pec_email'])<5 && strlen(trim($anagra['fe_cod_univoco']))<6) { //se il cliente non ha codice univoco o pec tolgo il link e do la possibilità di richiederli via mail o carta
                            $d_title = 'Invia richiesta PEC e/o codice SdI all\'indirizzo: '.$anagra["e_mail"];
							$dest='&dest=E';
							if (strlen($anagra['e_mail'])<5){
								$dest='';
								$d_title = 'Stampa richiesta cartacea (cliente senza mail)';
							}
                            echo '<td align=\"center\"><button onclick="confirPecSdi(this);return false;" id="doc3' . $r["clfoco"] . '" url="stampa_richiesta_pecsdi.php?codice='.$r['clfoco'].$dest.'" href="#" title="'. $d_title . '" mail="' . $anagra["e_mail"] . '" namedoc="Richiesta codice SdI o indirizzo PEC"  class="btn btn-xs btn-default btn-elimina"><i class="glyphicon glyphicon-tag"></i></button>';
                        } else { // quando ho pec e/o codice univoco ma non ho creato pacchetti zip
                            echo "<td align=\"center\">";
                        }
                        echo '<a class="btn btn-xs btn-default btn-xml" onclick="confirFae(this);return false;" id="doc1" '.$r["id_tes"].'" fae_reinvio="'.$r["fae_reinvio"].'" fae_attuale="'.$r["fae_attuale"].'" fae_n_reinvii="'.$r["fattura_elettronica_reinvii"].'" n_fatt="'. $r["numfat"]."/". $r["seziva"].'" target="_blank" href="'.$modulo_fae.'" title="genera il file '.$r["fae_attuale"].' o fai il '.intval($r["fattura_elettronica_reinvii"]+1).'° reinvio ">xml</a><a class="btn btn-xs btn-default" title="Visualizza in stile www.fatturapa.gov.it" href="electronic_invoice.php?id_tes='.$r['id_tes'].'&viewxml"><i class="glyphicon glyphicon-eye-open"></i> </a></td>';
                    } else {
                        echo "<td></td>";
                    }

                    // Colonna "Mail"
                    echo "<td align=\"center\">";
                    if (!empty($anagra["e_mail"])) {
                        echo '<a class="btn btn-xs btn-default btn-email" onclick="confirMail(this);return false;" id="doc' . $r["id_tes"] . '" url="' . $modulo . '&dest=E" href="#" title="Mailto: ' . $anagra["e_mail"] . '"
            mail="' . $anagra["e_mail"] . '" namedoc="' . $tipodoc . ' n.' . $r["numfat"] . ' del ' . gaz_format_date($r["datfat"]) . '"><i class="glyphicon glyphicon-envelope"></i></a>';
                    } else {
                        echo '<a title="Non hai memorizzato l\'email per questo cliente, inseriscila ora" href="admin_client.php?codice=' . substr($clfoco["codice"], 3) . '&Update#email"><i class="glyphicon glyphicon-edit"></i></a>';
                    }
                    echo "</td>";
                    // Colonna "Origine"
                    if ($r["tipdoc"] == 'FAD') {
                        $ddt_result = gaz_dbi_dyn_query('*', $gTables['tesdoc'], "tipdoc = '" . $r["tipdoc"] . "' AND numfat = " . $r["numfat"] . " AND datfat = '" . $r["datfat"] . "'", 'datemi DESC');
                        echo "<td align=\"center\">";
                        $cmr = false;
                        if ( $r['ddt_type']=='R' ) {
                            $cmr = true;
                        }
                        if ( gaz_dbi_num_rows($ddt_result) > 5 ) {
                            if ( $cmr ) {
                                echo "<a href=\"report_doccmr.php\" style=\"font-size:10px;\" class=\"btn btn-xs btn-default\"><i class=\"glyphicon glyphicon-plane\"></i>CMR</a>";
                                while ($r_d = gaz_dbi_fetch_array($ddt_result)) {
                                    echo " <a class=\"btn btn-xs btn-default btn-ddt\" title=\"Visualizza i CMR\" href=\"stampa_docven.php?id_tes=" . $r_d['id_tes'] . "&template=CMR\" style=\"font-size:9px;\">" . $r_d['numdoc'] . "</a>\n";
                                }
                            } else {
                                echo "<a href=\"report_doctra.php\" style=\"font-size:10px;\" class=\"btn btn-xs btn-default\"><i class=\"glyphicon glyphicon-plane\"></i>DdT</a>";
                                while ($r_d = gaz_dbi_fetch_array($ddt_result)) {
                                    echo " <a class=\"btn btn-xs btn-default btn-ddt\" title=\"Visualizza il DdT\" href=\"stampa_docven.php?id_tes=" . $r_d['id_tes'] . "&template=DDT\" style=\"font-size:9px;\">" . $r_d['numdoc'] . "</a>\n";
                                }
                            }
                        } else {
                            while ($r_d = gaz_dbi_fetch_array($ddt_result)) {
                                if ( $cmr ) {
                                    echo " <a class=\"btn btn-xs btn-default btn-ddt\" title=\"Visualizza il CMR\" href=\"stampa_docven.php?id_tes=" . $r_d['id_tes'] . "&template=CMR\" style=\"font-size:10px;\"><i class=\"glyphicon glyphicon-plane\"></i>&nbsp;CMR" . $r_d['numdoc'] . "</a>\n";
                                } else {
                                    echo " <a class=\"btn btn-xs btn-default btn-ddt\" title=\"Visualizza il DdT\" href=\"stampa_docven.php?id_tes=" . $r_d['id_tes'] . "&template=DDT\" style=\"font-size:10px;\"><i class=\"glyphicon glyphicon-plane\"></i>&nbsp;DdT" . $r_d['numdoc'] . "</a>\n";
                                }
                            }
                        }
                        echo "</td>";
                    } elseif ($r["id_contract"] > 0) {
                        $con_result = gaz_dbi_dyn_query('*', $gTables['contract'], "id_contract = " . $r["id_contract"], 'conclusion_date DESC');
                        echo "<td align=\"center\">";
                        while ($r_d = gaz_dbi_fetch_array($con_result)) {
                            echo " <a class=\"btn btn-xs btn-default btn-contr\" title=\"Visualizza il contratto\" href=\"print_contract.php?id_contract=" . $r_d['id_contract'] . "\" style=\"font-size:10px;\"><i class=\"glyphicon glyphicon-list-alt\"></i>&nbsp;Contr." . $r_d['doc_number'] . "</a>\n";
                        }
                        echo "</td>";
                    } elseif ($lot->thereisLot($r['id_tes'])) {
                        echo "<td> <a class=\"btn btn-xs btn-default\" title=\"" . $script_transl['print_lot'] . "\" href=\"lotmag_print_cert.php?id_tesdoc=" . $r['id_tes'] . "\" style=\"font-size:10px;\">Cert.<i class=\"glyphicon glyphicon-tags\"></i></a></td>\n";
                    } else {
                        echo "<td>";
                        $resorigine = gaz_dbi_dyn_query('*', $gTables['rigdoc'], "id_tes = " . $r["id_tes"], 'id_tes', 1,1);
                        if ( gaz_dbi_num_rows( $resorigine )>0 ) {
                            $rigdoc_result = gaz_dbi_dyn_query('DISTINCT id_order', $gTables['rigdoc'], "id_tes = " . $r["id_tes"], 'id_tes');
                            while ( $rigdoc = gaz_dbi_fetch_array($rigdoc_result) ) {
                                if($rigdoc['id_order']>0){
                                    $tesbro_result = gaz_dbi_dyn_query('*', $gTables['tesbro'], "id_tes = " . $rigdoc['id_order'], 'id_tes');
                                    $t_r = gaz_dbi_fetch_array($tesbro_result);
                                    echo " <a class=\"btn btn-xs btn-default\" title=\"" . $script_transl['view_ord'] . "\" href=\"stampa_ordcli.php?id_tes=" . $rigdoc['id_order'] . "\" style=\"font-size:10px;\"><i class=\"glyphicon glyphicon-check\"></i>&nbsp;Ord." . $t_r['numdoc'] . "</a>\n";
                                }
                            }
                        }
                        echo "</td>";
                    }
                    // Colonna "Cancella"
                    echo "<td align=\"center\">";
                    if ($ultimo_documento['id_tes'] == $r["id_tes"] || ($ultimo_documento['tipdoc'] == 'FAD' && $ultimo_documento['protoc'] == $r['protoc'])) {
                        // Permette di cancellare il documento.
                        if ($r["id_con"] > 0) {
                            echo "<a class=\"btn btn-xs btn-default btn-elimina\" title=\"Cancella il documento e la registrazione contabile relativa\" href=\"delete_docven.php?seziva=" . $r["seziva"] . "&protoc=" . $r['protoc'] . "&anno=" . substr($r["datfat"], 0, 4) . "\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
                        } else {
                            echo "<a class=\"btn btn-xs btn-default btn-elimina\" title=\"Cancella il documento\" href=\"delete_docven.php?seziva=" . $r["seziva"] . "&protoc=" . $r['protoc'] . "&anno=" . substr($r["datfat"], 0, 4) . "\"><i class=\"glyphicon glyphicon-remove\"></i></a>";
                        }
                    } else {
                        echo "<button title=\"Per garantire la sequenza corretta della numerazione, non &egrave; possibile cancellare un documento diverso dall'ultimo\" class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button>";
                    }
                    echo "</td>";
                    /*        echo "<td class=\"FacetDataTD\" align=\"right\">";
                      $querytime=print_querytime($querytime);
                      echo "</td>"; */
                    echo "</tr>\n";
                }
                $ctrl_doc = sprintf('%09d', $r['protoc']) . $r['datfat'];
            }
            echo '<tr><td class="FacetFieldCaptionTD" colspan="10" align="right">Querytime: ';
            print_querytime($querytime);
            echo ' sec.</td></tr>';
            ?>
        </table>
</form>
</div>
<?php
require("../../library/include/footer.php");
?>