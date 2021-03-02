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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

require("../../library/include/document.php");
$tesbro = gaz_dbi_get_row($gTables['tesbro'],"id_tes", intval($_GET['id_tes']));

$lang = false;
$id_anagra = gaz_dbi_get_row($gTables['clfoco'], 'codice', $tesbro['clfoco']);
$stato = gaz_dbi_get_row($gTables['anagra'], 'id', $id_anagra['id_anagra']);
if ($stato AND $stato['country'] !== "IT"){
    $lang = 'english';
	}

if ($tesbro['tipdoc']=='VOR' || $tesbro['tipdoc']=='VOG') {
	$type=false;
	$template='OrdineCliente';
    if (isset($_GET['dest'])&& $_GET['dest']=='E' ){ // se l'utente vuole inviare una mail
		$type='E';
    } 
	if (isset($_GET['lh'])){ // se l'utente vuole che venga stampata su una carta intestata
		$type='H';
	}
	if ($tesbro['template']=='Ticket'){
		$template='Ticket';
	}
    createDocument($tesbro,$template,$gTables,'rigbro',$type,$lang);
} elseif ($tesbro['tipdoc']=='VOW'){ 
    createDocument($tesbro, 'OrdineWeb',$gTables,'rigbro',$type,$lang);
} else {
    header("Location: report_broven.php");
    exit;
}
?>