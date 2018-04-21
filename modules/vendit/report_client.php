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

$titolo = 'Clienti';

$mascli = $admin_aziend['mascli'] . "000000";
$clienti = $admin_aziend['mascli'];
require("../../library/include/header.php");
if (isset($_GET['privacy'])) {
	echo '<script>    window.onload = function(){
         window.open("stampa_privacy.php?codice='.intval($_GET['privacy']).'", "_blank"); // will open new tab on window.onload
		} </script>';
}
$script_transl = HeadMain();
$where = "codice BETWEEN " . $clienti . "000000 AND " . $clienti . "999999 and codice > $mascli";
$all = $where;

if (isset($_GET['auxil'])) {
    $auxil = $_GET['auxil'];
} else {
    $auxil = "";
}

if (isset($_GET['auxil1'])) {
    $auxil1 = $_GET['auxil1'];
} else {
    $auxil1 = "";
}

gaz_flt_var_assign('codice', 'v');
gaz_flt_var_assign('ragso1', 'v');

if (isset($_GET['all'])) {
    $auxil = "&all=yes";
    $passo = 100000;
} else {
    if (isset($_GET['auxil']) and $auxil1 == "") {
        $where .= " AND ragso1 LIKE '" . addslashes($auxil) . "%'";
    } elseif (isset($_GET['auxil1'])) {
        $codicetemp = intval($mascli) + intval($auxil1);
        $where .= " AND codice LIKE '" . $codicetemp . "%'";
    }
}

if (!isset($_GET['field'])) {
    $orderby = "codice DESC";
}

if (isset($_GET['ricerca_completa'])) {
    $ricerca_testo = $_GET['ricerca_completa'];
    $where .= " and ( ragso1 like '%" . $ricerca_testo . "%' ";
    $where .= " or ragso2 like '%" . $ricerca_testo . "%' ";
    $where .= " or legrap_pf_nome like '%" . $ricerca_testo . "%' ";
    $where .= " or legrap_pf_cognome like '%" . $ricerca_testo . "%' ";
    $where .= " or pariva like '%" . $ricerca_testo . "%' ";
    $where .= " or codfis like '%" . $ricerca_testo . "%' ";
    $where .= " or citspe like '%" . $ricerca_testo . "%' )";
}
?>
<div align="center" class="FacetFormHeaderFont">Clienti</div>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div class="box-primary table-responsive">
    <table class="Tlarge table table-striped table-bordered table-condensed">
        <tr>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_int("codice", "Codice cli."); ?>
            </td>
            <td class="FacetFieldCaptionTD">
                <?php gaz_flt_disp_int("ragso1", "Nome cliente"); //gaz_flt_disp_select ( "clfoco", $gTables['anagra'].".ragso1", $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $all, $orderby, "ragso1");  ?>
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                &nbsp;
            </td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-xs btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
            </td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-xs btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;">
            </td>
        </tr>
        <?php
        $result = gaz_dbi_dyn_query('*', $gTables['clfoco'] . ' LEFT JOIN ' . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
        $headers_ = array(
            "Codice" => "codice",
            "Ragione Sociale" => "ragso1",
            "Tipo" => "sexper",
            "Citt&agrave;" => "citspe",
            "Telefono" => "telefo",
            "P.IVA - C.F." => "",
            "Privacy" => "",
            "Riscuoti" => "",
            "Visualizza <br /> e/o stampa" => "",
            "Cancella" => ""
        );
        $linkHeaders = new linkHeaders($headers_);
        $linkHeaders->output();
        $recordnav = new recordnav($gTables['clfoco'] . ' LEFT JOIN ' . $gTables['anagra'] . ' ON ' . $gTables['clfoco'] . '.id_anagra = ' . $gTables['anagra'] . '.id', $where, $limit, $passo);
        $recordnav->output();
        ?>
        </tr>
        <?php
        while ($a_row = gaz_dbi_fetch_array($result)) {
            echo "<tr class=\"FacetDataTD\">";
            // Colonna codice cliente
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"admin_client.php?codice=" . substr($a_row["codice"], 3) . "&Update\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;" . substr($a_row["codice"], 3) . "</a> &nbsp</td>";
            // Colonna ragione sociale
            echo "<td title=\"" . $a_row["ragso2"] . "\">" . $a_row["ragso1"] . " &nbsp;</td>";
            // colonna sesso
            echo "<td align=\"center\">" . $a_row["sexper"] . "</td>";
            // colonna indirizzo
            $google_string = str_replace(" ", "+", $a_row["indspe"]) . "," . str_replace(" ", "+", $a_row["capspe"]) . "," . str_replace(" ", "+", $a_row["citspe"]) . "," . str_replace(" ", "+", $a_row["prospe"]);
            echo "<td title=\"" . $a_row["capspe"] . " " . $a_row["indspe"] . "\">";
            echo "<a class=\"btn btn-xs btn-default\" target=\"_blank\" href=\"https://www.google.it/maps/place/" . $google_string . "\">" . $a_row["citspe"] . " (" . $a_row["prospe"] . ")&nbsp;<i class=\"glyphicon glyphicon-map-marker\"></i></a>";
            echo "<a class=\"btn btn-xs btn-default\" target=\"_blank\" href=\"https://www.google.it/maps/dir/" . $admin_aziend['latitude'] . "," . $admin_aziend['longitude'] . "/" . $google_string . "\">  <i class=\"glyphicon glyphicon-random\"></i></a>";
            echo "</td>";
            // composizione telefono
            $title = "";
            $telefono = "";
            if (!empty($a_row["telefo"])) {
                $telefono = $a_row["telefo"];
                if (!empty($a_row["cell"])) {
                    $title .= "cell:" . $a_row["cell"];
                }
                if (!empty($a_row["fax"])) {
                    $title .= " fax:" . $a_row["fax"];
                }
            } elseif (!empty($a_row["cell"])) {
                $telefono = $a_row["cell"];
                if (!empty($a_row["fax"])) {
                    $title .= " fax:" . $a_row["fax"];
                }
            } elseif (!empty($a_row["fax"])) {
                $telefono = "fax:" . $a_row["fax"];
            } else {
                $telefono = "_";
                $title = " nessun contatto telefonico memorizzato ";
            }
            // colonna telefono
            echo "<td title=\"$title\" align=\"center\">" . gaz_html_call_tel($telefono) . " &nbsp;</td>";
            // colonna fiscali
            if ($a_row['pariva'] > 0 and empty($a_row['codfis'])) {
                echo "<td align=\"center\">" . $a_row['country'] . " " . $a_row['pariva'] . "</td>";
            } elseif ($a_row['pariva'] == 0 and ! empty($a_row['codfis'])) {
                echo "<td align=\"center\">" . $a_row['codfis'] . "</td>";
            } elseif ($a_row['pariva'] > 0 and ! empty($a_row['codfis'])) {
                if ($a_row['pariva'] == $a_row['codfis']) {
                    echo "<td align=\"center\">";
                    echo gaz_html_ae_checkiva($a_row['country'], $a_row['pariva']);
                    echo "</td>";
                } else {
                    echo "<td align=\"center\">" . gaz_html_ae_checkiva($a_row['country'], $a_row['pariva']) . "<br>" . $a_row['codfis'] . "</td>";
                }
            } else {
                echo "<td class=\"FacetDataTDred\" align=\"center\"> * NO * </td>";
            }
            // colonna stampa privacy
            echo "<td title=\"stampa informativa sulla privacy\" align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"stampa_privacy.php?codice=" . $a_row["codice"] . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a></td>";
            echo "<td title=\"Effettuato un pagamento da " . $a_row["ragso1"] . "\" align=\"center\"><a class=\"btn btn-xs btn-default btn-pagamento\" href=\"customer_payment.php?partner=" . $a_row["codice"] . "\"><i class=\"glyphicon glyphicon-euro\"></i></a></td>";
            echo "<td title=\"Visualizza e stampa il partitario\" align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"../contab/select_partit.php?id=" . $a_row["codice"] . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-check\"></i>&nbsp;<i class=\"glyphicon glyphicon-print\"></a></td>";
            echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_client.php?codice=" . substr($a_row["codice"], 3) . "\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
            echo "</tr>\n";
        }
        ?>
        <tr><th class="FacetFieldCaptionTD" colspan="10"></th></tr>
</form>
</table>
    </div>
<?php
require("../../library/include/footer.php");
?>