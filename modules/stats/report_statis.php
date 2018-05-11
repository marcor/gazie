<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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
$message = "";

$anno = date("Y");
if (!isset($_POST['annimp'])) { //al primo accesso allo script
     $form['annimp'] = $anno;
     $form['ordine'] = 0;
     $form['acqven'] = 0;
} else {
     $form['annimp'] = intval($_POST['annimp']);
     $form['ordine'] = $_POST['ordine'];
     $form['acqven'] = $_POST['acqven'];
}
if ($form['acqven'] == 0 ) {
     $where = "( tipdoc like 'F__' OR tipdoc = 'DDT' OR tipdoc = 'VCO' ) ";
} else {
     $where = " tipdoc like 'A__' ";
}
$sqlquery = 'SELECT datemi,'.$gTables['tesdoc'].'.clfoco,tiprig,codart,'.$gTables['rigdoc'].'.descri,'.$gTables['rigdoc'].'.unimis,catmer,quanti,prelis,'
            .$gTables['tesdoc'].'.sconto as scotes, '.$gTables['rigdoc'].'.sconto as scorig,'.
            $gTables['clfoco'].'.codice,'.$gTables['tesdoc'].'. id_tes, ragso1, ragso2 FROM '.$gTables['rigdoc'].' LEFT JOIN '.
            $gTables['tesdoc'].' ON '.$gTables['rigdoc'].'.id_tes = '.$gTables['tesdoc'].
            '.id_tes LEFT JOIN '.$gTables['artico'].' ON '.$gTables['rigdoc'].'.codart = '.
            $gTables['artico'].'.codice LEFT JOIN '.$gTables['clfoco'].' ON '.
            $gTables['tesdoc'].'.clfoco = '.$gTables['clfoco'].
            '.codice LEFT JOIN '.$gTables['anagra'].' ON '.
            $gTables['anagra'].'.id = '.$gTables['clfoco'].
            '.id_anagra WHERE YEAR(datemi) = '.$form['annimp'].' AND tiprig BETWEEN 0 AND 1 AND '.$where.
            'ORDER BY catmer, codart, datemi DESC';
$rs_documenti = gaz_dbi_query($sqlquery);
// preparo il castelletto delle vendite degli articoli partendo dai movimenti
$totali=array();
$totali['valore'] = 0;
$totali['quanti'] = 0;
$totali['max_valore'] = 0;
$totali['max_quanti'] = 0;
$totali['max_valcode'] = "";
$totali['max_quacode'] = "";
$castelletto_articoli = array();
while ($rigo_documenti = gaz_dbi_fetch_array($rs_documenti)) {
      if ($rigo_documenti['scotes'] > 0){
         if ($rigo_documenti['tiprig'] == 0){
             $valore = CalcolaImportoRigo(1, CalcolaImportoRigo($rigo_documenti['quanti'], $rigo_documenti['prelis'], $rigo_documenti['scorig']), $rigo_documenti['scotes']);
         } else {
             $valore = CalcolaImportoRigo(1, CalcolaImportoRigo(1, $rigo_documenti['prelis'], $rigo_documenti['scorig']), $rigo_documenti['scotes']);
         }

      } else {
         if ($rigo_documenti['tiprig'] == 0){
            $valore = CalcolaImportoRigo($rigo_documenti['quanti'], $rigo_documenti['prelis'], $rigo_documenti['scorig']);
         } else {
            $valore = CalcolaImportoRigo(1, $rigo_documenti['prelis'], $rigo_documenti['scorig']);
         }
      }
      $totali['valore'] += $valore;
      $totali['quanti'] += $rigo_documenti['quanti'];
      if (!isset($castelletto_articoli[$rigo_documenti['codart']])) {
          $castelletto_articoli[$rigo_documenti['codart']] = array('catmer'=>$rigo_documenti['catmer'],
                                                                      'descri'=>$rigo_documenti['descri'],
                                                                      'unimis'=>$rigo_documenti['unimis'],
                                                                      'quanti'=>$rigo_documenti['quanti'],
                                                                      'valore'=>$valore,
                                                                      'numven'=>1,
                                                                      'id_tes'=>$rigo_documenti['id_tes'],
                                                                      'ragso1'=>$rigo_documenti['ragso1']." ".$rigo_documenti['ragso2'],
                                                                      'ultven'=>substr($rigo_documenti['datemi'],8,2).
                                                                            ".".substr($rigo_documenti['datemi'],5,2).
                                                                            ".".substr($rigo_documenti['datemi'],0,4));
      } else {
          $castelletto_articoli[$rigo_documenti['codart']]['quanti'] += $rigo_documenti['quanti'];
          $castelletto_articoli[$rigo_documenti['codart']]['valore'] += $valore;
          $castelletto_articoli[$rigo_documenti['codart']]['numven'] ++;
      }
      if ($castelletto_articoli[$rigo_documenti['codart']]['quanti'] > $totali['max_quanti']){
          $totali['max_quanti'] = $castelletto_articoli[$rigo_documenti['codart']]['quanti'];
          $totali['max_quacode'] = $rigo_documenti['codart'];
      }
      if ($castelletto_articoli[$rigo_documenti['codart']]['valore'] > $totali['max_valore']){
          $totali['max_valore'] = $castelletto_articoli[$rigo_documenti['codart']]['valore'];
          $totali['max_valcode'] = $rigo_documenti['codart'];
      }
}
if ($form['ordine'] == 1) {
   foreach ($castelletto_articoli as $key=>$value) {
        $indicizzato[$key] = $value['quanti'];
   }
   @array_multisort($indicizzato, SORT_DESC, $castelletto_articoli);
} elseif ($form['ordine'] == 2) {
   foreach ($castelletto_articoli as $key=>$value) {
        $indicizzato[$key] = $value['valore'];
   }
   @array_multisort($indicizzato, SORT_DESC, $castelletto_articoli);
}
$color=array( "000000","000099","0000FF","990000","990099","9900FF","FF0000","FF0099",
              "FF00FF","009900","009999","0099FF","999900","999999","9999FF","FF9900",
              "FF9999","FF99FF","00FF00","00FF99","00FFFF","99FF00","99FF99","99FFFF",
              "FFFF00","FFFF99","FFFFFF");
shuffle($color);
require("../../library/include/header.php");
$script_transl = HeadMain();
echo "<form method=\"POST\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".ucfirst($script_transl[0]);
echo "<select name=\"acqven\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter = 0; $counter <= 1; $counter++ ) {
     $i = $counter + 1;
     $selected = "";
     if($counter == $form['acqven']) $selected = "selected";
     echo "\t <option value=\"$counter\"  $selected >".$script_transl[$i]."</option>\n";
}
echo "\t </select>".$script_transl[9];
echo "<select name=\"annimp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter = $anno-10; $counter <= $anno+10; $counter++ ) {
    $selected = "";
    if($counter == $form['annimp'])
            $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select> ".$script_transl[4];
echo " <select name=\"ordine\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter = 0; $counter <= 2; $counter++ ) {
     $i = $counter + 10;
     $selected = "";
     if($counter == $form['ordine']) $selected = "selected";
     echo "\t <option value=\"$counter\"  $selected >".$script_transl[$i]."</option>\n";
}
echo "\t </select></div>\n";
echo "</form>";
echo "<table border=\"0\" align=\"center\" bgcolor=\"white\">";
$i=0;
foreach ($castelletto_articoli as $key=>$value) {
        if ($key == ""){
              $value['descri'] = "--- $script_transl[13] ---";
              $value['unimis'] = "";
        }
        if ($i==27) $i=0;
        if ($form['ordine'] == 2) {
           $val_width = intval($value['valore']*400/$totali['max_valore']);
           $val_unimis = $admin_aziend['symbol'];
           $val_quanti = gaz_format_number($value['valore']);
           $val_title = $script_transl[11].$value['unimis']." ".intval($value['quanti']);
        } else {
           $val_width = intval($value['quanti']*400/$totali['max_quanti']);
           $val_unimis = $value['unimis'];
           $val_quanti = intval($value['quanti']);
           $val_title = $script_transl[12].gaz_format_number($value['valore']);
        }
        if ($form['acqven'] == 1) {
         $val_acqven = '../acquis/admin_docacq';
         $script_transl[8] = $script_transl[7];
        } else {
         $val_acqven = '../vendit/admin_docven';
        }
        echo "<tr valign=\"middle\"><td><a href=\"$val_acqven.php?Update&id_tes=".$value['id_tes'].
             "\" class=\"FacetText\" title=\"".$script_transl[8].$value['ultven'].$script_transl[6].$value['ragso1']."\">".$value['descri'].
             "</a></td><td title=\"$val_title\"><img src=\"../../library/include/gif1pixel.php?color=$color[$i]\" width=\"$val_width\" height=\"8\" border=\"1\" />
             $key</td><td align=\"right\"></td><td>\n";
        if ($admin_aziend['conmag'] > 0) {
             echo "<a href=\"select_schart.php?id=$key&di=0101".
                  $form['annimp']."&df=3112".$form['annimp'].
                  "\">$val_unimis</a></td><td align=\"right\"".
                  "<a href=\"select_schart.php?id=$key&di=0101".
                  $form['annimp']."&df=3112".$form['annimp'].
                  "\">$val_quanti</a></td></tr>\n";
        } else {
             echo $val_unimis."</td><td align=\"right\">$val_quanti</td></tr>\n";
        }
        $i++;
}
echo "</table>";
?>
<?php
require("../../library/include/footer.php");
?>