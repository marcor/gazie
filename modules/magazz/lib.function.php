<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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

class magazzForm extends GAzieForm {

    function get_magazz_ordinati ( $codice, $tip='AOR' ) {
    global $gTables;
	
	$show_artico_composit = gaz_dbi_get_row($gTables['company_config'], 'var', 'show_artico_composit');
	$tipo_composti = gaz_dbi_get_row($gTables['company_config'], 'var', 'tipo_composti');
	
    $column = $gTables['artico'].".codice,
        ".$gTables['artico'].".good_or_service,
        ".$gTables['rigbro'].".id_tes,
        ".$gTables['rigbro'].".codart,
        ".$gTables['rigbro'].".unimis,
        ".$gTables['rigbro'].".quanti,
        ".$gTables['tesbro'].".tipdoc";
	if ($tipo_composti['val']=="STD") { // Antonio Germani se siamo in modalità composti STD si escludono solo gli articoli servizi
    $tables = $gTables['artico']."
        INNER JOIN ".$gTables['rigbro']."
            ON ".$gTables['artico'].".codice = ".$gTables['rigbro'].".codart
        INNER JOIN ".$gTables['tesbro']."
            ON ".$gTables['rigbro'].".id_tes = ".$gTables['tesbro'].".id_tes";

    $where = $gTables['artico'].".good_or_service != 1
		AND ".$gTables['rigbro'].".id_doc = 0
        AND ".$gTables['artico'].".codice = '".$codice."'
        AND ".$gTables['tesbro'].".tipdoc = '".$tip."'";
	} else { // se siamo in modalità KIT si prendono solo gli articoli semplici
		$tables = $gTables['artico']."
        INNER JOIN ".$gTables['rigbro']."
            ON ".$gTables['artico'].".codice = ".$gTables['rigbro'].".codart
        INNER JOIN ".$gTables['tesbro']."
            ON ".$gTables['rigbro'].".id_tes = ".$gTables['tesbro'].".id_tes";

		$where = $gTables['artico'].".good_or_service = 0
		AND ".$gTables['rigbro'].".id_doc = 0
        AND ".$gTables['artico'].".codice = '".$codice."'
        AND ".$gTables['tesbro'].".tipdoc = '".$tip."'";
	}

    $orderby = $gTables['artico'].".codice ASC";
    $limit = "0";
    $passo = "999";

    $restemp = gaz_dbi_dyn_query($column, $tables, $where, $orderby, $limit, $passo);
    $totord = 0;
    while ($row = gaz_dbi_fetch_array($restemp)) {
        $totord += $row['quanti'];
    }
	
	// Antonio Germani - calcolo evasi
	$toteva = 0;
	if ($tip!="AOR" AND $totord>0){
		$preord=0;
		$query = "SELECT ".$gTables['rigbro'].".codart, ".$gTables['rigbro'].".id_tes FROM " . $gTables['rigbro'] . " LEFT JOIN ". $gTables['tesbro'] ." ON ".$gTables['rigbro'].".id_tes=".$gTables['tesbro'].".id_tes  WHERE codart ='" . $codice. "' AND tiprig <= '1' AND ". $gTables['tesbro'].".tipdoc ='".$tip."'";
		$result = gaz_dbi_query($query); // prendo tutti i righi ordine per questo articolo
		while ($row = $result->fetch_assoc()){
			$query = "SELECT " . 'quanti'. ",". 'id_rig' . " FROM " . $gTables['rigdoc'] . " WHERE id_order ='" . $row['id_tes']. "' AND tiprig <= '1' AND codart = '".$codice."'";
			$res = gaz_dbi_query($query); // prendo i righi documento che rappresentano gli evasi
			$n=0;
			while ($row2 = $res->fetch_assoc()){
				// qui devo evitare che, se nello stesso ordine ci sono più righi con lo stesso articolo, vengano conteggiati più volte
				if ($preord==$row['id_tes']){ // se l'ordine è lo stesso del precedente
				// non faccio nulla perché già conteggiato nel ciclo precedente
					} else {		
				$toteva=$toteva+$row2['quanti']; // incremento il totale evaso
					}
				$n++;
			}
			$preord=$row['id_tes'];
		}		
	}
	// fine calcolo evasi
	
    return $totord-$toteva;
    }

    function selItem($name, $val, $strSearch = '', $mesg='', $val_hiddenReq = '', $class = 'FacetSelect') {
        global $gTables, $admin_aziend;
        if ($admin_aziend['artsea'] == 'B') {        //ricerca per codice a barre
            $field = 'barcode';
        } elseif ($admin_aziend['artsea'] == 'D') { //ricerca per descrizione
            $field = 'descri';
        } else {                   //ricerca per codice (default)
            $field = 'codice';
        }
        if (!empty($val)) { //vengo da una modifica della precedente select case quindi non serve la ricerca
            $item = gaz_dbi_get_row($gTables['artico'], 'codice', $val);
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"" . $item[$field] . "\">\n";
            echo "\t<input type=\"submit\" value=\"" . $item['codice'] . " - " . $item['descri'] . "\" name=\"change\" onclick=\"this.form.$name.value=''; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
        } else {
            if (strlen($strSearch) >= 1) { //sto ricercando un nuovo articolo
                $item = gaz_dbi_dyn_query("codice,descri,barcode", $gTables['artico'], $field . " LIKE '$strSearch%'", "codice ASC, descri DESC");
                echo "\t<select name=\"$name\" class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
                echo "<option value=\"0\"> ---------- </option>";
                if (gaz_dbi_num_rows($item) > 0) {
                    while ($r = gaz_dbi_fetch_array($item)) {
                        $selected = '';
                        if ($r['codice'] == $val) {
                            $selected = "selected";
                        }
                        echo "\t\t <option value=\"" . $r['codice'] . "\" $selected >" . $r['codice'] . " - " . $r["descri"] . "</option>\n";
                    }
                    echo "\t </select>\n";
                } else {
                    $msg = $mesg[0];
                }
            } else {
                $msg = $mesg[1];
                echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            }
            echo "\t<input type=\"text\" name=\"search[$name]\" value=\"" . $strSearch . "\" maxlength=\"15\"  class=\"FacetInput\">\n";
			if (isset($msg)) {
				echo "<input type=\"text\" style=\"color: red; font-weight: bold;\"  disabled value=\"$msg\">";
			}
            //echo "\t<input type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
            /** ENRICO FEDELE */
            /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
            echo '<button type="submit" class="btn btn-default btn-sm" name="search_str"><i class="glyphicon glyphicon-search"></i></button>';
            /** ENRICO FEDELE */
        }
    }

    function selectCaumag($val,$operat=-1,$empty=false,$val_hiddenReq='',$class='FacetSelect',$clifor=-1) {
        global $gTables;
        $refresh = '';
        if (!empty($val_hiddenReq)) {
            $refresh = "onchange=\"this.form.hidden_req.value='caumag'; this.form.submit();\"";
        }
        $query = "SELECT * FROM " . $gTables['caumag'] . " WHERE clifor = ".$clifor." AND operat = $operat";
        echo "\t <select name=\"caumag\" class=\"$class\" $refresh >\n";
        if ($empty) {
            echo "\t\t <option value=\"\">---------</option>\n";
        }
        $result = gaz_dbi_query($query);
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if ($r['codice'] == $val) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"" . $r['codice'] . "\" $selected >" . $r['descri'] . "</option>\n";
        }
        echo "\t </select>\n";
    }

    function getOperators() {  // Creo l'array associativo degli operatori dei documenti
        return array("VCO" => -1, "VRI" => -1, "DDT" => -1, "FAD" => -1, "FAI" => -1, "FAA" => -1, "FAF" => -1, "FAQ" => -1, "FAP" => -1, "FNC" => 1, "FND" => -1,
            "DDR" => -1, "DDL" => -1, "DDV" => -1, "RDV" => 1, "DDY" => -1, "DDS" => -1, "AFA" => 1, "AFT" => 1, "ADT" => 1, "AFC" => -1, "AFD" => 1, "VPR" => -1, 
            "VOR" => -1, "VOW" => -1, "VOG" => -1, "CMR" => -1, "RDL" => 1);
    }

    function get_codice_caumag($clifor,$insdoc,$operat) {  // trovo il codice della causale in base al tipo di partner e di documento
        global $gTables;
		$query = 'SELECT * FROM `' . $gTables['caumag'] . '` WHERE `clifor`='.$clifor.' AND `insdoc`='.$insdoc.' AND `operat`='.$operat.' ORDER BY `codice` ASC';
		$result = gaz_dbi_query($query);	
        return gaz_dbi_fetch_row($result)[0]; // restituisco il codice (index 0)
    }

    function getStockEvalMethod() {  // Prendo il metodo di valorizzazione del magazzino impostato in configurazione azienda
        global $gTables;
        $enterprise = gaz_dbi_get_row($gTables['aziend'], 'codice', $_SESSION['company_id']);
        return $enterprise['stock_eval_method'];
    }

    function getLastCost($item_code, $decimal_price) {  // Prendo il costo dall'ultimo movimento d'aquisto
        global $gTables;
        $rs_last_cost = gaz_dbi_dyn_query("*", $gTables['movmag'], " artico = '$item_code' AND tipdoc LIKE 'A%' AND operat = 1 ", "datreg DESC, id_mov DESC", 0, 1);
        $last_cost = gaz_dbi_fetch_array($rs_last_cost);
        if ($last_cost) {
            return CalcolaImportoRigo(1, $last_cost['prezzo'], array($last_cost['scochi'], $last_cost['scorig']), $decimal_price);
        } else {
            return 0;
        }
    }

    function ctrlMovYearsAfter($year, $item_code) {  // Controllo che non ci siano movimenti negli anni successivi
        global $gTables;
        $rs_years_after = gaz_dbi_dyn_query("*", $gTables['movmag'], "YEAR(datreg) > $year AND artico = '$item_code' AND tipdoc LIKE 'A%' AND operat = 1 ", "datreg DESC, id_mov DESC", 0, 1);
        $years_after = gaz_dbi_fetch_array($rs_years_after);
        if ($years_after) {
            return false;   // non si pu� aggiornare il valore dell'esistente perch� ci sono movimenti su anni successivi
        } else {
            return true;
        }
    }

	private function getBOMfromDB($codcomp,$depth){
        global $gTables;
		$ret=[];
		$rs_BOM = gaz_dbi_dyn_query("*", $gTables['distinta_base'].' LEFT JOIN '.$gTables['artico'].' ON codice_artico_base = codice', "codice_composizione = '".$codcomp."'",'codice_artico_base');
		while ($r = gaz_dbi_fetch_array($rs_BOM)) {
			$r['depth']=$depth;
			$ret[$r['codice_artico_base']]=$r;
		}
		return $ret;
	}
	
	private function getlevel($codbase){
        global $gTables;
		$ret=[];
		$rs_BOM = gaz_dbi_dyn_query("*", $gTables['distinta_base'], "codice_artico_base = '".$codbase."' GROUP BY codice_composizione",'codice_artico_base');
		while ($r = gaz_dbi_fetch_array($rs_BOM)) {
			$ret[$r['codice_composizione']]=$r;
		}
		return $ret;
	}
	

	function buildTrunk($cod,$exist=false){ 
		/* funzione che permette di eseguire due operazioni:
		1- se non si passa $exixt crea un array mutlidimensionale (profondità 5) con tutti i "genitori" in cui è presente l'articolo passato come referenza in $cod
		2- qualora si passi $exit esso si limita a controlla la presenza dello stesso tra tutti i genitori di quello passato su $cod, in caso positivo ritorna $cod
			altrimenti false
		*/
        global $gTables;
		$acc=$this->getlevel($cod);
		if (count($acc)>=1){
			foreach ($acc as $k0=>$v0){
				if ($exist==$k0) return $cod; // questi righi li utilizzo per ritornare il codice dell'articolo di base per controllare l'esistenza 
				$acc[$k0]['up']=$this->getlevel($v0['codice_composizione']);
				if (count($acc[$k0]['up'])>0){
					foreach ($acc[$k0]['up'] as $k1=>$v1){
						if ($exist==$k1) return $cod;
						$acc[$k0]['up'][$k1]['up']=$this->getlevel($v1['codice_composizione']);
						if (count($acc[$k0]['up'][$k1]['up'])>0){
							foreach ($acc[$k0]['up'][$k1]['up'] as $k2=>$v2){
								if ($exist==$k2) return $cod;
								$acc[$k0]['up'][$k1]['up'][$k2]['up']=$this->getlevel($v2['codice_composizione']);
								if (count($acc[$k0]['up'][$k1]['up'][$k2]['up'])>0){
									foreach ($acc[$k0]['up'][$k1]['up'][$k2]['up'] as $k3=>$v3){
										if ($exist==$k3) return $cod;
										$acc[$k0]['up'][$k1]['up'][$k2]['up'][$k3]['up']=$this->getlevel($v3['codice_composizione']);
										if (count($acc[$k0]['up'][$k1]['up'][$k2]['up'][$k3]['up'])>0){
											foreach ($acc[$k0]['up'][$k1]['up'][$k2]['up'][$k3]['up'] as $k4=>$v4){
												if ($exist==$k4) return $cod;
												$acc[$k0]['up'][$k1]['up'][$k2]['up'][$k3]['up'][$k4]['up']=$this->getlevel($v4['codice_composizione']);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if ($exist){
			return false;
		} else{
			return $acc;
		}
	}
		
	
    function getBOM($codcomp) {  // Creo l'array multidimensionale della distita base (BOM)
		$depth=0;
		$data0=$this->getBOMfromDB($codcomp,0);
		$n0=count($data0);
		if ($n0>=1){
			foreach ($data0 as $k=>$v){
				$data1=$this->getBOMfromDB($v['codice_artico_base'],1);
				$data0[$k]['totq']=floatval($v['quantita_artico_base']);
				$n1=count($data1);
				if ($n1>=1){
					$data0[$k]['codice_artico_base']=$data1;
					foreach ($data1 as $k2=>$v2){	
						$data0[$k]['codice_artico_base'][$k2]['totq']=$v['quantita_artico_base']*$v2['quantita_artico_base'];
						$data2=$this->getBOMfromDB($v2['codice_artico_base'],2);
						$n2=count($data2);
						if ($n2>=1){
							$data0[$k]['codice_artico_base'][$k2]['codice_artico_base']=$data2;
							foreach ($data2 as $k3=>$v3){	
								$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['totq']=$v['quantita_artico_base']*$v2['quantita_artico_base']*$v3['quantita_artico_base'];
								$data3=$this->getBOMfromDB($v3['codice_artico_base'],3);
								$n3=count($data3);
								if ($n3>=1){
									$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['codice_artico_base']=$data3;
									foreach ($data3 as $k4=>$v4){	
										$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['codice_artico_base'][$k4]['totq']=$v['quantita_artico_base']*$v2['quantita_artico_base']*$v3['quantita_artico_base']*$v4['quantita_artico_base'];
										$data4=$this->getBOMfromDB($v4['codice_artico_base'],4);
										$n4=count($data4);
										if ($n4>=1){
											$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['codice_artico_base'][$k4]['codice_artico_base']=$data4;
											foreach ($data4 as $k5=>$v5){	
												$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['codice_artico_base'][$k4]['codice_artico_base'][$k5]['totq']=$v['quantita_artico_base']*$v2['quantita_artico_base']*$v3['quantita_artico_base']*$v4['quantita_artico_base']*$v5['quantita_artico_base'];
												$data5=$this->getBOMfromDB($v5['codice_artico_base'],5);
												$n5=count($data5);
												if ($n5>=1){
													$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['codice_artico_base'][$k4]['codice_artico_base'][$k5]['codice_artico_base']=$data5;
													foreach ($data5 as $k6=>$v6){	
														$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['codice_artico_base'][$k4]['codice_artico_base'][$k5]['codice_artico_base'][$k6]['totq']=$v['quantita_artico_base']*$v2['quantita_artico_base']*$v3['quantita_artico_base']*$v4['quantita_artico_base']*$v5['quantita_artico_base']*$v6['quantita_artico_base'];
														$data6=$this->getBOMfromDB($v6['codice_artico_base'],6);
														$n6=count($data6);
														if ($n6>=1){
															$data0[$k]['codice_artico_base'][$k2]['codice_artico_base'][$k3]['codice_artico_base'][$k4]['codice_artico_base'][$k5]['codice_artico_base'][$k6]['codice_artico_base']=$data6;
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $data0;
	}
	
    function print_tree_BOM($codcomp) {  // Stampo la distinta base
		$color='eeeeee';
		global $gTables;
		$art=gaz_dbi_get_row($gTables['artico'], "codice", $codcomp);
		$data=$this->getBOM($codcomp);
		if (count($data)>=1){
        echo '<div class="panel panel-default"><div class="panel-heading"><h4>Distinta base della composizione: '.$codcomp.'-'.$art['descri']."\n</h4>".'</div><div class="panel-body"><ul class="col-xs-12 col-md-11 col-lg-10">';
		foreach($data as $k0=>$v0) {
			$icona=(is_array($v0['codice_artico_base']))?'<a class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-list"></i></a>':'';
			echo '<li class="collapsible" id="'.$v0[2].'" data-toggle="collapse" data-target=".' . $v0[2] . '"><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-success" href="admin_artico.php?Update&amp;codice=' . $v0[2] . '">'.$v0[2].'</a> - '.$v0['descri'].' '.$icona.' <span class="pull-right"> '.$v0['unimis'].': '.floatval($v0['quantita_artico_base']).'</span></div>';
			$color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
			if (is_array($v0['codice_artico_base'])){
			  echo '<ul class="collapse ' . $v0[2] . '">';
			  foreach($v0['codice_artico_base'] as $k1=>$v1) {
				  echo '<li class="" id=""><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-primary" href="admin_artico.php?Update&amp;codice=' . $v1[2] . '">'.$v1[2].'</a> - '.$v1['descri'].' <span class="pull-right">'.$v1['unimis'].': '.floatval($v1['quantita_artico_base']).'</span></div>';
				  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
				  if (is_array($v1['codice_artico_base']))	{
					echo '<ul class="">';
					foreach($v1['codice_artico_base'] as $k2=>$v2) {
					  echo '<li class="" id=""><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-info" href="admin_artico.php?Update&amp;codice=' . $v2[2] . '">'.$v2[2].'</a> - '.$v2['descri'].' <span class="pull-right"> '.$v2['unimis'].': '.floatval($v2['quantita_artico_base']).'</span></div>';
					  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
					  if (is_array($v2['codice_artico_base']))	{
						echo '<ul class="">';
						foreach($v2['codice_artico_base'] as $k3=>$v3) {
						  echo '<li class="" id=""><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-warning" href="admin_artico.php?Update&amp;codice=' . $v3[2] . '">'.$v3[2].'</a> - '.$v3['descri'].' <span class="pull-right"> '.$v3['unimis'].': '.floatval($v3['quantita_artico_base']).'</span></div>';
						  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
						  if (is_array($v3['codice_artico_base']))	{
							echo '<ul class="">';
							foreach($v3['codice_artico_base'] as $k4=>$v4) {
							  echo '<li class="" id=""><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-danger" href="admin_artico.php?Update&amp;codice=' . $v4[2] . '">'.$v4[2].'</a> - '.$v4['descri'].' <span class="pull-right"> '.$v4['unimis'].': '.floatval($v4['quantita_artico_base']).'</span></div>';
							  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
							  if (is_array($v4['codice_artico_base']))	{
								echo '<ul class="">';
								foreach($v4['codice_artico_base'] as $k5=>$v5) {
								  echo '<li class="" id=""><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-default" href="admin_artico.php?Update&amp;codice=' . $v5[2] . '">'.$v5[2].'</a> - '.$v5['descri'].' <span class="pull-right"> '.$v5['unimis'].': '.floatval($v5['quantita_artico_base']).'</span></div>';
								  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
								  if (is_array($v5['codice_artico_base']))	{
									echo '<ul class="">';
									foreach($v5['codice_artico_base'] as $k6=>$v6) {
									  echo '<li class="" id=""><div style="background-color: #'.$color.'"><a class="btn btn-xs btn-basic" href="admin_artico.php?Update&amp;codice=' . $v6[2] . '">'.$v6[2].'</a> - '.$v6['descri'].' <span class="pull-right"> '.$v6['unimis'].': '.floatval($v6['quantita_artico_base']).'</span></div></li>';
									  $color=($color=='fcfcfc')?'eeeeee':'fcfcfc';
									}
									echo "</ul>\n";
								  }
								  echo "</li>\n";
								}
								echo "</ul>\n";
							  }
							  echo "</li>\n";
							}
							echo "</ul>\n";
						  }
 		  				  echo "</li>\n";
						}
						echo "</ul>\n";
					  }
					  echo "</li>\n";
					}
					echo "</ul>\n";
				  }
				  echo "</li>\n";
			  }
  			  echo "</ul>\n";
			} else{
				
			}
			echo "</li>\n";
		}
		echo "</ul></div></div>\n";
		}
	}
	function print_trunks_BOM($codcomp){
        global $gTables;
		$art=gaz_dbi_get_row($gTables['artico'], "codice", $codcomp);
		$acc=$this->getLevelfromDB($codcomp);
		$c='eeeeee';
		if (count($acc)>=1){
         echo '<div class="panel panel-default"><div class="panel-heading"><h4>'.$codcomp.'-'.$art['descri']. ' è un articolo contenuto nelle seguenti composizioni:</h4></div><div class="panel-body"><ul class="col-xs-12 col-md-11 col-lg-10">';
		 foreach ($acc as $k0=>$v0){
			//$icona=(is_array($v0['codice_artico_base']))?'<a class="btn btn-xs btn-info"><i class="glyphicon glyphicon-list"></i></a>':'';
			echo '<li><div style="background-color: #'.$c.'"><a class="btn btn-xs btn-success" href="admin_artico.php?Update&amp;codice=' . $v0['codice'] . '">'.$v0['codice'].'</a> - '.$v0['descri'].'<span class="pull-right">  pz: '.floatval($v0['quantita_artico_base']).'</span></div>';
		    $c=($c=='fcfcfc')?'eeeeee':'fcfcfc';
			$acc[$k0]['dad']=$this->getLevelfromDB($v0['codice']);
			if (count($acc[$k0]['dad'])>0){
			  echo '<ul class="">';
			  foreach ($acc[$k0]['dad'] as $k1=>$v1){
				echo '<li><div style="background-color: #'.$c.'"><a class="btn btn-xs btn-primary" href="admin_artico.php?Update&amp;codice=' . $v1['codice'] . '">'.$v1['codice'].'</a> - '.$v1['descri'].'<span class="pull-right">  pz: '.floatval($v1['quantita_artico_base']).'</span></div>';
				$c=($c=='fcfcfc')?'eeeeee':'fcfcfc';
				$acc[$k0]['dad'][$k1]['dad']=$this->getLevelfromDB($v1['codice']);
				if (count($acc[$k0]['dad'][$k1]['dad'])>0){
				  echo '<ul class="">';
				  foreach ($acc[$k0]['dad'][$k1]['dad'] as $k2=>$v2){
					echo '<li><div style="background-color: #'.$c.'"><a class="btn btn-xs btn-info" href="admin_artico.php?Update&amp;codice=' . $v2['codice'] . '">'.$v2['codice'].'</a> - '.$v2['descri'].'<span class="pull-right">  pz: '.floatval($v2['quantita_artico_base']).'</span></div>';
					$c=($c=='fcfcfc')?'eeeeee':'fcfcfc';
					$acc[$k0]['dad'][$k1]['dad'][$k2]['dad']=$this->getLevelfromDB($v2['codice']);
					if (count($acc[$k0]['dad'][$k1]['dad'][$k2]['dad'])>0){
					  echo '<ul class="">';
					  foreach ($acc[$k0]['dad'][$k1]['dad'][$k2]['dad'] as $k3=>$v3){
						echo '<li><div style="background-color: #'.$c.'"><a class="btn btn-xs btn-warning" href="admin_artico.php?Update&amp;codice=' . $v3['codice'] . '">'.$v3['codice'].'</a> - '.$v3['descri'].'<span class="pull-right">  pz: '.floatval($v3['quantita_artico_base']).'</span></div>';
						$c=($c=='fcfcfc')?'eeeeee':'fcfcfc';
						$acc[$k0]['dad'][$k1]['dad'][$k2]['dad'][$k3]['dad']=$this->getLevelfromDB($v3['codice']);
						if (count($acc[$k0]['dad'][$k1]['dad'][$k2]['dad'][$k3]['dad'])>0){
						  echo '<ul class="">';
						  foreach ($acc[$k0]['dad'][$k1]['dad'][$k2]['dad'][$k3]['dad'] as $k4=>$v4){
							echo '<li><div style="background-color: #'.$c.'"><a class="btn btn-xs btn-danger" href="admin_artico.php?Update&amp;codice=' . $v4['codice'] . '">'.$v4['codice'].'</a> - '.$v4['descri'].'<span class="pull-right">  pz: '.floatval($v4['quantita_artico_base']).'</span></div>';
							$c=($c=='fcfcfc')?'eeeeee':'fcfcfc';
							$acc[$k0]['dad'][$k1]['dad'][$k2]['dad'][$k3]['dad'][$k4]['dad']=$this->getLevelfromDB($v4['codice']);
							if (count($acc[$k0]['dad'][$k1]['dad'][$k2]['dad'][$k3]['dad'][$k4]['dad'])>0){
							  echo '<ul class="">';
							  foreach ($acc[$k0]['dad'][$k1]['dad'][$k2]['dad'][$k3]['dad'][$k4]['dad'] as $k5=>$v5){
							    echo '<li><div<div style="background-color: #'.$c.'">><a class="btn btn-xs btn-default" href="admin_artico.php?Update&amp;codice=' . $v5['codice'] . '">'.$v5['codice'].'</a> - '.$v5['descri'].'<span class="pull-right">  pz: '.floatval($v5['quantita_artico_base']).'</span></div></li>';
								$c=($c=='fcfcfc')?'eeeeee':'fcfcfc';
							  }
							  echo '</ul>';
							}
							echo '</li>';
						  }
						  echo '</ul>';
						}
						echo '</li>';
					  }
					  echo '</ul>';
					}
					echo '</li>';
				  }
	  			  echo '</ul>';
				}
				echo '</li>';
			  }
			  echo '</ul>';
			}
			echo '</li>';
		  }
		  echo "</ul></div></div>\n";
		} 
	}

	function getLevelfromDB($codcomp){
        global $gTables;
		$acc=[];
		$rs = gaz_dbi_dyn_query("quantita_artico_base,codice_composizione,codice_artico_base", $gTables['distinta_base'], "codice_artico_base = '".$codcomp."' GROUP BY codice_composizione");
		while ($r = gaz_dbi_fetch_assoc($rs)) {
			$art=gaz_dbi_get_row($gTables['artico'], "codice", $r['codice_composizione']);
			$r['codice']=$art['codice'];
			$r['descri']=$art['descri'];
			$acc[]=$r;
		}
		return $acc;
	}
	
    function print_trunk_BOM($codcomp) {  // Stampo i padri nei quali è contenuto l'articolo
	}
	
    function getStockValue($id_mov = false, $item_code = null, $date = null, $stock_eval_method = null, $decimal_price = 2)
    /* Questa funzione serve per restituire la valorizzazione dello scarico
      a seconda del metodo (WMA,LIFO,FIFO) scelto per ottenerla.
      Puo' essere sufficiente valorizzare il solo $id_mov, ma questo costringe
      la funzione ad una query per ottenere gli altri valori; oppure il solo
      codice dell'articolo, in questo caso si prende in considerazione l'ultimo
      movimento riferito all'articolo
     */ {
        global $gTables;
        if (!$id_mov && empty($item_code)) { // non ho nulla!
            return array('q' => 0, 'v' => 0, 'q_g' => 0, 'v_g' => 0);
        } elseif (!$id_mov && !empty($item_code)) {    // ho il codice articolo  senza id
            if ($date) { // ho anche la data
                $rs_last_mov = gaz_dbi_dyn_query("*", $gTables['movmag'], "artico = '" . $item_code . "' AND datreg <= '$date'", "datreg DESC, id_mov DESC", 0, 1);
            } else {   // non ho la data limite
                $rs_last_mov = gaz_dbi_dyn_query("*", $gTables['movmag'], "artico = '" . $item_code . "'", "datreg DESC, id_mov DESC", 0, 1);
            }
            $last_mov = gaz_dbi_fetch_array($rs_last_mov);
            if ($last_mov) {
                $id_mov = $last_mov['id_mov'];
                $date = $last_mov['datreg'];
            } else {
                return array('q' => 0, 'v' => 0, 'q_g' => 0, 'v_g' => 0);
            }
        } elseif (!$date || empty($item_code)) {    //ho il solo id_mov
            $mm = gaz_dbi_get_row($gTables['movmag'], "id_mov", $id_mov);
            $date = $mm['datreg'];
            $item_code = $mm['artico'];
        }
        if (!$stock_eval_method) {
            $stock_eval_method = $this->getStockEvalMethod();
        }
				
			$rs_last_inventory = gaz_dbi_dyn_query("*", $gTables['movmag'], "artico = '$item_code' AND caumag = 99 AND (datreg < '" . $date . "' OR (datreg = '" . $date . "' AND id_mov <= $id_mov ))", "datreg DESC, id_mov DESC", 0, 1);
			 $last_inventory = gaz_dbi_fetch_array($rs_last_inventory);
			if ($last_inventory) {
				$last_invDate = $last_inventory['datreg'];
				$last_invPrice = $last_inventory['prezzo'];
				$last_invQuanti = $last_inventory['quanti'];
			} else {
				$last_invDate = '2000-01-01';
				$last_invPrice = 0;
				$last_invQuanti = 0;
			}
		
		// fine ricerca inventario
		 
        $utsdatePrev = mktime(0, 0, 0, intval(substr($date, 5, 2)), intval(substr($date, 8, 2)) - 1, intval(substr($date, 0, 4)));
        $datePrev = date("Y-m-d", $utsdatePrev);
        $where = "artico = '$item_code' AND (datreg BETWEEN '$last_invDate' AND '$datePrev' OR (datreg = '$date' AND id_mov <= $id_mov))";
        $orderby = "datreg ASC, id_mov ASC"; //ordino in base alle date 
        $return_val = array();
        $accumulatore = array();
        switch ($stock_eval_method) { //calcolo il nuovo valore in base al metodo scelto in configurazione azienda
            case "0": //standard
            case "3": // FIFO
                $rs_movmag = gaz_dbi_dyn_query("*", $gTables['movmag'], "caumag < 99 AND " . $where, $orderby);
                // Qui metto i valori dell'ultimo inventario
                $accumulatore[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice);
                $giacenza = array('q_g' => $last_invQuanti, 'v_g' => $last_invPrice * $last_invQuanti);
                $return_val[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice,
                    'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                // Fine valorizzazione con ultimo inventario
                while ($r = gaz_dbi_fetch_array($rs_movmag)) {
                    // questo e' il prezzo che usero' solo per gli acquisti
                    $row_val = CalcolaImportoRigo(1, $r['prezzo'], array($r['scorig'], $r['scochi']), $decimal_price);
                    if ($r['operat'] == 1) { //carico
                        $accumulatore[] = array('q' => $r['quanti'], 'v' => $row_val);
                        $giacenza['q_g']+=$r['quanti'];
                        $giacenza['v_g']+=$r['quanti'] * $row_val;
                        if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                            $return_val[0] = array('q' => $r['quanti'], 'v' => $row_val,
                                'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                        }
                    } elseif ($r['operat'] == -1) { //scarico
                        $return_val = array(); //azzero l'accumulatore per il ritorno
                        foreach ($accumulatore as $k => $acc_val) {   //attraverso l'accumulatore
                            if ($acc_val['q'] > $r['quanti']) { // la quantita' nell'accumulatore e' sufficiente per coprire lo scarico
                                $accumulatore[$k]['q'] -= $r['quanti'];
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                $r['quanti'] = 0;
                                break;
                            } elseif ($acc_val['q'] == $r['quanti']) {  // la quantita' da scaricare e' la stessa nell'accumulatore
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                                $r['quanti'] = 0;
                                break;
                            } else {  // la quantita' da scaricare e' maggiore di quella nell'accumulatore
                                $r['quanti'] -= $acc_val['q'];
                                $giacenza['q_g']-=$acc_val['q'];
                                $giacenza['v_g']-=$acc_val['q'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                    $return_val[] = array('q' => $acc_val['q'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                            }
                        }
                        // esco dal loop ma potrebbe accadere che i carichi non erano sufficienti a coprire lo scarico
                        if ($r['quanti'] > 0) { // e' il movimento che voglio valorizzare: lo accumulo
                            $giacenza['q_g']-=$r['quanti'];
                            $giacenza['v_g']-=0;
                            if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                $return_val[] = array('q' => -$r['quanti'], 'v' => 0,
                                    'q_g' => $giacenza['q_g'], 'v_g' => 0);
                            }
                        }
                    }
                }
                break;
            case "1": // WMA
                $rs_movmag = gaz_dbi_dyn_query("*", $gTables['movmag'], $where . " AND caumag < 99", $orderby);
                $giacenza = array('q_g' => $last_invQuanti, 'v_g' => $last_invPrice * $last_invQuanti);
                $return_val[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice,
                    'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                while ($r = gaz_dbi_fetch_array($rs_movmag)) {
                    if ($r['operat'] == 1) { //carico
                        $row_val = CalcolaImportoRigo(1, $r['prezzo'], array($r['scorig'], $r['scochi']), $decimal_price);
                        $giacenza['q_g']+=$r['quanti'];
                        $giacenza['v_g']+=$r['quanti'] * $row_val;
                    } elseif ($r['operat'] == -1) { //scarico
                        if ($giacenza['q_g'] <= 0) { // se la quantità è già sotto zero forzo anche il valore a 0
                            $giacenza['v_g'] = 0;
                            $row_val = 0;
                        } else {
                            $row_val = $giacenza['v_g'] / $giacenza['q_g'];
                        }
                        if ($giacenza['q_g'] <= $r['quanti']) { // se la quantità è andata sotto zero forzo anche il valore a 0
                            $giacenza['v_g'] = 0;
                            $row_val = 0;
                        }
                        $giacenza['q_g']-=$r['quanti'];
                        $giacenza['v_g']-=$r['quanti'] * $row_val;
                    }
                    if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare
                        $return_val[0] = array('q' => $r['quanti'], 'v' => $row_val,
                            'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                    }
                }
                break;
            case "2": // LIFO
                $rs_movmag = gaz_dbi_dyn_query("*", $gTables['movmag'], $where . " AND caumag < 99", $orderby);
                // Qui metto i valori dell'ultimo inventario
                $accumulatore[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice);
                $giacenza = array('q_g' => $last_invQuanti, 'v_g' => $last_invPrice * $last_invQuanti);
                $return_val[0] = array('q' => $last_invQuanti, 'v' => $last_invPrice,
                    'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                // Fine valorizzazione con ultimo inventario
                while ($r = gaz_dbi_fetch_array($rs_movmag)) {
                    // questo e' il prezzo che usero' solo per gli acquisti
                    $row_val = CalcolaImportoRigo(1, $r['prezzo'], array($r['scorig'], $r['scochi']));
                    if ($r['operat'] == 1) { //carico
                        $accumulatore[] = array('q' => $r['quanti'], 'v' => $row_val);
                        $giacenza['q_g']+=$r['quanti'];
                        $giacenza['v_g']+=$r['quanti'] * $row_val;
                        if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                            $return_val[0] = array('q' => $r['quanti'], 'v' => $row_val,
                                'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                        }
                    } elseif ($r['operat'] == -1) { //scarico
                        $return_val = array(); //azzero l'accumulatore per il ritorno
                        $accumulatore = array_reverse($accumulatore);
                        foreach ($accumulatore as $k => $acc_val) {   //attraverso l'accumulatore
                            if ($acc_val['q'] > $r['quanti']) { // la quantita' nell'accumulatore e' sufficiente per coprire lo scarico
                                $accumulatore[$k]['q'] -= $r['quanti'];
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                $r['quanti'] = 0;
                                break;
                            } elseif ($acc_val['q'] == $r['quanti']) {  // la quantita' da scaricare e' la stessa nell'accumulatore
                                $giacenza['q_g']-=$r['quanti'];
                                $giacenza['v_g']-=$r['quanti'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento di riferimento
                                    $return_val[] = array('q' => $r['quanti'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                                $r['quanti'] = 0;
                                break;
                            } else {  // la quantita' da scaricare e' maggiore di quella nell'accumulatore
                                $r['quanti'] -= $acc_val['q'];
                                $giacenza['q_g']-=$acc_val['q'];
                                $giacenza['v_g']-=$acc_val['q'] * $acc_val['v'];
                                if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                    $return_val[] = array('q' => $acc_val['q'], 'v' => $acc_val['v'],
                                        'q_g' => $giacenza['q_g'], 'v_g' => $giacenza['v_g']);
                                }
                                unset($accumulatore[$k]);
                            }
                        }
                        $accumulatore = array_reverse($accumulatore);
                        // esco dal loop ma potrebbe accadere che i carichi non erano sufficienti a coprire lo scarico
                        if ($r['quanti'] > 0) { // e' il movimento che voglio valorizzare: lo accumulo
                            $giacenza['q_g']-=$r['quanti'];
                            $giacenza['v_g']-=0;
                            if ($r['id_mov'] == $id_mov) { // e' il movimento che voglio valorizzare: lo accumulo
                                $return_val[] = array('q' => -$r['quanti'], 'v' => 0,
                                    'q_g' => $giacenza['q_g'], 'v_g' => 0);
                            }
                        }
                    }
                }

                break;
            default:
        }
        return $return_val;
    }

    function uploadMag($id_rigo_doc = '0', $tipdoc='', $numdoc=0, $seziva='', $datdoc='', $clfoco=0, $sconto_chiusura=0, $caumag='', $codart='', $quantita=0, $prezzo=0, $sconto_rigo=0, $id_movmag = 0, $stock_eval_method = null, $data_from_admin_mov = false, $protoc = '',$id_lotmag=0,$id_orderman=0,$campo_coltivazione=0) {  // su id_rigo_doc 0 per inserire 1 o + per fare l'upload 'DEL' per eliminare il movimento
        // in $data_from_admin_mov  ci sono i dati in più provenienti da admin_movmag (desdoc,operat, datreg)
        global $gTables, $admin_aziend;
        $docOperat = $this->getOperators();
        if ($tipdoc == 'FAD') {  // per il magazzino una fattura differita è come dire DDT
            $tipdoc = 'DDT';
        }
        if (substr($tipdoc, 0, 1) == 'A' or $tipdoc == 'DDR' or $tipdoc == 'DDL') { //documento di acquisto
            require("../../modules/acquis/lang." . $admin_aziend['lang'] . ".php");
            $desdoc = $strScript['admin_docacq.php'][0][$tipdoc];
        } elseif ($tipdoc == 'INV') {
            require("../../modules/magazz/lang." . $admin_aziend['lang'] . ".php");
            $desdoc = $strScript['admin_artico.php']['esiste'];
        } else {//documento di vendita
            require("../../modules/vendit/lang." . $admin_aziend['lang'] . ".php");
            $desdoc = $strScript['admin_docven.php']['doc_name'][$tipdoc];
        }
        if (substr($tipdoc, 0, 1) == 'D' || $tipdoc == 'VCO') {
            $desdoc .= " n." . $numdoc;
            if ($seziva != '')
                $desdoc .= "/" . $seziva;
        } else {
            $desdoc .= " n." . $numdoc;
            if ($seziva != '')
                $desdoc .= "/" . $seziva;
            $desdoc .= " prot." . $protoc;
            if ($seziva != '')
                $desdoc .= "/" . $seziva;
        }
        $new_caumag = gaz_dbi_get_row($gTables['caumag'], 'codice', $caumag);
        $operat = $new_caumag['operat'];
        if (!$data_from_admin_mov) {         // se viene da un documento
            $datreg = $datdoc;               // la data di registrazione coincide con quella del documento
            $operat = $docOperat[$tipdoc];    // e la descrizione la ricavo dal tipo documento
        } else {                            // se � stato passato l'array dei dati
            $datreg = $data_from_admin_mov['datreg']; // prendo la descrizione e l'operatore da questo
            $operat = $data_from_admin_mov['operat'];
            $desdoc = $data_from_admin_mov['desdoc'];
		}
        $row_movmag = array('caumag' => $caumag,
            'operat' => $operat,
            'datreg' => $datreg,
            'tipdoc' => $tipdoc,
            'desdoc' => $desdoc,
            'datdoc' => $datdoc,
            'clfoco' => $clfoco,
            'scochi' => $sconto_chiusura,
            'id_rif' => $id_rigo_doc,
            'artico' => $codart,
            'quanti' => $quantita,
            'prezzo' => $prezzo,
            'scorig' => $sconto_rigo,
            'id_lotmag'=>$id_lotmag,
			'id_orderman'=>$id_orderman,
			'campo_coltivazione'=>$campo_coltivazione,
			'synccommerce_classname'=>$admin_aziend['synccommerce_classname']);
        if ($id_movmag == 0) {                             // si deve inserire un nuovo movimento
            $id_movmag = movmagInsert($row_movmag);
            //gaz_dbi_put_row($gTables['rigdoc'], 'id_rig', $id_rigo_doc, 'id_mag', gaz_dbi_last_id());
            gaz_dbi_query("UPDATE " . $gTables['rigdoc'] . " SET id_mag = " . $id_movmag . " WHERE `id_rig` = $id_rigo_doc ");
        } elseif ($id_rigo_doc === 'DEL') {                 // si deve eliminare un movimento esistente
            $old_movmag = gaz_dbi_get_row($gTables['movmag'], 'id_mov', $id_movmag);
            $old_caumag = gaz_dbi_get_row($gTables['caumag'], 'codice', $old_movmag['caumag']);
            gaz_dbi_del_row($gTables['movmag'], 'id_mov', $id_movmag);
            $codart = $old_movmag['artico'];
            if (!empty($admin_aziend['synccommerce_classname']) && class_exists($admin_aziend['synccommerce_classname'])){
                // aggiorno l'e-commerce ove presente
                $gs=$admin_aziend['synccommerce_classname'];
                $gSync = new $gs();
				if($gSync->api_token && isset($codart)){
					$gSync->SetProductQuantity($codart);
				}				
			}
        } else {   // si deve modificare un movimento esistente
            $old_movmag = gaz_dbi_get_row($gTables['movmag'], 'id_mov', $id_movmag);
            $old_caumag = gaz_dbi_get_row($gTables['caumag'], 'codice', $old_movmag['caumag']);
            $id = array('id_mov', $id_movmag);
            if (!isset($new_caumag['operat'])) {
                $new_caumag['operat'] = 0;
            }
            if (!isset($old_caumag['operat'])) {
                $old_caumag['operat'] = 0;
            }
            movmagUpdate($id, $row_movmag);
        }
        return $id_movmag;
    }

    /* sends a Javascript toast to the client */

    function toast($message, $id = 'alert-discount', $class = 'alert-warning') {
        /*
          echo "<script type='text/javascript'>toast('$message');</script>"; */
        if (!empty($message)) {
            echo '<div class="container">
					<div id="' . $id . '" class="row alert ' . $class . ' fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
							<span aria-hidden="true">&times;</span>
						</button>
						<span class="glyphicon glyphicon-alert" aria-hidden="true"></span>&nbsp;' . $message . '
					</div>
				  </div>';
        }
        return '';
    }

    function getLastBuys($codart, $rettable=false) {
      global $gTables, $admin_aziend;
      // ritorna un array con gli acquisti aggregati per fornitore
      $rs=gaz_dbi_query("SELECT ".$gTables['movmag'] .".desdoc,".$gTables['movmag'] .".quanti,".$gTables['movmag'] .".scorig,".$gTables['movmag'] .".prezzo, ".$gTables['rigdoc'] .".id_tes AS docref, ".$gTables['rigdoc'] .".codice_fornitore, CONCAT(".$gTables['anagra'] .".ragso1,".$gTables['anagra'] .".ragso2) AS supplier, SUM(ROUND(".$gTables['movmag'] .".quanti*prezzo*(100-scorig)/100,2)) AS amount FROM " . $gTables['movmag'] . " LEFT JOIN ".$gTables['clfoco'] ." ON ".$gTables['movmag'] .".clfoco = ".$gTables['clfoco'] .".codice LEFT JOIN ".$gTables['anagra'] ." ON ".$gTables['clfoco'] .".id_anagra = ".$gTables['anagra'] .".id LEFT JOIN ".$gTables['rigdoc'] ." ON ".$gTables['movmag'] .".id_rif = ".$gTables['rigdoc'] .".id_rig WHERE ".$gTables['movmag'] .".artico = '".$codart."' AND ".$gTables['movmag'] .".clfoco LIKE '". $admin_aziend['masfor'] ."%' GROUP BY ".$gTables['movmag'] .".clfoco ORDER BY ".$gTables['movmag'] .".datdoc DESC");
      $acc=false;
      while ($r = gaz_dbi_fetch_array($rs)) {
        if ($rettable){
          // creo una tabella direttamente stampabile
          //print_r($r);
          $acc .= '<div class="col-xs-1"></div><div class="col-xs-11 row"><div class="col-sm-4">'.$r['supplier'].'</div><div class="col-sm-4"><a class="btn btn-default btn-xs" href="../acquis/admin_docacq.php?Update&id_tes='.$r['docref'].'">'.$r['desdoc'].'</a></div><div class="col-sm-4"><b>'.$r['codice_fornitore'].'</b> '.floatval($r['quanti']).' x '.floatval($r['prezzo']).(($r['scorig']>0.01)?(' sconto:'.floatval($r['scorig']).'% '):('')).' tot. € '.floatval($r['amount']).'</div></div>'; 
        } else {
          // creo un array con chiave il codice fornitore
          $acc[$r['clfoco']]=$r;  
        }
      }
      return $acc;
    }
    
    
}
 function getLastSianDay(){ // restituisce la data nel formato aaaa-mm-gg dell'ultimo movimento SIAN creato
	$admin_aziend = checkAdmin();	
	if ($handle = opendir(DATA_DIR . 'files/' . $admin_aziend['codice'] . '/sian/')){
			$i=0;
			while (false !== ($file = readdir($handle))){
				if (substr($file,-12) == "OPERREGI.txt"){
					if ($file=="." OR $file==".."){ continue;}
						$prevfiles[$i]['nome']=$file; // prendo nome file
						$prevfiles[$i]['content']=@file_get_contents(DATA_DIR . 'files/' . $admin_aziend['codice'] . '/sian/'.$file);// prendo contenuto file
						$i++;	
				}			
			}
			closedir($handle);
			if (isset($prevfiles)){ // se ci sono file
				rsort($prevfiles);// ordino per nome file
			}
		}
		// vedo se l'ultimo file è di tipo 'I'nserimento o 'C'ancellazione
		if (isset($prevfiles)){ // se ci sono files
			for ($n=0 ; $n <= $i-1 ; $n++){
				if (substr($prevfiles[$n]['content'],875,1)=="I"){ // se il file è di inserimento ne prendo la data dell'ultimo record
					$fileField=explode (";",$prevfiles[$n]['content']);
					$uldtfile=$fileField[((((count($fileField)-1)/49)-1)*49)+3];
					$uldtfile=substr($uldtfile,4,4)."-".substr($uldtfile,2,2)."-".substr($uldtfile,0,2);// imposto la data aaaa-mm-gg
					break; // esco dal ciclo
				} else { // se non è 'I', cioè è 'C', faccio saltare il file successivo perché annullato da questo
					$n++;
				}
			}
		}		
	return $uldtfile ;
}

?>