<?php
/*
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
require("../../library/include/datlib.inc.php");

$admin_aziend = checkAdmin();

$codice = $_GET['codice'];

$msg = array('err' => array(), 'war' => array());
/*$today=	strtotime(date("Y-m-d H:i:s",time()));
$presente="";
$largeimg="";*/

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}


if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso
    
} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    
} else { //se e' il primo accesso per INSERT
    
}

require("../../library/include/header.php");
$script_transl = HeadMain();

?>

<form method="POST" name="form" enctype="multipart/form-data" id="add-product">
<?php
    if (!empty($form['descri'])) $form['descri'] = htmlentities($form['descri'], ENT_QUOTES);
    echo '<input type="hidden" name="ritorno" value="' . $form['ritorno'] . '" />';
    echo '<input type="hidden" name="ref_code" value="' . $form['ref_code'] . '" />';
    echo '<div class="text-center"><b>' . $script_transl['mod_this'] . ' ' . $codice . '</b></div>';  
    echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    ?>
        <div class="panel panel-default gaz-table-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
            <?php
                $where = "codice_composizione = '".$codice."'";
                $result = gaz_dbi_dyn_query('*', $gTables['distinta_base'], $where, 'id', 0, PER_PAGE);
                
                if ( gaz_dbi_num_rows($result)==0 ) {
                    echo 'non ci sono articoli';
                } else {
                    while ($row = gaz_dbi_fetch_array($result)) {
                        echo 'articolo '. $row['codice_artico_base'];
                    }
                }
            ?>
                    </div>
                </div>
            <!-- Fine modfica a mano -->				
            <div class="col-sm-12">
            <div>&nbsp;</div>
            <?php
            echo '<div class="col-sm-8 text-center"><input name="Submit" type="submit" class="btn btn-warning" value="' . strtoupper($script_transl[$toDo]) . '!" /></div>';
            ?>
            </div>
        </div> <!-- chiude container -->
    </div><!-- chiude panel -->
</form>

<?php
require("../../library/include/footer.php");
?>
