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

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = '';
}

if (isset($_POST['Update'])) {   //se non e' il primo accesso
    $form=gaz_dbi_parse_post('files');
    preg_match("/\.([^\.]+)$/", $_FILES['userfile']['name'], $matches);
    if ($form['extension']!=$matches[1]) { // se è stata cambiata l'estensione
        $form['title']='Original name: '.$_FILES["userfile"]["name"]; // modifico pure il titolo
    }
    $form['extension']=$matches[1];
    $form['ritorno'] = $_POST['ritorno'];
    if (isset($_POST['Submit'])) { // conferma tutto
       if ($_FILES['userfile']['error']==0) {
        if ( $_FILES['userfile']['type'] == "image/png" ||
             $_FILES['userfile']['type'] == "image/x-png" ||
             $_FILES['userfile']['type'] == "application/pdf" ||
             $_FILES['userfile']['type'] == "image/pjpeg" ||
             $_FILES['userfile']['type'] == "image/jpeg" ||
             $_FILES['userfile']['type'] == "text/plain" ||
             $_FILES['userfile']['type'] == "application/msword" ||
             $_FILES['userfile']['type'] == "image/tiff" ||
             $_FILES['userfile']['type'] == "application/rtf" || (
             $_FILES['userfile']['type'] == "application/octet-stream" && ($form['extension']=='odt' ||
                                                                           $form['extension']=='docx'))
             ) {
        } else {
           $msg .= "0+";
        }
        // controllo che il file non sia piu' grande di 10Mb
        if ( $_FILES['userfile']['size'] > 10485760 ){
            $msg .= "1+";
        } elseif($_FILES['userfile']['size'] == 0)  {
           $msg .= "2+";
        }
       }
       if (empty($msg)) { // nessun errore
          // aggiorno il filesystem ed il db
          move_uploaded_file($_FILES["userfile"]["tmp_name"] ,"../../data/files/".$form['id_doc'].".".$form['extension']);
          gaz_dbi_table_update('files',array('id_doc',$form['id_doc']),$form);
          header("Location: ".$form['ritorno']);
          exit;
       }
    } elseif (isset($_POST['Return'])) { // torno indietro
          header("Location: ".$form['ritorno']);
          exit;
    }
} else { //se e' il primo accesso per UPDATE
    $form = gaz_dbi_get_row($gTables['files'], 'id_doc',intval($_GET['id_doc']));
    $form['ritorno']=$_SERVER['HTTP_REFERER'];
}

require("../../library/include/header.php");
$script_transl = HeadMain();
require("./lang.".$admin_aziend['lang'].".php");
$script_transl += $strScript["browse_document.php"];
$gForm = new magazzForm();
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['admin']."</div>\n";
echo "<form method=\"POST\" name=\"form\" enctype=\"multipart/form-data\">\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"".$form['ritorno']."\">\n";
echo "<input type=\"hidden\" name=\"id_doc\" value=\"".$form['id_doc']."\">\n";
echo "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">";
echo "<table class=\"Tmiddle\">\n";
if (!empty($msg)) {
    echo '<tr><td colspan="3" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
}
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">ID</td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">".$form['id_doc']."</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">File : </td>\n";
echo "\t<td class=\"FacetDataTD\"><a href=\"../root/retrieve.php?id_doc=".$form["id_doc"]."\"><img src=\"../../library/images/vis.gif\" title=\"".$script_transl['view']."!\" border=\"0\"> /data/files/".$form['id_doc'].".".$form['extension']."</a></td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\" align=\"right\">".$script_transl['update']." :  <input name=\"userfile\" type=\"file\"> </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['item']."</td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">".$form['item_ref']."</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['note']."</td>\n";
echo "\t<td colspan=\"2\" class=\"FacetDataTD\">
      <input type=\"text\" name=\"annota\" value=\"".$form['title']."\" maxlength=\"50\" size=\"50\" /></td>\n";
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