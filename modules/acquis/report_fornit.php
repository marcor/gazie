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
$titolo="Lista dei Fornitori";
$message = "";

$masfor = $admin_aziend['masfor']."000000";
$fornit = $admin_aziend['masfor'];
require("../../library/include/header.php");
$script_transl=HeadMain();

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
} else {
   $auxil = "";
   $where = "codice like '$fornit%' and codice > '$masfor'";
}

if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "codice like '$fornit%' and codice > '$masfor'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "codice like '$fornit%' and codice > '$masfor' and ragso1 like '".addslashes($auxil)."%'";
   }
}

if (!isset($_GET['field'])) {
   $orderby = "codice desc";
}

?>
<div align="center" class="FacetFormHeaderFont">Fornitori</div>
<form method="GET">
<table class="Tlarge">
<tr>
<td></td>
<td class="FacetFieldCaptionTD">
<input placeholder="Ragione Sociale" class="input-xs form-control" type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" class="btn btn-xs btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td colspan="3">
<input type="submit" class="btn btn-xs btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<tr>
<?php
$result = gaz_dbi_dyn_query ('*', $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_fornit = array  (
            "Codice" => "codice",
            "Ragione sociale" => "ragso1",
            "Tipo" => "sexper",
            "Citt&agrave;" => "citspe",
            "Telefono" => "",
            "P.IVA - C.F." => "",
            "Privacy" => "" ,
            "Paga" => "" ,
            "Visualizza<br> e/o stampa" => "",
            "Cancella" => ""
            );
$linkHeaders = new linkHeaders($headers_fornit);
$linkHeaders -> output();
$recordnav = new recordnav( $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $where, $limit, $passo);
$recordnav -> output();
?>
</tr>
<?php
while ($a_row = gaz_dbi_fetch_array($result)) {
    echo "<tr>";
	 //colonna codice
    echo "<td class=\"FacetDataTD\"><a class=\"btn btn-xs btn-default\" href=\"admin_fornit.php?codice=".substr($a_row["codice"],3)."&Update\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".substr($a_row["codice"],3)."</a></td>";
    echo "<td class=\"FacetDataTD\" title=\"".$a_row["ragso2"]."\">".$a_row["ragso1"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["sexper"]."</td>";
	 $google_string = str_replace(" ","+",$a_row["indspe"]).",".str_replace(" ","+",$a_row["capspe"]).",".str_replace(" ","+",$a_row["citspe"]).",".str_replace(" ","+",$a_row["prospe"]);
		echo "<td class=\"FacetDataTD\" title=\"".$a_row["capspe"]." ".$a_row["indspe"]."\">";	
		echo "<a class=\"btn btn-xs btn-default\" target=\"_blank\" href=\"https://www.google.it/maps/place/".$google_string."\">".$a_row["citspe"]." (".$a_row["prospe"].")&nbsp;<i class=\"glyphicon glyphicon-map-marker\"></i></a>";
		echo "</td>";
    //echo "<td class=\"FacetDataTD\" title=\"".$a_row["capspe"]." ".$a_row["indspe"]."\">".$a_row["citspe"]." (".$a_row["prospe"].")</td>";

    $title = "";
    $telefono = "";
    if (!empty($a_row["telefo"])){
       $telefono = $a_row["telefo"];
       if (!empty($a_row["cell"])){
             $title .= "cell:".$a_row["cell"];
       }
       if (!empty($a_row["fax"])){
             $title .= " fax:".$a_row["fax"];
       }
    } elseif (!empty($a_row["cell"])) {
       $telefono = $a_row["cell"];
       if (!empty($a_row["fax"])){
             $title .= " fax:".$a_row["fax"];
       }
    } elseif (!empty($a_row["fax"])) {
       $telefono = "fax:".$a_row["fax"];
    } else {
       $telefono = "_";
       $title = " nessun contatto telefonico memorizzato ";
    }
    echo "<td class=\"FacetDataTD\" title=\"$title\" align=\"center\">".gaz_html_call_tel($telefono)." &nbsp;</td>";
    if ($a_row['pariva'] > 0 and empty($a_row['codfis'])){
        echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row['pariva']."</td>";
    } elseif($a_row['pariva'] == 0 and !empty($a_row['codfis'])) {
        echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row['codfis']."</td>";
    } elseif($a_row['pariva'] > 0 and !empty($a_row['codfis'])) {
		if ( $a_row['pariva'] == $a_row['codfis'] ) {
			echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row['pariva']."</td>";		
		} else {
			echo "<td class=\"FacetDataTDsmall\" align=\"center\">".$a_row['pariva']."<br>".$a_row['codfis']."</td>";
		}
    } else {
        echo "<td class=\"FacetDataTDred\" align=\"center\"> * NO * </td>";
    }
    echo "<td title=\"stampa informativa sulla privacy\" class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"stampa_privacy.php?codice=".$a_row["codice"]."\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a></td>";
    echo "<td title=\"Effettua un pagamento a ".$a_row["ragso1"]."\" class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-pagamento\" href=\"supplier_payment.php?partner=".$a_row["codice"]."\"><i class=\"glyphicon glyphicon-euro\"></i></a></td>";
    echo "<td title=\"Visualizza e stampa il partitario\" class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"../contab/select_partit.php?id=".$a_row["codice"]."\" target=\"_blank\"><i class=\"glyphicon glyphicon-check\"></i>&nbsp;<i class=\"glyphicon glyphicon-print\"></a></td>";
    echo "<td title=\"Cancella\" class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_fornit.php?codice=".substr($a_row["codice"],3)."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
    echo "</tr>\n";
}
?>
</form>
</table>
</div><!-- chiude div container role main --></body>
</html>