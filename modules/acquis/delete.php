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
// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}
if ((isset($_POST['type'])&&isset($_POST['ref'])) OR (isset($_POST['type']) && isset($_POST['id_tes']) && is_numeric($_POST['id_tes']))) { 
	require("../../library/include/datlib.inc.php");
	require("../../modules/magazz/lib.function.php");
	$upd_mm = new magazzForm;
	$admin_aziend = checkAdmin();
	switch ($_POST['type']) {
        case "broacq":
			$i=intval($_POST['id_tes']);
			//cancello la testata
			gaz_dbi_del_row($gTables['tesbro'], "id_tes", $i);
			//... e i righi
			$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = '{$i}'","id_tes desc");
			while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
				gaz_dbi_del_row($gTables['rigbro'], "id_rig", $a_row['id_rig']);
			}
		break;
		case "docacq":
			
			$i=intval($_POST['id_tes']);
			$data = gaz_dbi_get_row($gTables['tesdoc'], "id_tes", $i);
			if ($data['tipdoc']!="AFT"){ // se non è una fattura AFT con DDT a riferimento posso cancellare
				gaz_dbi_del_row($gTables['tesdoc'], "id_tes", $i);
				gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $data['id_con']);

                // qui controllo se il documento ha generato reverse charge ed eventualmente elimino anche quello
                $id_rc=gaz_dbi_get_row($gTables['rigmoi'], 'reverse_charge_idtes', $data['id_con']); // in $id_rc['id_tes'] ho il riferimento a tesmov figlio 
                // cancello l'eventuale figlio (fattura su reg.vendite del reverse charge)
                if ($id_rc){
                  gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $id_rc['id_tes']);
                  gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $id_rc['id_tes']);
                  gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $id_rc['id_tes']);
                  // manca la cancellazione del futuro tesdoc-rigdoc (entro 2023)
                }

                // prima di eliminare i righi contabili devo eliminare le eventuali partite aperte ad essi collegati
				$rs_rmocdel = gaz_dbi_dyn_query("*", $gTables['rigmoc'], "id_tes = ".$data['id_con'],"id_tes");
   				while ($rd = gaz_dbi_fetch_array($rs_rmocdel)) {
                    gaz_dbi_del_row($gTables['paymov'], "id_rigmoc_doc", $rd['id_rig']);
                }

                // ... quindi elimino il rigo contabile 
                gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $data['id_con']);
				gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $data['id_con']);
				$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '".$i."'","id_tes desc");
				
				while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
					gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $a_row['id_rig']);
					if (intval($a_row['id_mag']) > 0){  //se c'� stato un movimento di magazzino lo azzero
						$upd_mm->uploadMag('DEL', '', '', '', '', '', '', '', '', '', '', '', $a_row['id_mag']);
						
						// cancello pure eventuale movimento sian 
						gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", $a_row['id_mag']);
					}				
				}
			} else { // se è AFT (fattura con ddt a riferimento)
                    if (empty($data['ddt_type'])){
                        $tipdoc="ADT";
                        $data['ddt_type']="T";
                    } elseif ( $data['ddt_type']=="T") {
						$tipdoc="AD".$data["ddt_type"]; 
					} elseif ($data['ddt_type']=="L"){
						$tipdoc="RD".$data["ddt_type"];
					} else {
						$tipdoc="AM".$data["ddt_type"]; // Contratto di traporto in entrata
					}
						
					$groups=gaz_dbi_dyn_query("*", $gTables['tesdoc'], "protoc = '".$data['protoc']."' AND datfat = '".$data['datfat']."' AND seziva = '".$data['seziva']."' AND clfoco = '".$data['clfoco']."'");
					
					while ($data = gaz_dbi_fetch_array($groups)){
                      if ($data['status']=='DdtAnomalo'){
						gaz_dbi_del_row($gTables['tesdoc'], "id_tes", $data['id_tes']);
						$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '".$i."'","id_tes desc");
						while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
							gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $a_row['id_rig']);
							if (intval($a_row['id_mag']) > 0){  //se c'� stato un movimento di magazzino lo azzero
								$upd_mm->uploadMag('DEL', '', '', '', '', '', '', '', '', '', '', '', $a_row['id_mag']);
								// cancello pure eventuale movimento sian 
								gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", $a_row['id_mag']);
							}				
						}
                      } else {
                        $newval=$data;
						$newval['datreg']=$data['datemi'];$newval['protoc']="";$newval['ddt_type']="";$newval['numfat']="";$newval['datfat']="";$newval['id_con']=0;$newval['tipdoc']=$tipdoc;
						$newval['fattura_elettronica_original_name']="";$newval['fattura_elettronica_original_content']="";
						tesdocUpdate(array('id_tes', $newval['id_tes']), $newval);						
                      }
                      // qui controllo se il documento ha generato reverse charge ed eventualmente elimino anche quello
                      $id_rc=gaz_dbi_get_row($gTables['rigmoi'], 'reverse_charge_idtes', $data['id_con']); // in $id_rc['id_tes'] ho il riferimento a tesmov figlio 
                      // cancello l'eventuale figlio (fattura su reg.vendite del reverse charge)
                      if ($id_rc){
                        gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $id_rc['id_tes']);
                        gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $id_rc['id_tes']);
                        gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $id_rc['id_tes']);
                        // manca la cancellazione del futuro tesdoc-rigdoc (entro 2023)
                      }
                      gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $data['id_con']);

                      // prima di eliminare i righi contabili devo eliminare le eventuali partite aperte ad essi collegati
                      $rs_rmocdel = gaz_dbi_dyn_query("*", $gTables['rigmoc'], "id_tes = ".$data['id_con'],"id_tes");
                      while ($rd = gaz_dbi_fetch_array($rs_rmocdel)) {
                        gaz_dbi_del_row($gTables['paymov'], "id_rigmoc_doc", $rd['id_rig']);
                      }
                      
                      // ... quindi elimino il rigo contabile 
					  gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $data['id_con']);
					  gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $data['id_con']);
                      
					}
										 			
			}
		break;
		case "pagdeb":
			$i=intval($_POST['id_tes']);
			//cancello la testata
			gaz_dbi_del_row($gTables['tesbro'], "id_tes", $i);
			//... e i righi
			$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes = '{$i}'","id_tes desc");
			while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
				  gaz_dbi_del_row($gTables['rigbro'], "id_rig", $a_row['id_rig']);
			}
		break;
		case "fornit":
			$i=intval($_POST['ref']);
			gaz_dbi_del_row($gTables['clfoco'], 'codice', $i);
		break;
		case "email":
			$i=filter_var($_POST['ref'], FILTER_VALIDATE_EMAIL);
			gaz_dbi_put_query($gTables['tesbro'], " email LIKE '%".$i."%'",'email','');
		break;
		case "supplier_schedule":
			$i=intval($_POST['ref']);
			gaz_dbi_del_row($gTables['paymov'], 'id_tesdoc_ref', $i);
		break;
	}
}
?>