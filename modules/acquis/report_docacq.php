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
$message = "";

if (isset($_GET['auxil']) && !isset($_GET['flt_tipdoc'])) {
    $seziva = $_GET['auxil'];
    $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'";
} else {
    $seziva = 1;
    $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'";
}
//assegno a $all la stringa per la query che comporrà i filtri 
$all = $where;

if (isset($_GET['protoc'])) {
    if ($_GET['protoc'] > 0) {
        $protocollo = $_GET['protoc'];
        $auxil = $_GET['auxil'] . "&protoc=" . $protocollo;
        $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'  AND protoc = $protocollo GROUP BY protoc, datfat";
        $passo = 10;
    }
} else {
    $protocollo = '';
}
if (isset($_GET['flt_tipo']) && $_GET['flt_tipo']!= "All") {
    if ($_GET['flt_tipo'] != "") {
        $tipdoc = $_GET['flt_tipo'];
        $auxil = $_GET['auxil'] . "&flt_tipo=" . $tipdoc;
        $where .= " AND tipdoc like '%$tipdoc%'";
        $passo = 9999;
    }
} else {
    $tipdoc = '';
}
if (isset($_GET['numfat'])) {
    if ($_GET['numfat'] != "") {
        $numfat = $_GET['numfat'];
        $auxil = $_GET['auxil'] . "&numfat=" . $numfat;
        $where .= " AND numfat like '%$numfat%'";
        $passo = 9999;
    }
} else {
    $numfat = '';
}
if (isset($_GET['flt_year'])) {
	if ($_GET['flt_year'] != "" && $_GET['flt_year']!= "All") {
        $year = $_GET['flt_year'];
		$auxil = $_GET['auxil'] . "&datfat=" . $year;
		$where .= " and datfat >= \"".$year."/01/01\" and datfat <= \"".$year."/12/31\"";
        $passo = 9999;
    } else {
		$year = 'All';
	}
} else {
	$year = 'All';    
}
if (isset($_GET['flt_ragso1'])) {
    if ($_GET['flt_ragso1'] != "") {
        $ragso1 = $_GET['flt_ragso1'];
		if ($ragso1!="All") {
			$auxil = $_GET['auxil'] . "&ragso1=" . $ragso1;
			$where .= " and ".$gTables["tesdoc"].".clfoco = ".$ragso1;
		}
        $passo = 9999;
    }
} else {
    $ragso1 = '';
}

if (isset($_GET['all'])) {
	$year="";
	$datfat="";
	$tipdoc="";
	$ragso1="";
	
	
    $where = "tipdoc LIKE 'AF_' AND " . $gTables['tesdoc'] . ".seziva = '$seziva'  GROUP BY protoc, datfat";
    $auxil = $_GET['auxil'] . "&all=yes";
    $passo = 100000;
    $protocollo = '';
}


$titolo = "Documenti d'acquisto";
require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<form method="GET" >
    <div align="center" class="FacetFormHeaderFont"><?php echo $titolo; ?>
        <select name="auxil" class="FacetSelect" onchange="this.form.submit()">
            <?php
            for ($sez = 1; $sez <= 9; $sez++) {
                $selected = "";
                if ($seziva == $sez) {
                    $selected = " selected ";
                }
                echo "<option value=\"" . $sez . "\"" . $selected . ">" . $sez . "</option>";
            }
            ?>
        </select></font>
    </div>
    <?php
    if (!isset($_GET['field']) || (empty($_GET['field'])))
        $orderby = "protoc DESC";
    $recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
    $recordnav->output();
	
    ?>
    <div class="box-primary table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
        <tr>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="text" placeholder="Cerca Prot." class="input-sm form-control" name="protoc" value="<?php if (isset($protocollo)) print $protocollo; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
            </td>
			<td colspan="1" class="FacetFieldCaptionTD">
				&nbsp;
			</td>
			<td colspan="1" class="FacetFieldCaptionTD">
				<select class="form-control input-sm" name="flt_tipo" onchange="this.form.submit()">
				<option value="All"><?php echo $script_transl['tuttitipi']; ?></option>
				<?php $res = gaz_dbi_dyn_query("distinct tipdoc", $gTables["tesdoc"], $all, $orderby, 0, 999);
					while ( $val = gaz_dbi_fetch_array($res) ) {
						if ( $tipdoc == $val["tipdoc"] ) $selected = "selected";
						else $selected = "";
						echo "<option value=\"".$val["tipdoc"]."\" ".$selected.">".$val["tipdoc"]."</option>";
					} ?>
				</select>
			</td>
            <td colspan="1" class="FacetFieldCaptionTD">
				<input type="text" placeholder="Cerca Num." class="input-sm form-control" name="numfat" value="<?php if (isset($numfat)) print $numfat; ?>" size="3" tabindex="3" class="FacetInput">			
			</td>
			<td colspan="1" class="FacetFieldCaptionTD">
				<select class="form-control input-sm" name="flt_year" onchange="this.form.submit()">
				<option value="All"><?php echo $script_transl['tuttianni']; ?></option>
				<?php $res = gaz_dbi_dyn_query("distinct YEAR(datfat) as year", $gTables["tesdoc"], $all, $orderby, 0, 999);
					while ( $val = gaz_dbi_fetch_array($res) ) {
						if ( $year == $val["year"] ) $selected = "selected";
						else $selected = "";
						echo "<option value=\"".$val["year"]."\" ".$selected.">".$val["year"]."</option>";
					} ?>
				</select>
			</td>
			<td colspan="1" class="FacetFieldCaptionTD">
				<select class="form-control input-sm" name="flt_ragso1" onchange="this.form.submit()">
				<option value="All"><?php echo $script_transl['tutticlienti']; ?></option>
				<?php $res = gaz_dbi_dyn_query("distinct ".$gTables['anagra'].".ragso1,".$gTables["tesdoc"].".clfoco", $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $all, $gTables['anagra'].".ragso1", 0, 999);
					while ( $val = gaz_dbi_fetch_array($res) ) {
						if ( $ragso1 == $val["clfoco"] ) $selected = "selected";
						else $selected = "";
						echo "<option value=\"".$val["clfoco"]."\" ".$selected.">".$val["ragso1"]."</option>";
					} ?>
				</select>
			</td>
			<td colspan="1" class="FacetFieldCaptionTD">
				&nbsp;
			</td>
			<td colspan="1" class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
            </td>
            <td colspan="1" class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;">
            </td>
        </tr>
        <tr>
            <?php
// creo l'array (header => campi) per l'ordinamento dei record
            $headers_tesdoc = array(
                "Prot." => "protoc",
                "Dat.Reg." => "datreg",
                "Documento" => "tipdoc",
                "Numero" => "numfat",
                "Data" => "datfat",
                "Fornitore" => "ragso1",
                "Status" => "",
                "Stampa" => "",
                "Cancella" => ""
            );
            $linkHeaders = new linkHeaders($headers_tesdoc);
            $linkHeaders->output();
            ?>
        </tr>
        <?php
/*        $rs_last_doc = gaz_dbi_dyn_query("MAX(protoc) AS maxpro, YEAR(datfat) AS y", $gTables['tesdoc'], "tipdoc LIKE 'AF_' AND seziva = '$seziva' GROUP BY y ", 'protoc DESC');
        while ($last_doc = gaz_dbi_fetch_array($rs_last_doc)) {
            $lt_doc[$last_doc['y']] = $last_doc['maxpro'];
        }*/

//recupero le testate in base alle scelte impostate
    	//echo $where." ".$year." ".$ragso1;    
        $result = gaz_dbi_dyn_query($gTables['tesdoc'] . ".*," . $gTables['anagra'] . ".ragso1", $gTables['tesdoc'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['tesdoc'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $where, $orderby, $limit, $passo);
        $ctrlprotoc = "";
        while ($row = gaz_dbi_fetch_array($result)) {
            $y = substr($row['datfat'], 0, 4);
            if ($row["tipdoc"] == 'AFA') {
                $tipodoc = "Fattura";
                $modulo = "stampa_docacq.php?id_tes=" . $row['id_tes'];
                $modifi = "admin_docacq.php?Update&id_tes=" . $row['id_tes'];
            } elseif ($row["tipdoc"] == 'AFC') {
                $tipodoc = "Nota Credito";
                $modulo = "stampa_docacq.php?id_tes=" . $row['id_tes'];
                $modifi = "admin_docacq.php?Update&id_tes=" . $row['id_tes'];
            }

            if ($row["protoc"] <> $ctrlprotoc) {
                $clfoco = gaz_dbi_get_row($gTables['clfoco'], 'codice', $row['clfoco']);
                $anagra = gaz_dbi_get_row($gTables['anagra'], 'id', $clfoco['id_anagra']);
                echo "<tr class=\"FacetDataTD\">";
                if (!empty($modifi)) {
                    echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"" . $modifi . "\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . $row["protoc"] . "</td>";
                } else {
                    echo "<td><button class=\"btn btn-xs btn-default btn-edit disabled\">" . $row["protoc"] . " &nbsp;</button></td>";
                }
                echo "<td>" . gaz_format_date($row["datreg"]) . " &nbsp;</td>";
                if (empty($row["fattura_elettronica_original_name"])) {
					print '<td>'.$tipodoc."</td>\n";
                } else {
					print '<td><a class="btn btn-xs btn-default btn-xml" target="_blank" href="view_fae.php?id_tes=' . $row["id_tes"] . '">'.$tipodoc.' '.$row["fattura_elettronica_original_name"]."</a></td>";
				}
				echo "<td>" . $row["numfat"] . " &nbsp;</td>";
                echo "<td>" . gaz_format_date($row["datfat"]) . " &nbsp;</td>";
                echo "<td><a title=\"Dettagli fornitore\" href=\"report_fornit.php?auxil=" . htmlspecialchars($anagra["ragso1"]) . "&search=Cerca\">" . $anagra["ragso1"] . ((empty($anagra["ragso2"]))?"":" ".$anagra["ragso2"]) . "</a>&nbsp;</td>";
                if ($row["id_con"] > 0) {
                    echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-default\" href=\"../contab/admin_movcon.php?id_tes=" . $row["id_con"] . "&Update\">Cont. n." . $row["id_con"] . "</a></td>";
                } else {
                    echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-cont\" href=\"accounting_documents.php?type=A&last=" . $row["protoc"] . "\">Contabilizza</a></td>";
                }
                echo "<td><a class=\"btn btn-xs btn-default\" href=\"" . $modulo . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a></td>";
                //if ($lt_doc[$y] == $row['protoc']) {
                    echo "<td><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_docacq.php?id_tes=" . $row["id_tes"] . "\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
                //} else {
//                    echo "<td><button title=\"Per garantire la sequenza corretta della numerazione, non &egrave; possibile cancellare un documento diverso dall'ultimo\" class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button></td>";
  //              }
                echo "</tr>\n";
            }
            $ctrlprotoc = $row["protoc"];
        }
        ?>
</form>
<tr><td colspan="9" class="FacetFieldCaptionTD"></td></tr>
</table>
</div>
<?php
require("../../library/include/footer.php");
?>