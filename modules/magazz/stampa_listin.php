<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend=checkAdmin();
if ($admin_aziend['decimal_quantity']>4){
	$admin_aziend['decimal_quantity']=4;
}

if (!isset($_GET['li']) or
    !isset($_GET['ci']) or
    !isset($_GET['cf']) or
    !isset($_GET['ai']) or
    !isset($_GET['af'])) {
    header("Location: select_listin.php");
    exit;
}

if (empty ($_GET['af'])) {
    $_GET['af'] = 'zzzzzzzzzzzzzzz';
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

require("../../config/templates/report_template.php");
$what = $gTables['catmer'].".codice AS codcat , ".$gTables['catmer'].".descri AS descat , ".
        $gTables['artico'].".codice AS codart,".$gTables['artico'].".descri AS desart,".$gTables['artico'].".* , ".
        $gTables['aliiva'].".codice AS codiva, ".$gTables['aliiva'].".aliquo ";
$table = $gTables['artico']." LEFT JOIN ".$gTables['catmer']." ON (".$gTables['artico'].".catmer = ".$gTables['catmer'].".codice)
         LEFT JOIN ".$gTables['aliiva']." ON (".$gTables['artico'].".aliiva = ".$gTables['aliiva'].".codice)";
$where = "catmer BETWEEN '".intval($_GET['ci']).
         "' AND '".intval($_GET['cf']).
         "' AND ".$gTables['artico'].".codice BETWEEN '".substr($_GET['ai'],0,15).
         "' AND '".substr($_GET['af'],0,15)."'";
$result = gaz_dbi_dyn_query ($what, $table,$where,"catmer ASC,".$gTables['artico'].".codice ASC");

switch($_GET['li']) {
        case '0':
        $descrlis = 'd\'acquisto';
        break;
        case '1':
        $descrlis = 'di vendita n.1';
        break;
        case '2':
        $descrlis = 'di vendita n.2';
        break;
        case '3':
        $descrlis = 'di vendita n.3';
        break;
        case 'web':
        $descrlis = 'di vendita online';
        break;
}

$title=array('luogo_data'=>$luogo_data,
               'title'=>"Listino ".$descrlis,
               'hile'=>array(array('lun' => 35,'nam'=>'Codice'),
                             array('lun' => 85,'nam'=>'Descrizione'),
                             array('lun' => 15,'nam'=>'U.M.'),
                             array('lun' => 25,'nam'=>'Prezzo'),
                             array('lun' => 25,'nam'=>'Esistenza'),
                             array('lun' => 15,'nam'=>'% I.V.A.'),
                             array('lun' => 70,'nam'=>'Annotazioni')
                             )
            );
$gForm = new magazzForm();
$pdf = new Report_template();
$pdf->setVars($admin_aziend,$title,'L');
$pdf->setAuthor($admin_aziend['ragso1'].' '.$_SESSION['Login']);
$pdf->setTitle($title['title']);
$pdf->SetTopMargin(39);
$pdf->setFooterMargin(10);
$pdf->AddPage('L');
$pdf->SetFillColor(hexdec(substr($admin_aziend['colore'],0,2)),hexdec(substr($admin_aziend['colore'],2,2)),hexdec(substr($admin_aziend['colore'],4,2)));
$ctrlcatmer=0;
while ($row = gaz_dbi_fetch_array($result)) {
       $mv=$gForm->getStockValue(false,$row['codice']);
       $magval=array_pop($mv);
       $pdf->SetFont('helvetica','',10);
       switch($_GET['li']) {
        case '0':
        $price = $row['preacq'];
        break;
        case '1':
        $price = $row['preve1'];
        break;
        case '2':
        $price = $row['preve2'];
        break;
        case '3':
        $price = $row['preve3'];
        break;
        case 'web':
        $price = $row['web_price']*$row['web_multiplier'];
        $row['unimis'] = $row['web_mu'];
        break;
      }
      if ($row["catmer"] <> $ctrlcatmer) {
        gaz_set_time_limit (30);
        $pdf->Cell(120,5,'Categoria Merceologica n.'.$row['codcat'].' = '.$row['descat'],1,1,'L',1);
      }
      $pdf->Cell(35,5,$row['codart'],1);
      $pdf->Cell(85,5,$row['desart'],1);
      $pdf->Cell(15,5,$row['unimis'],1,0,'C');
      $pdf->Cell(25,5,number_format($price,$admin_aziend['decimal_price'],',','.'),1,0,'R');
      $pdf->Cell(25,5,number_format($magval['q_g'],$admin_aziend['decimal_quantity'],',','.'),1,0,'R');
      $pdf->Cell(15,5,$row['aliquo'],1,0,'C');
      $pdf->Cell(70,5,$row['annota'],1,1,'C');
      $ctrlcatmer=$row["catmer"];
}
$pdf->Output();
?>