<?php
 /*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend=checkAdmin();
$titolo = 'Webmail';
require("../../library/include/header.php");
$script_transl=HeadMain();
$where = "var = \"ruburl\"";
$orderby = "id desc";

if ( !isset($_POST["id"]) ) $id = 1;
else $id = $_POST["id"];

$corrente = "";
$result = gaz_dbi_dyn_query('*',$gTables['company_config'], "var=\"ruburl\"", $orderby, $limit, $passo);
?>
	<div align="center" class="FacetFormHeaderFont">Gestione Rubrica Indirizzi</div><br>
	<table class="Tsmall">
	<?php 
	$headers_ = array  (
      "ID" => "id",
		"Descrizione" => "description",
      "Indirizzo" => "val",
      "Elim." => ""             
   );
	$linkHeaders = new linkHeaders($headers_);
	$linkHeaders -> output();
	while ($row = gaz_dbi_fetch_array($result)) {
		if ( $row["id"] == $id ) {
			$corrente = $row["val"];
			$default = "selected";
		} else {
			$default = "";
		}
		?>
			<tr>
				<td class="FacetDataTD">
				<a class="btn btn-xs btn-default" href="admin_ruburl.php?id=<?php echo $row["id"]; ?>&Update">
					<i class="glyphicon glyphicon-edit"></i> <?php echo $row["id"]; ?>
				</a>
				</td>
				<td class="FacetDataTD">
					<?php echo $row["description"]; ?>
				</td>
				<td class="FacetDataTD">
					<a href="ruburl.php?id=<?php echo $row['id']."\">".$row["val"]; ?>
				</td>
				<td class="FacetDataTD">
					<a class="btn btn-xs btn-default btn-elimina" href="delete_ruburl.php?id=<?php echo $row["id"]; ?>">
						<i class="glyphicon glyphicon-remove"></i></a>
				</td>
			</tr>
		<?php
	}
	?>	
	</table>
</div><!-- chiude div container role main --></body>
</html>