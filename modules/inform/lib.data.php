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

function company_dataUpdate ($codice, $newValue)
{
    $table = 'company_data';
    $columns = array('description','var','data','ref');
    tableUpdate($table, $columns, $codice, $newValue);
}

function company_dataInsert ($newValue)
{
    $table = 'company_data';
    $columns = array('description','var','data','ref');
    tableInsert($table, $columns, $newValue);
}

?>