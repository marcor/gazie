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

class ordermanForm extends GAzieForm {
	
	// Antonio Germani - Come select selectFromDB ma con in più preleva $key4 da $table2, dove $key3 è uguale a $key2, e lo visualizza nella scelta del select. Cioè nelle scelte del select ci sarà $key e $key4
	function selectFrom2DB($table,$table2,$key3,$key4, $name, $key, $val, $order = false, $empty = false, $bridge = '', $key2 = '', $val_hiddenReq = '', $class = 'FacetSelect', $addOption = null, $style = '', $where = false, $echo=false) {
        global $gTables;
		$acc='';
        $refresh = '';
		
        if (!$order) {
            $order = $key;
        }
		
        $query = 'SELECT * FROM `' . $gTables[$table] . '` ';
        if ($where) {
            $query .= ' WHERE ' . $where;
        }
        $query .= ' ORDER BY `' . $order . '`'; 
        if (!empty($val_hiddenReq)) {
            $refresh = "onchange=\"this.form.hidden_req.value='$val_hiddenReq'; this.form.submit();\"";
        }
        $acc .= "\t <select id=\"$name\" name=\"$name\" class=\"$class\" $refresh $style>\n";
        if ($empty) {
            $acc .= "\t\t <option value=\"\"></option>\n";
        }
		
        $result = gaz_dbi_query($query);
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if ($r[$key] == $val) {
                $selected = "selected";
            }
						
			$r2 = gaz_dbi_get_row($gTables[$table2], $key3, $r[$key2]);
			
            $acc .= "\t\t <option value=\"" . $r[$key] . "\" $selected >";
            if (empty($key2)) {
                $acc .= substr($r[$key], 0, 43) . "</option>\n";
            } else {
                $acc .= substr($r[$key], 0, 28) . $bridge . substr($r2[$key4], 0, 35) . "</option>\n";
            }
        }
        if ($addOption) {
            $acc .= "\t\t <option value=\"" . $addOption['value'] . "\"";
            if ($addOption['value'] == $val) {
                $acc .= " selected ";
            }
            $acc .= ">" . $addOption['descri'] . "</option>\n";
        }
        $acc .= "\t </select>\n";
		if ($echo){
			return $acc;
		} else {
			echo $acc;
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
	
	function getStockEvalMethod() {  // Prendo il metodo di valorizzazione del magazzino impostato in configurazione azienda
        global $gTables;
        $enterprise = gaz_dbi_get_row($gTables['aziend'], 'codice', $_SESSION['company_id']);
        return $enterprise['stock_eval_method'];
    }
	
}

class lotmag {

   function __construct() {
      $this->available = array();
   }

   function getLot($id) {
// restituisce i dati relativi ad uno specifico lotto
      global $gTables;
      $sqlquery = "SELECT * FROM " . $gTables['lotmag'] . "
            LEFT JOIN " . $gTables['movmag'] . " ON " . $gTables['lotmag'] . ".id_movmag =" . $gTables['movmag'] . ".id_mov  
            WHERE " . $gTables['lotmag'] . ".id = '" . $id . "'";
      $result = gaz_dbi_query($sqlquery);
      $this->lot = gaz_dbi_fetch_array($result);
      return $this->lot;
   }
   
   function getLotQty($id) {
// Antonio Germani - restituisce la quantità disponibile di uno specifico lotto
      global $gTables;
      $sqlquery = "SELECT operat, quanti FROM " . $gTables['movmag'] . " WHERE id_lotmag = '" . $id . "'";
      $result = gaz_dbi_query($sqlquery);
	  $lotqty=0;
      while ($row = gaz_dbi_fetch_array($result)) {
		  if ($row['operat']>0){$lotqty=$lotqty+$row['quanti'];}
		  if ($row['operat']<0){$lotqty=$lotqty-$row['quanti'];}
	  }
      return $lotqty;
   }

   function getAvailableLots($codart, $excluded_movmag = 0) {
// restituisce tutti i lotti non completamente venduti ordinandoli in base alla configurazione aziendale (FIFO o LIFO)
// e propone una ripartizione, se viene passato un movimento di magazzino questo verrà escluso perché si suppone sia lo stesso
// che si sta modificando
      global $gTables, $admin_aziend;
      $ob = ' ASC'; // FIFO-PWM-STANDARD
      if ($admin_aziend['stock_eval_method'] == 2) {
         $ob = ' DESC'; // LIFO
      }
      $sqlquery = "SELECT *, SUM(quanti*operat) AS rest FROM " . $gTables['movmag'] . " LEFT JOIN " . $gTables['lotmag'] . " ON " . $gTables['movmag'] . ".id_mov =" . $gTables['lotmag'] . ".id_movmag WHERE " . $gTables['movmag'] . ".artico = '" . $codart . "' AND id_mov <> " . $excluded_movmag . " GROUP BY " . $gTables['movmag'] . ".id_lotmag ORDER BY " . $gTables['movmag'] . ".datreg" . $ob;
      $result = gaz_dbi_query($sqlquery);
      $acc = array();
      $rs = false;
      while ($row = gaz_dbi_fetch_array($result)) {
         if ($row['rest'] >= 0.00001) { // l'articolo ha almeno un lotto caricato 
            $rs = true;
            $acc[] = $row;
         }
      }
      $this->available = $acc;
      return $rs;
   }

   function divideLots($quantity) {
// riparto la quantità tra i vari lotti presenti se questi non sono sufficienti
// ritorno il resto non assegnato 
      $acc = array();
      $rest = $quantity;
      foreach ($this->available as $v) {
         if ($v['rest'] >= $rest) { // c'è capienza
            $acc[$v['id_lotmag']] = $v + array('qua' => $rest);
         } elseif ($v['rest'] < $rest) { // non c'è capienza
            $acc[$v['id_lotmag']] = $v + array('qua' => $v['rest']);
         }
         $rest -= $v['rest'];
      }
      $this->divided = $acc;
      if ($rest >= 0.00001) {
// ritorno il resto, quindi non ho abbastanza lotti per contenere la quantità venduta 
         return $rest;
      } else {
         return NULL;
      }
   }
   
   function dispLotID ($codart, $lotMag, $excluded_movmag = 0) {
// Antonio Germani - restituisce la disponibilità per id lotto 
		global $gTables;
		$query="SELECT SUM(quanti*operat) FROM ". $gTables['movmag'] . " WHERE artico='" .$codart. "' AND id_lotmag='" .$lotMag. "' AND id_mov <> '". $excluded_movmag ."' AND caumag < '99' ";
		$sum_in=gaz_dbi_query($query);
		$sum =gaz_dbi_fetch_array($sum_in);
		$disp = $sum['SUM(quanti*operat)'];
		return $disp;
   }
}
?>