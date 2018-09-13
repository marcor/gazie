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



function getMovements($date_ini,$date_fin)
    {
        global $gTables,$admin_aziend;
        $m=array();
        $where="datdoc BETWEEN $date_ini AND $date_fin"; // Antonio Germani prendo la data di attuazione
        $what=$gTables['movmag'].".*, ".
              $gTables['caumag'].".codice, ".$gTables['caumag'].".descri, ".
			  //$gTables['clfoco'].".codice, ".$gTables['clfoco'].".descri AS ragsoc, ".
              $gTables['artico'].".codice, ".$gTables['artico'].".descri AS desart, ".$gTables['artico'].".unimis, ".$gTables['artico'].".scorta, ".$gTables['artico'].".catmer, ".$gTables['artico'].".rame_metallico ";
        $table=$gTables['movmag']." LEFT JOIN ".$gTables['caumag']." ON (".$gTables['movmag'].".caumag = ".$gTables['caumag'].".codice)     
               LEFT JOIN ".$gTables['artico']." ON (".$gTables['movmag'].".artico = ".$gTables['artico'].".codice)";
        $rs=gaz_dbi_dyn_query ($what,$table,$where, 'datreg ASC, tipdoc ASC, clfoco ASC, operat DESC, id_mov ASC');
        while ($r = gaz_dbi_fetch_array($rs)) {
            $m[] = $r;
        }
        return $m;
    }


$luogo_data=$admin_aziend['citspe'].", lì ";

if (isset($_GET['ds'])) {
   $giosta = substr($_GET['ds'],0,2);
   $messta = substr($_GET['ds'],2,2);
   $annsta = substr($_GET['ds'],4,4);
   $utssta= mktime(0,0,0,$messta,$giosta,$annsta);
   $luogo_data .= ucwords(strftime("%d %B %Y",$utssta));
} else {
   $luogo_data .=ucwords(strftime("%d %B %Y", mktime (0,0,0,date("m"),date("d"),date("Y"))));
}

$giori = substr($_GET['ri'],0,2);
$mesri = substr($_GET['ri'],2,2);
$annri = substr($_GET['ri'],4,4);
$utsri= mktime(0,0,0,$mesri,$giori,$annri);
$giorf = substr($_GET['rf'],0,2);
$mesrf = substr($_GET['rf'],2,2);
$annrf = substr($_GET['rf'],4,4);
$utsrf= mktime(0,0,0,$mesrf,$giorf,$annrf);

$result=getMovements(strftime("%Y%m%d",$utsri),strftime("%Y%m%d",$utsrf));
$giorni=intval(($utsrf-$utsri)/86400);
require("../../config/templates/report_template_qc.php");
$title = array('luogo_data'=>$luogo_data,
               'title'=>"DICHIARAZIONE RAME METALLO USATO dal ".strftime("%d %B %Y",$utsri)." al ".strftime("%d %B %Y",$utsrf)." = ".$giorni." giorni",
               'hile'=>array(
							array('lun' => 10,'nam'=>'N.'),
							array('lun' => 55,'nam'=>'Campo'),
							
                             array('lun' => 20,'nam'=>'Superficie ha'),                          
                             array('lun' => 30,'nam'=>'Rame metallo usato'),
                             array('lun' => 35,'nam'=>'Rame metallo ammesso'),
							 array('lun' => 10,'nam'=>'U.M.'),
							 array('lun' => 80,'nam'=>'Immagine')
                            )
              );

$n=0; $campi=array(); 
if (sizeof($result) > 0) { 
  while (list($key, $row) = each($result)) {
	  If ($row['campo_coltivazione']>0 && $row['type_mov']==1){ // se nel movimento è inserito un campo di coltivazione ed è un movimento del registro di campagna
				if ($row['rame_metallico']>0){ // se l'articolo contiene rame metallo
				$camp = gaz_dbi_get_row($gTables['campi'], "codice", $row['campo_coltivazione']); //carico i dati del campo di coltivazione
	  $array[$n]= array('campo_coltivazione'=>$row['campo_coltivazione'],'descri_campo'=>$camp['descri'],'img_campo'=>$camp['image'],'rame_metallo_prodotto'=>$row['rame_metallico'], 'superficie'=>$camp['ricarico'], 'rame_metallo_usato_su_campo'=>$row['rame_metallico']*$row['quanti']);
				$n++;  //ho creato un array con i dati che mi servono				
	  }}
  }
  
  rsort ($array); // ordino l'array per il primo valore che è il campo di coltivazione
 
  
  $c=0; for ($i=0; $i<$n; $i++){
	 
	  if ($i==0){ $campi[$c]=array('campo_coltivazione'=>$array[$i]['campo_coltivazione'],'descri_campo'=>$array[$i]['descri_campo'],'img_campo'=>$array[$i]['img_campo'],'superficie'=> $array[$i]['superficie'],'totale_rame'=> $array[$i]['rame_metallo_usato_su_campo']); }
	  else {
		  if ($array[$i]['campo_coltivazione']==$array[$i-1]['campo_coltivazione']){$campi[$c]['totale_rame']=$campi[$c]['totale_rame']+$array[$i]['rame_metallo_usato_su_campo'];}
			
			else {$c=$c+1; $campi[$c]=array('campo_coltivazione'=>$array[$i]['campo_coltivazione'],'descri_campo'=>$array[$i]['descri_campo'],'img_campo'=>$array[$i]['img_campo'],'superficie'=> $array[$i]['superficie'],'totale_rame'=> $array[$i]['rame_metallo_usato_su_campo']);}
			 }
	}
	  

 // inizio creazione PDF
$pdf = new Report_template('L','mm','A4',true,'UTF-8',false,true);
$pdf->setVars($admin_aziend,$title);
$pdf->SetTopMargin(42);
$pdf->SetFooterMargin(20);
$config = new Config;
$pdf->AddPage('L',$config->getValue('page_format'));
$pdf->SetFont('helvetica','',9);
  
	for ($i=0; $i<$c+1; $i++) {
		$pdf->Cell(10,6,$campi[$i]['campo_coltivazione'],1);	  
		$pdf->Cell(55,6,$campi[$i]['descri_campo'],1);		
		$pdf->Cell(20,6,gaz_format_quantity($campi[$i]['superficie'],1,$admin_aziend['decimal_quantity']),1);	
		$pdf->Cell(30,6,gaz_format_quantity($campi[$i]['totale_rame'],1,$admin_aziend['decimal_quantity']),1);
		$rame_ammesso = $campi[$i]['superficie']*6;
		$pdf->Cell(35,6,gaz_format_quantity($rame_ammesso,1,$admin_aziend['decimal_quantity'])." limite annuo",1);
		$pdf->Cell(10,6,"Kg",1);
		if (strlen($campi[$i]['img_campo'])>0){	
			$pdf->Image('@'.$campi[$i]['img_campo'], $x='', $y='', $w=80, $h=0, $type='', $link='', $align='', $resize=true, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
		}
		if ($i<$c) {
			$pdf->AddPage(); 
		}
    
	}
}
$pdf->Output();
?>