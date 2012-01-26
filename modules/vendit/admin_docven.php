<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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
require("../../modules/magazz/lib.function.php");
$admin_aziend=checkAdmin();
$msg='';

$upd_mm = new magazzForm;
$docOperat = $upd_mm->getOperators();

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and  !isset($_GET['id_tes'])) and
    !isset($_GET['tipdoc'] )) {
    header("Location: ".$form['ritorno']);
    exit;
}


if (isset($_POST['newdestin'])) {
    $_POST['id_des']=0;
    $_POST['destin']="";
}

if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or (isset($_POST['Update']))) {   //se non e' il primo accesso
    //qui si dovrebbe fare un parsing di quanto arriva dal browser...
    $form['id_tes'] = $_POST['id_tes'];
    $anagrafica = new Anagrafica();
    $cliente = $anagrafica->getPartner($_POST['clfoco']);
    $form['hidden_req'] = $_POST['hidden_req'];
    // ...e della testata
    foreach($_POST['search'] as $k=>$v){
       $form['search'][$k]=$v;
    }
    $form['cosear'] = $_POST['cosear'];
    $form['seziva'] = $_POST['seziva'];
    $form['tipdoc'] = $_POST['tipdoc'];
    $form['template'] = $_POST['template'];
    $form['gioemi'] = $_POST['gioemi'];
    $form['mesemi'] = $_POST['mesemi'];
    $form['annemi'] = $_POST['annemi'];
    $form['giotra'] = $_POST['giotra'];
    $form['mestra'] = $_POST['mestra'];
    $form['anntra'] = $_POST['anntra'];
    $form['oratra'] = $_POST['oratra'];
    $form['mintra'] = $_POST['mintra'];
    $form['protoc'] = $_POST['protoc'];
    $form['numdoc'] = $_POST['numdoc'];
    $form['numfat'] = $_POST['numfat'];
    $form['datfat'] = $_POST['datfat'];
    $form['clfoco'] = $_POST['clfoco'];
    //tutti i controlli su  tipo di pagamento e rate
    $form['speban'] = floatval($_POST['speban']);
    $form['numrat'] = intval($_POST['numrat']);
    $form['stamp'] = floatval($_POST['stamp']);
    $form['round_stamp'] = intval($_POST['round_stamp']);
    $form['pagame'] = $_POST['pagame'];
    $form['change_pag'] = $_POST['change_pag'];
    if ($form['change_pag'] != $form['pagame']) {  //se è stato cambiato il pagamento
       $new_pag = gaz_dbi_get_row($gTables['pagame'],"codice",$form['pagame']);
       if ($toDo == 'update') {  //se è una modifica mi baso sulle vecchie spese
              $old_header = gaz_dbi_get_row($gTables['tesdoc'],"id_tes",$form['id_tes']);
              if ($cliente['speban'] == "S" && ($new_pag['tippag']=='T' || $new_pag['tippag']=='B'  || $new_pag['tippag']=='V')) {
                 if ($old_header['speban'] > 0) {
                    $form['speban'] = $old_header['speban'];
                 } else {
                    $form['speban'] = $admin_aziend['sperib'];
                 }
              }  else {
                   $form['speban'] = 0.00;
              }
       } else { //altrimenti, se previste,  mi avvalgo delle nuove dell'azienda
              if ($cliente['speban'] == "S" && ($new_pag['tippag']=='B' || $new_pag['tippag']=='T' || $new_pag['tippag']=='V')) {
                 $form['speban'] = $admin_aziend['sperib'];
              } else {
                 $form['speban'] = 0;
              }
       }
       if ($new_pag['tippag'] == 'T' && $form['stamp']==0) {  //se il pagamento prevede il bollo
           $form['stamp'] = $admin_aziend['perbol'];
           $form['round_stamp'] = $admin_aziend['round_bol'];
       } elseif ($new_pag['tippag'] == 'R') {
           $form['stamp'] = $admin_aziend['ricbol'];
           $form['round_stamp'] = 0;
       } elseif ($new_pag['tippag'] != 'T') {
           $form['stamp'] = 0;
           $form['round_stamp'] = 0;
       }
       $form['numrat'] = $new_pag['numrat'];
       $form['pagame'] = $_POST['pagame'];
       $form['change_pag'] = $_POST['pagame'];
    }
    $form['banapp'] = $_POST['banapp'];
    $form['vettor'] = $_POST['vettor'];
    $form['id_agente'] = intval($_POST['id_agente']);
    $form['net_weight'] = floatval($_POST['net_weight']);
    $form['gross_weight'] = floatval($_POST['gross_weight']);
    $form['units'] = intval($_POST['units']);
    $form['volume'] = floatval($_POST['volume']);
    $form['listin'] = $_POST['listin'];
    $form['spediz'] = $_POST['spediz'];
    $form['portos'] = $_POST['portos'];
    $form['imball'] = $_POST['imball'];
    $form['destin'] = $_POST['destin'];
    $form['id_des'] = substr($_POST['id_des'],3);
    $form['traspo'] = $_POST['traspo'];
    $form['spevar'] = $_POST['spevar'];
    $form['cauven'] = $_POST['cauven'];
    $form['caucon'] = $_POST['caucon'];
    $form['caumag'] = $_POST['caumag'];
    $form['caucon'] = $_POST['caucon'];
    $form['sconto'] = floatval(preg_replace("/\,/",'.',$_POST['sconto']));
    // inizio rigo di input
    $form['in_descri'] = $_POST['in_descri'];
    $form['in_tiprig'] = $_POST['in_tiprig'];
    $form['in_artsea'] = $_POST['in_artsea'];
    $form['in_codart'] = $_POST['in_codart'];
    $form['in_pervat'] = $_POST['in_pervat'];
    $form['in_tipiva'] = $_POST['in_tipiva'];
    $form['in_ritenuta'] = $_POST['in_ritenuta'];
    $form['in_unimis'] = $_POST['in_unimis'];
    $form['in_prelis'] = $_POST['in_prelis'];
    $form['in_sconto'] = $_POST['in_sconto'];
    $form['in_quanti'] = gaz_format_quantity($_POST['in_quanti'],0,$admin_aziend['decimal_quantity']);
    $form['in_codvat'] = $_POST['in_codvat'];
    $form['in_codric'] = $_POST['in_codric'];
    $form['in_provvigione'] = $_POST['in_provvigione'];
    $form['in_id_mag'] = $_POST['in_id_mag'];
    $form['in_annota'] = $_POST['in_annota'];
    $form['in_scorta'] = $_POST['in_scorta'];
    $form['in_pesosp'] = $_POST['in_pesosp'];
    $form['in_status'] = $_POST['in_status'];
    // fine rigo input
    $form['rows'] = array();
    $next_row = 0;
    $rows_text ='';
    if (isset($_POST['rows'])) {
       foreach ($_POST['rows'] as $next_row => $v) {
            if (isset($_POST["row_$next_row"])) { //se ho un rigo testo
               $rows_text .= ",row_$next_row";
               $form["row_$next_row"] = $_POST["row_$next_row"];
            }
            $form['rows'][$next_row]['descri'] = substr($v['descri'],0,50);
            $form['rows'][$next_row]['tiprig'] = intval($v['tiprig']);
            $form['rows'][$next_row]['codart'] = substr($v['codart'],0,15);
            $form['rows'][$next_row]['pervat'] = preg_replace("/\,/",'.',$v['pervat']);
            $form['rows'][$next_row]['tipiva'] = strtoupper(substr($v['tipiva'],0,1));
            $form['rows'][$next_row]['ritenuta'] = preg_replace("/\,/",'.',$v['ritenuta']);
            $form['rows'][$next_row]['unimis'] = substr($v['unimis'],0,3);
            $form['rows'][$next_row]['prelis'] = number_format(floatval(preg_replace("/\,/",'.',$v['prelis'])),$admin_aziend['decimal_price'],'.','');
            $form['rows'][$next_row]['sconto'] = floatval(preg_replace("/\,/",".",$v['sconto']));
            $form['rows'][$next_row]['quanti'] = gaz_format_quantity($v['quanti'],0,$admin_aziend['decimal_quantity']);
            $form['rows'][$next_row]['codvat'] = intval($v['codvat']);
            $form['rows'][$next_row]['codric'] = intval($v['codric']);
            if (isset($v['provvigione'])) {
               $form['rows'][$next_row]['provvigione'] = intval($v['provvigione']);
            }
            $form['rows'][$next_row]['id_mag'] = intval($v['id_mag']);
            $form['rows'][$next_row]['annota'] = substr($v['annota'],0,50);
            $form['rows'][$next_row]['scorta'] = floatval($v['scorta']);
            $form['rows'][$next_row]['pesosp'] = floatval($v['pesosp']);
            $form['rows'][$next_row]['status'] = substr($v['status'],0,10);
            if (isset($_POST['upd_row'])) {
               $k_row = key($_POST['upd_row']);
               if ($k_row == $next_row) {
                  // sottrazione ai totali peso,pezzi,volume
                  $artico = gaz_dbi_get_row($gTables['artico'],"codice",$form['rows'][$k_row]['codart']);
                  $form['net_weight'] -= $form['rows'][$k_row]['quanti']*$artico['peso_specifico'];
                  $form['gross_weight'] -= $form['rows'][$k_row]['quanti']*$artico['peso_specifico'];
                  if ($artico['pack_units'] > 0){
                     $form['units'] -= intval(round($form['rows'][$k_row]['quanti']/$artico['pack_units']));
                  }
                  $form['volume'] -= $form['rows'][$k_row]['quanti']*$artico['volume_specifico'];
                  // fine sottrazione peso,pezzi,volume
                  $form['in_descri'] = $form['rows'][$k_row]['descri'];
                  $form['in_tiprig'] = $form['rows'][$k_row]['tiprig'];
                  $form['in_codart'] = $form['rows'][$k_row]['codart'];
                  $form['in_pervat'] = $form['rows'][$k_row]['pervat'];
                  $form['in_tipiva'] = $form['rows'][$k_row]['tipiva'];
                  $form['in_ritenuta'] = $form['rows'][$k_row]['ritenuta'];
                  $form['in_unimis'] = $form['rows'][$k_row]['unimis'];
                  $form['in_prelis'] = $form['rows'][$k_row]['prelis'];
                  $form['in_sconto'] = $form['rows'][$k_row]['sconto'];
                  $form['in_quanti'] = $form['rows'][$k_row]['quanti'];
                  $form['in_codvat'] = $form['rows'][$k_row]['codvat'];
                  $form['in_codric'] = $form['rows'][$k_row]['codric'];
                  $form['in_provvigione'] = $form['rows'][$k_row]['provvigione'];
                  $form['in_id_mag'] = $form['rows'][$k_row]['id_mag'];
                  $form['in_annota'] = $form['rows'][$k_row]['annota'];
                  $form['in_scorta'] = $form['rows'][$k_row]['scorta'];
                  $form['in_pesosp'] = $form['rows'][$k_row]['pesosp'];
                  $form['in_status'] = "UPDROW".$k_row;
                  if ($form['in_artsea'] == 'D'){
                    $artico_u = gaz_dbi_get_row($gTables['artico'],'codice',$form['rows'][$k_row]['codart']);
                    $form['cosear'] = $artico_u['descri'];
                  } elseif ($form['in_artsea'] == 'B') {
                    $artico_u = gaz_dbi_get_row($gTables['artico'],'codice',$form['rows'][$k_row]['codart']);
                    $form['cosear'] = $artico_u['barcode'];
                  } else {
                    $form['cosear'] = $form['rows'][$k_row]['codart'];
                  }
                  array_splice($form['rows'],$k_row,1);
                  $next_row--;
               }
            } elseif ($_POST['hidden_req'] == 'ROW') {
                  if (!empty($form['hidden_req'])){ // al primo ciclo azzero ma ripristino il lordo
                      $form['gross_weight'] -= $form['net_weight'];
                      $form['net_weight'] = 0;
                      $form['units'] = 0;
                      $form['volume'] = 0;
                      $form['hidden_req'] = '';
                  }
                  $artico = gaz_dbi_get_row($gTables['artico'],"codice",$form['rows'][$next_row]['codart']);
                  $form['net_weight'] += $form['rows'][$next_row]['quanti']*$artico['peso_specifico'];
                  $form['gross_weight'] += $form['rows'][$next_row]['quanti']*$artico['peso_specifico'];
                  if ($artico['pack_units'] > 0){
                     $form['units'] += intval(round($form['rows'][$next_row]['quanti']/$artico['pack_units']));
                  }
                  $form['volume'] += $form['rows'][$next_row]['quanti']*$artico['volume_specifico'];
            }
            $next_row++;
       }
    }
    // Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
       $sezione=$form['seziva'];
       $datemi = $form['annemi']."-".$form['mesemi']."-".$form['gioemi'];
       $utsemi = mktime(0,0,0,$form['mesemi'],$form['gioemi'],$form['annemi']);
       if ($form['tipdoc']!='DDT' && $form['template']!='FatturaImmediata'){
           $initra = $datemi;
           $utstra = $utsemi;
       } else {
           $initra = $form['anntra']."-".$form['mestra']."-".$form['giotra'];
           $utstra = mktime(0,0,0,$form['mestra'],$form['giotra'],$form['anntra']);
       }
       if (!checkdate( $form['mestra'], $form['giotra'], $form['anntra']))
          $msg .= "37+";
       if ($utstra < $utsemi) {
          $msg .= "38+";
       }
       if (!isset($_POST['rows'])) {
          $msg .= "39+";
       }
       // --- inizio controllo coerenza date-numerazione
       if ($toDo == 'update') {  // controlli in caso di modifica
          if ($form['tipdoc'] == 'DDT' or $form['tipdoc'] == 'FAD') {  //se è un DDT vs Fattura differita
             $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = ".$form['annemi']." and datemi < '$datemi' and ( tipdoc like 'DD_' or tipdoc = 'FAD') and seziva = $sezione","numdoc desc",0,1);
             $result = gaz_dbi_fetch_array($rs_query); //giorni precedenti
             if ($result and ($form['numdoc'] < $result['numdoc'])) {
                $msg .= "40+";
             }
             $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = ".$form['annemi']." and datemi > '$datemi' and ( tipdoc like 'DD_' or tipdoc = 'FAD') and seziva = $sezione","numdoc asc",0,1);
             $result = gaz_dbi_fetch_array($rs_query); //giorni successivi
             if ($result and ($form['numdoc'] > $result['numdoc'])) {
                $msg .= "41+";
             }
          } else { //se sono altri documenti
            $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = ".$form['annemi']." and datfat < '$datemi' and tipdoc like '".$form['tipdoc']."' and seziva = $sezione","protoc desc",0,1);
            $result = gaz_dbi_fetch_array($rs_query); //giorni precedenti
            if ($result and ($form['numfat'] < $result['numfat'])) {
               $msg .= "42+";
            }
            $rs_query = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = ".$form['annemi']." and datfat > '$datemi' and tipdoc like '".$form['tipdoc']."' and seziva = $sezione","protoc asc",0,1);
            $result = gaz_dbi_fetch_array($rs_query); //giorni successivi
            if ($result and ($form['numfat'] > $result['numfat'])) {
               $msg .= "43+";
            }
          }
       } else {    //controlli in caso di inserimento
         if ($form['tipdoc'] == 'DDT') {  //se è un DDT
            $rs_ultimo_ddt = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = ".$form['annemi']." AND (tipdoc LIKE 'DD_' OR tipdoc = 'FAD') AND seziva = ".$sezione,"datemi DESC ,numdoc DESC ",0,1);
            $ultimo_ddt = gaz_dbi_fetch_array($rs_ultimo_ddt);
            $utsUltimoDdT = mktime(0,0,0,substr($ultimo_ddt['datemi'],5,2),substr($ultimo_ddt['datemi'],8,2),substr($ultimo_ddt['datemi'],0,4));
            if ($ultimo_ddt and ($utsUltimoDdT > $utsemi)) {
               $msg .= "44+";
            }
         } else { //se sono altri documenti
           $rs_ultimo_tipo = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = ".$form['annemi']." and tipdoc like '".substr($form['tipdoc'],0,1)."%' and seziva = $sezione","protoc desc, datfat desc, datemi desc",0,1);
           $ultimo_tipo = gaz_dbi_fetch_array($rs_ultimo_tipo);
           $utsUltimoProtocollo = mktime(0,0,0,substr($ultimo_tipo['datfat'],5,2),substr($ultimo_tipo['datfat'],8,2),substr($ultimo_tipo['datfat'],0,4));
           if ($ultimo_tipo and ($utsUltimoProtocollo > $utsemi)) {
              $msg .= "45+";
           }
         }
       }
       // --- fine controllo coerenza date-numeri
       if (!checkdate( $form['mesemi'], $form['gioemi'], $form['annemi']))
          $msg .= "46+";
       if (empty ($form["clfoco"]))
          $msg .= "47+";
       if (empty ($form["pagame"]))
          $msg .= "48+";
       //controllo che i rows non abbiano descrizioni  e unita' di misura vuote in presenza di quantita diverse da 0
       foreach ($form['rows'] as $i => $v) {
            if ($v['descri']=='' && ($v['quanti']>0 || $v['quanti']<0)) {
                $msgrigo= $i+1;
                $msg .= "49+";
            }
            if ($v['unimis']=='' && ($v['quanti']>0 || $v['quanti']<0)) {
                $msgrigo= $i+1;
                $msg .= "50+";
            }
       }
       if ($msg == "") {// nessun errore
          $initra .= " ".$form['oratra'].":".$form['mintra'].":00";
          if (preg_match("/^id_([0-9]+)$/",$form['clfoco'],$match)) {
             $new_clfoco = $anagrafica->getPartnerData($match[1],1);
             $form['clfoco']=$anagrafica->anagra_to_clfoco($new_clfoco,$admin_aziend['mascli']);
          }
          if ($toDo == 'update') { // e' una modifica
             $old_rows = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = ".$form['id_tes'],"id_rig asc");
             $i=0;
             $count = count($form['rows'])-1;
             while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
              if ($i <= $count) { //se il vecchio rigo e' ancora presente nel nuovo lo modifico
                 $form['rows'][$i]['id_tes'] = $form['id_tes'];
                 $codice = array('id_rig',$val_old_row['id_rig']);
                 rigdocUpdate($codice,$form['rows'][$i]);
                 if (isset($form["row_$i"]) && $val_old_row['id_body_text'] > 0) { //se è un rigo testo già presente lo modifico
                      bodytextUpdate(array('id_body',$val_old_row['id_body_text']),array('table_name_ref'=>'rigdoc','id_ref'=>$val_old_row['id_rig'],'body_text'=>$form["row_$i"]));
                      gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $val_old_row['id_rig'], 'id_body_text', $val_old_row['id_body_text']);
                 } elseif (isset($form["row_$i"]) && $val_old_row['id_body_text'] == 0 ) { //prima era un rigo diverso da testo
                      bodytextInsert(array('table_name_ref'=>'rigdoc','id_ref'=>$val_old_row['id_rig'],'body_text'=>$form["row_$i"]));
                      gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $val_old_row['id_rig'], 'id_body_text', gaz_dbi_last_id());
                 } elseif (!isset($form["row_$i"]) && $val_old_row['id_body_text'] > 0){ //un rigo che prima era testo adesso non lo è più
                      gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigdoc' AND id_ref", $val_old_row['id_rig']);
                 }
                 if ($form['rows'][$i]['id_mag'] > 0 ){ //se il rigo ha un movimento di magazzino associato
                     $upd_mm->uploadMag($val_old_row['id_rig'],
                                    $form['tipdoc'],
                                    $form['numdoc'],
                                    $form['protoc'].'/'.$form['seziva'],
                                    $datemi,
                                    $form['clfoco'],
                                    $form['sconto'],
                                    $form['caumag'],
                                    $form['rows'][$i]['codart'],
                                    $form['rows'][$i]['quanti'],
                                    $form['rows'][$i]['prelis'],
                                    $form['rows'][$i]['sconto'],
                                    $val_old_row['id_mag'],
                                    $admin_aziend['stock_eval_method']
                                    );
                 }
              } else { //altrimenti lo elimino
                  if (intval($val_old_row['id_mag']) > 0){  //se c'è stato un movimento di magazzino lo azzero
                     $upd_mm->uploadMag('DEL',$form['tipdoc'],'','','','','','','','','','',$val_old_row['id_mag'],$admin_aziend['stock_eval_method']);
                  }
                  if (intval($val_old_row['id_body_text']) > 0){  //se c'è un testo allegato al rigo elimino anch'esso
                      gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigdoc' AND id_ref", $val_old_row['id_rig']);
                  }
                  gaz_dbi_del_row($gTables['rigdoc'], 'id_rig', $val_old_row['id_rig']);
              }
              $i++;
             }
             //qualora i nuovi righi fossero di più dei vecchi inserisco l'eccedenza
             for ($i = $i; $i <= $count; $i++) {
                $form['rows'][$i]['id_tes'] = $form['id_tes'];
                rigdocInsert($form['rows'][$i]);
                if ($admin_aziend['conmag'] == 2 and
                   $form['rows'][$i]['tiprig'] == 0 and
                   !empty($form['rows'][$i]['codart'])) { //se l'impostazione in azienda prevede l'aggiornamento automatico dei movimenti di magazzino
                   $upd_mm->uploadMag(gaz_dbi_last_id(),
                                    $form['tipdoc'],
                                    $form['numdoc'],
                                    $form['protoc'].'/'.$form['seziva'],
                                    $datemi,
                                    $form['clfoco'],
                                    $form['sconto'],
                                    $form['caumag'],
                                    $form['rows'][$i]['codart'],
                                    $form['rows'][$i]['quanti'],
                                    $form['rows'][$i]['prelis'],
                                    $form['rows'][$i]['sconto'],
                                    0,
                                    $admin_aziend['stock_eval_method']
                                    );
                }
             }
             //modifico la testata con i nuovi dati...
             $old_head = gaz_dbi_get_row($gTables['tesdoc'],'id_tes',$form['id_tes']);
             if (substr($form['tipdoc'],0,2) == 'DD') { //se è un DDT non fatturato
               $form['datfat'] = '';
               $form['numfat'] = 0;
             } elseif ($form['tipdoc']== 'FAD') {  // se è fatturato
               $form['datfat'] = $old_head['datfat'];
               $form['numfat'] = $old_head['numfat'];
             } else {
               $form['datfat'] = $datemi;
               $form['numfat'] = $old_head['numfat'];
             }
             $form['geneff'] = $old_head['geneff'];
             $form['id_contract'] = $old_head['id_contract'];
             $form['id_con'] = $old_head['id_con'];
             $form['status'] = $old_head['status'];
             $form['initra'] = $initra;
             $form['datemi'] = $datemi;
             $codice = array('id_tes',$form['id_tes']);
             tesdocUpdate($codice,$form);
             header("Location: ".$form['ritorno']);
             exit;
          } else { // e' un'inserimento
            // ricavo i progressivi in base al tipo di documento
            $where = " fattura DESC";
            switch ($form['tipdoc']) {
                  case "DDT":
                  case "DDV":
                  $sql_documento = "YEAR(datemi) = ".$form['annemi']." AND( tipdoc like 'DD_' or tipdoc = 'FAD') and seziva = $sezione";
                  $where = "numdoc DESC";
                  $sql_protocollo = " 0";
                  break;
                  case "FAI":
                  case "FAP":
                  $sql_documento = "YEAR(datfat) = ".$form['annemi']." AND tipdoc LIKE 'FA_' AND seziva = $sezione ";
                  $sql_protocollo = "YEAR(datfat) = ".$form['annemi']." AND tipdoc LIKE 'F__' AND seziva = $sezione ";
                  break;
                  case "FNC":
                  $sql_documento = "YEAR(datfat) = ".$form['annemi']." and tipdoc = 'FNC' and seziva = $sezione";
                  $sql_protocollo = "YEAR(datfat) = ".$form['annemi']." and tipdoc like 'F__' and seziva = $sezione";
                  break;
                  case "FND":
                  $sql_documento = "YEAR(datfat) = ".$form['annemi']." and tipdoc = 'FND' and seziva = $sezione";
                  $sql_protocollo = "YEAR(datfat) = ".$form['annemi']." and tipdoc like 'F__' and seziva = $sezione";
                  break;
            }
            $rs_ultimo_documento = gaz_dbi_dyn_query("numdoc, numfat*1 AS fattura", $gTables['tesdoc'], $sql_documento,$where,0,1);
            $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
            // se e' il primo documento dell'anno, resetto il contatore
            if ($ultimo_documento) {
               $form['numfat'] = $ultimo_documento['fattura'] + 1;
               $form['numdoc'] = $ultimo_documento['numdoc'] + 1;
            } else {
               $form['numfat'] = 1;
               $form['numdoc'] = 1;
            }
            $rs_ultimo_protocollo = gaz_dbi_dyn_query("protoc", $gTables['tesdoc'], $sql_protocollo,"protoc desc",0,1);
            $ultimo_protocollo = gaz_dbi_fetch_array($rs_ultimo_protocollo);
            // se e' il primo protocollo dell'anno, resetto il contatore
            if ($ultimo_protocollo) {
               $form['protoc'] = $ultimo_protocollo['protoc'] + 1;
            } else {
               $form['protoc'] = 1;
            }
            if ($form['tipdoc'] == 'DDT'){  //ma se e' un ddt il protocollo è 0 così come il numero e data fattura
               $form['protoc'] = 0;
               $form['numfat'] = 0;
               $form['datfat'] = 0;
               $form['status'] = 'FATTURARE';
            } else { //in tutti gli altri casi coincide con il numero documento.
               $form['numdoc'] = $form['numfat'];
               $form['datfat'] = $datemi;
               $form['status'] = 'DA CONTAB';
            }
            //inserisco la testata
            $form['initra'] = $initra;
            $form['datemi'] = $datemi;
            tesdocInsert($form);
            //recupero l'id assegnato dall'inserimento
            $ultimo_id = gaz_dbi_last_id();
            //inserisco i righi
            foreach ($form['rows'] as $i => $v) {
                  $form['rows'][$i]['id_tes'] = $ultimo_id;
                  rigdocInsert($form['rows'][$i]);
                  $last_rigdoc_id = gaz_dbi_last_id();
                  if (isset($form["row_$i"])) { //se è un rigo testo lo inserisco il contenuto in body_text
                      bodytextInsert(array('table_name_ref'=>'rigdoc','id_ref'=>$last_rigdoc_id,'body_text'=>$form["row_$i"]));
                      gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $last_rigdoc_id, 'id_body_text', gaz_dbi_last_id());
                  }
                  if ($admin_aziend['conmag'] == 2 and
                     $form['rows'][$i]['tiprig'] == 0 and
                     !empty($form['rows'][$i]['codart'])) { //se l'impostazione in azienda prevede l'aggiornamento automatico dei movimenti di magazzino
                     $upd_mm->uploadMag($last_rigdoc_id,
                                    $form['tipdoc'],
                                    $form['numdoc'],
                                    $form['protoc'].'/'.$form['seziva'],
                                    $datemi,
                                    $form['clfoco'],
                                    $form['sconto'],
                                    $form['caumag'],
                                    $form['rows'][$i]['codart'],
                                    $form['rows'][$i]['quanti'],
                                    $form['rows'][$i]['prelis'],
                                    $form['rows'][$i]['sconto'],
                                    0,
                                    $admin_aziend['stock_eval_method']
                                    );
                  }
            }
            $_SESSION['print_request']=$ultimo_id;
            header("Location: invsta_docven.php");
            exit;
          }
    }
  }
  // Se viene inviata la richiesta di conferma cliente
  if ($_POST['hidden_req']=='clfoco') {
    $anagrafica = new Anagrafica();
    if (preg_match("/^id_([0-9]+)$/",$form['clfoco'],$match)) {
        $cliente = $anagrafica->getPartnerData($match[1],1);
    } else {
        $cliente = $anagrafica->getPartner($form['clfoco']);
    }
    $result = gaz_dbi_get_row($gTables['imball'],"codice",$cliente['imball']);
    $form['imball']=$result['descri'];
    if (($form['net_weight'] - $form['gross_weight']) >= 0) {
       $form['gross_weight'] += $result['weight'];
    }
    $result = gaz_dbi_get_row($gTables['portos'],"codice",$cliente['portos']);
    $form['portos']=$result['descri'];
    $result = gaz_dbi_get_row($gTables['spediz'],"codice",$cliente['spediz']);
    $form['spediz']=$result['descri'];
    $form['destin']=$cliente['destin'];
    $form['id_agente']=$cliente['id_agente'];
    if ($form['id_agente'] > 0) { // carico la provvigione standard
           $provvigione = new Agenti;
           $form['in_provvigione'] = $provvigione -> getPercent($form['id_agente']);
           if (isset($_POST['rows'])) {  // aggiorno le provvigioni sui righi
              foreach ($_POST['rows'] as $k => $val) {
                 $form['rows'][$k]['provvigione'] = $provvigione -> getPercent($form['id_agente'],$val['codart']);
              }
           }
    }
    $form['id_des']=$cliente['id_des'];
    $id_des = $anagrafica->getPartner($form['id_des']);
    $form['search']['id_des']=substr($id_des['ragso1'],0,10);
    $form['in_codvat']=$cliente['aliiva'];
    if ($cliente['ritenuta']>0 && $admin_aziend['ritenuta']==0) { // carico la ritenuta se previsto
       $form['in_ritenuta']=$cliente['ritenuta'];
    }
    $form['sconto']=$cliente['sconto'];
    $form['pagame']=$cliente['codpag'];
    $form['change_pag']=$cliente['codpag'];
    $form['banapp']=$cliente['banapp'];
    $form['listin']=$cliente['listin'];
    $pagame = gaz_dbi_get_row($gTables['pagame'],"codice",$form['pagame']);
    if (($pagame['tippag'] == 'B' or $pagame['tippag'] == 'T' or $pagame['tippag'] == 'V')
        and $cliente['speban'] == 'S') {
           $form['speban'] = $admin_aziend['sperib'];
           $form['numrat'] = $pagame['numrat'];
           $form['stamp'] = $admin_aziend['perbol'];
           $form['round_stamp'] = $admin_aziend['round_bol'];
    } elseif ($pagame['tippag'] == 'R') {
           $form['speban'] = 0.00;
           $form['numrat'] = 1;
           $form['stamp'] = $admin_aziend['ricbol'];
           $form['round_stamp'] = 0;
    } else {
           $form['speban'] = 0.00;
           $form['numrat'] = 1;
           $form['stamp'] = 0;
           $form['round_stamp'] = 0;
    }
    $form['hidden_req']='';
  }

  // Se viene modificato l'agente
  if ($_POST['hidden_req'] == 'AGENTE') {
      if ($form['id_agente'] > 0) { // carico la provvigione standard
           $provvigione = new Agenti;
           $form['in_provvigione'] = $provvigione -> getPercent($form['id_agente']);
           if (isset($_POST['rows'])) {  // aggiorno le provvigioni sui righi
              foreach ($_POST['rows'] as $k => $val) {
                 $form['rows'][$k]['provvigione'] = $form['in_provvigione'];
                 $form['rows'][$k]['provvigione'] = $provvigione -> getPercent($form['id_agente'],$val['codart']);
              }
           }
    }
    $form['hidden_req']='';
  }

  // Se viene inviata la richiesta di conferma rigo
  if (isset($_POST['in_submit_x'])) {
    $artico = gaz_dbi_get_row($gTables['artico'],"codice",$form['in_codart']);
    // addizione ai totali peso,pezzi,volume
    $form['net_weight'] += $form['in_quanti']*$artico['peso_specifico'];
    $form['gross_weight'] += $form['in_quanti']*$artico['peso_specifico'];
    if ($artico['pack_units'] > 0){
       $form['units'] += intval(round($form['in_quanti']/$artico['pack_units']));
    }
    $form['volume'] += $form['in_quanti']*$artico['volume_specifico'];
    // fine addizione peso,pezzi,volume
    if (substr($form['in_status'],0,6) == "UPDROW"){ //se è un rigo da modificare
         $old_key = intval(substr($form['in_status'],6));
         $form['rows'][$old_key]['tiprig'] = $form['in_tiprig'];
         $form['rows'][$old_key]['descri'] = $form['in_descri'];
         $form['rows'][$old_key]['id_mag'] = $form['in_id_mag'];
         $form['rows'][$old_key]['status'] = "UPDATE";
         $form['rows'][$old_key]['unimis'] = $form['in_unimis'];
         $form['rows'][$old_key]['quanti'] = $form['in_quanti'];
         $form['rows'][$old_key]['codart'] = $form['in_codart'];
         $form['rows'][$old_key]['codric'] = $form['in_codric'];
         $form['rows'][$old_key]['ritenuta'] = $form['in_ritenuta'];
         $form['rows'][$old_key]['provvigione'] = $form['in_provvigione'];
         $form['rows'][$old_key]['prelis'] = number_format($form['in_prelis'],$admin_aziend['decimal_price'],'.','');
         $form['rows'][$old_key]['sconto'] = $form['in_sconto'];
         $form['rows'][$old_key]['codvat'] = $form['in_codvat'];
         $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
         $form['rows'][$old_key]['pervat'] = $iva_row['aliquo'];
         $form['rows'][$old_key]['tipiva'] = $iva_row['tipiva'];
         $form['rows'][$old_key]['scorta'] = '';
         $form['rows'][$old_key]['annota'] = '';
         $form['rows'][$old_key]['pesosp'] = '';
         if ($form['in_tiprig'] == 0 and !empty($form['in_codart'])) {  //rigo normale
            $form['rows'][$old_key]['annota'] = $artico['annota'];
            $form['rows'][$old_key]['pesosp'] = $artico['peso_specifico'];
            $form['rows'][$old_key]['unimis'] = $artico['unimis'];
            $form['rows'][$old_key]['descri'] = $artico['descri'];
            if ($form['listin'] == 2) {
               $form['rows'][$old_key]['prelis'] = number_format($artico['preve2'],$admin_aziend['decimal_price'],'.','');
            } elseif ($form['listin'] == 3) {
               $form['rows'][$old_key]['prelis'] = number_format($artico['preve3'],$admin_aziend['decimal_price'],'.','');
            } else {
               $form['rows'][$old_key]['prelis'] = number_format($artico['preve1'],$admin_aziend['decimal_price'],'.','');
            }
            $magval=array_pop($upd_mm->getStockValue(false,$form['in_codart'],$form['annemi'].'-'.$form['mesemi'].'-'.$form['gioemi'],$admin_aziend['stock_eval_method']));
            $form['rows'][$old_key]['scorta'] = $magval['q_g'] - $artico['scorta'];
         } elseif ($form['in_tiprig'] == 2) { //rigo descrittivo
            $form['rows'][$old_key]['codart'] = "";
            $form['rows'][$old_key]['annota'] = "";
            $form['rows'][$old_key]['pesosp'] = "";
            $form['rows'][$old_key]['unimis'] = "";
            $form['rows'][$old_key]['quanti'] = 0;
            $form['rows'][$old_key]['prelis'] = 0;
            $form['rows'][$old_key]['codric'] = 0;
            $form['rows'][$old_key]['sconto'] = 0;
            $form['rows'][$old_key]['pervat'] = 0;
            $form['rows'][$old_key]['tipiva'] = 0;
            $form['rows'][$old_key]['ritenuta'] = 0;
            $form['rows'][$old_key]['codvat'] = 0;
         } elseif ($form['in_tiprig'] == 1) { //rigo forfait
            $form['rows'][$old_key]['codart'] = "";
            $form['rows'][$old_key]['unimis'] = "";
            $form['rows'][$old_key]['quanti'] = 0;
            $form['rows'][$old_key]['sconto'] = 0;
         } elseif ($form['in_tiprig'] == 3) {   //var.tot.fatt.
            $form['rows'][$old_key]['codart'] = "";
            $form['rows'][$old_key]['quanti'] = "";
            $form['rows'][$old_key]['unimis'] = "";
            $form['rows'][$old_key]['sconto'] = 0;
         }
         ksort($form['rows']);
    } else { //se è un rigo da inserire
         $form['rows'][$next_row]['tiprig'] = $form['in_tiprig'];
         $form['rows'][$next_row]['descri'] = $form['in_descri'];
         $form['rows'][$next_row]['id_mag'] = $form['in_id_mag'];
         $form['rows'][$next_row]['status'] = "INSERT";
         $form['rows'][$next_row]['scorta'] = '';
         if ($form['in_tiprig'] == 0) {  //rigo normale
            $form['rows'][$next_row]['codart'] = $form['in_codart'];
            $form['rows'][$next_row]['annota'] = $artico['annota'];
            $form['rows'][$next_row]['pesosp'] = $artico['peso_specifico'];
            $form['rows'][$next_row]['descri'] = $artico['descri'];
            $form['rows'][$next_row]['unimis'] = $artico['unimis'];
            $form['rows'][$next_row]['prelis'] = number_format($form['in_prelis'],$admin_aziend['decimal_price'],'.','');
            $form['rows'][$next_row]['codric'] = $form['in_codric'];
            $form['rows'][$next_row]['quanti'] = $form['in_quanti'];
            $form['rows'][$next_row]['sconto'] = $form['in_sconto'];
            $form['rows'][$next_row]['ritenuta'] = $form['in_ritenuta'];
            $provvigione = new Agenti;
            $form['rows'][$next_row]['provvigione'] = $provvigione -> getPercent($form['id_agente'],$form['in_codart']);
            if ($form['listin'] == 2) {
               $form['rows'][$next_row]['prelis'] = number_format($artico['preve2'],$admin_aziend['decimal_price'],'.','');
            } elseif ($form['listin'] == 3) {
               $form['rows'][$next_row]['prelis'] = number_format($artico['preve3'],$admin_aziend['decimal_price'],'.','');
            } else {
               $form['rows'][$next_row]['prelis'] = number_format($artico['preve1'],$admin_aziend['decimal_price'],'.','');
            }
            $form['rows'][$next_row]['codvat'] = $admin_aziend['alliva'];
            $iva_azi = gaz_dbi_get_row($gTables['aliiva'],"codice",$admin_aziend['alliva']);
            $form['rows'][$next_row]['pervat'] = $iva_azi['aliquo'];
            $form['rows'][$next_row]['tipiva'] = $iva_azi['tipiva'];
            if ($artico['aliiva'] > 0) {
               $form['rows'][$next_row]['codvat'] = $artico['aliiva'];
               $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$artico['aliiva']);
               $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
               $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
            }
            if ($form['in_codvat'] > 0) {
               $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
               $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
               $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
               $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
            }
            if ($artico['codcon'] > 0 ) {
               $form['rows'][$next_row]['codric'] = $artico['codcon'];
               $form['in_codric'] = $artico['codcon'];
            } elseif (!empty($artico['codice'])) {
               $form['rows'][$next_row]['codric'] = $admin_aziend['impven'];
               $form['in_codric'] = $admin_aziend['impven'];
            }
            if ($form['tipdoc'] == 'FNC') { // nel caso che si tratti di nota di credito
               $form['rows'][$next_row]['codric'] = $admin_aziend['sales_return'];
               $form['in_codric'] = $admin_aziend['sales_return'];
            }
            $magval=array_pop($upd_mm->getStockValue(false,$form['in_codart'],$form['annemi'].'-'.$form['mesemi'].'-'.$form['gioemi'],$admin_aziend['stock_eval_method']));
            $form['rows'][$next_row]['scorta'] = $magval['q_g'] - $artico['scorta'];
         } elseif ($form['in_tiprig'] == 1) { //forfait
            $form['rows'][$next_row]['codart'] = "";
            $form['rows'][$next_row]['annota'] = "";
            $form['rows'][$next_row]['pesosp'] = "";
            $form['rows'][$next_row]['unimis'] = "";
            $form['rows'][$next_row]['quanti'] = 0;
            $form['rows'][$next_row]['prelis'] = 0;
            $form['rows'][$next_row]['codric'] = $form['in_codric'];
            $form['rows'][$next_row]['sconto'] = 0;
            $form['rows'][$next_row]['codvat'] = $admin_aziend['alliva'];
            $iva_azi = gaz_dbi_get_row($gTables['aliiva'],"codice",$admin_aziend['alliva']);
            $form['rows'][$next_row]['pervat'] = $iva_azi['aliquo'];
            $form['rows'][$next_row]['tipiva'] = $iva_azi['tipiva'];
            if ($form['in_codvat'] > 0) {
               $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
               $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
               $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
               $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
            }
            $form['rows'][$next_row]['ritenuta'] = $form['in_ritenuta'];
         } elseif ($form['in_tiprig'] == 2) { //descittivo
            $form['rows'][$next_row]['codart'] = "";
            $form['rows'][$next_row]['annota'] = "";
            $form['rows'][$next_row]['pesosp'] = "";
            $form['rows'][$next_row]['unimis'] = "";
            $form['rows'][$next_row]['quanti'] = 0;
            $form['rows'][$next_row]['prelis'] = 0;
            $form['rows'][$next_row]['codric'] = 0;
            $form['rows'][$next_row]['sconto'] = 0;
            $form['rows'][$next_row]['pervat'] = 0;
            $form['rows'][$next_row]['tipiva'] = 0;
            $form['rows'][$next_row]['ritenuta'] = 0;
            $form['rows'][$next_row]['codvat'] = 0;
         } elseif ($form['in_tiprig'] == 3) {   //var tot
            $form['rows'][$next_row]['codart'] = "";
            $form['rows'][$next_row]['annota'] = "";
            $form['rows'][$next_row]['pesosp'] = "";
            $form['rows'][$next_row]['unimis'] = "";
            $form['rows'][$next_row]['quanti'] = 0;
            $form['rows'][$next_row]['prelis'] = number_format($form['in_prelis'],$admin_aziend['decimal_price'],'.','');
            $form['rows'][$next_row]['codric'] = $form['in_codric'];
            $form['rows'][$next_row]['sconto'] = 0;
            $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
            $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
            $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
            $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
            $form['rows'][$next_row]['ritenuta'] = 0;
         } elseif ($form['in_tiprig']>5 && $form['in_tiprig']<9) { //testo
            $rows_text .= ",row_$next_row";
            $form["row_$next_row"] = "";
            $form['rows'][$next_row]['codart'] = "";
            $form['rows'][$next_row]['annota'] = "";
            $form['rows'][$next_row]['pesosp'] = "";
            $form['rows'][$next_row]['unimis'] = "";
            $form['rows'][$next_row]['quanti'] = 0;
            $form['rows'][$next_row]['prelis'] = 0;
            $form['rows'][$next_row]['codric'] = 0;
            $form['rows'][$next_row]['sconto'] = 0;
            $form['rows'][$next_row]['pervat'] = 0;
            $form['rows'][$next_row]['tipiva'] = 0;
            $form['rows'][$next_row]['ritenuta'] = 0;
            $form['rows'][$next_row]['codvat'] = 0;
         }
    }
     // reinizializzo rigo di input tranne che tipo rigo, aliquota iva, ritenuta e conto ricavo
     $form['in_descri'] = "";
     $form['in_codart'] = "";
     $form['in_unimis'] = "";
     $form['in_prelis'] = 0;
     $form['in_sconto'] = 0;
     $form['in_quanti'] = 0;
     $form['in_id_mag'] = 0;
     $form['in_annota'] = "";
     $form['in_scorta'] = 0;
     $form['in_pesosp'] = 0;
     $form['in_status'] = "INSERT";
     // fine reinizializzo rigo input
     $form['cosear'] = "";
     $next_row++;
  }
  // Se viene inviata la richiesta di spostamento verso l'alto del rigo
  if (isset($_POST['upper_row'])) {
     $upp_key = key($_POST['upper_row']);
     $k_next = $upp_key-1;
     if (isset($form["row_$k_next"])) { //se ho un rigo testo prima gli cambio l'index
         $form["row_$upp_key"] = $form["row_$k_next"];
         unset($form["row_$k_next"]);
         $rows_text=str_replace("row_$k_next","row_$upp_key",$rows_text);
     }
     if ($upp_key > 0) {
        $new_key = $upp_key-1;
     } else {
        $new_key = $next_row-1;
     }
     $pull_row = $form['rows'][$new_key] ;
     $form['rows'][$new_key] = $form['rows'][$upp_key] ;
     $form['rows'][$upp_key] = $pull_row ;
     ksort($form['rows']);
     unset($pull_row);
  }
  // Se viene inviata la richiesta elimina il rigo corrispondente
  if (isset($_POST['del'])) {
    $delri= key($_POST['del']);
    // sottrazione ai totali peso,pezzi,volume
    $artico = gaz_dbi_get_row($gTables['artico'],"codice",$form['rows'][$delri]['codart']);
    $form['net_weight'] -= $form['rows'][$delri]['quanti']*$artico['peso_specifico'];
    $form['gross_weight'] -= $form['rows'][$delri]['quanti']*$artico['peso_specifico'];
    if ($artico['pack_units'] > 0){
       $form['units'] -= intval(round($form['rows'][$delri]['quanti']/$artico['pack_units']));
    }
    $form['volume'] -= $form['rows'][$delri]['quanti']*$artico['volume_specifico'];
    // fine sottrazione peso,pezzi,volume

    // diminuisco o lascio inalterati gli index dei testi
    $rows_text='';
    foreach ($form['rows'] as $k => $val) {
            if (isset($form["row_$k"])) { //se ho un rigo testo
               if ($k > $delri) { //se ho un rigo testo dopo
                   $new_k=$k-1;
                   $rows_text .= ",row_$new_k";
                   $form["row_$new_k"] = $form["row_$k"];
                   unset($form["row_$k"]);
               } else {
                   $rows_text .= ",row_$k";
               }
            }
    }
    array_splice($form['rows'],$delri,1);
    $next_row--;
  }
} elseif ((!isset($_POST['Update'])) and (isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $tesdoc = gaz_dbi_get_row($gTables['tesdoc'],"id_tes",$_GET['id_tes']);
    $anagrafica = new Anagrafica();
    $cliente = $anagrafica->getPartner($tesdoc['clfoco']);
    $id_des = $anagrafica->getPartner($tesdoc['id_des']);
    $rs_rig = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = ".intval($_GET['id_tes']),"id_rig asc");
    $form['id_tes'] = intval($_GET['id_tes']);
    $form['hidden_req'] = '';
    // inizio rigo di input
    $form['in_descri'] = "";
    $form['in_tiprig'] = 0;
    $form['in_artsea'] = $admin_aziend['artsea'];
    $form['in_codart'] = "";
    $form['in_pervat'] = 0;
    $form['in_tipiva'] = 0;
    $form['in_ritenuta'] = $admin_aziend['ritenuta'];
    $form['in_unimis'] = "";
    $form['in_prelis'] = 0;
    $form['in_sconto'] = 0;
    $form['in_quanti'] = 0;
    $form['in_codvat'] = $admin_aziend['alliva'];
    $form['in_codric'] = $admin_aziend['impven'];
    $form['in_id_mag'] = 0;
    $form['in_annota'] = "";
    $form['in_scorta'] = 0;
    $form['in_pesosp'] = 0;
    $form['in_status'] = "INSERT";
    // fine rigo input
    $form['rows'] = array();
    // ...e della testata
    $form['search']['clfoco']=substr($cliente['ragso1'],0,10);
    $form['cosear'] = "";
    $form['seziva'] = $tesdoc['seziva'];
    $form['tipdoc'] = $tesdoc['tipdoc'];
    if ( $tesdoc['tipdoc'] == 'FAD' ) {
       $msg .= '57+';
    }
    if ( $tesdoc['id_con'] > 0 ) {
       $msg .= '58+';
    }
    if ($form['tipdoc'] == 'FNC') { // nel caso che si tratti di nota di credito
        $form['in_codric'] = $admin_aziend['sales_return'];
    }
    $form['template'] = $tesdoc['template'];
    $form['gioemi'] = substr($tesdoc['datemi'],8,2);
    $form['mesemi'] = substr($tesdoc['datemi'],5,2);
    $form['annemi'] = substr($tesdoc['datemi'],0,4);
    $form['giotra'] = substr($tesdoc['initra'],8,2);
    $form['mestra'] = substr($tesdoc['initra'],5,2);
    $form['anntra'] = substr($tesdoc['initra'],0,4);
    $form['oratra'] = substr($tesdoc['initra'],11,2);
    $form['mintra'] = substr($tesdoc['initra'],14,2);
    $form['protoc'] = $tesdoc['protoc'];
    $form['numdoc'] = $tesdoc['numdoc'];
    $form['numfat'] = $tesdoc['numfat'];
    $form['datfat'] = $tesdoc['datfat'];
    $form['clfoco'] = $tesdoc['clfoco'];
    $form['pagame'] = $tesdoc['pagame'];
    $form['change_pag'] = $tesdoc['pagame'];
    $form['speban'] = $tesdoc['speban'];
    $pagame = gaz_dbi_get_row($gTables['pagame'],"codice",$form['pagame']);
    if (($pagame['tippag'] == 'B' or $pagame['tippag'] == 'T' or $pagame['tippag'] == 'V') and $cliente['speban'] == 'S') {
            $form['numrat'] = $pagame['numrat'];
    } else {
            $form['speban'] = 0.00;
            $form['numrat'] = 1;
    }
    $form['banapp'] = $tesdoc['banapp'];
    $form['vettor'] = $tesdoc['vettor'];
    $form['id_agente'] = $tesdoc['id_agente'];
    $provvigione = new Agenti;
    $form['in_provvigione'] = $provvigione -> getPercent($form['id_agente']);
    $form['net_weight'] = $tesdoc['net_weight'];
    $form['gross_weight'] = $tesdoc['gross_weight'];
    $form['units'] = $tesdoc['units'];
    $form['volume'] = $tesdoc['volume'];
    $form['listin'] = $tesdoc['listin'];
    $form['spediz'] = $tesdoc['spediz'];
    $form['portos'] = $tesdoc['portos'];
    $form['imball'] = $tesdoc['imball'];
    $form['destin'] = $tesdoc['destin'];
    $form['id_des'] = $tesdoc['id_des'];
    $form['search']['id_des']=substr($id_des['ragso1'],0,10);
    $form['traspo'] = $tesdoc['traspo'];
    $form['spevar'] = $tesdoc['spevar'];
    $form['stamp'] = $tesdoc['stamp'];
    $form['round_stamp'] = $tesdoc['round_stamp'];
    if ($form['tipdoc']=='VRI') {
        $form['round_stamp'] = -1;
        $form['stamp'] = $admin_aziend['ricbol'];
    }
    $form['cauven'] = $tesdoc['cauven'];
    $form['caucon'] = $tesdoc['caucon'];
    $form['caumag'] = $tesdoc['caumag'];
    $form['caucon'] = $tesdoc['caucon'];
    $form['sconto'] = $tesdoc['sconto'];
    $next_row = 0;
    $rows_text = '';
    while ($rigo = gaz_dbi_fetch_array($rs_rig)) {
       $articolo = gaz_dbi_get_row($gTables['artico'],"codice",$rigo['codart']);
       if ($rigo['id_body_text'] > 0) { //se ho un rigo testo
           $text = gaz_dbi_get_row($gTables['body_text'],"id_body",$rigo['id_body_text']);
           $rows_text .= ",row_$next_row";
           $form["row_$next_row"] = $text['body_text'];
       }
       $form['rows'][$next_row]['descri'] = $rigo['descri'];
       $form['rows'][$next_row]['tiprig'] = $rigo['tiprig'];
       $form['rows'][$next_row]['codart'] = $rigo['codart'];
       $form['rows'][$next_row]['pervat'] = $rigo['pervat'];
       $iva_row = gaz_dbi_get_row($gTables['aliiva'],'codice',$rigo['codvat']);
       $form['rows'][$next_row]['tipiva'] = $iva_row['tipiva'];
       $form['rows'][$next_row]['ritenuta'] = $rigo['ritenuta'];
       $form['rows'][$next_row]['unimis'] = $rigo['unimis'];
       $form['rows'][$next_row]['prelis'] = number_format($rigo['prelis'],$admin_aziend['decimal_price'],'.','');
       $form['rows'][$next_row]['sconto'] = $rigo['sconto'];
       $form['rows'][$next_row]['quanti'] = gaz_format_quantity($rigo['quanti'],0,$admin_aziend['decimal_quantity']);
       $form['rows'][$next_row]['codvat'] = $rigo['codvat'];
       $form['rows'][$next_row]['codric'] = $rigo['codric'];
       $form['rows'][$next_row]['provvigione'] = $rigo['provvigione'];
       $form['rows'][$next_row]['id_mag'] = $rigo['id_mag'];
       $form['rows'][$next_row]['annota'] = $articolo['annota'];
       $magval=array_pop($upd_mm->getStockValue(false,$rigo['codart'],$form['annemi'].'-'.$form['mesemi'].'-'.$form['gioemi'],$admin_aziend['stock_eval_method']));
       $form['rows'][$next_row]['scorta'] = $magval['q_g'] - $articolo['scorta'];
       $form['rows'][$next_row]['pesosp'] = $articolo['peso_specifico'];
       $form['rows'][$next_row]['status'] = "UPDATE";
       $next_row++;
    }
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['tipdoc'] = $_GET['tipdoc'];
    $form['id_tes'] = "";
    $form['gioemi'] = date("d");
    $form['mesemi'] = date("m");
    $form['annemi'] = date("Y");
    $form['giotra'] = date("d");
    $form['mestra'] = date("m");
    $form['anntra'] = date("Y");
    $form['oratra'] = date("H");
    $form['mintra'] = date("i");
    $form['rows'] = array();
    $next_row = 0;
    $rows_text ='';
    $form['hidden_req'] = '';
    // inizio rigo di input
    $form['in_descri'] = "";
    $form['in_tiprig'] = 0;
    $form['in_artsea'] = $admin_aziend['artsea'];
    $form['in_codart'] = "";
    $form['in_pervat'] = "";
    $form['in_tipiva'] = "";
    $form['in_ritenuta'] = $admin_aziend['ritenuta'];
    $form['in_unimis'] = "";
    $form['in_prelis'] = 0;
    $form['in_sconto'] = 0;
    $form['in_quanti'] = 0;
    $form['in_codvat'] = $admin_aziend['alliva'];
    $form['in_codric'] = $admin_aziend['impven'];
    if ($form['tipdoc'] == 'FNC') { // nel caso che si tratti di nota di credito
        $form['in_codric'] = $admin_aziend['sales_return'];
        if ($form['in_codric'] < 300000000) {
           $form['in_codric']='4';
        }
    }
    $form['in_provvigione'] = 0;
    $form['in_id_mag'] = 0;
    $form['in_annota'] = "";
    $form['in_scorta'] = 0;
    $form['in_pesosp'] = 0;
    $form['in_status'] = "INSERT";
    // fine rigo input
    $form['search']['clfoco']='';
    $form['cosear'] = "";
    if (!isset($_GET['seziva']) and $form['tipdoc'] == 'FAI') {
         switch($admin_aziend['fatimm']) {
                case "1":
                $form['seziva'] = 1;
                break;
                case "2":
                $form['seziva'] = 2;
                break;
                case "3":
                $form['seziva'] = 3;
                break;
                case "R":
                $form['seziva'] = substr($auxil,0,1);
                break;
                case "U":
                $rs_ultimo = gaz_dbi_dyn_query("seziva", $gTables['tesdoc'], "tipdoc = 'FAI'","datfat desc",0,1);
                $ultimo = gaz_dbi_fetch_array($rs_ultimo);
                if ($ultimo){
                   $form['seziva'] = $ultimo['seziva'];
                } else {
                   $form['seziva'] = 1;
                }
                break;
                default:
                $form['seziva'] = 1;
         }
    } elseif (!isset($_GET['seziva'])){
         $form['seziva'] = 1;
    } else {
         $form['seziva'] = $_GET['seziva'];
    }
    //cerco l'ultimo template
    $rs_ultimo_template = gaz_dbi_dyn_query($gTables['tesdoc'].".template", $gTables['tesdoc'] ,
                                   "tipdoc = '".$form['tipdoc']."' and seziva = ".
                                   $form['seziva'],'datfat desc, protoc desc',0,1);
    $ultimo_template = gaz_dbi_fetch_array($rs_ultimo_template);
    if ($ultimo_template['template'] == 'FatturaImmediata') {
       $form['template'] = "FatturaImmediata";
    } elseif($ultimo_template['template'] != '') {
       $form['template'] = $ultimo_template['template'];
    } elseif ($form['tipdoc'] == 'FAP'){  //se e' una parcella
       $form['template'] = 'Parcella';
    } else {
       $form['template'] = "FatturaSemplice";
    }
    $form['protoc'] = "";
    $form['numdoc'] = "";
    $form['numfat'] = "";
    $form['datfat'] = "";
    $form['clfoco'] = "";
    $form['pagame'] = "";
    $form['change_pag'] = "";
    $form['banapp'] = "";
    $form['vettor'] = "";
    $form['id_agente'] = 0;
    $form['net_weight'] = 0;
    $form['gross_weight'] = 0;
    $form['units'] = 0;
    $form['volume'] = 0;
    $form['listin'] = "";
    $form['destin'] = "";
    $form['id_des'] = "";
    $form['search']['id_des']='';
    $form['spediz'] = "";
    $form['portos'] = "";
    $form['imball'] = "";
    $form['traspo'] = 0.00;
    $form['numrat'] = 1;
    $form['speban'] = 0;
    $form['spevar'] = 0;
    $form['stamp'] = 0;
    $form['round_stamp'] = $admin_aziend['round_bol'];
    if ($form['tipdoc']=='VRI') {
        $form['round_stamp'] = -1;
        $form['stamp'] = $admin_aziend['ricbol'];
    }
    $form['cauven'] = 0;
    $form['caucon'] = '';
    $form['caumag'] = 0;
    $form['sconto'] = 0;
    $cliente['indspe']="";
}

require("../../library/include/header.php");
$script_transl = HeadMain(0,array('tiny_mce/tiny_mce',
                                  'boxover/boxover',
                                  'calendarpopup/CalendarPopup',
                                  'jquery/jquery-1.4.2.min',
                                  'jquery/ui/jquery.ui.core',
                                  'jquery/ui/jquery.ui.widget',
                                  'jquery/ui/jquery.ui.position',
                                  'jquery/ui/jquery.ui.autocomplete',
                                  'jquery/autocomplete_anagra'));

if ($form['id_tes'] > 0) {
   $title = ucfirst($script_transl[$toDo].$script_transl[0][$form['tipdoc']])." n.".$form['numdoc'];
} else {
   $title = ucfirst($script_transl[$toDo].$script_transl[0][$form['tipdoc']]);
}

echo "<script type=\"text/javascript\">
// Initialize TinyMCE with the new plugin and menu button
tinyMCE.init({
  mode : \"exact\",
  theme : \"advanced\",
  language : \"it\",
  forced_root_block : false,
  force_br_newlines : true,
  force_p_newlines : false,
  elements : \"".$rows_text."\",
  plugins : \"table,advlink\",
  theme_advanced_buttons1 : \"mymenubutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,|,link,unlink,code,|,formatselect,forecolor,backcolor,|,tablecontrols\",
  theme_advanced_buttons2 : \"\",
  theme_advanced_buttons3 : \"\",
  theme_advanced_toolbar_location : \"external\",
  theme_advanced_toolbar_align : \"left\",
});

function pulldown_menu(selectName, destField)
{
    // Create a variable url to contain the value of the
    // selected option from the the form named broven and variable selectName
    var url = document.docven[selectName].options[document.docven[selectName].selectedIndex].value;
    document.docven[destField].value = url;
}";
?>
</script>
<SCRIPT LANGUAGE="JavaScript" ID="datapopup">
var cal = new CalendarPopup();
cal.setReturnFunction("setMultipleValues");
function setMultipleValues(y,m,d) {
  document.docven.anntra.value=y;
  document.docven.mestra.value=LZ(m);
  document.docven.giotra.value=LZ(d);
  }
</SCRIPT>
<?php
echo "<form method=\"POST\" name=\"docven\" >\n";
$gForm = new venditForm();
echo "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">\n";
echo "<input type=\"hidden\" value=\"".$form['id_tes']."\" name=\"id_tes\">\n";
echo "<input type=\"hidden\" value=\"".$form['seziva']."\" name=\"seziva\">\n";
echo "<input type=\"hidden\" value=\"".$form['tipdoc']."\" name=\"tipdoc\">\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\">\n";
echo "<input type=\"hidden\" value=\"".$form['change_pag']."\" name=\"change_pag\">\n";
echo "<input type=\"hidden\" value=\"".$form['protoc']."\" name=\"protoc\">\n";
echo "<input type=\"hidden\" value=\"".$form['numdoc']."\" name=\"numdoc\">\n";
echo "<input type=\"hidden\" value=\"".$form['numfat']."\" name=\"numfat\">\n";
echo "<input type=\"hidden\" value=\"".$form['datfat']."\" name=\"datfat\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">$title ";
if ($form['tipdoc'] == 'FAI') {
   echo "<select name=\"template\" class=\"FacetSelect\">\n";
   echo "<option value=\"FatturaImmediata\" ";
   if ($form['template'] == "FatturaImmediata") {
      echo " selected";
   }
   echo ">Accompagn.</option>\n";
   echo "<option value=\"FatturaSemplice\" ";
   if ($form['template'] == "FatturaSemplice") {
      echo " selected";
   }
   echo ">Normale</option></select>\n";
} else {
   echo "<input type=\"hidden\" value=\"".$form['template']."\" name=\"template\">\n";
}
echo " :";

$select_cliente = new selectPartner('clfoco');
$select_cliente->selectDocPartner('clfoco',$form['clfoco'],$form['search']['clfoco'],'clfoco',$script_transl['mesg'],$admin_aziend['mascli']);
//tabula solo se non e' stato settato il cliente
$tabula =" tabindex=\"3\" ";
if ($form['clfoco'] > 0)
     $tabula ="";
echo "</div>\n";
echo "<table class=\"Tlarge\">\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[4]</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"seziva\" class=\"FacetSelect\">\n";
for ($counter = 1; $counter <= 3; $counter++) {
    $selected="";
    if ($form["seziva"] == $counter) {
       $selected = " selected ";
    }
    echo "<option value=\"".$counter."\"".$selected.">".$counter."</option>\n";
}
echo "</select></td>\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice( explode('+',chop($msg)),0,-1);
    foreach ($rsmsg as $v){
            $message .= $script_transl['error']."! -> ";
            $rsval = explode('-',chop($v));
            foreach ($rsval as $valmsg){
                    $message .= $script_transl[$valmsg]." ";
            }
            $message .= "<br />";
    }
    echo '<td colspan="4" class="FacetDataTDred">'.$message."</td>\n";
} else {
    echo "<td class=\"FacetFieldCaptionTD\">$script_transl[5]</td><td colspan=\"3\">".$cliente['indspe']."<br />";
    echo "</td>\n";
}
echo "<td class=\"FacetFieldCaptionTD\">$script_transl[6]</td><td class=\"FacetDataTD\">\n";
// select del giorno
echo "\t <select name=\"gioemi\" class=\"FacetSelect\" >\n";
for( $counter = 1; $counter <= 31; $counter++ )
    {
    $selected = "";
    if($counter ==  $form['gioemi'])
            $selected = "selected";
    echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
    }
echo "\t </select>\n";
// select del mese
echo "\t <select name=\"mesemi\" class=\"FacetSelect\" >\n";
for( $counter = 1; $counter <= 12; $counter++ )
    {
    $selected = "";
    if($counter == $form['mesemi'])
            $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime (0,0,0,$counter,1,0)));
    echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
    }
echo "\t </select>\n";
// select del anno
echo "\t <select name=\"annemi\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter = $form['annemi']-10; $counter <= $form['annemi']+10; $counter++ )
    {
    $selected = "";
    if($counter == $form['annemi'])
            $selected = "selected";
    echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
    }
echo "\t </select></td></tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[7]</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"listin\" class=\"FacetSelect\">\n";
for ($lis = 1; $lis <= 3; $lis++) {
    $selected="";
    if ($form["listin"] == $lis) {
        $selected = " selected ";
    }
    echo "<option value=\"".$lis."\"".$selected.">".$lis."</option>\n";
}
echo "</select></td>\n";
echo "<td class=\"FacetFieldCaptionTD\">$script_transl[8]</td><td colspan=\"3\" class=\"FacetDataTD\">\n";
$select_pagame = new selectpagame("pagame");
$select_pagame -> addSelected($form["pagame"]);
$select_pagame -> output();
echo "</td><td class=\"FacetFieldCaptionTD\">$script_transl[9]</td><td  class=\"FacetDataTD\">\n";
$select_banapp = new selectbanapp("banapp");
$select_banapp -> addSelected($form["banapp"]);
$select_banapp -> output();
echo "</td></tr>\n";
echo "<tr>\n";
echo "<td align=\"right\" class=\"FacetFieldCaptionTD\" title=\"".$script_transl['traspo_title']."\">$script_transl[28] $money[2]</td>\n";
echo "<td class=\"FacetDataTD\" title=\"".$script_transl['traspo_title']."\"><input type=\"text\" value=\"".$form['traspo']."\" name=\"traspo\" maxlength=\"6\" size=\"1\" onchange=\"this.form.submit()\" /></td>\n";
echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">".$script_transl[51]."</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"caumag\" class=\"FacetSelect\">\n";
$result = gaz_dbi_dyn_query("*", $gTables['caumag']," clifor = -1 AND operat = ".$docOperat[$form['tipdoc']],"codice asc, descri asc");
while ($row = gaz_dbi_fetch_array($result)) {
    $selected="";
    if($form["caumag"] == $row['codice']) {
       $selected = " selected ";
    }
    echo "<option value=\"".$row['codice']."\"".$selected.">".$row['codice']."-".substr($row['descri'],0,20)."</option>\n";
}
echo "</select></td>\n";
echo "<td class=\"FacetFieldCaptionTD\" title=\"".$script_transl['speban_title']."\">".$script_transl['speban']."</td>
      <td class=\"FacetDataTD\" title=\"".$script_transl['speban_title']."\"><input type=\"text\" value=\"".$form['speban']."\" name=\"speban\" maxlength=\"6\" size=\"1\" onchange=\"this.form.submit()\" /> x ".$form['numrat']."</td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['id_agente']."</td>";
echo "<td class=\"FacetDataTD\">\n";
     $select_agente = new selectAgente("id_agente");
     $select_agente -> addSelected($form["id_agente"]);
     $select_agente -> output();
echo "</td></tr></table>\n";
echo "<div class=\"FacetSeparatorTD\" align=\"center\">$script_transl[1]</div>\n";
echo "<table class=\"Tlarge\">\n";
echo "<input type=\"hidden\" value=\"".$form['in_descri']."\" name=\"in_descri\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_pervat']."\" name=\"in_pervat\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_tipiva']."\" name=\"in_tipiva\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_ritenuta']."\" name=\"in_ritenuta\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_unimis']."\" name=\"in_unimis\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_prelis']."\" name=\"in_prelis\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_id_mag']."\" name=\"in_id_mag\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_annota']."\" name=\"in_annota\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_scorta']."\" name=\"in_scorta\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_pesosp']."\" name=\"in_pesosp\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_status']."\" name=\"in_status\" />\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<tr><td class=\"FacetColumnTD\">$script_transl[15]:\n";
$select_artico = new selectartico("in_codart");
$select_artico -> addSelected($form['in_codart']);
$select_artico -> output(substr($form['cosear'],0,20),$form['in_artsea']);
echo $script_transl['in_artsea'];
$gForm->selSearchItem('in_artsea',$form['in_artsea']);
echo "\n$script_transl[17]:";
$gForm->selTypeRow('in_tiprig',$form['in_tiprig']);
echo "</TD><TD class=\"FacetColumnTD\">$script_transl[16]: <input type=\"text\" value=\"".$form['in_quanti']."\" maxlength=\"11\" size=\"7\" name=\"in_quanti\" tabindex=\"5\" accesskey=\"q\">\n";
echo "</TD><TD class=\"FacetColumnTD\" align=\"right\"><input type=\"image\" name=\"in_submit\" src=\"../../library/images/vbut.gif\" tabindex=\"6\" title=\"".$script_transl['submit'].$script_transl['thisrow']."!\">\n";
echo "</td></tr>\n";
echo "<tr><td class=\"FacetColumnTD\">$script_transl[18]: ";
$ric=intval(substr($form['in_codric'],0,1));
if ($form['tipdoc'] == 'FAP') {
   $ric=array('sub',1,2,4,5);
}
$gForm->selectAccount('in_codric',$form['in_codric'],$ric);
echo " %$script_transl[24]: <input type=\"text\" value=\"".$form['in_sconto']."\" maxlength=\"4\" size=\"1\" name=\"in_sconto\">";
echo " %$script_transl[56]: <input type=\"text\" value=\"".$form['in_provvigione']."\" maxlength=\"6\" size=\"1\" name=\"in_provvigione\">";
echo ' %'.$script_transl['ritenuta'].": <input type=\"text\" value=\"".$form['in_ritenuta']."\" maxlength=\"6\" size=\"1\" name=\"in_ritenuta\">";
echo "</TD><TD class=\"FacetColumnTD\"> $script_transl[19]: ";
$select_in_codvat = new selectaliiva("in_codvat");
$select_in_codvat -> addSelected($form["in_codvat"]);
$select_in_codvat -> output();
echo "</td><TD class=\"FacetColumnTD\"></TD></tr>\n";
$totimp_body=0.00;
$totimp_decalc=0.00;
$totivafat=0.00;
$totimpfat=0.00;
echo "</table>\n";
echo "<table class=\"Tlarge\">\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[20]</td>
          <td colspan=\"2\" class=\"FacetFieldCaptionTD\">$script_transl[21]</td>
          <td class=\"FacetFieldCaptionTD\">$script_transl[22]</td>
          <td class=\"FacetFieldCaptionTD\">$script_transl[16]</td>
          <td class=\"FacetFieldCaptionTD\">$script_transl[23]</td>
          <td class=\"FacetFieldCaptionTD\">%".substr($script_transl[24],0,2).".</td>
          <td class=\"FacetFieldCaptionTD\">%".substr($script_transl[56],0,5).".</td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[25]</td>
          <td class=\"FacetFieldCaptionTD\">$script_transl[19]</td>
          <td class=\"FacetFieldCaptionTD\">$script_transl[18]</td>
          <td class=\"FacetFieldCaptionTD\"></td>
          </tr>\n";
$castle=array();
$decalc_castle=array();
$rit=0;
$carry=0;
foreach ($form['rows'] as $k => $v) {
        //creo il castelletto IVA
        $imprig=0;
        if ($v['tiprig'] <= 1) {
            $imprig = CalcolaImportoRigo($v['quanti'], $v['prelis'], $v['sconto']);
            $v_for_castle = CalcolaImportoRigo($v['quanti'], $v['prelis'], array($v['sconto'],$form['sconto']));
            if ($v['tiprig'] == 1) {//ma se del tipo forfait
                $imprig = CalcolaImportoRigo(1, $v['prelis'], 0);
                $v_for_castle = CalcolaImportoRigo(1, $v['prelis'], $form['sconto']);
            }
            if (!isset($castle[$v['codvat']])) {
                $castle[$v['codvat']] = 0.00;
                if ($v['tipiva']!='C' && $v['tipiva']!='S' ) {
                   $decalc_castle[$v['codvat']] = 0.00;
                }
            }
            $totimp_body += $imprig;
            $castle[$v['codvat']] += $v_for_castle;
            if ($v['tipiva']!='C' && $v['tipiva']!='S' ) {
                   $decalc_castle[$v['codvat']] += $v_for_castle;
                   $totimp_decalc += $v_for_castle;
            }
            $totimpfat += $v_for_castle;
            $rit+=round($imprig*$v['ritenuta']/100,2);
        } elseif ($v['tiprig'] == 3) {
            $carry+=$v['prelis'];
        }
        $descrizione=$v['descri'];
        echo "<input type=\"hidden\" value=\"".$v['codart']."\" name=\"rows[$k][codart]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['status']."\" name=\"rows[$k][status]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['tiprig']."\" name=\"rows[$k][tiprig]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['codvat']."\" name=\"rows[$k][codvat]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['pervat']."\" name=\"rows[$k][pervat]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['tipiva']."\" name=\"rows[$k][tipiva]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['ritenuta']."\" name=\"rows[$k][ritenuta]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['codric']."\" name=\"rows[$k][codric]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['id_mag']."\" name=\"rows[$k][id_mag]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['annota']."\" name=\"rows[$k][annota]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['scorta']."\" name=\"rows[$k][scorta]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['pesosp']."\" name=\"rows[$k][pesosp]\">\n";
        $rit_title = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$script_transl['ritenuta'].$v['ritenuta'].'% = '.gaz_format_number(round($imprig*$v['ritenuta']/100,2))."]  fade=[on] fadespeed=[0.03] \"";
        //stampo i righi in modo diverso a secondo del tipo
        switch($v['tiprig']) {
        case "0":
        echo "<tr>";
        $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$v['annota']."] body=[<center><img src='../root/view.php?table=artico&value=".$v['codart']."'>] fade=[on] fadespeed=[0.03] \"";
        if ($v['pesosp'] != 0){
            $boxpeso = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[peso = ".gaz_format_number($v['quanti'] * $v['pesosp'])."]  fade=[on] fadespeed=[0.03] \"";
        } else {
            $boxpeso = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[]  fade=[on] fadespeed=[0.03] \"";
        }
        if ($v['scorta'] < 0){
            $scorta_col = 'FacetDataTDsmallRed';
        } else {
            $scorta_col = 'FacetDataTDsmall';
        }
        echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."! Sottoscorta =".$v['scorta']."\"><input class=\"$scorta_col\" type=\"submit\" name=\"upd_row[$k]\" value=\"".$v['codart']."\" /></td>\n";
        echo "<td $boxover><input type=\"text\" name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"50\" size=\"50\" /></td><td><input type=\"image\" name=\"upper_row[$k]\" src=\"../../library/images/upp.png\" title=\"".$script_transl['3']."!\" /></td>\n";
        echo "<td $boxpeso><input type=\"text\" name=\"rows[$k][unimis]\" value=\"".$v['unimis']."\" maxlength=\"3\" size=\"1\" /></td>\n";
        echo "<td $boxpeso><input type=\"text\" name=\"rows[$k][quanti]\" value=\"".$v['quanti']."\" align=\"right\" maxlength=\"11\" size=\"4\" onchange=\"this.form.hidden_req.value='ROW'; this.form.submit();\" /></td>\n";
        echo "<td><input type=\"text\" name=\"rows[$k][prelis]\" value=\"".$v['prelis']."\" align=\"right\" maxlength=\"11\" size=\"7\" onchange=\"this.form.submit()\" /></td>\n";
        echo "<td><input type=\"text\" name=\"rows[$k][sconto]\" value=\"".$v['sconto']."\" maxlength=\"4\" size=\"1\" onchange=\"this.form.submit()\" /></td>\n";
        echo "<td><input type=\"text\" name=\"rows[$k][provvigione]\" value=\"".$v['provvigione']."\" maxlength=\"6\" size=\"1\" /></td>\n";
        echo "<td $rit_title align=\"right\">".gaz_format_number($imprig)."</td>\n";
        echo "<td $rit_title >".$v['pervat']."%</td>\n";
        echo "<td $rit_title >".$v['codric']."</td>\n";
        break;
        case "1":
        echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."!\">
              <input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"".$script_transl['typerow'][$v['tiprig']]."\" /></td>\n";
        echo "<td><input type=\"text\"   name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"50\" size=\"50\" /></td><td><input type=\"image\" name=\"upper_row[$k]\" src=\"../../library/images/upp.png\" title=\"".$script_transl['3']."!\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][provvigione]\" value=\"\" /></td>\n";
        echo "<td></td>\n";
        echo "<td $rit_title align=\"right\"><input style=\"text-align:right\" type=\"text\" name=\"rows[$k][prelis]\" value=\"".number_format($v['prelis'],2,'.','')."\" align=\"right\" maxlength=\"11\" size=\"7\" onchange=\"this.form.submit()\" /></td>\n";
        echo "<td $rit_title >".$v['pervat']."%</td>\n";
        echo "<td $rit_title >".$v['codric']."</td>\n";
        break;
        case "2":
        echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."!\">
              <input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"".$script_transl['typerow'][$v['tiprig']]."\" /></td>\n";
        echo "<td><input type=\"text\"   name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"50\" size=\"50\" /></td><td><input type=\"image\" name=\"upper_row[$k]\" src=\"../../library/images/upp.png\" title=\"".$script_transl['3']."!\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][provvigione]\" value=\"\" /></td>\n";
        echo "<td></td>\n";
        echo "<td></td>\n";
        echo "<td></td>\n";
        break;
        case "3":
        echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."!\">
              <input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"".$script_transl['typerow'][$v['tiprig']]."\" /></td>\n";
        echo "<td><input type=\"text\"   name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"50\" size=\"50\"></td><td><input type=\"image\" name=\"upper_row[$k]\" src=\"../../library/images/upp.png\" title=\"".$script_transl['3']."!\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>\n";
        echo "<td><input type=\"hidden\" name=\"rows[$k][provvigione]\" value=\"\" /></td>\n";
        echo "<td></td>\n";
        echo "<td align=\"right\"><input style=\"text-align:right\" type=\"text\" name=\"rows[$k][prelis]\" value=\"".number_format($v['prelis'],2,'.','')."\" align=\"right\" maxlength=\"11\" size=\"7\" onchange=\"this.form.submit()\" /></td>\n";
        echo "<td></td>\n";
        echo "<td></td>\n";
        break;
        case "6":
        case "7":
        case "8":
        echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."!\">
              <input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"".$script_transl['typerow'][$v['tiprig']]."\" /></td>\n";
        echo "<td colspan=\"10\"><textarea id=\"row_$k\" name=\"row_$k\" style=\"width:100%;height:100px;\">".$form["row_$k"]."</textarea></td>\n";
        echo "<input type=\"hidden\" name=\"rows[$k][descri]\" value=\"\" />\n";
        echo "<input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" />\n";
        echo "<input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" />\n";
        echo "<input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" />\n";
        echo "<input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" />\n";
        echo "<input type=\"hidden\" name=\"rows[$k][provvigione]\" value=\"\" /></td>\n";
        break;
        }
        echo "<TD align=\"right\"><input type=\"image\" name=\"del[$k]\" src=\"../../library/images/xbut.gif\" title=\"".$script_transl['delete'].$script_transl['thisrow']."!\" /></td></tr>\n";
    }
echo "</table>\n";
echo "<div class=\"FacetSeparatorTD\" align=\"center\">$script_transl[2]</div>\n";
echo "<table class=\"Tlarge\">\n";
echo "<input type=\"hidden\" value=\"".$form['numrat']."\" name=\"numrat\">\n";
echo "<input type=\"hidden\" value=\"".$form['spevar']."\" name=\"spevar\">\n";
echo "<input type=\"hidden\" value=\"".$form['stamp']."\" name=\"stamp\">\n";
echo "<input type=\"hidden\" value=\"".$form['round_stamp']."\" name=\"round_stamp\">\n";
echo "<input type=\"hidden\" value=\"".$form['cauven']."\" name=\"cauven\">\n";
echo "<input type=\"hidden\" value=\"".$form['caucon']."\" name=\"caucon\">\n";
if ($form['tipdoc']=='DDT' || $form['template']=='FatturaImmediata') {
     echo "<tr>";
     echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[26]</td>\n";
     echo "<td colspan=\"2\" class=\"FacetDataTD\"><input type=\"text\" name=\"imball\" value=\"".$form["imball"]."\" maxlength=\"50\" size=\"25\" class=\"FacetInput\">\n";
     $select_spediz = new SelectValue("imballo");
     $select_spediz -> output('imball', 'imball');
     echo "</td>";
     echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[27]</td>\n";
     echo "<td colspan=\"3\" class=\"FacetDataTD\"><input type=\"text\" name=\"spediz\" value=\"".$form["spediz"]."\" maxlength=\"50\" size=\"25\" class=\"FacetInput\">\n";
     $select_spediz = new SelectValue("spedizione");
     $select_spediz -> output('spediz', 'spediz');
     echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[14]</td>";
     echo "</td>";
     echo "<td colspan=\"2\" class=\"FacetDataTD\">\n";
     $select_vettor = new selectvettor("vettor");
     $select_vettor -> addSelected($form["vettor"]);
     $select_vettor -> output();
     echo "</td>";
     echo "</tr>\n";
     echo "<tr>";
     echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[29]</td>\n";
     echo "<td colspan=\"2\" class=\"FacetDataTD\"><input type=\"text\" name=\"portos\" value=\"".$form["portos"]."\" maxlength=\"50\" size=\"25\" class=\"FacetInput\">\n";
     $select_spediz = new SelectValue("portoresa");
     $select_spediz -> output('portos', 'portos');
     echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[30]</td>\n";
     echo "<td colspan=\"3\" class=\"FacetDataTD\"><INPUT class=\"FacetText\" TYPE=\"text\" NAME=\"giotra\" VALUE=\"".$form['giotra']."\" size=\"2\">\n";
     echo "<INPUT class=\"FacetText\" TYPE=\"text\" NAME=\"mestra\" VALUE=\"".$form['mestra']."\" size=\"2\">\n";
     echo "<INPUT class=\"FacetText\" TYPE=\"text\" NAME=\"anntra\" VALUE=\"".$form['anntra']."\" size=\"2\">\n";
     echo "<A HREF=\"#\" onClick=\"cal.showCalendar('anchor','".$form['mestra']."/".$form['giotra']."/".$form['anntra']."'); return false;\" TITLE=\" cambia la data! \" NAME=\"anchor\" ID=\"anchor\">\n";
     echo "<img border=\"0\" src=\"../../library/images/cal.png\"></A>$script_transl[31]";
     // select dell'ora
     echo "\t <select name=\"oratra\" class=\"FacetText\" >\n";
     for( $counter = 0; $counter <= 23; $counter++ ) {
          $selected = "";
          if($counter ==  $form['oratra'])
             $selected = "selected";
             echo "\t\t <option value=\"".sprintf('%02d',$counter)."\" $selected >".sprintf('%02d',$counter)."</option>\n";
    }
    echo "\t </select>\n ";
    // select dell'ora
    echo "\t <select name=\"mintra\" class=\"FacetText\" >\n";
    for( $counter = 0; $counter <= 59; $counter++ ) {
         $selected = "";
         if($counter ==  $form['mintra'])
            $selected = "selected";
         echo "\t\t <option value=\"".sprintf('%02d',$counter)."\" $selected >".sprintf('%02d',$counter)."</option>\n";
    }
    echo "\t</select></td>\n";
    echo "<td class=\"FacetFieldCaptionTD\">$script_transl[10]</td>\n";
    if ($form['id_des'] > 0) {
       echo "<td class=\"FacetDataTD\">\n";
       $select_id_des = new selectPartner('id_des');
       $select_id_des->selectDocPartner('id_des','id_'.$form['id_des'],$form['search']['id_des'],'id_des',$script_transl['mesg'],$admin_aziend['mascli']);
       echo "<input type=\"hidden\" name=\"destin\" value=\"".$form['destin']."\">\n";
    } else {
       echo "<td class=\"FacetDataTD\"><textarea rows=\"1\" cols=\"30\" name=\"destin\" class=\"FacetInput\">".$form["destin"]."</textarea></td>\n";
       echo "<input type=\"hidden\" name=\"id_des\" value=\"".$form['id_des']."\"></td>\n";
       echo "<input type=\"hidden\" name=\"search[id_des]\" value=\"".$form['search']['id_des']."\">\n";
    }
    echo "\t</tr>\n";
    echo "<tr>\n";
    echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[52]</td>\n";
    echo "<td class=\"FacetDataTD\"><input type=\"text\" value=\"".$form['net_weight']."\" name=\"net_weight\" maxlength=\"9\" size=\"5\" ></td>\n";
    echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[53]</td>\n";
    echo "<td class=\"FacetDataTD\"><input type=\"text\" value=\"".$form['gross_weight']."\" name=\"gross_weight\" maxlength=\"9\" size=\"5\" ></td>\n";
    echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[54]</td>\n";
    echo "<td class=\"FacetDataTD\"><input type=\"text\" value=\"".$form['units']."\" name=\"units\" maxlength=\"6\" size=\"4\" ></td>\n";
    echo "<td align=\"right\" class=\"FacetFieldCaptionTD\">$script_transl[55]</td>\n";
    echo "<td class=\"FacetDataTD\"><input type=\"text\" value=\"".$form['volume']."\" name=\"volume\" maxlength=\"9\" size=\"4\" ></td>\n";
    echo "</tr>";
} else {
    echo "<input type=\"hidden\" value=\"".$form['imball']."\" name=\"imball\">\n";
    echo "<input type=\"hidden\" value=\"".$form['spediz']."\" name=\"spediz\">\n";
    echo "<input type=\"hidden\" value=\"".$form['vettor']."\" name=\"vettor\">\n";
    echo "<input type=\"hidden\" value=\"".$form['portos']."\" name=\"portos\">\n";
    echo "<input type=\"hidden\" value=\"".$form['anntra']."\" name=\"anntra\">\n";
    echo "<input type=\"hidden\" value=\"".$form['mestra']."\" name=\"mestra\">\n";
    echo "<input type=\"hidden\" value=\"".$form['giotra']."\" name=\"giotra\">\n";
    echo "<input type=\"hidden\" value=\"".$form['oratra']."\" name=\"oratra\">\n";
    echo "<input type=\"hidden\" value=\"".$form['mintra']."\" name=\"mintra\">\n";
    echo "<input type=\"hidden\" value=\"".$form['id_des']."\" name=\"id_des\">\n";
    echo "<input type=\"hidden\" value=\"".$form['destin']."\" name=\"destin\">\n";
    echo "<input type=\"hidden\" value=\"".$form['net_weight']."\" name=\"net_weight\">\n";
    echo "<input type=\"hidden\" value=\"".$form['gross_weight']."\" name=\"gross_weight\">\n";
    echo "<input type=\"hidden\" value=\"".$form['units']."\" name=\"units\">\n";
    echo "<input type=\"hidden\" value=\"".$form['volume']."\" name=\"volume\">\n";
}

echo "<tr><td class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[32]</td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[33]</td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[34]</td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">% $script_transl[24] <input type=\"text\" name=\"sconto\" value=\"".$form["sconto"]."\" maxlength=\"6\" size=\"1\" onchange=\"this.form.submit()\"></td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[32]</td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[19]</td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">".$script_transl['stamp']."</td>
          <td class=\"FacetFieldCaptionTD\" align=\"right\">$script_transl[36] $money[2]</td>\n";
$somma_spese = $form['traspo'] + $form['speban']*$form['numrat'] + $form['spevar'];
$last=count($decalc_castle);
$acc_val=$somma_spese;
foreach ($castle as $k=> $v) {
      $vat = gaz_dbi_get_row($gTables['aliiva'],"codice",$k);
      if (isset($decalc_castle[$k])) {
        $last--;
        if ($last == 0) {
           $v += $acc_val;
           $totimpfat += $acc_val;
        } else {
           $decalc=round($somma_spese*$v/$totimp_decalc,2);
           $v += $decalc;
           $totimpfat += $decalc;
           $acc_val-=$decalc;
        }
      }
      $ivacast = round($v*$vat['aliquo'])/100;
      $totivafat += $ivacast;
      if ($next_row > 0) {
        echo "<tr><td align=\"right\">".gaz_format_number($v)."</td><td align=\"right\">".$vat['descri']." ".gaz_format_number($ivacast)."</td>\n";
      }
}

if ($next_row > 0) {
        if ($form['stamp'] > 0) {
          $v_stamp = new Compute;
          $stamp = $v_stamp->stampTax($totimpfat+$totivafat+$carry-$rit,$form['stamp'],$form['round_stamp']*$form['numrat']);
        } else {
          $stamp = 0;
        }
        echo "<td align=\"right\">".gaz_format_number($totimp_body)."</td>
              <td align=\"right\">".gaz_format_number(($totimp_body-$totimpfat+$somma_spese),2, '.', '')."</td>
              <td align=\"right\">".gaz_format_number($totimpfat)."</td>
              <td align=\"right\">".gaz_format_number($totivafat)."</td>
              <td align=\"right\">".gaz_format_number($stamp)."</td>
              <td align=\"right\" style=\"font-weight:bold;\">".gaz_format_number($totimpfat+$totivafat+$stamp)."</td>\n";
        echo '<td colspan ="2" class="FacetFieldCaptionTD" align="center"><input name="ins" id="preventDuplicate" onClick="chkSubmit();" onClick="chkSubmit();" type="submit" value="'.strtoupper($script_transl[$toDo]).'!"></td></tr>';
        if ($rit > 0) {
            echo "<tr>";
            echo "<td colspan=\"7\" align=\"right\">".$script_transl['ritenuta']."</td>";
            echo "<td align=\"right\">".gaz_format_number($rit)."</td>";
            echo "</tr>\n";
            echo "<tr>";
            echo "<td colspan=\"7\" align=\"right\">".$script_transl['netpay']."</td>";
            echo "<td align=\"right\">".gaz_format_number($totimpfat+$totivafat+$stamp-$rit)."</td>";
            echo "</tr>\n";
        }
}
echo "</table>";
?>
</form>
</body>
</html>