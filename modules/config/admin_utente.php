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
require("../../library/include/datlib.inc.php");
$aut = 9;
if (!isset($_POST['ritorno'])) {
	$_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}
$global_config = new Config;
$user_data = gaz_dbi_get_row($gTables['admin'], "user_name", $_SESSION["user_name"]);

$msg = array('err' => array(), 'war' => array());
if ((isset($_POST['Update'])) or ( isset($_GET['Update']))) {
	$toDo = 'update';
	$accessi = $_GET["user_name"];
	if (!isset($_GET["user_name"])) {
		header("Location: " . $_POST['ritorno']);
		exit;
	}
	if ($_SESSION["user_name"] == $_GET["user_name"] or $user_data['Abilit'] == 9) {
		$aut = 0;
	}
} elseif ((isset($_POST['Insert'])) or ( isset($_GET['Insert']))) {
	$toDo = 'insert';
	$accessi = "";
	$aut = 9;
} else {
	header("Location: " . $_POST['ritorno']);
	exit;
}

$admin_aziend = checkAdmin($aut);
if (isset($_POST['Return'])) {
	header("Location: " . $_POST['ritorno']);
	exit;
}

if ((isset($_POST['Insert'])) || (isset($_POST['Update']))) {   //se non e' il primo accesso
	$form["user_lastname"] = substr($_POST['user_lastname'], 0, 30);
	$form["user_firstname"] = substr($_POST['user_firstname'], 0, 30);
	$form['user_email'] = trim($_POST['user_email']);
	$form["lang"] = substr($_POST['lang'], 0, 15);
	$form["id_warehouse"] = intval($_POST['id_warehouse']);
	$form["theme"] = filter_input(INPUT_POST,'theme');
	$form["style"] = substr($_POST['style'], 0, 30);
	$form["skin"] = substr($_POST['skin'], 0, 30);
	$form["Abilit"] = intval($_POST['Abilit']);
    $form['hidden_req'] = $_POST['hidden_req'];
	$form['company_id'] = intval($_POST['company_id']);
	$form['search']['company_id'] = $_POST['search']['company_id'];
	$form["Access"] = intval($_POST['Access']);
	$form["user_name"] = preg_replace("/[^A-Za-z0-9]i/", '',substr($_POST["user_name"], 0, 15));
	$form["user_password_old"] = substr($_POST['user_password_old'], 0, 40);
	$form["user_password_new"] = substr($_POST['user_password_new'], 0, 40);
	$form["user_password_ver"] = substr($_POST['user_password_ver'], 0, 40);
	$form["user_active"] = intval($_POST['user_active']);
	$form['body_text'] = filter_input(INPUT_POST, 'body_text');
	if ($toDo == 'insert') {
		$rs_utente = gaz_dbi_dyn_query("*", $gTables['admin'], "user_name = '" . $form["user_name"] . "'", "user_name DESC", 0, 1);
		$risultato = gaz_dbi_fetch_array($rs_utente);
		if ($risultato) {
			$msg['err'][] = 'exlogin';
		}
	}
} elseif ((!isset($_POST['Update'])) && (isset($_GET['Update']))) {
	$form = gaz_dbi_get_row($gTables['admin'], "user_name", preg_replace("/[^A-Za-z0-9]/", '',substr($_GET["user_name"], 0, 15)));
	if (!$form){
		header("Location: " . $_POST['ritorno']);
		exit;
	}
	// attingo il valore del motore di template dalla tabella configurazione utente
	$admin_config = gaz_dbi_get_row($gTables['admin_config'], 'var_name', 'theme', "AND adminid = '{$form['user_name']}'");
	$form = gaz_dbi_get_row($gTables['admin'], "user_name", preg_replace("/[^A-Za-z0-9]/", '',substr($_GET["user_name"], 0, 15)));
	// dal custom field di admin_module relativo al magazzino trovo il magazzino di default
	$magmodule = gaz_dbi_get_row($gTables['module'], "name",'magazz');
	$mod_customfield = gaz_dbi_get_row($gTables['admin_module'], "moduleid",$magmodule['id']," AND adminid='{$form['user_name']}' AND company_id=" . $admin_aziend['company_id']);
  $mod_customfield['custom_field'] = ($mod_customfield['custom_field'] === NULL) ? '' : $mod_customfield['custom_field'];
	$customfield=json_decode($mod_customfield['custom_field']);
	$form['id_warehouse'] = (isset($customfield->user_id_warehouse))?$customfield->user_id_warehouse:0;
	$form['user_password_old'] = '';
	$form['user_password_new'] = '';
	$form['user_password_ver'] = '';
	$form['theme'] = $admin_config['var_value'];
	// attingo il testo delle email dalla tabella configurazione utente
	$bodytext = gaz_dbi_get_row($gTables['admin_config'], 'var_name', 'body_send_doc_email', "AND adminid = '{$form['user_name']}'");
	$form['body_text'] = ($bodytext)?$bodytext['var_value']:'';
    $form['hidden_req'] = '';
    $form['search']['company_id'] = '';
} else {
	$form["user_lastname"] = "";
	$form["user_firstname"] = "";
	$form['user_email'] = '';
	$form["image"] = "";
	$form["theme"] = "/library/theme/g7";
	$form["style"] = $admin_aziend['style'];
	$form["skin"] = $admin_aziend['skin'];
	$form["lang"] = $admin_aziend['lang'];
	$form["id_warehouse"]=0;
	$form["Abilit"] = 5;
	// propongo la stessa azienda attiva sull'utente amministratore
    $form['hidden_req'] = '';
    $form['company_id'] = $user_data['company_id'];
    $form['search']['company_id'] = '';
	$form["Access"] = 0;
	$form["user_name"] = "";
	$form['user_password_old'] = '';
	$form['user_password_new'] = '';
	$form['user_password_ver'] = '';
	$form["user_active"] = 1;
	$form['body_text'] = "";

	if (preg_match("/school/", $_SERVER['HTTP_REFERER'])) {
		// nel caso voglio inserire un nuovo insegnante propongo abilitazione a 9
		$form["Abilit"] = 9;
	};
}

if (isset($_POST['Submit'])) {
	$old_data = gaz_dbi_get_row($gTables['admin'], "user_name", $form["user_name"]);
	//controllo i campi
	if (empty($form["user_lastname"]))
	$msg['err'][] = 'user_lastname';
	if (empty($form["user_name"]))
	$msg['err'][] = "user_name";
	if (!filter_var($form['user_email'], FILTER_VALIDATE_EMAIL) && !empty($form['user_email'])) {
		$msg['err'][] = 'email'; // non coincide, segnalo l'errore
	}
	if ($toDo == 'update' && !empty($form["user_password_old"])) {
		if (password_verify( $form["user_password_old"]  , $old_data["user_password_hash"] )) {
			// voglio reimpostare la password ed è giusta
		} else {
			$msg['err'][] = 'passold'; // non coincide, segnalo l'errore
		}
	} else if ($toDo == 'insert'){
		if (strlen($form["user_password_new"]) < $global_config->getValue('psw_min_length'))
		$msg['err'][] = 'passlen';

	}
	if ($form["user_password_new"] != $form["user_password_ver"])
	$msg['err'][] = 'confpass';
	if (preg_match("/[<> \/\"]+/i", $form["user_password_new"])) {
		$msg['err'][] = 'charpass';
	}
	if ($form["Abilit"] > $user_data["Abilit"])
	$msg['err'][] = 'upabilit';
	if (!empty($_FILES['userfile']['name'])) {
		if (!( $_FILES['userfile']['type'] == "image/jpeg" || $_FILES['userfile']['type'] == "image/pjpeg"))
		$msg['err'][] = 'filmim';
		// controllo che il file non sia pi&ugrave; grande di 64kb
		if ($_FILES['userfile']['size'] > 63999)
		$msg['err'][] = 'filsiz';
	}
	if ($form["Abilit"] < 9) {
		$ricerca = trim($form["user_name"]);
		// impedisco agli utenti non amministratori di cambiarsi l'azienda di lavoro
		$form["company_id"] = ($old_data)?$old_data["company_id"]:0;
		$rs_utente = gaz_dbi_dyn_query("*", $gTables['admin'], "user_name <> '$ricerca' AND Abilit ='9'", "user_name", 0, 1);
		$risultato = gaz_dbi_fetch_array($rs_utente);
		$student = false;
		if (preg_match("/([a-z0-9]{1,9})[0-9]{4}$/", $table_prefix, $tp)) {
			$rs_student = gaz_dbi_dyn_query("*", $tp[1] . '_students', "student_name = '" . $ricerca . "'");
			$student = gaz_dbi_fetch_array($rs_student);
		}
		if (!$risultato && !$student) {
			$msg['err'][] = 'Abilit';
		} elseif ($form["Abilit"] < 7 && $student) {
			$msg['err'][] = 'Abilit_stud';
		}
	}
	if (count($msg['err']) == 0) { // nessun errore
		// preparo la stringa dell'immagine
		if ($_FILES['userfile']['size'] > 0) { //se c'e' una nuova immagine nel buffer
			$form['image'] = file_get_contents($_FILES['userfile']['tmp_name']);
		} else {   // altrimenti riprendo la vecchia
			$form['image'] = $old_data['image'];
		}
		// preparo l'update di custom_field che potrebbe contenere altri dati
		$magmodule = gaz_dbi_get_row($gTables['module'], "name",'magazz');
		$thisadmin_module = gaz_dbi_get_row($gTables['admin_module'], "moduleid",$magmodule['id']," AND adminid='{$form['user_name']}' AND company_id=" . $admin_aziend['company_id']);
		$thiscustom_field=(array)json_decode($thisadmin_module['custom_field']);
		$thiscustom_field['user_id_warehouse']=$form['id_warehouse'];
		$form['custom_field']=json_encode($thiscustom_field);
		// aggiorno il db
		$query="UPDATE ".$gTables['admin_module']." SET custom_field='".$form['custom_field']."' WHERE moduleid=".$magmodule['id']." AND adminid='{$form['user_name']}' AND company_id=" . $admin_aziend['company_id'];
		gaz_dbi_query($query);
		$form["datacc"] = date("YmdHis");
		$form["datpas"] = date("YmdHis");
		$tbt = trim($form['body_text']);
		if ($user_data['Abilit'] == 9) {
			foreach ($_POST AS $key => $value) {
				if (preg_match("/^([0-9]{3})acc_/", $key, $id)) {
					updateAccessRights($form["user_name"], preg_replace("/^[0-9]{3}acc_/", '', $key), $value, $id[1]);
				} elseif (preg_match("/^([0-9]{3})nusr_/", $key, $id)) {
					updateAccessRights($form["user_name"], 1, 3, $user_data['company_id']);
					$mod_data = gaz_dbi_get_row($gTables['module'], 'name', preg_replace("/^[0-9]{3}nusr_/", '', $key));
					if (!empty($mod_data)) {
						updateAccessRights($form["user_name"], $mod_data['id'], $value, $id[1]);
					}
				} elseif (preg_match("/^([0-9]{3})new_/", $key, $id) && $value == 3) { // il nuovo modulo non è presente in gaz_module
				  $name = preg_replace("/^[0-9]{3}new_/", '', $key);
				  // controllo se il modulo è già stato attivato allora aggiungo solo l'utente
				  $mod_data = gaz_dbi_get_row($gTables['module'], 'name', $name);
				  // trovo l'ultimo peso assegnato ai moduli esistenti e lo accodo
				  $rs_last = gaz_dbi_dyn_query("MAX(weight)+1 AS max_we", $gTables['module'], 'id > 1');
				  $r = gaz_dbi_fetch_array($rs_last);
				  $modclass=(isset($module_class))?$module_class:'';
				  if($mod_data){ // il modulo è presente aggiungo solo l'utente in admin_module
					updateAccessRights($form["user_name"], $mod_data['id'], 3, $id[1]);
				  } else { // non c'è nulla aggiungo tutto e creo il menù
					require("../../modules/" . $name . "/menu.creation_data.php");
					$mod_id = gaz_dbi_table_insert('module', array('name' => $name, 'link' => $menu_data['m1']['link'], 'icon' => $name . '.png', 'class'=>$modclass, 'weight' => $r['max_we']));
					updateAccessRights($form["user_name"], $mod_id, 3, $id[1]);
					// trovo l'ultimo id del sub menu
					$rs_last = gaz_dbi_dyn_query("MAX(id)+1 AS max_id", $gTables['menu_module'], 1);
					$r = gaz_dbi_fetch_array($rs_last);
					$m2_id = $r['max_id'];
					foreach ($menu_data['m2'] as $k_m2 => $v_2) {
						gaz_dbi_table_insert('menu_module', array('id' => $m2_id, 'id_module' => $mod_id, 'link' => $v_2['link'], 'translate_key' => $k_m2, 'weight' => $v_2['weight']));
						if (isset($menu_data['m3']['m2'][$k_m2])) {
							foreach ($menu_data['m3']['m2'][$k_m2] as $v_3) {
								// trovo l'ultimo id del sub menu
								$rs_last = gaz_dbi_dyn_query("MAX(id)+1 AS max_id", $gTables['menu_script'], 1);
								$r = gaz_dbi_fetch_array($rs_last);
								gaz_dbi_table_insert('menu_script', array('id' => $r['max_id'], 'id_menu' => $m2_id, 'link' => $v_3['link'], 'translate_key' => $v_3['translate_key'], 'weight' => $v_3['weight']));
							}
						}
						$m2_id ++;
					}
					if (isset($update_db)&&is_array($update_db)){
						/*
						Se il nuovo modulo prevede un update della base dati allora eseguo (unatantum) le query in essa contenute;
						pertanto se si vuole modificare il database si deve valorizzare una variabile di nome "$update_db" del file
						menu.creatione_data.php  e mettere in essa tutte le query al database necessarie per il funzionamento del nuovo
						modulo
						*/
						global $table_prefix;
            $query = "SELECT codice FROM `".$table_prefix."_aziend`";
            $result = gaz_dbi_query ($query);
            $companies = array();
            while($r=gaz_dbi_fetch_array($result)){
              $companies[]=$r['codice'];
            }
						foreach ($update_db as $vq) {
              if (preg_match("/XXX/",$vq)) { // query ricorsive sulle tabelle di tutte le aziende
                foreach ($companies as $i) {
                  $sql = preg_replace("/XXX/", sprintf('%03d',$i), $vq);
                  if (!gaz_dbi_query($sql)) { //se non è stata eseguita l'istruzione lo segnalo
                    echo "Query Fallita";
                    echo "$sql <br/>";
                    exit;
                  }
                }
              } else { // query singola sulla tabella comune alle aziende
                if (!gaz_dbi_query($vq)) { //se non è stata eseguita l'istruzione lo segnalo
                  echo "Query Fallita";
                  echo "$sql <br/>";
                  exit;
                }
              }
						}
					}
				  }
				}
			}
		}
		if ($toDo == 'insert') {
			$form['company_id'] = $user_data['company_id'];
			$form['user_registration_datetime']= date('Y-m-d H:i:s');
			$form['user_active']=1;
			// faccio l'hash della password prima di scrivere sul db
			require_once('../../modules/root/config_login.php');
			$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);
			$form["user_password_hash"] = password_hash($form["user_password_new"] , PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

			// Antonio Germani - Creo anche una nuova anagrafica nelle anagrafiche comuni
			$form['ragso1']=$form['user_lastname'];
			$form['ragso2']=$form['user_firstname'];
			$form['legrap_pf_nome']="";
			$form['legrap_pf_cognome']="";
			$form['email']=$form['user_email'];
			$form['id_anagra']=gaz_dbi_table_insert('anagra', $form);

			gaz_dbi_table_insert('admin', $form);
			$form['adminid'] = $form["user_name"];
			$form['var_descri'] = 'Menu/header/footer personalizzabile';
			$form['var_name'] = 'theme';
			$form['var_value'] = $form['theme'];
			gaz_dbi_table_insert('admin_config', $form);
			if (!empty($tbt)) {
				$form['var_descri'] = 'Contenuto in HTML del testo del corpo delle email inviate dell\'utente';
				$form['var_name'] = 'body_send_doc_email';
				$form['var_value'] = $tbt;
				gaz_dbi_table_insert('admin_config', $form);
			}
			// qui aggiungo alla tabella breadcrumb/widget gli stessi che ha l'utente che abilita il nuovo, altrimenti sulla homepage non apparirebbe nulla
			$get_widgets = gaz_dbi_dyn_query("*", $gTables['breadcrumb'],"adminid='".$admin_aziend['user_name']."' AND exec_mode>=1", 'exec_mode,position_order');
			while($row=gaz_dbi_fetch_array($get_widgets)){
				$row['adminid']=$form["user_name"];
				gaz_dbi_table_insert('breadcrumb',$row);
			}

		} elseif ($toDo == 'update') {
			if (!empty($form["user_password_old"])) {
				if (password_verify( $form["user_password_old"]  , $old_data["user_password_hash"] )) {
					$form["datpas"] = date("YmdHis"); //cambio la data di modifica password
					// faccio l'hash della password prima di scrivere sul db
					require_once('../../modules/root/config_login.php');
					$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);
					$form["user_password_hash"] = password_hash($form["user_password_new"] , PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
				}
			}
			gaz_dbi_table_update('admin', array("user_name", $form["user_name"]), $form);
			// se esiste aggiorno anche il tema
			$admin_config_theme = gaz_dbi_get_row($gTables['admin_config'], 'var_name', 'theme', "AND adminid = '{$form['user_name']}'");
			if ($admin_config_theme) {
				gaz_dbi_put_query($gTables['admin_config'], "adminid = '" . $form["user_name"] . "' AND var_name ='theme'", 'var_value', $form['theme']);
			} else { // altrimenti lo inserisco
				$form['adminid'] = $form["user_name"];
				$form['var_descri'] = 'Menu/header/footer personalizzabile';
				$form['var_name'] = 'theme';
				$form['var_value'] = $form['theme'];
				gaz_dbi_table_insert('admin_config', $form);
			}
			// aggiorno o inserisco il testo da inserire nelle email trasmesse dall'utente
			$bodytext = gaz_dbi_get_row($gTables['admin_config'], 'var_name', 'body_send_doc_email', "AND adminid = '{$form['user_name']}'");
			if ($bodytext) {
				gaz_dbi_put_query($gTables['admin_config'], "adminid = '" . $form["user_name"] . "' AND var_name ='body_send_doc_email'", 'var_value', $tbt);
			} else {  // non c'era lo inserisco
				$form['adminid'] = $form["user_name"];
				$form['var_descri'] = 'Contenuto in HTML del testo del corpo delle email inviate dell\'utente';
				$form['var_name'] = 'body_send_doc_email';
				$form['var_value'] = $tbt;
				gaz_dbi_table_insert('admin_config', $form);
			}
			// vado ad aggiornare anche la tabella studenti dell'installazione di base qualora ce ne fosse uno
			if (@$student) {
				gaz_dbi_put_row($tp[1] . '_students', 'student_name', $form["user_name"], 'student_firstname', $form['user_firstname']);
				gaz_dbi_put_row($tp[1] . '_students', 'student_name', $form["user_name"], 'student_lastname', $form['user_lastname']);
			}
			if ($admin_config_theme['var_value']<>$form['theme']) {
				session_destroy();
				header("Location: ../root/login_user.php?tp=".$table_prefix);
				exit;
			}
		}
		header("Location: " . $_POST['ritorno']);
		exit;
	}
}
require("../../library/include/header.php");
$script_transl = HeadMain(0,['appendgrid/AppendGrid','capslockstate/src/jquery.capslockstate']);
?>
<script>
$(function(){
	$("#dialog_module_card").dialog({ autoOpen: false });
	$('.dialog_module_card').click(function() {
		var mod = $(this).attr('module');
		var username = $(this).attr('adminid');
		var jsondatastr = null;
		var deleted_rows = [];
		$("p#iddescri").html('<img src="../'+mod+'/'+mod+'.png" height="32"> '+$(this).attr("transl_name")+'</b>');
		$.ajax({ // prendo tutti i files php del modulo filtrati di quelli che so non essere di interesse
			'async': false,
			url:"./search.php",
			type: "POST",
			dataType: 'text',
			data: { term: mod, opt: 'module', adminid: username },
			success:function(jsonstr) {
				//alert(jsonstr);
				jsondatastr = jsonstr;
			}
		});

		var myAppendGrid = new AppendGrid({ // creo la tabella vuota
		  element: "tblAppendGrid",
		  uiFramework: "bootstrap4",
		  iconFramework: "default",
		  initRows: 1,
		  columns: [
        {
          name: "script_name",
          display: "Script",
          type: "text",
          ctrlAttr: { 'readonly': 'readonly' },
          ctrlCss: {'font-size': '12px'}
        },
        {
          name: "chk_script",
          display: "Nega accesso",
          type: "checkbox",
          cellCss: {'text-align': 'center'},
          cellCss: {'width': '20px'}
        },
		  ],
      hideButtons: {
        // Remove all buttons at the end of rows
        insert: true,
        remove: true,
        moveUp: true,
        moveDown: true,
        append: true,
        removeLast: true
      },
      hideRowNumColumn: true
		});

		if (jsondatastr){
      // popolo la tabella
      var jsondata = $.parseJSON(jsondatastr);
      myAppendGrid.load( jsondata );
		}

		$( "#dialog_module_card" ).dialog({
			minHeight: 1,
			width: 370,
      position: { my: "top+100", at: "top+100", of: "div.container-fluid,div.wrapper div.content-wrapper",collision:" none" },
      modal: "true",
			show: "blind",
			hide: "explode",
			buttons: {
				delete:{
					text:'Annulla',
					'class':'btn btn-default',
					click:function (event, ui) {
						$(this).dialog("close");
					}
				},
				confirm :{
				  text:'CONFERMA',
				  'class':'btn btn-warning',
				  click:function() {
					var msg = null;
					$.ajax({ // registro con i nuovi dati il cartellino presenze
						'async': false,
						data: {del_script: myAppendGrid.getAllValue(), type: 'module', ref: mod, adminid: username },
						type: 'POST',
						url: './delete.php',
						success: function(output){
							msg = output;
							console.log(msg);
						}
					});
					if (msg) {
						alert(msg);
					} else {
						window.location.replace("./admin_utente.php?user_name=<?php echo $admin_aziend['user_name']; ?>&Update");
					}
				  }
				}
			}
		});
		$("#dialog_module_card" ).dialog( "open" );
	});
});
</script>
<form method="POST" enctype="multipart/form-data"  autocomplete="off">
<input type="hidden" name="ritorno" value="<?php print $_POST['ritorno']; ?>">
<input type="hidden" name="hidden_req" value="<?php if (isset($_POST['hidden_req'])){ print $_POST['hidden_req']; } ?>">
<?php
if ($toDo == 'insert') {
	echo "<div class=\"text-center\"><h3>" . $script_transl['ins_this'] . "</h3></div>\n";
} else {
	echo "<div class=\"text-center\"><h3>" . $script_transl['upd_this'] . " '" . $form["user_name"] . "'</h3></div>\n";
	echo "<input type=\"hidden\" value=\"" . $form["user_name"] . "\" name=\"user_name\" />\n";
}
$gForm = new configForm();
if (count($msg['err']) > 0) { // ho un errore
	$gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="">';
?>
<table class="Tmiddle table-striped">
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_lastname']; ?>* </td>
<td colspan="2" class="FacetDataTD"><input title="Cognome" type="text" name="user_lastname" value="<?php print $form["user_lastname"] ?>" maxlength="30"  class="FacetInput">&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_firstname']; ?></td>
<td colspan="2" class="FacetDataTD"><input title="Nome" type="text" name="user_firstname" value="<?php print $form["user_firstname"] ?>" maxlength="30"  class="FacetInput">&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_email']; ?></td>
<td colspan="2" class="FacetDataTD"><input title="Mail" type="email" name="user_email" value="<?php print $form["user_email"] ?>" class="FacetInput" maxlength="50">&nbsp;</td>
</tr>
<tr>
<?php
print "<td class=\"FacetFieldCaptionTD\"><img src=\"../root/view.php?table=admin&value=" . $form["user_name"] . "&field=user_name\" width=\"100\"></td>";
print "<td colspan=\"2\" class=\"FacetDataTD\">" . $script_transl['image'] . ":<br /><input name=\"userfile\" type=\"file\" class=\"FacetDataTD\"></td>";
?>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['lang']; ?></td>
<?php
echo '<td colspan="2" class="FacetDataTD">';
echo '<select name="lang" class="FacetSelect">';
$relativePath = '../../language';
if ($handle = opendir($relativePath)) {
	while ($file = readdir($handle)) {
		if (($file == ".") or ( $file == "..") or ( $file == ".svn"))
		continue;
		$selected = "";
		if ($form["lang"] == $file) {
			$selected = " selected ";
		}
		echo "<option value=\"" . $file . "\"" . $selected . ">" . ucfirst($file) . "</option>";
	}
	closedir($handle);
}
echo "</td></tr>\n";
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['theme']; ?> </td>
<td colspan="2" class="FacetDataTD">
<?php
$gForm->selThemeDir('theme', $form["theme"]);
?>
</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['style']; ?></td>
<?php
echo '<td colspan="2" class="FacetDataTD">';
echo '<select name="style" class="FacetSelect">';
$relativePath = '../..' . $_SESSION['theme'] . '/scheletons/';
if ($handle = opendir($relativePath)) {
	while ($file = readdir($handle)) {
		// accetto solo i file css
		if (!preg_match("/^[a-z0-9\s\_\-]+\.css$/", $file)) {
			continue;
		}
		$selected = "";
		if ($form["style"] == $file) {
			$selected = " selected ";
		}
		echo "<option value=\"" . $file . "\"" . $selected . ">" . $file . "</option>";
	}
	closedir($handle);
}
echo "</td></tr>\n";
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['skin']; ?></td>
<?php
echo '<td colspan="2" class="FacetDataTD">';
echo '<select name="skin" class="FacetSelect">';
$relativePath = '../..' . $_SESSION['theme'] . '/skins/';
if ($handle = opendir($relativePath)) {
	while ($file = readdir($handle)) {
		// accetto solo i file css
		if (!preg_match("/^[a-z0-9\s\_\-]+\.css$/", $file)) {
			continue;
		}
		$selected = "";
		if ($form["skin"] == $file) {
			$selected = " selected ";
		}
		echo "<option value=\"" . $file . "\"" . $selected . ">" . $file . "</option>";
	}
	closedir($handle);
}
echo "</td></tr>\n";
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Abilit']; ?></td>
<td colspan="2" class="FacetDataTD">
<?php
    $gForm->variousSelect('Abilit', $script_transl['Abilit_value'], $form['Abilit'], "col-sm-8", true, '', false, 'style="max-width: 300px;"');
?>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['mesg_co'][2]; ?></td>
<td class="FacetDataTD" colspan="2">
<?php
if ($user_data['Abilit'] == 9) {
	$gForm->selectCompany('company_id', $form['company_id'], $form['search']['company_id'], $form['hidden_req'], $script_transl['mesg_co']);
} else {
	$company = gaz_dbi_get_row($gTables['aziend'], 'codice', $form['company_id']);
	echo '<input type="hidden" name="company_id" value="'.$form['company_id'].'">';
	echo $company['ragso1'].' '.$company['ragso2'];
}
?>
</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Magazzino predefinito</td>
<td class="FacetDataTD" colspan="2">
<?php
	$gForm->selectFromDB('warehouse','id_warehouse','id',$form["id_warehouse"],'id',false,' - ','name','0','col-sm-6',['value'=>0,'descri'=>'Sede'],'');
?>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['Access']; ?></td>
<td colspan="2" class="FacetDataTD"><input title="Accessi" type="text" name="Access" value="<?php print $form["Access"] ?>" maxlength="7" class="FacetInput">&nbsp;</td>
</tr>
<?php
if ($toDo == 'insert') {
	echo '<tr><td class="FacetFieldCaptionTD">' . $script_transl["user_name"] . ' *</td>
	<td class="FacetDataTD" colspan="2"><input title="user_name" type="text" name="user_name" value="' . $form["user_name"] . '" maxlength="20" class="FacetInput">&nbsp;</td>
	</tr>';
}
?>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_password_old'] ?></td>
<td colspan="2" class="FacetDataTD"><input title="Vecchia password" type="password" id="login-password" name="user_password_old" value="<?php echo $form["user_password_old"]; ?>" maxlength="40" class="FacetInput" id="ppass" /><div class="FacetDataTDred" id="pmsg"></div>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD" colspan="3">
<div id="capsWarning" class="alert alert-warning col-sm-12" style="display:none;">Blocco maiuscole attivato! Caps lock on! Bloqueo de mayusculas!</div>
</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_password_new']; ?> </td>
<td colspan="2" class="FacetDataTD"><input title="Conferma Password" type="password" id="user_password_new" name="user_password_new" value="<?php print $form["user_password_new"]; ?>" maxlength="40" class="FacetInput" id="cpass" /><div class="FacetDataTDred" id="cmsg"></div>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_password_ver']; ?></td>
<td colspan="2" class="FacetDataTD"><input title="Conferma Password" type="password" id="user_password_ver" name="user_password_ver" value="<?php print $form["user_password_ver"]; ?>" maxlength="40" class="FacetInput" id="cpass" /><div class="FacetDataTDred" id="cmsg"></div>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['user_active']; ?></td>
<td colspan="2" class="FacetDataTD">
<?php
    $gForm->variousSelect('user_active', $script_transl['user_active_value'], $form['user_active'], "col-sm-8", true, '', false, 'style="max-width: 300px;"');
?>
<div class="FacetDataTDred" id="user_active"></div>&nbsp;</td>
</tr>
<tr>
<td class="FacetFieldCaptionTD"><?php echo $script_transl['body_text']; ?></td>
<td colspan="2" class="FacetDataTD">
<textarea id="body_text" name="body_text" class="mceClass" style="width:100%;"><?php echo $form['body_text']; ?></textarea>
</td>
</tr>

<?php
if ($user_data["Abilit"] == 9) {
	function getModule($login, $company_id) {
		global $gTables, $admin_aziend;
		//trovo i moduli installati
		$mod_found = [];
		$relativePath = '../../modules';
		if ($handle = opendir($relativePath)) {
			while ($exist_mod = readdir($handle)) {
				if ($exist_mod == "." || $exist_mod == ".." || $exist_mod == ".svn" || $exist_mod == "root" || !file_exists("../../modules/$exist_mod/menu." . $admin_aziend['lang'] . ".php"))
				continue;
				$rs_mod = gaz_dbi_dyn_query("am.access,am.moduleid, am.custom_field, module.name ", $gTables['admin_module'] . ' AS am LEFT JOIN ' . $gTables['module'] .
				' AS module ON module.id=am.moduleid ', " am.adminid = '" . $login . "' AND module.name = '$exist_mod' AND am.company_id = '$company_id'", "am.adminid", 0, 1);
				require("../../modules/$exist_mod/menu." . $admin_aziend['lang'] . ".php");
				$row = gaz_dbi_fetch_array($rs_mod);
				$row['excluded_script'] = [];
				if (!isset($row['moduleid'])) {
					$row['name'] = $exist_mod;
					$row['moduleid'] = 0;
					$row['access'] = 0;
          $row['custom_field'] = '';
				}
        $chkes = is_string($row['custom_field'])?json_decode($row['custom_field']):false;
        if ($chkes && isset($chkes->excluded_script)) {
 					$row['excluded_script'] = $chkes->excluded_script;
        }
				$row['transl_name'] = $transl[$exist_mod]['name'];
				$mod_found[$exist_mod] = $row;
			}
			closedir($handle);
		}
		return $mod_found;
	}

	//richiamo tutte le aziende installate e vedo se l'utente  e' abilitato o no ad essa
	$table = $gTables['aziend'] . ' AS a';
	$what = "a.codice AS id, ragso1 AS ragsoc, (SELECT COUNT(*) FROM " . $gTables['admin_module'] . " WHERE a.codice=" . $gTables['admin_module'] . ".company_id AND " . $gTables['admin_module'] . ".adminid='" . $form["user_name"] . "') AS set_co ";
	$co_rs = gaz_dbi_dyn_query($what, $table, 1, "ragsoc ASC");
	while ($co = gaz_dbi_fetch_array($co_rs)) {
		$co_id = sprintf('%03d', $co['id']);
		echo '</table><br/><div class="text-center"><h3><img src="../../modules/root/view.php?table=aziend&value='.$co['id'].'" alt="Logo" height="30"> ' . $co['ragsoc'] . '  - ID:' . $co['id'] . '</h3></div><table class="Tmiddle table-striped"><tbody>';
		echo "<tr><td class=\"FacetDataTD\">" .'<input type=hidden name="' . $co_id . 'nusr_root" value="3"><b>'. $script_transl['mod_perm'] . ":</b></td>\n";
		echo "<td><b>" . $script_transl['all'] . "</b></td>\n";
		echo '<td align="center"><b> Script esclusi</b></td>';
		echo "<td><b>" . $script_transl['none'] . "</b></td></tr>\n";
		$mod_found = getModule($form["user_name"], $co['id']);
		$mod_admin = getModule($user_data["user_name"], $co['id']);
		foreach ($mod_found as $mod) {
			echo "<tr>\n";
			echo '<td>
								<img height="16" src="../' . $mod['name'] . '/' . $mod['name'] . '.png" /> ' . $mod['transl_name'] . ' (' . $mod['name'] . ")</td>\n";
			if ($mod['moduleid'] == 0) { // il modulo non è stato mai attivato
				if ($form["user_name"] <> $user_data["user_name"]) { // sono un amministratore che sta operando sul profilo di altro utente
          if ($mod_admin[$mod['name']]['access']==3){ // il modulo è attivo sull'amministratore
              // per evitare conflitti nemmeno l'amministratore può attivare un modulo se questo non lo è ancora sul suo
              echo "  <td colspan=2 ><input type=radio name=\"" . $co_id . "nusr_" . $mod['name'] . "\" value=\"3\"></td>";
              echo "  <td><input type=radio checked name=\"" . $co_id . "nusr_" . $mod['name'] . "\" value=\"0\"></td>";
          } else { // modulo non attivo sull'amministratore
              echo '  <td colspan=2 >Non attivato</td>';
              echo '  <td><input type="hidden"  name="' . $co_id . "nusr_" . $mod['name'] . '" value="0"></td>';
          }
				} elseif ($co['set_co'] == 0) { // il modulo mai attivato
					echo "  <td colspan=2><input type=radio name=\"" . $co_id . "nusr_" . $mod['name'] . "\" value=\"3\"></td>";
					echo "  <td><input type=radio checked name=\"" . $co_id . "nusr_" . $mod['name'] . "\" value=\"0\"></td>";
				} else { // se l'amministratore che sta operando sul proprio profilo può attivare un nuovo modulo e creare il relativo menù
					echo "  <td class=\"FacetDataTDred\" colspan=2><input class=\"btn btn-warning\" type=radio name=\"" . $co_id . "new_" . $mod['name'] . "\" value=\"3\">Modulo attivabile</td>";
					echo "  <td class=\"FacetDataTDred\"><input type=radio checked name=\"" . $co_id . "new_" . $mod['name'] . "\" value=\"0\"></td>";
				}
			} elseif ($mod['access'] == 0) { // il modulo è attivato, quindi propongo i valori precedenti
				echo "  <td colspan=2><input type=radio name=\"" . $co_id . "acc_" . $mod['moduleid'] . "\" value=\"3\"></td>";
				echo "  <td><input type=radio checked name=\"" . $co_id . "acc_" . $mod['moduleid'] . "\" value=\"0\"></td>";
			} else {
				echo '<td><input type=radio checked name="'. $co_id . 'acc_' . $mod['moduleid'] . '" value="3"> </td><td><a class="btn btn-xs dialog_module_card" module="'.$mod['name'].'" adminid="'.$form['user_name'].'" transl_name="'.$mod['transl_name'].'"><i class="glyphicon glyphicon-edit"></i>'.((count($mod['excluded_script'])>=1)?'<p class="text-left">'.implode('.php</p><p class="text-left">',$mod['excluded_script']).'.php</p>':'nessuno</p>').'</a></td>';
				echo "  <td><input type=radio name=\"" . $co_id . "acc_" . $mod['moduleid'] . "\" value=\"0\"></td>";
			}
			echo "</tr>\n";
		}
	}
}

?>
</table><br/>
<div class="FacetFooterTD text-center"><input name="Submit" class="btn btn-warning" type="submit" value="<?php echo ucfirst($script_transl[$toDo]); ?>"></div>
</form>
<?php
if ($admin_aziend['Abilit']==9){
	?>
	<div style="display:none; padding-bottom: 30px;" id="dialog_module_card" title="Disabilitazione script">
    <p><b>Modulo:</b></p>
		<p class="ui-state-highlight" id="iddescri"></p>
		<table id="tblAppendGrid"></table>
	</div>
	<div style="padding-top: 30px; padding-bottom: 3000px;">
    <div class="col-sm-12 col-md-1"></div><div class="col-sm-12 col-md-11"><b>Gli amministratore possono </b> <a data-toggle="collapse" class="btn btn-sm btn-warning" href="#gconfig" aria-expanded="false" aria-controls="gconfig"> accedere ai dati globali ↕ </a></div>
    <div class="collapse" id="gconfig">
      <iframe src="../../modules/root/set_config_data.php?iframe=TRUE" title="Configurazione globale" width="100%" height="1330"  frameBorder="0"></iframe>
    </div>
	</div>
	<?php
}
?>
<?php
require("../../library/include/footer.php");
?>
