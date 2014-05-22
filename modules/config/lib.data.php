<?php
function aziendInsert ($codice, $newValue)
{
    $table = 'aziend';
    $columns = array('ragso1', 'ragso2', 'image' ,'sedleg', 'legrap', 'sexper', 'datnas',
                    'luonas', 'pronas','indspe', 'capspe', 'citspe', 'prospe', 'country',
                    'telefo', 'fax', 'codfis', 'pariva', 'rea', 'e_mail', 'cod_ateco',
                    'regime', 'vat_susp', 'decimal_quantity', 'decimal_price', 'stock_eval_method',
                    'mascli', 'masfor', 'masban', 'cassa_', 'ivaacq', 'ivaven', 'iva_susp', 'ivacor',
                    'ivaera', 'impven', 'imptra', 'impimb', 'impspe', 'impvar', 'boleff', 'omaggi',
                    'impacq', 'cost_tra' ,'cost_imb' ,'cost_var' ,'latitude', 'longitude',
                    'coriba', 'cotrat', 'cocamb', 'c_ritenute', 'ritenuta', 'upgrie', 'upggio',
                    'upginv', 'upgve1', 'upgve2', 'upgve3', 'upgac1', 'upgac2', 'upgac3', 'upgco1',
                    'upgco2', 'upgco3', 'acciva', 'ricbol', 'perbol', 'ivabol', 'round_bol', 'sperib',
                    'desez1', 'desez2', 'desez3', 'fatimm', 'artsea', 'colore', 'conmag', 'ivam_t',
                    'interessi', 'alliva', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function aziendUpdate ($codice, $newValue)
{
   $table = 'aziend';
   $columns = array('ragso1', 'ragso2', 'image' ,'sedleg', 'legrap', 'sexper', 'datnas',
                    'luonas', 'pronas','indspe', 'capspe', 'citspe', 'prospe', 'country',
                    'telefo', 'fax', 'codfis', 'pariva', 'rea', 'e_mail', 'cod_ateco',
                    'regime', 'vat_susp', 'decimal_quantity', 'decimal_price', 'stock_eval_method',
                    'mascli', 'masfor', 'masban', 'cassa_', 'ivaacq', 'ivaven', 'ivacor',
                    'ivaera', 'impven', 'imptra', 'impimb', 'impspe', 'impvar', 'boleff', 'omaggi',
                    'impacq', 'cost_tra' ,'cost_imb' ,'cost_var' ,'latitude', 'longitude',
                    'coriba', 'cotrat', 'cocamb', 'c_ritenute', 'ritenuta', 'upgrie', 'upggio',
                    'upginv', 'upgve1', 'upgve2', 'upgve3', 'upgac1', 'upgac2', 'upgac3', 'upgco1',
                    'upgco2', 'upgco3', 'acciva', 'ricbol', 'perbol', 'ivabol', 'round_bol', 'sperib',
                    'desez1', 'desez2', 'desez3', 'fatimm', 'artsea', 'colore', 'conmag', 'ivam_t',
                    'interessi', 'alliva', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function aliivaInsert ($newValue)
{
    $table = 'aliiva';
    $columns=array('codice', 'tipiva', 'aliquo', 'descri', 'status', 'annota', 'adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function aliivaUpdate ($codice, $newValue)
{
    $table = 'aliiva';
    $columns=array('codice', 'tipiva', 'aliquo', 'descri', 'status', 'annota', 'adminid');
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