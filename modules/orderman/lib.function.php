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

class ordermanForm extends GAzieForm {

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