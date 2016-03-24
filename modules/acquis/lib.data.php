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

function lotmagInsert($newValue)
{
    $table = 'lotmag';
    $columns = array('codart','id_movmag','id_rigdoc','identifier','expiry');
    $last_id=tableInsert($table, $columns, $newValue);
    return $last_id;
}

?>