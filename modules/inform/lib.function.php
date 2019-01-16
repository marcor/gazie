<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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

class informForm extends GAzieForm {
	
	private $testata = array(); 
	
	function __construct($testata) {
		$this->TestataLettera = $testata;
	}
	
	//Funzione per elaborare gli schortcode
	function shortcode($testo){
		//Recupero i dati della lettera
		foreach ($this->TestataLettera as $key => $value) {
			if ($key == datemi){
				$testo = str_replace('[' . $key .' dFY]', date('d F Y',strtotime($value)), $testo);
				$testo = str_replace('[' . $key .' dmT]', date('d/m/Y',strtotime($value)), $testo);			
			}else{
				$testo = str_replace('[' . $key .']', $value , $testo);	
			}
		}
		
		//Cerco se c'è da recuperare la stampa del preventivo
		$regex = '/\[preventivo\s(.*?)\]/i';
		preg_match_all($regex, $testo, $matches, PREG_SET_ORDER);
		// No matches, skip this
		if ($matches){
			foreach ($matches as $match)
			{
			$matcheslist = explode(',', $match[1]);

			$output = $this->righepreventivo(trim($matcheslist[0]));
					
			$testo = str_replace($match[0], $output, $testo);
			}	
		}
		
		//Cerco se c'è da recuperare il totale del preventivo
		$regex = '/\[totalepreventivo\s(.*?)\]/i';
		preg_match_all($regex, $testo, $matches, PREG_SET_ORDER);
		// No matches, skip this
		if ($matches){
			foreach ($matches as $match)
			{
			$matcheslist = explode(',', $match[1]);

			$output = $this->totalepreventivo(trim($matcheslist[0]));
					
			$testo = str_replace($match[0], $output, $testo);
			}	
		}
		
		return $testo;
	}
	
	//Funzione per la creazione della tabella del preventivo
	function righepreventivo($numero){
		global $gTables;
		
		//Recupero le righe del 
		$old_rows = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = " . $numero, "id_rig asc");
		
		//Disegno la tabella
		$output = '<table cellspacing="0" cellpadding="1" border="0">';
		$output .= '<tr style="background-color:#f8f8f8;color:#000;">';
		$output .= '<th width="55%">Descrizione</th>';
		$output .= '<th width="15%" align="right">Prezzo</th>';
		$output .= '<th width="15%" align="right">Quantit&agrave;</th>';
		$output .= '<th width="15%" align="right">Importo</th>';
		$output .= '</tr>';
		while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
			$output .= '<tr>';
			$output .= '<td>' . $val_old_row['descri'] . '</td>';
			$output .= '<td align="right">&euro; ' . round($val_old_row['prelis'], 2) . '</td>';
			$output .= '<td align="right">' . $val_old_row['unimis'] . ' ' . round($val_old_row['quanti'], 2) . '</td>';
			$output .= '<td align="right">&euro; ' . round($val_old_row['quanti'] * $val_old_row['prelis'], 2) . '</td>';
			$output .= '</tr>';
		}
		$output .= '</table>'; 
		return $output ;
	}
	
	//Funzione per la creazione della tabella del preventivo
	function totalepreventivo($numero){
		global $gTables;
		
		//Recupero le righe del 
		$old_rows = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = " . $numero, "id_rig asc");
		$tot = 0;
		while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
			$tot += round($val_old_row['quanti'] * $val_old_row['prelis'], 2);
		} 
		return '&euro; ' .$tot ;
	}	
}
?>