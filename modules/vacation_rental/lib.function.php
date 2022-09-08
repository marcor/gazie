<?php
/*
    --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-20223 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)
  Ogni diritto è riservato.
  E' possibile usare questo modulo solo dietro autorizzazione dell'autore
  --------------------------------------------------------------------------
 */

function iCalDecoder($file) {
    $ical = @file_get_contents($file);
    preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $ical, $result, PREG_PATTERN_ORDER);
    for ($i = 0; $i < count($result[0]); $i++) {
      $tmpbyline = explode("\r\n", $result[0][$i]);
      if (count($tmpbyline)<3){// se non sono riuscito a separare i righi con \r\n
        $tmpbyline = explode("\n", $result[0][$i]); // provo solo con \n"
      }
      foreach ($tmpbyline as $item) {
        if (substr($item,0,7)=="DTSTART"){
            $majorarray['start']=substr($item,19,10);
        }
        if (substr($item,0,5)=="DTEND"){
            $majorarray['end']=substr($item,17,10);
        }
        if (substr($item,0,3)=="UID"){
            $majorarray['uid']=substr($item,3);
        }
      }
      $icalarray[] = $majorarray;
      unset($majorarray);
    }
    if (isset($icalarray)){
      return $icalarray;
    }
}

// controlla se il numero carta di credito è corretto
function validatecard($cardnumber) {// L' algoritmo di Luhn , noto anche come algoritmo 10, è una semplice checksum utilizzata per convalidare numeri di identificazione, come il numero delle carte di credito
    $cardnumber=preg_replace("/\D|\s/", "", $cardnumber);  # strip any non-digits
    $cardlength=strlen($cardnumber);
    $parity=$cardlength % 2;
    $sum=0;
    for ($i=0; $i<$cardlength; $i++) {
      $digit=$cardnumber[$i];
      if ($i%2==$parity) $digit=$digit*2;
      if ($digit>9) $digit=$digit-9;
      $sum=$sum+$digit;
    }
    $valid=($sum%10==0);
    return $valid;
}

// Ricerca gli sconti applicabili -> vengono esclusi i buoni sconto
function searchdiscount($house="",$facility="",$start="",$end="",$stay=0,$anagra=0){
  global $link, $azTables;
  $where="";
  $and=" WHERE (";
  if (strlen($house)>0){
    $where .= $and." accommodation_code = '".$house."' OR accommodation_code='')";
    $and=" AND (";
  }
  if (intval($facility)>0){
    $where .= $and." facility_id = '".$facility."' OR facility_id = 0)";
    $and=" AND (";
  }
  if (intval($start)>0){
    $where .= $and." valid_from <= '".date("Y-m-d", strtotime($start))."' OR valid_from = '0000-00-00')";
    $and=" AND (";
  }
  if (intval($end)>0){
    $where .= $and." valid_to >= '".date("Y-m-d", strtotime($end))."' OR valid_to = '0000-00-00')";
    $and=" AND (";
  }
  if (intval($stay)>0){
    $where .= $and." min_stay <= '".$stay."' OR min_stay = 0)";
    $and=" AND (";
  }
  if (intval($anagra)>0){
    $where .= $and." id_anagra = '".$anagra."' OR id_anagra = 0)";
    $and=" AND (";
  }
  $where .= $and." status = 'CREATED' AND (discount_voucher_code = '' OR discount_voucher_code = NULL ))";
  $sql = "SELECT * FROM ".$azTables."rental_discounts".$where." ORDER BY priority DESC, id ASC";
  //echo "<br>query: ",$sql,"<br>";
  if ($result = mysqli_query($link, $sql)) {
    return ($result);
  }else {
     echo "Error: " . $sql . "<br>" . mysqli_error($link);
  }
}

// come selectFromDB ma permette di fare join
function selectFromDBJoin($table, $name, $key, $val, $order = false, $empty = false, $bridge = '', $key2 = '', $val_hiddenReq = '', $class = 'FacetSelect', $addOption = null, $style = '', $where = false, $echo=false) {
        global $gTables;
		$acc='';
        $refresh = '';
        if (!$order) {
            $order = $key;
        }
        $query = 'SELECT * FROM ' . $table . ' ';
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
            $acc .= "\t\t <option value=\"" . $r[$key] . "\" $selected >";
            if (empty($key2)) {
                $acc .= substr($r[$key], 0, 43) . "</option>\n";
            } else {
                $acc .= substr($r[$key], 0, 28) . $bridge . substr($r[$key2], 0, 35) . "</option>\n";
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
function get_string_lang($string, $lang){
	$string = " ".$string;
	$ini = strpos($string,"<".$lang.">");
	if ($ini == 0) return $string;
	$ini += strlen("<".$lang.">");
	$len = strpos($string,"</".$lang.">",$ini) - $ini;
  if (intval($len)>0){// se è stato trovato il tag lingua restituisco filtrato
    return substr($string,$ini,$len);
  }else{// altrimenti restituisco come era
    return $string;
  }
}
// calcolo dei giorni da pagare per la tassa turistica
function tour_tax_daytopay($night,$start,$end,$tour_tax_from,$tour_tax_to,$tour_tax_day){
  $daytopay=intval($night);
  if (strtotime($tour_tax_from)){// se è stato impostato un periodo specifico per la tassa turistica
    if (strtotime($start)>= strtotime($tour_tax_from) && strtotime($start)<= strtotime($tour_tax_to)){// se la data di inizio è dentro al periodo tassa turistica
     if (strtotime($end) > strtotime($tour_tax_to)){// se la fine prenotazione va fuori dal periodo tassa turistica
         $diff=date_diff(date_create($tour_tax_to),date_create($start));
         $daytopay= $diff->format("%a");
         if ($daytopay==0){// se sono uguali pago una sola notte
           $daytopay=1;
         }
      }else{// se la fine prenotazione è dentro al periodo tassa turistica
        $diff=date_diff(date_create($end),date_create($tour_tax_to));
        $daytopay= $diff->format("%a");
      }
    }else{// se la data di inizio è fuori dal periodo tassa turistica
      if (strtotime($end) >= strtotime($tour_tax_from) AND strtotime($end)<= strtotime($tour_tax_to)){// se la fine prenotazione è dentro al periodo tassa turistica
        $diff=date_diff(date_create($tour_tax_from),date_create($end));
        $daytopay= $diff->format("%a");
        if ($daytopay==0){// se sono uguali pago una sola notte
           $daytopay=1;
         }
      }else{// se nemmeno la fine è dentro al periodo tassa turistica
        $daytopay=''; // Non pago niente
      }
    }
  }
  if (intval($tour_tax_day) >0 && intval($daytopay) > intval($tour_tax_day)){// se è stato impostato un numero massimo di giorni e i giorni da pagare sono di più di quelli pagabili, li riduco
    $daytopay=$tour_tax_day;
  }
  return $daytopay;
}

// calcolo totale locazione
function get_total_booking($tesbro){// da fare ...
  global $link, $azTables;
}
?>
