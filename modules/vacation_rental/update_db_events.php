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
if(isset($_GET['id']) && intval($_GET['id'])>0){
// a causa di un problema di fuso orario bisogna aggiungere un'ora alle date
$_GET['start']=date('Y-m-d', strtotime($_GET['start']. ' + 1 hour'));
$_GET['end']=date('Y-m-d', strtotime($_GET['end']. ' + 1 hour'));
$columns = array('id','title', 'start','end','house_code');
$newValue = array('title'=>substr($_GET['title'],0,128), 'start'=>$_GET['start'],'end'=>$_GET['end'],'house_code'=>substr($_GET['house_code'],0,32));
$codice=array();
$codice[0]="id";
$codice[1]=intval($_GET['id']);
tableUpdate('rental_events', $columns, $codice, $newValue);
}
?>
