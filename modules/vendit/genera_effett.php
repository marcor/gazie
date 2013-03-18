<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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
require("../../library/include/calsca.inc.php");
$msg = '';


function getDocumentsBill($upd=false)
{
    global $gTables,$admin_aziend;
    $from =  $gTables['tesdoc'].' AS tesdoc
             LEFT JOIN '.$gTables['pagame'].' AS pay
             ON tesdoc.pagame=pay.codice
             LEFT JOIN '.$gTables['clfoco'].' AS customer
             ON tesdoc.clfoco=customer.codice
             LEFT JOIN '.$gTables['anagra'].' AS anagraf
             ON anagraf.id=customer.id_anagra';
    $where = "(tippag = 'B' OR tippag = 'T' OR tippag = 'V') AND geneff = '' AND tipdoc LIKE 'FA_'";
    $orderby = "datfat ASC, protoc ASC";
    $result = gaz_dbi_dyn_query('tesdoc.*,
                        pay.tippag,pay.numrat,pay.tipdec,pay.giodec,pay.tiprat,pay.mesesc,pay.giosuc,
                        customer.codice, customer.speban AS addebitospese,
                        CONCAT(anagraf.ragso1,\' \',anagraf.ragso2) AS ragsoc,CONCAT(anagraf.citspe,\' (\',anagraf.prospe,\')\') AS citta',
                        $from,$where,$orderby);
    $doc=array();
    $ctrlp=0;
    while ($tes = gaz_dbi_fetch_array($result)) {
           //il numero di protocollo contiene anche l'anno nei primi 4 numeri
           $year_prot=intval(substr($tes['datfat'],0,4))*1000000+$tes['protoc'];
           if ($year_prot <> $ctrlp) { // la prima testata della fattura
                $carry=0;
                $somma_spese=0;
                $cast_vat=array();
                $totimp_decalc=0.00;
                $n_vat_decalc=0;
                $totimpdoc=0;
                $spese_incasso=0;
                $rit=0;
                if (($tes['tippag'] == 'B' ||
                    $tes['tippag'] == 'T' ||
                    $tes['tippag'] == 'V') && $tes['addebitospese'] == 'S' ) {
                    $spese_incasso=$tes['numrat']*$tes['speban'] ;
                } elseif ($tes['tippag'] == 'R') { // il pagamento prevede una imposta di bollo fissa
                    if (!isset($cast_vat[$admin_aziend['ivabol']]['import'])) {
                        $cast_vat[$admin_aziend['ivabol']]['periva'] = 0;
                        $cast_vat[$admin_aziend['ivabol']]['import'] = $tes['stamp'];
                    } else {
                        $cast_vat[$admin_aziend['ivabol']]['import'] += $tes['stamp'];
                    }
                }
           } else {
                $spese_incasso=0;
           }
           //recupero i dati righi per creare il castelletto
           $from =  $gTables['rigdoc'].' AS rows
                    LEFT JOIN '.$gTables['aliiva'].' AS vat
                    ON rows.codvat=vat.codice';
           $rs_rig = gaz_dbi_dyn_query('rows.*,vat.tipiva AS tipiva',$from, "rows.id_tes = ".$tes['id_tes'],"id_tes DESC");
           while ($r = gaz_dbi_fetch_array($rs_rig)) {
              if ($r['tiprig'] <= 1) {//ma solo se del tipo normale o forfait
                 //calcolo importo rigo
                 $importo = CalcolaImportoRigo($r['quanti'],$r['prelis'],array($r['sconto'],$tes['sconto']));
                 if ($r['tiprig'] == 1) {
                    $importo = CalcolaImportoRigo(1,$r['prelis'], $tes['sconto']);
                 }
                 //creo il castelletto IVA
                 if (!isset($cast_vat[$r['codvat']]['import'])) {
                    $cast_vat[$r['codvat']]['import']=0;
                    $cast_vat[$r['codvat']]['periva']=$r['pervat'];
                    $cast_vat[$r['codvat']]['tipiva']=$r['tipiva'];
                 }
                 $cast_vat[$r['codvat']]['import']+=$importo;
                 if ($r['tipiva']!='C' && $r['tipiva']!='S' ) {
                     $totimp_decalc += $importo;
                     $n_vat_decalc++;
                 }
                 $totimpdoc += $importo;
                 $rit+=round($importo*$r['ritenuta']/100,2);
              } elseif($r['tiprig'] == 3) {
                 $carry += $r['prelis'] ;
              }
           }
           $doc[$year_prot]['tes']=$tes;
           $doc[$year_prot]['car']=$carry;
           $doc[$year_prot]['rit']=$rit;
           $ctrlp=$year_prot;
           // aggiungo i valori della testata al castelletto IVA
           $new_cast_vat=array();
           $somma_spese += $tes['traspo'] + $spese_incasso + $tes['spevar'];
           $last=count($cast_vat);
           $acc_val=$somma_spese;
           foreach ($cast_vat as $k=> $v) {
                   if ($v['tipiva']!='C' && $v['tipiva']!='S' ) {
                      if ($last == 1) {
                         $v['import'] += $acc_val;
                         $totimpdoc += $acc_val;
                      } else {
                         $decalc=round($somma_spese*$v['import']/$totimp_decalc,2);
                         $v['import'] += $decalc;
                         $totimpdoc += $decalc;
                         $acc_val-=$decalc;
                      }
                      $last--;
                   }
                   $new_cast_vat[$k]['import']=$v['import'];
                   $new_cast_vat[$k]['periva']=$v['periva'];
                   $new_cast_vat[$k]['tipiva']=$v['tipiva'];
           }
           $doc[$year_prot]['vat']=$new_cast_vat;
           // fine aggiunta spese non documentate al castelletto IVA
           if ($upd) {
            gaz_dbi_query ("UPDATE ".$gTables['tesdoc']." SET geneff = 'S' WHERE id_tes = ".$tes['id_tes'].";");
           }
    }
    return $doc;
}

function getReceiptNumber($date)
{
    global $gTables;
    $where = "tipeff = 'B' AND YEAR(datemi) = ".substr($date,0,4);
    $orderby = "datemi DESC, progre DESC";
    $result = gaz_dbi_dyn_query('*',$gTables['effett'],$where,$orderby,0,1);
    $last = gaz_dbi_fetch_array($result);
    $first['R'] = 1+$last['progre'];
    $where = "tipeff = 'T' AND YEAR(datemi) = ".substr($date,0,4);
    $orderby = "datemi DESC, progre DESC";
    $result = gaz_dbi_dyn_query('*',$gTables['effett'],$where,$orderby,0,1);
    $last = gaz_dbi_fetch_array($result);
    $first['T'] = 1+$last['progre'];
    $where = "tipeff = 'V' AND YEAR(datemi) = ".substr($date,0,4);
    $orderby = "datemi DESC, progre DESC";
    $result = gaz_dbi_dyn_query('*',$gTables['effett'],$where,$orderby,0,1);
    $last = gaz_dbi_fetch_array($result);
    $first['V'] = 1+$last['progre'];
    return $first;
}

function computeTot($data,$carry,$stamp_percent=false,$round=5)
{
   $vat=0;
   $tax=0;
   $sta=0;
   foreach($data as $k=>$v) {
          $tax += $v['import'];
          $vat += round($v['import']*$v['periva'])/ 100;
   }
   $tot=$vat+$tax;
   if ($stamp_percent) { // è stata passata la percentuale
          $v_stamp = new Compute;
          $sta = $v_stamp->stampTax($tot+$carry,$stamp_percent,$round);
          $tot+=$sta;
   } else {
          $tot+=$carry;
   }
   return array('taxable'=>$tax,'vat'=>$vat,'stamp'=>$sta,'tot'=>$tot);
}


if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {    // accessi successivi
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    $form['ritorno'] = $_POST['ritorno'];
    if (isset($_POST['submit'])&& empty($msg)) {   //confermo la generazione
       $rs=getDocumentsBill(1);
       if (count($rs>0)) {
          $ctrl_date='';
          foreach($rs as $k=>$v) {
             if($ctrl_date <> substr($v['tes']['datemi'],0,4)) {
               $n=getReceiptNumber($v['tes']['datemi']);
             }
             // calcolo i totali
             $stamp=false;
             $round=0;
             if($v['tes']['tippag']=='T') {
                $stamp=$v['tes']['stamp'];
                $round=$v['tes']['numrat']*$v['tes']['round_stamp'];
             }
             $tot=computeTot($v['vat'],$v['car']-$v['rit'],$stamp,$round);
             //fine calcolo totali
             $rate = CalcolaScadenze($tot['tot'],substr($v['tes']['datfat'],8,2),substr($v['tes']['datfat'],5,2),substr($v['tes']['datfat'],0,4),$v['tes']['tipdec'],$v['tes']['giodec'],$v['tes']['numrat'],$v['tes']['tiprat'],$v['tes']['mesesc'],$v['tes']['giosuc']);
             $tot_doc = $tot['tot'];
             if ($tot['tot'] > 0) {
                foreach($rate['import'] as $k_r=>$v_r) {
                       $v['tes']['tipeff']=$v['tes']['tippag'];
                       $n_type=$v['tes']['tippag'];
                       print_r($v['tes']);
                       if ($n_type == 'B'){
                           $n_type = 'R';
                       }
                       $v['tes']['datemi']=$v['tes']['datfat'];
                       $v['tes']['progre']=$n[$n_type];
                       $tot_doc = round($tot_doc-$v_r,2);
                       $v['tes']['totfat']=$tot['tot'];
                       $v['tes']['salacc']='C';
                       if ($tot_doc == 0) {
                            $v['tes']['salacc']='S';
                       }
                       $v['tes']['impeff']=$v_r;
                       $v['tes']['scaden']=$rate['anno'][$k_r].'-'.$rate['mese'][$k_r].'-'.$rate['giorno'][$k_r];
                       $v['tes']['id_doc']=$v['tes']['id_tes'];
                       $v['tes']['id_con']=0;
                       effettInsert($v['tes']);
                       $n[$n_type]++;
                }
             }
             $ctrl_date=substr($v['tes']['datfat'],0,4);
          }
          header("Location: ".$form['ritorno']);
          exit;
       } else {
          $msg .= "1+";
       }
    }
}

require("../../library/include/header.php");
$script_transl=HeadMain(0);
echo "<form method=\"POST\" name=\"create\">\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\" />\n";
$gForm = new GAzieForm();
echo "<br /><div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title'];
echo "</div>\n";
echo "<table class=\"Tsmall\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
}
echo "\t<tr class=\"FacetDataTD\">\n";
echo "\t<td class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"return\" value=\"".
     $script_transl['return']."\"></td>\n";
echo '<td align="right"><input type="submit" name="preview" value="';
echo $script_transl['view'];
echo '">';
echo "\t </td>\n";
echo "\t </tr>\n";
echo "</table>\n";

//mostro l'anteprima
if (isset($_POST['preview'])) {
   $rs=getDocumentsBill();
   echo "<BR /><div align=\"center\"><b>".$script_transl['preview']."</b></div>";
   echo "<table class=\"Tlarge\">";
   echo "<th class=\"FacetFieldCaptionTD\">".$script_transl['date_reg']."</th>
         <th class=\"FacetFieldCaptionTD\">".$script_transl['protoc']."</th>
         <th class=\"FacetFieldCaptionTD\">".$script_transl['doc_type']."</th>
         <th class=\"FacetFieldCaptionTD\">N.</th>
         <th class=\"FacetFieldCaptionTD\">".$script_transl['customer']."</th>
         <th class=\"FacetFieldCaptionTD\">".$script_transl['tot']."</th>\n";
   $ctrl_date='';
   $tot_type=array('B'=>0,'T'=>0,'V'=>0);
   foreach($rs as $k=>$v) {
         if($ctrl_date <> substr($v['tes']['datfat'],0,4)) {
            $n=getReceiptNumber($v['tes']['datfat']);
         }
         // calcolo i totali
         $stamp=false;
         $round=0;
         if($v['tes']['tippag']=='T') {
            $stamp=$v['tes']['stamp'];
            $round=$v['tes']['numrat']*$v['tes']['round_stamp'];
         }
         $tot=computeTot($v['vat'],$v['car']-$v['rit'],$stamp,$round);
         //fine calcolo totali
         echo "<tr class=\"FacetDataTD\">
               <td align=\"center\">".gaz_format_date($v['tes']['datfat'])."</td>
               <td align=\"center\">".$v['tes']['protoc'].'/'.$v['tes']['seziva']."</td>
               <td>".$script_transl['doc_type_value'][$v['tes']['tipdoc']]."</td>
               <td>".$v['tes']['numfat']."</td>
               <td>".$v['tes']['ragsoc']."</td>
               <td align=\"right\">".gaz_format_number($tot['tot'])."</td>
               </tr>\n";
               $rate = CalcolaScadenze($tot['tot'],substr($v['tes']['datfat'],8,2),substr($v['tes']['datfat'],5,2),substr($v['tes']['datfat'],0,4),$v['tes']['tipdec'],$v['tes']['giodec'],$v['tes']['numrat'],$v['tes']['tiprat'],$v['tes']['mesesc'],$v['tes']['giosuc']);
               foreach($rate['import'] as $k_r=>$v_r) {
                       $n_type=$v['tes']['tippag'];
                       $tot_type[$v['tes']['tippag']]+=$v_r;
                       if ($n_type == 'B'){
                           $n_type = 'R';
                       }
                       echo "<tr>";
                       echo "</td>
                       <td align=\"right\" colspan=\"6\">";
                       echo $script_transl['gen'].$script_transl['type_value'][$v['tes']['tippag']].
                            ' n.'.$n[$n_type].' '.$script_transl['end'].$rate['giorno'][$k_r].'-'.$rate['mese'][$k_r].'-'.$rate['anno'][$k_r].
                            ' '.$admin_aziend['symbol'];
                       echo "</td>
                       <td align=\"right\">";
                       echo gaz_format_number($v_r);
                       echo "</td>
                       </tr>\n";
                       $n[$n_type]++;
               }
         $ctrl_date=substr($v['tes']['datfat'],0,4);
   }
   if (count($rs) > 0) {
       foreach ($tot_type as $k_t=>$v_t) {
              if ( $v_t>0 ) {
                 echo "\t<tr class=\"FacetFieldCaptionTD\">\n";
                 echo '<td colspan="6" align="right">';
                 echo $script_transl['total_value'][$k_t];
                 echo "</td>
                       <td align=\"right\">";
                 echo $admin_aziend['symbol'].' '.gaz_format_number($v_t);
                 echo "\t </td>\n";
                 echo "\t </tr>\n";
              }
      }
      echo "\t<tr class=\"FacetFieldCaptionTD\">\n";
      echo '<td colspan="7" align="right"><input type="submit" name="submit" value="';
      echo $script_transl['submit'];
      echo '">';
      echo "\t </td>\n";
      echo "\t </tr>\n";
   } else {
      echo "\t<tr>\n";
      echo '<td colspan="7" align="center" class="FacetDataTDred">';
      echo $script_transl['errors'][0];
      echo "\t </td>\n";
      echo "\t </tr>\n";
   }
}
?>
</form>
</body>
</html>