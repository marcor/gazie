<?php
/*$Id: report_docven.php,v 1.46 2011/01/22 15:50:47 devincen Exp $
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
$anno = date("Y");
$message = "";
if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
   $where = "tipdoc LIKE 'F%' AND ".$gTables['tesdoc'].".seziva = '$auxil' GROUP BY protoc, datfat";
} else {
   $auxil = 1;
   $where = "tipdoc LIKE 'F%' AND ".$gTables['tesdoc'].".seziva = '$auxil' GROUP BY protoc, datfat";
}
if (isset($_GET['protoc'])) {
   if ($_GET['protoc'] > 0) {
      $protocollo = $_GET['protoc'];
      $auxil = $_GET['auxil']."&protoc=".$protocollo;
      $where = "tipdoc LIKE 'F%' AND ".$gTables['tesdoc'].".seziva = '$auxil' AND protoc = '$protocollo' GROUP BY protoc, datfat";
      $passo = 1;
   }
}  else {
   $protocollo ='';
}
if (isset($_GET['all'])) {
   $where = "tipdoc LIKE 'F%' AND ".$gTables['tesdoc'].".seziva = '$auxil' GROUP BY protoc, datfat";
   $auxil = $_GET['auxil']."&all=yes";
   $passo = 100000;
   $protocollo ='';
}

$titolo="Documenti di vendita a clienti";
require("../../library/include/header.php");
$script_transl=HeadMain();
switch($admin_aziend['fatimm']) {
    case "1":
        $sezfatimm = 1;
    break;
    case "2":
        $sezfatimm = 2;
    break;
    case "3":
        $sezfatimm = 3;
    break;
    case "R":
        $sezfatimm = substr($auxil,0,1);
    break;
    case "U":
        $rs_ultimo = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "datemi LIKE '$anno%' AND tipdoc = 'FAI'","datfat desc",0,1);
        $ultimo = gaz_dbi_fetch_array($rs_ultimo);
        $sezfatimm = $ultimo['seziva'];
    break;
    default:
        $sezfatimm = substr($auxil,0,1);
}

?>
<table border="0" cellpadding="3" cellspacing="1" align="center" width="70%">
<tr>
<td class="FacetFormHeaderFont"><a href="admin_docven.php?Insert&seziva=<?php echo $sezfatimm; ?>&tipdoc=FAI" accesskey="e">Emetti Nuova Fattura Immediata</a></td>
<td class="FacetFormHeaderFont"><a href="admin_docven.php?Insert&seziva=<?php echo substr($auxil,0,1); ?>&tipdoc=FNC">Emetti Nuova Nota Credito</a></td>
<td class="FacetFormHeaderFont"><a href="admin_docven.php?Insert&seziva=<?php echo substr($auxil,0,1); ?>&tipdoc=FAP">Emetti Nuova Parcella</a></td>
</tr>
<tr>
<td class="FacetFormHeaderFont"><a href="emissi_fatdif.php?seziva=<?php echo substr($auxil,0,1); ?>" accesskey="g">Genera Fatture Differite da D.d.T</a></td>
<td class="FacetFormHeaderFont"><a href="accounting_documents.php?type=F&vat_section=<?php echo substr($auxil,0,1); ?>" accesskey="c">Contabilizzazione Fatture</a></td>
<td class="FacetFormHeaderFont"><a href="select_docforprint.php?seziva=<?php echo substr($auxil,0,1); ?>" accesskey="s">Ristampa Documenti gi&agrave; emessi</a></td>
</tr>
<tr>
<td class="FacetFormHeaderFont"><a href="admin_docven.php?Insert&seziva=<?php echo substr($auxil,0,1); ?>&tipdoc=FND">Emetti Nuova Nota Debito</a></td>
</tr>
</table>
<form method="GET" >
<div align="center"><font class="FacetFormHeaderFont">Documenti di vendita della sezione
<select name="auxil" class="FacetSelect" onchange="this.form.submit()">
<?php
for ($sez = 1; $sez <= 3; $sez++) {
    $selected = "";
    if(substr($auxil,0,1) == $sez) {
        $selected = " selected ";
    }
    echo "<option value=\"".$sez."\"".$selected.">".$sez."</option>";
}
?>
</select></font></div>
<?php
if (!isset($_GET['field']) or ($_GET['field'] == 2) or(empty($_GET['field'])))
        $orderby = "datfat desc, protoc desc";
$recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
<tr>
<td colspan="2" class="FacetFieldCaptionTD">Protocollo:
<input type="text" name="protoc" value="<?php if (isset($protocollo)) echo $protocollo; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
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
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
            "Prot." => "protoc",
            "Tipo" => "tipdoc",
            "Numero" => "numfat",
            "Data" => "datfat",
            "Cliente" => "ragso1",
            "Status" => "",
            "Stampa" => "",
            "Cancella" => ""
            );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
$rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where,'datfat DESC, protoc DESC',0,1);
$ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesdoc'].".*, MAX(".$gTables['tesdoc'].".id_tes) AS reftes,".$gTables['anagra'].".ragso1,".$gTables['pagame'].".tippag", $gTables['tesdoc']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesdoc'].".clfoco = ".$gTables['clfoco'].".codice LEFT JOIN ".$gTables['anagra']." ON ".$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id  LEFT JOIN ".$gTables['pagame']." ON ".$gTables['tesdoc'].".pagame = ".$gTables['pagame'].".codice", $where, $orderby,$limit, $passo);
$ctrl_doc = "";
$ctrl_eff = 999999;
while ($r = gaz_dbi_fetch_array($result)) {
    if ($r["tipdoc"] == 'FAI') {
        $tipodoc="Fattura Immediata";
        $modulo="stampa_docven.php?id_tes=".$r['id_tes'];
        $modifi="admin_docven.php?Update&id_tes=".$r['id_tes'];
    } elseif ($r["tipdoc"] == 'FAD') {
        $tipodoc="Fattura Differita";
        $modulo="stampa_docven.php?td=2&si=".$r["seziva"]."&pi=".$r['protoc']."&pf=".$r['protoc']."&di=".$r['datfat']."&df=".$r['datfat'];
        $modifi="";
    } elseif ($r["tipdoc"] == 'FAP') {
        $tipodoc="Parcella";
        $modulo="stampa_docven.php?id_tes=".$r['id_tes'];
        $modifi="admin_docven.php?Update&id_tes=".$r['id_tes'];
    } elseif ($r["tipdoc"] == 'FNC') {
        $tipodoc="Nota Credito";
        $modulo="stampa_docven.php?id_tes=".$r['id_tes'];
        $modifi="admin_docven.php?Update&id_tes=".$r['id_tes'];
    } elseif ($r["tipdoc"] == 'FND') {
        $tipodoc="Nota Debito";
        $modulo="stampa_docven.php?id_tes=".$r['id_tes'];
        $modifi="admin_docven.php?Update&id_tes=".$r['id_tes'];
    } else {
        $tipodoc="DOC.SCONOSCIUTO";
        $modulo="stampa_docven.php?id_tes=".$r['id_tes'];
        $modifi="admin_docven.php?Update&id_tes=".$r['id_tes'];
    }
    if (sprintf('%09d',$r['protoc']).$r['datfat'] <> $ctrl_doc)    {
        $n_e=0;
        echo "<tr>";
        if (! empty ($modifi)) {
           echo "<td class=\"FacetDataTD\"><a href=\"".$modifi."\">".$r["protoc"]."</td>";
        } else {
           echo "<td class=\"FacetDataTD\">".$r["protoc"]." &nbsp;</td>";
        }
        echo "<td class=\"FacetDataTD\">".$tipodoc." &nbsp;</td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">".$r["numfat"]." &nbsp;</td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($r["datfat"])." &nbsp;</td>";
        echo "<td class=\"FacetDataTD\">".$r["ragso1"]."&nbsp;</td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">";
        if ($r["id_con"] > 0) {
           echo "<a href=\"../contab/admin_movcon.php?id_tes=".$r["id_con"]."&Update\">Cont. n.".$r["id_con"]."</a> ";
        } else {
           echo "<a href=\"accounting_documents.php?type=F&vat_section=".substr($auxil,0,1)."&last=".$r["protoc"]."\">Contabilizza</a>";
        }
        $effett_result = gaz_dbi_dyn_query ('*',$gTables['effett'],"id_doc = ".$r["reftes"],'progre');
        while ($r_e = gaz_dbi_fetch_array ($effett_result)){
           // La fattura ha almeno un effetto emesso
           $n_e++;
           if ($r_e["tipeff"] == "B") {
                        echo " <a title=\"visualizza la ricevuta bancaria generata per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=".$r_e["id_tes"]."\">";
                        echo "RiBa ".$r_e["progre"];
                        echo "</a>";
           } elseif ($r_e["tipeff"] == "T")  {
                        echo " <a title=\"visualizza la cambiale tratta generata per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=".$r_e["id_tes"]."\">";
                        echo "Tratta ".$r_e["progre"];
                        echo "</a>";
           }  else {
                        echo " <a title=\"visualizza l'effetto\" href=\"stampa_effett.php?id_tes=".$r_e["id_tes"]."\">";
                        echo $r_e["tipeff"].$r_e["progre"];
                        echo "</a>";
           }
        }
        if ($n_e==0 && ($r["tippag"]=='B' || $r["tippag"]=='T')) {
              echo " <a title=\"genera gli effetti previsti per il regolamento delle fatture\" href=\"genera_effett.php\"> Genera effetti</a>";
              if (strtoupper($r["tippag"])=='S'){
                    // Gli effetti della fattura sono stati generati in passato, ma poi
                    // sono stati rimossi tutti.
                    gaz_dbi_put_row ($gTables['tesdoc'],"id_tes",$r["id_tes"],"geneff","");
              }

        }
        echo "</td>";
        // Colonna "Stampa"
        echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"".$modulo."\"><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a></td>";
        // Colonna "Cancella"
        echo "<td class=\"FacetDataTD\" align=\"center\">";
        if ($ultimo_documento['id_tes'] == $r["id_tes"] ) {
           // Permette di cancellare il documento.
           if ($r["id_con"] > 0) {
               echo "<a title=\"cancella il documento e la registrazione contabile relativa\" href=\"delete_docven.php?seziva=".$r["seziva"]."&protoc=".$r['protoc']."&anno=".substr($r["datfat"],0,4)."\"><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a>";
           } else {
               echo "<a title=\"cancella il documento\" href=\"delete_docven.php?seziva=".$r["seziva"]."&protoc=".$r['protoc']."&anno=".substr($r["datfat"],0,4)."\"><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a>";
           }
        } else {
           echo "<img title=\"per garantire la sequenza corretta della numerazione, non &egrave; possibile cancellare un documento diverso dall'ultimo\" src=\"../../library/images/stop-info.gif\" alt=\"!\" border=\"0\">";
        }
        echo "</td>";
        //
        echo "</tr>\n";
    }
    $ctrl_doc = sprintf('%09d',$r['protoc']).$r['datfat'];
}
?>
</form>
</table>
</body>
</html>