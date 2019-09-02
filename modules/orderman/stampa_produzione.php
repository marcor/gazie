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

require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();

if (!ini_get('safe_mode')){ //se me lo posso permettere...
	ini_set('memory_limit','128M');
	gaz_set_time_limit (0);
}

$resord = gaz_dbi_get_row($gTables['orderman'], "id", intval($_GET['id_orderman']));	
$now = new DateTime();
$luogo_data=$admin_aziend['citspe'].", lì ".$now->format('d-m-Y');

require("../../config/templates/report_template.php");
$title = array('luogo_data'=>$luogo_data,
           'title'=>"Distinta della produzione n.".intval($_GET['id_orderman']).' - '.$resord['description'],
           'hile'=>array(array('lun' => 16,'nam'=>'Numero'),
						array('lun' => 18,'nam'=>'Data'),
						array('lun' => 45,'nam'=>'Cliente'),
						array('lun' => 55,'nam'=>'Descrizione'),
						array('lun' => 30,'nam'=>'Informazioni'),
                         array('lun' => 15,'nam'=>'Articolo'),
                         array('lun' => 15,'nam'=>'Quantità'),
                         array('lun' => 30,'nam'=>'Lotto'),
                         array('lun' => 18,'nam'=>'Scadenza'),
						 array('lun' => 25,'nam'=>'Luogo'),
						 array('lun' => 10,'nam'=>'Durata'),
                        )
          );

$aRiportare = array('top' => array(array('lun' => 166, 'nam' => 'da riporto : '),
        array('lun' => 20, 'nam' => '')
    ),
    'bot' => array(array('lun' => 166, 'nam' => 'a riportare : '),
        array('lun' => 20, 'nam' => '')
    )
);

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
$pdf->setRiporti('');
$pdf->AddPage('L',$config->getValue('page_format'));
$pdf->SetFillColor(hexdec(substr($admin_aziend['colore'], 0, 2)), hexdec(substr($admin_aziend['colore'], 2, 2)), hexdec(substr($admin_aziend['colore'], 4, 2)));
$pdf->SetFont('helvetica','',8);
$pdf->setJPEGQuality(15);
$n="";
$pdf->Cell(16,4,$resord['id'],1, 0, 'C', 0, '', 1);
$pdf->Cell(18,4,gaz_format_date($restes['datemi']),1, 0, 'C', 0, '', 1);
$pdf->Cell(45,4,substr($resclfo['descri'],0,35),1, 0, 'L', 0, '', 1);
$pdf->Cell(55,4,substr($resord['description'],0,40),1, 0, 'L', 0, '', 1);
$pdf->Cell(30,4,substr($resord['add_info'],0,30),1, 0, 'L', 0, '', 1);
$pdf->Cell(15,4,$resrig['codart'],1, 0, 'L', 0, '', 1);
if ($resrig['quanti']>=0.01){
	$pdf->Cell(15,4,$resrig['quanti'],1, 0, 'L', 0, '', 1);
} else {
	$pdf->Cell(15,4,'',1, 0, 'L', 0, '', 1);
}
$pdf->Cell(30,4,substr($reslot['identifier'],0,30),1, 0, 'L', 0, '', 1);
if (strlen($reslot['expiry'])>=10){
	$pdf->Cell(18,4,gaz_format_date($reslot['expiry']),1, 0, 'L', 0, '', 1);
} else {
	$pdf->Cell(18,4,'',1, 0, 'L', 0, '', 1);
}
$pdf->Cell(25,4,substr($rescamp['descri'],0,25),1, 0, 'L', 0, '', 1);
$pdf->Cell(10,4,$resord['duration'],1, 1, 'C', 0, '', 1);
$pdf->Ln(2);
// Antonio Germani - Stampa operai
$query="SELECT ".'id_staff'." FROM ".$gTables['staff_worked_hours']. " WHERE id_orderman =". intval($_GET['id_orderman']);
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
// Antonio Germani - Stampa componenti
if ($resart['good_or_service']==2){ // se l'articolo prodotto prevede componenti
	$query="SELECT artico, quanti, id_lotmag FROM ".$gTables['movmag']. " WHERE id_orderman =". intval($_GET['id_orderman'])." AND operat = '-1'";
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
			if ($reslot['expiry']==0) {
				$pdf->MultiCell(25, 4, "" , 1, 'L', 0, 1, '225', $sp, false,0,true,true);
			} else {
				$pdf->MultiCell(25, 4, gaz_format_date($reslot['expiry']) , 1, 'L', 0, 1, '225', $sp, false,0,true,true);
			}
			$sp=$sp+6;
			
		}
		
	}
}

// STAMPA LISTA ORDINI
$ctrlAOR=0;
$tot=0.00;
$ctrlAORtot=0.00;
$query="SELECT *,".$gTables['rigbro'].".descri AS rigdes FROM ".$gTables['rigbro']. " 
LEFT JOIN ".$gTables['tesbro']. " ON ".$gTables['rigbro'].".id_tes = ".$gTables['tesbro'].".id_tes 
LEFT JOIN ".$gTables['clfoco']. " ON ".$gTables['tesbro'].".clfoco = ".$gTables['clfoco'].".codice 
LEFT JOIN ".$gTables['anagra']. " ON ".$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id 
WHERE tipdoc='AOR' AND ".$gTables['rigbro'].".id_orderman =".intval($_GET['id_orderman'])." ORDER BY datemi ASC, ".$gTables['tesbro'].".id_tes ASC";
$res=gaz_dbi_query($query);
while($row=$res->fetch_assoc()){
	//print_r($row);
	switch ($row['tiprig']){
	    case "0": // normale
	    case "50": // normale c/allegato
			$amount=CalcolaImportoRigo($row['quanti'],$row['prelis'],$row['sconto']);
        break;
		case "1": //forfait
			$amount=CalcolaImportoRigo(1,$row['prelis'],$row['sconto']);
        break;
		default:
		$amount=0;
	}
	$tot+=$amount;
	$ctrlAORtot+=$amount;
	if ($ctrlAOR==0){
		$pdf->Cell(277,5,'LISTA DEGLI ORDINI A FORNITORI','LTR', 1, 'L', 1, '', 1);
		$pdf->Cell(105,5,'Fornitore','LBR',0,'L',1);
		$pdf->Cell(82,5,'descrizione acquisto','LBR',0,'L',1);
		$pdf->Cell(10,5,'U.M.','LBR',0,'C',1);
		$pdf->Cell(20,5,'quantità','LBR',0,'R',1); 
		$pdf->Cell(20,5,'prezzo','LBR',0,'R',1);
		$pdf->Cell(10,5,'sconto','LBR',0,'C',1);
		$pdf->Cell(30,5,'importo','LBR',1,'R',1);
	}
	if ($ctrlAOR<>$row['id_tes']){
		$pdf->Cell(277,5,$row['descri'].' ORDINE n.'.$row['numdoc'].' del '.gaz_format_date($row['datemi']),1, 1, 'L', 0, '', 1);
		if ($amount>=0.01&&$ctrlAORtot==0.00){ // è cambiato l'ordine ma il precedente ha un totale a zero...
			$pdf->SetTextColor(255,0,0);
			$pdf->Cell(105,4);
			$pdf->Cell(172,5,' O R D I N E   D I   V A L O R E    N U L L O   ! ?',1,1,'C');
			$pdf->SetTextColor(0);
		}
		$ctrlAORtot=0.00;
	}	
	if ($amount>=0.01){
		$pdf->Cell(105,5);
		$pdf->Cell(82,5,$row['rigdes'],1,0,'L');
		$pdf->Cell(10,5,$row['unimis'],1,0,'C');
		$pdf->Cell(20,5,$row['quanti'],1,0,'R'); 
		$pdf->Cell(20,5,$row['prelis'],1,0,'R');
		$pdf->Cell(10,5,$row['sconto'],1,0,'C');
		$pdf->Cell(30,5,gaz_format_number($amount),1, 1, 'R', 0, '', 1);
	}
	$ctrlAOR=$row['id_tes'];
}
if ($tot>=0.01){
	$pdf->Cell(277,4,'TOTALE DELL\'ORDINATO PER LA PRODUZIONE: '.gaz_format_number($tot),1, 1, 'R', 1, '', 1);
	
}
// FINE STAMPA LISTA ORDINI

$pdf->setRiporti('');
$pdf->Output();
?>