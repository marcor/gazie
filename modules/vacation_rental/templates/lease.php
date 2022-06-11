<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
require('booking_template.php');

class Lease extends Template
{
    function setTesDoc()
    {
        $this->tesdoc = $this->docVars->tesdoc;
        $this->giorno = substr($this->tesdoc['datemi'],8,2);
        $this->mese = substr($this->tesdoc['datemi'],5,2);
        $this->anno = substr($this->tesdoc['datemi'],0,4);
        $this->docVars->gazTimeFormatter->setPattern('MMMM');
        $this->nomemese = ucwords($this->docVars->gazTimeFormatter->format(new DateTime($this->tesdoc['datemi'])));
        $this->sconto = $this->tesdoc['sconto'];
        $this->trasporto = $this->tesdoc['traspo'];
        $this->tipdoc = 'Contratto n.'.$this->tesdoc['numdoc'].'/'.$this->tesdoc['seziva'].' del '.$this->giorno.' '.$this->nomemese.' '.$this->anno;
        $this->show_artico_composit = $this->docVars->show_artico_composit;
    }
    function newPage() {
        $this->AddPage();
        $this->SetFillColor(hexdec(substr($this->colore,0,2)),hexdec(substr($this->colore,2,2)),hexdec(substr($this->colore,4,2)));
        $this->SetFont('helvetica','',9);

    }

    function pageHeader()
    {
        $this->setTesDoc();
        $this->StartPageGroup();
        $this->newPage();
    }
    function body()
    {
      $admin_aziend = checkAdmin();
      require("./lang." . $admin_aziend['lang'] . ".php");
      $script_transl = $strScript["lease.php"];

      $lines = $this->docVars->getRigo();

      // create some HTML content
      $html = "<h1>".$script_transl['contratto_n'].$this->tesdoc['numdoc']."</h1><p>".$script_transl['parti']."<br>".$script_transl['locatore'].": ".$this->intesta1.$this->intesta2.$this->intesta3."<br>"
      .$script_transl['e'].$script_transl['conduttore'].": ".$this->cliente1." ".$this->cliente2." ".$this->cliente3." ".$this->cliente4." "."<br>".$script_transl['body1']."</p>
      <p>1- <b>".$script_transl['oggetto']."</b><br>".$script_transl['body2']."</p>";
      $html .= "<ul>";
      foreach ($lines as $rigo){
        //echo "<br><pre>",print_r($rigo);
        if (($custom = json_decode($rigo['custom_field'],true)) && (json_last_error() == JSON_ERROR_NONE)){
          if (array_key_exists('accommodation_type', $custom['vacation_rental'])) {// è un alloggio
              switch ($custom['vacation_rental']['accommodation_type']) {//3 => 'Appartamento', 4 => 'Casa indipendente', 5=> 'Bed & breakfast'
                case "3":
                  $accomodation_type=$script_transl['apartment'];
                  break;
                case "4":
                  $accomodation_type=$script_transl['house'];
                  break;
                case "5":
                  $accomodation_type=$script_transl['bandb'];
                  break;
              }
              $html .= "<li>".$accomodation_type." denominato ".$rigo['desart'].", ".$rigo['annota'];
              if (strlen($rigo['web_url'])>5){
                $html .= "<br>".$script_transl['body3'].": ".$rigo['web_url'].". ".$script_transl['body4'];
              }
              $html .= "</li>";
              $adult=$rigo['adult'];
              $child=$rigo['child'];
              $start=$rigo['start'];
              $end=$rigo['end'];
          }
          if (array_key_exists('extra', $custom['vacation_rental'])) { // è un extra
              $html .= "<li>".intval($rigo['quanti'])."Extra ".$rigo['desart']." ".$rigo['annota'];
              if (strlen($rigo['web_url'])>5){
                $html .= "<br>   ".$script_transl['body3'].": ".$rigo['web_url'].".   ".$script_transl['body4'];
              }
              $html .= "</li>";
          }
        } elseif($rigo['codart']=="TASSA-TURISTICA"){ // è la tassa turistica
          $html .= "<li>".$rigo['descri']."</li>";
        }
      }

      $diff=date_diff(date_create($start),date_create($end));
      $nights = $diff->format("%a");

      $this->docVars->setTotal($this->tesdoc['traspo']);
      $totimpfat = $this->docVars->totimpfat;
      $totivafat = $this->docVars->totivafat;
      $impbol = $this->docVars->impbol;
      $taxstamp=$this->docVars->taxstamp;

      $html .= "</ul>";

      $html .= "<dl>";

      $html .= "<dt>2- <b>".$script_transl['durata']."</b></dt>" ;
      $html .= "<dd>- ".$script_transl['durata1'].$nights."</dd><dd>- ".$script_transl['durata2'].date("d-m-Y", strtotime($start))."</dd>
                <dd>- ".$script_transl['durata3'].date("d-m-Y", strtotime($end)).$script_transl['durata4']."</dd>
                <dd>- ".$script_transl['durata5']."</dd>";

      $html .= "<dt>3- <b>".$script_transl['canone']."</b></dt>" ;
      $html .= "<dd>- ".$script_transl['body5'].(intval($adult)+intval($child)).$script_transl['body6'].$adult.$script_transl['body7'].$child.$script_transl['body8']."</dd>";

      $html .= "<dd>- ".$script_transl['canone1']." € ".number_format(($totimpfat + $totivafat + $impbol+$taxstamp),2,",",".").$script_transl['canone2']."</dd>".
               "<dd>- ".$script_transl['canone3']." dep cauz . ".$script_transl['canone4']."</dd>";

      $html .= "<dt>4- <b>".$script_transl['divieti']."</b></dt>";
      $html .= "<dd>- ".$script_transl['divieto1']."</dd>"."<dd>- ".$script_transl['divieto2']."</dd>"."<dd>- ".$script_transl['divieto3']."</dd>"."<dd>- ".$script_transl['divieto5']."</dd>"."<dd>- ".$script_transl['divieto7']."</dd>"."<dd>- ".$script_transl['divieto4']."</dd>"."<dd>- ".$script_transl['divieto6']."</dd>";

      $html .= "<dt>5- <b>".$script_transl['recesso']."</b></dt>" ;
      $html .= "<dd>- ".$script_transl['recesso1']."</dd>"."<dd>- ".$script_transl['recesso2']."</dd>"."<dd>- ".$script_transl['recesso3']."</dd>"."<dd>- ".$script_transl['recesso4']."</dd>"."<dd>- ".$script_transl['recesso5']."</dd>"."<dd>- ".$script_transl['recesso6']."</dd>"."<dd>- ".$script_transl['recesso7']."</dd>"."<dd>- ".$script_transl['recesso8']."</dd>";

      $html .= "<dt>6- <b>".$script_transl['rinvio']."</b></dt>" ;
      $html .= "<dd>- ".$script_transl['rinvio1']."</dd>";

      $html .= "<dt>7- <b>".$script_transl['accettazione']."</b></dt>" ;
      $html .= "<dd>- ".$script_transl['accettazione1']."</dd>"."<dd>- ".$script_transl['accettazione2']."</dd>";

      $html .= "<dl>";

      // output the HTML content
      $this->writeHTML($html, true, false, true, false, '');

    }


    function compose()
    {
        $this->body();
    }

    function pageFooter()
    {
        $y = $this->GetY();

      $this->SetY(224);

    }

    function Footer()
    {
        //Page footer
        $this->SetY(-20);
        $this->SetFont('helvetica', '', 8);
        if ( $this->sedelegale!="" ) {
            $this->MultiCell(184, 4, $this->intesta1 . ' ' . $this->intesta2 . ' ' . $this->intesta3 . ' ' . $this->intesta4 . ' ' . "SEDE LEGALE: ".$this->sedelegale, 0, 'C', 0);
        } else {
            $this->MultiCell(184, 4, $this->intesta1 . ' ' . $this->intesta2 . ' ' . $this->intesta3 . ' ' . $this->intesta4, 0, 'C', 0);
        }
    }
}

?>
