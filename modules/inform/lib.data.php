<?php
function letterInsert ($newValue)
{
    $table = 'letter';
    $columns = array( 'write_date','numero','revision','clfoco','tipo','c_a','oggetto','corpo','signature','note','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function letterUpdate ($codice, $newValue)
{
    $table = 'letter';
    $columns = array( 'write_date','numero','revision','clfoco','tipo','c_a','oggetto','corpo','signature','note','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

?>