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
$data = array();

if(isset($_GET['id'])){
  $sql = "SELECT * FROM ".$azTables."rental_events WHERE house_code='".substr(mysqli_escape_string($link,$_GET['id']), 0, 32)."' AND (start >= '".date('Y-m-d')."' OR end >= '".date('Y-m-d')."') ORDER BY id ASC";
  $result = mysqli_query($link, $sql); 
  if (isset($result)){
	foreach($result as $row){
		$data[] = array(
		'id'   => $row["id"],
		'title'   => addslashes($row["title"]),
		'start'   => $row["start"],
		'end'   => $row["end"]
		);
	}
  }
	echo json_encode($data);
}
?>
