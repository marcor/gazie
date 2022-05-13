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
require("../../library/include/datlib.inc.php");
 $columns = array('id','title', 'start','end','house_code');
 if ($_GET['end']==null){
	 $_GET['end']=$_GET['start'];
 }
 $newValue = array('title'=>substr($_GET['title'],0,128), 'start'=>substr($_GET['start'],0,10),'end'=>substr($_GET['end'],0,10),'house_code'=>substr($_GET['house_code'],0,32));
 tableInsert('rental_events', $columns, $newValue);
?>
