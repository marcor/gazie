<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
 // prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}

// *****************************************************************************
if(isset($_GET['term'])) {	//	Evitiamo errori se lo script viene chiamato direttamente
	
	if(isset($_GET['opt'])) {
		$opt = $_GET['opt'];	
	} else {
		$opt = 'anagra';
	}
	
	require("../../library/include/datlib.inc.php");
	$admin_aziend = checkAdmin();
	$return_arr   = array();

	$term         = filter_var(substr($_GET['term'],0,20),FILTER_SANITIZE_MAGIC_QUOTES);
	$term         = gaz_dbi_real_escape_string($term);
	 
	$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Sono consentiti solo lettere e numeri..."));
	$json_invalid = json_encode($a_json_invalid);
	 
	// replace multiple spaces with one
	$term = preg_replace('/\s+/', ' ', $term);
	 
	// SECURITY HOLE ***************************************************************
	// allow space, any unicode letter and digit, underscore dash and %
	//if(preg_match("/[^\040\pL\pN\%_-]/u", $term)) {
	if(preg_match("/[^\040\pL\pN\%\/\._-]/u", $term)) {
	  print $json_invalid;
	  exit;
	}

	if(strlen($term)<2) {	//	Equivalente del precedente strlen($term)>1
		return;
	}
/*
	if($term!='%%') {
		$term = str_replace('%', '', $term);
	} */
	$parts = explode(' ', $term);

	//$result = gaz_dbi_dyn_query("id,ragso1,citspe",$gTables['anagra'],"ragso1 LIKE '%".$term."%'",'ragso1');
	/** ENRICO FEDELE */
	/* tolto citspe perchè pare inutilizzato, associato nome 'label' a ragso1 per poter usare fetch_assoc dopo */
	
	switch($opt) {
		case 'product':
			foreach($parts as $id => $part) {
				$like[] = like_prepare("descri", $part);
			}
			$like = implode(" AND ", $like);	//	creo la porzione di query per il like
			$result = gaz_dbi_dyn_query("codice AS id, descri AS label, descri AS value", $gTables['artico'], $like, "descri ASC");
			break;
		case 'location':
			foreach($parts as $id => $part) {
				$like[] = like_prepare($gTables['municipalities'].".name", $part);
			}
			$like = implode(" AND ", $like);	//	creo la porzione di query per il like
			$result = gaz_dbi_dyn_query("UPPER(".$gTables['municipalities'].".name) AS value,
							".$gTables['municipalities'].".postal_code AS id,
							".$gTables['provinces'].".abbreviation AS prospe, 
							".$gTables['country'].".name AS nation, 
							".$gTables['country'].".iso AS country, 
							CONCAT(".$gTables['municipalities'].".postal_code, ' ', ".$gTables['municipalities'].".name, ' (', ".$gTables['provinces'].".abbreviation, ') ', ".$gTables['regions'].".name, ' ', ".$gTables['country'].".name) AS label ",
							$gTables['municipalities']." 
							LEFT JOIN ".$gTables['provinces']." ON 
							".$gTables['municipalities'].".id_province = ".$gTables['provinces'].".id
							LEFT JOIN ".$gTables['regions']." ON 
							".$gTables['provinces'].".id_region = ".$gTables['regions'].".id
							LEFT JOIN ".$gTables['country']." ON 
							".$gTables['regions'].".iso_country = ".$gTables['country'].".iso",
							$like,$gTables['municipalities'].".name ASC");
			break;
		default:
			foreach($parts as $id => $part) {
				$like[] = like_prepare("ragso1", $part);
			}
			$like = implode(" AND ", $like);	//	creo la porzione di query per il like
		$result = gaz_dbi_dyn_query("id, ragso1 AS label, ragso1 AS value",$gTables['anagra'],$like,'ragso1');
	}
	while($row = gaz_dbi_fetch_assoc($result)) {
		$return_arr[] = $row;
	}

	if($term!='%%') {
		$return_arr = apply_highlight($return_arr, str_replace("%", '', $parts));
	}
	echo json_encode($return_arr);
} else {
  return;
}

/** ENRICO FEDELE */
/**
 * prepara la porzione di like per la query
 * se l'utente non ha inserito
 *
 * @param string $dbfield: campo del db sul quale fare la like
 * @param string $txtsearch: testo da cercare nel campo
 * @return array or false
 */
function like_prepare($dbfield, $txtsearch) {
	if(mb_stripos_all($txtsearch, '%')===false) {	//	L'utente non ha inserito il carattere jolly
		return $dbfield." LIKE '%".$txtsearch."%'";
	} else {	//	L'utente sta usanto il carattere jolly %, quindi non devo inserirlo nella query
		return $dbfield." LIKE '".$txtsearch."'";
	}
}

/** ENRICO FEDELE */
/* Codice preso da
   http://www.pontikis.net/blog/jquery-ui-autocomplete-step-by-step
*/
/**
 * mb_stripos all occurences
 * based on http://www.php.net/manual/en/function.strpos.php#87061
 *
 * Find all occurrences of a needle in a haystack
 *
 * @param string $haystack
 * @param string $needle
 * @return array or false
 */
function mb_stripos_all($haystack, $needle) {
	$s = 0;
	$i = 0;
	
	while(is_integer($i)) {
		$i = mb_stripos($haystack, $needle, $s);
		
		if(is_integer($i)) {
			$aStrPos[] = $i;
			$s         = $i + mb_strlen($needle);
		}
	}
	
	if(isset($aStrPos)) {
		return $aStrPos;
	} else {
		return false;
	}
}
 
/**
 * Apply highlight to row label
 *
 * @param string $a_json json data
 * @param array $parts strings to search
 * @return array
 */
function apply_highlight($a_json, $parts) {
	$p    = count($parts);
	$rows = count($a_json);
	
	for($row = 0; $row < $rows; $row++) {
		$label         = $a_json[$row]["label"];
		$a_label_match = array();
	
		for($i = 0; $i < $p; $i++) {
			$part_len      = mb_strlen($parts[$i]);
			$a_match_start = mb_stripos_all($label, $parts[$i]);
	
			foreach($a_match_start as $part_pos) {	
				$overlap = false;
				foreach($a_label_match as $pos => $len) {
					if($part_pos - $pos >= 0 && $part_pos - $pos < $len) {
						$overlap = true;
						break;
					}
				}
				if(!$overlap) {
					$a_label_match[$part_pos] = $part_len;
				}
			}
		}
		if(count($a_label_match) > 0) {
			ksort($a_label_match);
			
			$label_highlight = '';
			$start           = 0;
			$label_len       = mb_strlen($label);
	
			foreach($a_label_match as $pos => $len) {
				if($pos - $start > 0) {
					$no_highlight     = mb_substr($label, $start, $pos - $start);
					$label_highlight .= $no_highlight;
				}
				$highlight        = '<span class="hl_results">' . mb_substr($label, $pos, $len) . '</span>';
				$label_highlight .= $highlight;
				$start            = $pos + $len;
			}
			if($label_len - $start > 0) {
				$no_highlight     = mb_substr($label, $start);
				$label_highlight .= $no_highlight;
			}
			$a_json[$row]["label"] = $label_highlight;
		}
	}
	return $a_json;
}
?>

