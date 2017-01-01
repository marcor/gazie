<?php
    require("../../library/include/datlib.inc.php");

    $form['description'] = "";
    $form['variable'] = $_POST['name'];
    $form['cvalue'] = $_POST['val'];
    $form['last_modified'] = date('Y-m-d H:i:s');
    
    $result = gaz_dbi_dyn_query("*", $gTables['config'], "variable='".$form['variable']."'");
    if ( gaz_dbi_num_rows( $result ) >= 1 ) {
        gaz_dbi_put_row($gTables['config'], 'variable', $form['variable'], 'cvalue', $form['cvalue']);
    } else {
        gaz_dbi_table_insert ( 'config', $form );
    }
?>