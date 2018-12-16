<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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

$admin_aziend = checkAdmin();

require("../../library/syncronize/opencart.php");

?>
<?php
require("../../library/include/header.php");
$script_transl = HeadMain();

// Ottengo la lista dei clienti Opencart
$anagrs = Syncro\Anagr::getAll();
?>
<div class="container">
  <div class="row">
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
        <td>ID<td>
        <td>Ragione Sociale<td>
        <td>Indirizzo<td>
        <td>Codice Fiscale<td>
        <td>Partita IVA<td>
      </tr>	
    </table>
   </div>
  </div>
</div>
<?php
require("../../library/include/footer.php");
?>

