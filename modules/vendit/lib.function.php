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

class venditForm extends GAzieForm {

   function ticketPayments($name, $val, $class = 'FacetSelect') {
      global $gTables;
      $query = 'SELECT codice,descri,tippag FROM `' . $gTables['pagame'] . "` WHERE tippag = 'D' OR tippag = 'C' OR tippag = 'K' ORDER BY tippag";
      echo "\t <select name=\"$name\" class=\"$class\">\n";
      $result = gaz_dbi_query($query);
      while ($r = gaz_dbi_fetch_array($result)) {
         $selected = '';
         if ($r['codice'] == $val) {
            $selected = "selected";
         }
         echo "\t\t<option value=\"" . $r['codice'] . "\" $selected >" . $r['descri'] . "</option>\n";
      }
      print "\t </select>\n";
   }

   function getECR_userData($login) {
      global $gTables;
      return gaz_dbi_get_row($gTables['cash_register'], 'adminid', $login);
   }

   function getECRdata($id) {
      global $gTables;
      return gaz_dbi_get_row($gTables['cash_register'], 'id_cash', $id);
   }

   function selectCustomer($name, $val, $strSearch = '', $val_hiddenReq = '', $mesg, $class = 'FacetSelect') {
      global $gTables, $admin_aziend;
      $anagrafica = new Anagrafica();
      if ($val > 100000000) { //vengo da una modifica della precedente select case quindi non serve la ricerca
         $partner = $anagrafica->getPartner($val);
         echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
         echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"" . substr($partner['ragso1'], 0, 8) . "\">\n";
         echo "\t<input type=\"submit\" value=\"" . $partner['ragso1'] . " " . $partner["ragso2"] . " " . $partner["citspe"] . " (" . $partner["codice"] . ")\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
      } else {
         if (strlen($strSearch) >= 2) { //sto ricercando un nuovo partner
            echo "\t<select tabindex=\"1\" name=\"$name\" class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
            echo "<option value=\"0\"> ---------- </option>";
            $partner = $anagrafica->queryPartners("*", "codice LIKE '" . $admin_aziend['mascli'] . "%' AND codice >" . intval($admin_aziend['mascli'] . '000000') . "  AND ragso1 LIKE '" . addslashes($strSearch) . "%'", "codice ASC");
            if (count($partner) > 0) {
               foreach ($partner as $r) {
                  $selected = '';
                  if ($r['codice'] == $val) {
                     $selected = "selected";
                  }
                  echo "\t\t <option value=\"" . $r['codice'] . "\" $selected >" . $r['ragso1'] . " " . $r["ragso2"] . " " . $r["citspe"] . "</option>\n";
               }
               echo "\t </select>\n";
            } else {
               $msg = $mesg[0];
            }
         } else {
            $msg = $mesg[1];
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
         }
         echo "\t<input tabindex=\"2\" type=\"text\" id=\"search_$name\" name=\"search[$name]\" value=\"" . $strSearch . "\" maxlength=\"15\"  class=\"FacetInput\">\n";
         if (isset($msg)) {
            echo "<input type=\"text\" style=\"color: red; font-weight: bold;\"  disabled value=\"$msg\">";
         }
//echo "\t<input tabindex=\"3\" type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
         /** ENRICO FEDELE */
         /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
         echo '<button type="submit" class="btn btn-default btn-sm" name="search_str" tabindex="3"><i class="glyphicon glyphicon-search"></i></button>';
         /** ENRICO FEDELE */
      }
   }
   function selectAsset($name, $val, $class = 'FacetSelect') {
        global $gTables, $admin_aziend;
        echo "<select id=\"$name\" name=\"$name\" class=\"$class\">\n";
        echo "\t<option value=\"0\"> ---------- </option>\n";
        $result = gaz_dbi_dyn_query("acc_fixed_assets, descri", $gTables['assets'], "type_mov = 1");
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            $v = $r["acc_fixed_assets"];
            if ($val == $v) {
                $selected .= " selected ";
            }
            echo "\t<option value=\"" . $v . "\"" . $selected . ">" . $r["acc_fixed_assets"] . "-" . $r['descri'] . "</option>\n";
        }
        echo "</select>\n";
   }

   function selRifDettaglioLinea($name, $val, $RiferimentoNumeroLinea, $class = '') {
        global $gTables, $admin_aziend;
        echo '<select id="'.$name.'" name="'.$name.'" class="'.$class.'">';
        echo '<option value="">Tutto il documento</option>';
		foreach ($RiferimentoNumeroLinea as $k=>$v) {
			$selected = '';
			if ($k == $val) $selected = ' selected';
			echo '<option value="'.$k.'" '.$selected.' >Linea n.'.$k.' '.$v.'</option>';
		}
        echo "</select>\n";
   }
   
   function concileArtico($name,$key,$val,$class='small') {
      global $gTables;
	  $acc='';
	  $query = 'SELECT * FROM `' . $gTables['artico'] . '`  ORDER BY `catmer`,`codice`';
      $acc .= '<select id="'.$name.'" name="'.$name.'" class="'.$class.'">';
      $acc .= '<option value="" style="background-color:#5bc0de;">NON IN MAGAZZINO</option>';
      $acc .= '<option value="Insert_New" style="background-color:#f0ad4e;">INSERISCI COME NUOVO</option>';
      $result = gaz_dbi_query($query);
      while ($r = gaz_dbi_fetch_array($result)) {
          $selected = '';
          $setstyle = '';
          if ($r[$key] == $val) {
              $selected = " selected ";
              $setstyle = ' style="background-color:#5cb85c;" ';
          }
          $acc .= '<option class="small" value="'.$r[$key].'"'.$selected.''.$setstyle.'>'.$r['codice'].'-'.substr($r['descri'],0,30).'</option>';
      }
      $acc .= '</select>';
		return $acc;
   }

   function selectRegistratoreTelematico($val,$user_name) { // funzione per selezionare tra i registratori telematici abiliti per l'utente
        global $gTables, $admin_aziend;
        echo '<select id="id_cash" name="id_cash">';
        echo '<option value="0">File XML (no RT)</option>';
        $result = gaz_dbi_dyn_query("id_cash, descri", $gTables['cash_register'], "enabled_users LIKE '%".$user_name."%'");
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if ($val == $r["id_cash"]) {
                $selected .= " selected ";
            }
            echo '<option value="' . $r["id_cash"] . '"' . $selected . '>' . $r['descri'] . "</option>\n";
        }
        echo "</select>\n";
   }

   function chkRegistratoreTelematico($user_name) { // controllo se l'utente è abilitato ad almeno un RT e restituisco il valore altrimenti false
        global $gTables;
        // trovo il registratore che è stato usato per ultimo dall'utente abilitato
        $rs_last = gaz_dbi_dyn_query("*", $gTables['cash_register']." LEFT JOIN ".$gTables['tesdoc']." ON ".$gTables['cash_register'].".id_cash = ".$gTables['tesdoc'].".id_contract", "tipdoc ='VCO' AND id_contract > 0 AND enabled_users LIKE '%".$user_name."%'", $gTables['tesdoc'].'.datemi DESC,'.$gTables['tesdoc'].'.numdoc DESC', 0, 1);
        $exist = gaz_dbi_fetch_array($rs_last);
        return ($exist)?$exist['id_cash']:false;
   }

   function selectRepartoIVA($val,$id_cash) { // per selezionare l'aliquota IVA, tutte se viene prodotto un XML (id_cash=0) ed in base ai reparti del Registatore Telematico se viene utilizzato questo (id_cash > 0)  
        global $gTables;
        echo '<select id="in_codvat" name="in_codvat">';
        echo '<option value="0">-------------</option>';
        $result = gaz_dbi_dyn_query($gTables['aliiva'].".codice, ".$gTables['aliiva'].".descri", $gTables['cash_register_reparto']. " LEFT JOIN ". $gTables['aliiva']." ON ".$gTables['cash_register_reparto'].".aliiva_codice = ".$gTables['aliiva'].".codice");
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if ($val == $r["codice"]) {
                $selected .= " selected ";
            }
            echo '<option value="' . $r["codice"] . '"' . $selected . '>' . $r['descri'] . "</option>\n";
        }
        echo "</select>\n";
   }
   
   function chkReparto($codvat,$id_cash) { // controllo se il codice IVA dell'articolo ha un reparto associato, se presente restituisco il valore
        global $gTables;
        $exist = gaz_dbi_get_row($gTables['cash_register_reparto'],"aliiva_codice",$codvat, "AND cash_register_id_cash = ".$id_cash);
        return ($exist)?$exist['reparto']:false;
   }
}

class Agenti {

   function getPercent($id_agente, $articolo = '') {
      global $gTables;
      if ($id_agente < 1) {
         return false;
      } else { // devo ricavare la percentuale associata all'articolo(prioritaria) o categoria merceologica
         $value = gaz_dbi_get_row($gTables['artico'], 'codice', $articolo);
         $rs = gaz_dbi_dyn_query($gTables['agenti'] . ".*," . $gTables['provvigioni'] . ".*", $gTables['agenti'] . " LEFT JOIN " . $gTables['provvigioni'] . " ON " . $gTables['agenti'] . ".id_agente = " . $gTables['provvigioni'] . ".id_agente", $gTables['provvigioni'] . ".id_agente = " . $id_agente . " AND ((cod_articolo = '" . $articolo . "' AND cod_articolo != '') OR (cod_catmer = " . intval($value['catmer']) . " AND cod_articolo = ''))", 'cod_articolo DESC', 0, 1);
         $result = gaz_dbi_fetch_array($rs);
         if ($result) {
            return $result['percentuale'];
         } else {
            $result = gaz_dbi_get_row($gTables['agenti'], 'id_agente', $id_agente);
            return $result['base_percent'];
         }
      }
   }

}

class venditCalc extends Compute {

   function contractCalc($id_contract) {
//recupero il contratto da calcolare
      global $gTables, $admin_aziend;
      $this->contract_castle = array();
      $contract = gaz_dbi_get_row($gTables['contract'], "id_contract", $id_contract);
      $this->contract_castel[$contract['vat_code']]['impcast'] = $contract['current_fee'];
      $result = gaz_dbi_dyn_query('*', $gTables['contract_row'], $gTables['contract_row'] . '.id_contract =' . $id_contract, $gTables['contract_row'] . '.id_row');
      while ($row = gaz_dbi_fetch_array($result)) {
         $r_val = CalcolaImportoRigo($row['quanti'], $row['price'], array($row['discount']));
         if (!isset($this->contract_castel[$row['vat_code']])) {
            $this->contract_castel[$row['vat_code']]['impcast'] = 0.00;
         }
         $this->contract_castel[$row['vat_code']]['impcast']+=$r_val;
      }
      $this->add_value_to_VAT_castle($this->contract_castel, $admin_aziend['taxstamp'], $admin_aziend['taxstamp_vat']);
   }

   function computeRounTo($rows, $body_discount, $down = false, $decimal = 5) {
// questa funzione mi servrà per arrotondare ad 1 euro (sia per difetto che per eccesso) i documenti di vendita
      $tot = 0;
      $tqu = 0;
      foreach ($rows as $k => $v) {
         $rows[$k]['sortkey'] = $k; // mi serve per ricordare l'ordine originale
         $rows[$k]['sortquanti'] = 9999999; // mi serve per evitare di ordinare quantità a zero
         if ($v['tiprig'] == 1 || ($v['quanti'] >= 0.001 && $v['tiprig'] == 0)) {
            if ($v['tiprig'] == 0) { // tipo normale
               $tot_row = CalcolaImportoRigo($v['quanti'], $v['prelis'], array($v['sconto'], $body_discount, -$v['pervat']));
            } else {                 // tipo forfait
               $tot_row = CalcolaImportoRigo(1, $v['prelis'], -$v['pervat']);
               $v['quanti'] = 1;
            }
            $rows[$k]['totrow'] = $tot_row;
            $rows[$k]['sortquanti'] = $v['quanti'];
         }
         $tot+=$tot_row;
         $tqu+=$v['quanti'];
         $tot_row = 0;
      }
      $vt = ceil($tot);
      if ($down) {
         $vt = floor($tot);
      }
// cifra totale da arrontondare  e non superare!!!
      $diff = round(($vt - $tot), 2);
// cifra da arrotondare per ogni rigo (IVA compresa)
      $rest = $diff / $tqu;
// riordino l'array per quantità in modo da tentare di imputare le variazioni di prezzo per prima alle quantità maggiori dove è più difficile raggiungere questo obbiettivo
      usort($rows, function($a, $b) {
         return $b['sortquanti'] - $a['sortquanti'];
      });
// riattraverso l'array e scrivo di quanto dovrebbe essere aumentato il prezzo per ogni rigo
      $acc_diff = 0;
      $acc = $rows;
      foreach ($rows as $k => $v) { // riattraverso l'array e scrivo di quanto dovrebbe essere aumentato il prezzo per ogni rigo
         if ($v['tiprig'] == 1 || ($v['quanti'] >= 0.001 && $v['tiprig'] == 0)) {
// tolgo l'iva che verrà sommata ma ci aggiungo gli eventuali sconti
            $rest_part = $rest / (1 + $v['pervat'] / 100) / (1 - $body_discount / 100) / (1 - $v['sconto'] / 100);
            $acc[$k]['prelis'] = round(($v['prelis'] + $rest_part), $decimal);
            if ($v['tiprig'] == 0) { // tipo normale
               $new_tot_row = CalcolaImportoRigo($v['quanti'], $acc[$k]['prelis'], array($v['sconto'], $body_discount, -$v['pervat']));
            } else {                 // tipo forfait
               $new_tot_row = CalcolaImportoRigo(1, $acc[$k]['prelis'], -$v['pervat']);
            }
            $acc[$k]['totrow'] = $new_tot_row;
// accumulo la differenza
            $acc_diff -= ($rows[$k]['totrow'] - $new_tot_row);
         }
      }
// controllo se ho arrotondato tutta la diffarenza iniziale
      $ctrl_diff = round(($diff - $acc_diff), 2);
// sull'ultimo rigo che è pure quello con la quantità più bassa provo ad arrotondare perchè più facile farlo modificando il solo prezzo 
      end($acc);
      $lastkey = key($acc);
      $decpow = pow(10, $decimal);
      if (($ctrl_diff <= -0.01 || $ctrl_diff >= 0.01) && $acc[$lastkey]['quanti'] > 0.001) { // se sto arrotondando per eccesso no posso diminuire di troppo allora il valore non dovrà eccedere
         $diff_prelis = ceil($ctrl_diff / (1 + $acc[$lastkey]['pervat'] / 100) / (1 - $body_discount / 100) / (1 - $acc[$lastkey]['sconto'] / 100) / $acc[$lastkey]['quanti'] * $decpow) / $decpow;
         $acc[$lastkey]['prelis'] += $diff_prelis;
         if ($v['tiprig'] == 0) { // tipo normale
            $new_tot_row = CalcolaImportoRigo($acc[$lastkey]['quanti'], $acc[$lastkey]['prelis'], array($acc[$lastkey]['sconto'], $body_discount, -$acc[$lastkey]['pervat']));
         } else {                 // tipo forfait
            $new_tot_row = CalcolaImportoRigo(1, $acc[$lastkey]['prelis'], -$acc[$lastkey]['pervat']);
         }
//vedo se sono riuscito a compensare la differenza iniziale
         $new_diff = round(($acc[$lastkey]['totrow'] - $new_tot_row - $diff + $acc_diff), 2);
         if ($new_diff >= 0.01) {
// non ci sono riuscito: provo con lo sconto che vado ad indicare in array sul rigo id=0
            $acc[0]['new_body_discount'] = $body_discount + (floor($new_diff / $tot * 10000)) / 100;
         }
      }
// INFINE riordino l'array secondo le key originarie
      usort($acc, function($a, $b) {
         return $a['sortkey'] - $b['sortkey'];
      });
      return $acc;
   }

   /**
    * controlla nell'ordine:
    * 1) prezzo netto cliente/articolo
    * 2) sconto cliente/articolo
    * 3) sconto cliente/raggruppamento (anche per tutti i super-raggruppamenti
    * 4) sconto cliente
    * 5) sconto articolo
    * 
    * se trova un prezzo netto nella tabella sconti cliente/articolo restituisce il numero in negativo, 
    * altrimenti restituisce un numero positivo
    */
   function trovaPrezzoNetto_Sconto($codcli, $codart) {
      global $gTables, $msgtoast;
      $tabellaClienti = $gTables['clfoco'];
      $tabellaArticoli = $gTables['artico'];
      $tabellaScontiArticoli = $gTables['sconti_articoli'];
      $tabellaScontiRaggruppamenti = $gTables['sconti_raggruppamenti'];
//cerco prezzo netto cliente/articolo
      $prezzo_netto = gaz_dbi_get_single_value($tabellaScontiArticoli, "prezzo_netto", "clfoco='$codcli' and codart='$codart'");
      if ($prezzo_netto > 0) {
         $msgtoast = $codart . ": prezzo netto articolo riservato al cliente";
         return -$prezzo_netto;
      }
//cerco sconto cliente/articolo
      $scontoTrovato = gaz_dbi_get_single_value($tabellaScontiArticoli, "sconto", "clfoco='$codcli' and codart='$codart'");
      if ($scontoTrovato > 0) { // sconto cliente/articolo
         $msgtoast = $codart . ": sconto articolo riservato al cliente";
         return $scontoTrovato;
      }
//cerco sconto cliente/raggruppamento
      $scontoGenericoArticolo = gaz_dbi_get_single_value($tabellaArticoli, "sconto", "codice='$codart'");
      if ($scontoGenericoArticolo > 0) { //se lo sconto nella scheda dell'articolo è zero, l'articolo non è soggetto ad ulteriori sconti
         $raggruppamento = gaz_dbi_get_single_value($tabellaArticoli, "ragstat", "codice='$codart'");
         while (!empty($raggruppamento)) {
            $scontoTrovato = gaz_dbi_get_single_value($tabellaScontiRaggruppamenti, "sconto", "clfoco='$codcli' and ragstat = '$raggruppamento'");
            if ($scontoTrovato > 0) { // sconto presente
               $msgtoast = $codart . ": sconto raggruppamento statistico riservato al cliente";
               return $scontoTrovato;
            }
            $raggruppamento = substr($raggruppamento, 0, -1); // levo il carattere più a destra così passo al raggruppamento superiore
         }
      }
//cerco sconto cliente
      $scontoTrovato = gaz_dbi_get_single_value($tabellaClienti, "sconto", "codice='$codcli'");
      if ($scontoTrovato > 0) { // sconto cliente/articolo
         $msgtoast = $codart . ": sconto generico riservato al cliente";
         return $scontoTrovato;
      }
//cerco sconto articolo
//      $scontoTrovato = gaz_dbi_get_single_value($tabellaArticoli, "sconto", "codice='$codart'");
      if ($scontoGenericoArticolo > 0) { // sconto articolo
         $msgtoast = $codart . ": sconto da anagrafe articoli";
         return $scontoGenericoArticolo;
      }
      return 0;
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

   function getAvailableLots($codart, $excluded_movmag = 0) {
// restituisce tutti i lotti non completamente venduti ordinandoli in base alla configurazione aziendale (FIFO o LIFO)
// e propone una ripartizione, se viene passato un movimento di magazzino questo verrà escluso perché si suppone sia lo stesso
// che si sta modificando
// Antonio Germani - si escludono dal conteggio tutti gli inventari: caumag 99
      global $gTables, $admin_aziend;
      $ob = ' ASC'; // FIFO-PWM-STANDARD (First In First Out)
      if ($admin_aziend['stock_eval_method'] == 2) {
         $ob = ' DESC'; // LIFO (Last In First Out)
      }
      $sqlquery = "SELECT *, SUM(quanti*operat) AS rest FROM " . $gTables['movmag'] . "
            LEFT JOIN " . $gTables['lotmag'] . " ON " . $gTables['movmag'] . ".id_mov =" . $gTables['lotmag'] . ".id_movmag  
            WHERE " . $gTables['movmag'] . ".artico = '" . $codart . "' AND id_mov <> '" . $excluded_movmag . "' AND caumag < '99' GROUP BY " . $gTables['movmag'] . ".id_lotmag ORDER BY " . $gTables['lotmag'] . ".expiry" . $ob .", ". $gTables['lotmag'] . ".identifier" . $ob;
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