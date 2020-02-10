<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../modules/magazz/lib.function.php");

$upd_mm = new magazzForm;
$docOperat = $upd_mm->getOperators();

$message = "Sei sicuro di voler rimuovere ?";
if (!isset($_POST['ritorno'])) {
        $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

if (!isset($_GET['id_tes'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
} else {
    $form = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", intval($_GET['id_tes']));
}


if (isset($_POST['Delete'])) {
    if  (substr($form['tipdoc'],0,2) == 'DD') {
        $where = "tipdoc LIKE 'DD_' AND seziva = ".$form['seziva']." AND numfat = 0" ;
		$order='numdoc DESC';
    } elseif  (substr($form['tipdoc'],0,2) == 'AF'){ // fattura o nota credito fornitore
        $where = "tipdoc LIKE 'AF_' AND seziva = ".$form['seziva']." AND YEAR(datreg) = '".substr($form['datreg'],0,4)."'";
		$order='protoc DESC';
    } elseif  (substr($form['tipdoc'],0,2) == 'AD'){
        $where = "tipdoc LIKE 'AD_'";
		$order='id_tes DESC';
	}
    $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where,$order,0,1);
    $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
    // ricavo il progressivo annuo, ma se e' il primo documento dell'anno, resetto il contatore
    if (!empty($ultimo_documento) && $ultimo_documento['id_tes']==$form['id_tes']) {
           //allora procedo all'eliminazione della testata e dei righi...
           gaz_dbi_del_row($gTables['tesdoc'], "id_tes", $form['id_tes']);
           gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $form['id_con']);
           gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $form['id_con']);
           gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $form['id_con']);
           $rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '".$form['id_tes']."'","id_tes desc");
           while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
                  gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $a_row['id_rig']);
                  if (intval($a_row['id_mag']) > 0){  //se c'� stato un movimento di magazzino lo azzero
                     $upd_mm->uploadMag('DEL',$form['tipdoc'],'','','','','','','','','','',$a_row['id_mag'],$admin_aziend['stock_eval_method']);
					 // se c'è stato, cancello pure il movimento sian 
					gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", $a_row['id_mag']);
                  }
           }
           header("Location: ".$_POST['ritorno']);
           exit;
    } else {
          $message = "Si st&agrave; tentando di eliminare un documento diverso dall'ultimo emesso".$ultimo_documento['tipdoc'].$ultimo_documento['id_tes'];
    }
}

if (isset($_POST['Return'])) {
    header("Location: report_ddtacq.php");
    exit;
}
$anagrafica = new Anagrafica();
$fornitore = $anagrafica->getPartner($form['clfoco']);
$titolo="Eliminazione Documento d'Acquisto";
require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<form method="POST">
<input type="hidden" name="id_tes" value="<?php print $_GET['id_tes']; ?>">
<input type="hidden" name="ritorno" value="<?php print $_POST['ritorno'];?>">
<?php        
echo '<div class="alert alert-danger text-center" role="alert">' . $script_transl['title'] . '</div>';
?>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
  <!-- BEGIN Error -->
  <tr>
    <td colspan="2" class="FacetDataTD">
    <?php
    if (!empty($message)) {
        print "$message";
    }
    ?>
    </td>
  </tr>
  <!-- END Error -->
  <tr>
  <tr>
    <td class="FacetFieldCaptionTD">ID &nbsp;</td>
    <td class="FacetDataTD"><?php print $form["id_tes"]; ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Fornitore &nbsp;</td>
    <td class="FacetDataTD"><?php print $fornitore["ragso1"]; ?>&nbsp;</td>
  </tr>
<?php if ($form['numfat']>0){ ?>
  <tr>
    <td class="FacetFieldCaptionTD">Numero fattura&nbsp;</td>
    <td class="FacetDataTD"><?php print $form["numfat"]; ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Data fattura &nbsp;</td>
    <td class="FacetDataTD"><?php print gaz_format_date($form["datfat"]); ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Data di Registrazione &nbsp;</td>
    <td class="FacetDataTD"><?php print gaz_format_date($form["datreg"]); ?>&nbsp;</td>
  </tr>
<?php } else { ?>
  <tr>
    <td class="FacetFieldCaptionTD">Numero Documento di Trasporto&nbsp;</td>
    <td class="FacetDataTD"><?php print $form["numfat"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Data di Emissione &nbsp;</td>
    <td class="FacetDataTD"><?php print gaz_format_date($form["datemi"]); ?>&nbsp;</td>
  </tr>
<?php } ?>
    <td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
    <!-- BEGIN Button Return --><input type="submit" name="Return" value="Indietro"><!-- END Button Return -->&nbsp;
    <!-- BEGIN Button Insert --><input type="submit" name="Delete" value="Elimina"><!-- END Button Insert -->&nbsp;
    </td>
  </tr>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>