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
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$msg = array('err' => array(), 'war' => array());

if (!isset($_POST['hidden_req'])) { //al primo accesso allo script per update
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['hidden_req'] = '';
    $form['search_partner'] = '';
    $form['descri_partner'] = '';
    $form['id_partner'] = 0;
    if (isset($_GET['id_partner'])){
        $form['id_partner'] = intval($_GET['id_partner']);
        $partner = gaz_dbi_get_row($gTables['clfoco'], 'codice',  $form['id_partner']);
        $form['search_partner'] = $partner['ragso1'];
        $form['descri_partner'] = $partner['ragso1'];
    }
} else { // accessi successivi
    $form['ritorno'] = $_POST['ritorno'];
    $form['hidden_req'] = $_POST['hidden_req'];
    $form['id_partner'] = intval($_POST['id_partner']);
    $form['search_partner'] = '';
    if ($form['id_partner']>0){
       $form['search_partner'] = gaz_dbi_get_row($gTables['clfoco']." LEFT JOIN ".$gTables['anagra']." ON ".$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id", 'codice',  $form['id_partner'])['ragso1'];
    }
    // Se viene inviata la richiesta di cambio produzione
    if ($_POST['hidden_req'] == 'change_partner') {
        $form['id_partner'] = 0;
        $form['search_partner'] = '';
        $form['descri_partner'] = '';
        $form['hidden_req'] = '';
    }

    if (count($msg['err']) <= 0) { // non ci sono errori, posso procedere
    }
    
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/autocomplete'));
?>
<script>
$( function() {
    $( "#search_partner" ).autocomplete({
        source: "search.php?opt=partner",
        minLength: 2,
        html: true, // optional (jquery.ui.autocomplete.html.js required)

        // optional (if other layers overlap autocomplete list)
        open: function(event, ui) {
            $(".ui-autocomplete").css("z-index", 1000);
        },
        select: function(event, ui) {
            $("#id_partner").val(ui.item.value);
            $(this).closest("form").submit();
        }
    });
});
</script>
<form method="POST" name="form">
<input type="hidden" name="ritorno" value="<?php echo $form['ritorno']; ?>">
<input type="hidden" name="hidden_req" value="<?php echo $form['hidden_req']; ?>">
<?php
$gForm = new informForm();

if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
if (count($msg['war']) > 0) { // ho un alert
    $gForm->gazHeadMessage($msg['war'], $script_transl['war'], 'war');
}
?>
<div class="h3 text-center"><?php echo ucfirst($script_transl['title']); ?></div>
<div class="panel panel-default gaz-table-form div-bordered">
  <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="id_partner" class="col-sm-4 control-label"><?php echo $script_transl['id_partner']; ?></label>
    <?php
    $gForm->selectPartner($form['search_partner'], $form['id_partner'], $admin_aziend['mascli']);    
    ?>
                </div>
            </div>
        </div><!-- chiude row  -->
</div>
</div>
</form>
<?php
require("../../library/include/footer.php");
?>