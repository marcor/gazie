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
$message = "";
$anno = date("Y");
if (isset($_GET['auxil'])) {
   $auxil = $_GET['auxil'];
} else {
   $auxil = 1;
}
$where = " (tipdoc = 'FAD' or tipdoc like 'DD_') and seziva = '$auxil'";
$documento ='';
$cliente='';
if (isset($_GET['numdoc'])) {
   if ($_GET['numdoc'] > 0) {
      $documento = $_GET['numdoc'];
      $where = " (tipdoc = 'FAD' or tipdoc like 'DD_') and seziva = '$auxil' and numdoc = '$documento'";
      $passo = 1;
      $auxil = $_GET['auxil']."&numdoc=".$documento;
   }
}
if (isset($_GET['cliente'])) {
   if ($_GET['cliente'] <> '') {
      $cliente = $_GET['cliente'];
      $where = " (tipdoc = 'FAD' or tipdoc like 'DD_') and seziva = '$auxil' and descri like '%$cliente%'";
      $passo = 50;
      $auxil = $_GET['auxil']."&cliente=".$cliente;
	  unset($documento);
   }
}
if (isset($_GET['all'])) {
   gaz_set_time_limit (0);
   $auxil = $_GET['auxil']."&all=yes";
   $passo = 100000;
   $where = " (tipdoc = 'FAD' or tipdoc like 'DD_') and seziva = '$auxil'";
   unset($documento);
   unset($cliente);
}

$titolo="Documenti di trasporto";
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
<form method="GET">

<div id="dialog" title="<?php echo $script_transl['mail_alert0']; ?>">
      <p id="mail_alert1"><?php echo $script_transl['mail_alert1']; ?></p>
      <p class="ui-state-highlight" id="mail_adrs"></p>
      <p id="mail_alert2"><?php echo $script_transl['mail_alert2']; ?></p>
      <p class="ui-state-highlight" id="mail_attc"></p>
</div>

<div align="center" class="FacetFormHeaderFont"> D.d.T. della sezione
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
        $orderby = "datemi desc, numdoc desc";
$recordnav = new recordnav($gTables['tesdoc'].' LEFT JOIN '.$gTables['clfoco'].' on '.$gTables['tesdoc'].'.clfoco = '.$gTables['clfoco'].'.codice', $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge">
	<tr>
		<td class="FacetFieldCaptionTD">
			<input placeholder="Cerca Numero" class="input-xs form-control" type="text" name="numdoc" value="<?php if (isset($documento) && $documento > 0) print $documento; ?>" maxlength="6" size="3" tabindex="1" class="FacetInput">
		</td>
		<td>
		</td>
		<td colspan="1" class="FacetFieldCaptionTD">
			<input placeholder="Cerca Cliente" class="input-xs form-control" type="text" name="cliente" value="<?php if ($cliente <> '') print $cliente; ?>" maxlength="40" size="30" tabindex="2" class="FacetInput">
		</td>
		<td>
			<input class="btn btn-xs btn-default" type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
		</td>
		<td colspan="3">
			<input class="btn btn-xs btn-default" type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
		</td>
	</tr>

<tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
              "Numero" => "numdoc",
              "Data" => "datemi",
              "Cliente" => "ragso1",
              "Status" => "",
              "Stampa" => "",
              "Mail" => "",
              "Origine" => "",
              "Cancella" => ""
              );
$urlPrintDoc = "";
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
$rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'].' LEFT JOIN '.$gTables['clfoco'].' on '.$gTables['tesdoc'].'.clfoco = '.$gTables['clfoco'].'.codice', $where,"datemi desc, numdoc desc",0,1);
$ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
if ($ultimo_documento)
    $ultimoddt = $ultimo_documento['numdoc'];
else
    $ultimoddt = 1;
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query($gTables['tesdoc'].".*,".$gTables['anagra'].".ragso1,".$gTables['clfoco'].".codice,".$gTables['anagra'].".e_mail", $gTables['tesdoc']."
                            LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['tesdoc'].".clfoco = ".$gTables['clfoco'].".codice
                            LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra",
                            $where, $orderby,$limit, $passo);
while ($r = gaz_dbi_fetch_array($result)) {
    switch($r['tipdoc']) {
        case "DDT":
            echo "<tr>";
			// Colonna protocollo
            echo "<td class=\"FacetDataTD\" align=\"left\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"admin_docven.php?Update&id_tes=".$r["id_tes"]."\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$r["numdoc"]."</a> &nbsp;</td>";
			// Colonna data emissione
            echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
            // Colonna Cliente
				?>
				<td class="FacetDataTD">
					<a href="report_client.php?auxil=<?php echo $r["ragso1"]; ?>&search=Cerca">
					<?php echo $r["ragso1"]; ?>
					</a>
				</td>
				<?php
            // Colonna numero fattura
	    if ($r['numfat'] > 0) {
                echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default\" title=\"stampa la fattura differita n. ".$r["numfat"]."\" href=\"stampa_docven.php?td=2&si=".$r["seziva"]."&pi=".$r['protoc']."&pf=".$r['protoc']."&di=".$r['datfat']."&df=".$r['datfat']."\"><i class=\"glyphicon glyphicon-print\"></i> fatt. n. ".$r["numfat"]."</a></td>";
                if ($r["id_con"] > 0) {
                    echo "<a title=\"visualizza la registrazione contabile della fattura differita\" href=\"../contab/admin_movcon.php?id_tes=".$r["id_con"]."&Update\">cont. n.".$r["id_con"]."</a>";
                }
            } else {
                echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-success\" title=\"fattuazione da d.d.t.\" href=\"emissi_fatdif.php\">da fatturare</a></td>";
            }

            $urlPrintDoc = "stampa_docven.php?id_tes=".$r["id_tes"]."&template=DDT";
            echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"$urlPrintDoc\"><i class=\"glyphicon glyphicon-print\"></i></a>";
            echo "</td>\n";

            // Colonna "Mail"
            echo "<td class=\"FacetDataTD\" align=\"center\">";
            if (!empty($r["e_mail"])) {
                echo '<a class="btn btn-xs btn-default btn-mail" onclick="confirMail(this);return false;" id="doc'.$r["id_tes"].'" url="'.$urlPrintDoc.'&dest=E" href="#" title="mailto: '.$r["e_mail"].'"
                mail="'.$r["e_mail"].'" namedoc="'.$r['tipdoc'].' n.'.$r["numdoc"].' del '.gaz_format_date($r["datemi"]).'"><i class="glyphicon glyphicon-envelope"></i></a>';
            } else {
		echo '<a title="Non hai memorizzato l\'email per questo cliente, inseriscila ora" target="_blank" href="admin_client.php?codice='.substr($r["codice"],3).'&Update"><i class="glyphicon glyphicon-edit"></i></a>';
	    }
            echo "</td>\n";

            echo "<td class=\"FacetDataTD\" align=\"center\">";
            $rigbro_result = gaz_dbi_dyn_query ('*',$gTables['rigbro'],"id_doc = ".$r['id_tes']." GROUP BY id_doc",'id_tes');
            while ($rigbro_r = gaz_dbi_fetch_array ($rigbro_result)) {
                $r_d = gaz_dbi_get_row($gTables['tesbro'],"id_tes",$rigbro_r["id_tes"]);
                if ($r_d["id_tes"] > 0) {
                    echo " <a class=\"btn btn-xs btn-default\" title=\"visualizza l'Ordine\" href=\"stampa_ordcli.php?id_tes=".$r_d['id_tes']."\" style=\"font-size:10px;\">Ord.".$r_d['numdoc']."</a>\n";
                }
            }
            echo "</td\n>";
			
            if ($ultimoddt == $r["numdoc"] and $r['numfat'] == 0)
            echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_docven.php?id_tes=".$r["id_tes"]."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
            else
                echo "<td class=\"FacetDataTD\" align=\"center\"><button class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button></td>";
            echo "</tr>\n";
            break;
        case "DDR":
        case "DDL":
            echo "<tr>";
            echo "<td class=\"FacetDataTD\" align=\"left\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"../acquis/admin_docacq.php?Update&id_tes=".$r["id_tes"]."\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$r["numdoc"]."</a> &nbsp;</td>";
            echo "<td class=\"FacetDataTDred\" align=\"center\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
            ?>
				<td class="FacetDataTDred">
					<a href="report_client.php?auxil=<?php echo $r["ragso1"]; ?>&search=Cerca">
					<?php echo $r["ragso1"]; ?>
					</a>
				</td>
				<?php
            echo "<td class=\"FacetDataTDred\" align=\"center\">D.d.T. a Fornitore &nbsp;</td>";

            $urlPrintDoc = "../acquis/stampa_docacq.php?id_tes=".$r["id_tes"]."&template=DDT";
            echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default\" href=\"$urlPrintDoc\"><i class=\"glyphicon glyphicon-print\"></i></a>";
            echo "</td>\n";

            // Colonna "Mail"
            echo "<td class=\"FacetDataTD\" align=\"center\">";
            if (!empty($r["e_mail"])) {
                echo '<a class="btn btn-xs btn-default btn-mail" onclick="confirMail(this);return false;" id="doc'.$r["id_tes"].'" url="'.$urlPrintDoc.'&dest=E" href="#" title="mailto: '.$r["e_mail"].'"
                mail="'.$r["e_mail"].'" namedoc="'.$r['tipdoc'].' n.'.$r["numdoc"].' del '.gaz_format_date($r["datemi"]).'"><i class="glyphicon glyphicon-envelope"></i></a>';
            } else {
		echo '<a title="Non hai memorizzato l\'email per questo fornitore, inseriscila ora" target="_blank" href="../acquis/admin_fornit.php?codice='.substr($r["codice"],3).'&Update"><i class="glyphicon glyphicon-edit"></i></a>';
	    }
            echo "</td>\n";

            echo "<td class=\"FacetDataTD\" align=\"center\"></td>";
            if ($ultimoddt == $r["numdoc"] and $r['numfat'] == 0)
            // Colonna Elimina
			echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_docven.php?id_tes=".$r["id_tes"]."\"><center><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
            else
                echo "<td class=\"FacetDataTD\" align=\"center\"></td>";
            echo "</tr>\n";
            break;
        case "FAD":
            echo "<tr>";
			// Colonna protocollo
            echo "<td class=\"FacetDataTD\" align=\"left\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"admin_docven.php?Update&id_tes=".$r["id_tes"]."\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$r["numdoc"]."</a></td>";
            // Colonna Data emissione
			echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($r["datemi"])." &nbsp;</td>";
            // Colonna Cliente
			?>
			<td class="FacetDataTD">
				<a href="report_client.php?auxil=<?php echo $r["ragso1"]; ?>&search=Cerca">
				<?php echo $r["ragso1"]; ?>
				</a>
			</td>
			<?php
			// Colonna Stato
            echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default\" title=\"stampa la fattura differita n. ".$r["numfat"]."\" href=\"stampa_docven.php?td=2&si=".$r["seziva"]."&pi=".$r['protoc']."&pf=".$r['protoc']."&di=".$r['datfat']."&df=".$r['datfat']."\">Fat ".$r["numfat"]."</a>";
            if ($r["id_con"] > 0) {
                echo "&nbsp;<a class=\"btn btn-xs btn-default btn-registrazione\" title=\"visualizza la registrazione contabile della fattura differita\" href=\"../contab/admin_movcon.php?id_tes=".$r["id_con"]."&Update\">Cont ".$r["id_con"]."</a>";
            }
            echo "</td>";

            $urlPrintDoc = "stampa_docven.php?id_tes=".$r["id_tes"]."&template=DDT";
			// Colonna stampa
            echo "<td class=\"FacetDataTD\" align=\"center\">
            <a class=\"btn btn-xs btn-default\" title=\"stampa il documento di trasporto n. ".$r["numdoc"]."\" href=\"$urlPrintDoc\"><i class=\"glyphicon glyphicon-print\"></i></a>";
            echo "</td>";

         // Colonna "Mail"
            echo "<td class=\"FacetDataTD\" align=\"center\">";
            if (!empty($r["e_mail"])) {
                echo '<a class="btn btn-xs btn-default btn-mail" onclick="confirMail(this);return false;" id="doc'.$r["id_tes"].'" url="'.$urlPrintDoc.'&dest=E" href="#" title="mailto: '.$r["e_mail"].'"
                mail="'.$r["e_mail"].'" namedoc="DDT n.'.$r["numdoc"].' del '.gaz_format_date($r["datemi"]).'"><i class="glyphicon glyphicon-envelope"></i></a>';
            } else {
				echo '<a title="Non hai memorizzato l\'email per questo cliente, inseriscila ora" target="_blank" href="admin_client.php?codice='.substr($r["codice"],3).'&Update"><i class="glyphicon glyphicon-edit"></i></a>';
	    }
            echo "</td>";
				// Colonna
            echo "<td class=\"FacetDataTD\" align=\"center\">";
            $rigbro_result = gaz_dbi_dyn_query ('*',$gTables['rigbro'],"id_doc = ".$r['id_tes']." GROUP BY id_doc",'id_tes');
            while ($rigbro_r = gaz_dbi_fetch_array ($rigbro_result)) {
                $r_d = gaz_dbi_get_row($gTables['tesbro'],"id_tes",$rigbro_r["id_tes"]);
                if ($r_d["id_tes"] > 0) {
                    echo "<a title=\"visualizza l'Ordine\" href=\"stampa_ordcli.php?id_tes=".$r_d['id_tes']."\" style=\"font-size:10px;\">Ord.".$r_d['numdoc']."</a>\n";
                }
            }
            echo "</td>";
            echo "<td class=\"FacetDataTD\"></td>";
            echo "</tr>\n";
            break;
    }
}
?>
</table>
</form>
</body>
</html>