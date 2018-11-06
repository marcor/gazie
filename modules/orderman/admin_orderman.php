<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");
require ("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();$msg="";

$gForm = new magazzForm();

if (isset ($_GET['popup'])){ //controllo se proviene da una richiesta apertura popup
		$popup=$_GET['popup'];
	}
	else {
		$popup="";
	}
If (isset ($_GET['type'])){ // controllo se proviene anche da una richiesta del modulo camp
	$form['order_type']=$_GET['type'];
}

if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

if ((isset($_GET['Update']) and  !isset($_GET['codice'])) or isset($_POST['Return'])) {
    header("Location: ".$_POST['ritorno']);
    exit;
}

if ((isset($_POST['Insert'])) or ( isset($_POST['Update']))) {    // Antonio Germani se non e' il primo accesso

	$form=gaz_dbi_parse_post('orderman');
	
	$form['order_type']=$_POST['order_type'];
	$form['description'] = $_POST['description'];	
	$form['gioinp'] = $_POST['gioinp'];
	$form['mesinp'] = $_POST['mesinp'];
	$form['anninp'] = $_POST['anninp'];
	$form['day_of_validity'] = $_POST['day_of_validity'];
	$form["campo_impianto"] = $_POST["campo_impianto"];
	$form['order']=$_POST['order'];
	If (intval($form['order'])>0) {// se c'è un numero ordine lo importo
		$res = gaz_dbi_get_row($gTables['tesbro'],"numdoc",$form['order']);
		if (isset($res)) { // se esiste veramente l'ordine
			$res2 = gaz_dbi_get_row($gTables['rigbro'],"id_tes",$res['id_tes']);
			$form['artico']=$res2['codart'];
			$form['quanti']=$res2['quanti'];
			$form['id_tesbro']=$res['id_tes'];
			$form['id_rigbro']=$res2['id_rig'];
		} else { // se l'ordine non esiste ed è stato inserito un numero anomalo
			$form['artico']="";
			$form['quanti']=0;
			$form['id_tesbro']=0;
			$form['id_rigbro']=0;
			$form['order']=0;
		}
	} else {
		$form['artico']=$_POST['artico'];
		$form['quanti']=$_POST['quanti'];
		$form['id_tesbro']= 0;
		$form['id_rigbro']= 0;
		$form['order']=0;
	}
	$form['nmov']=$_POST['nmov'];
	$form['nmovdb']=$_POST['nmovdb'];
	for ($m = 0; $m <= $form['nmov']; ++$m){
		$form['staff'][$m] = $_POST['staff'.$m];
	}
	if ($toDo=="update") {// se update mantengo il codice staff memorizzato inizialmente nel data base
		for ($m = 0; $m <= $form['nmovdb']; ++$m){
			$form['staffdb'][$m] = $_POST['staffdb'.$m];
		}
	}		
	$form['filename']=$_POST['filename'];
	$form['identifier']=$_POST['identifier'];
	$form['expiry']=$_POST['expiry'];
	$form['lot_or_serial']=$_POST['lot_or_serial'];
	$form['datreg']=$_POST['datreg'];
	$form['id_movmag']=$_POST['id_movmag'];
	$form['id_lotmag']=$_POST['id_lotmag'];
	if (isset($_POST['numcomp'])){
		$form['numcomp']=$_POST['numcomp'];
	
		If ($form['numcomp']>0){
			for ($m = 0; $m <= $form['numcomp']-1; ++$m){
				if (!empty($_POST['id_lotmag'.$m])){
					$form['id_lotmag'][$m] = $_POST['id_lotmag'.$m];
					$form['artcomp'][$m] = $_POST['artcomp'.$m];
				}
			}
		}
	}
	

// Antonio Germani > questo serve per aggiungere o togliere un operaio
if (isset($_POST['add_staff'])){ 
	$form['nmov']=$_POST['nmov'];
	for ($m = 0; $m <= $form['nmov']; ++$m){
		$form['staff'][$m] = $_POST['staff'.$m];	
	}
	$form['nmov']=$form['nmov']+1;
	$form['staff'][$form['nmov']] = "";
}
if (isset($_POST['Del_mov'])) {
	$form['staff'][$form['nmov']] = "";
	If ($_POST['nmov']>0) {
		$form['nmov']=$form['nmov']-1;
	}
}

    // Se viene inviata la richiesta di conferma totale ...CONTROLLO ERRORI
	$form['datemi'] = $form['anninp'] . "-" . $form['mesinp'] . "-" . $form['gioinp'];
    if (isset($_POST['ins'])) {
		
		$itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
		if ($form['artico']<>"" && !isset($itemart)) { // controllo se codice articolo non esiste o se è nullo    
			$msg .= "20+";
			}   
       
       if (empty($form['description'])){  //descrizione vuota
             $msg .= "4+";
       } 
	   
	   if (strlen($form['order_type'])<3){  //tipo produzione vuota
             $msg .= "12+";
       } 
	   
	   if ($form['order_type']=="IND" or $form['order_type']=="ART") { // in produzione industriale e artigianale
		   if (strlen($form['artico'])==0){ // articolo vuoto
			   $msg .= "16+"; 
		   }
		   if ($form['quanti']==0){ // quantità vuota
			   $msg .= "17+";
		   } 
		   if ($form['staff'][0]>0 &&  $form['day_of_validity']>13) { // D. Lgs. 66/2003 > massimo ore giornaliere lavorabili = 13
			   $msg .= "18+";
		   }
	   }
	   
		if ($msg == "") {// nessun errore
	   
 // Antonio Germani >>>> inizio SCRITTURA dei database    §§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§

			// ricarico i dati dell'articolo che non sono nel form; li avrò in array $resartico
			$resartico = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
		
			if ($toDo=="update"){ // se è un update cancello eventuali precedenti file temporanei nella cartella tmp
						foreach (glob("../../modules/orderman/tmp/*") as $fn) { 
							unlink($fn);
						}
						$id_orderman=$_GET['codice'];
			} else { // se è insert
				if ($form['order_type']=="IND" or $form['order_type']=="ART") { // se produzione industriale 
					$query="SHOW TABLE STATUS LIKE '".$gTables['movmag']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_movmag = $row['Auto_increment']; // trovo l'ID che avrà il movimento di magazzino MOVMAG
				}
					$query="SHOW TABLE STATUS LIKE '".$gTables['orderman']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_orderman = $row['Auto_increment']; // trovo l'ID che avrà il movimento di produzione ORDERMAN
						
						if ($form['lot_or_serial']==1) {
							$query="SHOW TABLE STATUS LIKE '".$gTables['lotmag']."'"; unset($row); 
								$result = gaz_dbi_query($query);
								$row = $result->fetch_assoc();
								$id_lotmag = $row['Auto_increment']; // trovo l'ID che avrà il lotto
						}
						
					$query="SHOW TABLE STATUS LIKE '".$gTables['tesbro']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_tesbro = $row['Auto_increment']; // trovo l'ID che avrà TESBRO testata documento
					$query="SHOW TABLE STATUS LIKE '".$gTables['rigbro']."'"; unset($row); 
						$result = gaz_dbi_query($query);
						$row = $result->fetch_assoc();
						$id_rigbro = $row['Auto_increment']; // trovo l'ID che avrà RIGBRO rigo documento	
			}	
if ($form['order_type']=="AGR" or $form['order_type']=="RIC" or $form['order_type']=="PRF"){
	// escludo AGR RIC e PRF dal creare movimento di magazzino e lotti
}	else {			
// scrittura movimento di magazzino MOVMAG
			if ($toDo=="update"){ // se è update, aggiorno in ogni caso
				$query="UPDATE " . $gTables['movmag'] . " SET quanti = '".$form['quanti']."', datreg = '".$form['datreg']."', datdoc = '".$form['datemi']."', artico = '".$form['artico']."' , campo_coltivazione = '"  . $form['campo_impianto'] . "', id_orderman = '"  . $_GET['codice'] . "' , id_lotmag = '" .$form['id_lotmag']. "' WHERE id_mov ='".$form['id_movmag']."'"; 
				gaz_dbi_query ($query) ;
			}
			if ($toDo=="insert" && $form['order_type']=="IND"){ // se è insert, creo il movimento di magazzino solo se produzione industriale
				$query="INSERT INTO " . $gTables['movmag'] . "(type_mov,operat,datreg,tipdoc,desdoc,datdoc,artico,campo_coltivazione,quanti,id_orderman,id_lotmag,adminid) VALUES ('0', '1', '".$form['datreg']."', 'MAG', 'Produzione', '".$form['datemi']."', '".$form['artico']."', '".$form['campo_impianto']."', '".$form['quanti']."', '".$id_orderman."', '".$id_lotmag."', '".$admin_aziend['adminid']."')"; 
				gaz_dbi_query ($query) ;
			}
		 
//Antonio Germani - > inizio salvo LOTTO, se c'è lotto e se il prodotto li richiede
		
		if ($form['lot_or_serial']> 0 ) { // se l'articolo prevede un lotto 
		// ripulisco il numero lotto inserito da caratteri dannosi
			$form['identifier'] = (empty($form['identifier'])) ? '' : filter_var($form['identifier'], FILTER_SANITIZE_STRING);
			if (strlen ($form['identifier']) ==0) { // se non c'è il lotto lo inserisco con data e ora in automatico
			$form['identifier']=date("Ymd Hms");
			}
			if (strlen ($form['expiry']) ==0) { // se non c'è la scadenza la inserisco a zero in automatico
			$form['expiry']="0000-00-00 00:00:00";
			} 
		 // è un nuovo INSERT 
			if (strlen ($form['identifier']) >0 && $toDo=="insert") {
				$form['id_lotmag']=$id_lotmag; //inserisco il nuovo lotto che deve essere nuovo ad ogni inerimento di orderman
				gaz_dbi_query("INSERT INTO " . $gTables['lotmag'] . "(codart,id_movmag,identifier,expiry) VALUES ('". $form['artico'] . "','" . $id_movmag . "','" . $form['identifier'] . "','" . $form['expiry'] . "')");
				gaz_dbi_query ("UPDATE " . $gTables['movmag'] . " SET id_lotmag = '" .$form['id_lotmag']. "' WHERE id_mov ='".$form['id_movmag']."'") ; // aggiorno id_lotmag sul movmag	
			}		 
		 //  è un UPDATE 
		 
			if (strlen ($form['identifier']) >0  && $toDo=="update"){
				$resin = gaz_dbi_get_row($gTables['orderman'],"id",$_GET['codice']);
				$resin2 = gaz_dbi_get_row($gTables['lotmag'],"id",$resin['id_lotmag']);
				if ($resin2['identifier']==$form['identifier']){ // se ha lo stesso numero di lotto di quello precedentemente salvato faccio update di lotmag
					gaz_dbi_query("UPDATE " . $gTables['lotmag'] . " SET codart = '" . $form['artico'] . "' , id_movmag = '" . $form['id_movmag'] . "' , identifier = '" . $form['identifier'] . "' , expiry = '" . $form['expiry'] . "' WHERE id = '" . $form['id_lotmag'] . "'");
				} else { // se non è lo stesso numero, cancello il lotto iniziale e ne creo uno nuovo
					gaz_dbi_query("DELETE FROM ".$gTables['lotmag']." WHERE id = ".$resin['id_lotmag']);
					gaz_dbi_query("INSERT INTO " . $gTables['lotmag'] . "(codart,id_movmag,identifier,expiry) VALUES ('". $form['artico'] . "','" . $form['id_movmag'] . "','" . $form['identifier'] . "','" . $form['expiry'] . "')");
					
					$query="SHOW TABLE STATUS LIKE '".$gTables['lotmag']."'"; unset($row); 
					$result = gaz_dbi_query($query);
					$row = $result->fetch_assoc();
					$form['id_lotmag'] = $row['Auto_increment']-1; // vedo dove è stato salvato lotmag
					
					gaz_dbi_query ("UPDATE " . $gTables['movmag'] . " SET id_lotmag = '" .$form['id_lotmag']. "' WHERE id_mov ='".$form['id_movmag']."'") ; // aggiorno id_lotmag sul movmag			
				}
				
			}		
		}
		
// Antonio Germani - inizio salvo documento/CERTIFICATO lotto
		if ($toDo=="update") { // se è update lascio $form id_lotmag del form
			$form['id_lotmag'];// 
		} else { // se è insert nuovo metto il nuovo id cercat ad inizio salvataggio
			$form['id_lotmag']=$id_lotmag;
		}
		if (substr($form['filename'], 0, 7) <> 'lotmag_') { // se è stato cambiato il file, cioè il nome non inizia con lotmag e, quindi, anche se è un nuovo insert
			if (!empty($form['filename'])) { // e se ha un nome impostato nel form
				$tmp_file = "../../data/files/tmp/" . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $form['filename'];
				// sposto il file nella cartella definitiva, rinominandolo e cancellandolo dalla temporanea    
				$fd = pathinfo($form['filename']);
				rename($tmp_file, "../../data/files/" . $admin_aziend['company_id'] . "/lotmag_" . $form['id_lotmag'] . '.' . $fd['extension']);
			}
		} // altrimenti se il file non è cambiato, anche se è update, non faccio nulla
// <<< fine salvo lotti	
}			

			
// INIZIO gestione salvataggio database operai

for ($form['mov'] = 0; $form['mov'] <= $form['nmov']; ++$form['mov']){ // per ogni operaio

	If (intval($form['staff'][$form['mov']])>0){ // se il codice operaio esiste
		$id_worker=$form['staff'][$form['mov']]; //identificativo operaio
		
		// questa è la data documento iniziale >> $form['datdocin']
		$work_day= $form['anninp'] . "-" . $form['mesinp'] . "-" . $form['gioinp']; // giorno lavorato
		
		if ($form['day_of_validity']>8){
			$hours_normal=8; //ore lavorate normali 
			$hours_extra=$form['day_of_validity']-8; //ore lavorate extra 
			$id_work_type_extra=2;
		} else {
			$hours_normal=$form['day_of_validity'];
			$hours_extra=0;
			$id_work_type_extra=0;
		}
		$result2 = gaz_dbi_get_row($gTables['tesbro'],"id_orderman",$id_orderman); // prendo le ore della vecchia registrazione della produzione
		if ($result2['day_of_validity']>8){
			$hours_normal_pre=8;
			$hours_extra_pre=$result2['day_of_validity']-8;
		} else {
			$hours_normal_pre=$result2['day_of_validity'];
			$hours_extra_pre=0;
		}
		
// controllo se è una variazione movimento e se è stato cambiato l'operaio

		If ($form['nmov']<=$form['nmovdb'] && $toDo == "update" && $form['staffdb'][$form['mov']]<> $id_worker) { // se è update ed è stato cambiato l'operaio già memorizzato nel database
			
			If (strtotime($work_day) == strtotime($result2['datemi'])) {  // se non è stata cambiata la data della produzione
			
			// all'operaio che è stato sostituito, devo togliere le ore
				$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $form['staffdb'][$form['mov']]."' AND work_day ='".$work_day."' AND id_orderman ='".$id_orderman);
				If (isset($rin)) { // se confermato che esiste giorno e operaio sostituito, tolgo le ore lavorate memorizzate in precedenza
					$ore_normal= $rin['hours_normal'] - $hours_normal_pre;
					$ore_extra= $rin['hours_extra'] - $hours_extra_pre;
					if ($hours_extra==0) {
						$id_work_type_extra="";
					} else {
						$id_work_type_extra=2;
					} 
					// e faccio l'UPDATE 
					$query = "UPDATE " . $gTables['staff_worked_hours'] . " SET hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', id_orderman = '', hours_extra = '".$ore_extra."' WHERE id_staff = '".$form['staffdb'][$form['mov']]."' AND work_day = '".$work_day."' AND id_orderman = '".$id_orderman."'";
					gaz_dbi_query($query);
				}
			// al nuovo operaio devo aggiungere le ore lavorate
				$r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day."' AND id_orderman ='".$id_orderman);
				If (isset($r)) { // se esiste il nuovo operaio nel giorno e con id_orderman, vedo se ci sono ore lavorate in precedenza e ci aggiungo quelle della produzione
					$ore_normal=$r['hours_normal']+$hours_normal;
					$ore_extra=$r['hours_extra']+$hours_extra;
				}	
					if ($ore_normal>8) {
						$ore_extra=$ore_extra+($ore_normal-8);
						$ore_normal=8;
					}
					If ($ore_extra>0){
						$id_work_type_extra=2;
					} else {
						$id_work_type_extra="";
					}
			// salvo ore su nuovo operaio			
				$exist=gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = ".$id_worker."' AND id_orderman ='".$id_orderman );
				if ($exist>=1){ // se ho già un record del lavoratore per quella data e con quel id_orderman faccio UPDATE
					$query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff ='.$id_worker.", id_orderman = '".$id_orderman."', work_day = '".$work_day."', hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', hours_extra = '".$ore_extra."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."' AND id_orderman = '".$id_orderman."'";
					gaz_dbi_query($query);
				} else { // altrimenti faccio l'INSERT
					$v=array();
					$v['id_staff']=$id_worker;
					$v['work_day']=$work_day;
					$v['hours_normal']=$hours_normal;
					$v['hours_extra']=$hours_extra;
					$v['id_orderman']=$id_orderman;
					$v['id_work_type_extra']=$id_work_type_extra;
					gaz_dbi_table_insert('staff_worked_hours', $v);
				}					
			
			} else { // se è stata cambiata la data di produzione

			// all'operaio che è stato sostituito, devo togliere le ore al giorno in cui gli erano state date
				$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $form['staffdb'][$form['mov']]."' AND work_day ='".$result2['datemi']."' AND id_orderman ='".$id_orderman);
				If (isset($rin)) { // se confermato che esiste giorno e operaio sostituito con quell' id_ordeman, tolgo le ore lavorate memorizzate in precedenza 
					$ore_normal=$rin['hours_normal']-$hours_normal_pre;
					$ore_extra=$rin['hours_extra']-$hours_extra_pre;
					if ($ore_extra==0){
						$id_work_type_extra="";
					} else {
						$id_work_type_extra=2;
					}
					// e faccio l'UPDATE 
					$query = "UPDATE " . $gTables['staff_worked_hours'] . " SET hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', id_orderman = '', hours_extra = '".$ore_extra."' WHERE id_staff = '".$form['staffdb'][$form['mov']]."' AND work_day = '".$result2['datemi']." AND id_orderman = '".$id_ordeman."'";
					gaz_dbi_query($query);
				}		
			// al nuovo operaio devo aggiungere le ore lavorate nel nuovo giorno di produzione
				$r = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day."' AND id_orderman ='".$id_orderman);
				If (isset($r)) { // se esiste giorno e nuovo operaio con id_orderman, vedo se ci sono ore lavorate in precedenza e ci aggiungo quelle della produzione
					$ore_normal=$r['hours_normal']+$hours_normal;
					$ore_extra=$r['hours_extra']+$hours_extra;
				}
					if ($ore_normal>8) {
						$ore_extra=$ore_extra+($ore_normal-8);
						$ore_normal=8;
					}
					If ($ore_extra>0){
						$id_work_type_extra=2;
					} else {
						$id_work_type_extra="";
					}
				
			// salvo ore su nuovo operaio			
				$exist=gaz_dbi_record_count($gTables['staff_worked_hours'], "work_day = '" . $work_day . "' AND id_staff = ".$id_worker."' AND id_orderman ='".$id_orderman );
				if ($exist>=1){ // se ho già un record del lavoratore per quella data e con quel id_orderman faccio UPDATE
					$query = 'UPDATE ' . $gTables['staff_worked_hours'] . ' SET id_staff ='.$id_worker.", id_orderman = '".$id_orderman."', work_day = '".$work_day."', hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', hours_extra = '".$ore_extra."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."' AND id_orderman = '".$id_orderman."'";
					gaz_dbi_query($query);
				} else { // altrimenti faccio l'INSERT
					$v=array();
					$v['id_staff']=$id_worker;
					$v['work_day']=$work_day;
					$v['hours_normal']=$hours_normal;
					$v['hours_extra']=$hours_extra;
					$v['id_orderman']=$id_orderman;
					$v['id_work_type_extra']=$id_work_type_extra;
					gaz_dbi_table_insert('staff_worked_hours', $v);
				}
			}				
		} else { // se non è stato cambiato operaio ed è sempre update
			If ($toDo == "update" && $form['staffdb'][$form['mov']] == $id_worker && $form['nmov']<=$form['nmovdb']) { // se è update e NON è stato cambiato l'operaio del database e non è un nuovo aggiunto
			
				If (strtotime($work_day) <> strtotime($result2['datemi'])) { // se è stata cambiata la data
			
				// tolgo le ore al giorno iniziale e gli azzero pure il riferimento alla produzione perché non è più fatta in quel giorno, quindi id_orderman=""
					$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$result2['datemi']."' AND id_orderman ='".$id_orderman);
					If (isset($rin)) { // se esiste giorno e operaio gli tolgo le ore memorizzate in precedenza
						$ore_normal=$rin['hours_normal']-$hours_normal_pre;
						$ore_extra=$rin['hours_extra']-$hours_extra_pre;
					  
						if ($ore_extra==0) {
							$id_work_type_extra="";
						} else {
							$id_work_type_extra=2;
						}
						$query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', id_orderman = '', hours_extra = '".$ore_extra."' WHERE id_staff = '".$id_worker."' AND work_day = '".$result2['datemi']."' AND id_orderman= '".$id_orderman."'";
						gaz_dbi_query($query);
					}					
									
					$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day."' AND id_orderman ='".$id_orderman);
					If (isset($rin)) { // se esiste giorno e operaio con id_orderman gli aggiungo le ore
						$ore_normal=$rin['hours_normal']+$hours_normal;
						$ore_extra=$rin['hours_extra']+$hours_extra;
						if ($ore_normal>8) {
							$ore_extra=$ore_extra+($ore_normal-8);
							$ore_normal=8;
						}
						If ($ore_extra>0){
							$id_work_type_extra=2;
						} else {
						$id_work_type_extra="";
						}
						
						$query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', hours_extra = '".$ore_extra."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."' AND id_orderman = '".$id_orderman."'";
						gaz_dbi_query($query);
					} else { // altrimenti faccio l'INSERT
						$v=array();
						$v['id_staff']=$id_worker;
						$v['work_day']=$work_day; 
						$v['hours_normal']=$hours_normal;
						$v['id_orderman']=$id_orderman;
						$v['hours_extra']=$hours_extra;
						$v['id_work_type_extra']=$id_work_type_extra;
						gaz_dbi_table_insert('staff_worked_hours', $v);
					}		
		
				} else { //se NON è stata cambiata la data aggiorno solo le ore
					
					$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day."' AND id_orderman ='".$id_orderman); 
					If (isset($rin)) { // se esiste giorno e operaio con id_orderman gli modifico le ore nello stesso giorno
						$ore_normal= $rin['hours_normal'] - $hours_normal_pre + $hours_normal; 
						$ore_extra= $rin['hours_extra'] - $hours_extra_pre + $hours_extra;
						if ($ore_normal>8) {
							$ore_extra=$ore_extra+($ore_normal-8);
							$ore_normal=8;
						}
						If ($ore_extra>0){
							$id_work_type_extra=2;
						} else {
						$id_work_type_extra="";
						}       
						$query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', hours_extra = '".$ore_extra."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."' AND id_orderman = '".$id_orderman."'";
						gaz_dbi_query($query);
					}	else { // altrimenti faccio l'INSERT perché è stato aggiunto operaio in update senza operai
						$v=array();
						$v['id_staff']=$id_worker;
						$v['work_day']=$work_day;
						$v['hours_normal']=$hours_normal;
						$v['id_orderman']=$id_orderman;
						$v['hours_extra']=$hours_extra;
						$v['id_work_type_extra']=$id_work_type_extra;
						gaz_dbi_table_insert('staff_worked_hours', $v);
					}
				}
			}	
		}
		If ($toDo=="update" && $form['nmov']>$form['nmovdb'] && $form['staffdb'][$form['mov']] <> $id_worker){ // se è update ed è stato aggiunto un nuovo operaio a quelli esistenti e si tratta proprio di quello aggiunto
			$rin = gaz_dbi_get_row($gTables['staff_worked_hours'], "id_staff ", $id_worker."' AND work_day ='".$work_day."' AND id_orderman ='".$id_orderman);
			If (isset($rin)) { // se esiste giorno e operaio con id_ordeman gli aggiungo le ore e aggiorno database
				$ore_normal=$rin['hours_normal']+$hours_normal;
				$ore_extra=$rin['hours_extra']+$hours_extra;
				if ($ore_normal>8) {
					$ore_extra=$ore_extra+($ore_normal-8);
					$ore_normal=8;
				}
				If ($ore_extra>0){
					$id_work_type_extra=2;
				} else {
					$id_work_type_extra="";
				}
				$query = 'UPDATE ' . $gTables['staff_worked_hours'] . " SET hours_normal = '".$ore_normal."', id_work_type_extra = '".$id_work_type_extra."', hours_extra = '".$ore_extra."', id_orderman = '".$id_orderman."' WHERE id_staff = '".$id_worker."' AND work_day = '".$work_day."'";
				gaz_dbi_query($query);
			} else { // altrimenti faccio l'INSERT
				$v=array();
				$v['id_staff']=$id_worker;
				$v['work_day']=$work_day;
				$v['hours_normal']=$hours_normal;
				$v['id_orderman']=$id_orderman;
				$v['hours_extra']=$hours_extra;
				$v['id_work_type_extra']=$id_work_type_extra;
				gaz_dbi_table_insert('staff_worked_hours', $v);
			}		
		}
		
		If ($toDo <> "update") { // se non è un update è per forza una nuova produzione, quindi devo fare un insert		
				// INSERT nuovo rigo su staff_worked_hours
					$v=array();
					$v['id_staff']=$id_worker;
					$v['work_day']=$work_day;
					$v['hours_normal']=$hours_normal;
					$v['id_orderman']=$id_orderman;
					$v['hours_extra']=$hours_extra;
					$v['id_work_type_extra']=$id_work_type_extra;
					gaz_dbi_table_insert('staff_worked_hours', $v);
							
		}
	}
}
// FINE gestione registrazione database operai 

// Antonio Germani - Inizio Scrittura produzione ORDERMAN e, se non già creati da un ordine, scrittura di TESBRO E RIGBRO
			if ($toDo == 'update') { //  se e' una modifica, aggiorno orderman e tesbro
			
				$query="UPDATE ".$gTables['orderman']." SET ".'order_type'." = '".$form['order_type']."', ".'description'." = '".$form['description']."', ".'campo_impianto'." = '".$form["campo_impianto"]."', ".'id_lotmag'." = '".$form['id_lotmag']."', ".'add_info'." = '".$form['add_info']."' WHERE id = '".$form['id']."'";
				gaz_dbi_query($query);
				$resin=gaz_dbi_get_row($gTables['tesbro'],"id_orderman",$id_orderman);
				
				if ($resin['id_tes']<>$form['id_tesbro']) { // se l'ordine iniziale è diverso da quello del form
					if ($resin['tipdoc']=="PRO") { // se era autogenerato, cioè era PRO, lo cancello e basta perché vuol dire che è stato tolto completamente dal form o sostituito con un vero ordine VOR
						gaz_dbi_query("DELETE FROM ".$gTables['tesbro']." WHERE id_orderman = ".$id_orderman);
						// devo cancellare anche il relativo rigo rigbro ad esso connesso
						gaz_dbi_query("DELETE FROM ".$gTables['rigbro']." WHERE id_tes = ".$resin['id_tes']);					
					} else {// se il numero ordine iniziale non era PRO, cioè era un ordine vero, gli azzero solo id orderman
						gaz_dbi_query("UPDATE ".$gTables['tesbro']." SET id_orderman = '' WHERE id_tes = '".$resin['id_tes']."'"); 
					}
					$query="UPDATE ".$gTables['orderman']." SET ".'id_tesbro'." = '', ".'id_rigbro'." = '' WHERE id = '".$form['id']."'";// azzero anche i riferimenti su orderman
					gaz_dbi_query($query);
					
					if ($form['id_tesbro']>0) { // poi, se c'è un nuovo ordine VOR nel form, lo collego a id orderman 
						gaz_dbi_query("UPDATE ".$gTables['tesbro']." SET id_orderman = '".$id_orderman."' WHERE id_tes = '".$form['id_tesbro']."'");
						$query="UPDATE ".$gTables['orderman']." SET ".'id_tesbro'." = '".$form['id_tesbro']."', ".'id_rigbro'." = '".$form['id_rigbro']."' WHERE id = '".$form['id']."'";
						gaz_dbi_query($query); // aggiorno i riferimenti su orderman
						
					} else { // se non c'è un nuovo ordine lo creo in automatico in tesbro, rigbro e metto i riferimenti su orderman
						$query="SHOW TABLE STATUS LIKE '".$gTables['tesbro']."'"; unset($row); 
							$result = gaz_dbi_query($query);
							$row = $result->fetch_assoc();
							$id_tesbro = $row['Auto_increment']; // trovo l'ID che avrà TESBRO testata documento
						$query="SHOW TABLE STATUS LIKE '".$gTables['rigbro']."'"; unset($row); 
							$result = gaz_dbi_query($query);
							$row = $result->fetch_assoc();
							$id_rigbro = $row['Auto_increment']; // trovo l'ID che avrà RIGBRO rigo documento
						gaz_dbi_query("INSERT INTO " . $gTables['tesbro'] . "(tipdoc,day_of_validity,datemi,numdoc,id_orderman,status,adminid) VALUES ('PRO','" . $form['day_of_validity'] . "','" . $form['datemi'] . "', '".time()."', '" . $id_orderman ."', 'AUTOGENERA', '".$admin_aziend['adminid']."')"); // creo tesbro
						gaz_dbi_query("INSERT INTO " . $gTables['rigbro'] . "(id_tes,codart,descri,unimis,quanti) VALUES ('".$id_tesbro."','" . $form['artico'] . "','" . $resartico['descri'] . "','" . $resartico['unimis'] ."', '".$form['quanti']."')"); // creo rigbro
						$query="UPDATE ".$gTables['orderman']." SET ".'id_tesbro'." = '".$id_tesbro."', ".'id_rigbro'." = '".$id_rigbro."' WHERE id = '".$form['id']."'";
						gaz_dbi_query($query); // aggiorno i riferimenti su orderman
						
					}
					
					
					
				} else { // se il numero d'ordine NON è stato cambiato posso fare update solo se è PRO, cioè autogenerato
					if ($resin['tipdoc']=="PRO"){
						$res=gaz_dbi_get_row($gTables['rigbro'],"id_tes",$form['id_tesbro']);   
						if (isset($res)) { // se esiste il rigo lo aggiorno tesbro e rigbro
							$query="UPDATE ".$gTables['tesbro']." SET ".'datemi'." = '".$form['datemi']."', ".'day_of_validity'." = '".$form['day_of_validity']."', id_orderman = '".$id_orderman."' WHERE id_tes = '".$form['id_tesbro']."'";
							$res = gaz_dbi_query($query);							
							$query="UPDATE ".$gTables['rigbro']." SET ".'codart'." = '".$form['artico']."', ".'descri'." = '".$resartico['descri']."', ".'unimis'." = '".$resartico['unimis']."', ".'quanti'." = '".$form['quanti']."' WHERE id_tes = '".$form['id_tesbro']."'";
							$res = gaz_dbi_query($query);
						}
					}
				}
				
			} else { // e' un nuovo inserimento
												// creo e salvo ORDERMAN
				gaz_dbi_query("INSERT INTO " . $gTables['orderman'] . "(order_type,description,add_info,id_tesbro,id_rigbro,campo_impianto,id_lotmag,adminid) VALUES ('". $form[	'order_type'] . "','" . $form['description'] . "','" . $form['add_info'] . "','" . $id_tesbro . "', '" . $id_rigbro . "', '" . $form['campo_impianto'] . "', '" . $form['id_lotmag'] . "', '". $admin_aziend['adminid'] ."')");
				
				if (intval($form['order'])<=0){ // se non c'è un numero ordine ne creo uno fittizio in TESBRO e RIGBRO
					gaz_dbi_query("INSERT INTO " . $gTables['tesbro'] . "(tipdoc,day_of_validity,datemi,numdoc,id_orderman,status,adminid) VALUES ('PRO','" . $form['day_of_validity'] . "','" . $form['datemi'] . "', '".time()."', '" . $id_orderman ."', 'AUTOGENERA', '".$admin_aziend['adminid']."')");
					gaz_dbi_query("INSERT INTO " . $gTables['rigbro'] . "(id_tes,codart,descri,unimis,quanti) VALUES ('".$id_tesbro."','" . $form['artico'] . "','" . $resartico['descri'] . "','" . $resartico['unimis'] ."', '".$form['quanti']."')");
				} else { // se c'è l'ordine lo collego ad orderman
					$query="UPDATE ".$gTables['tesbro']." SET ".'id_orderman'." = '".$id_orderman."' WHERE id_tes = '".$form['id_tesbro']."'";
					$res = gaz_dbi_query($query);
				}
			}
// fine Orderman tesbro e rigbro			
			
			
		// se sono in un popup lo chiudo dopo aver salvato tutto	
			if($popup==1){
				echo "<script> 
				window.opener.location.reload(true);
				window.close();</script>";
			}else {
				header("Location: ".$_POST['ritorno']);
				}
				exit;
			
		}
	}
	
//  fine scrittura database §§§§§§§§§§§§§§§§§§§§§§§§§§§§



} elseif ((!isset($_POST['Update'])) and ( isset($_GET['Update']))) { //se e' il primo accesso per UPDATE

$result = gaz_dbi_get_row($gTables['orderman'],"id",$_GET['codice']);
	$form['ritorno'] = $_POST['ritorno'];
	$form['id']=$_GET['codice'];
    $form['order_type']=$result['order_type'];
    $form['description']=$result['description'];
    $form['id_tesbro']=$result['id_tesbro'];
	$form['id_rigbro']=$result['id_rigbro'];
	$form['add_info']=$result['add_info'];
$result4 = gaz_dbi_get_row($gTables['movmag'],"id_orderman",$_GET['codice']);	
	$form['datreg']=$result4['datreg'];
	$form['quanti']=$result4['quanti'];
	$form['id_movmag']=$result4['id_mov'];	
$result2 = gaz_dbi_get_row($gTables['tesbro'],"id_tes",$result['id_tesbro']);
	$form['gioinp'] = substr($result2['datemi'], 8, 2);
	$form['mesinp'] = substr($result2['datemi'], 5, 2);
	$form['anninp'] = substr($result2['datemi'], 0, 4);	
	$form['datemi']=$result2['datemi'];
	$form['day_of_validity']=$result2['day_of_validity'];
	$form['campo_impianto']=$result['campo_impianto'];
	$form['id_lotmag']=$result['id_lotmag'];
	$form['order']=$result2['numdoc'];
$result3 = gaz_dbi_get_row($gTables['rigbro'],"id_rig",$result['id_rigbro']);
	$form['artico']=$result3['codart'];
	$form['quanti']=$result3['quanti']; // sovrascrive la quantità presente nel movmag se c'è un ordine a riferimento
$result5 = gaz_dbi_get_row($gTables['lotmag'],"id",$result['id_lotmag']);		
	$form['identifier']=$result5['identifier'];
	$form['expiry']=$result5['expiry'];
// Antonio Germani - se è presente, recupero il file documento lotto
	$form['filename'] = "";
	If (file_exists('../../data/files/' . $admin_aziend['company_id'])>0) {		
		// recupero il filename dal filesystem 
		$dh = opendir('../../data/files/' . $admin_aziend['company_id']);
		while (false !== ($filename = readdir($dh))) {
			$fd = pathinfo($filename); 
			$r = explode('_', $fd['filename']); 
			if ($r[0] == 'lotmag' && $r[1] == $result['id_lotmag']) {
				// riassegno il nome file 
				$form['filename'] = $fd['basename'];
			} 
		}
	} 	
// se presenti, prendo gli operai
	$query="SELECT ".'*'." FROM ".$gTables['staff_worked_hours']. " WHERE id_orderman ='". $_GET['codice']."'";
	$result6 = gaz_dbi_query($query);$form['mov']=0;$form['nmov']=0;$form['nmovdb']=0;$form['staff'][$form['mov']]="";$form['staffdb'][$form['mov']]="";
	if ($result6->num_rows >0){
	while($row = $result6->fetch_assoc()){
		$form['staff'][$form['mov']]=$row['id_staff'];
		$form['staffdb'][$form['mov']]=$row['id_staff'];
		$form['mov']++;
	}
	$form['nmov']=$form['mov']-1;
	$form['nmovdb']=$form['mov']-1;
	}
	

} else { //se e' il primo accesso per INSERT
    
	$form['ritorno'] = $_SERVER['HTTP_REFERER'];
	If (isset ($_GET['type'])){ // controllo se proviene anche da una richiesta del modulo camp
		$form['order_type']=$_GET['type'];
	} else {
		$form['order_type']="";
	}
    $form['description']="";
    $form['id_tesbro']="";
	$form['add_info']="";
	$form['gioinp'] = date("d");
    $form['mesinp'] = date("m");
    $form['anninp'] = date("Y");
	$form['day_of_validity']="";
	$form["campo_impianto"] ="";
	$form['order']="";
	$form['artico']="";
	$form['mov']=0;
	$form['nmov']=0;
	$form['nmovdb']=0;
	$form['staff'][$form['mov']]="";
	$form['filename']="";
	$form['identifier']="";
	$form['expiry']="";
	$form['lot_or_serial']="";
	$form['datreg']=date("Y-m-d");
	$form['quanti']="";
	$form['id_movmag']="";
	$form['id_lotmag']="";
	$form['numcomp']=0;
}
If (isset($_POST['Cancel'])){ // se è stato premuto ANNULLA
	$form['hidden_req'] = ''; 
	$form['order_type']="";
    $form['description']="";
    $form['id_tesbro']="";
	$form['add_info']="";
	$form['gioinp'] = date("d");
    $form['mesinp'] = date("m");
    $form['anninp'] = date("Y");
	$form['day_of_validity']="";
	$form["campo_impianto"] ="";
	$form['order']="";
	$form['artico']="";
	$form['mov']=0;
	$form['nmov']=0;
	$form['nmovdb']=0;
	$form['staff'][$form['mov']]="";
	$form['filename']="";
	$form['identifier']="";
	$form['expiry']="";	
	$form['quanti']="";
	$form['id_movmag']="";
	$form['id_lotmag']="";
	$form['numcomp']=0;
}
if (!empty ($_FILES['docfile_']['name'])) { // Antonio Germani - se c'è un nome in $_FILES
	$prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'];
	foreach (glob("../../data/files/tmp/" . $prefix . "_*.*") as $fn) {// prima cancello eventuali precedenti file temporanei
             unlink($fn);
    }
	$mt = substr($_FILES['docfile_']['name'], -3);
	if (($mt == "png" || $mt == "odt" || $mt == "peg" || $mt == "jpg" || $mt == "pdf") && $_FILES['docfile_']['size'] > 1000){ // se rispetta limiti e parametri lo salvo nella cartella tmp
		move_uploaded_file($_FILES['docfile_']['tmp_name'], '../../data/files/tmp/' . $prefix . '_' . $_FILES['docfile_']['name']);                
		$form['filename']=$_FILES['docfile_']['name'];
	} else {
		$msg .= "14+";
	}
}

require("../../library/include/header.php");
$script_transl = HeadMain();
if ($toDo == 'update') {
   $title = ucwords($script_transl['upd_this'])." n.".$form['id'];
} else {
   $title = ucwords($script_transl['ins_this']);
}

print "<form method=\"POST\" enctype=\"multipart/form-data\">\n";
print "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">\n";
print "<input type=\"hidden\" name=\"id_tesbro\" value=\"".$form['id_tesbro']."\">\n";
print "<input type=\"hidden\" value=\"".$_POST['ritorno']."\" name=\"ritorno\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">$title</div>";
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
if ($toDo == 'update') {
   print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[0]</td><td class=\"FacetDataTD\"><input type=\"hidden\" name=\"id\" value=\"".$form['id']."\" />".$form['id']."</td></tr>\n";
}

// Antonio Germani > inserimento tipo di produzione 
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td><td class=\"FacetDataTD\">";
	
?>
<script>
  $(function() {
    $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
});
</script>

<?php if ($toDo=="insert"){
	?>
	<select name="order_type" onchange="this.form.submit()" >
	<option <?php if ($form['order_type'] == "" ) echo 'selected' ; ?> value="">--</option>
	<option <?php if ($form['order_type'] == "AGR" ) echo 'selected' ; ?> value="AGR">Agricola</option>
	<option <?php if ($form['order_type'] == "IND" ) echo 'selected' ; ?> value="IND">Industriale</option>
	<option <?php if ($form['order_type'] == "RIC" ) echo 'selected' ; ?> value="RIC">Ricerca e sviluppo</option>
	<option <?php if ($form['order_type'] == "PRF" ) echo 'selected' ; ?> value="PRF">Professionale</option>
	<option <?php if ($form['order_type'] == "ART" ) echo 'selected' ; ?> value="ART">Artigianale</option>
	</select>
<?php 
	} else {
		echo $form['order_type'],"&nbsp &nbsp";
		echo '<input type="hidden" name="order_type" value="'.$form['order_type'].'">';
	}

	if ($form['order_type']=="IND") {
		echo '<label>' . 'Data registrazione: ' . ' </label><input class="datepicker" type="text" onchange="this.form.submit();" name="datreg"  value="' . $form['datreg']. '">';
	} else {
		echo '<input type="hidden" name="datreg" value="">';
		if ($form['order_type'] != ""){
			echo "Non registra magazzino!";
		}
	}
?>
</td></tr>
<?php

if ($form['order_type']<>"AGR") { // input esclusi se produzione agricola

// Antonio Germani > inserimento ordine	
?>
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['8']; ?> </td>
<!-- Antonio Germani inizio script autocompletamento ORDINI dalla tabella mysql tesbro	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['tesbro'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		if (intval($row['clfoco'])>0){
		$resforname = gaz_dbi_get_row($gTables['clfoco'],"codice",$row['clfoco']);	
		} else {
			$resforname['descri']="AUTO";
		}
		$stringa.="\"".$row['numdoc']." - ".$resforname['descri']."\", ";			
	}
	$stringa=substr($stringa,0,-1);
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
	
	<td colspan="2" class="FacetDataTD">
		<input id="autocomplete" type="text" name="order" onchange="this.form.submit()" Value="<?php echo $form['order']; ?>"/>
	</td>
</tr>

<!-- Antonio Germani > inserimento articolo	-->
<tr> 
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['9']; ?> </td>
<!-- Antonio Germani inizio script autocompletamento ARTICOLO dalla tabella mysql artico	-->	
  <script>
	$(document).ready(function() {
	$("input#autocomplete2").autocomplete({
		source: [<?php
	$stringa="";
	$query="SELECT * FROM ".$gTables['artico'];
	$result = gaz_dbi_query($query);
	while($row = $result->fetch_assoc()){
		$stringa.="\"".$row['codice']."\", ";		
	}
	$stringa=substr($stringa,0,-1);
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
	
	<td colspan="2" class="FacetDataTD">
		<input id="autocomplete2" type="text" name="artico" Value="<?php echo $form['artico']; ?>"/>
	<?php // prendo i dati dall'articolo
		$resartico = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
		echo $resartico['descri'];// visualizzo la descrizione
		echo '<input type="hidden" name="lot_or_serial" value="' . $resartico['lot_or_serial'] . '"';$form['lot_or_serial']=$resartico['lot_or_serial'];
		
		if ($resartico['good_or_service']==2){ // se è un articolo composto
			?>
			<div class="container-fluid">
			<div class="row" style="margin-left: 0px;">
			<div class="col-sm-12" align="center" style="border:1px solid red">Articolo composto
			</div>
			</div>
			<?php
			$query="SELECT * FROM ".$gTables['distinta_base']." WHERE codice_composizione = '".$form['artico']."'";
			$rescompo = gaz_dbi_query($query);
			
			
			$nc=0;
			while($row = $rescompo->fetch_assoc()){ // visualizzo i componenti e li memorizzo nel form
				$mv = $gForm->getStockValue(false, $row['codice_artico_base']);
				$magval = array_pop($mv); // controllo disponibilità in magazzino
				
				?>
				<input type="hidden" name="artcomp<?php echo $nc; ?>" value="<?php echo $row['codice_artico_base']; ?>">
				
					<div class="row" style="margin-left: 0px;">
						<div class="col-sm-3 "  style="background-color:lightcyan;"><?php echo $row['codice_artico_base'];?>
						</div>
						<div class="col-sm-4 "  style="background-color:lightcyan;"><?php echo "Necessari: ",gaz_format_quantity($row['quantita_artico_base'],0,$admin_aziend['decimal_quantity']);?>
						</div>
						<div class="col-sm-4 "  style="background-color:lightcyan;"><?php echo "Disponibili: ",gaz_format_quantity($magval['q_g'],0,$admin_aziend['decimal_quantity']);?>
						</div>
						<?php						
						if ($magval['q_g']-$row['quantita_artico_base'] >= 0) {
						?>
							<div class="col-sm-1" style="background-color:lightgreen;"> OK</div>
						<?php
							
							
						} else {
							?>
							<div class="col-sm-1" style="background-color:red;"> KO</div>
							<?php
							
						}
						$artico = gaz_dbi_get_row($gTables['artico'], "codice", $row['codice_artico_base']); 
						if ($artico['lot_or_serial']==1){ // se il componente prevede lotti
							echo "lotto";
						/*Antonio Germani scelta lotto fra quelli esistenti  */
							$query="SELECT ".'*'." FROM ".$gTables['lotmag']. " WHERE codart ='". $row['codice_artico_base']."'";
							$result = gaz_dbi_query($query);
							if ($result->num_rows >0) { // se ci sono lotti attivo  selezione
								echo '<select name="id_lotmag'.$nc.'" class="FacetSelect" onchange="this.form.submit()">\n';
								echo "<option value=\"\">-seleziona fra lotti esistenti-</option>\n";	
								$sel=0;
								while ($rowlot = gaz_dbi_fetch_array($result)) {
									$selected = "";
									if ($form['id_lotmag'][$nc] == $rowlot['id']) {
										$selected = " selected ";$sel=1;
									}
								echo "<option value=\"" . $rowlot['id'] . "\"" . $selected . ">" . $rowlot['id'] . " - " . $rowlot['identifier'] . " - " . gaz_format_date($rowlot['expiry']) . "</option>\n";
								} 
								echo "</select>&nbsp;";
								 
							}
						// fine scelta lotto fra esistenti 
						} else { // se non prevede lotto azzero id_lotmag $nc
							echo '<input type="hidden" name="id_lotmag'.$nc.'" value="">';
						}
						
						?>
						
						
					</div> <!-- chiude row  -->
				</div>	
							
				<?php
				$nc=$nc+1;
			}
			echo '<input type="hidden" name="numcomp" value="'. $nc .'">';
		}
		
	?>	
	</td>
</tr>

<!--- Antonio Germani - inserimento quantità  -->
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['15']; ?> </td>
	<td colspan="2" class="FacetDataTD">
		<input type="text" name="quanti" onchange="this.form.submit()" value="<?php echo $form['quanti']; ?>" />
		<input type="hidden" name="id_movmag" value="<?php echo $form['id_movmag']; ?>">
	</td>
</tr>
<?php

} else { // se è produzione agricola
	print "<tr><td><input type=\"hidden\" name=\"order\" value=\"\">";
	print "<input type=\"hidden\" name=\"artico\" value=\"\">";
	print "<input type=\"hidden\" name=\"id_movmag\" value=\"\">";
	print "<input type=\"hidden\" name=\"quanti\" value=\"\"></td></tr>";
}

?>
<!--- Antonio Germani - inserimento descrizione  -->
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['2']; ?> </td>
	<td colspan="2" class="FacetDataTD">
		<textarea type="text" name="description" align="right" maxlength="255" cols="67" rows="3"><?php echo $form['description']; ?></textarea>
	</td>
</tr>
<?php
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[3]</td><td class=\"FacetDataTD\"><input type=\"text\" name=\"add_info\" value=\"".$form['add_info']."\" maxlength=\"80\" size=\"80\" /></td></tr>\n";
// DATA inizio produzione
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[5] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"gioinp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
    $selected = "";
    if ($counter == $form['gioinp'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesinp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
    $selected = "";
    if ($counter == $form['mesinp'])
        $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"anninp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
    $selected = "";
    if ($counter == $form['anninp'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td>\n";
// end data inizio produzione

// Antonio Germani > DURATA produzione
if ($form['order_type'] == "AGR") {
	print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[6]</td>";
} else {
	print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[11]</td>";
}

	print "<td class=\"FacetDataTD\"><input type=\"number\" name=\"day_of_validity\" min=\"0\" maxlength=\"3\" step=\"any\" size=\"3\" value=\"".$form['day_of_validity']."\"  /></td></tr>\n";
		
/*Antonio Germani LUOGO di produzione  */
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"campo_impianto\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
echo "<option value=\"\">-------------</option>\n";
$result = gaz_dbi_dyn_query("*", $gTables['campi']);
while ($row = gaz_dbi_fetch_array($result)) {
    $selected = "";
    if ($form["campo_impianto"] == $row['codice']) {
        $selected = " selected ";
    }
    echo "<option value=\"" . $row['codice'] . "\"" . $selected . ">" . $row['codice'] . " - " . $row['descri'] . "</option>\n";
} 
echo "</select></td></tr>";
if ($form['order_type']<>"AGR") { // input esclusi se produzione agricola
// Antonio Germani selezione operai


				if ($toDo=="update") {// mantengo il codice staff memorizzato inizialmente nel data base
					echo '<tr><td>';
					for ($form['mov'] = 0; $form['mov'] <= $form['nmovdb']; ++$form['mov']){
						echo '<input type="hidden" name="staffdb'.$form['mov'].'" value="' . $form['staffdb'][$form['mov']] . '">';
					}
					echo '</td></tr>';
				}

			for ($form['mov'] = 0; $form['mov'] <= $form['nmov']; ++$form['mov']){
		
				echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[10] . "</td><td class=\"FacetDataTD\">\n";
				echo '<select name="staff'.$form['mov'].'" class="FacetSelect" onchange="this.form.submit()">';	echo "<option value=\"\">-------------</option>\n";
				$result = gaz_dbi_dyn_query("*", $gTables['staff']);
				while ($row = gaz_dbi_fetch_array($result)) {
					$selected = ""; 
					if ($form['staff'][$form['mov']] == $row['id_staff']) {
						$selected = " selected ";
					}
					$anagra = gaz_dbi_get_row($gTables['clfoco'], "codice", $row['id_clfoco']); 
					echo "<option value=\"" . $row['id_staff'] . "\"" . $selected . ">" . $row['id_staff'] . " - " . $anagra['descri'] . "</option>\n"; 
				}
				
				;
				
				If ($form['staff'][$form['mov']] > 0) {
					echo "<input type=\"submit\" name=\"add_staff\" value=\"" . $script_transl[19] . "\">\n";
				}
				If ($form['mov']>0 && $form['mov']>$form['nmovdb']){ // se è update non si possono togliere gli operai già memorizzati nel database
				echo "<input type=\"submit\" title=\"Togli ultimo operaio\" name=\"Del_mov\" value=\"X\">\n";
				}
			
			} $form['mov']=$form['nmov'];
	echo "<input type=\"hidden\" name=\"nmovdb\" value=\"" . $form['nmovdb'] . "\">\n";		
	echo "<input type=\"hidden\" name=\"nmov\" value=\"" . $form['nmov'] . "\">\n</td></tr>";

// Antonio Germani > Inizio LOTTO in entrata o creazione nuovo	
	if (intval($form['lot_or_serial']) == 1) { // se l'articolo prevede il lotto apro la gestione lotti		
?>	  
		<tr><td class="FacetFieldCaptionTD"><?php echo $script_transl[13];?></td>
		<td class="FacetDataTD" >
		<input type="hidden" name="filename" value="<?php echo $form['filename']; ?>">
		<input type="hidden" name="id_lotmag" value="<?php echo $form['id_lotmag']; ?>">
<?php 	
              if (strlen($form['filename'])==0) {
                    echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog">'. 'Inserire nuovo certificato' . ' '.'<i class="glyphicon glyphicon-tag"></i>'
                    . '</button></div>';
			  } else { 
					echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog">'. $form['filename'] . ' '.'<i class="glyphicon glyphicon-tag"></i>'
                    . '</button>';
					echo '</div>';
				}		
 					
              if (strlen($form['identifier'])==0){
                    echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog_lot">' . 'Inserire nuovo Lotto' . ' ' . '<i class="glyphicon glyphicon-tag"></i></button></div>';
			  } else {
				  if(intval($form['expiry'])>0){
					echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot">' . $form['identifier']. ' ' . gaz_format_date($form['expiry']) . '<i class="glyphicon glyphicon-tag"></i></button></div>';
				  } else{
					 echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot" >' . $form['identifier']. '<i class="glyphicon glyphicon-tag" ></i></button></div>'; 
				  }
			  }		  
                echo '<div id="lm_dialog" class="collapse" >
                        <div class="form-group">
                          <div>';						
				?>
               <input type="file" onchange="this.form.submit();" name="docfile_">
			 </div>
		     </div>
             </div>
				<?php
			  
				 echo '<div id="lm_dialog_lot" class="collapse" >
                        <div class="form-group">
                          <div>';
				echo '<label>' . "Numero: " . '</label><input type="text" name="identifier" value="'.$form['identifier'].'" >';
				echo "<br>";			
                echo '<label>' . 'Scadenza: ' . ' </label><input class="datepicker" type="text" onchange="this.form.submit();" name="expiry"  value="' . $form['expiry']. '"></div></div></div>';
	} else { 
		echo '<tr><td><input type="hidden" name="filename" value="' . $form['filename'] . '">';
		echo '<input type="hidden" name="identifier" value="' . $form['identifier'] . '">';
		echo '<input type="hidden" name="id_lotmag" value="'.$form['id_lotmag'].'">';
		echo '<input type="hidden" name="expiry" value="' . $form['expiry'] . '"></td></tr>';
	
	}   
// fine LOTTI in entrata	
} else { //se è produzione agricola
	print "<tr><td><input type=\"hidden\" name=\"nmov\" value=\"0\">";
	print "<input type=\"hidden\" name=\"nmovdb\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"staff0\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"filename\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"expiry\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"identifier\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"id_lotmag\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"lot_or_serial\" value=\"\"></td></tr>";
	
}
	if ($popup<>1){
		//ANNULLA/RESET NON FUNZIONA DA RIVEDERE > print "<tr><td class=\"FacetFieldCaptionTD\"><input type=\"reset\" name=\"Cancel\" value=\"".$script_transl['cancel']."\">\n";
		print "<tr><td class=\"FacetDataTD\" align=\"right\">\n";
		print "<input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\">\n";
	} 
	else {
		print "<tr><td>";
	}
if ($toDo == 'update') {
   print '<input type="submit" accesskey="m" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.strtoupper($script_transl['update']).'!">';
} else {
   print '<input type="submit" accesskey="i" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="'.strtoupper($script_transl['insert']).'!">';
}
print "</td></tr></table>\n";
?>
</form>

<?php

require("../../library/include/footer.php");
?>