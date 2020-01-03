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
require("../../library/include/classes/Autoloader.php");

$test_email = isset($_GET['e-test']);
$mail = new \GAzie\Mailer();
if ( $test_email ) {
	if ( $mail->testing() )
		echo json_encode(["send"=>true]);
	else
		echo json_encode([
			"send"=>false,
			"error"=> $mail->getError(),
		]);
	exit;
}

$admin_aziend = checkAdmin(9);

if (isset($_POST["elimina"])) {   // si vuole eliminare l'azienda
    // mi sposto con le attività sulla prima azienda 
    gaz_dbi_put_row($gTables['admin'], "user_name", $admin_aziend["user_name"], 'company_id', 1);
    // cancello il rigo dalla tabella aziend 
    gaz_dbi_del_row($gTables['aziend'], 'codice', $admin_aziend["company_id"]);
    // cancello il rigo dalla tabella admin_modules 
    gaz_dbi_del_row($gTables['admin_module'], 'company_id', $admin_aziend["company_id"]);
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
    header("Location: ../root/login_admin.php?tp=" . $table_prefix);
    exit;
} else {
    if (count($_POST) > 0) {
        foreach ($_POST as $key => $value) {
            gaz_dbi_put_row($gTables['company_config'], 'var', $key, 'val', $value);
        }
        header("Location: config_aziend.php?ok");
    }
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$result = gaz_dbi_dyn_query("*", $gTables['company_config'], "1=1", ' id ASC', 0, 1000);
?>
<div align="center" class="FacetFormHeaderFont">
    <?php echo $script_transl['title']; ?><br>
</div>
<div class="container-fluid">
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="pill" href="#generale">Configurazione</a></li>
        <li class=""><a data-toggle="pill" href="#email">Email</a></li>
        <?php
        if ($admin_aziend["company_id"] >= 2) {
            echo "<li><a data-toggle=\"pill\" href=\"#elimina\">Elimina azienda</a></li>\n";
        }
        ?>
    </ul>
    <div class="tab-content">
    <div id="generale" class="tab-pane fade in active">
        <form class="form-horizontal" method="post"> 
            <div class="FacetDataTD">
                <div class="divgroup">
                    <?php if (isset($_GET["ok"])) { ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo "Le modifiche sono state salvate correttamente<br/>"; ?>
                        </div>
                    <?php } ?>
                    <?php
                    if (gaz_dbi_num_rows($result) > 0) {
                        while ($r = gaz_dbi_fetch_array($result)) {
                            ?>

                            <div class="form-group">
                                <label for="input<?php echo $r["id"]; ?>" class="col-sm-5 control-label"><?php echo $r["description"]; ?></label>
                                <div class="col-sm-7">
                                    <?php
                                    if ($r['var'] == 'company_email_text') {
                                        ?>
                                        <textarea id="input<?php echo $r["id"]; ?>" name="<?php echo $r["var"]; ?>" class="mceClass" style="width:100%;"><?php echo $r['val']; ?></textarea>
                                        <?php
                                    } else {
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
                            <?php
                        }
                    }
                    ?>                    
                    <div class="form-group lastrow">
                        <div class="col-sm-offset-11 col-sm-1">
                            <button type="submit" class="btn btn-default">Salva</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="email" class="tab-pane fade">
	<div class="FacetDataTD">
	<div class="divgroup">
		<center>
			<div>Il test di configurazione email ti permette di verificare la configurazione della tua mail. <br><b>Salva</b> la configurazione prima di avviare il test. Verr&aacute; inviata una mail a <i><?= $mail->getSender(); ?></i>
		</br></br><hr>
			<div id="btn_send" class="btn btn-default">TEST INVIO MAIL</div>
			<div id="reply_send"></div>
		</center>
	</div>
	</div>
    </div>
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
   			  		$("#reply_send").html( "<strong>Invio riuscito</strong><br><div>Controlla se ti è arrivata una email in <i><?= $mail->getSender(); ?></i>!</div>");
				} else {
					$("#reply_send").html("<strong>Invio FALLITO!</strong><br><div>Errore: "+result.error+"!</div>");
				}
  			},
  			error: function(richiesta,stato,errori){
     				$("#reply_send").html("<strong>Invio FALLITO!</strong><br><div>"+errori+"</div>");
			},
		})
	});
    </script>
    <div id="elimina" class="tab-pane fade">
        <form class="form-horizontal" method="post"> 
            <div>
                <div class="divgroup">
                    <div class="form-group bg-danger">
                        <div class="col-sm-2 control-label">
                            <button class="btn btn-default" name="annulla" value="true">Annulla</button>
                        </div>
                        <div class="col-sm-8 control-label">
                            <p class="text-center text-danger">ATTENZIONE!!! CLICCANDO TUTTI I DATI DI QUESTA AZIENDA ANDRANNO DEFINITIVAMENTI ED IRRIMEDIABILMENTE PERSI!</p>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-danger" name="elimina" value="true">Elimina</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </div>
</div>
<?php
require("../../library/include/footer.php");
?>
