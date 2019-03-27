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
$admin_aziend=checkAdmin();
$msg = "";
require("../../library/include/header.php");
$script_transl = HeadMain();
require("lang.".$admin_aziend['lang'].".php");

if (isset($_GET['all'])) {
	$where = "";
	$passo = 100000;
} else {
	$implode = array();
	if (isset($_GET['movimento']) && !empty($_GET['movimento'])) {
		$movimento = $_GET['movimento'];
		$implode[] = $gTables['movmag'].".id_mov = " . $_GET['movimento'];
		$passo = 100000;
	}
	
	if (isset($_GET['causale']) && !empty($_GET['causale'])) {
		$causale = $_GET['causale'];
		$implode[] = "caumag LIKE '" . $_GET['causale'] . "%'";
		$passo = 100000;
	}

	if (isset($_GET['documento']) && !empty($_GET['documento'])) {
		$documento = $_GET['documento'];
		$implode[] = "desdoc LIKE '%".$_GET['documento']."%'";
		$passo = 100000;
	}
	
	if (isset($_GET['articolo']) && !empty($_GET['articolo'])) {
		$articolo = $_GET['articolo'];
		$implode[] = "artico LIKE '%".$_GET['articolo']."%'";
		$passo = 100000;
	}
	
	if (isset($_GET['lotto']) && !empty($_GET['lotto'])) {
		$idlotto = $_GET['lotto'];
		$implode[] = "id_lotmag LIKE '%".$_GET['lotto']."%'";
		$passo = 100000;
	}
	
	$where = implode(" AND ", $implode);
}

if (!isset($_GET['flag_order']) || empty($_GET['flag_order'])) {
   $orderby = "id_mov desc";
   $field = 'id_mov';
   $flag_order = 'DESC';
   $flagorpost = 'ASC';
}

?>
<div align="center" class="FacetFormHeaderFont "><?php echo $script_transl[3].$script_transl[0]; ?></div>
<?php

$recordnav = new recordnav($gTables['movmag'], $where, $limit, $passo);
$recordnav -> output();

?>
<form method="GET">
  <div class="table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
	<tr>
		<td class="FacetFieldCaptionTD">
		  <input type="text" name="movimento" placeholder="Movimento" class="input-sm form-control"  value="<?php echo (isset($movimento))? $movimento : ""; ?>" maxlength ="6" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="causale" placeholder="<?php echo $strScript['admin_movmag.php'][2];?>" class="input-sm form-control" value="<?php echo (isset($causale))? $causale : ""; ?>" maxlength="6" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="documento" placeholder="<?php echo $script_transl[8];?>" class="input-sm form-control" value="<?php echo (isset($documento))? $documento : ""; ?>" maxlength="15" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="articolo" placeholder="<?php echo $script_transl[5];?>" class="input-sm form-control" value="<?php echo (isset($articolo))? $articolo : ""; ?>" maxlength="15" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="lotto" placeholder="<?php echo "ID ",$script_transl[11];?>" class="input-sm form-control" value="<?php echo (isset($idlotto))? $idlotto : ""; ?>" maxlength="15" size="3" tabindex="1">
		</td>
		<td class="FacetFieldCaptionTD" colspan="3">
			<input type="submit" class="btn btn-xs btn-default" name="search" value="<?php echo $script_transl['search'];?>" tabindex="1" onClick="javascript:document.report.all.value=1;">
			<input type="submit" class="btn btn-xs btn-default" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value=1;">
		</td>
	</tr>

<?php
$table = $gTables['movmag']." LEFT JOIN ".$gTables['caumag']." on (".$gTables['movmag'].".caumag = ".$gTables['caumag'].".codice)
         LEFT JOIN ".$gTables['artico']." ON (".$gTables['movmag'].".artico = ".$gTables['artico'].".codice)
		 LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['movmag'].".clfoco = ".$gTables['clfoco'].".codice)
         LEFT JOIN ".$gTables['rigdoc']." ON (".$gTables['movmag'].".id_rif = ".$gTables['rigdoc'].".id_rig)
		 LEFT JOIN ".$gTables['lotmag']." ON (".$gTables['movmag'].".id_lotmag = ".$gTables['lotmag'].".id)
		 LEFT JOIN ".$gTables['orderman']." ON (".$gTables['movmag'].".id_orderman = ".$gTables['orderman'].".id)";
$result = gaz_dbi_dyn_query ($gTables['movmag'].".*, ".$gTables['artico'].".descri AS descart, ".$gTables['caumag'].".descri AS descau, ".$gTables['lotmag'].".*, ".$gTables['rigdoc'].".id_tes AS testata", $table, $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_mov = array  (
            "n.ID" => "id_mov",
            $script_transl[4] => "datreg",
            $strScript["admin_movmag.php"][2] => "caumag",
            $script_transl[8] => "",
            $script_transl[5] => "artico",
			$script_transl[11] => "identifier",
            $script_transl[6] => "",
            $script_transl[7] => "",
            $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_mov);
echo '<tr>';
$linkHeaders -> output();
echo '</tr>';
$anagrafica = new Anagrafica();

/** ENRICO FEDELE */
/* Inizializzo la variabile */
$tot_movimenti = 0;
/** ENRICO FEDELE */

while ($a_row = gaz_dbi_fetch_array($result)) {
    $partner = $anagrafica->getPartner($a_row["clfoco"]);
    $title =  $partner['ragso1']." ".$partner['ragso2'];
	$descri=$a_row["descart"];
    $valore = CalcolaImportoRigo($a_row['quanti'], $a_row['prezzo'], $a_row['scorig']) ;
    $valore = CalcolaImportoRigo(1, $valore, $a_row['scochi']) ;
    echo "<tr>\n";
    echo "<td class=\"FacetDataTD\"><a class=\"btn btn-xs btn-default\" href=\"admin_movmag.php?id_mov=".$a_row["id_mov"]."&Update\" title=\"".ucfirst($script_transl['update'])."!\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$a_row["id_mov"]."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($a_row["datreg"])." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["caumag"]." - ".$a_row["descau"]."</td>\n";
    if ($a_row['id_rif'] == 0) {
		if ($a_row['id_orderman']>0){
			echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../orderman/admin_orderman.php?Update&codice=".$a_row['id_orderman']."\">".$a_row['descau']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])." - ID: ".$a_row['id_orderman']."</a></td>\n";
		} else {
			echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</td>\n";
		}
    } else {
        if ($a_row['tipdoc'] == "ADT"
         || $a_row['tipdoc'] == "AFA"
         || $a_row['tipdoc'] == "AFC") {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../acquis/admin_docacq.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        } else {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../vendit/admin_docven.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        }
    }
   	echo "<td class=\"FacetDataTD\"  align=\"center\"><p data-toggle=\"tooltip\" data-placement=\"auto\" title=\"$descri\">".$a_row["artico"]."</p>";
	if ($a_row['id']>0) {
		echo "<td class=\"FacetDataTD\" align=\"center\">"."ID:".$a_row['id']." - ".$a_row['identifier']."</td>\n";
	} else {
		echo "<td class=\"FacetDataTD\"></td>";
	}
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_quantity($a_row["quanti"],1,$admin_aziend['decimal_quantity'])."</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($valore)." </td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_movmag.php?id_mov=".$a_row["id_mov"]."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>\n";
    echo "</tr>\n";
	/** ENRICO FEDELE */
	/* Incremento il totale */
	$tot_movimenti += $valore;
	/** ENRICO FEDELE */
}
	/** ENRICO FEDELE */
	/* Stampo il totale */
	//if($tot_movimenti!=0) {	//	Inizialmente avevo pensato di stampare il totale solo se diverso da zero, ma la cosa risulta fuorviante in alcuni casi
								//	meglio stamparlo sempre
		echo "<tr>
				<td colspan=\"7\" class=\"FacetFieldCaptionTD\" align=\"right\"><strong>TOTALE</strong></td>
				<td class=\"FacetFieldCaptionTD\" align=\"right\"><strong>".gaz_format_number($tot_movimenti)."</strong></td>
				<td class=\"FacetFieldCaptionTD\">&nbsp;</td>
			  </tr>";
	//}
	/** ENRICO FEDELE */
?>
     </table>
	</div>
</form>
<script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();   
});
</script>
<?php
require("../../library/include/footer.php");
?>