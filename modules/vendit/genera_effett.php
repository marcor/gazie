<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/calsca.inc.php");
$msg = '';
if (isset($_POST['group_rid']) && $_POST['group_rid']=='no'){
  $group_rid_n='checked';
  $group_rid_y='';
} else {
  $group_rid_y='checked';
  $group_rid_n='';
};

function getDocumentsBill($upd = false,$group_rid=false) {
    global $gTables, $admin_aziend;
    $calc = new Compute;
    $from = $gTables['tesdoc'] . ' AS tesdoc
             LEFT JOIN ' . $gTables['pagame'] . ' AS pay
             ON tesdoc.pagame=pay.codice
             LEFT JOIN ' . $gTables['clfoco'] . ' AS customer
             ON tesdoc.clfoco=customer.codice
             LEFT JOIN ' . $gTables['anagra'] . ' AS anagraf
             ON anagraf.id=customer.id_anagra
             LEFT JOIN ' . $gTables['files'] . " AS files
             ON tesdoc.clfoco=( SELECT files.id_ref FROM ". $gTables['files'] . "  WHERE files.table_name_ref='clfoco' AND files.item_ref='mndtritdinf' ORDER BY id_tes DESC LIMIT 1 )";
    $where = "(tippag = 'B' OR tippag = 'T' OR tippag = 'V' OR tippag = 'I') AND geneff = '' AND tipdoc LIKE 'FA_'";
    $orderby = "datfat ASC, protoc ASC, id_tes ASC";
    $result = gaz_dbi_dyn_query('tesdoc.*,
                        pay.tippag,pay.numrat,pay.tipdec,pay.giodec,pay.tiprat,pay.mesesc,pay.giosuc,customer.codice, customer.speban AS addebitospese, customer.iban,
                        CONCAT(anagraf.ragso1,\' \',anagraf.ragso2) AS ragsoc,CONCAT(anagraf.citspe,\' (\',anagraf.prospe,\')\') AS citta,
                        files.id_doc AS mndtritdinf, files.custom_field AS files_data ', $from, $where, $orderby);
    $doc = array();
    $ctrlp = 0;
    while ($tes = gaz_dbi_fetch_array($result)) {
        //il numero di protocollo contiene anche l'anno nei primi 4 numeri
        $year_prot = intval(substr($tes['datfat'], 0, 4)) * 1000000 + $tes['protoc'];
        if ($year_prot <> $ctrlp) { // la prima testata della fattura
            if ($ctrlp > 0 && ($doc[$ctrlp]['tes']['stamp'] >= 0.01 || $taxstamp >= 0.01 )) { // non è il primo ciclo faccio il calcolo dei bolli del pagamento e lo aggiungo ai castelletti
                $calc->payment_taxstamp($calc->total_imp + $calc->total_vat + $carry - $rit + $taxstamp, $doc[$ctrlp]['tes']['stamp'], $doc[$ctrlp]['tes']['round_stamp'] * $doc[$ctrlp]['tes']['numrat']);
                $calc->add_value_to_VAT_castle($doc[$ctrlp]['vat'], $taxstamp + $calc->pay_taxstamp, $admin_aziend['taxstamp_vat']);
                $doc[$ctrlp]['vat'] = $calc->castle;
                // aggiungo il castelleto conti
                if (!isset($doc[$ctrlp]['acc'][$admin_aziend['boleff']])) {
                    $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] = 0;
                }
                $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] += $taxstamp + $calc->pay_taxstamp;
            }
            $carry = 0;
            $somma_spese = 0;
            $cast_vat = array();
            $totimp_decalc = 0.00;
            $n_vat_decalc = 0;
            $totimpdoc = 0;
            $spese_incasso = $tes['numrat'] * $tes['speban'];
            $cigcup = ''; /* aggiungo un solo valore di CIG CUP per ogni testata
             * che andrò a valorizzare solo se incontrerò questi sui righi tipo 11 e 12
             */
            $taxstamp = 0;
            $rit = 0;
        } else {
            $spese_incasso = 0;
        }
        // aggiungo il bollo sugli esenti/esclusi se nel DdT c'è ma non è ancora stato mai aggiunto
        if ($tes['taxstamp'] >= 0.01 && $taxstamp < 0.01) {
            $taxstamp = $tes['taxstamp'];
        }
        if ($tes['virtual_taxstamp'] == 0 || $tes['virtual_taxstamp'] == 3) { //  se è a carico dell'emittente non lo aggiungo al castelletto IVA
            $taxstamp = 0.00;
        }
        //recupero i dati righi per creare il castelletto
        $from = $gTables['rigdoc'] . ' AS rs
                    LEFT JOIN ' . $gTables['aliiva'] . ' AS vat
                    ON rs.codvat=vat.codice';
        $rs_rig = gaz_dbi_dyn_query('rs.*,vat.tipiva AS tipiva', $from, "rs.id_tes = " . $tes['id_tes'], "id_tes DESC");
        while ($r = gaz_dbi_fetch_array($rs_rig)) {
            if ($r['tiprig'] <= 1) {//ma solo se del tipo normale o forfait
                //calcolo importo rigo
                $importo = CalcolaImportoRigo($r['quanti'], $r['prelis'], array($r['sconto'], $tes['sconto']));
                if ($r['tiprig'] == 1) {
                    $importo = CalcolaImportoRigo(1, $r['prelis'], $tes['sconto']);
                }
                //creo il castelletto IVA
                if (!isset($cast_vat[$r['codvat']]['import'])) {
                    $cast_vat[$r['codvat']]['impcast'] = 0;
                    $cast_vat[$r['codvat']]['ivacast'] = round(($importo * $r['pervat']) / 100, 2);
                    ;
                    $cast_vat[$r['codvat']]['import'] = 0;
                    $cast_vat[$r['codvat']]['periva'] = $r['pervat'];
                    $cast_vat[$r['codvat']]['tipiva'] = $r['tipiva'];
                }
                $cast_vat[$r['codvat']]['impcast']+=$importo;
                $cast_vat[$r['codvat']]['import']+=$importo;
                $totimpdoc += $importo;
                $rit+=round($importo * $r['ritenuta'] / 100, 2);
            } elseif ($r['tiprig'] == 3) {
                $carry += $r['prelis'];
            } elseif ($r['tiprig'] == 11) {
                $cigcup = 'CIG ' . $r['descri'];
            } elseif ($r['tiprig'] == 12) {
                $cigcup .= ' CUP ' . $r['descri'];
            }
        }
        $doc[$year_prot]['tes'] = $tes;
        $doc[$year_prot]['tes']['cigcup'] = $cigcup;
        $doc[$year_prot]['car'] = $carry;
        $doc[$year_prot]['rit'] = $rit;
        $ctrlp = $year_prot;
        $somma_spese += $tes['traspo'] + $spese_incasso + $tes['spevar'];
        $calc->add_value_to_VAT_castle($cast_vat, $somma_spese, $tes['expense_vat']);
        $doc[$ctrlp]['vat'] = $calc->castle;
        // quando confermo segno l'effetto come generato e se un RID valorizzo con l'ultimo mandato del cliente
        if ($upd) {
            gaz_dbi_query("UPDATE " . $gTables['tesdoc'] . " SET geneff = 'S' WHERE id_tes = " . $tes['id_tes'] . ";");
        }
    }
    if (count($doc)>0 && ($doc[$ctrlp]['tes']['stamp'] >= 0.01 || $taxstamp >= 0.01)) { // a chiusura dei cicli faccio il calcolo dei bolli del pagamento e lo aggiungo ai castelletti
        $calc->payment_taxstamp($calc->total_imp + $calc->total_vat + $carry - $rit + $taxstamp, $doc[$ctrlp]['tes']['stamp'], $doc[$ctrlp]['tes']['round_stamp'] * $doc[$ctrlp]['tes']['numrat']);
        // aggiungo al castelletto IVA
        $calc->add_value_to_VAT_castle($doc[$ctrlp]['vat'], $taxstamp + $calc->pay_taxstamp, $admin_aziend['taxstamp_vat']);
        $doc[$ctrlp]['vat'] = $calc->castle;
        // aggiungo il castelleto conti
        if (!isset($doc[$ctrlp]['acc'][$admin_aziend['boleff']])) {
            $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] = 0;
        }
        $doc[$ctrlp]['acc'][$admin_aziend['boleff']]['import'] += $doc[$ctrlp]['tes']['taxstamp'] + $calc->pay_taxstamp;
    }

    // INIZIO ciclo delle fatture che dovranno generare effetti con le singole scadenze
    $ctrl_date = '';
    $effetti=[];
    $cliscad=[];
    $ke=0;
    foreach ($doc as $k => $v) {
        if ($ctrl_date <> substr($v['tes']['datemi'], 0, 4)) {
            $n = getReceiptNumber($v['tes']['datemi']);
        }
        // calcolo i totali
        $stamp = false;
        $round = 0;
        if ($v['tes']['tippag'] == 'T') {
            $stamp = $v['tes']['stamp'];
            $round = $v['tes']['numrat'] * $v['tes']['round_stamp'];
        }

        $tot = computeTot($v['vat'], $v['car'] - $v['rit']);
        //fine calcolo totali
        $rate = CalcolaScadenze($tot['tot'], substr($v['tes']['datfat'], 8, 2), substr($v['tes']['datfat'], 5, 2), substr($v['tes']['datfat'], 0, 4), $v['tes']['tipdec'], $v['tes']['giodec'], $v['tes']['numrat'], $v['tes']['tiprat'], $v['tes']['mesesc'], $v['tes']['giosuc']);
        $tot_doc = $tot['tot'];
        if ($tot['tot'] > 0) {
            foreach ($rate['import'] as $k_r => $v_r) {
                $ke++;
                $v['tes']['tipeff'] = $v['tes']['tippag'];
                $v['tes']['scaden'] = $rate['anno'][$k_r] . '-' . $rate['mese'][$k_r] . '-' . $rate['giorno'][$k_r];
                $n_type = $v['tes']['tippag'];
                if ($n_type == 'B') {
                    $n_type = 'R';
                }
                // valorizzo l'indice con una chiave clfoco-scaden (cliente-scadenza che eventualemente mi servirà sotto per accumulare in un unico RID le scadenze dello stesso cliente
                // per fare l'accumulo in fase di emissione della distinta RID darò lo stesso progressivo a righi diversi della tabella gaz_NNNeffett
                // controllo se il cliente ha già generato un rid con la stessa scadenza
                if (isset($cliscad[$v['tes']['clfoco'].$v['tes']['scaden']]) && $v['tes']['tipeff']=='I' && $group_rid) { // se ho chiesto il raggruppamento dei RID il progressivo sarà lo stesso a parità di scadenza
                    $v['tes']['progre'] = $cliscad[$v['tes']['clfoco'].$v['tes']['scaden']]['progre'];
                    $effetti[$cliscad[$v['tes']['clfoco'].$v['tes']['scaden']]['key']]['raggru'] = 1; //segno come accumulato anche l'effetto precedente con lo stesso cliente-scadenza
                    $effetti[$cliscad[$v['tes']['clfoco'].$v['tes']['scaden']]['key']]['status'] = 'RAGGRUPPA'; // per visualizzare sui report, nonincide sulla logica
                    $v['tes']['raggru'] = 1;
                    $v['tes']['status'] = 'RAGGRUPPA';
                } else { // altrimenti il progressivo NON sarà rivisto per l'accumulo
                    $cliscad[$v['tes']['clfoco'].$v['tes']['scaden']] = ['progre'=>$n[$n_type],'key'=>$ke];
                    $v['tes']['raggru'] = 0;
                    $v['tes']['progre'] = $n[$n_type];
                    $n[$n_type] ++;
                }
                $v['tes']['datemi'] = $v['tes']['datfat'];
                $tot_doc = round($tot_doc - $v_r, 2);
                $v['tes']['totfat'] = $tot['tot'];
                $v['tes']['salacc'] = 'C';
                if ($tot_doc == 0) {
                    $v['tes']['salacc'] = 'S';
                }
                $v['tes']['impeff'] = $v_r;
                $v['tes']['id_doc'] = $v['tes']['id_tes'];
                $v['tes']['id_con'] = 0;
                $effetti[$ke]=$v['tes'];
            }
        }
        $ctrl_date = substr($v['tes']['datfat'], 0, 4);
    }
    // FINE ciclo fatture con effetti con o senza progressivi raggruppati, pronti per essere visualizzati o inseriti sul db in base alla scelta

    if ($upd) { // ho scelto di generarle
        foreach ($effetti as $k=>$v) {
            effettInsert($v);
        }
    } else {
        return $effetti;
    }
}

function getReceiptNumber($date) {
    global $gTables;
    $orderby = "datemi DESC, progre DESC";
    $where = "tipeff = 'B' AND YEAR(datemi) = " . substr($date, 0, 4);
    $result = gaz_dbi_dyn_query('*', $gTables['effett'], $where, $orderby, 0, 1);
    $lastB = gaz_dbi_fetch_array($result);
    $first['R'] = ($lastB)? (1 + $lastB['progre']):1;
    $where = "tipeff = 'T' AND YEAR(datemi) = " . substr($date, 0, 4);
    $result = gaz_dbi_dyn_query('*', $gTables['effett'], $where, $orderby, 0, 1);
    $lastT = gaz_dbi_fetch_array($result);
    $first['T'] = ($lastT)? (1 + $lastT['progre']):1;
    $where = "tipeff = 'V' AND YEAR(datemi) = " . substr($date, 0, 4);
    $result = gaz_dbi_dyn_query('*', $gTables['effett'], $where, $orderby, 0, 1);
    $lastV = gaz_dbi_fetch_array($result);
    $first['V'] = ($lastV)? (1 + $lastV['progre']):1;
    $where = "tipeff = 'I' AND YEAR(datemi) = " . substr($date, 0, 4);
    $result = gaz_dbi_dyn_query('*', $gTables['effett'], $where, $orderby, 0, 1);
    $lastI = gaz_dbi_fetch_array($result);
    $first['I'] = ($lastI)? (1 + $lastI['progre']):1;
    return $first;
}

function computeTot($data, $carry) {
    $tax = 0;
    $vat = 0;
    foreach ($data as $k => $v) {
        $tax += $v['impcast'];
        $vat += round($v['impcast'] * $v['periva']) / 100;
    }
    $tot = $vat + $tax + $carry;
    return array('taxable' => $tax, 'vat' => $vat, 'tot' => $tot);
}

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {    // accessi successivi
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    $form['ritorno'] = $_POST['ritorno'];
    if (isset($_POST['submit']) && empty($msg)) {   //confermo la generazione
        $rs = getDocumentsBill(true,($group_rid_y=='checked'?true:false));
        header("Location: report_effett.php");
        exit;
    }
}

require("../../library/include/header.php");
$script_transl = HeadMain(0);
echo "<form method=\"POST\" name=\"create\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"" . $form['ritorno'] . "\" name=\"ritorno\" />\n";
$gForm = new GAzieForm();
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title'];?></div>
<div class="panel panel-info div-bordered">
 <div class="panel-body">
  <div class="container-fluid">
   <div class="row">
    <div class="form-group col-xs-12 col-sm-6 col-md-4 bg-info text-center">Genera un solo RID per lo stesso cliente con le stesse scadenze</div>
    <div class="form-group col-xs-12 col-sm-6 col-md-4 text-center">
      <input name="group_rid" type="radio" value="yes" id="group_rid_y" <?php echo $group_rid_y; ?> >
      <label for="group_rid_y">SI</label>
      <input name="group_rid" type="radio" value="no" id="group_rid_n" <?php echo $group_rid_n; ?>>
      <label for="group_rid_n">NO</label>
    </div>
    <div class="form-group col-xs-12 col-sm-6 col-md-4 text-center"><input type="submit" class="btn btn-info" name="preview" value="<?php echo $script_transl['view'];?>">
    </div>
   </div>
  </div>
 </div>
</div>
<?php
if (!empty($msg)) {
    echo '<div>' . $gForm->outputErrors($msg, $script_transl['errors']) . "</div>";
}

//mostro l'anteprima
if (isset($_POST['preview'])) {
    $errors=false;
    $rs = getDocumentsBill(false,($group_rid_y=='checked'?true:false));
    echo "<br /><div align=\"center\"><b>" . $script_transl['preview'] . "</b></div>";
    echo "<table class=\"table table-bordered table-responsive\">";
    echo "<th class=\"FacetFieldCaptionTD\">" . $script_transl['date_reg'] . "</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['protoc'] . "</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['doc_type'] . "</th>
         <th class=\"FacetFieldCaptionTD\">N.</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['customer'] . "</th>
         <th class=\"FacetFieldCaptionTD\">" . $script_transl['tot'] . "fattura</th>\n
         <th class=\"FacetFieldCaptionTD\"> Importo effetto</th>\n";
    $ctrl_date = '';
    $tot_type = ['B'=>0,'I'=>0,'T'=>0,'V'=>0];
    $class_type = ['B'=>'bg-success','I'=>'bg-info','T'=>'bg-warning','V'=>'bg-default'];
    $ctrldoc=0;
    foreach ($rs as $k => $v) {
        $tot_type[$v['tippag']]+=$v['impeff'];
        $e='';
        if ($ctrl_date <> substr($v['datfat'], 0, 4)) {
            $n = getReceiptNumber($v['datfat']);
        }
        if (strlen($v['iban'])<20&&$v['tippag']=='I') { // non c'è l'iban
            $errors=true;
            $e=$script_transl['errors']['noiban'];
        }
        if ($v['mndtritdinf']<1&&$v['tippag']=='I') { // non c'è il mandato
            $errors=true;
            $e=$script_transl['errors']['nomandato'];
        }
        if ($v['banapp']<1&&($v['tippag']=='B'||$v['tippag']=='T')) { // non c'è la banca d'appoggio
            $errors=true;
            $e=$script_transl['errors']['nobanapp'];
        }
        if ($ctrldoc<>$v['protoc']){
            echo "<tr class=\"".$class_type[$v['tippag']]."\">
               <td align=\"center\">" . gaz_format_date($v['datfat']) . "</td>
               <td title=\"".$v['cigcup']."\" align=\"center\">" . $v['protoc'] . '/' . $v['seziva'] . "</td>
               <td>" . $script_transl['doc_type_value'][$v['tipdoc']] . "</td>
               <td>" . $v['numfat'] . '</td>
               <td><a class="btn btn-sm btn-'.((strlen($e)>10)?"danger":"default").'" href="./admin_client.php?codice='.substr($v['clfoco'],-6).'&Update">'.$v['ragsoc']. "</a></td>
               <td align=\"right\">" . $admin_aziend['symbol'].' '.gaz_format_number($v['totfat']) . "</td>
               </tr>\n";
        }
        echo "<tr>";
        echo '<td align="right" colspan="6"><span class="text-danger">'.$e.'</span> ';
        echo $script_transl['gen'] .'<b>' .$script_transl['type_value'][$v['tippag']] .
        ' n.' . $v['progre'] .(($v['raggru']==1)?' <span class="text-danger">[raggruppato] </span>':'').'</b> ' . $script_transl['end'] . gaz_format_date($v['scaden']);
        echo "</td>
                   <td align=\"right\">";
        echo  $admin_aziend['symbol'].' '.gaz_format_number($v['impeff']);
        echo "</td></tr>\n";
        $ctrldoc=$v['protoc'];
    }

    foreach ($tot_type as $k_t => $v_t) {
        if ($v_t > 0) {
            echo "\t<tr>\n";
            echo '<td class=\"FacetFieldCaptionTD\" colspan="6" align="right"><b>';
            echo $script_transl['total_value'][$k_t];
            echo "</b></td>
                   <td class=\"FacetFieldCaptionTD\" align=\"right\"><b>";
            echo $admin_aziend['symbol'] . ' ' . gaz_format_number($v_t);
            echo "</b> </td>\n";
            echo "\t </tr>\n";
        }
    }
    echo "\t<tr>\n";
    echo "\t </tr></table>\n";
    echo '<div class="text-center">'.($errors?'<span class="gaz-costi">Attenzione!!! Puoi generare gli effetti ma devi essere consapevole degli errori sopra riportati</span><br/>':'').'<button type="submit" class="btn btn-warning" name="submit">'.$script_transl['submit'].'</button>';
    echo "\t </td>\n";

}
?>
</form>
<?php
require("../../library/include/footer.php");
?>
