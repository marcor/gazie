<?php
/*
  --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-2023 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)

  --------------------------------------------------------------------------
   --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
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
function searchdiscount($house="",$facility="",$start="",$end="",$stay=0,$anagra=0,$table=""){
  global $link, $azTables;
  if ($table == ""){
	  $table = $azTables."rental_discounts";
  }
  $where=" ";
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
  $sql = "SELECT * FROM ".$table.$where." ORDER BY priority DESC, id ASC";
  //echo "<br>query: ",$sql,"<br>";
  if ($result = mysqli_query($link, $sql)) {
    return ($result);
  }else {
     echo "Error: " . $sql . "<br>" . mysqli_error($link);
  }
}

// Ricerca gli sconti più vicini -> vengono esclusi i buoni sconto
function search_near_discount($house="",$facility="",$start="",$end="",$stay=0,$anagra=0,$table=""){
  global $link, $azTables;
  if ($table == ""){
	  $table = $azTables."rental_discounts";
  }
  $where=" ";
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
    $near_stay = $stay+3;
    $where .= $and." min_stay <= '".$near_stay."' AND min_stay > '".$stay."' )";
    $and=" AND (";
  }
  if (intval($anagra)>0){
    $where .= $and." id_anagra = '".$anagra."' OR id_anagra = 0)";
    $and=" AND (";
  }
  $where .= $and." status = 'CREATED' AND (discount_voucher_code = '' OR discount_voucher_code = NULL ))";
  $sql = "SELECT * FROM ".$table.$where." ORDER BY priority DESC, id ASC";
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
  $tour_tax_from=$tour_tax_from."-".date("Y", strtotime($start)); // aggiungo l'anno all'inizio pagamento tassa turistica
  $tour_tax_to=$tour_tax_to."-".date("Y", strtotime($start)); // aggiungo l'anno alla fine pagamento tassa turistica

  $daytopay=intval($night);
  if (strtotime($tour_tax_from)){// se è stato impostato un periodo specifico per la tassa turistica

    if (strtotime($start)>= strtotime($tour_tax_from) && strtotime($start)<= strtotime($tour_tax_to)){// se la data di inizio è dentro al periodo tassa turistica

     if (strtotime($end) > strtotime($tour_tax_to)){// se la fine prenotazione va fuori dal periodo tassa turistica
         $diff=date_diff(date_create($tour_tax_to),date_create($start));

         $daytopay= $diff->format("%a");

      }else{// se la fine prenotazione è dentro al periodo tassa turistica
        $diff=date_diff(date_create($end),date_create($start));
        $daytopay= $diff->format("%a");
      }
    }else{// se la data di inizio è fuori dal periodo tassa turistica
      if (strtotime($end) >= strtotime($tour_tax_from) AND strtotime($end)<= strtotime($tour_tax_to)){// se la fine prenotazione è dentro al periodo tassa turistica
        $diff=date_diff(date_create($end),date_create($tour_tax_from));
        $daytopay= $diff->format("%a");

      }else{// se la fine è fuori al periodo tassa turistica
        if (strtotime($start) < strtotime($tour_tax_from) && strtotime($end) > strtotime($tour_tax_to)){// se la prenotazione è a cavallo, cioè ingloba il periodo
          $diff=date_diff(date_create($tour_tax_to),date_create($tour_tax_from));// paga per il periodo della tassa turistica
          $daytopay= $diff->format("%a");
        }else{// se è fuori non paga nulla
          $daytopay=0;
        }
      }
    }
  }

  if (intval($tour_tax_day) >0 && intval($daytopay) > intval($tour_tax_day)){// se è stato impostato un numero massimo di giorni e i giorni da pagare sono di più di quelli pagabili, li riduco
    $daytopay=$tour_tax_day;
  }

  return $daytopay;
}

// calcolo totale locazione
function get_totalprice_booking($tesbro){
  if ($tesbro!==''){
    $tesbro=intval($tesbro);
    global $link, $azTables, $gTables;// posso chiamare la funzione con entrambi i metodi
    if ($azTables){
      $tablerig = $azTables."rigbro";
      $tabletes = $azTables."tesbro";
    }else{
      $tablerig = $gTables['rigbro'];
      $tabletes = $gTables['tesbro'];
    }
    $where = " WHERE id_tes = '".$tesbro."'";
    $sql = "SELECT SUM(quanti * prelis) AS totalprice FROM ".$tablerig.$where;
    if ($result = mysqli_query($link, $sql)) {
       $row = mysqli_fetch_assoc($result);
        $where = " WHERE id_tes = '".$tesbro."'";
        $sql = "SELECT speban FROM ".$tabletes.$where." LIMIT 1";
        if ($result = mysqli_query($link, $sql)) {
          $rowtes = mysqli_fetch_assoc($result);
		  $rowtes['speban']=(isset($rowtes['speban']))?$rowtes['speban']:0;
          $totalprice= $row['totalprice']+$rowtes['speban'];// aggiungo eventuali spese bancarie
           return  $totalprice;
        }else{
          echo "Error: " . $sql . "<br>" . mysqli_error($link);
        }
    }else {
       echo "Error: " . $sql . "<br>" . mysqli_error($link);
    }
  }else{
    $err="tesbro vuoto";
    return $err ;
  }
}

function get_total_promemo($startprom,$endprom){
  global $link, $azTables, $gTables;// posso chiamare la funzione con entrambi i metodi
  if ($azTables){
    $tableart = $azTables."artico";
    $tablerent_ev = $azTables."rental_events";
    $tabletes = $azTables."tesbro";
  }else{
    $tableart = $gTables['artico'];
    $tablerent_ev = $gTables['rental_events'];
    $tabletes = $gTables['tesbro'];
  }
  $data = [];
  $tot_nights_booked=0;
  $ret=[];
  $ret['totalprice_booking']=0;
  $what = "codice";
  $datediff = strtotime($endprom)-strtotime($startprom);
  $night_promemo = round($datediff / (60 * 60 * 24));// numero notti dell'arco di tempo richiesto
  $where = "good_or_service=1 AND (custom_field REGEXP 'accommodation_type')";
  $sql = "SELECT ".$what." FROM ".$tableart." WHERE ".$where;
  $resulth = mysqli_query($link, $sql); // prendo tutti gli alloggi
  $num_all = $resulth->num_rows;// numero alloggi presenti in GAzie
  foreach ($resulth as $resh){ // per ogni alloggio
    // prendo tutti gli eventi dell'alloggio che interessano l'arco di tempo richiesto
    $sql = "SELECT * FROM ".$tablerent_ev." LEFT JOIN ".$tabletes." ON  ".$tablerent_ev.".id_tesbro = ".$tabletes.".id_tes WHERE  ".$tablerent_ev.".type = 'ALLOGGIO' AND ".$tablerent_ev.".id_tesbro > 0 AND (custom_field IS NULL OR custom_field LIKE '%PENDING%' OR custom_field LIKE '%CONFIRMED%' OR custom_field LIKE '%FROZEN%') AND house_code='".substr($resh['codice'], 0, 32)."' AND (start >= '".$startprom."' OR start <= '".$endprom."' OR end >= '".$startprom."' OR end <= '".$endprom."') ORDER BY id ASC";
    //echo $sql;
    $result = mysqli_query($link, $sql);

    foreach($result as $row){ // per ogni evento dell'alloggio
      //echo "<pre>evento alloggio:",print_r($row);
      $datediff = strtotime($row['end'])-strtotime($row['start']);
      $nights_event = round($datediff / (60 * 60 * 24));// numero notti totali della prenotazione(evento)
      $tot_n_event_in_promemo=0;
      $start=$row['start'];
      $end=$row['end'];
      // ciclo i giorni dell'evento
      while (strtotime($start) < strtotime($end)) {// per ogni giorno dell'evento

        if ($start >= $startprom AND $start <= $endprom) {// se il giorno è dentro l'arco di tempo richiesto
		  //echo "<br>",$start," è dentro";
          if (!isset($data[$start])){
            $data[$start]=array();
          }
            if (!in_array($row['house_code'],$data[$start])){// escludendo i giorni che hanno già quell'alloggio
             array_push($data[$start],$row['house_code']);// conteggio il giorno per questo alloggio
             $tot_nights_booked  ++;
             $tot_n_event_in_promemo ++;
          }

        }
        $start = date ("Y-m-d", strtotime("+1 days", strtotime($start)));// aumento di un giorno il ciclo
      }
      $ret['totalprice_booking'] += ((get_totalprice_booking($row['id_tesbro']))/$nights_event)*$tot_n_event_in_promemo;// aggiungo il costo medio della locazione(evento) calcolata sui giorni che rientrano nell'arco di tempo richiesto
	  //echo "<br>totale:",$ret['totalprice_booking']," prezzo prenotazione:",((get_totalprice_booking($row['id_tesbro']))/$nights_event)*$tot_n_event_in_promemo;
	}
  }
  $ret['tot_nights_bookable']= $num_all * $night_promemo;
  $ret['perc_booked'] = ($tot_nights_booked/$ret['tot_nights_bookable'])*100;
  $ret['tot_nights_booked'] = $tot_nights_booked;

  return $ret;
}

function get_next_check($startprom,$endprom){
  global $link, $azTables, $gTables;// posso chiamare la funzione con entrambi i metodi
  $next['in']=[];
  $next['out']=[];
  if ($azTables){
    $tableart = $azTables."artico";
    $tablerent_ev = $azTables."rental_events";
    $tabletes = $azTables."tesbro";
  }else{
    $tableart = $gTables['artico'];
    $tablerent_ev = $gTables['rental_events'];
    $tabletes = $gTables['tesbro'];
  }

  $rs_booking = gaz_dbi_dyn_query("id,start,end", $tablerent_ev, "(start >= ".$startprom." OR start <= ".$endprom." OR end >= ".$startprom." OR end <= ".$endprom.")  AND type = 'ALLOGGIO'", "id asc");

  while ($booking = gaz_dbi_fetch_assoc($rs_booking)){// ciclo le prenotazioni che interessano arco di tempo richiesto
    if (intval($booking['id'])>0 && $booking['start']>= $startprom && $booking['start'] <= $endprom){//se la data di check-in è dentro
      $next['in'][]=$booking;
    }
    if (intval($booking['id'])>0 && $booking['end']>= $startprom && $booking['end'] <= $endprom){//se la data di check-out è dentro

	  $next['out'][]=$booking;
    }
  }
  return $next;
}

function get_total_paid($idtesbro){
  global $link, $azTables, $gTables;// posso chiamare la funzione con entrambi i metodi
  if ($azTables){
    $tablerent_pay = $azTables."rental_payments";
  }else{
    $tablerent_pay = $gTables['rental_payments'];
  }
  $where = " WHERE id_tesbro = '".$idtesbro."' AND payment_status = 'Completed'";
  $sql = "SELECT SUM(payment_gross) AS totalpaid FROM ".$tablerent_pay.$where;
  if ($result = mysqli_query($link, $sql)) {
    $row = mysqli_fetch_assoc($result);

    return $row['totalpaid'];
  }else {
     echo "Error: " . $sql . "<br>" . mysqli_error($link);
  }
}
?>
