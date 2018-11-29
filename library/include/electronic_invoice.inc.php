<?php

/* $
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

require("../../library/include/expiry_calc.php");

class invoiceXMLvars {

    function setXMLvars($gTables, $tesdoc, $testat, $tableName, $ecr = false) {
        $this->gTables = $gTables;
        $admin_aziend = gaz_dbi_get_row($gTables['aziend'], 'codice', $_SESSION['company_id']);
        $this->azienda = $admin_aziend;
        $this->pagame = gaz_dbi_get_row($gTables['pagame'], "codice", $tesdoc['pagame']);
        $this->banapp = gaz_dbi_get_row($gTables['banapp'], "codice", $tesdoc['banapp']);
        $anagrafica = new Anagrafica();
        $this->banacc = $anagrafica->getPartner($this->pagame['id_bank']);
        $this->vettor = gaz_dbi_get_row($gTables['vettor'], "codice", $tesdoc['vettor']);
        $this->tableName = $tableName;
        $this->intesta1 = $admin_aziend['ragso1'];
        $this->intesta1bis = $admin_aziend['ragso2'];
        $this->intesta2 = $admin_aziend['indspe'] . ' ' . sprintf("%05d", $admin_aziend['capspe']) . ' ' . $admin_aziend['citspe'] . ' (' . $admin_aziend['prospe'] . ')';
        $this->intesta3 = 'Tel.' . $admin_aziend['telefo'] . ' ';
        $this->aziendTel = $admin_aziend['telefo'];
        $this->aziendFax = $admin_aziend['fax'];
        $this->codici = '';
        if ($admin_aziend['codfis'] != '') {
            $this->codici .= 'C.F. ' . $admin_aziend['codfis'] . ' ';
        }
        if ($admin_aziend['pariva']) {
            $this->codici .= 'P.I. ' . $admin_aziend['pariva'] . ' ';
        }
        if ($admin_aziend['rea']) {
            $this->codici .= 'R.E.A. ' . $admin_aziend['rea'];
        }
        if ($tesdoc['template'] == 'FatturaImmediata') {
            $this->sempl_accom = true;
        } else {
            $this->sempl_accom = false;
        }
        $this->intesta4 = $admin_aziend['e_mail'];
        $this->intesta5 = $admin_aziend['sexper'];
        if ($admin_aziend['sexper'] == 'G') {
            $this->TipoRitenuta = 'RT02';
        } else {
            $this->TipoRitenuta = 'RT01';
        }

        $this->colore = $admin_aziend['colore'];
        $this->decimal_quantity = $admin_aziend['decimal_quantity'];
        $this->decimal_price = $admin_aziend['decimal_price'];
        $this->logo = $admin_aziend['image'];
        $this->link = $admin_aziend['web_url'];
        $this->perbollo = 0;
        $this->iva_bollo = gaz_dbi_get_row($gTables['aliiva'], "codice", $admin_aziend['taxstamp_vat']);
        $this->client = $anagrafica->getPartner($tesdoc['clfoco']);
        $this->cliente1 = $this->client['ragso1'];
        $this->cliente2 = $this->client['ragso2'];
        $this->cliente3 = $this->client['indspe'];
		$this->pec_email = $this->client['pec_email'];
        if (!empty($this->client['citspe'])) {
            $this->cliente4 = sprintf("%05d", $this->client['capspe']) . ' ' . strtoupper($this->client['citspe']) . ' ' . strtoupper($this->client['prospe']);
        } else {
            $this->cliente4 = '';
        }
        $country = gaz_dbi_get_row($gTables['country'], "iso", $this->client['country']);
        if ($this->client['country'] != 'IT') {
            $this->cliente4b = strtoupper($country['istat_name']);
        } else {
            $this->cliente4b = '';
        }
        if (!empty($this->client['pariva'])) {
            $this->cliente5 = 'P.I. ' . $this->client['pariva'] . ' ';
        } else {
            $this->cliente5 = '';
        }
        if (!empty($this->client['pariva'])) { //se c'e' la partita iva
            if (!empty($this->client['codfis']) and $this->client['codfis'] == $this->client['pariva']) {
                $this->cliente5 = 'C.F. e P.I. ' . $this->client['codfis'];
            } elseif (!empty($this->client['codfis']) and $this->client['codfis'] != $this->client['pariva']) {
                $this->cliente5 = 'C.F. ' . $this->client['codfis'] . ' P.I. ' . $this->client['pariva'];
            } else { //per es. se non c'e' il codice fiscale
                $this->cliente5 = ' P.I. ' . $this->client['pariva'];
            }
        } else { //se  NON c'e' la partita iva
            $this->cliente5 = '';
            if (!empty($this->client['codfis'])) {
                $this->cliente5 = 'C.F. ' . $this->client['codfis'];
            }
        }
        // variabile e' sempre un array
        $this->id_agente = gaz_dbi_get_row($gTables['agenti'], 'id_agente', $tesdoc['id_agente']);
        $this->rs_agente = $anagrafica->getPartner($this->id_agente['id_fornitore']);
        $this->name_agente = substr($this->rs_agente['ragso1'] . " " . $this->rs_agente['ragso2'], 0, 47);
        if ((isset($tesdoc['id_des_same_company'])) and ( $tesdoc['id_des_same_company'] > 0)) {
            $this->partner_dest = gaz_dbi_get_row($gTables['destina'], 'codice', $tesdoc['id_des_same_company']);
            $this->destinazione = substr($this->partner_dest['unita_locale1'] . " " . $this->partner_dest['unita_locale2'], 0, 45);
            $this->destinazione .= "\n" . substr($this->partner_dest['indspe'], 0, 45);
            $this->destinazione .= "\n" . substr($this->partner_dest['capspe'] . " " . $this->partner_dest['citspe'] . " (" . $this->partner_dest['prospe'] . ")", 0, 45);
        } elseif ((isset($tesdoc['id_des'])) and ( $tesdoc['id_des'] > 0)) {
            $this->partner_dest = $anagrafica->getPartnerData($tesdoc['id_des']);
            $this->destinazione = substr($this->partner_dest['ragso1'] . " " . $this->partner_dest['ragso2'], 0, 45);
            $this->destinazione .= "\n" . substr($this->partner_dest['indspe'], 0, 45);
            $this->destinazione .= "\n" . substr($this->partner_dest['capspe'] . " " . $this->partner_dest['citspe'] . " (" . $this->partner_dest['prospe'] . ")", 0, 45);
        } else {
            if (isset($tesdoc['destin']) and is_array($tesdoc['destin'])) {
                $this->destinazione = $tesdoc['destin'];
            } elseif (isset($tesdoc['destin']) and is_string($tesdoc['destin'])) {
                $destino = preg_split("/[\r\n]+/i", $tesdoc['destin'], 3);
                $this->destinazione = substr($destino[0], 0, 45);
                foreach ($destino as $key => $value) {
                    if ($key == 1) {
                        $this->destinazione .= "\n" . substr($value, 0, 45) . "\n";
                    } elseif ($key > 1) {
                        $this->destinazione .= substr(preg_replace("/[\r\n]+/i", ' ', $value), 0, 45);
                    }
                }
            } else {
                $this->destinazione = '';
            }
        }
        $this->vettore = false;
		if ($tesdoc['vettor']>0){
			$this->vettore = gaz_dbi_get_row($gTables['vettor'], "codice", $tesdoc['vettor']);
		}
        $this->clientSedeLegale = ((trim($this->client['sedleg']) != '') ? preg_split("/\n/", trim($this->client['sedleg'])) : array());
        $this->client = $anagrafica->getPartner($tesdoc['clfoco']);
        $this->tesdoc = $tesdoc;
        $this->expense_pervat = gaz_dbi_get_row($gTables['aliiva'], "codice", $this->tesdoc['expense_vat']);
        $this->min = substr($tesdoc['initra'], 14, 2);
        $this->ora = substr($tesdoc['initra'], 11, 2);
        $this->day = substr($tesdoc['initra'], 8, 2);
        $this->month = substr($tesdoc['initra'], 5, 2);
        $this->year = substr($tesdoc['initra'], 0, 4);
        $this->trasporto = $tesdoc['traspo'];
        $this->testat = $testat;
        $this->ddt_data = false;

        $this->TipoDocumento = 'TD01';    // <TipoDocumento> 2.1.1.1
        $this->docRelNum = $this->tesdoc["numdoc"];    // Numero del documento relativo
        $this->docRelDate = $this->tesdoc["datemi"];    // Data del documento relativo

        switch ($tesdoc["tipdoc"]) {
            case "FAD":
                $this->ddt_data = true;
                $this->docRelNum = $this->tesdoc["numfat"];
                $this->docRelDate = $this->tesdoc["datfat"];
                break;
            case "FAI":
                $this->docRelNum = $this->tesdoc["numfat"];
                $this->docRelDate = $this->tesdoc["datfat"];
                break;
            case "FNC":
                $this->TipoDocumento = 'TD04';    // <TipoDocumento> 2.1.1.1
                $this->docRelNum = $this->tesdoc["numfat"];
                $this->docRelDate = $this->tesdoc["datfat"];
                break;
            case "FND":
                $this->TipoDocumento = 'TD05';    // <TipoDocumento> 2.1.1.1
                $this->docRelNum = $this->tesdoc["numfat"];
                $this->docRelDate = $this->tesdoc["datfat"];
                break;
            case "FAP":
                $this->TipoDocumento = 'TD06';    // <TipoDocumento> 2.1.1.1
                $this->docRelNum = $this->tesdoc["numfat"];
                $this->docRelDate = $this->tesdoc["datfat"];
                break;
            case "DDT":
            case "DDL":
            case "DDR":
            default:
                $this->ddt_data = true;
                $this->docRelNum = $this->tesdoc["numdoc"];    // Numero del documento relativo
                $this->docRelDate = $this->tesdoc["datemi"];    // Data del documento relativo
        }

        $this->protoc = $this->tesdoc["protoc"];
        $this->seziva = $this->tesdoc["seziva"];
        $this->docYear = substr($this->tesdoc["datemi"], 0, 4);    // Anno del documento
        // aggiungo l'eventuale intermediario in caso di installazione "da commercialista"
        $intermediary_code = gaz_dbi_get_row($gTables['config'], 'variable', 'intermediary');
        if ($intermediary_code['cvalue'] > 0) {
            $intermediary = gaz_dbi_get_row($gTables['aziend'], 'codice', $intermediary_code['cvalue']);
            $this->IdCodice = $intermediary['codfis'];
            $this->Intermediary = $intermediary['codice'];
        } else {
            $this->IdCodice = $admin_aziend['codfis'];
            $this->Intermediary = false;
        }
        $this->totimp_body = 0;
        $this->totimp_decalc = 0;
        $this->totimp_doc = 0;
    }

    function getXMLrows() {
        $this->tot_trasporto += $this->trasporto;
        if ($this->taxstamp < 0.01 && $this->tesdoc['taxstamp'] >= 0.01) {
            $this->taxstamp = $this->tesdoc['taxstamp'];
        }
        $from = $this->gTables[$this->tableName] . ' AS rows
                 LEFT JOIN ' . $this->gTables['aliiva'] . ' AS vat
                 ON rows.codvat=vat.codice';
        $rs_rig = gaz_dbi_dyn_query('rows.*,vat.tipiva AS tipiva, vat.fae_natura AS natura', $from, "rows.id_tes = " . $this->testat, "id_tes DESC, id_rig");
        $this->riporto = 0.00;
        $this->ritenuta = 0.00;
        $this->cassa_prev = array();
        $righiDescrittivi = array();
        $last_normal_row = 0;
        $nr = 1;
        $results = array();
        $dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
        while ($rigo = gaz_dbi_fetch_array($rs_rig)) {
            $rigo['imp_sconto'] = 0.00;
            if ($rigo['tiprig'] <= 1) {
                $last_normal_row = $nr; // mi potrebbe servire se alla fine dei righi mi ritrovo con dei descrittivi non ancora indicizzati perché seguono l'ultimo rigo normale
                // se ho avuto dei righi descrittivi che hanno preceduto  questo allora li inputo a questo rigo
                if (isset($righiDescrittivi[0])) {
                    foreach ($righiDescrittivi[0] as $v) {
                        $righiDescrittivi[$nr][] = $v; // faccio il push su un array indicizzato con $nr (numero rigo)
                    }
                }
                unset($righiDescrittivi[0]); // svuoto l'array per prepararlo ad eventuali nuovi righi descrittivi
                $rigo['importo'] = CalcolaImportoRigo($rigo['quanti'], $rigo['prelis'], $rigo['sconto']);
                $v_for_castle = CalcolaImportoRigo($rigo['quanti'], $rigo['prelis'], array($rigo['sconto'], $this->tesdoc['sconto']));
                if ($rigo['tiprig'] == 1) {
                    $rigo['importo'] = CalcolaImportoRigo(1, $rigo['prelis'], 0);
                    $v_for_castle = CalcolaImportoRigo(1, $rigo['prelis'], $this->tesdoc['sconto']);
					$rigo['quanti']=1;
                }
				$rigo['imp_sconto'] = number_format((CalcolaImportoRigo($rigo['quanti'], $rigo['prelis'], 0) - $rigo['importo'])/$rigo['quanti'], 2, '.', ''); // l'elemento <Importo> in <ScontoMaggiorazione>  è relativo ad una unità e non all'intero rigo
                if (!isset($this->castel[$rigo['codvat']])) {
                    $this->castel[$rigo['codvat']] = 0;
                }
                if (!isset($this->body_castle[$rigo['codvat']])) {
                    $this->body_castle[$rigo['codvat']]['impcast'] = 0;
                }
                $this->body_castle[$rigo['codvat']]['impcast'] += $v_for_castle;
                $this->castel[$rigo['codvat']] += $v_for_castle;
                $this->totimp_body += $rigo['importo'];
                $this->ritenuta += round($rigo['importo'] * $rigo['ritenuta'] / 100, 2);
                $this->totimp_doc += $v_for_castle;
                // aggiungo all'accumulatore l'eventuale iva non esigibile (split payment PA)   
                if ($rigo['tipiva'] == 'T') {
                    $this->ivasplitpay += round(($v_for_castle * $rigo['pervat']) / 100, 2);
                }
            } elseif ($rigo['tiprig'] == 2) { // descrittivo
                // faccio prima il parsing XML e poi il push su un array ancora da indicizzare (0)
                $righiDescrittivi[0][] = htmlspecialchars($rigo['descri'], ENT_XML1 | ENT_QUOTES, 'UTF-8', true);
            } elseif ($rigo['tiprig'] == 4) { // cassa previdenziale
                if (!isset($this->castel[$rigo['codvat']])) {
                    $this->castel[$rigo['codvat']] = 0;
                }
                if (!isset($this->body_castle[$rigo['codvat']])) {
                    $this->body_castle[$rigo['codvat']]['impcast'] = 0;
                }
                $rigo['importo'] = round($rigo['provvigione']*$rigo['prelis']/100,2);
                $v_for_castle = $rigo['importo'] ;
                $this->body_castle[$rigo['codvat']]['impcast'] += $v_for_castle;
                $this->castel[$rigo['codvat']] += $v_for_castle;
                $this->totimp_body += $rigo['importo'];
                $this->ritenuta += round($rigo['importo'] * $rigo['ritenuta'] / 100, 2);
                $this->totimp_doc += $v_for_castle;
                // aggiungo all'accumulatore l'eventuale iva non esigibile (split payment PA)   
                if ($rigo['tipiva'] == 'T') {
                    $this->ivasplitpay += round(($v_for_castle * $rigo['pervat']) / 100, 2);
                }
				/* con codart valorizzo l'elemento <TipoCassa> e creo l'array che mi servirà per generare gli elementi <DatiCassaPrevidenziale> */
                if (!isset($this->cassa_prev[$rigo['codart']])) { // se il tipo cassa non ce l'ho 
                    $this->cassa_prev[$rigo['codart']] = array('AlCassa'=>$rigo['provvigione'],'ImportoContributoCassa'=>$rigo['importo'],'ImponibileCassa'=>$rigo['prelis'],'AliquotaIVA'=>$rigo['pervat'],'Ritenuta'=>$rigo['ritenuta'],'Natura'=>$rigo['natura']);
                } else { // ho già l'elemento <TipoCassa>
					$this->cassa_prev[$rigo['codart']]['ImponibileCassa'] +=$rigo['prelis'];
					$this->cassa_prev[$rigo['codart']]['ImportoContributoCassa'] +=$rigo['importo'];
				}
            } elseif ($rigo['tiprig'] == 6 || $rigo['tiprig'] == 8) {
                $body_text = gaz_dbi_get_row($this->gTables['body_text'], "id_body", $rigo['id_body_text']);
                $dom->loadHTML($body_text['body_text']);
                $rigo['descri'] = strip_tags($dom->saveXML());
                $res = explode("\n", wordwrap($rigo['descri'], 60, "\n"));
                // faccio il push ricorsivo su un array ancora da indicizzare (0)
                foreach ($res as $v) {
                    $ctrl_v = trim($v);
                    if (!empty($ctrl_v)) {
                        $righiDescrittivi[0][] = $v;
                    }
                }
            } elseif ($rigo['tiprig'] == 3) {  // var.totale fattura
                $this->riporto += $rigo['prelis'];
            }
            $results[$nr] = $rigo;
            $nr++;
            //creo il castelletto IVA ma solo se del tipo normale o forfait
        }
        /* se finiti i righi ho incontrato dei descrittivi che non sono stati
          imputati a dei righi normali perché successivi a questi allora
          li imputo all'ultimo normale incontrato
         */
        if (isset($righiDescrittivi[0])) {
            foreach ($righiDescrittivi[0] as $v) {
                $righiDescrittivi[$last_normal_row][] = $v; // faccio il push su un array indicizzato con $nr (numero rigo)
            }
        }
        unset($righiDescrittivi[0]);
        // se ho dei trasporti lo aggiungo ai righi del relativo DdT
        if ($this->trasporto >= 0.1) {
            $rigo_T = array('tiprig' => 'T', 'descri' => 'TRASPORTO',
                'importo' => $this->trasporto,
                'pervat' => $this->expense_pervat['aliquo'],
                'ritenuta' => 0,
                'natura' => $this->expense_pervat['fae_natura']);
            $results[$nr] = $rigo_T;
            $nr++;
        }

        foreach ($results as $k => $v) { // associo l'array dei righi descrittivi con quello del righo corrispondente
            $r[$k] = $v;
            if (isset($righiDescrittivi[$k])) {
                $r[$k]['descrittivi'] = $righiDescrittivi[$k];
            }
        }
        return $r;
    }

    function setXMLtot() {
        $calc = new Compute();
        $this->totivafat = 0.00;
        $this->totimpfat = 0.00;
        $this->totimpmer = 0.00;
        $this->tot_ritenute = $this->ritenuta;
        $this->virtual_taxstamp = $this->tesdoc['virtual_taxstamp'];
        $this->impbol = 0.00;
        $this->BolloVirtuale = ''; // ovviamente il bollo potrà essere solo virtuale ma comunque lo setto per evidenziare l'errore
        if ($this->tesdoc['virtual_taxstamp'] == 2 || $this->tesdoc['virtual_taxstamp'] == 3) { // bollo virtualmente assolto
            $this->BolloVirtuale = 'SI';
        }
        $this->totriport = $this->riporto;
        $this->speseincasso = $this->tesdoc['speban'] * $this->pagame['numrat'];
        if (!isset($this->castel)) {
            $this->castel = array();
        }
        if (!isset($this->totimp_body)) {
            $this->totimp_body = 0;
        }
        $this->totimpmer = $this->totimp_body;
        $this->totimp_body = 0;
        if (!isset($this->totimp_doc)) {
            $this->totimp_doc = 0;
        }
        $this->totimpfat = $this->totimp_doc;
        $this->totimp_doc = 0;
        $somma_spese = $this->tot_trasporto + $this->speseincasso + $this->tesdoc['spevar'];
        $calc->add_value_to_VAT_castle($this->body_castle, $somma_spese, $this->tesdoc['expense_vat']);
        if ($this->tesdoc['stamp'] > 0) {
            $calc->payment_taxstamp($calc->total_imp + $this->totriport + $calc->total_vat - $this->tot_ritenute + $this->taxstamp - $this->ivasplitpay, $this->tesdoc['stamp'], $this->tesdoc['round_stamp'] * $this->pagame['numrat']);
            $this->impbol = $calc->pay_taxstamp;
        }
        $this->totimpfat = $calc->total_imp;
        $this->totivafat = $calc->total_vat;
        // aggiungo gli eventuali bolli al castelletto
        $chk_taxstamp = true;
        if ($this->virtual_taxstamp == 0 || $this->virtual_taxstamp == 3) { //  se è a carico dell'emittente non lo aggiungo al castelletto IVA
            $chk_taxstamp = false;
        }
        if ($this->impbol >= 0.01 || ($this->taxstamp >= 0.01 && $chk_taxstamp)) {
            $this->impbol += $this->taxstamp;
            $calc->add_value_to_VAT_castle($calc->castle, $this->impbol, $this->azienda['taxstamp_vat']);
        } elseif (!$chk_taxstamp) { // bollo da non addebitare ma esistente
            $this->impbol = $this->taxstamp;
        }
        $this->cast = $calc->castle;
        $this->riporto = 0;
        $this->ritenute = 0;
    }

    function encodeSendingNumber($data, $b = 62) {
        /* questa funzione mi serve per convertire un numero decimale in uno a base 36
          -- SCHEMA DEI DATI PER INVIO TRAMITE INTERMEDIARIO $data[intemediary] =true --
          |   SEZIONE IVA   |  ANNO DOCUMENTO  |  CODICE AZIENDA  | NUMERO PROTOCOLLO |
          |     INT (1)     |      INT(1)      |       INT(2)     |      INT(4)       |
          |        3        |        9         |         99       |      9999         |
          | $data[sezione]  |   $data[anno]    |  $data[azienda]  | $data[protocollo] |
          ------------------------------------------------------------------------------
          --- SCHEMA DEI DATI PER INVIO SENZA INTERMEDIARIO $data[intemediary]=false ---
          |   SEZIONE IVA   |  ANNO DOCUMENTO  |         NUMERO PROTOCOLLO            |
          |     INT (1)     |      INT(1)      |                INT(6)                |
          |        3        |        9         |                999999                |
          | $data[sezione]  |   $data[anno]    |           $data[protocollo]          |
          ------------------------------------------------------------------------------
         */
        $num = $data['sezione'] . substr($data['anno'], 3, 1);
        if ($data['intermediary']) {
            $num .= substr(str_pad($data['azienda'], 2, '0', STR_PAD_LEFT), 0, 2) . substr(str_pad($data['protocollo'], 4, '0', STR_PAD_LEFT), -4);
        } else {
            $num .= substr(str_pad($data['protocollo'], 6, '0', STR_PAD_LEFT), -6);
        }
        $num = intval($num);
        $base = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $r = $num % $b;
        $res = $base[$r];
        $q = floor($num / $b);
        while ($q) {
            $r = $q % $b;
            $q = floor($q / $b);
            $res = $base[$r] . $res;
        }
        return $res;
    }

    function decodeFromSendingNumber($num, $b = 62) {
        $base = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $limit = strlen($num);
        $res = strpos($base, $num[0]);
        for ($i = 1; $i < $limit; $i++) {
            $res = $b * $res + strpos($base, $num[$i]);
        }
        return $res;
    }

}

function create_XML_invoice($testata, $gTables, $rows = 'rigdoc', $dest = false, $name_ziparchive = false) {
    $XMLvars = new invoiceXMLvars();
    $domDoc = new DOMDocument;
	$domDoc->preserveWhiteSpace = false;
	$domDoc->formatOutput = true;
    $ctrl_doc = 0;
    $ctrl_fat = 0;
    $n_linea = 1;
    // definisco le variabili dei totali 
    $XMLvars->totimp_body = 0;
    $XMLvars->taxstamp = 0;
    $XMLvars->virtual_taxstamp = 0;
    $XMLvars->tot_trasporto = 0;
    $XMLvars->body_castle = array();
    $XMLvars->ivasplitpay = 0.00;

    while ($tesdoc = gaz_dbi_fetch_array($testata)) {
        $XMLvars->setXMLvars($gTables, $tesdoc, $tesdoc['id_tes'], $rows, false);
        $cod_destinatario=trim($XMLvars->client['fe_cod_univoco']); // elemento 1.1.4
		if ($ctrl_fat <>$XMLvars->tesdoc['numfat']) {
			// stabilisco quale template dovrò usare ad ogni cambio di fattura
			if ($XMLvars->docYear <= 2016) { // FAttura Elettronica PA fino al 2016
				$domDoc->load("../../library/include/template_fae.xml");
				$XMLvars->FormatoTrasmissione='FPA';
			} elseif (strlen($cod_destinatario)<=0 || strlen($cod_destinatario)>=7) { // FAttura Elettronica Privati
				$domDoc->load("../../library/include/template_fae_FPR12.xml");
				$XMLvars->FormatoTrasmissione='FPR';
			} else { // FAttura Elettronica PA a partire dal 2017
				$domDoc->load("../../library/include/template_fae_FPA12.xml");
				$XMLvars->FormatoTrasmissione='FPA';
			}
			$xpath = new DOMXPath($domDoc);
		}
        // controllo se ho un ufficio diverso da quello di base
        if (isset($tesdoc['id_des_same_company']) && $tesdoc['id_des_same_company'] > 0) {
            $dest = gaz_dbi_get_row($gTables['destina'], 'codice', $tesdoc['id_des_same_company']);
            $cod_destinatario=trim($dest['fe_cod_ufficio']); // elemento 1.1.4
            $XMLvars->client['fe_cod_univoco']=$cod_destinatario;
        }
        if ($ctrl_doc == 0) {
            $id_progressivo = substr($XMLvars->docRelDate, 2, 2) . $XMLvars->seziva . str_pad($XMLvars->protoc, 7, '0', STR_PAD_LEFT);
            //per il momento sono singole chiamate xpath a regime e' possibile usare un array associativo da passare ad una funzione
            $results = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione/IdTrasmittente/IdPaese")->item(0);
            $attrVal = $domDoc->createTextNode('IT');
            $results->appendChild($attrVal);


            $results = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione/ProgressivoInvio")->item(0);
            $attrVal = $domDoc->createTextNode($id_progressivo);
            $results->appendChild($attrVal);

		   //Il formato della trasmissione è encodato nei file 	
           // $results = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione/FormatoTrasmissione")->item(0);
           // $attrVal = $domDoc->createTextNode("SDI11");
           // $results->appendChild($attrVal);

            $codice_trasmittente = $XMLvars->IdCodice;
            $results = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione/IdTrasmittente/IdCodice")->item(0);
            $attrVal = $domDoc->createTextNode($codice_trasmittente);
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/IdFiscaleIVA/IdPaese")->item(0);
            $attrVal = $domDoc->createTextNode("IT");
            $results->appendChild($attrVal);

            //il IdCodice iva e' la partita iva?
            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/IdFiscaleIVA/IdCodice")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['pariva']));
            $results->appendChild($attrVal);

            //nodo 1.2.1.2 Codice Fiscale richiesto da alcune amministrazioni come obbligatorio 
            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/CodiceFiscale")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['codfis']));
            $results->appendChild($attrVal);

			if ($XMLvars->FormatoTrasmissione == "FPA") {
				//nodo 1.1.4
				$results = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione/CodiceDestinatario")->item(0);
				$attrVal = $domDoc->createTextNode(trim($XMLvars->client['fe_cod_univoco']));
				$results->appendChild($attrVal);
			} else {
				if (strlen($cod_destinatario) == "0") {
					$results = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione/CodiceDestinatario")->item(0);
					$attrVal = $domDoc->createTextNode("0000000");
					$results->appendChild($attrVal);
					//nodo 1.1.6
					$el = $domDoc->createElement("PECDestinatario", trim($XMLvars->client['pec_email']));
					$results1 = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione")->item(0);
					$results1->appendChild($el);
				} else {
					$results = $xpath->query("//FatturaElettronicaHeader/DatiTrasmissione/CodiceDestinatario")->item(0);
					$attrVal = $domDoc->createTextNode(trim($XMLvars->client['fe_cod_univoco']));
					$results->appendChild($attrVal);
                }							
			}	


            $el = $domDoc->createElement("CodiceFiscale", trim($XMLvars->client['codfis']));
            $results = $xpath->query("//CessionarioCommittente/DatiAnagrafici")->item(0);
            $results1 = $xpath->query("//CessionarioCommittente/DatiAnagrafici/Anagrafica")->item(0);
            $results->insertBefore($el, $results1);


            // nodo 1.4.1.1 partita IVA del committente, se disponibile
            if (!empty($XMLvars->client['pariva'])) {
                $el = $domDoc->createElement("IdFiscaleIVA", '');
                $results = $el->appendChild($domDoc->createElement('IdPaese', 'IT'));
                $results = $el->appendChild($domDoc->createElement('IdCodice', $XMLvars->client['pariva']));
                $results = $xpath->query("//CessionarioCommittente/DatiAnagrafici")->item(0);
                $results1 = $xpath->query("//CessionarioCommittente/DatiAnagrafici/CodiceFiscale")->item(0);
                $results->insertBefore($el, $results1);
            }


            $results = $xpath->query("//CessionarioCommittente/DatiAnagrafici/Anagrafica/Denominazione")->item(0);
            $attrVal = $domDoc->createTextNode(substr(trim($XMLvars->client['ragso1']) . " " . trim($XMLvars->client['ragso2']), 0, 80));
            $results->appendChild($attrVal);

            $results = $xpath->query("//CessionarioCommittente/Sede/Indirizzo")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->client['indspe']));
            $results->appendChild($attrVal);


            $el = $domDoc->createElement("Provincia", strtoupper(trim($XMLvars->client['prospe'])));
            $results = $xpath->query("//CessionarioCommittente/Sede")->item(0);
            $results1 = $xpath->query("//CessionarioCommittente/Sede/Nazione")->item(0);
            $results->insertBefore($el, $results1);


            $results = $xpath->query("//CessionarioCommittente/Sede/Comune")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->client['citspe']));
            $results->appendChild($attrVal);

            $results = $xpath->query("//CessionarioCommittente/Sede/CAP")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->client['capspe']));
            $results->appendChild($attrVal);

            $results = $xpath->query("//CessionarioCommittente/Sede/Nazione")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->client['country']));
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/TipoDocumento")->item(0);
            $attrVal = $domDoc->createTextNode($XMLvars->TipoDocumento);
            $results->appendChild($attrVal);

            //sempre in euro?
            $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/Divisa")->item(0);
            $attrVal = $domDoc->createTextNode("EUR");
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/Data")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->docRelDate));
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/Numero")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->docRelNum));
            $results->appendChild($attrVal);


            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Denominazione")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->intesta1 . " " . $XMLvars->intesta1bis));
            $results->appendChild($attrVal);

            //regime fiscale RF01 valido per il regime fiscale ordinario
            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/RegimeFiscale")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['fiscal_reg']));
            $results->appendChild($attrVal);


            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Indirizzo")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['indspe']));
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/CAP")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['capspe']));
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Comune")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['citspe']));
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Provincia")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['prospe']));
            $results->appendChild($attrVal);

            $results = $xpath->query("//FatturaElettronicaHeader/CedentePrestatore/Sede/Nazione")->item(0);
            $attrVal = $domDoc->createTextNode(trim($XMLvars->azienda['country']));
            $results->appendChild($attrVal);
        } elseif ($ctrl_doc <> $XMLvars->docRelNum) { // quando cambia il DdT
            /*
              in caso di necessità qui potrò aggiungere linee di codice
             */
        }
        //elenco beni in fattura  
        $lines = $XMLvars->getXMLrows();
        $cig = "";
        $cup = "";
        $id_documento = "";
		foreach ($lines AS $key => $rigo) {
            $nl = false;
            switch ($rigo['tiprig']) {
                case "0":       // normale
					$benserv = $xpath->query("//FatturaElettronicaBody/DatiBeniServizi")->item(0);
                    $el = $domDoc->createElement("DettaglioLinee", "");
                    $el1 = $domDoc->createElement("NumeroLinea", $n_linea);
                    $el->appendChild($el1);
                    if (isset($rigo['descrittivi'])) {
                        // se ho dei righi descrittivi associati li posso aggiungere fino a che la lunghezza non superi 1000 caratteri quindi ne posso aggiungere al massimo 15*60
                        foreach ($rigo['descrittivi'] as $k => $v) {
                            if ($k < 16) {
                                $rigo['descri'] .= $v; // ogni $v è lungo al massimo 60 caratteri
                                unset($rigo['descrittivi'][$k]); // lo tolgo in modo da mettere un eventuale accesso sotto
                            }
                        }
                    }
                    $el1 = $domDoc->createElement("Descrizione",  substr($rigo['descri'], -1000));
                    $el->appendChild($el1);
                    $el1 = $domDoc->createElement("Quantita", number_format($rigo['quanti'], 2, '.', ''));
                    $el->appendChild($el1);
                    $el1 = $domDoc->createElement("UnitaMisura", $rigo['unimis']);
                    $el->appendChild($el1);
                    $el1 = $domDoc->createElement("PrezzoUnitario", number_format($rigo['prelis'], $XMLvars->decimal_price, '.', ''));
                    $el->appendChild($el1);
                    // sconto/maggiorazione rigo 2.2.1.10
                    if (abs($rigo['imp_sconto']) >= 0.01) {
                        $el1 = $domDoc->createElement("ScontoMaggiorazione", "");
                        $el->appendChild($el1);
                        if ($rigo['imp_sconto'] < 0) { // è una maggiorazione
                            $t = 'MG';
                        } else {
                            $t = 'SC';
                        }
                        $el2 = $domDoc->createElement("Tipo", $t);
                        $el1->appendChild($el2);
                        $el2 = $domDoc->createElement("Percentuale", number_format(abs($rigo['sconto']), 2, '.', ''));
                        $el1->appendChild($el2);
						//promemoria Importo non dovrebbe riferirsi al totale dello sconto ma allo sconto per singolo PrezzoUnitario
                        $el2 = $domDoc->createElement("Importo", number_format(abs($rigo['imp_sconto']), 2, '.', ''));
                        $el1->appendChild($el2);
                    }
                    $el1 = $domDoc->createElement("PrezzoTotale", number_format($rigo['importo'], 2, '.', ''));
                    $el->appendChild($el1);
                    $el1 = $domDoc->createElement("AliquotaIVA", number_format($rigo['pervat'], 2, '.', ''));
                    $el->appendChild($el1);
                    if ($rigo['ritenuta'] > 0) {
                        $el1 = $domDoc->createElement("Ritenuta", 'SI');
                        $el->appendChild($el1);
                    }
                    if ($rigo['pervat'] <= 0) {
                        $el1 = $domDoc->createElement("Natura", $rigo['natura']);
                        $el->appendChild($el1);
                    }
                    if (isset($rigo['descrittivi']) && count($rigo['descrittivi']) > 0) {
                        foreach ($rigo['descrittivi'] as $k => $v) {
                            $el1 = $domDoc->createElement("AltriDatiGestionali", '');
                            $el->appendChild($el1);
                            $el2 = $domDoc->createElement("TipoDato", 'txt' . $k);
                            $el1->appendChild($el2);
                            $el2 = $domDoc->createElement("RiferimentoTesto", $v);
                            $el1->appendChild($el2);
                        }
                    }
                    $benserv->appendChild($el);
                    $nl = true;
                    break;

                case "1":
                case "T":       // forfait o trasporto
					$benserv = $xpath->query("//FatturaElettronicaBody/DatiBeniServizi")->item(0);
                    $el = $domDoc->createElement("DettaglioLinee", "");
                    $el1 = $domDoc->createElement("NumeroLinea", $n_linea);
                    $el->appendChild($el1);
                    if (isset($rigo['descrittivi'])) {
                        // se ho dei righi descrittivi associati li posso aggiungere fino a che la lunghezza non superi 1000 caratteri quindi ne posso aggiungere al massimo 15*60
                        foreach ($rigo['descrittivi'] as $k => $v) {
                            if ($k < 16) {
                                $rigo['descri'] .= $v; // ogni $v è lungo al massimo 60 caratteri
                                unset($rigo['descrittivi'][$k]); // lo tolgo in modo da mettere un eventuale accesso sotto
                            }
                        }
                    }
                    $el1 = $domDoc->createElement("Descrizione", substr($rigo['descri'], -1000));
                    $el->appendChild($el1);
                    $el1 = $domDoc->createElement("PrezzoUnitario", number_format($rigo['importo'], 2, '.', ''));
                    $el->appendChild($el1);
                    $el1 = $domDoc->createElement("PrezzoTotale", number_format($rigo['importo'], 2, '.', ''));
                    $el->appendChild($el1);
                    $el1 = $domDoc->createElement("AliquotaIVA", number_format($rigo['pervat'], 2, '.', ''));
                    $el->appendChild($el1);
                    if ($rigo['ritenuta'] > 0) {
                        $el1 = $domDoc->createElement("Ritenuta", 'SI');
                        $el->appendChild($el1);
                    }
                    if ($rigo['pervat'] <= 0) {
                        $el1 = $domDoc->createElement("Natura", $rigo['natura']);
                        $el->appendChild($el1);
                    }
                    if (isset($rigo['descrittivi']) && count($rigo['descrittivi']) > 0) {
                        foreach ($rigo['descrittivi'] as $k => $v) {
                            $el1 = $domDoc->createElement("AltriDatiGestionali", '');
                            $el->appendChild($el1);
                            $el2 = $domDoc->createElement("TipoDato", 'txt' . $k);
                            $el1->appendChild($el2);
                            $el2 = $domDoc->createElement("RiferimentoTesto", $v);
                            $el1->appendChild($el2);
                        }
                    }
                    $benserv->appendChild($el);
                    $nl = true;
                    break;
                case "2":       // descrittivo
                    /* ! ATTENZIONE: tipo rigo spostato in appendice <2.2.1.16> ai righi "normale" !!!
                     */

                    break;
                case "3":       // variazione totale fatture 
                    /* ! ATTENZIONE: questa tipologia di rigo non si deve utilizzare in caso di PA !!!
                     */
                    break;
                case "6":
                case "8":
                    /* ! ATTENZIONE: tipo rigo spostato in appendice <2.2.1.16>  ai righi "normale" !!!
                     */
                    break;
                case "11":
                    $cig = $rigo['descri'];
                    break;
                case "12":
                    $cup = $rigo['descri'];
                    break;
                case "13":
                    $id_documento = $rigo['descri'];
                    break;
            }
            if ($rigo['ritenuta'] > 0) {
                /*
				*/
            }
				// se c'è un ddt di origine ogni rigo deve avere il suo riferimento in <DatiDDT>
            if ($XMLvars->ddt_data && $nl) {

                $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali")->item(0);
                $el_ddt = $domDoc->createElement("DatiDDT", "");
                $el1 = $domDoc->createElement("NumeroDDT", $XMLvars->tesdoc['numdoc']);
                $el_ddt->appendChild($el1);
                $el1 = $domDoc->createElement("DataDDT", $XMLvars->tesdoc['datemi']);
                $el_ddt->appendChild($el1);
                $el1 = $domDoc->createElement("RiferimentoNumeroLinea", $n_linea);
                $el_ddt->appendChild($el1);
                $results->appendChild($el_ddt);
            }
            if ($nl) {
                $n_linea++;
            }
        }
        $ctrl_doc = $XMLvars->tesdoc['numdoc'];
        $ctrl_fat = $XMLvars->tesdoc['numfat'];
    }
    // alla fine del ciclo aggiungo le eventuali spese di incasso ma queste essendo cumulative per diversi eventuali DdT non hanno un riferimento 
    if ($XMLvars->tesdoc['speban'] >= 0.01) {
		$results = $xpath->query("//FatturaElettronicaBody/DatiBeniServizi")->item(0);
        $el = $domDoc->createElement("DettaglioLinee", "");
        $el1 = $domDoc->createElement("NumeroLinea", $n_linea);
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("Descrizione", 'SPESE INCASSO N.' . $XMLvars->pagame['numrat']);
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("PrezzoUnitario", number_format($XMLvars->tesdoc['speban'], 2, '.', ''));
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("PrezzoTotale", number_format(($XMLvars->tesdoc['speban'] * $XMLvars->pagame['numrat']), 2, '.', ''));
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("AliquotaIVA", number_format($XMLvars->expense_pervat['aliquo'], 2, '.', ''));
        $el->appendChild($el1);
        $results->appendChild($el);
        $n_linea++;
    }

    //dati ordine di acquisto
    $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali")->item(0);
    if ($id_documento != "") {
        $el_datiOrdineAcquisto = $domDoc->createElement("DatiOrdineAcquisto", "");
        $el1 = $domDoc->createElement("IdDocumento", $id_documento);
        $el_datiOrdineAcquisto->appendChild($el1);
    }
    if ($id_documento != "" and $cup != "") {
        $el1 = $domDoc->createElement("CodiceCUP", $cup);
        $el_datiOrdineAcquisto->appendChild($el1);
    }
    if ($id_documento != "" and $cig != "") {
        $el1 = $domDoc->createElement("CodiceCIG", $cig);
        $el_datiOrdineAcquisto->appendChild($el1);
    }
    //occorre testare qui se presente il ddt altrimeni occorrera' fare l'insertbefore
    if ($id_documento != "") {
        $results1 = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiDDT")->item(0);
        if (is_null($results1)) {
            $results->appendChild($el_datiOrdineAcquisto);
        } else {
            $results->insertBefore($el_datiOrdineAcquisto, $results1);
        }
    }




    $XMLvars->setXMLtot();
    if ($XMLvars->tot_ritenute > 0) {
        $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento")->item(0);
        $el = $domDoc->createElement("DatiRitenuta", "");
        $el1 = $domDoc->createElement("TipoRitenuta", $XMLvars->TipoRitenuta);
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("ImportoRitenuta", number_format($XMLvars->tot_ritenute, 2, '.', ''));
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("AliquotaRitenuta", number_format($XMLvars->azienda['ritenuta'], 2, '.', ''));
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("CausalePagamento", $XMLvars->azienda['causale_pagam_770']);
        $el->appendChild($el1);
        $results->appendChild($el);
    }

    if (count($XMLvars->cassa_prev) >= 1) {
        $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento")->item(0);
	    foreach ($XMLvars->cassa_prev as $key => $value) {
			$el = $domDoc->createElement("DatiCassaPrevidenziale", "");
			$el1 = $domDoc->createElement("TipoCassa", $key);
			$el->appendChild($el1);
			$el1 = $domDoc->createElement("AlCassa", number_format($value['AlCassa'], 2, '.', ''));
			$el->appendChild($el1);
			$el1 = $domDoc->createElement("ImportoContributoCassa", number_format($value['ImportoContributoCassa'], 2, '.', ''));
			$el->appendChild($el1);
			$el1 = $domDoc->createElement("ImponibileCassa", number_format($value['ImponibileCassa'], 2, '.', ''));
			$el->appendChild($el1);
			$el1 = $domDoc->createElement("AliquotaIVA", number_format($value['AliquotaIVA'], 2, '.', ''));
			$el->appendChild($el1);
			if ($value['Ritenuta']>=0.01){
				$el1 = $domDoc->createElement("Ritenuta", 'SI');
				$el->appendChild($el1);
			}
			if (substr($value['Natura'],0,1)=='N'){
				$el1 = $domDoc->createElement("Natura", $value['Natura']);
				$el->appendChild($el1);
			}
			$results->appendChild($el);
        }
    }

    if ($XMLvars->impbol >= 0.01) {
        $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento")->item(0);
        $el = $domDoc->createElement("DatiBollo", "");
        $el1 = $domDoc->createElement("BolloVirtuale", $XMLvars->BolloVirtuale);
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("ImportoBollo", number_format($XMLvars->impbol, 2, '.', ''));
        $el->appendChild($el1);
        $results->appendChild($el);
    }

    $results = $xpath->query("//FatturaElettronicaBody/DatiBeniServizi")->item(0);
    foreach ($XMLvars->cast as $key => $value) {
        $el = $domDoc->createElement("DatiRiepilogo", "");
        $el1 = $domDoc->createElement("AliquotaIVA", number_format($value['periva'], 2, '.', ''));
        $el->appendChild($el1);
        if ($value['periva'] < 0.01) {
            $el1 = $domDoc->createElement("Natura", $value['fae_natura']);
            $el->appendChild($el1);
        }
        // necessario per l'elemento 2.2.2.7
        $value['esigibilita'] = 'I'; // I=esigibiltà immediata
        if ($value['tipiva'] == 'T') { // è un'IVA non esigibile per split payment PA
            $value['esigibilita'] = 'S'; // S=scissione dei pagamenti
        }

        $el1 = $domDoc->createElement("ImponibileImporto", number_format($value['impcast'], 2, '.', ''));
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("Imposta", number_format($value['ivacast'], 2, '.', ''));


        if ($value['fae_natura'] == 'N1' || $value['fae_natura'] == 'N2' || $value['fae_natura'] == 'N3' || $value['fae_natura'] == 'N4' || $value['fae_natura'] == 'N5' || $value['fae_natura'] == 'N6') {
            //non viene inserito il nodo EsigibilitaIVA
        } else {
            $el->appendChild($el1);
            $el1 = $domDoc->createElement("EsigibilitaIVA", $value['esigibilita']);
        }

        $el->appendChild($el1);
        $el1 = $domDoc->createElement("RiferimentoNormativo", $value['descriz']);
        $el->appendChild($el1);
        $results->appendChild($el);
    }

    if ($XMLvars->sempl_accom) {
        // se è una fattura accompagnatoria qui inserisco anche i dati relativi al trasporto
        $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali")->item(0);
        $el = $domDoc->createElement("DatiTrasporto", "");
		if ($XMLvars->vettore) { // ho un vettore
			$el1 = $domDoc->createElement("DatiAnagraficiVettore", '');
				$el2 = $domDoc->createElement("IdFiscaleIVA", '');
					$el3 = $domDoc->createElement("IdPaese", 'IT');
					$el2->appendChild($el3);
					$el3 = $domDoc->createElement("IdCodice", $XMLvars->vettore['partita_iva']);
					$el2->appendChild($el3);
				$el1->appendChild($el2);
				$el2 = $domDoc->createElement("Anagrafica", '');
					$el3 = $domDoc->createElement("Denominazione",$XMLvars->vettore['ragione_sociale']);
					$el2->appendChild($el3);
				$el1->appendChild($el2);
			$el->appendChild($el1);
		}
		$el1 = $domDoc->createElement("MezzoTrasporto", $XMLvars->tesdoc['spediz']);
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("CausaleTrasporto", 'VENDITA');
        $el->appendChild($el1);
		if ($XMLvars->tesdoc['units']>=1){
			$el1 = $domDoc->createElement("NumeroColli", $XMLvars->tesdoc['units']);
			$el->appendChild($el1);
		}
        $el1 = $domDoc->createElement("Descrizione", $XMLvars->tesdoc['imball']);
        $el->appendChild($el1);
		if (($XMLvars->tesdoc['net_weight']+$XMLvars->tesdoc['gross_weight'])>=0.001){
			$el1 = $domDoc->createElement("UnitaMisuraPeso", 'kg');
			$el->appendChild($el1);
			if ($XMLvars->tesdoc['gross_weight']>=0.001){
				$el1 = $domDoc->createElement("PesoLordo", $XMLvars->tesdoc['gross_weight']);
				$el->appendChild($el1);
			}
			if ($XMLvars->tesdoc['net_weight']>=0.001){
				$el1 = $domDoc->createElement("PesoNetto", $XMLvars->tesdoc['net_weight']);
				$el->appendChild($el1);
			}
        }
        $el1 = $domDoc->createElement("DataInizioTrasporto", substr($XMLvars->tesdoc['initra'], 0, 10));
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("DataOraConsegna", substr($XMLvars->tesdoc['initra'], 0, 10) . 'T' . substr($XMLvars->tesdoc['initra'], 11, 8));
        $el->appendChild($el1);
        $results->appendChild($el);
    }

// ----- CALCOLO TOTALI E RATE DEL PAGAMENTO
    $totpag = $XMLvars->totimpfat + $XMLvars->totriport + $XMLvars->totivafat - $XMLvars->tot_ritenute - $XMLvars->ivasplitpay;
// ----- INSERITA variabile che calcola il totale della fattura al lordo della RDA e dell'IVA
    $totpar = $XMLvars->totimpfat + $XMLvars->totriport + $XMLvars->totivafat;
    if ($XMLvars->virtual_taxstamp == 1 || $XMLvars->virtual_taxstamp == 2) {
        $totpag = $totpag + $XMLvars->impbol;
        $totpar = $totpar + $XMLvars->impbol;
    }
    $ex = new Expiry;
    $ratpag = $ex->CalcExpiry($totpag, $XMLvars->tesdoc["datfat"], $XMLvars->pagame['tipdec'], $XMLvars->pagame['giodec'], $XMLvars->pagame['numrat'], $XMLvars->pagame['tiprat'], $XMLvars->pagame['mesesc'], $XMLvars->pagame['giosuc']);
    if ($XMLvars->pagame['numrat'] > 1) {
        $cond_pag = 'TP01';
    } else {
        $cond_pag = 'TP02';
    }
    // elementi dei <DatiPagamento> (2.4)
    $results = $xpath->query("//FatturaElettronicaBody")->item(0);
    $el = $domDoc->createElement("DatiPagamento", "");
    $el1 = $domDoc->createElement("CondizioniPagamento", $cond_pag); // 2.4.1
    $el->appendChild($el1);
    $results->appendChild($el);
    foreach ($ratpag as $k => $v) {
        $results = $xpath->query("//FatturaElettronicaBody/DatiPagamento")->item(0);
        $el = $domDoc->createElement("DettaglioPagamento", ''); // 2.4.2
        $el1 = $domDoc->createElement("Beneficiario", trim($XMLvars->intesta1 . " " . $XMLvars->intesta1bis)); // 2.4.2.1
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("ModalitaPagamento", $XMLvars->pagame['fae_mode']); // 2.4.2.2
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("DataScadenzaPagamento", $v['date']); // 2.4.2.5
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("ImportoPagamento", $v['amount']); // 2.4.2.6
        $el->appendChild($el1);
        if ($XMLvars->pagame['tippag'] == 'B') { // se il pagamento è una RiBa indico CAB e ABI
            $el1 = $domDoc->createElement("ABI", str_pad($XMLvars->banapp['codabi'], 5, '0', STR_PAD_LEFT)); // 2.4.2.14
            $el->appendChild($el1);
            $el1 = $domDoc->createElement("CAB", str_pad($XMLvars->banapp['codcab'], 5, '0', STR_PAD_LEFT)); // 2.4.2.15
            $el->appendChild($el1);
        } elseif (!empty($XMLvars->banacc['iban'])) { // se il pagamento ha un IBAN associato
            $el1 = $domDoc->createElement("IBAN", $XMLvars->banacc['iban']); // 2.4.2.13
            $el->appendChild($el1);
        }
        $results->appendChild($el);
    }

    //Modifica per il sicoge che richiede obbligatoriamente popolato il punto 2.1.1.9
    $results = $xpath->query("//FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento")->item(0);
    // sconto/maggiorazione totale documento 2.1.1.8
    $sc = round($XMLvars->totimpmer * $XMLvars->tesdoc['sconto'] / 100, 2);
    if (abs($sc) >= 0.01) {
        $el = $domDoc->createElement("ScontoMaggiorazione", "");
        if ($sc < 0) { // è una maggiorazione
            $t = 'MG';
        } else {
            $t = 'SC';
        }
        $el1 = $domDoc->createElement("Tipo", $t);
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("Percentuale", $XMLvars->tesdoc['sconto']);
        $el->appendChild($el1);
        $el1 = $domDoc->createElement("Importo", number_format(abs($sc), 2, '.', ''));
        $el->appendChild($el1);
        $results->appendChild($el);
    }
    $el = $domDoc->createElement("ImportoTotaleDocumento", number_format($totpar, 2, '.', ''));  // totale fatura al lordo di RDA
    $results->appendChild($el);

    // faccio l'encode per ricavare il progressivo unico di invio
    $data = array('azienda' => $XMLvars->azienda['codice'],
        'anno' => $XMLvars->docRelDate,
        'sezione' => $XMLvars->seziva,
        'protocollo' => $XMLvars->protoc,
        'intermediary' => $XMLvars->Intermediary);
    $progressivo_unico_invio = $XMLvars->encodeSendingNumber($data, 36);

    //print $XMLvars->decodeFromSendingNumber($progressivo_unico_invio);
    $nome_file = "IT" . $codice_trasmittente . "_" . $progressivo_unico_invio;

    $id_tes = $XMLvars->tesdoc['id_tes'];
    $data_ora_ricezione = $XMLvars->docRelDate;

	if ($name_ziparchive){
		$verifica = gaz_dbi_get_row($gTables['fae_flux'], 'filename_ori', $nome_file . ".xml");
		if ($verifica == false) {
			$valori = array('filename_ori' => $nome_file . ".xml",
				'filename_zip_package'=>$name_ziparchive,
				'id_tes_ref' => $id_tes,
				'exec_date' => $data_ora_ricezione,
				'received_date' => $data_ora_ricezione,
				'delivery_date' => $data_ora_ricezione,
				'filename_son' => '',
				'id_SDI' => 0,
				'filename_ret' => '',
				'mail_id' => 0,
				'data' => '',
				'flux_status' => '#',
				'progr_ret' => '000',
				'flux_descri' => '');
			fae_fluxInsert($valori);
		}
		return $domDoc->saveXML();
	} else {
		$verifica = gaz_dbi_get_row($gTables['fae_flux'], 'filename_ori', $nome_file . ".xml");
		if ($verifica == false) {
			$valori = array('filename_ori' => $nome_file . ".xml",
			'filename_zip_package'=>'',
            'id_tes_ref' => $id_tes,
            'exec_date' => $data_ora_ricezione,
            'received_date' => $data_ora_ricezione,
            'delivery_date' => $data_ora_ricezione,
            'filename_son' => '',
            'id_SDI' => 0,
            'filename_ret' => '',
            'mail_id' => 0,
            'data' => '',
            'flux_status' => '#',
            'progr_ret' => '000',
            'flux_descri' => '');
			fae_fluxInsert($valori);
		}
		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=" . $nome_file . ".xml");
		print $domDoc->saveXML();
	}
}

?>
