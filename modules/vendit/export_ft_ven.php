<?php

//session_start();
require_once("../../library/include/datlib.inc.php");
$filename = "ft_ven.csv";
$intestazioni=array("Codice", "Data", "CodArticolo", "Un", "Qt");
$rows = $_SESSION['rs_ftven'];
unset($_SESSION['rs_ftven']);
require_once("../../library/include/exportCSV.php");
?>