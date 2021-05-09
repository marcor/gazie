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
  
  >>>>>> Antonio Germani -- MOSTRA riepilogo vendite giornaliero  <<<<<<
  
 */
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
function dailyrep($id_con) { // restituisce i righi delle vendite giornaliere del movimento contabile
	global $gTables;
	
	// LEFT JOIN ". $gTables['rigdoc'] ." ON ".$gTables['rigdoc'].".id_order = ".$gTables['rigbro'].".id_tes AND ". $gTables['rigdoc'].".codart = '". $codice. "'
	// LEFT JOIN ". $gTables['clfoco'] ." ON ".$gTables['clfoco'].".codice=".$gTables['tesbro'].".clfoco 
	// GROUP BY id_rig ASC
	
	$query ="
      SELECT ". $gTables['rigdoc'] .".*, ". $gTables['artico'] .".catmer, ". $gTables['catmer'] .".descri AS descri_cat, ". $gTables['tesdoc'] .".*, ". $gTables['aliiva'] .".aliquo
	  FROM " . $gTables['tesdoc'] . "
      LEFT JOIN ". $gTables['rigdoc'] ." ON ".$gTables['rigdoc'].".id_tes=".$gTables['tesdoc'].".id_tes 
      LEFT JOIN ". $gTables['artico'] ." ON ".$gTables['artico'].".codice=".$gTables['rigdoc'].".codart 
	  LEFT JOIN ". $gTables['catmer'] ." ON ".$gTables['catmer'].".codice=".$gTables['artico'].".catmer 
      LEFT JOIN ". $gTables['aliiva'] ." ON ".$gTables['aliiva'].".codice=".$gTables['tesdoc'].".expense_vat
	  WHERE ". $gTables['tesdoc'].".id_con = '". $id_con ."' AND (". $gTables['rigdoc'] .".tiprig = 0 OR ". $gTables['rigdoc'] .".codric > 0)
      ORDER BY catmer DESC, ". $gTables['tesdoc'] .".id_tes ASC;
      ";
    $result = gaz_dbi_query($query); // eseguo query
	$cat=[];	
	$lastest="";
	$n=0;
	while ($res=$result->fetch_assoc()){ // raggruppo per categoria e faccio le somme per categoria
		
		if (!$res['catmer']){
			$res['catmer']= 9999 + $n;// creo una categoria fittizia
			$n++;
		}		
		if (isset($cat[$res['catmer']]['sum'])){
			$cat[$res['catmer']]['sum'] += ($res['quanti']*$res['prelis'])-((($res['quanti']*$res['prelis'])*$res['sconto'])/100);
			$cat[$res['catmer']]['sumvat'] += (((($res['quanti']*$res['prelis'])-((($res['quanti']*$res['prelis'])*$res['sconto'])/100))*$res['pervat'])/100);
			$cat[$res['catmer']]['count']++;
			if ($res['id_tes'] <> $lastest){
				$cat[$res['catmer']]['traspo'] += $res['traspo'];
				$cat[$res['catmer']]['speban'] += $res['speban'];
				$cat[$res['catmer']]['spevar'] += $res['spevar'];
				$cat[$res['catmer']]['traspovat'] += $res['traspo']*$res['aliquo']/100;
				$cat[$res['catmer']]['spebanvat'] += $res['speban']*$res['aliquo']/100;
				$cat[$res['catmer']]['spevarvat'] += $res['spevar']*$res['aliquo']/100;
			}
		} else {
			$cat[$res['catmer']]['sum'] = ($res['quanti']*$res['prelis'])-((($res['quanti']*$res['prelis'])*$res['sconto'])/100);
			$cat[$res['catmer']]['sumvat'] = (($cat[$res['catmer']]['sum']*$res['pervat'])/100);
			$cat[$res['catmer']]['count'] = 1;
			$cat[$res['catmer']]['traspo'] = $res['traspo'];
			$cat[$res['catmer']]['speban'] = $res['speban'];
			$cat[$res['catmer']]['spevar'] = $res['spevar'];
			$cat[$res['catmer']]['traspovat'] = $res['traspo']*$res['aliquo']/100;
			$cat[$res['catmer']]['spebanvat'] = $res['speban']*$res['aliquo']/100;
			$cat[$res['catmer']]['spevarvat'] = $res['spevar']*$res['aliquo']/100;
		}
		$cat[$res['catmer']][] = $res;
		$lastest = $res['id_tes'];
	}	
	return $cat;
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array());
$id_con=intval($_GET['id_con']);
$retcat=dailyrep($id_con);

$totivacomp=0;
$totiva=0;
$totimp=0;
?>
<div align="center" class="FacetFormHeaderFont">Riepilogo vendite contabilizzate nel movimento ID <?php echo $id_con; ?></div>
<form method="GET" >
<div class="table-responsive">
	<table class="Tlarge table table-bordered table-condensed table-striped">
		<tr>
			<td class="FacetFieldCaptionTD">
				<?php echo "Data"; ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php echo "Quantità"; ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php echo "Categoria";; ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php echo "Prezzo unitario medio"; ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php echo "Aliquota IVA"; ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php echo "IVA"; ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php echo "Imponibile"; ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php echo "IVA"; ?>
			</td>
		</tr>
		<?php
		$tottraspo=0;
		$tottraspovat=0;
		$totspeban=0;
		$totspebanvat=0;
		$totspevar=0;
		$totspevarvat=0;
		$n=0;
		$key = array();
		foreach ($retcat as $cat){ // ciclo le righe per categoria raggruppata
			
			foreach ($cat as $catrow){
				
				if (isset($catrow['descri_cat']) AND $catrow['catmer']<9999){ // se categoria esistente stampo il rigo
					echo "<tr><td>".gaz_format_date($catrow['datemi'])."</td><td>".$cat['count']."</td><td>".$catrow['descri_cat']."</td><td>".gaz_format_number($cat['sum']/$cat['count'])."</td><td>".$catrow['pervat']."</td><td>".gaz_format_number($cat['sumvat']/$cat['count'])."</td><td>".gaz_format_number($cat['sum'])."</td><td>".gaz_format_number($cat['sumvat'])."</td>";
					break;					
				} elseif (isset($catrow['descri'])){// se è una categoria fittizia
					if(isset($key[$catrow['descri']])){// e se c'è descri Creo una chiave per le spese
						$key[$catrow['descri']][0] += $catrow['prelis'];
						$key[$catrow['descri']]['vat'] += $cat['sumvat'];
					}else{
						$key[$catrow['descri']][0] = $catrow['prelis'];
						$key[$catrow['descri']]['pervat'] = $catrow['pervat'];
						$key[$catrow['descri']]['vat'] = $cat['sumvat'];
					}
				}				
				echo "</tr>";
				
			}
			
			$totivacomp += $cat['sum']+$cat['sumvat'];
			$totimp += $cat['sum'];
			$totiva += $cat['sumvat'];
			$tottraspo += $cat['traspo'];
			$tottraspovat += $cat['traspo']+$cat['traspovat'];
			$totspeban += $cat['speban'];
			$totspebanvat += $cat['speban']+$cat['spebanvat'];
			$totspevar += $cat['spevar'];
			$totspevarvat += $cat['spevar']+$cat['spevarvat'];
		}
		
		foreach($key as $k => $value){ // stampo i righi delle categorie fittizie	
			echo "<tr><td>".gaz_format_date($catrow['datemi'])."</td><td> 1 </td><td>".$k."</td><td></td><td></td><td>".$value['pervat']."</td><td>".gaz_format_number($value[0])."</td><td>".gaz_format_number($value['vat'])."</td>";
		}
		// se presenti in testata (vecchio sistema) stampo le spese della testata
		if ($tottraspo>0){
			echo "<tr><td>".gaz_format_date($catrow['datemi'])."</td><td>1 </td><td> Spese trasporto</td><td></td><td></td><td></td><td>".gaz_format_number($tottraspo)."</td><td>".gaz_format_number($tottraspovat)."</td></tr>";
			$totimp += $tottraspo;
			$totiva += $tottraspovat;
		}
		if ($totspeban>0){
			echo "<tr><td>".gaz_format_date($catrow['datemi'])."</td><td>1 </td><td> Spese incasso</td><td></td><td></td><td></td><td>".gaz_format_number($totspeban)."</td><td>".gaz_format_number($totspebanvat)."</td></tr>";
			$totimp += $totspeban;
			$totiva += $totspebanvat;
		}
		if ($totspevar>0){
			echo "<tr><td>".gaz_format_date($catrow['datemi'])."</td><td>1 </td><td> Spese varie</td><td></td><td></td><td></td><td>".gaz_format_number($totspevar)."</td><td>".gaz_format_number($totspevarvat)."</td></tr>";
			$totimp += $totspevar;
			$totiva += $totspevarvat;
		}
		// stampo i totali
		echo "<tr></tr><tr class=\"FacetDataTD\"><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"> Totale giornaliero </td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\">". gaz_format_number($totimp) ."</td><td class=\"FacetDataTD\">". gaz_format_number($totiva) ."</td></tr>";
		echo "<tr class=\"FacetDataTD\"><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"> Totale giornaliero </td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\"> IVA compresa </td><td class=\"FacetDataTD\"><b>". gaz_format_number(($totimp+$totiva)) ."</b></td></tr>";
		?>
		</tr>
	</table>
</div>
</form>
<?php

?>