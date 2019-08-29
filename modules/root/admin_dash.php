<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
function getDashFiles()
{
	$fileArr=[];
	foreach(glob('../*', GLOB_ONLYDIR) as $dir) {
	    if ($handle = opendir($dir)) {
			while ($file = readdir($handle)) {
				if(($file == ".")||($file == "..")||($file == "dash_order_update.php")) continue;
				if(!preg_match("/^dash_[A-Za-z0-9 _ .-]+\.php$/",$file)) continue; //filtro i nomi contenenti il suffisso dash e estensione .php
				$fileArr[] = str_replace('../', '', $dir).'/'.$file; // push sull'accumulatore con una stringa adatta alla colonna del DB
			}
		}
	}
	return $fileArr;
}
 
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/bootstrap-switch'));

// eseguo l'aggiornamento del db se richiesto
if(isset($_POST['addrow'])&&!empty($_POST['addrow'])){ // aggiungo il widget
	gaz_dbi_query("INSERT INTO " . $gTables['breadcrumb'] . "(position_order,exec_mode,file,titolo,adminid)  SELECT MAX(position_order)+1,'2','".filter_var($_POST['addrow'],FILTER_SANITIZE_STRING).".php','".filter_var($_POST['title-'.$_POST['addrow']], FILTER_SANITIZE_STRING)."','" . $admin_aziend['user_name'] . "' FROM " . $gTables['breadcrumb']);
}elseif(isset($_POST['delrow'])&&!empty($_POST['delrow'])){ // elimino il widget
	gaz_dbi_query("DELETE FROM ".$gTables['breadcrumb']." WHERE file = '".$_POST['delrow'].".php' AND adminid = '".$admin_aziend['user_name']."'");
}
?>
<style>
.vertical-align {
    display: flex;
    align-items: center;
}
</style>
<script type="text/javascript">
    $(function () {
		$(".yn_toggle").bootstrapSwitch({
			on: 'YES',
			off: 'NO',
			onClass: 'success'}, true);
		$(".yn_toggle").change(function () {  
			var str = $(this).attr('name'); 
            if($(this).is(":checked")){
				$('#delrow').disabled = true;
				$('#addrow').disabled = false;
				$('#addrow').val(str);
            } else if($(this).is(":not(:checked)")){
				$('#addrow').disabled = true;
				$('#delrow').disabled = false;
				$('#delrow').val(str);
            }			
			$('form#widform').submit();
			}) 
    });
</script>
<form id="widform" method='post' class="form-horizontal">
  <div class="mainbox mainbox col-md-offset-3 col-md-6">
     <div class="panel panel-default">
     <input type="hidden" id="delrow" name="delrow" />
     <input type="hidden" id="addrow" name="addrow" />
<?php
foreach(getDashFiles() as $w){
	$v=substr($w,0,-4);
	// controllo se sulla tabella del database ho il relativo rigo
	$widget_exist=gaz_dbi_get_row($gTables['breadcrumb'], "adminid ='".$admin_aziend['user_name']."' AND file", $w); 
	$cked='';
	if($widget_exist){
		$cked='checked';
	}else{
		$widget_exist['titolo']='';
	}
	echo '<div class="row vertical-align">
			<div class="col-xs-7" title="'.$v.'"><img class="img-thumbnail" src="../'.$v.'.png">
			</div>
			<div class="col-xs-3">
			<input type="text"  name="title-'.$v.'" value="'.$widget_exist['titolo'].'"/>
			</div>
			<div class="col-xs-2">
			<input type="checkbox" '.$cked.' class="yn_toggle" name="'.$v.'" data-on-text="YES" data-off-text="NO" />
			</div>
		 </div>';
}
?> 
	</div><!-- chiude panel  -->
  </div><!-- chiude mainbox  -->
</form>
<?php
require("../../library/include/footer.php");
?>