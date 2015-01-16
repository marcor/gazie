<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
$gForm = new magazzForm;
$msg='';

if (!isset($_POST['ritorno'])) { //al primo accesso allo script
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['date_Y'] = date("Y");
    $form['date_M'] = date("m");
    $form['date_D'] = date("d");
    $rs_first = gaz_dbi_dyn_query("codice", $gTables['catmer'],1,"codice ASC",0,1);
    $cm_first = gaz_dbi_fetch_array($rs_first);
    $form['catmer'] = $cm_first['codice'];
    $utsdate= mktime(0,0,0,$form['date_M'],$form['date_D'],$form['date_Y']);
    $date = date("Y-m-d",$utsdate);
    $result = gaz_dbi_dyn_query($gTables['artico'].'.*, '.$gTables['catmer'].'.descri AS descat,'.$gTables['catmer'].'.annota AS anncat', $gTables['artico'].' LEFT JOIN '.$gTables['catmer'].' ON catmer = '.$gTables['catmer'].'.codice', "catmer = ".$form["catmer"],'catmer ASC, '.$gTables['artico'].'.codice ASC');
    if ($result) {
    	// Imposto totale valore giacenza by DF
    	$tot_val_giac = 0;
        while ($r = gaz_dbi_fetch_array($result)) {
              $mv=$gForm->getStockValue(false,$r['codice'],$date,null,$admin_aziend['decimal_price']); 
              $magval=array_pop($mv);
              $form['a'][$r['codice']]['i_d'] = $r['descri'];
              $form['a'][$r['codice']]['i_u'] = $r['unimis'];
              $form['a'][$r['codice']]['v_a'] = $magval['v'];
              $form['a'][$r['codice']]['v_r'] = $magval['v'];
              $form['a'][$r['codice']]['i_a'] = $r['annota'];
              $form['a'][$r['codice']]['i_g'] = $r['catmer'];
              $form['a'][$r['codice']]['g_d'] = $r['descat'];
              $form['a'][$r['codice']]['g_a'] = $magval['q_g'];
              $form['a'][$r['codice']]['g_r'] = $magval['q_g'];
              $form['a'][$r['codice']]['v_g'] = $magval['v_g'];
              $form['vac_on'.$r['codice']] = '';
              if ($magval['q_g'] < 0 ){
                 $form['chk_on'.$r['codice']] = ' checked ';
                 $form['a'][$r['codice']]['col'] = 'red';
              } elseif ($magval['q_g']>0) {
                 $form['chk_on'.$r['codice']] = ' checked ';
                 $form['a'][$r['codice']]['col'] = '';
              } else {
                 $form['chk_on'.$r['codice']] = '';
                 $form['a'][$r['codice']]['col'] = '';
              }
              
              // Calcolo totale valore giacenza by DF
              $tot_val_giac += $magval['v_g'];
        }
    }
} else { //nelle  successive entrate
    if (isset($_POST['Return'])) {
        header("Location: ".$_POST['ritorno']);
        exit;
    }
    $form['date_Y'] = intval($_POST['date_Y']);
    $form['date_M'] = intval($_POST['date_M']);
    $form['date_D'] = intval($_POST['date_D']);
    $form['catmer'] = intval($_POST['catmer']);
    if ($_POST['hidden_req'] == 'catmer' || $_POST['hidden_req'] == 'date') {
      $utsdate= mktime(0,0,0,$form['date_M'],$form['date_D'],$form['date_Y']);
      $date = date("Y-m-d",$utsdate);
      $where="catmer = ".$form["catmer"];
      if ($form['catmer']==100){
        $where=1;
      }
      $ctrl_cm=0;
      $result = gaz_dbi_dyn_query($gTables['artico'].'.*, '.$gTables['catmer'].'.descri AS descat,'.$gTables['catmer'].'.annota AS anncat', $gTables['artico'].' LEFT JOIN '.$gTables['catmer'].' ON catmer = '.$gTables['catmer'].'.codice', $where,'catmer ASC, '.$gTables['artico'].'.codice ASC');
      if ($result) {
      	// Imposto totale valore giacenza by DF
      	$tot_val_giac = 0;
         while ($r = gaz_dbi_fetch_array($result)) {
           if ($r['catmer']<>$ctrl_cm ){
             gaz_set_time_limit (30);
             $ctrl_cm=$r['catmer'];
           }
           $mv=$gForm->getStockValue(false,$r['codice'],$date,null,$admin_aziend['decimal_price']); 
           $magval=array_pop($mv);
           $form['a'][$r['codice']]['i_d'] = $r['descri'];
           $form['a'][$r['codice']]['i_u'] = $r['unimis'];
           $form['a'][$r['codice']]['v_a'] = $magval['v'];
           $form['a'][$r['codice']]['v_r'] = $magval['v'];
           $form['a'][$r['codice']]['i_a'] = $r['annota'];
           $form['a'][$r['codice']]['i_g'] = $r['catmer'];
           $form['a'][$r['codice']]['g_d'] = $r['descat'];
           $form['a'][$r['codice']]['g_r'] = $magval['q_g'];
           $form['a'][$r['codice']]['g_a'] = $magval['q_g'];
           $form['a'][$r['codice']]['v_g'] = $magval['v_g'];
           $form['vac_on'.$r['codice']] = '';
           if ($magval['q_g'] < 0 ){
                 $form['chk_on'.$r['codice']] = ' checked ';
                 $form['a'][$r['codice']]['col'] = 'red';
           } elseif ($magval['q_g']>0) {
                 $form['chk_on'.$r['codice']] = ' checked ';
                 $form['a'][$r['codice']]['col'] = '';
           } else {
                 $form['chk_on'.$r['codice']] = '';
                 $form['a'][$r['codice']]['col'] = '';
           }
           // Calcolo totale valore giacenza by DF
           $tot_val_giac += $magval['v_g'];
         }
      }
    } elseif (isset($_POST['preview']) || isset($_POST['insert'])) {  //in caso di conferma
        $cau99 = gaz_dbi_get_row($gTables['caumag'],'codice',99);
        $cau98 = gaz_dbi_get_row($gTables['caumag'],'codice',98);
        $form['date_Y'] = $_POST['date_Y'];
        $form['date_M'] = $_POST['date_M'];
        $form['date_D'] = $_POST['date_D'];
        $form['catmer'] = $_POST['catmer'];
        foreach ($_POST as $k=>$v) { //controllo sui dati inseriti e flaggati
           if ($k=='a') {
             foreach ($v as $ka=>$va) { // ciclo delle singole righe (a)
                 $form['chk_on'.$ka] = '';
                 if (isset($_POST['chk'.$ka])) { // se l'articolo e' da inventariare lo controllo
                    $form['chk_on'.$ka] = ' checked ';
                    if ($va['g_r']<0) {
                       $msg .= $ka.'-0+';
                    } elseif($va['g_r']==0 && $va['g_a']==0) { //inutile fare l'inventario di una cosa che non c'era e non c'e'
                       $msg .= $ka.'-2+';
                    }
                    if ($va['v_r']<=0) {
                       $msg .= $ka.'-1+';
                    }
                 }
                 $form['vac_on'.$ka] = '';
                 if (isset($_POST['vac'.$ka])) $form['vac_on'.$ka] = ' checked ';
                 $form['a'][$ka]['i_d'] = substr($va['i_d'],0,30);
                 $form['a'][$ka]['i_u'] = substr($va['i_u'],0,3);
                 $form['a'][$ka]['v_a'] = gaz_format_quantity($va['v_a'],0,$admin_aziend['decimal_price']);
                 $form['a'][$ka]['v_r'] = gaz_format_quantity($va['v_r'],0,$admin_aziend['decimal_price']);
                 $form['a'][$ka]['i_a'] = $va['i_a'];
                 $form['a'][$ka]['i_g'] = $va['i_g'];
                 $form['a'][$ka]['g_d'] = $va['g_d'];
                 $form['a'][$ka]['g_r'] = $va['g_r'];
                 $form['a'][$ka]['g_a'] = gaz_format_quantity($va['g_a'],0,$admin_aziend['decimal_quantity']);
                 $form['a'][$ka]['v_g'] = gaz_format_quantity($va['v_g'],0,$admin_aziend['decimal_price']);
                 $form['a'][$ka]['col'] = $va['col'];
             }
           }
        }
        if (isset($_POST['insert']) && empty($msg)) { // se devo inserire e non ho errori rifaccio il ciclo dei righi per inserire i movimenti
             foreach ($form['a'] as $k=>$v) { // ciclo delle singole righe (a)
               if ($form['chk_on'.$k] == ' checked ') {   // e' un rigo da movimentare
                 if ($v['g_a']>$v['g_r']) { // in caso di giacenza reale minore
                    // devo fare prima uno storno per scaricare
                    $mq=$v['g_a']-$v['g_r'];
                    movmagInsert(array('caumag'=>98,
                                       'operat'=>-1,
                                       'datreg'=>$form['date_Y'].'-'.$form['date_M'].'-'.$form['date_D'],
                                       'tipdoc'=>'INV',
                                       'desdoc'=>$cau98['descri'],
                                       'datdoc'=>$form['date_Y'].'-'.$form['date_M'].'-'.$form['date_D'],
                                       'artico'=>$k,
                                       'quanti'=>$mq,
                                       'prezzo'=>$v['v_r']
                                        ));
                 } elseif ($v['g_a']<$v['g_r']) { // se maggiore carico
                    // devo fare prima uno storno per caricare
                    $mq=$v['g_r']-$v['g_a'];
                    movmagInsert(array('caumag'=>98,
                                       'operat'=>1,
                                       'datreg'=>$form['date_Y'].'-'.$form['date_M'].'-'.$form['date_D'],
                                       'tipdoc'=>'INV',
                                       'desdoc'=>$cau98['descri'],
                                       'datdoc'=>$form['date_Y'].'-'.$form['date_M'].'-'.$form['date_D'],
                                       'artico'=>$k,
                                       'quanti'=>$mq,
                                       'prezzo'=>$v['v_r'],
                                        ));
                 }
                 // inserisco il rigo con causale 99
                 movmagInsert(array('caumag'=>99,
                                    'operat'=>1,
                                    'datreg'=>$form['date_Y'].'-'.$form['date_M'].'-'.$form['date_D'],
                                    'tipdoc'=>'INV',
                                    'desdoc'=>$cau99['descri'],
                                    'datdoc'=>$form['date_Y'].'-'.$form['date_M'].'-'.$form['date_D'],
                                    'artico'=>$k,
                                    'quanti'=>$v['g_r'],
                                    'prezzo'=>$v['v_r'],
                                     ));
               }
             }
             header("Location: report_movmag.php");
             exit;
        }
    }
}
require("../../library/include/header.php");
$script_transl=HeadMain(0,array('jquery/jquery-1.3.2.min','boxover/boxover'));
?>
<SCRIPT LANGUAGE="JavaScript" ID="datapopup">
function toggle(boxID, toggleID) {
  var box = document.getElementById(boxID);
  var toggle = document.getElementById(toggleID);
  updateToggle = box.checked ? toggle.disabled=false : toggle.disabled=true;
}

$( function() {
  $( '.checkAll' ).live( 'change', function() {
    $( '.jq_chk' ).attr( 'checked', $( this ).is( ':checked' ) ? 'checked' : '' );
  });
  $( '.invertSelection' ).live( 'click', function() {
    $( '.jq_chk' ).each( function() {
      $( this ).attr( 'checked', $( this ).is( ':checked' ) ? '' : 'checked' );
    }).trigger( 'change' );
  });
});
</SCRIPT>
<?php
echo "<form method=\"POST\" name=\"maschera\">\n";
echo "<input type=\"hidden\" name=\"hidden_req\" value=\"\" />";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"".$_POST['ritorno']."\" />";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".ucfirst($script_transl['title'])." ". $script_transl['del'];
$gForm->Calendar('date',$form['date_D'],$form['date_M'],$form['date_Y'],'FacetSelect','date');
echo $script_transl['catmer'];
$gForm->selectFromDB('catmer','catmer','codice',$form['catmer'],false,false,'-','descri','catmer','FacetSelect',array('value'=>100,'descri'=>'*** '.$script_transl['all'].' ***'));
echo "</div>\n";
echo "<table class=\"Tlarge\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="9" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
}
echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['select']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['code']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['descri']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['mu']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['v_a']."</td>
         <td align=\"right\" class=\"FacetFieldCaptionTD\">".$script_transl['v_r']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['g_a']."</td>
         <td align=\"right\" class=\"FacetFieldCaptionTD\">".$script_transl['g_r']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['g_v']."</td>
         </tr>\n";
$ctrl_cm=0;
if (isset($form['a'])) {
   $elem_n=0;
   foreach($form['a'] as $k=>$v) {
        //ini default value
        $title = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$v['i_a']."] body=[<center><img src='../root/view.php?table=artico&value=".$k."'>] fade=[on] fadespeed=[0.03] \"";
        $class= ' class="FacetDataTD'.$v['col'].'" ';
        // end default value
        if ($ctrl_cm <> $v['i_g']) {
            $cm_title = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$v['g_d']."] body=[<center><img src='../root/view.php?table=catmer&value=".$v['i_g']."'>] fade=[on] fadespeed=[0.03] \"";
            echo "<input type=\"hidden\" value=\"".$v['g_d']."\" name=\"a[$k][g_d]\">\n";
            echo "<tr>\n";
            if ($ctrl_cm == 0) {
                echo "<td><input type=\"checkbox\" class=\"checkAll\" title=\"".$script_transl['selall']."\" /><br /><a href=\"javascript:void(0);\" class=\"invertSelection\" title=\"".$script_transl['invsel']."\" > <img src=\"../../library/images/recy.gif\" width=\"14\" border=\"0\"/></a></td>";
            } else {
                echo "<td></td>";
            }echo "<td $cm_title class=\"FacetFieldCaptionTD\" colspan=\"8\" align=\"left\">".$v['i_g'].' - '.$v['g_d']."</td>\n";
            echo "</tr>\n";
        }

        echo "<input type=\"hidden\" value=\"".$v['i_a']."\" name=\"a[$k][i_a]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['col']."\" name=\"a[$k][col]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['i_g']."\" name=\"a[$k][i_g]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['g_d']."\" name=\"a[$k][g_d]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['i_d']."\" name=\"a[$k][i_d]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['i_u']."\" name=\"a[$k][i_u]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['v_a']."\" name=\"a[$k][v_a]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['v_r']."\" name=\"a[$k][v_r]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['g_a']."\" name=\"a[$k][g_a]\">\n";
        echo "<input type=\"hidden\" value=\"".$v['v_g']."\" name=\"a[$k][v_g]\">\n";

        echo "<tr>\n";
        echo "<td class=\"FacetFieldCaptionTD\" align=\"center\">\n
             <input class=\"jq_chk\" name=\"chk$k\" ".$form['chk_on'.$k]." type=\"checkbox\" /></td>\n";
        echo "<td $title $class align=\"left\">".$k."</td>\n";
        echo "<td $title $class align=\"left\">".$v['i_d']."</td>\n";
        echo "<td $class align=\"center\">".$v['i_u']."</td>\n";
        echo "<td $class align=\"center\" align=\"right\">".gaz_format_quantity($v['v_a'],0,$admin_aziend['decimal_price'])."</td>\n";
        echo "<td $class align=\"right\">
              <input id=\"vac$k\" name=\"vac$k\" ".$form['vac_on'.$k]." onClick=\"toggle('vac$k', 'a[$k][v_r]')\" type=\"checkbox\" />
              <input type=\"text\" size=\"10\" style=\"text-align:right\" onchange=\"document.maschera.chk$k.checked=true\" id=\"a[$k][v_r]\" name=\"a[$k][v_r]\" value=\"".gaz_format_quantity($v['v_r'],0,$admin_aziend['decimal_price'])."\" disabled ></td>\n";
        echo "<td $class align=\"center\" align=\"right\">".gaz_format_quantity($v['g_a'],0,$admin_aziend['decimal_quantity'])."</td>\n";
        echo "<td $class align=\"right\"><input type=\"text\" style=\"text-align:right\" onchange=\"document.maschera.chk$k.checked=true\" name=\"a[$k][g_r]\" value=\"".$v['g_r']."\"></td>\n";
        echo "<td $class align=\"center\" align=\"right\">".gaz_format_quantity($v['v_g'],0,$admin_aziend['decimal_price'])."</td>\n";
        echo "</tr>\n";
        $ctrl_cm = $v['i_g'];
        $elem_n++;
   }
   echo "<tr>
      <td  colspan=\"2\" class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">&nbsp;</td>
      <td align=\"center\" colspan=\"6\" class=\"FacetFooterTD\"><input type=\"submit\" name=\"preview\" value=\"".$script_transl['view']."!\">&nbsp;</td>
      <td align=\"center\" class=\"FacetFormHeaderFont\">Tot. ".gaz_format_number($tot_val_giac)."</td>
      </tr>\n";
   if (isset($_POST['preview']) && empty($msg)) { // e' possibile confermare, non i sono errori formali
       echo "</table><table class=\"Tlarge\">\n";
       echo "<tr><td colspan=\"8\" class=\"FacetFormHeaderFont\">".$script_transl['preview_title']."</td></tr>\n";
       echo "<tr><td class=\"FacetFieldCaptionTD\"></td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['code']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['descri']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['mu']."</td>
         <td align=\"right\" class=\"FacetFieldCaptionTD\">".$script_transl['load']."</td>
         <td align=\"right\" class=\"FacetFieldCaptionTD\">".$script_transl['unload']."</td>
         <td align=\"right\" class=\"FacetFieldCaptionTD\">".$script_transl['v_r']."</td>
         <td class=\"FacetFieldCaptionTD\">".$script_transl['value']."</td>
         </tr>\n";
       foreach ($form['a'] as $k=>$v) { // ciclo delle singole righe (a)
         if ($form['chk_on'.$k] == ' checked ') {   // e' un rigo da movimentare
           $load='';
           $unload='';
           if ($v['g_a']>$v['g_r']) { // in caso di giacenza reale minore
             // devo fare prima uno storno per scaricare
             $mq=$v['g_a']-$v['g_r'];
             echo "<tr>\n";
             echo "<td class=\"FacetDataTD\">98-".$cau98['descri']."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"left\">".$k."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"left\">".$v['i_d']."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"left\">".$v['i_u']."</td>\n";
             echo "<td class=\"FacetDataTD\"></td>\n";
             echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_quantity($mq,0,$admin_aziend['decimal_quantity'])."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"right\">".$v['v_r']."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_quantity($v['v_r']*$mq,0,$admin_aziend['decimal_price'])."</td>\n";
             echo "</tr>\n";

           } elseif ($v['g_a']<$v['g_r']) { // se maggiore carico
             // devo fare prima uno storno per caricare
             $mq=$v['g_r']-$v['g_a'];
             echo "<tr>\n";
             echo "<td class=\"FacetDataTD\">98-".$cau98['descri']."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"left\">".$k."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"left\">".$v['i_d']."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"left\">".$v['i_u']."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_quantity($mq,0,$admin_aziend['decimal_quantity'])."</td>\n";
             echo "<td class=\"FacetDataTD\"></td>\n";
             echo "<td class=\"FacetDataTD\" align=\"right\">".$v['v_r']."</td>\n";
             echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_quantity($v['v_r']*$mq,0,$admin_aziend['decimal_price'])."</td>\n";
             echo "</tr>\n";
           }
           echo "<tr>\n";
           echo "<td class=\"FacetDataTD\">99-".$cau99['descri']."</td>\n";
           echo "<td class=\"FacetDataTD\" align=\"left\">".$k."</td>\n";
           echo "<td class=\"FacetDataTD\" align=\"left\">".$v['i_d']."</td>\n";
           echo "<td class=\"FacetDataTD\" align=\"left\">".$v['i_u']."</td>\n";
           echo "<td class=\"FacetDataTD\" align=\"right\">".$v['g_r']."</td>\n";
           echo "<td class=\"FacetDataTD\"></td>\n";
           echo "<td class=\"FacetDataTD\" align=\"right\">".$v['v_r']."</td>\n";
           echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_quantity($v['v_r']*$v['g_r'],0,$admin_aziend['decimal_price'])."</td>\n";
           echo "</tr>\n";
         }
       }
       echo "<tr><td align=\"right\" colspan=\"8\" class=\"FacetFooterTD\"><input type=\"submit\" name=\"insert\" value=\"".$script_transl['submit']."!\">&nbsp;</td></tr>\n";
   }
} else {
   echo "<tr>
      <td colspan=\"9\" class=\"FacetDataTDred\">".$script_transl['noitem']."</td>
      </tr>\n";

}
echo "</table>\n";
?>
</form>
</body>
</html>