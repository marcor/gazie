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
$msg = '';

require("../../library/include/check.inc.php");

if (!isset($_GET['anno'])) { //al primo accesso allo script suppongo che si debba produrre l'elenco per l'anno precedente
    $_GET['anno'] = date("Y")-1;
    $_GET['min_limit'] = 3000;
    if ($_GET['anno'] < 2011) {
      $_GET['min_limit'] = 25000;
    } else {
      $_GET['min_limit'] = 3000;
    }
    $Testa = getHeaderData();
    if ($admin_aziend['sexper'] != 'G') { // le persone fisiche hanno due campi separati
      $_GET['ragso1'] = $Testa['cognome'];
      $_GET['ragso2'] = $Testa['nome'];
    } else {
      $_GET['ragso1'] = strtoupper($admin_aziend['ragso1']);
      $_GET['ragso2'] = strtoupper($admin_aziend['ragso2']);
    }
}

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

function printTransact($transact,$error)
{
          global $script_transl,$admin_aziend;
          echo "<td align=\"center\" class=\"FacetDataTD\" >N.Mov.</td>";
          echo "<td class=\"FacetDataTD\" >".$script_transl['soggetto']."</td>";
          echo "<td align=\"center\" class=\"FacetDataTD\" >".$script_transl['sourcedoc']."</td>";
          echo "<td align=\"center\" class=\"FacetDataTD\" >".$script_transl['soggetto_type']."</td>";
          echo "<td align=\"center\" class=\"FacetDataTD\" >".$script_transl['op_type']."</td>";
          echo "</tr>\n";
          echo "<tr>";
          echo "<td align=\"center\" class=\"FacetDataTD\" >".$script_transl['op_date']."</td>";
          echo "<td align=\"center\" class=\"FacetDataTD\" >".$script_transl['codfis']." / ".$script_transl['pariva']."</td>";
          echo "<td align=\"right\" class=\"FacetDataTD\" >".$script_transl['amount']."</td>";
          echo "<td align=\"right\" class=\"FacetDataTD\" >".$script_transl['tax']."</td>";
          echo "<td align=\"center\" class=\"FacetDataTD\" >".$script_transl['imptype']."</td>";
          echo "</tr>\n";
          foreach ($transact as $key=>$value ) {
               $class = ' ';
               if ($value['soggetto_type']==3) {
                  $class = 'style="color:#4305F1; background-color: #FF8888;"';
                
               } elseif($value['soggetto_type']==1) {
                  $class = 'style="color:#0543A1; background-color: #88FF88;" ';
               } 
               if (isset($error[$key])){
                  $class = ' class="FacetDataTDred" ';
               }
               $totale = gaz_format_number($value['operazioni_imponibili']+$value['imposte_addebitate']+$value['operazioni_nonimp']+$value['operazioni_esente']);
               echo "<tr>";
               echo "<td align=\"center\" $class><a href=\"../contab/admin_movcon.php?id_tes=".$value['id_tes']."&Update\">".$value['id_tes']."</a></td>";
               echo "<td $class>".$value['ragso1'].' '.$value['ragso2']."</td>";
               echo "<td $class align=\"center\">";
               $docref=getDocRef($value);
               if (!empty($docref)){
                 echo "<a title=\" ".$value['caucon']." N.".$value['numdoc']." date ".gaz_format_date($value['datdoc'])."\" href=\"$docref\"><img src=\"../../library/images/stampa.gif\" border=\"0\"></a>";
               }
               echo "</td>";
               echo "<td align=\"center\" $class>".$script_transl['soggetto_type_value'][$value['soggetto_type']]."</td>";
               echo "<td align=\"center\" ";
               if ($value['op_type']>2) {
                  echo 'style="color:#000000; background-color: #AFC8D8;"';
               } else {
                  echo 'style="color:#000000; background-color: #B3CFA8;" ';
               } 
               echo ">".$script_transl['op_type_value'][$value['op_type']]."</td>";
               echo "</tr>\n";
               echo "<tr>";
               echo "<td align=\"center\" $class>".gaz_format_date($value['datreg'])."</td>";
               echo "<td $class>".$value['codfis']." ".$value['pariva']."</td>";
               echo "<td align=\"right\" $class>$totale</td>";
               echo "<td align=\"right\" $class>".gaz_format_number($value['imposte_addebitate'])."</td>";
               echo "<td align=\"center\" $class>".$script_transl['imptype_value'][$value['tipiva']]."</td>";
               echo "</tr>\n";
               if (isset($error[$key])) {
                  foreach ($error[$key] as $val_err ) {
                          echo "<tr>";
                          echo "<td class=\"FacetDataTDred\" colspan=\"10\">".$val_err;
                          if (substr($value['clfoco'],0,3) == $admin_aziend['mascli']) {
                             echo ", <a href='../vendit/admin_client";
                          } else {
                             echo ", <a href='../acquis/admin_fornit";
                          }
                          echo ".php?codice=".substr($value['clfoco'],3,6)."&Update' target='_NEW'>". $script_transl['errors'][0]."</a><br /></td>
                               </tr>\n";
                  }
               }
          }
}

function getHeaderData()
{
      global $admin_aziend;
      // preparo il nome dell'azienda e faccio i controlli di errore
      $Testa['anno'] = intval($_GET['anno']);
      $Testa['pariva'] = $admin_aziend['pariva'];
      $Testa['codfis'] = $admin_aziend['codfis'];
      if ($admin_aziend['sexper'] == 'G') {
         // persona giuridica
         if (strlen($Testa['codfis']) <> 11) {
            $Testa['fatal_error'] = 'codfis';
         }
         if (empty($admin_aziend['ragso1']) and empty($admin_aziend['ragso2'])) {
            $Testa['fatal_error'] = 'ragsoc';
         } else {
            $Testa['ragsoc'] = strtoupper($admin_aziend['ragso1']." ".$admin_aziend['ragso2']);
         }
         if (empty($admin_aziend['citspe'])) {
            $Testa['fatal_error'] = 'citspe';
         } else {
            $Testa['sedleg'] = strtoupper($admin_aziend['citspe']);
         }
         if (strlen(trim($admin_aziend['prospe'])) < 2) {
            $Testa['fatal_error'] = 'prospe';
         } else {
            $Testa['proleg'] = strtoupper($admin_aziend['prospe']);
         }
      } elseif ($admin_aziend['sexper'] == 'F' or $admin_aziend['sexper'] == 'M') {
        // persona fisica
        $gn=substr($Testa['codfis'],9,2);
        if (($admin_aziend['sexper'] == 'M' and ($gn < 1 or $gn > 31))
            or
           ($admin_aziend['sexper'] == 'F' and ($gn < 41 or $gn > 71))) {
            $Testa['fatal_error'] = 'sexper';
        }
        $Testa['sesso'] = strtoupper($admin_aziend['sexper']);
        if (!empty($admin_aziend['legrap'])) {
            // persona fisica con cognome e nome non separati nel campo legale rappresentante
            $Testa['cognome'] = '';
            $Testa['nome'] = '';
            $line = strtoupper($admin_aziend['legrap']);
            $nuova = explode(' ',chop($line));
            $lenght = count($nuova);
            $middle = intval(($lenght+1)/2);
            for( $i = 0; $i < $lenght; $i++ ) {
                 if ($i < $middle) {
                    $Testa['cognome'] .= $nuova[$i]." ";
                 } else {
                    $Testa['nome'] .= $nuova[$i]." ";
                 }
            }
        } elseif(!empty($admin_aziend['ragso1']) and !empty($admin_aziend['ragso2'])) {
            // persona fisica con cognome e nome separati tra ragso1 e ragso2
            $Testa['cognome'] = strtoupper($admin_aziend['ragso1']);
            $Testa['nome'] = strtoupper($admin_aziend['ragso2']);
        } else {
            $Testa['fatal_error'] = 'legrap';
        }
        if (empty($admin_aziend['luonas'])) {
                $Testa['fatal_error'] = 'luonas';
        } else {
            $Testa['luonas'] = strtoupper($admin_aziend['luonas']);
        }
        if (strlen(trim($admin_aziend['pronas'])) < 2) {
                $Testa['fatal_error'] = 'pronas';
        } else {
            $Testa['pronas'] = strtoupper($admin_aziend['pronas']);
        }
        $d=substr($admin_aziend['datnas'],8,2);
        $m=substr($admin_aziend['datnas'],5,2);
        $Y=substr($admin_aziend['datnas'],0,4);
        if (checkdate($m, $d, $Y)) {
            $Testa['datnas'] = $admin_aziend['datnas'];
        } else {
            $Testa['fatal_error'] = 'datnas';
        }
      } else {
        $Testa['fatal_error'] = 'nosexper';
      }
      return $Testa;
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

if (isset($_GET['pdf'])) {
    header("Location: stampa_comopril.php?anno=".$_GET['anno']."&min_limit=".$_GET['min_limit']);
    exit;
}

if (isset($_GET['file_agenzia'])) {
      $queryData = createRowsAndErrors(intval($_GET['min_limit']));
      require("../../library/include/agenzia_entrate.inc.php");
      $annofornitura = date("y");
      // --- preparo gli array da passare alla classe AgenziaEntrate a secondo della scelta effettuata
      $Testa = getHeaderData();
      $agenzia = new AgenziaEntrate;
      print '<br>testa: - ';
      print_r($Testa);
      print '<br>dati: - ';
      print_r($queryData);
/*
      // Impostazione degli header per l'opozione "save as" dello standard input che verrà generato
      header('Content-Type: text/x-a21');
      header("Content-Disposition: attachment; filename=".$admin_aziend['codfis'].'_'.$_GET['anno'].".a21");
      header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');// per poter ripetere l'operazione di back-up più volte.
      if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
         header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
         header('Pragma: public');
      } else {
         header('Pragma: no-cache');
      }
      $content = $agenzia->creaFileART21($Testa,$Dati);
      print $content;
      exit;*/
}

require("../../library/include/header.php");
$script_transl = HeadMain();
echo "<form method=\"GET\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".ucfirst($script_transl['title'])."</div>\n";
echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice( explode('+',chop($msg)),0,-1);
    foreach ($rsmsg as $value){
            $message .= $script_transl['error']."! -> ";
            $rsval = explode('-',chop($value));
            foreach ($rsval as $valmsg){
                    $message .= $script_transl[$valmsg]." ";
            }
            $message .= "<br />";
    }
    echo '<tr><td colspan="5" class="FacetDataTDred">'.$message."</td></tr>\n";
}
echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['limit']."</td>
     <td class=\"FacetDataTD\">\n";
echo "<input type=\"text\" name=\"min_limit\" value=\"".$_GET['min_limit']."\" align=\"right\" maxlength=\"10\" size=\"10\" /></td>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['ragso1']."</td>
     <td class=\"FacetDataTD\"><input type=\"text\" value=\"".$_GET['ragso1']."\" maxlength=\"50\" size=\"40\" name=\"ragso1\"></td></td></tr>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['ragso2']."</td>
     <td class=\"FacetDataTD\"><input type=\"text\" value=\"".$_GET['ragso2']."\" maxlength=\"50\" size=\"40\" name=\"ragso2\"></td></tr>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['year']."</td><td class=\"FacetDataTD\" colspan=\"3\">";
echo "\t <select name=\"anno\" class=\"FacetSelect\" >\n";
for( $counter =  date("Y")-10; $counter <=  date("Y")+10; $counter++ ){
    $selected = "";
    if($counter == $_GET['anno'])
            $selected = "selected";
    echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td></tr>\n";
echo "<tr>\n
     <td class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"Return\" value=\"".ucfirst($script_transl['return'])."\"></td>\n
     <td align=\"right\" class=\"FacetFooterTD\"><input type=\"submit\" name=\"view\" value=\"".ucfirst($script_transl['view'])."\"></td>\n
     </tr>\n";
echo "</table>\n";
if (isset($_GET['view'])) {
   $queryData = createRowsAndErrors(intval($_GET['min_limit']));
   $Testa = getHeaderData();
   if (!isset ($queryData[1][0])) { // nessun errore sulle impostazioni aziendali
       echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['view']."</div>";
       echo "<table class=\"Tlarge\">";
       echo "<tr>";
       echo "<td colspan=\"1\"></td>";
       echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['codfis']."</td>";
       echo "<td colspan=\"2\" class=\"FacetDataTD\">".$Testa['codfis']."</td>";
       echo "</tr>\n";
       echo "<tr>";
       echo "<td colspan=\"1\"></td>";
       echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['pariva']."</td>";
       echo "<td colspan=\"2\" class=\"FacetDataTD\">".$Testa['pariva']."</td>";
       echo "</tr>\n";
       echo "<tr>";
       echo "<td colspan=\"1\"></td>";
       echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['sex']."</td>";
       echo "<td colspan=\"2\" class=\"FacetDataTD\">".$admin_aziend['sexper']."</td>";
       if (!isset($Testa['sesso'])){ // è una persona giuridica
          echo "<tr>";
          echo "<td colspan=\"1\"></td>";
          echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['sedleg']."</td>";
          echo "<td colspan=\"2\" class=\"FacetDataTD\">".$Testa['sedleg']."</td>";
          echo "</tr>\n";
          echo "<tr>";
          echo "<td colspan=\"1\"></td>";
          echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['proleg']."</td>";
          echo "<td colspan=\"2\" class=\"FacetDataTD\">".$Testa['proleg']."</td>";
          echo "</tr>\n";
       } else {     // persona fisica
          echo "<tr>";
          echo "<td colspan=\"1\"></td>";
          echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['sex']."</td>";
          echo "<td colspan=\"2\" class=\"FacetDataTD\">".$Testa['sesso']."</td>";
          echo "</tr>\n";
          echo "<tr>";
          echo "<td colspan=\"1\"></td>";
          echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['datnas']."</td>";
          echo "<td colspan=\"2\" class=\"FacetDataTD\">".gaz_format_date($Testa['datnas'])."</td>";
          echo "</tr>\n";
          echo "<tr>";
          echo "<td colspan=\"1\"></td>";
          echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['luonas']."</td>";
          echo "<td colspan=\"2\" class=\"FacetDataTD\">".$Testa['luonas']."</td>";
          echo "</tr>\n";
          echo "<tr>";
          echo "<td colspan=\"1\"></td>";
          echo "<td colspan=\"1\" class=\"FacetFieldCaptionTD\">".$script_transl['pronas']."</td>";
          echo "<td colspan=\"2\" class=\"FacetDataTD\">".$Testa['pronas']."</td>";
          echo "</tr>\n";
       }
       if (!empty($queryData[1]) ){ // ci sono errori tra i movimenti
               echo "<tr>\n
                    <td class=\"FacetDataTDred\" colspan=\"5\">".$script_transl['errors'][13].":</td>
                    </tr>\n";
       } elseif (isset($Testa['fatal_error'])) {
              echo "<tr>\n
                   <td align=\"center\" class=\"FacetFieldCaptionTD\" colspan=\"2\"><input type=\"submit\" name=\"pdf\" value=\"PDF\"></td>\n
                   <td align=\"center\" class=\"FacetDataTDred\" colspan=\"3\">".$script_transl['errors'][15]."</td>\n
                   </tr>\n";
       } else {
              echo "<tr>\n
                   <td align=\"center\" class=\"FacetFieldCaptionTD\" colspan=\"2\"><input type=\"submit\" name=\"pdf\" value=\"PDF\"></td>\n
                   <td a7lign=\"center\" class=\"FacetFieldCaptionTD\" colspan=\"3\"><input type=\"submit\" name=\"file_agenzia\" value=\"File Internet (ART21)\"></td>\n
                   </tr>\n";
       }
       printTransact($queryData[0],$queryData[1]);
       echo "</table>";
   } else {
       echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$queryData[1][0]."</div>";
   }
}
?>
</form>
</body>
</html>