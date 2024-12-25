<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-present - Antonio De Vincentiis Montesilvano (PE)
         (https://www.devincentiis.it)
           <https://gazie.sourceforge.net>
 --------------------------------------------------------------------------
    Questo programma e` free software;   e` lecito redistribuirlo  e/o
    modificarlo secondo i  termini della Licenza Pubblica Generica GNU
    come e` pubblicata dalla Free Software Foundation; o la versione 2
    della licenza o (a propria scelta) una versione successiva.

    Questo programma  e` distribuito nella speranza  che sia utile, ma
    SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
    NEGOZIABILITA` o di  APPLICABILITA` PER UN  PWorkerLARE SCOPO.  Si
    veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

    Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
    Generica GNU insieme a   questo programma; in caso  contrario,  si
    scriva   alla   Free  Software Foundation, 51 Franklin Street,
    Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
use tecnickcom\tcpdf\tcpdf;
use setasign\Fpdi\Tcpdf\Fpdi;
require("../../library/include/datlib.inc.php");
$gTables['calendar'] = $table_prefix . "_calendar";
$admin_aziend=checkAdmin();
$begin = new DateTime('2025-01-01');
$end = new DateTime('2026-01-01');
$easter_date=date('md',easter_date('2025'));
$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);
$gazTimeFormatter->setPattern('d eee');
$start_easter=false;
$count_easter=0;

class calPdf extends Fpdi {

  private $azienda = [];

  public function setVars($aziend) {
    $this->azienda = $aziend;
  }

  public function Header() {
    $this->SetFillColor(hexdec(substr($this->azienda['colore'], 0, 2)), hexdec(substr($this->azienda['colore'], 2, 2)), hexdec(substr($this->azienda['colore'], 4, 2)));
    $im = imagecreatefromstring ($this->azienda['image']);
    $ratio = round(imagesx($im)/imagesy($im),2);
    $x=42;$y=0;
    if ($ratio<1.55){ $x=0; $y=27; }
    $imglink = !empty($this->azienda['web_url']) ? $this->azienda['web_url'] : '../config/admin_aziend.php';
    $this->SetFont('helvetica', '', 10);
    $this->Cell(211,30,'',0,0,0,1);
    $this->SetFont('times','B',42);
    $this->SetX(35);
    $this->Cell(116,20,'GENNAIO',0,2,'C');
    $this->SetFont('times','B',24);
    $this->Cell(116,10,'2025',0,2,'C');
    $this->SetFont('helvetica', '', 10);
    $this->SetXY(150,4);
    $this->Cell(57,5,$this->azienda['ragso1'].' '.$this->azienda['ragso2'],0,2,0,1,'',1);
    $this->Cell(57,5,$this->azienda['indspe'],0,2,0,1,'',1);
    $this->Cell(57,5,$this->azienda['citspe'].' ('.$this->azienda['prospe'].')',0,2,0,1,'',1);
    $this->Cell(57,5,'Tel: '.$this->azienda['telefo'].' - '.$this->azienda['e_mail'],0,0,0,1,'',1);
    $this->Image('@'.$this->azienda['image'],15,1,$x,$y,'',$imglink);
  }

  public function Footer() {
  }
}

$pdf = new calPdf();
$pdf->setVars($admin_aziend);
$pdf->SetMargins(0,5,3);
$pdf->SetFooterMargin(5);
$pdf->SetCreator('GAzie - ' . $admin_aziend['ragso1'] . ' ' . $admin_aziend['ragso2']);
$pdf->SetFont('helvetica','',7);
$ctrl_month=0;
foreach($period as $dt) {
  $m=$dt->format('n');
  if ($m>$ctrl_month) {
    $pdf->AddPage();
  }
  $dbdaycal=gaz_dbi_get_row($gTables['calendar'],'day',$dt->format('j')," AND month = ".$m);
  if ($dt->format('md') == $easter_date) $start_easter=true;
  if ($start_easter) {
    $count_easter++;
    $dbdaycal['holiday']=1;
    if ($count_easter>=2) $start_easter=false;
  }
 // echo '<p>'.$gazTimeFormatter->format($dt).' count:'.$count_easter.'</p>';
 // var_dump($dbdaycal);
 $ctrl_month=$m;
}
$pdf->Output();
?>
