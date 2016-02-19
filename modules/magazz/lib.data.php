<?php
function bodytextInsert ($newValue)
{
    $table = 'body_text';
    $columns = array('table_name_ref','id_ref','body_text','lang_id');
    tableInsert($table, $columns, $newValue);
}
function bodytextUpdate ($codice, $newValue)
{
    $table = 'body_text';
    $columns = array('table_name_ref','id_ref','body_text','lang_id');
    tableUpdate($table, $columns, $codice, $newValue);
}
?>