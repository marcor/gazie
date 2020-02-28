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
$anagrafica = new Anagrafica();
$msg = '';


// ************** VARIABILI PER GESTIONE TESTO LETTERA SOLLECITO **********************
$letter_sol=0;
$letter_head="<style>
        table {
            font-size: 8pt;
        }
        </style><p><strong>La presente lettera quale sollecito per il pagamento del saldo del Vostro debito come dal prospetto sottosegnato: </strong></p><p>&nbsp;</p>\n<hr />\n";
$form['oggetto'] = 'Sollecito di pagamento';

// *************** FINE VARIABILI LETTERA SOLLECITO ******************************


if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['date_ini_D'] = date("d");
    $form['date_ini_M'] = date("m");
    $form['date_ini_Y'] = date("Y");
    $form['expiry_ini'] ='01/01/'.(date("Y")-1);
    $form['expiry_fin'] ='31/12/'.(date("Y")+1);   
    $form['clfoco'] = 0;
	$form['search']['clfoco'] = '';
} else { // accessi successivi
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    $form['ritorno'] = $_POST['ritorno'];
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }
    $form['date_ini_D'] = intval($_POST['date_ini_D']);
    $form['date_ini_M'] = intval($_POST['date_ini_M']);
    $form['date_ini_Y'] = intval($_POST['date_ini_Y']);
    $form['expiry_ini'] = substr($_POST['expiry_ini'], 0, 10);
    $form['expiry_fin'] = substr($_POST['expiry_fin'], 0, 10);
    $form['clfoco'] = intval($_POST['clfoco']);
    $cliente = $anagrafica->getPartner($form['clfoco']);
    if (isset($_POST['return'])) {
        header("Location: " . $form['ritorno']);
        exit;
    }
}

//controllo i campi
if (!checkdate($form['date_ini_M'], $form['date_ini_D'], $form['date_ini_Y'])) {
    $msg .= '0+';
}
// fine controlli

if (isset($_POST['print']) && $msg == '') {
    $_SESSION['print_request'] = array('script_name' => 'print_partner_status',
        'date' => $form['date_ini_Y'] . '-' . $form['date_ini_M'] . '-' . $form['date_ini_D']
    );
    header("Location: sent_print.php");
    exit;
}
if (isset($_POST['sollecito'])) { // ho chiesto di generare una lettera di sollecito riferita a questa scedenza
    $paymov = new Schedule;
    $letter_sol = intval(key($_POST['sollecito']));
    $form['write_date'] = date("Y-m-d", mktime(0, 0, 0, $form['date_ini_M'], $form['date_ini_D'],$form['date_ini_Y']));
	$form['tipo']='SOL';
    $form['status'] = $letter_sol;
    $form['signature'] = 0;
	$_POST['confirm']=true;
	// prima inserisco la nuova lettera di sollecito, poi mi riposiziono sull'id giusto
	// sulla colonna status metto il riferimento alla partita
	require("../inform/lib.data.php");
	$rs_last_n = gaz_dbi_dyn_query("numero", $gTables['letter'], "tipo = 'SOL'", 'id_let DESC', 0, 1);
    $last_n = gaz_dbi_fetch_array($rs_last_n);
    if ($last_n) {
        $form['numero'] = $last_n['numero'] + 1;
    } else {
        $form['numero'] = 1;
    }
	$form['corpo']=$letter_head;
	// qui riepilogo tutte le scadenze della partita sollecitata
	$form['clfoco']=$paymov->getDocumentData($letter_sol)['clfoco'];
    $paymov->getPartnerStatus($form['clfoco'],$form['write_date']);
	$tot_scaduto=0.00;
	$descristato=' saldata ';
	$descri_pay=' pagate ';
    foreach ($paymov->PartnerStatus as $k => $v) {
			$form['corpo'].='<p>';
            if ($paymov->docData[$k]['numdoc'] >= 1) {
                $form['corpo'].= $paymov->docData[$k]['descri'] . ' n.' .
                $paymov->docData[$k]['numdoc'] . '/' .
                $paymov->docData[$k]['seziva'] . ' del ' .
                gaz_format_date($paymov->docData[$k]['datdoc'])."</p>\n";
            } else {
                $form['corpo'].= $paymov->docData[$k]['descri']."</p>\n";
            }
            foreach ($v as $ki => $vi) {
                $v_op = '';
                $cl_exp = '';
                if ($vi['op_val'] >= 0.01) {
                    $v_op = gaz_format_number($vi['op_val']);
                }
                $v_cl = '';
                if ($vi['cl_val'] >= 0.01) {
                    $v_cl = gaz_format_number($vi['cl_val']);
                    $cl_exp = gaz_format_date($vi['cl_exp']);
                }
                $expo = '';
                if ($vi['expo_day'] >= 1) {
                    $expo = $vi['expo_day'];
                    if ($vi['cl_val'] == $vi['op_val']) {
                        $vi['status'] = 2; // la partita è chiusa ma è esposta a rischio insolvenza 
						$descristato=' ( scade tra '.$expo.'gg ) ';
						$descri_pay='esposte ';
                    }
                } else {
                    if ($vi['cl_val'] == $vi['op_val']) { // chiusa e non esposta
                        $cl_exp = '';
						$descristato=' saldata ';
						$descri_pay=' pagate ';
                    } elseif ($vi['status'] == 3) { // SCADUTA
						$tot_scaduto+=($vi['op_val']-$vi['cl_val']);
                        $cl_exp = '';
						$descristato=' <strong> importo scaduto € '.gaz_format_number($vi['op_val']-$vi['cl_val']).'</strong> <span style="color: #ff0000;">#</span>';
						$descri_pay='pagate ';
                    } elseif ($vi['status'] == 9) { // PAGAMENTO ANTICIPATO
                        $vi['expiry'] = $vi['cl_exp'];
						$descristato=' anticipate '.gaz_format_number($vi['cl_val']).'</b>';
						$descri_pay=' pagate ';
                    }
                }
                $form['corpo'].= '<p>Importo: '.$v_op.' '.$descri_pay.': '.$v_cl.' scadenza: '.gaz_format_date($vi['expiry']).$descristato.' </p>';
			}
			$form['corpo'].= '<hr />';
	}
	$form['corpo'].="\n<p>&nbsp;</p><p>Si prega di provvedere con cortese sollecitudine al regolamento del vostro debito <strong>scaduto di € ".gaz_format_number($tot_scaduto)."</strong>. </p>\n";
	$form['corpo'].='<p style="text-align: center;">'.$admin_aziend['ragso1'].' '.$admin_aziend['ragso2']."</p>";
	//print $form['corpo'];
    letterInsert($form);
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup','custom/autocomplete'));
?>
<script>

<?php
echo "
var cal = new CalendarPopup();
var calName = '';
function setMultipleValues(y,m,d) {
     document.getElementById(calName+'_Y').value=y;
     document.getElementById(calName+'_M').selectedIndex=m*1-1;
     document.getElementById(calName+'_D').selectedIndex=d*1-1;
}
function setDate(name) {
  calName = name.toString();
  var year = document.getElementById(calName+'_Y').value.toString();
  var month = document.getElementById(calName+'_M').value.toString();
  var day = document.getElementById(calName+'_D').value.toString();
  var mdy = month+'/'+day+'/'+year;
  cal.setReturnFunction('setMultipleValues');
  cal.showCalendar('anchor', mdy);
}
";
?>
$(function () {
    $("#expiry_ini, #expiry_fin").datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
    $("#expiry_ini, #expiry_fin").change(function () {
        this.form.submit();
    });
    $("html, body").delay(500).animate({scrollTop: $('#scroll_me').offset().top-300}, 1000);
});

function confirmemail(cod_partner,id_let) {
	var cliente=$("#cliente_"+id_let).attr('value');
	$("#confirm_email").attr('title', 'Lettera di sollecito a '+cliente);
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
			"Stampa PDF" : function() {
				window.open('../inform/stampa_letter.php?id_let='+id_let, '');
			},
			"Modifica lettera" : function() {
				window.location.href = '../inform/admin_letter.php?id_let='+id_let+'&Update';
			},
			Annulla : function() {
				$(this).dialog('close');
			},
			"Invia Mail": function() {
				if ( !( emailRegex.test( $("#mailaddress").val() ) ) ) {
					alert('Mail formalmente errata');
				} else {
					$("#mailbutt div").remove();
					var dest=$("#mailaddress").val();
					window.location.href = '../inform/stampa_letter.php?id_let='+id_let+'&dest='+dest;
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

</script>
<?php
echo "<form method=\"POST\" name=\"select\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['ritorno'] . "\" name=\"ritorno\" />\n";
$gForm = new venditForm();
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'];
echo "</div>\n";
echo "<div class=\"table-responsive\">";
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
}
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['date_ini'] . "</td><td colspan=\"3\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('date_ini', $form['date_ini_D'], $form['date_ini_M'], $form['date_ini_Y'], 'FacetSelect', 1);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">Data scadenza inizio</td><td class=\"FacetDataTD\">\n";
echo '<input type="text" id="expiry_ini" name="expiry_ini" placeholder="GG/MM/AAAA" value="'.$form['expiry_ini'].'">';
echo "</td>\n";
echo "<td class=\"FacetFieldCaptionTD\">Data scadenza fine</td><td class=\"FacetDataTD\">\n";
echo '<input type="text" id="expiry_fin" name="expiry_fin" placeholder="GG/MM/AAAA" value="'.$form['expiry_fin'].'">';
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">Cliente</td><td  colspan=\"3\" class=\"FacetDataTD\">\n";
    $select_cliente = new selectPartner("clfoco");
    $select_cliente->selectDocPartner('clfoco', $form['clfoco'], $form['search']['clfoco'], 'clfoco', array('La ricerca non ha dato risultati!','Inserire almeno 2 caratteri!','Cambia cliente'), $admin_aziend['mascli']);
echo "</td>\n";
echo "</tr>\n";
echo "\t<tr class=\"FacetFieldCaptionTD\">\n";
echo "<td align=\"left\"><input type=\"submit\" name=\"return\" value=\"" . $script_transl['return'] . "\">\n";
echo '<td align="right" colspan="2"> <input type="submit" accesskey="i" name="confirm" value="';
echo $script_transl['submit'];
echo '" tabindex="100" >';
echo "\t </td>\n";
echo "\t </tr>\n";
echo "</table></div>\n";

if (isset($_POST['confirm'])) {
    $paymov = new Schedule;
	$cliref=($form['clfoco']>100000000)?$form['clfoco']:$admin_aziend['mascli'];
    $paymov->setScheduledPartner($cliref);
	echo "<div class=\"table-responsive\">";
    echo "<table class=\"Tlarge table table-striped table-bordered table-condensed\">";
    if (sizeof($paymov->Partners) > 0) {
        $anagrafica = new Anagrafica();
        echo "<tr>";
        $linkHeaders = new linkHeaders($script_transl['header']);
        $linkHeaders->setAlign(array('right', 'right', 'right', 'center', 'center', 'center', 'center'));
        $linkHeaders->output();
        echo "</tr>";
        foreach ($paymov->Partners as $p) {
            $ctrl_close_partner = false;
            $prt = $anagrafica->getPartner($p);
            echo "<tr>";
            echo "<td class=\"FacetFieldCaptionTD text-center\" colspan='7'>" . $prt['ragso1'] . " " . $prt['ragso2'] .
            " tel:" . gaz_html_call_tel($prt['telefo']) .
            " fax:" . $prt['fax'] .
            " mob:" . gaz_html_call_tel($prt['cell']) . "</td>";
            echo "</tr>\n";
            $paymov->getPartnerStatus($p, $form['date_ini_Y'] . '-' . $form['date_ini_M'] . '-' . $form['date_ini_D']);
            foreach ($paymov->PartnerStatus as $k => $v) {
				$scrl=($k==$letter_sol)?true:false;
                echo "<tr>";
                echo "<td class=\"FacetDataTDred\" colspan='2' ";
				echo ($scrl)?'id="scroll_me"':'';
				echo ">REF: $k</td>";
                echo "<td colspan='3'><a class=\"btn btn-xs btn-default btn-edit\" href=\"../contab/admin_movcon.php?Update&id_tes=" . $paymov->docData[$k]['id_tes'] . "\"><i class=\"glyphicon glyphicon-edit\"></i>" .
                $paymov->docData[$k]['id_tes'] . ' ' .
                $paymov->docData[$k]['descri'];
                if ($paymov->docData[$k]['numdoc'] >= 1) {
                    echo ' n.' .
                    $paymov->docData[$k]['numdoc'] . '/' .
                    $paymov->docData[$k]['seziva'] . ' del ' .
                    gaz_format_date($paymov->docData[$k]['datdoc']);
                }
                echo "</a></td>\n";
				// controllo la presenza di eventuali lettere di sollecito sugli scaduti
				$solleciti=getLetters($k);
				if (count($solleciti)>=1){
					echo '<td colspan=2 class="bg-warning text-center"> Sollecito/i: ';
					foreach($solleciti as $vs){
						echo '<a class="btn btn-xs btn-danger" onclick="confirmemail(\''.$vs["clfoco"].'\',\''.$vs['id_let'].'\');" id="cliente_'.$vs['id_let'].'" value="'.$prt['ragso1'] . " " . $prt['ragso2'].'" id="doc'.$vs["id_let"].'" ><i class="glyphicon glyphicon-print"></i> '.$vs['numero'].' <i class="glyphicon glyphicon-envelope"></i></a> ';
					}
				}else{
					echo '<td colspan=2>';
				}
				echo "</td>\n</tr>\n";
                foreach ($v as $ki => $vi) {
                    $ctrl_close_paymov = false; 
                    $lnk = '';
                    $class_paymov = 'btn btn-success';
                    $v_op = '';
                    $cl_exp = '';
                    if ($vi['op_val'] >= 0.01) {
                        $v_op = gaz_format_number($vi['op_val']);
                    }
                    $v_cl = '';
                    if ($vi['cl_val'] >= 0.01) {
                        $v_cl = gaz_format_number($vi['cl_val']);
                        $cl_exp = gaz_format_date($vi['cl_exp']);
                    }
                    $expo = '';
                    if ($vi['expo_day'] >= 1) {
                        $expo = $vi['expo_day'];
                        if (round($vi['cl_val'],2) == round($vi['op_val'],2)) {
                            $vi['status'] = 2; // la partita è chiusa ma è esposta a rischio insolvenza 
                            $class_paymov = 'btn btn-warning';
                        }
                    } else {
                        if (round($vi['cl_val'],2) == round($vi['op_val'],2)) { // chiusa e non esposta
                            $cl_exp = '';
                            $class_paymov = 'btn btn-success';
                            $ctrl_close_paymov = true;
                        } elseif ($vi['status'] == 3) { // SCADUTA
                            $cl_exp = '';
                            $class_paymov = 'btn btn-danger';
                            $lnk = " &nbsp;<a title=\"Riscuoti\" class=\"btn btn-xs btn-default btn-pagamento\" href=\"customer_payment.php?partner=" . $p . "\"><i class=\"glyphicon glyphicon-euro\"></i></a>";
                            $expo= ' &nbsp;<button type="submit" name="sollecito[' . $k . ']"  class="btn btn-xs btn-warning" ><i class="glyphicon glyphicon-warning-sign"> Nuovo sollecito</i></button>';
                        } elseif ($vi['status'] == 9) { // PAGAMENTO ANTICIPATO
                            $class_paymov = 'btn btn-default';
                            $vi['expiry'] = $vi['cl_exp'];
                        } elseif ($vi['status'] == 0) { // APERTA
							$class_paymov = 'btn btn-edit';
                            $lnk = " &nbsp;<a title=\"Riscuoti\" class=\"btn btn-xs btn-default btn-pagamento\" href=\"customer_payment.php?partner=" . $p . "\"><i class=\"glyphicon glyphicon-euro\"></i></a>";
                        }
                    }
					// compare dates
					$di=strtotime(gaz_format_date($form['expiry_ini'], true));
					$df=strtotime(gaz_format_date($form['expiry_fin'], true));
					$de=strtotime($vi['expiry']);
					if ($de>=$di&&$de<=$df){ // scadenza compresa tra le date selezionate
						echo "<tr>";
						echo "<td class='text-right'>".$vi['id'] . "</td>";
						echo "<td class='text-right'>" . $v_op . "</td>";
						echo "<td class='text-center'>" . gaz_format_date($vi['expiry']) . "</td>";
						echo "<td class='text-right'>" . $v_cl . "</td>";
						echo "<td class='text-center'>" . $cl_exp . "</td>";
						echo "<td class='text-center'>" . $expo . "</td>";
						echo "<td class='" . $class_paymov . "' align=\"center\">" . $script_transl['status_value'][$vi['status']] . " &nbsp; $lnk</td>";
						echo "</tr>\n";
					}
                }
                if ($ctrl_close_paymov) {
                    $ctrl_close_partner = true; 
                    echo "<tr>";
                    echo '<td class="text-right" colspan="7"> &nbsp;<a title="Cancella tutti i movimenti relativi a questa partita oramai chiusa (rimarranno comunque i movimenti contabili)" class="btn btn-xs btn-default btn-elimina" href="delete_schedule.php?id_tesdoc_ref=' . $k . '">' . $script_transl['delete'] . ' <i class="glyphicon glyphicon-remove"></i></a></td>';
                    echo "</tr>\n";
                    echo '<tr><td colspan="7"></td></tr>';
                }
            }
            if ($ctrl_close_partner == true) {
                echo "<tr>";
                echo "<td class=\"text-right\" colspan='7'><a title=\"Elimina tutte le partite chiuse di questo cliente\" class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_schedule.php?partner=" . $p . "\"><i class=\"glyphicon glyphicon-remove\"></i> &nbsp;" . $script_transl['remove'] . $prt['ragso1'] . " " . $prt['ragso2'] . "</a></td>";
                echo "</tr>\n";
                echo '<tr><td colspan="7"></td></tr>';
            }
        }
        echo "\t<tr>\n";
        echo '<td class="FacetFieldCaptionTD" colspan="5" align="right"';
		echo (!$scrl)?'id="scroll_me"':'';
		echo '><input type="submit" name="print" value="';
        echo $script_transl['print'];
        echo '">';
        echo "\t </td>\n";
        echo "<td class=\"text-right\" colspan='2'><a title=\"Elimina tutte le partite chiuse di tutti i clienti\" class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_schedule.php?all\"><i class=\"glyphicon glyphicon-remove\"></i> &nbsp;" . $script_transl['remove'] .  " TUTTI!!!</a></td>";
        echo "\t </tr>\n";
    } else {
        echo "<tr><td class=\"FacetDataTDred\" align=\"center\">" . $script_transl['errors'][1] . "</td></tr>\n";
    }
    echo "</table></div></form>";
}
?>
<div class="modal" id="confirm_email" title="Invia mail...">
    <fieldset>
        <div>
            <label id="maillabel" for="mailaddress">all'indirizzo:</label>
            <input type="text"  placeholder="seleziona sotto oppure digita" value="" id="mailaddress" name="mailaddress" maxlength="100" size="40" />
        </div>
        <div id="mailbutt">
		</div>
    </fieldset>
</div>

<?php
require("../../library/include/footer.php");

function getLetters($id_tesdoc_ref){
	global $gTables;
	$acc=array();
	$letters=gaz_dbi_dyn_query("*", $gTables['letter'], "status = '".$id_tesdoc_ref."'", 'write_date DESC');
	while ($r=gaz_dbi_fetch_array($letters)) {
		$acc[]=$r;
	}
	return $acc;
}

?>