<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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

$titolo = 'Clienti';

$mascli = $admin_aziend['mascli']."000000";
$clienti = $admin_aziend['mascli'];
require("../../library/include/header.php");
$script_transl=HeadMain();
$where = "codice BETWEEN ".$clienti."000000 AND ".$clienti."999999 and codice > $mascli";

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
} else {
   $auxil = "";
}

if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where .= " AND ragso1 LIKE '".addslashes($auxil)."%'";
   }
}

if (!isset($_GET['field'])) {
   $orderby = "codice DESC";
}


?>
<table border="0" cellpadding="3" cellspacing="1" align="center" width="70%">
<tr>
<td align="center" class="FacetFormHeaderFont"><a href="admin_client.php?Insert">Inserisci Nuovo Cliente</a></td>
<td align="center" class="FacetFormHeaderFont"><a href="report_credit.php" title="Visualizzazione e Stampa della Lista dei Crediti verso Clienti">Lista dei crediti</a></td>
</tr>
</table>
<div align="center" class="FacetFormHeaderFont">Clienti</div>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table class="Tlarge">
<tr>
<td></td>
<td class="FacetFieldCaptionTD">Ragione sociale:
<input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td colspan="3">
<input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<?php
$result = gaz_dbi_dyn_query ('*', $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_ = array  (
            "Codice" => "codice",
            "Ragione Sociale" => "ragso1",
            "Tipo" => "sexper",
            "Citt&agrave;" => "citspe",
            "Telefono" => "telefo",
            "P.IVA - C.F." => "",
            "Privacy" => "" ,
            "Riscuoti" => "" ,
            "Visualizza <br /> e/o stampa" => "",
            "Cancella" => ""
            );
$linkHeaders = new linkHeaders($headers_);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $where, $limit, $passo);
$recordnav -> output();
?>
</tr>
<?php
while ($a_row = gaz_dbi_fetch_array($result)) {
    echo "<tr>";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"admin_client.php?codice=".substr($a_row["codice"],3)."&Update\">".substr($a_row["codice"],3)."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTD\" title=\"".$a_row["ragso2"]."\">".$a_row["ragso1"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["sexper"]."</td>";
    echo "<td class=\"FacetDataTD\" title=\"".$a_row["capspe"]." ".$a_row["indspe"]."\">".$a_row["citspe"]." (".$a_row["prospe"].")</td>";
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
    echo "<td class=\"FacetDataTD\" title=\"$title\" align=\"center\">".$telefono." &nbsp;</td>";
    if ($a_row['pariva'] > 0 and empty($a_row['codfis'])){
        echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row['pariva']."</td>";
    } elseif($a_row['pariva'] == 0 and !empty($a_row['codfis'])) {
        echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row['codfis']."</td>";
    } elseif($a_row['pariva'] > 0 and !empty($a_row['codfis'])) {
        echo "<td class=\"FacetDataTDsmall\" align=\"center\">".$a_row['pariva']."<br>".$a_row['codfis']."</td>";
    } else {
        echo "<td class=\"FacetDataTDred\" align=\"center\"> * NO * </td>";
    }
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"stampa_privacy.php?codice=".$a_row["codice"]."\"><img src=\"../../library/images/privacy.gif\" title=\"Stampa informativa sulla privacy a ".$a_row["ragso1"]."\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"salcon_credit.php?codice=".$a_row["codice"]."\"><img src=\"../../library/images/pay.gif\" title=\"Effettuato un pagamento da ".$a_row["ragso1"]."\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"../contab/select_partit.php?id=".$a_row["codice"]."\"><center><img src=\"../../library/images/vis.gif\" alt=\"Visualizza e stampa il partitario\" border=\"0\"><img src=\"../../library/images/stampa.gif\" alt=\"Visualizza e stampa il partitario\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_client.php?codice=".substr($a_row["codice"],3)."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
    echo "</tr>\n";
}
?>
</form>
</table>
</body>
</html>