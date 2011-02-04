<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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
require('../../library/tcpdf/tcpdf.php');
$aziend = gaz_dbi_get_row($gTables['aziend'],"codice",$admin_aziend['enterprise_id']);

if ($aziend['ivam_t']== 'M') {
    $mesetrim='Mese';
} else {
    $mesetrim='Trimestre';
}
$intesta1=$aziend['ragso1'].$aziend['ragso2'];
$intesta2=$aziend['indspe'];
$intesta3=sprintf("%05d",$aziend['capspe']).' '.$aziend['citspe'].' ('.$aziend['prospe'].')';
$intesta4='P.I. '.$aziend['pariva'];
$intesta5=$aziend['luonas'].' '.substr($aziend['datnas'],8,2).'-'.substr($aziend['datnas'],5,2).'-'.substr($aziend['datnas'],0,4);
$logo=$aziend['image'];

class PDF extends TCPDF
{
    function Header()
    {
        global $intesta1,$intesta2,$intesta3,$intesta4,$intesta5,$mesetrim,$logo;
        $this->SetFont('freesans','B',10);
        $this->MemImage($logo,10,8,20,0,'Logo aziendale');
        $this->Cell(25);
        $this->Cell(103,6,$intesta1,0,0,'L');
        $this->Cell(62,6,'ACQUISTI DI CARBURANTE','LTR',1,'C',1);
        $this->SetFont('freesans','',8);
        $this->Cell(25);
        $this->Cell(103,6,$intesta2,0,0,'L');
        $this->SetFont('freesans','B',10);
        $this->Cell(62,6,'PER AUTOTRAZIONE','LBR',1,'C',1);
        $this->SetFont('freesans','',10);
        $this->Cell(25);
        $this->Cell(103,4,$intesta3,0,0,'L');
        $this->SetFont('freesans','',10);
        $this->Cell(31,4,'Scheda n.','LR',0,'L');
        $this->Cell(31,4,'Registrata il','R',1,'L');
        $this->SetFont('freesans','',10);
        $this->Cell(25);
        $this->Cell(103,4,$intesta4,0,0,'L');
        $this->Cell(31,6,'','LBR',0);
        $this->Cell(31,6,'','BR',1);
        $this->SetFont('freesans','',10);
        $this->Cell(104,4,'Targa o telaio del veicolo','LTR',0,'L');
        $this->Cell(62,4,$mesetrim,'TR',0,'L');
        $this->Cell(24,4,'Anno','R',1,'L');
        $this->SetFont('freesans','',10);
        $this->Cell(104,7,'','LB',0);
        $this->Cell(62,7,'','LBR',0);
        $this->Cell(6,7,'','BR',0);
        $this->Cell(6,7,'','BR',0);
        $this->Cell(6,7,'','BR',0);
        $this->Cell(6,7,'','BR',1);
        $this->SetFont('freesans','',10);
        $this->Cell(110,4,'Intestatario del veicolo','LTR',0,'L');
        $this->Cell(80,4,'Luogo e data di nascita','R',1,'L');
        $this->SetFont('freesans','',10);
        $this->Cell(110,7,'','LB',0);
        $this->Cell(80,7,$intesta5,'LBR',1,'C');
        $this->Ln(5);
        $this->SetFont('freesans','',10);
        $this->Cell(10,4,'N.',1,0,'L',1);
        $this->Cell(23,4,'Data',1,0,'L',1);
        $this->Cell(20,4,'Tipo(*)',1,0,'L',1);
        $this->Cell(20,4,'Quant.',1,0,'L',1);
        $this->Cell(20,4,'Prezzo',1,0,'L',1);
        $this->Cell(40,4,'Firma',1,0,'L',1);
        $this->Cell(57,4,'Timbro-dati anagrafici e fiscali',1,1,'L',1);
    }

    function Footer()
    {
        global $mesetrim;
        //Page footer
        $this->SetY(-30);
        $this->SetFont('freesans','',10);
        $this->Cell(35,4,'Imponibile','LTR',0,'L');
        $this->Cell(10,4,'IVA','T',0,'L');
        $this->Cell(10,4,'____',0,0,'L');
        $this->Cell(10,4,'%','T',0,'L');
        $this->Cell(35,4,'Totale','LTR',0,'L',1);
        $this->Cell(42,4,'Km/Ore Iniziali','TR',0,'L');
        $this->Cell(48,4,'Km/Ore Fine '.$mesetrim,'TR',1,'L');
        $this->Cell(35,10,'','LBR',0,'L');
        $this->Cell(30,10,'','B',0,'L');
        $this->Cell(35,10,'','LBR',0,'L',1);
        $this->Cell(42,10,'','BR',0,'L');
        $this->Cell(48,10,'','BR',1,'L');
        $this->Cell(190,6,'(*)BSP=benzina senza piombo - BS=benzina super - G=gasolio - GPL=gas liquido - M=metano MX=miscela',0,0,'C');
    }
}

$pdf=new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetFillColor(hexdec(substr($aziend['colore'],0,2)),hexdec(substr($aziend['colore'],2,2)),hexdec(substr($aziend['colore'],4,2)));
$pdf->SetTopMargin(65);
$pdf->SetHeaderMargin(5);
$pdf->AddPage();
//Introdurre qui la procedura
for ($i = 1; $i < 12; $i++) {
    $pdf->SetFont('freesans','B',10);
    $pdf->Cell(10,13,$i,1,0);
    $pdf->Cell(23,13,'',1,0);
    $pdf->Cell(20,13,'',1,0);
    $pdf->Cell(20,13,'',1,0);
    $pdf->Cell(20,13,'',1,0);
    $pdf->Cell(40,13,'',1,0);
    $pdf->Cell(57,13,'',1,1);
    $pdf->Ln(5);
}
$pdf->Output();
?>