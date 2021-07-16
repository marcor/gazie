<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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

function print_querytime($prev) {
    list($usec, $sec) = explode(" ", microtime());
    $this_time = ((float) $usec + (float) $sec);
    echo round($this_time - $prev, 3);
    return $this_time;
}

$partner_select = !gaz_dbi_get_row($gTables['company_config'], 'var', 'partner_select_mode')['val'];
$tesdoc_e_partners = $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id';

// campi ammissibili per la ricerca
$search_fields = [
    'sezione'
        => "seziva = %d",
    'proto'
        => "protoc = %d",
    'tipo'
        => "tipdoc LIKE '%s'",
    'numero'
        => "numfat LIKE '%%%s%%'",
    'anno'
        => "YEAR(datfat) = %d",
    'fornitore'
        => $partner_select ? "clfoco = '%s'" : "ragso1 LIKE '%s%%'"
];

// creo l'array (header => campi) per l'ordinamento dei record
$sortable_headers = array(
    "Prot." => "protoc",
    "Dat.Reg." => "datreg",
    "Documento" => "tipdoc",
    "Numero" => "numfat",
    "Data" => "datfat",
    "Fornitore" => "ragso1",
    "Info" => "",
    "Stampa" => "",
    "Cancella" => ""
);

require("../../library/include/header.php");
$script_transl = HeadMain();

$ts = new TableSorter(
    !$partner_select && isset($_GET["fornitore"]) ? $tesdoc_e_partners : $gTables['tesdoc'], 
    $passo, 
    ['datreg' => 'desc', 'protoc' => 'desc'], 
    ['sezione' => 1, 'tipo' => 'AF_']
);

# le select spaziano solo tra i documenti d'acquisto del sezionale corrente
$where_select = sprintf("tipdoc LIKE 'AF_' AND seziva = %d", $sezione);

?>
<script>
$(function() {
	$("#dialog_delete").dialog({ autoOpen: false });
	$('.dialog_delete').click(function() {
		$("p#idcodice").html($(this).attr("ref"));
		$("p#iddescri").html($(this).attr("fornitore"));
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
						data: {'type':'docacq',id_tes:id},
						type: 'POST',
						url: '../acquis/delete.php',
						success: function(output){
		                    //alert(output);
							window.location.replace("./report_docacq.php");
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
<form method="GET" >
	<div style="display:none" id="dialog_delete" title="Conferma eliminazione">
        <p><b>documento di acquisto:</b></p>
        <p>ID:</p>
        <p class="ui-state-highlight" id="idcodice"></p>
        <p>Fornitore</p>
        <p class="ui-state-highlight" id="iddescri"></p>
	</div>
    <div align="center" class="FacetFormHeaderFont">
        <?php echo $script_transl['title']; ?>

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
<?php
    list ($usec, $sec) = explode(' ', microtime());
    $querytime = ((float) $usec + (float) $sec);
    $querytime_before = $querytime;
    $ts->output_navbar();
?>
    <div class="box-primary table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
        <tr>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="text" placeholder="Cerca Prot." class="input-sm form-control" name="proto" value="<?php if (isset($proto)) print $proto; ?>" maxlength="6" tabindex="1" class="FacetInput">
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
<?php
                gaz_flt_disp_select("tipo", "tipdoc as tipo", $gTables["tesdoc"], $where_select, "tipo ASC");
?>
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="text" placeholder="Cerca Num." class="input-sm form-control" name="numero" value="<?php if (isset($numero)) print $numero; ?>" tabindex="3" class="FacetInput">			
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
<?php 
                gaz_flt_disp_select("anno", "YEAR(datfat) AS anno", $gTables["tesdoc"],  $where_select, "anno DESC");
?>
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
<?php 
                if ($partner_select) {
                    gaz_flt_disp_select("fornitore", "clfoco AS fornitore, ragso1 as nome", 
                        $tesdoc_e_partners,
                        $where_select, "nome ASC", "nome");
                } else { 
?>
                    <input type="text" placeholder="Cerca fornitore" class="input-sm form-control" name="fornitore" value="<?php if (isset($fornitore)) print $fornitore; ?>" tabindex="5" class="FacetInput"> 
<?php 
                } 
?>
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="6" onClick="javascript:document.report.all.value = 1;">
                <?php $ts->output_order_form(); ?>
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <a class="btn btn-xs btn-default" href="?" tabindex="7">Reset</a>
            </td>
        </tr>
        <tr>
<?php
            $ts->output_headers();
?>
        </tr>
<?php

//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesdoc'].".protoc,".$gTables['tesdoc'].".datfat,".$gTables['tesdoc'].".numfat,".$gTables['tesdoc'].".tipdoc,".$gTables['tesdoc'].".clfoco,".$gTables['tesdoc'].".id_tes,".$gTables['tesdoc'].".datreg,".$gTables['tesdoc'].".fattura_elettronica_original_name,". $gTables['tesdoc'].".id_con,".$gTables['anagra'].".ragso1",$tesdoc_e_partners, $ts->where, $ts->orderby, $ts->getOffset(), $ts->getLimit(),"protoc,datfat");
$paymov = new Schedule();

// creo un array con gli ultimi documenti dei vari anni (gli unici eliminabili senza far saltare il protocollo del registro IVA)
$rs_last_docs = gaz_dbi_query("SELECT id_tes 
            FROM ".$gTables['tesdoc']." AS t1
            JOIN ( SELECT MAX(protoc) AS max_protoc FROM ".$gTables['tesdoc']." WHERE tipdoc LIKE 'A%' AND seziva = ".$sezione." GROUP BY YEAR(datreg)) AS t2 
            ON t1.protoc = t2.max_protoc WHERE t1.tipdoc LIKE 'A%' AND t1.seziva = ".$sezione);
$year_last_protoc_id_tes=[];
while ($ld = gaz_dbi_fetch_array($rs_last_docs)){
    $year_last_protoc_id_tes[$ld['id_tes']]=true;
}
// fine creazione array con i documenti eliminabili

while ($row = gaz_dbi_fetch_array($result)) {
    // faccio il check per vedere se ci sono righi da trasferire in contabilità di magazzino
    $ck = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes=". $row['id_tes']." AND  LENGTH(TRIM(codart))>=1 AND tiprig=0 AND id_mag=0");
    $check = gaz_dbi_fetch_array($ck);
    // fine check magazzino
	// se contabilizzato trovo l'eventuale stato dei pagamenti 
	$paymov_status = false;
	if ($row['id_con'] > 0) {
		$tesmov = gaz_dbi_get_row($gTables['tesmov'], 'id_tes', $row['id_con']);
        if ($tesmov) {
            $paymov->getStatus(substr($tesmov['datreg'],0,4).$tesmov['regiva'].$tesmov['seziva']. str_pad($tesmov['protoc'], 9, 0, STR_PAD_LEFT)); // passo il valore formattato di id_tesdoc_ref
            $paymov_status = $paymov->Status;
            // riprendo il rigo  della contabilità con il cliente per avere l'importo 
            $importo = gaz_dbi_get_row($gTables['rigmoc'], 'id_tes', $row['id_con'], "AND codcon = ".$row['clfoco']);
        }
	}
  $template=""; 
    $y = substr($row['datfat'], 0, 4);
    if ($row["tipdoc"] == 'AFA') {
        $tipodoc = "Fattura";
        $modulo = "stampa_docacq.php?id_tes=" . $row['id_tes']."&template=".$template;
        $modifi = "admin_docacq.php?Update&id_tes=" . $row['id_tes'];
    } elseif ($row["tipdoc"] == 'AFD') {
        $tipodoc = "Nota Debito";
        $modulo = "stampa_docacq.php?id_tes=" . $row['id_tes']."&template=".$template;
        $modifi = "admin_docacq.php?Update&id_tes=" . $row['id_tes'];
    } elseif ($row["tipdoc"] == 'AFC') {
        $tipodoc = "Nota Credito";
        $modulo = "stampa_docacq.php?id_tes=" . $row['id_tes']."&template=".$template;
        $modifi = "admin_docacq.php?Update&id_tes=" . $row['id_tes'];
    } elseif ($row["tipdoc"] == 'AFT') {
        $tipodoc = "Fattura";
        $modulo = "stampa_docacq.php?id_tes=" . $row['id_tes']."&template=".$template;
        $modifi = "";
	}
    $clfoco = gaz_dbi_get_row($gTables['clfoco'], 'codice', $row['clfoco']);
    $anagra = gaz_dbi_get_row($gTables['anagra'], 'id', $clfoco['id_anagra']);
    echo "<tr class=\"FacetDataTD\">";
    if (!empty($modifi)) {
        echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"" . $modifi . "\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . $row["protoc"] . "</td>";
    } else {
        echo "<td><button class=\"btn btn-xs btn-default btn-edit disabled\">" . $row["protoc"] . " &nbsp;</button></td>";
    }
    echo "<td>" . gaz_format_date($row["datreg"]) . " &nbsp;</td>";
    if (empty($row["fattura_elettronica_original_name"])) {
        print '<td>'.$tipodoc."</td>\n";
    } else {
        print '<td>';
        print '<a class="btn btn-xs btn-default btn-xml" target="_blank" href="view_fae.php?id_tes=' . $row["id_tes"] . '">'.$tipodoc.' '.$row["fattura_elettronica_original_name"] . '</a>';
        print '<a class="btn btn-xs btn-default" href="download_fattura_elettronica.php?id='.$row["id_tes"].'"><i class="glyphicon glyphicon-download"></i></a>';
        print '</td>';
    }
    echo "<td>" . $row["numfat"] . " &nbsp;</td>";
    echo "<td>" . gaz_format_date($row["datfat"]) . " &nbsp;</td>";
    echo "<td><a title=\"Dettagli fornitore\" href=\"report_fornit.php?nome=" . htmlspecialchars($anagra["ragso1"]) . "\">" . $anagra["ragso1"] . ((empty($anagra["ragso2"]))?"":" ".$anagra["ragso2"]) . "</a>&nbsp;</td>";
// Colonna movimenti (info)
    echo "<td align=\"center\">";
    if ($row["id_con"] > 0) {
      // non usando le transazioni devo aggiunger un controllo di effettiva esistenza della testata di movimento contabile, se qualcosa non è andato per il verso giusto elimini il riferimento
      $existtesmov = gaz_dbi_get_row($gTables['tesmov'], 'id_tes', $row['id_con']);
      if ($existtesmov){
        echo " <a class=\"btn btn-xs btn-".$paymov_status['style']."\" style=\"font-size:10px;\" title=\"Modifica il movimento contabile " . $row["id_con"] . " generato da questo documento\" href=\"../contab/admin_movcon.php?id_tes=" . $row["id_con"] . "&Update\"> <i class=\"glyphicon glyphicon-euro\"></i> " .((isset($importo["import"]))?$importo["import"]:'0.00'). "</a> ";
      } else {
        if ( $row['id_con'] >=1 ) { 
            // ripristino la possibilità di contabilizzare il documento che ho trovato orfano ma ATTENZIONE!!!
            // NON RIESCO A TROVARE IN QUALE CIRCOSTANZA E DA QUALE SCRIPT A VOLTE VIENE CANCELLATO IL MOVIMENTO CONTABILE DI ALCUNE FATTURE DI ACQUISTO!!!
            // mi tengo l'id_con inesistente sulla colonna status per tentare di capire quando questo avviene 
            // gaz_dbi_query("UPDATE ".$gTables['tesdoc']." SET id_con = 0 WHERE id_tes = ".$row['id_tes']);
            // gaz_dbi_query("UPDATE ".$gTables['tesdoc']." SET status = 'IDCON".$row["id_con"]."' WHERE id_tes = ".$row['id_tes']);
        }
        echo "<a class=\"btn btn-xs btn-default btn-cont\" href=\"accounting_documents.php?type=A&last=" . $row["protoc"] . "\">Contabilizza</a>";					
      }
    } else {
        echo "<a class=\"btn btn-xs btn-default btn-cont\" href=\"accounting_documents.php?type=A&last=" . $row["protoc"] . "\">Contabilizza</a>";					
    }
    if ($check) { // ho qualche rigo da traferire
        echo " <a class=\"btn btn-xs btn-default btn-warning\" href=\"../magazz/genera_movmag.php\">Movimenta magazzino</a> ";
    }
    echo "</td>";
    echo "<td><a class=\"btn btn-xs btn-default\" href=\"" . $modulo . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a></td>";
        echo "<td>";
		// faccio il controllo di eliminazione dell'ultima fattura ricevuta 
		if (isset($year_last_protoc_id_tes[$row['id_tes']])) {
			?>			
			<a class="btn btn-xs btn-default btn-elimina dialog_delete" title="Elimina questo documento" ref="<?php echo $row['id_tes'];?>" fornitore="<?php echo $anagra['ragso1']; ?>">
				<i class="glyphicon glyphicon-remove"></i>
			</a>
			<?php

		} else {
			?>   
			<button title="Non puoi eliminare un documento diverso dall'ultimo emesso" class="btn btn-xs btn-default btn-elimina disabled"><i class="glyphicon glyphicon-remove"></i></button>
			<?php
		}
    echo "</td></tr>";
}
	echo '<tr><td colspan="6"></td><td colspan="3">Query & render: '; print_querytime($querytime); echo " sec.</td></tr>\n";
?>
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
        $("form").find( ":input" ).prop( "disabled", false );
    });
</script>

<?php
require("../../library/include/footer.php");
?>
