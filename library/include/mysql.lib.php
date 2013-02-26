<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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
    scriva   alla   Free  Software Foundation,  Inc.,   675  Mass Ave,
    Cambridge, MA 02139, Stati Uniti.
 --------------------------------------------------------------------------
*/

function connectToDB ()
{
    global $link, $Host, $Database, $User, $Password;
    $count;
    //
    // Salva il valore precedente di error_reporting().
    //
    $level = error_reporting();
    //
    // Tenta per almeno cinque volte di eseguire la connessione.
    //
    for ($count = 5; $count > 0; $count--)
      {
        //
        // Per i primi quattro tentativi non mostra gli avvertimenti.
        //
        if ($count > 1)
          error_reporting (E_ERROR | E_PARSE | E_NOTICE);
        $link = mysql_connect($Host, $User, $Password);
        //
        // Riprisinta la modalitÃ  precedente di segnalazione degli
        // errori.
        //
        error_reporting ($level);
        //
        // Se non Ã¨ riuscito a connettersi, attende un po' e poi riprova;
        // altrimenti esce.
        //
        if (!$link)
          sleep (rand (3, 7));
        else
          break;
      }
    mysql_set_charset('utf8');
    if (!$link) die (" Can't connect to MySQL<br />Impossibile connettersi a MySql <br />No se puede conectar a MySQL");
    mysql_select_db($Database,$link) or die (
             "Was not found, << $Database >>  database! <br />
             Could not be installed, try to do so by <a href=\"../../setup/install/install.php\"> clicking HERE! </a><br />
             <br />Non &egrave; stata trovata la base dati di nome << $Database >>! <br />
             Potrebbe non essere stato installata, prova a farlo <a href=\"../../setup/install/install.php\"> cliccando QUI! </a> <br />
             <br />No se ha encontrado, la base de datos << $Database >>  ! <br />
             No pudo ser instalado, trate de hacerlo haciendo <a href=\"../../setup/install/install.php\">  clic AQU&Iacute;! </a>");
}

function connectIsOk()
{
    global $Host, $User, $Password, $link;
    $result = True;
    $link = mysql_connect($Host, $User, $Password) or ($result = False); // In $result l'esito della connessione
    return $result;
}

function createDatabase($Database)
{
    global $link;
    mysql_query("CREATE DATABASE $Database DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;",$link) or die ("ERRORE: il database $Database non &egrave; stato creato!");
}

function databaseIsOk()
{
    global $link, $Database;
    $result = True;
    mysql_select_db( $Database, $link) or ($result = False); // In $result l'esito della selezione
    return $result;
}

function gaz_dbi_query ($query,$ar=false)
{
    global $link;
    $result = mysql_query($query, $link);
    if (!$result) die ("Error in gaz_dbi_query:".$query.mysql_error());
    if ($ar){
        return mysql_affected_rows();
    } else {
        return $result;
    }
}

function gaz_dbi_fetch_array ($resource)
{
    $result = mysql_fetch_array($resource);
    return $result;
}

function gaz_dbi_fetch_row ($resource)
{
    $result = mysql_fetch_row($resource);
    return $result;
}

function gaz_dbi_num_rows ($resource)
{
    $result = mysql_num_rows($resource);
    return $result;
}

function gaz_dbi_fetch_object ($resource)
{
    $result = mysql_fetch_object($resource);
    return $result;
}

function gaz_dbi_free_result ($result)
{
    mysql_free_result($result);
}

//uso un metodo simile a quello di phpMyAdmin in sql.php per controllare i tipi di campo
function gaz_dbi_get_fields_meta($result)
{
    $fields=array();
    $fields['num']=mysql_num_fields($result);
    for ($i = 0; $i < $fields['num']; $i++) {
        $fields['data'][]=mysql_fetch_field($result, $i);
    }
    return $fields;
}

function gaz_dbi_get_row( $table, $fnm, $fval)
{
    global $link;
    $result = mysql_query("SELECT * FROM $table WHERE $fnm = '$fval'", $link);
    if (!$result) die (" Error gaz_dbi_get_row: ".mysql_error());
    return mysql_fetch_array( $result);
}

function gaz_dbi_put_row ($table, $CampoCond, $ValoreCond , $Campo, $Valore)
{
    global $link;
    $field_results = gaz_dbi_query ("SELECT * FROM ".$table);
    $field_meta=gaz_dbi_get_fields_meta($field_results);
    $where=' WHERE '.$CampoCond.' = ';
    $query = "UPDATE ".$table.' SET '.$Campo.' = ';
    for ($j = 0; $j < $field_meta['num']; $j++) {
        if ($field_meta['data'][$j]->name==$Campo) {
           if ($field_meta['data'][$j]->blob && !empty($Valore)) {
              $query .= '0x'.bin2hex($Valore);
           } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
              $query .= floatval($Valore);
           } else {
              $elem = addslashes($Valore); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
              $elem = preg_replace("/\\\'/","''",$elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
              $query .="'".$elem."'";
           }
        }
        if ($field_meta['data'][$j]->name==$CampoCond) {
           if ($field_meta['data'][$j]->blob && !empty($ValoreCond)) {
              $where .= '0x'.bin2hex($Valore);
           } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
              $where .= floatval($ValoreCond);
           } else {
              $elem = addslashes($ValoreCond); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
              $elem = preg_replace("/\\\'/","''",$elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
              $where .="'".$elem."'";
           }
        }
    }
    $query .= $where.' LIMIT 1';
    $result = mysql_query ($query, $link);
    if (!$result ) die ("Error gaz_dbi_put_row: <b>$query</b>".mysql_error() );
    return $result;
}

function gaz_dbi_put_query ($table, $where , $Campo, $Valore)
{
    global $link;
    $result = mysql_query ("UPDATE $table SET $Campo='$Valore' WHERE $where", $link);
    if (!$result) die ($where."Error gaz_dbi_put_query: ".mysql_error() );
}

function gaz_dbi_del_row( $table, $fname, $fval)
{
    global $link;
    $result = mysql_query("DELETE FROM $table WHERE $fname = '$fval'", $link) or die (" Errore di cancellazione: ".mysql_error());
    if (!$result) die ($where."Error gaz_dbi_del_row: ".mysql_error() );
}

// restituisce l'id dell'ultimo insert
function gaz_dbi_last_id()
{
    global $link;
    $num_id = mysql_insert_id($link);
    return $num_id;
}

// restituisce il numero record di una query
function gaz_dbi_record_count($table, $where)
{
    global $link;
    $result = mysql_query("SELECT * FROM ".$table." WHERE ".$where."" ,$link);
    $count = mysql_num_rows($result);
    return $count;
}

// funzione che compone una query con i parametri: tabella, where, orderby, limit e passo (riga di inizio e n. record)
function gaz_dbi_dyn_query($select, $tabella, $where=1, $orderby=2, $limit=0, $passo=2000000)
{
    global $link, $session;
    $query = "SELECT $select FROM $tabella ";
    if ($where != '') {
        $query .= "WHERE $where ";
    }
    if ($orderby == '2') {
        $query .= "LIMIT $limit, $passo";
    } else {
        $query .= "ORDER BY $orderby LIMIT $limit, $passo";
    }
    $result = mysql_query($query, $link);
    if (!$result) die (" Errore di gaz_dbi_dyn_query:<b> $query </b> ".mysql_error());
    return $result;
}

function gaz_dbi_fields($table)
{
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * questa funzione genera un array(chiave=>valore) contenente tutte le chiavi
    * della tabella richiesta a valori nulli o 0 a secondo del tipo
    */
    global $link, $gTables;
    $acc=array();
    $field_results = gaz_dbi_query ("SELECT * FROM ".$gTables[$table]);
    $field_meta=gaz_dbi_get_fields_meta($field_results);
    for ($j = 0; $j < $field_meta['num']; $j++) {
              if ($field_meta['data'][$j]->blob && !empty($value[$field_meta['data'][$j]->name])) {
                 // i binari
                 $acc[$field_meta['data'][$j]->name] = '';
              } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
                 // i numerici
                 $acc[$field_meta['data'][$j]->name] = 0;
              } else {
                 // le stringhe di caratteri
                 $acc[$field_meta['data'][$j]->name] = '';
              }
    }
    return $acc;
}

function gaz_dbi_parse_post($table)
{
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * questa funzione genera un array(chiave=>valore) contenente le sole chiavi
    * omonime presenti in $_POST e con i valori parsati in base al tipo di colonna
    */
    global $link, $gTables;
    $acc=array();
    $field_results = gaz_dbi_query ("SELECT * FROM ".$gTables[$table]);
    $field_meta=gaz_dbi_get_fields_meta($field_results);
    for ($j = 0; $j < $field_meta['num']; $j++) {
           if (isset($_POST[$field_meta['data'][$j]->name])) {
              if ($field_meta['data'][$j]->blob && !empty($_POST[$field_meta['data'][$j]->name])) {
                 // i binari non li considero
              } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
                 // i numerici
                 $acc[$field_meta['data'][$j]->name] = floatval(preg_replace("/\,/",'.',$_POST[$field_meta['data'][$j]->name]));
              } else {
                 // le stringhe di caratteri
                 $acc[$field_meta['data'][$j]->name] = substr($_POST[$field_meta['data'][$j]->name],0,mysql_field_len($field_results,$j));
              }
           }
    }
    return $acc;
}

function gaz_dbi_table_insert($table,$value)
{
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * $value - array associativo del tipo nome_colonna=>valore con i valori da inserire
    */
    global $link, $gTables;
    $first=true;
    $auto_increment=false;
    $colName='';
    $colValue='';
    $field_results=gaz_dbi_query ("SELECT * FROM ".$gTables[$table]);
    $rs_auto_increment=gaz_dbi_query ("SHOW COLUMNS FROM ".$gTables[$table]);
    while ($ai=mysql_fetch_assoc($rs_auto_increment)){
          if ($ai['Extra']=='auto_increment'){
             $auto_increment=$ai['Field'];
          }
    }
    $field_meta=gaz_dbi_get_fields_meta($field_results);
    for ($j = 0; $j < $field_meta['num']; $j++) {
       if ($field_meta['data'][$j]->name != $auto_increment) {  // il campo auto increment non dev'essere passato
           $colName .= ($first ? $field_meta['data'][$j]->name : ', '.$field_meta['data'][$j]->name);
           $colValue .= ($first ? " " : ", ");
           $first=false;
           if (isset($value[$field_meta['data'][$j]->name])) {
              if ($field_meta['data'][$j]->blob && !empty($value[$field_meta['data'][$j]->name])) {
                 $colValue .= '0x'.bin2hex($value[$field_meta['data'][$j]->name]);
              } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
                 $colValue .= floatval($value[$field_meta['data'][$j]->name]);
              } else {
                 $elem = addslashes($value[$field_meta['data'][$j]->name]); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
                 $elem = preg_replace("/\\\'/","''",$elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
                 $colValue .="'".$elem."'";
              }
           } elseif ($field_meta['data'][$j]->name == 'adminid') { //l'adminid non lo si deve passare
              $colValue .= "'".$_SESSION['Login']."'";
           } else {
              if ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
                 $colValue .='0';
              } else {
                 $colValue .="''";
              }
           }
       }
    }
    $query = "INSERT INTO ".$gTables[$table]." ( ".$colName." ) VALUES ( ".$colValue.");";
    $result = mysql_query($query, $link);
    if (!$result) die ("Error gaz_dbi_table_insert:<b> $query </b> ".mysql_error());
}

function gaz_dbi_table_update($table,$id,$newValue)
{
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * $id - stringa con il valore del campo "codice" da aggiornare o array(0=>nome,1=>valore,2=>nuovo_valore)
    * $newValue - array associativo del tipo nome_colonna=>valore con i valori da inserire
    */
    global $link, $gTables;
    $field_results = gaz_dbi_query ("SELECT * FROM ".$gTables[$table]);
    $field_meta=gaz_dbi_get_fields_meta($field_results);
    $query = "UPDATE ".$gTables[$table].' SET ';
    $first = true;
    $quote_id="'";
    for ($j = 0; $j < $field_meta['num']; $j++) {
        if (isset($newValue[$field_meta['data'][$j]->name])) {
           $query .= ($first ? $field_meta['data'][$j]->name." = " : ", ".$field_meta['data'][$j]->name." = ");
           $first = false;
           if ($field_meta['data'][$j]->blob && !empty($newValue[$field_meta['data'][$j]->name])) {
              $query .= '0x'.bin2hex($newValue[$field_meta['data'][$j]->name]);
           } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
              $query .= floatval($newValue[$field_meta['data'][$j]->name]);
           } else {
              $elem = addslashes($newValue[$field_meta['data'][$j]->name]); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
              $elem = preg_replace("/\\\'/","''",$elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
              $query .="'".$elem."'";
           }
           //per superare lo STRICT_MODE del server non metto gli apici ai numerici
            if ((is_array($id) && $field_meta['data'][$j]->name == $id[0] && $field_meta['data'][$j]->numeric)
               || (is_string($id) && $field_meta['data'][$j]->name == 'codice' && $field_meta['data'][$j]->numeric)) {
               $quote_id='';
           }
        } elseif ($field_meta['data'][$j]->name == 'adminid') { //l'adminid non lo si deve passare
           $query .= ", adminid = '".$_SESSION['Login']."'";
        }
    }
    //   se in $id c'è un array uso il nome del campo presente all'index [0] ed il valore dell'index [1],
    //   eventualmente anche l'index [2] per il nuovo valore del codice che quindi verrà modificato
    if (is_array($id)){
        if (isset($id[2])){
            $query .= ", $id[0] = $quote_id$id[2]$quote_id";
        }
        $query .= " WHERE $id[0] = $quote_id$id[1]$quote_id";
    } else { //altrimenti uso "codice"
        $query .= " WHERE codice = $quote_id$id$quote_id";
    }
    $result = mysql_query ($query, $link);
    if (!$result) die ("Error gaz_dbi_table_update:<b> $query </b>".mysql_error() );
}

function tableInsert ($table, $columns, $newValue)
{
    global $link, $gTables;
    $first = True;
    $colName = "";
    $colValue = "";
    while(list($key,$field) = each($columns)) {
        $colName .= ($first ? $field : ','.$field);
        $colValue .= ($first ? " '" : ", '");
        $first = False;
        $colValue .= (isset($newValue[$field]) ? addslashes($newValue[$field]) : '')."'";
    }
    $query = "INSERT INTO ".$gTables[$table]." ( ".$colName." ) VALUES ( ".$colValue.")";
    $result = mysql_query($query, $link);
    if (!$result) die ("Error tableUpdate: ".mysql_error());
}

function tableUpdate ($table, $column, $codice, $newValue)
{
    global $link, $gTables;
    $first = True;
    $query = "UPDATE ".$gTables[$table].' SET';
    while(list($key,$field) = each($column)) {
        $query .= ($first ? " $field = '" : ", $field = '");
        $first = False;
        $query .= (isset($newValue[$field]) ? addslashes($newValue[$field]) : '')."'";
    }
    //   se in $codice c'è un array uso il nome del campo presente all'index [0],
    //   eventualmente anche l'index [2] per il nuovo valore del codice che quindi verrà modificato
    if (is_array($codice)){
        if (isset($codice[2])){
            $query .= ", $codice[0] = '$codice[2]'";
        }
        $query .= " WHERE $codice[0] = '$codice[1]'";
    } else { //altrimenti uso "codice"
        $query .= " WHERE codice = '$codice'";
    }
    $result = mysql_query ($query, $link);
    if (!$result) die ("Error tableUpdate: ".mysql_error() );

}

function mergeTable($table1,$campi1,$table2,$campi2,$campomerge,$where)
{
    global $link;
    $result = mysql_query("SELECT $campi1 FROM $table1 LEFT JOIN $table2 ON $table1.$campomerge = $table2.$campomerge WHERE $where", $link);
    if (!$result) die (" Error mergeTable: ".mysql_error());
    return $result;
}

function rigmoiInsert($newValue)
{
    $table = 'rigmoi';
    $columns = array('id_rig','id_tes','tipiva','codiva','periva','imponi','impost');
    tableInsert($table, $columns, $newValue);
}

function rigmocInsert($newValue)
{
    $table = 'rigmoc';
    $columns = array('id_rig','id_tes','darave','codcon','import');
    tableInsert($table, $columns, $newValue);
}

function paymovInsert($newValue)
{
    $table = 'paymov';
    $columns = array('id','id_tesdoc_ref','id_rigmoc_pay','id_rigmoc_doc','amount','expiry');
    tableInsert($table, $columns, $newValue);
}

function rigbroInsert ($newValue)
{
    $table = 'rigbro';
    $columns = array('id_tes','tiprig','codart','descri','id_body_text','unimis','quanti','prelis','sconto','codvat','pervat','codric','provvigione','ritenuta','delivery_date','id_doc','id_mag','status');
    tableInsert($table, $columns, $newValue);
}

function rigbroUpdate ($codice, $newValue)
{
    $table = 'rigbro';
    $columns = array('id_tes','tiprig','codart','descri','id_body_text','unimis','quanti','prelis','sconto','codvat','pervat','codric','provvigione','ritenuta','delivery_date','id_doc','id_mag','status');
    tableUpdate($table, $columns, $codice, $newValue);
}

function rigdocInsert ($newValue)
{
    $table = 'rigdoc';
    $columns = array('id_tes','tiprig','codart','descri','id_body_text','unimis','quanti','prelis','sconto','codvat','pervat','codric','provvigione','ritenuta','id_order','id_mag','status');
    tableInsert($table, $columns, $newValue);
}

function rigdocUpdate ($codice, $newValue)
{
    $table = 'rigdoc';
    $columns = array('id_tes','tiprig','codart','descri','id_body_text','unimis','quanti','prelis','sconto','codvat','pervat','codric','provvigione','ritenuta','id_order','id_mag','status');
    tableUpdate($table, $columns, $codice, $newValue);
}

function tesbroInsert ($newValue)
{
    $table = 'tesbro';
    $columns = array('seziva','tipdoc','template','print_total','delivery_time','day_of_validity','datemi','protoc','numdoc','numfat','datfat','clfoco','pagame','banapp','vettor','listin','destin','id_des','spediz','portos','imball','traspo','speban','spevar','round_stamp','cauven','caucon','caumag','id_agente','id_pro','sconto','vat_susp','stamp','net_weight','gross_weight','units','volume','initra','geneff','id_contract','id_con','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function tesbroUpdate ($codice, $newValue)
{
    $table = 'tesbro';
    $columns = array('seziva','tipdoc','template','print_total','delivery_time','day_of_validity','datemi','protoc','numdoc','numfat','datfat','clfoco','pagame','banapp','vettor','listin','destin','id_des','spediz','portos','imball','traspo','speban','spevar','round_stamp','cauven','caucon','caumag','id_agente','id_pro','sconto','vat_susp','stamp','net_weight','gross_weight','units','volume','initra','geneff','id_contract','id_con','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function tesdocInsert ($newValue)
{
    $table = 'tesdoc';
    $columns = array('seziva','tipdoc','template','datemi','protoc','numdoc','numfat','datfat','clfoco','pagame','banapp','vettor','listin','destin','id_des','spediz','portos','imball','traspo','speban','spevar','round_stamp','cauven','caucon','caumag','id_agente','id_pro','sconto','vat_susp','stamp','net_weight','gross_weight','units','volume','initra','geneff','id_contract','id_con','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function tesdocUpdate ($codice, $newValue)
{
    $table = 'tesdoc';
    $columns = array('seziva','tipdoc','template','datemi','protoc','numdoc','numfat','datfat','clfoco','pagame','banapp','vettor','listin','destin','id_des','spediz','portos','imball','traspo','speban','spevar','round_stamp','cauven','caucon','caumag','id_agente','id_pro','sconto','vat_susp','stamp','net_weight','gross_weight','units','volume','initra','geneff','id_contract','id_con','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function tesmovUpdate ($codice, $newValue)
{
    $table = 'tesmov';
    $columns = array( 'caucon', 'descri', 'datreg', 'seziva', 'id_doc', 'protoc', 'numdoc', 'datdoc', 'clfoco', 'regiva', 'operat', 'libgio','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

function tesmovInsert ($newValue)
{
    $table = 'tesmov';
    $columns = array( 'caucon', 'descri', 'datreg', 'seziva', 'id_doc', 'protoc', 'numdoc', 'datdoc', 'clfoco', 'regiva', 'operat', 'libgio','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function movmagInsert ($newValue)
{
    $table = 'movmag';
    $columns = array( 'caumag','operat','datreg','tipdoc','desdoc','datdoc','clfoco','scochi','id_rif','artico','quanti','prezzo','scorig','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableInsert($table, $columns, $newValue);
}

function movmagUpdate ($codice, $newValue)
{
    $table = 'movmag';
    $columns = array( 'caumag','operat','datreg','tipdoc','desdoc','datdoc','clfoco','scochi','id_rif','artico','quanti','prezzo','scorig','status','adminid');
    $newValue['adminid'] = $_SESSION['Login'];
    tableUpdate($table, $columns, $codice, $newValue);
}

//===============================================================
// Gestione Access Rights
//===============================================================
function updateAccessRights ($adminid, $moduleid, $access, $enterprise_id=1)
{
    global $gTables, $link;

    $result = mysql_query ("SELECT * FROM ".$gTables['admin_module']." WHERE adminid='".$adminid."' AND moduleid=".$moduleid.' AND enterprise_id='.$enterprise_id);
    if (gaz_dbi_num_rows ($result) < 1) {
        $query = "INSERT INTO ".$gTables['admin_module'].
                 " (adminid, enterprise_id, moduleid, access) VALUES ('".$adminid."',$enterprise_id,$moduleid,$access)";
    } else {
        $query = "UPDATE ".$gTables['admin_module'].
                 " SET access=".$access.
                 " WHERE adminid='".$adminid."' AND moduleid=".$moduleid.' AND enterprise_id='.$enterprise_id;
    }
    $result = mysql_query($query, $link) or die ("Errore di updateAccessRights ".mysql_error() );
}

function getAccessRights($userid='',$enterprise_id=1)
{
    global $gTables, $link;
    $query_co= " AND am.enterprise_id='".$enterprise_id."'";
    $ck_co=gaz_dbi_fields('admin_module');
    if (!array_key_exists('enterprise_id', $ck_co)){
      $query_co='';
    };
    if ($userid == '') {
       $query = 'SELECT module.name,module.link,module.id AS m1_id,module.access,module.weight '.
                 'FROM '.$gTables['module'].' AS module '.
                 "ORDER BY weight";
    } else {
        /* LA: 17-02-2008  */
        $query = 'SELECT am.adminid, am.access,
        m1.id AS m1_id, m1.name, m1.link, m1.icon, m1.class, m1.weight,
        m2.id AS m2_id,m2.link AS m2_link,m2.icon AS m2_icon,m2.class AS m2_class,m2.translate_key AS m2_trkey,m2.accesskey AS m2_ackey,m2.weight AS m2_weight,
        m3.id AS m3_id,m3.link AS m3_link,m3.icon AS m3_icon,m3.class AS m3_class,m3.translate_key AS m3_trkey,m3.accesskey AS m3_ackey,m3.weight AS m3_weight
        FROM '.$gTables['menu_module'].' AS m2 '.
        'LEFT JOIN '.$gTables['module'].' AS m1 ON m1.id=m2.id_module '.
        'LEFT JOIN '.$gTables['admin_module'].' AS am ON am.moduleid=m1.id '.
        'LEFT JOIN '.$gTables['menu_script'].' AS m3 ON m3.id_menu=m2.id '.
        "WHERE am.adminid='".$userid."' ".$query_co.
        "ORDER BY m1.weight,m1_id,m2.weight,m2_id,m3.weight";
    }
    $result = mysql_query($query, $link) or die("Query failed getAccessRights ".mysql_error());
    return $result;
}

function checkAccessRights($adminid,$module,$enterprise_id=0)
{
    global $gTables, $link;
    $ck_co=gaz_dbi_fields('admin_module');
    if ( $enterprise_id==0 || (!array_key_exists('enterprise_id', $ck_co)) ) {  // vengo da una vecchia versione (<4.0.12)
         $query = 'SELECT am.access FROM '.$gTables['admin_module'].' AS am'.
                  ' LEFT JOIN '.$gTables['module'].' AS module ON module.id=am.moduleid'.
                  " WHERE am.adminid='".$adminid."' AND module.name='".$module."'";
    } else {   //nuove versione >= 4.0.12
         $query = 'SELECT am.access FROM '.$gTables['admin_module'].' AS am'.
                  ' LEFT JOIN '.$gTables['module'].' AS module ON module.id=am.moduleid'.
                  " WHERE am.adminid='".$adminid."' AND module.name='".$module."' AND am.enterprise_id = $enterprise_id ";
    }
    $result = mysql_query($query, $link) or die ('Errore in query: '.$query.' Errore checkAccessRights '.mysql_error());
    if (gaz_dbi_num_rows ($result) < 1) {
        return 0;
    }
    $row = gaz_dbi_fetch_array($result);
    return $row['access'];
}

?>