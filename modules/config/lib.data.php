<?php

function aliivaInsert ($newValue)
{
    $table = 'aliiva';
    $columns=array('codice', 'tipiva', 'aliquo', 'fae_natura', 'descri', 'status', 'annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function aliivaUpdate ($codice, $newValue)
{
    $table = 'aliiva';
    $columns=array('codice', 'tipiva', 'aliquo', 'taxstamp', 'fae_natura', 'descri', 'status', 'annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function pagameInsert ($newValue)
{
    $table = 'pagame';
    $columns = array('codice','descri','tippag','incaut','tipdec','giodec','mesesc','messuc','giosuc','numrat','tiprat','fae_mode','id_bank','annota');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function pagameUpdate ($codice, $newValue)
{
    $table = 'pagame';
    $columns = array('codice','descri','tippag','incaut','tipdec','giodec','mesesc','messuc','giosuc','numrat','tiprat','fae_mode','id_bank','annota');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function vettoreUpdate ($codice,$newValue)
{
    $table = 'vettor';
    $columns=array('codice','ragione_sociale','indirizzo','cap','citta','provincia','partita_iva','codice_fiscale','n_albo','descri','telefo','annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function vettoreInsert ($newValue)
{
    $table = 'vettor';
    $columns=array('codice','ragione_sociale','indirizzo','cap','citta','provincia','partita_iva','codice_fiscale','n_albo','descri','telefo','annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}
?>