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

require('../../library/tcpdf/tcpdf.php');

class Template extends TCPDF
{
    function setVars(&$docVars,$Template='')
    {
        $this->docVars =& $docVars;
        $this->gaz_path = '../../';
        $this->rigbro = $docVars->gTables['rigbro'];
        $this->aliiva = $docVars->gTables['aliiva'];
        $this->tesdoc = $docVars->tesdoc;
        $this->testat = $docVars->testat;
        $this->pagame = $docVars->pagame;
        $this->banapp = $docVars->banapp;
        $this->banacc = $docVars->banacc;
        $this->logo = $docVars->logo;
        $this->intesta1 = $docVars->intesta1;
        $this->intesta1bis = $docVars->intesta1bis;
        $this->intesta2 = $docVars->intesta2;
        $this->intesta3 = $docVars->intesta3.$docVars->intesta4;
        $this->intesta4 = $docVars->codici;
        $this->colore = $docVars->colore;
        $this->decimal_quantity = $docVars->decimal_quantity;
        $this->decimal_price = $docVars->decimal_price;
        $this->perbollo = $docVars->perbollo;
        $this->cliente1 = $docVars->cliente1;
        $this->cliente2 = $docVars->cliente2;
        $this->cliente3 = $docVars->cliente3;
        $this->cliente4 = $docVars->cliente4;  // CAP, Città, Provincia
        $this->cliente5 = $docVars->cliente5;  // P.IVA e C.F.
        $this->agente = $docVars->name_agente;
        $this->destinazione = $docVars->destinazione;
        $this->clientSedeLegale = '';
        if (!empty ($docVars->clientSedeLegale)) {
                     foreach($docVars->clientSedeLegale as $value) {
                         $this->clientSedeLegale .= $value.' ';
                     }
        }
        $this->c_Attenzione = $docVars->c_Attenzione;
        $this->min = $docVars->min;
        $this->ora = $docVars->ora;
        $this->day = $docVars->day;
        $this->month = $docVars->month;
        $this->year = $docVars->year;
        $this->tottraspo = 0.00;
    }

    function Header()
    {
       if (isset($this->appendix)) { // se viene passato l'appendice

       } else {
        $this->SetFillColor(hexdec(substr($this->colore,0,2)),hexdec(substr($this->colore,2,2)),hexdec(substr($this->colore,4,2)));
        $this->SetFont('times','B',14);
        $this->Cell(130,6,$this->intesta1,0,1,'L');
        $this->SetFont('helvetica','',8);
        $interlinea = 14;
        if (!empty($this->intesta1bis))  {
           $this->Cell(130,4,$this->intesta1bis,0,2,'L');
           $interlinea = 10;
        }
        $this->Cell(130,4,$this->intesta2,0,2,'L');
        $this->Cell(130,4,$this->intesta3,0,2,'L');
        $this->Cell(130,4,$this->intesta4,0,0,'L');
        $this->Image('@'.$this->logo,140,5,40,0,'Logo de la empresa');
        $this->Line(0,93,3,93); //questa marca la linea d'aiuto per la piegatura del documento
        $this->Line(0,143,3,143); //questa marca la linea d'aiuto per la foratura del documento
        $this->Ln($interlinea);
        $this->SetFont('helvetica','',11);
        $this->Cell(110,5,$this->tipdoc,1,1,'L',1);
        if ($this->tesdoc['tipdoc'] == 'NOP') {
           $this->Cell(30,5);
        } else {
           $this->Cell(30,5,'Pagina '.$this->getGroupPageNo().' de '.$this->getPageGroupAlias(),0,0,'L');
        }
        $this->Ln(6);
        $interlinea = $this->GetY();
        $this->Ln(6);
        $this->SetFont('helvetica','',9);
        if (!empty ($this->destinazione)) {
            if (is_array($this->destinazione)){ //quando si vuole indicare un titolo diverso da destinazione si deve passare un array con titolo index 0 e descrizione index 1
                $this->Cell(80,5,$this->destinazione[0],'LTR',2,'L',1);
                $this->MultiCell(80,4,$this->destinazione[1],'LBR','L');
            } else {
                $this->Cell(80,5,"Destino :",'LTR',2,'L',1);
                $this->MultiCell(80,4,$this->destinazione,'LBR','L');
            }
        }
        $this->SetFont('helvetica','',10);
        $this->SetXY(110,$interlinea+3);
        $this->Cell(15,5,'Estimados ',0,0,'R');
        $this->Cell(75,5,$this->cliente1,0,1,'L');
        if (!empty ($this->cliente2)) {
            $this->Cell(115);
            $this->Cell(75,5,$this->cliente2,0,1,'L');
        }
        $this->SetFont('helvetica','',10);
        $this->Cell(115);
        $this->Cell(75,5,$this->cliente3,0,1,'L');
        $this->Cell(115);
        $this->Cell(75,5,$this->cliente4,0,1,'L');
        $this->SetFont('helvetica','',7);
        $this->Cell(115);
        $this->Cell(75,5,$this->cliente5,0,1,'L');
        if (!empty ($this->c_Attenzione)) {
            $this->SetFont('helvetica','',10);
            $this->Cell(115,8,'a C.A.',0,0,'R');
            $this->Cell(75,8,$this->c_Attenzione,0,1);
        }
        $this->SetFont('helvetica','',7);
        if (!empty ($this->clientSedeLegale)) {
            $this->Cell(115,8,'Sede legal: ',0,0,'R');
            $this->Cell(75,8,$this->clientSedeLegale,0,1);
        } else {
            $this->Ln(4);
        }

       }
    }

}