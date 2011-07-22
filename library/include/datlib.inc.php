<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/

require("../../config/config/gconfig.php");
require('../../library/include/'.$NomeDB.'.lib.php');
$gTables['admin'] = $table_prefix."_admin";
$gTables['admin_module'] = $table_prefix."_admin_module";
$gTables['anagra'] = $table_prefix."_anagra";
$gTables['aziend'] = $table_prefix."_aziend";
$gTables['config'] = $table_prefix."_config";
$gTables['country'] = $table_prefix."_country";
$gTables['regions'] = $table_prefix."_regions";
$gTables['provinces'] = $table_prefix."_provinces";
$gTables['municipalities'] = $table_prefix."_municipalities";
$gTables['menu_module'] = $table_prefix."_menu_module";
$gTables['module'] = $table_prefix."_module";
$gTables['menu_script'] = $table_prefix."_menu_script";

require("../../library/include/function.inc.php");

$id=1;
if (isset($_SESSION['enterprise_id'])) {
  $id=sprintf('%03d',$_SESSION['enterprise_id']);
}

/* controllo anche se includere il file dei nomi di tabelle specifico del modulo
     residente nella directory del module stesso, con queste caratteristiche:
     modules/nome_modulo/lib.data.php
*/
if(@file_exists('./lib.data.php') ) {
    require('./lib.data.php');
}

$gTables['aliiva'] = $table_prefix.'_'.$id.'aliiva';
$gTables['agenti'] = $table_prefix.'_'.$id.'agenti';
$gTables['artico'] = $table_prefix.'_'.$id.'artico';
$gTables['banapp'] = $table_prefix.'_'.$id.'banapp';
$gTables['body_text'] = $table_prefix.'_'.$id."body_text";
$gTables['cash_register'] = $table_prefix.'_'.$id.'cash_register';
$gTables['catmer'] = $table_prefix.'_'.$id.'catmer';
$gTables['caucon'] = $table_prefix.'_'.$id.'caucon';
$gTables['caumag'] = $table_prefix.'_'.$id.'caumag';
$gTables['clfoco'] = $table_prefix.'_'.$id.'clfoco';
$gTables['company_config'] = $table_prefix.'_'.$id.'config';
$gTables['effett'] = $table_prefix.'_'.$id.'effett';
$gTables['imball'] = $table_prefix.'_'.$id.'imball';
$gTables['letter'] = $table_prefix.'_'.$id.'letter';
$gTables['movmag'] = $table_prefix.'_'.$id.'movmag';
$gTables['pagame'] = $table_prefix.'_'.$id.'pagame';
$gTables['paymov'] = $table_prefix.'_'.$id.'paymov';
$gTables['portos'] = $table_prefix.'_'.$id.'portos';
$gTables['provvigioni'] = $table_prefix.'_'.$id.'provvigioni';
$gTables['rigbro'] = $table_prefix.'_'.$id.'rigbro';
$gTables['rigdoc'] = $table_prefix.'_'.$id.'rigdoc';
$gTables['rigmoc'] = $table_prefix.'_'.$id.'rigmoc';
$gTables['rigmoi'] = $table_prefix.'_'.$id.'rigmoi';
$gTables['spediz'] = $table_prefix.'_'.$id.'spediz';
$gTables['tesbro'] = $table_prefix.'_'.$id.'tesbro';
$gTables['tesdoc'] = $table_prefix.'_'.$id.'tesdoc';
$gTables['tesmov'] = $table_prefix.'_'.$id.'tesmov';
$gTables['vettor'] = $table_prefix.'_'.$id.'vettor';
?>