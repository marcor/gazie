<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
         (www.facebook.com/antonio.devincentiis.9)
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

$message = "";
$anno = date("Y");
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
</script>';
?>
<table align="center" width="80%">
<tr>
<td><font class="FacetFormHeaderFont"><a href="admin_broacq.php?tipdoc=APR" accesskey="p">Nuovo Preventivo</a></td>
<td><font class="FacetFormHeaderFont"><a href="admin_broacq.php?tipdoc=AOR" accesskey="o">Nuovo Ordine</a></td>
<br>
</tr>
</table>
<div align="center" class="FacetFormHeaderFont">Preventivi e ordini a fornitori</div>
<?php
$where = "tipdoc = 'APR' or tipdoc = 'AOR'";
$recordnav = new recordnav($gTables['tesbro'], $where, $limit, $passo);
$recordnav -> output();
?>
<form method="GET" >

<div id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
      <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
      <p class="ui-state-highlight" id="mail_adrs"></p>
      <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
      <p class="ui-state-highlight" id="mail_attc"></p>
</div><table class="Tlarge">
<tr>
<?php
$headers_tesdoc = array  (
              "ID" => "id_tes",
              "Tipo" => "tipdoc",
              "Numero" => "numdoc",
              "Data" => "datemi",
              "Cliente" => "clfoco",
              "Status" => "",
              "Stampa" => "",
              "Mail" => "",
              "Cancella" => ""
              );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
if (!isset($_GET['flag_order']))
       $orderby = "id_tes desc";
$result = gaz_dbi_dyn_query ('*', $gTables['tesbro'], $where, $orderby, $limit, $passo);
$ctrlprotoc = "";
$anagrafica = new Anagrafica();
while ($r = gaz_dbi_fetch_array($result)) {
    if ($r["tipdoc"] == 'APR') {
        $tipodoc="Preventivo";
        $modulo="stampa_prefor.php?id_tes=".$r['id_tes'];
        $modifi="admin_broacq.php?id_tes=".$r['id_tes']."&Update";
    }
    if ($r["tipdoc"] == 'AOR') {
        $tipodoc="Ordine";
        $modulo="stampa_ordfor.php?id_tes=".$r['id_tes'];
        $modifi="admin_broacq.php?id_tes=".$r['id_tes']."&Update";
    }
    $fornitore = $anagrafica->getPartner($r['clfoco']);
    echo "<tr>";
    if (! empty ($modifi)) {
       echo "<td class=\"FacetDataTD\"><a href=\"".$modifi."\">".$r["id_tes"]."</td>";
    } else {
       echo "<td class=\"FacetDataTD\">".$r["id_tes"]." &nbsp;</td>";
    }
    echo "<td class=\"FacetDataTD\">".$tipodoc." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$r["numdoc"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$fornitore["ragso1"]."&nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$r["status"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\"><a href=\"".$modulo."\"><center><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\"></a>";
    echo "</td>";
     // Colonna "Mail"
    echo "<td class=\"FacetDataTD\" align=\"center\">";
    if (!empty($fornitore["e_mail"])) {
        echo '<a onclick="confirMail(this);return false;" id="doc'.$r["id_tes"].'" url="'.$modulo.'&dest=E" href="#" title="mailto: '.$fornitore["e_mail"].'"
        mail="'.$fornitore["e_mail"].'" namedoc="'.$tipodoc.' n.'.$r["numdoc"].' del '.gaz_format_date($r["datemi"]).'"><img src="../../library/images/email.gif" border="0"></a>';
    }  
    echo "</td>";
    echo "<td class=\"FacetDataTD\"><a href=\"delete_broacq.php?id_tes=".$r['id_tes']."\"><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
    echo "</tr>";
}
?>
</table>
</form>
</body>
</html>