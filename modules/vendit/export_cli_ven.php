<?php

//session_start();
require_once("../../library/include/datlib.inc.php");
$filename = "art_ven.csv";
$intestazioni=array("Codice", 
      "RagSoc", 
      "Sede legale", 
      "IndirizzoSped", 
      "CAPSped", 
      "CittaSped", 
      "ProvSped", 
      "CF", 
      "PIva", 
      "Telefono", 
      "Fax", 
      "Email", 
      "Cellulare");
$rows = $_SESSION['rs_cliven'];
unset($_SESSION['rs_cliven']);
require_once("../../library/include/exportCSV.php");
?>

