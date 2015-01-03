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

require('template.php');

class Template_con_scheda extends Template
{
    public function SchedaTrasporto()
    {
        $this->SetXY(10,10);
        $this->SetFont('times','B',14);
        $this->Cell(184,6,'JUNTA DE TRANSPORTE',0,1,'C');
        $this->SetFont('times','I',8);
        $this->Cell(184,4,'(a rellenar por el cliente y mantener la compañía en el vehículo: el articulo. 7-bis del decreto-ley no. 286/2005)',0,1,'C');

        $this->SetFont('times','B',11);
        $this->Cell(46,8,'A- DATOS DEL TRANPORTISTA');
        $this->SetFont('times','I',8);
        $this->Cell(138,8,'(art. 2, parrafo 1, let. b - D.Lgs. 286/2005)',0,1);
        $this->SetFont('times','',8);
        $this->Cell(62,4,'Título (1) Nombre de la empresa','LT');
        $this->Cell(122,4,$this->docVars->vettor['ragione_sociale'],'LTR',1);
        $this->Cell(62,4,'Dirección y Sede Empresa','L');
        $this->Cell(122,4,$this->docVars->vettor['indirizzo'].' '.$this->docVars->vettor['cap'].' '.$this->docVars->vettor['citta'].' ('.$this->docVars->vettor['provincia'].')','LR',1);
        $this->Cell(62,4,'(números de teléfonos o e-mail)','L');
        $this->Cell(122,4,$this->docVars->vettor['telefo'],'LR',1);
        $this->Cell(62,4,'Partida IVA',1);
        $this->Cell(122,4,$this->docVars->vettor['partita_iva'],1,1);
        $this->Cell(62,4,'Número de Registro de los conductores',1);
        $this->Cell(122,4,$this->docVars->vettor['n_albo'],1,1);

        $this->SetFont('times','B',11);
        $this->Cell(62,8,'B - DATOS DEL CONTRATISTA');
        $this->SetFont('times','I',8);
        $this->Cell(122,8,'(art. 2, parrafo 1, let. c - D.Lgs. 286/2005)',0,1);
        $this->SetFont('times','',8);
        $this->Cell(62,4,'Título (1) Nombre de la empresa','LT');
        $this->Cell(122,4,$this->intesta1.' '.$this->intesta1bis,'LTR',1);
        $this->Cell(62,4,'Dirección y Sede Empresa','L');
        $this->Cell(122,4,$this->intesta2,'LR',1);
        $this->Cell(62,4,'(riferencia telefonica o email)','L');
        $this->Cell(122,4,$this->intesta3,'LR',1);
        $this->Cell(62,4,'Partida IVA / Codigo Fiscal',1);
        $this->Cell(122,4,$this->intesta4,1,1);

        $this->SetFont('times','B',11);
        $this->Cell(60,8,'C - DATOS DEL CARGADOR');
        $this->SetFont('times','I',8);
        $this->Cell(124,8,'(art. 2, parrafo 1, let. d - D.Lgs. 286/2005)',0,1);
        $this->SetFont('times','',8);
        $this->Cell(62,4,'Título (1) Nombre de la empresa','LT');
        $this->Cell(122,4,$this->intesta1.' '.$this->intesta1bis,'LTR',1);
        $this->Cell(62,4,'Dirección y Sede Empresa','L');
        $this->Cell(122,4,$this->intesta2,'LR',1);
        $this->Cell(62,4,'(riferencia telefonica o email)','L');
        $this->Cell(122,4,$this->intesta3,'LR',1);
        $this->Cell(62,4,'Partida IVA / Codigo Fiscal',1);
        $this->Cell(122,4,$this->intesta4,1,1);

        $this->SetFont('times','B',11);
        $this->Cell(86,8,'D - DATOS DEL PROPRIETARIO DE LA MERCADERIA');
        $this->SetFont('times','I',8);
        $this->Cell(98,8,'(art. 2, parrafo 1, let. e - D.Lgs. 286/2005)',0,1);
        $this->SetFont('times','',8);
        $this->Cell(62,4,'Título (1) Nombre de la empresa','LT');
        $this->Cell(122,4,$this->cliente1.' '.$this->cliente2,'LTR',1);
        $this->Cell(62,4,'Dirección y Sede Empresa','L');
        if (!empty($this->clientSedeLegale)) {
          $this->Cell(122,4,$this->clientSedeLegale,'LR',1);
        } else {
          $this->Cell(122,4,$this->cliente3.' '.$this->cliente4,'LR',1);
        }
        $this->Cell(62,4,'(riferencia telefonica o email)','L');
        $this->Cell(122,4,$this->docVars->client['telefo'].' '.$this->docVars->client['e_mail'],'LR',1);
        $this->Cell(62,4,'Partida IVA / Codigo Fiscal',1);
        $this->Cell(122,4,$this->cliente5,1,1);

        $this->Ln(4);
        $this->SetFont('times','B',10);
        $this->Cell(184,8,'Eventuales declaraciones (2)','LTR',1);
        $this->Cell(184,14,'','LBR',1);

        $this->Ln(4);
        $this->Cell(62,5,'E - DATOS MERCANCIA TRANSPORTADA',1,1);
        $this->SetFont('times','',8);
        $this->Cell(62,4,'Tipo','LT');
        $this->Cell(122,4,'Ver '.$this->tipdoc,'LTR',1);
        $this->Cell(62,4,'Cantidad / Peso','LT');
        $this->Cell(122,4,'Ver '.$this->tipdoc,'LTR',1);
        $this->Cell(62,4,'Lugar de carga de mercancias',1);
        $this->Cell(122,4,$this->intesta2,1,1);
        $this->Cell(62,4,'Lugar de descarga',1);
        if (!empty($this->destinazione)) {
           $this->Cell(122,4,$this->destinazione,'LBR',1);
        } else {
           $this->Cell(122,4,$this->cliente3.' '.$this->cliente4,1,1);
        }
        $this->Ln(5);
        $this->SetFont('times','B',10);
        $this->Cell(184,8,'Observaciones varias (3)','LTR',1);
        $this->Cell(184,10,'','LBR',1);

        $this->Ln(4);
        $this->Cell(184,8,'Eventuales instrucciones (4)','LTR',1);
        $this->Cell(184,10,'','LBR',1);

        $this->Ln(4);
        $this->Cell(52,5,'Lugar y fecha compilación',1,1);
        $this->SetFont('times','',8);
        $this->Cell(52,4,'Lugar y fecha',1);
        $this->Cell(66,4,'Datos del compilador (5)',1);
        $this->Cell(66,4,'Firma del compilador',1,1);
        $this->Cell(52,12,$this->docVars->azienda['citspe'].', '.$this->giorno.'.'.$this->mese.'.'.$this->anno,1);
        $this->Cell(66,12,$this->docVars->user['Nome'].' '.$this->docVars->user['Cognome'],1);
        $this->Cell(66,12,'',1,1);

        $this->Cell(67,3,'-----------------------------------------------------------',0,1);
        $this->SetFont('times','',7);
        $this->Cell(184,3,"(1) Usar el nombre de las empresas de capital, a nombre de las empresas y las personas de la empresa para empresarios individuales",0,1);
        $this->Cell(184,3,"(2) Para ser completado en los casos en los que no se puede indicar la figura del dueño",0,1);
        $this->Cell(184,3,"(3) Para ser completado por el transportista o su conductor cuando hay variaciones en las indicaciones originales de esta ",0,1);
        $this->Cell(184,3,"    documento (por ejemplo, cambio en la inclinación, cambiar el tipo y cantidad de los bienes, ...)",0,1);
        $this->Cell(184,3,"(4) Informe de las instrucciones suministradas por el promotor o uno de los actores de la cadena de transporte de la compañía",0,1);
        $this->Cell(184,3,"(5) Dar detalles de la persona que firma la carta en nombre y por cuenta del empresario",0,1);
    }
}