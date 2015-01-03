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
$msg = "";

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
if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
} else {
   $auxil = "";
   $where = "ragso1 LIKE '$auxil%' AND ".$gTables['letter'].".adminid = '".$_SESSION['Login']."'";
}

if (isset($_GET['all'])) {
   $auxil = "&all=yes";
   $where = $gTables['letter'].".adminid = '".$_SESSION['Login']."'";
   $passo = 100000;
} else {
   if (isset($_GET['auxil'])) {
      $where = "ragso1 like '".addslashes($auxil)."%' AND ".$gTables['letter'].".adminid = '".$_SESSION['Login']."'";
   }
}

if (!isset($_GET['flag_order'])) {
   $orderby = " write_date desc, numero desc";
}
?>
<div id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
      <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
      <p class="ui-state-highlight" id="mail_adrs"></p>
      <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
      <p class="ui-state-highlight" id="mail_attc"></p>
</div>
<?php
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">$script_transl[0]</div>\n";
echo "<form method=\"GET\">";
echo "<table class=\"Tlarge\">\n";
echo "<tr><td colspan=\"2\"></td><td class=\"FacetFieldCaptionTD\" colspan=\"2\">$script_transl[4] :\n";
echo "<input type=\"text\" name=\"auxil\" value=\"";
if ($auxil != "&all=yes"){
    echo $auxil;
}
echo "\" maxlength=\"6\" size=\"3\" tabindex=\"1\" class=\"FacetInput\"></td>\n";
echo "<td><input type=\"submit\" name=\"search\" value=\"".$script_transl['search']."\" tabindex=\"1\" onClick=\"javascript:document.report.all.value=1;\"></td>\n";
echo "<td><input type=\"submit\" name=\"all\" value=\"".$script_transl['vall']."\" onClick=\"javascript:document.report.all.value=1;\"></td></tr>\n";
$table = $gTables['letter']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['letter'].".clfoco = ".$gTables['clfoco'].".codice
                              LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra";
$result = gaz_dbi_dyn_query ($gTables['letter'].".id_let, ".
                    $gTables['letter'].".write_date, ".
                    $gTables['letter'].".numero, ".
                    $gTables['letter'].".tipo, ".
                    $gTables['letter'].".oggetto, ".
                    $gTables['clfoco'].".codice, ".
                    $gTables['anagra'].".ragso1, ".
                    $gTables['anagra'].".ragso2, ".
                    $gTables['anagra'].".e_mail ", $table, $where, $orderby, $limit, $passo);
// creo l'array (header => campi) per l'ordinamento dei record
$headers_mov = array  (
            "n.ID" => "id_let",
            $script_transl[1] => "write_date",
            $script_transl[2] => "numero",
            $script_transl[3] => "tipo",
            $script_transl[4] => "ragso1",
            $script_transl[5] => "oggetto",
            ucwords(strtolower($script_transl['print'])) => "",
			'Mail'=>'',
            $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_mov);
$linkHeaders -> output();
$recordnav = new recordnav($table, $where, $limit, $passo);
$recordnav -> output();
while ($a_row = gaz_dbi_fetch_array($result)) {
    echo "<tr>\n";
    echo "<td class=\"FacetDataTD\" align=\"right\"><a href=\"admin_letter.php?id_let=".$a_row["id_let"]."&Update\" title=\"".ucfirst($script_transl['update'])."!\">".$a_row["id_let"]."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["write_date"]." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["numero"]."</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["tipo"]."</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row['ragso1']." ".$a_row['ragso2']."</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\">".$a_row["oggetto"]." &nbsp;</td>\n";
    echo "<td class=\"FacetDataTD\" align=\"center\"> <a href=\"stampa_letter.php?id_let=".$a_row["id_let"]."\"><img src=\"../../library/images/stampa.gif\" alt=\"Stampa\" border=\"0\" /></a></td>";
    // Colonna "Mail"
    echo "<td class=\"FacetDataTD\" align=\"center\">";
    if (!empty($a_row["e_mail"])) {
        echo '<a onclick="confirMail(this);return false;" id="doc'.$a_row["id_let"].'" url="stampa_letter.php?id_let='.$a_row["id_let"].'&dest=E" href="#" title="mailto: '.$a_row["e_mail"].'"
        mail="'.$a_row["e_mail"].'" namedoc="Lettera n.'.$a_row["numero"].' del '.gaz_format_date($a_row["write_date"]).'"><img src="../../library/images/email.gif" border="0"></a>';
    }  
    echo "</td>";
    echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_letter.php?id_let=".$a_row["id_let"]."\"><img src=\"../../library/images/x.gif\" title=\"".$script_transl['delete']."!\" border=\"0\"></a></td>\n";
    echo "</tr>\n";
}
?>
</table>
</body>
</html>