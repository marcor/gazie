<?php
/* $Id: invsta_broacq.php,v 1.19 2011/01/01 11:07:17 devincen Exp $
 -----------------------------------------------------------------------
                         Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                          (www.devincentiis.it)
                      <http://gazie.sourceforge.net>
 -----------------------------------------------------------------------
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
 -----------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();

if (isset($_SESSION['print_request'])){
    $id_tes = $_SESSION['print_request'];
    unset ($_SESSION['print_request']);
    $result = gaz_dbi_dyn_query("*", $gTables['tesbro'], "id_tes = '$id_tes' and status = 'GENERATO'","id_tes desc");
    $documento = gaz_dbi_fetch_array($result);
    $TipoDocumento = array ("AOR" => "l'Ordine a Fornitore","APR" => "il Preveventivo d'Acquisto");
    if ($documento['numdoc'] > 0) {
        echo "<HTML><HEAD><TITLE>Wait for PDF</TITLE>\n";
        echo "<script type=\"text/javascript\">\n";
        if ($documento['tipdoc'] == 'AOR') {
            echo "setTimeout(\"window.location='stampa_ordfor.php?id_tes=".$documento['id_tes']."'\",1000)\n";
        } else {
            echo "setTimeout(\"window.location='stampa_prefor.php?id_tes=".$documento['id_tes']."'\",1000)\n";
        }
        echo "</script></HEAD>\n<BODY><DIV align=\"center\">Wait for PDF</DIV></BODY></HTML>";
    } else {
        header("Location:report_broacq.php");
        exit;
    }
} else {
    header("Location:report_broacq.php");
    exit;
}
?>