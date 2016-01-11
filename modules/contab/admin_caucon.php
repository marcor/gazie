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
$admin_aziend=checkAdmin();
$msg = "";


if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    if (!isset($_GET['codice'])) {
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit;
    } else {
        $form['codice'] = substr($_GET['codice'],0,3);
    }
    $toDo = 'update';
    $titolo = "Modifica Causale Contabile";
} elseif ((isset($_POST['Insert'])) or (isset($_GET['Insert']))) {
    $toDo = 'insert';
    $titolo = "Inserimento Causale Contabile";
} else {
    $toDo = '';
    $titolo = 'Causale Contabile';
}

if (!isset($_POST['Update']) and isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
    $cau = gaz_dbi_get_row($gTables['caucon'],"codice",$form["codice"]);
    $form["descri"] = $cau["descri"];
    $form["insdoc"] = $cau["insdoc"];
    $form["regiva"] = $cau["regiva"];
    $form["operat"] = $cau["operat"];
    $form["pay_schedule"] = $cau["pay_schedule"];
    for ($i=1; $i<=6; $i++) {
      $form["contr".$i] = $cau["contr".$i];
      $form["tipim".$i] = $cau["tipim".$i];
      $form["daav_".$i] = $cau["daav_".$i];
    }
} elseif (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    $form['ritorno'] = $_POST['ritorno'];
    $form['hidden_req']=$_POST['hidden_req'];
    $form["codice"] = strtoupper(substr($_POST["codice"],0,3));;
    $form["descri"] = substr($_POST["descri"],0,50);
    $form["insdoc"] = intval($_POST["insdoc"]);
    $form["regiva"] = intval($_POST["regiva"]);
    $form["operat"] = intval($_POST["operat"]);
    $form["pay_schedule"] = intval($_POST["pay_schedule"]);
    $chk_acc=true;
    for ($i=1; $i<=6; $i++) {
      $form["contr".$i] = intval($_POST["contr".$i]);
      if ($form["contr".$i]>100 && $chk_acc==true) {
          $chk_acc=false;
      }
      $form["tipim".$i] = substr($_POST["tipim".$i],0,1);
      $form["daav_".$i] = substr($_POST["daav_".$i],0,1);
    }
    if (isset($_POST['submit'])) {
       if ($toDo == 'insert') {  //se è un'inserimento
         if ($chk_acc) $msg .= "3+";
         if (empty($form["descri"])) $msg .= "1+";
         if (!empty($form["codice"])) {
                $rs_cau = gaz_dbi_dyn_query("*", $gTables['caucon'], "codice = '".$form["codice"]."'","codice DESC",0,1);
                $rs = gaz_dbi_fetch_array($rs_cau);
                if ($rs) {
                    $msg .= "2+";
                }
                switch ($form["codice"]) {
                       case "CHI":
                         $msg .= "4+";
                       break;
                       case "APE":
                         $msg .= "5+";
                       break;
                }
         } else {
           $msg .= "0+";
         }
         if ( $msg == "") {// nessun errore
            gaz_dbi_table_insert('caucon',$form);
            header("Location: report_caucon.php");
            exit;
         }
       } else { //è una modifica
         if (empty($form["descri"])) $msg .= "1+";
         if ($chk_acc) $msg .= "3+";
         if ( $msg == "") {// nessun errore
            // aggiorno il db
            gaz_dbi_table_update('caucon',$form['codice'],$form);
            header("Location: report_caucon.php");
            exit;
         }
       }
    } elseif (isset($_POST['return'])) {
        header("Location: ".$_POST['ritorno']);
        exit;
    }

} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
    $form["codice"] = "";
    $form["descri"] = "";
    $form["insdoc"] = 0;
    $form["regiva"] = 0;
    $form["operat"] = 0;
    $form["pay_schedule"] = 0;
    for ($i=1; $i<=6; $i++) {
      $form["contr".$i] = 0;
      $form["tipim".$i] = '';
      $form["daav_".$i] = '';
    }
}
require("../../library/include/header.php");
$script_transl=HeadMain();
echo "<form method=\"POST\" name=\"select\">\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\" />\n";
echo "<input type=\"hidden\" value=\"\" name=\"".ucfirst($toDo)."\">\n";
$gForm = new contabForm();
if ($toDo == 'insert') {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['ins_this']."</div>\n";
} else {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['upd_this']." '".$form['codice']."'</div>\n";
   echo "<input type=\"hidden\" value=\"".$form['codice']."\" name=\"codice\" />\n";
}
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="2" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
}
if ($toDo == 'insert') {
   echo "<tr>\n";
   echo "\t<td class=\"FacetFieldCaptionTD\" colspan=\"2\">".$script_transl['codice']."</td>\n";
   echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"codice\" value=\"".$form['codice']."\" align=\"right\" maxlength=\"3\" size=\"3\" /></td>\n";
   echo "</tr>\n";
}
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\" colspan=\"2\">".$script_transl['descri']."</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"descri\" value=\"".$form['descri']."\" align=\"right\" maxlength=\"50\" size=\"50\" /></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\" colspan=\"2\">".$script_transl['insdoc']."</td><td class=\"FacetDataTD\">\n";
$gForm->selectNumber('insdoc',$form['insdoc'],1);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\" colspan=\"2\">".$script_transl['regiva']."</td><td class=\"FacetDataTD\">\n";
$gForm->variousSelect('regiva',$script_transl['regiva_value'],$form['regiva']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\" colspan=\"2\">".$script_transl['operat']."</td><td class=\"FacetDataTD\">\n";
$gForm->variousSelect('operat',$script_transl['operat_value'],$form['operat']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\" colspan=\"2\">".$script_transl['pay_schedule']."</td><td class=\"FacetDataTD\">\n";
$gForm->variousSelect('pay_schedule',$script_transl['pay_schedule_value'],$form['pay_schedule']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFormHeaderFont\" align=\"center\" colspan=\"3\">".$script_transl['head']."</td>";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['contr']."</td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['tipim']."</td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['daav']."</td>\n";
echo "</tr>\n";
for ($i=1; $i<=6; $i++) {
    echo "<tr>\n";
    echo "\t<td class=\"FacetDataTD\">\n";
    $gForm->selCauAccount('contr'.$i,$form['contr'.$i]);
    echo "\t</td>\n";
    echo "\t<td  class=\"FacetDataTD\">\n";
    $gForm->variousSelect('tipim'.$i,$script_transl['tipim_value'],$form['tipim'.$i]);
    echo "\t </td>\n";
    echo "\t<td  class=\"FacetDataTD\">\n";
    $gForm->variousSelect('daav_'.$i,$script_transl['daav_value'],$form['daav_'.$i]);
    echo "\t </td>\n";
    echo "</tr>\n";
}
    echo "<tr>\n";
    echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['sqn']."</td>";
    echo "\t </td>\n";
    echo "\t<td  class=\"FacetDataTD\">\n";
    echo '<input name="return" type="submit" value="'.$script_transl['return'].'!">';
    echo "\t </td>\n";
    echo "\t<td  class=\"FacetDataTD\" align=\"right\">\n";
    echo '<input name="submit" type="submit" value="'.strtoupper($script_transl[$toDo]).'!">';
    echo "\t </td>\n";
?>
</table>
</form>
</div><!-- chiude div container role main --></body>
</html>