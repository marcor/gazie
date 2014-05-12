<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

require("../../library/include/electronic_invoice.inc.php");
require("../../library/include/calsca.inc.php");

// recupero i dati
if (isset($_GET['id_tes'])) {   //se viene richiesta la stampa di un solo documento attraverso il suo id_tes
   $id_testata = intval($_GET['id_tes']);
   $testata = gaz_dbi_get_row($gTables['tesdoc'], 'id_tes', $id_testata);

} else { // in tutti gli altri casi devo passare i valori su $_GET
   if (!isset($_GET['protoc']) || !isset($_GET['year']) || !isset($_GET['seziva'])) {
      header("Location: report_docven.php");
      exit;
   }
}
//recupero i dati
$testate = gaz_dbi_dyn_query("*", $gTables['tesdoc'],"seziva = ".intval($_GET['seziva'])." AND
                                                     YEAR(datfat) = ".intval($_GET['year'])." AND
                                                     protoc = ".intval($_GET['protoc']));
create_XML_invoice($testate,$template,$gTables);
?>