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
$admin_aziend = checkAdmin();
$msg = '';
$modal_ok_insert = false;
/** ENRICO FEDELE */
/* Inizializzo la variabile per aprire in finestra modale */
$modal = false;
if (isset($_POST['mode']) || isset($_GET['mode'])) {
   $modal = true;
   if (isset($_GET['ok_insert'])) {
      $modal_ok_insert = true;
   }
}
/** ENRICO FEDELE */
if (isset($_POST['Update']) || isset($_GET['Update'])) {
   $toDo = 'update';
} else {
   $toDo = 'insert';
}

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
   $form = gaz_dbi_parse_post('artico');
   $form['codice'] = trim($form['codice']);
   $form['ritorno'] = $_POST['ritorno'];
   $form['ref_code'] = substr($_POST['ref_code'], 0, 15);
   // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
   $form["preacq"] = number_format($form['preacq'], $admin_aziend['decimal_price'], '.', '');
   $form["preve1"] = number_format($form['preve1'], $admin_aziend['decimal_price'], '.', '');
   $form["preve2"] = number_format($form['preve2'], $admin_aziend['decimal_price'], '.', '');
   $form["preve3"] = number_format($form['preve3'], $admin_aziend['decimal_price'], '.', '');
//    $form["sconto"] = number_format($form['sconto'],$admin_aziend['decimal_price'],'.','');
   $form["web_price"] = number_format($form['web_price'], $admin_aziend['decimal_price'], '.', '');
   $form['rows'] = array();
   /** inizio modifica FP 03/12/2015
    * fornitore
    */
   $form['id_anagra'] = $_POST['id_anagra'];
   foreach ($_POST['search'] as $k => $v) {
      $form['search'][$k] = $v;
   }
   /** fine modifica FP */
   // inizio documenti/certificati
   $next_row = 0;
   if (isset($_POST['rows'])) {
      foreach ($_POST['rows'] as $next_row => $value) {
         $form['rows'][$next_row]['id_doc'] = intval($value['id_doc']);
         $form['rows'][$next_row]['extension'] = substr($value['extension'], 0, 5);
         $form['rows'][$next_row]['title'] = substr($value['title'], 0, 255);
         $next_row++;
      }
   }
   // fine documenti/certificati
   /** ENRICO FEDELE */
   /* Controllo se il submit viene da una modale */
   if (isset($_POST['Submit']) || ($modal === true && isset($_POST['mode-act']))) { // conferma tutto
      /** ENRICO FEDELE */
      if ($toDo == 'update') {  // controlli in caso di modifica
         if ($form['codice'] != $form['ref_code']) { // se sto modificando il codice originario
            // controllo che l'articolo ci sia gia'
            $rs_articolo = gaz_dbi_dyn_query('codice', $gTables['artico'], "codice = '" . $form['codice'] . "'", "codice DESC", 0, 1);
            $rs = gaz_dbi_fetch_array($rs_articolo);
            if ($rs) {
               $msg .= "0+";
            }
            // controllo che il precedente non abbia movimenti di magazzino associati
            $rs_articolo = gaz_dbi_dyn_query('artico', $gTables['movmag'], "artico = '" . $form['ref_code'] . "'", "artico DESC", 0, 1);
            $rs = gaz_dbi_fetch_array($rs_articolo);
            if ($rs) {
               $msg .= "1+";
            }
         }
      } else {
         // controllo che l'articolo ci sia gia'
         $rs_articolo = gaz_dbi_dyn_query('codice', $gTables['artico'], "codice = '" . $form['codice'] . "'", "codice DESC", 0, 1);
         $rs = gaz_dbi_fetch_array($rs_articolo);
         if ($rs) {
            $msg .= "2+";
         }
      }
      if (!empty($_FILES['userfile']['name'])) {
         if (!( $_FILES['userfile']['type'] == "image/png" ||
                 $_FILES['userfile']['type'] == "image/x-png" ||
                 $_FILES['userfile']['type'] == "image/jpeg" ||
                 $_FILES['userfile']['type'] == "image/jpg" ||
                 $_FILES['userfile']['type'] == "image/gif" ||
                 $_FILES['userfile']['type'] == "image/x-gif"))
            $msg .= "3+";
         // controllo che il file non sia piu' grande di circa 10kb
         if ($_FILES['userfile']['size'] > 10999)
            $msg .= "4+";
      }
      $msg .= (empty($form["codice"]) ? "5+" : '');
      $msg .= (empty($form["descri"]) ? "6+" : '');
      $msg .= (empty($form["unimis"]) ? "7+" : '');
      $msg .= (empty($form["aliiva"]) ? "8+" : '');
      // per poter avere la tracciabilità è necessario attivare la contabità di magazzino in configurazione azienda
      $msg .= (($form["lot_or_serial"] > 0 && $admin_aziend['conmag'] <= 1 ) ? "9+" : '');

      if (empty($msg)) { // nessun errore
         if ($_FILES['userfile']['size'] > 0) { //se c'e' una nuova immagine nel buffer
            $form['image'] = file_get_contents($_FILES['userfile']['tmp_name']);
         } elseif ($toDo == 'update') { // altrimenti riprendo la vecchia ma solo se è una modifica
            $oldimage = gaz_dbi_get_row($gTables['artico'], 'codice', $form['ref_code']);
            $form['image'] = $oldimage['image'];
         } else {
            $form['image'] = '';
         }
         /** inizio modifica FP 03/12/2015
          * aggiorno il campo con il codice fornitore
          */
         $form['clfoco'] = $form['id_anagra'];
         /** fine modifica FP */
         // aggiorno il db
         if ($toDo == 'insert') {
            gaz_dbi_table_insert('artico', $form);
         } elseif ($toDo == 'update') {
            gaz_dbi_table_update('artico', $form['ref_code'], $form);
         }
         /** ENRICO FEDELE */
         /* Niente redirect se sono in finestra modale */
         if ($modal === false) {
            header("Location: " . $form['ritorno']);
         } else {
            header("Location: ../../modules/magazz/admin_artico.php?mode=modal&ok_insert=1");
         }
         /** ENRICO FEDELE */
         exit;
      }
      /** ENRICO FEDELE */
   } elseif (isset($_POST['Return']) && $modal === false) { // torno indietro
      /* Solo se non sono in finestra modale */
      /** ENRICO FEDELE */
      header("Location: " . $form['ritorno']);
      exit;
   }
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
   $form = gaz_dbi_get_row($gTables['artico'], 'codice', substr($_GET['codice'], 0, 15));
   /** ENRICO FEDELE */
   if ($modal === false) {
      $form['ritorno'] = $_SERVER['HTTP_REFERER'];
   } else {
      $form['ritorno'] = 'admin_artico.php';
   }
   /** ENRICO FEDELE */
   $form['ref_code'] = $form['codice'];
   // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
   $form["preacq"] = number_format($form['preacq'], $admin_aziend['decimal_price'], '.', '');
   $form["preve1"] = number_format($form['preve1'], $admin_aziend['decimal_price'], '.', '');
   $form["preve2"] = number_format($form['preve2'], $admin_aziend['decimal_price'], '.', '');
   $form["preve3"] = number_format($form['preve3'], $admin_aziend['decimal_price'], '.', '');
   $form["web_price"] = number_format($form['web_price'], $admin_aziend['decimal_price'], '.', '');
   $form['rows'] = array();
   /** inizio modifica FP 03/12/2015
    * fornitore
    */
   $form['id_anagra'] = $form['clfoco'];
   $form['search']['id_anagra'] = '';
   /** fine modifica FP */
   // inizio documenti/certificati
   $next_row = 0;
   $rs_row = gaz_dbi_dyn_query("*", $gTables['files'], "item_ref = '" . $form['codice'] . "'", "id_doc DESC");
   while ($row = gaz_dbi_fetch_array($rs_row)) {
      $form['rows'][$next_row] = $row;
      $next_row++;
   }
   // fine documenti/certificati
} else { //se e' il primo accesso per INSERT
   $form = gaz_dbi_fields('artico');
   /** ENRICO FEDELE */
   if ($modal === false) {
      $form['ritorno'] = $_SERVER['HTTP_REFERER'];
   } else {
      $form['ritorno'] = 'admin_artico.php';
   }
   /** ENRICO FEDELE */
   $form['ref_code'] = '';
   $form['aliiva'] = $admin_aziend['preeminent_vat'];
   // i prezzi devono essere arrotondati come richiesti dalle impostazioni aziendali
   $form["preacq"] = number_format($form['preacq'], $admin_aziend['decimal_price'], '.', '');
   $form["preve1"] = number_format($form['preve1'], $admin_aziend['decimal_price'], '.', '');
   $form["preve2"] = number_format($form['preve2'], $admin_aziend['decimal_price'], '.', '');
   $form["preve3"] = number_format($form['preve3'], $admin_aziend['decimal_price'], '.', '');
   $form["web_price"] = number_format($form['web_price'], $admin_aziend['decimal_price'], '.', '');
   $form['web_url'] = '';
   /** inizio modifica FP 03/12/2015
    * filtro per fornitore ed ordinamento
    */
   $form['id_anagra'] = '';
   $form['search']['id_anagra'] = '';
   /** fine modifica FP */
}

/** ENRICO FEDELE */
/* Solo se non sono in finestra modale carico il file di lingua del modulo */
if ($modal === false) {
   require("../../library/include/header.php");
   $script_transl = HeadMain();
} else {
   $script = basename($_SERVER['PHP_SELF']);
   require("../../language/" . $admin_aziend['lang'] . "/menu.inc.php");
   require("../../modules/magazz/lang." . $admin_aziend['lang'] . ".php");
   if (isset($script)) { // se è stato tradotto lo script lo ritorno al chiamante
      $script_transl = $strScript[$script];
   }

   $script_transl = $strCommon + $script_transl;
}
/** ENRICO FEDELE */
/* Assegno un id al form, quindi distinguo tra modale e non 
 * in caso di finestra modale, aggiungo un campo nascosto che mi serve per salvare nel database
 */
echo '<form method="POST" name="form" enctype="multipart/form-data" id="add-product">';

if ($modal === true) {
   echo '<input type="hidden" name="mode" value="modal" />
		  <input type="hidden" name="mode-act" value="submit" />';
}
echo '<input type="hidden" name="ritorno" value="' . $form['ritorno'] . '" />';
echo '<input type="hidden" name="ref_code" value="' . $form['ref_code'] . '" />';

if ($modal_ok_insert === true) {
   echo '<div class="alert alert-success" role="alert">' . $script_transl['modal_ok_insert'] . '</div>';
   foreach ($form as $k => $v) {
      //echo '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
   }
   echo '<div class=" text-center"><button class="btn btn-lg btn-default" type="submit" name="none">' . $script_transl['iterate_invitation'] . '</button></div>';
//    echo '<div><input name="none" type="submit" value="' . $script_transl['iterate_invitation'] . '" /></div>';
} else {
   $gForm = new magazzForm();
   $mv = $gForm->getStockValue(false, $form['codice']);
   $magval = array_pop($mv);

   /** ENRICO FEDELE */
   /* Se sono in finestra modale, non visualizzo questo titolo */
   if ($modal === false) {
      if ($toDo == 'insert') {
         echo '<div align="center" class="FacetFormHeaderFont">' . $script_transl['ins_this'] . '</div>';
      } else {
         echo '<div align="center" class="FacetFormHeaderFont">' . $script_transl['upd_this'] . ' ' . $form['codice'] . '</div>';
      }
   }
   echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
   /** ENRICO FEDELE */
   echo '<table class="Tmiddle">';
   if (!empty($msg)) {
      echo '<tr><td colspan="3" class="FacetDataTDred">' . $gForm->outputErrors($msg, $script_transl['errors']) . '</td></tr>';
   }
   echo '<tr>
		<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['codice'] . '*</td>
		<td colspan="2" class="FacetDataTD">
			<input type="text" name="codice" value="' . $form['codice'] . '" align="right" maxlength="15" size="15" />


		</td>
	  </tr>
	  <tr>
		<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['descri'] . '*</td>
		<td colspan="2" class="FacetDataTD">
			<input type="text" name="descri" value="' . $form['descri'] . '" align="right" maxlength="255" size="70" />
		</td>
	  </tr>
	  <tr>
	    <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['lot_or_serial'] . '</td>
		<td colspan="2" class="FacetDataTD">';
   $gForm->variousSelect('lot_or_serial', $script_transl['lot_or_serial_value'], $form['lot_or_serial']);
   echo '  </td>
      </tr>
	  <tr>
	    <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['barcode'] . '</td>
		<td colspan="2" class="FacetDataTD">
			<input type="text" name="barcode" value="' . $form['barcode'] . '" align="right" maxlength="13" size="15" />

		</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap"><img src="../root/view.php?table=artico&value=' . $form['codice'] . '" width="100" /></td>
		<td colspan="2" class="FacetFieldCaptionTD">
			' . $script_transl['image'] . '&nbsp;
			<div class="input-group">
                <span class="input-group-btn">
                    <span class="file-input btn btn-default btn-file">
                       ' . $script_transl['browse_for_file'] . '<input name="userfile" type="file" />
                    </span>
                </span>
                <input type="text" readonly="" class="form-control">
            </div>
		</td>
	  </tr>
	  <tr>
	    <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['unimis'] . '*</td>
		<td colspan="2" class="FacetDataTD">
			<input type="text" name="unimis" value="' . $form['unimis'] . '" align="right" maxlength="3" size="15" />
		</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['catmer'] . '</td>
		<td colspan="2" class="FacetDataTD">';
   $gForm->selectFromDB('catmer', 'catmer', 'codice', $form['catmer'], false, 1, ' - ', 'descri');
   /** inizio modifica FP 15/10/2015
    * aggiunto campo raggruppamento statistico
    */
   echo '	  </td>
        </tr>
		<tr>
		  <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['ragstat'] . '</td>
		  <td colspan="2" class="FacetDataTD">';
   $gForm->selectFromDB('ragstat', 'ragstat', 'codice', $form['ragstat'], false, 1, ' - ', 'descri');
   /** fine modifica FP */
   echo '  </td>
      </tr>
	  <tr>
	    <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['preacq'] . '</td>
		<td colspan="2" class="FacetDataTD">
			<input type="text" name="preacq" value="' . $form['preacq'] . '" style="text-align:right;" maxlength="15" size="15" />
		</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['preve1'] . '</td>
	  	<td colspan="2" class="FacetDataTD">
      		<input type="text" name="preve1" value="' . $form['preve1'] . '" style="text-align:right;" maxlength="15" size="15" />
	  	</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['preve2'] . '</td>
	  	<td colspan="2" class="FacetDataTD">
      		<input type="text" name="preve2" value="' . $form['preve2'] . '" style="text-align:right;" maxlength="15" size="15" />
		</td>
	  </tr>
	  <tr>
	 	 <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['preve3'] . '</td>
	  	<td colspan="2" class="FacetDataTD">
      		<input type="text" name="preve3" value="' . $form['preve3'] . '" style="text-align:right;" maxlength="15" size="15" />
		</td>
	  </tr>
	  <tr>
	    <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['sconto'] . '</td>
		<td colspan="2" class="FacetDataTD">
			<input type="text" name="sconto" value="' . $form['sconto'] . '" style="text-align:right;" maxlength="6" size="15" />
		</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['aliiva'] . ' * </td><td colspan="2" class="FacetDataTD">';
   $gForm->selectFromDB('aliiva', 'aliiva', 'codice', $form['aliiva'], 'codice', 1, ' - ', 'descri');
   echo '</td>
	  </tr>
	  <tr>
		<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['esiste'] . '</td>
		<td colspan="2" class="FacetDataTD">' . $magval['q_g'] . '</td>
	  </tr>
	  <tr>
		<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['valore'] . '</td>
		<td colspan="2" class="FacetDataTD">' . $admin_aziend['symbol'] . $magval['v_g'] . '</td>
	  </tr>
	  <tr>
		<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['last_cost'] . '</td>
		<td colspan="2" class="FacetDataTD">
      		<input type="text" name="last_cost" value="' . $form['last_cost'] . '" style="text-align:right;" maxlength="15" size="15" />
		</td>
	  </tr>
	  <tr>
	    <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['scorta'] . '</td>
	    <td colspan="2" class="FacetDataTD">
      		<input type="text" name="scorta" value="' . $form['scorta'] . '" style="text-align:right;" maxlength="13" size="15" />
		</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['riordino'] . '</td>
	  	<td class="FacetDataTD" colspan="2">
      		<input type="text" name="riordino" value="' . $form['riordino'] . '" style="text-align:right;" maxlength="13" size="15" />
		</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['uniacq'] . '</td>
	  	<td colspan="2" class="FacetDataTD">
      		<input type="text" name="uniacq" value="' . $form['uniacq'] . '" align="right" maxlength="3" size="15" />


		</td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['peso_specifico'] . '</td>
	  	<td colspan="2" class="FacetDataTD">
      		<input type="text" name="peso_specifico" value="' . $form['peso_specifico'] . '" align="right" maxlength="13" size="15" />
		</td>
	  </tr>
	  <tr>
	 	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['volume_specifico'] . '</td>
	  	<td colspan="2" class="FacetDataTD">
      		<input type="text" name="volume_specifico" value="' . $form['volume_specifico'] . '" style="text-align:right;" maxlength="13" size="15" />
		</td>
	  </tr>
	  <tr>
	    <td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['pack_units'] . '</td>
		<td colspan="2" class="FacetDataTD">
      		<input type="text" name="pack_units" value="' . $form['pack_units'] . '" style="text-align:right;" maxlength="6" size="15" />
		</td>
	</tr>
	<tr>
		<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['codcon'] . '</td><td colspan="2" class="FacetDataTD">';
   $gForm->selectAccount('codcon', $form['codcon'], 4);
   echo '  </td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['id_cost'] . '</td><td colspan="2" class="FacetDataTD">';
   $gForm->selectAccount('id_cost', $form['id_cost'], 3);
   echo '  </td>
	  </tr>
	  <tr>
	  	<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['annota'] . '</td>
		<td colspan="2" class="FacetDataTD">
      		<input type="text" name="annota" value="' . $form['annota'] . '" maxlength="50" size="50" /></td>
	  </tr>';
   if ($toDo == 'update') {
      echo '  <tr>
  			<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['document'] . ' :</td>
			<td class="FacetDataTD" colspan="2">';
      if ($next_row > 0) {
         echo '<table>';
         foreach ($form['rows'] as $k => $val) {
            echo '	<input type="hidden" value="' . $val['id_doc'] . '" name="rows[' . $k . '][id_doc]">
					<input type="hidden" value="' . $val['extension'] . '" name="rows[' . $k . '][extension]">
					<input type="hidden" value="' . $val['title'] . '" name="rows[' . $k . '][title]">
					<tr class="FacetFieldCaptionTD">
						<td>".DATA_DIR."files/' . $val['id_doc'] . '.' . $val['extension'] . '</td>
						<td>
							<a href="../root/retrieve.php?id_doc=' . $val["id_doc"] . '" title="' . $script_transl['view'] . '!" class="btn btn-default btn-sm">
								<i class="glyphicon glyphicon-file"></i>
							</a>
						</td>
						<td>' . $val['title'] . '</td>
						<td align="right" >
							<input type="button" value="' . ucfirst($script_transl['update']) . ' " onclick="location.href=\'admin_document.php?id_doc=' . $val['id_doc'] . '&Update\';" />
						</td>
					</tr>';
         }
         echo '		<tr>
					<td align="right" colspan="4">
						<input type="button" value="' . ucfirst($script_transl['insert']) . '" onclick="location.href=\'admin_document.php?item_ref=' . $form['codice'] . '&Insert\';" />

					</td>
				</tr>
		  	</table>
		  	</td>
		  </tr>';
      } else {
         echo ' <input type="button" value="' . ucfirst($script_transl['insert']) . '" onclick="location.href=\'admin_document.php?item_ref=' . $form['codice'] . '&Insert\';">
		  	</td>
		  </tr>';
      }
   }

   /** inizio modifica FP 03/12/2015
    * fornitore
    */
   echo "<tr>\n";
   echo "\t<td class=\"FacetFieldCaptionTD\">" . $script_transl['id_anagra'] . " </td><td class=\"FacetDataTD\" colspan=\"2\">\n";
   $select_id_anagra = new selectPartner("id_anagra");
   $select_id_anagra->selectDocPartner('id_anagra', $form['id_anagra'], $form['search']['id_anagra'], 'id_anagra', $script_transl['mesg'], $admin_aziend['masfor'], -1, 1, true);
   echo "</td>\n";
   echo "</tr>\n";
   echo "<tr>\n";

   /** fine modifica FP */
   echo '<tr>
			<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['web_mu'] . '</td>
			<td colspan="2" class="FacetDataTD">
			  <input type="text" name="web_mu" value="' . $form['web_mu'] . '" maxlength="15" size="15" />
			</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['web_price'] . '</td>
			<td colspan="2" class="FacetDataTD">
			  <input type="text" name="web_price" value="' . $form['web_price'] . '" style="text-align:right;" maxlength="15" size="15" />
			</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['web_multiplier'] . '</td>
			<td colspan="2" class="FacetDataTD">
			  <input type="text" name="web_multiplier" value="' . $form['web_multiplier'] . '" style="text-align:right;" maxlength="15" size="15" />
			</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['web_url'] . '</td>
			<td colspan="2" class="FacetDataTD">
			  <input type="text" name="web_url" value="' . $form['web_url'] . '" maxlength="255" size="50" />
			</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['web_public'] . '</td>
			<td colspan="2" class="FacetDataTD">';
   $gForm->variousSelect('web_public', $script_transl['web_public_value'], $form['web_public']);
   echo '		</td>
		</tr>
		<tr>
			<td class="FacetFieldCaptionTD" nowrap="nowrap">' . $script_transl['sqn'] . '</td>
			<td class="FacetDataTD">';
   /** ENRICO FEDELE */
   /* SOlo se non sono in finestra modale */

   if ($modal === false) {
      echo '<input name="none" type="submit" value="" disabled>&nbsp;<input name="Return" type="submit" value="' . $script_transl['return'] . '!">';
   }
   /** ENRICO FEDELE */
   echo '  </td>
		<td class="FacetDataTD" align="right">
			<input name="Submit" type="submit" value="' . strtoupper($script_transl[$toDo]) . '!" />
		</td>
	  </tr>';
}
?>
</table>
</form>
<script type="text/javascript">
   // Basato su: http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/
   $(document).on('change', '.btn-file :file', function () {
       var input = $(this),
               numFiles = input.get(0).files ? input.get(0).files.length : 1,
               label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
       input.trigger('fileselect', [numFiles, label]);
   });

   $(document).ready(function () {
       $('.btn-file :file').on('fileselect', function (event, numFiles, label) {

           var input = $(this).parents('.input-group').find(':text'),
                   log = numFiles > 1 ? numFiles + ' files selected' : label;

           if (input.length) {
               input.val(log);
           } else {
               if (log)
               alert(log);
           }

       });
   });
</script>


<?php
/** ENRICO FEDELE */
/* SOlo se non sono in finestra modale */
if ($modal === false) {
   echo '	</div><!-- chiude div container role main --></body>
		  </html>';
} else {
   ?>
   <script type="text/javascript">
      $("#add-product").submit(function (e) {
          $.ajax({
              type: "POST",
              url: "../../modules/magazz/admin_artico.php",
              data: $("#add-product").serialize(), // serializes the form's elements.
              success: function (data) {
                  $("#edit-modal .modal-sm").css('width', '850px');
                  $("#edit-modal .modal-sm").css('min-width', '850px');
                  $("#edit-modal .modal-body").html(data);
              }
          });

          e.preventDefault(); // avoid to execute the actual submit of the form.
      });
   </script>
   <?php
}
?>