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
//$intestazioni = array("Codice",
//    "RagSoc",
//    "RagSoc2",
//    "Indirizzo",
//    "CAP",
//    "Citta",
//    "Prov",
//    "IndirizzoAmm",
//    "CAPAmm",
//    "CittaAmm",
//    "ProvAmm",
//    "CF",
//    "PIva",
//    "Telefono",
//    "Fax",
//    "Email",
//    "Cellulare",
//    "RespRapporti");

$rows = $_SESSION['rs_cliven'];
unset($_SESSION['rs_cliven']);
require_once("../../library/include/exportCSV.php");
?>

