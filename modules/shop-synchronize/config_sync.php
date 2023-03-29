<?php
/*
	  --------------------------------------------------------------------------
	  GAzie - Gestione Azienda
	  Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
	  (http://www.devincentiis.it)
	  <http://gazie.sourceforge.net>
	  --------------------------------------------------------------------------
	  SHOP SYNCHRONIZE è un modulo creato per GAzie da Antonio Germani, Massignano AP
	  Copyright (C) 2018-2021 - Antonio Germani, Massignano (AP)
	  https://www.lacasettabio.it
	  https://www.programmisitiweb.lacasettabio.it
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
	  # free to use, Author name and references must be left untouched  #
	  --------------------------------------------------------------------------
-------------------------------------------------------------------------

*** ANTONIO GERMANI  ***
**Configurazione inpostazioni FTP per sincronizzazione con modulo shop-synchronize**
***

 */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin(9);
$getenable_sync = gaz_dbi_get_row($gTables['aziend'], 'codice', $admin_aziend['codice'])['gazSynchro'];
$enable_sync = explode(",",$getenable_sync);

  if (count($_POST) > 0) { // ho modificato i valori

    $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (!empty($_FILES['myfile']['name'])) {
			// cancello eventuale vecchio file e salvo il nuovo nella cartella files
			$path = DATA_DIR . 'files/' . $admin_aziend['codice'] . '/secret_key/';
			if (!file_exists($path)) { // se è la prima volta e non esiste la cartella la creo
				mkdir($path, 0777, true);
			}
			$exten = strtolower(pathinfo($_FILES['myfile']['name'], PATHINFO_EXTENSION));
			$file_pattern = $path.$_FILES['myfile']['name'];
			unlink ( $file_pattern );
			move_uploaded_file($_FILES['myfile']['tmp_name'], $file_pattern);

		}

    foreach ($_POST as $k => $v) {
			if ($k=="chiave" AND !empty($_FILES['myfile']['name'])){

				if ( $v !== $_FILES['myfile']['name']){
					unlink ($path.$v);
				}
				$v=$_FILES['myfile']['name'];
			}
      $value=filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $key=filter_var($k, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

      $res=gaz_dbi_put_row($gTables['company_config'], 'var', $key, 'val', $value);
    }

		$n=0;
		unset ($value);
		if (isset ($_POST['addval'])){
			foreach ($_POST['addval'] as $add) {
				if ($_POST['addvar'][$n]=="chiave" AND !empty($_FILES['myfile']['name'])){
					$add=$_FILES['myfile']['name'];
				}
				$value['val']=$add;
				$value['var']=$_POST['addvar'][$n];
				$value['description']=$_POST['adddes'][$n];

				gaz_dbi_table_insert('company_config', $value);
				$n++;
			}
		}
    if ($_POST['set_enable_sync']=="SI" && $enable_sync[0] == "shop-synchronize"){// se era già attivato ed è rimasto attivato
      // non faccio nulla
    }else{
      if ($_POST['set_enable_sync']=="SI" && $enable_sync[0] !== "shop-synchronize"){
        array_unshift($enable_sync , 'shop-synchronize');// aggiungo shopsync all'inizio dell'array
      } else {
        if ($enable_sync[0] == "shop-synchronize"){
          unset($enable_sync[0]);
        }
      }
      $set_sync=implode(",", $enable_sync);
      gaz_dbi_table_update("aziend", $admin_aziend['codice'], array("gazSynchro"=>$set_sync));// aggiorno i nomi dei moduli
    }

    header("Location: config_sync.php?ok");
    exit;
  }

//$script = basename($_SERVER['PHP_SELF']);
require('../../library/include/header.php');
	$script_transl = HeadMain();
require("../../language/" . $admin_aziend['lang'] . "/menu.inc.php");
require("./lang." . $admin_aziend['lang'] . ".php");
if (isset($script)) { // se è stato tradotto lo script lo ritorno al chiamante
    $script_transl = $strScript[$script];
}

$script_transl = $strCommon + $script_transl;
$result = gaz_dbi_dyn_query("*", $gTables['company_config'], "1=1", ' id ASC', 0, 1000);
$used_var=array('server','user','pass','ftp_path','Sftp','port','home','chiave','menu_alerts_check','path','keypass','accpass','img_limit');
if (gaz_dbi_num_rows($result) > 0) {
  while ($r = gaz_dbi_fetch_array($result)) {
    if (in_array($r['var'], $used_var)){
      ${$r['var']}["id"]=$r["id"];
      ${$r['var']}["description"]=$r["description"];
      ${$r['var']}["var"]=$r["var"];
      ${$r['var']}["val"]=$r["val"];
    }
  }
}
?>
<div align="center" class="FacetFormHeaderFont">
	Impostazioni per sincronizzazione sito web tramite il modulo shop-synchronize
    <br> di Antonio Germani
</div>


<div class="panel panel-default gaz-table-form div-bordered">
  <div class="container-fluid">
    <div class="tab-content">
      <div id="generale" class="tab-pane fade in active">
        <form method="post" id="sbmt-form" enctype="multipart/form-data">
          <?php if (isset($_GET["ok"])) { ?>
            <div class="alert alert-success text-center" role="alert">
                <?php echo "Le modifiche sono state salvate correttamente<br/>"; ?>
            </div>
          <?php }
          ?>



          <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $user["id"]; ?>" class="col-sm-5 control-label"><?php echo $user["description"]; ?></label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" id="input<?php echo $user["id"]; ?>" name="<?php echo $user["var"]; ?>" placeholder="<?php echo $user["var"]; ?>" value="<?php echo $user["val"]; ?>">

            </div>
            </div>
          </div><!-- chiude row  -->
          <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $server["id"]; ?>" class="col-sm-5 control-label"><?php echo $server["description"]; ?></label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" id="input<?php echo $server["id"]; ?>" name="<?php echo $server["var"]; ?>" placeholder="<?php echo $server["var"]; ?>" value="<?php echo $server["val"]; ?>">
            </div>
            </div>
          </div><!-- chiude row  -->
          <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $pass["id"]; ?>" class="col-sm-5 control-label"><?php echo $pass["description"]; ?></label>
            <div class="col-sm-7">
              <input type="password" class="form-control input-sm" id="input<?php echo $pass["id"]; ?>" name="<?php echo $pass["var"]; ?>" placeholder="<?php echo $pass["var"]; ?>" value="<?php echo $pass["val"]; ?>">
            </div>
            </div>
          </div><!-- chiude row  -->
          <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $ftp_path["id"]; ?>" class="col-sm-5 control-label"><?php echo $ftp_path["description"],". <p style='font-size:8px;'> Percorso FTP assoluto del server per raggiungere la cartella dei file di interfaccia a partire dalla posizione di accesso FTP </p>"; ?></label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" id="input<?php echo $ftp_path["id"]; ?>" name="<?php echo $ftp_path["var"]; ?>" placeholder="<?php echo $ftp_path["var"]; ?>" value="<?php echo $ftp_path["val"]; ?>">
            </div>
            </div>
          </div><!-- chiude row  -->
          <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $path["id"]; ?>" class="col-sm-5 control-label"><?php echo $path["description"],". <p style='font-size:8px;'> Percorso per raggiungere la cartella dei file di interfaccia a partire dal dominio del sito e compreso http(s). Ad esempio: https://shoptest.it/GAzie_sync/</p>"; ?></label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" id="input<?php echo $path["id"]; ?>" name="<?php echo $path["var"]; ?>" placeholder="<?php echo $path["var"]; ?>" value="<?php echo $path["val"]; ?>">
            </div>
            </div>
          </div><!-- chiude row  -->
          <div class="row">
            <div class="form-group" >
              <label for="input<?php echo $user["id"]; ?>" class="col-sm-5 control-label">Attiva la sincronizzazione automatica <p style='font-size:8px;'> Per un corretto allineamento di GAzie con l'e-commerce, si consiglia di mantere sempre attivato.</p></label>
              <div class="col-sm-7">
                <?php
                if ($enable_sync[0]=="shop-synchronize"){
                  ?>
                  <input type="radio" value="SI" name="set_enable_sync" checked="checked" >Si - No<input type="radio" value="NO" name="set_enable_sync">
                  <?php
                } else {
                  ?>
                  <input type="radio" value="SI" name="set_enable_sync">Si - No<input type="radio" value="NO" name="set_enable_sync" checked="checked">
                  <?php
                }
                ?>
              </div>
            </div>
          </div><!-- chiude row  -->
          <div class="row">
            <div class="form-group" >
              <label for="input<?php echo $menu_alerts_check["id"]; ?>" class="col-sm-5 control-label"><?php echo $menu_alerts_check["description"]; ?></label>
              <div class="col-sm-7">
                <input type="text" class="form-control input-sm" id="input<?php echo $menu_alerts_check["id"]; ?>" name="<?php echo $menu_alerts_check["var"]; ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57" placeholder="<?php echo $menu_alerts_check["var"]; ?>" value="<?php echo $menu_alerts_check["val"]; ?>">
              </div>
            </div>
          </div><!-- chiude row  -->

          <?php
          if (isset($accpass['id']) AND $accpass['id']>0){
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $accpass["id"]; ?>" class="col-sm-5 control-label"><?php echo $accpass["description"]; ?></label>
            <div class="col-sm-7">
              <input type="password" class="form-control input-sm" id="input<?php echo $accpass["id"]; ?>" name="<?php echo $accpass["var"]; ?>" placeholder="<?php echo $accpass["var"]; ?>" value="<?php echo $accpass["val"]; ?>">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          } else {
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="inputport" class="col-sm-5 control-label">Password di accesso ai file interfaccia shop-sync</label>
            <div class="col-sm-7">
              <input type="password" class="form-control input-sm" name="addval[]" >
              <input type="hidden" name="addvar[]" value="accpass">
              <input type="hidden" name="adddes[]" value="Password di accesso ai file di interfaccia shop-sync">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          }

          if (isset($Sftp['id']) AND $Sftp['id']>0){
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $Sftp["id"]; ?>" class="col-sm-5 control-label"><?php echo $Sftp["description"],". <p style='font-size:8px;'> Se impostato su sì, selezionare anche se si intende usare la password o il file della chiave segreta </p>"; ?></label>
            <div class="col-sm-3">

                <?php
              if ($Sftp["val"]=="SI"){
                ?>
                <input type="radio" value="SI" name="<?php echo $Sftp["var"]; ?>" checked="checked" >Si - No<input type="radio" value="NO" name="<?php echo $Sftp["var"]; ?>">
                <?php
              } else {
                ?>
                <input type="radio" value="SI" name="<?php echo $Sftp["var"]; ?>">Si - No<input type="radio" value="NO" name="<?php echo $Sftp["var"]; ?>" checked="checked">
                <?php
              }
              ?>
            </div>
            <div class="col-sm-4">
              <?php
              if ($keypass["val"]=="key"){
                ?>

                <input type="radio" value="key" name="<?php echo $keypass["var"]; ?>" checked="checked" >Key - Password<input type="radio" value="pass" name="<?php echo $keypass["var"]; ?>">
                <?php
              } else {
                ?>
                <input type="radio" value="key" name="<?php echo $keypass["var"]; ?>">Key - Password<input type="radio" value="pass" name="<?php echo $keypass["var"]; ?>" checked="checked">
                <?php
              }
              ?>
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          } else {
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="inputSftp" class="col-sm-5 control-label">Usa il protocollo di trasferimento file sicuro Sftp. Se impostato su sì, selezionare anche se si intende usare la password o il file della chiave segreta.</label>
            <div class="col-sm-3">
              <input type="radio" value="SI" name="addval[]">Si - No<input type="radio" value="NO" name="addval[]" checked="checked">
              <input type="hidden" name="addvar[]" value="Sftp">
              <input type="hidden" name="adddes[]" value="Usa il protocollo di trasferimento file sicuro Sftp">
            </div>

            <div class="col-sm-4">
            <select name="addval[]" id="cars" >
              <option value="pass">Password</option>
              <option value="key">File chiave segreta</option>
            </select>
              <input type="hidden" name="addvar[]" value="keypass">
              <input type="hidden" name="adddes[]" value="Usa password o file chiave segreta">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          }

          if (isset($chiave['id']) AND $chiave['id']>0){
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $chiave["id"]; ?>" class="col-sm-5 control-label"><?php echo $chiave["description"],". <p style='font-size:8px;'> Se impostato sopra, selezionare il file della chiave segreta da caricare. </p>"; ?></label>
            <div class="col-sm-7">
            <input type="file" id="myfile" name="myfile">
            <input type="text" class="form-control input-sm" id="input<?php echo $chiave["id"]; ?>" name="<?php echo $chiave["var"]; ?>" placeholder="<?php echo $chiave["var"]; ?>" value="<?php echo $chiave["val"]; ?>" disabled="disabled">
            <input type="hidden" id="input<?php echo $chiave["id"]; ?>" name="<?php echo $chiave["var"]; ?>" value="<?php echo $chiave["val"]; ?>">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          } else {
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="inputport" class="col-sm-5 control-label">Chiave segreta Sftp. Se impostato sopra, caricare il file della chiave segreta.</label>
            <div class="col-sm-7">
            <input type="file" id="myfile" name="myfile">
            <input type="text" class="form-control input-sm" name="addval[]" disabled="disabled" value="" >
            <input type="hidden" name="addval[]" value="SFTP_key">
            <input type="hidden" name="addvar[]" value="chiave">
            <input type="hidden" name="adddes[]" value="Chiave segreta Sftp">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          }

          if (isset($port['id']) AND $port['id']>0){
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $port["id"]; ?>" class="col-sm-5 control-label"><?php echo $port["description"],". <p style='font-size:8px;'> Se si usa il semplice FTP lasciare vuoto. </p>"; ?></label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" id="input<?php echo $port["id"]; ?>" name="<?php echo $port["var"]; ?>" placeholder="<?php echo $port["var"]; ?>" value="<?php echo $port["val"]; ?>">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          } else {
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="inputport" class="col-sm-5 control-label">Porta Sftp. Se si usa il semplice FTP lasciare vuoto.</label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" name="addval[]" >
              <input type="hidden" name="addvar[]" value="port">
              <input type="hidden" name="adddes[]" value="Porta Sftp">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          }

          if (isset($home['id']) AND $home['id']>0){
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $home["id"]; ?>" class="col-sm-5 control-label"><?php echo $home["description"],". <p style='font-size:8px;'> Se non si usa lasciare vuoto. </p>"; ?></label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" id="input<?php echo $home["id"]; ?>" name="<?php echo $home["var"]; ?>" placeholder="<?php echo $home["var"]; ?>" value="<?php echo $home["val"]; ?>">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          } else {
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="inputport" class="col-sm-5 control-label">ID per pubblicazione in home page.  Se non si usa lasciare vuoto.</label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" name="addval[]" >
              <input type="hidden" name="addvar[]" value="home">
              <input type="hidden" name="adddes[]" value="Id per pubblicazione in home page">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          }

          if (isset($img_limit['id']) AND $img_limit['id']>0){
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="input<?php echo $img_limit["id"]; ?>" class="col-sm-5 control-label"><?php echo $img_limit["description"],". <p style='font-size:8px;'> Evita il time-out del server; impostare a zero per escludere. </p>"; ?></label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" id="input<?php echo $img_limit["id"]; ?>" name="<?php echo $img_limit["var"]; ?>" placeholder="<?php echo $img_limit["var"]; ?>" value="<?php echo $img_limit["val"]; ?>">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          } else {
            ?>
            <div class="row">
            <div class="form-group" >
            <label for="inputport" class="col-sm-5 control-label">Limitare l'upload massivo delle immagini per evitare il timeout del server(0=senza limiti).</label>
            <div class="col-sm-7">
              <input type="text" class="form-control input-sm" name="addval[]"  onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="10" >
              <input type="hidden" name="addvar[]" value="img_limit">
              <input type="hidden" name="adddes[]" value="Limite immagini per upload massivo">
            </div>
            </div>
            </div><!-- chiude row  -->
            <?php
          }


        ?>
        <div class="row">
            <div class="form-group" >
                <div class="col-sm-6 text-center">
                    <button type="button" onclick="window.location.href='synchronize.php'" class="btn btn-primary">Indietro</button>
                </div>
                <div class="col-sm-6 text-center">
                    <button type="submit" class="btn btn-warning">Salva</button>
                </div>
            </div>
        </div>
        </form>
    </div><!-- chiude generale  -->

  </div><!-- chiude tab-content  -->
 </div><!-- chiude container-fluid  -->
</div><!-- chiude panel  -->

<?php
require("../../library/include/footer.php");
?>
