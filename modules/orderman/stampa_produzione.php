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
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */

 // Antonio Germani - STAMPA DI UNA PRODUZIONE 
 
	require("../../library/include/datlib.inc.php");

	$admin_aziend=checkAdmin();

	if (!ini_get('safe_mode')){ //se me lo posso permettere...
		ini_set('memory_limit','128M');
		gaz_set_time_limit (0);
	}

	$luogo_data=$admin_aziend['citspe'].", lì ";
	$now = new DateTime();
// $_GET['id_orderman'] è l'id della produzione
 
	require("../../config/templates/report_template_qc.php");
	$title = array('luogo_data'=>$luogo_data,
               'title'=>"Distinta di produzione del ".$now->format('d-m-Y'),
               'hile'=>array(array('lun' => 16,'nam'=>'N. ordine'),
							array('lun' => 18,'nam'=>'Data ordine'),
							array('lun' => 35,'nam'=>'Cliente'),
							array('lun' => 40,'nam'=>'Descrizione'),
							array('lun' => 30,'nam'=>'Informazioni'),
                             array('lun' => 15,'nam'=>'Articolo'),
                             array('lun' => 15,'nam'=>'Quantità'),
                             array('lun' => 30,'nam'=>'Lotto'),
                             array('lun' => 18,'nam'=>'Scadenza'),
							 array('lun' => 25,'nam'=>'Luogo'),
							 array('lun' => 10,'nam'=>'Durata'),
                            )
              );
// Antonio Germani 
	$resord = gaz_dbi_get_row($gTables['orderman'], "id", $_GET['id_orderman']);	
	$restes = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $resord['id_tesbro']);
	$resrig = gaz_dbi_get_row($gTables['rigbro'], "id_rig", $resord['id_rigbro']);
	$resclfo = gaz_dbi_get_row($gTables['clfoco'], "codice", $restes['clfoco']);
	$reslot = gaz_dbi_get_row($gTables['lotmag'], "id", $resord['id_lotmag']);
	$resart = gaz_dbi_get_row($gTables['artico'], "codice", $resrig['codart']);
	$rescamp = gaz_dbi_get_row($gTables['campi'], "codice", $resord['campo_impianto']);
	
	$pdf = new Report_template('L','mm','A4',true,'UTF-8',false,true);
	$pdf->setVars($admin_aziend,$title);
	$pdf->SetTopMargin(42);
	$pdf->SetFooterMargin(20);
	$config = new Config;
	$pdf->AddPage('L',$config->getValue('page_format'));
	$pdf->SetFont('helvetica','',8);
	$pdf->setJPEGQuality(15);
	$n="";
//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')	 
		
			$pdf->Cell(16,4,$restes['numdoc'],1);
			$pdf->Cell(18,4,$restes['datemi'],1);
			$pdf->Cell(35,4,substr($resclfo['descri'],0,35),1);
			$pdf->Cell(40,4,substr($resord['description'],0,40),1);
			$pdf->Cell(30,4,substr($resord['add_info'],0,30),1);
			$pdf->Cell(15,4,$resrig['codart'],1);
			$pdf->Cell(15,4,$resrig['quanti'],1);
			$pdf->Cell(30,4,substr($reslot['identifier'],0,30),1);
			$pdf->Cell(18,4,gaz_format_date($reslot['expiry']),1);
			$pdf->Cell(25,4,substr($rescamp['descri'],0,25),1);
			$pdf->Cell(10,4,$resord['duration'],1);
$pdf->Ln(8);

// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)

	// Antonio Germani - Stampa operai
		$query="SELECT ".'id_staff'." FROM ".$gTables['staff_worked_hours']. " WHERE id_orderman ='". $_GET['id_orderman']."'";
			$resoper = gaz_dbi_query($query);
			
			if ($resoper->num_rows >0) {
				$pdf->SetFillColor(255, 255, 127);
				$pdf->MultiCell(50, 4, 'Elenco operai', 1, 'C', 1, 0, '', 50, false);
				$sp=55;
				while($row = $resoper->fetch_assoc()){
					
					$resstaff = gaz_dbi_get_row($gTables['staff'], "id_staff", $row['id_staff']);
					$resnome = gaz_dbi_get_row($gTables['clfoco'], "codice", $resstaff['id_clfoco']);
					
					$pdf->MultiCell(50, 4, $resnome['descri'], 1, 'L', 0, 0, '', $sp, false);
					$sp=$sp+5;
				}
			}
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)			
	// Antonio Germani - Stampa componenti
			If ($resart['good_or_service']==2){ // se l'articolo prodotto prevede componenti
				$query="SELECT artico, quanti, id_lotmag FROM ".$gTables['movmag']. " WHERE id_orderman ='". $_GET['id_orderman']."' AND operat = '-1'";
				$rescomp = gaz_dbi_query($query);
				if ($rescomp->num_rows >0) {
					$pdf->SetFillColor(255, 255, 127);
					$pdf->MultiCell(25, 4, 'Componenti', 1, 'C', 1, 0, '150', 50, false,0,false,true,100);
					$pdf->MultiCell(25, 4, 'Quantità', 1, 'C', 1, 0, '175', 50, false,0,false,true,100);
					$pdf->MultiCell(25, 4, 'Lotto', 1, 'C', 1, 0, '200', 50, false,0,false,true,100);
					$pdf->MultiCell(25, 4, 'Scadenza', 1, 'C', 1, 0, '225', 50, false,0,false,true,100);
					$sp=55;
					while($row = $rescomp->fetch_assoc()){
						
						$reslot = gaz_dbi_get_row($gTables['lotmag'], "id", $row['id_lotmag']);	
						$pdf->MultiCell(25, 4, $row['artico'] , 1, 'L', 0, 1, '150', $sp, false,0,true,true);
						$pdf->MultiCell(25, 4, $row['quanti'] , 1, 'L', 0, 1, '175', $sp, false,0,true,true);
						$pdf->MultiCell(25, 4, $reslot['identifier'] , 1, 'L', 0, 1, '200', $sp, false,0,true,true);
						$pdf->MultiCell(25, 4, gaz_format_date($reslot['expiry']) , 1, 'L', 0, 1, '225', $sp, false,0,true,true);
						$sp=$sp+6;
						
					}
					
				}
				
			}
	
			

	$pdf->Output();
?>