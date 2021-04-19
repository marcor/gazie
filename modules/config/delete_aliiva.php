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

$titolo="Eliminazione aliquota I.V.A.";
$admin_aziend=checkAdmin();
$message = "Sei sicuro di voler rimuovere ?";
if (isset($_POST['Delete'])) {
  // eseguo i controlli di integrità, evito la cancellazione nel caso in cui l'aliquota IVA sia stata utilizzata sui rigmoi (codiva), rigdoc e rigbro (codvat)
  $nt=0;
  $c=intval($_GET['codice']);
  $rs=gaz_dbi_query("SELECT COUNT(*) AS nu FROM ".$gTables['rigmoi']." WHERE codiva=".$c);
  $r=gaz_dbi_fetch_array($rs);
  $nt += ($r['nu'] >= 1)? $r['nu'] : 0;
  $rs=gaz_dbi_query("SELECT COUNT(*) AS nu FROM ".$gTables['rigdoc']." WHERE codvat=".$c);
  $r=gaz_dbi_fetch_array($rs);
  $nt += ($r['nu'] >= 1)? $r['nu'] : 0;
  $rs=gaz_dbi_query("SELECT COUNT(*) AS nu FROM ".$gTables['rigbro']." WHERE codvat=".$c);
  $r=gaz_dbi_fetch_array($rs);
  $nt += ($r['nu'] >= 1)? $r['nu'] : 0;
  if ($nt >= 1) {
    $message= "Errore: stai tentando di eliminare una aliquota usata ".$nt." volte su una o più tabelle Movimenti IVA, Righi documenti fiscali, righi ordini/preventivi.";  
    $form = gaz_dbi_get_row($gTables['aliiva'], "codice", $c);
  }else{
    $result = gaz_dbi_del_row($gTables['aliiva'], "codice", $c);
    header("Location: report_aliiva.php");
    exit;
  }
} else {
  $form = gaz_dbi_get_row($gTables['aliiva'], "codice", intval($_GET['codice']));
}
if (isset($_POST['Return'])){
        header("Location: report_aliiva.php");
        exit;
}
require("../../library/include/header.php");
$script_transl=HeadMain('','','admin_aliiva');
?>
<form method="POST">
<div class="FacetFormHeaderFont text-center"><?php echo $script_transl['warning'].'!!! '.$script_transl['delete'].$script_transl[0].' ID '.intval($_GET['codice']); ?> </div>
<div class="text-center"><b class="text-danger"><?php echo $message; ?></b> </div>
<table class="GazFormDeleteTable">
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl[3]; ?></td>
    <td class="FacetDataTD"><?php print $form["aliquo"] ?>&nbsp;</td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl[2]; ?></td>
    <td class="FacetDataTD"><?php print $form["descri"] ?>&nbsp;</td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD"><?php echo $script_transl[9]; ?></td>
    <td class="FacetDataTD"><?php print  $script_transl['tipiva'][$form["tipiva"]] ?></td>
  </tr>
 <tr>
    <td class="FacetFieldCaptionTD">Codice esenzione</td>
    <td class="FacetDataTD"><?php print $form["fae_natura"] ?></td>
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