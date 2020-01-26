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

$errors = [];

// Syncronizza client da id opencart
$sync = isset($_GET['sync'])   ? boolval($_GET['sync']) : NULL;
$id_oc = isset($_GET['id_oc']) ? intval($_GET['id_oc']) : NULL;

// set up params
$config = new GAzie\Config;
$url = $config->getUrl();
 
$fields = array(
  'username' => $config->getUser(),
  'password' => $config->getPassword(),
);

$api = new Opencart\Api( $url, $fields['username'], $fields['password']);
$errors = new \View\Message;
if ( $sync ) {
	if ( $id_oc > 0 ) {
		$customer = new Opencart\Customer;
		$customer->setApi ( $api );		
		$customer->getById( $id_oc );
		$result_syncronize = GAzie\Anagra::syncCustomer($customer);
		if ( !$result_syncronize ) {
			
			$errors->setError("Errore! Anagrafica inserita o con problemi di inserimento");
		} else {
			// Inserisco il risultato
			$errors->setNotice("Inserisco $id_oc nella Tabella di Sincronizzazione");
			$errors->setNotice("L'id di sincro e' $id_oc " );
			$syncro = new \Syncro\SyncronizeOc;
			$syncro->setData('customer','anagra',"$id_oc","$result_syncronize");
			$syncro->add();
			$syncro_id =$syncro->save();
			if ( ! $syncro_id ) {
				$errors->setError("Errore! Non riesco a salvare nella tabella syncronize!");
			} else {
				$errors->setSuccess("Success! Cliente sincronizzato correttamente!");
			}
		}
	} else {
		$errors->setError("Error! Non hai selezionato il cliente!");
	}
}


$cs = $api->getCustomers();
$customers = Opencart\Customer::list_from_array( $cs );
?>

<?php
require("../../library/include/header.php");
$script_transl = HeadMain();

// Ottengo la lista dei clienti Opencart
$anagra = new \GAzie\Anagra();
$anagrs = $anagra->getAll();
?>
<div class="container">
  <div class="row">
	<?= $errors; ?> 
   <div class="col-sm-6">
    <div class="row center">
    Lista Anagrafiche ( Totali = <?= $anagrs->count() ?> )
    </div>
    <table class="table table-striped Tmiddle">
      <tr>
        <th>ID</th>
        <th>Ragione Sociale</th>
        <th>Indirizzo</th>
        <th>Codice Fiscale</th>
        <th>Partita IVA</th>
      </tr>	
<?php foreach( $anagrs->asObject() as $a ) { ?>
      <tr>
        <td><?= $a->id; ?></td>
	<td><a href="../../modules/vendit/admin_client.php?codice=<?= $a->id ?>&Update" target="_blank"><?= $a->ragso1; ?></a></td>
        <td><?= $a->indspe . " " . $a->capspe; ?></td>
        <td><?= $a->codfis; ?></td>
        <td><?= $a->pariva; ?></td>
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

