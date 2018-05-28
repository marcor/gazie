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
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");

$admin_aziend = checkAdmin();


if (isset($_POST['Delete'])) {
    $upd_mm = new magazzForm;
    $form = gaz_dbi_get_row($gTables['movmag'], 'id_mov', intval($_POST['id_mov']));$id_mov=$form['id_mov'];$clfoco=$form['clfoco'];// Antonio Germani questo è il numero dell'id_mov che cancellerò >> $form['id_mov'] e il numero del campo di coltivazione eventualmente associato >> $form['clfoco']
	
	echo "ID movimento: ",$id_mov,"  Codice campo: ",$clfoco," <br>";
	if ($clfoco>0) {
	$form2 = gaz_dbi_get_row($gTables['campi'], 'codice', intval($clfoco));
	if (intval($form2['id_mov'])==intval($id_mov)){
		echo "da azzerare",$form2['id_mov'],$form2['giorno_deca'],$form2['cod_prod_us'],"<br>";
// prendo tutti i movimenti di magazzino che hanno interessato il campo di coltivazione
$n=0;$array=array();
		$query="SELECT ".'*'." FROM ".$gTables['movmag']. " WHERE clfoco ='". $clfoco."'";
		$result = gaz_dbi_query($query);
		while($row = $result->fetch_assoc()) {
// cerco i giorni di sospensione del prodotto interessato ad ogni movimento
			$n=$n+1;$artico= $row['artico'];
			$form3 =gaz_dbi_get_row($gTables['artico'], 'codice', $artico);
			$temp_sosp = $form3['peso_specifico'];echo " tempo sospensione: ",$temp_sosp;
// creo un array con tempo di sospensione + codice articolo + movimento magazzino
					$temp_deca=(intval($temp_sosp)*86400)+strtotime($row["datdoc"]);
				$array[$n]= array('temp_deca'=>$temp_deca,'datdoc'=>$row["datdoc"],'artico'=>$artico, 'id_mov'=>$row["id_mov"]);
			
// ordino l'array per tempo di sospensione
		
        echo $n," id: ". $row["id_mov"]. " - Name: ". $row["datdoc"]. " " . $row["artico"] . "<br>";
		}
		echo "----------- Array non ordinato <br>";
		print_r ($array);
		echo "<br> ----------------- Array ordinato <br>";
		rsort ($array);
		print_r ($array);
		
			if (isset ($array[1]['temp_deca'])) {	$dt=date('Y/m/d', $array[1]['temp_deca']);
		// aggiorno la tabella del campo di coltivazione
			$query="UPDATE " . $gTables['campi'] . " SET giorno_deca = '" . $dt .  "' , cod_prod_us = '"  .$array[1]['artico']. "' , id_mov = '"  .$array[1]['id_mov'].  "' WHERE codice ='". intval($clfoco)."'";
			gaz_dbi_query ($query) ;
			}	
			else {$query="UPDATE " . $gTables['campi'] . " SET giorno_deca = '" . "" .  "' , cod_prod_us = '"  ."". "' , id_mov = '"  ."".  "' WHERE codice ='". intval($clfoco)."'";
			gaz_dbi_query ($query) ;
			}
	}
	}
	
    $upd_mm->uploadMag('DEL', $form['tipdoc'], '', '', '', '', '', '', '', '', '', '', $form['id_mov'], $admin_aziend['stock_eval_method']);
    if ($form['id_rif'] > 0) {  //se il movimento di magazzino � stato generato da un rigo di documento lo azzero
        gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $form['id_rif'], 'id_mag', 0);
    }
	
    header("Location: report_movmag.php");
    exit;
} else {
    $form = gaz_dbi_get_row($gTables['movmag'], 'id_mov', $_GET['id_mov']);
    $causal = gaz_dbi_get_row($gTables['caumag'], 'codice', $form['caumag']);
}

if (isset($_POST['Return'])) {
    header("Location: report_movmag.php");
    exit;
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, 0, 'admin_movmag');
print "<form method=\"POST\">\n";
echo "<input type=\"hidden\" value=\"" . $form['id_mov'] . "\" name=\"id_mov\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['del_this'] . "</div>\n";
print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
$anagrafica = new Anagrafica();
$a_part = $anagrafica->getPartner($form['clfoco']);
$partner = $a_part['ragso1'] . " " . $a_part['ragso2'];
print "<tr><td class=\"FacetFieldCaptionTD\">n. ID </td><td class=\"FacetDataTD\">" . $form["id_mov"] . "</td></tr>";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[1] . "</td><td class=\"FacetDataTD\">" . $form["datreg"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[2] . "</td><td class=\"FacetDataTD\">" . $causal["descri"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl["operat"] . "</td><td class=\"FacetDataTD\">" . $script_transl["operat_value"][$form["operat"]] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl["partner"] . "</td><td class=\"FacetDataTD\">" . $partner . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[8] . "</td><td class=\"FacetDataTD\">" . $form["datdoc"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">" . $form["artico"] . "</td></tr>\n";
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[12] . "</td><td class=\"FacetDataTD\">" . $form["quanti"] . "</td></tr>\n";


print "<td colspan=\"2\" align=\"right\"><input type=\"submit\" name=\"Return\" value=\"" . $script_transl['return'] . "\"><input type=\"submit\" name=\"Delete\" value=\"" . strtoupper($script_transl['delete']) . "!\"></td></tr>";
?>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>