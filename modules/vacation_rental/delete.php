<?php
/*
 --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-20223 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)
  Ogni diritto è riservato.
  E' possibile usare questo modulo solo dietro autorizzazione dell'autore
  --------------------------------------------------------------------------
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
// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}

if ((isset($_POST['type'])&&isset($_POST['ref'])) OR (isset($_POST['type'])&&isset($_POST['id_tes']))) {
	require("../../library/include/datlib.inc.php");
	require("../../modules/magazz/lib.function.php");
	$upd_mm = new magazzForm;
	$admin_aziend = checkAdmin();
	switch ($_POST['type']) {
        case "artico":
			$i=substr($_POST['ref'],0,32);
			//Cancello le eventuali immagini web e i documenti
			$rs=gaz_dbi_dyn_query ("*",$gTables['files'],"table_name_ref = 'artico' AND item_ref = '".$i."'");
			foreach ($rs as $delimg){
				gaz_dbi_del_row($gTables['files'], "id_doc", $delimg['id_doc']);
				unlink (DATA_DIR."files/".$admin_aziend['codice']."/images/". $delimg['id_doc'] . "." . $delimg['extension']);
			}
			// Cancello l'eventuale body_text
			gaz_dbi_del_row($gTables['body_text'], "table_name_ref", "artico_".$i);
			//Cancello se presenti gli articoli presenti in distinta base
			$result = gaz_dbi_del_row($gTables['distinta_base'], "codice_composizione", $i );
			//Cancello l'articolo
			$result = gaz_dbi_del_row($gTables['artico'], "codice", $i);
		break;
		case "extra":
			$i=substr($_POST['ref'],0,32);
			//Cancello le eventuali immagini web e i documenti
			$rs=gaz_dbi_dyn_query ("*",$gTables['files'],"table_name_ref = 'artico' AND item_ref = '".$i."'");
			foreach ($rs as $delimg){
				gaz_dbi_del_row($gTables['files'], "id_doc", $delimg['id_doc']);
				unlink (DATA_DIR."files/".$admin_aziend['codice']."/images/". $delimg['id_doc'] . "." . $delimg['extension']);
			}
			// Cancello l'eventuale body_text
			gaz_dbi_del_row($gTables['body_text'], "table_name_ref", "artico_".$i);
			//Cancello se presenti gli articoli presenti in distinta base
			$result = gaz_dbi_del_row($gTables['distinta_base'], "codice_composizione", $i );
			//Cancello l'articolo
			$result = gaz_dbi_del_row($gTables['artico'], "codice", $i);
			//Cancello anche il rispettivo rigo dalla tabella rental_extra
			$result = gaz_dbi_del_row($gTables['rental_extra'], "rif_alloggio", $i);
		break;
		case "booking":
			//procedo all'eliminazione della testata e dei righi...
			$tipdoc = gaz_dbi_get_row($gTables['tesbro'], "id_tes", intval($_POST['id_tes']))['tipdoc'];
			//cancello la testata
			gaz_dbi_del_row($gTables['tesbro'], "id_tes", intval($_POST['id_tes']));
			//... e i righi
			$rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigbro'], "id_tes =". intval($_POST['id_tes']),"id_tes DESC");
			while ($a_row = gaz_dbi_fetch_array($rs_righidel)) {
				gaz_dbi_del_row($gTables['rigbro'], "id_rig", $a_row['id_rig']);
                if (!empty($admin_aziend['synccommerce_classname']) && class_exists($admin_aziend['synccommerce_classname']) AND $tipdoc!=="VOW"){
                    // aggiorno l'e-commerce ove presente se l'ordine non è web
                    $gs=$admin_aziend['synccommerce_classname'];
                    $gSync = new $gs();
					if($gSync->api_token){
						$gSync->SetProductQuantity($a_row['codart']);
					}
				}
				gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigbro' AND id_ref ",$a_row['id_rig']);
				// cancello anche l'evento
				gaz_dbi_del_row($gTables['rental_events'], "id_tesbro", $_POST['id_tes']);
			}
		break;
		case "ical":
			// elimino l'Ical dalla tabella ical
			gaz_dbi_del_row($gTables['rental_ical'], 'id', intval($_POST['ref']));
			// elimino tutti i suoi eventi dalla tabella rental_events
			gaz_dbi_del_row($gTables['rental_events'], 'Ical_sync_id', intval($_POST['ref']));
		break;
    case "discount":
    // elimino lo sconto dalla tabella rental_discounts
    gaz_dbi_del_row($gTables['rental_discounts'], 'id', intval($_POST['ref']));
		break;
	}
}
?>
