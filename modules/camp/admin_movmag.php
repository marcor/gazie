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
	  
	  Registro di Campagna è un modulo creato da Antonio Germani Massignano AP
	  --------------------------------------------------------------------------
*/
require ("../../library/include/datlib.inc.php");
require ("../../modules/magazz/lib.function.php");
$lm = new lotmag;
$g2Form = new campForm();
$gForm = new magazzForm;
$admin_aziend = checkAdmin();
$msg = "";
$print_magval = "";
$dose = "";
$dose_usofito = "";
$tempo_sosp = "";
$dim_campo = "";
$rame_met_annuo = "";
$scadaut = "";
$scorta = "";
$service = "";
$today = strtotime(date("Y-m-d H:i:s", time()));


if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
$form = array();

/* per calcolo prezzo */
function getItemPrice($item, $partner = 0) {
    global $admin_aziend, $gTables;
    $artico = gaz_dbi_get_row($gTables['artico'], 'codice', $item);
    if ($partner > 0) {
        $partner = gaz_dbi_get_row($gTables['clfoco'], 'codice', $partner);
        $list = $partner['listin'];
        if (substr($partner['codice'], 0, 3) == $admin_aziend['mascli'] && $list > 0 && $list <= 3) {
            $price = $artico["preve$list"];
        } else {
            $price = $artico["preacq"];
        }
        $sconto = $partner['sconto'];
    } else { // prezzo articolo
        $sconto = 0;
        $price = $artico["preve1"];
    }
    return CalcolaImportoRigo(1, $price, $sconto, $admin_aziend['decimal_price']);
}
// Antonio Germani questo serve per la ricerca avversità
if (isset($_POST['nome_avv'])) {
    $form['mov'] = $_POST['mov'];
    $form['nmov'] = $_POST['nmov'];
    for ($m = 0;$m <= $form['nmov'];++$m) {
        $form['nome_avv'][$m] = $_POST['nome_avv' . $m];
        $form['id_avversita'][$m] = intval($form['nome_avv'][$m]);
    }
}

// se è stato premuto il pulsante di reset coltura
if (isset($_POST['erase'])) {
    $_POST['id_colture'] = 0;
    $_POST['nome_colt'] = "";
    $form['id_colture'] = 0;
    $form['nome_colt'] = "";
}

// se è stato premuto il pulsante di reset produzione
if (isset($_POST['erase2']) ) {
    $_POST['description'] = "";
    $_POST['id_colture'] = 0;
    $_POST['nome_colt'] = "";
    $_POST['campo_coltivazione'] = "";
	$_POST['id_orderman'] = "";
	$_POST['coseprod']="";
	$_form['coseprod']="";  
}
// Antonio Germani questo serve per la ricerca colture
if (isset($_POST['nome_colt'])) {
    $form['nome_colt'] = $_POST['nome_colt'];
    $form['id_colture'] = intval($form['nome_colt']);
    if ($form['id_colture'] == 0 && strlen($form['nome_colt']) > 0) {
        $msg.= "37+";
    }
}

// imposta se è un update o un insert
if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    if (!isset($_GET['id_mov'])) {
        header("Location: " . $_POST['ritorno']);
        exit;
    } else {
        $_POST['id_mov'] = $_GET['id_mov'];
    }
    $toDo = 'update';
} else {
    $toDo = 'insert';
}
if (!isset($_POST['Update']) and isset($_GET['Update'])) { //se è il primo accesso per UPDATE
    $form['hidden_req'] = '';
    $form['mov'] = 0;
    $form['nmov'] = 0;
    //recupero il movimento
    $result = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
    $form['id_mov'] = $result['id_mov'];
    $form['type_mov'] = $result['type_mov'];
    $form['id_rif'] = $result['id_rif'];
    $form['caumag'] = $result['caumag'];	
    $form['operat'] = $result['operat'];
	$res_caumag['operat']=$result['operat'];
    $form['gioreg'] = substr($result['datreg'], 8, 2);
    $form['mesreg'] = substr($result['datreg'], 5, 2);
    $form['annreg'] = substr($result['datreg'], 0, 4);
    $form['campo_coltivazione1'] = $result['campo_coltivazione']; //campo di coltivazione
	$form['ncamp']=1;
	$n=1;
    $form['clfoco'][$form['mov']] = $result['clfoco'];
    $form['clfocoin'] = $result['clfoco'];
    $result2 = gaz_dbi_get_row($gTables['staff'], "id_clfoco", $result['clfoco']);
    $form['staff'][$form['mov']] = $result2['id_staff'];
    $form['adminid'] = $result['adminid'];
    $form['id_orderman'] = intval($result['id_orderman']);
    $resultorderman = gaz_dbi_get_row($gTables['orderman'], "id", $form['id_orderman']);
    If ($form['id_orderman'] > 0) {
        $form['description'] = $resultorderman['description'];
		$form['coseprod']=$form['description'];
    } else {
        $form['description'] = "";
		$form['coseprod']="";
    }
    $form['tipdoc'] = $result['tipdoc'];
    $form['desdoc'] = $result['desdoc'];
    $form['id_colt'] = $result['id_colture'];
    $form['id_avv'] = $result['id_avversita'];
    $form['id_avversita'][$form['mov']] = $result['id_avversita'];
    $form['id_colture'] = $result['id_colture'];
    $colt = gaz_dbi_get_row($gTables['camp_colture'], "id_colt", $form['id_colt']);
    $form['nome_colt'] = $form['id_colt'] . " - " . $colt['nome_colt'];
    $avv = gaz_dbi_get_row($gTables['camp_avversita'], "id_avv", $form['id_avv']);
    $form['nome_avv'][$form['mov']] = $form['id_avv'] . " - " . $avv['nome_avv'];
    $form['scochi'] = $result['scochi'];
    $form['giodoc'] = substr($result['datdoc'], 8, 2);
    $form['mesdoc'] = substr($result['datdoc'], 5, 2);
    $form['anndoc'] = substr($result['datdoc'], 0, 4);
    $form['artico'][$form['mov']] = $result['artico'];
	$form['cosear'][$form['mov']] = $result['artico'];
	$itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
    $form['id_lotmag'][$form['mov']] = $result['id_lotmag'];
    $reslotmag = gaz_dbi_get_row($gTables['lotmag'], "id", $result['id_lotmag']);
    $form['identifier'][$form['mov']] = $reslotmag['identifier'];
    $form['expiry'][$form['mov']] = $reslotmag['expiry'];
    // Antonio Germani - se è presente, recupero il file documento lotto
    $form['filename'][$form['mov']] = "";
    If (file_exists('../../data/files/' . $admin_aziend['company_id']) > 0) {
        // recupero il filename dal filesystem
        $dh = opendir('../../data/files/' . $admin_aziend['company_id']);
        while (false !== ($filename = readdir($dh))) {
            $fd = pathinfo($filename);
            $r = explode('_', $fd['filename']);
            if ($r[0] == 'lotmag' && $r[1] == $result['id_lotmag']) {
                // riassegno il nome file
                $form['filename'][$form['mov']] = $fd['basename'];
            }
        }
    }
    // fine recupero file documento lotto
    $form['quanti'][$form['mov']] = gaz_format_quantity($result['quanti'], 0, $admin_aziend['decimal_quantity']);
    $form['quantiin'] = $result['quanti'];
    $form['datdocin'] = $result['datdoc'];
    $form['prezzo'][$form['mov']] = number_format($result['prezzo'], $admin_aziend['decimal_price'], '.', '');
    $form['scorig'][$form['mov']] = $result['scorig'];
    $form['clfoco'][$form['mov']] = $result['clfoco'];
    $form['status'] = $result['status'];
    $form['search_partner'] = ""; //Antonio Germani
    $form['search_item'] = "";
	
} elseif (isset($_POST['Insert']) or isset($_POST['Update'])) {    //     **** se non e' il primo accesso per update****
	$form['mov'] = $_POST['mov'];
	$form['nmov'] = $_POST['nmov'];
	if ($form['nmov']==$form['mov']){
		$form['cosear'][$form['mov']] = $_POST['cosear'];
		$form['artico'][$form['mov']] = $_POST['cosear'];
		$_POST['artico'.$form['mov']] = $_POST['cosear'];
	}
		
	if (isset($_POST['mov']) ) { // Antonio Germani - se è stato inserito un rigo faccio il parsing di tutti i righi presenti
		for ($m = 0;$m <= $form['nmov'];++$m) {
			$form['artico'][$m] = $_POST['artico' . $m];
			$form['cosear'][$m] = $form['artico'][$m];
			$form['id_lotmag'][$m] = $_POST['id_lotmag' . $m];
			$form['lot_or_serial'][$m] = $_POST['lot_or_serial' . $m];
			if ($form['lot_or_serial'][$m] == 1) {
				$form['identifier'][$m] = $_POST['identifier' . $m];
				$form['expiry'][$m] = $_POST['expiry' . $m];
				$form['filename'][$m] = $_POST['filename' . $m];
			} else {
				$form['identifier'][$m] = "";
				$form['expiry'][$m] = "";
				$form['filename'][$m] = "";
			}
			$form['quanti'][$m] = gaz_format_quantity($_POST['quanti' . $m], 0, $admin_aziend['decimal_quantity']);
			$form['scorig'][$m] = $_POST['scorig' . $m];
			$form['prezzo'][$m] = gaz_format_quantity($_POST['prezzo' . $m], 0, $admin_aziend['decimal_quantity']);
			$form['clfoco'][$m] = $_POST['clfoco' . $m];
			$form['nome_avv'][$m] = $_POST['nome_avv' . $m];
			$form['id_avversita'][$m] = intval($form['nome_avv'][$m]);
			if (isset($_POST['staff' . $m])) {
				$form['staff'][$m] = $_POST['staff' . $m];
			} else {
				$form['staff'][$m] = "";
			}
		}
		
		$itm = gaz_dbi_get_row($gTables['staff'], "id_staff", $form['staff'][$form['mov']]); // se c'è un operaio nel rigo ne carico i dati
		
		// se è il caso carica tabella camp_uso_fitofarmaci
		$dose_usofito = "";
		if ($form['artico'][$form['mov']] <> "" && $form['nome_avv'][$form['mov']] <> "") {
			$query = "SELECT " . 'dose' . " FROM " . $gTables['camp_uso_fitofarmaci'] . " WHERE cod_art ='" . $form['artico'][$form['mov']] . "' AND id_colt ='" . $form['id_colture'] . "' AND id_avv ='" . $form['id_avversita'][$form['mov']] . "'";
			$result_uso_fito = gaz_dbi_query($query);
			while ($row = $result_uso_fito->fetch_assoc()) {
				$dose_usofito = $row['dose'];
			}
		}  
	}

    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    //ricarico i registri per il form facendo gli eventuali parsing
    $form['id_mov'] = intval($_POST['id_mov']);
    $form['type_mov'] = 1;
    $form['id_rif'] = intval($_POST['id_rif']);
	
    $form['caumag'] = intval($_POST['caumag']);
	$res_caumag = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
	$form['operat'] = $res_caumag['operat'];
	if ($res_caumag['insdoc'] == 0) { //se la nuova causale non prevede i dati del documento
            $form['tipdoc'] = "";
            $form['desdoc'] = "";
            $form['giodoc'] = date("d");
            $form['mesdoc'] = date("m");
            $form['anndoc'] = date("Y");
            $form['scochi'] = 0;
            $form['id_rif'] = 0;
        }
	
    $form['gioreg'] = intval($_POST['gioreg']);
    $form['mesreg'] = intval($_POST['mesreg']);
    $form['annreg'] = intval($_POST['annreg']);
    $form['clfocoin'] = $_POST['clfocoin'];
    $form['quantiin'] = $_POST['quantiin'];
    $form['datdocin'] = $_POST['datdocin'];
	$form['ncamp']= $_POST['ncamp'];
	$nmax=$form['ncamp'];
	$nn=0;
	for ($n = 1;$n <= $nmax;++$n) {
		if (!isset($_POST['campo_coltivazione'.$n])){
			$form['campo_coltivazione'.$n]=""; $form['ncamp']--;
		} else {
		if ($_POST['campo_coltivazione'.$n]>0 ){
			$nn++;
			$form['campo_coltivazione'.$nn] = intval($_POST['campo_coltivazione'.$n]); //campo di coltivazione
		} else {
			$form['campo_coltivazione'.$n]=""; $form['ncamp']--;
		}
		}
	} $n--;
	if ($form['ncamp']==0){
		$form['ncamp']=1;
	}
    $form['adminid'] = "Utente connesso";
    $form['tipdoc'] = intval($_POST['tipdoc']);
    $form['desdoc'] = substr($_POST['desdoc'], 0, 50);
    $form['giodoc'] = intval($_POST['giodoc']);
    $form['mesdoc'] = intval($_POST['mesdoc']);
    $form['anndoc'] = intval($_POST['anndoc']);
    $form['scochi'] = floatval(preg_replace("/\,/", '.', $_POST['scochi']));
    $form['id_lotmag'][$form['mov']] = $_POST['id_lotmag' . $form['mov']];
    $form['lot_or_serial'][$form['mov']] = $_POST['lot_or_serial' . $form['mov']];
    if ($form['lot_or_serial'][$form['mov']] == 1) {
        $form['identifier'][$form['mov']] = $_POST['identifier' . $form['mov']];
        $form['expiry'][$form['mov']] = $_POST['expiry' . $form['mov']];
        $form['filename'][$form['mov']] = $_POST['filename' . $form['mov']];
    } else {
        $form['identifier'][$form['mov']] = "";
        $form['expiry'][$form['mov']] = "";
        $form['filename'][$form['mov']] = "";
    }
    $form['quanti'][$form['mov']] = gaz_format_quantity($_POST['quanti' . $form['mov']], 0, $admin_aziend['decimal_quantity']);
    if ((isset($_POST['prezzo' . $form['mov']]) > 0) && (strlen($_POST['prezzo' . $form['mov']]) > 0)) {
        $form['prezzo'][$form['mov']] = $_POST['prezzo' . $form['mov']];
        $form['prezzo'][$form['mov']] = str_replace('.', '', $form['prezzo'][$form['mov']]);
        $form['prezzo'][$form['mov']] = str_replace(',', '.', $form['prezzo'][$form['mov']]);
    } else {
        $form['prezzo'][$form['mov']] = "";
    }
    if (isset($_POST['scorig' . $form['mov']])) {
        $form['scorig'][$form['mov']] = floatval(preg_replace("/\,/", '.', $_POST['scorig' . $form['mov']]));
    } else {
        $form['scorig'][$form['mov']] = 0;
    }
    $form['status'] = substr($_POST['status'], 0, 10);
	$form['coseprod']= $_POST['coseprod'];
	$form['description']= $_POST['coseprod'];
	$res = gaz_dbi_get_row($gTables['orderman'], "description", $form['coseprod']);
    $form['id_orderman'] = $res['id'];
	
    if (intval($form['id_orderman']) > 0) { //se è stata inserita una produzione
        $rs_orderman = gaz_dbi_get_row($gTables['orderman'], "id", $form['id_orderman']);
        // Carico il campo della produzione nel form
		$form['campo_coltivazione1'] = $rs_orderman['campo_impianto'];
    } 
    $form['search_partner'] = "";
	if (isset ($form['campo_coltivazione1'])){ // se inserito il primo campo ne prendo la coltura
		$item_campi = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione1']);
		if ($item_campi['id_colture'] > 0) { // se c'è una coltura nel campo la carico nel form
			$form['id_colture'] = $item_campi['id_colture'];
			$res = gaz_dbi_get_row($gTables['camp_colture'], "id_colt", $form['id_colture']);
			$form['nome_colt'] = $form['id_colture'] . " - " . $res['nome_colt'];
		} else { // altrimenti azzero la coltura del form
			$form['nome_colt']="";
		}
	}
	
    // Antonio Germani - se è stato inserita una coltura la inserisco anche se diversa da quella del campo di coltivazione
    if ($_POST['nome_colt'] > 0) {
        $form['nome_colt'] = $_POST['nome_colt'];
        $form['id_colture'] = intval($form['nome_colt']);
    }
    // Antonio Germani - controllo se c'è una coltura deve esserci un campo di coltivazione
    if ($form['campo_coltivazione1'] < 1 && $form['id_colture'] > 0) {
        $msg.= "35+";
    }
    // Antonio Germani controllo e avviso se è stata cambiata la coltura nel campo di coltivazione
    if (isset($_POST['nome_colt'])) {
        if ($form['campo_coltivazione1'] > 0) { // se c'è un campo di coltivazione
            $result = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione1']);
            if ($result['id_colture'] <> $form['id_colture']) { // se è stata cambiata la coltura avviso
                $err = gaz_dbi_get_row($gTables['camp_colture'], "id_colt", $result['id_colture']);
?>
				<div class="alert alert-warning alert-dismissible">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<strong>Warning!</strong> Nel campo di coltivazione è presente la coltura <?php echo $result['id_colture'], " - ", $err['nome_colt']; ?> che è diversa da quella inserita. Se si conferma, verrà modificata la coltura nel campo di coltivazione!
				</div>
				<?php
            }
        }
    }
	$itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]); // acquisisco i dati dell'articolo del rigo in questione
	
	if ($form['artico'][$form['mov']] <> "" && !isset($itemart)) {// controllo se il codice articolo inserito esiste nella tabella artico
		$msg.= "18+";
	} 
    
	
    if (isset($_POST['newpartner'])) {
        $anagrafica = new Anagrafica();
        $partner = $anagrafica->getPartner($_POST['clfoco']);
        $form['search_partner'] = substr($partner['ragso1'], 0, 4);
        $form['clfoco'] = 0;
    }
    if (isset($_POST['Return'])) {
        header("Location: " . $_POST['ritorno']);
        exit;
    }
    /* Antonio Germani  per calcolo prezzo*/
    if ($_POST['hidden_req'] == 'new_price') {
        $form['prezzo'][$form['mov']] = getItemPrice($form['artico'][$form['mov']], $form['clfoco']);
        $form['hidden_req'] = '';
    }
	
    if (!empty($_POST['Insert'])) { // 	Se viene inviata la richiesta di conferma totale ................
        $utsreg = mktime(0, 0, 0, $form['mesreg'], $form['gioreg'], $form['annreg']);
        $utsdoc = mktime(0, 0, 0, $form['mesdoc'], $form['giodoc'], $form['anndoc']);
        if (!checkdate($form['mesreg'], $form['gioreg'], $form['annreg'])) $msg.= "16+";
        if (!checkdate($form['mesdoc'], $form['giodoc'], $form['anndoc'])) $msg.= "15+";
        if ($utsdoc > $utsreg) {
            $msg.= "17+";
        }     		
		// Antonio Germani controllo se in ricerca produzione è stato impostata la voce selezionandola dal menù a tendina
		if (strlen($_POST['coseprod'])>0 && intval($form['id_orderman'])==0) {
			$msg.= "30+"; // non esiste fra quelle create
      	}
		
		// Antonio Germani creo la data di ATTUAZIONE DELL'OPERAZIONE selezionata che poi confronterò con quella di sospensione del campo
        $dt = substr("0" . $form['giodoc'], -2) . "-" . substr("0" . $form['mesdoc'], -2) . "-" . $form['anndoc'];
        $dt = strtotime($dt);
       
		$nn=0;$tot_sup=0;
		if ($form['campo_coltivazione1']>0) { // se c'è almeno un campo
			for ($n = 1;$n <= $form['ncamp'];++$n) { // ciclo i campi inseriti
				$query = "SELECT " . 'giorno_decadimento' . "," . 'ricarico' . " FROM " . $gTables['campi'] . " WHERE codice ='" . $form['campo_coltivazione'.$n] . "'";
				$result = gaz_dbi_query($query);
				while ($row = $result->fetch_assoc()) {				
					$form['fine_sosp'.$n] = strtotime($row['giorno_decadimento']);// prendo la data di fine sospensione dai campi di coltivazione selezionati
					$form['dim_campo'.$n] = $row['ricarico']; // prendo pure la dimensione del campo e la metto in $dim_campo
				}
				// controllo se è ammesso il raccolto sul campo di coltivazione selezionato $msg .=24+ errore tempo di sospensione
				If ($form['campo_coltivazione'.$n] > 0 && $form['operat'] == 1 && intval($dt) < intval($form['fine_sosp'.$n])) {
					$msg.= "24+";
				}
				$tot_sup=$tot_sup+$form['dim_campo'.$n];
			}
		}		
		
        /* inizio controlli sulle righe articoli >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> */
		
        for ($m = 0;$m <= $form['nmov'];++$m) {
			$itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$m]); // carico i dati dell'articolo del rigo
            
			// Antonio Germani controllo che, se la causale movimento non opera, non ci sia un articolo con magazzino
			$service = intval($itemart['good_or_service']);
            If ($service == 0 && $form['operat'] == 0) {
                $msg.= "36+";
            }
            If ($service == 2 && $form['operat'] == 0) {
                $msg.= "36+";
            }
        
			
			// controllo mancanza articolo
            if (empty($form['artico'][$m])) { //manca l'articolo
                $msg.= "18+";
            }
            // controllo quantità uguale a zero
            if (gaz_format_quantity($form['quanti'][$m], 0, $admin_aziend['decimal_quantity']) == 0) { //la quantità è zero
                $msg.= "19+";
            }
			if (isset($_POST['Update'])) { // se è un update carico il movimento di magazzino presente nel database per fare i confronti
                 $check_movmag = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
			}					
            // Antonio Germani calcolo giacenza di magazzino, la metto in $print_magval e, se è uno scarico, controllo sufficiente giacenza
            
            If ($itemart['good_or_service'] == 0 or $itemart['good_or_service'] == 2) { // se non è un servizio
                // controllo se sono stati inseriti articoli merci uguali in più righe /perché non è possibile altrimenti non funzionano i controlli sulle quantità per lotto/
				
                for ($mart = 0;$mart <= $form['nmov'];++$mart) {
                    if ($m <> $mart) {
                        if ((intval($form['id_lotmag'][$m]) > 0) && (intval($form['id_lotmag'][$m]) == intval($form['id_lotmag'][$mart]))) {
                            $msg.= "40+";
                        }
                    }
                }
                $mv = $gForm->getStockValue(false, $form['artico'][$m]);
                $magval = array_pop($mv);
                $print_magval = floatval(str_replace(',', '', $magval['q_g']));
                if (isset($_POST['Update'])) {
                    if ($check_movmag['artico'] == $form['artico'][$m]){// Se l'articolo inserito nel form è lo stesso precedentemente memorizzato nel db, prendo la quantità precedentemente memorizzata e la riaggiungo alla giacenza di magazzino
					$print_magval = $print_magval + $check_movmag['quanti'];
					}
                }
                if ($form['operat'] == - 1 and (floatval(str_replace(',', '', $print_magval)) - floatval(str_replace(',', '', $form['quanti'][$m])) < 0)) {
                    //Antonio Germani quantità insufficiente
                    $msg.= "23+";
                }
            }
            // Antonio Germani > controllo che non sia caricato un articolo composito
            if ($itemart['good_or_service'] == 2 && $form['operat'] == 1) {
                $msg.= "42+"; // il carico di articolo composti si può fare solo dal modulo produzioni
                
            }
            // Antonio Germani - se l'articolo ha lotti in uscita controllo se il lotto selezionato ha quantità sufficiente
            If (($itemart['good_or_service'] == 0) && ($itemart['lot_or_serial'] == 1) && ($form['operat'] == - 1)) { // se è merce e ha lotti
                $lotqty = $lm->getLotQty($form['id_lotmag'][$m]);
				if ($toDo=="update" && intval ($check_movmag['id_lotmag']) == intval($form['id_lotmag'][$m])){
					$lotqty=$lotqty+$check_movmag['quanti'];
				}
                if ($lotqty < $form['quanti'][$m]) { 
                    $msg.= "38+";
                }
            }
            If (($itemart['good_or_service'] == 2) && ($itemart['lot_or_serial'] == 1) && ($form['operat'] == - 1)) { // se è articolo composto e ha lotti
                $lotqty = $lm->getLotQty($form['id_lotmag'][$m]);				
				if ($toDo=="update" && intval ($check_movmag['id_lotmag']) == intval($form['id_lotmag'][$m])){
					$lotqty=$lotqty+$check_movmag['quanti'];
				}
                if ($lotqty < $form['quanti'][$m]) {
                    $msg.= "38+";
                }
            }
            //Antonio Germani controllo se il prodotto è presente nel database fitofarmaci ed eventualmente se è scaduta l'autorizzazione
            $query = "SELECT " . 'SCADENZA_AUTORIZZAZIONE' . " FROM " . $gTables['camp_fitofarmaci'] . " WHERE PRODOTTO ='" . $form['artico'][$m] . "'";
            $result = gaz_dbi_query($query);
            while ($row = $result->fetch_assoc()) {
                $scadaut = $row['SCADENZA_AUTORIZZAZIONE'];
                $scadaut = strtotime(str_replace('/', '-', $scadaut));
                if ($scadaut > 0) {
                    if ($scadaut < $today) {
                        $msg.= "27+";
                    }
                }
            }
            // se è presente nel db fitofarmaci CONTROLLO QUANDO è StATO FATTO L'ULTIMO AGGIORNAMENTO del db fitofarmaci
            If (($result->num_rows) > 0) {
                $query = "SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_SCHEMA = '" . $Database . "' AND TABLE_NAME = '" . $gTables['camp_fitofarmaci'] . "'";
                $result = gaz_dbi_query($query);
                while ($row = $result->fetch_assoc()) {
                    $update = strtotime($row['UPDATE_TIME']);
                }
                // 1 giorno è 24*60*60=86400 - 30 giorni 30*86400=2592000
                If (intval($update) + 2592000 < $today) {
                    $msg.= "28+";;
                }
            }
			// Antonio Germani prendo la dose massima e il rame metallo
				$item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$m]);
				$dose_artico = $item['dose_massima']; // prendo la dose
				$rame_metallo = $item['rame_metallico']; // prendo anche il rame metallo del prodotto oggetto del movimento
				$query = "SELECT " . 'dose' . ", " . 'tempo_sosp' . " FROM " . $gTables['camp_uso_fitofarmaci'] . " WHERE cod_art ='" . $form['artico'][$m] . "' AND id_colt ='" . $form['id_colture'] . "' AND id_avv ='" . $form['id_avversita'][$m] . "'";
				$result = gaz_dbi_query($query);
				while ($row = $result->fetch_assoc()) {
					$dose_usofito = $row['dose'];
				}
			// per ogni rigo articolo devo ciclare i campi di coltivazione per i seguenti controlli  |||||||||||||||||||||||||||
			
			$nn=0;$quanti=0;
			if (isset ($form['dim_campo1'])) { // se c'è almeno un campo
				for ($n = 1;$n <= $form['ncamp'];++$n) { // ciclo i campi inseriti
					$quanti=((($form['dim_campo'.$n]/$tot_sup)*100)*$form['quanti'][$m])/100; // questa è la dose suddivisa in percentuale per il campo 
					
					if ($dose_usofito > 0) { //Controllo se la quantità o dose è giusta rapportata al campo di coltivazione
						If ($dose_usofito > 0 && $quanti > $dose_usofito * $form['dim_campo'.$n] && $form['operat'] == - 1 && $form['dim_campo'.$n] > 0) {
						$msg.= "34+"; // errore dose uso fito superata
                    
						?>
						<div class="alert alert-warning alert-dismissible">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<strong>Warning!</strong> Dose superata nel prodotto <?php echo $form['artico'][$m]; ?> con la coltura <?php echo $form['nome_colt']; ?>. La quantità massima utilizzabile è <?php echo gaz_format_quantity($dose_usofito * $form['dim_campo'.$n], 1, $admin_aziend['decimal_quantity']) ?>.
						</div>
						<?php
						}
					} else {
						if ($dose_artico > 0 && $quanti > $dose_artico * $form['dim_campo'.$n] && $form['operat'] == - 1 && $form['dim_campo'.$n] > 0) {
						$msg.= "25+"; // errore dose artico superata
                    
						?>
						<div class="alert alert-warning alert-dismissible">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<strong>Warning!</strong> Dose superata nel prodotto <?php echo $form['artico'][$m]; ?>. La quantità massima utilizzabile è <?php echo gaz_format_quantity($dose_artico * $form['dim_campo'.$n], 1, $admin_aziend['decimal_quantity']) ?> .
						</div>
						<?php
						}
					}
					// Antonio Germani Calcolo quanto rame metallo è stato usato nell'anno di esecuzione di questo movimento
					If ($form['campo_coltivazione'.$n] > 0) { // se il prodotto va in un campo di coltivazione
						$rame_met_annuo=0;
						if ($rame_metallo > 0) { //se questo prodotto contiene rame metallo
							$query = "SELECT " . 'artico' . "," . 'datdoc' . "," . 'quanti' . " FROM " . $gTables['movmag'] . " WHERE datdoc >'" . $form['anndoc'] . "' AND " . 'campo_coltivazione' . " = '" . $form['campo_coltivazione'.$n] . "'"; // prendo solo le righe dell'anno di esecuzione del trattamento e degli anni successivi con il campo di coltivazione selezionato nel form
							$result = gaz_dbi_query($query);
							while ($row = $result->fetch_assoc()) {
								if (substr($row['datdoc'], 0, 4) == $form['anndoc']) { // elimino dal conteggio gli eventuali anni successivi
									$item = gaz_dbi_get_row($gTables['artico'], "codice", $row['artico']);
									if ($item['rame_metallico'] > 0) {
										$rame_met_annuo = $rame_met_annuo + $item['rame_metallico'] * $row['quanti'];
									}
								}
							}
						}
					}
					// Antonio Germani controllo se con questo movimento non si supera la doce massima annua di 6Kg ad ha di rame metallo
			
					if ($toDo == "update" && $check_movmag['artico']==$form['artico'][$m] && $form['campo_coltivazione'.$n] > 0) { // se è un update, e non è stato cambiato l'articolo tolgo il rame metallo memorizzato in precedenza
						$rame_met_annuo = $rame_met_annuo - $rame_metallo * gaz_format_quantity($quanti, 0, $admin_aziend['decimal_quantity']);
					}
					if (($quanti>0 && $form['campo_coltivazione'.$n] > 0) && ($form['dim_campo'.$n] > 0) && ($rame_met_annuo + ($rame_metallo * gaz_format_quantity($quanti, 0, $admin_aziend['decimal_quantity'])) > (6 * $form['dim_campo'.$n]))) {
						$msg.= "26+"; // errore superato il limite di rame metallo ad ettaro                
						?>
						<div class="alert alert-warning alert-dismissible">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<strong>Warning!</strong> ERRORE rame metallo <br> Rame metallo annuo già usato:  <?php echo gaz_format_quantity($rame_met_annuo, 1, $admin_aziend['decimal_quantity']); ?>Kg - Rame metallo che si tenta di usare:  <?php echo gaz_format_quantity($rame_metallo * $quanti, 1, $admin_aziend['decimal_quantity']); ?>Kg - Limite annuo di legge per questo campo:  <?php echo gaz_format_quantity((6 * $form['dim_campo'.$n]), 1, $admin_aziend['decimal_quantity']); ?>Kg
						</div>
						<?php
					}				
				} // fine cliclo campi di coltivazione  
			}
        }/*  fine controllo righe articoli    <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
		
		
	
        //  §§§§§§§§§§§§§§§§ INIZIO salvataggio sui database §§§§§§§§§§§§§§§§§§§
        if (empty($msg)) { // nessun errore
			
            if ($toDo == "update") { // se è un update cancello eventule file di certificato lotto messo sulla cartella tmp
                foreach (glob("../../modules/camp/tmp/*") as $fn) { // prima cancello eventuali precedenti file temporanei
                    unlink($fn);
                }
            }
            $upd_mm = new magazzForm;
            //formatto le date
            $form['datreg'] = $form['annreg'] . "-" . $form['mesreg'] . "-" . $form['gioreg'];
            $form['datdoc'] = $form['anndoc'] . "-" . $form['mesdoc'] . "-" . $form['giodoc'];
            $new_caumag = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
            for ($form['mov'] = 0;$form['mov'] <= $form['nmov'];++$form['mov']) { // per ogni movimento inserito
                if (!empty($form['artico'][$form['mov']])) { // se è stato inserito un articolo
					
					$nn=0;
					for ($n = 1;$n <= $form['ncamp'];++$n) { // ciclo i campi inseriti
						if (isset ($form['dim_campo'.$n])) {
							$quanti=((($form['dim_campo'.$n]/$tot_sup)*100)*$form['quanti'][$form['mov']])/100; // questa è la dose suddivisa in percentuale per il campo 
						} else {
							$quanti=$form['quanti'][$form['mov']];
						}
						$upd_mm->uploadMag($form['id_rif'], $form['tipdoc'], 0, // numdoc � in desdoc
						0, // seziva � in desdoc
						$form['datdoc'], $form['clfoco'][$form['mov']], $form['scochi'], $form['caumag'], $form['artico'][$form['mov']], $quanti, $form['prezzo'][$form['mov']], $form['scorig'][$form['mov']], $form['id_mov'], $admin_aziend['stock_eval_method'], array('datreg' => $form['datreg'], 'operat' => $form['operat'], 'desdoc' => $form['desdoc']));
						//Antonio Germani Non riesco a capire come funziona la funzione uploadMag qui sopra; ho perso troppo tempo!!!!
						// risolvo in questa maniera per far scrivere i nuovi campi di movmag, specifici del quaderno di campagna
						If ($form['id_mov'] > 0) {
							$id_movmag = $form['id_mov'];
						} else {
							$query = "SHOW TABLE STATUS LIKE '" . $gTables['movmag'] . "'";
							$result = gaz_dbi_query($query);
							$row = $result->fetch_assoc();
							$id_movmag = $row['Auto_increment'];
							// siccome ha già registrato il movimento di magazzino devo togliere 1
							$id_movmag = $id_movmag - 1;
						}
						// inframezzo il salvataggio del movmag con i lotti perché altrimenti, se è un movimento in entrata, non ho id_lotmag da salvare in movmag
						//Antonio Germani - >>> inizio salvo lotti -se ci sono e se il prodotto li richiede-
						if ($form['operat'] == 1) { // se il movimento è in entrata -carico-
							$idlotcontroll = gaz_dbi_get_row($gTables['lotmag'], "id", $form['id_lotmag'][$form['mov']]); // in $idlotcontroll['id_movmag'] ho l'id del movimento madre del lotto mi
							if ($form['lot_or_serial'][$form['mov']] > 0 && intval($form['id_lotmag'][$form['mov']]) == 0) { // se l'articolo prevede un lotto e non ho id_lotmag, vuol dire che non ho scelto il lotto fra gli esistenti e quindi devo creare un  nuovo lotto
								// ripulisco il numero lotto da caratteri dannosi
								$form['identifier'][$form['mov']] = (empty($form['identifier'][$form['mov']])) ? '' : filter_var($form['identifier'][$form['mov']], FILTER_SANITIZE_STRING);
								if (strlen($form['identifier'][$form['mov']]) == 0) { // se non c'è il lotto lo inserisco con data e ora in automatico
									$form['identifier'][$form['mov']] = date("Ymd Hms");
								}
								if (strlen($form['expiry'][$form['mov']]) == 0) { // se non c'è la scadenza la inserisco a zero in automatico
									$form['expiry'][$form['mov']] = "0000-00-00 00:00:00";
								}
								// è un nuovo INSERT
								if (strlen($form['identifier'][$form['mov']]) > 0) {
									/* formatto la data per il database se arriva come yyyy/mm/dd
									$arraydt = explode("/", $form['expiry'][$form['mov']]);
									$form['expiry'][$form['mov']] = $arraydt[2]."-".$arraydt[1]."-".$arraydt[0];*/
									gaz_dbi_query("INSERT INTO " . $gTables['lotmag'] . "(codart,id_movmag,identifier,expiry) VALUES ('" . $form['artico'][$form['mov']] . "','" . $id_movmag . "','" . $form['identifier'][$form['mov']] . "','" . $form['expiry'][$form['mov']] . "')");
									// vedo in quale id è stato salvato il lotto e lo metto in id_lotmag di movmag
									$query = "SHOW TABLE STATUS LIKE '" . $gTables['lotmag'] . "'";
									$result = gaz_dbi_query($query);
									$row = $result->fetch_assoc();
									$form['id_lotmag'][$form['mov']] = $row['Auto_increment'] - 1;
								}
							} else {
								if (($form['lot_or_serial'][$form['mov']] > 0) && (intval($form['id_mov']) == intval($idlotcontroll['id_movmag']))) { // se l'articolo prevede un lotto e ho id_lotmag e se il movimento movmag è la madre del lotto devo modificare il lotto
									//  è un UPDATE
									if (strlen($form['identifier'][$form['mov']]) > 0) {
										/* formatto la data per il database	se arriva come yyy/mm/dd
										$arraydt = explode("/", $form['expiry'][$form['mov']]);
										$form['expiry'][$form['mov']] = $arraydt[2]."-".$arraydt[1]."-".$arraydt[0];*/
										gaz_dbi_query("UPDATE " . $gTables['lotmag'] . " SET codart = '" . $form['artico'][$form['mov']] . "' , id_movmag = '" . $id_movmag . "' , identifier = '" . $form['identifier'][$form['mov']] . "' , expiry = '" . $form['expiry'][$form['mov']] . "' WHERE id = '" . $form['id_lotmag'][$form['mov']] . "'");
									}
								}
							}
							// Antonio Germani - inizio salvo documento/certificato
							if (substr($form['filename'][$form['mov']], 0, 7) <> 'lotmag_') { // se è stato cambiato il file, cioè il nome non inizia con lotmag e, quindi, anche se è un nuovo insert
								if (!empty($form['filename'][$form['mov']])) { // e se ha un nome impostato nel form
									$tmp_file = "../../data/files/tmp/" . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $form['mov'] . '_' . $form['filename'][$form['mov']];
									// sposto nella cartella definitiva, rinominandolo, il relativo file temporaneo
									$fd = pathinfo($form['filename'][$form['mov']]);
									rename($tmp_file, "../../data/files/" . $admin_aziend['company_id'] . "/lotmag_" . $form['id_lotmag'][$form['mov']] . '.' . $fd['extension']);
								}
							}
						}
						// <<< fine salvo lotti
						// riprendo il salvataggio del movimento di magazzino in movmag
						$query = "UPDATE " . $gTables['movmag'] . " SET type_mov = '" . 1 . "', tipdoc = 'MAG' , campo_coltivazione = '" . $form['campo_coltivazione'.$n] . "' , id_avversita = '" . $form['id_avversita'][$form['mov']] . "' , id_colture = '" . $form['id_colture'] . "' , id_orderman = '" . $form['id_orderman'] . "' , id_lotmag = '" . $form['id_lotmag'][$form['mov']] . "' WHERE id_mov ='" . $id_movmag . "'";
						gaz_dbi_query($query);
						// Antonio Germani - aggiorno la tabella campi se c'è un campo inserito (cioè >0) e se l'operazione è uno scarico (cioè operat<0) e se la data di fine sospensione già presente nel campo è inferiore alla data di sospensione del prodotto appena usato (cioè $fine_sosp<$dt)
						//Antonio Germani per prima cosa determino il codice del movimento che eventualmente andrà nella tabella del campo di coltivazione
						if (!isset($_POST['Update'])) {
							// Antonio Germani se è un iserimento vedo quale sarà il prossimo codice del movimento del magazzino che verrà utilizzato !NB il codice è incremental!
							$query = "SHOW TABLE STATUS LIKE '" . $gTables['movmag'] . "'";
							$result = gaz_dbi_query($query);
							$row = $result->fetch_assoc();
							$id_mov = $row['Auto_increment'];
							// siccome ha già registrato il movimento di magazzino devo togliere 1
							$id_mov = $id_mov - 1;
						} else { // se non è un nuovo inserimento prendo il codice del movimento di magazzino selezionato
							$id_mov = $form['id_mov'];
						}
						// adesso vedo se si deve aggiornare il campo di coltivazione
						if ($form['campo_coltivazione'.$n] > 0 && $form['operat'] < 0) {
							/* Antonio Germani creo la data del trattamento selezionato a cui poi aggiungerò i giorni di sospensione. */
							$dt = substr("0" . $form['giodoc'], -2) . "-" . substr("0" . $form['mesdoc'], -2) . "-" . $form['anndoc'];
							$dt = strtotime($dt);
							// se è presente prendo il tempo di sospensione specifico
							$query = "SELECT " . 'tempo_sosp' . " FROM " . $gTables['camp_uso_fitofarmaci'] . " WHERE cod_art ='" . $form['artico'][$form['mov']] . "' AND id_colt ='" . $form['id_colture'] . "' AND id_avv ='" . $form['id_avversita'][$form['mov']] . "'";
							$result = gaz_dbi_query($query);
							while ($row = $result->fetch_assoc()) {
								$tempo_sosp = $row['tempo_sosp'];
							}
							if ($tempo_sosp == 0) { // se non è presente un tempo di sospensione specifico prendo quello generico. Lo metto in $temp_sosp
								$item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
								$temp_sosp = $item['tempo_sospensione'];
							} else {
								$temp_sosp = $tempo_sosp; // se è presente in $temp_sosp ci metto quello specifico
                            
							}
							$dt = $dt + (86400 * intval($temp_sosp)); //al giorno di attuazione i giorni di sospensione (Un giorno = 86400 timestamp)
							// Antonio Germani controllo se il tempo di sospensione del campo di coltivazione è inferiore a quello che si crea con questo trattamento. Se lo è aggiorno il database campi nel campo di coltivazione selezionato
							
							if ($fine_sosp < $dt) {
								$dt = date('Y/m/d', $dt);
								$query = "UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . $dt . "' , codice_prodotto_usato = '" . $artico . "' , id_mov = '" . $id_mov . "' , id_colture = '" . $form['id_colture'] . "' WHERE codice ='" . $form['campo_coltivazione'.$n] . "'";
								gaz_dbi_query($query);
							} else { // altrimenti
								if ($toDo == "update") { // se è un update, devo vedere se ci sono altri movimenti con un tempo superiore
									// prendo tutti i movimenti di magazzino che hanno interessato il campo di coltivazione escludendo il movimento oggetto di update
									$n = 0;
									$array = array();
									$query = "SELECT " . '*' . " FROM " . $gTables['movmag'] . " WHERE campo_coltivazione ='" . $form['campo_coltivazione'.$n] . "' AND operat ='-1' AND id_mov <> " . $id_movmag;
									$result = gaz_dbi_query($query);
									while ($row = $result->fetch_assoc()) {
										// cerco i giorni di sospensione del prodotto interessato ad ogni movimento
										$artico = $row['artico'];
										$id_avversita = $row['id_avversita'];
										$id_colture = $row['id_colture'];
										$form3 = gaz_dbi_get_row($gTables['artico'], 'codice', $artico);
										$temp_sosp = $form3['tempo_sospensione'];
										// se è presente prendo il tempo di sospensione specifico altrimenti lascio quello generico
										$query2 = "SELECT " . 'tempo_sosp' . " FROM " . $gTables['camp_uso_fitofarmaci'] . " WHERE cod_art ='" . $artico . "' AND id_colt ='" . $id_colture . "' AND id_avv ='" . $id_avversita . "'";
										$result2 = gaz_dbi_query($query2);
										while ($row2 = $result2->fetch_assoc()) {
											$temp_sosp = $row2['tempo_sosp'];
										}
										// creo un array con tempo di sospensione + codice articolo + movimento magazzino
										$temp_deca = (intval($temp_sosp) * 86400) + strtotime($row["datdoc"]);
										$array[$n] = array('temp_deca' => $temp_deca, 'datdoc' => $row["datdoc"], 'artico' => $artico, 'id_mov' => $row["id_mov"]);
										$n = $n + 1;
										// ordino l'array per tempo di sospensione
                                    
									}
									rsort($array);
									$dt_db_movmag = date('Y/m/d', $array[0]['temp_deca']);
									if ($n > 0 && $fine_sosp < $array[0]['temp_deca'] && $array[0]['temp_deca'] > $dt) { //se la data nel campo è minore della data trovata nei movimenti di magazzino che è maggiore di quella di questo movimento
										// memorizzo nel campo la data trovata nei movimenti
										$query = "UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . $dt_db_movmag . "' , codice_prodotto_usato = '" . $array[0]['artico'] . "' , id_mov = '" . $array[0]['id_mov'] . "' WHERE codice ='" . $form['campo_coltivazione'.$n] . "'";
										gaz_dbi_query($query);
									} elseif ($n > 0 && $fine_sosp > $array[0]['temp_deca'] && $array[0]['temp_deca'] > $dt) { // se la data nel campo è maggiore della data trovata nei movimenti di magazzino e la data trovata nei movimenti di magazzino è maggiore di quella di questo movimento
										// memorizzo nel campo la data trovata nei movimenti di magazzino
										$query = "UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . date('Y/m/d', $array[0]['temp_deca']) . "' , codice_prodotto_usato = '" . $artico . "' , id_mov = '" . $array[0]['id_mov'] . "' WHERE codice ='" . $form['campo_coltivazione'.$n] . "'";
										gaz_dbi_query($query);
									} elseif ($n == 1 && $dt > $array[0]['temp_deca']) { // se c'è un solo movimento di magazzino, oltre a questo in update, e la data di questo movimento è maggiore di quella del movimento di magazzino
										// memorizzo nel campo la data di questo movimento
										$query = "UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . date('Y/m/d', $dt) . "' , codice_prodotto_usato = '" . $artico . "' , id_mov = '" . $id_mov . "' WHERE codice ='" . $form['campo_coltivazione'.$n] . "'";
										gaz_dbi_query($query);
									} elseif ($n == 0) { // se non ci altri movimenti di magazzino, cioè questo è unico
										// memorizzo nel campo la data di questo movimento
										$query = "UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . date('Y/m/d', $dt) . "' , codice_prodotto_usato = '" . $artico . "' , id_mov = '" . $id_mov . "' WHERE codice ='" . $form['campo_coltivazione'.$n] . "'";
										gaz_dbi_query($query);
									} else { // altrimenti non faccio nulla perché va bene la data memorizzata in precedenza nel campo
                                    
									}
								}
							}
						}
						// fine gestione giorno di sospensione tabella campi
						// aggiornare tabella campi se è stata cambiata la coltura
						if ($form['campo_coltivazione'.$n] > 0) { // se c'è un campo di coltivazione
							$result = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione'.$n]);
							if ($result['id_colture'] <> $form['id_colture']) { // se è stato cambiato lo aggiorno
								$query = "UPDATE " . $gTables['campi'] . " SET id_colture = '" . $form['id_colture'] . "' WHERE codice ='" . $form['campo_coltivazione'.$n] . "'";
								gaz_dbi_query($query);
							}
						}
						// fine aggiorna campi coltura
					
					} // fine ciclo i campi inseriti
					
                    // INIZIO gestione registrazione database operai
                    If (intval($form['staff'][$form['mov']]) > 0) {
                        $id_worker = $form['staff'][$form['mov']]; //identificativo operaio
                        $form['datdocin']; // questa è la data documento iniziale
                        $work_day = $form['anndoc'] . "-" . $form['mesdoc'] . "-" . $form['giodoc']; // giorno lavorato
                        $hours_form = $form['quanti'][$form['mov']]; //ore lavorate normali del form
                        $id_orderman = $form['id_orderman'];
                        // controllo se è una variazione movimento e se è stato cambiato l'operaio
                        $res2 = gaz_dbi_get_row($gTables['staff'], "id_clfoco", $form['clfocoin']);
                        If ($toDo == "update" && $res2['id_staff'] <> $id_worker) { // se è stato cambiato l'operaio
                            If (strtotime($work_day) == strtotime($form['datdocin'])) { // se non è stata cambiata la data documento
                                // all'operaio iniziale, cioè quello che è stato sostituito, devo togliere le ore
                                $rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $res2['id_staff'], "AND work_day = '$work_day'");
                                If (isset($rin)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate e tolgo quelle odierne
                                    $hours_normal = $rin['hours_normal'] - $form['quantiin']; // e faccio l'UPDATE - NON tocco id_orderman ma ATTENZIONE
                                    // la gestione della tabella "staff_worked_hours" sarebbe da rivedere perché non contempla che un operaio possa lavorare a più produzioni (id_orderman) nello stesso giorno !!!
                                    $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff =' . $res2['id_staff'] . ", work_day = '" . $work_day . "', hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $res2['id_staff'] . "' AND work_day = '" . $work_day . "'";
                                    gaz_dbi_query($query);
                                }
                                // al nuovo operaio devo aggiungere le ore lavorate
                                $r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $id_worker, "AND work_day = '$work_day'");
                                If (isset($r)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate
                                    $ore_lavorate = $r['hours_normal'];
                                } else {
                                    $ore_lavorate = 0;
                                }
                                $hours_normal = $ore_lavorate + $hours_form;
                                // salvo ore su operaio attuale
                                $exist = gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = " . $id_worker);
                                if ($exist >= 1) { // se ho già un record del lavoratore per quella data faccio UPDATE
                                    $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff =' . $id_worker . ", id_orderman = '" . $id_orderman . "', work_day = '" . $work_day . "', hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $id_worker . "' AND work_day = '" . $work_day . "'";
                                    gaz_dbi_query($query);
                                } else { // altrimenti faccio l'INSERT
                                    $v = array();
                                    $v['id_staff'] = $id_worker;
                                    $v['work_day'] = $work_day;
                                    $v['hours_normal'] = $hours_normal;
                                    $v['id_orderman'] = $id_orderman;
                                    gaz_dbi_table_insert('staff_worked_hours', $v);
                                }
                            } else { // se è stata cambiata la data documento (giorno lavorato)
                                // all'operaio iniziale, cioè quello che è stato sostituito, devo togliere le ore nel giorno iniziale
                                $rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $res2['id_staff'], "AND work_day = '{$form['datdocin']}'");
                                If (isset($rin)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate e tolgo quelle odierne
                                    $hours_normal = $rin['hours_normal'] - $form['quantiin']; // e faccio l'UPDATE - NON tocco id_orderman
                                    $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff =' . $res2['id_staff'] . ", work_day = '" . $form['datdocin'] . "', hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $res2['id_staff'] . "' AND work_day = '" . $form['datdocin'] . "'";
                                    gaz_dbi_query($query);
                                }
                                // al nuovo operaio devo aggiungere le ore lavorate nel giorno del documento
                                $r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $id_worker, "AND work_day = '$work_day'");
                                If (isset($r)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate
                                    $ore_lavorate = $r['hours_normal'];
                                } else {
                                    $ore_lavorate = 0;
                                }
                                $hours_normal = $ore_lavorate + $hours_form;
                                // salvo ore su operaio attuale
                                $exist = gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = " . $id_worker);
                                if ($exist >= 1) { // se ho già un record del lavoratore per quella data faccio UPDATE
                                    $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff =' . $id_worker . ", id_orderman = '" . $id_orderman . "', work_day = '" . $work_day . "', hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $id_worker . "' AND work_day = '" . $work_day . "'";
                                    gaz_dbi_query($query);
                                } else { // altrimenti faccio l'INSERT
                                    $v = array();
                                    $v['id_staff'] = $id_worker;
                                    $v['work_day'] = $work_day;
                                    $v['hours_normal'] = $hours_normal;
                                    $v['id_orderman'] = $id_orderman;
                                    gaz_dbi_table_insert('staff_worked_hours', $v);
                                }
                            }
                        } else {
                            If ($toDo == "update" && $res2['id_staff'] == $id_worker) { // se è update e NON è stato cambiato l'operaio
                                If (strtotime($work_day) <> strtotime($form['datdocin'])) { // se è stata cambiata la data
                                    // devo togliere le ore al giorno iniziale e metterle nel giorno del documento
                                    // tolgo le ore al giorno iniziale
                                    $rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $id_worker, "AND work_day = '{$form['datdocin']}'");
                                    If (isset($rin)) { // se esiste giorno e operaio gli modifico le ore
                                        $hours_normal = $rin['hours_normal'] - $form['quantiin'];
                                        $query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $id_worker . "' AND work_day = '" . $form['datdocin'] . "'";
                                        gaz_dbi_query($query);
                                    }
                                    // metto le ore del form nel giorno del documento
                                    $rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $id_worker, "AND work_day ='$work_day'");
                                    If (isset($rin)) { // se esiste giorno e operaio gli modifico le ore
                                        $hours_normal = $rin['hours_normal'] + $hours_form;
                                        $query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $id_worker . "' AND work_day = '" . $work_day . "'";
                                        gaz_dbi_query($query);
                                    } else { // altrimenti faccio l'INSERT
                                        $v = array();
                                        $v['id_staff'] = $id_worker;
                                        $v['work_day'] = $work_day;
                                        $v['hours_normal'] = $hours_form;
                                        $v['id_orderman'] = $id_orderman;
                                        gaz_dbi_table_insert('staff_worked_hours', $v);
                                    }
                                } else { //se NON è stata cambiata la data
                                    // modifico le ore nello stesso giorno del documento
                                    $rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $id_worker, "AND work_day ='$work_day'");
                                    If (isset($rin)) { // se esiste giorno e operaio gli modifico le ore
                                        $hours_normal = $rin['hours_normal'] - $form['quantiin'] + $hours_form;
                                        $query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $id_worker . "' AND work_day = '" . $work_day . "'";
                                        gaz_dbi_query($query);
                                    }
                                }
                            }
                        }
                        If ($toDo <> "update") { // se non è un update
                            $r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff", $id_worker, "AND work_day ='$work_day'");
                            If (isset($r)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate
                                $ore_lavorate = $r['hours_normal'];
                            } else {
                                $ore_lavorate = 0;
                            }
                            If ($toDo == "update") {
                                $hours_normal = $ore_lavorate - $form['quantiin'] + $hours_form;
                            } else {
                                $hours_normal = $ore_lavorate + $hours_form;
                            }
                            // salvo ore su operaio attuale
                            $exist = gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = " . $id_worker);
                            if ($exist >= 1) { // se ho già un record del lavoratore per quella data faccio UPDATE
                                $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff =' . $id_worker . ", id_orderman = '" . $id_orderman . "', work_day = '" . $work_day . "', hours_normal = '" . $hours_normal . "' WHERE id_staff = '" . $id_worker . "' AND work_day = '" . $work_day . "'";
                                gaz_dbi_query($query);
                            } else { // altrimenti faccio l'INSERT
                                $v = array();
                                $v['id_staff'] = $id_worker;
                                $v['work_day'] = $work_day;
                                $v['hours_normal'] = $hours_normal;
                                $v['id_orderman'] = $id_orderman;
                                gaz_dbi_table_insert('staff_worked_hours', $v);
                            }
                        }
                    }
                    // FINE gestione registrazione database operai
                    
                } // fine se c'è un articolo impostato nel movimento
            } //fine ciclo for mov
            header("Location:report_movmag.php");
            exit;
        }
    }
    // §§§§§§§§§§§§§§§§§§§§  FINE salvataggio sui database §§§§§§§§§§§§§§§§§§§
    
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['hidden_req'] = '';
    //registri per il form della testata
    $form['id_mov'] = 0;
    $form['type_mov'] = 1;
    $form['gioreg'] = date("d");
    $form['mesreg'] = date("m");
    $form['annreg'] = date("Y");
    $form['caumag'] = "";
	$res_caumag = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
	$form['ncamp']=1;
    $form['campo_coltivazione1']= ""; //campo di coltivazione
    $form['clfocoin'] = 0;
    $form['quantiin'] = 0;
    $form['datdocin'] = "";
    $form['adminid'] = "Utente connesso";
    $form['tipdoc'] = "";
    $form['desdoc'] = "";
    $form['giodoc'] = date("d");
    $form['mesdoc'] = date("m");
    $form['anndoc'] = date("Y");
    $form['scochi'] = 0;
    $form['id_colture'] = 0;
    $form['nome_colt'] = "";
    $form['mov'] = 0;
    $form['operat'] = "";
    $form['nome_avv'][$form['mov']] = "";
    $form['id_avversita'][$form['mov']] = 0;
    $form['artico'][$form['mov']] = "";
	$form['cosear'][$form['mov']] = "";
    $form['id_lotmag'][$form['mov']] = 0;
    $form['identifier'][$form['mov']] = "";
    $form['expiry'][$form['mov']] = "";
    $form['filename'][$form['mov']] = "";
    $form['lot_or_serial'][$form['mov']] = "";
    $form['quanti'][$form['mov']] = 0;
    $form['prezzo'][$form['mov']] = 0;
    $form['scorig'][$form['mov']] = 0;
    $form['clfoco'][$form['mov']] = 0;
    $form['status'] = "";
    $form['search_partner'] = "";
    $form['search_item'] = "";
    $form['id_rif'] = 0;
    $form['description'] = "";
    $form['id_orderman'] = 0;
	$form['coseprod']="";
    $form['nmov'] = 0;
    $form['staff'][$form['mov']] = "";
}
// Antonio Germani questo serve per aggiungere o togliere un movimento
if (isset($_POST['Add_mov'])) {
    $form['nmov'] = $_POST['nmov'];
    for ($m = 0;$m <= $form['nmov'];++$m) {
        $form['artico'][$m] = $_POST['artico' . $m];
		$form['cosear'][$m] = $_POST['artico' . $m];
        $form['id_lotmag'][$m] = $_POST['id_lotmag' . $m];
        $form['lot_or_serial'][$m] = $_POST['lot_or_serial' . $m];
        if ($form['lot_or_serial'][$m] == 1) {
            $form['identifier'][$m] = $_POST['identifier' . $m];
            $form['expiry'][$m] = $_POST['expiry' . $m];
            $form['filename'][$m] = $_POST['filename' . $m];
        } else {
            $form['identifier'][$m] = "";
            $form['expiry'][$m] = "";
            $form['filename'][$m] = "";
        }
        $form['quanti'][$m] = gaz_format_quantity($_POST['quanti' . $m], 0, $admin_aziend['decimal_quantity']);
        $form['prezzo'][$m] = gaz_format_quantity($_POST['prezzo' . $m], 0, $admin_aziend['decimal_quantity']);
        $form['scorig'][$m] = $_POST['scorig' . $m];
        $form['staff'][$m] = intval($_POST['staff' . $m]);
        $form['clfoco'][$m] = $_POST['clfoco' . $m];
        $form['nome_avv'][$m] = $_POST['nome_avv' . $m];
        $form['id_avversita'][$m] = $_POST['id_avversita' . $m];
    }
    $form['nmov'] = $form['nmov'] + 1;
    $form['artico'][$form['nmov']] = "";
	$form['cosear'][$form['nmov']] = "";
    $form['id_lotmag'][$form['nmov']] = 0;
    $form['identifier'][$form['nmov']] = "";
    $form['expiry'][$form['nmov']] = "";
    $form['filename'][$form['nmov']] = "";
    $form['lot_or_serial'][$form['nmov']] = "";
    $form['quanti'][$form['nmov']] = 0;
    
    $form['prezzo'][$form['nmov']] = 0;
    $form['scorig'][$form['nmov']] = 0;
    $form['staff'][$form['nmov']] = "";
    $form['clfoco'][$form['nmov']] = 0;
    $form['nome_avv'][$form['nmov']] = "";
    $form['id_avversita'][$form['nmov']] = 0;
}
if (isset($_POST['Del_mov'])) {
    $form['artico'][$form['nmov']] = "";
	$form['cosear'][$form['nmov']] = "";
    $form['id_lotmag'][$form['nmov']] = 0;
    $form['identifier'][$form['nmov']] = "";
    $form['expiry'][$form['nmov']] = "";
    $form['filename'][$form['nmov']] = "";
    $form['lot_or_serial'][$form['nmov']] = "";
    $form['quanti'][$form['nmov']] = 0;
    
    $form['prezzo'][$form['nmov']] = 0;
    $form['scorig'][$form['nmov']] = 0;
    $form['staff'][$form['nmov']] = "";
    $form['clfoco'][$form['nmov']] = 0;
    $form['nome_avv'][$form['nmov']] = "";
    $form['id_avversita'][$form['nmov']] = 0;
    If ($_POST['nmov'] > 0) {
        $form['nmov'] = $form['nmov'] - 1;
        $form['mov'] = $form['mov'] - 1;
    }
}
if (!empty($_FILES['docfile_' . $form['mov']]['name'])) { // Antonio Germani - se c'è un nome in $_FILES
    $prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $form['mov'];
    foreach (glob("../../data/files/tmp/" . $prefix . "_*.*") as $fn) { // prima cancello eventuali precedenti file temporanei
        unlink($fn);
    }
    $mt = substr($_FILES['docfile_' . $form['mov']]['name'], -3);
    if (($mt == "png" || $mt == "odt" || $mt == "peg" || $mt == "jpg" || $mt == "pdf") && $_FILES['docfile_' . $form['mov']]['size'] > 1000) { // se rispetta limiti e parametri lo salvo nella cartella tmp
        move_uploaded_file($_FILES['docfile_' . $form['mov']]['tmp_name'], '../../data/files/tmp/' . $prefix . '_' . $_FILES['docfile_' . $form['mov']]['name']);
        $form['filename'][$form['mov']] = $_FILES['docfile_' . $form['mov']]['name'];
    } else {
        $msg.= "39+";
    }
}
if (isset($_POST['acquis'])) { //compilazione ordine a fornitore
    $item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
    $scorta = $item['scorta']; // prendo la scorta minima
    $fornitore = $item['clfoco']; //prendo codice clfoco per codice fornitore per ordine a fornitore
    if (isset($fornitore)) { ?>
<form action="../../modules/acquis/admin_broacq.php?tipdoc=AOR" method="post" name="docacq">
	<input type="hidden" name="Insert" value="insert">
	<input type="hidden" value="AOR" name="tipdoc">
	<input type="hidden" name="clfoco" value="<?php echo $fornitore; ?>"> <!-- questo è il fornitore che devo prendere dalla tabella atico, colonna clfoco se è zero devo bloccare perché non è stato inserito il fornitore nell'articolo -->
	<input type="hidden" name="search[clfoco]" value="<?php echo $fornitore; ?>">
	<input type="hidden" name="gioemi" value="<?php echo date('d'); ?>"> <!-- giorno -->
	<input type="hidden" name="mesemi" value="<?php echo date('m'); ?>"> <!-- mese -->
	<input type="hidden" name="annemi" value="<?php echo date('Y'); ?>"><!-- anno -->
	<input type="hidden" value="INSERT" name="in_status">
	<input type="hidden" name="in_codart" value="<?php echo $form['artico'][$form['mov']]; ?>">
	<input type="hidden" name="cosear" value="<?php echo $form['artico'][$form['mov']]; ?>">
	<input type="hidden" value="<?php echo $scorta; ?>"  name="in_quanti"> 
	<input type="hidden" name="in_codric" value="330000004">
	<input type="hidden" value="<?php echo $form['artico'][$form['mov']]; ?>" name="codart">
	<script type="text/javascript" >
		document.forms["docacq"].submit(); 
	</script>
</form>
<?php
    } else { ?>
<div class="alert alert-warning alert-dismissible">
	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	<strong>Warning!</strong> Non è possibile riordinare; il prodotto non è ordinabile o non è inserito il fornitore!
</div>
<?php
    }
}

If (isset($_POST['cancel'])) {// se è stato premuto annulla
    $form['hidden_req'] = ''; 
    //registri per il form della testata
    $form['id_mov'] = 0;
    $form['type_mov'] = 1;
    $form['gioreg'] = date("d");
    $form['mesreg'] = date("m");
    $form['annreg'] = date("Y");
    $form['caumag'] = "";
    $form['campo_coltivazione'] = ""; //campo di coltivazione
    $form['adminid'] = "Utente connesso";
    $form['tipdoc'] = "";
    $form['desdoc'] = "";
    $form['giodoc'] = date("d");
    $form['mesdoc'] = date("m");
    $form['anndoc'] = date("Y");
    $form['scochi'] = 0;
    $form['id_avversita'][$form['mov']] = 0;
    $form['nome_avv'][$form['mov']] = "";
    $form['id_colture'] = 0;
    $form['nome_colt'] = "";
    $form['nmov'] = 0;
    $form['mov'] = 0;
    $form['operat'] = "";
    $form['artico'][$form['mov']] = "";
    $form['id_lotmag'][$form['mov']] = 0;
    $form['identifier'][$form['mov']] = "";
    $form['expiry'][$form['mov']] = "";
    $form['filename'][$form['mov']] = "";
    $form['lot_or_serial'][$form['mov']] = "";
    $form['prezzo'][$form['mov']] = 0;
    $form['scorig'][$form['mov']] = 0;
    $form['quanti'][$form['mov']] = 0;
    $form['staff'][$form['mov']] = "";
    $form['clfoco'][$form['mov']] = 0;
    $form['clfocoin'] = 0;
    $form['quantiin'] = 0;
    $form['datdocin'] = "";
    $form['status'] = "";
    $form['search_partner'] = "";
    $form['search_item'] = "";
    $form['id_rif'] = 0;
    $form['description'] = "";
    $form['id_orderman'] = 0;
	$form['coseprod']="";
    $fornitore = "";
}
require ("../../library/include/header.php");
$script_transl = HeadMain(0,array('custom/autocomplete',));
require ("./lang." . $admin_aziend['lang'] . ".php");
if ($form['id_mov'] > 0) {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0]) . " n." . $form['id_mov'];
} else {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0]);
}
//   ***********    inizio FORM            >>>>>>>>>>
echo "<form method=\"POST\" name=\"myform\" enctype=\"multipart/form-data\">";
echo "<input type=\"hidden\" name=\"" . ucfirst($toDo) . "\" value=\"\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"" . $_POST['ritorno'] . "\">\n";
echo "<input type=\"hidden\" name=\"id_mov\" value=\"" . $form['id_mov'] . "\">\n";
echo "<input type=\"hidden\" name=\"nmov\" value=\"" . $form['nmov'] . "\">\n";
echo "<input type=\"hidden\" name=\"id_rif\" value=\"" . $form['id_rif'] . "\">\n";
echo "<input type=\"hidden\" name=\"tipdoc\" value=\"" . $form['tipdoc'] . "\">\n";
echo "<input type=\"hidden\" name=\"status\" value=\"" . $form['status'] . "\">\n";
echo "<input type=\"hidden\" name=\"clfocoin\" value=\"" . $form['clfocoin'] . "\">\n";
echo "<input type=\"hidden\" name=\"quantiin\" value=\"" . $form['quantiin'] . "\">\n";
echo "<input type=\"hidden\" name=\"datdocin\" value=\"" . $form['datdocin'] . "\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">$title</div>\n";
$importo_rigo = CalcolaImportoRigo($form['quanti'][$form['mov']], $form['prezzo'][$form['mov']], $form['scorig'][$form['mov']]);
$importo_totale = CalcolaImportoRigo(1, $importo_rigo, $form['scochi']);
echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice(explode('+', chop($msg)), 0, -1);
    foreach ($rsmsg as $value) {
        $message.= $script_transl['error'] . "! -> ";
        $rsval = explode('-', chop($value));
        foreach ($rsval as $valmsg) {
            $message.= $script_transl[$valmsg] . " ";
        }
        $message.= "<br />";
    }
    echo '<tr><td colspan="3" class="FacetDataTDred">' . $message . "</td></tr>\n";
}

// Inserimento produzione 
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[29] . "</td><td colspan=\"1\" class=\"FacetDataTD\"\n>";
$select_production = new selectproduction("description");
$select_production->addSelected($form['description']);			
$select_production->output($form['coseprod']);

?>
	  
<script>
	var stile = "top=10, left=10, width=600, height=800 status=no, menubar=no, toolbar=no scrollbar=no";
	   function Popup(apri) {
	      window.open(apri, "", stile);
	   }
</script>
<button type="submit" name="erase2" title="Reset produzione" class="btn btn-default"  style="border-radius= 85px; "> <i class="glyphicon glyphicon-remove-circle"></i></button>
<br>
<a href="javascript:Popup('../../modules/orderman/admin_orderman.php?Insert&popup=1&type=AGR')"> Crea nuova produzione <i class="glyphicon glyphicon-plus-sign" style="color:green" ></i></a>
</td></tr>
<?php
/* fine inserisci produzione  */

/*Antonio Germani CAMPO coltivazione  */
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[3] . "</td><td class=\"FacetDataTD\">\n";

	for ($n = 1;$n <= $form['ncamp'];++$n){ // ciclo i campi
		$gForm->selectFromDB('campi', 'campo_coltivazione'.$n ,'codice', $form['campo_coltivazione'.$n], 'codice', 1, ' - ','descri','TRUE','FacetSelect' , null, '');
	}

$form['campo_coltivazione'.$n]="";
if ($n>1 AND $form['campo_coltivazione'.($n-1)]>0){
	$gForm->selectFromDB('campi', 'campo_coltivazione'.$n,'codice', $form['campo_coltivazione'.$n], 'codice', 1, ' - ','descri','TRUE','FacetSelect' , null, '');
}
$form['ncamp']=$n;
echo "<input type=\"hidden\" name=\"ncamp\" value=\"" . $form['ncamp'] . "\">\n</td></tr>";

/* Antonio Germani -  COLTURA */
?>
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql camp_colture	-->	
<script>
	$(document).ready(function() {
	$("input#autocomplete4").autocomplete({
		source: [<?php
$stringa = "";
$query = "SELECT * FROM " . $gTables['camp_colture'];
$result = gaz_dbi_query($query);
while ($row = $result->fetch_assoc()) {
    $stringa.= "\"" . $row['id_colt'] . " - " . $row['nome_colt'] . "\", ";
}
$stringa = substr($stringa, 0, -1);
echo $stringa;
?>],
		minLength:1,
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
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[33] . "</td><td class=\"FacetDataTD\"\n>";
if (intval($form['nome_colt']) == 0) {
    $form['nome_colt'] = "";
} 
?>
<input id="autocomplete4" type="text" value="<?php echo $form['nome_colt']; ?>" name="nome_colt" maxlength="50" size="50"/>
<input type="hidden" value="<?php echo intval($form['nome_colt']); ?>" name="id_colture"/>
<button type="submit" name="erase" title="Reset coltura" class="btn btn-default"  style="border-radius= 85px; "> <i class="glyphicon glyphicon-remove-circle"></i></button>
</td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete4 -->	 
<?php
/* fine coltura */
//   CAUSALE
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[2] . "</td><td class=\"FacetDataTD\">\n";
$gForm->selectFromDB('caumag', 'caumag','codice', $form['caumag'], 'codice', 1, ' - ','descri','TRUE','FacetSelect' , null, '');
if ($res_caumag['operat'] == 0) {
    echo " Non opera";
}
if ($res_caumag['operat'] == 1) {
    echo " Carico";
}
if ($res_caumag['operat'] == - 1) {
    echo " Scarico";
}

echo "</td></tr>";
?>
<?php
// DATA della REGISTRAZIONE
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[1] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"gioreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1;$counter <= 31;$counter++) {
    $selected = "";
    if ($counter == $form['gioreg']) $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1;$counter <= 12;$counter++) {
    $selected = "";
    if ($counter == $form['mesreg']) $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"annreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10;$counter <= date("Y") + 10;$counter++) {
    $selected = "";
    if ($counter == $form['annreg']) $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td></tr>\n";
/* Antonio Germani qui si seleziona la DATA di ATTUAZIONE */
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[8] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"giodoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1;$counter <= 31;$counter++) {
    $selected = "";
    if ($counter == $form['giodoc']) $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesdoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1;$counter <= 12;$counter++) {
    $selected = "";
    if ($counter == $form['mesdoc']) $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"anndoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10;$counter <= date("Y") + 10;$counter++) {
    $selected = "";
    if ($counter == $form['anndoc']) $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td></tr>\n";
// fine data di attuazione
// ANNOTAZIONE o DESCRIZIONE DOCUMENTO
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[9] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['desdoc'] . "\" maxlength=\"50\" size=\"35\" name=\"desdoc\"></td></tr>";

// >>>>>>>>>> Inizio ciclo righi mov   <<<<<<<<<<<<<<<<<
for ($form['mov'] = 0;$form['mov'] <= $form['nmov'];++$form['mov']) {
    $anchor["num"] = $form['mov']; // Antonio Germani imposto la riga che dovrà essere ancorata allo scroll
    $importo_rigo = CalcolaImportoRigo($form['quanti'][$form['mov']], $form['prezzo'][$form['mov']], $form['scorig'][$form['mov']]);
    $importo_totale = CalcolaImportoRigo(1, $importo_rigo, $form['scochi']);
    echo "<tr><td><input type=\"hidden\" name=\"mov\" value=\"" . $form['mov'] . "\">\n";
    echo "<input type=\"hidden\" name=\"scochi\" value=\"" . $form['scochi'] . "\">\n</td></tr>";
    echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">\n";
    $messaggio = "";
    $print_unimis = "";
    $ric_mastro = substr($form['artico'][$form['mov']], 0, 3);
	
	if ($form['mov']==$form['nmov']){ // se è l'ultimo rigo attivo l'auotcomplete
		$select_artico = new selectartico("artico");
		$select_artico->addSelected($form['artico'][$form['mov']]);			
		$select_artico->output(substr($form['cosear'][$form['mov']], 0, 20));
	} else {		
		?>
		<input type="hidden" name="artico<?php echo $form['mov']; ?>" value="<?php echo $form['artico'][$form['mov']]; ?>" />
		<input type="hidden" name="cosear<?php echo $form['mov']; ?>" value="<?php echo $form['cosear'][$form['mov']]; ?>" />
		<input type="hidden" name="cosear" value="<?php echo $form['artico'][$form['mov']]; ?>" />
		<?php
	}
	
    if ($form['artico'][$form['mov']] != "") {
		// carico l'articolo dell'attuale mov in itemart
		$itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
        $print_unimis = $itemart['unimis'];
        $dose = $itemart['dose_massima']; // prendo anche la dose
        $scorta = $itemart['scorta']; // prendo la scorta minima
        $descri = $itemart['descri']; //prendo descrizione articolo
        $form['lot_or_serial'][$form['mov']] = $itemart['lot_or_serial']; //vedo se è un articolo con lotti
        $form['prezzo'][$form['mov']] = $itemart['preacq']; // prendo il prezzo di acquisto
        $service = intval($itemart['good_or_service']); // carico $service per vedere se è articolo o servizio
		if ($form['mov']!=$form['nmov']){ // se non è il movimento attivo visualizzo la descrizione
				echo " ", substr($itemart['descri'], 0, 25), " ";
			}
        If ($service == 0 or $service == 2) { //Antonio Germani se è un articolo con magazzino
            // Antonio Germani calcolo giacenza di magazzino e la metto in $print_magval
            $mv = $gForm->getStockValue(false, $itemart['codice']);
            $magval = array_pop($mv);
            $print_magval = str_replace(",", "", $magval['q_g']);
            if (isset($_POST['Update']) or $toDo == "update") { // se è un update
                $qta = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
                if ($qta['artico'] == $form['artico'][$form['mov']]) { // se l'update è per lo stesso articolo
                    $print_magval = $print_magval + $qta['quanti']; // prendo la quantità precedentemente memorizzata e la riaggiungo alla giacenza di magazzino altrimenti il controllo quantità non funziona bene
                }
            }
			if ($dose > 0) {
                echo "<br>Dose generica: ", gaz_format_quantity($dose, 1, $admin_aziend['decimal_quantity']), " ", $print_unimis, "/ha";
            }
        }
        if ($service == 2 && $form['operat'] == 1) { // se è articolo composito avviso che non è possibile il carico
            echo '<div><button class="btn btn-xs btn-danger" type="image" >';
            echo $script_transl[42];
            echo '</button></div>';
        }
    }
?>
<input type="hidden" name="id_lotmag<?php echo $form['mov']; ?>" value="<?php echo $form['id_lotmag'][$form['mov']]; ?>" />
<input type="hidden" name="lot_or_serial<?php echo $form['mov']; ?>" value="<?php echo $form['lot_or_serial'][$form['mov']]; ?>" />

<?php
?></tr>
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl[12]; ?></td>
	<td class="FacetDataTD" ><input type="text" value="<?php echo gaz_format_quantity($form['quanti'][$form['mov']], 1, $admin_aziend['decimal_quantity']); ?>" maxlength="10" size="10" name="quanti<?php echo $form['mov']; ?>" onChange="this.form.submit()"><?php echo "&nbsp;" . $print_unimis; ?>
		<?php
    if ($service == 0 or $service == 2) { //Antonio Germani se è un articolo con magazzino
        echo " " . $script_transl[22] . " " . gaz_format_quantity($print_magval, 1, $admin_aziend['decimal_quantity']) . " " . $print_unimis . "&nbsp;&nbsp;";
        // Antonio Germani se sottoscorta si attiva il pulsante di allerta e riordino. Al click si apre il popup con l'ordine compilato. >>> NB: al ritorno dall'ordine e dopo un submit, c'è un problema DA RISOLVERE: si apre una nuova finestra. <<< preferisco questo problema a quello che c'era prima, cioè si apriva la pagina dell'ordine annullando quanto già inserito nei movimenti.
        if ($print_magval < $scorta and $service == 0 and $scorta > 0) {
            echo "<button type=\"submit\" name=\"acquis\"  class=\"btn btn-default btn-lg\" title=\"Sottoscorta, riordinare\" onclick=\"myform.target='POPUPW'; POPUPW = window.open(
			  'about:blank','POPUPW','width=800,height=400');\" style=\"background-color:red\"><span class=\"glyphicon glyphicon-alert\" aria-hidden=\"true\"></span></button>";
        }
        if ($print_magval < $scorta and $service == 2 and $scorta > 0) {
            echo "<button type=\"submit\" name=\"acquis\"  class=\"btn btn-default btn-lg\" title=\"Sottoscorta, riordinare\" onclick=\"myform.target='POPUPW'; POPUPW = window.open(
			  'about:blank','POPUPW','width=800,height=400');\" style=\"background-color:red\"><span class=\"glyphicon glyphicon-alert\" aria-hidden=\"true\"></span></button>";
        }
?>
	</td>
</tr>
<?php 
        // Antonio Germani --- inizio gestione form  LOTTI	in uscita
        if (($form['lot_or_serial'][$form['mov']] > 0) && ($form['operat'] == - 1)) {
?>
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl[41]; ?></td>
	<td class="FacetDataTD" >
		<input type="hidden" name="filename<?php echo $form['mov']; ?>" value="<?php echo $form['filename'][$form['mov']]; ?>">			
		<?php
            $lm->getAvailableLots($form['artico'][$form['mov']], $form['id_mov']);
            $ld = $lm->divideLots($form['quanti'][$form['mov']]);
            if ($ld > 0) {
                echo "ERRORE ne mancano: ", $ld, "<br>"; // >>>>> quantità insufficiente - riaggiorno form[quanti] con quello che c'è !!
                $form['quanti'][$form['mov']] = $form['quanti'][$form['mov']] - $ld;
?>
		<input type="hidden" name="quanti<?php echo $form['mov']; ?>" value="<?php echo $form['quanti'][$form['mov']]; ?>">
		<?php
            }
			
				// Antonio Germani - calcolo delle giacenze per ogni singolo lotto in $count['identifier']
			$count=array();
			foreach ($lm->available as $v_lm) {
				$key=$v_lm['identifier']; // chiave per il conteggio dei totali raggruppati per lotto 
				if( !array_key_exists($key, $count) ){ // se la chiave ancora non c'è nell'array
					// Aggiungo la chiave con il rispettivo valore iniziale
					$count[$key] = $v_lm['rest'];
				} else {
					// Altrimenti, aggiorno il valore della chiave
					$count[$key] += $v_lm['rest'];
				}
			}
			
			if (isset($form['id_lotmag'][$form['mov']]) && $form['id_lotmag'][$form['mov']] == 0)  {
                $l = $form['mov']; // ripartisco la quantità introdotta tra i vari lotti disponibili per l'articolo
                // e se è il caso creo più righe
                foreach ($lm->divided as $k => $v) {
                    if ($v['qua'] >= 0.00001) {
                        $form['id_lotmag'][$l] = $v['id']; // setto il lotto
                        $form['quanti'][$l] = $v['qua']; // e la quantità in base al riparto
                        ?>
						<input type="hidden" name="id_lotmag<?php echo $l; ?>" value="<?php echo $form['id_lotmag'][$l]; ?>">
						<input type="hidden" name="quanti<?php echo $l; ?>" value="<?php echo $form['quanti'][$l]; ?>">
						<input type="hidden" name="artico<?php echo $l; ?>" value="<?php echo $form['artico'][$form['mov']]; ?>">
						<input type="hidden" name="scorig<?php echo $l; ?>" value="<?php echo $form['scorig'][$form['mov']]; ?>">
						<input type="hidden" name="clfoco<?php echo $l; ?>" value="<?php echo $form['clfoco'][$form['mov']]; ?>">
						<input type="hidden" name="nome_avv<?php echo $l; ?>" value="<?php echo $form['nome_avv'][$form['mov']]; ?>">
						<input type="hidden" name="id_avversita<?php echo $l; ?>" value="<?php echo $form['id_avversita'][$form['mov']]; ?>">
						<input type="hidden" name="staff<?php echo $l; ?>" value="<?php echo $form['staff'][$form['mov']]; ?>">
						<input type="hidden" name="prezzo<?php echo $l; ?>" value="<?php echo gaz_format_quantity($form['prezzo'][$form['mov']], 1, $admin_aziend['decimal_quantity']); ?>">
						<input type="hidden" name="identifier<?php echo $l; ?>" value="">
						<input type="hidden" name="expiry<?php echo $l; ?>" value="">
						<input type="hidden" name="lot_or_serial<?php echo $l; ?>" value="<?php echo $form['lot_or_serial'][$form['mov']]; ?>">
						<input type="hidden" name="filename<?php echo $l; ?>" value="<?php echo $form['filename'][$form['mov']]; ?>">
						<?php
                        $l++;
                    }
                }
				?>		
				</div>	
				<?php
            }
		
            if (isset($form['id_lotmag'][$form['mov']]) && $form['id_lotmag'][$form['mov']] > 0) {
                $selected_lot = $lm->getLot($form['id_lotmag'][$form['mov']]);
				
                echo '<div><button class="btn btn-xs btn-success" title="clicca per cambiare lotto" type="image"  data-toggle="collapse" href="#lm_dialog' . $form['mov'] . '">' . $selected_lot['id'] . ' lotto n.:' . $selected_lot['identifier'];
                if (intval($form['expiry'][$form['mov']]) > 0) {
                    echo ' scadenza:' . gaz_format_date($selected_lot['expiry']);
                }
				if (!isset($count[$selected_lot['identifier']])) {
					echo "<br><b> LOTTO IN ERRORE!!!</b><br>Probabilmente manca il movimento madre.";
				} else {
					echo ' - disponibili: ' . gaz_format_quantity($count[$selected_lot['identifier']]) . '<i class="glyphicon glyphicon-tag"></i></button>';
				}
				?>
				<input type="hidden" name="id_lotmag<?php echo $form['mov']; ?>" value="<?php echo $form['id_lotmag'][$form['mov']]; ?> ">
				<?php
            } 
			?>
			<!-- Antonio Germani - Cambio lotto solo se c'è una sola riga di movimento altrimenti si rischia un errore inserendo due volte lo stesso lotto -->
			<div id="lm_dialog<?php echo $form['mov']; ?>" class="collapse" >
				<div class="form-group">
					<?php 
				if ((count($lm->available) > 1) && (intval($form['nmov']) == 0)) {
					foreach ($lm->available as $v_lm) {
						if ($v_lm['id'] <> $form['id_lotmag'][$form['mov']]) {
							echo '<div>change to:<button class="btn btn-xs btn-warning" type="image" onclick="this.form.submit();" name="id_lotmag' . $form['mov'] . '" value="' . $v_lm['id'] . '">' . $v_lm['id'] . ' lotto n.:' . $v_lm['identifier'] . ' scadenza:' . gaz_format_date($v_lm['expiry']) . ' - disponibili: ' . gaz_format_quantity($count[$v_lm['identifier']]) . '</button></div>';
						}
					}
				} else {
					echo '<div><button class="btn btn-xs btn-danger" type="image" >Non sono disponibili altri lotti, <br> oppure non è possibile cambiare lotto negli inserimenti multipli</button></div>';
				}
				?>
				</div>
			</div>
			</div>
	</td>
</tr>
<?php
        }
        // Fine  LOTTI	in uscita
        
?>  
<script>
	$(function() {
	  $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
	});
</script>
<?php
        // Inizio LOTTI in entrata
        if (($form['lot_or_serial'][$form['mov']] > 0) && ($form['operat'] == 1)) {
            $idlotcontroll = gaz_dbi_get_row($gTables['lotmag'], "id", $form['id_lotmag'][$form['mov']]); // in $idlotcontroll['id_movmag'] ho l id del movimento madre che ha generato il lotto
            
?>
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl[41];
            echo "<br> <p> <font color=blue size='1pt'> N.b.: è possibile modificare <br> il lotto solo dal movimento<br> che lo ha creato </font></p>"; ?></td>
	<td class="FacetDataTD" >
		<input type="hidden" name="filename<?php echo $form['mov']; ?>" value="<?php echo $form['filename'][$form['mov']]; ?>">			  
		<?php
            if (intval($form['id_mov']) > 0 or intval($form['id_mov']) == intval($idlotcontroll['id_movmag'])) { // attiva inserimento certificato in alcuni casi
                if (strlen($form['filename'][$form['mov']]) == 0) {
                    echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog' . $form['mov'] . '">' . 'Inserire nuovo certificato' . ' ' . '<i class="glyphicon glyphicon-tag"></i>' . '</button></div>';
                } else {
                    echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog' . $form['mov'] . '">' . $form['filename'][$form['mov']] . ' ' . '<i class="glyphicon glyphicon-tag"></i>' . '</button>';
                    if ($toDo == "update") {
                        foreach (glob("../../modules/camp/tmp/*") as $fn) { // prima cancello eventuali precedenti file temporanei
                            unlink($fn);
                        }
                        if (strlen($form['filename'][$form['mov']]) > 0) {
                            $tmp_file = "../../data/files/" . $admin_aziend['company_id'] . "/" . $form['filename'][$form['mov']];
                            // sposto nella cartella di lettura il relativo file temporaneo
                            copy($tmp_file, "../../modules/camp/tmp/" . $form['filename'][$form['mov']]);
                        }
						?>
						<a  class="btn btn-info btn-md" href="javascript:;" onclick="window.open('<?php echo "../../modules/camp/tmp/" . ($form['filename'][$form['mov']]) ?>', 'titolo', 'width=800, height=400, left=80%, top=80%, resizable, status, scrollbars=1, location');">
						<span class="glyphicon glyphicon-eye-open"></span></a></div>
						<?php
                    } else {
                        echo '</div>';
                    }
                }
            }
            // Esclusione del movimento Madre dalla scelta fra i lotti esistenti
            // se questo è il movimento che ha generato il lotto in lotmag (movimento madre), allora escludo la scelta fra quelli esistenti, così si ha la possibilità di modificare il lotto su lotmag
            $idlotcontroll = gaz_dbi_get_row($gTables['lotmag'], "id", $form['id_lotmag'][$form['mov']]);
            if ((intval($form['id_mov']) == 0) or (intval($form['id_mov']) <> intval($idlotcontroll['id_movmag']))) {
                /*Antonio Germani scelta lotto fra quelli esistenti  */
                $query = "SELECT " . '*' . " FROM " . $gTables['lotmag'] . " WHERE codart ='" . $form['artico'][$form['mov']] . "'";
                $result = gaz_dbi_query($query);
                if ($result->num_rows > 0) { // se ci sono lotti attivo la selezione
                    echo '<select name="id_lotmag' . $form['mov'] . '" class="FacetSelect" onchange="this.form.submit()">\n';
                    echo "<option value=\"\">-seleziona fra lotti esistenti-</option>\n";
                    $sel = 0;
                    while ($rowlot = gaz_dbi_fetch_array($result)) {
                        $selected = "";
                        if ($form['id_lotmag'][$form['mov']] == $rowlot['id']) {
                            $selected = " selected ";
                            $sel = 1;
                        }
                        echo "<option value=\"" . $rowlot['id'] . "\"" . $selected . ">" . $rowlot['id'] . " - " . $rowlot['identifier'] . " - " . gaz_format_date($rowlot['expiry']) . "</option>\n";
                    }
                    echo "</select>&nbsp;";
                    If ((intval($form['id_lotmag'][$form['mov']]) > 0) && (intval($sel) == 1)) { // se è stato selezionato un lotto
                        $rowlot = gaz_dbi_get_row($gTables['lotmag'], "id", $form['id_lotmag'][$form['mov']]);
                        $form['identifier'][$form['mov']] = $rowlot['identifier'];
                        $form['expiry'][$form['mov']] = $rowlot['expiry'];
                    }
                }
                // fine scelta lotto fra esistenti
                
            } // fine (esclusione del movimento madre)
            if (strlen($form['identifier'][$form['mov']]) == 0) {
                echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog_lot' . $form['mov'] . '">' . 'Inserire nuovo Lotto' . " " . '<i class="glyphicon glyphicon-tag"></i></button></div>';
            } else {
                if (intval($form['expiry'][$form['mov']]) > 0) {
                    echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot' . $form['mov'] . '">' . $form['identifier'][$form['mov']] . " " . gaz_format_date($form['expiry'][$form['mov']]) . " " . '<i class="glyphicon glyphicon-tag"></i></button></div>';
                } else {
                    echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot' . $form['mov'] . '">' . $form['identifier'][$form['mov']] . " " . '<i class="glyphicon glyphicon-tag"></i></button></div>';
                }
            }
            if (intval($form['id_mov']) == intval($idlotcontroll['id_movmag'])) {
                echo '<div id="lm_dialog' . $form['mov'] . '" class="collapse" >
			                   <div class="form-group">
			                     <div>';
?>
		<input type="file" onchange="this.form.submit();" name="docfile_<?php echo $form['mov']; ?>">
		</div>
		</div>
		</div>
		<?php
            }
            echo '<div id="lm_dialog_lot' . $form['mov'] . '" class="collapse" >
			                   <div class="form-group">
			                     <div>';
            echo '<label>' . "Numero: " . '</label><input type="text" name="identifier' . $form['mov'] . '" value="' . $form['identifier'][$form['mov']] . '" >';
            echo "<br>";
            echo '<label>' . "Scadenza: " . ' </label><input class="datepicker" type="text" onchange="this.form.submit();" name="expiry' . $form['mov'] . '"  value="' . $form['expiry'][$form['mov']] . '" >
			
			</div>
			  </div>
			         </div>' . "\n";
        } else {
            echo '<tr><td> <input type="hidden" name="identifier' . $form['mov'] . '" value="' . $form['identifier'][$form['mov']] . '" />';
            echo ' <input type="hidden" name="expiry' . $form['mov'] . '" value="' . $form['expiry'][$form['mov']] . '" />';
            echo ' <input type="hidden" name="filename' . $form['mov'] . '" value="' . $form['filename'][$form['mov']] . '" /></td></tr>';
        }
        // fine LOTTI in entrata
        
?>
<tr>
	<td>	
		<input type="hidden" name="clfoco<?php echo $form['mov']; ?>" value="<?php $form['clfoco'][$form['mov']]; ?>">
		<input type="hidden" name="staff<?php echo $form['mov']; ?>" value="">
	</td>
</tr>
<?php
    } else { // se è articolo senza magazzino
        
?>
<input type="hidden" name="staff<?php echo $form['mov']; ?>" value="<?php echo $form['staff'][$form['mov']]; ?>">
<?php
        /*Antonio Germani se l'unità di misura è oraria attiva Operaio */
        if ($print_unimis == "h") {
            echo "&nbsp;&nbsp;" . $script_transl[32] . "&nbsp;";
			$g2Form->selectFrom2DB('staff','clfoco','codice','descri', 'staff'.$form['mov'],'id_staff', $form['staff'][$form['mov']], 'id_staff', 1, ' - ','id_clfoco','TRUE','FacetSelect' , null, '');
        }		
?>
<input type="hidden" name="clfoco<?php echo $form['mov']; ?>" value="<?php echo $itm['id_clfoco']; ?>"></td></tr>
<?php
    }
    /* Antonio Germani -  AVVERSITà */
    if ($print_unimis <> "h") { // se è una lavorazione agricola disattivare avversità
        
?>
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql camp_avversita	-->	
<script>
	$(document).ready(function() {
	$("input#autocomplete3").autocomplete({
		source: [<?php
        $stringa = "";
        $query = "SELECT * FROM " . $gTables['camp_avversita'];
        $result = gaz_dbi_query($query);
        while ($row = $result->fetch_assoc()) {
            $stringa.= "\"" . $row['id_avv'] . " - " . $row['nome_avv'] . "\", ";
        }
        $stringa = substr($stringa, 0, -1);
        echo $stringa;
?>],
		minLength:1,
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
        echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[20] . "</td><td class=\"FacetDataTD\"\n>";
        if (intval($form['nome_avv'][$form['mov']]) == 0) {
            $form['nome_avv'][$form['mov']] = "";
        }
?>
<input class="col-sm-4" id="autocomplete3" type="text" value="<?php echo $form['nome_avv'][$form['mov']]; ?>" name="nome_avv<?php echo $form['mov']; ?>" maxlength="15" />
<input type="hidden" value="<?php echo intval($form['nome_avv'][$form['mov']]); ?>" name="id_avversita<?php echo $form['mov']; ?>"/>
<?php 
            if ($dose_usofito > 0) {
                echo "Dose specifica: ", gaz_format_quantity($dose_usofito, 1, $admin_aziend['decimal_quantity']), " ", $print_unimis, "/ha";
            }
        
?>
</td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete3 -->	 
<?php
    } else {
?>
<tr>
	<td>
		<input type="hidden" value="" name="nome_avv<?php echo $form['mov']; ?>"/>
		<input type="hidden" value="" name="id_avversita<?php echo $form['mov']; ?>"/>
	</td>
</tr>
<?php
    }
    /* fine avversità */
    $print_magval = "";
    $scorta = "";
    $dose = ""; // le azzero perché altrimenti me le ritrovo nell'eventuale movimento/riga successivo
    /* Antonio Germani  prezzo e lo sconto del rigo movimento */
    $importo_totale = ($form['prezzo'][$form['mov']] * floatval(preg_replace("/\,/", '.', $form['quanti'][$form['mov']]))) - ((($form['prezzo'][$form['mov']] * floatval(preg_replace("/\,/", '.', $form['quanti'][$form['mov']]))) * $form['scorig'][$form['mov']]) / 100);
?>
<!-- COSTO MOVIMENTO  -->
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl[13]; ?></td>
	<td class="FacetDataTD" colspan="1"><input type="text" value="<?php echo number_format($importo_totale, $admin_aziend['decimal_price'], ',', ''); ?>" name="total" size="20" readonly /><?php echo "&nbsp;" . $admin_aziend['symbol'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $script_transl[31]; ?>
		<input type="text" value="<?php echo number_format($form['prezzo'][$form['mov']], $admin_aziend['decimal_price'], ',', '') ?>" maxlength="12" size="12" name="prezzo<?php echo $form['mov'] ?>" onChange="this.form.submit()"><?php echo " " . $admin_aziend['symbol']; ?>
		<input type="hidden" value="<?php echo $form['scorig'][$form['mov']]; ?>" maxlength="4" size="4" name="scorig<?php echo $form['mov'] ?>" onChange="this.form.submit()">
	</td>
</tr>
<tr>
	<td style="font-size:5pt;" colspan="4">
		<?php echo $form['mov'] + 1; ?>
		<a name="<?php echo $form['mov']; ?>"></a> <!-- Antonio Germani Questa è l'ancora dello scroll -->	
<?php
}
$form['mov'] = $form['nmov'];
if (isset($l) && $l - 1 > $form['mov']) { // se la suddivisione dei lotti ha creato nuovi righi aggiorno il numero totale dei righi
    $form['nmov'] = $form['nmov'] + ($l - 1);
}
echo "<input type=\"hidden\" name=\"nmov\" onchange=\"this.form.submit();\" value=\"" . $form['nmov'] . "\">\n";
if (isset($l) && $l - 1 > $form['mov']) { // se la suddivisione dei lotti ha creato nuovi righi ricarico il form
    
?>
<script>				
	document.myform.submit();
</script>
<?php
}
echo '</td></tr>';
//<<<<<<<<<<<<<<<<<<<<<<       Fine ciclo righi mov     <<<<<<<<<<<<<<<<<<<
/*ANtonio Germani - visualizzo l'operatore */
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[21] . "</td><td class=\"FacetDataTD\" colspan=\"1\">" . $form["adminid"] . "</td>\n";
/* fine visualizzo l'operatore */
echo "</tr><tr><td colspan=\"1\"><input type=\"submit\" name=\"cancel\" value=\"" . $script_transl['cancel'] . "\">\n";
echo "<input type=\"submit\" name=\"Return\" value=\"" . $script_transl['return'] . "\">\n";
echo "</td><td align=\"right\" colspan=\"1\">\n";
if ($toDo !== 'update') {
    If ($form['artico'][$form['mov']] <> "") {
        echo "<input type=\"submit\" name=\"Add_mov\" value=\"" . $script_transl['add'] . "\">\n";
    }
    If ($form['nmov'] > 0) {
        echo "<input type=\"submit\" title=\"Togli ultimo movimento\" name=\"Del_mov\" value=\"X\">\n";
    }
}
if ($toDo == 'update') {
    echo '<input type="submit" accesskey="m" name="Insert" value="' . strtoupper($script_transl['update']) . '!"></td></tr><tr></tr>';
} else {
    echo '<input type="submit" accesskey="i" name="Insert" value="' . strtoupper($script_transl['insert']) . '!"></td></tr><tr></tr>';
}
echo "</table>\n";
// ************* Fine FORM   <<<<<<<<<<<<<<<<<<<
?>
</form>
<?php
// Antonio Germani questo serve per fare lo scroll all'ultimo movimento inserito
if (isset($anchor["num"])){ 
	echo "<script type='text/javascript'>\n" . "window.location.hash = '#{$anchor["num"]}';" . //◄■■■ JUMP TO LOCAL ANCHOR.
	"</script>\n";
}
require ("../../library/include/footer.php");
?>

