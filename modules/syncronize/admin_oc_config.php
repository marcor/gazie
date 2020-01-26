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
require("../../modules/magazz/lib.function.php");
require("../../library/include/classes/Autoloader.php");

$admin_aziend = checkAdmin();
$file = "download/sync-gazie.1.1.ocmod.zip";

if ( $_GET['download'] == true ) {
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header('Content-type: application/zip');
	header('Content-Disposition: attachment; filename="sync-gazie.ocmod.zip"');
	header("Content-Transfer-Encoding: binary");
	header('Pragma: public');
	readfile($file);
	exit;
}

require("../../library/include/header.php");

$script_transl = HeadMain();

# Ottengo configurazione


$config = new GAzie\Config;
$message = new \View\Message;
if ( $_POST ) {
	$data = array(
		'user' => $_POST['user'],		
		'pass' => $_POST['password'],		
		'url' => $_POST['url'],		
	);
	if ( $result = $config->putData($data) ) { 
		$message->setSuccess("Success! Configurazione API Opencart modificata!");
	} else {
		$message->setError("Error! Errore nella modifica delle configurazione!");
	}
	$config = new GAzie\Config;
}
?>
<div class="container">
  <div class="row">
   <?= $message; ?>
   <div class="col-sm-8">
    <div class="row center">
    Configurazione Accesso Opencart
    </div>
    <form method="POST">
    <table class="table table-striped Tmiddle">
      <tr>
        <th>User</th>
        <th>Password</th>
        <th>Shop Opencart</th>
      </tr>	
      <tr>
	<td><input type="text" name="user" id="user" value="<?= $config->getUser(); ?>" /></td>
	<td><input type="text" name="password" id="password" value="<?= $config->getPassword(); ?>" /></td>
	<td><input type="text" name="url" id="url" value="<?= $config->getUrl(); ?>" /></td>
      </tr>
      <tr>
	<td><input type="submit" name="sbt" value="Conferma" /></td>
	<td></td>
	<tr></td>
      </tr>	
    </table>
    </form>
   </div>
   <div class="col-sm-4">
	<h2>Opencart Extentions</h2>
	<a class="button" href="?download=true"><span>Download Extension Api Opencart</span></a>
   </div>
  </div>
</div>
<?php
require("../../library/include/footer.php");
?>

