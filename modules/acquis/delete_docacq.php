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
}

if (isset($_POST['Delete'])) {
    $testata = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", $_GET['id_tes']);
    if  (substr($testata['tipdoc'],0,2) == 'DD'){
        $where = "tipdoc LIKE 'DD_' AND seziva = '".$testata['seziva']."' AND numfat = 0" ;
    } elseif  (substr($testata['tipdoc'],0,2) == 'AF'){
        $where = "tipdoc LIKE 'AF_'";
    } elseif  (substr($testata['tipdoc'],0,2) == 'AD'){
        $where = "tipdoc LIKE 'AD_'";
    }
    $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], $where,"id_tes DESC",0,1);
    $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
    // ricavo il progressivo annuo, ma se e' il primo documento dell'anno, resetto il contatore
    if ($ultimo_documento and $ultimo_documento['id_tes'] == $testata['id_tes']) {
           //allora procedo all'eliminazione della testata e dei righi...
           gaz_dbi_del_row($gTables['tesdoc'], "id_tes", $testata['id_tes']);
           gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $testata['id_con']);
           gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $testata['id_con']);
           gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $testata['id_con']);
           $rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '".$testata['id_tes']."'","id_tes desc");
           while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
                  gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $a_row['id_rig']);
                  if (intval($a_row['id_mag']) > 0){  //se c'� stato un movimento di magazzino lo azzero
                     $upd_mm->uploadMag('DEL',$testata['tipdoc'],'','','','','','','','','','',$a_row['id_mag'],$admin_aziend['stock_eval_method']);
                  }
           }
           header("Location: ".$_POST['ritorno']);
           exit;
    } else {
          $message = "Si st&agrave; tentando di eliminare un documento diverso dall'ultimo emesso !".$ultimo_documento['tipdoc'].$ultimo_documento['id_tes'];
    }
}

if (isset($_POST['Return'])) {
    header("Location: report_ddtacq.php");
    exit;
}
$form = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", $_GET['id_tes']);
$anagrafica = new Anagrafica();
$cliente = $anagrafica->getPartner($form['clfoco']);
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
    if (! $message == "") {
        print "$message";
    }
    ?>
    </td>
  </tr>
  <!-- END Error -->
  <tr>
  <tr>
    <td class="FacetFieldCaptionTD">ID del Documento &nbsp;</td>
    <td class="FacetDataTD"><?php print $form["id_tes"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Numero del documento &nbsp;</td>
    <td class="FacetDataTD"><?php print $form["numdoc"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Data di Emissione &nbsp;</td>
    <td class="FacetDataTD"><?php print $form["datemi"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Cliente &nbsp;</td>
    <td class="FacetDataTD"><?php print $cliente["ragso1"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Status &nbsp;</td>
    <td class="FacetDataTD"><?php print $form["status"] ?>&nbsp;</td>
  </tr>
    <td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
    <!-- BEGIN Button Return --><input type="submit" name="Return" value="Indietro"><!-- END Button Return -->&nbsp;
    <!-- BEGIN Button Insert --><input type="submit" name="Delete" value="ELIMINA !"><!-- END Button Insert -->&nbsp;
    </td>
  </tr>
</table>
</form>
</div><!-- chiude div container role main --></body>
</html>