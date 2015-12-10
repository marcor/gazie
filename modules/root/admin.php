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
    $form['company_id']=$admin_aziend['company_id'];
    $form['search']['company_id']='';
} else {
  if (isset($_POST['logout'])) {
      header("Location: logout.php");
      exit;
  }
  $form['hidden_req']=$_POST['hidden_req'];
  $form['company_id']=$_POST['company_id'];
  $form['search']['company_id']=$_POST['search']['company_id'];
}

function selectCompany($name,$val,$strSearch='',$val_hiddenReq='',$mesg,$class='FacetSelect')
{
    global $gTables,$admin_aziend;
    $table=$gTables['aziend'].' LEFT JOIN '. $gTables['admin_module'].' ON '.$gTables['admin_module'].'.company_id = '.$gTables['aziend'].'.codice';
    $where=$gTables['admin_module'].'.adminid=\''.$admin_aziend['Login'].'\' GROUP BY company_id';
    if ($val>0 && $val<1000) { // vengo da una modifica della precedente select case quindi non serve la ricerca
          $co_rs=gaz_dbi_dyn_query("*",$table,'company_id = '.$val.' AND '.$where,"ragso1 ASC");
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
                     if ($r['company_id'] == $val) {
                         $selected = "selected";
                     }
                     echo "\t\t <option value=\"".$r['company_id']."\" $selected >".intval($r['company_id'])."-".$r["ragso1"]."</option>\n";
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
      //echo "\t<input type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
	  /** ENRICO FEDELE */
	  /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
	  echo '<button type="submit" class="btn btn-default btn-sm" name="search_str"><i class="glyphicon glyphicon-search"></i></button>';
	  /** ENRICO FEDELE */
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
?>

<?php
echo '<form method="POST" name="myform">
		<input type="hidden" value="'.$form['hidden_req'].'" name="hidden_req" />
		<div id="admin_main" >
			<table border="1" class="Tmiddle">
				<tr class="FacetFormHeaderFont">
					<td class="FacetDataTD text-center">
						<a href="../config/admin_utente.php?Login='.$admin_aziend['Login'].'&Update">
							<img class="img-circle usr-picture" src="view.php?table=admin&field=Login&value='.$admin_aziend['Login'].'" alt="'.$admin_aziend['Cognome'].' '.$admin_aziend['Nome'].'" title="'.$script_transl['change_usr'].'" />
						</a>
					</td>
					<td id="admin_welcome">
						'.ucfirst($msg)." ".$admin_aziend['Nome'].' (ip='.$admin_aziend['last_ip'].')&nbsp;'.$script_transl['access'].$admin_aziend['Access'].$script_transl['pass'].gaz_format_date($admin_aziend['datpas']).'<br />
						<div id="admin_p_logout">
							'.$script_transl['logout'].' &rarr; <input name="logout" type="submit" value=" Logout ">
						</div>
					</td>
					<td align="center" bgcolor="#'.$admin_aziend['colore'].'">
						'.$script_transl['company'].'
						<a href="../config/admin_aziend.php">
							<img src="view.php?table=aziend&value='.$form['company_id'].'" width="200" alt="Logo" border="0" title="'.$script_transl['upd_company'].'" />
						</a>
						<br />'.$script_transl['mesg_co'][2].' &rarr; ';
selectCompany('company_id',$form['company_id'],$form['search']['company_id'],$form['hidden_req'],$script_transl['mesg_co']);
echo '				</td>
				</tr>
			</table>
		</div>';

		echo '<div class="container custom-tab">';	
		
		
	$result    = gaz_dbi_dyn_query("*", $gTables['menu_usage'] , ' company_id="'. $form['company_id'].'" AND adminid="'.$admin_aziend['Login'].'" ',' click DESC, last_use DESC',0,8);
	$res_last  = gaz_dbi_dyn_query("*", $gTables['menu_usage'] , ' company_id="'. $form['company_id'].'" AND adminid="'.$admin_aziend['Login'].'" ',' last_use DESC, click DESC',0,8);
	
	if ( gaz_dbi_num_rows($result)>0 ) {
		while ($r = gaz_dbi_fetch_array($result)) {
			$rl = gaz_dbi_fetch_array($res_last);
			?>
			<div class="row">
				<div class="col-xs-6">
					<a href="<?php 
						if ( $r["link"]!="" ) echo '../../modules'.$r["link"];
						else echo "&nbsp;";
					?>" type="button" class="btn btn-default btn-success btn-lista">
					<span ><?php echo $r["click"].' click - <b>'.$r["name"].'</b>'; ?></span></a>
				</div>
				<div class="col-xs-6">
					<a href="<?php 
						if ( $rl["link"]!="" ) echo '../../modules'.$rl["link"];
						else echo "&nbsp;";
					?>" type="button" class="btn btn-default btn-success btn-lista">
					<span ><?php 
						echo gaz_time_from(strtotime($rl["last_use"])).' - <b>'.$rl["name"].'</b>'; 
					?></span></a>
				</div>
			</div>
			<?php
		}
	}
	echo '</div>';

	echo '<div id="admin_footer" class="small text-center">
			<div align="center">
				GAzie Version: '.$versSw.' Software Open Source (lic. GPL) '.$script_transl['business'].' '.$script_transl['proj'].'<a  target="_new" title="'.$script_transl['auth'].'" href="http://http://www.devincentiis.it"> http://www.devincentiis.it</a>
			</div>
			<div>
			<table border="0" class="Tmiddle">
				<tr align="center">
					<td>
						<a href="http://gazie.sourceforge.net" target="_new" title="'.$script_transl['devel'].' www.gazie.it">
							<img src="../../library/images/gazie.gif" height="38" border="0" />
						</a>';
	foreach ($script_transl['strBottom'] as $value){
			echo '<a href="'.$value['href'].'" title="'.$value['title'].'" target="_new">
					<img src="../../library/images/'.$value['img'].'" border="0" />
				  </a>';
	}
	echo '			</td>
				</tr>
			</table>';
	if (file_exists("help/".$admin_aziend['lang']."/admin_help.php")) {
		include("help/".$admin_aziend['lang']."/admin_help.php");
	}
	echo '</div>';
?>
</div><!-- chiude div container role main --></body>
</html>