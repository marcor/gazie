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
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$msg = array('err' => array(), 'war' => array());
$gForm = new magazzForm();

if(isset($_GET['delete'])) {
    gaz_dbi_del_row($gTables['distinta_base'], 'id', intval($_GET['delete']));
    header("Location: ../magazz/admin_artico.php?Update&codice=".$_GET['codcomp']);
	exit;
}

if(!isset($form['ritorno'])){
	$form['ritorno']=$_SERVER['HTTP_REFERER'];	
}else{
	$form['ritorno']=$_POST['ritorno'];	
}
if (isset($_GET['codice'])){
    $codcomp = filter_var($_GET['codice'],FILTER_SANITIZE_STRING);
}else{
    header("Location: ../magazz/admin_artico_compost.php?codice=".$codcomp );
	
}

if(isset($_POST['Update'])||isset($_GET['Update'])){
    $toDo = 'update';
}else{
    $toDo = 'insert';
}

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    $form['hidden_req'] = $_POST['hidden_req'];
    $form['cosear'] = filter_var($_POST['cosear'],FILTER_SANITIZE_STRING);
    $form['codart'] = filter_var($_POST['codart'],FILTER_SANITIZE_STRING);
    $form['quanti'] = floatval($_POST['quanti']);
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
        $form['hidden_req'] = '';
	    $form['cosear'] = "";
	    $form['codart'] = "";
	    $form['quanti'] = '';
} else { //se e' il primo accesso per INSERT
        $form['hidden_req'] = '';
	    $form['cosear'] = "";
	    $form['codart'] = "";
	    $form['quanti'] = '';
}

if(isset($_POST['OKsub'])&&$_POST['OKsub']=="Salva"){
    $qta=$_POST['qta'];
    foreach ($qta as $val=>$v){
		if (floatval($v)<0.00001) {
			$msg['err'][] = 'quarow';
		}
	}
	// controllo se l'articolo che sto aggiungendo è un genitore e quindi un assurdo...
	$ctrl_exist_new_codart=$gForm->buildTrunk($codcomp,$form['codart']);
	if ($ctrl_exist_new_codart==$codcomp){
			$msg['err'][] = 'artpar';
	}
	if(floatval($form['quanti'])>=0.00001&&strlen($form['codart'])<=2){
			$msg['err'][] = 'codart';
	} elseif(floatval($form['quanti'])<0.00001&&strlen($form['codart'])>2){
			$msg['err'][] = 'quanti';
	}

	if (count($msg['err']) == 0) {// nessun errore
        foreach ($qta as $val=>$v){
            gaz_dbi_table_update ("distinta_base", array ("0"=>"id","1"=>$val), array("quantita_artico_base"=>$v) );
        }
		if($form['quanti']>=0.00001&&strlen($form['codart'])>2){
			$rx=gaz_dbi_get_row($gTables['distinta_base'], 'codice_composizione', $codcomp, "AND codice_artico_base ='". $form['codart'] . "'");
			if(!$rx){
				gaz_dbi_query("INSERT INTO " . $gTables['distinta_base'] . "(codice_composizione,codice_artico_base,quantita_artico_base) VALUES ('".$codcomp. "','".$form['codart']."','". $form['quanti'] . "')");
			} else {
				$msg['err'][] = 'artexi';
			}
		}
		if (count($msg['err']) == 0) { // tutto è andato a buon fine, ricarico la pagina con i nuovi valori
			header ( 'location: ../magazz/admin_artico_compost.php?Update&codice='.$codcomp);
		}
	}
}

require("../../library/include/header.php");
$script_transl = HeadMain(0,array('custom/autocomplete'));

?>
<style>
@media only screen and (min-width: 762px) {
	.pull-gazie{
         float:right;
	}
} 
</style>
<script>
$(function(){
	$("html, body").delay(500).animate({scrollTop: $('#search_cosear').offset().top},'slow', function() {
        $("#search_cosear").focus();
    });
});
function itemErase(id,descri,codcomp){
	$(".compost_name").append('ID:'+id+' -'+descri);
	//alert(descri);
	$("#confirm_erase").dialog({
		modal: true,
		show: "blind",
		hide: "explode",
		buttons: {
			No: function() {
				$(".compost_name").empty();
				$( this ).dialog( "close" );
			},
			Togli: function() {
				window.location.href = 'admin_artico_compost.php?delete='+id+'&codcomp='+codcomp;
			}

		  },
		  close: function(){	
			$(".compost_name").empty();
		  }
		});
}		
</script>

<form method="POST" name="form" enctype="multipart/form-data">
<?php
    echo '<input type="hidden" name="ritorno" value="' . $form['ritorno'] . '" />';
    echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
	echo '<input type="hidden" name="hidden_req" value="'.$form['hidden_req'].'" />';
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
 echo '<div class="container-fluid">';
		$color='eeeeee';
		$art=gaz_dbi_get_row($gTables['artico'],"codice",$codcomp);
		$data=$gForm->getBOM($codcomp);
        echo '<div class="panel panel-default"><div class="panel-heading"><h4>Distinta base della composizione: <a class="btn btn-md btn-success" href="admin_artico.php?Update&codice=' . $codcomp . '">'.$codcomp.'</a> -'.$art['descri']."\n</h4>".'</div><div class="panel-body">';
		if (count($data)>=1){
        echo '<ul class="col-xs-12 col-sm-12 col-md-11 col-lg-10">';
		foreach($data as $k0=>$v0) {
			$icona=(is_array($v0['codice_artico_base']))?'<a class="btn btn-xs btn-warning collapsible" id="'.$v0[2].'" data-toggle="collapse" data-target=".' . $v0[2] . '"><i class="glyphicon glyphicon-list"></i></a>':'';
			echo '<li><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-success" href="admin_artico.php?Update&amp;codice=' . $v0[2] . '">'.$v0[2].'</a> - '.$v0['descri'].' '.$icona.' _ _ _ _ <a class="btn btn-xs btn-danger" onclick="itemErase('.intval($v0['id']).',\''.$v0['descri'].'\',\''.$codcomp.'\');">  togli X </a><span class="pull-gazie"> '.$v0['unimis'].':<input type="number" style="height:25px;width:80px;" step="any" min="0.00001" name="qta['.intval($v0['id']).']" value="'.floatval($v0['quantita_artico_base']).'" /> </span>  </div>';
			$color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
			if (is_array($v0['codice_artico_base'])){
			  echo '<ul class="collapse ' . $v0[2] . '">';
			  foreach($v0['codice_artico_base'] as $k1=>$v1) {
				  echo '<li><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-primary" href="admin_artico.php?Update&amp;codice=' . $v1[2] . '">'.$v1[2].'</a> - '.$v1['descri'].' <span class="pull-right">'.$v1['unimis'].': '.floatval($v1['quantita_artico_base']).'</span></div>';
				  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
				  if (is_array($v1['codice_artico_base']))	{
					echo '<ul>';
					foreach($v1['codice_artico_base'] as $k2=>$v2) {
					  echo '<li><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-info" href="admin_artico.php?Update&amp;codice=' . $v2[2] . '">'.$v2[2].'</a> - '.$v2['descri'].' <span class="pull-right"> '.$v2['unimis'].': '.floatval($v2['quantita_artico_base']).'</span></div>';
					  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
					  if (is_array($v2['codice_artico_base']))	{
						echo '<ul>';
						foreach($v2['codice_artico_base'] as $k3=>$v3) {
						  echo '<li><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-warning" href="admin_artico.php?Update&amp;codice=' . $v3[2] . '">'.$v3[2].'</a> - '.$v3['descri'].' <span class="pull-right"> '.$v3['unimis'].': '.floatval($v3['quantita_artico_base']).'</span></div>';
						  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
						  if (is_array($v3['codice_artico_base']))	{
							echo '<ul>';
							foreach($v3['codice_artico_base'] as $k4=>$v4) {
							  echo '<li><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-danger" href="admin_artico.php?Update&amp;codice=' . $v4[2] . '">'.$v4[2].'</a> - '.$v4['descri'].' <span class="pull-right"> '.$v4['unimis'].': '.floatval($v4['quantita_artico_base']).'</span></div>';
							  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
							  if (is_array($v4['codice_artico_base']))	{
								echo '<ul>';
								foreach($v4['codice_artico_base'] as $k5=>$v5) {
								  echo '<li><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-default" href="admin_artico.php?Update&amp;codice=' . $v5[2] . '">'.$v5[2].'</a> - '.$v5['descri'].' <span class="pull-right"> '.$v5['unimis'].': '.floatval($v5['quantita_artico_base']).'</span></div>';
								  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
								  if (is_array($v5['codice_artico_base']))	{
									echo '<ul>';
									foreach($v5['codice_artico_base'] as $k6=>$v6) {
									  echo '<li><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-basic" href="admin_artico.php?Update&amp;codice=' . $v6[2] . '">'.$v6[2].'</a> - '.$v6['descri'].' <span class="pull-right"> '.$v6['unimis'].': '.floatval($v6['quantita_artico_base']).'</span></div></li>';
									  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
									}
									echo "</ul>\n";
								  }
								  echo "</li>\n";
								}
								echo "</ul>\n";
							  }
							  echo "</li>\n";
							}
							echo "</ul>\n";
						  }
 		  				  echo "</li>\n";
						}
						echo "</ul>\n";
					  }
					  echo "</li>\n";
					}
					echo "</ul>\n";
				  }
				  echo "</li>\n";
			  }
  			  echo "</ul>\n";
			} else{
				
			}
			echo "</li>\n";
		}
		echo '</ul>';
}
echo '<div class="col-xs-12 col-md-6">Nuovo componente:'; 
$select_artico = new selectartico("codart");
$select_artico->addSelected($form['codart']);
$select_artico->output(substr($form['cosear'], 0, 20),'C',"");
	echo '</div><div class="col-xs-12 col-md-4"> Quantità:<input type="number" style="height:25px;" step="any" min="0.00001" value="'.$form['quanti'].'" name="quanti" />
</div><div class="col-xs-12 col-md-2">
		<input type="submit" class="btn btn-warning" name="OKsub" value="Salva">
	</div>
		</div>
</div>';

?>
    </div> <!-- chiude container -->
</form>
<div class="modal" id="confirm_erase" title="Togli articolo dalla composizione">
    <fieldset>
       <div class="compost_name"></div>
    </fieldset>
</div>
<?php
    require("../../library/include/footer.php");
?>