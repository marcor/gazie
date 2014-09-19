<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
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
$anno = date("Y");
$cliente='';
$message = "";

function print_querytime($prev)
{
    list($usec, $sec) = explode(" ", microtime());
    $this_time= ((float)$usec + (float)$sec);
    echo round($this_time-$prev,8);
    return $this_time;
}

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
      $where = "tipdoc LIKE 'F%' AND ".$gTables['tesdoc'].".seziva = '$auxil' AND protoc = '$protocollo' GROUP BY protoc, datfat";
      $auxil = $_GET['auxil']."&protoc=".$protocollo;
      $passo = 1;
   }
}  else {
   $protocollo ='';
}
if (isset($_GET['cliente'])) {
   if ($_GET['cliente'] <> '') {
      $cliente = $_GET['cliente'];
      $where = " tipdoc LIKE 'F%' AND ".$gTables['tesdoc'].".seziva = '$auxil' and ".$gTables['clfoco'].".descri like '%".addslashes($cliente)."%' GROUP BY protoc, datfat";
      $auxil = $_GET['auxil']."&cliente=".$cliente;
      $passo = 50;
      unset($protocollo);
   }
}
if (isset($_GET['all'])) {
   gaz_set_time_limit (0);
   $where = "tipdoc LIKE 'F%' AND ".$gTables['tesdoc'].".seziva = '$auxil' GROUP BY protoc, datfat";
   $auxil = $_GET['auxil']."&all=yes";
   $passo = 100000;
   unset($protocollo);
   unset($cliente);
}

$titolo="Documenti di vendita a clienti";
require("../../library/include/header.php");
$script_transl=HeadMain(0,array('jquery/jquery-1.7.1.min',
                                  'jquery/ui/jquery.ui.core',
                                  'jquery/ui/jquery.ui.widget',
                                  'jquery/ui/jquery.ui.mouse',
                                  'jquery/ui/jquery.ui.button',
                                  'jquery/ui/jquery.ui.dialog',
                                  'jquery/ui/jquery.ui.position',
                                  'jquery/ui/jquery.ui.draggable',
                                  'jquery/ui/jquery.ui.resizable',
                                  'jquery/ui/jquery.effects.core',
                                  'jquery/ui/jquery.effects.scale',
                                  'jquery/modal_form'));
echo '<script>
$(function() {
   $( "#dialog" ).dialog({
      autoOpen: false
   });
   
   $( "#dialog1" ).dialog({
      autoOpen: false
   });
   
});
function confirMail(link){
   tes_id = link.id.replace("doc", "");
   $.fx.speeds._default = 500;
   targetUrl = $("#doc"+tes_id).attr("url");
   //alert (targetUrl);
   $("p#mail_adrs").html($("#doc"+tes_id).attr("mail"));
   $("p#mail_attc").html($("#doc"+tes_id).attr("namedoc"));
   $( "#dialog" ).dialog({
         modal: "true",
      show: "blind",
      hide: "explode",
         buttons: {
                      " '.$script_transl['submit'].' ": function() {
                         window.location.href = targetUrl;
                      },
                      " '.$script_transl['cancel'].' ": function() {
                        $(this).dialog("close");
                      }
                  }
         });
   $("#dialog" ).dialog( "open" );
}



function confirFae(link){

   $.fx.speeds._default = 500;
  
   $( "#dialog1" ).dialog({
         modal: "true",
      show: "blind",
      hide: "explode",
         buttons: {
                      " '.$script_transl['submit'].' ": function() {
                         window.location.href = link.href;
                          $(this).dialog("close");
                      },
                      " '.$script_transl['cancel'].' ": function() {
                        $(this).dialog("close");
                      }
                  }
         });
   $("#dialog1" ).dialog( "open" );
}


</script>';
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

<div id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
      <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
      <p class="ui-state-highlight" id="mail_adrs"></p>
      <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
      <p class="ui-state-highlight" id="mail_attc"></p>
</div>

<div id="dialog1" title="<?php echo $script_transl['fae_alert0']; ?>">
      <p id="mail_alert1"><?php echo $script_transl['fae_alert1']; ?></p>
      <p class="ui-state-highlight" id="fae1"></p>
      <p id="mail_alert2"><?php echo $script_transl['fae_alert2']; ?></p>
      <p class="ui-state-highlight" id="fae2"></p>
</div>

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
list($usec, $sec) = explode(' ',microtime());
$querytime = ((float)$usec + (float)$sec);
$querytime_before = $querytime;
$recordnav = new recordnav($gTables['tesdoc'].' LEFT JOIN '.$gTables['clfoco'].' on '.$gTables['tesdoc'].'.clfoco = '.$gTables['clfoco'].'.codice', $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
 <tr>
   <td class="FacetFieldCaptionTD">Protocollo:
     <input type="text" name="protoc" value="<?php if (isset($protocollo)) echo $protocollo; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
   </td>
   <td></td>
   <td class="FacetFieldCaptionTD"></td>
   <td></td>
   <td colspan="1" class="FacetFieldCaptionTD">Cliente:
     <input type="text" name="cliente" value="<?php if (isset($cliente)) { print $cliente;} ?>" maxlength="40" size="30" tabindex="2" class="FacetInput">
   </td>
   <td>
     <input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
   </td>
   <td colspan="2">
     <input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
   </td>
   <td colspan="2">
   
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
            "FAE" => "",
            "Mail" => "",
            "Origine" => "",
            "Cancella" => ""
            );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
$rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'].' LEFT JOIN '.$gTables['clfoco'].' on '.$gTables['tesdoc'].'.clfoco = '.$gTables['clfoco'].'.codice', $where,'datfat DESC, CONVERT(numfat,UNSIGNED INTEGER) DESC',0,1);
$ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesdoc'].".*, MAX(".$gTables['tesdoc'].".id_tes) AS reftes,".$gTables['anagra'].".fe_cod_univoco,".$gTables['anagra'].".ragso1,".$gTables['anagra'].".e_mail,".$gTables['pagame'].".tippag", $gTables['tesdoc']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesdoc'].".clfoco = ".$gTables['clfoco'].".codice LEFT JOIN ".$gTables['anagra']." ON ".$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id  LEFT JOIN ".$gTables['pagame']." ON ".$gTables['tesdoc'].".pagame = ".$gTables['pagame'].".codice", $where, $orderby,$limit, $passo);
$ctrl_doc = "";
$ctrl_eff = 999999;
while ($r = gaz_dbi_fetch_array($result)) {
    $modulo_fae="electronic_invoice.php?id_tes=".$r['id_tes'];
    $modulo_fae_report="report_fae_sdi.php?id_tes=".$r['id_tes'];
    if ($r["tipdoc"] == 'FAI') {
        $tipodoc="Fattura Immediata";
        $modulo="stampa_docven.php?id_tes=".$r['id_tes'];
        $modifi="admin_docven.php?Update&id_tes=".$r['id_tes'];
    } elseif ($r["tipdoc"] == 'FAD') {
        $tipodoc="Fattura Differita";
        $modulo="stampa_docven.php?td=2&si=".$r["seziva"]."&pi=".$r['protoc']."&pf=".$r['protoc']."&di=".$r['datfat']."&df=".$r['datfat'];
        $modulo_fae="electronic_invoice.php?seziva=".$r["seziva"]."&protoc=".$r['protoc']."&year=".substr($r['datfat'],0,4);
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
           echo " <a style=\"font-size:10px;\" title=\"Modifica il movimento contabile generato da questo documento\" href=\"../contab/admin_movcon.php?id_tes=".$r["id_con"]."&Update\">Cont.".$r["id_con"]."</a> ";
        } else {
           echo " <a href=\"accounting_documents.php?type=F&vat_section=".substr($auxil,0,1)."&last=".$r["protoc"]."\">Contabilizza</a>";
        }
        $effett_result = gaz_dbi_dyn_query ('*',$gTables['effett'],"id_doc = ".$r["reftes"],'progre');
        while ($r_e = gaz_dbi_fetch_array ($effett_result)){
           // La fattura ha almeno un effetto emesso
           $n_e++;
           if ($r_e["tipeff"] == "B") {
                        echo " <a style=\"font-size:10px;\" title=\"visualizza la ricevuta bancaria generata per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=".$r_e["id_tes"]."\">";
                        echo "RiBa".$r_e["progre"];
                        echo "</a>";
           } elseif ($r_e["tipeff"] == "T")  {
                        echo " <a style=\"font-size:10px;\" title=\"visualizza la cambiale tratta generata per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=".$r_e["id_tes"]."\">";
                        echo "Tratta".$r_e["progre"];
                        echo "</a>";
           } elseif ($r_e["tipeff"] == "V")  {
                        echo " <a style=\"font-size:10px;\" title=\"visualizza il pagamento mediante avviso generato per il regolamento della fattura\" href=\"stampa_effett.php?id_tes=".$r_e["id_tes"]."\">";
                        echo "MAV".$r_e["progre"];
                        echo "</a>";
           }  else {
                        echo " <a style=\"font-size:10px;\" title=\"visualizza l'effetto\" href=\"stampa_effett.php?id_tes=".$r_e["id_tes"]."\">";
                        echo $r_e["tipeff"].$r_e["progre"];
                        echo "</a>";
           }
        }
        if ($n_e==0 && ($r["tippag"]=='B' || $r["tippag"]=='T' || $r["tippag"]=='V')) {
              echo " <a title=\"genera gli effetti previsti per il regolamento delle fatture\" href=\"genera_effett.php\"> Genera effetti</a>";
              if (strtoupper($r["geneff"])=='S'){
                    // Gli effetti della fattura sono stati generati in passato, ma poi
                    // sono stati rimossi tutti.
                    /* gaz_dbi_put_row ($gTables['tesdoc'],"id_tes",$r["id_tes"],"geneff","");
                       IN ALCUNE INSTALLAZIONI CREA PROBLEMI */
              }

        }
        echo "</td>";
        // Colonna "Stampa"
        echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"".$modulo."\"><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a>";
        echo "</td>";
        
        // Colonna "Fattura elettronica"
        if (substr($r["tipdoc"],0,1)=='F'){
            if (strlen($r["fe_cod_univoco"])!=6) { // se il cliente non è un ufficio della PA tolgo il link
               $modulo_fae='';
               echo "<td class=\"FacetDataTD\" align=\"center\"><img width=\"20px\" src=\"../../library/images/e_inv_disabled.png\" title=\"Fattura elettronica non disponibile: codice ufficio univoco non presente\" border=\"0\">";
               echo "</td>";
            } else {
              echo "<td class=\"FacetDataTD genera\" align=\"center\"><a onclick=\"confirFae(this);return false;\" n_fatt=\"".$r["numfat"]."\" target=\"_blank\" href=\"".$modulo_fae."\"><img width=\"20px\" src=\"../../library/images/e_inv.png\" alt=\"Fattura elettronica\" border=\"0\"></a>";
              //identifica le fatture inviate all'sdi           
              $where2 = " id_tes_ref = ".$r['id_tes'] . " AND (flux_status LIKE '@' OR flux_status LIKE '#')";
              $result2 = gaz_dbi_dyn_query ("*", $gTables['fae_flux'], $where2);
              $r2 = gaz_dbi_fetch_array($result2);   
              if ($r2 == false) {
              } elseif ($r2['flux_status']=="@") {
                 echo " <a  title=\"Fattura elettronica inviata: VEDI REPORT\" class=\"FacetDataTDred\" target=\"_blank\" href=\"".$modulo_fae_report."\"> <img width=\"20px\" src=\"../../library/images/listed.png\" border=\"0\"></a>";
              } elseif ($r2['flux_status']=="#") {
                 echo " <a title=\"Fattura elettronica generata: VEDI REPORT\" target=\"_blank\" href=\"".$modulo_fae_report."\"> #<img width=\"20px\" src=\"../../library/images/listed.png\" border=\"0\"></a>";
              }   
              echo "</td>";
            }
         } else {
           echo "<td></td>";
         }
                 
        // Colonna "Mail"
        echo "<td class=\"FacetDataTD\" align=\"center\">";
        if (!empty($r["e_mail"])) {
            echo '<a onclick="confirMail(this);return false;" id="doc'.$r["id_tes"].'" url="'.$modulo.'&dest=E" href="#" title="mailto: '.$r["e_mail"].'"
            mail="'.$r["e_mail"].'" namedoc="'.$tipodoc.' n.'.$r["numfat"].' del '.gaz_format_date($r["datfat"]).'"><img src="../../library/images/email.gif" border="0"></a>';
        }  
        echo "</td>";
        // Colonna "Origine"
        if ($r["tipdoc"]=='FAD'){
           $ddt_result = gaz_dbi_dyn_query ('*',$gTables['tesdoc'],"tipdoc = '".$r["tipdoc"]."' AND numfat = ".$r["numfat"]." AND datfat = '".$r["datfat"]."'",'datemi DESC');
           echo "<td class=\"FacetDataTD\" align=\"center\">";
           while ($r_d = gaz_dbi_fetch_array ($ddt_result)){
             echo " <a title=\"visualizza il DdT\" href=\"stampa_docven.php?id_tes=".$r_d['id_tes']."&template=DDT\" style=\"font-size:10px;\">DdT".$r_d['numdoc']."</a>\n";
           }
           echo "</td>";
        } elseif($r["id_contract"]>0) {
           $con_result = gaz_dbi_dyn_query ('*',$gTables['contract'],"id_contract = ".$r["id_contract"],'conclusion_date DESC');
           echo "<td class=\"FacetDataTD\" align=\"center\">";
           while ($r_d = gaz_dbi_fetch_array ($con_result)){
             echo " <a title=\"visualizza il contratto\" href=\"print_contract.php?id_contract=".$r_d['id_contract']."\" style=\"font-size:10px;\">Contr.".$r_d['doc_number']."</a>\n";
           }
           echo "</td>";
        } else {
           echo "<td class=\"FacetDataTD\"></td>";
        }
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
/*        echo "<td class=\"FacetDataTD\" align=\"right\">";
        $querytime=print_querytime($querytime);
        echo "</td>";*/
        echo "</tr>\n";
    }
    $ctrl_doc = sprintf('%09d',$r['protoc']).$r['datfat'];
}
echo '<tr><td colspan="9" align="right">Querytime: ';
print_querytime($querytime);
echo ' sec.</td></tr>';
?>
</table>
</form>
</body>
</html>