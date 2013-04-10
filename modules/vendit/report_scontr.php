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
// se l'utente non ha alcun registratore di cassa associato nella tabella cash_register non può emettere scontrini
$ecr_user = gaz_dbi_get_row($gTables['cash_register'],'adminid',$admin_aziend['Login']);
if (!$ecr_user){
    header("Location: error_msg.php?ref=admin_scontr");
    exit;
};



function getLastId($date,$seziva)
{
    global $gTables;
    // ricavo l'ultimo id del giorno
    $rs_last = gaz_dbi_dyn_query("id_tes", $gTables['tesdoc'], "tipdoc = 'VCO' AND datemi = '".$date."' AND seziva = ".intval($seziva),'numdoc DESC',0,1);
    $last = gaz_dbi_fetch_array($rs_last);
    $id = 0;
    if ($last) {
       $id = $last['id_tes'];
    }
    return $id;
}

$gForm = new venditForm();
$ecr=$gForm->getECR_userData($admin_aziend['Login']);
$where = "tipdoc = 'VCO' AND seziva = ".$ecr['seziva'];
if (isset($_GET['all'])) {
   $passo = 100000;
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new GAzieForm();
echo "<form method=\"GET\" name=\"report\">\n";
echo "<input type=\"hidden\" name=\"hidden_req\">\n";
echo "<table align=\"center\" width=\"70%\">\n";
echo "<tr class=\"FacetFormHeaderFont\">\n";
echo "<td><a href=\"admin_scontr.php?Insert\">".$script_transl['link1']."</a>
      </td>\n";
echo "<td><a href=\"close_ecr.php\">".$script_transl['link2']."</a>
      </td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title'].$script_transl['seziva'];
echo $ecr['seziva'];
echo "</div>\n";
if (!isset($_GET['field']) || $_GET['field'] == 2 || empty($_GET['field'])) {
   $orderby = "datemi DESC, id_con ASC, numdoc DESC";
}
$recordnav = new recordnav($gTables['tesdoc'], $where, $limit, $passo);
$recordnav->output();
?>
<table class="Tlarge">
<tr>
<td colspan="2">
</td>
<td>
<input type="submit" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value=1;">
</td>
</tr>
<tr>
<?php
// creo l'array (header => campi) per l'ordinamento dei record
$headers_tesdoc = array  (
            $script_transl['id'] => "id_tes",
            $script_transl['date'] => "datemi",
            $script_transl['number'] => "numdoc",
            $script_transl['invoice'] => "clfoco",
            $script_transl['status'] => "",
            $script_transl['delete'] => "",
            '' => ""
            );
$linkHeaders = new linkHeaders($headers_tesdoc);
$linkHeaders -> output();
?>
</tr>
<?php
//recupero le testate in base alle scelte impostate
$result = gaz_dbi_dyn_query('*',$gTables['tesdoc'], $where, $orderby,$limit, $passo);
$anagrafica = new Anagrafica();
while ($row = gaz_dbi_fetch_array($result)) {
        if ($row['id_con']>0){
           $status=$script_transl['status_value'][1];
        } else {
           $status=$script_transl['status_value'][0];
        }
        if ($row['numfat']>0) {
           $cliente = $anagrafica->getPartner($row['clfoco']);
           $invoice="<a href=\"stampa_docven.php?id_tes=".$row['id_tes']."&template=FatturaAllegata\">n.".$row['numfat']." del ".gaz_format_date($row['datfat']).' a '.$cliente['ragso1']."&nbsp;<img src=\"../../library/images/stampa.gif\" border=\"0\"></a>\n";
        } else {
           $invoice='';
        }
        echo "<tr>";
        echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"admin_scontr.php?Update&id_tes=".$row['id_tes']."\">".$row["id_tes"]."</a></td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($row['datemi'])."</td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">".$row["numdoc"]." &nbsp;</td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">$invoice</td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">".$status." &nbsp;</td>";
        if ($row["id_con"] == 0) {
           if (getLastId($row['datemi'],$row['seziva']) == $row["id_tes"]) {
               echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"delete_docven.php?id_tes=".$row['id_tes']."\"><img src=\"../../library/images/x.gif\" border=\"0\"></a></td>";
           } else {
               echo "<td class=\"FacetDataTD\"></td>";
           }
        } else {
           echo "<td class=\"FacetDataTD\"></td>";
        }
        echo "<td class=\"FacetDataTD\" align=\"center\"><a href=\"resend_to_ecr.php?id_tes=".$row['id_tes']."\" >".$script_transl['send']."</a>";
        echo "</tr>\n";
}
?>
</form>
</table>
</body>
</html>