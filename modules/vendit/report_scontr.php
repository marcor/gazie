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
   gaz_set_time_limit (0);
   $passo = 100000;
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new GAzieForm();
echo "<form method=\"GET\" name=\"report\">\n";
echo "<input type=\"hidden\" name=\"hidden_req\">\n";
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
<td class="FacetFieldCaptionTD" colspan="2">
</td>
<td class="FacetFieldCaptionTD" colspan="8">
<input type="submit" class="btn btn-default btn-xs" name="all" value="<?php echo $script_transl['vall']; ?>" onClick="javascript:document.report.all.value=1;">
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
            $script_transl['amount'] => "",
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
$tot=0;
while ($row = gaz_dbi_fetch_array($result)) {
        $cast_vat=array();
        $cast_acc=array();
        $tot_tes=0;
        //recupero i dati righi per creare i castelletti
        $rs_rig = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = ".$row['id_tes'],"id_rig");
        while ($v = gaz_dbi_fetch_array($rs_rig)) {
              if ($v['tiprig'] <= 1) {    //ma solo se del tipo normale o forfait
                 if ($v['tiprig'] == 0) { // tipo normale
                    $tot_row = CalcolaImportoRigo($v['quanti'], $v['prelis'],array($v['sconto'],$row['sconto'],-$v['pervat']));
                 } else {                 // tipo forfait
                    $tot_row = CalcolaImportoRigo(1,$v['prelis'],-$v['pervat']);
                 }
                 if (!isset($cast_vat[$v['codvat']])) {
                    $cast_vat[$v['codvat']]['totale']=0.00;
                    $cast_vat[$v['codvat']]['imponi']=0.00;
                    $cast_vat[$v['codvat']]['impost']=0.00;
                    $cast_vat[$v['codvat']]['periva']=$v['pervat'];
                 }
                 $cast_vat[$v['codvat']]['totale']+=$tot_row;
                 // calcolo il totale del rigo stornato dell'iva
                 $imprig=round($tot_row/(1+($v['pervat']/100)),2);
                 $cast_vat[$v['codvat']]['imponi']+=$imprig;
                 $cast_vat[$v['codvat']]['impost']+=$tot_row-$imprig;
                 $tot+=$tot_row;
                 $tot_tes+=$tot_row;
                 // inizio AVERE
                 if (!isset($cast_acc[$admin_aziend['ivacor']]['A'])) {
                     $cast_acc[$admin_aziend['ivacor']]['A']=0;
                 }
                 $cast_acc[$admin_aziend['ivacor']]['A']+=$tot_row-$imprig;
                 if (!isset($cast_acc[$v['codric']]['A'])) {
                     $cast_acc[$v['codric']]['A']=0;
                 }
                 $cast_acc[$v['codric']]['A']+=$imprig;
                 // inizio DARE
                 if ($row['clfoco']>100000000) { // c'è un cliente selezionato
                     if (!isset($cast_acc[$row['clfoco']]['D'])) {
                         $cast_acc[$row['clfoco']]['D']=0;
                     }
                     $cast_acc[$row['clfoco']]['D']+=$tot_row;
                 } else {  // il cliente è anonimo lo passo direttamente per cassa
                     if (!isset($cast_acc[$admin_aziend['cassa_']]['D'])) {
                         $cast_acc[$admin_aziend['cassa_']]['D']=0;
                     }
                     $cast_acc[$admin_aziend['cassa_']]['D']+=$tot_row;
                 }
              }
        }
        $doc['all'][]= array('tes'=>$row,
                                 'vat'=>$cast_vat,
                                 'acc'=>$cast_acc,
                                 'tot'=>$tot_tes);
        if ($row['clfoco']>100000000) {
              $doc['invoice'][]= array('tes'=>$row,
                                       'vat'=>$cast_vat,
                                       'acc'=>$cast_acc,
                                       'tot'=>$tot_tes);
        } else {
              $doc['ticket'][]= array('tes'=>$row,
                                      'vat'=>$cast_vat,
                                      'acc'=>$cast_acc,
                                      'tot'=>$tot_tes);
        }
        // ************* FINE CREAZIONE TOTALI SCONTRINO ***************
        if ($row['id_con']>0){
           $status=$script_transl['status_value'][1];
        } else {
           $status=$script_transl['status_value'][0];
        }
        if ($row['numfat']>0) {
           $cliente = $anagrafica->getPartner($row['clfoco']);
           $invoice="<a href=\"stampa_docven.php?id_tes=".$row['id_tes']."&template=FatturaAllegata\" class=\"btn btn-xs btn-default\" title=\"Stampa\">n.".$row['numfat']." del ".gaz_format_date($row['datfat']).' a '.$cliente['ragso1']."&nbsp;<i class=\"glyphicon glyphicon-print\"></i></a>\n";
        } else {
           $invoice='';
        }
        
        echo "<tr>";
        // Colonna ID scontrino
		echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-edit\" href=\"admin_scontr.php?Update&id_tes=".$row['id_tes']."\"><i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".$row["id_tes"]."</a></td>";
        // Colonna data emissione
		echo "<td class=\"FacetDataTD\" align=\"center\">".gaz_format_date($row['datemi'])."</td>";
		// Colonna numero documento
        echo "<td class=\"FacetDataTD\" align=\"center\">".$row["numdoc"]." &nbsp;</td>";
		// Colonna fattura
        echo "<td class=\"FacetDataTD\" align=\"center\">$invoice</td>";
        echo "<td class=\"FacetDataTD\" align=\"center\">".$status." &nbsp;</td>";
        echo '<td class="FacetDataTD" align="right" style="font-weight=bolt;">';
        echo gaz_format_number($tot_tes);
        echo "\t </td>\n";
        // Colonna Elimina
		if ($row["id_con"] == 0) {
           if (getLastId($row['datemi'],$row['seziva']) == $row["id_tes"]) {
               echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_docven.php?id_tes=".$row['id_tes']."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
           } else {
               echo "<td class=\"FacetDataTD\" align=\"center\"><button class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button></td>";
           }
        } else {
           echo "<td class=\"FacetDataTD\" align=\"center\"><button class=\"btn btn-xs btn-default btn-elimina disabled\"><i class=\"glyphicon glyphicon-remove\"></i></button></td>";
        }
		// Colonna invia a ECR
        echo "<td class=\"FacetDataTD\" align=\"center\"><a class=\"btn btn-xs btn-primary btn-ecr\" href=\"resend_to_ecr.php?id_tes=".$row['id_tes']."\" >".$script_transl['send']."</a>";
        echo "</tr>\n";
}
?>
</form>
</table>
</div><!-- chiude div container role main --></body>
</html>