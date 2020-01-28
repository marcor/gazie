<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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

$admin_aziend=checkAdmin(9);

// Qui viene tenuto dagli sviluppatori la lista dei siti che hanno messo a disposizione il file di check della propria versione
$tutor[1] = array('zone'=>'Abruzzo','city'=>'Montesilvano (PE)','sms'=>'+393383121161','web'=>'http://www.devincentiis.it','check'=>'http://www.devincentiis.it/file_ver');
// fine lista
$configurazione = gaz_dbi_get_row($gTables['config'],'variable','update_url');
// se si ha un sito "personalizzato" per il download diverso da quello ufficiale su Sourceforge: modifico quello di default
$URI_files = gaz_dbi_get_row($gTables['config'],'variable','update_URI_files');
if (!empty($URI_files['cvalue'])){ $update_URI_files = $URI_files['cvalue']; }
require("../../library/include/header.php");
// Richiamo la class per l'aggiornamento
require("../../library/include/classes/GAzie/Upgrade.php");


if (isset($_POST['check'])){// se viene richiesta una modifica della fonte di check
    foreach ($_POST['check'] as $key => $value){
         if ($key != 'disabled'){
             //modifico il valore della configurazione sul DB
             gaz_dbi_put_row($gTables['config'], 'variable','update_url', "cvalue", $tutor[$key]['check']);
         } else {
             gaz_dbi_put_row($gTables['config'], 'variable','update_url', "cvalue", '' );
         }
    }
    $configurazione = gaz_dbi_get_row($gTables['config'],'variable','update_url');
}

function tutor_list($tutor,$configurazione,$script_transl)
{
    echo "<form method=\"POST\"><table class=\"Tlarge table table-striped table-bordered table-condensed table-responsive\">\n";
    echo "<tr><th class=\"FacetFieldCaptionTD\">".$script_transl['zone']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['city']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['sms']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['web']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['choice']."</th></tr>\n";
    foreach ($tutor as $key => $value){
            echo "<tr><td>".$value['zone']."</td>\n";
            echo "<td>".$value['city']."</td>\n";
            echo "<td>".$value['sms']."</td>\n";
            echo "<td align=\"center\"><a href=\"".$value['web']."\" target=\"_NEW\">".$value['web']."</a></td>\n";
            if (!empty($value['check']) and $configurazione['cvalue'] == $value['check']) {
               echo "<td class=\"FacetDataTD\" align=\"right\"><input disabled style=\"color:red;\" type=\"submit\" value=\"".$script_transl['check_value'][1]."\" name=\"check[$key]\" title=\"".$script_transl['check_title_value'][1]."\" /></td></tr>\n";
            } else {
               echo "<td align=\"right\"><input type=\"submit\" value=\"".$script_transl['check_value'][0]."\" name=\"check[$key]\" title=\"".$script_transl['check_title_value'][0]."\" /></td></tr>\n";
            }
    }
    echo "<tr><td colspan=\"5\" class=\"FacetDataTD\" align=\"right\"><input type=\"submit\" value=\"".$script_transl['all_disabling'][0]."\" name=\"check[disabled]\" title=\"".$script_transl['all_disabling'][1]."\" /></td></tr>\n";
    echo "</table></form>";
}

$script_transl=HeadMain();
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title']; ?></div>
<br />
<?php
if ($configurazione['cvalue']) {
   $remote_id = @file_get_contents($configurazione['cvalue']);
   if (preg_match("/^([0-9]{1,2}).([0-9]{1,2})/",$remote_id,$regs)){
      // versione locale presa da gconfig.php
      $pz_local = explode(".", GAZIE_VERSION);
      $pz_remote = explode(".", $remote_id);
      $local = $pz_local[0] * 100 + $pz_local[1];
      $remote = $regs[1]*100 + $regs[2];
      if ($remote <= $local) {
         $newversion = false;
      } else {
         $newversion = true;
      }
      if ($newversion) {
        echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['new_ver1'].$regs[1]. $regs[2].$script_transl['new_ver2'].": <a href=\"".$update_URI_files."\" target=\"_blank\">".$update_URI_files."</a></div>";
      } else {
        echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['is_align']."(".$remote_id.")</div>";
        tutor_list($tutor,$configurazione,$script_transl);
      }
   } else {
        echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['no_conn']."<br />".$configurazione['cvalue']."</div>";
        tutor_list($tutor,$configurazione,$script_transl);
   }
} else {
    echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['disabled'].": </div>";
    tutor_list($tutor,$configurazione,$script_transl);
}

$file = isset($_FILES['file']) ? $_FILES['file'] : [];
$upgrade = new \GAzie\Upgrade;
$deleted_folder = \GAzie\GAzie::factory()->getConfig()->getDirectories();
$success = $upgrade->zip($file);
?>
<br><br><br>
<div class="container text-center">
<div class="col-md-12">
	<h3>Aggiornamento Automatico</h3>
</div>
<?php
if ( !$success ) {
	foreach( $upgrade->getErrors() as $error) {
?>
<div class="col-md-12 alert alert-danger">
<?= $error; ?>
</div>
<?php
	}
}
?>
<?php
if ( $success ) {
?>
<div class="col-md-12 alert alert-success">
Esci e rientra nella nuova versione! <a href="../../modules/root/logout.php">Logout</a>
</div>
<?php
}
?>
<div class="col-md-4"></div>
<div class="col-md-4">
<form enctype="multipart/form-data" method="post" class="form-inline" onsubmit="return showAlertBackup();">
  <div class="form-group">
	<input id="file" type="file" name="file" class="text-center">  
  </div>
  <button type="submit" class="btn btn-primary" id="save" name="save" ><i class="icon-ok icon-white"></i> Upload</button>
</form>
</div>
<script>
function showAlertBackup() {
	alert('Saranno cancellate le cartelle:\n <?= implode($deleted_folder,'\n'); ?>');
	return confirm("Verifica Backup Database e File di Configurazione!");
}
</script>
<div class="col-md-4"></div>
</div>

<?php
require("../../library/include/footer.php");
?>
