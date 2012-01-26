<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2012 - Antonio De Vincentiis Montesilvano (PE)
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
$msg = '';

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    $form=gaz_dbi_parse_post('artico');
    $form['ritorno'] = $_POST['ritorno'];
    $form['ref_code']= substr($_POST['ref_code'],0,15);
    // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
    $form["preacq"] = number_format($form['preacq'],$admin_aziend['decimal_price'],'.','');
    $form["preve1"] = number_format($form['preve1'],$admin_aziend['decimal_price'],'.','');
    $form["preve2"] = number_format($form['preve2'],$admin_aziend['decimal_price'],'.','');
    $form["preve3"] = number_format($form['preve3'],$admin_aziend['decimal_price'],'.','');
    $form["web_price"] = number_format($form['web_price'],$admin_aziend['decimal_price'],'.','');
    $form['rows'] = array();
    // inizio documenti/certificati
    $next_row = 0;
    if (isset($_POST['rows'])) {
       foreach ($_POST['rows'] as $next_row => $value) {
            $form['rows'][$next_row]['id_doc'] = intval($value['id_doc']);
            $form['rows'][$next_row]['extension'] = substr($value['extension'],0,5);
            $form['rows'][$next_row]['title'] = substr($value['title'],0,255);
            $next_row++;
       }
    }
    // fine documenti/certificati

    if (isset($_POST['Submit'])) { // conferma tutto
       if ($toDo == 'update') {  // controlli in caso di modifica
         if ($form['codice'] != $form['ref_code']) { // se sto modificando il codice originario
          // controllo che l'articolo ci sia gia'
          $rs_articolo = gaz_dbi_dyn_query('codice', $gTables['artico'], "codice = '".$form['codice']."'","codice DESC",0,1);
          $rs = gaz_dbi_fetch_array($rs_articolo);
          if ($rs) { $msg .= "0+"; }
          // controllo che il precedente non abbia movimenti di magazzino associati
          $rs_articolo = gaz_dbi_dyn_query('artico', $gTables['movmag'], "artico = '".$form['ref_code']."'","artico DESC",0,1);
          $rs = gaz_dbi_fetch_array($rs_articolo);
          if ($rs) { $msg .= "1+"; }
         }
       } else {
          // controllo che l'articolo ci sia gia'
          $rs_articolo = gaz_dbi_dyn_query('codice', $gTables['artico'], "codice = '".$form['codice']."'","codice DESC",0,1);
          $rs = gaz_dbi_fetch_array($rs_articolo);
          if ($rs) {
             $msg .= "2+";
          }
       }
       if (! empty($_FILES['userfile']['name'])) {
        if (!( $_FILES['userfile']['type'] == "image/png" ||
               $_FILES['userfile']['type'] == "image/x-png" ||
               $_FILES['userfile']['type'] == "image/jpeg" ||
               $_FILES['userfile']['type'] == "image/jpg" ||
               $_FILES['userfile']['type'] == "image/gif" ||
               $_FILES['userfile']['type'] == "image/x-gif"))
           $msg .= "3+";
           // controllo che il file non sia piu' grande di circa 10kb
        if ( $_FILES['userfile']['size'] > 10999)
           $msg .= "4+";
       }
       $msg .= (empty($form["codice"]) ? "5+" : '');
       $msg .= (empty($form["descri"]) ? "6+" : '');
       $msg .= (empty($form["unimis"]) ? "7+" : '');
       $msg .= (empty($form["aliiva"]) ? "8+" : '');
       if (empty($msg)) { // nessun errore
          if ($_FILES['userfile']['size'] > 0) { //se c'e' una nuova immagine nel buffer
             $form['image'] = file_get_contents($_FILES['userfile']['tmp_name']);
          } elseif ($toDo == 'update') { // altrimenti riprendo la vecchia ma solo se è una modifica
             $oldimage = gaz_dbi_get_row($gTables['artico'],'codice',$form['ref_code']);
             $form['image'] = $oldimage['image'];
          } else {
             $form['image'] = '';
          }
          // aggiorno il db
          if ($toDo == 'insert') {
             gaz_dbi_table_insert('artico',$form);
          } elseif ($toDo == 'update') {
             gaz_dbi_table_update('artico',$form['ref_code'],$form);
          }
          header("Location: ".$form['ritorno']);
          exit;
       }
    } elseif (isset($_POST['Return'])) { // torno indietro
          header("Location: ".$form['ritorno']);
          exit;
    }
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    $form = gaz_dbi_get_row($gTables['artico'], 'codice',substr($_GET['codice'],0,15));
    $form['ritorno']=$_SERVER['HTTP_REFERER'];
    $form['ref_code']=$form['codice'];
    // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
    $form["preacq"] = number_format($form['preacq'],$admin_aziend['decimal_price'],'.','');
    $form["preve1"] = number_format($form['preve1'],$admin_aziend['decimal_price'],'.','');
    $form["preve2"] = number_format($form['preve2'],$admin_aziend['decimal_price'],'.','');
    $form["preve3"] = number_format($form['preve3'],$admin_aziend['decimal_price'],'.','');
    $form["web_price"] = number_format($form['web_price'],$admin_aziend['decimal_price'],'.','');
    $form['rows'] = array();
    // inizio documenti/certificati
    $next_row = 0;
    $rs_row = gaz_dbi_dyn_query("*", $gTables['files'], "item_ref = '".$form['codice']."'","id_doc DESC");
    while ($row = gaz_dbi_fetch_array($rs_row)) {
           $form['rows'][$next_row] = $row;
           $next_row++;
    }
    // fine documenti/certificati

} else { //se e' il primo accesso per INSERT
    $form=gaz_dbi_fields('artico');
    $form['ritorno']=$_SERVER['HTTP_REFERER'];
    $form['ref_code']='';
    $form['aliiva']=$admin_aziend['alliva'];
    // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
    $form["preacq"] = number_format($form['preacq'],$admin_aziend['decimal_price'],'.','');
    $form["preve1"] = number_format($form['preve1'],$admin_aziend['decimal_price'],'.','');
    $form["preve2"] = number_format($form['preve2'],$admin_aziend['decimal_price'],'.','');
    $form["preve3"] = number_format($form['preve3'],$admin_aziend['decimal_price'],'.','');
    $form["web_price"] = number_format($form['web_price'],$admin_aziend['decimal_price'],'.','');
    $form['web_url']='';
}

require("../../library/include/header.php");
$script_transl = HeadMain();
echo "<form method=\"POST\" name=\"form\" enctype=\"multipart/form-data\">\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"".$form['ritorno']."\">\n";
echo "<input type=\"hidden\" name=\"ref_code\" value=\"".$form['ref_code']."\">\n";
echo "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">";
$gForm = new magazzForm();
$magval=array_pop($gForm->getStockValue(false,$form['codice']));
if ($toDo == 'insert') {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['ins_this']."</div>\n";
} else {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['upd_this']." '".$form['codice']."'</div>\n";
}
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="3" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
}
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['codice']."* </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"codice\" value=\"".$form['codice']."\" align=\"right\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['descri']."* </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"descri\" value=\"".$form['descri']."\" align=\"right\" maxlength=\"255\" size=\"70\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['barcode']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"barcode\" value=\"".$form['barcode']."\" align=\"right\" maxlength=\"13\" size=\"13\" /></td>\n";
echo "</tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\"><img src=\"../root/view.php?table=artico&value=".$form['codice']."\" width=\"100\"></td>\n";
echo "<td colspan=\"2\" class=\"FacetFieldCaptionTD\">".$script_transl['image']." <input name=\"userfile\" type=\"file\">";
echo "</td></tr>";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['unimis']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"unimis\" value=\"".$form['unimis']."\" align=\"right\" maxlength=\"3\" size=\"3\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['catmer']."</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('catmer','catmer','codice',$form['catmer'],false,1,' - ','descri');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['preacq']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"preacq\" value=\"".$form['preacq']."\" style=\"text-align:right;\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['preve1']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"preve1\" value=\"".$form['preve1']."\" style=\"text-align:right;\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['preve2']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"preve2\" value=\"".$form['preve2']."\" style=\"text-align:right;\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['preve3']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"preve3\" value=\"".$form['preve3']."\" style=\"text-align:right;\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['aliiva']." * </td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectFromDB('aliiva','aliiva','codice',$form['aliiva'],'codice',1,' - ','descri');
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['esiste']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">";
echo $magval['q_g'];
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['valore']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">$money[1] ";
echo $magval['v_g'];
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['last_cost']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"last_cost\" value=\"".$form['last_cost']."\" style=\"text-align:right;\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['scorta']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"scorta\" value=\"".$form['scorta']."\" style=\"text-align:right;\" maxlength=\"13\" size=\"13\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['riordino']." </td>\n";
echo "\t<td class=\"FacetDataTD\" colspan=\"2\">
      <input type=\"text\" name=\"riordino\" value=\"".$form['riordino']."\" style=\"text-align:right;\" maxlength=\"13\" size=\"13\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['uniacq']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"uniacq\" value=\"".$form['uniacq']."\" align=\"right\" maxlength=\"3\" size=\"3\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['peso_specifico']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"peso_specifico\" value=\"".$form['peso_specifico']."\" align=\"right\" maxlength=\"13\" size=\"13\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['volume_specifico']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"volume_specifico\" value=\"".$form['volume_specifico']."\" style=\"text-align:right;\" maxlength=\"13\" size=\"13\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['pack_units']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"pack_units\" value=\"".$form['pack_units']."\" style=\"text-align:right;\" maxlength=\"6\" size=\"6\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['codcon']."</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('codcon',$form['codcon'],4);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['id_cost']."</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->selectAccount('id_cost',$form['id_cost'],3);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['annota']."</td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"annota\" value=\"".$form['annota']."\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
if ($toDo == 'update') {
  echo "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['document']." :</td><td class=\"FacetDataTD\" colspan=\"2\">\n";
  if ($next_row>0) {
    echo "<table>\n";
    foreach ($form['rows'] as $k=>$val) {
            echo "<input type=\"hidden\" value=\"".$val['id_doc']."\" name=\"rows[$k][id_doc]\">\n";
            echo "<input type=\"hidden\" value=\"".$val['extension']."\" name=\"rows[$k][extension]\">\n";
            echo "<input type=\"hidden\" value=\"".$val['title']."\" name=\"rows[$k][title]\">\n";
            echo "<tr class=\"FacetFieldCaptionTD\">\n";
            echo "<td>data/files/".$val['id_doc'].".".$val['extension']."</td>\n";
            echo "<td><a href=\"../root/retrieve.php?id_doc=".$val["id_doc"]."\"><img src=\"../../library/images/doc.png\" title=\"".$script_transl['view']."!\" border=\"0\"></a></td>";
            echo "<td>".$val['title']."</td>\n";
            echo "<td align=\"right\" ><input type=\"button\" value=\"".ucfirst($script_transl['update'])." \" onclick=\"location.href='admin_document.php?id_doc=".$val['id_doc']."&Update';\"></td>";
            echo "\t </tr>\n";
    }
    echo "<tr><td align=\"right\" colspan=\"4\"><input type=\"button\" value=\"".ucfirst($script_transl['insert'])." \" onclick=\"location.href='admin_document.php?item_ref=".$form['codice']."&Insert';\"></td></tr>\n";
    echo "\t </table></td></tr>\n";
  } else {
    echo "\t <input type=\"button\" value=\"".ucfirst($script_transl['insert'])." \" onclick=\"location.href='admin_document.php?item_ref=".$form['codice']."&Insert';\"></td></tr>\n";
  }
}
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['web_mu']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"web_mu\" value=\"".$form['web_mu']."\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['web_price']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"web_price\" value=\"".$form['web_price']."\" style=\"text-align:right;\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['web_multiplier']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"web_multiplier\" value=\"".$form['web_multiplier']."\" style=\"text-align:right;\" maxlength=\"15\" size=\"15\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['web_url']." </td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"web_url\" value=\"".$form['web_url']."\" maxlength=\"255\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['web_public']."</td><td colspan=\"2\" class=\"FacetDataTD\">\n";
$gForm->variousSelect('web_public',$script_transl['web_public_value'],$form['web_public']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['sqn']."</td>";
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\">\n";
echo '<input name="none" type="submit" value="" disabled>';
echo '<input name="Return" type="submit" value="'.$script_transl['return'].'!">';
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\" align=\"right\">\n";
echo '<input name="Submit" type="submit" value="'.strtoupper($script_transl[$toDo]).'!">';
echo "\t </td>\n";
echo "</tr>\n";
?>
</table>
</form>
</body>
</html>