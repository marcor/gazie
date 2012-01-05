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

function getDocRef($data){
    global $gTables;
    $r=array();
    return $r;
}

if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
   if ($_GET['auxil']=='VPR'){
       $what='VPR';
   } else {
       $what=substr($auxil,0,2)."_";
   }
   $where = "tipdoc LIKE '$what'";
} else {
   $auxil = 'VO_';
   $_GET['auxil']='VOR';
   $where = "tipdoc LIKE '$auxil'";
}

if (isset($_GET['numdoc'])) {
   if ($_GET['numdoc'] > 0) {
      $numero = $_GET['numdoc'];
      $auxil = $_GET['auxil'];
      $where = "tipdoc LIKE '$auxil' AND numdoc = ".$numero;
      $passo = 1;
   }
}  else {
   $numero ='';
}

if (isset($_GET['all'])) {
   $auxil = $_GET['auxil']."&all=yes";
   if ($_GET['auxil']=='VPR'){
       $what='VPR';
   } else {
       $what=substr($auxil,0,2)."_";
   }
   $where = "tipdoc LIKE '$what'";
   $passo = 100000;
   $numero ='';
}

require("../../library/include/header.php");
$script_transl=HeadMain();
$a=substr($auxil,0,3);
?>
<table border="0" cellpadding="3" cellspacing="1" align="center" width="70%">
<tr>
<?php
if (substr($a,1,1)=='O') {
echo '<td align="center" class="FacetFormHeaderFont">';
echo '<a href="'.$script_transl['link_value']['VOR'].'">'.$script_transl['link_title']['VOR'].'</a></td>';
echo '<td align="center" class="FacetFormHeaderFont">  ';
echo '<a href="'.$script_transl['link_value']['VOW'].'">'.$script_transl['link_title']['VOW'].'</a></td>';
echo '<td align="center" class="FacetFormHeaderFont"><a href="select_evaord.php">'.$script_transl['issue_ord'].'</a></td>';
}  else {
echo '<td align="center" class="FacetFormHeaderFont">';
echo '<a href="'.$script_transl['link_value']['VPR'].'">'.$script_transl['link_title']['VPR'].'</a></td>';
}
?>

</tr>
</table>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title_value'][$_GET['auxil']]; ?></div>
<?php
$recordnav = new recordnav($gTables['tesbro'], $where, $limit, $passo);
$recordnav -> output();
?>
<form method="GET" >
<table class="Tlarge">
<tr>
<td colspan="3" class="FacetFieldCaptionTD"><?php echo $script_transl['number'];?>:&nbsp;
<input type="text" name="numdoc" value="<?php if (isset($numero)) echo $numero; ?>" maxlength="14" size="14" tabindex="1" class="FacetInput">
</td>
<td>
<input type="submit" name="search" value="<?php echo $script_transl['search'];?>" tabindex="1" onClick="javascript:document.report.all.value=1;">
</td>
<td>
<input type="submit" name="all" value="<?php echo $script_transl['vall'];?>" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<tr>
<?php
echo '<input type="hidden" name="auxil" value="'.substr($_GET['auxil'],0,3).'">';
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesbro = array  (
              "ID" => "id_tes",
              $script_transl['type'] => "tipdoc",
              $script_transl['number'] => "numdoc",
              $script_transl['date'] => "datemi",
              "Cliente" => "clfoco",
              $script_transl['status'] => "status",
              $script_transl['print'] => "",
              $script_transl['delete'] => ""
              );
$linkHeaders = new linkHeaders($headers_tesbro);
$linkHeaders -> output();
?>
</tr>
<?php
if (!isset($_GET['flag_order']))
       $orderby = "datemi DESC, numdoc DESC";
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesbro'].".*,".$gTables['anagra'].".ragso1", $gTables['tesbro']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesbro'].".clfoco = ".$gTables['clfoco'].".codice  LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $where, $orderby,$limit, $passo);
$ctrlprotoc = "";
while ($r = gaz_dbi_fetch_array($result)) {
    if ($r["tipdoc"] == 'VPR') {
        $modulo="stampa_precli.php?id_tes=".$r['id_tes'];
        $modifi="admin_broven.php?Update&id_tes=".$r['id_tes'];
    }
    if (substr($r["tipdoc"],1,1) == 'O') {
        $modulo="stampa_ordcli.php?id_tes=".$r['id_tes'];
        $modifi="admin_broven.php?Update&id_tes=".$r['id_tes'];
    }
    echo "<tr>";
    if (!empty ($modifi)) {
       echo "<td class=\"FacetDataTD\"><a href=\"".$modifi."\">".$r["id_tes"]."</td>";
    } else {
       echo "<td class=\"FacetDataTD\">".$r["id_tes"]." &nbsp;</td>";
    }
    echo "<td class=\"FacetDataTD\">".$script_transl['type_value'][$r["tipdoc"]]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$r["numdoc"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$r["ragso1"]."&nbsp;</td>";
    $remains_atleastone = false; // Almeno un rigo e' rimasto da evadere.
    $processed_atleastone = false; // Almeno un rigo e' gia' stato evaso.
    $rigbro_result = gaz_dbi_dyn_query ('*',$gTables['rigbro'],"id_tes = ".$r["id_tes"]." AND tiprig <=1 ",'id_tes DESC');
    while ($rigbro_r = gaz_dbi_fetch_array ($rigbro_result)) {
        if ($rigbro_r["id_doc"] == 0) {
            $remains_atleastone = true;
            continue;
        }
        $tesdoc_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],"id_tes = ".$rigbro_r["id_doc"],'id_tes DESC');
        $tesdoc_r = gaz_dbi_fetch_array ($tesdoc_result);
        if ($rigbro_r["id_doc"] != $tesdoc_r["id_tes"]) {
            //
            // Azzera il numero documento nel rigo dell'ordine, dato
            // che non e' piu' valido.
            //
            gaz_dbi_put_row ($gTables['rigbro'],"id_tes",$rigbro_r["id_tes"],"id_doc",0);
            //
            // Il rigo e' da evadere.
            //
            $remains_atleastone = true;
        } else {
            //
            // L'ordine sembra evaso.
            //
            $processed_atleastone = true;
        }
    }
    //
    // Se l'ordine e' da evadere completamente, verifica lo status ed
    // eventualmente lo aggiorna.
    //
    if ($remains_atleastone && !$processed_atleastone) {
        //
        // L'ordine e' completamente da evadere.
        //
        if ($r["status"] != "GENERATO") {
            gaz_dbi_put_row ($gTables['tesbro'],"id_tes",$r["id_tes"],"status","RIGENERATO");
        }
        echo "<td class=\"FacetDataTD\"><a href=\"select_evaord.php?id_tes=".$r['id_tes']."\">evadi</a></td>";
    } elseif ($remains_atleastone) {
        echo "<td class=\"FacetDataTD\">";

        $ultimo_documento = 0;
        //
        // Interroga la tabella gaz_XXXrigbro per le righe corrispondenti
        // a questa testata.
        //
        $rigbro_result = gaz_dbi_dyn_query ('*',$gTables['rigbro'],"id_tes = ".$r["id_tes"],'id_tes DESC');
        //
        while ($rigbro_r = gaz_dbi_fetch_array ($rigbro_result)) {
            if ($rigbro_r["id_doc"] == 0) {
                continue;
            } else {
                if ($ultimo_documento == $rigbro_r["id_doc"]) {
                    continue;
                } else {
                    //
                    // Individua il documento.
                    //
                    $tesdoc_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],"id_tes = ".$rigbro_r["id_doc"],'id_tes DESC');
                    #
                    $tesdoc_r = gaz_dbi_fetch_array ($tesdoc_result);
                    #
                    if ($tesdoc_r["tipdoc"] == "FAI") {
                        echo "<a title=\"visualizza la fattura immediata\" href=\"stampa_docven.php?id_tes=".$rigbro_r["id_doc"]."\">";
                        echo "fatt. ". $tesdoc_r["numfat"];
                        echo "</a> ";
                    } elseif ($tesdoc_r["tipdoc"] == "DDT") {
                        echo "<a title=\"visualizza il documento di trasporto\" href=\"stampa_docven.php?id_tes=".$rigbro_r["id_doc"]."&template=DDT\">";
                        echo "ddt ". $tesdoc_r["numdoc"];
                        echo "</a> ";
                    } else {
                        echo $tesdoc_r["tipdoc"].$rigbro_r["id_doc"]. " ";
                    }
                    $ultimo_documento = $rigbro_r["id_doc"];
                }
            }
        }
        echo "<a href=\"select_evaord.php?id_tes=".$r['id_tes']."\">evadi il rimanente</a></td>";
    } else {
        echo "<td class=\"FacetDataTD\">";
        //
        $ultimo_documento = 0;
        //
        // Interroga la tabella gaz_XXXrigbro per le righe corrispondenti
        // a questa testata.
        //
        $rigbro_result = gaz_dbi_dyn_query ('*',$gTables['rigbro'],"id_tes = ".$r["id_tes"],'id_tes DESC');
        //
        while ($rigbro_r = gaz_dbi_fetch_array ($rigbro_result)) {
            if ($rigbro_r["id_doc"] == 0) {
                continue;
            } else {
                if ($ultimo_documento == $rigbro_r["id_doc"]) {
                    continue;
                } else {
                    //
                    // Individua il documento.
                    //
                    $tesdoc_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],"id_tes = ".$rigbro_r["id_doc"],'id_tes DESC');
                    $tesdoc_r = gaz_dbi_fetch_array ($tesdoc_result);
                    if ($tesdoc_r["tipdoc"] == "FAI") {
                        echo "<a title=\"visualizza la fattura immediata\" href=\"stampa_docven.php?id_tes=".$rigbro_r["id_doc"]."\">";
                        echo "fatt. ". $tesdoc_r["numfat"];
                        echo "</a> ";
                    } elseif ($tesdoc_r["tipdoc"] == "DDT" || $tesdoc_r["tipdoc"] == "FAD") {
                        echo "<a title=\"visualizza il documento di trasporto\" href=\"stampa_docven.php?id_tes=".$rigbro_r["id_doc"]."&template=DDT\">";
                        echo "ddt ". $tesdoc_r["numdoc"];
                        echo "</a> ";
                    } elseif ($tesdoc_r["tipdoc"] == "VCO") {
                        echo "<a title=\"visualizza lo scontrino come fattura\" href=\"stampa_docven.php?id_tes=".$rigbro_r["id_doc"]."&template=FatturaAllegata\">";
                        echo "scontrino n.". $tesdoc_r["numdoc"]."<br />del ".gaz_format_date($tesdoc_r["datemi"]);
                        echo "</a> ";
                    } else {
                        echo $tesdoc_r["tipdoc"].$rigbro_r["id_doc"]. " ";
                    }
                    $ultimo_documento = $rigbro_r["id_doc"];
                }
            }
        }
        echo "</td>";
    }
    echo "<td class=\"FacetDataTD\"><a href=\"".$modulo."\"><center><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
    echo "<td class=\"FacetDataTD\">";
    if (!$remains_atleastone || !$processed_atleastone) {
        //possono essere cancellati solo gli ordini inevasi o completamente evasi
        echo "<a href=\"delete_broven.php?id_tes=".$r['id_tes']."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a>";
    }
    echo "</td>";
    echo "</tr>\n";
}
?>
</table>
</form>
</body>
</html>