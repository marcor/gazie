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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
require("./lang.".$admin_aziend['lang'].".php");
$script_transl = $strScript["select_comopril.php"];
if (!isset($_GET['min_limit']) or
    !isset($_GET['anno'])) {
    header("Location: select_elencf.php");
    exit;
}
require("../../library/include/check.inc.php");

function getDocRef($data){
    global $gTables;
    $r='';
    switch ($data['caucon']) {
        case "FAI":
        case "FND":
        case "FNC":
            $tesdoc_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],
                                                "id_con = ".$data["id_tes"],
                                                'id_tes DESC',0,1);
            $tesdoc_r = gaz_dbi_fetch_array ($tesdoc_result);
            if ($tesdoc_r) {
                $r="../vendit/stampa_docven.php?id_tes=".$tesdoc_r["id_tes"];
            }
        break;
        case "FAD":
            $tesdoc_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],
                                                "tipdoc = \"".$data["caucon"]."\" AND seziva = ".$data["seziva"]." AND protoc = ".$data["protoc"]." AND numfat = '".$data["numdoc"]."' AND datfat = \"".$data["datdoc"]."\"",
                                                'id_tes DESC');
            $tesdoc_r = gaz_dbi_fetch_array ($tesdoc_result);
            if ($tesdoc_r) {
                $r="../vendit/stampa_docven.php?td=2&si=".$tesdoc_r["seziva"]."&pi=".$tesdoc_r['protoc']."&pf=".$tesdoc_r['protoc']."&di=".$tesdoc_r["datfat"]."&df=".$tesdoc_r["datfat"] ;
            }
        break;
        case "RIB":
        case "TRA":
            $effett_result = gaz_dbi_dyn_query ('*',$gTables['effett'],"id_con = ".$data["id_tes"],'id_tes',0,1);
            $effett_r = gaz_dbi_fetch_array ($effett_result);
            if ($effett_r) {
                $r="../vendit/stampa_effett.php?id_tes=".$effett_r["id_tes"];
            }
        break;
    }
    return $r;
}

function createRowsAndErrors($min_limit){
    global $gTables,$admin_aziend,$script_transl;
    $sqlquery= "SELECT ".$gTables['rigmoi'].".*, ragso1,ragso2,sedleg,sexper,indspe,
               citspe,prospe,country,codfis,pariva,clfoco,protoc,numdoc,datdoc,seziva,caucon,datreg,op_type,datnas,luonas,pronas,counas,
               operat, SUM(impost - impost*2*(caucon LIKE '_NC')) AS imposta,".$gTables['rigmoi'].".id_tes AS idtes,
               SUM(imponi - imponi*2*(caucon LIKE '_NC')) AS imponibile FROM ".$gTables['rigmoi']."
               LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoi'].".id_tes = ".$gTables['tesmov'].".id_tes
               LEFT JOIN ".$gTables['aliiva']." ON ".$gTables['rigmoi'].".codiva = ".$gTables['aliiva'].".codice
               LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesmov'].".clfoco = ".$gTables['clfoco'].".codice
               LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra
               WHERE YEAR(datdoc) = ".intval($_GET['anno'])." AND ( clfoco LIKE '".$admin_aziend['masfor']."%' OR clfoco LIKE '".$admin_aziend['mascli']."%')
               GROUP BY id_tes, tipiva
               ORDER BY regiva, datreg";
    $result = gaz_dbi_query($sqlquery);
    $castel_transact= array();
    $error_transact= array();
    if (gaz_dbi_num_rows($result) > 0 ) {
       // inizio creazione array righi ed errori
       $progressivo = 0;
       $ctrl_id = 0;
       $value_imponi = 0.00;
       $value_impost = 0.00;
       while ($row = gaz_dbi_fetch_array($result)) {
         if ($row['operat'] == 1) {
                $value_imponi = $row['imponibile'];
                $value_impost = $row['imposta'];
         } elseif ($row['operat'] == 2) {
                $value_imponi = -$row['imponibile'];
                $value_impost = -$row['imposta'];
         } else {
                $value_imponi = 0;
                $value_impost = 0;
         }
         if ($ctrl_id <> $row['idtes']) {
            // se il precedente movimento non ha raggiunto l'importo lo elimino
            if (isset($castel_transact[$ctrl_id]) && $castel_transact[$ctrl_id]['operazioni_imponibili'] < $min_limit ) {
               unset ($castel_transact[$ctrl_id]);
               unset ($error_transact[$ctrl_id]);
            }
               // inizio controlli su CF e PI
               $nuw = new check_VATno_TAXcode();
               $resultpi = $nuw->check_VAT_reg_no($row['pariva']);
               if ($admin_aziend['country'] != $row['country']) { // è uno non residente (caso 3)
                     if (!empty($row['datnas'])) { // è un persona fisica straniera
                        if (empty($row['pronas']) || empty($row['luonas']) || empty($row['counas'])) {
                            $error_transact[$row['idtes']][] = $script_transl['errors'][9];
                        }
                     }                
               } elseif (empty($resultpi) && !empty($row['pariva'])) { // ha la partita IVA ed è giusta (caso 2) 
                 if( strlen(trim($row['codfis'])) == 11) { // è una persona giuridica
                     $resultcf = $nuw->check_VAT_reg_no($row['codfis']);
                     if (intval($row['codfis']) == 0) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][1];
                     } elseif ($row['sexper'] != 'G') {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][2];
                     }
                 } else {           // è una una persona fisica
                     $resultcf = $nuw->check_TAXcode($row['codfis']);
                     if (empty($row['codfis'])) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][3];
                     } elseif ($row['sexper'] == 'G' and empty($resultcf)) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][4];
                     } elseif ($row['sexper'] == 'M' and empty($resultcf) and
                         (intval(substr($row['codfis'],9,2)) > 31 or
                         intval(substr($row['codfis'],9,2)) < 1) ) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][5];
                     } elseif ($row['sexper'] == 'F' and empty($resultcf) and
                         (intval(substr($row['codfis'],9,2)) > 71 or
                         intval(substr($row['codfis'],9,2)) < 41) ) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][6];
                     } elseif (! empty ($resultcf)) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][7];
                     }
                 }
               } else {        // è un soggetto con codice fiscale senza partita IVA (caso 1)
                     $resultcf = $nuw->check_TAXcode($row['codfis']);
                     if (empty($row['codfis'])) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][3];
                     } elseif ($row['sexper'] == 'G' and empty($resultcf)) {
                        $error_transact[$row['idtes']][] = $script_transl['errors'][4];
                     } elseif ($row['sexper'] == 'M' and empty($resultcf) and
                         (intval(substr($row['codfis'],9,2)) > 31 or
                         intval(substr($row['codfis'],9,2)) < 1) ) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][5];
                     } elseif ($row['sexper'] == 'F' and empty($resultcf) and
                         (intval(substr($row['codfis'],9,2)) > 71 or
                         intval(substr($row['codfis'],9,2)) < 41) ) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][6];
                     } elseif (!empty ($resultcf)) {
                         $error_transact[$row['idtes']][] = $script_transl['errors'][7];
                     }
               }
                 // fine controlli su CF e PI

                 $castel_transact[$row['idtes']] = $row;

                 if ($row['pariva'] >0){
                        $castel_transact[$row['idtes']]['soggetto_type'] = 2;
                 } elseif ($admin_aziend['country'] != $row['country']){
                        $castel_transact[$row['idtes']]['soggetto_type'] = 3;
                 } else {
                        $castel_transact[$row['idtes']]['soggetto_type'] = 1;
                 }
                 if ($row['op_type'] == 0 && substr($row['clfoco'],0,3) == $admin_aziend['masfor'] ){
                     $castel_transact[$row['idtes']]['op_type'] = 3;
                 } elseif ($row['op_type'] == 0 && substr($row['clfoco'],0,3) == $admin_aziend['mascli'] ) {
                     $castel_transact[$row['idtes']]['op_type'] = 1;
                 }
                 if (!empty($row['sedleg'])){
                     if ( preg_match("/([\w\,\.\s]+)([0-9]{5})[\s]+([\w\s\']+)\(([\w]{2})\)/",$row['sedleg'],$regs)) {
                        $castel_transact[$row['idtes']]['Indirizzo'] = $regs[1];
                        $castel_transact[$row['idtes']]['Comune'] = $regs[3];
                        $castel_transact[$row['idtes']]['Provincia'] = $regs[4];
                     } else {
                       $error_transact[$row['idtes']][] = $script_transl['errors'][10];
                     }
                 }
                 // inizio valorizzazione imponibile,imposta,senza_iva,art8
                 $castel_transact[$row['idtes']]['operazioni_imponibili'] = 0;
                 $castel_transact[$row['idtes']]['imposte_addebitate'] = 0;
                 $castel_transact[$row['idtes']]['operazioni_esente'] = 0;
                 $castel_transact[$row['idtes']]['operazioni_nonimp'] = 0;
                 $castel_transact[$row['idtes']]['tipiva'] = 1;
                 switch ($row['tipiva']) {
                        case 'I':
                        case 'D':
                             $castel_transact[$row['idtes']]['operazioni_imponibili'] = $value_imponi;
                             $castel_transact[$row['idtes']]['imposte_addebitate'] = $value_impost;
                             if ($value_impost == 0){  //se non c'è imposta il movimento è sbagliato
                                $error_transact[$row['idtes']][] = $script_transl['errors'][11];
                             }
                        break;
                        case 'E':
                             $castel_transact[$row['idtes']]['tipiva'] = 3;
                             $castel_transact[$row['idtes']]['operazioni_esente'] = $value_imponi;
                             if ($value_impost != 0){  //se c'è imposta il movimento è sbagliato
                                $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                             }
                        break;
                        case 'N':
                        //case 'C':
                             $castel_transact[$row['idtes']]['tipiva'] = 2;
                             $castel_transact[$row['idtes']]['operazioni_nonimp'] = $value_imponi;
                             if ($value_impost != 0){  //se c'è imposta il movimento è sbagliato
                                $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                             }
                        break;
                 }
              } else { //movimenti successivi al primo ma dello stesso id
                 // inizio addiziona valori imponibile,imposta,esente,non imponibile
                 switch ($row['tipiva']) {
                        case 'I':
                        case 'D':
                             $castel_transact[$row['idtes']]['operazioni_imponibili'] += $value_imponi;
                             $castel_transact[$row['idtes']]['imposte_addebitate'] += $value_impost;
                             if ($value_impost == 0){  //se non c'è imposta il movimento è sbagliato
                                $error_transact[$row['idtes']][] = $script_transl['errors'][11];
                             }
                        break;
                        case 'E':
                             $castel_transact[$row['idtes']]['operazioni_esente'] = $value_imponi;
                             if ($value_impost != 0){  //se c'è imposta il movimento è sbagliato
                                $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                             }
                        break;
                        case 'N':
                        //case 'C':
                             $castel_transact[$row['idtes']]['operazioni_nonimp'] = $value_imponi;
                             if ($value_impost != 0){  //se c'è imposta il movimento è sbagliato
                                $error_transact[$row['idtes']][] = $script_transl['errors'][12];
                             }
                        break;
                 }
                 // fine addiziona valori imponibile,imposta,esente,non imponibile
              }
              // fine valorizzazione imponibile,imposta,esente,non imponibile
              $ctrl_id = $row['idtes'];

       }
       // se il precedente movimento non ha raggiunto l'importo lo elimino
       if (isset($castel_transact[$ctrl_id]) && $castel_transact[$ctrl_id]['operazioni_imponibili'] < $min_limit ) {
           unset ($castel_transact[$ctrl_id]);
           unset ($error_transact[$ctrl_id]);
       }
    } else {
              $error_transact[0] = $script_transl['errors'][15];
    }
    // fine creazione array righi ed errori
    return array($castel_transact,$error_transact);
}

require("../../config/templates/report_template.php");

$pdf = new Report_template();

$title = array('title'=>$script_transl['title'].' - '.intval($_GET['anno']),
               'hile'=>array(array('lun' => 18,'nam'=>'N/Data'),
                             array('lun' => 70,'nam'=>$script_transl['soggetto'].'/'.$script_transl['pariva']),
                             array('lun' => 33,'nam'=>$script_transl['amount']),
                             array('lun' => 33,'nam'=>$script_transl['tax']),
                             array('lun' => 33,'nam'=>$script_transl['op_type'])
                            )
              );

$pdf->setVars($admin_aziend,$title);
$pdf->setAuthor($admin_aziend['ragso1'].' '.$_SESSION['Login']);
$pdf->setTitle($title['title']);
$pdf->SetTopMargin(43);
$pdf->SetFooterMargin(20);
//$pdf->StartPageGroup();
$pdf->AddPage();

if ($_GET['anno'] >2000 && $_GET['min_limit'] > 0){
    $queryData = createRowsAndErrors(intval($_GET['min_limit']));
    foreach ($queryData[0] as $key=>$value ) {
      $totale = gaz_format_number($value['operazioni_imponibili']+$value['imposte_addebitate']+$value['operazioni_nonimp']+$value['operazioni_esente']);
      $docref=getDocRef($value);
      if (!empty($docref)){
         $docref=$value['caucon']." N.".$value['numdoc']." date ".gaz_format_date($value['datdoc']);
      }
      $pdf->SetFont('helvetica','',7);
      $pdf->Cell(18,3,$value['id_tes'],'LTR',0,'R');
      $pdf->Cell(70,3,$value['ragso1'].' '.$value['ragso2'],'T');
      $pdf->Cell(33,3,$docref,'T');
      $pdf->Cell(33,3,$script_transl['soggetto_type_value'][$value['soggetto_type']],'T');
      $pdf->Cell(33,3,$script_transl['op_type_value'][$value['op_type']],'TR',1,'C');
      $pdf->Cell(18,3,gaz_format_date($value['datreg']),'LB');
      $pdf->Cell(70,3,$value['codfis']." ".$value['pariva'],'B');
      $pdf->Cell(33,3,$totale,'B');
      $pdf->Cell(33,3,gaz_format_number($value['imposte_addebitate']),'B',0,'C');
      $pdf->Cell(33,3,$script_transl['imptype_value'][$value['tipiva']],'B',1,'R');
    }
}

$pdf->Output();
?>