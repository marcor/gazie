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
require("../../modules/magazz/lib.function.php");
require("include/opencart/Api.php");
require("include/config.php");
require("include/opencart/customer.php");

$admin_aziend = checkAdmin();

require("include/gazie/opencart.php");

$errors = [];

// Syncronizza client da id opencart
$sync = boolval($_GET['sync']);
$id_oc = intval($_GET['id_oc']);

// set up params
$config = new Syncronize\Config;
$url = $config->getUrl();
 
$fields = array(
  'username' => $config->getUser(),
  'password' => $config->getPassword(),
);

$api = new Opencart\Api( $url, $fields['username'], $fields['password']);
if ( $sync ) {
	if ( $id_oc > 0 ) {
		$customer = new Opencart\Customer;
		$customer->setApi ( $api );		
		$customer->getById( $id_oc );
		$result_syncronize = Syncro\Anagr::syncCustomer($customer);
	        if ( !$result_syncronize ) 
			$errors[] = "Errore nella sincronizzazione di IdOpencart $id_oc";
	} else {
		$errors[] = "Id cliente non selezionato";
	}
}


$cs = $api->getCustomers();
$customers = Opencart\Customer::list_from_array( $cs );
?>

<?php
require("../../library/include/header.php");
$script_transl = HeadMain();

// Ottengo la lista dei clienti Opencart
$anagrs = Syncro\Anagr::getAll();
?>
<div class="container">
  <div class="row">
   <?php foreach ($errors as $e ) { ?>
	<div class="col-sm-12"><i><?= $e; ?></i></div>
   <?php } ?>
   <div class="col-sm-6">
    <div class="row center">
    Lista Anagrafiche ( Totali = <?= count($anagrs) ?> )
    </div>
    <table class="table table-striped Tmiddle">
      <tr>
        <th>ID</th>
        <th>Ragione Sociale</th>
        <th>Indirizzo</th>
        <th>Codice Fiscale</th>
        <th>Partita IVA</th>
      </tr>	
<?php foreach( $anagrs as $a ) { ?>
      <tr>
        <td><?= $a->getId(); ?></td>
        <td><?= $a->getRagso1(); ?></td>
        <td><?= $a->getAddress(); ?></td>
        <td><?= $a->getCodfis(); ?></td>
        <td><?= $a->getParIva(); ?></td>
      </tr>	
<?php } ?>
    </table>
   </div>
   <div class="col-sm-6">
    <div class="row center">
      Lista Clienti Opencart
    </div>
    <table class="table table-striped Tmiddle">
      <tr>
        <th>Syncronize</th>
        <th>ID</th>
        <th>Nome</th>
        <th>Cognome</th>
        <th>Email</th>
        <th>Telefono</th>
      </tr>	
<?php foreach( $customers as $a ) { ?>
      <tr>
      <td><a href="?sync=true&id_oc=<?= $a->getCustomerId(); ?>">Sincronizza</a></td>
        <td><?= $a->getCustomerId(); ?></td>
        <td><?= $a->getFirstname(); ?></td>
        <td><?= $a->getLastname(); ?></td>
        <td><?= $a->getEmail(); ?></td>
        <td><?= $a->getTelephone(); ?></td>
      </tr>	
<?php } ?>
    </table>
   </div>
  </div>
</div>
<?php
require("../../library/include/footer.php");
?>

