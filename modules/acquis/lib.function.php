<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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

class acquisForm extends GAzieForm {

    function selectSupplier($name, $val, $strSearch = '', $val_hiddenReq = '', $mesg, $class = 'FacetSelect') {
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
                $partner = $anagrafica->queryPartners("*", "codice LIKE '" . $admin_aziend['masfor'] . "%' AND codice >" . intval($admin_aziend['masfor'] . '000000') . "  AND ragso1 LIKE '" . addslashes($strSearch) . "%'", "codice ASC");
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
            echo "\t<input tabindex=\"2\" type=\"text\" id=\"search_$name\" name=\"search[$name]\" value=\"" . $strSearch . "\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
            if (isset($msg)) {
                echo "<input type=\"text\" style=\"color: red; font-weight: bold;\" size=\"" . strlen($msg) . "\" disabled value=\"$msg\">";
            }
            // echo "\t<input tabindex=\"3\" type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
            /** ENRICO FEDELE */
            /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
            echo '<button type="submit" class="btn btn-default btn-sm" name="search_str" tabindex="3"><i class="glyphicon glyphicon-search"></i></button>';
            /** ENRICO FEDELE */
        }
    }

    function selAmmortamentoMin($nameFileXML, $name, $gruppo_specie, $val) {
        $refresh = '';
        if (file_exists('../../library/include/' . $nameFileXML)) {
            $xml = simplexml_load_file('../../library/include/' . $nameFileXML);
        } else {
            exit('Failed to open: ../../library/include/' . $nameFileXML);
        }
        echo "\t <select id=\"$name\" name=\"$name\" tabindex=13   class=\"col-sm-8 small\" style=\"max-width:300px\" onchange=\"this.form.hidden_req.value='ss_amm_min'; this.form.submit();\">\n";
        foreach ($xml->gruppo as $vg) {
            foreach ($vg->specie as $v) {
                $g_s = $vg->gn[0] . $v->ns[0];
                if ($g_s == $gruppo_specie) {
                    $i = 0;
                    echo '<option value="999"> ---------- </option>';
                    foreach ($v->ssd as $v2) {
                        $selected = '';
                        if ($val == $i) {
                            $selected = 'selected';
                        }
                        echo "\t\t <option value=\"" . $i . "\" $selected >" . $v->ssrate[$i] . '% ' . $v2 . "</option>\n";
                        $i++;
                    }
                }
            }
        }
        echo "\t </select>\n";
    }
	
    function selectArtico($name, $val, $strSearch = '', $val_hiddenReq = '', $mesg, $class = 'FacetSelect') {
		// funzione per la selezione di un articolo di magazzino avvalendosi dell'autocomplete
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
      global $gTables, $admin_aziend;
      $ob = ' ASC'; // FIFO-PWM-STANDARD
      if ($admin_aziend['stock_eval_method'] == 2) {
         $ob = ' DESC'; // LIFO
      }
      $sqlquery = "SELECT *, SUM(quanti*operat) AS rest FROM " . $gTables['movmag'] . "
            LEFT JOIN " . $gTables['lotmag'] . " ON " . $gTables['movmag'] . ".id_mov =" . $gTables['lotmag'] . ".id_movmag  
            WHERE " . $gTables['movmag'] . ".artico = '" . $codart . "' AND id_mov <> " . $excluded_movmag . " GROUP BY " . $gTables['movmag'] . ".id_lotmag ORDER BY " . $gTables['movmag'] . ".datreg" . $ob;
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

   function thereisLot($id_tesdoc) {
// restituisce true se nel documento di vendita c'è almeno un rigo al quale è assegnato un lotto 
      $r = false;
      global $gTables;
      $sqlquery = "SELECT * FROM " . $gTables['rigdoc'] . " AS rd
            LEFT JOIN " . $gTables['movmag'] . " AS mm ON rd.id_mag = mm.id_mov  
            WHERE rd.id_tes = " . $id_tesdoc . " AND mm.id_lotmag > 0 LIMIT 1";
      $result = gaz_dbi_query($sqlquery);
      $rows = gaz_dbi_num_rows($result);
      if ($rows > 1) { // il documento ha almeno un lotto caricato 
         $r = true;
      }
      return $r;
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

}


?>