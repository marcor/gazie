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
 
class campForm extends GAzieForm {
	
	// Antonio Germani - Come select selectFromDB ma con in più preleva $key4 da $table2, dove $key3 è uguale a $key2, e lo visualizza nella scelta del select. Cioè nelle scelte del select ci sarà $key e $key4
	function selectFrom2DB($table,$table2,$key3,$key4, $name, $key, $val, $order = false, $empty = false, $bridge = '', $key2 = '', $val_hiddenReq = '', $class = 'FacetSelect', $addOption = null, $style = '', $where = false, $echo=false, $disabled="") {
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
        $acc .= "\t <select $disabled id=\"$name\" name=\"$name\" class=\"$class\" $refresh $style>\n";
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
	
	function getLotRecip($codsil,$codart=""){// funzione per trovare l'ID dell'ultimo lotto inserito nel recipiente di stoccaggio
		$id_lotma=false;
		global $gTables,$admin_aziend;
		$sil = new lotmag();
		$what=$gTables['movmag'].".id_lotmag, ".$gTables['movmag'].".id_mov, ".$gTables['movmag'].".artico ";
		$table=$gTables['movmag']." LEFT JOIN ".$gTables['camp_mov_sian']." ON ".$gTables['camp_mov_sian'].".id_movmag = ".$gTables['movmag'].".id_mov";
		$where="recip_stocc = '".$codsil."'";
		if (strlen($codart)>0){
			$where = $where." AND artico = '".$codart."'";
		}
		$orderby="id_mov DESC";
		$groupby= "";
		$passo=2000000;
		$limit=0;
		$lastmovmag=gaz_dbi_dyn_query ($what,$table,$where,$orderby,$limit,$passo,$groupby);
		
		while ($r = gaz_dbi_fetch_array($lastmovmag)) {
			$id_lotma = $r['id_lotmag'];
			$cont= $sil -> dispLotID ($r['artico'], $r['id_lotmag']); 
			if ($cont>0){
				break;
			}
		}
		$identifier=gaz_dbi_get_row($gTables['lotmag'], "id", $id_lotma)['identifier'];
		return array($id_lotma,$identifier) ;
	}
	
	function selectSilos($name, $key, $val, $order = false, $empty = false, $key2 = '', $val_hiddenReq = '', $class = 'FacetSelect', $addOption = null, $style = '', $where = false, $echo=false) {
        global $gTables;
		$campsilos = new silos();
		$acc='';
        $refresh = '';
        if (!$order) {
            $order = $key;
        }
        $query = 'SELECT * FROM `' . $gTables['camp_recip_stocc'] . '` ';
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
			$lot = $campsilos->getLotRecip($r[$key]);
			$cont = $campsilos->getCont($r[$key]);
            $selected = '';
            if ($r[$key] == $val) {
                $selected = "selected";
            }
            $acc .= "\t\t <option value=\"" . $r[$key] . "\" $selected >";
            if (empty($key2)) {
                $acc .= substr($r[$key], 0, 43) . "</option>\n";
            } else {
                $acc .= substr($r[$key], 0, 28) . " - Kg: " . substr($r[$key2], 0, 35) . " - Lotto: " . $lot[1] . " - Cont.Kg: ". $cont ."</option>\n";
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

// converte da ore decimali a hh:mm:ss - Es. da 5.75 a 05:45:00
function convertTime($h_dec){
    // start by converting to seconds
    $seconds = ($h_dec * 3600);
    // we're given hours, so let's get those the easy way
    $hours = floor($h_dec);
    // since we've "calculated" hours, let's remove them from the seconds variable
    $seconds -= $hours * 3600;
    // calculate minutes left
    $minutes = floor($seconds / 60);
    // remove those from seconds as well
    $seconds -= $minutes * 60;
	if (ceil($seconds) == 60){
		$minutes++;
		$seconds=0;
	}
    // return the time formatted HH:MM:SS
    //return lz($hours).":".lz($minutes).":".lz($seconds);
	// return the time formatted HH:MM
    return lz($hours).":".lz($minutes);
}
// lz = leading zero
function lz($num)
{
    return (strlen($num) < 2) ? "0{$num}" : $num;
}
// FALSE converte da hh:mm:ss a secondi - Es. da 05:45:00 a 20700. Oppure TRUE da hh:mm:ss in ore decimali - Es. da 05:45:00 a 5.75
function convertHours($time,$dec = FALSE){
	if ($dec == 0){
		$dec=1;
	} else {
		$dec=3600;
	}
	$parsed = date_parse($time);
	$seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
	return $seconds/$dec;
}
function ContTratt($artico,$idProd=""){// restituisce il numero di trattamenti
		global $gTables,$admin_aziend;
		$year = date("Y"); 
		$where=" WHERE artico = '".$artico."' AND tipdoc ='CAM' AND operat = '-1'";
		if ($idProd>0){
			$where .=" AND id_orderman = ". $idProd;
		} else {
			$where .=" AND SUBSTRING_INDEX(datdoc, '-', 1) = ". $year;
		}		
		$query = 'SELECT * FROM `' . $gTables['movmag'] . '` '. $where;
		//echo $query;
		$res=gaz_dbi_query ($query);
		return $res->num_rows;
}
?>