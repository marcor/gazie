<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
  (http://www.devincentiis.it)
  <http://gazie.sourceforge.net>
  --------------------------------------------------------------------------
  Questo programma e` free software;   e` lecito redistribuirlo  e/o
  modificarlo secondo i  termini della Licenza Pubblica Generica GNU
  come e` pubblicata dalla Free Software Foundation; o la versione 2
  della licenza o (a propria scelta) una versione successiva.

  Questo programma  e` distribuito nella speranza  che sia utile, ma
  SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
  NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
  veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

  Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
  Generica GNU insieme a   questo programma; in caso  contrario,  si
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */

$path_root = $_SERVER['DOCUMENT_ROOT'];
require( "../../config/config/gconfig.php" );
require( "../../library/include/" . $NomeDB . ".lib.php" );
require( "../../library/include/function.inc.php"  );

if ( $debug_active ) {
	error_reporting(E_ALL);
	require ( "../../library/kint/build/kint.phar");
} else {
	error_reporting(0);
}

if (isset($_SESSION['table_prefix'])) {
   $table_prefix=substr($_SESSION['table_prefix'],0,12);
} elseif(isset($_GET['tp'])) {
	if ( defined('FILTER_SANITIZE_ADD_SLASHES') ) {
		$table_prefix=filter_var(substr($_GET['tp'],0,12),FILTER_SANITIZE_ADD_SLASHES);
	} else {
		$table_prefix=addslashes(substr($_GET['tp'],0,12));
	}
} else {
	if ( defined('FILTER_SANITIZE_ADD_SLASHES') ) {
		$table_prefix=filter_var(substr($table_prefix,0,12),FILTER_SANITIZE_ADD_SLASHES);
	} else {
		$table_prefix=addslashes(substr($table_prefix,0,12));
	}
}

$month = array(1=>"Gennaio", 2=>"Febbraio", 3=>"Marzo", 4=>"Aprile", 5=>"Maggio", 6=>"Giugno", 7=>"Luglio", 8=>"Agosto", 9=>"Settembre", 10=>"Ottobre", 11=>"Novembre", 12=>"Dicembre");

// tabelle comuni alle aziende della stessa gestione
$tn = array('admin', 'admin_config', 'admin_module', 'anagra', 'aziend', 'classroom', 'config',
    'country', 'currencies', 'currency_history', 'destina', 'camp_avversita', 'camp_colture', 
	'camp_fitofarmaci', 'camp_uso_fitofarmaci',	'languages', 'menu_module', 'menu_script', 'menu_usage', 
    'module', 'municipalities', 'provinces', 'regions', 'staff_absence_type', 'staff_work_type', 'students',
    'breadcrumb' );
foreach ($tn as $v) {
    $gTables[$v] = $table_prefix . "_" . $v;
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $local = gaz_dbi_get_row($gTables['config'], 'variable', 'win_locale');
} else {
    $local = gaz_dbi_get_row($gTables['config'], 'variable', 'lin_locale');
}

if ($gazie_locale != "") {
    setlocale(LC_TIME, $gazie_locale);
} else {
    setlocale(LC_TIME, $local['cvalue']);
}

$id = 1;
if (isset($_SESSION['company_id'])) {
    $id = sprintf('%03d', $_SESSION['company_id']);
}

/* controllo anche se includere il file dei nomi di tabelle specifico del modulo
  residente nella directory del module stesso, con queste caratteristiche:
  modules/nome_modulo/lib.data.php
 */
if (@file_exists('./lib.data.php')) {
    require('./lib.data.php');
}

//tabelle aziendali
$tn = array('aliiva', 'agenti', 'artico', 'assets', 'banapp', 'body_text', 'campi', 'cash_register', 'cash_register_reparto', 'cash_register_tender',
    'catmer', 'caucon', 'caucon_rows', 'caumag', 'clfoco', 'company_config', 'company_data','contract', 'contract_row', 
	'comunicazioni_dati_fatture', 'contract', 'effett', 'expdoc', 'extcon', 'files', 'imball', 'letter', 
	'liquidazioni_iva', 'lotmag', 'movmag', 'pagame', 'paymov', 'portos', 'provvigioni', 'rigbro', 
	'rigdoc', 'rigmoc', 'rigmoi', 'spediz', 'staff', 'staff_skills', 'staff_worked_hours', 'tesbro',
	'tesdoc', 'tesmov', 'vettor', 'fae_flux', 'assist',	'ragstat', 'agenti_forn',	'movimenti', 
    'sconti_articoli', 'sconti_raggruppamenti', 'instal', 'instal_component', 'instal_type', 'orderman', 'registro_trattamento_dati', 
    'distinta_base', 'disbas', 'disbas_componente', 'tescmr', 'rigcmr', 'syncronize_oc','camp_mov_sian','camp_recip_stocc','camp_artico','camp_anagra');
foreach ($tn as $v) {
    $gTables[$v] = $table_prefix . "_" . $id . $v;
}

?>
