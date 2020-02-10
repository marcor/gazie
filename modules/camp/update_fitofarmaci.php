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
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
$esiste=0;$msg="";

function utf8_converter($array)
{
    array_walk_recursive($array, function(&$item, $key){
        if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
        }
    });

    return $array;
}

$admin_aziend=checkAdmin(); $title="Update tabella fitofarmaci dal database del Ministero della Salute";

require("../../library/include/header.php");
 $script_transl=HeadMain();

require("../../library/include/footer.php");

if (isset($_POST['update'])) {

// creo l'array dal file csv
$array = array();$delimiter = ";"; 
$lines = @file('http://www.dati.salute.gov.it/imgs/C_17_dataset_6_download_itemDownload0_upFile.CSV', FILE_IGNORE_NEW_LINES) or die ("Apertura del file fallita. Aspettare 1 minuto e riprovare. L'errore è: '$php_errormsg'");
//$lines = file('fitofarmaci.CSV', FILE_IGNORE_NEW_LINES); // commentare la riga sopra e togliere il commento a questa se si desidera prelevare i dati da un file scaricato precedentemente nel PC
foreach ($lines as $key => $value)
{
    $array[$key] = str_getcsv($value,$delimiter);
}
$array=utf8_converter($array);

// CONTROLLO QUANDO è StATO FATTO L'ULTIMO AGGIORNAMENTO
$query="SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_SCHEMA = '".$Database."' AND TABLE_NAME = '".$gTables['camp_fitofarmaci']."'";
$result = gaz_dbi_query($query);
while ($row = $result->fetch_assoc()) {
			 $update=strtotime($row['UPDATE_TIME']);
			}
$today=	strtotime(date("Y-m-d H:i:s",time()));
// 1 giorno è 24*60*60=86400 - 30 giorni 30*86400=2592000
		
If (intval($update)+2592000<$today){
	$msg=$msg."La tabella non era stata aggiornata da oltre 30 giorni.<br>";
}	

//svuoto la tabella per evitare di lasciare prodotti non più presenti - non so ancora bene come il Ministero aggiorni il suo database	
$query="TRUNCATE TABLE ".$gTables['camp_fitofarmaci']; 	
$result = gaz_dbi_query($query);

//riscrivo la tabella aggiornata
$dim_array=count($array);
	for ($i=1; $i<$dim_array; $i++){
	$query="INSERT INTO ".$gTables['camp_fitofarmaci']." ("."NUMERO_REGISTRAZIONE".", "."PRODOTTO".", "."IMPRESA".", "."SEDE_LEGALE_IMPRESA".", "."SCADENZA_AUTORIZZAZIONE".", "."INDICAZIONI_DI_PERICOLO".", "."DESCRIZIONE_FORMULAZIONE".", "."SOSTANZE_ATTIVE".") VALUES ('".$array[$i][0]."', '".substr(str_replace("'","^",$array[$i][1]),0,40)."', '".substr(str_replace("'","^",$array[$i][2]),0,30)."', '".substr(str_replace("'","^",$array[$i][3]),0,20)." ".substr(str_replace("'","^",$array[$i][5]),0,9)."', '".substr($array[$i][12],0,12)."', '".substr(str_replace("'","^",$array[$i][13]),0,45)."', '".substr(str_replace("'","^",$array[$i][16]),0,30)."', '".substr(str_replace("'","^",$array[$i][17]),0,30)."' ) ON DUPLICATE KEY UPDATE "."NUMERO_REGISTRAZIONE"."="."NUMERO_REGISTRAZIONE";	
	$result = gaz_dbi_query($query); 
	}$msg=$msg."Ho aggiornato la nuova tabella con i dati prelevati oggi dal Ministero della salute.<br>";
	
}        
echo "<form method=\"POST\" name=\"myform\">";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">$title</div>\n";

echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";

echo '<tr><td colspan="5" class="FacetDataTDred" align="center">' . "Procedura di aggiornamento della tabella fitofarmaci" . "</td></tr>\n";
echo "<tr><td class=\"FacetDataTD\">\n";
echo '<button type="button" onclick="location.href=\'report_artico.php\'" class="btn btn-default btn-sm" name="ritorno" title="' . "Indietro" . '!"><i class="glyphicon glyphicon-backward"></i></button></td>';
echo "<td class=\"FacetFieldCaptionTD\">" ."Questa procedura popola la tabella fitofarmaci, se è la prima volta che viene attivata; se non è la prima volta la aggiorna.<br> Può durare alcuni minuti e necessita di connessione ad internet. <br> Non cambiare pagina al browser finché non si riceve un messaggio di avvenuto aggiornamento o di errore." . "</td><td class=\"FacetDataTD\">\n";
echo '<button type="submit" class="btn btn-default btn-sm" name="update" title="' . $script_transl['submit'] . '"><i class="glyphicon glyphicon-refresh"></i></button></td></tr>';
echo '<tr><td></td><td class=\"FacetDataTD\">'.$msg.'</td><td></td></tr>';

?>
</div>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>