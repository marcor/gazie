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

class informForm extends GAzieForm {
	
	private $testata = array(); 
	
	function __construct($testata='') {
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
			$param = array();
			$MatchesListTemp = explode(' ', $match[1]);
			foreach ($MatchesListTemp as $match1)
			{
				$tmp = explode('=', $match1);
				$param[$tmp[0]] = str_replace("'", '',$tmp[1]);
			}
			$output = $this->righepreventivo($param);
					
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
			$param = array();
			$MatchesListTemp = explode(' ', $match[1]);
			foreach ($MatchesListTemp as $match1)
			{
				$tmp = explode('=', $match1);
				$param[$tmp[0]] = str_replace("'", '',$tmp[1]);
			}
			$output = $this->totalepreventivo($param);
					
			$testo = str_replace($match[0], $output, $testo);
			}	
		}
		
		return $testo;
	}
	
	//Funzione per la creazione della tabella del preventivo
	function righepreventivo($param){
		global $gTables, $admin_aziend;
		
		require("../../modules/vendit/lang." . $admin_aziend['lang'] . ".php");
		$script_transl = $strScript['admin_broven.php'];

		//Recupero le righe del 
		$old_rows = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = " . $param['id'], "id_rig asc");
		
		//Colori Tabella
		if(isset($param['thbackgroundcolor'])){
			$thbackgroundcolor = $param['thbackgroundcolor'];
		}else{
			$thbackgroundcolor = "#f8f8f8";
		}

		if(isset($param['thcolor'])){
			$thcolor = $param['thcolor'];
		}else{
			$thcolor = "#000";
		}

		if(isset($param['trbordertot'])){
			$trbordertot = $param['trbordertot'];
		}else{
			$trbordertot = "#f2f2f2";
		}
		
		//Calcolo le larghezze e i numeri delle colonne
		if(strtolower($param['noparriga'])!= 'si'){
			//Mostro la colonna Prezzo e quantità
			$descrilen = 55 ;
			$totalelen = 15 ;
			$ncdescri = 2;
		}else{
			//Nascondo la colonna Prezzo e quantità
			$descrilen = 80 ; 
			$totalelen = 20 ; 
			$ncdescri = 1 ;			
		}
		if(isset($param['checkbox']) && strtolower($param['checkbox'])== 'si'){
			$descrilen = $descrilen - 5;
		}
		
		//Disegno la tabella
		$output = '<table cellspacing="0" cellpadding="1" border="0">';
		$output .= '<tr style="background-color:' . $thbackgroundcolor . ';color:' . $thcolor .'">';
		
		//Intestazione della tabella
		$output .= '<th width="' . $descrilen . '%">' . $script_transl[21] . '</th>';

		if(strtolower($param['noparriga'])!= 'si'){
			$output .= '<th width="15%" align="right">' . $script_transl[23] . '</th>';
			$output .= '<th width="15%" align="right">' . $script_transl[16] . '</th>';
		}
		
		$output .= '<th width="' .$totalelen .'%" align="right">' . $script_transl[25] . '</th>';
		
		if(isset($param['checkbox']) && strtolower($param['checkbox'])== 'si'){
			$output .= '<th width="2%" align="right"></th>';
			$output .= '<th width="3%" align="right"></th>';
		}
		$output .= '</tr>';
		//disegno le righe della tabella del preventivo
		while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
			//Calcolo il prezzo e l'importo
			if(isset($param['calciva']) && strtolower($param['calciva']) == 'si'){
				$prezzo = round($val_old_row['prelis'] + ($val_old_row['prelis'] * $val_old_row['pervat'] / 100), 2);
			}else{
				$prezzo = round($val_old_row['prelis'], 2);
			}
			//Calcolo l'importo
			$importo = round($val_old_row['quanti'] * $prezzo, 2);
			
			//Disegno la riga
			$output .= '<tr>';
			$output .= '<td>' . $val_old_row['descri'] . '</td>';
			//Celle prezzo unitario e quantità
			if(strtolower($param['noparriga'])!= 'si'){
				$output .= '<td align="right">&euro; ' . $prezzo . '</td>';
				$output .= '<td align="right">' . $val_old_row['unimis'] . ' ' . round($val_old_row['quanti'], 2) . '</td>';
			}
			//Importo della riga
			$output .= '<td align="right">&euro; ' . $importo . '</td>';
			
			//Checkbox sulle righe
			if(isset($param['checkbox']) && strtolower($param['checkbox'])== 'si'){
				$output .= '<td></td>';
				$output .= '<td><div style="border:1px solid #a2a2a2;height:10px;"></div></td>';
			}
			$output .= '</tr>';
		}
		//disegno il totale 
		if(isset($param['totale']) && strtolower($param['totale'])== 'si'){
			$output .= '<tr>';
				$output .= '<td colspan="' . $ncdescri . '"></td>';
				
				if(strtolower($param['noparriga'])!= 'si'){
					$output .= '<td align="right" style="border-top:1px solid ' . $trbordertot . ';">' . $script_transl[36] . '</td>';
					$output .= '<td align="right" style="border-top:1px solid ' . $trbordertot . ';">' .$this->totalepreventivo($param) .'</td>';
				}else{
					$output .= '<td align="right" style="border-top:1px solid #f2f2f2;">' . $script_transl[36] . ' ' .$this->totalepreventivo($param) .'</td>';
				}
				if(isset($param['checkbox']) && strtolower($param['checkbox'])== 'si'){
					$output .= '<th width="2%" align="right"></th>';
					$output .= '<th width="3%" align="right"></th>';
				}				
			$output .= '</tr>';
		}
		
		$output .= '</table>'; 
		return $output ;
	}
	
	//Funzione per la creazione della tabella del preventivo
	function totalepreventivo($param){
		global $gTables;
		
		//Recupero le righe del 
		$old_rows = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = " . $param['id'], "id_rig asc");
		$tot = 0;
		while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
			if(isset($param['calciva']) && strtolower($param['calciva']) == 'si'){
				$prezzo = round($val_old_row['prelis'] + ($val_old_row['prelis'] * $val_old_row['pervat'] / 100), 2);
			}else{
				$prezzo = round($val_old_row['prelis'], 2);
			}
			//Calcolo l'importo
			$importo = round($val_old_row['quanti'] * $prezzo, 2);
			
			$tot += $importo;
		} 
		return '&euro; ' .$tot ;
	}

    function selectMunicipalities($cerca,$val) {
        global $gTables;
        if ($val >= 1) {
            $municipalities = gaz_dbi_get_row($gTables['municipalities'], 'id',  $val);
            echo '<input type="submit" tabindex="999" value="'.$municipalities['name'].'" name="change" onclick="this.form.hidden_req.value=\'change_municipalities\';" title="Cambia comune">';
            echo '<input type="hidden" name="search_municipalities" id="search_municipalities" value="' . $municipalities['name'] . '" />';
        } else {
            echo '<input type="text" name="search_municipalities" id="search_municipalities" placeholder=" cerca" tabindex="1" value="' . $cerca . '"  maxlength="16" />';
        }
        echo '<input type="hidden" id="id_municipalities" name="id_municipalities" value="'.$val.'">';
    }
	
}
?>