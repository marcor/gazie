<?php

require("../../library/include/datlib.inc.php");
checkAdmin(7);
$form['description'] = "";
$form['variable'] = substr($_POST['name'], 0, 20);
$form['cvalue'] = substr($_POST['val'], 0, 5);
$form['last_modified'] = date('Y-m-d H:i:s');
$result = gaz_dbi_dyn_query("*", $gTables['config'], "variable='" . $form['variable'] . "'");
if (gaz_dbi_num_rows($result) >= 1) {
    gaz_dbi_put_row($gTables['config'], 'variable', $form['variable'], 'cvalue', $form['cvalue']);
} else {
    gaz_dbi_table_insert('config', $form);
}

?>