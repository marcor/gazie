<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend = checkAdmin(9);

$modal_ok_insert = false;
$modal = false;
if (isset($_POST['mode']) || isset($_GET['mode'])) {
    $pdb=gaz_dbi_get_row($gTables['company_config'], 'var', 'menu_alerts_check')['val'];
    $period=($pdb==0)?60:$pdb;
    $modal = true;
    if (isset($_GET['ok_insert'])) {
        $modal_ok_insert = true;
    }
}

if (count($_POST) > 10) {
	$error='&ok_insert';
    $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    foreach ($_POST as $k => $v) {
        $key=filter_var($k, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		if(substr($key,0,4)=='json'){
			$v=html_entity_decode($v, ENT_QUOTES, 'UTF-8');
			if (isJson($v)){
				$value=$v;
			} else {
				$value='ERRORE!!! JSON NON VALIDO!: '.filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
				$error='&json_error';
			}
		} else {
            $value=filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		}
        gaz_dbi_put_row($gTables['company_config'], 'var', $key, 'val', $value);
    }
    header("Location: config_aziend.php?mode=modal".$error);
    exit;
}

if ($modal === false) {
    require("../../library/include/header.php");
    $script_transl = HeadMain(0, array('custom/autocomplete'));
} else {
    $script = basename($_SERVER['PHP_SELF']);
    require("../../language/" . $admin_aziend['lang'] . "/menu.inc.php");
    require("./lang." . $admin_aziend['lang'] . ".php");
    if (isset($script)) { // se è stato tradotto lo script lo ritorno al chiamante
        $script_transl = $strScript[$script];
    }
    $script_transl = $strCommon + $script_transl;
}
$result = gaz_dbi_dyn_query("*", $gTables['company_config'], "1=1", ' id ASC', 0, 1000);
?>
<div align="center" class="FacetFormHeaderFont">
    <?php echo $script_transl['title']; ?><br>
</div>

<ul class="nav nav-pills">
        <li class="active"><a data-toggle="pill" href="#generale">Configurazione</a></li>
        <li class=""><a data-toggle="pill" href="#email">Email</a></li>
        <li style="float: right;"><div class="btn btn-warning" id="upsave">Salva</div></li>
</ul>
<div class="panel panel-default gaz-table-form div-bordered">
  <div class="container-fluid">
    <div class="tab-content">
        <div id="generale" class="tab-pane fade in active">
        <form method="post" id="sbmt-form"> 
        <?php 
        if ($modal) { ?>
        	<input type="hidden" name="mode" value="modal" />
        <?php 
        }
        if (isset($_GET["ok_insert"])) { ?>
            <div class="alert alert-success text-center head-msg" role="alert"><b>
                <?php echo "Le modifiche sono state salvate correttamente<br/>"; ?>
            </b></div>
        <?php }
        if (isset($_GET["json_error"])) { ?>
            <div class="alert alert-danger text-center head-msg" role="alert"><b>
                <?php echo "Il valore immesso non è un JSON valido!<br/>"; ?>
            </b></div>
        <?php }
        $mail_sender='';        
        if (gaz_dbi_num_rows($result) > 0) {
            while ($r = gaz_dbi_fetch_array($result)) {
                ?>
                <div class="row">
                  <div class="form-group" >
                    <label for="input<?php echo $r["id"]; ?>" class="col-sm-5 control-label"><?php echo $r["description"]; ?></label>
                    <div class="col-sm-7">
                        <?php
                        if($r['var']=='company_email_text'||substr($r['var'],0,4)=='json'){
                        ?>
                        <textarea id="input<?php echo $r["id"]; ?>" name="<?php echo $r["var"]; ?>" style="width:100%;"><?php echo $r['val']; ?></textarea>
						<?php
                        }else{
							if($r['var']=='reply_to'){
							 $mail_sender = $r['val'];
							}
                        ?>
                        <input type="<?php echo ((strpos($r["var"],"pass")===false&&strpos($r["var"],"psw")===false)?'text':'password'); ?>" class="form-control input-sm" id="input<?php echo $r["id"]; ?>" name="<?php echo $r["var"]; ?>" placeholder="<?php echo $r["var"]; ?>" value="<?php echo $r["val"]; ?>">
						<?php
						}
						?>
                    </div>
                  </div>
                </div><!-- chiude row  -->
                <?php
            }
        }
        ?>                    
        <div class="row">
            <div class="form-group" >
                <label class="col-sm-5 control-label"></label>
                <div class="col-sm-7 text-center">
                    <button type="submit" class="btn btn-warning">Salva</button>
                </div>
            </div>
        </div>
        </form>
    </div><!-- chiude generale  -->
    <div id="email" class="tab-pane fade">
			<div>Il test di configurazione email ti permette di verificare la configurazione della tua mail. <br><b>Salva</b> la configurazione prima di avviare il test. Verr&aacute; inviata una mail a <i><?php echo $mail_sender; ?></i>
        </div>
		</br></br><hr>
			<div id="btn_send" class="btn btn-default">TEST INVIO MAIL</div>
			<div id="reply_send"></div>
    </script>
    </div><!-- chiude email  -->
  </div><!-- chiude tab-content  -->
 </div><!-- chiude container-fluid  -->
</div><!-- chiude panel  -->
<script>
if ($(".head-msg").length) {
} 

$("#btn_send").click( function() {
	$.ajax({
		url: "?e-test=true",
		type: "GET",
		data: { 'e-test': true },
		success: function(json) {
			result = JSON.parse(json);
			alert(result.send);
			if (  result.send ) {		
		  		$("#reply_send").html( "<strong>Invio riuscito</strong><br><div>Controlla se ti è arrivata una email in <i><?php echo $mail_sender; ?></i>!</div>");
			} else {
				$("#reply_send").html("<strong>Invio FALLITO!</strong><br><div>Errore: "+result.error+"!</div>");
			}
		},
		error: function(richiesta,stato,errori){
 				$("#reply_send").html("<strong>Invio FALLITO!</strong><br><div>"+errori+"</div>");
		},
	})
});
<?php
if ($modal === false) {
?>    
$( "#upsave" ).click(function() {
    $( "#sbmt-form" ).submit();
});
<?php
} else {
?>
$("#sbmt-form").submit(function (e) {
    $.ajax({
        type: "POST",
        url: "config_aziend.php?mode=modal",
        data: $("#sbmt-form").serialize(), // serializes the form's elements.
        success: function (data) {
            $("#edit-modal .modal-sm").css('width', '100%');
            $("#edit-modal .modal-body").html(data);
		},
        error: function(data){
            alert(data);
        }
    });
    e.preventDefault(); // avoid to execute the actual submit of the form.
});
$( "#upsave" ).click(function() {
    $.ajax({
        type: "POST",
        url: "config_aziend.php?mode=modal",
        data: $("#sbmt-form").serialize(), // serializes the form's elements.
        success: function (data) {
            $("#edit-modal .modal-sm").css('width', '100%');
            $("#edit-modal .modal-body").html(data);
        },
        error: function(data){
            alert(data);
        }
    });
    e.preventDefault(); // avoid to execute the actual submit of the form.
});
<?php
}
?>
</script>
<?php
require("../../library/include/footer.php");

function isJson($str) {
	return !preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', preg_replace('/"(\\.|[^"\\\\])*"/', '', $str));	
}
?>