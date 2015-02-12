<?php

define ( 'GAZ_ROOT', dirname(__FILE__).'/../' );
 
include 'testGazie.php';

include(GAZ_ROOT . "config/config/gconfig.php");
include(GAZ_ROOT . 'library/include/'.$NomeDB.'.lib.php');


if (isset($_SESSION['table_prefix'])) {
	$table_prefix=substr($_SESSION['table_prefix'],0,12);
} elseif (isset($_GET['tp'])) {
	$table_prefix=filter_var(substr($_GET['tp'],0,12),FILTER_SANITIZE_MAGIC_QUOTES);
} else {
	$table_prefix=filter_var(substr($table_prefix,0,12),FILTER_SANITIZE_MAGIC_QUOTES);
}


// tabelle comuni alle aziende della stessa gestione
$tn=array('admin','admin_module','anagra','aziend','config','country','currencies','currency_history',
		'languages','regions','provinces','municipalities','menu_module','module','menu_script');
foreach ($tn as $v){
	$gTables[$v]= $table_prefix."_".$v;
}

// 		if ( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
// 			$local=gaz_dbi_get_row($gTables['config'],'variable','win_locale');
// 		} else {
// 			$local=gaz_dbi_get_row($gTables['config'],'variable','lin_locale');
// 		}

// 		if ($gazie_locale != "") {
// 			setlocale(LC_TIME, $gazie_locale);
// 		} else {
// 			setlocale(LC_TIME, $local['cvalue']);
// 		}

$id=1;
if (isset($_SESSION['enterprise_id'])) {
	$id=sprintf('%03d',$_SESSION['enterprise_id']);
}

/* controllo anche se includere il file dei nomi di tabelle specifico del modulo
 residente nella directory del module stesso, con queste caratteristiche:
modules/nome_modulo/lib.data.php
*/
// 		if(@file_exists('./lib.data.php') ) {
// 			require('./lib.data.php');
// 		}

//tabelle aziendali
$tn=array('aliiva','agenti','artico','banapp','body_text','cash_register','catmer',
		'caucon','caumag','clfoco','company_config','company_data','contract','effett','extcon','files',
		'imball','letter','movmag','pagame','paymov','portos','provvigioni','rigbro',
		'rigdoc','rigmoc','rigmoi','spediz','staff','staff_skills','tesbro','tesdoc','tesmov','vettor','fae_flux');
foreach ($tn as $v){
	$gTables[$v]= $table_prefix."_".$id.$v;
}
