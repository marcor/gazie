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
if (!isset($_POST['hidden_req'])){

    $form['hidden_req']='';
    $form['enterprise_id']=$admin_aziend['enterprise_id'];
    $form['search']['enterprise_id']='';
} else {
  if (isset($_POST['logout'])) {
      header("Location: logout.php");
      exit;
  }
  $form['hidden_req']=$_POST['hidden_req'];
  $form['enterprise_id']=$_POST['enterprise_id'];
  $form['search']['enterprise_id']=$_POST['search']['enterprise_id'];
}

function selectCompany($name,$val,$strSearch='',$val_hiddenReq='',$mesg,$class='FacetSelect')
{
    global $gTables,$admin_aziend;
    $table=$gTables['aziend'].' LEFT JOIN '. $gTables['admin_module'].' ON '.$gTables['admin_module'].'.enterprise_id = '.$gTables['aziend'].'.codice';
    $where=$gTables['admin_module'].'.adminid=\''.$admin_aziend['Login'].'\' GROUP BY enterprise_id';
    if ($val>0 && $val<1000) { // vengo da una modifica della precedente select case quindi non serve la ricerca
          $co_rs=gaz_dbi_dyn_query("*",$table,'enterprise_id = '.$val.' AND '.$where,"ragso1 ASC");
          $co=gaz_dbi_fetch_array($co_rs);
          changeEnterprise(intval($val));
          echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
          echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"%%\">\n";
          echo "\t<input type=\"submit\" value=\"".$co['ragso1']."\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
    } else {
      if (strlen($strSearch) >= 2) { //sto ricercando un nuovo partner
         echo "\t<select name=\"$name\" class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
         $co_rs=gaz_dbi_dyn_query("*",$table,"ragso1 LIKE '".addslashes($strSearch)."%' AND ". $where,"ragso1 ASC");
         if ($co_rs){
               echo "<option value=\"0\"> ---------- </option>";
               while ($r = gaz_dbi_fetch_array($co_rs)) {
                     $selected = '';
                     if ($r['enterprise_id'] == $val) {
                         $selected = "selected";
                     }
                     echo "\t\t <option value=\"".$r['enterprise_id']."\" $selected >".intval($r['enterprise_id'])."-".$r["ragso1"]."</option>\n";
               }
               echo "\t </select>\n";
          } else {
               $msg = $mesg[0];
          }
       } else {
          $msg = $mesg[1];
          echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
       }
       echo "\t<input type=\"text\" name=\"search[$name]\" value=\"".$strSearch."\" maxlength=\"15\" size=\"6\" class=\"FacetInput\">\n";
       if (isset($msg)) {
          echo "<input type=\"text\" style=\"color: red; font-weight: bold;\" size=\"".strlen($msg)."\" disabled value=\"$msg\">";
       }
       echo "\t<input type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
    }
}

$checkUpd = new CheckDbAlign;
$data=$checkUpd->TestDbAlign();
if ($data){
	// induco l'utente ad aggiornare il db      
	header("Location: ../../setup/install/install.php?tp=".$table_prefix);
	exit;
}

require("../../library/include/header.php");
$script_transl = HeadMain();
$t=strftime("%H");
if ($t>4 && $t<=13) {
    $msg=$script_transl['morning'];
} elseif ($t>13 && $t<=17) {
    $msg=$script_transl['afternoon'];
} elseif ($t>17 && $t<=21) {
    $msg=$script_transl['evening'];
} else {
    $msg=$script_transl['night'];
}
echo "<form method=\"POST\" name=\"myform\">\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo '<div id="admin_main" ><table border="1" class="Tmiddle">';
echo "<tr class=\"FacetFormHeaderFont\">\n";
echo "<td class=\"FacetDataTD\"><A href=\"../config/admin_utente.php?Login=".$admin_aziend['Login']."&Update\"><img src=\"view.php?table=admin&field=Login&value=".$admin_aziend['Login']."\" alt=\"".$admin_aziend['Cognome'].' '.$admin_aziend['Nome']."\" width=100 title=\"".$script_transl['change_usr']."\" border=\"2\"></a>";
echo "</td>";
echo "<td id='admin_welcome'>";
echo ucfirst($msg)." ".$admin_aziend['Nome'].' (ip='.$admin_aziend['last_ip'].')'.
"\n";
echo "".$script_transl['access'].$admin_aziend['Access'].$script_transl['pass'].gaz_format_date($admin_aziend['datpas'])."<br>";
echo "<div id='admin_p_logout'>".$script_transl['logout']." &rarr; <input name=\"logout\" type=\"submit\" value=\" Logout \"></div></td>\n";
echo "<td align=\"center\" bgcolor=\"#".$admin_aziend['colore']."\">".$script_transl['company']."<a href=\"../config/admin_aziend.php\"><img src=\"view.php?table=aziend&value=".$form['enterprise_id']."\" width=\"200\" alt=\"Logo\" border=\"0\" title=\"".$script_transl['upd_company']."\"></a><br />".$script_transl['mesg_co'][2]." &rarr; ";
echo selectCompany('enterprise_id',$form['enterprise_id'],$form['search']['enterprise_id'],$form['hidden_req'],$script_transl['mesg_co']);
echo "</td>\n";
echo "</tr></table></div>\n";
echo "<div id='admin_footer'>";
echo "<div align=\"center\"><br /> GAzie Version: $versSw Software Open Source (lic. GPL) ".$script_transl['business']." ".$script_transl['proj']."<a  target=\"_new\" title=\"".$script_transl['auth']."\" href=\"http://http://www.devincentiis.it\"> http://www.devincentiis.it</a></div>\n";
echo '<div><table border="0" class="Tmiddle">';
echo "<tr align=\"center\"><td>\n";
echo "<a href=\"http://gazie.sourceforge.net\" target=\"_new\" title=\"".$script_transl['devel']." www.gazie.it\"><img src=\"../../library/images/gazie.gif\" height=\"38\" border=\"0\"></a>\n";
foreach ($script_transl['strBottom'] as $value){
        echo "<a href=\"".$value['href']."\" title=\"".$value['title']."\" target=\"_NEW\" >";
        // echo "<img src=\"http://".$_SERVER['HTTP_HOST']."/".$radix."/library/images/".$value['img']."\" border=\"0\" ></a>\n";
        echo "<img src=\"../../library/images/".$value['img']."\" border=\"0\" ></a>\n";
}
echo '</td></tr></table></div>';
echo '</form>';

//
// Se esiste, viene incluso il file "help/italian/docume_admin_help.php",
// o l'equivalente di un altro linguaggio.
//
if (file_exists("help/".$admin_aziend['lang']."/admin_help.php")) {
    include("help/".$admin_aziend['lang']."/admin_help.php");
}
echo "</div>";
?>
</body>
</html>