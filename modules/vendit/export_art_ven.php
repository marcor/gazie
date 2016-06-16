<?php

//session_start();
require_once("../../library/include/datlib.inc.php");
$filename = "art_ven.csv";
$intestazioni=array("Cod. Articolo","Descrizione");
$rows = $_SESSION['rs_artven'];
unset($_SESSION['rs_artven']);
require_once("../../library/include/exportCSV.php");
?>