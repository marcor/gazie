<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
	}
	
	if (isset($_GET['causale']) && !empty($_GET['causale'])) {
		$causale = $_GET['causale'];
		$implode[] = "caumag LIKE '" . $_GET['causale'] . "%'";
	}

	if (isset($_GET['documento']) && !empty($_GET['documento'])) {
		$documento = $_GET['documento'];
		$implode[] = "desdoc LIKE '%".$_GET['documento']."%'";
	}
	
	if (isset($_GET['campo']) && !empty($_GET['campo'])) {
		$articolo = $_GET['campo'];
		$implode[] = "campo_coltivazione LIKE '%".$_GET['campo']."%'";
	}
		
	if (isset($_GET['articolo']) && !empty($_GET['articolo'])) {
		$articolo = $_GET['articolo'];
		$implode[] = "artico LIKE '%".$_GET['articolo']."%'";
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
<div align="center" class="FacetFormHeaderFont "><?php echo $script_transl[14]; ?></div>
<form method="GET">
  <div class="table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
	<tr>
		<td class="FacetFieldCaptionTD">
		  <input type="text" name="movimento" placeholder="Movimento" class="input-sm form-control"  value="<?php echo (isset($movimento))? $movimento : ""; ?>" maxlength ="6" size="3" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="causale" placeholder="<?php echo $strScript['admin_movmag.php'][2];?>" class="input-sm form-control" value="<?php echo (isset($causale))? $causale : ""; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="documento" placeholder="<?php echo $script_transl[8];?>" class="input-sm form-control" value="<?php echo (isset($documento))? $documento : ""; ?>" maxlength="15" size="3" tabindex="1" class="FacetInput">
		</td>
		<!-- Antonio Germani - inserisco l'intestazione cerca per campi di coltivazione **DA COMPLETARE** qualcosa non va dopo aver cercato-->
		<td class="FacetFieldCaptionTD">
			<input type="text" name="campo" placeholder="<?php echo $script_transl[11];?>" class="input-sm form-control" value="<?php echo (isset($campo))? $campo : ""; ?>" maxlength="" size="3" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD">
			<input type="text" name="articolo" placeholder="<?php echo $script_transl[5];?>" class="input-sm form-control" value="<?php echo (isset($articolo))? $articolo : ""; ?>" maxlength="15" size="3" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD" colspan="4">
			<input type="submit" class="btn btn-xs btn-default" name="search" value="<?php echo $script_transl['search'];?>" tabindex="1" onClick="javascript:document.report.all.value=1;">
			<input type="submit" class="btn btn-xs btn-default" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value=1;">
		</td>
	</tr>

<?php
$table = $gTables['movmag']." LEFT JOIN ".$gTables['caumag']." on (".$gTables['movmag'].".caumag = ".$gTables['caumag'].".codice)
         LEFT JOIN ".$gTables['campi']." ON (".$gTables['movmag'].".campo_coltivazione = ".$gTables['campi'].".codice)
         LEFT JOIN ".$gTables['rigdoc']." ON (".$gTables['movmag'].".id_rif = ".$gTables['rigdoc'].".id_rig)"; // Antonio Germani ho scambiato la tabella clfoco con campi
		 $result = gaz_dbi_dyn_query ($gTables['movmag'].".*, ".$gTables['caumag'].".descri AS descau, ".$gTables['rigdoc'].".id_tes AS testata", $table, $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_mov = array  (
            "n.ID" => "id_mov",
			$script_transl[4] => "datdoc",
            $script_transl[15] => "datreg",
            $strScript["admin_movmag.php"][2] => "caumag",
            $script_transl[8] => "",
			$script_transl[11] => "",
			$script_transl[12] => "",
			$script_transl[13] => "",
            $script_transl[5] => "artico",
            $script_transl[6] => "",
            $script_transl[7] => "",
			$script_transl[16] => "",
            $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_mov);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['movmag'], $where, $limit, $passo);
$recordnav -> output();
$anagrafica = new Anagrafica();

/** ENRICO FEDELE */
/* Inizializzo la variabile */
$tot_movimenti = 0;
/** ENRICO FEDELE */

while ($a_row = gaz_dbi_fetch_array($result)) {
    $partner = $anagrafica->getPartner($a_row["clfoco"]);
    $title =  $partner['ragso1']." ".$partner['ragso2'];
    $valore = CalcolaImportoRigo($a_row['quanti'], $a_row['prezzo'], $a_row['scorig']) ;
    $valore = CalcolaImportoRigo(1, $valore, $a_row['scochi']) ;
    echo "<tr>\n";
    echo "<td class=\"FacetDataTD\"><a class=\"btn btn-xs btn-default\" href=\"admin_movmag.php?id_mov=".$a_row["id_mov"]."&Update\" title=\"".ucfirst($script_transl['update'])."!\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$a_row["id_mov"]."</a> &nbsp</td>";
	echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($a_row["datreg"])." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($a_row["datdoc"])." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["caumag"]." - ".$a_row["descau"]."</td>\n";
    if ($a_row['id_rif'] == 0) {
        echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\">".$a_row['desdoc']."</td>\n";
    } else {
        if ($a_row['tipdoc'] == "ADT"
         || $a_row['tipdoc'] == "AFA"
         || $a_row['tipdoc'] == "AFC") {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../acquis/admin_docacq.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        } else {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../vendit/admin_docven.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        }
    }
	
	// Antonio Germani carico la tabella campi
$res = gaz_dbi_dyn_query ('*', $gTables['campi']);
// fine carico tabella campi
	
	// Antonio Germani inserico colonna campi di coltivazione, superficie, coltura
	
	 echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["campo_coltivazione"]." &nbsp;</td>\n";
	
$colonna="0";
while($b_row = $res->fetch_assoc()) {
	if ($a_row["campo_coltivazione"]==$b_row["codice"]) { 
	echo "<td class=\"FacetDataTD\" align=\"center\">".str_replace('.', ',',$b_row["ricarico"])." &nbsp;</td>\n";
	 echo "<td class=\"FacetDataTD\" align=\"center\">".$b_row["annota"]." &nbsp;</td>\n";
	 $colonna="1";
		} 
	}
	 if ($colonna<1) {
		echo "<td class=\"FacetDataTD\" align=\"center\"></td>\n";
		echo "<td class=\"FacetDataTD\" align=\"center\"></td>\n"; 
	 }
	// fine inserisco colonna campi di coltivazione
/* Antonio germani reperisco unità di misura dell'articolo */
$unires= gaz_dbi_dyn_query("*", $gTables['artico']);
while ($unirow = gaz_dbi_fetch_array($unires)) {
    
    if ($a_row["artico"] == $unirow['codice']) {
        $unimis = $unirow['unimis'];
    }
}
/* fine reperisco unità di misura */	
	
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["artico"]." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_quantity($a_row["quanti"],1,$admin_aziend['decimal_quantity'])." ".$unimis."</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"right\">".$a_row["avversita"]." </td>\n";
	echo "<td class=\"FacetDataTD\" align=\"right\">".$a_row["adminid"]." </td>\n";
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
				<td colspan=\"9\" class=\"FacetFieldCaptionTD\" align=\"right\"></td>
				
			  </tr>";
	//}
	/** ENRICO FEDELE */
?>
        </form>
    </table>
</div>
<?php
require("../../library/include/footer.php");
?>