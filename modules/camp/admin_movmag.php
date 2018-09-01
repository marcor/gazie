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
$msg = "";$print_magval="";$dose="";$dose_usofito="";$dim_campo="";$rame_met_annuo="";$scadaut="";$scorta="";$service="";
$today=	strtotime(date("Y-m-d H:i:s",time()));
$gForm = new magazzForm(); // Antonio Germani attivo funzione calcolo giacenza di magazzino

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

$form = array();
/* Antonio Germani riattivo per calcolo prezzo */
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
if (isset($_POST['nome_avv'])){
	$form['mov']=$_POST['mov'];
	$form['nmov']=$_POST['nmov'];
	for ($m = 0; $m <= $form['nmov']; ++$m){
		$form['nome_avv'][$m] = $_POST['nome_avv'.$m];
		$form['id_avversita'][$m] = intval ($form['nome_avv'][$m]);
	}
}
	
// Antonio Germani questo serve per la ricerca colture
if (isset($_POST['nome_colt'])){
		$form['nome_colt'] = $_POST['nome_colt'];
		$form['id_colture'] = intval ($form['nome_colt']);		
}	

// Antonio Germani questo serve per la nuova ricerca articolo
if (isset($_POST['mov']) && isset($_POST['artico'.$_POST['mov']])){
	$form['mov']=$_POST['mov'];
	$form['nmov']=$_POST['nmov'];
	for ($m = 0; $m <= $form['nmov']; ++$m){
		$form['artico'][$m] = $_POST['artico'.$m];
		$form['quanti'][$m] = $_POST['quanti'.$m];
		$form['scorig'][$m] = $_POST['scorig'.$m];
		$form['prezzo'][$m] = $_POST['prezzo'.$m];
		$form['clfoco'][$m] = $_POST['clfoco'.$m];
		$form['nome_avv'][$m] = $_POST['nome_avv'.$m];
		$form['id_avversita'][$m] = intval ($form['nome_avv'][$m]);
		if (isset ($_POST['staff'.$m])){
			$form['staff'][$m] = $_POST['staff'.$m];
		} else {
			$form['staff'][$m]="";
		}
	}
$itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
if ($form['artico'][$form['mov']]<>"" && !isset($itemart)) {$msg .= "18+";}
	}
// Antonio Germani questo serve per la nuova ricerca produzione
if (isset($_POST['description'])){
$form['description']=$_POST['description'];
	if (intval ($form['description']) == 0 && strlen ($form['description']) > 0) {
		$msg .= "30+";
	}
}


if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
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
	$form['mov']=0;
	$form['nmov']=0;
    //recupero il movimento
    $result = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
    $form['id_mov'] = $result['id_mov'];
	$form['type_mov'] = $result['type_mov'];
    $form['id_rif'] = $result['id_rif'];
    $form['caumag'] = $result['caumag'];
    $form['operat'] = $result['operat'];
    $form['gioreg'] = substr($result['datreg'], 8, 2);
    $form['mesreg'] = substr($result['datreg'], 5, 2);
    $form['annreg'] = substr($result['datreg'], 0, 4);
    $form['campo_coltivazione'] = $result['campo_coltivazione']; //campo di coltivazione
	$form['clfoco'][$form['mov']] = $result['clfoco'];
	$form['clfocoin'] = $result['clfoco'];
	$result2 = gaz_dbi_get_row($gTables['staff'], "id_clfoco", $result['clfoco']);
	$form['staff'][$form['mov']]=$result2['id_staff'];
	$form['adminid'] = $result['adminid'];
	$form['id_orderman'] = intval ($result['id_orderman']);
	$resultorderman = gaz_dbi_get_row($gTables['orderman'], "id", $form['id_orderman']);
		If ($form['id_orderman']>0) {
			$form['description'] = $form['id_orderman']." ".$resultorderman['description'];
		} else {
			$form['description']="";
		}
 
    $form['tipdoc'] = $result['tipdoc'];
    $form['desdoc'] = $result['desdoc'];
	$form['id_colt'] = $result['id_colture'];
	$form['id_avv'] = $result['id_avversita'];
    $form['id_avversita'][$form['mov']] = $result['id_avversita'];
	$form['id_colture'] = $result['id_colture'];
	$colt = gaz_dbi_get_row($gTables['camp_colture'],"id_colt",$form['id_colt']);
	$form['nome_colt'] = $form['id_colt']." - ".$colt['nome_colt'];
	$avv = gaz_dbi_get_row($gTables['camp_avversita'],"id_avv",$form['id_avv']);
	$form['nome_avv'][$form['mov']] = $form['id_avv']." - ".$avv['nome_avv'];
	$form['scochi'] = $result['scochi'];
    $form['giodoc'] = substr($result['datdoc'], 8, 2);
    $form['mesdoc'] = substr($result['datdoc'], 5, 2);
    $form['anndoc'] = substr($result['datdoc'], 0, 4);
    $form['artico'][$form['mov']] = $result['artico'];
    $form['quanti'][$form['mov']] = gaz_format_quantity($result['quanti'], 0, $admin_aziend['decimal_quantity']);
	$form['quantiin']=$result['quanti'];
	$form['datdocin']=$result['datdoc'];
    $form['prezzo'][$form['mov']] = number_format($result['prezzo'], $admin_aziend['decimal_price'], '.', '');
    $form['scorig'][$form['mov']] = $result['scorig'];
	$form['clfoco'][$form['mov']]= $result['clfoco'];
    $form['status'] = $result['status'];
    $form['search_partner'] = ""; //Antonio Germani
    $form['search_item'] = "";
	
} elseif (isset($_POST['Insert']) or isset($_POST['Update'])) {   //se non e' il primo accesso
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    //ricarico i registri per il form facendo gli eventuali parsing
    $form['id_mov'] = intval($_POST['id_mov']);
	$form['type_mov'] = 1;
    $form['id_rif'] = intval($_POST['id_rif']);
    $form['caumag'] = intval($_POST['caumag']);
    $form['operat'] = intval($_POST['operat']);
    $form['gioreg'] = intval($_POST['gioreg']);
    $form['mesreg'] = intval($_POST['mesreg']);
    $form['annreg'] = intval($_POST['annreg']);
	$form['clfocoin'] = $_POST['clfocoin'];
	$form['quantiin'] = $_POST['quantiin'];
	$form['datdocin'] = $_POST['datdocin'];
    $form['campo_coltivazione'] = intval($_POST['campo_coltivazione']); //campo di coltivazione
	$form['adminid'] = "Utente connesso";
    $form['tipdoc'] = intval($_POST['tipdoc']);
    $form['desdoc'] = substr($_POST['desdoc'], 0, 50);
    $form['giodoc'] = intval($_POST['giodoc']);
    $form['mesdoc'] = intval($_POST['mesdoc']);
    $form['anndoc'] = intval($_POST['anndoc']);
	$form['scochi'] = floatval(preg_replace("/\,/", '.', $_POST['scochi']));
	    
    $form['quanti'][$form['mov']] = gaz_format_quantity($_POST['quanti'.$form['mov']], 0, $admin_aziend['decimal_quantity']);
	if ((isset($_POST['prezzo'.$form['mov']])>0) && (strlen ($_POST['prezzo'.$form['mov']])>0)) {
		$form['prezzo'][$form['mov']] = number_format(preg_replace("/\,/", '.', $_POST['prezzo'.$form['mov']]), $admin_aziend['decimal_price'], '.', '');
	} else {
		$form['prezzo'][$form['mov']]="";
	}
	if (isset($_POST['scorig'.$form['mov']])) {
		$form['scorig'][$form['mov']] = floatval(preg_replace("/\,/", '.', $_POST['scorig'.$form['mov']]));
	} else {
		$form['scorig'][$form['mov']]="";
	}
    $form['status'] = substr($_POST['status'], 0, 10);
	$form['id_orderman'] = intval ($_POST['description']);
	$form['nmov']=$_POST['nmov'];
		
	if (intval ($form['id_orderman'])>0) { //se è presente una produzione, carico il campo di coltivazione ad essa collegato e la relativa coltura
		$rs_orderman = gaz_dbi_get_row($gTables['orderman'], "id", $form['id_orderman']);
		$form['campo_coltivazione']=$rs_orderman['campo_impianto'];
		$res = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione']);
		if ($res['id_colture']>0){
			$form['id_colture']=$res['id_colture'];
			$res = gaz_dbi_get_row($gTables['camp_colture'], "id_colt", $form['id_colture']);
			$form['nome_colt']=$form['id_colture']." - ".$res['nome_colt'];
		}
	}
	$form['search_partner'] = "";
	
// Antonio Germani - se è stato inserito un campo di coltivazione senza produzione, inserisce automaticamente la coltura
if (isset ($_POST['campo_coltivazione'])&& intval ($form['id_orderman'])<=0) { 
	$res = gaz_dbi_get_row($gTables['campi'], "codice", $_POST['campo_coltivazione']);
		if ($res['id_colture']>0){
			$form['id_colture']=$res['id_colture'];
			$res = gaz_dbi_get_row($gTables['camp_colture'], "id_colt", $form['id_colture']);
			$form['nome_colt']=$form['id_colture']." - ".$res['nome_colt'];
		}
	}	

// Antonio Germani - controllo se c'è una coltura deve esserci un campo di coltivazione
if ($form['campo_coltivazione']<1 && $form['id_colture']>0) {
	$msg .= "35+";
}

// Antonio Germani controllo e avviso se è stata cambiata la coltura nel campo di coltivazione
if (isset($_POST['nome_colt'])){
			if ($form['campo_coltivazione']>0){ // se c'è un campo di coltivazione
				$result = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione']);
				if ($result['id_colture']<>$form['id_colture']){ // se è stata cambiata la coltura avviso	
echo "nella tabella: ",$result['id_colture'], " nel form: ",$form['id_colture'];
					?>
					<div class="alert alert-warning alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<strong>Warning!</strong> La coltura non è quella presente nel campo di coltivazione. Verrà modificata la coltura nel campo di coltivazione!
					</div>
					<?php
				}
			}
}
// fine controllo e avviso coltura
		
    if (isset($_POST['caumag'])) {          
        $causa = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
        $form['operat'] = $causa['operat'];
        $form['clorfo'] = $causa['clifor']; //cliente, fornitore o entrambi
        if (($causa['clifor'] < 0 and substr($form['clfoco'], 0, 3) == $admin_aziend['masfor']) or ( $causa['clifor'] > 0 and substr($form['clfoco'][$form['mov']], 0, 3) == $admin_aziend['mascli'])) {
            $form['clfoco'][$form['mov']]=0;
            $form['search_partner'] = "";
        }		
        if ($causa['insdoc'] == 0) {//se la nuova causale non prevede i dati del documento
            $form['tipdoc'] = "";
            $form['desdoc'] = "";
			$form['giodoc'] = date("d");
			$form['mesdoc'] = date("m");
			$form['anndoc'] = date("Y");
            $form['scochi'] = "";
            $form['id_rif'] = 0;
        }
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
    if (!empty($_POST['Insert'])) {   // Se viene inviata la richiesta di conferma totale ...
	    $utsreg = mktime(0, 0, 0, $form['mesreg'], $form['gioreg'], $form['annreg']);
        $utsdoc = mktime(0, 0, 0, $form['mesdoc'], $form['giodoc'], $form['anndoc']);
        if (!checkdate($form['mesreg'], $form['gioreg'], $form['annreg']))
            $msg .= "16+";
        if (!checkdate($form['mesdoc'], $form['giodoc'], $form['anndoc']))
            $msg .= "15+";
        if ($utsdoc > $utsreg) {
            $msg .= "17+";
        }
		for ($m = 0; $m <= $form['nmov']; ++$m){
			if (empty($form['artico'][$m])) {  //manca l'articolo
            $msg .= "18+";
			}
		}
		for ($m = 0; $m <= $form['nmov']; ++$m){
			if ($form['quanti'][$m] == 0) {  //la quantità è zero
            $msg .= "19+";
			}
		}

// Antonio Germani controllo che, se la causale movimento non opera, non sia inserito un articolo con magazzino
if (!empty($_POST['artico'.$_POST['mov']])) {
	$service=intval($itemart['good_or_service']);
	 	If ($service == 0 && $form['operat']==0 && isset($_POST['artico'.$_POST['mov']])) {
			$msg .= "36+"; echo "OPERAT:",$form['operat'];
		}
}
// fine
		
	 // Antonio Germani calcolo giacenza di magazzino, la metto in $print_magval e, se è uno scarico, controllo sufficiente giacenza
If ($itemart['good_or_service'] ==0) { // se non è un servizio
	 $mv = $gForm->getStockValue(false, $form['artico'][$form['mov']]);
        $magval = array_pop($mv); $print_magval=floatval($magval['q_g']); 
		if (isset($_POST['Update'])) {
			$qta = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
			// prendo la quantità precedentemente memorizzata e la riaggiungo alla giacenza di magazzino altrimenti il controllo quantità non funziona bene
			$print_magval=$print_magval+$qta['quanti'];}
		if ($form["operat"] == -1 and ($print_magval-$form['quanti'][$form['mov']]<0)) { //Antonio Germani quantità insufficiente
			$msg .= "23+";
			}
}

	//Antonio Germani controllo se il prodotto è presente nel database fitofarmaci ed eventualmente se è scaduta l'autorizzazione		
		$query="SELECT ".'SCADENZA_AUTORIZZAZIONE'." FROM ".$gTables['camp_fitofarmaci']. " WHERE PRODOTTO ='". $form['artico'][$form['mov']]."'";
			$result = gaz_dbi_query($query);
			while ($row = $result->fetch_assoc()) {
				$scadaut=$row['SCADENZA_AUTORIZZAZIONE']; $scadaut=strtotime(str_replace('/', '-', $scadaut));
				if ($scadaut>0){if ($scadaut < $today) {$msg .="27+";}}	
			}	
					// se è presente nel db fitofarmaci CONTROLLO QUANDO è StATO FATTO L'ULTIMO AGGIORNAMENTO del db fitofarmaci
					If (($result->num_rows)>0){
						$query="SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_SCHEMA = '".$Database."' AND TABLE_NAME = '".$gTables['camp_fitofarmaci']."'";
						$result = gaz_dbi_query($query); 
							while ($row = $result->fetch_assoc()) {
							$update=strtotime($row['UPDATE_TIME']);
							}
					// 1 giorno è 24*60*60=86400 - 30 giorni 30*86400=2592000		
					If (intval($update)+2592000<$today){$msg .="28+";;}	
					}
					
//Antonio Germani prendo e metto la data di fine sospensione del campo di coltivazione selezionato in $fine_sosp 
		$campo_coltivazione=$form['campo_coltivazione'];//campo di coltivazione inserito nel form
		$query="SELECT ".'giorno_decadimento'.",".'ricarico'." FROM ".$gTables['campi']. " WHERE codice ='". $campo_coltivazione."'";
			$result = gaz_dbi_query($query);
			while ($row = $result->fetch_assoc()) {
			$fine_sosp=$row['giorno_decadimento']; $fine_sosp=strtotime($fine_sosp);
			$dim_campo=$row['ricarico'];// prendo pure la dimensione del campo e la metto in $dim_campo
			}
			
// Antonio Germani Controllo se la quantità o dose è giusta rapportata al campo di coltivazione
			$item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
			$dose_artico=$item['dose_massima'];// prendo la dose
			$rame_metallo=$item['rame_metallico'];// già che ci sono, prendo anche il rame metallo del prodotto oggetto del movimento, che mi servirà per il prossimo controllo	
			
			$query="SELECT ".'dose'." FROM ".$gTables['camp_uso_fitofarmaci']. " WHERE cod_art ='". $form['artico'][$form['mov']]."' AND id_colt ='".$form['id_colture']."' AND id_avv ='".$form['id_avversita'][$form['mov']]."'";
			$result = gaz_dbi_query($query);
			while ($row = $result->fetch_assoc()) {
				$dose_usofito=$row['dose'];
			} 
			if ($dose_usofito>0){
			If ($dose_usofito>0 && $form['quanti'][$form['mov']] > $dose_usofito*$dim_campo && $form["operat"]==-1 && $dim_campo>0) {
				$msg .="34+"; // errore dose uso fito superata
				?>
					<div class="alert alert-warning alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<strong>Warning!</strong> Dose superata nel prodotto <?php echo $form['artico'][$form['mov']];?> con la coltura <?php echo $form['nome_colt'];?>.
					</div>
					<?php
			}} else {
				if ($dose_artico>0 && $form['quanti'][$form['mov']] > $dose_artico*$dim_campo && $form["operat"]==-1 && $dim_campo>0) {
					$msg .="25+"; // errore dose artico superata
					?>
					<div class="alert alert-warning alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<strong>Warning!</strong> Dose superata nel prodotto <?php echo $form['artico'][$form['mov']];?>
					</div>
					<?php
				}
			}
			
// Antonio Germani Calcolo quanto rame metallo è stato usato nell'anno di esecuzione di questo movimento
			If ($campo_coltivazione>0){ // se il prodotto va in un campo di coltivazione
				if ($rame_metallo>0){ //se questo prodotto contiene rame metallo
					$query="SELECT ".'artico'. ",".'datdoc'.",".'quanti'." FROM ".$gTables['movmag']. " WHERE datdoc >'". $form['anndoc'] ."' AND ".'campo_coltivazione'." = '".$campo_coltivazione."'"; // prendo solo le righe dell'anno di esecuzione del trattamento e degli anni successivi con il campo di coltivazione selezionato nel form
				
			$result = gaz_dbi_query($query); 
						while ($row = $result->fetch_assoc()) {
							if (substr($row['datdoc'],0,4) == $form['anndoc']){ // elimino dal conteggio gli eventuali anni successivi
							$item = gaz_dbi_get_row($gTables['artico'], "codice", $row['artico']);
							if ($item['rame_metallico']>0){$rame_met_annuo=$rame_met_annuo+$item['rame_metallico']*$row['quanti'];}
							}
						}
				}		
			}
// fine calcolo rame

		// Antonio Germani controllo se con questo movimento non si supera la doce massima annua di 6Kg ad ha di rame metallo
			
				if (($campo_coltivazione>0)&&($dim_campo>0)&&($rame_met_annuo+($rame_metallo* $form['artico'][$form['mov']])> (6 * $dim_campo))) {
					$msg .="26+";echo "CONTROLLO rame metallo: <br> Rame metallo anno già usato: ",$rame_met_annuo," Rame metallo che si tenta di usare: ",($rame_metallo* $form['artico'][$form['mov']]), " Limite annuo di legge per questo campo: ", (6 * $dim_campo);}	// errore superato il limite di rame metallo ad ettaro		
			
						
// Antonio Germani creo la data d I ATTUAZIONE DELL'OPERAZIONE selezionata che poi confronterò con quella di sospensione del campo 
		$dt=substr("0".$form['giodoc'],-2)."-".substr("0".$form['mesdoc'],-2)."-".$form['anndoc']; $dt=strtotime($dt); 			
// controllo se è ammesso il raccolto sul campo di coltivazione selezionato $msg .=24+ errore tempo di sospensione
		If ($form['campo_coltivazione']>0 && $form["operat"]==1 && intval($dt)<intval($fine_sosp)){
		
			$msg .="24+";	
			
		}
//  §§§§§§§§§§§§§§§§ INIZIO salvataggio sui database §§§§§§§§§§§§§§§§§§§	
        if (empty($msg)) { // nessun errore  
			$upd_mm = new magazzForm;
            //formatto le date
            $form['datreg'] = $form['annreg'] . "-" . $form['mesreg'] . "-" . $form['gioreg'];
            $form['datdoc'] = $form['anndoc'] . "-" . $form['mesdoc'] . "-" . $form['giodoc'];
            $new_caumag = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
for ($form['mov'] = 0; $form['mov'] <= $form['nmov']; ++$form['mov']){		
            if (!empty($form['artico'][$form['mov']])) {
                $upd_mm->uploadMag($form['id_rif'], $form['tipdoc'], 0, // numdoc � in desdoc
                        0, // seziva � in desdoc 
                        $form['datdoc'], $form['clfoco'][$form['mov']], $form['scochi'], $form['caumag'], $form['artico'][$form['mov']], $form['quanti'][$form['mov']], $form['prezzo'][$form['mov']], $form['scorig'][$form['mov']], $form['id_mov'], $admin_aziend['stock_eval_method'], array('datreg' => $form['datreg'], 'operat' => $form['operat'], 'desdoc' => $form['desdoc'])
                );
		//Antonio Germani Non riesco a capire come funziona la funzione qui sopra; ho perso troppo tempo!!!!
		// risolvo in questa maniera per far scrivere i nuovi campi di movmag, specifici del quaderno di campagna
		If ($form['id_mov']>0){
			$id_movmag=$form['id_mov'];
			}
		else {
				$query="SHOW TABLE STATUS LIKE '".$gTables['movmag']."'"; 
				$result = gaz_dbi_query($query);
				$row = $result->fetch_assoc();
				$id_movmag = $row['Auto_increment'];
				// siccome ha già registrato il movimento di magazzino devo togliere 1
				$id_movmag=$id_movmag-1;
			}		
		$query="UPDATE " . $gTables['movmag'] . " SET type_mov = '" . 1 .  "' , campo_coltivazione = '"  .$form['campo_coltivazione']. "' , id_avversita = '"  .$form['id_avversita'][$form['mov']]. "' , id_colture = '"  .$form['id_colture']. "' , id_orderman = '"  .$form['id_orderman']. "' WHERE id_mov ='". $id_movmag."'"; 
			gaz_dbi_query ($query) ;
					
// Antonio Germani - aggiorno la tabella campi se c'è un campo inserito (cioè >0) e se l'operazione è uno scarico (cioè operat<0) e se la data di fine sospensione già presente nel campo è inferiore alla data di sospensione del prodotto appena usato (cioè $fine_sosp<$dt)

//Antonio Germani per prima cosa determino il codice del movimento che eventualmente andrà nella tabella del campo di coltivazione
if (!isset($_POST['Update'])){
// Antonio Germani se è un iserimento vedo quale sarà il prossimo codice del movimento del magazzino che verrà utilizzato !NB il codice è incremental!
$query="SHOW TABLE STATUS LIKE '".$gTables['movmag']."'"; 
$result = gaz_dbi_query($query);
$row = $result->fetch_assoc();
$id_mov = $row['Auto_increment'];
// siccome ha già registrato il movimento di magazzino devo togliere 1
$id_mov=$id_mov-1; 
}
else {$id_mov=$form['id_mov'];} // se non è un nuovo inserimento prendo il codice del movimento di magazzino selezionato

// adesso vedo se si deve aggiornare il campo di coltivazione	
	if ($form['campo_coltivazione']>0 && $form["operat"]<0) {
/* Antonio Germani creo la data del trattamento selezionato a cui poi aggiungerò i giorni di sospensione. */
		$dt=substr("0".$form['giodoc'],-2)."-".substr("0".$form['mesdoc'],-2)."-".$form['anndoc']; $dt=strtotime($dt); 

// Antonio Germani prendo i giorni del tempo di sospensione dall'articolo selezionato e li aggiungo al giorno del trattamento (Un giorno = 86400 timestamp)
		$artico= $form['artico'][$form['mov']];
		$query="SELECT ".'tempo_sospensione'." FROM ".$gTables['artico']. " WHERE codice ='". $artico."'";
		$result = gaz_dbi_query($query);
			while ($row = $result->fetch_assoc()) {
			 $temp_sosp=$row['tempo_sospensione'];
			}
			$dt=$dt+(86400*intval($temp_sosp));
// Antonio Germani controllo se il tempo di sospensione del campo di coltivazione è inferiore a quello che si crea con questo trattamento aggiorno il database campi nel campo di coltivazione selezionato
		if (intval($fine_sosp)<intval($dt)) {
			$dt=date('Y/m/d', $dt);	
			$codcamp=$form['campo_coltivazione'];
			$query="UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . $dt .  "' , codice_prodotto_usato = '"  .$artico. "' , id_mov = '"  .$id_mov. "' , id_colture = '"  .$form['id_colture']. "' WHERE codice ='". $codcamp."'";
			gaz_dbi_query ($query) ;
		}
	}
// fine gestione giorno di sospensione tabella campi

// aggiornare tabella campi se è stata cambiata la coltura
if ($form['campo_coltivazione']>0){ // se c'è un campo di coltivazione
	$result = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione']);	
	if ($result['id_colture']<>$form['id_colture']){ // se è stato cambiato lo aggiorno
		$query="UPDATE " . $gTables['campi'] . " SET id_colture = '" .$form['id_colture']. "' WHERE codice ='". $form['campo_coltivazione'] ."'";
			gaz_dbi_query ($query) ;
	}
}
// fine aggiorna campi coltura 

// INIZIO gestione registrazione database operai
If (intval($form['staff'][$form['mov']])>0){
	$id_worker=$form['staff'][$form['mov']]; //identificativo operaio
	$form['datdocin']; // questa è la data documento iniziale
	$work_day= $form['anndoc'] . "-" . $form['mesdoc'] . "-" . $form['giodoc']; // giorno lavorato
	$hours_form=$form['quanti'][$form['mov']]; //ore lavorate normali del form
	$id_orderman=$form['id_orderman'];
	
// controllo se è una variazione movimento e se è stato cambiato l'operaio
	$res2 = gaz_dbi_get_row($gTables['staff'], "id_clfoco", $form['clfocoin']);
	If ($toDo == "update" && $res2['id_staff']<> $id_worker) { // se è stato cambiato l'operaio
		If (strtotime($work_day) == strtotime($form['datdocin'])) {  // se non è stata cambiata la data documento
			
// all'operaio iniziale, cioè quello che è stato sostituito, devo togliere le ore
		$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $res2['id_staff']."' AND work_day ='".$work_day);
			If (isset($rin)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate e tolgo quelle odierne
				$hours_normal= $rin['hours_normal']-$form['quantiin']; // e faccio l'UPDATE - NON tocco id_orderman ma ATTENZIONE
// la gestione della tabella "staff_worked_hours" sarebbe da rivedere perché non contempla che un operaio possa lavorare a più produzioni (id_orderman) nello stesso giorno !!!
				$query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff ='.$res2['id_staff'].", work_day = '".$work_day."', hours_normal = '".$hours_normal."' WHERE id_staff = '".$res2['id_staff']."' AND work_day = '".$work_day."'";
				gaz_dbi_query($query);
			}
// al nuovo operaio devo aggiungere le ore lavorate
			$r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day);
	If (isset($r)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate 
		$ore_lavorate = $r['hours_normal'];
	} else {
		$ore_lavorate = 0;
	}	
		$hours_normal = $ore_lavorate + $hours_form;
// salvo ore su operaio attuale					
				$exist=gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = ".$id_worker );
				if ($exist>=1){ // se ho già un record del lavoratore per quella data faccio UPDATE
				    $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff ='.$id_worker.", id_orderman = '".$id_orderman."', work_day = '".$work_day."', hours_normal = '".$hours_normal."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."'";
					gaz_dbi_query($query);
				} else { // altrimenti faccio l'INSERT
				$v=array();
					$v['id_staff']=$id_worker;
					$v['work_day']=$work_day;
					$v['hours_normal']=$hours_normal;
					$v['id_orderman']=$id_orderman;
					gaz_dbi_table_insert('staff_worked_hours', $v);
				}				
			
		} else { // se è stata cambiata la data documento (giorno lavorato)
// all'operaio iniziale, cioè quello che è stato sostituito, devo togliere le ore nel giorno iniziale
		$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $res2['id_staff']."' AND work_day ='".$form['datdocin']);
			If (isset($rin)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate e tolgo quelle odierne
				$hours_normal= $rin['hours_normal']-$form['quantiin']; // e faccio l'UPDATE - NON tocco id_orderman 
				$query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff ='.$res2['id_staff'].", work_day = '".$form['datdocin']."', hours_normal = '".$hours_normal."' WHERE id_staff = '".$res2['id_staff']."' AND work_day = '".$form['datdocin']."'";
				gaz_dbi_query($query);
			}
			
// al nuovo operaio devo aggiungere le ore lavorate nel giorno del documento
$r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day);
	If (isset($r)) { // se esiste giorno e operaio vedo se ci sono ore normali lavorate 
		$ore_lavorate = $r['hours_normal'];
	} else {
		$ore_lavorate = 0;
	}	
		$hours_normal = $ore_lavorate + $hours_form;
// salvo ore su operaio attuale					
				$exist=gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = ".$id_worker );
				if ($exist>=1){ // se ho già un record del lavoratore per quella data faccio UPDATE
				    $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff ='.$id_worker.", id_orderman = '".$id_orderman."', work_day = '".$work_day."', hours_normal = '".$hours_normal."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."'";
					gaz_dbi_query($query);
				} else { // altrimenti faccio l'INSERT
				$v=array();
					$v['id_staff']=$id_worker;
					$v['work_day']=$work_day;
					$v['hours_normal']=$hours_normal;
					$v['id_orderman']=$id_orderman;
					gaz_dbi_table_insert('staff_worked_hours', $v);
				}		
		}
	} else {
		
		If ($toDo == "update" && $res2['id_staff'] == $id_worker) { // se è update e NON è stato cambiato l'operaio
			If (strtotime($work_day) <> strtotime($form['datdocin'])) { // se è stata cambiata la data 
			// devo togliere le ore al giorno iniziale e metterle nel giorno del documento
				// tolgo le ore al giorno iniziale
				$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$form['datdocin']);
					If (isset($rin)) { // se esiste giorno e operaio gli modifico le ore
						$hours_normal= $rin['hours_normal']-$form['quantiin'];
						$query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '".$hours_normal."' WHERE id_staff = '".$id_worker."' AND work_day = '".$form['datdocin']."'";
						gaz_dbi_query($query);
					}
				// metto le ore del form nel giorno del documento
					
				$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day);
				
					If (isset($rin)) { // se esiste giorno e operaio gli modifico le ore
						$hours_normal= $rin['hours_normal']+$hours_form;
						$query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '".$hours_normal."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."'";
						gaz_dbi_query($query);
					} else { // altrimenti faccio l'INSERT
				$v=array();
					$v['id_staff']=$id_worker;
					$v['work_day']=$work_day;
					$v['hours_normal']=$hours_form;
					$v['id_orderman']=$id_orderman;
					gaz_dbi_table_insert('staff_worked_hours', $v);
				}		
			
			} else { //se NON è stata cambiata la data
			// modifico le ore nello stesso giorno del documento
			$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day);
				If (isset($rin)) { // se esiste giorno e operaio gli modifico le ore
					$hours_normal= $rin['hours_normal']-$form['quantiin']+$hours_form;
					$query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '".$hours_normal."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."'";
					gaz_dbi_query($query);
				}			
			}
		}	
		
	}

If ($toDo <> "update") { // se non è un update
	$r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day);
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
				$exist=gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = ".$id_worker );
				if ($exist>=1){ // se ho già un record del lavoratore per quella data faccio UPDATE
				    $query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff ='.$id_worker.", id_orderman = '".$id_orderman."', work_day = '".$work_day."', hours_normal = '".$hours_normal."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."'";
					gaz_dbi_query($query);
				} else { // altrimenti faccio l'INSERT
				$v=array();
					$v['id_staff']=$id_worker;
					$v['work_day']=$work_day;
					$v['hours_normal']=$hours_normal;
					$v['id_orderman']=$id_orderman;
					gaz_dbi_table_insert('staff_worked_hours', $v);
				}
	}
}
// FINE gestione registrazione database operai 

            }
		} //fine ciclo for mov
            header("Location:report_movmag.php");
            exit;
        }
    } 
// §§§§§§§§§§§§§§§§§§§§  FINE salvataggio sui database §§§§§§§§§§§§§§§§§§§

} elseif (!isset($_POST['Insert'])) {//se e' il primo accesso per INSERT
    $form['hidden_req'] = '';
    //registri per il form della testata
    $form['id_mov'] = 0;
	$form['type_mov'] = 1;
    $form['gioreg'] = date("d");
    $form['mesreg'] = date("m");
    $form['annreg'] = date("Y");
    $form['caumag'] = "";
    $form['operat'] = 0;
    $form['campo_coltivazione'] = ""; //campo di coltivazione
    $form['clfocoin'] = 0;
	$form['quantiin'] = "";
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
	$form['mov']=0;
	$form['nome_avv'][$form['mov']] = "";
	$form['id_avversita'][$form['mov']] = 0;
    $form['artico'][$form['mov']] = "";
    $form['quanti'][$form['mov']] = "";
    $form['prezzo'][$form['mov']] = 0;
    $form['scorig'][$form['mov']] = 0;
	$form['clfoco'][$form['mov']]= 0;
    $form['status'] = "";
    $form['search_partner'] = "";
    $form['search_item'] = "";
    $form['id_rif'] = 0;
	$form['description']="";
	$form['id_orderman']= 0;
	$form['nmov']=0;
	$form['staff'][$form['mov']]="";
}
// Antonio Germani questo serve per aggiungere o togliere un movimento
if (isset($_POST['Add_mov'])){ 
	$form['nmov']=$_POST['nmov'];
	for ($m = 0; $m <= $form['nmov']; ++$m){
		$form['artico'][$m] = $_POST['artico'.$m];
		$form['quanti'][$m] = $_POST['quanti'.$m];
		$form['prezzo'][$m] = $_POST['prezzo'.$m];
		$form['scorig'][$m] = $_POST['scorig'.$m];
		$form['staff'][$m]=intval($_POST['staff'.$m]);
		$form['clfoco'][$m]= $_POST['clfoco'.$m];
		$form['nome_avv'][$m] = $_POST['nome_avv'.$m];
		$form['id_avversita'][$m] = $_POST['id_avversita'.$m];
	}
	$form['nmov']=$form['nmov']+1;
	$form['artico'][$form['nmov']] = "";
	$form['quanti'][$form['nmov']] = "";
	$form['prezzo'][$form['nmov']] = 0;
	$form['scorig'][$form['nmov']] = 0;
	$form['staff'][$form['nmov']]= "";
	$form['clfoco'][$form['nmov']]= 0;
	$form['nome_avv'][$form['nmov']]= "";
	$form['id_avversita'][$form['nmov']]= 0;
	
} 
if (isset($_POST['Del_mov'])) {
	$form['mov']=$_POST['mov'];
	If ($_POST['nmov']>0) {
		$form['nmov']=$form['nmov']-1;
		$form['mov']=$form['mov']-1;
	}
}

if (isset($_POST['acquis'])){ //compilazione ordine a fornitore
$item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
    $scorta=$item['scorta'];// prendo la scorta minima
	$fornitore=$item['clfoco'];//prendo codice clfoco per codice fornitore per ordine a fornitore
		if (isset($fornitore)) {?>
			<form action="../../modules/acquis/admin_broacq.php?tipdoc=AOR" method="post" name="docacq">  
			<input type="hidden" name="Insert" value="insert">
			<input type="hidden" value="AOR" name="tipdoc">
			<input type="hidden" name="clfoco" value="<?php echo $fornitore;?>"> <!-- questo è il fornitore che devo prendere dalla tabella atico, colonna clfoco se è zero devo bloccare perché non è stato inserito il fornitore nell'articolo -->
			<input type="hidden" name="search[clfoco]" value="<?php echo $fornitore;?>">
			<input type="hidden" name="gioemi" value="<?php echo date('d');?>"> <!-- giorno -->
			<input type="hidden" name="mesemi" value="<?php echo date('m');?>"> <!-- mese -->
			<input type="hidden" name="annemi" value="<?php echo date('Y');?>"><!-- anno -->
			<input type="hidden" value="INSERT" name="in_status">
			<input type="hidden" name="in_codart" value="<?php echo $form['artico'][$form['mov']];?>">
			<input type="hidden" name="cosear" value="<?php echo $form['artico'][$form['mov']];?>">
			<input type="hidden" value="<?php echo $scorta;?>"  name="in_quanti"> 
			<input type="hidden" name="in_codric" value="330000004">
			<input type="hidden" value="<?php echo $form['artico'][$form['mov']];?>" name="codart">
				<script type="text/javascript" >
					document.forms["docacq"].submit(); 
				</script>
			</form>
<?php
	} else {?>
		<div class="alert alert-warning alert-dismissible">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<strong>Warning!</strong> Non è possibile riordinare; il prodotto non è ordinabile o non è inserito il fornitore!
		</div>
<?php		
		}
}

If (isset($_POST['cancel'])){$form['hidden_req'] = '';
    //registri per il form della testata
    $form['id_mov'] = 0;
	$form['type_mov'] = 1;
    $form['gioreg'] = date("d");
    $form['mesreg'] = date("m");
    $form['annreg'] = date("Y");
    $form['caumag'] = "";
    $form['operat'] = 0;
    $form['campo_coltivazione'] = ""; //campo di coltivazione
    
	$form['adminid'] = "Utente connesso";
    $form['tipdoc'] = "";
    $form['desdoc'] = "";
    $form['giodoc'] = date("d");
    $form['mesdoc'] = date("m");
    $form['anndoc'] = date("Y");
    $form['scochi'] = 0;
	$form['id_avversita'][$form['mov']] = 0;
	$form['id_colture'] = 0;
	$form['nmov']= 0;
	$form['mov']=0;
    $form['artico'][$form['mov']] = "";
    $form['prezzo'][$form['mov']] = 0;
    $form['scorig'][$form['mov']] = 0;
	$form['quanti'][$form['mov']] = 0;
	$form['staff'][$form['mov']]="";
	$form['clfoco'][$form['mov']]=0;
	$form['clfocoin'] = "";
	$form['quantiin'] = "";
	$form['datdocin'] = "";
    $form['status'] = "";
    $form['search_partner'] = "";
    $form['search_item'] = "";
    $form['id_rif'] = 0;
	$form['description']="";
	$form['id_orderman']=0;
	$fornitore="";	
	}

require("../../library/include/header.php");
$script_transl = HeadMain();
require("./lang." . $admin_aziend['lang'] . ".php");
if ($form['id_mov'] > 0) {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0]) . " n." . $form['id_mov'];
} else {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0]);
}

echo "<form method=\"POST\" name=\"myform\">";
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
$importo_rigo = CalcolaImportoRigo($form['artico'][$form['mov']], $form['prezzo'][$form['mov']], $form['scorig'][$form['mov']]);
$importo_totale = CalcolaImportoRigo(1, $importo_rigo, $form['scochi']);
echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice(explode('+', chop($msg)), 0, -1);
    foreach ($rsmsg as $value) {
        $message .= $script_transl['error'] . "! -> ";
        $rsval = explode('-', chop($value));
        foreach ($rsval as $valmsg) {
            $message .= $script_transl[$valmsg] . " ";
        }
        $message .= "<br />";
    }
    echo '<tr><td colspan="3" class="FacetDataTDred">' . $message . "</td></tr>\n";
}

?>
<!-- inizio inserisci produzione  -->
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql orderman	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete2").autocomplete({
		source: [<?php
	$stringa="";$cod="";
	$query="SELECT * FROM ".$gTables['orderman'];
	$res = gaz_dbi_query($query);
	while($row = $res->fetch_assoc()){
		$itemtesbro = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $row['id_tesbro']);
		$stringa.="\"".$row['id']." ".$row['description']." ".gaz_format_date($itemtesbro['datemi'])."\", ";			
	}
	$stringa=substr($stringa,0,-2).$cod;
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
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[29]."</td><td colspan=\"1\" class=\"FacetDataTD\"\n>";
?>
      <input class="col-sm-5" id="autocomplete2" type="text" value="<?php echo $form['description'] ?>" name="description" maxlength="30" /> <!-- per funzionare autocomplete id dell'input deve essere autocomplete2 -->	  
<script>
  var stile = "top=10, left=10, width=600, height=800 status=no, menubar=no, toolbar=no scrollbar=no";
     function Popup(apri) {
        window.open(apri, "", stile);
     }
</script>
<a href="javascript:Popup('../../modules/orderman/admin_orderman.php?Insert&popup=1')"> Crea nuova produzione <i class="glyphicon glyphicon-plus-sign" style="color:green" ></i></a>	  
</td></tr>
<?php	
/* fine inserisci produzione  */

/*Antonio Germani CAMPO coltivazione  */
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[3] . "</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"campo_coltivazione\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
echo "<option value=\"\">-------------</option>\n";
$result = gaz_dbi_dyn_query("*", $gTables['campi']);
while ($row = gaz_dbi_fetch_array($result)) {
    $selected = "";
    if ($form["campo_coltivazione"] == $row['codice']) {
        $selected = " selected ";
    }
    echo "<option value=\"" . $row['codice'] . "\"" . $selected . ">" . $row['codice'] . " - " . $row['descri'] . "</option>\n";
} 
echo "</select>&nbsp;";
// prendo la dimesione del campo 
$item = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione']);
echo "Superficie: ",gaz_format_quantity($item["ricarico"],1,$admin_aziend['decimal_quantity'])," ha</tr>";

//   CAUSALE
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[2] . "</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"caumag\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
echo "<option value=\"\">-------------</option>\n";
$result = gaz_dbi_dyn_query("*", $gTables['caumag'], " 1 ", "codice desc, descri asc");
while ($row = gaz_dbi_fetch_array($result)) {
    $selected = "";
    if ($form["caumag"] == $row['codice']) {
        $selected = " selected ";
    }
    echo "<option value=\"" . $row['codice'] . "\"" . $selected . ">" . $row['codice'] . " - " . $row['descri'] . "</option>\n";
}
echo "</select>&nbsp;";

echo "<select name=\"operat\" class=\"FacetSelect\">\n";
for ($counter = -1; $counter <= 1; $counter++) {
    $selected = "";
    if ($form["operat"] == $counter) {
        $selected = " selected ";
    }
    echo "<option value=\"$counter\" $selected > " . $strScript["admin_caumag.php"][$counter + 9] . "</option>\n";
}
echo "</td></tr>";

/* Antonio Germani -  COLTURA */
?>
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql camp_colture	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete4").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['camp_colture'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['id_colt']." - ".$row['nome_colt']."\", ";			
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
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[33]."</td><td class=\"FacetDataTD\"\n>";
?>
     <input id="autocomplete4" type="text" value="<?php echo $form['nome_colt']; ?>" name="nome_colt" maxlength="50" size="50"/>
	 <input type="hidden" value="<?php echo intval ($form['nome_colt']); ?>" name="id_colture"/>
	 </td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete4 -->	 
<?php
/* fine coltura */

// DATA della REGISTRAZIONE
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[1] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"gioreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
    $selected = "";
    if ($counter == $form['gioreg'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
    $selected = "";
    if ($counter == $form['mesreg'])
        $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"annreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
    $selected = "";
    if ($counter == $form['annreg'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td></tr>\n";

 /* Antonio Germani qui si seleziona la DATA di ATTUAZIONE */      	
echo "<tr></td><td class=\"FacetFieldCaptionTD\">" . $script_transl[8] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"giodoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
    $selected = "";
    if ($counter == $form['giodoc'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesdoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
    $selected = "";
    if ($counter == $form['mesdoc'])
        $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"anndoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
    $selected = "";
    if ($counter == $form['anndoc'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td></tr>\n"; 
// fine data di attuazione 

// ANNOTAZIONE o DESCRIZIONE DOCUMENTO 
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[9] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['desdoc'] . "\" maxlength=\"50\" size=\"35\" name=\"desdoc\"></td></tr>";

?>
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql artico	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['artico'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['codice']."\", ";			
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
 // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
  for ($form['mov'] = 0; $form['mov'] <= $form['nmov']; ++$form['mov']) {
	  
	  $importo_rigo = CalcolaImportoRigo($form['artico'][$form['mov']], $form['prezzo'][$form['mov']], $form['scorig'][$form['mov']]);
		$importo_totale = CalcolaImportoRigo(1, $importo_rigo, $form['scochi']);
		
		 echo "<input type=\"hidden\" name=\"mov\" value=\"" . $form['mov'] . "\">\n";
		 
 /* Antonio Germani questo non serve al quaderno di campagna
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl[10] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['scochi'] . "\" maxlength=\"5\" size=\"5\" name=\"scochi\" onChange=\"this.form.submit\"> %</td></tr>";
*/
//però devo metterlo come nascosto altrimenti mi segnala un warning su 'scochi'
echo "<input type=\"hidden\" name=\"scochi\" value=\"" . $form['scochi'] . "\">\n";

echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">\n";
$messaggio = "";

$print_unimis = ""; 
$ric_mastro = substr($form['artico'][$form['mov']], 0, 3);
?>
             <input class="col-sm-4" id="autocomplete" type="text" value="<?php echo $form['artico'][$form['mov']] ?>" name="artico<?php echo $form['mov'] ?>" maxlength="15" /> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete -->
 <?php 
if ($form['artico'][$form['mov']] == "") {
} else {	
    $itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico'][$form['mov']]);
	$print_unimis = $itemart['unimis'];
	$dose=$itemart['dose_massima'];// prendo anche la dose
	$scorta=$itemart['scorta'];// prendo la scorta minima
	$descri=$itemart['descri'];//prendo descrizione articolo
	if (isset($_POST['Insert'])) {
        $form['prezzo'][$form['mov']]=$itemart['preacq']; 
    }
	$service=intval($itemart['good_or_service']);
	 	If ($service == 0) { //Antonio Germani se è un articolo con magazzino
			// Antonio Germani calcolo giacenza di magazzino e la metto in $print_magval
			$mv = $gForm->getStockValue(false, $itemart['codice']);
			$magval = array_pop($mv); $print_magval=floatval($magval['q_g']);
				if (isset($_POST['Update'])) { // se è update
				$qta = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
				// Antonio Germani prendo la quantità precedentemente memorizzata e la riaggiungo alla giacenza di magazzino altrimenti il controllo quantità non funziona bene
				$print_magval=$print_magval+$qta['quanti'];
				}	 
			echo " ",substr($itemart['descri'], 0, 25)," ";	
			if ($dose>0) {echo "<br>Dose generica: ",gaz_format_quantity($dose,1,$admin_aziend['decimal_quantity'])," ",$print_unimis,"/ha";}
		}  
}
?></tr><tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl[12]; ?></td>
<td class="FacetDataTD" ><input type="text" value="<?php echo $form['quanti'][$form['mov']];?>" maxlength="10" size="10" name="quanti<?php echo $form['mov'] ?>" onChange="this.form.submit()"><?php echo "&nbsp;".$print_unimis;?>
<?php
	if ($service == 0) { //Antonio Germani se è un articolo con magazzino
		echo " ".$script_transl[22]." ".gaz_format_quantity($print_magval,1,$admin_aziend['decimal_quantity'])." ".$print_unimis."&nbsp;&nbsp;";
	
		if ($print_magval<$scorta and $service ==0 ) {
			echo "<button type=\"submit\" name=\"acquis\" class=\"btn btn-default btn-lg\" title=\"Sottoscorta, riordinare\" style=\"background-color:red\"><span class=\"glyphicon glyphicon-alert\" aria-hidden=\"true\"></span></button>";
		}
		?>
	<input type="hidden" name="clfoco<?php echo $form['mov']; ?>" value="<?php $form['clfoco'][$form['mov']]; ?>">
	<input type="hidden" name="staff<?php echo $form['mov']; ?>" value="<?php echo $form['staff'][$form['mov']];?>">
<?php	
	} else {
/*Antonio Germani se l'unità di misura è oraria attivare Operaio */
			if ($print_unimis == "h"){
				echo "&nbsp;&nbsp;" .$script_transl[32]. "&nbsp;";
				?>				
				<select name="staff<?php echo $form['mov'] ?>" class="FacetSelect" onchange="this.form.submit()">
				<?php
				echo "<option value=\"\">-------------</option>\n";
				$result = gaz_dbi_dyn_query("*", $gTables['staff']);
				while ($row = gaz_dbi_fetch_array($result)) {
					$selected = ""; 
					if ($form['staff'][$form['mov']] == $row['id_staff']) {
						$selected = " selected ";
					}
					$anagra = gaz_dbi_get_row($gTables['clfoco'], "codice", $row['id_clfoco']); 
					echo "<option value=\"" . $row['id_staff'] . "\"" . $selected . ">" . $row['id_staff'] . " - " . $anagra['descri'] . "</option>\n"; $form['clfoco'][$form['mov']]=$row['id_clfoco'];
				}
				
			}
			$itm = gaz_dbi_get_row($gTables['staff'], "id_staff", $form['staff'][$form['mov']]);
			?>
			<input type="hidden" name="clfoco<?php echo $form['mov']; ?>" value="<?php echo $itm['id_clfoco'];?>">
			<?php
	}
echo "</select>&nbsp;"; 
echo "</td></tr>\n";

/* Antonio Germani -  AVVERSITà */
if ($print_unimis <> "h"){ // se è una lavorazione agricola disattivare avversità
?>
<!-- Antonio Germani inizio script autocompletamento dalla tabella mysql camp_avversita	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete3").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['camp_avversita'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['id_avv']." - ".$row['nome_avv']."\", ";			
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
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[20]."</td><td class=\"FacetDataTD\"\n>";
?>
     <input id="autocomplete3" type="text" value="<?php echo $form['nome_avv'][$form['mov']]; ?>" name="nome_avv<?php echo $form['mov'] ?>" maxlength="50" size="50"/>
	 <input type="hidden" value="<?php echo intval ($form['nome_avv'][$form['mov']]); ?>" name="id_avversita<?php echo $form['mov'] ?>"/>
	 <?php
	 $query="SELECT ".'dose'." FROM ".$gTables['camp_uso_fitofarmaci']. " WHERE cod_art ='". $form['artico'][$form['mov']]."' AND id_colt ='".$form['id_colture']."' AND id_avv ='".$form['id_avversita'][$form['mov']]."'";
			$result = gaz_dbi_query($query);
			while ($row = $result->fetch_assoc()) {
				$dose_usofito=$row['dose'];
			} 
	 if ($dose_usofito>0) {echo "Dose specifica: ",gaz_format_quantity($dose_usofito,1,$admin_aziend['decimal_quantity'])," ",$print_unimis,"/ha";}
	 ?>
	 </td></tr> <!-- per funzionare autocomplete, id dell'input deve essere autocomplete3 -->	 
<?php
} else {
	?>
     <input type="hidden" value="" name="nome_avv<?php echo $form['mov'] ?>"/>
	 <input type="hidden" value="" name="id_avversita<?php echo $form['mov'] ?>"/>
	 <?php
}
	
/* fine avversità */

$print_magval=""; $scorta=""; $dose=""; // le azzero perché altrimenti me le ritrovo nell'eventuale movimento/riga successivo
/* Antonio Germani riattivo il prezzo e lo sconto che nel quaderno di campagna servono */
$importo_totale=($form['prezzo'][$form['mov']]*floatval(preg_replace("/\,/", '.', $form['quanti'][$form['mov']])))-((($form['prezzo'][$form['mov']]*floatval(preg_replace("/\,/", '.', $form['quanti'][$form['mov']])))*$form['scorig'][$form['mov']])/100);
?>
<!-- COSTO MOVIMENTO  -->
<tr><td class="FacetFieldCaptionTD"><?php echo $script_transl[13]; ?></td><td class="FacetDataTD" colspan="1"><input type="text" value="<?php echo number_format ($importo_totale,$admin_aziend['decimal_price'], ',', ''); ?>" name="total" size="20" readonly /><?php echo "&nbsp;" . $admin_aziend['symbol'] . "&nbsp;&nbsp;&nbsp;&nbsp;". $script_transl[31]; ?>
<input type="text" value="<?php echo number_format ($form['prezzo'][$form['mov']],$admin_aziend['decimal_price'], ',', '') ?>" maxlength="12" size="12" name="prezzo<?php echo $form['mov'] ?>" onChange="this.form.submit()"><?php echo " ".$admin_aziend['symbol'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $script_transl[14] . "&nbsp;"; ?>
<input type="text" value="<?php echo $form['scorig'][$form['mov']];?>" maxlength="4" size="4" name="scorig<?php echo $form['mov'] ?>" onChange="this.form.submit()"><?php echo " %" . "&nbsp;&nbsp;&nbsp;"; ?></td></tr>
<tr><td style="font-size:5pt;" colspan="4"><?php echo $form['mov']+1; ?></td></tr>
 <?php
/* fine riattivo prezzo e sconto */

	 } $form['mov']=$form['nmov'];
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<



/*ANtonio Germani - visualizzo l'operatore */
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl[21]."</td><td class=\"FacetDataTD\" colspan=\"1\">".$form["adminid"]."</td>\n"; 
/* fine visualizzo l'operatore */
echo "</select></td></tr><tr><td colspan=\"1\"><input type=\"submit\" name=\"cancel\" value=\"" . $script_transl['cancel'] . "\">\n";

echo "<input type=\"submit\" name=\"Return\" value=\"" . $script_transl['return'] . "\">\n";
echo "</td><td align=\"right\" colspan=\"1\">\n";
if ($toDo !== 'update') { 
	If ($form['artico'][$form['mov']] <> "") {
		echo "<input type=\"submit\" name=\"Add_mov\" value=\"" . $script_transl['add'] . "\">\n";
	}
	If ($form['nmov']>0){
		echo "<input type=\"submit\" title=\"Togli ultimo movimento\" name=\"Del_mov\" value=\"X\">\n";
	}
}
if ($toDo == 'update') {
    echo '<input type="submit" accesskey="m" name="Insert" value="' . strtoupper($script_transl['update']) . '!"></td></tr><tr></tr>';
} else {
    echo '<input type="submit" accesskey="i" name="Insert" value="' . strtoupper($script_transl['insert']) . '!"></td></tr><tr></tr>';
}
echo "</td></tr></table>\n";
?>
</form>
<?php
require("../../library/include/footer.php");
?>