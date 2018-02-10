<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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

class InformativaPrivacy extends Template
{
    function setTesDoc()
    {
        $this->tesdoc = $this->docVars->tesdoc;
        $this->intesta1 = $this->docVars->intesta1;
        $this->intesta1bis = $this->docVars->intesta1bis;
        $this->intesta2 = $this->docVars->intesta2;
        $this->intesta3 = $this->docVars->intesta3;
        $this->intesta4 = $this->docVars->intesta4;
        $this->colore = $this->docVars->colore;
        $this->tipdoc = 'PRIVACIDAD DEL TRATAMIENTO DE DATOS PERSONALES';
        $this->cliente1 = $this->docVars->cliente1;
        $this->cliente2 = $this->docVars->cliente2;
        $this->cliente3 = $this->docVars->cliente3;
        $this->cliente4 = $this->docVars->cliente4;
        $this->cliente5 = $this->docVars->cliente5;
        $this->cliente6 = $this->docVars->client['sexper'];
        if ($this->docVars->intesta5 == 'F'){
           $this->descriAzienda = 'la sottoscritta';
        } elseif ($this->docVars->intesta5 == 'M'){
           $this->descriAzienda = 'il sottoscritto';
        } else {
           $this->descriAzienda = 'la sociedad';
        }
        $this->giorno = substr($this->tesdoc['datemi'],8,2);
        $this->mese = substr($this->tesdoc['datemi'],5,2);
        $this->anno = substr($this->tesdoc['datemi'],0,4);
        $this->nomemese = ucwords(strftime("%B", mktime (0,0,0,substr($this->tesdoc['datemi'],5,2),1,0)));
    }

    function newPage() {
        $this->AddPage();
        $this->SetFont('helvetica','',11);
    }

    function pageHeader() {
        $this->StartPageGroup();
        $this->SetFillColor(hexdec(substr($this->colore,0,2)),hexdec(substr($this->colore,2,2)),hexdec(substr($this->colore,4,2)));
        $this->newPage();
    }


    function compose()
    {
        $this->setTesDoc();
        $this->body();
    }

    function body()
    {
    $testo =
    ucfirst($this->descriAzienda)." ".$this->intesta1." ".$this->intesta1bis." persona que posee el procesamiento de datos, por este medio informar a la respetable.el $this->cliente1 $this->cliente2 la existencia de una colección de datos personales.
1) Los datos se procesan a mano o computadora. La base de datos esta organizada de manera que el acceso y edición de datos de la misma solo se permitira al titular de la base de datos y el personal de formacion especifica de los datos.
2) La finalidad de la recogida de datos están vinculados a:
 a) las obligaciones impuestas por la ley en materia tributaria;
 b) el envío de un banco de datos de facturacion relativos a la percepcion por cualquier banco;
 c) la formación de una dirección para el envío de cualquier comunicacion
3) Como parte de los tratamientos descritos requiere conocimiento y almacenar la información sobre personal y fiscal, y cualquier otro dato necesario para realizar sólo las obligaciones impuestas por las cuestiones fiscales y administrativas.
4) obligación de E 'para el tratamiento de los datos a revelar a los interesados que cualquier no-divulgación de información catalogada como obligatoria tiene como consecuencia la incapacidad del titular para cumplir con las obligaciones impuestas por la autoridad fiscal y administrativa a la que va dirigida.
5) De conformidad con el Decreto n º decreto legislativo 196/2003, el titular de que la información, en su totalidad o en parte, según proceda y dentro de los límites de la necesidad, para comunicar mejor a nuestro asesor de impuestos para la preparación de las cuentas y cumplir con sus obligaciones en virtud de la normativa fiscales y administrativas. Esta comunicación va a pasar con garantía de protección de los derechos de los y prohibición de su difusión o publicación sin la autorización expresa a este respecto.
6)El Estimado $this->cliente1 $this->cliente2 puedan hacer valer sus derechos de acuerdo con el art. 7 del Decreto. El decreto legislativo n. 196/2003, seguido por ponerse en contacto con el propietario del tratamiento: $this->intesta1 $this->intesta1bis $this->intesta2 $this->intesta3 $this->intesta4

";
     $diritti =
"1. Usted tiene el derecho a obtener la confirmación de si los datos personales que le conciernen, aunque aún no registrados, y su comunicación en forma inteligible.
2. Usted tiene el derecho a obtener información:
 a) el origen de los datos personales;
 b) los fines y métodos de tratamiento;
 c) la lógica aplicada en caso de tratamiento con la ayuda de medios electrónicos;
 d) la identidad del propietario y del representante designado en virtud del artículo 5, párrafo 2;
 e) los sujetos o categorías de personas a las que los datos puedan ser comunicados o que pueden conocerlos en calidad de representante designado en el Estado, los directivos o agentes.
3. Usted tiene el derecho a obtener:
 a) la actualización, rectificación o, en su interés, la integración de los datos;
 b) la cancelación, anónima o el bloqueo de los datos tratados violando la ley, incluidos aquellos cuya conservación no es necesaria para los fines para los que fueron recogidos o sucesivamente tratados;
 c) la certificación que las operaciones en las letras a) y b) han sido notificados, también en cuanto a su contenido, a aquellos a quienes los datos han sido comunicados o difundidos, a menos que este requisito imposible o suponga un manifiestamente desproporcionado con respecto al derecho protegido.
4. Usted tiene el derecho a oponerse, en todo o en parte:
 a) por motivos legítimos al tratamiento de datos personales, cuando sean pertinentes a la finalidad de la recogida;
 b) el tratamiento de datos personales con fines de envío de material publicitario o de venta directa o para llevar a cabo estudios de mercado o de comunicación comercial.";
    $this->Ln(4);
    $this->SetFont('helvetica','B',14);
    $this->Cell(184,6,'DECRETO LEGISLATIVO N. 196/2003 (Política de Privacidad)',0,1,'C');
    $this->SetFont('helvetica','',8);
    $this->MultiCell(184,4,$testo,0,'L');
    $this->SetFont('helvetica','B',10);
    $this->Cell(184,4,'ARTICULO 7 - Derecho de acceso a datos personales y otros derechos',0,1,'C');
    $this->SetFont('helvetica','',7);
    $this->MultiCell(184,3,$diritti,0,'L');
    $this->Ln(6);
    $this->Cell(120,6,"Firma de aceptación _________________________");
    }
    function pageFooter()
    {
    }
}
?>