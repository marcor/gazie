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
require ("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();


if (isset($_POST['Delete'])) {
	
    $upd_mm = new magazzForm;
    $form = gaz_dbi_get_row($gTables['movmag'], 'id_mov', intval($_POST['id_mov']));$id_mov=$form['id_mov'];$campo_coltivazione=$form['campo_coltivazione'];// Antonio Germani questo è il numero dell'id_mov che cancellerò >> $form['id_mov'] e il numero del campo di coltivazione eventualmente associato >> $form['campo_coltivazione']
	
// inizio cancellazione ore operaio	
// controllo se clfoco è un operaio e ne prendo l'id_staff
	$res = gaz_dbi_get_row($gTables['staff'], "id_clfoco", $form['clfoco']);
		If (isset ($res)) { // se c'è nello staff, cioè è un operaio			
			$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $res['id_staff'], "AND work_day ='{$form['datdoc']}'");
			If (isset($rin)) { // se esiste il giorno dell'operaio prendo le ore normali lavorate e gli sottraggo quelle del movimento da cancellare
				$hours_normal=$rin['hours_normal']-$form['quanti'];
				// ne aggiorno il database
				$query = "UPDATE ". $gTables['staff_worked_hours']." SET hours_normal ='".$hours_normal."' WHERE id_staff = '".$res['id_staff']."' AND work_day = '".$form['datdoc']."'";
				gaz_dbi_query($query);
			}	
		} 
// fine cancellazione ore operaio		
			
	if ($campo_coltivazione>0) { // se c'è un campo di coltivazione aggiorno il giorno di sospensione
	$form2 = gaz_dbi_get_row($gTables['campi'], 'codice', intval($campo_coltivazione));
	if (intval($form2['id_mov'])==intval($id_mov)){
		
// prendo tutti i movimenti di magazzino che hanno interessato il campo di coltivazione
$n=0;$array=array();
		$query="SELECT ".'*'." FROM ".$gTables['movmag']. " WHERE campo_coltivazione ='". $campo_coltivazione."' AND operat ='-1' AND id_mov <> ".$form2['id_mov'];
		
		$result = gaz_dbi_query($query);
		while($row = $result->fetch_assoc()) {
// cerco i giorni di sospensione del prodotto che si trovano in ogni movimento
			$artico= $row['artico'];
			$id_avversita=$row['id_avversita'];
			$id_colture=$row['id_colture'];
			$form3 =gaz_dbi_get_row($gTables['artico'], 'codice', $artico);
			$temp_sosp = $form3['tempo_sospensione'];

// se è presente prendo il tempo di sospensione specifico altrimenti lascio quello generico
	$query2="SELECT ".'tempo_sosp'." FROM ".$gTables['camp_uso_fitofarmaci']. " WHERE cod_art ='". $artico ."' AND id_colt ='".$id_colture."' AND id_avv ='".$id_avversita."'";
	
			$result2 = gaz_dbi_query($query2);
			while ($row2 = $result2->fetch_assoc()) {
				$temp_sosp=$row2['tempo_sosp'];
			}				
					
				
// creo un array con tempo di sospensione + codice articolo + movimento magazzino
			$temp_deca=(intval($temp_sosp)*86400)+strtotime($row["datdoc"]);
			$array[$n]= array('temp_deca'=>$temp_deca,'datdoc'=>$row["datdoc"],'artico'=>$artico, 'id_mov'=>$row["id_mov"]);
			$n=$n+1;        
		}
		// ordino l'array per tempo di sospensione
		rsort ($array);		
				
			if (isset ($array[0]['temp_deca']) && $n>0) { // se c'è un tempo decadimento nei movimenti di magazzino e c'è almeno un movimento
			
		// aggiorno la tabella del campo di coltivazione con il movimento di magazzino che ha il decadimento più elevato
				$dt=date('Y/m/d', $array[0]['temp_deca']);
				$query="UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . $dt .  "' , codice_prodotto_usato = '"  .$array[0]['artico']. "' , id_mov = '"  .$array[0]['id_mov'].  "' WHERE codice ='". intval($campo_coltivazione)."'";
				
				gaz_dbi_query ($query) ;
			}	
			else { // in tutti gli altri casi
			// aggiorno la tabella del campo di coltivazione azzerando il decadimento e l'ID movimento che lo ha creato
				$query="UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . "" .  "' , codice_prodotto_usato = '"  ."". "' , id_mov = '"  ."".  "' WHERE codice ='". intval($campo_coltivazione)."'";
				
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
print "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[32] . "</td><td class=\"FacetDataTD\">" . $partner . "</td></tr>\n";
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