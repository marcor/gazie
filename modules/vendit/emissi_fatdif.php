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
if (!ini_get('safe_mode')){ //se me lo posso permettere...
    ini_set('memory_limit','128M');
    gaz_set_time_limit (0);
}

function FattureDaDdt($period,$sezione=1,$cliente=0,$excludeDdt=array())
{
    global $gTables;
    $annoemissione = substr($period['fine'],0,4);
    $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = $annoemissione AND tipdoc LIKE 'F%' AND seziva = $sezione","protoc DESC",0,1);
    $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
    // ricavo il progressivo annuo, ma se e' il primo documento dell'anno, resetto il contatore
    if ($ultimo_documento) {
        $last_pr = $ultimo_documento['protoc'];
    } else {
        $last_pr = 0;
    }
    $rs_ultima_fa = gaz_dbi_dyn_query("numdoc, numfat*1 AS fattura", $gTables['tesdoc'], "YEAR(datemi) = $annoemissione AND tipdoc LIKE 'FA%' AND seziva = $sezione","fattura DESC",0,1);
    $ultima_fa = gaz_dbi_fetch_array($rs_ultima_fa);
    // ricavo il progressivo annuo delle Fattura, ma se e' la prima Fattura dell'anno, resetto il contatore
    if ($ultima_fa) {
        $last_nu = $ultima_fa['fattura'] ;
    } else {
        $last_nu = 0;
    }
    //preparo la query al database
    $clientesel = '';
    if ($cliente > 0) {
        $clientesel = ' AND clfoco = '.$cliente;
    }
    $orderby = "ragso1 ASC, pagame ASC, numdoc ASC, id_rig ASC";
    $where = "tipdoc = 'DDT' AND datemi BETWEEN '".$period['inizio']."' AND '".$period['fine']."' AND seziva = '$sezione' ".$clientesel;
    //recupero i dati dal DB (testate+cliente+pagamento+righi)
    $field = 'tes.id_tes,tes.clfoco,tes.numdoc,tes.pagame,tes.traspo,tes.speban,tes.banapp,tes.datemi,
              CONCAT(ana.ragso1,\' \',ana.ragso2,\' \',ana.citspe,\' \',ana.prospe) AS ragsoc,
              cli.codice,cli.ragdoc,
              pag.tippag,pag.incaut,pag.numrat,pag.descri AS despag,
              rig.id_tes,rig.id_rig,rig.codart,rig.descri,rig.unimis,rig.quanti,rig.prelis,rig.tiprig,rig.sconto';
    $from = $gTables['tesdoc'].' AS tes '.
            'LEFT JOIN '.$gTables['clfoco'].' AS cli ON tes.clfoco=cli.codice '.
            'LEFT JOIN '.$gTables['anagra'].' AS ana ON cli.id_anagra=ana.id '.
            'LEFT JOIN '.$gTables['pagame'].' AS pag ON pag.codice=tes.pagame '.
            'LEFT JOIN '.$gTables['rigdoc'].' AS rig ON rig.id_tes=tes.id_tes ';
    $result = gaz_dbi_dyn_query($field, $from, $where, $orderby);
    $ctrlnum = gaz_dbi_num_rows($result);
    $fatture = array();
    if ($ctrlnum) {
       //creo l'array associativo testate-righi
       $ctrlc = 0;
       $ctrlp = 0;
       $ctrld = 0;
       $totale_imponibile = 0;
       while ($row = gaz_dbi_fetch_array($result)) {
             if (in_array($row['id_tes'],$excludeDdt) and $ctrld != $row['id_tes']) { // se  tra gli esclusi vado avanti ma mantengo il riferimento
                $fatture['no'][] = array('id'=>$row['id_tes'],
                                                   'ragionesociale'=>$row['ragsoc'],
                                                   'numero'=>$row['numdoc'],
                                                   'data'=>$row['datemi'],
                                                   'pagamento'=>$row['despag']
                                                   );
                continue;
             }
             if ($row['clfoco'] != $ctrlc or $row['pagame'] != $ctrlp or ($row['id_tes'] != $ctrld and $row['ragdoc'] == 'N')) {  //se  un'altro cliente o il cliente ha un pagamento diverso dal precedente
                    if ($ctrlc > 0 and $ctrlp > 0)  {  //se non  la prima fattura pongo il totale della precedente nell'array
                       $fatture['yes'][$last_pr]['totale'] = $totale_imponibile;
                    }
                    $totale_imponibile = 0;
                    $last_pr ++;
                    $last_nu ++;
                    // nuova testata fattura
                    $fatture['yes'][$last_pr] = array('numero'=>$last_nu,'codicecliente'=>$row['clfoco'],'ragionesociale'=>$row['ragsoc']);
                    $fatture['yes'][$last_pr]['speseincasso'] = $row['numrat']*$row['speban'];
                    //$totale_imponibile += $fatture['yes'][$last_pr]['speseincasso'];
             }
             if ($row['id_tes'] != $ctrld) {  //se  un'altro ddt
                   if ($row['clfoco'] == $ctrlc and $row['pagame'] != $ctrlp){
                        $fatture['yes'][$last_pr]['righi'][] = array('codice'=>'_MSG_',
                                                   'descrizione'=>' Cliente con diversi pagamenti! '
                                                   );
                   }
                   $fatture['yes'][$last_pr]['righi'][] = array('codice'=>'_DES_',
                                                   'numero'=>$row['numdoc'],
                                                   'id'=>$row['id_tes'],
                                                   'data'=>$row['datemi'],
                                                   'codpag'=>$row['pagame'],
                                                   'despag'=>$row['despag']
                                                   );
                   if ($row['incaut'] == 'S') {
                        $fatture['yes'][$last_pr]['righi'][] = array('codice'=>'_MSG_',
                                                   'descrizione'=>' Pagamento che prevede l\'incasso automatico! '
                                                   );
                   }
                   if (($row['tippag'] == 'B' or $row['tippag'] == 'T') and $row['banapp'] == 0) {
                        $fatture['yes'][$last_pr]['righi'][] = array('codice'=>'_MSG_',
                                                   'descrizione'=>' ATTENZIONE! MANCA LA BANCA D\'APPOGGIO ! '
                                                   );
                   }
                   if ($row['traspo'] > 0) {
                      $fatture['yes'][$last_pr]['righi'][] = array('codice'=>'_TRA_',
                                                   'descrizione'=>'TRASPORTO',
                                                   'importo'=>$row['traspo']
                                                   );
                      $totale_imponibile += $row['traspo'];
                   }
             }
             $importo_rigo = CalcolaImportoRigo($row['quanti'], $row['prelis'], $row['sconto']);
             if ($row['tiprig'] == 1) {
                   $importo_rigo = CalcolaImportoRigo(1, $row['prelis'], 0);
             }
             $totale_imponibile += $importo_rigo;
             //aggiungo il rigo
             $fatture['yes'][$last_pr]['righi'][] = array('codice'=>$row['codart'],
                                                   'descrizione'=>$row['descri'],
                                                   'unitamisura'=>$row['unimis'],
                                                   'quantita'=>$row['quanti'],
                                                   'prezzo'=>$row['prelis'],
                                                   'sconto'=>$row['sconto'],
                                                   'importo'=>$importo_rigo);
             $ctrld = $row['id_tes'];
             $ctrlc = $row['clfoco'];
             $ctrlp = $row['pagame'];
       }
       $fatture['yes'][$last_pr]['totale'] = $totale_imponibile;
    }
    return $fatture;
}
$message = '';

$clienti = $admin_aziend['mascli'];

if (!isset($_POST['seziva']) and isset($_GET['seziva'])){
    $sezione=intval($_GET['seziva']);
} elseif (isset($_POST['seziva'])) {
    $sezione=intval($_POST['seziva']);
} else {
    $sezione=1;
}

if (!isset($_POST['excludeDdt'])){
    $_POST['excludeDdt'] = array();
}

if (isset($_POST['add_ex'])) {
    $_POST['anteprima'] = '';
    $_POST['excludeDdt'][] = key($_POST['add_ex']);
}

if (isset($_POST['del_ex'])) {
    $_POST['anteprima'] = '';
    $key = array_search(key($_POST['del_ex']), $_POST['excludeDdt']);
    unset($_POST['excludeDdt'][$key]);
}

// vedo se ci sono dei DdT non ancora fatturati
$rs_ddtsez = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "tipdoc = 'DDT' and seziva = $sezione","datemi asc",0,1);
$ddtsez = gaz_dbi_fetch_array($rs_ddtsez);
if (!isset($_POST['ragso1'])){
    $_POST['ragso1'] = '';
}
if (!$ddtsez) {
    $message .="Non ci sono D.d.T. da fatturare nella sezione IVA n.".$sezione." !!!<br>";
    $_POST['codcli'] = '';
    $_POST['gioemi'] = date("d");
    $_POST['mesemi'] = date("m");
    $_POST['annemi'] = date("Y");
    $_POST['gioini'] = date("d");
    $_POST['mesini'] = date("m");
    $_POST['annini'] = date("Y");
    $_POST['giofin'] = date("d");
    $_POST['mesfin'] = date("m");
    $_POST['annfin'] = date("Y");
    }
    if (!isset($_POST['codcli']))
        $_POST['codcli'] = '';
    if (!isset($_POST['gioemi']))
       $_POST['gioemi'] = strftime ("%d", mktime (0,0,0,substr($ddtsez['datemi'],5,2)+1,0,substr($ddtsez['datemi'],0,4)));
    if (!isset($_POST['mesemi']))
       $_POST['mesemi'] = substr($ddtsez['datemi'],5,2);
    if (!isset($_POST['annemi']))
       $_POST['annemi'] = substr($ddtsez['datemi'],0,4);
    if (!isset($_POST['gioini']))
       $_POST['gioini'] = "1";
    if (!isset($_POST['mesini']))
       $_POST['mesini'] = substr($ddtsez['datemi'],5,2);
    if (!isset($_POST['annini']))
       $_POST['annini'] = substr($ddtsez['datemi'],0,4);
    if (!isset($_POST['giofin']))
       $_POST['giofin'] = strftime ("%d", mktime (0,0,0,substr($ddtsez['datemi'],5,2)+1,0,substr($ddtsez['datemi'],0,4)));
    if (!isset($_POST['mesfin']))
       $_POST['mesfin'] = substr($ddtsez['datemi'],5,2);
    if (!isset($_POST['annfin']))
       $_POST['annfin'] = substr($ddtsez['datemi'],0,4);

    //controllo i campi
    if (!checkdate( $_POST['mesemi'], $_POST['gioemi'], $_POST['annemi']))
        $message .= "La data ".$_POST['gioemi']."-".$_POST['mesemi']."-".$_POST['annemi']." non &egrave; corretta! <br>";
    if (!checkdate( $_POST['mesini'], $_POST['gioini'], $_POST['annini']))
        $message .= "La data ".$_POST['gioini']."-".$_POST['mesini']."-".$_POST['annini']." non &egrave; corretta! <br>";
    if (!checkdate( $_POST['mesfin'], $_POST['giofin'], $_POST['annfin']))
        $message .= "La data ".$_POST['giofin']."-".$_POST['mesfin']."-".$_POST['annfin']." non &egrave; corretta! <br>";
    $utsemi= mktime(0,0,0,$_POST['mesemi'],$_POST['gioemi'],$_POST['annemi']);
    $utsini= mktime(0,0,0,$_POST['mesini'],$_POST['gioini'],$_POST['annini']);
    $utsfin= mktime(0,0,0,$_POST['mesfin'],$_POST['giofin'],$_POST['annfin']);
    if ($utsemi < $utsfin)
        $message .="La data di emissione non pu&ograve; essere antecedente alla data dell'ultimo DdT !<br>";
    if ($utsini > $utsfin)
        $message .="La data di inizio dei DdT da emettere non pu&ograve; essere successivo alla data dell'ultimo !<br>";

    // ricavo il progressivo annuo del protocollo e controllo se la data non e' precedente a quella dell'ultimo protocollo emesso
    $rs_ultimo_tipo = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = ".$_POST['annemi']." AND tipdoc LIKE 'F%' AND seziva = $sezione"," protoc DESC, datfat DESC, datemi DESC",0,1);
    $ultimo_tipo = gaz_dbi_fetch_array($rs_ultimo_tipo);
    $utsUltimoProtocollo = mktime(0,0,0,substr($ultimo_tipo['datfat'],5,2),substr($ultimo_tipo['datfat'],8,2),substr($ultimo_tipo['datfat'],0,4));
    if ($ultimo_tipo and ($utsUltimoProtocollo > $utsemi)) {
        $message .= "ERRORE! L'ultimo documento emesso <BR /> (protocollo ".$ultimo_tipo['protoc']." n.".$ultimo_tipo['numfat'].") ha una data (".$ultimo_tipo['datfat'].")<BR /> successiva a quella di emissione!<BR />\n";
    }

if (isset($_POST['genera']) and $message == "") {
    $periodo = array ('inizio'=>sprintf("%04d-%02d-%02d", $_POST['annini'], $_POST['mesini'], $_POST['gioini']),
                      'fine'=>sprintf("%04d-%02d-%02d", $_POST['annfin'], $_POST['mesfin'], $_POST['giofin'])
                      );
    $data_emissione = sprintf("%04d-%02d-%02d", $_POST['annemi'], $_POST['mesemi'], $_POST['gioemi']);
    $fatture = FattureDaDdt($periodo,$sezione,$_POST['codcli'],$_POST['excludeDdt']);
    $protocollo_inizio = 0;
    foreach ($fatture['yes'] as $kt=>$vt) {
            // rilevamento protocollo iniziale
            if ($protocollo_inizio == 0){
               $protocollo_inizio = $kt;
            }
            foreach ($vt['righi'] as $kr=>$vr) {
                    if (isset($vr['id'])) {
                       //vado a modificare la testata cambiando il tipo e introducendo protocollo,numero,data fattura
                       $data['tipdoc']='FAD';
                       $data['protoc']=$kt;
                       $data['numfat']=$vt['numero'];
                       $data['datfat']=$data_emissione;
                       // questo e' troppo lento: gaz_dbi_table_update('tesdoc', array('id_tes',$vr['id']),$data);
                       gaz_dbi_query ("UPDATE ".$gTables['tesdoc']." SET tipdoc = 'FAD', protoc = ".$kt.
                                                                      ", numfat = '".$vt['numero'].
                                                                      "', datfat = '".$data_emissione."' WHERE id_tes = ".$vr['id'].";");
                    }
            }
            $protocollo_fine = $kt;
    }
    //Mando in stampa le fatture generate
    $locazione = "Location: select_docforprint.php?tipdoc=2&seziva=".$sezione."&proini=".$protocollo_inizio."&profin=".$protocollo_fine;
    header($locazione);
    exit;
}


if (isset($_POST['Return']))
    {
    header("Location:report_docven.php");
    exit;
}

$titolo = 'Emissione fatture differite da D.d.T.';
require("../../library/include/header.php");
HeadMain(6);
?>
<form method="POST">
<div align="center" class="FacetFormHeaderFont">Emissione fatture differite da D.d.T. della sez.
<?php
echo "<select name=\"seziva\" class=\"FacetFormHeaderFont\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 3; $counter++) {
    $selected="";
    if ($sezione == $counter) {
       $selected = " selected ";
    }
    echo "<option value=\"".$counter."\"".$selected.">".$counter."</option>\n";
}
echo "</select>\n";
?>
</div>
<table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
  <!-- BEGIN Error -->
  <tr>
    <td colspan="2" class="FacetDataTD" style="color: red;">
    <?php
    if (! $message == "")
    {
    echo "$message";
    }
    ?>
    </td>
  </tr>
  <!-- END Error -->

<tr>
    <td class="FacetFieldCaptionTD">Selezione Cliente &nbsp;</td>
    <td class="FacetDataTD" >
    <?php
    $messaggio = "";
    $tabula =" tabindex=\"1\" ";
    $cerca = $_POST['ragso1'];
    echo "<select name=\"codcli\" class=\"FacetSelect\" onchange=\"this.form.submit()\">";
    echo "\t\t <option value=\"\">Tutti i clienti !</option>\n";
    if (strlen($_POST['ragso1']) >= 2)
        {
        $mascon=$clienti*1000000;
        $result = gaz_dbi_dyn_query("*", $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id',
                                         "codice like '$clienti%' and codice > '$mascon'  and ragso1 like '".addslashes($cerca)."%'","ragso1 DESC");
        $numclfoco = gaz_dbi_num_rows($result);
        if ($numclfoco > 0)
            {
            $tabula="";

            while ($a_row = gaz_dbi_fetch_array($result))
                {
                $selected = "";
                if($a_row["codice"] == $_POST['codcli'])
                $selected = "selected";
                echo "\t\t <option value=\"".$a_row["codice"]."\" $selected >".$a_row["ragso1"]."&nbsp;".$a_row["citspe"]."</option>\n";
                }
            }
            else $messaggio = "Non &egrave; stato trovato nulla!";
        }
        else
        {
        $messaggio = "Inserire min. 2 caratteri!";
        }
        echo "\t </select>\n";
        echo "\t<input type=\"text\" name=\"ragso1\" ".$tabula." accesskey=\"e\" value=\"".$_POST['ragso1']."\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
        echo $messaggio;
        echo "\t <input type=\"image\" align=\"middle\" accesskey=\"c\" ".$tabula." name=\"clfoco\" src=\"../../library/images/cerbut.gif\">\n";
        ?>
    </td>
</tr>
<tr>
    <td class="FacetFieldCaptionTD">Data di emissione &nbsp;</td>

    <td class="FacetDataTD" >
         <?php
            // select del giorno
            echo "\t <select name=\"gioemi\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 1; $counter <= 31; $counter++ )
                {
                $selected = "";
                if($counter ==  $_POST['gioemi'])
                        $selected = "selected";
                echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
                }
            echo "\t </select>\n";
            // select del mese
            echo "\t <select name=\"mesemi\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";

            for( $counter = 1; $counter <= 12; $counter++ )
                {
                $selected = "";
                if($counter == $_POST['mesemi'])
                        $selected = "selected";
                $nome_mese = ucwords(strftime("%B", mktime (0,0,0,$counter,1,0)));
                echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
                }
            echo "\t </select>\n";
            // select del anno
            echo "\t <select name=\"annemi\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 2002; $counter <= 2030; $counter++ )
                {
                $selected = "";
                if($counter == $_POST['annemi'])
                        $selected = "selected";
                echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
                }
            echo "\t </select>\n";
         ?>
    </td>
  </tr>
  <tr>

    <td class="FacetFieldCaptionTD">Data DdT inizio &nbsp;</td>

    <td class="FacetDataTD" >
         <?php
            // select del giorno
            echo "\t <select name=\"gioini\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 1; $counter <= 31; $counter++ )
                {
                $selected = "";
                if($counter ==  $_POST['gioini'])
                        $selected = "selected";
                echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
                }
            echo "\t </select>\n";
            // select del mese
            echo "\t <select name=\"mesini\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";

            for( $counter = 1; $counter <= 12; $counter++ )
                {
                $selected = "";
                if($counter == $_POST['mesini'])
                        $selected = "selected";
                $nome_mese = ucwords(strftime("%B", mktime (0,0,0,$counter,1,0)));
                echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
                }
            echo "\t </select>\n";
            // select del anno
            echo "\t <select name=\"annini\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 2002; $counter <= 2030; $counter++ )
                {
                $selected = "";
                if($counter == $_POST['annini'])
                        $selected = "selected";
                echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
                }
            echo "\t </select>\n";
         ?>
    </td>
  </tr>
  <tr>

    <td class="FacetFieldCaptionTD">Data DdT fine &nbsp;</td>

    <td class="FacetDataTD" >
         <?php
            // select del giorno
            echo "\t <select name=\"giofin\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 1; $counter <= 31; $counter++ )
                {
                $selected = "";
                if($counter ==  $_POST['giofin'])
                        $selected = "selected";
                echo "\t\t <option value=\"$counter\" $selected >$counter</option>\n";
                }
            echo "\t </select>\n";
            // select del mese
            echo "\t <select name=\"mesfin\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";

            for( $counter = 1; $counter <= 12; $counter++ )
                {
                $selected = "";
                if($counter == $_POST['mesfin'])
                        $selected = "selected";
                $nome_mese = ucwords(strftime("%B", mktime (0,0,0,$counter,1,0)));
                echo "\t\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
                }
            echo "\t </select>\n";
            // select del anno
            echo "\t <select name=\"annfin\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
            for( $counter = 2002; $counter <= 2030; $counter++ )
                {
                $selected = "";
                if($counter == $_POST['annfin'])
                        $selected = "selected";
                echo "\t\t <option value=\"$counter\"  $selected >$counter</option>\n";
                }
            echo "\t </select>\n";
         ?>

    </td>
  </tr>

    <td class="FacetFieldCaptionTD">&nbsp;</td>
    <td colspan="2" align="right" nowrap class="FacetFooterTD">
    <input type="submit" name="Return" value="Indietro">&nbsp;
    <input type="submit" name="anteprima" value="VISUALIZZA L'ANTEPRIMA !">&nbsp;
    </td>
  </tr>
</table>
<?php
if (isset($_POST['anteprima']) and $message == "") {
    $periodo = array ('inizio'=>sprintf("%04d-%02d-%02d", $_POST['annini'], $_POST['mesini'], $_POST['gioini']),
                      'fine'=>sprintf("%04d-%02d-%02d", $_POST['annfin'], $_POST['mesfin'], $_POST['giofin'])
                      );
    $fatture = FattureDaDdt($periodo,$sezione,$_POST['codcli'],$_POST['excludeDdt']);
    echo '<div align="center"><b>ANTEPRIMA DI FATTURAZIONE</b></div>';
    echo "<table class=\"Tlarge\">";
    if (isset($fatture['yes']) && !isset($fatture['yes'][0]['totale'])){
      foreach ($fatture['yes'] as $kt=>$vt) {
            echo "<tr>";
            echo "<td> ".$vt['codicecliente']." &nbsp;</td>";
            echo "<td colspan=\"4\"> ".$vt['ragionesociale']." &nbsp;</td>";
            echo "<td> Fatt. n.".$vt['numero']." &nbsp;</td>";
            echo "<td> Prot. n.".$kt."</td>";
            echo "</tr>\n";
            foreach ($vt['righi'] as $kr=>$vr) {
                    if ($vr['codice'] == '_MSG_')  {
                       echo "<tr>";
                       echo "<td class=\"FacetDataTDred\" colspan=\"7\">".$vr['descrizione']." </td>";
                       echo "</tr>\n";
                    } elseif ($vr['codice'] == '_TRA_') {
                       echo "<tr>";
                       echo "<td class=\"FacetDataTD\"></td><td class=\"FacetDataTD\" colspan=\"5\" align=\"right\">".$vr['descrizione']." </td>";
                       echo "<td class=\"FacetDataTD\" align=\"right\"> ".gaz_format_number($vr['importo'])." &nbsp;</td>";
                       echo "</tr>\n";
                    } elseif ($vr['codice'] == '_DES_') {
                       echo "<tr>";
                       echo "<td class=\"FacetDataTD\" colspan=\"2\">da D.d.T. n.<a href=\"admin_docven.php?Update&id_tes=".$vr['id']."\">".$vr['numero']."</a> del ".$vr['data']." &hArr; ".$vr['despag']."</td>";
                       echo "<td ><input class=\"FacetText\" type=\"submit\" name=\"add_ex[{$vr['id']}]\" value=\"Escludi!\" /></td>";
                       echo "</tr>\n";
                    } else {
                       echo "<tr>";
                       echo "<td class=\"FacetDataTD\">".$vr['codice']." &nbsp;</td>";
                       echo "<td class=\"FacetDataTD\">".$vr['descrizione']." </td>";
                       echo "<td class=\"FacetDataTD\"> ".$vr['unitamisura']." &nbsp;</td>";
                       echo "<td class=\"FacetDataTD\" align=\"right\"> ".$vr['quantita']." &nbsp;</td>";
                       echo "<td class=\"FacetDataTD\" align=\"right\"> ".number_format($vr['prezzo'],3,',','.')." &nbsp;</td>";
                       echo "<td class=\"FacetDataTD\" align=\"right\"> ".$vr['sconto']." &nbsp;</td>";
                       echo "<td class=\"FacetDataTD\" align=\"right\"> ".gaz_format_number($vr['importo'])." &nbsp;</td>";
                       echo "</tr>\n";
                    }
            }
            echo "<tr>";
            echo "<td class=\"FacetDataTDred\"></td><td class=\"FacetDataTD\" colspan=\"5\" align=\"right\">TOTALE</td>";
            echo "<td class=\"FacetDataTDred\" align=\"right\"> ".gaz_format_number($vt['totale'])." &nbsp;</td>";
            echo "</tr>\n";
            if ($vt['speseincasso'] > 0) {
                       echo "<tr>";
                       echo "<td class=\"FacetFooterTD\"></td><td class=\"FacetFooterTD\" colspan=\"5\" align=\"right\">SPESE INCASSO</td>";
                       echo "<td class=\"FacetFooterTD\" align=\"right\"> ".gaz_format_number($vt['speseincasso'])." &nbsp;</td>";
                       echo "</tr>\n";
            }
      }
      echo "<tr><td  align=\"right\" colspan=\"7\"><input type=\"submit\" name=\"genera\" value=\"CONFERMA LA GENERAZIONE DELLE FATTURE COME DA ANTEPRIMA !\"></TD></TR>";
    } else { 
      echo "<tr><td class=\"FacetDataTDred\" colspan=\"7\" align=\"right\">Non ci sono DdT  da fatturare</td></tr>";
    }
    if (isset($fatture['no'])) {
            echo "<tr><td class=\"FacetDataTDred\" colspan=\"3\" align=\"right\">I DdT sottosegnati sono stati esclusi dalla fatturazione&darr; </td></TR>";
            $ctrld=0;
            foreach ($fatture['no'] as $key => $value) {
                 if ($ctrld!=$value['id']) {
                       echo "<input type=\"hidden\" name=\"excludeDdt[{$key}]\" value=\"".$value['id']."\" />\n";
                       echo "<tr>";
                       echo "<td class=\"FacetDisabledTD\" colspan=\"7\"><input class=\"FacetText\" type=\"submit\" name=\"del_ex[{$value['id']}]\" value=\"Ripristina!\" /> il DdT n.<a href=\"admin_docven.php?Update&id_tes=".$value['id']."\">".$value['numero']."</a> del ".$value['data']." a:".$value['ragionesociale']." &hArr; ".$value['pagamento']."</td>";
                       echo "</tr>\n";
                 }
             $ctrld=$value['id'];
            }
    }
    echo "</table>\n";
}
?>
</form>
</body>
</html>