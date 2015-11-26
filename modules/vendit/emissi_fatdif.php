<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
if (!ini_get('safe_mode')) { //se me lo posso permettere...
    ini_set('memory_limit', '128M');
    gaz_set_time_limit(0);
}
$msg = '';
$clienti = $admin_aziend['mascli'];

function getInvoiceableBillLimits($Y, $M, $D, $sez = 1, $set_newdate = false) {
    $acc = array();
    global $gTables;
    $utsexe = mktime(0, 0, 0, $M, $D, $Y);
    $acc['date_exe'] = date("Y-m-d", $utsexe);
    $acc['date_ini'] = $acc['date_exe'];
    $acc['date_fin'] = $acc['date_ini'];
    $ini_ctrl = new DateTime($acc['date_exe']);
    $fin_ctrl = $ini_ctrl;
    // ricavo i limiti di fatturabilità e le date dei vari tipi di DdT
    $doctype = array('DDT', 'DDV', 'DDY');
    foreach ($doctype as $k => $v) {
        switch ($v) {
            default :
            case 'DDT':
                $utsddt = mktime(0, 0, 0, $M, $D, $Y);
                $dateddt = date("Y-m-d", $utsddt);
                $acc[$v]['n_invoiceable'] = gaz_dbi_record_count($gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi <= '" . $dateddt . "'");
                $acc[$v]['n_remainder'] = gaz_dbi_record_count($gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi > '" . $dateddt . "'");
                $rs_first = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi <= '" . $dateddt . "'", "numdoc ASC", 0, 1);
                $rs_last = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi <= '" . $dateddt . "'", "numdoc DESC", 0, 1);
                break;
            case 'DDV':
                // se sono in conto visione ho l'obbligo di fatturazione solo dopo 1 anno dall'emissione
                $utsddv = mktime(0, 0, 0, $M, $D, $Y - 1);
                $dateddv = date("Y-m-d", $utsddv);
                $acc[$v]['n_invoiceable'] = gaz_dbi_record_count($gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi <= '" . $dateddv . "'");
                $acc[$v]['n_remainder'] = gaz_dbi_record_count($gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi > '" . $dateddv . "'");
                $rs_first = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi <= '" . $dateddv . "'", "numdoc ASC", 0, 1);
                $rs_last = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi <= '" . $dateddv . "'", "numdoc DESC", 0, 1);
                break;
            case 'DDY':
                // se sono in conto triangolazione che faccio? Al momento non li fatturo!
                $utsddy = mktime(0, 0, 0, $M, $D, $Y);
                $dateddy = date("Y-m-d", $utsddy);
                $acc[$v]['n_invoiceable'] = gaz_dbi_record_count($gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi <= '" . $dateddy . "'");
                $acc[$v]['n_remainder'] = gaz_dbi_record_count($gTables['tesdoc'], "tipdoc = '$v' AND seziva = $sez AND datemi > '" . $dateddy . "'");
                break;
        }
        $first = gaz_dbi_fetch_array($rs_first);
        if ($first) {
            // faccio il push solo se ho una data precedente 
            $nd = new DateTime($first['datemi']);
            if ($nd < $ini_ctrl) {
                $acc['date_ini'] = $first['datemi'];
                $ini_ctrl = $nd;
            }
        }
        $last = gaz_dbi_fetch_array($rs_last);
        if ($last) {
            // faccio il push solo se ho una data precedente
            $nd = new DateTime($last['datemi']);
            if ($nd < $fin_ctrl) {
                $acc['date_fin'] = $last['datemi'];
                $fin_ctrl = $nd;
            }
        }
    }
    // ricavo il progressivo annuo del numero protocollo
    $rs_last_invoice_protoc = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = $Y AND tipdoc LIKE 'F%' AND seziva = $sez", "protoc DESC", 0, 1);
    $last_invoice_protoc = gaz_dbi_fetch_array($rs_last_invoice_protoc);
    if ($last_invoice_protoc) {
        $acc['last_pr'] = $last_invoice_protoc['protoc'];
    } else {
        $acc['last_pr'] = 0;
    }
    // ricavo il progressivo annuo del numero fattura
    $rs_last_invoice_numfat = gaz_dbi_dyn_query("numdoc, numfat*1 AS fattura", $gTables['tesdoc'], "YEAR(datemi) = $Y AND tipdoc LIKE 'FA%' AND seziva = $sez", "fattura DESC", 0, 1);
    $last_invoice_numfat = gaz_dbi_fetch_array($rs_last_invoice_numfat);
    if ($last_invoice_numfat) {
        $acc['last_nu'] = $last_invoice_numfat['fattura'];
    } else {
        $acc['last_nu'] = 0;
    }
    if ($set_newdate) {
        $acc['date_ini_Y'] = date("Y", strtotime($acc['date_ini']));
        $acc['date_ini_M'] = date("m", strtotime($acc['date_ini']));
        $acc['date_ini_D'] = date("d", strtotime($acc['date_ini']));
        $acc['date_fin_Y'] = date("Y", strtotime($acc['date_fin']));
        $acc['date_fin_M'] = date("m", strtotime($acc['date_fin']));
        $acc['date_fin_D'] = date("d", strtotime($acc['date_fin']));
    }
    return $acc;
}

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    if (isset($_GET['seziva'])) {
        $form['seziva'] = intval($_GET['seziva']);
    } else {
        $form['seziva'] = 1;
    }
    $form['clfoco'] = 0;
    $form['search']['clfoco'] = '';
    $form['excludedDtT'] = array();
    $form['date_exe_Y'] = date("Y");
    $form['date_exe_M'] = date("m");
    $form['date_exe_D'] = date("d");
    $ini_data = getInvoiceableBillLimits($form['date_exe_Y'], $form['date_exe_M'], $form['date_exe_D'], $form['seziva'], true);
    $form += $ini_data;
} else { // accessi successivi
    $form['hidden_req'] = filter_input(INPUT_POST, 'hidden_req');
    $form['ritorno'] = filter_input(INPUT_POST, 'ritorno');
    $form['seziva'] = intval($_POST['seziva']);
    $form['clfoco'] = substr($_POST['clfoco'], 0, 15);
    foreach ($_POST['search'] as $k => $v) {
        $form['search'][$k] = $v;
    }
    $form['date_ini_D'] = intval($_POST['date_ini_D']);
    $form['date_ini_M'] = intval($_POST['date_ini_M']);
    $form['date_ini_Y'] = intval($_POST['date_ini_Y']);
    $form['date_fin_D'] = intval($_POST['date_fin_D']);
    $form['date_fin_M'] = intval($_POST['date_fin_M']);
    $form['date_fin_Y'] = intval($_POST['date_fin_Y']);
    $form['date_exe_Y'] = intval($_POST['date_exe_Y']);
    $form['date_exe_M'] = intval($_POST['date_exe_M']);
    $form['date_exe_D'] = intval($_POST['date_exe_D']);
    $form['excludedDtT'] = filter_input(INPUT_POST, 'excludedDtT');
    if ($form['hidden_req'] == 'clfoco') {
        $anagrafica = new Anagrafica();
        if (preg_match("/^id_([0-9]+)$/", $form['clfoco'], $match)) {
            $partner = $anagrafica->getPartnerData($match[1], 1);
        } else {
            $partner = $anagrafica->getPartner($form['clfoco']);
        }
        $form['hidden_req'] = '';
    }

    // escludo un ddt
    if (isset($_POST['add_ex'])) {
        $form['preview'] = '';
        $form['excludedDtT'][] = key($_POST['add_ex']);
    }
    // ripristino il ddt
    if (isset($_POST['del_ex'])) {
        $form['preview'] = '';
        $key = array_search(key($form['del_ex']), $form['excludedDtT']);
        unset($form['excludedDtT'][$key]);
    }
    if (!checkdate($form['date_exe_M'], $form['date_exe_D'], $form['date_exe_Y']) ||
            !checkdate($form['date_ini_M'], $form['date_ini_D'], $form['date_ini_Y']) ||
            !checkdate($form['date_fin_M'], $form['date_fin_D'], $form['date_fin_Y'])) {
        $msg .= '0+';
    }
    $utsexe = mktime(0, 0, 0, $form['date_exe_M'], $form['date_exe_D'], $form['date_exe_Y']);
    $utsini = mktime(0, 0, 0, $form['date_ini_M'], $form['date_ini_D'], $form['date_ini_Y']);
    $utsfin = mktime(0, 0, 0, $form['date_fin_M'], $form['date_fin_D'], $form['date_fin_Y']);
    if ($utsexe < $utsfin) {
        $msg .="1+";
    }
    if ($utsini > $utsfin) {
        $msg .="2+";
    }
    $ini_data = getInvoiceableBillLimits($form['date_exe_Y'], $form['date_exe_M'], $form['date_exe_D'], $form['seziva']);
    $form += $ini_data;
}

function InvoiceFromBills($period, $sez = 1, $cliente = 0, $excludeDdt = array()) {
    //preparo la query al database
    $clientesel = '';
    if ($cliente > 0) {
        $clientesel = ' AND clfoco = ' . $cliente;
    }
    $orderby = "search ASC, ragbol ASC, pagame ASC, numdoc ASC, id_rig ASC";
    $where = "tipdoc = 'DDT' AND datemi BETWEEN '" . $period['inizio'] . "' AND '" . $period['fine'] . "' AND seziva = '$sez' and da_fatturare=true " . $clientesel;
    //recupero i dati dal DB (testate+cliente+pagamento+righi)
    $field = 'tes.id_tes,tes.clfoco,tes.numdoc,tes.pagame,tes.traspo,tes.speban,tes.banapp,tes.datemi,
              CONCAT(ana.search,\' \',ana.ragso2,\' \',ana.citspe,\' \',ana.prospe) AS ragsoc,
              cli.codice,cli.ragdoc,
              pag.tippag,pag.incaut,pag.numrat,pag.descri AS despag,
              rig.id_tes,rig.id_rig,rig.codart,rig.descri,rig.unimis,rig.quanti,rig.prelis,rig.tiprig,rig.sconto,' .
            /** inizio modifica FP 20/10/15 */
            'tes.da_fatturare,tes.data_ordine,tes.ragbol ';
    /** fine modifica FP */
    $from = $gTables['tesdoc'] . ' AS tes ' .
            'LEFT JOIN ' . $gTables['clfoco'] . ' AS cli ON tes.clfoco=cli.codice ' .
            'LEFT JOIN ' . $gTables['anagra'] . ' AS ana ON cli.id_anagra=ana.id ' .
            'LEFT JOIN ' . $gTables['pagame'] . ' AS pag ON pag.codice=tes.pagame ' .
            'LEFT JOIN ' . $gTables['rigdoc'] . ' AS rig ON rig.id_tes=tes.id_tes ';
    $result = gaz_dbi_dyn_query($field, $from, $where, $orderby);
    $ctrlnum = gaz_dbi_num_rows($result);
    $fatture = array();
    if ($ctrlnum) {
        //creo l'array associativo testate-righi
        $ctrlc = 0;
        $ctrlp = 0;
        $ctrld = 0;
        $ctrlr = 0;    // rappresenta il raggruppamento bolle
        $totale_imponibile = 0;
        while ($row = gaz_dbi_fetch_array($result)) {
            if (in_array($row['id_tes'], $excludedDtT) and $ctrld != $row['id_tes']) { // se è tra gli esclusi vado avanti ma mantengo il riferimento
                $fatture['no'][] = array('id' => $row['id_tes'],
                    'ragionesociale' => $row['ragsoc'],
                    'numero' => $row['numdoc'],
                    'data' => $row['datemi'],
                    'pagamento' => $row['despag']
                );
                continue;
            }
            if ($row['clfoco'] != $ctrlc or $row['pagame'] != $ctrlp or $row['ragbol'] != $ctrlr or ( $row['id_tes'] != $ctrld and $row['ragdoc'] == 'N')) {  //se è un'altro cliente o il cliente ha un pagamento diverso dal precedente o è cambiato il raggruppamento bolle
                if ($ctrlc > 0 and $ctrlp > 0) {  //se non è la prima fattura pongo il totale della precedente nell'array
                    $fatture['yes'][$last_pr]['totale'] = $totale_imponibile;
                }
                $totale_imponibile = 0;
                $last_pr ++;
                $last_nu ++;
                // nuova testata fattura
                $fatture['yes'][$last_pr] = array('numero' => $last_nu, 'codicecliente' => $row['clfoco'], 'ragionesociale' => $row['ragsoc']);
                $fatture['yes'][$last_pr]['speseincasso'] = $row['numrat'] * $row['speban'];
                //$totale_imponibile += $fatture['yes'][$last_pr]['speseincasso'];
            }
            if ($row['id_tes'] != $ctrld) {  //se è un'altro ddt
                if ($row['clfoco'] == $ctrlc and $row['pagame'] != $ctrlp) {
                    $fatture['yes'][$last_pr]['righi'][] = array('codice' => '_MSG_',
                        'descrizione' => ' Cliente con diversi pagamenti! '
                    );
                }
                $fatture['yes'][$last_pr]['righi'][] = array('codice' => '_DES_',
                    'numero' => $row['numdoc'],
                    'id' => $row['id_tes'],
                    'data' => $row['datemi'],
                    'codpag' => $row['pagame'],
                    'despag' => $row['despag']
                );
                if ($row['incaut'] == 'S') {
                    $fatture['yes'][$last_pr]['righi'][] = array('codice' => '_MSG_',
                        'descrizione' => ' Pagamento che prevede l\'incasso automatico! '
                    );
                }
                if (($row['tippag'] == 'B' or $row['tippag'] == 'T') and $row['banapp'] == 0) {
                    $fatture['yes'][$last_pr]['righi'][] = array('codice' => '_MSG_',
                        'descrizione' => ' ATTENZIONE! MANCA LA BANCA D\'APPOGGIO ! '
                    );
                }
                if ($row['traspo'] > 0) {
                    $fatture['yes'][$last_pr]['righi'][] = array('codice' => '_TRA_',
                        'descrizione' => 'TRASPORTO',
                        'importo' => $row['traspo']
                    );
                    $totale_imponibile += $row['traspo'];
                }
            }
            $importo_rigo = CalcolaImportoRigo($row['quanti'], $row['prelis'], $row['sconto']);
            if ($row['tiprig'] == 1) {
                $importo_rigo = CalcolaImportoRigo(1, $row['prelis'], 0);
            }
            $totale_imponibile += $importo_rigo;
            //aggiungo il rigo
            $fatture['yes'][$last_pr]['righi'][] = array('codice' => $row['codart'],
                'descrizione' => $row['descri'],
                'unitamisura' => $row['unimis'],
                'quantita' => $row['quanti'],
                'prezzo' => $row['prelis'],
                'sconto' => $row['sconto'],
                'importo' => $importo_rigo);
            $ctrld = $row['id_tes'];
            $ctrlc = $row['clfoco'];
            $ctrlp = $row['pagame'];
            $ctrlr = $row['ragbol'];
        }
        $fatture['yes'][$last_pr]['totale'] = $totale_imponibile;
    }
    return $fatture;
}

if (isset($_POST['genera']) and $msg == "") {
    $periodo = array('inizio' => sprintf("%04d-%02d-%02d", $_POST['annini'], $_POST['mesini'], $_POST['gioini']),
        'fine' => sprintf("%04d-%02d-%02d", $_POST['annfin'], $_POST['mesfin'], $_POST['giofin'])
    );
    $data_emissione = sprintf("%04d-%02d-%02d", $_POST['annemi'], $_POST['mesemi'], $_POST['gioemi']);
    $fatture = FattureDaDdt($periodo, $sez, $_POST['clfoco'], $_POST['excludedDtT']);
    $protocollo_inizio = 0;
    foreach ($fatture['yes'] as $kt => $vt) {
        // rilevamento protocollo iniziale
        if ($protocollo_inizio == 0) {
            $protocollo_inizio = $kt;
        }
        foreach ($vt['righi'] as $kr => $vr) {
            if (isset($vr['id'])) {
                //vado a modificare la testata cambiando il tipo e introducendo protocollo,numero,data fattura
                $data['tipdoc'] = 'FAD';
                $data['protoc'] = $kt;
                $data['numfat'] = $vt['numero'];
                $data['datfat'] = $data_emissione;
                // questo e' troppo lento: gaz_dbi_table_update('tesdoc', array('id_tes',$vr['id']),$data);
                gaz_dbi_query("UPDATE " . $gTables['tesdoc'] . " SET tipdoc = 'FAD', protoc = " . $kt .
                        ", numfat = '" . $vt['numero'] .
                        "', datfat = '" . $data_emissione . "' WHERE id_tes = " . $vr['id'] . ";");
            }
        }
        $protocollo_fine = $kt;
    }
    //Mando in stampa le fatture generate
    $locazione = "Location: select_docforprint.php?tipdoc=2&seziva=" . $sez . "&proini=" . $protocollo_inizio . "&profin=" . $protocollo_fine;
    header($locazione);
    exit;
}


if (isset($_POST['return'])) {
    header("Location:report_docven.php");
    exit;
}

$titolo = 'Emissione fatture differite da D.d.T.';
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('calendarpopup/CalendarPopup',
    'custom/autocomplete_anagra'));
echo "<script type=\"text/javascript\">
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
</script>
";
echo "<form method=\"POST\" name=\"select\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['ritorno'] . "\" name=\"ritorno\" />\n";
$gForm = new venditForm();
$select_customer = new selectPartner('clfoco');
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'];
echo "<select name=\"seziva\" class=\"FacetFormHeaderFont\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 3; $counter++) {
    $selected = "";
    if ($form['seziva'] == $counter) {
        $selected = " selected ";
    }
    echo "<option value=\"" . $counter . "\"" . $selected . ">" . $counter . "</option>\n";
}
echo "</select>\n";
echo "</div>\n";
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . "</td></tr>\n";
}
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl['cliente'] . " </td><td class=\"FacetDataTD\">\n";
$select_customer->selectDocPartner('clfoco', $form['clfoco'], $form['search']['clfoco'], 'clfoco', $script_transl['mesg'], $admin_aziend['mascli']);
echo "</td></tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['date_exe'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('date_exe', $form['date_exe_D'], $form['date_exe_M'], $form['date_exe_Y'], 'FacetSelect', 1);
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['date_ini'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('date_ini', $form['date_ini_D'], $form['date_ini_M'], $form['date_ini_Y'], 'FacetSelect', 1);
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl['date_fin'] . "</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('date_fin', $form['date_fin_D'], $form['date_fin_M'], $form['date_fin_Y'], 'FacetSelect', 1);
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetDataTDred\">" . $script_transl['doc_type'] . "</td>\n";
echo "<td class=\"FacetDataTDred\">" . $script_transl['n_inv'] . "</td>\n";
echo "<td class=\"FacetDataTDred\">" . $script_transl['n_rem'] . "</td></tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">DDT</td>"
 . "<td class=\"FacetDataTD\">\n" . $form['DDT']['n_invoiceable'] . "<td class=\"FacetDataTD\">\n" . $form['DDT']['n_remainder'] . " </td></tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">DDV</td>"
 . "<td class=\"FacetDataTD\">\n" . $form['DDV']['n_invoiceable'] . "<td class=\"FacetDataTD\">\n" . $form['DDV']['n_remainder'] . " </td></tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">DDY</td>"
 . "<td class=\"FacetDataTD\">\n" . $form['DDY']['n_invoiceable'] . "<td class=\"FacetDataTD\">\n" . $form['DDY']['n_remainder'] . " </td></tr>\n";
?>
<tr>
    <td class="FacetFieldCaptionTD">&nbsp;</td>
    <td colspan="2" align="right" nowrap class="FacetFooterTD">
        <input type="submit" name="return" value="<?php echo $script_transl['return']; ?>">&nbsp;
        <input type="submit" name="preview" value="<?php echo $script_transl['view'] . " !"; ?>" &nbsp;
    </td>
</tr>
</table>
<?php
if (isset($form['preview']) and $msg == "") {
    $periodo = array('inizio' => sprintf("%04d-%02d-%02d", $form['annini'], $form['mesini'], $form['gioini']),
        'fine' => sprintf("%04d-%02d-%02d", $form['annfin'], $form['mesfin'], $form['giofin'])
    );
    $fatture = FattureDaDdt($periodo, $sez, $form['clfoco'], $form['excludedDtT']);
    echo '<div align="center"><b>ANTEPRIMA DI FATTURAZIONE</b></div>';
    echo "<table class=\"Tlarge\">";
    if (isset($fatture['yes']) && !isset($fatture['yes'][0]['totale'])) {
        foreach ($fatture['yes'] as $kt => $vt) {
            echo "<tr>";
            echo "<td> " . $vt['codicecliente'] . " &nbsp;</td>";
            echo "<td colspan=\"4\"> " . $vt['ragionesociale'] . " &nbsp;</td>";
            echo "<td> Fatt. n." . $vt['numero'] . " &nbsp;</td>";
            echo "<td> Prot. n." . $kt . "</td>";
            echo "</tr>\n";
            foreach ($vt['righi'] as $kr => $vr) {
                if ($vr['codice'] == '_MSG_') {
                    echo "<tr>";
                    echo "<td class=\"FacetDataTDred\" colspan=\"7\">" . $vr['descrizione'] . " </td>";
                    echo "</tr>\n";
                } elseif ($vr['codice'] == '_TRA_') {
                    echo "<tr>";
                    echo "<td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\" colspan=\"5\" align=\"right\">" . $vr['descrizione'] . " </td>";
                    echo "<td class=\"FacetDataTD\" align=\"right\"> " . gaz_format_number($vr['importo']) . " &nbsp;</td>";
                    echo "</tr>\n";
                } elseif ($vr['codice'] == '_DES_') {
                    echo "<tr>";
                    echo "<td class=\"FacetDataTD\" colspan=\"2\">da D.d.T. n.<a href=\"admin_docven.php?Update&id_tes=" . $vr['id'] . "\">" . $vr['numero'] . "</a> del " . $vr['data'] . " &hArr; " . $vr['despag'] . "</td>";
                    echo "<td ><input class=\"FacetText\" type=\"submit\" name=\"add_ex[{$vr['id']}]\" value=\"Escludi!\" /></td>";
                    echo "</tr>\n";
                } else {
                    echo "<tr>";
                    echo "<td class=\"FacetDataTD\">" . $vr['codice'] . " &nbsp;</td>";
                    echo "<td class=\"FacetDataTD\">" . $vr['descrizione'] . " </td>";
                    echo "<td class=\"FacetDataTD\"> " . $vr['unitamisura'] . " &nbsp;</td>";
                    echo "<td class=\"FacetDataTD\" align=\"right\"> " . $vr['quantita'] . " &nbsp;</td>";
                    echo "<td class=\"FacetDataTD\" align=\"right\"> " . number_format($vr['prezzo'], 3, ',', '.') . " &nbsp;</td>";
                    echo "<td class=\"FacetDataTD\" align=\"right\"> " . $vr['sconto'] . " &nbsp;</td>";
                    echo "<td class=\"FacetDataTD\" align=\"right\"> " . gaz_format_number($vr['importo']) . " &nbsp;</td>";
                    echo "</tr>\n";
                }
            }
            echo "<tr>";
            echo "<td class=\"FacetDataTDred\"></td><td class=\"FacetDataTD\" colspan=\"5\" align=\"right\">TOTALE</td>";
            echo "<td class=\"FacetDataTDred\" align=\"right\"> " . gaz_format_number($vt['totale']) . " &nbsp;</td>";
            echo "</tr>\n";
            if ($vt['speseincasso'] > 0) {
                echo "<tr>";
                echo "<td class=\"FacetFooterTD\"></td><td class=\"FacetFooterTD\" colspan=\"5\" align=\"right\">SPESE INCASSO</td>";
                echo "<td class=\"FacetFooterTD\" align=\"right\"> " . gaz_format_number($vt['speseincasso']) . " &nbsp;</td>";
                echo "</tr>\n";
            }
        }
        echo "<tr><td  align=\"right\" colspan=\"7\"><input type=\"submit\" name=\"genera\" value=\"CONFERMA LA GENERAZIONE DELLE FATTURE COME DA ANTEPRIMA !\"></TD></TR>";
    } else {
        echo "<tr><td class=\"FacetDataTDred\" colspan=\"7\" align=\"right\">Non ci sono DdT  da fatturare</td></tr>";
    }
    if (isset($fatture['no'])) {
        echo "<tr><td class=\"FacetDataTDred\" colspan=\"3\" align=\"right\">I DdT sottosegnati sono stati esclusi dalla fatturazione&darr; </td></TR>";
        $ctrld = 0;
        foreach ($fatture['no'] as $key => $value) {
            if ($ctrld != $value['id']) {
                echo "<input type=\"hidden\" name=\"excludedDtT[{$key}]\" value=\"" . $value['id'] . "\" />\n";
                echo "<tr>";
                echo "<td class=\"FacetDisabledTD\" colspan=\"7\"><input class=\"FacetText\" type=\"submit\" name=\"del_ex[{$value['id']}]\" value=\"Ripristina!\" /> il DdT n.<a href=\"admin_docven.php?Update&id_tes=" . $value['id'] . "\">" . $value['numero'] . "</a> del " . $value['data'] . " a:" . $value['ragionesociale'] . " &hArr; " . $value['pagamento'] . "</td>";
                echo "</tr>\n";
            }
            $ctrld = $value['id'];
        }
    }
    echo "</table>\n";
}
?>
</form>
</body>
</html>