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
$admin_aziend = checkAdmin();
require("../../library/include/document.php");

function createCertificate($testata, $gTables,$id_movmag=0, $dest = false) {
    $config = new Config;
    $configTemplate = new configTemplate;
    require_once ("../../config/templates" . ($configTemplate->template ? '.' . $configTemplate->template : '') . '/certificate.php');
    $pdf = new Certificate();
    $docVars = new DocContabVars();
    $docVars->setData($gTables, $testata, $testata['id_tes'],'rigdoc');
    $pdf->setVars($docVars,'Certificate');
    $pdf->setTesDoc();
    $pdf->setCreator('GAzie - ' . $docVars->intesta1);
    $pdf->setAuthor($docVars->user['Cognome'] . ' ' . $docVars->user['Nome']);
    $pdf->setTitle('Certificate');
    $pdf->setTopMargin(79);
    $pdf->setHeaderMargin(5);
    $pdf->Open();
    $pdf->pageHeader();
    $pdf->compose();
    $pdf->pageFooter();
    $doc_name = preg_replace("/[^a-zA-Z0-9]+/", "_", $docVars->intesta1 . '_' . $pdf->tipdoc) . '.pdf';
    if ($dest && $dest == 'E') { // è stata richiesta una e-mail
        $dest = 'S';     // Genero l'output pdf come stringa binaria
        // Costruisco oggetto con tutti i dati del file pdf da allegare
        $content = new StdClass;
        $content->name = $doc_name;
        $content->string = $pdf->Output($doc_name, $dest);
        $content->encoding = "base64";
        $content->mimeType = "application/pdf";
        $gMail = new GAzieMail();
        $gMail->sendMail($docVars->azienda, $docVars->user, $content, $docVars->client);
    } elseif ($dest && $dest == 'X') { // è stata richiesta una stringa da allegare
        $dest = 'S';     // Genero l'output pdf come stringa binaria
        // Costruisco oggetto con tutti i dati del file pdf
        $content->descri = $doc_name;
        $content->string = $pdf->Output($content->descri, $dest);
        $content->mimeType = "PDF";
        return ($content);
    } else { // va all'interno del browser
        $pdf->Output($doc_name);
    }
}

// recupero i dati
if (isset($_GET['id_movmag'])) {   //se viene richiesta la stampa di un solo documento attraverso il suo id_movmag
    $movmag = gaz_dbi_get_row($gTables['movmag'], 'id_mov', intval($_GET['id_movmag']));
    $rigdoc = gaz_dbi_get_row($gTables['rigdoc'], 'id_rig', $movmag['id_rif']);
    $tesdoc = gaz_dbi_get_row($gTables['tesdoc'], 'id_tes', $rigdoc['id_tes']);
    createCertificate($tesdoc, $gTables);
} else { // in tutti gli altri casi devo passare direttamente la testata del documento 
}
?>