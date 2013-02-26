<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
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
$titolo = 'Categorie Merceologiche';
echo "<script src=\"../../js/boxover/boxover.js\"></script>";
require("../../library/include/header.php");
$script_transl =HeadMain();

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = "descri like '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "descri like '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = "descri like '".addslashes($auxil)."%'";
}
?>
<div align="center" class="FacetFormHeaderFont"><a href="admin_catmer.php?Insert">Inserisci Nuova Categoria Merceologica</a></div>
<div align="center" class="FacetFormHeaderFont">Categorie Merceologiche</div>
<?php
$recordnav = new recordnav($gTables['catmer'], $where, $limit, $passo);
$recordnav -> output();
?>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table class="Tlarge">
<tr>
<td></td>
<td class="FacetFieldCaptionTD">Descrizione:
<input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td>
<input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<tr>
<?php
$result = gaz_dbi_dyn_query ('*', $gTables['catmer'], $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_catmer = array  (
            "Codice" => "codice",
            "Descrizione" => "descri",
            "% Ricarico" => "ricarico",
            "Annotazioni" => "annota",
            "Cancella" => ""
            );
$linkHeaders = new linkHeaders($headers_catmer);
$linkHeaders -> output();
?>
</tr>
<?php
while ($a_row = gaz_dbi_fetch_array($result)) {
    if(!isset($_GET['all']) and !empty($a_row["image"])){
            $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[{$a_row['annota']}] body=[<center><img src='../root/view.php?table=catmer&value=".$a_row['codice']."'>] fade=[on] fadespeed=[0.03] \"";
    } else {
            $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[{$a_row['annota']}]  fade=[on] fadespeed=[0.03] \"";
    }
    echo "<tr>";
    echo "<td class=\"FacetDataTD\" $boxover><a href=\"admin_catmer.php?Update&codice=".$a_row["codice"]."\">".$a_row["codice"]."</a> </td>";
    echo "<td class=\"FacetDataTD\" $boxover>".$a_row["descri"]." </td>";
    echo "<td class=\"FacetDataTD\">".$a_row["ricarico"]." </td>";
    echo "<td class=\"FacetDataTD\">".$a_row["annota"]." </td>";
    echo "<td class=\"FacetDataTD\"><a href=\"delete_catmer.php?codice=".$a_row["codice"]."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></center></a></td>";
    echo "</tr>\n";
}
?>
</table>
</body>
</html>