<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend = checkAdmin();
$msg = "";$print_magval="";$dose="";$dim_campo="";
$gForm = new magazzForm(); // Antonio Germani attivo funzione calcolo giacenza di magazzino

if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

$form = array();
/* Antonio Germani non serve per Q.d.c.
function getItemPrice($item, $partner = 0) {
    global $admin_aziend, $gTables;
    $artico = gaz_dbi_get_row($gTables['artico'], 'codice', $item);
    if ($partner > 0) {
        $partner = gaz_dbi_get_row($gTables['clfoco'], 'codice', $partner);
        $list = $partner['listin'];
        if (substr($partner['codice'], 0, 3) == $admin_aziend['mascli'] && $list > 0 && $list <= 3) {
            $price = $artico["preve$list"];
        } else {
            $price = $artico["preacq"];
        }
        $sconto = $partner['sconto'];
    } else { // prezzo articolo
        $sconto = 0;
        $price = $artico["preve1"];
    }
    return CalcolaImportoRigo(1, $price, $sconto, $admin_aziend['decimal_price']);
}
*/
if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
    if (!isset($_GET['id_mov'])) {
        header("Location: " . $_POST['ritorno']);
        exit;
    } else {
        $_POST['id_mov'] = $_GET['id_mov'];
    }
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if (!isset($_POST['Update']) and isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    $form['hidden_req'] = '';
    //recupero il movimento
    $result = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
    $form['id_mov'] = $result['id_mov'];
	$form['type_mov'] = $result['type_mov'];
    $form['id_rif'] = $result['id_rif'];
    $form['caumag'] = $result['caumag'];
    $form['operat'] = $result['operat'];
    $form['gioreg'] = substr($result['datreg'], 8, 2);
    $form['mesreg'] = substr($result['datreg'], 5, 2);
    $form['annreg'] = substr($result['datreg'], 0, 4);
    $form['campo_coltivazione'] = $result['campo_coltivazione']; //campo di coltivazione
	$form['clfoco'] = "";
	$form['adminid'] = $result['adminid'];
    if (!empty($form['caumag'])) { //controllo quale partner prevede la causale
        $rs_causal = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
        $form['clorfo'] = $rs_causal['clifor']; //cliente, fornitore o entrambi
    } else {
        $form['clorfo'] = 0; // entrambi
    }
    $form['tipdoc'] = $result['tipdoc'];
    $form['desdoc'] = $result['desdoc'];
    $form['avversita'] = $result['avversita'];
	$form['scochi'] = $result['scochi'];
    $form['giodoc'] = substr($result['datdoc'], 8, 2);
    $form['mesdoc'] = substr($result['datdoc'], 5, 2);
    $form['anndoc'] = substr($result['datdoc'], 0, 4);
    $form['artico'] = $result['artico'];
    $form['quanti'] = gaz_format_quantity($result['quanti'], 0, $admin_aziend['decimal_quantity']);
    $form['prezzo'] = number_format($result['prezzo'], $admin_aziend['decimal_price'], '.', '');
    $form['scorig'] = $result['scorig'];
    $form['status'] = $result['status'];
    $form['search_partner'] = "";
    $form['search_item'] = "";
} elseif (isset($_POST['Insert']) or isset($_POST['Update'])) {   //se non e' il primo accesso
    $form['hidden_req'] = htmlentities($_POST['hidden_req']);
    //ricarico i registri per il form facendo gli eventuali parsing
    $form['id_mov'] = intval($_POST['id_mov']);
	$form['type_mov'] = 1;
    $form['id_rif'] = intval($_POST['id_rif']);
    $form['caumag'] = intval($_POST['caumag']);
    $form['operat'] = intval($_POST['operat']);
    $form['gioreg'] = intval($_POST['gioreg']);
    $form['mesreg'] = intval($_POST['mesreg']);
    $form['annreg'] = intval($_POST['annreg']);
	$form['clfoco'] = "";
    $form['clorfo'] = ""; //cliente, fornitore o entrambi
    $form['campo_coltivazione'] = intval($_POST['campo_coltivazione']); //campo di coltivazione
	$form['adminid'] = "Utente connesso";
//$form['clorfo'] = $_POST['clorfo']; //era cliente, fornitore -> adesso non serve per Q.d.c.
    $form['tipdoc'] = intval($_POST['tipdoc']);
    $form['desdoc'] = substr($_POST['desdoc'], 0, 50);
    $form['giodoc'] = intval($_POST['giodoc']);
    $form['mesdoc'] = intval($_POST['mesdoc']);
    $form['anndoc'] = intval($_POST['anndoc']);
	$form['scochi'] = "";
    $form['avversita'] = substr($_POST['avversita'],0,50);
    $form['artico'] = $_POST['artico'];
    $form['quanti'] = gaz_format_quantity($_POST['quanti'], 0, $admin_aziend['decimal_quantity']);
   $form['prezzo'] = 0;
   $form['scorig'] = 0;
    $form['status'] = substr($_POST['status'], 0, 10);
// Antonio Germani tolto non serve $form['search_partner'] = $_POST['search_partner'];
    $form['search_item'] = $_POST['search_item'];
    // Se viene inviata la richiesta di conferma della causale la carico con le relative contropartite...
    /** ENRICO FEDELE */
    /* Con button non funziona _x */
    //if (isset($_POST['inscau_x'])) {
    /** ENRICO FEDELE */
    if (isset($_POST['inscau'])) {          
        $causa = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
        $form['operat'] = $causa['operat'];
		
		/* Antonio Germani questo non serve per il Q.d.c.
        $form['clorfo'] = $causa['clifor']; //cliente, fornitore o entrambi
        if (($causa['clifor'] < 0 and substr($form['clfoco'], 0, 3) == $admin_aziend['masfor']) or ( $causa['clifor'] > 0 and substr($form['clfoco'], 0, 3) == $admin_aziend['mascli'])) {
            $form['clfoco'] = 0;
            $form['search_partner'] = "";
        }
		// fine non serve */
		
        if ($causa['insdoc'] == 0) {//se la nuova causale non prevede i dati del documento
            $form['tipdoc'] = "";
            $form['desdoc'] = "";
            $form['giodoc'] = "";
            $form['mesdoc'] = "";
            $form['anndoc'] = "";
            $form['scochi'] = "";
            $form['id_rif'] = 0;
        }
    }
	
	/* Antonio Germani non serve per il Q.d.c.
    if (isset($_POST['newpartner'])) {
        $anagrafica = new Anagrafica();
        $partner = $anagrafica->getPartner($_POST['clfoco']);
        $form['search_partner'] = substr($partner['ragso1'], 0, 4);
        $form['clfoco'] = 0;
    }
	Fine non serve */
	
    if (isset($_POST['newitem'])) {
        $result = gaz_dbi_get_row($gTables['artico'], "codice", $_POST['artico']);
        $form['search_item'] = substr($result['codice'], 0, 4);
        $form['artico'] = "";
    }
    if (isset($_POST['Return'])) {
        header("Location: " . $_POST['ritorno']);
        exit;
    }
/* Antonio Germani non serve per Q.d.c.    if ($_POST['hidden_req'] == 'new_price') {
        $form['prezzo'] = getItemPrice($form['artico'], $form['clfoco']);
        $form['hidden_req'] = '';
    } */
    if (!empty($_POST['Insert'])) {   // Se viene inviata la richiesta di conferma totale ...
        $utsreg = mktime(0, 0, 0, $form['mesreg'], $form['gioreg'], $form['annreg']);
        $utsdoc = mktime(0, 0, 0, $form['mesdoc'], $form['giodoc'], $form['anndoc']);
        if (!checkdate($form['mesreg'], $form['gioreg'], $form['annreg']))
            $msg .= "16+";
        if (!checkdate($form['mesdoc'], $form['giodoc'], $form['anndoc']))
            $msg .= "15+";
        if ($utsdoc > $utsreg) {
            $msg .= "17+";
        }
        if (empty($form['artico'])) {  //manca l'articolo
            $msg .= "18+";
        }
        if ($form['quanti'] == 0) {  //la quantità è zero
            $msg .= "19+";
        }
				
		
	 // Antonio Germani calcolo giacenza di magazzino, la metto in $print_magval e, se è uno scarico, controllo sufficiente giacenza
	 $mv = $gForm->getStockValue(false, $form['artico']);
        $magval = array_pop($mv); $print_magval=floatval($magval['q_g']);
		if (isset($_POST['Update'])) {
			$qta = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
			// prendo la quantità precedentemente memorizzata e la riaggiungo alla giacenza di magazzino altrimenti il controllo quantità non funziona bene
			$print_magval=$print_magval+$qta['quanti'];}
		if ($form["operat"] == -1 and ($print_magval-$form['quanti']<0)) { //Antonio Germani quantità insufficiente
			$msg .= "23+";
			}
			
//Antonio Germani prendo e metto la data di fine sospensione del campo di coltivazione selezionato in $fine_sosp 
		$campo_coltivazione=$form['campo_coltivazione'];//campo di coltivazione inserito nel form
		$query="SELECT ".'giorno_decadimento'.",".'ricarico'." FROM ".$gTables['campi']. " WHERE codice ='". $campo_coltivazione."'";
			$result = gaz_dbi_query($query);
			while ($row = $result->fetch_assoc()) {
			$fine_sosp=$row['giorno_decadimento']; $fine_sosp=strtotime($fine_sosp);
			$dim_campo=$row['ricarico'];// prendo pure la dimensione del campo e la metto in $dim_campo
			}
			// Antonio Germani Controllo se la quantità è giusta rapportata al campo di coltivazione
			$item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
			$dose=$item['dose_massima'];// prendo la dose
			$rame_metallo=$item['rame_metallico'];// già che ci sono, prendo anche il rame metallo del prodotto oggetto del movimento, che mi servirà per il prossimo controllo
			if ($dose>0 && $form['quanti'] > $dose*$dim_campo && $form["operat"]==-1 && $dim_campo>0) {
				$msg .="25+"; // errore dose eccessiva
			}
			
// Antonio Germani Calcolo quanto rame metallo è stato usato nell'anno di esecuzione di questo movimento
			If ($campo_coltivazione>0){ // se il prodotto va in un campo di coltivazione
				if ($rame_metallo>0){ //se questo prodotto contiene rame metallo
					$query="SELECT ".'artico'. ",".'datdoc'.",".'quanti'." FROM ".$gTables['movmag']. " WHERE datdoc >'". $form['anndoc'] ."' AND ".'campo_coltivazione'." = '".$campo_coltivazione."'"; // prendo solo le righe dell'anno di esecuzione del trattamento e degli anni successivi con il campo di coltivazione selezionato nel form
				}
			$result = gaz_dbi_query($query); $rame_met_annuo="";
						while ($row = $result->fetch_assoc()) {
							if (substr($row['datdoc'],0,4) == $form['anndoc']){ // elimino dal conteggio gli eventuali anni successivi
							$item = gaz_dbi_get_row($gTables['artico'], "codice", $row['artico']);
							if ($item['rame_metallico']>0){$rame_met_annuo=$rame_met_annuo+$item['rame_metallico']*$row['quanti'];}
							}
						}
						
			}
// fine calcolo rame

			// Antonio Germani controllo se con questo movimento non si supera la doce massima annua di 6Kg ad ha
			
				if (($campo_coltivazione>0)&&($dim_campo>0)&&($rame_met_annuo+($rame_metallo* $form['quanti'])> (6 * $dim_campo))) {
					$msg .="26+";echo "CONTROLLO rame metallo: <br> Rame metallo anno già usato: ",$rame_met_annuo," Rame metallo che si tenta di usare: ",($rame_metallo* $form['quanti']), " Limite annuo di legge per questo campo: ", (6 * $dim_campo);}	// errore superato il limite di rame metallo ad ettaro		
			
			




						
// Antonio Germani creo la data d I ATTUAZIONE DELL'OPERAZIONE selezionata che poi confronterò con quella di sospensione del campo 
		$dt=substr("0".$form['giodoc'],-2)."-".substr("0".$form['mesdoc'],-2)."-".$form['anndoc']; $dt=strtotime($dt); 			
// controllo se è ammesso il raccolto sul campo di coltivazione selezionato $msg .=24+ errore tempo di sospensione
		If ($form['campo_coltivazione']>0 && $form["operat"]==1 && intval($dt)<intval($fine_sosp)){
		
			$msg .="24+";	
			
		}
			
        if (empty($msg)) { // nessun errore
            $upd_mm = new magazzForm;
            //formatto le date
            $form['datreg'] = $form['annreg'] . "-" . $form['mesreg'] . "-" . $form['gioreg'];
            $form['datdoc'] = $form['anndoc'] . "-" . $form['mesdoc'] . "-" . $form['giodoc'];
            $new_caumag = gaz_dbi_get_row($gTables['caumag'], "codice", $form['caumag']);
            if (!empty($form['artico'])) {
                $upd_mm->uploadMag($form['id_rif'], $form['tipdoc'], 0, // numdoc � in desdoc
                        0, // seziva � in desdoc >>> $form['campo_coltivazione'], $form['avversita']
                        $form['datdoc'], $form['clfoco'], $form['scochi'], $form['caumag'], $form['artico'], $form['quanti'], $form['prezzo'], $form['scorig'], $form['id_mov'], $admin_aziend['stock_eval_method'], array('datreg' => $form['datreg'], 'operat' => $form['operat'], 'desdoc' => $form['desdoc'])
                );
		//Antonio Germani Non riesco a capire come funziona la funzione qui sopra; ho perso troppo tempo!!!!
		// risolvo in questa maniera per far scrivere i nuovi campi di movmag, specifici del quaderno di campagna
		If ($form['id_mov']>0){
			$id_movmag=$form['id_mov'];
			}
		else {
				$query="SHOW TABLE STATUS LIKE '".$gTables['movmag']."'"; 
				$result = gaz_dbi_query($query);
				$row = $result->fetch_assoc();
				$id_movmag = $row['Auto_increment'];
				// siccome ha già registrato il movimento di magazzino devo togliere 1
				$id_movmag=$id_movmag-1;
			}
		
		
		$query="UPDATE " . $gTables['movmag'] . " SET type_mov = '" . 1 .  "' , campo_coltivazione = '"  .$form['campo_coltivazione']. "' , avversita = '"  .$form['avversita'].  "' WHERE id_mov ='". $id_movmag."'";
			gaz_dbi_query ($query) ;
				
				
// Antonio Germani - aggiorno la tabella campi se c'è un campo inserito (cioè >0) e se l'operazione è uno scarico (cioè operat<0) e se la data di fine sospensione già presente nel campo è inferiore alla data di sospensione del prodotto appena usato (cioè $fine_sosp<$dt)

//Antonio Germani per prima cosa determino il codice del movimento eventualmente andra nella tabella del campo di coltivazione
if (!isset($_POST['Update'])){
// Antonio Germani se è un iserimento vedo quale sarà il prossimo codice del movimento del magazzino che verrà utilizzato !NB il codice è incremental!
$query="SHOW TABLE STATUS LIKE '".$gTables['movmag']."'"; 
$result = gaz_dbi_query($query);
$row = $result->fetch_assoc();
$id_mov = $row['Auto_increment'];
// siccome ha già registrato il movimento di magazzino devo togliere 1
$id_mov=$id_mov-1; 
}
else {$id_mov=$form['id_mov'];} // se non è un nuovo inserimento prendo il codice del movimento di magazzino selezionato

// adesso vedo se si deve aggiornare il campo di coltivazione	
	if ($form['campo_coltivazione']>0 && $form["operat"]<0) {
/* Antonio Germani creo la data del trattamento selezionato a cui poi aggiungerò i giorni di sospensione. */
		$dt=substr("0".$form['giodoc'],-2)."-".substr("0".$form['mesdoc'],-2)."-".$form['anndoc']; $dt=strtotime($dt); 

// Antonio Germani prendo i giorni del tempo di sospensione dall'articolo selezionato e li aggiungo al giorno del trattamento (Un giorno = 86400 timestamp)
		$artico= $form['artico'];
		$query="SELECT ".'tempo_sospensione'." FROM ".$gTables['artico']. " WHERE codice ='". $artico."'";
		$result = gaz_dbi_query($query);
			while ($row = $result->fetch_assoc()) {
			 $temp_sosp=$row['tempo_sospensione'];
			}
			$dt=$dt+(86400*intval($temp_sosp));
// Antonio Germani controllo se il tempo di sospensione del campo di coltivazione è inferiore a quello che si crea con questo trattamento aggiorno il database campi nel campo di coltivazione selezionato
		if (intval($fine_sosp)<intval($dt)) {
			$dt=date('Y/m/d', $dt);	
			$codcamp=$form['campo_coltivazione'];
			$query="UPDATE " . $gTables['campi'] . " SET giorno_decadimento = '" . $dt .  "' , codice_prodotto_usato = '"  .$artico. "' , id_mov = '"  .$id_mov.  "' WHERE codice ='". $codcamp."'";
			gaz_dbi_query ($query) ;
		}
	}
// fine gestione giorno di sospensione tabella campi 

            }
            header("Location:report_movmag.php");
            exit;
        }
    }
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['hidden_req'] = '';
    //registri per il form della testata
    $form['id_mov'] = 0;
	$form['type_mov'] = 1;
    $form['gioreg'] = date("d");
    $form['mesreg'] = date("m");
    $form['annreg'] = date("Y");
    $form['caumag'] = "";
    $form['operat'] = 0;
    $form['campo_coltivazione'] = ""; //campo di coltivazione
	$form['clfoco'] = "";
    $form['clorfo'] = 0;
	$form['adminid'] = "Utente connesso";
    $form['tipdoc'] = "";
    $form['desdoc'] = "";
    $form['giodoc'] = date("d");
    $form['mesdoc'] = date("m");
    $form['anndoc'] = date("Y");
    $form['scochi'] = "";
	$form['avversita'] = "";
    $form['artico'] = "";
    $form['quanti'] = 0;
    $form['prezzo'] = 0;
    $form['scorig'] = 0;
    $form['status'] = "";
    $form['search_partner'] = "";
    $form['search_item'] = "";
    $form['id_rif'] = 0;
}

require("../../library/include/header.php");
$script_transl = HeadMain();
require("./lang." . $admin_aziend['lang'] . ".php");
if ($form['id_mov'] > 0) {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0]) . " n." . $form['id_mov'];
} else {
    $title = ucfirst($script_transl[$toDo] . $script_transl[0]);
}
?>
<SCRIPT LANGUAGE="JavaScript">
    function CalcolaImportoRigo()
    {
        var p = document.myform.prezzo.value.toString().replace(/\,/g, '.') * 1;
        if (isNaN(p)) {
            p = 0;
        }
        var q = document.myform.quanti.value.toString().replace(/\,/g, '.') * 1;
        if (isNaN(q)) {
            q = 0;
        }
        var s = document.myform.scorig.value.toString().replace(/\,/g, '.') * 1;
        if (isNaN(s)) {
            s = 0;
        }
        
        var sommarigo = p * q - p * q * s / 100;
        var sommatotale = sommarigo - sommarigo * c / 100;
        return((Math.round(sommatotale * 100) / 100).toFixed(2));
    }
</SCRIPT>
<?php
echo "<form method=\"POST\" name=\"myform\">";
echo "<input type=\"hidden\" name=\"" . ucfirst($toDo) . "\" value=\"\">\n";
echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"" . $_POST['ritorno'] . "\">\n";
echo "<input type=\"hidden\" name=\"id_mov\" value=\"" . $form['id_mov'] . "\">\n";
echo "<input type=\"hidden\" name=\"id_rif\" value=\"" . $form['id_rif'] . "\">\n";
echo "<input type=\"hidden\" name=\"tipdoc\" value=\"" . $form['tipdoc'] . "\">\n";
echo "<input type=\"hidden\" name=\"status\" value=\"" . $form['status'] . "\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">$title</div>\n";

echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
if (!empty($msg)) {
    $message = "";
    $rsmsg = array_slice(explode('+', chop($msg)), 0, -1);
    foreach ($rsmsg as $value) {
        $message .= $script_transl['error'] . "! -> ";
        $rsval = explode('-', chop($value));
        foreach ($rsval as $valmsg) {
            $message .= $script_transl[$valmsg] . " ";
        }
        $message .= "<br />";
    }
    echo '<tr><td colspan="5" class="FacetDataTDred">' . $message . "</td></tr>\n";
}
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[1] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"gioreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
    $selected = "";
    if ($counter == $form['gioreg'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
    $selected = "";
    if ($counter == $form['mesreg'])
        $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"annreg\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
    $selected = "";
    if ($counter == $form['annreg'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select></td>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl[2] . "</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"caumag\" class=\"FacetSelect\">\n";
echo "<option value=\"\">-------------</option>\n";
$result = gaz_dbi_dyn_query("*", $gTables['caumag'], " 1 ", "codice desc, descri asc");
while ($row = gaz_dbi_fetch_array($result)) {
    $selected = "";
    if ($form["caumag"] == $row['codice']) {
        $selected = " selected ";
    }
    echo "<option value=\"" . $row['codice'] . "\"" . $selected . ">" . $row['codice'] . " - " . $row['descri'] . "</option>\n";
}
echo '  </select>&nbsp;<button type="submit" class="btn btn-default btn-sm" name="inscau" title="' . $script_transl['submit'] . '!"><i class="glyphicon glyphicon-ok"></i></button>
		</td>
	   </tr>';

    $unimis = "unimis";

 /*antonio Germani campo coltivazione  */
 
echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[3] . "</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"campo_coltivazione\" class=\"FacetSelect\">\n";
echo "<option value=\"\">-------------</option>\n";
$result = gaz_dbi_dyn_query("*", $gTables['campi']);
while ($row = gaz_dbi_fetch_array($result)) {
    $selected = "";
    if ($form["campo_coltivazione"] == $row['codice']) {
        $selected = " selected ";
    }
    echo "<option value=\"" . $row['codice'] . "\"" . $selected . ">" . $row['codice'] . " - " . $row['descri'] . "</option>\n";
} 
echo "</select>&nbsp;";
// prendo la dimesione del campo
$item = gaz_dbi_get_row($gTables['campi'], "codice", $form['campo_coltivazione']);
echo "Superficie: ",$item["ricarico"]," ha";

echo '  <button type="submit" class="btn btn-default btn-sm" name="inscau" title="' . $script_transl['refresh'] . '!"><i class="glyphicon glyphicon-refresh"></i></button>  ';


 /* Antonio Germani qui si seleziona la data di attuazione */      	
echo "</td><td class=\"FacetFieldCaptionTD\">" . $script_transl[8] . "</td><td class=\"FacetDataTD\">\n";
echo "\t <select name=\"giodoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 31; $counter++) {
    $selected = "";
    if ($counter == $form['giodoc'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"mesdoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = 1; $counter <= 12; $counter++) {
    $selected = "";
    if ($counter == $form['mesdoc'])
        $selected = "selected";
    $nome_mese = ucwords(strftime("%B", mktime(0, 0, 0, $counter, 1, 0)));
    echo "\t <option value=\"$counter\"  $selected >$nome_mese</option>\n";
}
echo "\t </select>\n";
echo "\t <select name=\"anndoc\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
for ($counter = date("Y") - 10; $counter <= date("Y") + 10; $counter++) {
    $selected = "";
    if ($counter == $form['anndoc'])
        $selected = "selected";
    echo "\t <option value=\"$counter\"  $selected >$counter</option>\n";
}
/* fine qui si seleziona la data di attuazione */ 

echo "\t </select></td></tr>\n"; 

echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[9] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['desdoc'] . "\" maxlength=\"50\" size=\"35\" name=\"desdoc\"></td>";
/* Antonio Germani -  avversità */
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl[20] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['avversita'] . "\" maxlength=\"50\" size=\"35\" name=\"avversita\"></td></tr>";

/* Antonio Germani - prova ricerca automatica
$form['in_codart']="";$form['cosear']="";
echo "<tr><td class=\"FacetColumnTD\">$script_transl[7]: ";
$select_artico = new selectartico("in_codart");
$select_artico->addSelected($form['in_codart']);

$select_artico->output($form['cosear']);
echo "</td> </tr>";
*/ 

echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[7] . "</td><td class=\"FacetDataTD\">\n";
$messaggio = "";
$print_unimis = "";
$ric_mastro = substr($form['artico'], 0, 3);

echo "\t<input type=\"hidden\" name=\"artico\" value=\"" . $form['artico'] . "\">\n";
 
if ($form['artico'] == '') {
    if (strlen($form['search_item']) >= 1) {
        $result = gaz_dbi_dyn_query("*", $gTables['artico'], "codice like '" . $form['search_item'] . "%' ", "descri asc");
        if (gaz_dbi_num_rows($result) > 0) {
            echo "\t<select name=\"artico\" class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='new_price'; this.form.submit();\">\n";
            echo "<option value=\"\"> -------- </option>";
            while ($row = gaz_dbi_fetch_array($result)) {
                $selected = "";
                if ($row["codice"] == $form['artico']) {
                    $selected = "selected";
                }
                echo "\t\t <option value=\"" . $row["codice"] . "\" $selected >" . $row["descri"] . "&nbsp;</option>\n";
				
            }
            echo "\t </select>\n";
        } else {
            $messaggio = ucfirst($script_transl['notfound']) . " !";
        }
    } else {
        $messaggio = ucfirst($script_transl['minins']) . " 1 " . $script_transl['charat'] . "!";
    }
    echo "\t<input type=\"text\" name=\"search_item\" accesskey=\"e\" value=\"" . $form['search_item'] . "\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
    echo $messaggio;
    // echo "\t <input type=\"image\" align=\"middle\" accesskey=\"c\" name=\"search\" src=\"../../library/images/cerbut.gif\"></td>\n";
    /** ENRICO FEDELE */
    /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
    echo '&nbsp;<button type="submit" class="btn btn-default btn-sm" name="search" accesskey="c"><i class="glyphicon glyphicon-search"></i></button></td>';
    /** ENRICO FEDELE */
} else {
	
	
    $item = gaz_dbi_get_row($gTables['artico'], "codice", $form['artico']);
    $print_unimis = $item[$unimis];
	$dose=$item['dose_massima'];// prendo anche la dose
	// Antonio Germani calcolo giacenza di magazzino e la metto in $print_magval
	 $mv = $gForm->getStockValue(false, $item['codice']);
        $magval = array_pop($mv); $print_magval=floatval($magval['q_g']);
		if (isset($_POST['Update'])) {
			$qta = gaz_dbi_get_row($gTables['movmag'], "id_mov", $_GET['id_mov']);
			// Antonio Germani prendo la quantità precedentemente memorizzata e la riaggiungo alla giacenza di magazzino altrimenti il controllo quantità non funziona bene
			$print_magval=$print_magval+$qta['quanti'];}
	 
    echo "<input type=\"submit\" value=\"" . substr($item['descri'], 0, 30) . "\" name=\"newitem\" title=\"" . ucfirst($script_transl['update']) . "!\">\n ";if ($dose>0) {echo "dose: ",$dose," ",$print_unimis,"/ha";}
    echo "\t<input type=\"hidden\" name=\"artico\" value=\"" . $form['artico'] . "\">\n";
    echo "\t<input type=\"hidden\" name=\"search_item\" value=\"" . $form['search_item'] . "\">\n";
}
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl[12] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['quanti'] . "\" maxlength=\"10\" size=\"10\" name=\"quanti\" onChange=\"this.form.total.value=CalcolaImportoRigo();\"> $print_unimis". ' ',$script_transl[22],' '."$print_magval".' '."$print_unimis</td></tr>\n";
/* Antonio Germani sospendo il prezzo e lo sconto che nel quaderno di campagna non servono

echo "<tr><td class=\"FacetFieldCaptionTD\">" . $script_transl[13] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['prezzo'] . "\" maxlength=\"12\" size=\"12\" name=\"prezzo\" onChange=\"this.form.total.value=CalcolaImportoRigo();\"> " . $admin_aziend['symbol'] . "</td>\n";
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl[14] . "</td><td class=\"FacetDataTD\" ><input type=\"text\" value=\"" . $form['scorig'] . "\" maxlength=\"4\" size=\"4\" name=\"scorig\" onChange=\"this.form.total.value=CalcolaImportoRigo();\"> %</td></tr>\n";

fine sospendo prezzo e sconto */

echo "<td class=\"FacetFieldCaptionTD\">" . $strScript["admin_caumag.php"][4] . "</td><td class=\"FacetDataTD\">\n";
echo "<select name=\"operat\" class=\"FacetSelect\">\n";
for ($counter = -1; $counter <= 1; $counter++) {
    $selected = "";
    if ($form["operat"] == $counter) {
        $selected = " selected ";
    }
    echo "<option value=\"$counter\" $selected > " . $strScript["admin_caumag.php"][$counter + 9] . "</option>\n";
}


/*ANtonio Germani - visualizzo l'operatore */
echo "<td class=\"FacetFieldCaptionTD\">" . $script_transl[21]."</td><td class=\"FacetDataTD\">".$form["adminid"]."</td>\n"; 
/* fine visualizzo l'operatore */
echo "</select></td></tr><tr><td colspan=\"3\"><input type=\"reset\" name=\"Cancel\" value=\"" . $script_transl['cancel'] . "\">\n";

echo "<input type=\"submit\" name=\"Return\" value=\"" . $script_transl['return'] . "\">\n";
echo "</td><td align=\"right\">\n";

if ($toDo == 'update') {
    echo '<input type="submit" accesskey="m" name="Insert" value="' . strtoupper($script_transl['update']) . '!"></td></tr><tr></tr>';
} else {
    echo '<input type="submit" accesskey="i" name="Insert" value="' . strtoupper($script_transl['insert']) . '!"></td></tr><tr></tr>';
}
echo "</td></tr></table>\n";
?>
</form>
<?php
require("../../library/include/footer.php");
?>