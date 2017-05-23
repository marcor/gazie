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
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();

$msg = array('err' => array(), 'war' => array());

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and ! isset($_GET['id_tes']))) {
    header("Location: " . $form['ritorno']);
    exit;
}

if (count($msg['err']) < 1) { // nessun errore
    if ($toDo == 'update') { // e' una modifica
        tesdocUpdate(array('id_tes', $form['id_tes']), $form);
        header("Location: " . $form['ritorno']);
        exit;
    } else { // e' un'inserimento
        header("Location: invsta_docven.php");
        exit;
    }
}

require("../../library/include/header.php");
$script_transl = HeadMain();
$gForm = new contabForm();
if (count($msg['err']) > 0) { // ho un errore
    $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
}
?>

<form method="POST" name="form" enctype="multipart/form-data">
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno">
    <div class="text-center"><b><?php echo $script_transl['title']; ?></b></div>
    <div class="panel panel-default gaz-table-form">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="col-sm-8">
                        </div>
                    </div>
                </div>
            </div><!-- chiude row  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                    </div>
                </div>
            </div><!-- chiude row  -->
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
</form>
<?php
require("../../library/include/footer.php");
?>