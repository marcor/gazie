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
 
class campForm extends GAzieForm {
	
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

class silos {	
		
	function getCont($codsil){// restituisce la quantità di olio di un recipiente
		global $gTables,$admin_aziend;
		$content=0;
		$orderby=2;
		$limit=0;
		$passo=2000000;
		$where="recip_stocc = '".$codsil."'";
		$what=	$gTables['movmag'].".operat, ".$gTables['movmag'].".quanti, ".$gTables['movmag'].".id_orderman, ".
				$gTables['camp_mov_sian'].".*, ".$gTables['camp_artico'].".confezione ";
		$groupby= "";
		$table=$gTables['camp_mov_sian']." LEFT JOIN ".$gTables['movmag']." ON ".$gTables['movmag'].".id_mov = ".$gTables['camp_mov_sian'].".id_movmag
											LEFT JOIN ".$gTables['camp_artico']." ON ".$gTables['camp_artico'].".codice = ".$gTables['movmag'].".artico
		";
		$ressilos=gaz_dbi_dyn_query ($what,$table,$where,$orderby,$limit,$passo,$groupby);
		while ($r = gaz_dbi_fetch_array($ressilos)) {
			if ($r['confezione']==0){
				$content=$content+($r['quanti']*$r['operat']);
			} 
		}
		$content=number_format ($content,3);
		
		return $content ;
	}
	
	function getLotRecip($codsil){// funzione per trovare l'ultimo lotto inserito nel recipiente di stoccaggio
		$id_lotmag=false;
		global $gTables,$admin_aziend;
		$what=$gTables['movmag'].".id_lotmag, ".$gTables['movmag'].".id_mov ";
		$table=$gTables['movmag']." LEFT JOIN ".$gTables['camp_mov_sian']." ON ".$gTables['camp_mov_sian'].".id_movmag = ".$gTables['movmag'].".id_mov";
		$where="recip_stocc = '".$codsil."'";
		$orderby="id_mov DESC";
		$groupby= "";
		$passo=2000000;
		$limit=0;
		$lastmovmag=gaz_dbi_dyn_query ($what,$table,$where,$orderby,$limit,$passo,$groupby);
		while ($r = gaz_dbi_fetch_array($lastmovmag)) {
			$id_lotmag = $r['id_lotmag'];break;
		}	
		return $id_lotmag ;
	}
	
}
?>