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
if(isset($_GET["id"])){
	require("../../library/include/datlib.inc.php");
	$event = gaz_dbi_get_row($gTables['rental_events'], "id", intval($_GET['id']));
	if (isset($event) && intval($event['id_tesbro'])>0){
		echo "Non posso cancellare: questa è una prenotazione diretta";
	} else{
	gaz_dbi_del_row($gTables['rental_events'], "id", intval($_GET['id']));
	}
}
?>
