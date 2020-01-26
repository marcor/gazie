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
// Consultazione database fitofarmaci importato dal Ministero della Salute

require("../../library/include/datlib.inc.php");



$admin_aziend=checkAdmin();
$titolo = 'Campi';
require("../../library/include/header.php");
$script_transl = HeadMain();
$form['nome_fito']="";

print "<form method=\"POST\" enctype=\"multipart/form-data\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">CONSULTAZIONE DATABASE FITOFARMACI</div>";
print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice( explode('+',chop($msg)),0,-1);
    foreach ($rsmsg as $value){
            $message .= $script_transl['error']."! -> ";
            $rsval = explode('-',chop($value));
            foreach ($rsval as $valmsg){
                    $message .= $script_transl[$valmsg]." ";
            }
            $message .= "<br />";
    }
    echo '<tr><td colspan="5" class="FacetDataTDred">'.$message."</td></tr>\n";
}
?>
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql artico	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT prodotto FROM ".$gTables['camp_fitofarmaci'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['prodotto']."\", ";			
	}
	$stringa=substr($stringa,0,-2);
	echo $stringa;
	?>],
		minLength:2,
	select: function(event, ui) {
        //assign value back to the form element
        if(ui.item){
            $(event.target).val(ui.item.value);
        }
        //submit the form
        $(event.target.form).submit();
    }
	});
	});
  </script>
 <!-- fine autocompletamento --> 
<?php
print "<tr><td class=\"FacetFieldCaptionTD\">NOME FITOFARMACO</td><td class=\"FacetDataTD\"><input type=\"text\" id=\"autocomplete\" name=\"nome_fito\" value=\"".$form['nome_fito']."\" maxlength=\"50\" size=\"50\" /></td></tr>\n";

if (isset ($_POST['nome_fito'])) {
	$form['nome_fito']=$_POST['nome_fito'];
	$fito = gaz_dbi_get_row($gTables['camp_fitofarmaci'], 'prodotto', $form['nome_fito']);
	?>
		
	<tr><td colspan="5" class="FacetDataTDred" align="center">
	<?php echo $form['nome_fito']; ?>
	</td></tr>
	<tr>
	<td class="FacetFieldCaptionTD">IMPRESA</td>
	<td class=\"FacetDataTD\">
	<?php echo $fito['IMPRESA']; ?>
	</td>
	</tr>
	<tr>
	<td class="FacetFieldCaptionTD">SEDE LEGALE</td>
	<td class=\"FacetDataTD\">
	<?php echo $fito['SEDE_LEGALE_IMPRESA']; ?>
	</td>
	</tr>
	<tr>
	<td class="FacetFieldCaptionTD">SCADENZA AUTORIZZAZIONE</td>
	<td class=\"FacetDataTD\">
	<?php echo $fito['SCADENZA_AUTORIZZAZIONE']; ?>
	</td>
	</tr>
	<tr>
	<td class="FacetFieldCaptionTD">INDICAZIONI DI PERICOLO</td>
	<td class=\"FacetDataTD\">
	<?php echo $fito['INDICAZIONI_DI_PERICOLO']; ?>
	</td>
	</tr>
	<tr>
	<td class="FacetFieldCaptionTD">DESCRIZIONE FORMULAZIONE</td>
	<td class=\"FacetDataTD\">
	<?php echo $fito['DESCRIZIONE_FORMULAZIONE']; ?>
	</td>
	</tr>
	<tr>
	<td class="FacetFieldCaptionTD">SOSTANZE ATTIVE</td>
	<td class=\"FacetDataTD\">
	<?php echo $fito['SOSTANZE_ATTIVE']; ?>
	</td>
	</tr>
	</table>
	</form>
<?php
}
require("../../library/include/footer.php");
?>