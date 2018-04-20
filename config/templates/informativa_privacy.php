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
        $this->tipdoc = 'INFORMATIVA PER IL TRATTAMENTO DEI DATI PERSONALI';
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
           $this->descriAzienda = 'la società';
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
    $testo ="<p>".ucfirst($this->descriAzienda)." <b>".$this->intesta1." ".$this->intesta1bis."</b> ".$this->intesta2." ".$this->intesta3." email: ".$this->intesta4." (in seguito <b>“Titolare”</b>),in qualità di titolare del trattamento, La informa ai sensi dell’art. 13 D.Lgs. 30.6.2003 n. 196 (in seguito, <b>“Codice Privacy”</b>) e dell’art. 13 Regolamento UE n. 2016/679 (in seguito, <b>“GDPR”</b>) che i Suoi dati saranno trattati con le modalità e per le finalità seguenti:</p>
<ol><li><h4><b>Oggetto del Trattamento</b></h4></li>
a) agli obblighi previsti dalla normativa vigente in materia fiscale;<br>
b) all'invio ad un istituto di credito dei dati relativi alla fatturazione per la eventuale relativa riscossione a mezzo banca;
 c) alla formazione di un indirizzario per l'invio di eventuali comunicazioni
3)Nell'ambito dei trattamenti descritti è necessaria la conoscenza e la memorizzazione di informazioni relative ai dati anagrafici e fiscali, e ad ogni altro dato necessario esclusivamente all'espletamento degli obblighi imposti dalla normativa fiscale e amministrativa.
4)E' obbligo del titolare del trattamento rendere noto agli interessati che l'eventuale non comunicazione di una informazione indicata come obbligatoria ha come conseguenze emergenti l'impossibilità del titolare di adempiere agli obblighi imposti dalla normativa fiscale e amministrativa cui esso sia indirizzato.
5)Ai sensi del Decreto Leg.vo n. 196/2003, il titolare informa che i dati possono in tutto o in parte, ove necessario e nei limiti della necessità stessa, essere comunicati al nostro consulente fiscale, per l'elaborazione della contabilità e per l'adempimento degli altri obblighi previsti dalla normativa fiscale e amministrativa. Tale comunicazione avverrà comunque con garanzia di tutela dei diritti dell'interessato e con divieto di ulteriore comunicazione o diffusione senza esplicita autorizzazione in proposito.
6)La Spett.le ".$this->cliente1 .$this->cliente2.
" può far valere i propri diritti come espressi dall'art. 7 del Decr. Leg.vo n. 196/2003, riportato di seguito, rivolgendosi al titolare del trattamento: ".
$this->intesta1 . $this->intesta1bis . $this->intesta2 .$this->intesta3 ." email: ". $this->intesta4;
     $diritti =
"1. L'interessato ha diritto di ottenere la conferma dell'esistenza o meno di dati personali che lo riguardano, anche se non ancora registrati, e la loro comunicazione in forma intelligibile.
2. L'interessato ha diritto di ottenere l'indicazione:
 a)dell'origine dei dati personali;
 b)delle finalità e modalità del trattamento;
 c)della logica applicata in caso di trattamento effettuato con l'ausilio di strumenti elettronici;
 d)degli estremi identificativi del titolare, dei responsabili e del rappresentante designato ai sensi dell'articolo 5, comma 2;
 e)dei soggetti o delle categorie di soggetti ai quali i dati personali possono essere comunicati o che possono venirne a conoscenza in qualità di rappresentante designato nel territorio dello Stato, di responsabili o incaricati.
3. L'interessato ha diritto di ottenere:
 a)l'aggiornamento, la rettificazione ovvero, quando vi ha interesse, l'integrazione dei dati;
 b)la cancellazione, la trasformazione in forma anonima o il blocco dei dati trattati in violazione di legge, compresi quelli di cui non è necessaria la conservazione in relazione agli scopi per i quali i dati sono stati raccolti o successivamente trattati;
 c)l'attestazione che le operazioni di cui alle lettere a) e b) sono state portate a conoscenza, anche per quanto riguarda il loro contenuto, di coloro ai quali i dati sono stati comunicati o diffusi, eccettuato il caso in cui tale adempimento si rivela impossibile o comporta un impiego di mezzi manifestamente sproporzionato rispetto al diritto tutelato.
4. L'interessato ha diritto di opporsi, in tutto o in parte:
 a)per motivi legittimi al trattamento dei dati personali che lo riguardano, ancorchè pertinenti allo scopo della raccolta;
 b)al trattamento di dati personali che lo riguardano a fini di invio di materiale pubblicitario o di vendita diretta o per il compimento di ricerche di mercato o di comunicazione commerciale.</ol>";
    $this->SetFont('courier','',8);
    $this->WriteHTMLCell(184,4,10,70,$testo, 0, 0, 0, true, 'J');
    }
    function pageFooter()
    {
    }
}
?>