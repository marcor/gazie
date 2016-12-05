<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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

$admin_aziend=checkAdmin();
if(!isset($_GET["annfin"])) {
            $annfin = date("Y");
} else {
            $annfin = $_GET["annfin"];
}
if(!isset($_GET["annini"])) {
            $annini = date("Y")-5;
} else {
            $annini = $_GET["annini"];
}

$message = "";
if (isset($_GET['stampa']) and $message == "") {
        //Mando in stampa i movimenti contabili generati
        $locazione = "Location: stampa_liscre.php?annini=".$annini."&annfin=".$annfin;
        header($locazione);
        exit;
}
if (isset($_GET['Return'])) {
        header("Location:docume_vendit.php");
        exit;
}

// garvin: Measure query time. TODO-Item http://sourceforge.net/tracker/index.php?func=detail&aid=571934&group_id=23067&atid=377411
list($usec, $sec) = explode(' ',microtime());
$querytime_before = ((float)$usec + (float)$sec);
$sqlquery= "SELECT COUNT(DISTINCT ".$gTables['rigmoc'].".id_tes) as nummov,codcon, ragso1, e_mail, telefo,".$gTables['clfoco'].".codice, sum(import*(darave='D')) as dare,sum(import*(darave='A')) as avere, sum(import*(darave='D') - import*(darave='A')) as saldo, darave FROM ".$gTables['rigmoc']." LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['rigmoc'].".codcon = ".$gTables['clfoco'].".codice LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra WHERE datreg between ".$annini."0101 and ".$annfin."1231 and codcon like '".$admin_aziend['mascli']."%' and caucon <> 'CHI' and caucon <> 'APE' or (caucon = 'APE' and codcon like '".$admin_aziend['mascli']."%' and datreg like '".$annini."%') GROUP BY codcon ORDER BY ragso1, darave";
$rs_castel = gaz_dbi_query($sqlquery);
list($usec, $sec) = explode(' ',microtime());
$querytime_after = ((float)$usec + (float)$sec);
$querytime = $querytime_after - $querytime_before;
require("../../library/include/header.php");
$script_transl=HeadMain(0,array(/** ENRICO FEDELE */
								  /*'jquery/jquery-1.7.1.min',
                                  'jquery/ui/jquery.ui.core',
                                  'jquery/ui/jquery.ui.widget',
                                  'jquery/ui/jquery.ui.mouse',
                                  'jquery/ui/jquery.ui.button',
                                  'jquery/ui/jquery.ui.dialog',
                                  'jquery/ui/jquery.ui.position',
                                  'jquery/ui/jquery.ui.draggable',
                                  'jquery/ui/jquery.ui.resizable',
                                  'jquery/ui/jquery.effects.core',
                                  'jquery/ui/jquery.effects.scale',*/
								  /** ENRICO FEDELE */
                                  'custom/modal_form'));

?><script>

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
                      " <?php echo $script_transl['submit']; ?> ": function() {
                         window.location.href = targetUrl;
                      },
                      " <?php echo $script_transl['cancel']; ?> ": function() {
                        $(this).dialog("close");
                      }
                  }
         });
   $("#dialog" ).dialog( "open" );
}
</script>
<div id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
      <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
      <p class="ui-state-highlight" id="mail_adrs"></p>
      <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
      <p class="ui-state-highlight" id="mail_attc"></p>
</div>
<form method="GET">
<div align="center" class="FacetFormHeaderFont">Crediti verso Clienti</div>
<table class="FacetFormTABLE" align="center">
<?php
if (! $message == "") {
    echo "<tr><td colspan=\"2\" class=\"FacetDataTDred\">".$message."</td></tr>";
}
?>
<tr>
<td class="FacetFieldCaptionTD">Anno inizio &nbsp;</td>
<td class="FacetDataTD">
	<!--// select del anno-->
	<select name="annini" class="FacetSelect" onchange="this.form.submit()">
<?php
for( $counter = date("Y")-10 ; $counter <= date("Y")+2; $counter++ ) {
    $selected = "";
    if($counter == $annini)
            $selected = "selected";
    echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
?>
	</select>
</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Anno fine &nbsp;</td>
<td class="FacetDataTD">
<?php
// select del anno
echo "\t <select name=\"annfin\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter = date("Y")-10 ; $counter <= date("Y")+2; $counter++ )
    {
    $selected = "";
    if($counter == $annfin)
            $selected = "selected";
    echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
    }
echo "\t </select>\n";
?>
</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"></td>
<td colspan="3" align="right" nowrap class="FacetFooterTD">
<input type="submit" name="Return" value="Indietro">
<?php
echo "<input type=\"submit\" name=\"stampa\" value=\"STAMPA !\">&nbsp;";
?>
</td>
</tr>
</table>
</form>
<br />
<div class="box-primary table-responsive">
<table class="Tlarge table table-striped table-bordered table-condensed">
<?php
echo '<tr><td colspan=4>La query ha impiegato '.number_format($querytime,4,'.','').' sec.</td></tr><tr>';
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesmov = array  (
          "Codice" => "",
          "Cliente" => "",
          "Telefono" => "",
          "Movimenti" => "",
          "Dare" => "",
          "Avere" => "",
          "Saldo" => "",
          "Riscuoti" => "" ,
          "Estr.Conto" => "",
		  "Mail"=>''
);
$linkHeaders = new linkHeaders($headers_tesmov);
$linkHeaders -> output();
?>
</tr>
<?php
$tot=0;
while ($r = gaz_dbi_fetch_array($rs_castel)) {
      if ($r['saldo'] != 0) {
         echo "<tr>";
         echo "<td class=\"FacetDataTD\">".$r['codcon']."&nbsp;</td>";
         echo "<td class=\"FacetDataTD\"><a title=\"Dettagli cliente\" href=\"report_client.php?auxil=".$r["ragso1"]."&search=Cerca\">".$r["ragso1"]."</a> &nbsp;</td>";
         echo "<td class=\"FacetDataTD\">".$r['telefo']." &nbsp;</td>";
         echo "<td class=\"FacetDataTD\" align=\"center\">".$r['nummov']." &nbsp;</td>";
         echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($r['dare'])." &nbsp;</td>";
         echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($r['avere'])." &nbsp;</td>";
         echo "<td class=\"FacetDataTD\" align=\"right\">".gaz_format_number($r['saldo'])." &nbsp;</td>";
         echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-pagamento\" title=\"Effettuato un pagamento da ".$r["ragso1"]."\"  href=\"salcon_credit.php?codice=".$r['codcon']."\"><i class=\"glyphicon glyphicon-euro\"></i></a></td>";
         echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default\" title=\"Stampa l'Estratto Conto di {$r['ragso1']}\" href=\"stampa_estcon.php?codice=".$r['codcon']."&annini=".$annini."&annfin=".$annfin."\" target=\"_blank\"><i class=\"glyphicon glyphicon-print\"></i></a></td>";
    // Colonna "Mail"
    echo "<td class=\"FacetDataTD\" align=\"center\">";
    if (!empty($r["e_mail"])) {
        echo '<a class="btn btn-xs btn-default" onclick="confirMail(this);return false;" id="doc'.$r["codcon"].'" url="stampa_estcon.php?codice='.$r["codcon"].'&annini='.$annini.'&annfin='.$annfin.'&dest=E" href="#" title="mailto: '.$r["e_mail"].'"
        mail="'.$r["e_mail"].'" namedoc="Estratto conto '.$annini.'-'.$annfin.'"><i class="glyphicon glyphicon-envelope"></i></a>';
    } else {
		echo '<a title="Non hai memorizzato l\'email per questo cliente, inseriscila ora" href="admin_client.php?codice='.substr($r["codice"],3).'&Update"><i class="glyphicon glyphicon-edit"></i></a>';
	} 
    echo "</td>";
         echo "</tr>";
         $tot += $r['saldo'];
      }
}
echo "<tr><td colspan=\"6\"></td><td class='FacetDataTD' style='border: 2px solid #666; text-align: center;'>".gaz_format_number($tot)."</td><td></td><td></td></tr>\n";
?>
</table>
</div>
<?php
require("../../library/include/footer.php");
?>