<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
$all="";
$where ="";

if ( !isset($ritorno) )
    $ritorno = $_SERVER['HTTP_REFERER'];

if ( !isset($codice) && isset($_GET['codice']) )
    $codice = $_GET['codice'];
else 
    $codice = "";

if ( !isset($_POST['cosear']) ) $form['cosear'] = $codice;
else $form['cosear'] = $_POST['cosear'];

$msg = array('err' => array(), 'war' => array());

if (isset($_POST['Update']) || isset($_GET['Update'])) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ( isset($_GET['Insert']) && $codice!="") {
    $form["codice_composizione"] = $codice;
    $form["codice_artico_base"] = $_GET['add'];
    $form["quantita_artico_base"] = "1";
    gaz_dbi_table_insert('distinta_base',$form);
    header("Location: ../magazz/admin_artico_compost.php?codice=".$codice );
}

if ( isset($_GET['del'])) {
    gaz_dbi_del_row($gTables['distinta_base'], 'id', $_GET['id'] );
    header("Location: ../magazz/admin_artico_compost.php?codice=".$codice );
}

if (isset($_POST['Insert']) || isset($_POST['Update'])) {   //se non e' il primo accesso

} elseif (!isset($_POST['Update']) && isset($_GET['Update'])) { //se e' il primo accesso per UPDATE
    
} else { //se e' il primo accesso per INSERT
    
}

if ( isset($_POST['submit']) && $_POST['submit']=="Salva" ) {
    $qta = $_POST['qta'];
    foreach ( $qta as $val => $v ) {
        gaz_dbi_table_update ("distinta_base", array ("0"=>"id","1"=>$val), array("quantita_artico_base"=>$v) );
    }
    header ( 'location: ../magazz/report_artico.php');
}

require("../../library/include/header.php");
$script_transl = HeadMain();

?>
<form method="POST" name="form" enctype="multipart/form-data">
<?php
    echo '<input type="hidden" name="ritorno" value="' . $ritorno . '" />';
    echo '<input type="hidden" name="' . ucfirst($toDo) . '" value="" />';
    if (count($msg['err']) > 0) { // ho un errore
        $gForm->gazHeadMessage($msg['err'], $script_transl['err'], 'err');
    }
    ?>
    <div class="container-fluid">
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo $script_transl['mod_this'] . ' '; ?></h3>
                    <?php
                    $select_artico = new selectartico("codice");
                    $select_artico->addSelected($codice);
                    $select_artico->output(substr($form['cosear'], 0, 20), ' and good_or_service = 2');
                    ?>
                </div>
                <div class="box-body">
                    <?php
                        $where2 = "codice_composizione = '".$codice."'";
                        $result = gaz_dbi_dyn_query('*', $gTables['distinta_base'], $where2, 'id', 0, PER_PAGE);
   
                        //preparo la variabile where per la prossima query
                        $where = "codice<>'".$codice."'";
                        //gaz_flt_var_assign('codice', 'v');
                        gaz_flt_var_assign('descri', 'v');
                        gaz_flt_var_assign('good_or_service', 'v');
						
						if ( isset($_POST['search']) && $_POST['search']=="Cerca" && isset($_POST['descri']) && $_POST['descri']!="" ) {
							$where .= " AND ( codice LIKE '%" . addslashes(substr($_POST['descri'], 0, 30)) . "%' OR descri LIKE '%" . addslashes(substr($_POST['descri'], 0, 30)) . "%')" ;
						}
						
						
                        if ( gaz_dbi_num_rows($result)==0 ) {
                            echo 'non ci sono articoli';
                        } else {
                            ?>
                            <table class="table table-responsive table-striped table-condensed cf">
                            <tr>
                                <th>Codice</th>
                                <th>Quantità</th>
                                <th>Remove</th>
                            </tr>
                            <?php
                            while ($row = gaz_dbi_fetch_array($result)) {
                                echo '<tr>';
                                echo '<td>'. $row['codice_artico_base'].'</td>';
                                echo '<td><input type="text" name="qta['.$row['id'].']" value="'. $row['quantita_artico_base'].'"></td>'; //onchange="this.form.submit()"
                                echo '<td><a class="btn btn-xs btn-default" href="../magazz/admin_artico_compost.php?del='.$row['codice_artico_base'].'&id='.$row['id'].'&codice='.$codice.'"><i class="glyphicon glyphicon-remove"></i></a></td>';
                                echo '</tr>';
                                
                                // se il codice è già presente nella distinta non lo visualizzo nella lista articoli
                                $where .= " and codice<>'".$row['codice_artico_base']."'";
                            }
                            echo '</table>';
                        }
                        ?>
                        <div class="form-group">
                        </div>
                    </div>
                    <div class="box-footer">
                        <input type="submit" class="btn btn-primary" name="submit" value="Salva">
                    </div>        
                </div>
            </div>

            <!--Disegno la lista articoli da inserire-->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Lista Articoli</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <table class="table table-responsive table-striped table-condensed cf">
                            <!-- Visualizzo i filtri articoli -->
                            <tr>
                                <th class="FacetFieldCaptionTD">
                                    <?php //gaz_flt_disp_int("codice", "Codice art."); ?>
                                </th>
                                <th class="FacetFieldCaptionTD">
                                    <?php 
                                    gaz_flt_disp_select("good_or_service", 'good_or_service', $gTables["artico"], $all, 'good_or_service',$script_transl['good_or_service_value']);
                                    ?>
                                </th>
                                <th class="FacetFieldCaptionTD">
                                    <?php gaz_flt_disp_int("descri", "Descrizione"); //gaz_flt_disp_select ( "clfoco", $gTables['anagra'].".ragso1", $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id', $all, $orderby, "ragso1");  ?>
                                </th>
                                
                                <th class="FacetFieldCaptionTD" colspan="1">
                                    <input class="btn btn-sm btn-default" type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;" autofocus />
                                </th>
                                <th class="FacetFieldCaptionTD" colspan="1">
                                    <input class="btn btn-sm btn-default" type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value = 1;" />
                                </th>
                            </tr>
                            <tr>
                                <th>Codice</th>
                                <th>Tipo</th>
                                <th>Descrizione</th>
                                <th>Unità misura</th>
                                <th>Prezzo</th>
                            </tr>
                            <?php
                            $result = gaz_dbi_dyn_query('*', $gTables['artico'], $where, 'codice', 0, PER_PAGE);
                            while ($row = gaz_dbi_fetch_array($result)) {
                                if ($row["good_or_service"] == 1) {
                                    $gooser_i = 'wrench';
                                } else if ($row["good_or_service"] == 0) {
                                    $gooser_i = 'shopping-cart';
                                } else if ($row["good_or_service"] == 2) {
                                    $gooser_i = 'tasks';
                                }
                            ?>
                            <tr>              
                                <td data-title="<?php echo $script_transl["codice"]; ?>">
                                    <?php
                                        if ( $row["good_or_service"] == 2 ) {
                                            echo '<a class="btn btn-xs btn-default" href="../magazz/admin_artico_compost.php?Insert&codice='.$row['codice'].'" ><i class="glyphicon glyphicon-menu-up"></i>&nbsp;'.$row['codice'].'</a>';
                                        }
                                        echo '<a class="btn btn-xs btn-default" href="../magazz/admin_artico_compost.php?Insert&add='.$row['codice'].'&codice='.$codice.'" ><i class="glyphicon glyphicon-menu-left"></i>&nbsp;'.$row['codice'].'</a>';

                                    ?>
                                </td>
                                <td data-title="<?php echo $script_transl["good_or_service"]; ?>" class="text-center">
                                    <?php //echo $ldoc; ?> &nbsp;   <i class="glyphicon glyphicon-<?php echo $gooser_i; ?>"></i> 
                                </td>
                                <td data-title="<?php echo $script_transl["descri"]; ?>">
                                    <span class="gazie-tooltip" data-type="product-thumb" data-id="<?php echo $row["codice"]; ?>" data-label="<?php echo $row['annota']; ?>"><?php echo $row["descri"]; ?></span>
                                </td>
                                <td data-title="<?php echo $script_transl["unimis"]; ?>">
                                    <?php echo $row["unimis"]; ?>
                                </td>
                                <td data-title="<?php echo $script_transl["preve1"]; ?>" class="text-right">
                                    <?php echo number_format($row["preve1"], $admin_aziend['decimal_price'], ',', '.'); ?>
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