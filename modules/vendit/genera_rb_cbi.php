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

$admin_aziend = checkAdmin();
if (!isset($_GET['ristam']) or ! isset($_GET['banacc']) or ! isset($_GET['scaini']) or ! isset($_GET['scafin']) or ! isset($_GET['proini']) or ! isset($_GET['profin'])) {
    header("Location: report_effett.php");
    exit;
}
if ($_GET['ristam'] <> 'S') {
    $ristampa = $gTables['effett'] . ".status <> 'DISTINTATO' AND ";
} else {
    $ristampa = "(banacc = '" . $_GET['banacc'] . "' OR banacc = 0) AND ";
}
require("../../library/include/riba_cbi.inc.php");

$anagrafica = new Anagrafica();
$contoAccredito = $anagrafica->getPartner(intval($_GET['banacc']));
$countryData = gaz_dbi_get_row($gTables['country'], "iso", $contoAccredito['country']);
$bancaAccredito = gaz_dbi_get_row($gTables['banapp'], "codice", $contoAccredito['banapp']);
if (isset($_GET['datemi'])) {
    $dataemissione = substr($_GET['datemi'], 8, 2) . substr($_GET['datemi'], 5, 2) . substr($_GET['datemi'], 2, 2);
} else {
    $dataemissione = date("dmy");
}
// creo il file di back up con il nome ottenuto in precedenza.
$filename = "RIBAdel$dataemissione.cbi";
$where = $ristampa . " tipeff = 'B' AND scaden BETWEEN '" . $_GET['scaini'] . "' AND '" . $_GET['scafin'] . "' AND progre BETWEEN '" . $_GET['proini'] . "' AND '" . $_GET['profin'] . "' ";
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query("*", $gTables['effett'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['effett'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['anagra'] . ".id = " . $gTables['clfoco'] . ".id_anagra LEFT JOIN " . $gTables['banapp'] . " ON " . $gTables['effett'] . ".banapp = " . $gTables['banapp'] . ".codice", $where, "tipeff ASC,scaden ASC, id_tes ASC");
//C.F. o P.I. creditore
if (empty($admin_aziend['pariva'])) {
    $codfis = $admin_aziend['codfis'];
} else {
    $codfis = $admin_aziend['pariva'];
}
$arrayTestata = array($bancaAccredito['codabi'],
    $bancaAccredito['codcab'],
    substr($contoAccredito['iban'], $countryData['account_number_pos'] - 1, $countryData['account_number_lenght']),
    $dataemissione,
    "SC_" . substr($_GET['scaini'], 8, 2) . "." . substr($_GET['scaini'], 5, 2) . "." . substr($_GET['scaini'], 2, 2) . "-" . substr($_GET['scafin'], 8, 2) . "." . substr($_GET['scafin'], 5, 2) . "." . substr($_GET['scafin'], 2, 2),
    "E",
    $admin_aziend['ragso1'],
    $admin_aziend['ragso2'],
    $admin_aziend['indspe'],
    $admin_aziend['capspe'] . " " . $admin_aziend['citspe'] . " " . $admin_aziend['prospe'],
    $codfis,
    $contoAccredito['sia_code']);
if (isset($_GET['eof'])) {
    $arrayTestata[12] = 1;
}
$arrayRiba = array();
while ($row = gaz_dbi_fetch_array($result)) {
    //C.F. o P.I. debitore
    if (empty($row['pariva'])) {
        $codfis = $row['codfis'];
    } else {
        $codfis = $row['pariva'];
    }
    // a saldo o in acconto
    if ($row['salacc'] == "S") {
        $descrizione_debito = "SALDO FT." . $row['numfat'] . "/" . $row['seziva'] . " DEL " . substr($row['datfat'], 8, 2) . "/" . substr($row['datfat'], 5, 2) . "/" . substr($row['datfat'], 2, 2);
    } else {
        $descrizione_debito = "ACCONTO FT." . $row['numfat'] . "/" . $row['seziva'] . " DEL " . substr($row['datfat'], 8, 2) . "/" . substr($row['datfat'], 5, 2) . "/" . substr($row['datfat'], 2, 2);
    }
    $arrayRiba[] = array($row['progre'],
        substr($row['scaden'], 8, 2) . substr($row['scaden'], 5, 2) . substr($row['scaden'], 2, 2),
        $row['impeff'] * 100,
        $row['ragso1'] . $row['ragso2'],
        $codfis,
        $row['indspe'],
        $row['capspe'],
        $row['citspe'],
        $row['codabi'],
        $row['codcab'],
        $row['descri'] . " " . $row['locali'] . " " . $row['codpro'],
        $row['clfoco'],
        $descrizione_debito,
        $row['prospe'],
        $row['cigcup']);
    //aggiorno il db solo se non &egrave; una ristampa
    if ($row["status"] <> 'DISTINTATO') {
        gaz_dbi_put_row($gTables['effett'], "id_tes", $row["id_tes"], "status", 'DISTINTATO');
        gaz_dbi_put_row($gTables['effett'], "id_tes", $row["id_tes"], "banacc", intval($_GET['banacc']));
    }
}
$RB = new RibaAbiCbi();
// Impostazione degli header per l'opozione "save as" dello standard input che verr� generato
header('Content-Type: text/x-cbi');
header("Content-Disposition: attachment; filename=" . $filename);
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // per poter ripetere l'operazione di back-up pi� volte.
if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
} else {
    header('Pragma: no-cache');
}
print $RB->creaFile($arrayTestata, $arrayRiba);
exit;
?>