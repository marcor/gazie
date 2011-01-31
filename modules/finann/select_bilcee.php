<?php
/*$Id: select_bilcee.php,v 1.28 2011/01/01 11:07:40 devincen Exp $
--------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
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
$titolo = "Bilancio IV direttiva CEE";
$oggi = date("Y-m-d");
$orainizio = date("Y-m-d H:i:s");
$message = "";
$errore ="";

$nromani = array(0=>"",1=>"I",2=>"II",3=>"III",4=>"IV",5=>"V",6=>"VI",7=>"VII",8=>"VIII",9=>"IX",10=>"X",11=>"XI",12=>"XII",13=>"XIII",14=>"XIV",15=>"XV",16=>"XVI",17=>"XVII",18=>"XVIII",19=>"XIX",20=>"XX");
$attdesc = array('A'=>array("Titolo"=>") CREDITI VERSO SOCI:"),'B'=>array("Titolo"=>") IMMOBILIZZAZIONI:",1=>" - Immobilizzazioni immateriali: ",2=>" - Immobilizzazioni materiali:",3=>" - Immobilizzazioni finanziarie: "),'C'=>array("Titolo"=>") ATTIVO CIRCOLANTE:",1=>" - Rimanenze: ",2=>" - Crediti: ",3=>" - Attivit&agrave; finanziarie: ",4=>" - Disponibilit&agrave; liquide: "),'D'=>array("Titolo"=>") RATEI E RISCONTI:"));
$pasdesc = array('A'=>array("Titolo"=>") PATRIMONIO NETTO:",1=>" - Capitale:",2=>" - Riserva da sovrapprezzo delle azioni:",3=>" - Riserva di rivalutazione:",4=>" - Riserva legale:",5=>" - Riserva per azioni proprie in portafoglio:",6=>" - Riserve statutarie:",7=>" - Altre riserve distintamente indicate:",8=>" - Utili (perdite) portati a nuovo:",9=>" - Utile (perdita) dell'esercizio:"),'B'=>array("Titolo"=>") FONDI RISCHI E ONERI:"),'C'=>array("Titolo"=>") TRATTAMENTO DI FINE RAPPORTO DI LAVORO SUBORDINATO:"),'D'=>array("Titolo"=>") DEBITI:"),'E'=>array("Titolo"=>") RATEI E RISCONTI:"));
$ecodesc = array('A'=>array("Titolo"=>") Valore della produzione:"),'B'=>array("Titolo"=>") Costi della produzione:"),'C'=>array("Titolo"=>") Proventi e oneri finanziari:"),'D'=>array("Titolo"=>") Rettifiche di valore di attività finanziarie:"),'E'=>array("Titolo"=>") Proventi e oneri straordinari:"),'_'=>array("Titolo"=>") Risultato prima delle imposte:"));
//Carica i dati del bilancio IV direttiva CEE
//Legge le linee del file
$data = array();
$descon = array();
$noclass = 'non riclassificato';
$lines=file('IVdirCEE.bil');
foreach($lines as $line) {
        $nuova = explode(';',$line,2);
        $descon[trim($nuova[0])] = $nuova[1];
        $data[] = trim($nuova[0]);
}
$data = array_slice($data,1);
if (!isset($_GET['gioini']))
    $_GET['gioini'] = "1";
if (!isset($_GET['mesini']))
    $_GET['mesini'] = "1";
if (!isset($_GET['annini']))
    $_GET['annini'] =   date("Y")-1;
if (!isset($_GET['giofin']))
    $_GET['giofin'] =  "31";
if (!isset($_GET['mesfin']))
    $_GET['mesfin'] =  "12";
if (!isset($_GET['annfin']))
    $_GET['annfin'] =  date("Y")-1;

//controllo i campi
if (!checkdate( $_GET['mesini'], $_GET['gioini'], $_GET['annini']))
    $message .= "La data ".$_GET['gioini']."-".$_GET['mesini']."-".$_GET['annini']." non &egrave; corretta!<br>";
if (!checkdate( $_GET['mesfin'], $_GET['giofin'], $_GET['annfin']))
    $message .= "La data ".$_GET['giofin']."-".$_GET['mesfin']."-".$_GET['annfin']." non &egrave; corretta!<br>";
    $utsini= mktime(0,0,0,$_GET['mesini'],$_GET['gioini'],$_GET['annini']);
    $utsdop= mktime(0,0,0,$_GET['mesini'],$_GET['gioini']-1,$_GET['annini']+1);
    $utsfin= mktime(0,0,0,$_GET['mesfin'],$_GET['giofin'],$_GET['annfin']);
    $datainizio = date("Ymd",$utsini);
    $datadopo = date("Ymd",$utsdop);
    $datafine = date("Ymd",$utsfin);
if ($utsini >= $utsfin)
    $message .="La data di inizio periodo dev'essere precedente alla data di fine periodo !<br>";
if (isset($_GET['stampa'])) {
    $locazione = "Location: stampa_bilcee.php?&bilini=".$datainizio."&bilfin=".$datafine;
    header($locazione);
    exit;
}
if (isset($_GET['Return'])) {
    header("Location:docume_finean.php");
    exit;
}
require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<form method="GET">
<div align="center" class="FacetFormHeaderFont">Bilancio IV direttiva CEE</div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
<tr>
<td colspan="2" class="FacetDataTD"  style="color: red;">
<?php
if (! $message == "") {
    print "$message";
}
?>
</td>
</tr>
<tr><td class="FacetFieldCaptionTD">Data Inizio Periodo &nbsp;</td>
<td class="FacetDataTD" colspan=3>
<?php
// select del giorno
print "\t <select name=\"gioini\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter = 1; $counter <= 31; $counter++ ) {
    $selected = "";
    if($counter ==  $_GET['gioini'])
       $selected = "selected";
    print "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
}
print "\t </select>\n";
// select del mese
echo "\t <select name=\"mesini\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for( $counter = 1; $counter <= 12; $counter++ ) {
     $selected = "";
     if($counter == $_GET['mesini'])
        $selected = "selected";
        $nome_mese = ucwords(strftime("%B", mktime (0,0,0,$counter,1,0)));
        echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
// select del anno
            print "\t <select name=\"annini\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 2003; $counter <= 2030; $counter++ )
                {
                $selected = "";
                if($counter == $_GET['annini'])
                        $selected = "selected";
                print "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
                }
            print "\t </select>\n";
         ?>
    </td>
  </tr>
  <tr>
    <td class="FacetFieldCaptionTD">Data Fine Periodo &nbsp;</td>
    <td class="FacetDataTD"  colspan=3 >
         <?php
            // select del giorno
            print "\t <select name=\"giofin\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 1; $counter <= 31; $counter++ )
                {
                $selected = "";
                if($counter ==  $_GET['giofin'])
                        $selected = "selected";
                print "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
                }
            print "\t </select>\n";
            // select del mese
            echo "\t <select name=\"mesfin\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 1; $counter <= 12; $counter++ )
                {
                $selected = "";
                if($counter == $_GET['mesfin'])
                        $selected = "selected";
                $nome_mese = ucwords(strftime("%B", mktime (0,0,0,$counter,1,0)));
                echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
                }
            echo "\t </select>\n";
            // select del anno
            print "\t <select name=\"annfin\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 2003; $counter <= 2030; $counter++ )
                {
                $selected = "";
                if($counter == $_GET['annfin'])
                        $selected = "selected";
               print "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
                }
            print "\t </select>\n";
         ?>
    </td>
  </tr>
<?php
if ($message == "")
        {
        echo "<tr><td class=\"FacetFieldCaptionTD\"> </td><td align=\"right\" nowrap class=\"FacetFooterTD\"><input type=\"submit\" name=\"Return\" value=\"Indietro\"> <input type=\"submit\" name=\"visualizza\" value=\"VISUALIZZA L'ANTEPRIMA !\"> </td></tr>";
        }
echo "</table>";
if (isset($_GET['visualizza']) and $message == "")
    {
    $where = "datreg between '$datainizio' and '$datafine' and caucon <> 'CHI' and caucon <> 'APE' or (caucon = 'APE' and datreg between '$datainizio' and '$datadopo') group by codcon ";
    $orderby = " codcon ";
    $rs_castel = gaz_dbi_dyn_query("codcon, ragso1,".$gTables['clfoco'].".descri AS descri, SUM(import*(darave='D')-import*(darave='A')) AS saldo, ceedar, ceeave", $gTables['rigmoc']."
                                   LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes
                                   LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['rigmoc'].".codcon = ".$gTables['clfoco'].".codice
                                   LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra", $where, $orderby);
    $ctrlnum = gaz_dbi_num_rows($rs_castel);
if ($ctrlnum > 0)
    {
    //procedura per la creazione dell'array dei conti per la riclassificazione...
    while($castel = gaz_dbi_fetch_array($rs_castel))
        {
    if ($castel["saldo"] > 0) //se l'eccedenza è in dare
            {
            if (! in_array(trim($castel['ceedar']),$data)) //se non e' riclassificato
                {
                // vedo se c'è la riclassificazione sul mastro
                $mastro = gaz_dbi_get_row($gTables['clfoco'],"codice",substr($castel['codcon'],0,3)."000000");
                $castel['ceedar']=trim($mastro['ceedar']);
                if (! in_array($castel['ceedar'],$data)) //se non e' riclassificato neanche il mastro
                    {
                    $errore .= 'Il conto '.$castel["codcon"]." ".$castel["descri"].' non &egrave; stato riclassificato per l\'eccedenza in dare! <br>' ;
                    $castel['ceedar']=trim($castel['codcon']);
                    }
                }
                $conti[$castel['codcon']] = array($castel["saldo"],$castel["descri"],$castel["ceedar"]);
            }
        if ($castel["saldo"] < 0) //se l'eccedenza è in avere
            {
            if(! in_array(trim($castel['ceeave']),$data))
                {
                // vedo se c'è la riclassificazione sul mastro
                $mastro = gaz_dbi_get_row($gTables['clfoco'],"codice",substr($castel['codcon'],0,3)."000000");
                $castel['ceeave']=trim($mastro['ceeave']);
                if (! in_array(trim($castel['ceeave']),$data)) //se non e' riclassificato neanche il mastro
                    {
                    $errore .= 'Il conto '.$castel["codcon"]." ".$castel["descri"].' non &egrave; stato riclassificato per l\'eccedenza in avere! <br>' ;
                    $castel['ceeave']=trim($castel['codcon']);
                    }
                 }
                 $conti[$castel['codcon']] = array($castel["saldo"],$castel["descri"],$castel["ceeave"]);
            }
           }
           $contiassoc = array();
           foreach ($conti as $value)
            {
            if (! array_key_exists($value[2],$contiassoc))
               $contiassoc[$value[2]] = $value[0];
            else
               $contiassoc[$value[2]] += $value[0];
            }
            ksort($contiassoc);
            //array conti creato chiave con codice e valore con saldo totale!
            // calcolo l'utile o la perdita (conto economico) e ricreo gli array attivita,passivita,economico.
            $economico = array();
            $attivo = array();
            $passivo = array();
            $risulta = array();
            foreach ($contiassoc as $key => $value)
                    {
                    $ctrlett = substr($key,1,1);
                    $ctrlrom = substr($key,2,2);
                    $ctrltipcon = substr($key,0,1);
                    switch($ctrltipcon)
                    {
                    case 'E':
                    case 4:
                    case 3:
                    if (trim($ctrlett) == '') {
                       $ctrlett='_';
                    }
                    $economico = $economico + array($key=>$value);
                    $risulta[$ctrlett][$ctrlrom][$key] = -$value;
                    break;
                    case 'A':
                    case 1:
                    $attivo[$ctrlett][$ctrlrom][$key] = $value;
                    break;
                    case 'P':
                    case 2:
                    $passivo[$ctrlett][$ctrlrom][$key] = -$value;
                    break;
                    }
                    }
                    //aggiungo l'utile(perdita) sul relativo conto e riclassifico
                    $passivo['A']['09']['PA09000'] = -array_sum($economico);
                    ksort($passivo);
                    ksort($risulta);
    $totrom =0.00;
    $totlet =0.00;
    $totale =0.00;
    echo "<div><center><b>ANTEPRIMA BILANCIO IV direttiva CEE AL ".$_GET['giofin']."-".$_GET['mesfin']."-".$_GET['annfin']."</b></CENTER></div>\n";
    echo "<table class=\"Tlarge\">";
    echo "<tr><td colspan=\"4\">Questo bilancio, riclassificato secondo la IV direttiva CEE, &egrave; stato generato leggendo i movimenti compresi nel periodo selezionato escludendo tutti quelli di apertura e chiusura, unica eccezione &egrave; l'apertura effettuata entro l'anno successivo alla data impostata come inizio periodo.</td></tr>\n";
    if ($errore != "" )
       {
       echo "<tr><td colspan=\"4\" style=\"color: red;\">Sono stati riscontrati i seguenti errori che non ne giustificano la stampa ma la sola visualizzazione: <br></td></tr>\n";
       echo "<tr><td colspan=\"4\" style=\"color: red;\">".$errore."</td></tr>\n";
       }
       else
       echo "<tr><td colspan=\"4\" align=\"center\"><input type=\"submit\" name=\"stampa\" value=\"STAMPA IL BILANCIO CEE !\"></TD></TR>\n";
       echo "<tr><td colspan=\"4\"><hr></TD></TR>\n";
       echo "<tr><TD><hr></TD><TD align=\"center\" class=\"FacetFormHeaderFont\">SITUAZIONE PATRIMONIALE AL ".$_GET['giofin']."-".$_GET['mesfin']."-".$_GET['annfin']."</TD><td colspan=\"2\"><hr></TD></TR>\n";
       echo "<tr><TD align=\"left\"  class=\"FacetFormHeaderFont\" style=\"color: blue;\">ATTIVO</TD><td colspan=\"3\"></TD></TR>\n";
       foreach ($attivo as $keylet => $vallet)
            {
            if (! key_exists($keylet,$attdesc)) {
               $keylet = strtoupper($noclass);
               $attdesc[$keylet]['Titolo']= '';
               $attdesc[$keylet][0]= ucfirst($noclass);
            }
            echo "<tr><TD align=\"left\" class=\"FacetFormHeaderFont\">".$keylet.$attdesc[$keylet]['Titolo']." </TD><td colspan=\"3\"></TD></TR>\n";
       foreach ($vallet as $keyrom => $valrom)
            {
            if (! key_exists($keyrom,$attdesc)) {
               $attdesc[$keylet][intval($keyrom)]= '';
            }
            echo "<tr><TD align=\"left\">".$nromani[intval($keyrom)].$attdesc[$keylet][intval($keyrom)]." </TD><td colspan=\"3\"></TD></TR>\n";
       foreach ($valrom as $key => $value)
            {
            $conto = substr($key,4,3);
            if ($conto == 0) $conto = ""; else $conto=intval($conto);
            $totrom +=$value;
            $totlet +=$value;
            $totale +=$value;
            if($key < 100000000)//controllo per i conti non classificati
            {
            if($value > 0)
               $stampaval = number_format($value,2,'.',''); else $stampaval = "(".number_format(-$value,2,'.','').")";
            echo "<tr><td align=\"right\">".$conto.substr($key,7,1).") </td><td>".$descon[$key]."</td><td>Euro</td><td align=\"right\">".$stampaval."</td></tr>\n";
            }
            else
            {
            if($value > 0)
               $stampaval = number_format($value,2,'.',''); else $stampaval = "(".number_format(-$value,2,'.','').")";
            $descricon=gaz_dbi_get_row($gTables['clfoco'],"codice",$key);
            echo "<tr><td></td><td style=\"color: red;\">\"".$key." - ".$descricon["descri"]."\" non riclassificato</td><td>Euro</td><td align=\"right\">".$stampaval."</td></tr>\n";
            }
            }
            if($totrom > 0)
               $stampaval = number_format($totrom,2,'.',''); else $stampaval = "(".number_format(-$totrom,2,'.','').")";
            echo "<tr><td></td><td align=\"right\"> Totale ".$nromani[intval($keyrom)]."</td><td>Euro</td><td align=\"right\"> ".$stampaval."</td></tr>\n";
            $totrom=0.00;
            }
            if($totlet > 0)
               $stampaval = number_format($totlet,2,'.',''); else $stampaval = "(".number_format(-$totlet,2,'.','').")";
            echo "<tr><td></td><td align=\"right\" class=\"FacetFormHeaderFont\"> Totale ".$keylet." </td><td>Euro</td><td align=\"right\" class=\"FacetFormHeaderFont\"> ".$stampaval."</td></tr>\n";
            $totlet=0.00;
            }
            echo "<tr><td align=\"center\" colspan=\"2\"></td><td colspan=\"2\"><hr></td></tr>";
            echo "<tr><td align=\"right\" colspan=\"2\" class=\"FacetFormHeaderFont\" style=\"color: blue;\"> TOTALE DELL'ATTIVO </td><td>Euro</td><td align=\"right\" class=\"FacetFormHeaderFont\" style=\"color: blue;\">".number_format($totale,2,'.','')."</td></tr>";
            $totale=0.00;
       echo "<tr><td colspan=\"4\"><hr></TD></TR>\n";
       echo "<tr><TD align=\"left\"  class=\"FacetFormHeaderFont\" style=\"color: brown;\">PASSIVO</TD><td colspan=\"3\"></TD></TR>";
       foreach ($passivo as $keylet => $vallet)
            {
            if (! key_exists($keylet,$pasdesc)) {
               $keylet = strtoupper($noclass);
               $pasdesc[$keylet]['Titolo']= '';
               $pasdesc[$keylet][0]= ucfirst($noclass);
            }
            echo "<tr><TD align=\"left\" class=\"FacetFormHeaderFont\">".$keylet.$pasdesc[$keylet]['Titolo']." </TD><td colspan=\"3\"></TD></TR>\n";
       foreach ($vallet as $keyrom => $valrom)
            {
            if (! key_exists($keyrom,$pasdesc)) {
               $pasdesc[$keylet][intval($keyrom)]= '';
            }
            if ($keyrom != 0)
            echo "<tr><TD align=\"left\">".$nromani[intval($keyrom)].$pasdesc[$keylet][intval($keyrom)]." </TD><td colspan=\"3\"></TD></TR>\n";
       foreach ($valrom as $key => $value)
            {
            $conto = substr($key,4,3);
            if ($conto == 0) $conto = ""; else $conto=intval($conto);
            $totrom +=$value;
            $totlet +=$value;
            $totale +=$value;
            if($value > 0)
               $stampaval = number_format($value,2,'.',''); else $stampaval = "(".number_format(-$value,2,'.','').")";
            if($key < 100000000)//controllo per i conti non classificati
            echo "<tr><td align=\"right\">".$conto.substr($key,7,1).") </td><td>".$descon[$key]."</td><td>Euro</td><td align=\"right\">".$stampaval."</td></tr>\n";
            else
            {
            $descricon=gaz_dbi_get_row($gTables['clfoco'],"codice",$key);
            echo "<tr><td></td><td style=\"color: red;\">\"".$key." - ".$descricon["descri"]."\" non riclassificato</td><td>Euro</td><td align=\"right\">".$stampaval."</td></tr>\n";
            }
            }
            if($totrom > 0)
               $stampaval = number_format($totrom,2,'.',''); else $stampaval = "(".number_format(-$totrom,2,'.','').")";
            echo "<tr><td></td><td align=\"right\"> Totale ".$nromani[intval($keyrom)]."</td><td>Euro</td><td align=\"right\"> ".$stampaval."</td></tr>\n";
            $totrom=0.00;
            }
            if($totlet > 0)
               $stampaval = number_format($totlet,2,'.',''); else $stampaval = "(".number_format(-$totlet,2,'.','').")";
            echo "<tr><td></td><td align=\"right\" class=\"FacetFormHeaderFont\"> Totale ".$keylet." </td><td>Euro</td><td align=\"right\" class=\"FacetFormHeaderFont\"> ".$stampaval."</td></tr>\n";
            $totlet=0.00;
            }
            echo "<tr><td align=\"center\" colspan=\"2\"></td><td colspan=\"2\"><hr></td></tr>";
            echo "<tr><td align=\"right\" colspan=\"2\" class=\"FacetFormHeaderFont\" style=\"color: brown;\"> TOTALE DEL PASSIVO </td><td>Euro</td><td align=\"right\" class=\"FacetFormHeaderFont\" style=\"color: brown;\">".number_format($totale,2,'.','')."</td></tr>\n";
            $totale=0.00;
       echo "<tr><td colspan=\"4\"><hr></TD></TR>\n";
       echo "<tr><TD><hr></TD><TD align=\"center\" class=\"FacetFormHeaderFont\">CONTO ECONOMICO DAL ".$_GET['gioini']."-".$_GET['mesini']."-".$_GET['annini']." AL ".$_GET['giofin']."-".$_GET['mesfin']."-".$_GET['annfin']."</TD><td colspan=\"2\"><hr></TD></TR>";
       echo "<tr><TD align=\"left\"  class=\"FacetFormHeaderFont\" style=\"color: orange;\">CONTO ECONOMICO</TD><td colspan=\"3\"></TD></TR>";
       foreach ($risulta as $keylet => $vallet)
            {
            if (! key_exists($keylet,$ecodesc)) {
               $keylet = strtoupper($noclass);
               $ecodesc[$keylet]['Titolo']= '';
               $ecodesc[$keylet][0]= ucfirst($noclass);
            }
            echo "<tr><TD align=\"left\" class=\"FacetFormHeaderFont\">".$keylet.$ecodesc[$keylet]['Titolo']." </TD><td colspan=\"3\"></TD></TR>\n";
       foreach ($vallet as $keyrom => $valrom)
            {
       foreach ($valrom as $key => $value)
            {
            $conto = substr($key,4,3);
            if ($conto == 0) $conto = ""; else $conto=intval($conto);
            $totrom +=$value;
            $totlet +=$value;
            $totale +=$value;
            if($value > 0)
               $stampaval = number_format($value,2,'.',''); else $stampaval = "(".number_format(-$value,2,'.','').")";
            if($key < 100000000)//controllo per i conti non classificati
            echo "<tr><td align=\"right\">".$conto.substr($key,7,1).") </td><td>".$descon[$key]."</td><td>Euro</td><td align=\"right\">".$stampaval."</td></tr>\n";
            else
            {
            $descricon=gaz_dbi_get_row($gTables['clfoco'],"codice",$key);
            echo "<tr><td></td><td style=\"color: red;\">\"".$key." - ".$descricon["descri"]."\" non riclassificato</td><td>Euro</td><td align=\"right\">".$stampaval."</td></tr>\n";
            }
            }
            if($totrom > 0)
               $stampaval = number_format($totrom,2,'.',''); else $stampaval = "(".number_format(-$totrom,2,'.','').")";
            echo "<tr><td></td><td align=\"right\"> Totale ".$nromani[intval($keyrom)]."</td><td>Euro</td><td align=\"right\"> ".$stampaval."</td></tr>\n";
            $totrom=0.00;
            }
            if($totlet > 0)
               $stampaval = number_format($totlet,2,'.',''); else $stampaval = "(".number_format(-$totlet,2,'.','').")";
            echo "<tr><td></td><td align=\"right\" class=\"FacetFormHeaderFont\"> Totale ".$keylet." </td><td>Euro</td><td align=\"right\" class=\"FacetFormHeaderFont\"> ".$stampaval."</td></tr>\n";
            $totlet=0.00;
            }
            if($totale > 0)
               $stampaval = number_format($totale,2,'.',''); else $stampaval = "(".number_format(-$totale,2,'.','').")";
            echo "<tr><td align=\"center\" colspan=\"2\"></td><td colspan=\"2\"><hr></td></tr>";
            echo "<tr><td align=\"right\" colspan=\"2\" class=\"FacetFormHeaderFont\" style=\"color: orange;\"> UTILE(PERDITA) DI ESERCIZIO </td><td>Euro</td><td align=\"right\" class=\"FacetFormHeaderFont\" style=\"color: orange;\">".$stampaval."</td></tr>\n";
            $totale=0.00;
    }
    }
?>
</table>
</form>
</body>
</html>