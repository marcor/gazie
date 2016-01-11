<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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

require("../../library/include/electronic_invoice.inc.php");


// recupero i dati
if (isset($_GET['id_tes'])) {   //se viene richiesta la stampa di un solo documento attraverso il suo id_tes
    $id_testata = intval($_GET['id_tes']);
    $testata = gaz_dbi_get_row($gTables['tesdoc'], 'id_tes', $id_testata);
    $si=$testata['seziva'];
    $yr=substr($testata['datfat'],0,4);
    $pr=$testata['protoc'];
//    create_XML_invoice($testata, $gTables);
//    exit;
} else { // in tutti gli altri casi devo passare i valori su $_GET
   if (!isset($_GET['protoc']) || !isset($_GET['year']) || !isset($_GET['seziva'])) {
      header("Location: report_docven.php");
      exit;
   } else {
    $si=intval($_GET['seziva']);
    $yr=intval($_GET['year']);
    $pr=intval($_GET['protoc']);
   }
}
//recupero i dati
$testate = gaz_dbi_dyn_query("*", $gTables['tesdoc'],"tipdoc LIKE 'F__' AND seziva = $si AND YEAR(datfat) = $yr AND protoc = ".$pr,'datemi ASC, numdoc ASC, id_tes ASC');
create_XML_invoice($testate,$gTables);
?>