<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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
require('template.php');

class Certificate extends Template
{
    function setTesDoc()
    {
        $this->tesdoc = $this->docVars->tesdoc;
        $this->giorno = substr($this->tesdoc['datemi'],8,2);
        $this->mese = substr($this->tesdoc['datemi'],5,2);
        $this->anno = substr($this->tesdoc['datemi'],0,4);
        $this->nomemese = ucwords(strftime("%B", mktime (0,0,0,substr($this->tesdoc['datemi'],5,2),1,0)));
        $descri='Certificati prodotti forniti con ';
        if ($this->tesdoc['tipdoc'] == 'FAD' || substr($this->tesdoc['tipdoc'],0,2) == 'DD') {
            $descri .= 'D.d.T. n.';
        } else {
            $descri .= 'Fattura n.';
        }
        $this->tipdoc = $descri.$this->tesdoc['numdoc'].'/'.$this->tesdoc['seziva'].' del '.$this->giorno.' '.$this->nomemese.' '.$this->anno;
        $this->destinazione = array('titolo','contenuto');   
        $this->noPageGroup = true;   
        }

    function newPage() {
        $this->AddPage();
        $this->SetFillColor(hexdec(substr($this->colore,0,2)),hexdec(substr($this->colore,2,2)),hexdec(substr($this->colore,4,2)));
        $this->SetFont('helvetica','',9);
        $this->Cell(30,6,'Codice',1,0,'L',1);
        $this->Cell(82,6,'Descrizione',1,0,'L',1);
        $this->Cell(10,6,'U.m.',1,0,'L',1);
        $this->Cell(30,6,'Quantità',1,0,'R',1);
        $this->Cell(25,6,'Prezzo',1,0,'R',1);
        $this->Cell(10,6,'%Sc.',1,1,'R',1);
    }

    function pageHeader()
    {
        $this->StartPageGroup();
        $this->newPage();
    }

    function compose()
    {
    }

    function pageFooter() {
    }

    function Footer()
    {
        //Page footer
        $this->SetY(-25);
        $this->Line(10,260,197,260);
        $this->Line(10,270,197,270);
        $this->SetFont('helvetica','',8);
        $this->MultiCell(186,4,$this->intesta1.' '.$this->intesta2.' '.$this->intesta3.' '.$this->intesta4.' ',0,'C',0);
    }
}
?>