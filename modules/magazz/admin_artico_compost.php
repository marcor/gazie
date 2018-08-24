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

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ( isset($_GET['Insert']) ) {
    $form["codice_composizione"] = $codice;
    $form["codice_artico_base"] = $_GET['add'];
    $form["quantita_artico_base"] = "1";
    gaz_dbi_table_insert('distinta_base',$form);
    header("Location: ../magazz/admin_artico_compost.php?codice=".$codice );
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
    echo '<input type="hidden" name="ritorno" value="' . $form['ritorno'] . '" />';
    echo '<input type="hidden" name="ref_code" value="' . $form['ref_code'] . '" />';
    echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    ?>
    <div class="container-fluid">
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo $script_transl['mod_this'] . ' ' . $codice; ?></h3>
                </div>
                    <div class="box-body">
                    <?php
                        $where = "codice_composizione = '".$codice."'";
                        $result = gaz_dbi_dyn_query('*', $gTables['distinta_base'], $where, 'id', 0, PER_PAGE);
   
                        //preparo where per la prossima query
                        $where = " codice<>'".$codice."'";
   
                        if ( gaz_dbi_num_rows($result)==0 ) {
                            echo 'non ci sono articoli';
                        } else {
                            echo '<table class="table table-responsive table-striped table-condensed cf">';
                            while ($row = gaz_dbi_fetch_array($result)) {
                                echo '<tr>';
                                echo '<td>'. $row['codice_artico_base'].'</td>';
                                echo '<td>'. $row['quantita_artico_base'].'</td>';
                                echo '</tr>';
                                
                                $where .= " and codice<>'".$row['codice_artico_base']."'";
                            }
                            echo '</table>';
                        }
                    ?>
                        <div class="form-group">


                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Salva</button>
                    </div>
            
            </div>
            </div>

            <div class="col-md-6">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Lista Articoli</h3>
                        </div>
                        <div class="box-body">
                        <div class="form-group">
                        <table class="table table-responsive table-striped table-condensed cf">
                        <?php


$result = gaz_dbi_dyn_query('*', $gTables['artico'], $where, 'codice', 0, PER_PAGE);
while ($row = gaz_dbi_fetch_array($result)) {
    ?>
    <tr>              
        <td data-title="<?php echo $script_transl["codice"]; ?>">
            <a class="btn btn-xs btn-default" href="../magazz/admin_artico_compost.php?Insert&add=<?php echo $row['codice']; ?>&codice=<?php echo $codice;?>" ><i class="glyphicon glyphicon-edit"></i>&nbsp;<?php echo $row['codice']; ?></a>
        </td>
        <td data-title="<?php echo $script_transl["descri"]; ?>">
            <span class="gazie-tooltip" data-type="product-thumb" data-id="<?php echo $row["codice"]; ?>" data-label="<?php echo $row['annota']; ?>"><?php echo $row["descri"]; ?></span>
        </td>
        <td data-title="<?php echo $script_transl["good_or_service"]; ?>" class="text-center">
            <?php echo $ldoc; ?> &nbsp;   <i class="glyphicon glyphicon-<?php echo $gooser_i; ?>"></i> 
        </td>
        <td data-title="<?php echo $script_transl["catmer"]; ?>" class="text-center">
            <?php echo $row["catmer"]; ?>
        </td>
        <td data-title="<?php echo $script_transl["unimis"]; ?>">
            <?php echo $row["unimis"]; ?>
        </td>
        <td data-title="<?php echo $script_transl["preve1"]; ?>" class="text-right">
            <?php echo number_format($row["preve1"], $admin_aziend['decimal_price'], ',', '.'); ?>
        </td>
        <td data-title="<?php echo $script_transl["clfoco"]; ?>">
            <?php echo $rsfor['descri']; ?>
        </td>
        </tr>
            <?php } ?>         
            </table>               
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- chiude container -->
</form>
<script src="../../js/custom/autocomplete.js"></script>
<?php
require("../../library/include/footer.php");
?>