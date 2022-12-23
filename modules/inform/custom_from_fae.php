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
$admin_aziend = checkAdmin();
$msg = array('err' => array(), 'war' => array());
$tipdoc_conv=array('TD01'=>'FAI','TD02'=>'FAA','TD03'=>'FAQ','TD04'=>'FNC','TD05'=>'FND','TD06'=>'FAP','TD24'=>'FAD','TD25'=>'FND','TD26'=>'FAF');
$preview = false; // visualizza dopo upload
$iszip = false;

function removeSignature($string, $filename) {
  $string = substr($string, strpos($string, '<?xml '));
  preg_match_all('/<\/.+?>/', $string, $matches, PREG_OFFSET_CAPTURE);
  $lastMatch = end($matches[0]);
	// trovo l'ultimo carattere del tag di chiusura per eliminare la coda
	$f_end = $lastMatch[1]+strlen($lastMatch[0]);
  $string = substr($string, 0, $f_end);
	// elimino le sequenze di caratteri aggiunti dalla firma (ancora da testare approfonditamente)
	$string = preg_replace ('/[\x{0004}]{1}[\x{0082}]{1}[\x{0001}\x{0002}\x{0003}\x{0004}]{1}[\s\S]{1}/i', '', $string);
	$string = preg_replace ('/[\x{0004}]{1}[\x{0081}]{1}[\s\S]{1}/i', '', $string);
	$string = preg_replace ('/[\x{0004}]{1}[A-Za-z]{1}/i', '', $string); // per eliminare tag finale
	return $string;
}

if (!isset($_POST['fattura_elettronica_original_name'])) { // primo accesso nessun upload
	$form['fattura_elettronica_original_name'] = '';
} else { // accessi successivi
	$form['fattura_elettronica_original_name'] = filter_var($_POST['fattura_elettronica_original_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	if (isset($_POST['Submit_file'])) { // conferma invio upload file
    if (!empty($_FILES['userfile']['name'])) {
      if ( $_FILES['userfile']['type'] == "application/pkcs7-mime" || $_FILES['userfile']['type'] == "text/xml" || $_FILES['userfile']['type'] == "application/zip"|| $_FILES['userfile']['type'] == "application/x-zip-compressed" ) {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], DATA_DIR.'files/' . $admin_aziend['codice'] . '/' . $_FILES['userfile']['name'])) { // nessun errore
          $form['fattura_elettronica_original_name'] = $_FILES['userfile']['name'];
          $preview = true;
        } else { // no upload
          $msg['err'][] = 'no_upload';
        }
        if ($_FILES['userfile']['type'] == "application/zip"|| $_FILES['userfile']['type'] == "application/x-zip-compressed" ) {
          $iszip = true;
        }
      } else { // mime del file non valido
        $msg['err'][] = 'filmim';
      }
		} else {
			$msg['err'][] = 'no_upload';
		}
	} else if (isset($_POST['Submit_form'])) { // ho  confermato l'inserimento
	} else if (isset($_POST['Download'])) { // faccio il download dell'allegato
	}
	if ($preview) { // non ho errori  vincolanti sul file posso proporre la visualizzazione in base al contenuto del file che ho caricato
    // definisco l'array dei righi
    $form['rows'] = [];
  }
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new informForm();
?>
<script type="text/javascript">
    $(function () {
        $("#datreg").datepicker({showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true});
        $("#datreg").change(function () {
            this.form.submit();
        });
    });
</script>
<div align="center" ><h2><?php echo $script_transl['title'];?></h2></div>
<form method="POST" name="form" enctype="multipart/form-data" id="add-invoice">
    <input type="hidden" name="fattura_elettronica_original_name" value="<?php echo $form['fattura_elettronica_original_name']; ?>" />
<?php
	// INIZIO form che permetterà all'utente di interagire per (es.) imputare i vari costi al piano dei conti (contabilità) ed anche le eventuali merci al magazzino
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    if (count($msg['war']) > 0) { // ho un alert
        $gForm->gazHeadMessage($msg['war'], $script_transl['war'], 'war');
    }
if ($preview){
 ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12"><b><?php echo $script_transl['head_text1']. '</b><span class="label label-success">'.$form['fattura_elettronica_original_name'] .'</span><b>'.$script_transl['head_text2']; ?></b>
            </div>
        </div> <!-- chiude row  -->
    </div>
    <div class="panel-body">

<?php
		foreach ($form['rows'] as $k => $v) {
			// creo l'array da passare alla funzione per la creazione della tabella responsive
      /*
            $resprow[$k] = array(
                array('head' => $script_transl["nrow"], 'class' => '',
                    'value' => $k+1),
                array('head' => $script_transl["codart"], 'class' => '',
                    'value' => $codart_dropdown),
                array('head' => $script_transl["descri"], 'class' => 'col-sm-12 col-md-3 col-lg-3',
                    'value' => $v['descri']),
                array('head' => $script_transl["unimis"], 'class' => '',
                    'value' => $v['unimis']),
                array('head' => $script_transl["quanti"], 'class' => 'text-right numeric',
                    'value' => $v['quanti']),
                array('head' => $script_transl["prezzo"], 'class' => 'text-right numeric',
                    'value' => $v['prelis']),
                array('head' => $script_transl["sconto"], 'class' => 'text-right numeric',
                    'value' => $v['sconto']),
                array('head' => $script_transl["amount"], 'class' => 'text-right numeric',
					'value' => $v['amount'], 'type' => ''),
                array('head' => $script_transl["tax"], 'class' => 'text-center numeric',
					'value' => $codvat_dropdown, 'type' => ''),
                array('head' => 'Ritenuta', 'class' => 'text-center numeric',
					'value' => $v['ritenuta'], 'type' => ''),
                array('head' => '%', 'class' => 'text-center numeric',
					'value' => $v['pervat'], 'type' => ''),
                array('head' => $script_transl["conto"], 'class' => 'text-center numeric',
					'value' => $codric_dropdown, 'type' => '')
            );
      */
		}
		//$gForm->gazResponsiveTable($resprow, 'gaz-responsive-table');
?>
	   <div class="col-sm-6 text-right"><input name="Submit_form" type="submit" class="btn btn-warning" value="<?php echo $script_transl['submit']; ?>" /> </div>
    </div>
</div>


<?php
} else { // all'inizio chiedo l'upload di un file xml, p7m o zip
?>
<div class="panel panel-default gaz-table-form">
	<div class="container-fluid">
       <div class="row">
           <div class="col-md-12">
               <div class="form-group">
                   <label for="image" class="col-sm-4 control-label">Seleziona il file ( xml, p7m o zip)</label>
                   <div class="col-sm-8">File: <input type="file" accept=".xml,.p7m,.zip" name="userfile" />
				   </div>
               </div>
           </div>
       </div><!-- chiude row  -->
	   <div class="col-sm-12 text-right"><input name="Submit_file" type="submit" class="btn btn-warning" value="<?php echo $script_transl['btn_acquire']; ?>" />
	   </div>
	</div> <!-- chiude container -->
</div><!-- chiude panel -->
<?php
}
echo '</form>';
require("../../library/include/footer.php");
?>

