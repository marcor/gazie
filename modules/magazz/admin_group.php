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
// prima stesura: Antonio Germani
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$msg = array('err' => array(), 'war' => array());
$modal_ok_insert = false;
$today=	strtotime(date("Y-m-d H:i:s",time()));
$presente="";
$largeimg="";
/** ENRICO FEDELE */
/* Inizializzo per aprire in finestra modale */
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
  $form = gaz_dbi_parse_post('artico_group');
  $form['id_artico_group'] = trim($form['id_artico_group']);
  $form['ritorno'] = $_POST['ritorno'];
  $form['ref_ecommerce_id_main_product'] = substr($_POST['ref_ecommerce_id_main_product'], 0, 9);  
  $form['large_descri'] = filter_input(INPUT_POST, 'large_descri');
  /** ENRICO FEDELE */
  /* Controllo se il submit viene da una modale */
  if (isset($_POST['Submit']) || ($modal === true && isset($_POST['mode-act']))) { // conferma tutto
    /** ENRICO FEDELE */
    if ($toDo == 'update') {  // controlli in caso di modifica
        
    } else {
        // controllo che l'articolo ci sia gia'
        $rs_articolo = gaz_dbi_dyn_query('id_artico_group', $gTables['artico_group'], "id_artico_group = '" . $form['id_artico_group'] . "'", "id_artico_group DESC", 0, 1);
        $rs = gaz_dbi_fetch_array($rs_articolo);
        if ($rs) {
            $msg['err'][] = 'codice';
        }
    }
    if (!empty($_FILES['userfile']['name'])) {
      if (!( $_FILES['userfile']['type'] == "image/png" ||
              $_FILES['userfile']['type'] == "image/x-png" ||
              $_FILES['userfile']['type'] == "image/jpeg" ||
              $_FILES['userfile']['type'] == "image/jpg" ||
              $_FILES['userfile']['type'] == "image/gif" ||
              $_FILES['userfile']['type'] == "image/x-gif")) $msg['err'][] = 'filmim';
				// controllo che il file non sia piu' grande di circa 64kb
      if ($_FILES['userfile']['size'] > 65530){
				//Antonio Germani anziche segnalare errore ridimensiono l'immagine
				$maxDim = 190;
				$file_name = $_FILES['userfile']['tmp_name'];
				list($width, $height, $type, $attr) = getimagesize( $file_name );
				if ( $width > $maxDim || $height > $maxDim ) {
					$target_filename = $file_name;
					$ratio = $width/$height;
					if( $ratio > 1) {
						$new_width = $maxDim;
						$new_height = $maxDim/$ratio;
					} else {
						$new_width = $maxDim*$ratio;
						$new_height = $maxDim;
					}
					$src = imagecreatefromstring( file_get_contents( $file_name ) );
					$dst = imagecreatetruecolor( $new_width, $new_height );
					imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
					imagedestroy( $src );
					imagepng( $dst, $target_filename); // adjust format as needed
					imagedestroy( $dst );
				}
			// fine ridimensionamento immagine
			$largeimg=1;
			}
    }
    if (empty($form["id_artico_group"]) AND $toDo == 'update') {
        $msg['err'][] = 'valcod';
    }
    if (empty($form["descri"])) {
        $msg['err'][] = 'descri';
    }

  if (count($msg['err']) == 0) { // nessun errore
    if (!empty($_FILES['userfile']) && $_FILES['userfile']['size'] > 0) { //se c'e' una nuova immagine nel buffer
			if ($largeimg==0){
				$form['image'] = file_get_contents($_FILES['userfile']['tmp_name']);
			} else {
				$form['image'] = file_get_contents($target_filename);
			}
    } elseif ($toDo == 'update') { // altrimenti riprendo la vecchia ma solo se è una modifica
      $oldimage = gaz_dbi_get_row($gTables['artico_group'], 'id_artico_group', $form['ref_ecommerce_id_main_product']);
      $form['image'] = $oldimage['image'];
    } else {
      $form['image'] = '';
    }
    
    $form['large_descri'] = htmlspecialchars_decode (addslashes($form['large_descri']));
    // aggiorno il db
    if ($toDo == 'insert') {
      gaz_dbi_table_insert('artico_group', $form);     
    } elseif ($toDo == 'update') {
      gaz_dbi_table_update('artico_group', array( 0 => "id_artico_group", 1 => $form['id_artico_group']), $form);
     
      
    }
    if (!empty($admin_aziend['synccommerce_classname']) && class_exists($admin_aziend['synccommerce_classname'])){
        // Aggiornamento parent su e-commerce
		
        $gs=$admin_aziend['synccommerce_classname'];
        $gSync = new $gs();
		if($gSync->api_token){
			
			$gSync->UpsertParent($form);
			
			//exit;
		}
		
	}
    /** ENRICO FEDELE */
    /* Niente redirect se sono in finestra modale */
    if ($modal === false) {
		
		header("Location: ../../modules/magazz/report_artico.php");
        exit;
			
    } else {
		header("Location: ../../modules/magazz/admin_artico.php?mode=modal&ok_insert=1");
      exit;
    }
  }
  /** ENRICO FEDELE */
} elseif (isset($_POST['Return']) && $modal === false) { // torno indietro
	/* Solo se non sono in finestra modale */
	/** ENRICO FEDELE */
	header("Location: " . $form['ritorno']);
	exit;
}
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    $form = gaz_dbi_get_row($gTables['artico_group'], 'id_artico_group', substr($_GET['id_artico_group'], 0, 9));
    /** ENRICO FEDELE */
    if ($modal === false) {
        $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    } else {
        $form['ritorno'] = 'admin_artico.php';
    }
  
} else { //se e' il primo accesso per INSERT
    $form = gaz_dbi_fields('artico');
    /** ENRICO FEDELE */
    if ($modal === false) {
        $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    } else {
        $form['ritorno'] = 'admin_artico.php';
    }
   
    $form['web_public'] = 1;
    $form['depli_public'] = 1;
    
    // eventuale descrizione ampliata
    $form['large_descri'] = '';	
	$form['ref_ecommerce_id_main_product']="";
	$form['id_artico_group'] = "";
   
}

/** ENRICO FEDELE */
/* Solo se non sono in finestra modale carico il file di lingua del modulo */
if ($modal === false) {
    require("../../library/include/header.php");
    $script_transl = HeadMain(0, array('custom/autocomplete'));
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
?>

<form method="POST" name="form" enctype="multipart/form-data" id="add-product">
	<?php 
	if (!empty($form['descri'])) $form['descri'] = htmlentities($form['descri'], ENT_QUOTES);
	if ($modal === true) {
		echo '<input type="hidden" name="mode" value="modal" />
			  <input type="hidden" name="mode-act" value="submit" />';
	}
	echo '<input type="hidden" name="ritorno" value="' . $form['ritorno'] . '" />';
	echo '<input type="hidden" name="ref_ecommerce_id_main_product" value="' . $form['ref_ecommerce_id_main_product'] . '" />';

	if ($modal_ok_insert === true) {
		echo '<div class="alert alert-success" role="alert">' . $script_transl['modal_ok_insert'] . '</div>';
		echo '<div class=" text-center"><button class="btn btn-lg btn-default" type="submit" name="none">' . $script_transl['iterate_invitation'] . '</button></div>';
	} else {
	   $gForm = new magazzForm();
		/** ENRICO FEDELE */
		/* Se sono in finestra modale, non visualizzo questo titolo */
		$changesubmit = '';
		if ($modal === false) {
			?>
				<!--+ DC - 06/02/2019 -->
				<script type="text/javascript" src="../../library/IER/IERincludeExcludeRows.js"></script>

				<input type="hidden" id="IERincludeExcludeRowsInput" name="IERincludeExcludeRowsInput" />

			<div id="IERenableIncludeExcludeRows" title="Personalizza videata" onclick="enableIncludeExcludeRows()"></div>
				<a target="_blank" href="../wiki/099 - Interfaccia generale/99.. Personalizzare una form a run-time (lato utente).md"><div id="IERhelpIncludeExcludeRows" title="Aiuto"></div></a>
				<div id="IERsaveIncludeExcludeRows" title="Nessuna modifica fatta" onclick="saveIncludeExcludeRows()"></div>
			<div id="IERresetIncludeExcludeRows" title="Ripristina"></div>
			<!--- DC - 06/02/2019 -->
				<?php
		}
		echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
		if (count($msg['err']) > 0) { // ho un errore
			$gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
		}
		if (isset($_SESSION['ok_ins'])){
			$gForm->toast('L\'articolo ' . $_SESSION['ok_ins'].' è stato inserito con successo, sotto per modificarlo. Oppure puoi: <a class="btn btn-info" href="admin_artico.php?Insert">Inserire uno nuovo articolo</a> ' , 'alert-last-row', 'alert-success');
			unset($_SESSION['ok_ins']);
		}
		if ($toDo == 'insert') {
			echo '<div class="text-center"><h3>' . $script_transl['ins_this'] . '</h3></div>';
		} else {
			echo '<div class="text-center"><h3>' . $script_transl['upd_this'] . ' ' . $form['id_artico_group'] . '</h3></div>';
		}
		?>
		<div class="text-center"><p>Solitamente, gli e-commerce usano creare degli articoli, le varianti, molto simili fra loro ponendoli sotto un articolo principale, il genitore. <br>Per GAzie, il genitore è il gruppo e le varianti sono i singoli articoli che fanno riferimento allo stesso gruppo. </p></div>';
		<div class="panel panel-default gaz-table-form div-bordered">
			<div class="container-fluid">
				<ul class="nav nav-pills">
					<li class="active"><a data-toggle="pill" href="#home">Dati principali</a></li>
					<li><a data-toggle="pill" href="#magazz">Magazzino</a></li>
					<li><a data-toggle="pill" href="#contab">Contabilità</a></li>
					<li><a data-toggle="pill" href="#chifis">Chimico-fisiche</a></li>
					<li style="float: right;"><?php echo '<input name="Submit" type="submit" class="btn btn-warning" value="' . ucfirst($script_transl[$toDo]) . '" />'; ?></li>
				</ul>  
				<div class="tab-content">
					<div id="home" class="tab-pane fade in active">
						<?php if ($toDo !== 'insert'){?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label for="codice" class="col-sm-4 control-label"><?php echo $script_transl['codice']; ?></label>
									<input class="col-sm-4" type="text" value="<?php echo $form["id_artico_group"]; ?>" name="id_artico_group" id="id_artico_group" maxlength="9" tabindex="1" readonly="readonly"/>
									</td>
								</div>
							</div>
						</div><!-- chiude row  -->
						<?php } else {
							echo '<input type="hidden" name="id_artico_group" value="" />';
						}?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label for="descri" class="col-sm-4 control-label"><?php echo $script_transl['descri']; ?></label>
									<input class="col-sm-8" type="text" value="<?php echo $form['descri']; ?>" name="descri" maxlength="255" id="suggest_descri_artico" />
								</div>
							</div>
						</div><!-- chiude row  -->
						<!--+ DC - 06/02/2019 -->
						<!--
						Come rendere una videata personalizzabile:
						Su tutte le div con class="row" (tranne quelle che contengono campi obbligatori)
						sostituirle nel seguente modo:
						PRIMA:
						<div class="row">
						DOPO:
						<div id="catMer" class="row IERincludeExcludeRow">
						In pratica inserite un id (unico per ogni riga) ed aggiungere la classe "IERincludeExcludeRow"
						-->
											  
						<!--+ DC - 06/02/2019 div class="row" --->
						<div id="bodyText" class="row IERincludeExcludeRow">
							<div class="col-md-12">
								<div class="form-group">
									<label for="large_descri" class="col-sm-4 control-label"><?php echo $script_transl['body_text']; ?></label>
									<div class="col-sm-8">
										<textarea id="large_descri" name="large_descri" class="mceClass"><?php echo $form['large_descri']; ?></textarea>
									</div>
								</div>
							</div>
						</div><!-- chiude row  -->					   
					   
						<!--+ DC - 06/02/2019 div class="row" --->
						<div id="image" class="row IERincludeExcludeRow">
							<div class="col-md-12">
								<div class="form-group">
									<label for="image" class="col-sm-4 control-label"><img src="../root/view.php?table=artico_group&value=<?php echo $form['id_artico_group']; ?>&field=id_artico_group" width="100" >*</label>
									<div class="col-sm-8"><?php echo $script_transl['image']; ?><input type="file" name="userfile" /></div>
								</div>
							</div>
						</div><!-- chiude row  -->				   
					</div><!-- chiude tab-pane  -->	
					
					<div id="magazz" class="tab-pane fade">									
						<!--+ DC - 06/02/2019 div class="row" --->
						<div id="refEcommercIdProduct" class="row IERincludeExcludeRow">
							<div class="col-md-12">
								<div class="form-group">
									<label for="ref_ecommerce_id_product" class="col-sm-4 control-label">ID riferimento e-commerce</label>
									<input class="col-sm-4" type="text" value="<?php echo $form['ref_ecommerce_id_main_product']; ?>" name="ref_ecommerce_id_main_product" maxlength="15" />
								</div>
							</div>
						</div><!-- chiude row  -->
						<!--+ DC - 06/02/2019 div class="row" --->
						<div id="webUrl" class="row IERincludeExcludeRow">
						<div class="col-md-12">
							<div class="form-group">
								<label for="web_url" class="col-sm-4 control-label"><?php echo $script_transl['web_url']; ?></label>
								<input class="col-sm-8" type="text" value="<?php echo $form['web_url']; ?>" name="web_url" maxlength="255" />
							</div>
						</div>
						</div><!-- chiude row  -->
						<!--+ DC - 06/02/2019 div class="row" --->
						<div id="depliPublic" class="row IERincludeExcludeRow">
							<div class="col-md-12">
								<div class="form-group">
									<label for="depli_public" class="col-sm-4 control-label"><?php echo $script_transl['depli_public']; ?></label>
			<?php
			$gForm->variousSelect('depli_public', $script_transl['depli_public_value'], $form['depli_public'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
			?>
								</div>
							</div>
						</div><!-- chiude row  -->
						<!--+ DC - 06/02/2019 div class="row" --->
						<div id="webPublic" class="row IERincludeExcludeRow">
							<div class="col-md-12">
								<div class="form-group">
									<label for="web_public" class="col-sm-4 control-label"><?php echo $script_transl['web_public']; ?></label>
			<?php
			$gForm->variousSelect('web_public', $script_transl['web_public_value'], $form['web_public'], "col-sm-8", true, '', false, 'style="max-width: 200px;"');
			?>
								</div>
							</div>
						</div><!-- chiude row  -->				
					</div><!-- chiude tab-pane  -->
					
					<div id="contab" class="tab-pane fade">
					
					</div><!-- chiude tab-pane  -->
					
					<div id="chifis" class="tab-pane fade">					
					
			<?php if ($toDo == 'update') { ?>
					   
						<!-- DA FARE IN SEGUITO, SE SERVIRà -- Antonio Germani inserimento/modifica immagini di qualità per e-commerce 
						
						<div id="qualityImgs" class="row IERincludeExcludeRow">
							<div class="col-md-12">
								<div class="form-group">
									<label for="annotaUpdate" class="col-sm-4 control-label"><?php echo $script_transl['imageweb']; ?></label>
			<?php if ($nimg > 0) { // se ho dei documenti  ?>
										<div>
										<?php foreach ($form['imgrows'] as $k => $val) { ?>
												<input type="hidden" value="<?php echo $val['id_doc']; ?>" name="imgrows[<?php echo $k; ?>][id_doc]">
												<input type="hidden" value="<?php echo $val['extension']; ?>" name="imgrows[<?php echo $k; ?>][extension]">
												<input type="hidden" value="<?php echo $val['title']; ?>" name="imgrows[<?php echo $k; ?>][title]">
					<?php echo DATA_DIR . 'files/' . $admin_aziend['company_id'] . '/images/' . $val['id_doc'] . '.' . $val['extension']; ?>
												<a href="../root/retrieve.php?id_doc=<?php echo $val["id_doc"]; ?>" title="<?php echo $script_transl['view']; ?>!" class="btn btn-default btn-sm">
													<i class="glyphicon glyphicon-file"></i>
												</a><?php echo $val['title']; ?>
												<input type="button" value="<?php echo ucfirst($script_transl['update']); ?>" onclick="location.href = 'admin_image.php?id_doc=<?php echo $val['id_doc']; ?>&Update'" />

				<?php } ?>
											<input type="button" value="<?php echo ucfirst($script_transl['insert']); ?>" onclick="location.href = 'admin_image.php?item_ref=<?php echo $form['id_artico_group']; ?>&Insert'" />
										</div>
										<?php } else { // non ho documenti  ?>
										<input type="button" value="<?php echo ucfirst($script_transl['insert']); ?>" onclick="location.href = 'admin_image.php?item_ref=<?php echo $form['id_artico_group']; ?>&Insert'">
									<?php } ?>
								</div>
							</div>
						</div>
						-->
		<?php } ?>
				</div><!-- chiude tab-pane  -->
				</div><!-- chiude tab content -->
				<div class="col-sm-12">
		<?php
		/** ENRICO FEDELE */
		/* SOlo se non sono in finestra modale */
		if ($modal === false) {
			echo '<div class="col-sm-4 text-left"><input name="none" type="submit" value="" disabled></div>';
		}
		/** ENRICO FEDELE */
		echo '<div class="col-sm-8 text-center"><input name="Submit" type="submit" class="btn btn-warning" value="' . ucfirst($script_transl[$toDo]) . '" /></div>';
	}
	?>
				</div>
			</div> <!-- chiude container -->
		</div><!-- chiude panel -->
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
    });</script>


<?php
/** ENRICO FEDELE */
/* SOlo se non sono in finestra modale */
if ($modal === false) {
} else {
    ?>
    <script type="text/javascript">
        $("#add-product").submit(function (e) {
            $.ajax({
                type: "POST",
                url: "../../modules/magazz/admin_group.php",
                data: $("#add-product").serialize(), // serializes the form's elements.
                success: function (data) {
                    $("#edit-modal .modal-sm").css('width', '100%');
                    $("#edit-modal .modal-body").html(data);
                }
            });
            e.preventDefault(); // avoid to execute the actual submit of the form.
        });
    </script>
    <?php
}
require("../../library/include/footer.php");
?>