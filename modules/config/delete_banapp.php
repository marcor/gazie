<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
if (isset($_POST['Delete'])) {
  // eseguo i controlli di integrità, evito la cancellazione nel caso in cui il pagamento sia stato utilizzato sui documenti fiscali
  $nt=0;
  $c=intval($_GET['codice']);
  $rs=gaz_dbi_query("SELECT COUNT(*) AS nu FROM ".$gTables['tesdoc']." WHERE banapp=".$c);
  $r=gaz_dbi_fetch_array($rs);
  $nt += ($r['nu'] >= 1)? $r['nu'] : 0;
  if ($nt >= 1) {
    $message= "Errore: stai tentando di eliminare una banca d'appoggio usata ".$nt." volte sui documenti fiscali di vendita e/o acquisti.";  
    $form = gaz_dbi_get_row($gTables['banapp'], "codice", $c);
  }else{
    gaz_dbi_del_row($gTables['banapp'], "codice", $c);
    header("Location: report_banapp.php");
    exit;
  }
}else{
    $form = gaz_dbi_get_row($gTables['banapp'], "codice",$c);
}
if (isset($_POST['Return'])) {
        header("Location: report_banapp.php");
        exit;
}
require("../../library/include/header.php");
$script_transl=HeadMain('','','admin_banapp');
?>
<form method="POST">
<div class="FacetFormHeaderFont text-center"><?php echo $script_transl['warning'].'!!! '.$script_transl['delete'].$script_transl['del_this'].' n.'.intval($_GET['codice']); ?> </div>
<div class="text-center"><b class="text-danger"><?php echo $message; ?></b> </div>
<table class="GazFormDeleteTable">
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['descri']; ?></td>
    <td class="FacetDataTD"><?php print $form["descri"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['codabi']; ?></td>
    <td class="FacetDataTD"><?php print $form["codabi"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['codcab']; ?></td>
    <td class="FacetDataTD"><?php print $form["codcab"] ?>&nbsp;</td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['locali']; ?></td>
    <td class="FacetDataTD"><?php print $form["locali"] ?>&nbsp;</td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['codpro']; ?></td>
    <td class="FacetDataTD"><?php print $form["codpro"] ?>&nbsp;</td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl['annota']; ?></td>
    <td class="FacetDataTD"><?php print $form["annota"] ?>&nbsp;</td>
  </tr>
<tr>
    <td align="right">
<?php
echo '<input type="submit" accesskey="r" name="Return" value="'.$script_transl['return'].'"></td><td>
     '.ucfirst($script_transl['safe']);
echo ' <input type="submit" accesskey="d" name="Delete" value="'.$script_transl['delete'].'">';
?>
</td>
</tr>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>