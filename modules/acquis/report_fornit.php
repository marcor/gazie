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
$titolo="Lista dei Fornitori";
$message = "";

$masfor = $admin_aziend['masfor']."000000";
$fornit = $admin_aziend['masfor'];
require("../../library/include/header.php");
$script_transl=HeadMain();
$where = "codice BETWEEN ".$fornit."000000 AND ".$fornit."999999 and codice > ".$masfor;
$all=$where;

if ( isset($_GET['codice'])) {
	$codice = $_GET['codice'];
	$where .= " and codice like '$fornit%$codice%'";
} else $codice = "";
if (isset($_GET['auxil'])) {
   $auxil = addslashes($_GET['auxil']);
   $where .= " and ragso1 like '%$auxil%'";
   $auxil = stripslashes($_GET['auxil']);
} else $auxil = "";
if (isset($_GET['flt_tipo']) && $_GET['flt_tipo']!='All') {
   $tipo = $_GET['flt_tipo'];
   $where .= " and sexper = '$tipo'";
} else $tipo = "";
if (isset($_GET['flt_citta']) && $_GET['flt_citta']!='All') {
   $citta = $_GET['flt_citta'];
   $where .= " and citspe = '$citta'";
} else $citta = "All";
if (isset($_GET['telefono'])) {
   $telefono = $_GET['telefono'];
   $where .= " and telefo like '%$telefono%'";
} else $telefono = "";
if (isset($_GET['fiscali'])) {
   $fiscali = $_GET['fiscali'];
   $where .= " and codfis like '%$fiscali%' and pariva like '%$fiscali%'";
} else $fiscali = "";
if (isset($_GET['all'])) {
	$codice="";
	$tipo="";
	$citta="";
	$telefono="";
	$fiscali="";
	$where=$all;
   $auxil = "&all=yes";
   //$where = "codice like '$fornit%' and codice > '$masfor'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      //$where = "codice like '$fornit%' and codice > '$masfor' and ragso1 like '".addslashes($auxil)."%'";
   }
}

if (!isset($_GET['field'])) {
   $orderby = "codice desc";
}

?>
<div align="center" class="FacetFormHeaderFont">Fornitori</div>
<form method="GET">
<table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
	<tr>
		<td class="FacetFieldCaptionTD">
			<input type="text" placeholder="Cerca cod." class="input-sm form-control" name="codice" value="<?php if (isset($codice)) print $codice; ?>" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD">
			<input placeholder="Ragione Sociale" class="input-sm form-control" type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD">
			<select class="form-control input-sm" name="flt_tipo" onchange="this.form.submit()">
				<option value="All" <?php echo ($tipo=="All") ? "selected" : "";?>><?php echo $script_transl['tuttitipi']; ?></option>
				<option value="G" <?php echo ($tipo=="G") ? "selected" : "";?>>Giuridica</option>
				<option value="M" <?php echo ($tipo=="M") ? "selected" : "";?>>Maschio</option>
				<option value="F" <?php echo ($tipo=="F") ? "selected" : "";?>>Femmina</option>
			</select>
		</td>
		<td class="FacetFieldCaptionTD">
			<select class="form-control input-sm" name="flt_citta" onchange="this.form.submit()">
				<option value="All" <?php echo ($citta=="All") ? "selected" : "";?>><?php echo $script_transl['tuttecitta']; ?></option>			
				<?php //$tabella = $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id'; 
				//gaz_filtro ( "citspe", $tabella, $all, $orderby);
				$res = gaz_dbi_dyn_query("distinct citspe", $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $all);
				while ( $val = gaz_dbi_fetch_array($res) ) {
					if ( $citta == $val["citspe"] ) $selected = "selected";
					else $selected = "";
					echo "<option value=\"".$val["citspe"]."\" ".$selected.">".$val["citspe"]."</option>";
				} ?>
			</select>
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" placeholder="Cerca tel." class="input-sm form-control" name="telefono" value="<?php if (isset($telefono)) print $telefono; ?>" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="text" placeholder="Cerca fisc." class="input-sm form-control" name="fiscali" value="<?php if (isset($fiscali)) print $fiscali; ?>" tabindex="1" class="FacetInput">
		</td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD"></td>
		<td class="FacetFieldCaptionTD">
			<input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
		</td>
		<td class="FacetFieldCaptionTD" colspan="1">
			<input type="submit" class="btn btn-sm btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
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
	// NOMINA A RESPONSABILE ESTERNO AL TRATTAMENTO DEI DATI?
	$regol_lnk='';
	if (isset ($a_row["external_resp"]) && $a_row["external_resp"]>0) {
		$regol_lnk='<a title="Stampa la Nomina a RESPONSABILE ESTERNO al trattamento dati personali" class="btn btn-xs btn-default btn-warning" href="stampa_nomina.php?id=' . $a_row["codice"] . '" target="_blank"><i class="glyphicon glyphicon-eye-close"></i></a> ';
	} else {
		$regol_lnk="<a class=\"btn btn-xs btn-default\" href=\"stampa_privacy.php?codice=".$a_row["codice"]."\" target=\"_blank\"><i class=\"glyphicon glyphicon-eye-close\"></i></a>";
	}
	
    echo "<tr class=\"FacetDataTD\">";
	 //colonna codice
    echo "<td><a class=\"btn btn-xs btn-default\" href=\"admin_fornit.php?codice=".substr($a_row["codice"],3)."&Update\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".substr($a_row["codice"],3)."</a></td>";
    echo "<td title=\"".$a_row["ragso2"]."\">".$a_row["ragso1"]." &nbsp;</td>";
    echo "<td align=\"center\">".$a_row["sexper"]."</td>";
	 $google_string = str_replace(" ","+",$a_row["indspe"]).",".str_replace(" ","+",$a_row["capspe"]).",".str_replace(" ","+",$a_row["citspe"]).",".str_replace(" ","+",$a_row["prospe"]);
		echo "<td title=\"".$a_row["capspe"]." ".$a_row["indspe"]."\">";	
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
    echo "<td title=\"$title\" align=\"center\">".gaz_html_call_tel($telefono)." &nbsp;</td>";
    if ($a_row['pariva'] > 0 and empty($a_row['codfis'])){
        echo "<td align=\"center\">".$a_row['pariva']."</td>";
    } elseif($a_row['pariva'] == 0 and !empty($a_row['codfis'])) {
        echo "<td align=\"center\">".$a_row['codfis']."</td>";
    } elseif($a_row['pariva'] > 0 and !empty($a_row['codfis'])) {
		if ( $a_row['pariva'] == $a_row['codfis'] ) {
			echo "<td align=\"center\">".$a_row['pariva']."</td>";		
		} else {
			echo "<td align=\"center\">".$a_row['pariva']."<br>".$a_row['codfis']."</td>";
		}
    } else {
        echo "<td class=\"FacetDataTDred\" align=\"center\"> * NO * </td>";
    }
    echo "<td title=\"stampa informativa sulla privacy\" align=\"center\">".
	$regol_lnk."</td>";
    echo "<td title=\"Effettua un pagamento a ".$a_row["ragso1"]."\" align=\"center\"><a class=\"btn btn-xs btn-default btn-pagamento\" href=\"supplier_payment.php?partner=".$a_row["codice"]."\"><i class=\"glyphicon glyphicon-euro\"></i></a></td>";
    echo "<td title=\"Visualizza e stampa il partitario\" align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"../contab/select_partit.php?id=".$a_row["codice"]."\" target=\"_blank\"><i class=\"glyphicon glyphicon-check\"></i>&nbsp;<i class=\"glyphicon glyphicon-print\"></a></td>";
    echo "<td title=\"Cancella\" align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_fornit.php?codice=".substr($a_row["codice"],3)."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
    echo "</tr>\n";
}
?>
</form>
</table>
<?php
require("../../library/include/footer.php");
?>