<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
if (isset($_POST["elimina"])) {   // si vuole eliminare l'azienda
    // mi sposto con le attività sulla prima azienda 
    gaz_dbi_put_row($gTables['admin'], "user_name", $admin_aziend["user_name"], 'company_id', 1);
    // cancello il rigo dalla tabella aziend 
    gaz_dbi_del_row($gTables['aziend'], 'codice', $admin_aziend["company_id"]);
    // cancello i righi dalla tabella admin_modules 
    gaz_dbi_del_row($gTables['admin_module'], 'company_id', $admin_aziend["company_id"]);
    // cancello i righi dalla tabella menu_usage 
    gaz_dbi_del_row($gTables['menu_usage'], 'company_id', $admin_aziend["company_id"]);
    $t_erased = array();
    $tp = $table_prefix . '_' . str_pad($admin_aziend["company_id"], 3, '0', STR_PAD_LEFT);
    //print $tp;
    $ve = gaz_dbi_query("SELECT CONCAT(  'DROP VIEW `', TABLE_NAME,  '`;' ) AS query, TABLE_NAME as tn FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME LIKE  '" . $tp . "%'");
    while ($r = gaz_dbi_fetch_array($ve)) {
        $t_erased[] = $r['tn'];
        gaz_dbi_query($r['query']);
    }
    $te = gaz_dbi_query("SELECT CONCAT(  'DROP TABLE IF EXISTS `', TABLE_NAME,  '`;' ) AS query, TABLE_NAME as tn FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE  '" . $tp . "%'");
    while ($r = gaz_dbi_fetch_array($te)) {
        $t_erased[] = $r['tn'];
        gaz_dbi_query($r['query']);
    }
    session_destroy();
    exit;
} elseif (isset($_POST["mode"])&& $_POST["mode"]=='modal') {   //  sono al primo accesso, non faccio nulla

} else { // ho modificato i valori
    if (count($_POST) > 0) {
        foreach ($_POST as $key => $value) {
            gaz_dbi_put_row($gTables['company_config'], 'var', $key, 'val', $value);
        }
        header("Location: config_aziend.php?ok");
    }
}
$script = basename($_SERVER['PHP_SELF']);
require("../../language/" . $admin_aziend['lang'] . "/menu.inc.php");
require("./lang." . $admin_aziend['lang'] . ".php");
if (isset($script)) { // se è stato tradotto lo script lo ritorno al chiamante
    $script_transl = $strScript[$script];
}
$script_transl = $strCommon + $script_transl;
$result = gaz_dbi_dyn_query("*", $gTables['company_config'], "1=1", ' id ASC', 0, 1000);
?>
<div align="center" class="FacetFormHeaderFont">
    <?php echo $script_transl['title']; ?><br>
</div>

<ul class="nav nav-pills">
        <li class="active"><a data-toggle="pill" href="#generale">Configurazione</a></li>
        <li class=""><a data-toggle="pill" href="#email">Email</a></li>
        <?php if ($admin_aziend["company_id"] >= 2) { ?>
        <li style="float: right;"><a data-toggle="pill" href="#elimina">Elimina azienda</a></li>
        <?php } ?>
        <li style="float: right;"><button type="submit" class="btn btn-warning">Salva</button></li>
</ul>
<div class="panel panel-default gaz-table-form div-bordered">
  <div class="container-fluid">
    <div class="tab-content">
        <div id="generale" class="tab-pane fade in active">
        <form method="post" id="sbmt-form"> 
        <?php if (isset($_GET["ok"])) { ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo "Le modifiche sono state salvate correttamente<br/>"; ?>
            </div>
        <?php }      
        if (gaz_dbi_num_rows($result) > 0) {
            while ($r = gaz_dbi_fetch_array($result)) {
                ?>
                <div class="row">
                  <div class="form-group" >
                    <label for="input<?php echo $r["id"]; ?>" class="col-sm-5 control-label"><?php echo $r["description"]; ?></label>
                    <div class="col-sm-7">
                        <?php
                        if ($r['var'] == 'company_email_text') {
                            ?>
                            <textarea id="input<?php echo $r["id"]; ?>" name="<?php echo $r["var"]; ?>" class="mceClass" style="width:100%;"><?php echo $r['val']; ?></textarea>
                            <?php
                        } else {
							if ($r['var'] == 'reply_to') {
								$mail_sender = $r['val'];
							}
                            ?>
                            <input type="<?php
                            if (strpos($r["var"], "pass") === false) {
                                echo "text";
                            } else {
                                echo "password";
                            }
                            ?>" class="form-control input-sm" id="input<?php echo $r["id"]; ?>" name="<?php echo $r["var"]; ?>" placeholder="<?php echo $r["var"]; ?>" value="<?php echo $r["val"]; ?>">
                               <?php } ?>
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
    <div id="elimina" class="tab-pane fade">
      <form method="post" id="sbmt-elimina"> 
        <h3 class="text-center text-danger col-xs-12">ATTENZIONE!!! Cliccando su <span class="btn btn-danger" style="cursor:default;"> Elimina </span> tutti i dati di questa azienda verranno DEFINITIVAMENTE E IRRIMEDIABILMENTE PERSI!</h3>
        <div class="col-xs-6"><button class="btn btn-default" name="annulla" value="true">Annulla</button></div>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-danger" name="elimina" value="<?php echo $admin_aziend['company_id'];?>">Elimina</button></div>
      </form>
    </div><!-- chiude elimina  -->
  </div><!-- chiude tab-content  -->
 </div><!-- chiude container-fluid  -->
</div><!-- chiude panel  -->
<script>
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
$("#sbmt-form").submit(function (e) {
    $.ajax({
        type: "POST",
        url: "config_aziend.php",
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
$("#sbmt-elimina").submit(function (e) {
    $.ajax({
        type: "POST",
        url: "config_aziend.php",
        data: {'elimina':true}, // serializes the form's elements.
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
</script>
<?php
require("../../library/include/footer.php");
?>
