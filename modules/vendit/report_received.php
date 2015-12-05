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

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
} else {
   $auxil = 1;
}

$where = " tipdoc = 'VRI' AND seziva = '$auxil'";
$doc ='';
if (isset($_GET['numdoc'])) {
   if ($_GET['numdoc'] > 0) {
      $doc = intval($_GET['numdoc']);
      $auxil = $_GET['auxil']."&numdoc=".$doc;
      $where = " tipdoc = 'VRI' AND seziva = '$auxil' AND numdoc = '$doc'";
      $passo = 1;
   }
}
if (isset($_GET['all'])) {
   $auxil = $_GET['auxil']."&all=yes";
   $passo = 100000;
   $where = " tipdoc = 'VRI' AND seziva = '$auxil'";
}

require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<form method="GET">
<div align="center" class="FacetFormHeaderFont"> Ricevute della sezione
<select name="auxil" class="FacetSelect" onchange="this.form.submit()">
<?php
for ($sez = 1; $sez <= 3; $sez++) {
     $selected="";
     if(substr($auxil,0,1) == $sez)
        $selected = " selected ";
     echo "<option value=\"".$sez."\"".$selected.">".$sez."</option>";
}
?>
</select>
</div>
<?php
if (!isset($_GET['field']) or ($_GET['field'] == 2) or(empty($_GET['field'])))
        $orderby = "datfat DESC, numfat DESC";
$recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<td colspan="2" class="FacetFieldCaptionTD">
<input type="text" placeholder="Cerca Numero" class="input-xs form-control" name="numdoc" value="<?php if ($doc > 0) print $doc; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" class="btn btn-xs btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td>
<input type="submit" class="btn btn-xs btn-default" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
              "Numero" => "numfat",
              "Data" => "datfat",
              "Cliente" => "ragso1",
              "Telefono" => "Importo",
              "Stampa" => "",
              "Cancella" => ""
              );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
$rs_last_received = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where,"datfat DESC, numfat DESC",0,1);
$last_received = gaz_dbi_fetch_array($rs_last_received);
if ($last_received)
    $last_n = $last_received['numdoc'];
else
    $last_n = 1;
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesdoc'].".*,".$gTables['anagra'].".ragso1,".$gTables['anagra'].".telefo", $gTables['tesdoc']."
                            LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesdoc'].".clfoco = ".$gTables['clfoco'].".codice
                            LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra", $where, $orderby,$limit, $passo);
while ($row = gaz_dbi_fetch_array($result)) {
    echo "<tr>";
    echo "<td class=\"FacetDataTD\"><a href=\"admin_docven.php?Update&id_tes=".$row["id_tes"]."\">".$row["numdoc"]."</a> &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$row["datfat"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$row["ragso1"]."&nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$row["telefo"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">
			<a href=\"stampa_docven.php?id_tes=".$row["id_tes"]."&template=Received\" title=\"Stampa\" class=\"btn btn-xs btn-default\">
				<i class=\"glyphicon glyphicon-print\"></i>
			</a>
		  </td>";
    if ($last_n == $row["numfat"] && $row["id_con"] == 0){
       echo "<td class=\"FacetDataTD\" align=\"center\">
	   			<a href=\"delete_docven.php?id_tes=".$row["id_tes"]."\" title=\"Cancella\" class=\"btn btn-xs btn-default\">
					<i class=\"glyphicon glyphicon-remove\"></i>
			 	</a>
			 </td>";
    } else {
        echo "<td class=\"FacetDataTD\"></td>";
    }
    echo "</tr>\n";
}
?>
</form>
</table>
</div><!-- chiude div container role main --></body>
</html>