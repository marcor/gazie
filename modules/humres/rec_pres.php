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
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
//echo "<pre>",print_r ($_POST);
$deleted_rows = (isset($_POST['deleted_rows']))?$_POST['deleted_rows']:$deleted_rows=array();

if (isset($_POST['rec_pres'])){
	$rec_pres = $_POST['rec_pres'];
	$n=0;
	foreach ($rec_pres as $rec_pre){
		// controlli
		$n++;
		if (strtotime($rec_pre['start_work']) > strtotime($rec_pre['end_work'])){
			echo "ERRORE riga ",$n,": l'ora di fine è inferiore a quella di inizio";
			break;
		}
		if (((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600)>8){
			echo "ERRORE riga ",$n,": non si può inserire un movimento con più di 8 ore";
			break;
		}
		if ($rec_pre['start_work'] == "" AND $rec_pre['end_work'] == ""){ 
			echo "ERRORE riga ",$n,": l'orario di inizio e di fine deve essere sempre impostato";
			break;
		}
		if ($rec_pre['start_work'] == $rec_pre['end_work']){ 
			echo "ERRORE riga ",$n,": l'orario di inizio e di fine non possono essere uguali";
			break;
		}
		
		// se ok, passo al DB
		if ($rec_pre['id']>0){ // è un update
			//echo "update";
			$cart = gaz_dbi_get_row($gTables['staff_work_movements'], "id", $rec_pre['id']);// carico il vecchio movimento cartellino
			$type = gaz_dbi_get_row($gTables['staff_work_type'], "id_work", $cart['id_work_type']);// carico il tipo di movimento del vecchio cartellino
			$rec_type = gaz_dbi_get_row($gTables['staff_work_type'], "id_work", $rec_pre['id_work_type']);// carico il tipo di movimento del nuovo cartellino
			$gen = gaz_dbi_get_row($gTables['staff_worked_hours'], "id", $rec_pre['id-worked']);// carico il vecchio movimento generale
			
			// tolgo il vecchio valore del cartellino dal generale
			if ($cart['id_work_type']==0){// lavoro ordinario	
				$gen['hours_normal']=$gen['hours_normal']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
				$updValue =array("hours_normal"=>$gen['hours_normal']); 	
			} elseif ($type['id_work_type']==1){// lavoro straordinario
				$gen['hours_extra']=$gen['hours_extra']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
				$updValue =array("hours_extra"=>$gen['hours_extra']);
				if ($gen['hours_extra']<0.009){
						$updValue['id_work_type_extra']=0;
					}
			} elseif ($type['id_work_type'] > 1 AND $type['id_work_type'] < 7){// lavoro notturno+lavoro domenicale+lavoro festivo+lavoro giorni riposo+lavoro in turni
				$gen['hours_other']=$gen['hours_other']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
				$updValue =array("hours_other"=>$gen['hours_other']);
				if ($gen['hours_other'] < 0.009){
						$updValue['id_other_type']=0;
					}
			} elseif ($type['id_work_type'] == 9){// Assenza
				$gen['hours_absence']=$gen['hours_absence']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
				$updValue =array("hours_absence"=>$gen['hours_absence']);
				if ($gen['hours_absence']<0.009){
						$updValue['id_absence_type']=0;
					}		
			}
			gaz_dbi_table_update("staff_worked_hours", array('id', $gen['id']), $updValue);
			
			// aggiorno il cartellino 
			$newValue =array("start_work"=>$_POST['date']." ".$rec_pre['start_work'], "end_work"=>$_POST['date']." ".$rec_pre['end_work'], "id_work_type"=>$rec_pre['id_work_type'], "min_delay"=>$rec_pre['min_delay'], "id_orderman"=>$rec_pre['id_orderman'], "note"=>$rec_pre['note']); 
			gaz_dbi_table_update("staff_work_movements", array('id', $rec_pre['id']), $newValue);
			
			// Aggiorno il generale con il nuovo movimento del cartellino 
			if ($rec_pre['id_work_type']==0){// lavoro ordinario			
				$gen['hours_normal']=$gen['hours_normal']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
				$updValue =array("hours_normal"=>$gen['hours_normal']);		
			} elseif ($rec_type['id_work_type']==1){// lavoro straordinario
				$gen['hours_extra']=$gen['hours_extra']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
				$updValue =array("hours_extra"=>$gen['hours_extra']);
				$updValue['id_work_type_extra']= $rec_pre['id_work_type']; 
			} elseif ($rec_type['id_work_type'] > 1 AND $rec_type['id_work_type'] < 7){// lavoro notturno+lavoro domenicale+lavoro festivo+lavoro giorni riposo+lavoro in turni
				$gen['hours_other']=$gen['hours_other']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
				$updValue =array("hours_other"=>$gen['hours_other']);
				$updValue['id_other_type']= $rec_pre['id_work_type'];
			} elseif ($rec_type['id_work_type'] == 9){// Assenza
				$gen['hours_absence']=$gen['hours_absence']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
				$updValue =array("hours_absence"=>$gen['hours_absence']);
				$updValue['id_absence_type']= $rec_pre['id_work_type'];
			}  
			// NOTA ID orderman non va più registrato in worked hours perché possono essere vari nell'arco di una giornata
			
			gaz_dbi_table_update("staff_worked_hours", array('id', $gen['id']), $updValue);
			
		} else { // è un insert
			
			//echo "insert";
			$rec_type = gaz_dbi_get_row($gTables['staff_work_type'], "id_work", $rec_pre['id_work_type']);// carico il tipo di movimento del nuovo cartellino
			$gen = gaz_dbi_get_row($gTables['staff_worked_hours'], "work_day", $_POST['date']);// cerco di caricare il vecchio movimento generale
			if (isset($gen)){// se esiste il movimento generale lo aggiorno
				// Aggiorno il generale con il nuovo movimento del cartellino 
				if ($rec_pre['id_work_type']==0){// lavoro ordinario			
					$gen['hours_normal']=$gen['hours_normal']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
					$updValue =array("hours_normal"=>$gen['hours_normal']);		
				} elseif ($rec_type['id_work_type']==1){// lavoro straordinario
					$gen['hours_extra']=$gen['hours_extra']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
					$updValue =array("hours_extra"=>$gen['hours_extra']);
					$updValue['id_work_type_extra']= $rec_pre['id_work_type']; 
				} elseif ($rec_type['id_work_type'] > 1 AND $rec_type['id_work_type'] < 7){// lavoro notturno+lavoro domenicale+lavoro festivo+lavoro giorni riposo+lavoro in turni
					$gen['hours_other']=$gen['hours_other']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
					$updValue =array("hours_other"=>$gen['hours_other']);
					$updValue['id_other_type']= $rec_pre['id_work_type'];
				} elseif ($rec_type['id_work_type'] == 9){// Assenza
					$gen['hours_absence']=$gen['hours_absence']+((strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600);
					$updValue =array("hours_absence"=>$gen['hours_absence']);
					$updValue['id_absence_type']= $rec_pre['id_work_type'];
				}  
				// NOTA ID orderman non va più registrato in worked hours perché possono essere vari nell'arco di una giornata
				
				gaz_dbi_table_update("staff_worked_hours", array('id', $gen['id']), $updValue);
				$id_staff_worked_hours = $gen['id'];
				
			} else {// altrimenti lo creo
				$insValue= array("id_staff"=>$_POST['id_staff'], "work_day"=>$_POST['date']);
				if ($rec_pre['id_work_type']==0){// lavoro ordinario					
					$insValue['hours_normal'] = (strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600;			
				} elseif ($rec_type['id_work_type']==1){// lavoro straordinario					
					$insValue['hours_extra'] = (strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600;
					$insValue['id_work_type_extra'] = $rec_pre['id_work_type']; 
				} elseif ($rec_type['id_work_type'] > 1 AND $rec_type['id_work_type'] < 7){// lavoro notturno+lavoro domenicale+lavoro festivo+lavoro giorni riposo+lavoro in turni
					$insValue['hours_other'] = (strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600;
					$insValue['id_other_type']= $rec_pre['id_work_type'];
				} elseif ($rec_type['id_work_type'] == 9){// Assenza
					$insValue['hours_absence'] = (strtotime($rec_pre['end_work'])-strtotime($rec_pre['start_work']))/3600;
					$insValue['id_absence_type']= $rec_pre['id_work_type'];
				} 
				// NOTA ID orderman non va più registrato in worked hours perché possono essere vari nell'arco di una giornata
				$id_staff_worked_hours = gaz_dbi_table_insert("staff_worked_hours", $insValue);
			}
			
			$value =array("id"=>$rec_pre['id'], "id_staff"=>$_POST['id_staff'], "start_work"=>$_POST['date']." ".$rec_pre['start_work'], "end_work"=>$_POST['date']." ".$rec_pre['end_work'], "id_work_type"=>$rec_pre['id_work_type'], "min_delay"=>$rec_pre['min_delay'], "id_orderman"=>$rec_pre['id_orderman'], "note"=>$rec_pre['note'], "id_staff_worked_hours"=>$id_staff_worked_hours);
			gaz_dbi_table_insert("staff_work_movements", $value);
					
		}		
	}	
}	
foreach ($deleted_rows as $del_row){// ciclo i righi da cancellare
	if (intval($del_row)>0){// se è un rigo esistente in staff_work_movements
		// aggiorno il generale e cancello il rigo cartellino
		//echo "cancello",$del_row;
		$cart = gaz_dbi_get_row($gTables['staff_work_movements'], "id", $del_row);// carico il vecchio movimento cartellino
		$type = gaz_dbi_get_row($gTables['staff_work_type'], "id_work", $cart['id_work_type']);// carico il tipo di movimento del vecchio cartellino
		$gen = gaz_dbi_get_row($gTables['staff_worked_hours'], "id", $cart['id_staff_worked_hours']);// carico il vecchio movimento generale
		
		// tolgo il valore del cartellino dal generale
		if ($cart['id_work_type']==0){// lavoro ordinario
		
			$gen['hours_normal']=$gen['hours_normal']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
			$updValue =array("hours_normal"=>$gen['hours_normal']); 	
		} elseif ($type['id_work_type']==1){// lavoro straordinario
			$gen['hours_extra']=$gen['hours_extra']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
			$updValue =array("hours_extra"=>$gen['hours_extra']);
			if ($gen['hours_extra']<0.009){
				$updValue['id_work_type_extra']=0;
			}
		} elseif ($type['id_work_type'] > 1 AND $type['id_work_type'] < 7){// lavoro notturno+lavoro domenicale+lavoro festivo+lavoro giorni riposo+lavoro in turni
			$gen['hours_other']=$gen['hours_other']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
			$updValue =array("hours_other"=>$gen['hours_other']); 
			if ($gen['hours_other'] < 0.009){
				$updValue['id_other_type']=0;
			}
		} elseif (substr($type['id_work_type'], 0, 1) == "A"){// Assenza
			$gen['hours_absence']=$gen['hours_absence']-((strtotime($cart['end_work'])-strtotime($cart['start_work']))/3600);
			$updValue =array("hours_absence"=>$gen['hours_absence']);
			if ($gen['hours_absence']<0.009){
				$updValue['id_absence_type']=0;
			}
		}
		
		gaz_dbi_table_update("staff_worked_hours", array('id', $gen['id']), $updValue);
		
		gaz_dbi_del_row($gTables['staff_work_movements'], "id", $del_row);
			
	}
}