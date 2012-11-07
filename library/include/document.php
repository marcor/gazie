<?php
 /* $Id: document.php,v 1.50 2011/01/04 16:13:01 devincen Exp $
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2012 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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

class DocContabVars
{
    function setVars($gTables, $tesdoc, $testat, $tableName,$ecr=false)
    {
        $this->ecr=$ecr;
        $this->gTables = $gTables;
        $admin_aziend = gaz_dbi_get_row($gTables['aziend'], 'codice', $_SESSION['enterprise_id']);
        $this->azienda = $admin_aziend;
        $this->user = gaz_dbi_get_row($gTables['admin'], 'Login', $_SESSION['Login']);
        $this->pagame = gaz_dbi_get_row($gTables['pagame'], "codice", $tesdoc['pagame']);
        $this->banapp = gaz_dbi_get_row($gTables['banapp'],"codice",$tesdoc['banapp']);
        $anagrafica = new Anagrafica();
        $this->banacc = $anagrafica->getPartner($this->pagame['id_bank']);
        $this->vettor = gaz_dbi_get_row($gTables['vettor'], "codice", $tesdoc['vettor']);
        $this->tableName = $tableName;
        $this->intesta1 = $admin_aziend['ragso1'];
        $this->intesta1bis = $admin_aziend['ragso2'];
        $this->intesta2 = $admin_aziend['indspe'].' '.sprintf("%05d",$admin_aziend['capspe']).' '.$admin_aziend['citspe'].' ('.$admin_aziend['prospe'].')';
        $this->intesta3 = 'Tel.'.$admin_aziend['telefo'].' ';
        $this->aziendTel = $admin_aziend['telefo'];
        $this->aziendFax = $admin_aziend['fax'];
        $this->codici = '';
        if ($admin_aziend['codfis'] != '') {
            $this->codici .= 'C.F. '.$admin_aziend['codfis'].' ';
        }
        if ($admin_aziend['pariva']) {
            $this->codici .= 'P.I. '.$admin_aziend['pariva'].' ';
        }
        if ($admin_aziend['rea']) {
            $this->codici .= 'R.E.A. '.$admin_aziend['rea'];
        }
        $this->intesta4 = $admin_aziend['e_mail'];
        $this->intesta5 = $admin_aziend['sexper'];
        $this->colore = $admin_aziend['colore'];
        $this->decimal_quantity = $admin_aziend['decimal_quantity'];
        $this->decimal_price = $admin_aziend['decimal_price'];
        $this->logo = $admin_aziend['image'];
        $this->link = $admin_aziend['web_url'];
        $this->perbollo = 0;
        $this->iva_bollo = gaz_dbi_get_row($gTables['aliiva'], "codice", $admin_aziend['ivabol']);
        $this->client = $anagrafica->getPartner($tesdoc['clfoco']);
        $this->cliente1 = $this->client['ragso1'];
        $this->cliente2 = $this->client['ragso2'];
        $this->cliente3 = $this->client['indspe'];
        if (!empty($this->client['citspe'])) {
           $this->cliente4 = sprintf("%05d",$this->client['capspe']).' '.$this->client['citspe'].' ('.$this->client['prospe'].')';
        } else {
           $this->cliente4 = '';
        }
        if (!empty($this->client['pariva'])){
           $this->cliente5 = 'P.I. '.$this->client['pariva'].' ';
        } else {
           $this->cliente5 = '';
        }
        if (!empty($this->client['pariva'])){ //se c'e' la partita iva
            if (!empty($this->client['codfis']) and $this->client['codfis'] == $this->client['pariva']) {
                $this->cliente5 = 'C.F. e P.I. '.$this->client['codfis'];
            } elseif(!empty($this->client['codfis']) and $this->client['codfis'] != $this->client['pariva']) {
                $this->cliente5 = 'C.F. '.$this->client['codfis'].' P.I. '.$this->client['pariva'];
            } else { //per es. se non c'e' il codice fiscale
                $this->cliente5 = ' P.I. '.$this->client['pariva'];
            }
        } else { //se  NON c'e' la partita iva
            $this->cliente5 = '';
            if (!empty($this->client['codfis'])) {
                $this->cliente5 = 'C.F. '.$this->client['codfis'];
            }
        }
        // variabile e' sempre un array
        $this->id_agente = gaz_dbi_get_row($gTables['agenti'],'id_agente',$tesdoc['id_agente']);
        $this->rs_agente = $anagrafica->getPartner($this->id_agente['id_fornitore']);
        $this->name_agente = substr($this->rs_agente['ragso1']." ".$this->rs_agente['ragso2'],0,47);
        if ((isset($tesdoc['id_des'])) and ($tesdoc['id_des'] > 0)) {
            $this->partner_dest = $anagrafica->getPartnerData($tesdoc['id_des']);
            $this->destinazione = substr($this->partner_dest['ragso1']." ".$this->partner_dest['ragso2'],0,45);
            $this->destinazione .= "\n".substr($this->partner_dest['indspe'],0,45);
            $this->destinazione .= "\n".substr($this->partner_dest['capspe']." ".$this->partner_dest['citspe']." (".$this->partner_dest['prospe'].")",0,45);
        } else {
            if (isset($tesdoc['destin']) and is_array($tesdoc['destin'])) {
                $this->destinazione = $tesdoc['destin'];
            } elseif (isset($tesdoc['destin']) and is_string($tesdoc['destin'])) {
                $destino = preg_split("/[\r\n]+/i",$tesdoc['destin'],3);
                $this->destinazione = substr($destino[0],0,45);
                foreach ($destino as $key => $value) {
                    if ($key == 1){
                        $this->destinazione .= "\n".substr($value,0,45)."\n";
                    } elseif($key > 1) {
                        $this->destinazione .= substr(preg_replace("/[\r\n]+/i",' ',$value),0,45);
                    }
                }
            } else {
                $this->destinazione = '';
            }
        }

        $this->clientSedeLegale = ((trim($this->client['sedleg']) != '') ? preg_split("/\n/", trim($this->client['sedleg'])) : array());

        if (isset($tesdoc['c_a'])) {
           $this->c_Attenzione = $tesdoc['c_a'];
        } else {
           $this->c_Attenzione = '';
        }
        $this->client = $anagrafica->getPartner($tesdoc['clfoco']);
        $this->tesdoc = $tesdoc;
        $this->min = substr($tesdoc['initra'],14,2);
        $this->ora = substr($tesdoc['initra'],11,2);
        $this->day = substr($tesdoc['initra'],8,2);
        $this->month = substr($tesdoc['initra'],5,2);
        $this->year = substr($tesdoc['initra'],0,4);
        $this->trasporto=$tesdoc['traspo'];
        $this->testat = $testat;
    }

    function open_drawer() // apre il cassetto dell'eventuale registratore di cassa
    {
       if ($this->ecr) {
            if (!empty($this->ecr['driver'])) {
              $ticket_printer = new $this->ecr['driver'];
              @$ticket_printer->set_serial($this->ecr['serial_port']);
              @$ticket_printer->open_drawer();
            }
       }
    }

    function getTicketRow()
    {
        // in caso di scontrino il calcolo dev'essere fatto scorporando dal totale l'IVA
        $rs_rig = gaz_dbi_dyn_query("*", $this->gTables[$this->tableName], "id_tes = $this->testat", "id_rig asc");
        $this->totale=0;
        $results = array();
        while ($rigo = gaz_dbi_fetch_array($rs_rig)) {
            $rigo['importo']=0;
            $rigo['totale']=0;
            //calcolo importo rigo
            if ($rigo['tiprig'] <= 1) {     // se del tipo normale o forfait
                if ($rigo['tiprig'] == 0) { // tipo normale
                    $rigo['totale'] = CalcolaImportoRigo($rigo['quanti'], $rigo['prelis'],array($rigo['sconto'],$this->tesdoc['sconto'],-$rigo['pervat']));
                } else {                    // tipo forfait
                    $rigo['totale'] = CalcolaImportoRigo(1,$rigo['prelis'],-$rigo['pervat']);
                }
                $rigo['importo']=round($rigo['totale']/(1+$rigo['pervat']/100),2);
                if (!isset($this->castel[$rigo['codvat']])) {
                  $iva = gaz_dbi_get_row($this->gTables['aliiva'],"codice",$rigo['codvat']);
                  $this->castel[$rigo['codvat']]['iva']=0.00;
                  $this->castel[$rigo['codvat']]['descri']=$iva['descri'];
                  $this->castel[$rigo['codvat']]['importo']=0.00;
                }
                $this->castel[$rigo['codvat']]['importo']+=$rigo['importo'];
                $this->castel[$rigo['codvat']]['iva']+=$rigo['totale']-$rigo['importo'];
                $this->totale+=$rigo['totale'];
            }
            $results[] = $rigo;
        }
        //inoltre devo settare la descrizione del misuratore fiscale
        $this->ecr = gaz_dbi_get_row($this->gTables['cash_register'], 'id_cash',$this->tesdoc['id_contract']);

        return $results;
    }

    function getRigo()
    {
        $from =  $this->gTables[$this->tableName].' AS rows
                 LEFT JOIN '.$this->gTables['aliiva'].' AS vat
                 ON rows.codvat=vat.codice';
        $rs_rig = gaz_dbi_dyn_query('rows.*,vat.tipiva AS tipiva',$from, "rows.id_tes = ".$this->testat,"id_tes DESC, id_rig");
        $this->riporto =0.00;
        $this->ritenuta=0.00;
        $results = array();
        while ($rigo = gaz_dbi_fetch_array($rs_rig)) {
            if ($rigo['tiprig'] <= 1) {
                $rigo['importo'] = CalcolaImportoRigo($rigo['quanti'], $rigo['prelis'], $rigo['sconto']);
                $v_for_castle = CalcolaImportoRigo($rigo['quanti'], $rigo['prelis'], array($rigo['sconto'],$this->tesdoc['sconto']));
                if ($rigo['tiprig'] == 1) {
                    $rigo['importo'] = CalcolaImportoRigo(1,$rigo['prelis'],0);
                    $v_for_castle = CalcolaImportoRigo(1, $rigo['prelis'],$this->tesdoc['sconto']);
                }
                if (!isset($this->castel[$rigo['codvat']])) {
                    $this->castel[$rigo['codvat']] = 0;
                    if ($rigo['tipiva']!='C' && $rigo['tipiva']!='S' ) {
                       $this->decalc_castle[$rigo['codvat']] = 0.00;
                    }
                }
                $this->castel[$rigo['codvat']] += $v_for_castle;
                if ($rigo['tipiva']!='C' && $rigo['tipiva']!='S' ) {
                   $this->decalc_castle[$rigo['codvat']] += $v_for_castle;
                   $this->totimp_decalc += $v_for_castle;
                }
                $this->totimp_body += $rigo['importo'];
                $this->ritenuta += round($rigo['importo']*$rigo['ritenuta']/100,2);
                $this->totimp_doc += $v_for_castle;
            } elseif ($rigo['tiprig']>5 && $rigo['tiprig']<9) {
               $body_text = gaz_dbi_get_row($this->gTables['body_text'], "id_body",$rigo['id_body_text']);
               $rigo['descri'] = $body_text['body_text'];
            } elseif ($rigo['tiprig'] == 3) {
               $this->riporto += $rigo['prelis'];
            }
            $results[] = $rigo;
            //creo il castelletto IVA ma solo se del tipo normale o forfait
        }
        return $results;
    }

    function setTotal($totTrasporto=0)
    {
        $bolli = new Compute();
        $this->totivafat = 0.00;
        $this->totimpfat = 0.00;
        $this->totimpmer = 0.00;
        $this->tot_ritenute = $this->ritenuta;
        $this->impbol = 0.00;
        $this->totriport = $this->riporto;
        $this->speseincasso = $this->tesdoc['speban'] * $this->pagame['numrat'];
        $this->cast = array();
        if (!isset($this->decalc_castle)){
            $this->decalc_castle= array();
        }
        if (!isset($this->castel)){
            $this->castel= array();
        }
        if (!isset($this->totimp_body)){
            $this->totimp_body=0;
        }
        $this->totimpmer = $this->totimp_body;
        $this->totimp_body=0;
        if (!isset($this->totimp_doc)){
            $this->totimp_doc=0;
        }
        $this->totimpfat = $this->totimp_doc;
        $this->totimp_doc = 0;
        $somma_spese = $totTrasporto + $this->speseincasso + $this->tesdoc['spevar'];
        $last=count($this->castel);
        $acc_val=$somma_spese;
        foreach ($this->castel as $k=>$v) {
            $vat = gaz_dbi_get_row($this->gTables['aliiva'],"codice",$k);
            if (isset($this->decalc_castle[$k])) {
               if ($last == 1) {
                  $v += $acc_val;
                  $this->totimpfat += $acc_val;
               } else {
                  $decalc=round($somma_spese*$v/$this->totimpmer,2);
                  $v += $decalc;
                  $this->totimpfat += $decalc;
                  $acc_val-=$decalc;
               }
               $last--;
            }
            $ivacast = round($v*$vat['aliquo'])/ 100;
            $this->totivafat += $ivacast;
            $this->cast[$k]['impcast'] = $v;
            $this->cast[$k]['ivacast'] = $ivacast;
            $this->cast[$k]['descriz'] = $vat['descri'];
        }
        //se il pagamento e' del tipo TRATTA calcolo i bolli da addebitare per l'emissione dell'effetto
        if ($this->pagame['tippag'] == 'T' or $this->pagame['tippag'] == 'R') {
           if ($this->pagame['tippag'] == 'T') {
              $this->impbol = $bolli->stampTax($this->totimpfat+$this->totriport+$this->totivafat-$this->tot_ritenute, $this->tesdoc['stamp'],$this->tesdoc['round_stamp']*$this->pagame['numrat']);
           } elseif($this->pagame['tippag'] == 'R') {
              $this->impbol = $this->tesdoc['stamp'];
           }
        }
        $this->riporto=0;
        $this->ritenute=0;
        $this->castel = array();
        $this->decalc_castle= array();
    }
}


function createDocument($testata, $templateName, $gTables, $rows='rigdoc', $dest=false)
{
    $templates = array('Received' => 'received',
                       'CartaIntestata' => 'carta_intestata',
                       'Lettera' => 'lettera',
                       'FatturaAcquisto' => 'fattura_acquisto',
                       'FatturaImmediata' => 'fattura_immediata',
                       'Parcella' => 'parcella',
                       'PreventivoCliente' => 'preventivo_cliente',
                       'OrdineCliente' => 'ordine_cliente',
                       'OrdineWeb' => 'ordine_web',
                       'FatturaSemplice' => 'fattura_semplice',
                       'FatturaAllegata' => 'fattura_allegata',
                       'OrdineFornitore' => 'ordine_fornitore',
                       'PreventivoFornitore' => 'preventivo_fornitore',
                       'InformativaPrivacy' => 'informativa_privacy',
                       'DDT' => 'ddt'
                       );

    $config = new Config;
    $configTemplate = new configTemplate;
    require("../../config/templates".($configTemplate->template ? '.'.$configTemplate->template : '').'/'.$templates[$templateName].'.php');
    $pdf = new $templateName();
    $ecr=gaz_dbi_get_row($gTables['cash_register'],'adminid',$_SESSION['Login']);
    if (!empty($ecr['driver'])) {
         require("../../library/cash_register/".$ecr['driver'].".php");
         $ticket_printer = new $ecr['driver'];
    } else {
       $ecr=false;
    }
    $docVars = new DocContabVars();
    $docVars->setVars($gTables, $testata, $testata['id_tes'], $rows, $ecr);
    $pdf->setVars($docVars,$templateName);
    $pdf->setTesDoc();
    //$pdf->SetPageFormat($config->getValue('page_format'));
    $pdf->setCreator('GAzie - '.$docVars->intesta1);
    $pdf->setAuthor($docVars->user['Cognome'].' '.$docVars->user['Nome']);
    $pdf->setTitle($templateName);
    $pdf->setTopMargin(79);
    $pdf->setHeaderMargin(5);
    $pdf->Open();
    $pdf->AliasNbPages();
    $pdf->pageHeader();
    $pdf->compose();
    $pdf->pageFooter();
    if ($dest && $dest=='E'){ // è stata richiesta una e-mail
       $dest = 'S';     // Genero l'output pdf come stringa binaria
       // Costruisco oggetto con tutti i dati del file pdf da allegare
       $content->name = $docVars->intesta1.'_FatturaDifferita_n.'.$docVars->tesdoc['numfat'].'_del_'.gaz_format_date($docVars->tesdoc['datfat']).'.pdf';
       $content->string = $pdf->Output($docVars->intesta1.'_'.$templateName.'_n.'.$docVars->tesdoc['numdoc'].'_del_'.gaz_format_date($docVars->tesdoc['datemi']).'.pdf',$dest);
       $content->encoding = "base64";
       $content->mimeType = "application/pdf";
       $gMail = new GAzieMail();
       $gMail->sendMail($docVars->azienda,$docVars->user,$content,$docVars->client);
    } else { // va all'interno del browser
       $pdf->Output();
    }
}

function createMultiDocument($results, $templateName, $gTables)
{
    $templates = array('Received' => 'received',
                       'CartaIntestata' => 'carta_intestata',
                       'Lettera' => 'lettera',
                       'FatturaAcquisto' => 'fattura_acquisto',
                       'FatturaImmediata' => 'fattura_immediata',
                       'Parcella' => 'parcella',
                       'PreventivoCliente' => 'preventivo_cliente',
                       'OrdineCliente' => 'ordine_cliente',
                       'OrdineWeb' => 'ordine_web',
                       'FatturaSemplice' => 'fattura_semplice',
                       'FatturaAllegata' => 'fattura_allegata',
                       'OrdineFornitore' => 'ordine_fornitore',
                       'PreventivoFornitore' => 'preventivo_fornitore',
                       'InformativaPrivacy' => 'informativa_privacy',
                       'DDT' => 'ddt'
                       );
    $config = new Config;
    $configTemplate = new configTemplate;
    require("../../config/templates".($configTemplate->template ? '.'.$configTemplate->template : '').'/'.$templates[$templateName].'.php');
    $pdf = new $templateName();
    $docVars = new DocContabVars();
    //$pdf->SetPageFormat($config->getValue('page_format'));
    $pdf->SetTitle($templateName);
    $pdf->SetTopMargin(79);
    $pdf->SetHeaderMargin(5);
    $ctrlprotoc = 0;
    while ($tesdoc = gaz_dbi_fetch_array($results)) {
    //se il cliente non e' lo stesso di prima
            $ref=$tesdoc['protoc'];
            if ($templateName == 'DDT') {
               $ref=$tesdoc['numdoc'];
            }
            if ($ref <> $ctrlprotoc && $ctrlprotoc > 0 ) {
                $pdf->pageFooter();
            }
            // Inizio pagina
            $testat = $tesdoc['id_tes'];
            $docVars->setVars($gTables, $tesdoc, $testat, 'rigdoc');
            $pdf->setVars($docVars,$templateName);
            $pdf->setTesDoc();
            if ($ctrlprotoc == 0) {
                $pdf->setCreator('GAzie - '.$docVars->intesta1);
                $pdf->setAuthor($docVars->user['Cognome'].' '.$docVars->user['Nome']);
                $pdf->Open();
            }
            //aggiungo una pagina
            $pdf->pageHeader();
            $ctrlprotoc = $tesdoc['protoc'];
            if ($templateName == 'DDT') {
               $ctrlprotoc=$tesdoc['numdoc'];
            }
            $testat = $tesdoc['id_tes'];
            $pdf->docVars->setVars($gTables, $tesdoc, $testat, 'rigdoc');
            $pdf->compose();
    }
    $pdf->pageFooter();
    $pdf->Output();
}

function createInvoiceFromDDT($result,$gTables,$dest=false) {
    $config = new Config;
    $configTemplate = new configTemplate;
    require("../../config/templates".($configTemplate->template ? '.'.$configTemplate->template : '').'/fattura_semplice.php');
    $pdf = new FatturaSemplice();
    $docVars = new DocContabVars();
    //$pdf->SetPageFormat($config->getValue('page_format'));
    $pdf->SetTitle('Fatture Differite da DDT');
    $pdf->SetTopMargin(79);
    $pdf->SetHeaderMargin(5);
    $pdf->Open();
    $ctrlprotoc = 0;
    while ($tesdoc = gaz_dbi_fetch_array($result)) {
    //se il cliente non e' lo stesso di prima
        if ($tesdoc['protoc'] <> $ctrlprotoc) {
            //se non e' piu' lo stesso cliente e non e' il primo Ddt stampo il piede della fattura
            if ($ctrlprotoc <> 0) {
                $pdf->pageFooter();
            }
            // Inizio pagina
            // se non e' il tipo di documento stampabile da questo modulo ... va a casa
               if ($tesdoc['tipdoc'] <> 'FAD') {
                header("Location: report_docven.php");
                exit;
               }

            $testat = $tesdoc['id_tes'];
            $docVars->setVars($gTables, $tesdoc, $testat, 'rigdoc');
            $pdf->setVars($docVars);
            $pdf->setTesDoc();
            if ($ctrlprotoc == 0) {
                $pdf->setCreator('GAzie - '.$docVars->intesta1);
                $pdf->setAuthor($docVars->user['Cognome'].' '.$docVars->user['Nome']);
                $pdf->Open();
            }
            //aggiungo una pagina
            $pdf->pageHeader();
            $ctrlprotoc = $tesdoc['protoc'];
        }
        $testat = $tesdoc['id_tes'];
        $pdf->docVars->setVars($gTables, $tesdoc, $testat, 'rigdoc');
        $pdf->compose();
    }
    $pdf->pageFooter();
    if ($dest && $dest=='E'){ // è stata richiesta una e-mail
       $dest = 'S';     // Genero l'output pdf come stringa binaria
       // Costruisco oggetto con tutti i dati del file pdf da allegare
       $content->name = $docVars->intesta1.'_FatturaDifferita_n.'.$docVars->tesdoc['numfat'].'_del_'.gaz_format_date($docVars->tesdoc['datfat']).'.pdf';
       $content->string = $pdf->Output($docVars->intesta1.'_FatturaDifferita_n.'.$docVars->tesdoc['numfat'].'_del_'.gaz_format_date($docVars->tesdoc['datfat']).'.pdf',$dest);
       $content->encoding = "base64";
       $content->mimeType = "application/pdf";
       $gMail = new GAzieMail();
       $gMail->sendMail($docVars->azienda,$docVars->user,$content,$docVars->client);
    } else { // va all'interno del browser
       $pdf->Output();
    }
}
?>