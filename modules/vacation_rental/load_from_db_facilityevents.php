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
include_once("manual_settings.php");
if ($_GET['token'] == md5($token.date('Y-m-d'))){
  //require("../../library/include/datlib.inc.php");
  include ("../../config/config/gconfig.myconf.php");

  $azTables = constant("table_prefix").$idDB;
  $IDaz=preg_replace("/[^1-9]/", "", $azTables );

  $servername = constant("Host");
  $username = constant("User");
  $pass = constant("Password");
  $dbname = constant("Database");
  $genTables = constant("table_prefix")."_";

  // Create connection
  $link = mysqli_connect($servername, $username, $pass, $dbname);
  // Check connection
  if (!$link) {
      die("Connection DB failed: " . mysqli_connect_error());
  }
  $link -> set_charset("utf8");

  $data = [];
  $dataTot = [];

  // prendo gli eventi a partire da oggi connessi alla struttura
  $sql = "SELECT ".$azTables."rental_events.* FROM ".$azTables."rental_events LEFT JOIN ".$azTables."artico ON ".$azTables."artico.id_artico_group = ". substr(mysqli_escape_string($link,$_GET['id']), 0, 9) ." WHERE ".$azTables."rental_events.house_code = ".$azTables."artico.codice AND (start >= '".date('Y-m-d')."' OR end >= '".date('Y-m-d')."') ORDER BY id ASC";
  $result = mysqli_query($link, $sql);    

	$n=-1;
  foreach($result as $res){// per ogni evento
    $start=$res['start'];
    $end=$res['end'];	
	
    while (strtotime($start) < strtotime($end)) {// per ogni giorno dell'evento
	
		if (!in_array($start,$data)){//se il giorno non è mai stato analizzato lo creo
			$data[]=$start;$n++;
			$house[$n][]= $res['house_code'];// con la stessa chiave $n, memorizzo il codice alloggio
			$dataTot[$n] = array(// il giorno è parzialmente occupato
			  'id'   => $res['id'],
			  'title'   => 'Occupato: '.$res['house_code'],
			  'start'   => $start,
			  'end'   => $start,
			  'display' => 'background'
			  );
		}else{
			$id = array_search($start, array_column($dataTot, 'start'));// trovo in quale array ho già il giorno analizzato
			if (!in_array($res['house_code'],$house[$id])){
				$house[$id][]= $res['house_code'];// nella stessa chiave $n, aggiungo il codice alloggio
				$dataTot[$id]['title']=$dataTot[$id]['title']." + ".$res['house_code'];				
			}
		}	  
      $start = date ("Y-m-d", strtotime("+1 days", strtotime($start)));// aumento di un giorno il ciclo
    }	
  }
   // echo "<br>DATA:",print_r($data);
  echo json_encode($dataTot);
}
?>