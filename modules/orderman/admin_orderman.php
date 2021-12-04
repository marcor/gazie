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
require ("../../library/include/datlib.inc.php");
require ("../../modules/magazz/lib.function.php");
require ("../../modules/vendit/lib.function.php");
require ("../../modules/camp/lib.function.php");
$admin_aziend = checkAdmin();
$msg = "";
$lm = new lotmag;
$magazz = new magazzForm();
$gForm = new ordermanForm();
$campsilos = new silos();
$warnmsg="";
$block_var="";
function gaz_select_data ( $nomecontrollo, $valore ) {
        $result_input = '<input size="8" type="text" id="'.$nomecontrollo.'" name="'.$nomecontrollo.'" value="'.$valore.'">';
        $result_input .= '<script>
        $(function () {
            $("#'.$nomecontrollo.'").datepicker({dateFormat: "dd-mm-yy", showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true})
        });</script>';
        return $result_input;
    }

function gaz_select_ora ( $nomecontrollo, $valore ) {
	$nomeora = $nomecontrollo."_ora";
	$nomeminuti = $nomecontrollo."_minuti";
	$valoreora = explode ( ":", $valore );

	$result_input = "<select name=\"".$nomeora."\" >\n";
	for ($counter = 0; $counter <= 23; $counter++) {
		$selected = "";
		if ($counter == $valoreora[0])
			$selected = ' selected=""';
		$result_input .=  "<option value=\"" . sprintf('%02d', $counter) . "\" $selected >" . sprintf('%02d', $counter) . "</option>\n";
	}
	$result_input .= "</select>\n ";
	// select dell'ora
	$result_input .= "<select name=\"".$nomeminuti."\" >\n";
	for ($counter = 0; $counter <= 59; $counter++) {
		$selected = "";
		if ($counter == $valoreora[1])
			$selected = ' selected=""';
		$result_input .= "<option value=\"" . sprintf('%02d', $counter) . "\" $selected >" . sprintf('%02d', $counter) . "</option>\n";
	}
	$result_input .= "</select>";
	return $result_input;
}

if (isset($_GET['popup'])) { //controllo se proviene da una richiesta apertura popup
    $popup = $_GET['popup'];
} else {
    $popup = "";
}
if (isset($_GET['type'])) { // controllo se proviene anche da una richiesta del modulo camp
    $form['order_type'] = substr($_GET['type'],0,3);
}
if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}
if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
if ((isset($_GET['Update']) and !isset($_GET['codice'])) or isset($_POST['Return'])) {
    header("Location: " . $_POST['ritorno']);
    exit;
}
if ((isset($_POST['Insert'])) || (isset($_POST['Update']))){ //Antonio Germani   **  Se non e' il primo accesso  **
    $form = gaz_dbi_parse_post('orderman');
    $form['order_type'] = $_POST['order_type'];
    $form['description'] = $_POST['description'];
    $form['add_info'] = $_POST['add_info'];
    $form['gioinp'] = $_POST['gioinp'];
    $form['mesinp'] = $_POST['mesinp'];
    $form['anninp'] = $_POST['anninp'];

	$form['iniprod'] = $_POST['iniprod'];
	$form['iniprodtime'] = $_POST['iniprodtime_ora'].":".$_POST['iniprodtime_minuti'];

	$form['fineprod'] = $_POST['fineprod'];
	$form['fineprodtime'] = $_POST['fineprodtime_ora'].":".$_POST['fineprodtime_minuti'];
    $form['day_of_validity'] = $_POST['day_of_validity'];
    $form["campo_impianto"] = $_POST["campo_impianto"];
    $form['quantip'] = $_POST['quantip'];
    $form['cosear'] = $_POST['cosear'];
    $form['codart'] = (isset($_POST['codart']))?$_POST['codart']:'';

    if (strlen ($_POST['cosear'])>0) {
		$resartico = gaz_dbi_get_row($gTables['artico'], "codice", $form['cosear']);
		$form['codart'] =($resartico)?$resartico['codice']:'';
	}  else {
		$resartico = gaz_dbi_get_row($gTables['artico'], "codice", $form['codart']);
	}
	if ($resartico) {
		$form['lot_or_serial'] = $resartico['lot_or_serial'];
		$form['SIAN'] = $resartico['SIAN'];
		$form['preacq'] = $resartico['preacq'];
		$form['quality'] = $resartico['quality'];
	} else {
		$form['lot_or_serial'] = '';
		$form['SIAN'] = '';
		$form['preacq'] = "";
		$form['quality'] = "";
	}
	$form['cod_operazione'] = $_POST['cod_operazione'];
    $form['recip_stocc'] = $_POST['recip_stocc'];
	$form['recip_stocc_destin'] = $_POST['recip_stocc_destin'];
	if (strlen($form['recip_stocc'])>0){ // se c'è un recipiente di stoccaggio prendo l'ID del lotto
		$idlotrecip=$campsilos->getLotRecip($form['recip_stocc'],$form['codart']); // è un array dove [0] è l'ID lotto e [1] è il numero lotto
		if ($form['cod_operazione']==5){ // se è una movimentazione interna SIAN limito la quantità a quella disponibile per l'ID lotto
			$qtaLotId = $lm -> dispLotID ($form['codart'], $idlotrecip[0], $_POST['id_movmag']);
			if ($form['quantip']>$qtaLotId){
				$form['quantip']=$qtaLotId; $warnmsg.="42+";
			}
		}
		if (intval($form['cod_operazione'] >0 AND intval($form['cod_operazione'])<4)) { // se sono operazioni che producono olio confezionato
		   $var_orig = $campsilos->getContentSil($form['recip_stocc']);
			unset($var_orig['varieta']['totale']);//tolgo il totale
			$var=implode(", ",array_keys($var_orig['varieta']));// creo l'elenco varietà
			if ($form['quality'] !== $var){ // se le varietà del silos non coincidono con quelle della confezione
				$warnmsg.= "44+";
			}
		}
	}
	if ($resartico && $resartico['good_or_service'] == 2) { // se è un articolo composto
		if ($toDo == "update") { //se UPDATE
			 // prendo i movimenti di magazzino dei componenti e l'unità di misura
			$where="operat = '-1' AND id_orderman = ". intval($_GET['codice']);
			$table = $gTables['movmag']." LEFT JOIN ".$gTables['artico']." on (".$gTables['movmag'].".artico = ".$gTables['artico'].".codice)";
			$result7 = gaz_dbi_dyn_query ($gTables['movmag'].".*, ".$gTables['artico'].".unimis", $table, $where );
		} else { // se INSERT
			// prendo i componenti che formerano l'articolo e l'unità di misura
			$where="codice_composizione = '" . $form['codart'] . "'";
			$table = $gTables['distinta_base']."
			LEFT JOIN ".$gTables['artico']." on (".$gTables['distinta_base'].".codice_artico_base = ".$gTables['artico'].".codice)";
            $rescompo = gaz_dbi_dyn_query ($gTables['distinta_base'].".*, ".$gTables['artico'].".*", $table, $where );
		}
	}

	if (intval($form['SIAN'])>0){ // se è una produzione SIAN se la data di questa produzione è antecedente a quella dell'ultimo file SIAN
		$uldtfile=getLastSianDay();
		if (strtotime($_POST['datreg']) < strtotime($uldtfile)){
			$warnmsg.="40+";
		}
	}

    $form['coseor'] = $_POST['coseor'];
    $quantiprod = 0;
    if (intval($form['coseor']) > 0) { // se c'è un numero ordine lo importo tramite l'id
        $res = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $form['coseor']);
		$form['order'] = $res['numdoc'];
		$form['id_tes'] = $res['id_tes'];
        if (isset($res)) { // se esiste veramente l'ordine ne prendo il rigo per l'articolo selezionato
            if (strlen($form['codart'])>0){//(se selezionato)
			$res2 = gaz_dbi_get_row($gTables['rigbro'], "id_tes", $res['id_tes'], "AND codart = '{$form['codart']}'");
            $form['quantipord'] = $res2['quanti'];
            $form['id_tesbro'] = $res['id_tes'];
            $form['id_rigbro'] = $res2['id_rig'];
			// prendo tutte le produzioni/orderman in cui c'è questo rigbro per conteggiare la quantità eventualmente già prodotta
            $query = "SELECT id FROM " . $gTables['orderman'] . " WHERE id_rigbro = '" . $res2['id_rig'] . "'";
            $resor = gaz_dbi_query($query);

            while ($row = $resor->fetch_assoc()) { // scorro tutte le produzioni/orderman trovate
                // per ogni orderman consulto movmag e conteggio le quantità per articolo già prodotte
                $rowmag = gaz_dbi_get_row($gTables['movmag'], "artico", $form['codart'], "AND operat = '1' AND id_orderman ='{$row['id']}'");
                $quantiprod = ($rowmag)?($quantiprod + $rowmag['quanti']):0;
            }

			}
        } else { // se l'ordine non esiste ed è stato inserito un numero anomalo
            $form['codart'] = "";
            $form['quantip'] = 0;
            $form['id_tesbro'] = 0;
            $form['id_rigbro'] = 0;
            $form['order'] = 0;
            $form['quantipord'] = 0;
        }
		if ($toDo == "update") { // se update importo il nome del cliente dell'ordine
			$res3 = gaz_dbi_get_row($gTables['clfoco'], "codice", $res['clfoco']);
		}
    } else {
		$form['id_tes'] ="";
        $form['id_tesbro'] = 0;
        $form['id_rigbro'] = 0;
        $form['order'] = 0;
        $form['quantipord'] = 0;
    }

    $form['filename'] = $_POST['filename'];
    $form['identifier'] = $_POST['identifier'];
    $form['expiry'] = $_POST['expiry'];

    if (strlen($_POST['datreg']) > 0) {
        $form['datreg'] = $_POST['datreg'];
    } else {
        $form['datreg'] = date("Y-m-d");
    }
    $form['id_movmag'] = $_POST['id_movmag'];
    $form['id_lotmag'] = $_POST['id_lotmag'];

    if (isset($_POST['numcomp'])) {
		$form['numcomp'] = $_POST['numcomp'];
        if ($form['numcomp'] > 0) {
			for ($m = 0;$m < $form['numcomp'];++$m) {
				$form['artcomp'][$m] = $_POST['artcomp' . $m];
				$form['SIAN_comp'][$m] = $_POST['SIAN_comp' . $m];
				$form['quality_comp'][$m] = $_POST['quality_comp' . $m];
                $form['quanti_comp'][$m] = $_POST['quanti_comp' . $m];
                $form['prezzo_comp'][$m] = $_POST['prezzo_comp' . $m];
                $form['q_lot_comp'][$m] = $_POST['q_lot_comp' . $m];
				$form['recip_stocc_comp'][$m] = $_POST['recip_stocc_comp' . $m];
				if (strlen($form['recip_stocc_comp'][$m])>0 AND intval($form['cod_operazione'] >0 AND intval($form['cod_operazione'])<4)) { // se sono operazioni che producono olio confezionato
				   $var_orig = $campsilos->getContentSil($form['recip_stocc_comp'][$m]);
					unset($var_orig['varieta']['totale']);//tolgo il totale
					$var=implode(", ",array_keys($var_orig['varieta']));// creo l'elenco varietà
					if ($form['quality_comp'][$m] !== $var){ // se le varietà del silos non coincidono con quelle della confezione
						$warnmsg.= "44+";$block_var="SI";
					}
				}
				if (isset($_POST['subtLot'. $m]) AND $form['q_lot_comp'][$m]>1){
					$form['q_lot_comp'][$m]--;
				}
				if (isset($_POST['addLot'. $m])){
					$form['q_lot_comp'][$m]++;
				}
				if (isset($_POST['manLot'. $m])) {
					$form['amLot'. $m] = $_POST['manLot'. $m];
				} elseif (isset($_POST['autoLot'. $m])) {
					$form['amLot'. $m] = $_POST['autoLot'. $m];
				} else {
					$form['amLot'. $m] = (isset ($_POST['amLot'. $m]))?$_POST['amLot'. $m]:'';
				}
				for ($n = 0;$n < $form['q_lot_comp'][$m];++$n) { // se q lot comp è zero vuol dire che non ci sono lotti
				    $form['id_lot_comp'][$m][$n] = (isset($_POST['id_lot_comp' . $m . $n]))?$_POST['id_lot_comp' . $m . $n]:0;
                    $form['lot_quanti'][$m][$n] = (isset($_POST['lot_quanti' . $m . $n]))?$_POST['lot_quanti' . $m . $n]:0;
                }
            }
        } else {
			$form['amLot0']="";
		}
    }

    // Se viene inviata la richiesta di conferma totale ... ******   CONTROLLO ERRORI   ******
    $form['datemi'] = $form['anninp'] . "-" . $form['mesinp'] . "-" . $form['gioinp'];
    if (isset($_POST['ins'])) {
        $itemart = gaz_dbi_get_row($gTables['artico'], "codice", $form['codart']);
        if ($form['codart'] <> "" && !isset($itemart)) { // controllo se codice articolo non esiste o se è nullo
            $msg.= "20+";
        }
		if ($itemart && $itemart['good_or_service'] == 2 && isset($form['numcomp'])) { // se articolo composto,
		//controllo se le quantità inserite per ogni singolo lotto, di ogni componente, corrispondono alla richiesta della produzione e alla reale disponbilità
            for ($nc = 0;$nc <= $form['numcomp'] - 1;++$nc) {
				if ($form['quanti_comp'][$nc] == "ERRORE"){
					$msg.= "43+";//Non c'è sufficiente disponibilità di un ID lotto selezionato
				}

				if (intval($form['q_lot_comp'][$nc])>0) {
					$tot=0;
					for ($l=0; $l<$form['q_lot_comp'][$nc]; ++$l) {
						if ($lm -> getLotQty($form['id_lot_comp'][$nc][$l]) < $form['lot_quanti'][$nc][$l]){
							$msg.= "21+";//Non c'è sufficiente disponibilità di un ID lotto selezionato
						}
						$tot=$tot + $form['lot_quanti'][$nc][$l];

						$checklot = gaz_dbi_get_row($gTables['lotmag']." LEFT JOIN ".$gTables['movmag']." ON ".$gTables['movmag'].".id_mov = id_movmag", 'id', $form['id_lot_comp'][$nc][$l]);
						if (strtotime($form['datreg']) < strtotime($checklot['datdoc']) ){// non può uscire un lotto prima della data della sua creazione
							$msg .= "45+";// Il lotto non può uscire in tale data in quanto ancora inesistente
						}
						//controllo se l'ID lotto è presente nel silos selezionato
						if (strlen($form['recip_stocc_comp'][$nc])>0){
							$var_idlot = $campsilos->getContentSil($form['recip_stocc_comp'][$nc]);
							unset($var_idlot['id_lotti']['totale']);//tolgo il totale
							$var=array_keys($var_idlot['id_lotti']);// creo array idlotti presenti nel silos
							if (!in_array($form['id_lot_comp'][$nc][$l], $var)){ // se l'id del lotto non è nel silos
								$msg.= "47+";
							}
						}
					}
					if ($tot != $form['quanti_comp'][$nc]){
						$msg.="25+";//La quantità inserita di un lotto, di un componente, è errata
					}
					if (intval($form['SIAN']) > 0 AND $form['SIAN_comp'][$nc] > 0 AND $campsilos -> getCont($form['recip_stocc_comp'][$nc]) < $form['quanti_comp'][$nc] AND intval($form['cod_operazione'])!==3){
						$msg.= "41+"; // il silos di origine non ha sufficiente quantità olio
					}

				}
            }
        }
        if ($form['order'] > 0) { // se c'è un numero ordine controllo che esista veramente l'ordine
            $itemord = gaz_dbi_get_row($gTables['tesbro'], "numdoc", $form['order']);
            if (!isset($itemord)) {
                $msg.= "23+";
                unset($itemord);
            }
            if (isset($_POST['okprod']) && $_POST['okprod'] <> "ok" && $toDo == "insert") {
                $msg.= "24+";
            }
        }
        if (empty($form['description'])) { //descrizione vuota
            // imposto la descrizione predefinita
            $descli=(isset($res3['descri']))?'/'.$res3['descri']:'';
			if (intval($form['coseor']) > 0){
				$form['description'] = "Produzione ".$form['codart']." ordine ".$form['coseor'].$descli;
			} else {
				$form['description'] = "Produzione ".$form['codart'];
			}
        }
        if (strlen($form['order_type']) < 3) { //tipo produzione vuota
            $msg.= "12+";
        }

        if ($form['order_type'] == "IND") { // in produzione industriale
            if (strlen($form['codart']) == 0) { // articolo vuoto
                $msg.= "16+";
            }

            if ($form['quantip'] == 0 || $form['quantip']=="" ) { // quantità produzione vuota
                $msg.= "17+";
            }

            if (intval($form['datreg']) == 0) { // se manca la data di registrazione
                $msg.= "22+";
            }
			if (intval($form['SIAN']) > 0 ){ // controlli SIAN
				if (intval($form['cod_operazione'])<1) { // se manca il codice operazione SIAN
					$msg.= "26+";
				}
				if (intval($form['cod_operazione'])==5){ // se M1 , movimentazione interna olio sfuso
					if (strlen ($form['recip_stocc_destin']) == 0 ) { // se M1 e manca il recipiente di destinazione
						$msg.= "27+";
					}
					if ($form['recip_stocc_destin']==$form['recip_stocc']) { // se M1 e i recipienti sono uguali
						$msg.= "28+";
					}
					$get_sil=gaz_dbi_get_row($gTables['camp_recip_stocc'],cod_silos,$form['recip_stocc_destin']);
					if ($campsilos -> getCont($form['recip_stocc_destin'])+$form['quantip'] > $get_sil['capacita']){// se non c'è spazio sufficiente nel recipiente di destinazione
						$msg.= "46+";
					}
				}
				if (intval($form['cod_operazione'])==3) { // se L2 l'olio prodotto può essere solo etichettato
					$rescampartico = gaz_dbi_get_row($gTables['camp_artico'], "codice", $form['codart']);
					if ($rescampartico['etichetta']==0){
						$msg.= "30+";
					}
				}
				if ($toDo == 'insert' AND (intval($form['cod_operazione'])>0 AND intval($form['cod_operazione'])<3 AND $form['numcomp']==0)){ // se confezioniamo
					$msg.= "39+"; // manca l'olio sfuso
				}
				if (intval($form['cod_operazione']>0 AND intval($form['cod_operazione'])<4)) { // se sono operazioni che producono olio confezionato
					$rescampartico = gaz_dbi_get_row($gTables['camp_artico'], "codice", $form['codart']);
					if ($rescampartico['confezione']==0){ // se l'olio è sfuso segnalo l'errore
						$msg.= "37+";
					}
				}
			}

			if ($toDo == 'insert' AND intval($form['SIAN']) > 0 AND (isset($form['numcomp']) AND $form['numcomp']>0)) { // se ci sono componenti faccio il controllo errori SIAN sui componenti
			    for ($m = 0;$m < $form['numcomp'];++$m) {
					$rescamparticocomp = gaz_dbi_get_row($gTables['camp_artico'], "codice", $form['artcomp'][$m]);
					if (isset($rescamparticocomp)){
						if (intval($form['cod_operazione'])==3 AND $rescamparticocomp['confezione']==0 ) { // se L2 etichettatura e c'è olio sfuso
							$msg.= "29+";
						}
						if (intval($form['cod_operazione'])==3 AND $rescamparticocomp['etichetta']==1 ) { // se L2 etichettatura e c'è olio etichettato
							$msg.= "32+";
						}
						if (intval($form['cod_operazione'])==3 AND ($rescamparticocomp['categoria']!== $rescampartico['categoria'] OR $rescamparticocomp['or_macro']!== $rescampartico['or_macro'] OR $rescamparticocomp['estrazione']!== $rescampartico['estrazione'] OR $rescamparticocomp['biologico']!== $rescampartico['biologico'] OR $rescamparticocomp['confezione']!== $rescampartico['confezione'] )) { // se L2 etichettatura e c'è olio non etichettato
							$msg.= "31+";
						}
						if ($rescamparticocomp['id_campartico']>0 AND strlen($form['recip_stocc_comp'][$m])==0 AND (intval($form['cod_operazione'])>0 AND intval($form['cod_operazione'])<3)){
						$msg.= "38+";
						}
					}
				}
			}
        }

        if ($msg == "") { // nessun errore
            // Antonio Germani >>>> inizio SCRITTURA dei database    §§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§§
			$start_work = date_format(date_create_from_format('d-m-Y', $form['iniprod']), 'Y-m-d')." ".$form['iniprodtime'];
			$end_work = date_format(date_create_from_format('d-m-Y', $form['fineprod']), 'Y-m-d')." ".$form['fineprodtime'];
            // i dati dell'articolo che non sono nel form li avrò nell' array $resartico
			$form['quantip']=gaz_format_quantity($form['quantip']);// trasformo la quantità per salvarla nel database

			if ($toDo == "update") { // se è un update cancello eventuali precedenti file temporanei nella cartella tmp
                foreach (glob("../../modules/orderman/tmp/*") as $fn) {
                    unlink($fn);
                }
                $id_orderman = intval($_GET['codice']);

				//aggiorno il movimento magazzino
				$form['id_orderman'] = $id_orderman; $form['quanti'] = $form['quantip']; $form['datdoc'] = $form['datemi']; $form['artico'] = $form['codart'];
				$update = array();
				$update[]="id_mov";
				$update[]=$form['id_movmag'];
				gaz_dbi_table_update('movmag', $update, $form);

				if ($form['SIAN']>0){ // Antonio Germani - aggiorno il movimento del SIAN
					$update = array();
					$update[]="id_movmag";
					$update[]=$form['id_movmag'];
					gaz_dbi_table_update('camp_mov_sian',$update,$form);
				}
            } else { // se è insert
                if (intval($form['order']) > 0) { // se c'è un ordine prendo gli id tesbro e rigbro esistenti nel form
                    $id_tesbro = $form['id_tesbro'];
                    $id_rigbro = $form['id_rigbro'];
                }
            }
            if ($form['order_type'] == "AGR" or $form['order_type'] == "RIC" or $form['order_type'] == "PRF") {
                // escludo AGR RIC e PRF dal creare movimento di magazzino e lotti
                $id_movmag="";
            } elseif ($toDo == "insert") {
				// e' un nuovo inserimento
                // creo e salvo ORDERMAN, tesbro e rigbro
                $status=0;
                if (intval($form['order']) <= 0) { // se non c'è un numero ordine ne creo uno fittizio in TESBRO e RIGBRO
					if (($form['order_type'] != "AGR") OR ($form['order_type'] == "AGR" AND strlen($form['codart'])>0)) { // le produzioni agricole creano un ordine fittizio solo se c'è un articolo
						$id_tesbro=tesbroInsert(array('tipdoc'=>'PRO','datemi'=>$form['datemi'],'numdoc'=>time(),'status'=>'AUTOGENERA','adminid'=>$admin_aziend['adminid']));
					}
					if ($form['order_type'] == "IND") { $status=9; } // una produzione industriale senza ordine a riferimento la chiudo perché prodotto per stoccaggio in magazzino
                } else { // se c'è l'ordine lo collego ad orderman
					tesbroUpdate(array('id_tes',$form['id_tesbro']), array('id_orderman'=>$id_orderman));

                    // usando i registri valorizzati per il form determino se devo mettere la produzione nello stato "9-chiuso" o lasciarla aperta
                    if (($quantiprod+$form['quantip'])>=$form['quantipord']) {  // ho prodotto di più o uguale a quanto richiesto dall'ordine specificato
                        $res = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $form['coseor']);
                        $res2 = gaz_dbi_get_row($gTables['rigbro'], "id_tes", $res['id_tes'], "AND codart = '".$form['codart']."'");
                        // prendo tutte le produzioni/orderman in cui c'è questo rigbro per conteggiare la quantità eventualmente già prodotta
                        $query = "SELECT id FROM " . $gTables['orderman'] . " WHERE id_rigbro = " . $res2['id_rig'];
                        $resor = gaz_dbi_query($query);
                        while ($row = $resor->fetch_assoc()) { // scorro tutte le produzioni/orderman trovate
                            // su ogni orderman precedente cambio lo stato
                            gaz_dbi_query("UPDATE " . $gTables['orderman'] . " SET stato_lavorazione = 9 WHERE id = " . $row['id']);
                        }
                        $status=9;
                    }
                }
                // inserisco orderman: l'attuale produzione
				$form['start_work']=$start_work; $form['end_work']=$end_work; $form['id_tesbro']=$id_tesbro; $form['stato_lavorazione']=$status; $form['adminid']=$admin_aziend['adminid']; $form['duration']=$form['day_of_validity'];
				$id_orderman = gaz_dbi_table_insert('orderman', $form);
				// connetto tesbro a orderman
				tesbroUpdate(array('id_tes',$id_tesbro), array('id_orderman'=>$id_orderman));

                // scrittura movimento di magazzino MOVMAG
				// inserisco il movimento carico di magazzino dell'articolo prodotto
				$mv = $magazz->getStockValue(false, $form['codart'], null, null, $admin_aziend['decimal_price']);
				$price=(isset($mv['v']))?$mv['v']:0;
				if (!isset($mv['v']) OR $mv['v']==0){// se getStockValue non mi ha restituito il prezzo allora lo prendo dal prezzo di default
					$price=(isset($row['preacq']))?$row['preacq']:0;
				}
				$id_movmag=$magazz->uploadMag('0', 'PRO', '', '', $form['datemi'], '', '', '82', $form['codart'], $form['quantip'], $price, '', 0, $admin_aziend['stock_eval_method'], array('datreg' => $form['datreg'], 'operat' => '1', 'desdoc' => 'Produzione'), 0, $id_lotmag, $id_orderman, $form['campo_impianto']);
				$prod_id_movmag=$id_movmag; // mi tengo l'id_movmag del movimento di magazzino di entrata da produzione, mi servirà successivamente per valorizzare il prezzo in base alla composizione ed anche in caso di SIAN
				if ($form['SIAN']>0){ // imposto l'id movmag e salvo il movimento SIAN dell'articolo prodotto
					$form['id_movmag']=$id_movmag;
					if ($form['cod_operazione']==5){ // Movimentazione interna senza cambio di origine
						$change=$form['recip_stocc'];// scambio i recipienti
						$form['recip_stocc']=$form['recip_stocc_destin'];
						$var_orig=$campsilos->getContentSil($form['recip_stocc'],$date="",$id_mov=0);
						unset($var_orig['varieta']['totale']);//tolgo il totale
						$form['recip_stocc_destin']=$change;
						$var_dest=$campsilos->getContentSil($form['recip_stocc_destin'],$date="",$id_mov=0);
						unset($var_dest['varieta']['totale']);//tolgo il totale
						if (count($var_dest['varieta'])>0 && count($var_orig['varieta'])>0 && $block_var!=="SI"){
							$form['varieta'] = "Traferimento olio ";
							if (count($var_dest['varieta'])>0){
								$form['varieta'] .= "varietà ". implode(", ",array_keys($var_dest['varieta']));
							}
							if (count($var_orig['varieta'])>0){
								$form['varieta'] .= " al recipiente contenente varietà ". implode(", ",array_keys($var_orig['varieta']));
							}
						}
					}elseif ($block_var!=="SI") {
						$form['varieta']=$form['quality'];
					}
					$id_mov_sian_rif=gaz_dbi_table_insert('camp_mov_sian', $form);
					$s7=""; // Si sta producendo olio
				} else {
					$s7=1; // Non si produce olio cioè l'articolo finito non è olio
					$id_mov_sian_rif="";
				}
				if ($form['cod_operazione']==5){ // se è una movimentazione interna SIAN creo un movimento di magazzino in uscita per far riportare la giacenza
					// inserisco il movimento di magazzino dell'articolo in uscita
					$id_movmag=$magazz->uploadMag('0', 'MAG', '', '', $form['datemi'], '', '', '81', $form['codart'], $form['quantip'], $form['preacq'], '', 0, $admin_aziend['stock_eval_method'], array('datreg' => $form['datreg'], 'operat' => '-1', 'desdoc' => 'Movimentazione interna'), 0, $idlotrecip[0], $id_orderman, $form['campo_impianto']);

					// e creo anche il relativo movimento SIAN
					$form['id_movmag']=$id_movmag;
					$form['cod_operazione']="";
					$change=$form['recip_stocc']; // scambio di nuovo i recipienti
					$form['recip_stocc']=$form['recip_stocc_destin'];
					$var_orig=$campsilos->getContentSil($form['recip_stocc'],$date="",$id_mov=0);
					unset($var_orig['varieta']['totale']);//tolgo il totale
					$form['recip_stocc_destin']=$change;
					$var_dest=$campsilos->getContentSil($form['recip_stocc_destin'],$date="",$id_mov=0);
					unset($var_dest['varieta']['totale']);//tolgo il totale
					if (count($var_dest['varieta'])>0 && count($var_orig['varieta'])>0 && $block_var!=="SI"){
						$form['varieta'] = "Traferimento olio ";
						if (count($var_orig['varieta'])>0){
								$form['varieta'] .= "varietà ". implode(", ",array_keys($var_orig['varieta']));
							}
						if (count($var_dest['varieta'])>0){
							$form['varieta'] .= " al recipiente contenente varietà ". implode(", ",array_keys($var_dest['varieta']));
						}
					}
					$form['id_mov_sian_rif']=$id_mov_sian_rif;
					gaz_dbi_table_insert('camp_mov_sian', $form);
					$form['id_movmag']=$prod_id_movmag;// reimposto l'id_movmag del movimento di entrata
					$id_movmag=$form['id_movmag'];
				}
				if ($itemart && $itemart['good_or_service'] == 2) { // se è un articolo composto
					$comp_total_val=0.00;
					for ($nc = 0;$nc <= $form['numcomp'] - 1;++$nc) { // *** faccio un ciclo con tutti i componenti  ***
						// accumulo il valore dei singoli componenti, mi servirà a fine ciclo per valorizzare il movimento 'PRO' precedentemente inserito

						$comp_total_val += $form['quanti_comp'][$nc]*$form['prezzo_comp'][$nc]/$form['quantip'];
						if ($form['q_lot_comp'][$nc] > 0) { // se il componente ha lotti
							for ($n = 0;$n < $form['q_lot_comp'][$nc];++$n) { //faccio un ciclo con i lotti di ogni singolo componente
								if ($form['lot_quanti'][$nc][$n]>0){ // questo evita che, se è stato forzato un lotto a quantità zero, venga generato un  movimento di magazzino
									// Scarico dal magazzino il componente usato e i suoi lotti
									$id_mag=$magazz->uploadMag('0', 'MAG', '', '', $form['datemi'], '', '', '81', $form['artcomp'][$nc], $form['lot_quanti'][$nc][$n], $form['prezzo_comp'][$nc], '', 0, $admin_aziend['stock_eval_method'], array('datreg' => $form['datreg'], 'operat' => '-1', 'desdoc' => 'Scarico per Produzione con lotto'), 0, $form['id_lot_comp'][$nc][$n], $id_orderman, $form['campo_impianto']);

									if ($form['SIAN_comp'][$nc]>0){ // imposto l'id movmag e creo il movimento SIAN del componente usato, se previsto
										$form['id_movmag']=$id_mag;
										$form['id_mov_sian_rif']=$id_mov_sian_rif; // connetto il mov sian del componente a quello del prodotto
										$form['recip_stocc']=$form['recip_stocc_comp'][$nc];
										gaz_dbi_query("UPDATE " . $gTables['camp_mov_sian'] . " SET recip_stocc = '" . $form['recip_stocc'] . "' WHERE id_mov_sian ='" . $id_mov_sian_rif . "'"); // aggiorno id_lotmag sul movmag
										$form['cod_operazione']="";
										$var_orig=$campsilos->getContentSil($form['recip_stocc'],$date="",$id_mov=0);
										unset($var_orig['varieta']['totale']);//tolgo il totale
										if (isset($var_orig) && $block_var!=="SI"){
											$form['varieta'] = implode(", ",array_keys($var_orig['varieta']));
										}
										if ($s7==1){ // S7 è uno scarico di olio destinato ad altri consumi
											$form['cod_operazione']="S7";
										}
										gaz_dbi_table_insert('camp_mov_sian', $form);
									}
								}
							}
						} else { // se il componente non ha lotti scarico semplicemente il componente dal magazzino
							// Scarico il magazzino con l'articolo usato
							$id_mag=$magazz->uploadMag('0', 'MAG', '', '', $form['datemi'], '', '', '81', $form['artcomp'][$nc], $form['quanti_comp'][$nc], $form['prezzo_comp'][$nc], '', 0, $admin_aziend['stock_eval_method'], array('datreg' => $form['datreg'], 'operat' => '-1', 'desdoc' => 'Scarico per Produzione senza lotto'), 0, '', $id_orderman, $form['campo_impianto']);

							if ($form['SIAN_comp'][$nc]>0){ // imposto l'id movmag e salvo il movimento SIAN del componente usato, se previsto
								$form['id_movmag']=$id_mag;
								$form['id_mov_sian_rif']=$id_mov_sian_rif;// connetto il mov sian del componente a quello del prodotto
								$form['recip_stocc']=$form['recip_stocc_comp'][$nc];
								gaz_dbi_query("UPDATE " . $gTables['camp_mov_sian'] . " SET recip_stocc = '" . $form['recip_stocc'] . "' WHERE id_mov_sian ='" . $id_mov_sian_rif . "'"); // aggiorno id_lotmag sul movmag
								$form['cod_operazione']="";
								$var_orig=$campsilos->getContentSil($form['recip_stocc'],$date="",$id_mov=0);
								unset($var_orig['varieta']['totale']);//tolgo il totale
								if (isset($var_orig) && $block_var!=="SI"){
									$form['varieta'] = implode(", ",array_keys($var_orig['varieta']));
								}
								if ($s7==1){ // S7 è uno scarico di olio destinato ad altri consumi
									$form['cod_operazione']="S7";
								}
								gaz_dbi_table_insert('camp_mov_sian', $form);
							}
						}
					}
					if ($comp_total_val>0){// se è valorizzato, aggiorno il prezzo del movimento di produzione sulla base del prezzo dei componenti su movmag altrimenti lascio il valore di getStockValue or di preacq precedentemente inserito
						gaz_dbi_query("UPDATE " . $gTables['movmag'] . " SET prezzo = " . round($comp_total_val,5) . " WHERE id_mov = " . $prod_id_movmag);
					}
					$form['id_movmag']=$id_movmag;
				}
				if (intval($form['order']) <= 0) {// se non c'è l'ordine vero e devo creare quello fittizio
					//inserisco il rigo ordine rigbro
					$id_rigbro = rigbroInsert(array('id_tes'=>$id_tesbro,'codart'=>$form['codart'],'descri'=>addslashes ($resartico['descri']),'unimis'=>$resartico['unimis'],'quanti'=>$form['quantip'],'id_mag'=>$id_movmag,'status'=>'AUTOGENERA','id_orderman'=>$id_orderman));
				}
				// connetto movmag a rigbro
				movmagUpdate(array('id_mov',$id_movmag), array('id_rif'=>$id_rigbro));
			}

			$id_lotmag="";
			//Antonio Germani - > inizio LOTTO, se c'è lotto e se il prodotto lo richiede
			if ($form['lot_or_serial'] > 0) { // se l'articolo prevede un lotto

				// ripulisco il numero lotto inserito da caratteri dannosi
				$form['identifier'] = (empty($form['identifier'])) ? '' : filter_var($form['identifier'], FILTER_SANITIZE_STRING);
				if (strlen($form['identifier']) == 0) { // se non c'è il lotto lo inserisco con data e ora in automatico
					$form['identifier'] = date("Ymd Hms");
				}
				if (strlen($form['expiry']) == 0) { // se non c'è la scadenza la inserisco a zero in automatico
					$form['expiry'] = "0000-00-00 00:00:00";
				}
				// è un nuovo INSERT
				if (strlen($form['identifier']) > 0 && $toDo == "insert") {
					//inserisco il nuovo id lotto in lotmag e movmag. Ogni produzione di Orderman deve avere un lotto diverso
					gaz_dbi_query("INSERT INTO " . $gTables['lotmag'] . "(codart,id_movmag,identifier,expiry) VALUES ('" . $form['codart'] . "','" . $id_movmag . "','" . $form['identifier'] . "','" . $form['expiry'] . "')");
					$id_lotmag = gaz_dbi_last_id();
					gaz_dbi_query("UPDATE " . $gTables['movmag'] . " SET id_lotmag = '" . $id_lotmag . "' WHERE id_mov ='" . $form['id_movmag'] . "'"); // aggiorno id_lotmag sul movmag
				}
				//  è un UPDATE
				if (strlen($form['identifier']) > 0 && $toDo == "update") {
					$resin = gaz_dbi_get_row($gTables['orderman'], "id", intval($_GET['codice']));
					$resin2 = gaz_dbi_get_row($gTables['lotmag'], "id", $resin['id_lotmag']);
					if ($resin2['identifier'] == $form['identifier']) { // se ha lo stesso numero di lotto di quello precedentemente salvato faccio update di lotmag
						gaz_dbi_query("UPDATE " . $gTables['lotmag'] . " SET codart = '" . $form['codart'] . "' , id_movmag = '" . $form['id_movmag'] . "' , identifier = '" . $form['identifier'] . "' , expiry = '" . $form['expiry'] . "' WHERE id = '" . $form['id_lotmag'] . "'");
						$id_lotmag = $form['id_lotmag'];
					} else { // se non è lo stesso numero, cancello il lotto iniziale e ne creo uno nuovo
						gaz_dbi_query("DELETE FROM " . $gTables['lotmag'] . " WHERE id = " . $resin['id_lotmag']);
						gaz_dbi_query("INSERT INTO " . $gTables['lotmag'] . "(codart,id_movmag,identifier,expiry) VALUES ('" . $form['codart'] . "','" . $form['id_movmag'] . "','" . $form['identifier'] . "','" . $form['expiry'] . "')");

						$id_lotmag = gaz_dbi_last_id(); // vedo dove è stato salvato lotmag
						gaz_dbi_query("UPDATE " . $gTables['movmag'] . " SET id_lotmag = '" . $id_lotmag . "' WHERE id_mov ='" . $form['id_movmag'] . "'"); // aggiorno id_lotmag sul movmag
					}
				}
			}
			// Antonio Germani - inizio salvo documento/CERTIFICATO lotto
			if (substr($form['filename'], 0, 7) <> 'lotmag_') { // se è stato cambiato il file, cioè il nome non inizia con lotmag e, quindi, anche se è un nuovo insert
				if (!empty($form['filename'])) { // e se ha un nome impostato nel form
					$tmp_file = DATA_DIR."files/tmp/" . $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'] . '_' . $form['filename'];
					// sposto il file nella cartella definitiva, rinominandolo e cancellandolo dalla temporanea
					$fd = pathinfo($form['filename']);
					rename($tmp_file, DATA_DIR."files/" . $admin_aziend['company_id'] . "/lotmag_" . $id_lotmag . '.' . $fd['extension']);
				}
			} // altrimenti se il file non è cambiato, anche se è update, non faccio nulla
			// <<< fine salvo lotti

			if ($toDo == "insert") {
				// connetto orderman a rigbro e al lotto
				gaz_dbi_query("UPDATE " . $gTables['orderman'] . " SET id_rigbro = '".$id_rigbro."', id_lotmag = '".$id_lotmag."' WHERE id = " . $id_orderman);
			}

            if ($toDo == 'update') { //  se e' una modifica, aggiorno orderman e tesbro

				$form['start_work']=$start_work; $form['end_work']=$end_work; $form['id_tesbro']=$id_tesbro; $form['stato_lavorazione']=$status; $form['adminid']=$admin_aziend['adminid']; $form['duration']=$form['day_of_validity'];
				$update = array();
				$update[]="id";
				$update[]=$form['id'];
				gaz_dbi_table_update('orderman', $update, $form);

                $resin = gaz_dbi_get_row($gTables['tesbro'], "id_orderman", $id_orderman);
                if ($resin['id_tes'] <> $form['id_tesbro']) { // se l'ordine iniziale è diverso da quello del form
                    if ($resin['tipdoc'] == "PRO") { // se era autogenerato, cioè era PRO, lo cancello e basta perché vuol dire che è stato tolto completamente dal form o sostituito con un vero ordine VOR
                        gaz_dbi_query("DELETE FROM " . $gTables['tesbro'] . " WHERE id_orderman = " . $id_orderman);
                        // devo cancellare anche il relativo rigo rigbro ad esso connesso
                        gaz_dbi_query("DELETE FROM " . $gTables['rigbro'] . " WHERE id_tes = " . $resin['id_tes']);
                    } else { // se il numero ordine iniziale non era PRO, cioè era un ordine vero, gli azzero solo id orderman
                        gaz_dbi_query("UPDATE " . $gTables['tesbro'] . " SET id_orderman = '' WHERE id_tes = '" . $resin['id_tes'] . "'");
                    }
                    $query = "UPDATE " . $gTables['orderman'] . " SET " . 'id_tesbro' . " = '', " . 'id_rigbro' . " = '' WHERE id = '" . $form['id'] . "'"; // azzero anche i riferimenti su orderman
                    gaz_dbi_query($query);


                    if ($form['id_tesbro'] > 0) { // poi, se c'è un nuovo ordine VOR nel form, lo collego a id orderman
                        gaz_dbi_query("UPDATE " . $gTables['tesbro'] . " SET id_orderman = '" . intval($id_orderman) . "' WHERE id_tes = '" . $form['id_tesbro'] . "'");
                        $query = "UPDATE " . $gTables['orderman'] . " SET " . 'id_tesbro' . " = '" . intval($form['id_tesbro']) . "', " . 'id_rigbro' . " = '" . intval($form['id_rigbro']) . "' WHERE id = '" . $form['id'] . "'";
                        gaz_dbi_query($query); // aggiorno i riferimenti su orderman

                    } else { // se non c'è un nuovo ordine lo creo in automatico in tesbro, rigbro e metto i riferimenti su orderman

						$id_tesbro=tesbroInsert(array('tipdoc'=>'PRO','datemi'=>$form['datemi'],'numdoc'=>time(),'status'=>'AUTOGENERA','adminid'=>$admin_aziend['adminid'],'id_orderman'=>$id_orderman));// creo tesbro

						$id_rigbro = rigbroInsert(array('id_tes'=>$id_tesbro,'codart'=>$form['codart'],'descri'=>addslashes ($resartico['descri']),'unimis'=>$resartico['unimis'],'quanti'=>$form['quantip'],'id_mag'=>$id_movmag,'status'=>'AUTOGENERA','id_orderman'=>$id_orderman));// creo rigbro

						$query = "UPDATE " . $gTables['orderman'] . " SET " . 'id_tesbro' . " = '" . intval($id_tesbro) . "', " . 'id_rigbro' . " = '" . intval($id_rigbro) . "' WHERE id = '" . $form['id'] . "'";
                        gaz_dbi_query($query); // aggiorno i riferimenti su orderman

                    }
                } else { // se il numero d'ordine NON è stato cambiato posso fare update solo se è PRO, cioè autogenerato
                    if ($resin['tipdoc'] == "PRO") {
                        $res = gaz_dbi_get_row($gTables['rigbro'], "id_tes", $form['id_tesbro']);
                        if (isset($res)) { // se esiste il rigo aggiorno tesbro e rigbro
                            $query = "UPDATE " . $gTables['tesbro'] . " SET " . 'datemi' . " = '" . $form['datemi'] . "', id_orderman = '" . $id_orderman . "' WHERE id_tes = '" . $form['id_tesbro'] . "'";
                            $res = gaz_dbi_query($query);
                            $query = "UPDATE " . $gTables['rigbro'] . " SET " . 'codart' . " = '" . $form['codart'] . "', " . 'descri' . " = '" . addslashes ($resartico['descri']) . "', " . 'unimis' . " = '" . $resartico['unimis'] . "', " . 'quanti' . " = '" . $form['quantip'] . "' WHERE id_tes = '" . $form['id_tesbro'] . "'";
                            $res = gaz_dbi_query($query);
                        }
                    }
                }
            }

            // se sono in un popup lo chiudo dopo aver salvato tutto
            if ($popup == 1) {
                echo "<script>
				window.opener.location.reload(true);
				window.close();</script>";
            } else {
                header("Location: orderman_report.php");
            }
            exit;
        }
    }
    //  fine scrittura database §§§§§§§§§§§§§§§§§§§§§§§§§§§§

} elseif ((!isset($_POST['Update'])) and (isset($_GET['Update']))) {//  **  se e' il primo accesso per UPDATE  **
	if (!empty($admin_aziend['synccommerce_classname']) && class_exists($admin_aziend['synccommerce_classname'])){
		// allineo l'e-commerce con eventuali ordini non ancora caricati
		$gs=$admin_aziend['synccommerce_classname'];
		$gSync = new $gs();
		if($gSync->api_token){
			$gSync->get_sync_status(0);
		}
	}
    $result = gaz_dbi_get_row($gTables['orderman'], "id", intval($_GET['codice']));
    $form['ritorno'] = $_POST['ritorno'];
    $form['id'] = intval($_GET['codice']);
    $form['order_type'] = $result['order_type'];
    $form['description'] = $result['description'];
    $form['id_tesbro'] = $result['id_tesbro'];
    $form['id_rigbro'] = $result['id_rigbro'];
    $form['add_info'] = $result['add_info'];
    $form['day_of_validity'] = $result['duration'];
	$s = strtotime($result['start_work']);
	$form['iniprod'] = date('d-m-Y', $s);
	$form['iniprodtime'] = date('H:i', $s);
	$s = strtotime($result['end_work']);
	$form['fineprod'] = date('d-m-Y', $s);
	$form['fineprodtime'] = date('H:i', $s);
    $result4 = gaz_dbi_get_row($gTables['movmag'], "id_orderman", intval($_GET['codice']), "AND operat ='1'");
    $form['datreg'] = ($result4)?$result4['datreg']:'';
    $form['quantip'] = ($result4)?$result4['quanti']:0;
    $form['id_movmag'] = ($result4)?$result4['id_mov']:0;
    $resmov_sian = gaz_dbi_get_row($gTables['camp_mov_sian'], "id_movmag", $form['id_movmag']);
    $form['cod_operazione'] =($resmov_sian)?$resmov_sian['cod_operazione']:'';
    $form['recip_stocc'] =($resmov_sian)?$resmov_sian['recip_stocc']:'';
    $form['recip_stocc_destin'] =($resmov_sian)?$resmov_sian['recip_stocc_destin']:'';
    $result2 = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $result['id_tesbro']);
    $form['gioinp'] = substr(($result2)?$result2['datemi']:'', 8, 2);
    $form['mesinp'] = substr(($result2)?$result2['datemi']:'', 5, 2);
    $form['anninp'] = substr(($result2)?$result2['datemi']:'', 0, 4);
    $form['datemi'] = ($result2)?$result2['datemi']:'';
    $form['campo_impianto'] = $result['campo_impianto'];
    $form['id_lotmag'] = $result['id_lotmag'];
    $form['order'] = ($result2)?$result2['numdoc']:0;
	if (isset($result2['clfoco'])){
    $res3 = gaz_dbi_get_row($gTables['clfoco'], "codice", $result2['clfoco']);// importo il nome del cliente dell'ordine
	}
    $form['coseor'] = ($result2)?$result2['id_tes']:0;
    $form['id_tes'] = ($result2)?$result2['id_tes']:0;
    $result3 = gaz_dbi_get_row($gTables['rigbro'], "id_rig", $result['id_rigbro']);
    $form['codart'] = ($result3)?$result3['codart']:'';
    $form['quantipord'] = ($result3)?$result3['quanti']:0;
    $result5 = gaz_dbi_get_row($gTables['lotmag'], "id", $result['id_lotmag']);
    $form['identifier'] =($result5)?$result5['identifier']:'';
    $form['expiry'] =($result5)?$result5['expiry']:'';
    $resartico = gaz_dbi_get_row($gTables['artico'], "codice", $form['codart']);
    if ($resartico){
      $form['lot_or_serial'] = $resartico['lot_or_serial'];
      $form['SIAN'] = $resartico['SIAN'];
    } else {
      $form['lot_or_serial'] = '';
      $form['SIAN'] = '';
      $resartico=array('unimis'=>'','lot_or_serial'=>'','good_or_service'=>'');
    }
    if (count($resartico) > 4 && $resartico['good_or_service'] == 2) { // se è un articolo composto
		// prendo i movimenti di magazzino dei componenti e l'unità di misura
		$where="operat = '-1' AND id_orderman = ". intval($_GET['codice']);
		$table = $gTables['movmag']." LEFT JOIN ".$gTables['artico']." on (".$gTables['movmag'].".artico = ".$gTables['artico'].".codice)";
		$result7 = gaz_dbi_dyn_query ($gTables['movmag'].".*, ".$gTables['artico'].".unimis", $table, $where );
    }
    // Antonio Germani - se è presente, recupero il file documento lotto
    $form['filename'] = "";
    if (file_exists(DATA_DIR.'files/' . $admin_aziend['company_id']) > 0) {
        // recupero il filename dal filesystem
        $dh = opendir(DATA_DIR.'files/' . $admin_aziend['company_id']);
        while (false !== ($filename = readdir($dh))) {
            $fd = pathinfo($filename);
            $r = explode('_', $fd['filename']);
            if ($r[0] == 'lotmag' && $r[1] == $result['id_lotmag']) {
                // riassegno il nome file
                $form['filename'] = $fd['basename'];
            }
        }
    }
	$form['mov'] = 0;
    $form['nmov'] = 0;
    $form['nmovdb'] = 0;
	$form['id_staff_def'] = $result['id_staff_def'];

    $form['cosear'] = "";

} else {                 //                  **   se e' il primo accesso per INSERT    **
	if (!empty($admin_aziend['synccommerce_classname']) && class_exists($admin_aziend['synccommerce_classname'])){
		// allineo l'e-commerce con eventuali ordini non ancora caricati
		$gs=$admin_aziend['synccommerce_classname'];
		$gSync = new $gs();
		if($gSync->api_token){
			$gSync->get_sync_status(0);
		}
	}
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
	$form['numdoc']="";
    if (isset($_GET['type'])) { // controllo se proviene anche da una richiesta del modulo camp
        $form['order_type'] = substr($_GET['type'],0,3);
    } else { // altrimenti prendo quello in configurazione azienda
        $form['order_type'] = $admin_aziend['order_type'];
    }
    $form['description'] = "";
    $form['id_tesbro'] = "";
    $form['add_info'] = "";
    $form['gioinp'] = date("d");
    $form['mesinp'] = date("m");
    $form['anninp'] = date("Y");
	$form['datemi'] = date("Y-m-d", time());
	$form['iniprod'] = date ("d-m-Y");
	$form['iniprodtime'] = date ("H:i");
	$form['fineprod'] = date ("d-m-Y");
	$form['fineprodtime'] = date ("H:i");
    $form['day_of_validity'] = "";
    $form["campo_impianto"] = "";
    $form['order'] = 0;
    $form['coseor'] = "";
    $form['codart'] = "";
    $form['cosear'] = "";
    $form['mov'] = 0;
    $form['nmov'] = 0;
    $form['nmovdb'] = 0;
    //$form['staff'][$form['mov']] = "";
    $form['filename'] = "";
    $form['identifier'] = "";
    $form['expiry'] = "";
    $form['lot_or_serial'] = "";
	$form['SIAN'] = "";
	$form['quality'] = "";
	$form['cod_operazione']="";
	$form['recip_stocc']="";
	$form['recip_stocc_destin']="";
    $form['datreg'] = date("Y-m-d");
    $form['quantip'] = "";
    $form['quantipord'] = "";
    $form['id_movmag'] = "";
    $form['id_lotmag'] = "";
    $form['numcomp'] = 0;
	$resartico['lot_or_serial']="";
	$resartico['good_or_service']="";
	$resartico['unimis']="";
	$form['id_tes']="";
	$form['id_staff_def']=0;
}
if (isset($_POST['Cancel'])) { // se è stato premuto ANNULLA
    $form['hidden_req'] = '';
    $form['order_type'] = "";
    $form['description'] = "";
    $form['id_tesbro'] = "";
    $form['add_info'] = "";
    $form['gioinp'] = date("d");
    $form['mesinp'] = date("m");
    $form['anninp'] = date("Y");
    $form['day_of_validity'] = "";
    $form["campo_impianto"] = "";
    $form['order'] = "";
    $form['codart'] = "";
    $form['mov'] = 0;
    $form['nmov'] = 0;
    $form['nmovdb'] = 0;
    //$form['staff'][$form['mov']] = "";
	$form['id_staff_def']=0;
    $form['filename'] = "";
    $form['identifier'] = "";
    $form['expiry'] = "";
    $form['quantip'] = "";
    $form['id_movmag'] = "";
    $form['id_lotmag'] = "";
    $form['numcomp'] = 0;
}
if (!empty($_FILES['docfile_']['name'])) { // Antonio Germani - se c'è un nome in $_FILES
    $prefix = $admin_aziend['adminid'] . '_' . $admin_aziend['company_id'];
    foreach (glob(DATA_DIR."files/tmp/" . $prefix . "_*.*") as $fn) { // prima cancello eventuali precedenti file temporanei
        unlink($fn);
    }
    $mt = substr($_FILES['docfile_']['name'], -3);
    if (($mt == "png" || $mt == "odt" || $mt == "peg" || $mt == "jpg" || $mt == "pdf") && $_FILES['docfile_']['size'] > 1000) { // se rispetta limiti e parametri lo salvo nella cartella tmp
        move_uploaded_file($_FILES['docfile_']['tmp_name'], DATA_DIR.'files/tmp/' . $prefix . '_' . $_FILES['docfile_']['name']);
        $form['filename'] = $_FILES['docfile_']['name'];
    } else {
        $msg.= "14+";
    }
}
require ("../../library/include/header.php");
$script_transl = HeadMain(0,array('custom/autocomplete',));
if ($toDo == 'update') {
    $title = ucwords($script_transl['upd_this']) . " n." . $form['id'];
} else {
    $title = ucwords($script_transl['ins_this']);
}

print "<form method=\"POST\" name=\"myform\" enctype=\"multipart/form-data\">\n";
print "<input type=\"hidden\" name=\"" . ucfirst($toDo) . "\" value=\"\">\n";
print "<input type=\"hidden\" value=\"" . $_POST['ritorno'] . "\" name=\"ritorno\">\n";
print "<input type=\"hidden\" name=\"hidden_req\" value=\"TRUE\">\n"; // per auto submit on change select input
print "<div align=\"center\" class=\"FacetFormHeaderFont\">$title</div>";
print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
$class="btn-success";$addvalue="";
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
    echo '<tr><td colspan="5" class="FacetDataTDred">' . $message . "</td></tr>\n";
}
if (!empty($warnmsg)) {
    $message = "";
	$class="btn-danger"; $addvalue=" nonostante l'avviso";
    $rsmsg = array_slice(explode('+', chop($warnmsg)), 0, -1);
    foreach ($rsmsg as $value) {
        $message.= $script_transl['warning'] . "! -> ";
        $rsval = explode('-', chop($value));
        foreach ($rsval as $valmsg) {
            $message.= $script_transl[$valmsg] . " ";
        }
        $message.= "<br />";
    }
    echo '<tr><td colspan="5" class="FacetDataTDred">' . $message . "</td></tr>\n";
}
if ($toDo == 'update') {
    print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[0]</td><td class=\"FacetDataTD\"><input type=\"hidden\" name=\"id\" value=\"" . $form['id'] . "\" />" . $form['id'] . "</td></tr>\n";
}
// Antonio Germani > inserimento tipo di produzione
print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[1]</td><td class=\"FacetDataTD\">";
?>
<script>
  $(function() {
    $( ".datepicker" ).datepicker({dateFormat: 'yy-mm-dd' });

});
</script>

<?php if ($toDo == "insert") {

$gForm->variousSelect("order_type", $script_transl['order_type'], $form['order_type'], '', true, 'order_type');

} else {
    echo $form['order_type'], "&nbsp &nbsp";
    echo '<input type="hidden" name="order_type" value="' . $form['order_type'] . '">';
}
// inserimento data di registrazione
if ($form['order_type'] == "IND") {
    echo '<label>' . 'Data registrazione magazzino: ' . ' </label><input class="datepicker" type="text" onchange="this.form.submit();" name="datreg"  value="' . $form['datreg'] . '">';
} else {
    echo '<input type="hidden" name="datreg" value="">';
	echo '<input type="hidden" name="recip_stocc" value="">';
	echo '<input type="hidden" name="recip_stocc_destin" value="">';
	echo '<input type="hidden" name="cod_operazione" value="">';
    if ($form['order_type'] != "") {
        echo "Non registra magazzino!";
    }
}

?>
</td></tr>
<?php
if ($form['order_type'] <> "AGR") { // Se non è produzione agricola

    // Antonio Germani > inserimento ordine
	?>
	<tr>
		<td class="FacetFieldCaptionTD"><?php echo $script_transl['8']; ?> </td>
		<td colspan="2" class="FacetDataTD">
			<?php
		if (isset($res3) && $res3 && $toDo == "update") {
			echo "N: ",$form['order']," - Cliente: ",$res3['descri'];
		?>
			<input type="hidden" name="order" Value="<?php echo $form['order']; ?>"/>
			<input type="hidden" name="coseor" Value="<?php echo $form['coseor']; ?>"/>
			<?php
		} else {
			// Inserimento ORDINE
			$select_order = new selectorder("id_tes");
			$select_order->addSelected($form['id_tes']);
			$select_order->output($form['coseor']);
		}
		if (strlen($form['order']) > 0) {
		?>
		<!--	<span class="glyphicon glyphicon-bell fa-2x" title="L'ordine impone l'articolo e la quantità" style="color:blue"></span>-->
				<?php
		}
		?>
		</td>
	</tr>
	<?php
    if ($form['order'] > 0 && $toDo != "update") { // se c'è un ordine e non siamo in update seleziono l'articolo fra quelli ordinati
		echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[9] . "</td><td class=\"FacetDataTD\">\n";
		// SELECT articolo da rigbro
		$gForm->selectFromDB('rigbro', 'cosear','codart', $form['codart'], 'id_tes', 1, ' - ','descri','TRUE','FacetSelect' , null, '','id_tes = '. $form['id_tes'].' ');
	} else { //se non c'è l'ordine seleziono l'articolo da artico
		?>
		<!-- Antonio Germani > inserimento articolo	con autocomplete dalla tabella artico-->
		<tr>
		<td class="FacetFieldCaptionTD"><?php echo $script_transl['9']; ?> </td>
		<td colspan="2" class="FacetDataTD">
		<?php
		if ($toDo == "update") {
			echo $form['codart'];?>
			<input type="hidden" name="codart" Value="<?php echo $form['codart']; ?>"/>
			<input type="hidden" name="cosear" Value="<?php echo $form['cosear']; ?>"/>
			<?php
		} else {
			$select_artico = new selectartico("codart");
			$select_artico->addSelected($form['codart']);
			$select_artico->output(substr($form['cosear'], 0, 20));
		}
	}
	echo '<input type="hidden" name="lot_or_serial" value="' .(($resartico)?$resartico['lot_or_serial']:''). '"/>';

    if ($resartico && $resartico['good_or_service'] == 2) { // se è un articolo composto
		?>
		<div class="container-fluid">
			<div class="row" style="margin-left: 0px;">
				<div align="center">
				<a  title="Vai alla distinta base composizione" class="col-sm-12 btn btn-info btn-md" href="javascript:;" onclick="window.open('<?php echo"../../modules/magazz/admin_artico_compost.php?Update&codice=".$form['codart'];?>', 'menubar=no, toolbar=no, width=800, height=400, left=80%, top=80%, resizable, status, scrollbars=1, location');">
				Articolo composto &nbsp<span class="glyphicon glyphicon-tasks"></span></a>
				</div>
			</div>
			<?php
			if ($toDo == "update") { //se UPDATE
				// se ci sono, visualizzo i componenti; NON sarà, però,  possibile modificarli in update
				if (isset($result7)) {
					while ($row = $result7->fetch_assoc()) {
						?>
						<div class="row" style="margin-left: 0px;">
							<div class="col-sm-3 "  style="background-color:lightcyan;"><?php echo $row['artico']; ?>
							</div>
							<div class="col-sm-5 "  style="background-color:lightcyan;"><?php echo "Q.tà usata: ", number_format(str_replace(",","",$row['quanti']),5,",",".")," ",$row['unimis']; ?>
							</div>
							<?php
							if (intval($row['id_lotmag']) > 0) {
								?>
								<div class="col-sm-4 "  style="background-color:lightcyan;"><?php echo "id lotto: ", $row['id_lotmag']; ?>
								</div>
								<?php
							} else {
								?>
								<div class="col-sm-4 "  style="background-color:lightcyan;"><?php echo "articolo senza lotto "; ?>
								</div>
								<?php
							}
							?>
						</div> <!-- chiude row  -->
						<?php
					}
				}
			} else { // se INSERT
				$nc = 0; // numero componente
				$l = 0; // numero lotto componente

				while ($row = $rescompo->fetch_assoc()) { // creo gli input dei componenti visualizzandone anche disponibilità di magazzino

					if ($form['quantip'] > 0) {

						$row['quantita_artico_base'] = number_format ((floatval($row['quantita_artico_base']) * floatval($form['quantip'])),6);
						$mv = $magazz->getStockValue(false, $row['codice_artico_base'], null, null, $admin_aziend['decimal_price']);
						$magval = array_pop($mv);

						$price_comp=($magval)?$magval['v']:0;
						if ($price_comp==0){// se getStockValue non mi ha restituito il prezzo allora lo prendo dal prezzo di default
							$price_comp=$row['preacq'];
						}

						// controllo disponibilità in magazzino
						$magval=(is_numeric($magval))?['q_g'=>0,'v_g'=>0]:$magval;
						if ($toDo == "update") { // se è un update riaggiungo la quantità utilizzata
							$magval['q_g'] = $magval['q_g'] + $row['quantita_artico_base'];
						}
						?>
						<input type="hidden" name="SIAN_comp<?php echo $nc; ?>" value="<?php echo $row['SIAN']; ?>">
						<input type="hidden" name="artcomp<?php echo $nc; ?>" value="<?php echo $row['codice_artico_base']; ?>">
						<input type="hidden" name="prezzo_comp<?php echo $nc; ?>" value="<?php echo $price_comp; ?>">
						<input type="hidden" name="quality_comp<?php echo $nc; ?>" value="<?php echo $row['quality']; ?>">
						<div class="row" style="margin-left: 0px;">
							<div class="col-sm-3 "  style="background-color:lightcyan;"><?php echo $row['codice_artico_base']; ?>
							</div>
							<!-- Antonio Germani devo usare number_format perché la funzione gaz_format_quantity non accetta più di 3 cifre dopo la virgola. -->
							<div class="col-sm-4 "  style="background-color:lightcyan;"><?php echo $row['unimis']," ","Necessari: ", number_format(str_replace(",","",$row['quantita_artico_base']),5,",","."); ?>
							</div>
							<div class="col-sm-4 "  style="background-color:lightcyan;"><?php echo "Disponibili: ", number_format($magval['q_g'],5,",","."); ?>
							</div>
							<?php
							if (number_format($magval['q_g'],5,".","") - floatval(preg_replace('/[^\d.]/', '', $row['quantita_artico_base'])) >= 0) { // giacenza sufficiente
								?>
								<input type="hidden" name="quanti_comp<?php echo $nc; ?>" value="<?php echo floatval(preg_replace('/[^\d.]/', '', $row['quantita_artico_base'])); ?>"> <!-- quantità utilizzata di ogni componente   -->
								<div class="col-sm-1" style="background-color:lightgreen;"> OK</div>
								<?php
							} else { // giacenza insufficiente
								?>
								<input type="hidden" name="quanti_comp<?php echo $nc; ?>" value="ERRORE"> <!-- quantità 	insufficiente componente, ERRORE -->
								<div class="col-sm-1" style="background-color:red;"> KO</div>
								<?php
							}

							 // Antonio Germani - Inizio form SIAN
							if ($row['SIAN']>0 AND $form['order_type'] == "IND"){ // se l'articolo prevede un movimento SIAN e siamo su prod.industriale
								$rescampbase = gaz_dbi_get_row($gTables['camp_artico'], "codice", $row['codice_artico_base']);
								if ($rescampbase['confezione']==0){ // se è sfuso apro la richiesta contenitore
									?>
						</div> <!-- chiude row del nome articolo composto -->
									<div class="container-fluid">
									<div class="row">
									<label for="camp_recip_stocc_comp" class="col-sm-5"><?php echo "Recipiente stoccaggio del componente"; ?></label>
									<?php
									if (!isset($form['recip_stocc_comp'][$nc])){
										$form['recip_stocc_comp'][$nc]="";
									}
									$campsilos->selectSilos('recip_stocc_comp'.$nc ,'cod_silos', $form['recip_stocc_comp'][$nc], 'cod_silos', 1,'capacita','TRUE','col-sm-7' , null, '');
									?>
									</div>
									<?php
								} else {
									echo '<input type="hidden" name="recip_stocc_comp'.$nc.'" value="">';
								}
							} else {
								echo '<input type="hidden" name="recip_stocc_comp'.$nc.'" value="">';
							}
							// Antonio Germani - inserimento lotti in uscita

							$artico = gaz_dbi_get_row($gTables['artico'], "codice", $row['codice_artico_base']);
							if ($artico['lot_or_serial'] == 1) { // se il componente prevede lotti
								// PROBLEMA IN UPDATE non esclude il lotti, per questo motivo non sono modificabili
								$lm->getAvailableLots($row['codice_artico_base']); // Antonio Germani - non è stato inserito il movimento di magazzino da escludere perché questa funzione ne accetta uno solo e, invece, potrebbero essere di più. E' questo il motivo per cui, in update, sono stati bloccati articolo, componenti, lotti e quantità.
								$ld = $lm->divideLots($row['quantita_artico_base']);
								$l = 0;

								if ($ld > 0) { // segnalo preventivamente l'errore
									echo "ERRORE ne mancano:", gaz_format_quantity($ld,","), "<br>"; // >>>>> quantità insufficiente - metto come valore ERRORE così potrò ritrovarlo facilmente e annullo quanti lotti sono interessati per questo componente
									?>
									<input type="hidden" name="lot_quanti<?php echo $nc, $l; ?>" value="ERRORE">
									<input type="hidden" name="q_lot_comp<?php echo $nc; ?>" value="">
									<?php
								} else {
									if (!isset($form['amLot'. $nc])){
										$form['amLot'. $nc]="";
									}
									if ($form['amLot'. $nc] == "autoLot" OR $form['amLot'. $nc]=="" ){ // se selezione lotti automatica
										// ripartisco la quantità introdotta tra i vari lotti disponibili per l'articolo
										foreach ($lm->divided as $k => $v) { // ciclo i lotti scelti da getAvailableLots
											if ($v['qua'] >= 0.00001) {
												//$form['id_lot_comp'][$nc][$l]="";
												//$form['lot_quanti'][$nc][$l]="";
												if (!isset($form['id_lot_comp'][$nc][$l]) or (intval($form['id_lot_comp'][$nc][$l])==0)) {
													$form['id_lot_comp'][$nc][$l] = $v['id']; // al primo ciclo, cioè id lotto è zero, setto il lotto
													$form['lot_quanti'][$nc][$l] = $v['qua']; // e la quantità in base al riparto
												}
												$selected_lot = $lm->getLot($form['id_lot_comp'][$nc][$l]);
												$disp= $lm -> dispLotID ($artico['codice'], $selected_lot['id']);
												echo '<div><button class="btn btn-xs btn-success"  title="Lotto selezionato automaticamente" data-toggle="collapse" href="#lm_dialog' . $nc . $l.'">' . $selected_lot['id'] . ' Lotto n.: ' . $selected_lot['identifier'];
												if (intval($selected_lot['expiry'])>0){
													echo ' Scadenza: ' . gaz_format_date($selected_lot['expiry']);
												}
												echo ' disponibili:' . gaz_format_quantity($disp);
												echo '  <i class="glyphicon glyphicon-tag"></i></button>';
												?>
												<input type="hidden" name="id_lot_comp<?php echo $nc, $l; ?>" value="<?php echo $form['id_lot_comp'][$nc][$l]; ?>">
												Quantità<input type="text" name="lot_quanti<?php echo $nc, $l; ?>" value="<?php echo $form['lot_quanti'][$nc][$l]; ?>" onchange="this.form.submit();">
												<?php
												$l++;
											}
										}

										?>
										Passa a <input type="submit" class="btn glyphicon glyphicon-remove-circle" name="manLot<?php echo $nc; ?>" id="preventDuplicate" onClick="chkSubmit();" value="manuale">&#128075;
										<?php
									} elseif ($form['amLot'. $nc] == "manuale"){	// se selezione manuale
										for ($l = 0;$l < $form['q_lot_comp'][$nc];++$l) {
											if (!isset($form['id_lot_comp'][$nc][$l]) or (intval($form['id_lot_comp'][$nc][$l])==0)) {
												$form['id_lot_comp'][$nc][$l] = 0; // appena aggiunto rigo lotto ciclo setto il lotto a zero
												$form['lot_quanti'][$nc][$l] = 0;
											}
											$selected_lot = $lm->getLot($form['id_lot_comp'][$nc][$l]);
											$disp= $lm -> dispLotID ($artico['codice'], $selected_lot['id']);
											echo '<div><button class="btn btn-xs btn-success"  title="Lotto selezionato automaticamente" data-toggle="collapse" href="#lm_dialog' . $nc . $l.'">' . $selected_lot['id'] . ' Lotto n.: ' . $selected_lot['identifier'];
											if (intval($selected_lot['expiry'])>0){
												echo ' Scadenza: ' . gaz_format_date($selected_lot['expiry']);
											}
											echo ' disponibili:' . gaz_format_quantity($disp);
											echo '  <i class="glyphicon glyphicon-tag"></i></button>';
											?>
											<input type="hidden" name="id_lot_comp<?php echo $nc, $l; ?>" value="<?php echo $form['id_lot_comp'][$nc][$l]; ?>">
											Quantità<input type="text" name="lot_quanti<?php echo $nc, $l; ?>" value="<?php echo $form['lot_quanti'][$nc][$l]; ?>" onchange="this.form.submit();">
											<?php
										}
										?>
										Passa a <input type="submit" class="btn glyphicon glyphicon-remove-circle" name="autoLot<?php echo $nc; ?>" id="preventDuplicate" onClick="chkSubmit();" value="autoLot">&#128187;
										<div>
										<button type="submit" name="addLot<?php echo $nc; ?>" title="Aggiungi rigo lotto" class="btn btn-default"  style="border-radius= 85px; "> <i class="glyphicon glyphicon-plus-sign"></i></button>
										<button type="submit" name="subtLot<?php echo $nc; ?>" title="Togli rigo lotto" class="btn btn-default"  style="border-radius= 85px; "> <i class="glyphicon glyphicon-minus-sign"></i></button>
										</div>
										<?php
									}
									?>
									<input type="hidden" name="amLot<?php echo $nc; ?>" id="preventDuplicate" value="<?php echo $form['amLot'.$nc]; ?>">

									<input type="hidden" name="q_lot_comp<?php echo $nc; ?>" value="<?php echo $l; ?>">
									<?php // q lot comp ha volutamente una unità in più per distinguerlo da quando è zero cioè nullo

									for ($cl = 0; $cl < $l; $cl++) {
										?>
										<!-- Antonio Germani - Cambio lotto -->
										<div id="lm_dialog<?php echo $nc,$cl;?>" class="collapse" >

											<?php
											if ((count($lm->available) > 1)) {
												foreach ($lm->available as $v_lm) {
													if ($v_lm['id'] <> $form['id_lot_comp'][$nc][$cl]) {
														$disp= $lm -> dispLotID ($artico['codice'], $v_lm['id']);
														echo '<div>Cambia con:<button class="btn btn-xs btn-warning" type="text" onclick="this.form.submit();" name="id_lot_comp'.$nc.$cl.'" value="'.$v_lm['id'].'">'
														. $v_lm['id']. ' lotto n.:' . $v_lm['identifier'];
														if (intval($v_lm['expiry'])>0){
															echo ' scadenza:' . gaz_format_date($v_lm['expiry']);
														}
														echo ' disponibili:' . gaz_format_quantity($disp)
														. '</button></div>';
													}
												}
											} else {
												echo '<div><button class="btn btn-xs btn-danger" type="image" >Non ci sono disponibili altri lotti.</button></div>';
											}
											?>

										</div>
										<?php
									}
								}
								?>
								</div>
								<?php
							} else { // se non prevede lotto azzero id_lotmag e q_lot_mag di $nc
								echo '<input type="hidden" name="id_lot_comp' . $nc . '0" value="">';
								echo '<input type="hidden" name="q_lot_comp' . $nc . '" value="">'; // non ci sono lotti per questo componente
								echo " Componente senza lotto";
							}
							?>
						</div> <!-- chiude articolo composto  -->

				<?php
                    $nc = $nc + 1;
					}
				}
				echo '<input type="hidden" name="numcomp" value="' . $nc . '">'; // Antonio Germani - Nota bene: numcomp ha sempre una unità in più! Non l'ho tolta per distinguere se c'è un solo componente o nessuno.
			}
		?>
		</div>	<!-- chiude container  -->
		<?php
	}
	?>
	</td>
	</tr>
	<?php // Antonio Germani - Inizio form SIAN
	if ($form['SIAN']>0 AND $form['order_type'] == "IND"){ // se l'articolo prevede un movimento SIAN e siamo su prod.industriale
		$rescampbase = gaz_dbi_get_row($gTables['camp_artico'], "codice", $form['codart']);
		echo "<tr><td class=\"FacetFieldCaptionTD\">Gestione SIAN</td>";
		echo "<td>";
		?>
		<div class="container-fluid">
			<div class="row">
				<label for="cod_operazione" class="col-sm-6 control-label"><?php echo "Tipo operazione SIAN"; ?></label>
				<?php

				$gForm->variousSelect('cod_operazione', $script_transl['cod_operaz_value'], $form['cod_operazione'], "col-sm-6", false, '', false);

				?>
			</div>
			<?php if ($rescampbase['confezione']==0){ ?>
				<div class="row">
					<label for="camp_recip_stocc" class="col-sm-6"><?php echo "Recipiente stoccaggio"; ?></label>
					<?php
					$campsilos->selectSilos('recip_stocc' ,'cod_silos', $form['recip_stocc'], 'cod_silos', 1,'capacita','TRUE','col-sm-6' , null, '');
					?>
				</div>
				<?php
				if ($form['cod_operazione']==5){ ?>
					<div class="row">
					<label for="camp_recip_stocc" class="col-sm-6"><?php echo "Recipiente stoccaggio destinazione"; ?></label>
					<?php
					$campsilos->selectSilos('recip_stocc_destin' ,'cod_silos', $form['recip_stocc_destin'], 'cod_silos', 1,'capacita','TRUE','col-sm-6' , null, '');
					?>
				</div>
				<?php
				} else {
					echo '<input type="hidden" name="recip_stocc_destin" value="">';
				}
			} else {
				echo '<tr><td><input type="hidden" name="recip_stocc" value="">';
				echo '<input type="hidden" name="recip_stocc_destin" value="">';
			}

		echo "</div>";
		echo"</td></tr>";
	} else {
		echo '<tr><td><input type="hidden" name="recip_stocc" value="">';
		echo '<input type="hidden" name="recip_stocc_destin" value="">';
		echo '<input type="hidden" name="cod_operazione" value=""></td></tr>';
	}

	?>
	<!--- Antonio Germani - inserimento quantità  -->
	<tr>
		<td class="FacetFieldCaptionTD"><?php echo $script_transl['15']; ?> </td>
		<td colspan="2" class="FacetDataTD">
			<?php
			if ($toDo == "update") {

				echo ($form['order_type'] != "ART")?gaz_format_quantity($form['quantip'], true, $admin_aziend['decimal_quantity']):'';
				?>
				<input type="hidden" name="quantip" Value="<?php echo $form['quantip']; ?>"/>
				<?php
				echo ($resartico)?$resartico['unimis']:'';

				if ($form['quantipord'] - $form['quantip'] > 0) {
					echo " Sono ancora da produrre: ", gaz_format_quantity($form['quantipord'] - $form['quantip'], 0, $admin_aziend['decimal_quantity']);
				}
				if ($form['quantipord'] - $form['quantip'] <= 0 && $form['order_type'] != "ART") {
					echo " La produzione per questo ordine è completata";
				}
			} else {
				?>
				<input type="text" name="quantip" onchange="this.form.submit()" value="<?php echo $form['quantip']; ?>" />
				<?php
				echo ($resartico)?$resartico['unimis']:'';
				// Antonio Germani - Visualizzo quantità prodotte e rimanenti
				if (($form['order']) > 0 && strlen($form['codart']) > 0) { // se c'è un ordine e c'è un articolo selezionato, controllo se è già stato prodotto

					if ($quantiprod > 0) { // se c'è stata già una produzione per questo articolo e per questo ordine
						echo " già prodotti : <b>", $quantiprod. "</b>";
						echo " Ne servono ancora : <b>". gaz_format_quantity($form['quantipord'] - $quantiprod, 0, $admin_aziend['decimal_quantity']), "</b>";
					} else {
						echo " L'ordine ne richiede : <b>", gaz_format_quantity($form['quantipord'], 0, $admin_aziend['decimal_quantity'])."</b>";
					}
					if ($form['quantipord'] - $quantiprod > 0) {
						$form['okprod'] = "ok";
					} else {
						$form['okprod'] = "";
					}
					?>
					<input type="hidden" name="okprod" value="<?php echo $form['okprod']; ?>">
					<?php
				} else {
					?>
					<input type="hidden" name="okprod" value="">
					<?php
				}
			}
			?>
			<input type="hidden" name="id_movmag" value="<?php echo $form['id_movmag']; ?>">
		</td>
	</tr>
	<?php
} else { // se è produzione agricola
    print "<tr><td><input type=\"hidden\" name=\"order\" value=\"\">";
	?>
	<!-- Antonio Germani > inserimento articolo	con autocomplete dalla tabella artico-->
	<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['9']; ?> </td>
	<td colspan="2" class="FacetDataTD">
	<?php
	if ($toDo == "update") {
		echo $form['codart'];?>
		<input type="hidden" name="codart" Value="<?php echo $form['codart']; ?>"/>
		<input type="hidden" name="cosear" Value="<?php echo $form['cosear']; ?>"/>
		<?php
	} else {
		$select_artico = new selectartico("codart");
		$select_artico->addSelected($form['codart']);
		$select_artico->output(substr($form['cosear'], 0, 20));
	}
	?>
	</tr>
	<?php
    //print "<input type=\"hidden\" name=\"codart\" value=\"\">";
	//print "<input type=\"hidden\" name=\"cosear\" value=\"\">";
	print "<input type=\"hidden\" name=\"coseor\" value=\"\">";
    print "<input type=\"hidden\" name=\"id_movmag\" value=\"\">";
    print "<input type=\"hidden\" name=\"quantip\" value=\"\"></td></tr>";
}
?>
<!--- Antonio Germani - inserimento descrizione  -->
<tr>
	<td class="FacetFieldCaptionTD"><?php echo $script_transl['2']; ?> </td>
	<td colspan="2" class="FacetDataTD">
	<input type="text" name="description" value="<?php echo htmlspecialchars($form['description']); ?>" maxlength="80" />
	</td>
</tr>
<?php
echo "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[3]</td><td class=\"FacetDataTD\">";
?>
		<textarea type="text" name="add_info" align="right" maxlength="255" cols="67" rows="3"><?php echo $form['add_info']; ?></textarea>
<?php
echo "</td></tr>\n";

if ($form['order_type'] <> "AGR") { // Se non è produzione agricola
// DATA inizio produzione
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[5] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"gioinp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1;$counter <= 31;$counter++) {
    $selected = "";
    if ($counter == $form['gioinp']) $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesinp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1;$counter <= 12;$counter++) {
    $selected = "";
    if ($counter == $form['mesinp']) $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"anninp\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10;$counter <= date("Y") + 10;$counter++) {
    $selected = "";
    if ($counter == $form['anninp']) $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td>\n";
// end data inizio produzione
} else {
	?>
	<input type="hidden" name="gioinp" Value=""/>
	<input type="hidden" name="mesinp" Value=""/>
	<input type="hidden" name="anninp" Value=""/>
	<?php
}

// Antonio Germani > DURATA produzione

print "<tr><td class=\"FacetFieldCaptionTD\">$script_transl[11]</td>";

print "<td class=\"FacetDataTD\"><input type=\"number\" name=\"day_of_validity\" min=\"0\" maxlength=\"3\" step=\"any\"  size=\"10\" value=\"" . $form['day_of_validity'] . "\"  /></td></tr>\n";
/*Antonio Germani LUOGO di produzione  */
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">\n";
// SELECT luogo di produzione da campi
$gForm->selectFromDB('campi', 'campo_impianto','codice', $form['campo_impianto'], 'codice', 1, ' - ','descri','TRUE','FacetSelect' , null, '');
echo "</td></tr>";

// Antonio Germani selezione responsabile o addetto alla produzione fra l'elenco staff
// SELECT da staff con acquisizione nome da clfoco
echo "<tr><td class=\"FacetFieldCaptionTD\">Responsabile/addetto produzione</td><td class=\"FacetDataTD\">\n";
?>
<select name="id_staff_def" onchange="this.form.submit()">
<?php
$sql = gaz_dbi_dyn_query ($gTables['anagra'].".* ",
 $gTables['anagra']."
 LEFT JOIN ".$gTables['clfoco']." on (".$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id)
 LEFT JOIN ".$gTables['staff']." on (".$gTables['staff'].".id_clfoco = ".$gTables['clfoco'].".codice)
 LEFT JOIN ".$gTables['admin']." on (".$gTables['admin'].".id_anagra = ".$gTables['anagra'].".id)",
 "(".$gTables['staff'].".id_clfoco > 0 OR ". $gTables['admin'] .".id_anagra > 0) AND (". $gTables['staff'] .".end_date >= '". $form['datemi'] ."' OR ". $gTables['staff'] .".end_date IS NULL)");
$sel=0;
while ($row = $sql->fetch_assoc()){
	$selected = "";
	if ($row['id'] == $form['id_staff_def']) {
		$selected = "selected";
		$sel=1;
	}
	echo "<option ".$selected." value=\"".$row['id']."\">" . $row['ragso1'] ." ".$row['ragso2']. "</option>";
}
if ($sel==0){
	echo "<option selected value=\"\"></option>";
}
?>
</select>
<?php
// se è una produzione industriale visualizzo data e ora di inizio e fine
// Inserimento data inizio lavori
echo "<tr>
		<td class=\"FacetFieldCaptionTD\">" . $script_transl[33] . "</td>
		<td class=\"FacetDataTD\">
		". gaz_select_data ( "iniprod", $form['iniprod'] ) ."&nbsp;Ora inizio
		". gaz_select_ora ( "iniprodtime", $form['iniprodtime'] ) ."
		</td>
	</tr>";

// Inserimento data fine lavori
echo "<tr>
		<td class=\"FacetFieldCaptionTD\">" . $script_transl[34] . "</td>
		<td class=\"FacetDataTD\">
		". gaz_select_data ( "fineprod", $form['fineprod'] ) ."&nbsp;Ora fine
		". gaz_select_ora ( "fineprodtime", $form['fineprodtime'] ) ."
		</td>
	</tr>";

if ($form['order_type'] <> "AGR") { // input esclusi se produzione agricola
    // Antonio Germani > Inizio LOTTO in entrata o creazione nuovo

    if (intval($form['lot_or_serial']) == 1) { // se l'articolo prevede il lotto apro la gestione lotti nel form
?>
		<tr><td class="FacetFieldCaptionTD"><?php echo $script_transl[13]; ?></td>
		<td class="FacetDataTD" >
		<input type="hidden" name="filename" value="<?php echo $form['filename']; ?>">
		<input type="hidden" name="id_lotmag" value="<?php echo $form['id_lotmag']; ?>">
<?php
        if (strlen($form['filename']) == 0) {
            echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog">' . 'Inserire nuovo certificato' . ' ' . '<i class="glyphicon glyphicon-tag"></i>' . '</button></div>';
        } else {
            echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog">' . $form['filename'] . ' ' . '<i class="glyphicon glyphicon-tag"></i>' . '</button>';
            echo '</div>';
        }
        if (strlen($form['identifier']) == 0) {
            echo '<div><button class="btn btn-xs btn-danger" type="image" data-toggle="collapse" href="#lm_dialog_lot">' . 'Inserire nuovo Lotto' . ' ' . '<i class="glyphicon glyphicon-tag"></i></button></div>';
        } else {
            if (intval($form['expiry']) > 0) {
                echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot">' . $form['identifier'] . ' ' . gaz_format_date($form['expiry']) . '<i class="glyphicon glyphicon-tag"></i></button></div>';
            } else {
                echo '<div><button class="btn btn-xs btn-success" type="image" data-toggle="collapse" href="#lm_dialog_lot" >' . $form['identifier'] . '<i class="glyphicon glyphicon-tag" ></i></button></div>';
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
        echo '<label>' . "Numero: " . '</label><input type="text" name="identifier" value="' . $form['identifier'] . '" >';
        echo "<br>";
        echo '<label>' . 'Scadenza: ' . ' </label><input class="datepicker" type="text" onchange="this.form.submit();" name="expiry"  value="' . $form['expiry'] . '"></div></div></div>';
    } else {
        echo '<tr><td><input type="hidden" name="filename" value="' . $form['filename'] . '">';
        echo '<input type="hidden" name="identifier" value="' . $form['identifier'] . '">';
        echo '<input type="hidden" name="id_lotmag" value="' . $form['id_lotmag'] . '">';
        echo '<input type="hidden" name="expiry" value="' . $form['expiry'] . '"></td></tr>';
    }

    // fine LOTTI in entrata
} else { //se è produzione agricola
    print "<tr><td><input type=\"hidden\" name=\"nmov\" value=\"0\">";
    print "<input type=\"hidden\" name=\"nmovdb\" value=\"\">\n";
   // print "<input type=\"hidden\" name=\"staff0\" value=\"\">\n";
    print "<input type=\"hidden\" name=\"filename\" value=\"\">\n";
    print "<input type=\"hidden\" name=\"expiry\" value=\"\">\n";
    print "<input type=\"hidden\" name=\"identifier\" value=\"\">\n";
    print "<input type=\"hidden\" name=\"id_lotmag\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"SIAN\" value=\"\">\n";
	print "<input type=\"hidden\" name=\"quality\" value=\"\">\n";
    print "<input type=\"hidden\" name=\"lot_or_serial\" value=\"\"></td></tr>";
}
if ($popup <> 1) {
    //ANNULLA/RESET NON FUNZIONA DA RIVEDERE > print "<tr><td class=\"FacetFieldCaptionTD\"><input type=\"reset\" name=\"Cancel\" value=\"".$script_transl['cancel']."\">\n";
    print "<tr><td style=\"padding-top: 10px; text-align:center;\" class=\"FacetDataTD\" >\n";
    print "<input type=\"submit\" name=\"Return\" value=\"" . $script_transl['return'] . "\">\n</td><td style=\"padding-top: 10px; text-align:center;\" class=\"FacetDataTD\">";
} else {
    print "<tr><td>&nbsp;</td><td style=\"padding-top: 10px; text-align:center;\" class=\"FacetDataTD\" >";
}

if ($toDo == 'update') {
    print '<input type="submit" accesskey="m" class="btn '.$class.'" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="' . ucfirst($script_transl['update']) . $addvalue . '">';
} else {
    print '<input type="submit" accesskey="i" class="btn '.$class.'" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="' . ucfirst($script_transl['insert']) . $addvalue . '">';
}
print "</td></tr></table>\n";
?>
</form>
<?php
require ("../../library/include/footer.php");
?>
