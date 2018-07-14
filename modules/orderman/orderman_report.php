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
$admin_aziend = checkAdmin();
$titolo = 'Elenco produzioni';
require("../../library/include/header.php");
$script_transl = HeadMain();

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "description like '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "description like '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "description like '".addslashes($auxil)."%'";
}


if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or ( isset($_POST['Update']))) {   //se non e' il primo accesso
} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
}
?>
<div align="center" class="FacetFormHeaderFont">Elenco produzioni</div>
<?php
$recordnav = new recordnav($gTables['orderman'], $where, $limit, $passo);
$recordnav -> output();
?>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
    	<thead>
            <tr>
                <td></td>
                <td class="FacetFieldCaptionTD">Descrizione:
                    <input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput" />
                </td>
                <td>
                    <input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;" />
                </td>
                <td>
                    <input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;" />
                </td>
            </tr>
            <tr>
<?php
	$result = gaz_dbi_dyn_query ('*', $gTables['orderman'], $where, $orderby, $limit, $passo);
	// creo l'array (header => campi) per l'ordinamento dei record
	$headers_orderman = array("Codice ID"      => "id",
							"Descrizione" => "description",
							"Tipo lavorazione"  => "order_type",
							"Informazioni aggiuntive" => "add_info",
							"Inizio produzione" => "datemi",
							"Durata in giorni" => "day_of_validity",
							"Cancella"    => ""
							);
	$linkHeaders = new linkHeaders($headers_orderman);
	$linkHeaders -> output();
?>
        	</tr>
        </thead>
        <tbody>
<?php
while ($a_row = gaz_dbi_fetch_array($result)) {
?>		<tr class="FacetDataTD">
			<td>
				<a class="btn btn-xs btn-success btn-block" href="admin_orderman.php?Update&codice=<?php echo $a_row['id']; ?>">
					<i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $a_row['id'];?>
				</a>
			</td>
			<td>
				<span class="gazie-tooltip" data-type="catmer-thumb" data-id="<?php echo $a_row['id']; ?>" data-title="<?php echo $a_row['add_info']; ?>"><?php echo $a_row['description']; ?></span>
			</td>
			<td align="center"><?php echo $a_row['order_type'];?></td>
			<td align="center"><?php echo $a_row['add_info'];?></td>
			<?php $b_row = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $a_row['id_tesbro']);?>
			<td align="center"><?php echo $b_row['datemi'];?></td>
			<td align="center"><?php echo $b_row['day_of_validity'];?></td>
			<td align="center">
				<a class="btn btn-xs btn-default btn-elimina" href="delete_orderman.php?id=<?php echo $a_row['id']; ?>&id_tesbro=<?php echo $a_row['id_tesbro']; ?>">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
			</td>
		</tr>
<?php
}
?>
    		</tbody>
        </table>
		</form>

<?php

require("../../library/include/footer.php");
?>