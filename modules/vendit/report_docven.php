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
$message = "";
$lot = new lotmag();

$partner_select = !gaz_dbi_get_row($gTables['company_config'], 'var', 'partner_select_mode')['val'];
$tesdoc_e_partners = $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id';

function print_querytime($prev) {
    list($usec, $sec) = explode(" ", microtime());
    $this_time = ((float) $usec + (float) $sec);
    echo round($this_time - $prev, 8);
    return $this_time;
}

// funzione di utilità generale, adatta a mysqli.inc.php
function cols_from($table_name, ...$col_names) {
    $full_names = array_map(function ($col_name) use ($table_name) { return "$table_name.$col_name"; }, $col_names);
    return implode(", ", $full_names);
}

// campi ammissibili per la ricerca
$search_fields = [
    'sezione'
    => "seziva = %d",
    'protoc'
    => "protoc = %d",
    'tipo'
    => "tipdoc LIKE '%s'",
    'numero'
    => "numfat LIKE '%%%s%%'",
    'anno'
    => "YEAR(datfat) = %d",
    'cliente'
    => $partner_select ? "clfoco = '%s'" : "ragso1 LIKE '%%%s%%'"
];

// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array(
    "Prot." => "protoc",
    "Numero" => "numfat",
    "Data" => "datfat",
    "Cliente" => "",
    "Info" => "",
    "Stampa" => "",
    "FAE" => "",
    "Mail" => "",
    "Origine" => "",
    "Cancella" => ""
);

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/modal_form'));

$ts = new TableSorter(
    !$partner_select && isset($_GET["cliente"]) ? $tesdoc_e_partners : $gTables['tesdoc'], 
    $passo, 
    ['datfat' => 'desc', 'protoc' => 'desc'], 
    ['sezione' => 1, 'tipo' => 'F%'],
    ['protoc', 'datfat']
);

# le <select> spaziano solo tra i documenti di vendita del sezionale corrente
$where_select = sprintf("tipdoc LIKE 'F%%' AND seziva = %d", $sezione);

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
   tes_id = link.id.replace("doc_", "");
   $.fx.speeds._default = 500;
   targetUrl = $("#doc_"+tes_id).attr("url");
   $("p#mail_adrs").html($("#doc_"+tes_id).attr("mail"));
   $("p#mail_attc").html($("#doc_"+tes_id).attr("namedoc"));
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
   codice = link.id.replace("doc3_", "");
   $.fx.speeds._default = 500;
   targetUrl = $("#doc3_"+codice).attr("url");
   $("p#mailpecsdi").html($("#doc3_"+codice).attr("mail"));
   $("p#mail_attc").html($("#doc3_"+codice).attr("namedoc"));
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
	tes_id = link.id.replace("doc1_", "");
	$.fx.speeds._default = 500;
	var new_title = "Genera file XML per fattura n." + $("#doc1_"+tes_id).attr("n_fatt");
	var n_reinvii = parseInt($("#doc1_"+tes_id).attr("fae_n_reinvii"))+1;
	$("p#fae1").html("nome file: " + $("#doc1_"+tes_id).attr("fae_attuale"));
	$("span#fae2").html("<a href=\'"+link.href+"&reinvia\'> " + $("#doc1_"+tes_id).attr("fae_reinvio")+ " (" + n_reinvii.toString() + "° reinvio) </a>");
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
        <select name="sezione" class="FacetSelect" onchange="this.form.submit()">
	    <?php
            echo "<option value=''>1</option>\n"; # è l'opzione di default perciò ha valore vuoto
            for ($sez = 2; $sez <= 9; $sez++) {
                $selected = $sezione == $sez ? "selected" : "";
                echo "<option value='$sez' $selected > $sez </option>\n";
            }
	    ?>

        </select>
    </div>

    <div align="center">
	<?php
        list ($usec, $sec) = explode(' ', microtime());
        $querytime = ((float) $usec + (float) $sec);
        $querytime_before = $querytime;
        $ts->output_navbar();
	?>
    </div>

    <div class="box-body table-responsive">

        <table class="Tlarge table table-bordered table-condensed table-striped">
            <tr>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("protoc", "Numero Prot."); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_int("numero", "Numero Fatt."); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <?php gaz_flt_disp_select("anno", "YEAR(datfat) as anno", $gTables["tesdoc"], $where_select, "anno DESC"); ?>
                </td>
                <td class="FacetFieldCaptionTD">
		    <?php 
                    if ($partner_select) {
                        gaz_flt_disp_select("cliente", "clfoco AS cliente, ragso1 as nome", 
					    $tesdoc_e_partners,
					    $where_select, "nome ASC", "nome");
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
                    <?php $ts->output_order_form(); ?>
                </td>
                <td class="FacetFieldCaptionTD">
                    <a class="btn btn-sm btn-default btn-50" href="?">Reset</a>
                </td>
            </tr>
            <tr>
                <?php
                $ts->output_headers();
                ?>
            </tr>
            <?php
            $rs_ultimo_documento = gaz_dbi_dyn_query("id_tes,tipdoc,protoc", $gTables['tesdoc'], "tipdoc LIKE 'F%' AND seziva = '$sezione'", "datfat DESC, protoc DESC, id_tes DESC", 0, 1);
            $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
	    //recupero le testate in base alle scelte impostate
	    $result = gaz_dbi_dyn_query(cols_from($gTables['tesdoc'],
						  "*") . ", " .
					cols_from($gTables['anagra'],
						  "fe_cod_univoco",
						  "pec_email",
						  "ragso1",
						  "ragso2",
						  "e_mail") . ", " .
					"MAX(id_tes) as reftes, " .
					"GROUP_CONCAT(id_tes ORDER BY datemi DESC) as refs_id, " . 
					"GROUP_CONCAT(numdoc ORDER BY datemi DESC) as refs_num",
					$tesdoc_e_partners,
					$ts->where . " " . $ts->group_by,
					$ts->orderby,
					$ts->getOffset(),
					$ts->getLimit());
            $ctrl_doc = "";
            $ctrl_eff = 999999;
			$paymov = new Schedule(); 
            while ($r = gaz_dbi_fetch_array($result)) {
				// se contabilizzato trovo l'eventuale stato dei pagamenti 
				$paymov_status =false;
				$tesmov=gaz_dbi_get_row($gTables['tesmov'], 'id_tes', $r['id_con']);
				$paymov->getStatus(substr($tesmov['datdoc'],0,4).$tesmov['regiva'].$tesmov['seziva']. str_pad($tesmov['protoc'], 9, 0, STR_PAD_LEFT)); // passo il valore formattato di id_tesdoc_ref
				$paymov_status = $paymov->Status;
				// riprendo il rigo  della contabilità con il cliente per avere l'importo 
				$importo = gaz_dbi_get_row($gTables['rigmoc'], 'id_tes', $r['id_con'], "AND codcon = ".$r['clfoco']);
				$pagame = gaz_dbi_get_row($gTables['pagame'], 'codice', $r['pagame']);
                $modulo_fae = "electronic_invoice.php?id_tes=" . $r['id_tes'];
                $modulo_fae_report = "report_fae_sdi.php?id_tes=" . $r['id_tes'];
                $classe_btn = "btn-default";
                if ($r["tipdoc"] == 'FAI'||$r["tipdoc"] == 'FAA') {
                    $tipodoc = "Fattura Immediata";
                    $modulo = "stampa_docven.php?id_tes=" . $r['id_tes'];
                    $modifi = "admin_docven.php?Update&id_tes=" . $r['id_tes'];
                } elseif ($r["tipdoc"] == 'FAD') {
                    $tipodoc = "Fattura Differita";
                    $classe_btn = "btn-inverse";
                    $modulo = "stampa_docven.php?td=2&si=" . $r["seziva"] . "&pi=" . $r['protoc'] . "&pf=" . $r['protoc'] . "&di=" . $r['datfat'] . "&df=" . $r['datfat'];
                    $modulo_fae = "electronic_invoice.php?seziva=" . $r["seziva"] . "&protoc=" . $r['protoc'] . "&year=" . substr($r['datfat'], 0, 4);
                    if ( !$modifica_fatture_ddt ) {
                        $modifi = "";
                    } else {
                        $classe_btn = "btn-default";
                        $modifi = "admin_docven.php?Update&id_tes=" . $r["reftes"];
                    }
                } elseif ($r["tipdoc"] == 'FAP'||$r["tipdoc"] == 'FAQ') {
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
                if (sprintf('%09d', $r['protoc']) . $r['datfat'] <> $ctrl_doc) {
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
// Colonna numero documento
                    echo "<td align=\"center\">" . $r["numfat"] . " &nbsp;</td>";
// Colonna data documento
                    echo "<td align=\"center\">" . gaz_format_date($r["datfat"]) . " &nbsp;</td>";
// Colonna cliente
                    echo "<td><a title=\"Dettagli cliente\" href=\"report_client.php?nome=" . htmlspecialchars($r["ragso1"]) . "\">" . $r["ragso1"] . ((empty($r["ragso2"]))?"":" ".$r["ragso2"]) . "</a>";
					if (strlen(trim($r['fe_cod_univoco']))==6){
						echo '<a class="btn btn-sm btn-info" title="Codice Univoco Ufficio della Pubblica Amministrazione" href="admin_client.php?codice='.intval(substr($r["clfoco"],-6,6)).'&Update">[pa]@ '.$r['fe_cod_univoco'].' </a>';
					}
					echo "</td>";
// Colonna movimenti contabili
                    echo "<td align=\"left\">";
                    if ($r["id_con"] > 0) {
                        echo " <a class=\"btn btn-xs btn-".$paymov_status['style']."\" style=\"font-size:10px;\" title=\"Modifica il movimento contabile " . $r["id_con"] . " generato da questo documento\" href=\"../contab/admin_movcon.php?id_tes=" . $r["id_con"] . "&Update\"> <i class=\"glyphicon glyphicon-euro\"></i> " . $importo["import"] . "</a> ";
                    } else {
                        echo " <a class=\"btn btn-xs btn-default btn-cont\" href=\"accounting_documents.php?type=F&vat_section=" . $sezione . "&last=" . $r["protoc"] . "\"><i class=\"glyphicon glyphicon-euro\"></i>&nbsp;Contabilizza</a>";
                    }
                    $effett_result = gaz_dbi_dyn_query('*', $gTables['effett'], "id_doc = " . $r["reftes"], 'progre');
                    while ($r_e = gaz_dbi_fetch_array($effett_result)) {
                        // La fattura ha almeno un effetto emesso
                        $n_e++;
                        $map_eff = ['B' => ["la ricevuta bancaria generata", "RiBa", "riba"],
                                    'T' => ["la cambiale tratta generata", "Tratta", "cambiale"],
                                    'V' => ["il pagamento mediante avviso generato", "MAV", "avviso"]];
                        list($eff_desc, $eff, $eff_class) = isset($map_eff[$r_e["tipeff"]]) ? $map_eff[$r_e["tipeff"]] :
                                                             ["l'effetto generato", $r_e["tipeff"], "effetto"];
                        echo " <a class='btn btn-xs btn-default btn-$eff_class' style='font-size:10px;' title='Visualizza $eff_desc per il regolamento della fattura' href='stampa_effett.php?id_tes={$r_e['id_tes']}'> $eff {$r_e['progre']} </a>\n";
                    }
                    if ($n_e == 0) {
						if ($pagame["tippag"] == 'B' || $pagame["tippag"] == 'T' || $pagame["tippag"] == 'V') {
							echo " <a class=\"btn btn-xs btn-effetti\" title=\"Genera gli effetti previsti per il regolamento delle fatture\" href=\"genera_effett.php\"> Genera effetti</a>";
						}
					}
                    echo "</td>";
// Colonna "Stampa"
                    echo "<td align=\"center\"><a accesskey=\"p\" class=\"btn btn-xs btn-50 btn-default\" href=\"" . $modulo . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i>&nbsp;pdf</a>";
                    echo "</td>";

// Colonna "Fattura elettronica"
                    if (substr($r["tipdoc"], 0, 1) == 'F') {
			if(strlen($r["fattura_elettronica_original_name"])>10){ // ho un file importato dall'esterno
			    echo '<td><a class="btn btn-xs btn-warning" target="_blank" href="../acquis/view_fae.php?id_tes=' . $r["id_tes"] . '">File importato<i class="glyphicon glyphicon-eye-open"></i></a>'.'<a class="btn btn-xs btn-edit" title="Scarica il file XML originale" href="download_zip_package.php?fn='.$r['fattura_elettronica_original_name'].'">xml <i class="glyphicon glyphicon-download"></i> </a></td>';
			} else { // il file è generato al volo dal database
			    if(strlen($r["fattura_elettronica_zip_package"])>10){ // se è contenuto in un pacchetto di file permetterà sia il download del singolo XML che del pacchetto in cui è contenuto
				echo "<td align=\"center\">".'<a class="btn btn-xs btn-edit" title="Pacchetto di fatture elettroniche in cui è contenuta questa fattura" href="download_zip_package.php?fn='.$r['fattura_elettronica_zip_package'].'">zip <i class="glyphicon glyphicon-compressed"></i> </a>';							
			    } elseif (strlen($r['pec_email'])<5 && strlen(trim($r['fe_cod_univoco']))<6) { //se il cliente non ha codice univoco o pec tolgo il link e do la possibilità di richiederli via mail o carta
				$d_title = 'Invia richiesta PEC e/o codice SdI all\'indirizzo: '.$r["e_mail"];
				$dest='&dest=E';
				if (strlen($r['e_mail'])<5){
				    $dest='';
				    $d_title = 'Stampa richiesta cartacea (cliente senza mail)';
				}
				echo '<td align=\"center\"><button onclick="confirPecSdi(this);return false;" id="doc3_' . $r["clfoco"] . '" url="stampa_richiesta_pecsdi.php?codice='.$r['clfoco'].$dest.'" href="#" title="'. $d_title . '" mail="' . $r["e_mail"] . '" namedoc="Richiesta codice SdI o indirizzo PEC"  class="btn btn-xs btn-default btn-elimina"><i class="glyphicon glyphicon-tag"></i></button>';
                            } else { // quando ho pec e/o codice univoco ma non ho creato pacchetti zip
				echo "<td align=\"center\">";
                            }
                            echo '<a class="btn btn-xs btn-default btn-xml" onclick="confirFae(this);return false;" id="doc1_" '.$r["id_tes"].'" fae_reinvio="'.$r["fae_reinvio"].'" fae_attuale="'.$r["fae_attuale"].'" fae_n_reinvii="'.$r["fattura_elettronica_reinvii"].'" n_fatt="'. $r["numfat"]."/". $r["seziva"].'" target="_blank" href="'.$modulo_fae.'" title="genera il file '.$r["fae_attuale"].' o fai il '.intval($r["fattura_elettronica_reinvii"]+1).'° reinvio ">xml</a><a class="btn btn-xs btn-default" title="Visualizza in stile www.fatturapa.gov.it" href="electronic_invoice.php?id_tes='.$r['id_tes'].'&viewxml" target="_blank"><i class="glyphicon glyphicon-eye-open"></i> </a></td>';
			}
		    } else {
                        echo "<td></td>";
                    }

                    // Colonna "Mail"
                    echo "<td align=\"center\">";
                    if (!empty($r["e_mail"])) {
                        echo '<a class="btn btn-xs btn-default btn-email" onclick="confirMail(this);return false;" id="doc_' . $r["id_tes"] . '" url="' . $modulo . '&dest=E" href="#" title="Mailto: ' . $r["e_mail"] . '"
            mail="' . $r["e_mail"] . '" namedoc="' . $tipodoc . ' n.' . $r["numfat"] . ' del ' . gaz_format_date($r["datfat"]) . '"><i class="glyphicon glyphicon-envelope"></i></a>';
                    } else {
                        echo '<a title="Non hai memorizzato l\'email per questo cliente, inseriscila ora" href="admin_client.php?codice=' . substr($r['clfoco'], 3) . '&Update#email"><i class="glyphicon glyphicon-edit"></i></a>';
                    }
                    echo "</td>";
                    // Colonna "Origine"
                    if ($r["tipdoc"] == 'FAD') {
                        $docs = array_combine(explode(",", $r['refs_id']),
                                              explode(",", $r['refs_num']));
                        echo '<td align="center">';
                        list($doc_templa, $doc) = ($r['ddt_type'] == 'R') ? ['doccmr', 'CMR'] : ['doctra', 'DdT'];
                        $desc = $doc;
                        if (count($docs) > 5) {
                            echo "<a href='report_$doc_templ.php' style='font-size:10px;' class='btn btn-xs btn-default'><i class='glyphicon glyphicon-plane'></i>$doc</a>";
                            $desc = "";
                        }
                        foreach ($docs as $doc_id => $doc_num) {
                            echo " <a class='btn btn-xs btn-default btn-ddt' title='Visualizza il $doc' href='stampa_docven.php?id_tes=$doc_id&template=" . strtoupper($doc) . "' style='font-size:9px;'> $desc $doc_num </a>\n";
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
                    echo "</tr>\n";
                }
                $ctrl_doc = sprintf('%09d', $r['protoc']) . $r['datfat'];
            }
            echo '<tr><td class="FacetFieldCaptionTD" colspan="10" align="right">Querytime: ';
            print_querytime($querytime);
            echo ' sec.</td></tr>';
            ?>
        </table>
    </div>
</form>



<?php
require("../../library/include/footer.php");
?>
