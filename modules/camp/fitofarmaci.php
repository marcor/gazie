<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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

echo "IN COSTRUZIONE";

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

print "<tr><td class=\"FacetFieldCaptionTD\">NOME FITOFARMACO</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"nome_avv\" value=\"".$form['nome_fito']."\" maxlength=\"50\" size=\"50\" /></td></tr>\n";



print "</table>\n";
?>
</form>

<?php
  
require("../../library/include/footer.php");
?>