<?php
/* $Id: report_artico.php,v 1.42 2011/01/01 11:07:46 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
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

$search_field_Array = array('C'=>array('codice','Codice'), 'D'=>array('descri','Descrizione'),'B'=>array('barcode','Codice a barre'));
echo "<script src=\"../../js/boxover/boxover.js\"></script>";
require("../../library/include/header.php");
$script_transl=HeadMain();
if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
}
if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = $search_field_Array[$admin_aziend['artsea']][0]." LIKE '%'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = $search_field_Array[$admin_aziend['artsea']][0]." LIKE '".addslashes($_GET['auxil'])."%'";
   }
}

if (!isset($_GET['auxil'])) {
   $auxil = "";
   $where = $search_field_Array[$admin_aziend['artsea']][0]." LIKE '$auxil%'";
}
?>
<div align="center" class="FacetFormHeaderFont"><a href="admin_artico.php?Insert">Inserisci Nuovo Articolo</a></div>
<div align="center" class="FacetFormHeaderFont">Articoli</div>
<form method="GET">
<table class="Tlarge">
<tr>
<td class="FacetFieldCaptionTD" colspan="2"><?php echo $search_field_Array[$admin_aziend['artsea']][1]; ?>:
<input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="15" size="15" tabindex="1" class="FacetInput">
<input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td></td>
<td>
<input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<?php
$result = gaz_dbi_dyn_query ('*', $gTables['artico'], $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_artico = array  (
              "Codice" => "codice",
              "Descrizione" => "descri",
              "Categoria<br>merceologica" => "catmer",
              "U.M." => "unimis",
              "Prezzo 1" => "preve1",
              "Prezzo<br>acquisto" => "preacq",
              "Giacenza" => "esiste");
if ($admin_aziend['conmag']>0) {
   $headers_artico = array_merge($headers_artico,array(
              "Visualizza<br>e/o stampa"=>'',
              "Barcode" => "barcode",
              "Duplica" => "",
              "Cancella" => ""
              ));
} else {
   $headers_artico = array_merge ( $headers_artico,array(
              "Barcode" => "barcode",
              "Duplica" => "",
              "Cancella" => ""
              ));
}

$linkHeaders = new linkHeaders($headers_artico);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['artico'], $where, $limit, $passo);
$recordnav -> output();
$gForm = new magazzForm();
while ($r = gaz_dbi_fetch_array($result)) {
       set_time_limit (30);
       $magval=array_pop($gForm->getStockValue(false,$r['codice']));
       if(!isset($_GET['all']) and !empty($r["image"])){
            $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$r['annota']."] body=[<center><img src='../root/view.php?table=artico&value=".$r['codice']."'>] fade=[on] fadespeed=[0.03] \"";
       } else {
            $boxover = "title=\"cssbody=[FacetInput] cssheader=[FacetButton] header=[".$r['annota']."]  fade=[on] fadespeed=[0.03] \"";
       }
       $iva = gaz_dbi_get_row($gTables['aliiva'],"codice",$r["aliiva"]);
       echo "<tr>";
       echo "<td class=\"FacetDataTD\" $boxover><a href=\"admin_artico.php?codice=".$r["codice"]."&Update\">".$r["codice"]."</a> </td>";
       echo "<td class=\"FacetDataTD\" $boxover>".$r["descri"]." </td>";
       echo "<td class=\"FacetDataTD\" align=\"center\">".$r["catmer"]." </td>";
       echo "<td class=\"FacetDataTD\" align=\"center\">".$r["unimis"]." </td>";
       echo "<td class=\"FacetDataTD\" align=\"right\">".number_format($r["preve1"],$admin_aziend['decimal_price'],',','.')." </td>";
       echo "<td class=\"FacetDataTD\" align=\"right\">".number_format($r["preacq"],$admin_aziend['decimal_price'],',','.')." </td>";
       echo "<td class=\"FacetDataTD\" align=\"right\" title=\"$money[1] ".$magval['v_g']."\">".$magval['q_g']." </td>";
       if ($admin_aziend['conmag']>0) {
          echo "<td class=\"FacetDataTD\" align=\"center\" title=\"Visualizza e/o stampa la scheda di magazzino\">
                <a href=\"../magazz/select_schart.php?di=0101".date('Y')."&df=".date('dmY')."&id=".$r['codice']."\">
                <img src=\"../../library/images/vis.gif\" alt=\"Visualizza e stampa il partitario\" border=\"0\"><img src=\"../../library/images/stampa.gif\" border=\"0\"></a></td>";
       }
       echo "<td class=\"FacetDataTD\" align=\"center\" title=\"Stampa Codici a Barre\"><a href=\"stampa_barcode.php?code=".$r["codice"]."\"><img src=\"../../library/images/barcode.png\" border=\"0\"><br />".$r['barcode']."</a></td>";
       echo "<td class=\"FacetDataTD\" align=\"center\" title=\"Duplica articolo in (".$r["codice"]."_2)\"><a href=\"clone_artico.php?codice=".$r["codice"]."\"><img src=\"../../library/images/copy.png\" alt=\"Duplica!\" border=\"0\"></a></td>";
       echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_artico.php?codice=".$r["codice"]."\"><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
       echo "</tr>";
}
?>
</form>
</table>
</body>
</html>