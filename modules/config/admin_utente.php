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
$aut = 9;
if (!isset($_POST['ritorno'])){
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
$config = new Config;
$user_data = gaz_dbi_get_row($gTables['admin'], "Login", $_SESSION["Login"]);
$msg = "";
if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
    $accessi = $_GET['Login'];
    if (! isset($_GET['Login'])) {
       header("Location: ".$_POST['ritorno']);
       exit;
    }
    if ($_SESSION['Login'] == $_GET['Login'] or $user_data['Abilit'] == 9) {
       $aut = 0;

    }
} elseif ((isset($_POST['Insert'])) or (isset($_GET['Insert']))) {
    $toDo = 'insert';
    $accessi = "";
    $aut = 9;
} else {
    header("Location: ".$_POST['ritorno']);
    exit;
}

$admin_aziend=checkAdmin($aut);

if (isset($_POST['Return'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
}

if ((isset($_POST['Insert'])) || (isset($_POST['Update']))) {   //se non e' il primo accesso
    $form["Cognome"] = substr($_POST['Cognome'],0,30);
    $form["Nome"] = substr($_POST['Nome'],0,30);
    $form["lang"] = substr($_POST['lang'],0,15);
    $form["style"] = substr($_POST['style'],0,30);
    $form["Abilit"] = intval($_POST['Abilit']);
    $form["Access"] = intval($_POST['Access']);
    $form["Login"] = substr($_POST['Login'],0,15);
    $form["Password"] = substr($_POST['Password'],0,20);
    $form["confpass"] = substr($_POST['confpass'],0,20);
    if ($toDo == 'insert') {
        $ricerca = $_POST["Login"];
        $rs_utente = gaz_dbi_dyn_query("*", $gTables['admin'], "Login = '".$form['Login']."'","Login DESC",0,1);
        $risultato = gaz_dbi_fetch_array($rs_utente);
        if ($risultato) {
            $msg .= "0+";
        }
    }

} elseif ((!isset($_POST['Update'])) && (isset($_GET['Update']))) {
    /*
    * La prima entrata per update
    */
    $form = gaz_dbi_get_row($gTables['admin'], "Login", substr($_GET['Login'],0,15));
    $form['confpass'] = $form['Password'];
} else {
    /*
    * La prima entrata per insert
    */
    $form["Cognome"] = "";
    $form["Nome"] = "";
    $form["image"] = "";
    $form["style"] = $admin_aziend['style'];
    $form["lang"] = $admin_aziend['lang'];
    $form["Abilit"] = 5;
    $form["Access"] = 0;
    $form["Login"] = "";
    $form["Password"] = "";
    $form["confpass"] = "";
}

if (isset($_POST['Submit'])) {
    //controllo i campi
    if (empty($form["Cognome"]))
        $msg .= "1+";
    if (empty($form["Login"]))
        $msg .= "2+";
    if (empty($form["Password"]))
        $msg .= "3+";
    if (strlen($form["Password"]) < $config->getValue('psw_min_length'))
        $msg .= "4+";
    if ($form["Password"] != $form["confpass"] )
        $msg .= "5+";
    if ($form["Abilit"] > $user_data["Abilit"] )
        $msg .= "6+";
    if (! empty($_FILES['userfile']['name'])) {
        if (!( $_FILES['userfile']['type'] == "image/jpeg" || $_FILES['userfile']['type'] == "image/pjpeg"))
            $msg .= "7+";
            // controllo che il file non sia pi&ugrave; grande di 10kb
        if ( $_FILES['userfile']['size'] > 10999)
            $msg .= "8+";
    }
    if ($form["Abilit"] < 9) {
        $ricerca=$form["Login"];
        $rs_utente = gaz_dbi_dyn_query("*", $gTables['admin'], "Login <> '$ricerca' and Abilit ='9'", "Login",0,1);
        $risultato = gaz_dbi_fetch_array($rs_utente);
        if (!$risultato) {
            $msg .= "9+";
        }
    }
    if ( $msg == "") { // nessun errore
        // preparo la stringa dell'immagine
        if ($_FILES['userfile']['size'] > 0) { //se c'e' una nuova immagine nel buffer
            $form['image'] = file_get_contents($_FILES['userfile']['tmp_name']);
        } else {   // altrimenti riprendo la vecchia
            $oldimage = gaz_dbi_get_row($gTables['admin'],'Login',$form['Login']);
            $form['image'] = $oldimage['image'];
        }
        // aggiorno il db
        $form["datacc"] = date("YmdHis");
        $form["datpas"] = date("YmdHis");
        if ($user_data['Abilit'] == 9) {
            while (list($key, $value) = each($_POST)) {
                if (preg_match ("/^([0-9]{3})acc_/",$key,$id)) {
                    updateAccessRights($form['Login'], preg_replace("/^[0-9]{3}acc_/",'', $key), $value,$id[1]);
                } elseif (preg_match ("/^([0-9]{3})nusr_/",$key,$id)) {
                    updateAccessRights($form['Login'],1,3,$user_data['enterprise_id']);
                    $mod_data = gaz_dbi_get_row($gTables['module'],'name', preg_replace("/^[0-9]{3}nusr_/", '', $key));
                    if (!empty($mod_data)) {
                         updateAccessRights($form['Login'],$mod_data['id'],$value,$user_data['enterprise_id']);
                    }
                } elseif (preg_match("/^([0-9]{3})new_/",$key,$id) && $value==3) { // il nuovo modulo non  presente in gaz_module
                    $name = preg_replace("/^[0-9]{3}new_/",'',$key);
                    // includo il file dei dati per creazione del men
                    require("../../modules/".$name."/menu.creation_data.php");
                    // trovo l'ultimo peso assegnato ai moduli esistenti e lo accodo
                    $rs_last = gaz_dbi_dyn_query("MAX(weight)+1 AS max_we", $gTables['module'],'id > 1');
                    $r = gaz_dbi_fetch_array($rs_last);
                    gaz_dbi_table_insert('module',array('name'=>$name,'link'=>$menu_data['m1']['link'],'icon'=>$name.'.png','weight'=>$r['max_we']));
                    //recupero l'id assegnato dall'inserimento
                    $mod_id = gaz_dbi_last_id();
                    updateAccessRights($form['Login'], $mod_id, 3,$id[1]);
                    // trovo l'ultimo id del sub menu
                    $rs_last = gaz_dbi_dyn_query("MAX(id)+1 AS max_id", $gTables['menu_module'], 1);
                    $r = gaz_dbi_fetch_array($rs_last);
                    $m2_id = $r['max_id'];
                    foreach ($menu_data['m2'] as $k_m2=>$v_2){
                          gaz_dbi_table_insert('menu_module',array('id'=>$m2_id,'id_module'=>$mod_id,'link'=>$v_2['link'],'translate_key'=>$k_m2,'weight'=>$v_2['weight']));
                          if (isset($menu_data['m3']['m2'][$k_m2])) {
                              foreach ($menu_data['m3']['m2'] as $v_3){
                                  // trovo l'ultimo id del sub menu
                                  $rs_last = gaz_dbi_dyn_query("MAX(id)+1 AS max_id", $gTables['menu_script'], 1);
                                  $r = gaz_dbi_fetch_array($rs_last);
                                  gaz_dbi_table_insert('menu_script',array('id'=>$r['max_id'],'id_menu'=>$m2_id,'link'=>$v_3['link'],'translate_key'=>$v_3['translate_key'],'weight'=>$v_3['weight']));
                              }
                          }
                          $m2_id ++;
                    }
                }
           }
        }
        if ($toDo == 'insert') {
            $form['enterprise_id']=$user_data['enterprise_id'];
            gaz_dbi_table_insert('admin',$form);
        } elseif ($toDo == 'update') {
            //cambio la data di modifica password
            $getInit = gaz_dbi_get_row($gTables['admin'], "Login", $form['Login']);
            if ($form["Password"] != $getInit["Password"]) {
               $form["datpas"] = date("YmdHis");
            }
            gaz_dbi_table_update('admin',array('Login',$form['Login']),$form);
        }
        header("Location: ".$_POST['ritorno']);
        exit;
    }

}
require("../../library/include/header.php");
$script_transl=HeadMain(0,array('jquery/jquery-1.3.2.min',
                                'jquery/capslock'));
echo '<script type="text/javascript">
      $(document).ready(function() {

        var coptions = {
          caps_lock_on: function() { $("#cmsg").text("'.$script_transl['caps'].'");},
          caps_lock_off: function() { $("#cmsg").text(""); }
        };

        var poptions = {
          caps_lock_on: function() { $("#pmsg").text("'.$script_transl['caps'].'");},
          caps_lock_off: function() { $("#pmsg").text(""); }
        };

        $("#cpass").capslock(coptions);
        $("#ppass").capslock(poptions);

        $("#cpass").focus();
        $("#ppass").focus();

      });
      </script>';
?>

<form method="POST" enctype="multipart/form-data"  autocomplete="off">
<input type="hidden" name="ritorno" value="<?php print $_POST['ritorno'];?>">
<?php
if ($toDo == 'insert') {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['ins_this']."</div>\n";
} else {
   echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['upd_this']." '".$form['Login']."'</div>\n";
   echo "<input type=\"hidden\" value=\"".$form['Login']."\" name=\"Login\" />\n";
}
?>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<?php
$gForm = new GAzieForm();
echo '<input type="hidden" name="'.ucfirst($toDo).'" value="">';
if (!empty($msg)) {
    echo '<tr><td colspan="3" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td></tr>\n";
}
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Cognome']; ?>* </td>
<td colspan="2" class="FacetDataTD"><input title="Cognome" type="text" name="Cognome" value="<?php print $form["Cognome"] ?>" maxlength="30" size="30" class="FacetInput">&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Nome']; ?></td>
<td colspan="2" class="FacetDataTD"><input title="Nome" type="text" name="Nome" value="<?php print $form["Nome"] ?>" maxlength="30" size="30" class="FacetInput">&nbsp;</td>
</tr>
<tr>
<?php
print "<td class=\"FacetFieldCaptionTD\"><img src=\"../root/view.php?table=admin&value=".$form['Login']."&field=Login\" width=\"100\"></td>";
print "<td colspan=\"2\" class=\"FacetDataTD\" align=\"center\">".$script_transl['image'].":<br /><input name=\"userfile\" type=\"file\" class=\"FacetDataTD\"></td>";
?>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['lang']; ?></td>
<?php
echo '<td colspan=\"2\" class="FacetDataTD">';
echo '<select name="lang" class="FacetSelect">';
$relativePath = '../../language';
if ($handle = opendir($relativePath)) {
    while ($file = readdir($handle)) {
        if(($file == ".") or ($file == "..") or ($file == ".svn")) continue;
        $selected="";
        if ($form["lang"] == $file) {
            $selected = " selected ";
        }
        echo "<option value=\"".$file."\"".$selected.">".ucfirst($file)."</option>";
    }
    closedir($handle);
}
echo "</td></tr>\n";
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['style']; ?></td>
<?php
echo '<td colspan=\"2\" class="FacetDataTD">';
echo '<select name="style" class="FacetSelect">';
$relativePath = '../../library/style';
if ($handle = opendir($relativePath)) {
    while ($file = readdir($handle)) {
        // accetto solo i file css
        if (!preg_match("/^[a-z0-9\s\_]+\.css$/",$file)){
            continue;
        } 
        $selected="";
        if ($form["style"] == $file) {
            $selected = " selected ";
        }
        echo "<option value=\"".$file."\"".$selected.">".$file."</option>";
    }
    closedir($handle);
}
echo "</td></tr>\n";
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Abilit']; ?></td>
<td colspan="2" class="FacetDataTD"><input title="Livello " type="text" name="Abilit" value="<?php print $form["Abilit"] ?>" maxlength="1" size="1" class="FacetInput">&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Access']; ?></td>
<td colspan="2" class="FacetDataTD"><input title="Accessi" type="text" name="Access" value="<?php print $form["Access"] ?>" maxlength="7" size="7" class="FacetInput">&nbsp;</td>
</tr>
<?php
if ($toDo == 'insert') {
    echo '<tr><td class="FacetFieldCaptionTD">'.$script_transl['Login'].' *</td>
       <td class="FacetDataTD" colspan="2"><input title="Login" type="text" name="Login" value="'.$form["Login"].'" maxlength="20" size="20" class="FacetInput">&nbsp;</td>
       </tr>';
}
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['pre_pass'].' '.$config->getValue('psw_min_length').' '.$script_transl['post_pass']; ?> *</td>
<td colspan="2" class="FacetDataTD"><input title="Password" type="password" name="Password" value="<?php print $form["Password"]; ?>" maxlength="20" size="20" class="FacetInput" id="ppass" /><div class="FacetDataTDred" id="pmsg"></div>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['rep_pass'];?> *</td>
<td colspan="2" class="FacetDataTD"><input title="Conferma Password" type="password" name="confpass" value="<?php print $form["confpass"]; ?>" maxlength="20" size="20" class="FacetInput" id="cpass" /><div class="FacetDataTDred" id="cmsg"></div>&nbsp;</td>
</tr>
<?php
if ($user_data["Abilit"] == 9) {
    function getModule($login,$enterprise_id)
    {
       global $gTables,$admin_aziend;
       //trovo i moduli installati
       $mod_found=array();
       $relativePath = '../../modules';
       if ($handle = opendir($relativePath)) {
          while ($exist_mod = readdir($handle)) {
             if ($exist_mod == "."
                 || $exist_mod == ".."
                 || $exist_mod == ".svn"
                 || $exist_mod == "root" ) continue;
                 $rs_mod = gaz_dbi_dyn_query(" am.access ,am.moduleid, module.name", $gTables['admin_module'].' AS am LEFT JOIN '.$gTables['module'].
                               ' AS module ON module.id=am.moduleid ',
                               " am.adminid = '".$login."' AND module.name = '$exist_mod' AND am.enterprise_id = '$enterprise_id'","am.adminid",0,1);
                 require("../../modules/$exist_mod/menu.".$admin_aziend['lang'].".php");
                 $row = gaz_dbi_fetch_array($rs_mod);
                 if (!isset($row['moduleid'])){
                     $row['name']=$exist_mod;
                     $row['moduleid']=0;
                     $row['access']=0;
                 }
                 $row['transl_name'] = $transl[$exist_mod]['name'];
                 $mod_found[$exist_mod] = $row;
          }
          closedir($handle);
       }
       return $mod_found;
    }

    //richiamo tutte le aziende installate e vedo se l'utente  e' abilitato o no ad essa
    $table=$gTables['aziend'].' AS a';
    $what="a.codice AS id, ragso1 AS ragsoc, (SELECT COUNT(*) FROM ".$gTables['admin_module']." WHERE a.codice=".$gTables['admin_module'].".enterprise_id AND ".$gTables['admin_module'].".adminid='".$form['Login']."') AS set_co ";
    $co_rs=gaz_dbi_dyn_query($what,$table,1,"ragsoc ASC");
    while($co=gaz_dbi_fetch_array($co_rs)) {
      echo "<tr><td align=\"center\" colspan=\"3\">".$co['ragsoc'].'  - '.$co['set_co']."</tr>\n";
      echo "<tr><td class=\"FacetDataTD\">".$script_transl['mod_perm'].":</td>\n";
      echo "<td>".$script_transl['all']."</td>\n";
      echo "<td>".$script_transl['none']."</td></tr>\n";
      $mod_found=getModule($form['Login'],$co['id']);
      $co_id=sprintf('%03d',$co['id']);
      foreach ($mod_found as $mod) {
          echo "<tr>\n";
          echo "<td class=\"FacetFieldCaptionTD\">".$mod['transl_name'].' ('.$mod['name'].")</td>\n";
          if ($mod['moduleid'] == 0) {
              if ($toDo == 'insert') {
                  echo "  <td><input type=radio checked name=\"".$co_id."nusr_".$mod['name']."\" value=\"3\"></td>";
                  echo "  <td><input type=radio name=\"".$co_id."nusr_".$mod['name']."\" value=\"0\"></td>";
              } elseif ($co['set_co']==0) {
                  echo "  <td><input type=radio name=\"".$co_id."nusr_".$mod['name']."\" value=\"3\"></td>";
                  echo "  <td><input type=radio checked name=\"".$co_id."nusr_".$mod['name']."\" value=\"0\"></td>";
              } else {
                  echo "  <td class=\"FacetDataTDred\"><input type=radio name=\"".$co_id."new_".$mod['name']."\" value=\"3\">Trovato nuovo modulo!</td>";
                  echo "  <td class=\"FacetDataTDred\"><input type=radio checked name=\"".$co_id."new_".$mod['name']."\" value=\"0\"></td>";
              }
          } elseif ($mod['access'] == 0) {
              echo "  <td><input type=radio name=\"".$co_id."acc_".$mod['moduleid']."\" value=\"3\"></td>";
              echo "  <td><input type=radio checked name=\"".$co_id."acc_".$mod['moduleid']."\" value=\"0\"></td>";
          } else {
              echo "  <td><input type=radio checked name=\"".$co_id."acc_".$mod['moduleid']."\" value=\"3\"></td>";
              echo "  <td><input type=radio name=\"".$co_id."acc_".$mod['moduleid']."\" value=\"0\"></td>";
          }
          echo "</tr>\n";
      }
   }
}
?>
<tr>
<?php
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['sqn']."</td>";
echo "\t </td>\n";
echo "\t<td  class=\"FacetDataTD\">\n";
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


