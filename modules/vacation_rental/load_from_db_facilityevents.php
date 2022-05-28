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
 
//require("../../library/include/datlib.inc.php");
include ("../../config/config/gconfig.myconf.php");

include_once("manual_settings.php");
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

//load.php
$data = [];
$dataTot = [];
$count=0;
$what = "codice";
$where = "good_or_service=1 AND (custom_field REGEXP 'accommodation_type') AND id_artico_group=".substr(mysqli_escape_string($link,$_GET['id']), 0, 9);
$sql = "SELECT ".$what." FROM ".$azTables."artico WHERE ".$where;
$resulth = mysqli_query($link, $sql);

foreach ($resulth as $resh){ // per ogni alloggio
	// prendo gli eventi a partire da oggi
	$sql = "SELECT * FROM ".$azTables."rental_events WHERE house_code='".substr($resh['codice'], 0, 32)."' AND start >= ".date('Y-m-d')." ORDER BY id ASC";
  $result = mysqli_query($link, $sql);
  foreach($result as $row){
		$data[] = array(
		'id'   => $row["id"],
		'title'   => substr($resh['codice'], 0, 32),
		'start'   => $row["start"],
		'end'   => $row["end"]
		);
	}
}

foreach($data as $dt){// per ogni evento
	$start=$dt['start'];
	$end=$dt['end'];
	while (strtotime($start) < strtotime($end)) {// per ogni giorno dell'evento
		$house=[];//echo "<br>->-> analizzo il giorno:",$start;
		foreach($data as $dt2){// 2ciclo di nuovo tutti gli eventi
			if (!in_array($dt2['title'],$house)){// se c'è gia questo alloggio salto e velocizzoil giro
			//echo"<br>ciclo di nuovo questo evento:",print_r($dt2);
				$start2=$dt2['start'];
				$end2=$dt2['end'];
				while (strtotime($start2) < strtotime($end2)) {// 2per ogni giorno dell'evento
					if (!in_array($dt2['title'],$house) && $start2==$start){// cerco se c'è il giorno e se ancora non aggiunto aggiungo l'alloggio
	//echo "<br>non cè:",$dt2['title']," in -",print_r($house);
						array_push($house,$dt2['title']);//echo "<br>in questo giorno",$start2," ho aggiungo:",$dt2['title']," ecco l'array:",print_r($house);
						break;
					}
					$start2 = date ("Y-m-d", strtotime("+1 days", strtotime($start2)));// 2aumento di un giorno il ciclo
				}
			}
		}
		if (intval(count($house)) == intval($resulth->num_rows)){// se il contatore alloggi è uguale al numero totale alloggi
			$dataTot[] = array(// il giorno è totalmente occupato
			'id'   => $dt["id"],
			'title'   => 'TUTTO ESAURITO',
			'start'   => $start,
			'end'   => $start,
			'display' => 'background'
			);
			//echo "<br>il giorno occupato",$start;
		}elseif (null !==(count($house))&& intval(count($house))>0){
			$dataTot[] = array(// il giorno è parzialmente occupato
			'id'   => $dt["id"],
			'title'   => 'Disponibili '.(intval($resulth->num_rows) - intval(count($house))).' alloggi su '.intval($resulth->num_rows),
			'start'   => $start,
			'end'   => $start
			);
			//echo "<br>il giorno parzialmente occupato",$start;
		}
		$start = date ("Y-m-d", strtotime("+1 days", strtotime($start)));// aumento di un giorno il ciclo
	}
}
echo json_encode($dataTot);
?>
