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
$msg = '';

$anagrafica = new Anagrafica();
$gForm = new venditForm();
$magazz = new magazzForm();
$ecr=$gForm->getECR_userData($admin_aziend['Login']);
$operat=$magazz->getOperators();

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and  !isset($_GET['id_tes']))) {
    header("Location: ".$form['ritorno']);
    exit;
}

if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or (isset($_POST['Update']))) {   //se non e' il primo accesso
    //qui si deve fare un parsing di quanto arriva dal browser...
    $form['id_tes'] = intval($_POST['id_tes']);
    $form['hidden_req'] = $_POST['hidden_req'];
    $form['clfoco'] = substr($_POST['clfoco'],0,13);
    $form['fiscal_code'] = strtoupper(substr(trim($_POST['fiscal_code']),0,16));
    foreach($_POST['search'] as $k=>$v){
       $form['search'][$k]=$v;
    }
    $form['tipdoc'] = strtoupper(substr($_POST['tipdoc'],0,3));
    $form['numdoc'] = intval($_POST['numdoc']);
    $form['numfat'] = intval($_POST['numfat']);
    $form['id_cash'] = intval($_POST['id_cash']);
    $form['id_con'] = intval($_POST['id_con']);
    $form['seziva'] = intval($_POST['seziva']);
    $form['listin'] = intval($_POST['listin']);
    $form['datemi_Y'] = intval($_POST['datemi_Y']);
    $form['datemi_M'] = intval($_POST['datemi_M']);
    $form['datemi_D'] = intval($_POST['datemi_D']);
    $form['caumag'] = intval($_POST['caumag']);
    $form['sconto'] = floatval(substr(preg_replace("/\,/",'.',$_POST['sconto']),0,5));
    if ($form['sconto'] > 100 ){
        $form['sconto'] = 100;
    } elseif($form['sconto'] < -100 ) {
        $form['sconto'] = -100;
    }
    $form['address'] = $_POST['address'];
    $form['id_agente'] = intval($_POST['id_agente']);
    $form['pagame'] = intval($_POST['pagame']);

    // se non ho il cliente (nemmeno l'anonimo) azzero i dati
    if ($_POST['clfoco']<$admin_aziend['mascli']){
        $form['address'] = '';
        $form['id_agente'] = 0;
        $form['pagame'] = 1;
    };

    // inizio rigo di input
    $form['in_descri'] = $_POST['in_descri'];
    $form['in_tiprig'] = $_POST['in_tiprig'];
    $form['in_artsea'] = $_POST['in_artsea'];
    $form['in_codart'] = $_POST['in_codart'];
    $form['in_pervat'] = $_POST['in_pervat'];
    $form['in_unimis'] = $_POST['in_unimis'];
    $form['in_prezzo'] = $_POST['in_prezzo'];
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
    $form['cosear'] = $_POST['cosear'];
    // fine rigo input

    $form['rows'] = array();
    $next_row = 0;
    if (isset($_POST['rows'])) {
       foreach ($_POST['rows'] as $next_row => $v) {
            $form['rows'][$next_row]['tiprig'] = intval($v['tiprig']);
            $form['rows'][$next_row]['codart'] = substr($v['codart'],0,15);
            $form['rows'][$next_row]['status'] = substr($v['status'],0,30);
            $form['rows'][$next_row]['descri'] = substr($v['descri'],0,100);
            $form['rows'][$next_row]['unimis'] = substr($v['unimis'],0,3);
            $form['rows'][$next_row]['prelis'] = number_format(preg_replace("/\,/",'.',$v['prelis']),$admin_aziend['decimal_price'],'.','');
            $form['rows'][$next_row]['sconto'] = floatval(preg_replace("/\,/",'.',$v['sconto']));
            $form['rows'][$next_row]['quanti'] = gaz_format_quantity($v['quanti'],0,$admin_aziend['decimal_quantity']);
            $form['rows'][$next_row]['provvigione'] = intval($v['provvigione']);
            $form['rows'][$next_row]['codvat'] = intval($v['codvat']);
            $form['rows'][$next_row]['pervat'] = preg_replace("/\,/",'.',$v['pervat']);
            $form['rows'][$next_row]['codric'] = intval($v['codric']);
            $form['rows'][$next_row]['id_mag'] = intval($v['id_mag']);
            $form['rows'][$next_row]['annota'] = substr($v['annota'],0,50);
            $form['rows'][$next_row]['scorta'] = floatval($v['scorta']);
            $form['rows'][$next_row]['pesosp'] = floatval($v['pesosp']);
            if (isset($_POST['upd_row'])) {
               $key_row = key($_POST['upd_row']);
               if ($key_row == $next_row) {
                  $form['in_descri'] = $form['rows'][$key_row]['descri'];
                  $form['in_tiprig'] = $form['rows'][$key_row]['tiprig'];
                  $form['in_codart'] = $form['rows'][$key_row]['codart'];
                  $form['in_pervat'] = $form['rows'][$key_row]['pervat'];
                  $form['in_unimis'] = $form['rows'][$key_row]['unimis'];
                  $form['in_prezzo'] = $form['rows'][$key_row]['prelis'];
                  $form['in_sconto'] = $form['rows'][$key_row]['sconto'];
                  $form['in_quanti'] = $form['rows'][$key_row]['quanti'];
                  $form['in_codvat'] = $form['rows'][$key_row]['codvat'];
                  $form['in_codric'] = $form['rows'][$key_row]['codric'];
                  $form['in_provvigione'] = $form['rows'][$key_row]['provvigione'];
                  $form['in_id_mag'] = $form['rows'][$key_row]['id_mag'];
                  $form['in_annota'] = $form['rows'][$key_row]['annota'];
                  $form['in_scorta'] = $form['rows'][$key_row]['scorta'];
                  $form['in_pesosp'] = $form['rows'][$key_row]['pesosp'];
                  $form['in_status'] = "UPDROW".$key_row;
                  if ($form['in_artsea'] == 'D'){
                    $artico_u = gaz_dbi_get_row($gTables['artico'],'codice',$form['rows'][$key_row]['codart']);
                    $form['cosear'] = $artico_u['descri'];
                  } elseif ($form['in_artsea'] == 'B') {
                    $artico_u = gaz_dbi_get_row($gTables['artico'],'codice',$form['rows'][$key_row]['codart']);
                    $form['cosear'] = $artico_u['barcode'];
                  } else {
                    $form['cosear'] = $form['rows'][$key_row]['codart'];
                  }
                  array_splice($form['rows'],$key_row,1);
                  $next_row--;
               }
            }
            $next_row++;
       }
    }

    // Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
       $uts_datemi = mktime(0,0,0,$form['datemi_M'],$form['datemi_D'],$form['datemi_Y']);
       if (!checkdate($form['datemi_M'],$form['datemi_D'],$form['datemi_Y'])) {
          $msg .= "0+";
       }
       if ($form["clfoco"]<$admin_aziend['mascli']) { // non c'e' un cliente
          $msg .= "1+";
       } elseif($form["clfoco"]==$admin_aziend['mascli']) { //  e' un cliente anonimo
          // il pagamento dev'essere contestuale, non si fa credito agli anonimi!
          $payment = gaz_dbi_get_row($gTables['pagame'],'codice',$form["pagame"]);
          if ($payment['incaut'] != 'S'){
             $msg .= "6+";
          }
       }
       if (empty ($form["pagame"])) {
          $msg .= "2+";
       }
       //controllo dei righi e del totale
       $tot=0;
       $tim=0;
       foreach ($form['rows'] as $i=>$v) {
            if (empty($v['descri']) && $v['quanti']>0) {
                $msg .= "3+";
            }
            if (empty($v['unimis']) && $v['quanti']>0) {
                $msg .= "4+";
            }
            if ($v['tiprig'] <= 1) {    // se del tipo normale o forfait
               if ($v['tiprig'] == 0) { // tipo normale
                   $tim_row = CalcolaImportoRigo($v['quanti'], $v['prelis'],array($v['sconto'],$form['sconto']));
                   $tot_row = CalcolaImportoRigo($v['quanti'], $v['prelis'],array($v['sconto'],$form['sconto'],-$v['pervat']));
               } else {                 // tipo forfait
                   $tim_row = CalcolaImportoRigo($v['quanti'], $v['prelis'],0);
                   $tot_row = CalcolaImportoRigo(1,$v['prelis'],-$v['pervat']);
               }
               $tot+=$tot_row;
               $tim+=$tim_row;
            }
       }
       if ($tot==0) {  //il totale e' zero
          $msg .= "5+";
       } elseif($tim>=3000) { // se il totale supera i 3600 euro
          if($form["clfoco"]==$admin_aziend['mascli']) {
              $msg .= "9+";
          }
       }
       if (!empty($form['fiscal_code'])) {  // controllo codice fiscale
          require("../../library/include/check.inc.php");
          $ctrl_cf = new check_VATno_TAXcode();
          $rs_cf = $ctrl_cf->check_TAXcode($form['fiscal_code']);
          if (!empty($rs_cf)) {
              $msg .= "7+";
          }
       }
       if ($msg == "") { // nessun errore
          $form['datemi']=date("Ymd", $uts_datemi );
          if (preg_match("/^id_([0-9]+)$/",$form['clfoco'],$match)) {
             $new_clfoco = $anagrafica->getPartnerData($match[1],1);
             $form['clfoco']=$anagrafica->anagra_to_clfoco($new_clfoco,$admin_aziend['mascli']);
          }
          if ($toDo == 'update') { // e' una modifica
             $old_rows = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = ".$form['id_tes'],"id_tes, id_rig");
             $i=0;
             $count = count($form['rows'])-1;
             while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
                   if ($i <= $count) { //se il vecchio rigo e' ancora presente nel nuovo lo modifico
                      $form['rows'][$i]['id_tes'] = $form['id_tes'];
                      rigdocUpdate(array('id_rig',$val_old_row['id_rig']),$form['rows'][$i]);
                      if ($form['rows'][$i]['id_mag'] > 0 ){ //se il rigo ha un movimento di magazzino associato
                          $magazz->uploadMag($val_old_row['id_rig'],
                                    $form['tipdoc'],
                                    $form['numdoc'],
                                    '',
                                    $form['datemi'],
                                    $form['clfoco'],
                                    $form['sconto'],
                                    $form['caumag'],
                                    $form['rows'][$i]['codart'],
                                    $form['rows'][$i]['quanti'],
                                    $form['rows'][$i]['prelis'],
                                    $form['rows'][$i]['sconto'],
                                    $form['rows'][$i]['id_mag'],
                                    $admin_aziend['stock_eval_method']
                                    );
                      }
                   } else { //altrimenti lo elimino
                      if (intval($val_old_row['id_mag']) > 0){  //se c'è stato un movimento di magazzino lo azzero
                         $magazz->uploadMag('DEL',$form['tipdoc'],'','','','','','','','','','',$val_old_row['id_mag'],$admin_aziend['stock_eval_method']);
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
                   $magazz->uploadMag(gaz_dbi_last_id(),
                                    $form['tipdoc'],
                                    $form['numdoc'],
                                    '',
                                    $form['datemi'],
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
             $form['id_contract'] = $form['id_cash'];
             tesdocUpdate(array('id_tes',$form['id_tes']),$form);
             header("Location: ".$form['ritorno']);
             exit;
          } else { // e' un'inserimento
             $form['template'] = 'FatturaAllegata';
             $form['id_contract'] = $ecr['id_cash'];
             $form['seziva'] = $ecr['seziva'];
             $form['spediz'] = $form['fiscal_code'];
             // ricavo il progressivo della cassa del giorno (in id_contract c'è la cassa alla quale invio lo scontrino)
             $rs_last_n = gaz_dbi_dyn_query("numdoc", $gTables['tesdoc'], "tipdoc = 'VCO' AND id_con = 0 AND id_contract = ".$ecr['id_cash'],'datemi DESC, numdoc DESC',0,1);
             $last_n = gaz_dbi_fetch_array($rs_last_n);
             if ($last_n) {
                 $form['numdoc'] = $last_n['numdoc'] + 1;
             } else {
                 $form['numdoc'] = 1;
             }
             if ($form['clfoco']>100000000) {  // cliente selezionato quindi fattura allegata
                // ricavo l'ultimo numero di fattura dell'anno
                $rs_last_f = gaz_dbi_dyn_query("numfat*1 AS fattura", $gTables['tesdoc'], "YEAR(datfat) = ".$form['datemi_Y']." AND tipdoc = 'VCO' AND seziva = ".$ecr['seziva'],'fattura DESC',0,1);
                $last_f = gaz_dbi_fetch_array($rs_last_f);
                if ($last_f) {
                   $form['numfat'] = $last_f['fattura'] + 1;
                } else {
                   $form['numfat'] = 1;
                }
                $form['datfat'] = $form['datemi'];
            }
            tesdocInsert($form);
            $last_id = gaz_dbi_last_id();
            //inserisco i righi
            foreach ($form['rows'] as $v) {
                  $v['id_tes'] = $last_id;
                  rigdocInsert($v);
                  if ($admin_aziend['conmag'] == 2 and
                     $v['tiprig'] == 0 and
                     !empty($v['codart'])) { //se l'impostazione in azienda prevede l'aggiornamento automatico dei movimenti di magazzino
                     $magazz->uploadMag(gaz_dbi_last_id(),
                                    $form['tipdoc'],
                                    $form['numdoc'],
                                    '',
                                    $form['datemi'],
                                    $form['clfoco'],
                                    $form['sconto'],
                                    $form['caumag'],
                                    $v['codart'],
                                    $v['quanti'],
                                    $v['prelis'],
                                    $v['sconto'],
                                    0,
                                    $admin_aziend['stock_eval_method']
                                    );
                  }
            }
            // INIZIO l'invio dello scontrino alla stampante fiscale dell'utente
            require("../../library/cash_register/".$ecr['driver'].".php");
            $ticket_printer = new $ecr['driver'];
            $ticket_printer->set_serial($ecr['serial_port']);
            $ticket_printer->open_ticket();
            $ticket_printer->set_cashier($admin_aziend['Nome']);
            $tot=0;
            foreach ($form['rows'] as $i=>$v) {
                    if ($v['tiprig'] <= 1) {    // se del tipo normale o forfait
                       if ($v['tiprig'] == 0) { // tipo normale
                          $tot_row = CalcolaImportoRigo($v['quanti'], $v['prelis'],array($v['sconto'],$form['sconto'],-$v['pervat']));
                       } else {                 // tipo forfait
                          $tot_row = CalcolaImportoRigo(1,$v['prelis'],-$v['pervat']);
                          $v['quanti']=1;
                          $v['codart']=$v['descri'];
                       }
                       $price=$v['quanti'].'x'.round($tot_row/$v['quanti'],$admin_aziend['decimal_price']);
                       $ticket_printer->row_ticket($tot_row,$price,$v['codvat'],$v['codart']);
                       $tot+=$tot_row;
                   } else {                    // se descrittivo
                       $desc_arr=str_split(trim($v['descri']),24);
                       foreach ($desc_arr as $d_v) {
                                $ticket_printer->descri_ticket($d_v);
                       }
                   }
            }
            if (!empty($form['fiscal_code'])) { // è stata impostata la stampa del codice fiscale
               $ticket_printer->descri_ticket('CF= '.$form['fiscal_code']);
            }
            $ticket_printer->pay_ticket();
            $ticket_printer->close_ticket();
            // FINE invio
            if ($form['clfoco']>100000000) {
                // procedo alla stampa della fattura solo se c'è un cliente selezionato
                $_SESSION['print_request']=$last_id;
                header("Location: invsta_docven.php");
                exit;
            } else {
                header("Location: report_scontr.php");
                exit;
            }
          }
    }
  }

  // Se viene inviata la richiesta di conferma cliente
  if ($_POST['hidden_req']=='clfoco') {
    if (preg_match("/^id_([0-9]+)$/",$form['clfoco'],$match)) {
        $cliente = $anagrafica->getPartnerData($match[1],1);
    } else {
        $cliente = $anagrafica->getPartner($form['clfoco']);
    }
    $form['pagame']=$cliente['codpag'];
    $form['address']=$cliente['indspe'].' '.$cliente['citspe'];
    $form['id_agente']=$cliente['id_agente'];
    $form['in_codvat']=$cliente['aliiva'];
    $form['hidden_req']='';

  }

  // Se viene inviata la richiesta di conferma rigo
  if (isset($_POST['in_submit_x'])) {
    $artico = gaz_dbi_get_row($gTables['artico'],"codice",$form['in_codart']);
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
         $form['rows'][$old_key]['provvigione'] = $form['in_provvigione'];
         $form['rows'][$old_key]['prelis'] = number_format($form['in_prezzo'],$admin_aziend['decimal_price'],'.','');
         $form['rows'][$old_key]['sconto'] = $form['in_sconto'];
         $form['rows'][$old_key]['codvat'] = $form['in_codvat'];
         $pervat=gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
         $form['rows'][$old_key]['pervat'] = $pervat['aliquo'];
         $form['rows'][$old_key]['annota'] = '';
         $form['rows'][$old_key]['scorta'] = '';
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
            $magval=array_pop($magazz->getStockValue(false,$form['in_codart'],$form['datemi_Y'].'-'.$form['datemi_M'].'-'.$form['datemi_D'],$admin_aziend['stock_eval_method']));
            $form['rows'][$old_key]['scorta'] = $magval['q_g'] - $artico['scorta'];
         } elseif ($form['in_tiprig'] == 1) { //rigo forfait
            $form['rows'][$old_key]['codart'] = "";
            $form['rows'][$old_key]['unimis'] = "";
            $form['rows'][$old_key]['quanti'] = 0;
            $form['rows'][$old_key]['sconto'] = 0;
         } else { // rigo descrittivo
            $form['rows'][$old_key]['codart'] = "";
            $form['rows'][$old_key]['annota'] = "";
            $form['rows'][$old_key]['pesosp'] = "";
            $form['rows'][$old_key]['unimis'] = "";
            $form['rows'][$old_key]['quanti'] = 0;
            $form['rows'][$old_key]['prelis'] = 0;
            $form['rows'][$old_key]['codric'] = 0;
            $form['rows'][$old_key]['sconto'] = 0;
            $form['rows'][$old_key]['pervat'] = 0;
            $form['rows'][$old_key]['codvat'] = 0;
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
            $form['rows'][$next_row]['prelis'] = number_format($form['in_prezzo'],$admin_aziend['decimal_price'],'.','');
            $form['rows'][$next_row]['codric'] = $form['in_codric'];
            $form['rows'][$next_row]['quanti'] = $form['in_quanti'];
            $form['rows'][$next_row]['sconto'] = $form['in_sconto'];
            $provvigione = new Agenti;
            $form['rows'][$next_row]['provvigione'] = $provvigione->getPercent($form['id_agente'],$form['in_codart']);
            $form['rows'][$next_row]['codvat'] = $admin_aziend['alliva'];
            $iva_azi = gaz_dbi_get_row($gTables['aliiva'],"codice",$admin_aziend['alliva']);
            $form['rows'][$next_row]['pervat'] = $iva_azi['aliquo'];
            if ($artico['aliiva'] > 0) {
               $form['rows'][$next_row]['codvat'] = $artico['aliiva'];
               $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$artico['aliiva']);
               $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
            }
            if ($form['in_codvat'] > 0) {
               $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
               $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
               $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
            }
            if ($form['listin'] == 2) {
                $price=$artico['preve2'];
            } elseif ($form['listin'] == 3) {
                $price=$artico['preve3'];
            } else {
                $price=$artico['preve1'];
            }
            $form['rows'][$next_row]['prelis'] = number_format($price,$admin_aziend['decimal_price'],'.','');
            if ($artico['codcon'] > 0) {
               $form['rows'][$next_row]['codric'] = $artico['codcon'];
               $form['in_codric'] = $artico['codcon'];
            } elseif (!empty($artico['codice'])) {
               $form['rows'][$next_row]['codric'] = $admin_aziend['impven'];
               $form['in_codric'] = $admin_aziend['impven'];
            }
            $magval=array_pop($magazz->getStockValue(false,$form['in_codart'],$form['datemi_Y'].'-'.$form['datemi_M'].'-'.$form['datemi_D'],$admin_aziend['stock_eval_method']));
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
            if ($form['in_codvat'] > 0) {
               $form['rows'][$next_row]['codvat'] = $form['in_codvat'];
               $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_codvat']);
               $form['rows'][$next_row]['pervat'] = $iva_row['aliquo'];
            }
            $provvigione = new Agenti;
            $form['rows'][$next_row]['provvigione'] = $provvigione->getPercent($form['id_agente']);
         } elseif ($form['in_tiprig'] == 2) { //descrittivo
            $form['rows'][$next_row]['codart'] = "";
            $form['rows'][$next_row]['annota'] = "";
            $form['rows'][$next_row]['pesosp'] = "";
            $form['rows'][$next_row]['unimis'] = "";
            $form['rows'][$next_row]['quanti'] = 0;
            $form['rows'][$next_row]['prelis'] = 0;
            $form['rows'][$next_row]['codric'] = 0;
            $form['rows'][$next_row]['sconto'] = 0;
            $form['rows'][$next_row]['pervat'] = 0;
            $form['rows'][$next_row]['codvat'] = 0;
            $form['rows'][$next_row]['provvigione'] = 0;
         }
    }
    // reinizializzo rigo di input tranne che tipo rigo, aliquota iva e conto ricavo
    $form['in_descri'] = "";
    $form['in_codart'] = "";
    $form['in_unimis'] = "";
    $form['in_prezzo'] = 0;
    $form['in_sconto'] = 0;
    $form['in_quanti'] = 0;
    $form['in_id_mag'] = 0;
    $form['in_annota'] = "";
    $form['in_scorta'] = 0;
    $form['in_pesosp'] = 0;
    $form['in_status'] = "INSERT";
    $form['cosear'] = "";
    // fine reinizializzo rigo input
    $next_row++;
  }

  // Se viene inviata la richiesta di spostamento verso l'alto del rigo
  if (isset($_POST['upper_row'])) {
     $upp_key = key($_POST['upper_row']);
     $k_next = $upp_key-1;
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
    array_splice($form['rows'],$delri,1);
    $next_row--;
  }

} elseif ((!isset($_POST['Update'])) and (isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $tesdoc = gaz_dbi_get_row($gTables['tesdoc'],"id_tes",intval($_GET['id_tes']));
    $cliente = $anagrafica->getPartner($tesdoc['clfoco']);
    $form['hidden_req'] = '';
    $form['id_tes'] = $tesdoc['id_tes'];
    $form['tipdoc'] = $tesdoc['tipdoc'];
    $form['numdoc'] = $tesdoc['numdoc'];
    $form['id_cash'] = $tesdoc['id_contract'];
    $form['seziva'] = $tesdoc['seziva'];
    $form['id_con'] = $tesdoc['id_con'];
    $form['numfat'] = $tesdoc['numfat'];
    $form['clfoco'] = $tesdoc['clfoco'];
    // uso impropriamente la colonna spediz per mettere il codice fiscale inserito manualmente
    $form['fiscal_code'] = $tesdoc['spediz'];
    $form['search']['clfoco'] = substr($cliente['ragso1'],0,6);
    $form['id_agente'] = $tesdoc['id_agente'];
    $provvigione = new Agenti;
    $form['in_provvigione'] = $provvigione->getPercent($form['id_agente']);
    $form['listin'] = $tesdoc['listin'];
    $form['datemi_Y'] = substr($tesdoc['datemi'],0,4);
    $form['datemi_M'] = substr($tesdoc['datemi'],5,2);
    $form['datemi_D'] = substr($tesdoc['datemi'],8,2);
    $form['sconto'] = $tesdoc['sconto'];
    $form['address'] = $cliente['indspe'].' '.$cliente['citspe'];
    $form['pagame'] = $tesdoc['pagame'];
    $form['caumag'] = $tesdoc['caumag'];

    // inizio rigo di input
    $form['in_descri'] = "";
    $form['in_tiprig'] = 0;
    $form['in_artsea'] = $admin_aziend['artsea'];
    $form['in_codart'] = "";
    $form['in_pervat'] = 0;
    $form['in_unimis'] = "";
    $form['in_prezzo'] = 0;
    $form['in_sconto'] = 0;
    $form['in_quanti'] = 0;
    $form['in_codvat'] = $admin_aziend['alliva'];
    $form['in_codric'] = $admin_aziend['impven'];
    $form['in_id_mag'] = 0;
    $form['in_annota'] = "";
    $form['in_scorta'] = 0;
    $form['in_pesosp'] = 0;
    $form['in_status'] = "INSERT";
    $form['cosear'] = "";
    // fine rigo input
    // recupero i righi
    $rs_rows = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = ".intval($_GET['id_tes']),"id_rig");
    $next_row = 0;
    while ($r = gaz_dbi_fetch_array($rs_rows)) {
       $articolo = gaz_dbi_get_row($gTables['artico'],"codice",$r['codart']);
       $form['rows'][$next_row]['descri'] = $r['descri'];
       $form['rows'][$next_row]['tiprig'] = $r['tiprig'];
       $form['rows'][$next_row]['codart'] = $r['codart'];
       $form['rows'][$next_row]['pervat'] = $r['pervat'];
       $form['rows'][$next_row]['unimis'] = $r['unimis'];
       $form['rows'][$next_row]['prelis'] = number_format($r['prelis'],$admin_aziend['decimal_price'],'.','');
       $form['rows'][$next_row]['sconto'] = $r['sconto'];
       $form['rows'][$next_row]['quanti'] = gaz_format_quantity($r['quanti'],0,$admin_aziend['decimal_quantity']);
       $form['rows'][$next_row]['codvat'] = $r['codvat'];
       $form['rows'][$next_row]['codric'] = $r['codric'];
       $form['rows'][$next_row]['provvigione'] = $r['provvigione'];
       $form['rows'][$next_row]['id_mag'] = $r['id_mag'];
       $form['rows'][$next_row]['annota'] = $articolo['annota'];
       $magval=array_pop($magazz->getStockValue(false,$r['codart'],$form['datemi_Y'].'-'.$form['datemi_M'].'-'.$form['datemi_D'],$admin_aziend['stock_eval_method']));
       $form['rows'][$next_row]['scorta'] = $magval['q_g'] - $articolo['scorta'];
       $form['rows'][$next_row]['pesosp'] = $articolo['peso_specifico'];
       $form['rows'][$next_row]['status'] = "UPDATE";
       $next_row++;
    }
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    // se l'utente non ha alcun registratore di cassa associato nella tabella cash_register non può emettere scontrini
    $ecr_user = gaz_dbi_get_row($gTables['cash_register'],'adminid',$admin_aziend['Login']);
    if (!$ecr_user){
         header("Location: error_msg.php?ref=admin_scontr");
         exit;
    };
    $form['id_tes'] = 0;
    $form['tipdoc'] = 'VCO';
    $form['numdoc'] = 0;
    $form['numfat'] = 0;
    $form['id_cash'] = 1;
    $form['id_con'] = 0;
    $form['seziva'] = 1;
    $form['listin'] = 1;
    $form['datemi_Y'] = date("Y");
    $form['datemi_M'] = date("m");
    $form['datemi_D'] = date("d");
    $form['clfoco'] = $admin_aziend['mascli'];
    $form['fiscal_code'] = '';
    $form['search']['clfoco'] = '';
    $form['caumag'] = 0;
    $form['sconto'] = 0.00;
    $form['pagame'] = 0;
    $form['address'] = '';
    $form['caumag'] = 0;
    $form['id_agente'] = 0;
    $form['rows'] = array();
    $next_row = 0;
    $form['hidden_req'] = '';

    // inizio rigo di input
    $form['in_descri'] = "";
    $form['in_tiprig'] = 0;
    $form['in_artsea'] = $admin_aziend['artsea'];
    $form['in_codart'] = "";
    $form['in_pervat'] = 0;
    $form['in_unimis'] = "";
    $form['in_prezzo'] = 0;
    $form['in_sconto'] = 0;
    $form['in_provvigione'] = 0;
    $form['in_quanti'] = 0;
    $form['in_codvat'] = $admin_aziend['alliva'];
    $form['in_codric'] = $admin_aziend['impven'];
    $form['in_id_mag'] = 0;
    $form['in_annota'] = "";
    $form['in_scorta'] = 0;
    $form['in_pesosp'] = 0;
    $form['in_status'] = "INSERT";
    $form['cosear'] = "";
    // fine rigo input

    // ALLERTO SE NON E' STATA ESEGUITA LA CHIUSURA/CONTABILIZZAZIONE DEL GIORNO PRECEDENTE
    $rs_no_accounted = gaz_dbi_dyn_query("datemi", $gTables['tesdoc'], "id_con = 0 AND tipdoc = 'VCO' AND datemi < ".date("Ymd")." AND tipdoc = 'VCO'",'id_tes',0,1);
    $no_accounted = gaz_dbi_fetch_array($rs_no_accounted);
    if ($no_accounted) {
             $msg .= "8+";
    }
    // FINE ALLERTAMENTO
}

require("../../library/include/header.php");
$script_transl = HeadMain(0,array('boxover/boxover','calendarpopup/CalendarPopup',
                                  'jquery/jquery-1.4.2.min',
                                  'jquery/ui/jquery.ui.core',
                                  'jquery/ui/jquery.ui.widget',
                                  'jquery/ui/jquery.ui.position',
                                  'jquery/ui/jquery.ui.autocomplete',
                                  'jquery/autocomplete_anagra'));
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
echo "<form method=\"POST\" name=\"tesdoc\">\n";
echo "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">\n";
echo "<input type=\"hidden\" value=\"".$form['id_tes']."\" name=\"id_tes\">\n";
if ($form['id_tes'] > 0) { // è una modifica
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['upd_this']."<input type=\"text\" name=\"numdoc\" value=\"".$form['numdoc']."\" style=\"text-align:right\" maxlength=\"9\" size=\"3\"  onchange=\"this.form.submit()\" /></div>\n";
} else {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$admin_aziend['Nome'].', '.$script_transl['ins_this'].'<font class="FacetDataTD">'.$ecr['descri']."</font></div>\n";
   echo "<input type=\"hidden\" value=\"\" name=\"numdoc\">\n";
}
echo "<input type=\"hidden\" value=\"".$form['tipdoc']."\" name=\"tipdoc\">\n";
echo "<input type=\"hidden\" value=\"".$form['numfat']."\" name=\"numfat\">\n";
echo "<input type=\"hidden\" value=\"".$form['id_cash']."\" name=\"id_cash\">\n";
echo "<input type=\"hidden\" value=\"".$form['seziva']."\" name=\"seziva\">\n";
echo "<input type=\"hidden\" value=\"".$form['id_con']."\" name=\"id_con\">\n";
echo "<input type=\"hidden\" value=\"".$form['fiscal_code']."\" name=\"fiscal_code\">\n";
echo "<input type=\"hidden\" value=\"".$form['address']."\" name=\"address\">\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\">\n";
echo "<table class=\"Tlarge\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="6" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
}
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['seziva']."</td><td class=\"FacetDataTD\">\n";
echo $ecr['seziva'];
echo "\t </td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['customer'].": </td><td class=\"FacetDataTD\">\n";
$select_cliente = new selectPartner('clfoco');
$select_cliente->selectDocPartner('clfoco',$form['clfoco'],$form['search']['clfoco'],'clfoco',$script_transl['search_customer'],$admin_aziend['mascli'],$admin_aziend['mascli']);

echo "</td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['datemi']."</td><td class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('datemi',$form['datemi_D'],$form['datemi_M'],$form['datemi_Y']);
echo "\t</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['listin']."</td><td class=\"FacetDataTD\">\n";
$gForm->selectNumber('listin',$form['listin'],0,1,3);
echo "\t </td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['address']."</td><td  class=\"FacetDataTD\">\n";
echo $form['address'];
echo "\t </td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['pagame']."</td><td  class=\"FacetDataTD\">\n";
$gForm->ticketPayments('pagame',$form['pagame']);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['sconto']."</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"sconto\" value=\"".$form['sconto']."\" style=\"text-align:right\" maxlength=\"9\" size=\"3\"  onchange=\"this.form.submit()\"/></td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['id_agente']."</td><td  class=\"FacetDataTD\">\n";
$select_agente = new selectAgente("id_agente");
$select_agente->addSelected($form["id_agente"]);
$select_agente->output();
echo "</td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['caumag']."</td><td  class=\"FacetDataTD\">\n";
$magazz->selectCaumag($form['caumag'],$operat[$form['tipdoc']]);
echo "\t </td>\n";
echo "</tr>\n";
echo "</table>\n";
echo '<div class="FacetSeparatorTD" align="center">'.$script_transl['in_rows_title']."</div>\n";
echo "<table class=\"Tlarge\">\n";
echo "<input type=\"hidden\" value=\"".$form['in_descri']."\" name=\"in_descri\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_pervat']."\" name=\"in_pervat\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_unimis']."\" name=\"in_unimis\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_prezzo']."\" name=\"in_prezzo\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_id_mag']."\" name=\"in_id_mag\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_annota']."\" name=\"in_annota\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_scorta']."\" name=\"in_scorta\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_pesosp']."\" name=\"in_pesosp\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_status']."\" name=\"in_status\" />\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<tr>\n";
echo "<tr class=\"FacetColumnTD\">\n";
echo "<td>".$script_transl['item'].": \n";
$select_artico = new selectartico("in_codart");
$select_artico->addSelected($form['in_codart']);
$select_artico->output(substr($form['cosear'],0,20),$form['in_artsea']);
echo $script_transl['search']."\n";
$gForm->variousSelect('in_artsea',$script_transl['in_artsea_value'],$form['in_artsea'],'FacetDataTDsmall',false);
echo "\t </td>\n";
echo "<td>\n";
echo $script_transl['quanti'].": \n";
echo "<input type=\"text\" value=\"".$form['in_quanti']."\" maxlength=\"11\" size=\"7\" name=\"in_quanti\" tabindex=\"25\">\n";
echo "\t </td>\n";
echo "<td>\n";
echo "<input type=\"image\" name=\"in_submit\" src=\"../../library/images/vbut.gif\" title=\"".$script_transl['submit'].$script_transl['thisrow']."!\" tabindex=\"26\">\n";
echo "\t </td>\n";
echo "\t </tr>\n";
echo "\t<tr class=\"FacetColumnTD\">\n";
echo "\t<td colspan=\"3\">".$script_transl['tiprig'].": \n";
$gForm->variousSelect('in_tiprig',$script_transl['tiprig_value'],$form['in_tiprig']);
echo $script_transl['codric'].": \n";
$select_codric = new selectconven("in_codric");
$select_codric->addSelected($form['in_codric']);
$select_codric->output(substr($form['in_codric'],0,1));
echo "% ".$script_transl['sconto'].": \n";
echo "<input type=\"text\" value=\"".$form['in_sconto']."\" maxlength=\"4\" size=\"1\" name=\"in_sconto\">\n";
echo $script_transl['provvigione']."\n";
echo "<input type=\"text\" value=\"".$form['in_provvigione']."\" maxlength=\"6\" size=\"1\" name=\"in_provvigione\">\n";
echo $script_transl['codvat']."\n";
$gForm->selectFromDB('aliiva','in_codvat','codice',$form['in_codvat'],'codice',true,'-','descri');
echo "\t </td>\n";
echo "\t </tr>\n";
echo "</table>\n";
if ($next_row>0) {
    echo '<div class="FacetSeparatorTD" align="center">'.$script_transl['body_title']."</div>\n";
    echo "<table class=\"Tlarge\">\n";
    echo "\t<tr class=\"FacetColumnTD\" align=\"center\">\n";
    echo "\t<td></td>\n";
    echo "\t<td>".$script_transl['codart']."</td>\n";
    echo "\t<td>".$script_transl['descri']."</td>\n";
    echo "\t<td>".$script_transl['unimis']."</td>\n";
    echo "\t<td>".$script_transl['quanti']."</td>\n";
    echo "\t<td>".$script_transl['prezzo']."</td>\n";
    echo "\t<td>".$script_transl['sconto']."</td>\n";
    echo "\t<td>".$script_transl['provvigione']."</td>\n";
    echo "\t<td>".$script_transl['amount']."</td>\n";
    echo "\t<td>".$script_transl['codvat']."</td>\n";
    echo "\t<td>".$script_transl['codric']."</td>\n";
    echo "\t<td>".$script_transl['total']."</td>\n";
    echo "</tr>\n";
    $tot=0;
    $form['net_weight']=0;
    $form['units']=0;
    $form['volume']=0;
    foreach ($form['rows'] as $k=>$v) {
            $descrizione=$v['descri'];
            // addizione ai totali peso,pezzi,volume
            $artico = gaz_dbi_get_row($gTables['artico'],'codice',$v['codart']);
            $form['net_weight'] += $v['quanti']*$artico['peso_specifico'];
            if ($artico['pack_units']>0) {
               $form['units'] += intval(round($v['quanti']/$artico['pack_units']));
            }
            $form['volume'] += $v['quanti']*$artico['volume_specifico'];
            // fine addizione peso,pezzi,volume
            // calcolo importo totale (iva inclusa) del rigo e creazione castelletto IVA
            if ($v['tiprig'] <= 1) {    //ma solo se del tipo normale o forfait
               if ($v['tiprig'] == 0) { // tipo normale
                   $tot_row = CalcolaImportoRigo($v['quanti'], $v['prelis'],array($v['sconto'],$form['sconto'],-$v['pervat']));
               } else {                 // tipo forfait
                   $tot_row = CalcolaImportoRigo(1,$v['prelis'],-$v['pervat']);
               }
               if (!isset($castel[$v['codvat']])) {
                  $castel[$v['codvat']]=0.00;
               }
               $castel[$v['codvat']]+=$tot_row;
               // calcolo il totale del rigo stornato dell'iva
               $imprig=round($tot_row/(1+$v['pervat']/100),2);
               $tot+=$tot_row;
            }
            // fine calcolo importo rigo, totale e castelletto IVA
            $nr=$k+1;
            echo "<input type=\"hidden\" value=\"".$v['status']."\" name=\"rows[$k][status]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['codart']."\" name=\"rows[$k][codart]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['tiprig']."\" name=\"rows[$k][tiprig]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['codvat']."\" name=\"rows[$k][codvat]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['pervat']."\" name=\"rows[$k][pervat]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['codric']."\" name=\"rows[$k][codric]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['id_mag']."\" name=\"rows[$k][id_mag]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['annota']."\" name=\"rows[$k][annota]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['scorta']."\" name=\"rows[$k][scorta]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['provvigione']."\" name=\"rows[$k][provvigione]\">\n";
            echo "<input type=\"hidden\" value=\"".$v['pesosp']."\" name=\"rows[$k][pesosp]\">\n";
            echo "<tr class=\"FacetFieldCaptionTD\">\n";
            echo "<td style=\"text-align:right\"><input type=\"image\" name=\"upper_row[$k]\" src=\"../../library/images/upp.png\" title=\"".$script_transl['upper_row']."\" /> $nr</td>\n";
            //stampo i righi in modo diverso a secondo del tipo
            switch ($v['tiprig']) {
                   case "0":
                   $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$v['annota']."] body=[<center><img src='../root/view.php?table=artico&value=".$v['codart']."'>] fade=[on] fadespeed=[0.03] \"";
                   if ($v['pesosp'] != 0) {
                      $boxpeso = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[peso = ".gaz_format_number($v['quanti'] * $v['pesosp'])."]  fade=[on] fadespeed=[0.03] \"";
                   } else {
                      $boxpeso = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[]  fade=[on] fadespeed=[0.03] \"";
                   }
                   if ($v['scorta'] < 0) {
                      $scorta_col = 'FacetDataTDsmallRed';
                   } else {
                      $scorta_col = 'FacetDataTDsmall';
                   }
                   echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."! Sottoscorta =".$v['scorta']."\"><input class=\"$scorta_col\" type=\"submit\" name=\"upd_row[$k]\" value=\"".$v['codart']."\" /></td>\n";
                   echo "<td $boxover><input type=\"text\" name=\"rows[$k][descri]\" value=\"".$descrizione."\" maxlength=\"100\" size=\"50\" /></td>\n";
                   echo "<td $boxpeso><input type=\"text\" name=\"rows[$k][unimis]\" value=\"".$v['unimis']."\" maxlength=\"3\" size=\"2\" /></td>\n";
                   echo "<td $boxpeso><input type=\"text\" style=\"text-align:right\" name=\"rows[$k][quanti]\" value=\"".$v['quanti']."\" maxlength=\"11\" size=\"7\" onchange=\"this.form.submit()\" /></td>\n";
                   echo "<td><input type=\"text\" style=\"text-align:right\" name=\"rows[$k][prelis]\" value=\"".$v['prelis']."\" maxlength=\"15\" size=\"7\" onchange=\"this.form.submit()\" /></td>\n";
                   echo "<td><input type=\"text\" style=\"text-align:right\" name=\"rows[$k][sconto]\" value=\"".$v['sconto']."\" maxlength=\"4\" size=\"3\" onchange=\"this.form.submit()\" /></td>\n";
                   echo "<td class=\"FacetDataTDsmall\" style=\"text-align:center\">".$v['provvigione']."</td>\n";
                   echo "<td style=\"text-align:right\">".gaz_format_number($imprig)." </td>\n";
                   echo "<td style=\"text-align:right\" class=\"FacetDataTDsmall\">".$v['pervat']."%</td>\n";
                   echo "<td class=\"FacetDataTDsmall\">".$v['codric']."</td>\n";
                   echo "<td align=\"right\">".gaz_format_number($tot_row)."</td>\n";
                   break;
                   case "1":
                   echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."!\"><input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"* forfait *\" /></td>\n";
                   echo "<td><input type=\"text\"   name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"100\" size=\"50\" /></td>\n";
                   echo "<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>\n";
                   echo "<td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>\n";
                   echo "<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>\n";
                   echo "<td></td>\n";
                   echo "<td class=\"FacetDataTDsmall\" style=\"text-align:center\">".$v['provvigione']."</td>\n";
                   echo "<td align=\"right\"><input style=\"text-align:right\" type=\"text\" name=\"rows[$k][prelis]\" value=\"".gaz_format_number($v['prelis'])."\" maxlength=\"11\" size=\"7\" onchange=\"this.form.submit()\" /></td>\n";
                   echo "<td style=\"text-align:right\" class=\"FacetDataTDsmall\">".$v['pervat']."%</td>\n";
                   echo "<td class=\"FacetDataTDsmall\">".$v['codric']."</td>\n";
                   echo "<td align=\"right\">".gaz_format_number($tot_row)."</td>\n";
                   break;
                   case "2":
                   echo "<td title=\"".$script_transl['update'].$script_transl['thisrow']."!\"><input class=\"FacetDataTDsmall\" type=\"submit\" name=\"upd_row[$k]\" value=\"* descrittivo *\" /></td>\n";
                   echo "<td><input type=\"text\"   name=\"rows[$k][descri]\" value=\"$descrizione\" maxlength=\"50\" size=\"50\" /></td>\n";
                   echo "<td><input type=\"hidden\" name=\"rows[$k][unimis]\" value=\"\" /></td>\n";
                   echo "<td><input type=\"hidden\" name=\"rows[$k][quanti]\" value=\"\" /></td>\n";
                   echo "<td><input type=\"hidden\" name=\"rows[$k][prelis]\" value=\"\" /></td>\n";
                   echo "<td><input type=\"hidden\" name=\"rows[$k][sconto]\" value=\"\" /></td>\n";
                   echo "<td class=\"FacetDataTDsmall\"></td>\n";
                   echo "<td></td>\n";
                   echo "<td class=\"FacetDataTDsmall\"></td>\n";
                   echo "<td class=\"FacetDataTDsmall\"></td>\n";
                   echo "<td></td>\n";
                   break;
            }
            echo "<td align=\"right\"><input type=\"image\" name=\"del[$k]\" src=\"../../library/images/xbut.gif\" title=\"".$script_transl['delete'].$script_transl['thisrow']."!\" /></td></tr>\n";
            echo "\t </tr>\n";
    }
    echo "</table>\n";
    echo '<div class="FacetSeparatorTD" align="center">'.$script_transl['foot_title']."</div>\n";
    echo "<table class=\"Tlarge\">\n";
    echo "\t<tr class=\"FacetColumnTD\" align=\"center\">\n";
    echo "\t<td>".$script_transl['taxable']."</td>\n";
    echo "\t<td colspan=\"2\">".$script_transl['tax']."</td>\n";
    echo "\t<td>".$script_transl['net']."</td>\n";
    echo "\t<td>".$script_transl['units']."</td>\n";
    echo "\t<td>".$script_transl['volume']."</td>\n";
    echo "\t<td>".$script_transl['total']."</td>\n";
    echo "\t<td></td>\n";
    echo "</tr>\n";
    $last_castle_row=count($castel);
    echo "\t<tr align=\"center\">\n";
    foreach ($castel as $k=>$v) {
      $last_castle_row--;
      $r=gaz_dbi_get_row($gTables['aliiva'],"codice",$k);
      $impcast=round($v/(1+$r['aliquo']/ 100),2);
      $ivacast=$v-$impcast;
      if ($last_castle_row==0) {
         echo "<tr align=\"center\"><td align=\"right\">".gaz_format_number($impcast)."</td>
                                    <td>".$r['descri']."</td>
                                    <td align=\"right\">".gaz_format_number($ivacast)."</td>
                                    <td>".gaz_format_number($form['net_weight'])."</td>
                                    <td>".$form['units']."</td>
                                    <td>".gaz_format_number($form['volume'])."</td>
                                    <td style=\"font-weight:bold;\">".gaz_format_number($tot)."</td>
                                    <td align=\"right\"><input onClick=\"chkSubmit();\" id=\"preventDuplicate\" onClick=\"chkSubmit();\" type=\"submit\" name=\"ins\" value=\"".$script_transl['submit']."\" /></td></tr>\n";
      } else {
         echo "<tr align=\"right\"><td>".gaz_format_number($impcast)."</td>
                                   <td align=\"center\">".$r['descri']."</td>
                                   <td>".gaz_format_number($ivacast)."</td>
                                   <td colspan=\"5\"></td></tr>\n";
      }
    }
    echo "</table>\n";
}
?>
</form>
</body>
</html>