<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$msg = "";
require("../../library/include/header.php");
$script_transl = HeadMain();
require("lang.".$admin_aziend['lang'].".php");

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "caumag LIKE '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "caumag LIKE '".$_GET['auxil']."%'";
   }
}
 if (isset($_GET['mov']))
{
  if($_GET['mov']>0) {
  $numero=$_GET['mov'];
  $where = $gTables['movmag'].".id_mov =".$numero;
  $passo=1;
  }
  else
  {
  $numero='';
  }
 }
 
 /** ENRICO FEDELE */ 
 /* Leggo i dati inviati con il form */
if (isset($_GET['documento'])) {
	$documento = $_GET['documento'];
   if (isset($_GET['auxil'])) {
      $where .= " AND desdoc LIKE '%".$documento."%'";
   } else {
   		$where = "desdoc LIKE '%".$documento."%'";
   }
}
/** ENRICO FEDELE */

if (!isset($_GET['flag_order'])) {
   $orderby = " id_mov desc";
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "caumag LIKE '$auxil%'";
}
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl[3].$script_transl[0]; ?></div>
<form method="GET">
<table class="Tlarge">
<tr>
<td class="FacetFieldCaptionTD">
	  <input type="text" placeholder="Movimento" class="input-xs form-control" name="mov"
	  value="<?php if (isset($numero)) print $numero; ?>" maxlength ="6" size="3" tabindex="1" class="FacetInput">
</td>

    <!-- ENRICO FEDELE - Inizio modifica form -->
<td class="FacetFieldCaptionTD"></td>
	<td class="FacetFieldCaptionTD">
		<input type="text" name="auxil" placeholder="<?php echo $strScript['admin_movmag.php'][2];?>" class="input-xs form-control" value="<?php if ($auxil != "&all=yes"){echo $auxil;}?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
	</td>
	<td class="FacetFieldCaptionTD">
		<input type="text" name="documento" placeholder="<?php echo $script_transl[8];?>" class="input-xs form-control" value="<?php if (isset($documento)) print $documento; ?>" maxlength="15" size="3" tabindex="1" class="FacetInput">
	</td>
    <!-- ENRICO FEDELE - Fine modifica form -->

<td class="FacetFieldCaptionTD">
<input type="text" name="auxil" placeholder="<?php echo $strScript['admin_movmag.php'][2];?>" class="input-xs form-control"
value="<?php if ($auxil != "&all=yes"){echo $auxil;}?>" maxlength="6" size="3" tabindex="1" class="FacetInput"></td>
<td><input type="submit" class="btn btn-xs btn-default" name="search" value="<?php echo $script_transl['search'];?>" tabindex="1" onClick="javascript:document.report.all.value=1;"></td>
<td><input type="submit" class="btn btn-xs btn-default" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value=1;"></td></tr>

<?php
$table = $gTables['movmag']." LEFT JOIN ".$gTables['caumag']." on (".$gTables['movmag'].".caumag = ".$gTables['caumag'].".codice)
         LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['movmag'].".clfoco = ".$gTables['clfoco'].".codice)
         LEFT JOIN ".$gTables['rigdoc']." ON (".$gTables['movmag'].".id_rif = ".$gTables['rigdoc'].".id_rig)";
$result = gaz_dbi_dyn_query ($gTables['movmag'].".*, ".$gTables['caumag'].".descri AS descau, ".$gTables['rigdoc'].".id_tes AS testata", $table, $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_mov = array  (
            "n.ID" => "id_mov",
            $script_transl[4] => "datreg",
            $strScript["admin_movmag.php"][2] => "caumag",
            $script_transl[8] => "",
            $script_transl[5] => "artico",
            $script_transl[6] => "",
            $script_transl[7] => "",
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
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["caumag"]." - ".$a_row["descau"]."</td>\n";
    if ($a_row['id_rif'] == 0) {
        echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</td>\n";
    } else {
        if ($a_row['tipdoc'] == "ADT"
         || $a_row['tipdoc'] == "AFA"
         || $a_row['tipdoc'] == "AFC") {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../acquis/admin_docacq.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        } else {
            echo "<td class=\"FacetDataTD\" align=\"center\" title=\"$title\"><a href=\"../vendit/admin_docven.php?Update&id_tes=".$a_row['testata']."\">".$a_row['desdoc']." ".$script_transl[9]." ".gaz_format_date($a_row["datdoc"])."</a></td>\n";
        }
    }
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["artico"]." &nbsp;</td>\n";
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
				<td colspan=\"6\" class=\"FacetFormHeaderFont\" align=\"right\"><strong>TOTALE</strong></td>
				<td class=\"FacetFormHeaderFont\" align=\"right\"><strong>".gaz_format_number($tot_movimenti)."</strong></td>
				<td class=\"FacetFormHeaderFont\">&nbsp;</td>
			  </tr>";
	//}
	/** ENRICO FEDELE */
?>
</table>
</body>
</html>