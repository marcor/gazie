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
if ( is_numeric($_GET['title']) ) {
 $columns = array('id','title', 'start','end','house_code','price');
 $_GET['start']=date('Y-m-d', strtotime($_GET['start']. ' + 1 hour'));
 if ($_GET['end']==null){
	 $_GET['end']=date('Y-m-d', strtotime($_GET['start']. ' + 1 day'));;
 }
 $newValue = array('title'=>substr($_GET['title'],0,128), 'start'=>substr($_GET['start'],0,10),'end'=>substr($_GET['end'],0,10),'house_code'=>substr($_GET['house_code'],0,32),'price'=>substr($_GET['title'],0,14));
 tableInsert('rental_prices', $columns, $newValue);
}
?>
