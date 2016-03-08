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

class magazzForm extends GAzieForm {

    function selItem($name, $val, $strSearch = '', $mesg, $val_hiddenReq = '', $class = 'FacetSelect') {
        global $gTables, $admin_aziend;
        if ($admin_aziend['artsea'] == 'B') {        //ricerca per codice a barre
            $field = 'barcode';
        } elseif ($admin_aziend['artsea'] == 'D') { //ricerca per descrizione
            $field = 'descri';
        } else {                   //ricerca per codice (default)
            $field = 'codice';
        }
        if (!empty($val)) { //vengo da una modifica della precedente select case quindi non serve la ricerca
            $item = gaz_dbi_get_row($gTables['artico'], 'codice', $val);
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"" . $item[$field] . "\">\n";
            echo "\t<input type=\"submit\" value=\"" . $item['codice'] . " - " . $item['descri'] . "\" name=\"change\" onclick=\"this.form.$name.value=''; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
        } else {
            if (strlen($strSearch) >= 1) { //sto ricercando un nuovo articolo
                $item = gaz_dbi_dyn_query("codice,descri,barcode", $gTables['artico'], $field . " LIKE '$strSearch%'", "codice ASC, descri DESC");
                echo "\t<select name=\"$name\" class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
                echo "<option value=\"0\"> ---------- </option>";
                if (gaz_dbi_num_rows($item) > 0) {
                    while ($r = gaz_dbi_fetch_array($item)) {
                        $selected = '';
                        if ($r['codice'] == $val) {
                            $selected = "selected";
                        }
                        echo "\t\t <option value=\"" . $r['codice'] . "\" $selected >" . $r['codice'] . " - " . $r["descri"] . "</option>\n";
                    }
                    echo "\t </select>\n";
                } else {
                    $msg = $mesg[0];
                }
            } else {
                $msg = $mesg[1];
                echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            }
            echo "\t<input type=\"text\" name=\"search[$name]\" value=\"" . $strSearch . "\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
            if (isset($msg)) {
                echo "<input type=\"text\" style=\"color: red; font-weight: bold;\" size=\"" . strlen($msg) . "\" disabled value=\"$msg\">";
            }
            //echo "\t<input type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
            /** ENRICO FEDELE */
            /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
            echo '<button type="submit" class="btn btn-default btn-sm" name="search_str"><i class="glyphicon glyphicon-search"></i></button>';
            /** ENRICO FEDELE */
        }
    }

    function selectCaumag($val, $operat = -1, $empty = false, $val_hiddenReq = '', $class = 'FacetSelect') {
        global $gTables;
        $refresh = '';
        if (!empty($val_hiddenReq)) {
            $refresh = "onchange=\"this.form.hidden_req.value='caumag'; this.form.submit();\"";
        }
        $query = "SELECT * FROM " . $gTables['caumag'] . " WHERE clifor = -1 AND operat = $operat";
        echo "\t <select name=\"caumag\" class=\"$class\" $refresh >\n";
        if ($empty) {
            echo "\t\t <option value=\"\">---------</option>\n";
        }
        $result = gaz_dbi_query($query);
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if ($r['codice'] == $val) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"" . $r['codice'] . "\" $selected >" . $r['descri'] . "</option>\n";
        }
        echo "\t </select>\n";
    }

    function getOperators() {  // Creo l'array associativo degli operatori dei documenti
        return array("VCO" => -1, "VRI" => -1, "DDT" => -1, "FAD" => -1, "FAI" => -1, "FAP" => -1, "FNC" => 1, "FND" => -1,
            "DDR" => -1, "DDL" => -1, "DDV" => -1, "RDV" => 1, "DDY" => -1, "AFA" => 1, "ADT" => 1, "AFC" => -1, "VPR" => -1, "VOR" => -1, "VOW" => -1);
    }

    function getStockEvalMethod() {  // Prendo il metodo di valorizzazione del magazzino impostato in configurazione azienda
        global $gTables;
        $enterprise = gaz_dbi_get_row($gTables['aziend'], 'codice', $_SESSION['company_id']);
        return $enterprise['stock_eval_method'];
    }

    function getLastCost($item_code, $decimal_price) {  // Prendo il costo dall'ultimo movimento d'aquisto
        global $gTables;
        $rs_last_cost = gaz_dbi_dyn_query("*", $gTables['movmag'], " artico = '$item_code' AND tipdoc LIKE 'A%' AND operat = 1 ", "datreg DESC, id_mov DESC", 0, 1);
        $last_cost = gaz_dbi_fetch_array($rs_last_cost);
        if ($last_cost) {
            return CalcolaImportoRigo(1, $last_cost['prezzo'], array($last_cost['scochi'], $last_cost['scorig']), $decimal_price);
        } else {
            return 0;
        }
    }

    function ctrlMovYearsAfter($year, $item_code) {  // Controllo che non ci siano movimenti negli anni successivi
        global $gTables;
        $rs_years_after = gaz_dbi_dyn_query("*", $gTables['movmag'], "YEAR(datreg) > $year AND artico = '$item_code' AND tipdoc LIKE 'A%' AND operat = 1 ", "datreg DESC, id_mov DESC", 0, 1);
        $years_after = gaz_dbi_fetch_array($rs_years_after);
        if ($years_after) {
            return false;   // non si pu� aggiornare il valore dell'esistente perch� ci sono movimenti su anni successivi
        } else {
            return true;
        }
    }

    function getStockValue($id_mov = false, $item_code = null, $date = null, $stock_eval_method = null, $decimal_price = 2)
    /* Questa funzione serve per restituire la valorizzazione dello scarico
      a seconda del metodo (WMA,LIFO,FIFO) scelto per ottenerla.
      Puo' essere sufficiente valorizzare il solo $id_mov, ma questo costringe
      la funzione ad una query per ottenere gli altri valori; oppure il solo
      codice dell'articolo, in questo caso si prende in considerazione l'ultimo
      movimento riferito all'articolo
     */ {
        global $gTables;
        if (!$id_mov && empty($item_code)) { // non ho nulla!
            return array('q' => 0, 'v' => 0, 'q_g' => 0, 'v_g' => 0);
        } elseif (!$id_mov && !empty($item_code)) {    // ho il codice articolo  senza id
            if ($date) { // ho anche la data
                $rs_last_mov = gaz_dbi_dyn_query("*", $gTables['movmag'], "artico = '" . $item_code . "' AND datreg <= '$date'", "datreg DESC, id_mov DESC", 0, 1);
            } else {   // non ho la data limite
                $rs_last_mov = gaz_dbi_dyn_query("*", $gTables['movmag'], "artico = '" . $item_code . "'", "datreg DESC, id_mov DESC", 0, 1);
            }
            $last_mov = gaz_dbi_fetch_array($rs_last_mov);
            if ($last_mov) {
                $id_mov = $last_mov['id_mov'];
                $date = $last_mov['datreg'];
            } else {
                return array('q' => 0, 'v' => 0, 'q_g' => 0, 'v_g' => 0);
            }
        } elseif (!$date || empty($item_code)) {    //ho il solo id_mov
            $mm = gaz_dbi_get_row($gTables['movmag'], "id_mov", $id_mov);
            $date = $mm['datreg'];
            $item_code = $mm['artico'];
        }
        if (!$stock_eval_method) {
            $stock_eval_method = $this->getStockEvalMethod();
        }
        $rs_last_inventory = gaz_dbi_dyn_query("*", $gTables['movmag'], "artico = '$item_code' AND caumag = 99 AND (datreg < '" . $date . "' OR (datreg = '" . $date . "' AND id_mov <= $id_mov ))", "datreg DESC, id_mov DESC", 0, 1);
        $last_inventory = gaz_dbi_fetch_array($rs_last_inventory);
        if ($last_inventory) {
            $last_invDate = $last_inventory['datreg'];
            $last_invPrice = $last_inventory['prezzo'];
            $last_invQuanti = $last_inventory['quanti'];
        } else {
            $last_invDate = '2000-01-01';
            $last_invPrice = 0;
            $last_invQuanti = 0;
        }
        $utsdatePrev = mktime(0, 0, 0, intval(substr($date, 5, 2)), intval(substr($date, 8, 2)) - 1, intval(substr($date, 0, 4)));
        $datePrev = date("Y-m-d", $utsdatePrev);
        $where = "artico = '$item_code' AND (datreg BETWEEN '$last_invDate' AND '$datePrev' OR (datreg = '$date' AND id_mov <= $id_mov))";
        $orderby = "datreg ASC, id_mov ASC"; //ordino in base alle date 
        $return_val = array();
        $accumulatore = array();
        switch ($stock_eval_method) { //calcolo il nuovo valore in base al metodo scelto in configurazione azienda
            case "0": //standard
            case "3": // FIFO
                $rs_movmag = gaz_dbi_dyn_query("*", $gTables['movmag'], "caumag < 98 AND " . $where, $orderby);
                // Qui metto i valori dell'ultimo inventario
                $accumulatore[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice);
                $giacenza = array('q_g' => $last_invQuanti, 'v_g' => $last_invPrice * $last_invQuanti);
                $return_val[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice,
                    'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                // Fine valorizzazione con ultimo inventario
                while ($r = gaz_dbi_fetch_array($rs_movmag)) {
                    // questo e' il prezzo che usero' solo per gli acquisti
                    $row_val = CalcolaImportoRigo(1, $r['prezzo'], array($r['scorig'], $r['scochi']), $decimal_price);
                    if ($r['operat'] == 1) { //carico
                        $accumulatore[] = array('q' => $r['quanti'], 'v' => $row_val);
                        $giacenza['q_g']+=$r['quanti'];
                        $giacenza['v_g']+=$r['quanti'] * $row_val;
                        if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                            $return_val[0] = array('q' => $r['quanti'], 'v' => $row_val,
                                'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                        }
                    } elseif ($r['operat'] == -1) { //scarico
                        $return_val = array(); //azzero l'accumulatore per il ritorno
                        foreach ($accumulatore as $k => $acc_val) {   //attraverso l'accumulatore
                            if ($acc_val['q'] > $r['quanti']) { // la quantita' nell'accumulatore e' sufficiente per coprire lo scarico
                                $accumulatore[$k]['q'] -= $r['quanti'];
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                $r['quanti'] = 0;
                                break;
                            } elseif ($acc_val['q'] == $r['quanti']) {  // la quantita' da scaricare e' la stessa nell'accumulatore
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                                $r['quanti'] = 0;
                                break;
                            } else {  // la quantita' da scaricare e' maggiore di quella nell'accumulatore
                                $r['quanti'] -= $acc_val['q'];
                                $giacenza['q_g']-=$acc_val['q'];
                                $giacenza['v_g']-=$acc_val['q'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                    $return_val[] = array('q' => $acc_val['q'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                            }
                        }
                        // esco dal loop ma potrebbe accadere che i carichi non erano sufficienti a coprire lo scarico
                        if ($r['quanti'] > 0) { // e' il movimento che voglio valorizzare: lo accumulo
                            $giacenza['q_g']-=$r['quanti'];
                            $giacenza['v_g']-=0;
                            if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                $return_val[] = array('q' => -$r['quanti'], 'v' => 0,
                                    'q_g' => $giacenza['q_g'], 'v_g' => 0);
                            }
                        }
                    }
                }
                break;
            case "1": // WMA
                $rs_movmag = gaz_dbi_dyn_query("*", $gTables['movmag'], $where . " AND caumag < 98", $orderby);
                $giacenza = array('q_g' => $last_invQuanti, 'v_g' => $last_invPrice * $last_invQuanti);
                $return_val[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice,
                    'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                while ($r = gaz_dbi_fetch_array($rs_movmag)) {
                    if ($r['operat'] == 1) { //carico
                        $row_val = CalcolaImportoRigo(1, $r['prezzo'], array($r['scorig'], $r['scochi']), $decimal_price);
                        $giacenza['q_g']+=$r['quanti'];
                        $giacenza['v_g']+=$r['quanti'] * $row_val;
                    } elseif ($r['operat'] == -1) { //scarico
                        if ($giacenza['q_g'] <= 0) { // se la quantità è già sotto zero forzo anche il valore a 0
                            $giacenza['v_g'] = 0;
                            $row_val = 0;
                        } else {
                            $row_val = $giacenza['v_g'] / $giacenza['q_g'];
                        }
                        if ($giacenza['q_g'] <= $r['quanti']) { // se la quantità è andata sotto zero forzo anche il valore a 0
                            $giacenza['v_g'] = 0;
                            $row_val = 0;
                        }
                        $giacenza['q_g']-=$r['quanti'];
                        $giacenza['v_g']-=$r['quanti'] * $row_val;
                    }
                    if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare
                        $return_val[0] = array('q' => $r['quanti'], 'v' => $row_val,
                            'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                    }
                }
                break;
            case "2": // LIFO
                $rs_movmag = gaz_dbi_dyn_query("*", $gTables['movmag'], $where . " AND caumag < 98", $orderby);
                // Qui metto i valori dell'ultimo inventario
                $accumulatore[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice);
                $giacenza = array('q_g' => $last_invQuanti, 'v_g' => $last_invPrice * $last_invQuanti);
                $return_val[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice,
                    'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                // Fine valorizzazione con ultimo inventario
                while ($r = gaz_dbi_fetch_array($rs_movmag)) {
                    // questo e' il prezzo che usero' solo per gli acquisti
                    $row_val = CalcolaImportoRigo(1, $r['prezzo'], array($r['scorig'], $r['scochi']));
                    if ($r['operat'] == 1) { //carico
                        $accumulatore[] = array('q' => $r['quanti'], 'v' => $row_val);
                        $giacenza['q_g']+=$r['quanti'];
                        $giacenza['v_g']+=$r['quanti'] * $row_val;
                        if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                            $return_val[0] = array('q' => $r['quanti'], 'v' => $row_val,
                                'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                        }
                    } elseif ($r['operat'] == -1) { //scarico
                        $return_val = array(); //azzero l'accumulatore per il ritorno
                        $accumulatore = array_reverse($accumulatore);
                        foreach ($accumulatore as $k => $acc_val) {   //attraverso l'accumulatore
                            if ($acc_val['q'] > $r['quanti']) { // la quantita' nell'accumulatore e' sufficiente per coprire lo scarico
                                $accumulatore[$k]['q'] -= $r['quanti'];
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                $r['quanti'] = 0;
                                break;
                            } elseif ($acc_val['q'] == $r['quanti']) {  // la quantita' da scaricare e' la stessa nell'accumulatore
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                                $r['quanti'] = 0;
                                break;
                            } else {  // la quantita' da scaricare e' maggiore di quella nell'accumulatore
                                $r['quanti'] -= $acc_val['q'];
                                $giacenza['q_g']-=$acc_val['q'];
                                $giacenza['v_g']-=$acc_val['q'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                    $return_val[] = array('q' => $acc_val['q'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                            }
                        }
                        $accumulatore = array_reverse($accumulatore);
                        // esco dal loop ma potrebbe accadere che i carichi non erano sufficienti a coprire lo scarico
                        if ($r['quanti'] > 0) { // e' il movimento che voglio valorizzare: lo accumulo
                            $giacenza['q_g']-=$r['quanti'];
                            $giacenza['v_g']-=0;
                            if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                $return_val[] = array('q' => -$r['quanti'], 'v' => 0,
                                    'q_g' => $giacenza['q_g'], 'v_g' => 0);
                            }
                        }
                    }
                }

                break;
            default:
        }
        return $return_val;
    }

    function uploadMag($id_rigo_doc = 0, $tipdoc, $numdoc, $seziva, $datdoc, $clfoco, $sconto_chiusura, $caumag, $codart, $quantita, $prezzo, $sconto_rigo, $id_movmag = 0, $stock_eval_method = null, $data_from_admin_mov = false, $protoc = '',$id_lotmag=0) {  // su id_rigo_doc 0 per inserire 1 o + per fare l'upload 'DEL' per eliminare il movimento
        // in $data_from_admin_mov  ci sono i dati in più provenienti da admin_movmag (desdoc,operat, id_rif)
        global $gTables, $admin_aziend;
        $docOperat = $this->getOperators();
        if ($tipdoc == 'FAD') {  // per il magazzino una fattura differita è come dire DDT
            $tipdoc = 'DDT';
        }
        if (substr($tipdoc, 0, 1) == 'A' or $tipdoc == 'DDR' or $tipdoc == 'DDL') { //documento di acquisto
            require("../../modules/acquis/lang." . $admin_aziend['lang'] . ".php");
            $desdoc = $strScript['admin_docacq.php'][0][$tipdoc];
        } elseif ($tipdoc == 'INV') {
            require("../../modules/magazz/lang." . $admin_aziend['lang'] . ".php");
            $desdoc = $strScript['admin_artico.php']['esiste'];
        } else {//documento di vendita
            require("../../modules/vendit/lang." . $admin_aziend['lang'] . ".php");
            $desdoc = $strScript['admin_docven.php']['doc_name'][$tipdoc];
        }
        if (substr($tipdoc, 0, 1) == 'D' || $tipdoc == 'VCO') {
            $desdoc .= " n." . $numdoc;
            if ($seziva != '')
                $desdoc .= "/" . $seziva;
        } else {
            $desdoc .= " n." . $numdoc;
            if ($seziva != '')
                $desdoc .= "/" . $seziva;
            $desdoc .= " prot." . $protoc;
            if ($seziva != '')
                $desdoc .= "/" . $seziva;
        }
        $new_caumag = gaz_dbi_get_row($gTables['caumag'], 'codice', $caumag);
        $operat = $new_caumag['operat'];
        if (!$data_from_admin_mov) {         // se viene da un documento
            $datreg = $datdoc;               // la data di registrazione coincide con quella del documento
            $operat = $docOperat[$tipdoc];    // e la descrizione la ricavo dal tipo documento
        } else {                            // se � stato passato l'array dei dati
            $datreg = $data_from_admin_mov['datreg']; // prendo la descrizione e l'operatore da questo
            $operat = $data_from_admin_mov['operat'];
            $desdoc = $data_from_admin_mov['desdoc'];
        }
        $row_movmag = array('caumag' => $caumag,
            'operat' => $operat,
            'datreg' => $datreg,
            'tipdoc' => $tipdoc,
            'desdoc' => $desdoc,
            'datdoc' => $datdoc,
            'clfoco' => $clfoco,
            'scochi' => $sconto_chiusura,
            'id_rif' => $id_rigo_doc,
            'artico' => $codart,
            'quanti' => $quantita,
            'prezzo' => $prezzo,
            'scorig' => $sconto_rigo,
            'id_lotmag'=>$id_lotmag);
        if ($id_movmag == 0) {                             // si deve inserire un nuovo movimento
            movmagInsert($row_movmag);
            $ultimo_id_mm = gaz_dbi_last_id(); //id del rigo movimento magazzino
            //gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $id_rigo_doc, 'id_mag', gaz_dbi_last_id());
            gaz_dbi_query("UPDATE " . $gTables['rigdoc'] . " SET id_mag = " . gaz_dbi_last_id() . " WHERE `id_rig` = $id_rigo_doc ");
            $id_movmag = $ultimo_id_mm;
        } elseif ($id_rigo_doc === 'DEL') {                 // si deve eliminare un movimento esistente
            $old_movmag = gaz_dbi_get_row($gTables['movmag'], 'id_mov', $id_movmag);
            $old_caumag = gaz_dbi_get_row($gTables['caumag'], 'codice', $old_movmag['caumag']);
            gaz_dbi_del_row($gTables['movmag'], 'id_mov', $id_movmag);
            $codart = $old_movmag['artico'];
        } else {   // si deve modificare un movimento esistente
            $old_movmag = gaz_dbi_get_row($gTables['movmag'], 'id_mov', $id_movmag);
            $old_caumag = gaz_dbi_get_row($gTables['caumag'], 'codice', $old_movmag['caumag']);
            $id = array('id_mov', $id_movmag);
            if (!isset($new_caumag['operat'])) {
                $new_caumag['operat'] = 0;
            }
            if (!isset($old_caumag['operat'])) {
                $old_caumag['operat'] = 0;
            }
            movmagUpdate($id, $row_movmag);
        }
        return $id_movmag;
    }

    /* sends a Javascript toast to the client */

    function toast($message, $id = 'alert-discount', $class = 'alert-warning') {
        /*
          echo "<script type='text/javascript'>toast('$message');</script>"; */
        if (!empty($message)) {
            echo '<div class="container">
					<div id="' . $id . '" class="row alert ' . $class . ' fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
							<span aria-hidden="true">&times;</span>
						</button>
						<span class="glyphicon glyphicon-alert" aria-hidden="true"></span>&nbsp;' . $message . '
					</div>
				  </div>';
        }
        return '';
    }

}

?>