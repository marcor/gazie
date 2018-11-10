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
							array('lun' => 30,'nam'=>'Cliente'),
							array('lun' => 30,'nam'=>'Descrizione'),
                             array('lun' => 15,'nam'=>'Articolo'),
                             array('lun' => 15,'nam'=>'Quantità'),
                             array('lun' => 30,'nam'=>'Lotto'),
                             array('lun' => 18,'nam'=>'Scadenza'),
                            )
              );
// Antonio Germani 
	$resord = gaz_dbi_get_row($gTables['orderman'], "id", $_GET['id_orderman']); 
	// in caso di più orderman si dovrà inserire un ciclo
	$restes = gaz_dbi_get_row($gTables['tesbro'], "id_tes", $resord['id_tesbro']);
	$resrig = gaz_dbi_get_row($gTables['rigbro'], "id_rig", $resord['id_rigbro']);
	$resclfo = gaz_dbi_get_row($gTables['clfoco'], "codice", $restes['clfoco']);
	$reslot = gaz_dbi_get_row($gTables['lotmag'], "id", $resord['id_lotmag']);
	
	$pdf = new Report_template('L','mm','A4',true,'UTF-8',false,true);
	$pdf->setVars($admin_aziend,$title);
	$pdf->SetTopMargin(42);
	$pdf->SetFooterMargin(20);
	$config = new Config;
	$pdf->AddPage('L',$config->getValue('page_format'));
	$pdf->SetFont('helvetica','',7);
	$pdf->setJPEGQuality(15);
	$n="";
	 
		
			if ($n>0){// evita la pagina bianca alla fine del ciclo while
				$pdf->AddPage(); // manda alla pagina successiva
			}$n=1;
			$pdf->Cell(16,3,$restes['numdoc'],1);
			$pdf->Cell(18,3,$restes['datemi'],1);
			$pdf->Cell(30,3,substr($resclfo['descri'],0,30),1);
			$pdf->Cell(30,3,substr($resord['description'],0,30),1);
			$pdf->Cell(15,3,$resrig['codart'],1);
			$pdf->Cell(15,3,$resrig['quanti'],1);
			$pdf->Cell(30,3,substr($reslot['identifier'],0,30),1);
			$pdf->Cell(18,3,gaz_format_date($reslot['expiry']),1);       
	

	$pdf->Output();
?>