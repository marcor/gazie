<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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
  scriva   alla   Free  Software Foundation,  Inc.,   675  Mass Ave,
  Cambridge, MA 02139, Stati Uniti.
  --------------------------------------------------------------------------
 */

function connectToDB() {
   global $link, $Host, $Database, $User, $Password;
   $link = @mysqli_connect($Host, $User, $Password, $Database) or die("Was not found, << $Database >>  database! <br />
             Could not be installed, try to do so by <a href=\"../../setup/install/install.php\"> clicking HERE! </a><br />
             <br />Non &egrave; stata trovata la base dati di nome << $Database >>! <br />
             Potrebbe non essere stato installata, prova a farlo <a href=\"../../setup/install/install.php\"> cliccando QUI! </a> <br />
             <br />No se ha encontrado, la base de datos << $Database >>  ! <br />
			No pudo ser instalado, trate de hacerlo haciendo <a href=\"../../setup/install/install.php\">  clic AQU&Iacute;! </a>");
   mysqli_query($link, "SET SESSION sql_mode=''");
   mysqli_set_charset($link, 'utf8');
}

function connectIsOk() {
   global $Host, $User, $Password, $link;
   $result = True;
   $link = @mysqli_connect($Host, $User, $Password) or ( $result = False); // In $result l'esito della connessione
   mysqli_options ($link, MYSQLI_INIT_COMMAND, "SET SQL_MODE = ''");
   return $result;
}

function createDatabase($Database) {
   global $link;
   mysqli_query($link, "CREATE DATABASE $Database DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;") or die("ERRORE: il database $Database non &egrave; stato creato!");
}

function databaseIsOk() {
   global $link, $Database;
   $result = True;
   mysqli_select_db($link, $Database) or ( $result = False); // In $result l'esito della selezione
   // Verifico che il database non sia vuoto (condizione che può invece verificarsi nel caso in cui un amministratore di sistema fornisca db e user senza grant CREATE)
   if ($tablesResult = mysqli_query($link, "SELECT COUNT(*) AS numTables FROM information_schema.tables WHERE table_schema = '$Database';")) {
      $numTables = mysqli_fetch_row($tablesResult);
      if ($numTables[0] == 0) {
         $result = False;
      }
   } else {
      $result = False;
   }
   return $result;
}

function gaz_dbi_query($query, $ar = false) {
   global $link;
   $result = mysqli_query($link, $query);
   if (!$result)
      die("Error in gaz_dbi_query:" . $query . mysqli_error($link));
   if ($ar) {
      return mysqli_affected_rows($link);
   } else {
      return $result;
   }
}

function gaz_dbi_fetch_array($resource) {
   $result = mysqli_fetch_array($resource);
   return $result;
}

/** ENRICO FEDELE */
/* Possiamo usare questa funzione in futuro?
 *  Ritengo che sia decisamente più i mmediata, perchè restituisce giù un array associativo
 *  e ci evita di dover creare un array apposito in cui mettere quello che ci interessa
 */
function gaz_dbi_fetch_assoc($resource) {
   $result = mysqli_fetch_assoc($resource);
   return $result;
}

function gaz_dbi_real_escape_string($resource) {
   global $link;
   $result = mysqli_real_escape_string($link, $resource);
   return $result;
}

function gaz_dbi_fetch_row($resource) {
   $result = mysqli_fetch_row($resource);
   return $result;
}

function gaz_dbi_num_rows($resource) {
   $result = mysqli_num_rows($resource);
   return $result;
}

function gaz_dbi_fetch_object($resource) {
   $result = mysqli_fetch_object($resource);
   return $result;
}

function gaz_dbi_free_result($result) {
   mysqli_free_result($result);
}

//uso un metodo simile a quello di phpMyAdmin in sql.php per controllare i tipi di campo
function gaz_dbi_get_fields_meta($result) {
   $fields = array();
   $fields['num'] = mysqli_num_fields($result);
   for ($i = 0; $i < $fields['num']; $i++) {
      $data = mysqli_fetch_field($result);
      switch ($data->type) {
         // i numerici
         case 1:
         case 2:
         case 3:
         case 4:
         case 5:
         case 8:
         case 9:
         case 16:
         case 246:
            $data->numeric = 1;
            $data->blob = 0;
            break;
         case 252:
            $data->numeric = 0;
            $data->blob = 1;
            break;
         default:
            $data->blob = 0;
            $data->numeric = 0;
            break;
      }
      $fields['data'][] = $data;
   }
   return $fields;
}

function gaz_dbi_get_row($table, $fnm, $fval) {
   global $link;
   $result = mysqli_query($link, "SELECT * FROM $table WHERE $fnm = '$fval'");
   if (!$result)
      die(" Error gaz_dbi_get_row: " . mysqli_error($link));
   return mysqli_fetch_array($result);
}

function gaz_dbi_get_single_value($table, $campo, $where) {
   global $link;
   $result = mysqli_query($link, "SELECT $campo FROM $table WHERE $where");
   if (!$result)
      die(" Error gaz_dbi_get_single_value: " . mysqli_error($link));
   $ris = mysqli_fetch_array($result, MYSQLI_NUM);
   if (count($ris) == 1) {
      return $ris[0];
   } else {
      return null;
   }
}

function gaz_dbi_put_row($table, $CampoCond, $ValoreCond, $Campo, $Valore) {
   global $link;
   $field_results = gaz_dbi_query("SELECT * FROM " . $table);
   $field_meta = gaz_dbi_get_fields_meta($field_results);
   $where = ' WHERE ' . $CampoCond . ' = ';
   $query = "UPDATE " . $table . ' SET ' . $Campo . ' = ';
   for ($j = 0; $j < $field_meta['num']; $j++) {
      if ($field_meta['data'][$j]->name == $Campo) {
         if ($field_meta['data'][$j]->blob && !empty($Valore)) {
            $query .= '0x' . bin2hex($Valore);
         } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp') {
            $query .= floatval($Valore);
         } else {
            $elem = addslashes($Valore); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
            $elem = preg_replace("/\\\'/", "''", $elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
            $query .="'" . $elem . "'";
         }
      }
      if ($field_meta['data'][$j]->name == $CampoCond) {
         if ($field_meta['data'][$j]->blob && !empty($ValoreCond)) {
            $where .= '0x' . bin2hex($Valore);
         } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp') {
            $where .= floatval($ValoreCond);
         } else {
            $elem = addslashes($ValoreCond); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
            $elem = preg_replace("/\\\'/", "''", $elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
            $where .="'" . $elem . "'";
         }
      }
   }
   $query .= $where . ' LIMIT 1';
   $result = mysqli_query($link, $query);
   if (!$result)
      die("Error gaz_dbi_put_row: <b>$query</b>" . mysqli_error($link));
   return $result;
}

function gaz_dbi_put_query($table, $where, $Campo, $Valore) {
   global $link;
   $result = mysqli_query($link, "UPDATE $table SET $Campo='$Valore' WHERE $where");
   if (!$result)
      die($where . "Error gaz_dbi_put_query: " . mysqli_error($link));
}

function gaz_dbi_del_row($table, $fname, $fval) {
   global $link;
   $result = mysqli_query($link, "DELETE FROM $table WHERE $fname = '$fval'") or die(" Errore di cancellazione: " . mysqli_error($link));
   if (!$result)
      die($where . "Error gaz_dbi_del_row: " . mysqli_error($link));
}

// restituisce l'id dell'ultimo insert
function gaz_dbi_last_id() {
   global $link;
   $num_id = mysqli_insert_id($link);
   return $num_id;
}

// restituisce il numero record di una query
function gaz_dbi_record_count($table, $where) {
   global $link;
   $sql = "SELECT * FROM " . $table . (($where != "") ? " WHERE " . $where : "");
   $result = mysqli_query($link, $sql);
   $count = mysqli_num_rows($result);
   return $count;
}

// funzione che compone una query con i parametri: tabella, where, orderby, limit e passo (riga di inizio e n. record)
function gaz_dbi_dyn_query($select, $tabella, $where = 1, $orderby = 2, $limit = 0, $passo = 2000000, $groupby = '') {
   global $link, $session;
   $query = "SELECT " . $select . " FROM " . $tabella;
   if ($where != '') {
      $query .= " WHERE $where ";
   }

   if ($groupby != '') {
      $query .= " GROUP BY $groupby ";
   }

   if ($orderby == '2') {
      $query .= " LIMIT " . $limit . ", " . $passo;
   } else {
      $query .= " ORDER BY " . $orderby . " LIMIT " . $limit . ", " . $passo;
   }
   //echo $query;
//   msgDebug($query);
   $result = mysqli_query($link, $query);
   if (!$result)
      die(" Errore di gaz_dbi_dyn_query:<strong> " . $query . " </strong> " . mysqli_error($link));
   return $result;
}

function gaz_dbi_fields($table) {
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * questa funzione genera un array(chiave=>valore) contenente tutte le chiavi
    * della tabella richiesta a valori nulli o 0 a secondo del tipo
    */
   global $link, $gTables;
   $acc = array();
   $field_results = gaz_dbi_query("SELECT * FROM " . $gTables[$table]);
   $field_meta = gaz_dbi_get_fields_meta($field_results);
   for ($j = 0; $j < $field_meta['num']; $j++) {
      switch ($field_meta['data'][$j]->type) {
         // i numerici
         case 1:
         case 2:
         case 3:
         case 4:
         case 5:
         case 8:
         case 9:
         case 16:
         case 246:
            $acc[$field_meta['data'][$j]->name] = 0;
            break;
         default:
            $acc[$field_meta['data'][$j]->name] = '';
            break;
      }
   }
   return $acc;
}

function gaz_dbi_parse_post($table) {
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * questa funzione genera un array(chiave=>valore) contenente le sole chiavi
    * omonime presenti in $_POST e con i valori parsati in base al tipo di colonna
    */
   global $link, $gTables;
   $acc = array();
   $field_results = gaz_dbi_query("SELECT * FROM " . $gTables[$table]);
   $field_meta = gaz_dbi_get_fields_meta($field_results);
   for ($j = 0; $j < $field_meta['num']; $j++) {
      $nomeCampo = $field_meta['data'][$j]->name;
      if (isset($_POST[$nomeCampo])) {
         switch ($field_meta['data'][$j]->type) {
            // i numerici
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 8:
            case 9:
            case 16:
            case 246:
               $acc[$field_meta['data'][$j]->name] = floatval(preg_replace("/\,/", '.', $_POST[$field_meta['data'][$j]->name]));
               break;
            // i binari non li considero
            case 252:
               break;
            // gli altri eventualmente li tronco
            default:
               $acc[$field_meta['data'][$j]->name] = substr($_POST[$field_meta['data'][$j]->name], 0, $field_meta['data'][$j]->length);
               break;
         }
      }
   }
   return $acc;
}

function gaz_dbi_table_insert($table, $value) {
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * $value - array associativo del tipo nome_colonna=>valore con i valori da inserire
    */
   global $link, $gTables;
   $first = true;
   $auto_increment = false;
   $colName = '';
   $colValue = '';
   $field_results = gaz_dbi_query("SELECT * FROM " . $gTables[$table]);
   $rs_auto_increment = gaz_dbi_query("SHOW COLUMNS FROM " . $gTables[$table]);
   while ($ai = mysqli_fetch_assoc($rs_auto_increment)) {
      if ($ai['Extra'] == 'auto_increment') {
         $auto_increment = $ai['Field'];
      }
   }
   $field_meta = gaz_dbi_get_fields_meta($field_results);
   for ($j = 0; $j < $field_meta['num']; $j++) {
      if ($field_meta['data'][$j]->name != $auto_increment) {  // il campo auto increment non dev'essere passato
         $colName .= ($first ? '`'.$field_meta['data'][$j]->name.'`' : ', `' . $field_meta['data'][$j]->name.'`');
         $colValue .= ($first ? " " : ", ");
         $first = false;
         if (isset($value[$field_meta['data'][$j]->name])) {
            if ($field_meta['data'][$j]->blob && !empty($value[$field_meta['data'][$j]->name])) {
               $colValue .= '0x' . bin2hex($value[$field_meta['data'][$j]->name]);
            } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp') {
               $colValue .= floatval($value[$field_meta['data'][$j]->name]);
            } else {
               $elem = addslashes($value[$field_meta['data'][$j]->name]); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
               $elem = preg_replace("/\\\'/", "''", $elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
               $colValue .="'" . $elem . "'";
            }
         } elseif ($field_meta['data'][$j]->name == 'adminid') { //l'adminid non lo si deve passare
            $colValue .= "'" . $_SESSION['Login'] . "'";
         } elseif ($field_meta['data'][$j]->name == 'last_modified') {
             $colValue .= "NULL";
         } else {
            if ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp') {
               $colValue .='0';
            } else {
               $colValue .="''";
            }
         }
      }
   }
   $query = "INSERT INTO " . $gTables[$table] . " ( " . $colName . " ) VALUES ( " . $colValue . ");";
   $result = mysqli_query($link, $query);
   if (!$result)
      die("Error gaz_dbi_table_insert:<b> $query </b> " . mysqli_error($link));
}

function gaz_dbi_table_update($table, $id, $newValue) {
   /*
    * $table - il nome della tabella all'interno dell'array $gTables
    * $id - stringa con il valore del campo "codice" da aggiornare o array(0=>nome,1=>valore,2=>nuovo_valore)
    * $newValue - array associativo del tipo nome_colonna=>valore con i valori da inserire
    */
   global $link, $gTables;
   $field_results = gaz_dbi_query("SELECT * FROM " . $gTables[$table]);
   $field_meta = gaz_dbi_get_fields_meta($field_results);
   $query = "UPDATE " . $gTables[$table] . ' SET ';
   $first = true;
   $quote_id = "'";
   for ($j = 0; $j < $field_meta['num']; $j++) {
      if (isset($newValue[$field_meta['data'][$j]->name])) {
         $query .= ($first ? '`'.$field_meta['data'][$j]->name.'`' . " = " : ", " . '`'.$field_meta['data'][$j]->name.'`' . " = ");
         $first = false;
         if ($field_meta['data'][$j]->blob && !empty($newValue[$field_meta['data'][$j]->name])) {
            $query .= '0x' . bin2hex($newValue[$field_meta['data'][$j]->name]);
         } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp') {
            $query .= floatval($newValue[$field_meta['data'][$j]->name]);
         } else {
            $elem = addslashes($newValue[$field_meta['data'][$j]->name]); // risolve il classico problema dei caratteri speciali per inserimenti in SQL
            $elem = preg_replace("/\\\'/", "''", $elem); //cambia lo backslash+singlequote con 2 singlequote come fa phpmyadmin.
            $query .="'" . $elem . "'";
         }
         //per superare lo STRICT_MODE del server non metto gli apici ai numerici
         if ((is_array($id) && $field_meta['data'][$j]->name == $id[0] && $field_meta['data'][$j]->numeric) || (is_string($id) && $field_meta['data'][$j]->name == 'codice' && $field_meta['data'][$j]->numeric)) {
            $quote_id = '';
         }
      } elseif ($field_meta['data'][$j]->name == 'adminid') { //l'adminid non lo si deve passare
         $query .= ", adminid = '" . $_SESSION['Login'] . "'";
      }
   }
   //   se in $id c'è un array uso il nome del campo presente all'index [0] ed il valore dell'index [1],
   //   eventualmente anche l'index [2] per il nuovo valore del codice che quindi verrà modificato
   if (is_array($id)) {
      if (isset($id[2])) {
         $query .= ", $id[0] = $quote_id$id[2]$quote_id";
      }
      $query .= " WHERE $id[0] = $quote_id$id[1]$quote_id";
   } else { //altrimenti uso "codice"
      $query .= " WHERE codice = $quote_id$id$quote_id";
   }
   $result = mysqli_query($link, $query);
   if (!$result)
      die("Error gaz_dbi_table_update:<b> $query </b>" . mysqli_error($link));
}

function tableInsert($table, $columns, $newValue) {
   global $link, $gTables;
   $first = True;
   $colName = "";
   $colValue = "";
   while (list($key, $field) = each($columns)) {
      $colName .= ($first ? $field : ',' . $field);
      $colValue .= ($first ? " '" : ", '");
      $first = False;
      $colValue .= (isset($newValue[$field]) ? addslashes($newValue[$field]) : '') . "'";
   }
   $query = "INSERT INTO " . $gTables[$table] . " ( " . $colName . " ) VALUES ( " . $colValue . ")";
   $result = mysqli_query($link, $query);
   if (!$result)
      die("Error tableUpdate: " . mysqli_error($link));
   return mysqli_insert_id($link);
}

function tableUpdate($table, $column, $codice, $newValue) {
   global $link, $gTables;
   $first = True;
   $query = "UPDATE " . $gTables[$table] . ' SET';
   while (list($key, $field) = each($column)) {
      $query .= ($first ? " $field = '" : ", $field = '");
      $first = False;
      $query .= (isset($newValue[$field]) ? addslashes($newValue[$field]) : '') . "'";
   }
   //   se in $codice c'è un array uso il nome del campo presente all'index [0],
   //   eventualmente anche l'index [2] per il nuovo valore del codice che quindi verrà modificato
   if (is_array($codice)) {
      if (isset($codice[2])) {
         $query .= ", $codice[0] = '$codice[2]'";
      }
      $query .= " WHERE $codice[0] = '$codice[1]'";
   } else { //altrimenti uso "codice"
      $query .= " WHERE codice = '$codice'";
   }
   $result = mysqli_query($link, $query);
   if (!$result)
      die("Error tableUpdate: " . mysqli_error($link));
}

function mergeTable($table1, $campi1, $table2, $campi2, $campomerge, $where) {
   global $link;
   $result = mysqli_query($link, "SELECT $campi1 FROM $table1 LEFT JOIN $table2 ON $table1.$campomerge = $table2.$campomerge WHERE $where");
   if (!$result)
      die(" Error mergeTable: " . mysqli_error($link));
   return $result;
}

function rigmoiInsert($newValue) {
   $table = 'rigmoi';
   $columns = array('id_rig', 'id_tes', 'tipiva', 'codiva', 'periva', 'imponi', 'impost');
   tableInsert($table, $columns, $newValue);
}

function rigmocInsert($newValue) {
   $table = 'rigmoc';
   $columns = array('id_rig', 'id_tes', 'darave', 'codcon', 'import');
   tableInsert($table, $columns, $newValue);
}

function paymovInsert($newValue) {
   $table = 'paymov';
   $columns = array('id', 'id_tesdoc_ref', 'id_rigmoc_pay', 'id_rigmoc_doc', 'amount', 'expiry');
   tableInsert($table, $columns, $newValue);
}

function paymovUpdate($id, $newValue) {
   $table = 'paymov';
   $columns = array('id', 'id_tesdoc_ref', 'id_rigmoc_pay', 'id_rigmoc_doc', 'amount', 'expiry');
   tableUpdate($table, $columns, $id, $newValue);
}

function rigbroInsert($newValue) {
   $table = 'rigbro';
   $columns = array('id_tes', 'tiprig', 'codart', 'descri', 'id_body_text', 'unimis', 'quanti', 'prelis', 'sconto', 'codvat', 'pervat', 'codric', 'provvigione', 'ritenuta', 'delivery_date', 'id_doc', 'id_mag', 'status');
   tableInsert($table, $columns, $newValue);
}

function rigbroUpdate($codice, $newValue) {
   $table = 'rigbro';
   $columns = array('id_tes', 'tiprig', 'codart', 'descri', 'id_body_text', 'unimis', 'quanti', 'prelis', 'sconto', 'codvat', 'pervat', 'codric', 'provvigione', 'ritenuta', 'delivery_date', 'id_doc', 'id_mag', 'status');
   tableUpdate($table, $columns, $codice, $newValue);
}

function rigdocInsert($newValue) {
   $table = 'rigdoc';
   $columns = array('id_tes', 'tiprig', 'codart', 'descri', 'id_body_text', 'unimis', 'quanti', 'prelis', 'sconto', 'codvat', 'pervat', 'codric', 'provvigione', 'ritenuta', 'id_order', 'id_mag', 'status');
   tableInsert($table, $columns, $newValue);
}

function rigdocUpdate($codice, $newValue) {
   $table = 'rigdoc';
   $columns = array('id_tes', 'tiprig', 'codart', 'descri', 'id_body_text', 'unimis', 'quanti', 'prelis', 'sconto', 'codvat', 'pervat', 'codric', 'provvigione', 'ritenuta', 'id_order', 'id_mag', 'status');
   tableUpdate($table, $columns, $codice, $newValue);
}

function tesbroInsert($newValue) {
   $table = 'tesbro';
   $columns = array('seziva', 'tipdoc', 'template', 'print_total', 'delivery_time', 'day_of_validity', 'datemi', 'protoc', 'numdoc', 'numfat', 'datfat',
       'clfoco', 'pagame', 'banapp', 'vettor', 'listin', 'destin', 'id_des', 'spediz', 'portos', 'imball', 'traspo', 'speban', 'spevar',
       'round_stamp', 'cauven', 'caucon', 'caumag', 'id_agente', 'id_pro', 'sconto', 'expense_vat', 'stamp', 'net_weight', 'gross_weight',
       'taxstamp', 'virtual_taxstamp', 'units', 'volume', 'initra', 'geneff', 'id_contract', 'id_con', 'status', 'adminid');
   $newValue['adminid'] = $_SESSION['Login'];
   tableInsert($table, $columns, $newValue);
}

function tesbroUpdate($codice, $newValue) {
   $table = 'tesbro';
   $columns = array('seziva', 'tipdoc', 'template', 'print_total', 'delivery_time', 'day_of_validity', 'datemi', 'protoc', 'numdoc', 'numfat', 'datfat',
       'clfoco', 'pagame', 'banapp', 'vettor', 'listin', 'destin', 'id_des', 'spediz', 'portos', 'imball', 'traspo', 'speban', 'spevar',
       'round_stamp', 'cauven', 'caucon', 'caumag', 'id_agente', 'id_pro', 'sconto', 'expense_vat', 'stamp', 'net_weight', 'gross_weight',
       'taxstamp', 'virtual_taxstamp', 'units', 'volume', 'initra', 'geneff', 'id_contract', 'id_con', 'status', 'adminid');
   $newValue['adminid'] = $_SESSION['Login'];
   tableUpdate($table, $columns, $codice, $newValue);
}

function tesdocInsert($newValue) {
   $table = 'tesdoc';
   $columns = array('seziva', 'tipdoc', 'ddt_type', 'id_doc_ritorno', 'template', 'datemi', 'protoc', 'numdoc', 'numfat', 'datfat', 'clfoco', 'pagame', 'banapp', 'vettor', 'listin',
       'destin', 'id_des', 'spediz', 'portos', 'imball', 'traspo', 'speban', 'spevar', 'round_stamp', 'cauven', 'caucon', 'caumag',
       'id_agente', 'id_pro', 'sconto', 'expense_vat', 'stamp', 'net_weight', 'gross_weight', 'units', 'volume', 'initra', 'geneff',
       'taxstamp', 'virtual_taxstamp', 'id_contract', 'id_con', 'status', 'adminid',
       /** inizio modifica FP 19/10/2015 */
       'ragbol', 'data_ordine'
           /** fine modifica FP */           );
   $newValue['adminid'] = $_SESSION['Login'];
   tableInsert($table, $columns, $newValue);
}

function tesdocUpdate($codice, $newValue) {
   $table = 'tesdoc';
   $columns = array('seziva', 'tipdoc', 'ddt_type', 'id_doc_ritorno', 'template', 'datemi', 'protoc', 'numdoc', 'numfat', 'datfat', 'clfoco', 'pagame', 'banapp', 'vettor', 'listin',
       'destin', 'id_des', 'spediz', 'portos', 'imball', 'traspo', 'speban', 'spevar', 'round_stamp', 'cauven', 'caucon', 'caumag',
       'id_agente', 'id_pro', 'sconto', 'expense_vat', 'stamp', 'net_weight', 'gross_weight', 'units', 'volume', 'initra', 'geneff',
       'taxstamp', 'virtual_taxstamp', 'id_contract', 'id_con', 'status', 'adminid',
       /** inizio modifica FP 19/10/2015 */
       'ragbol', 'data_ordine'
           /** fine modifica FP */
   );
   $newValue['adminid'] = $_SESSION['Login'];
   tableUpdate($table, $columns, $codice, $newValue);
}

function tesmovUpdate($codice, $newValue) {
   $table = 'tesmov';
   $columns = array('caucon', 'descri', 'datreg', 'seziva', 'id_doc', 'protoc', 'numdoc', 'datdoc', 'clfoco', 'regiva', 'operat', 'libgio', 'adminid');
   $newValue['adminid'] = $_SESSION['Login'];
   tableUpdate($table, $columns, $codice, $newValue);
}

function tesmovInsert($newValue) {
   $table = 'tesmov';
   $columns = array('caucon', 'descri', 'datreg', 'seziva', 'id_doc', 'protoc', 'numdoc', 'datdoc', 'clfoco', 'regiva', 'operat', 'libgio', 'adminid');
   $newValue['adminid'] = $_SESSION['Login'];
   tableInsert($table, $columns, $newValue);
}

function movmagInsert($newValue) {
   $table = 'movmag';
   $columns = array('caumag', 'operat', 'datreg', 'tipdoc', 'desdoc', 'datdoc', 'clfoco', 'scochi', 'id_rif', 'artico', 'id_lotmag', 'quanti', 'prezzo', 'scorig', 'status', 'adminid');
   $newValue['adminid'] = $_SESSION['Login'];
   tableInsert($table, $columns, $newValue);
}

function movmagUpdate($codice, $newValue) {
   $table = 'movmag';
   $columns = array('caumag', 'operat', 'datreg', 'tipdoc', 'desdoc', 'datdoc', 'clfoco', 'scochi', 'id_rif', 'artico', 'id_lotmag', 'quanti', 'prezzo', 'scorig', 'status', 'adminid');
   $newValue['adminid'] = $_SESSION['Login'];
   tableUpdate($table, $columns, $codice, $newValue);
}

//===============================================================
// Gestione Access Rights
//===============================================================
function updateAccessRights($adminid, $moduleid, $access, $company_id = 1) {
   global $gTables, $link;

   $result = mysqli_query($link, "SELECT * FROM " . $gTables['admin_module'] . " WHERE adminid='" . $adminid . "' AND moduleid=" . $moduleid . ' AND company_id=' . $company_id);
   if (gaz_dbi_num_rows($result) < 1) {
      $query = "INSERT INTO " . $gTables['admin_module'] .
              " (adminid, company_id, moduleid, access) VALUES ('" . $adminid . "',$company_id,$moduleid,$access)";
   } else {
      $query = "UPDATE " . $gTables['admin_module'] .
              " SET access=" . $access .
              " WHERE adminid='" . $adminid . "' AND moduleid=" . $moduleid . ' AND company_id=' . $company_id;
   }
   $result = mysqli_query($link, $query) or die("Errore di updateAccessRights " . mysqli_error($link));
}

function getAccessRights($userid = '', $company_id = 1) {
   global $gTables, $link;
   $query_co = " AND am.company_id='" . $company_id . "'";
   $ck_co = gaz_dbi_fields('admin_module');
   if (!array_key_exists('company_id', $ck_co)) {
      $query_co = '';
   };
   if ($userid == '') {
      $query = 'SELECT  module.name,
	  					module.link,
						module.id AS m1_id,
						module.access,
						module.weight 
				FROM  ' . $gTables['module'] . ' AS module 
				ORDER BY weight';
   } else {
      /* LA: 17-02-2008  */
      $query = 'SELECT am.adminid, 
	  				   am.access, 
					   m1.id AS m1_id, 
					   m1.name,
					   m1.link, 
					   m1.icon, 
                                           m1.access as m1_ackey,
					   m1.class, 
					   m1.weight,
					   m2.id AS m2_id,
					   m2.link AS m2_link,
					   m2.icon AS m2_icon,
					   m2.class AS m2_class,
					   m2.translate_key AS m2_trkey,
					   m2.accesskey AS m2_ackey,
					   m2.weight AS m2_weight,
					   m3.id AS m3_id,
					   m3.link AS m3_link,
					   m3.icon AS m3_icon,
					   m3.class AS m3_class,
					   m3.translate_key AS m3_trkey,
					   m3.accesskey AS m3_ackey,
					   m3.weight AS m3_weight 
				FROM ' . $gTables['menu_module'] . '       AS m2 
				LEFT JOIN ' . $gTables['module'] . '       AS m1 ON m1.id      = m2.id_module 
				LEFT JOIN ' . $gTables['admin_module'] . ' AS am ON am.moduleid= m1.id 
				LEFT JOIN ' . $gTables['menu_script'] . '  AS m3 ON m3.id_menu = m2.id 
				WHERE am.adminid=\'' . $userid . '\' ' . $query_co . ' 
				ORDER BY m1.weight,
						 m1_id,
						 m2.weight,
						 m2_id,
						 m3.weight';
   }
   $result = mysqli_query($link, $query) or die("Query failed getAccessRights " . mysqli_error($link));
   return $result;
}

function checkAccessRights($adminid, $module, $company_id = 0) {
   global $gTables, $link;
   $ck_co = gaz_dbi_fields('admin_module');
   if ($company_id == 0 || (!array_key_exists('company_id', $ck_co))) {  // vengo da una vecchia versione (<4.0.12)
      $query = 'SELECT am.access FROM ' . $gTables['admin_module'] . ' AS am' .
              ' LEFT JOIN ' . $gTables['module'] . ' AS module ON module.id=am.moduleid' .
              " WHERE am.adminid='" . $adminid . "' AND module.name='" . $module . "'";
   } else {   //nuove versione >= 4.0.12
      $query = 'SELECT am.access FROM ' . $gTables['admin_module'] . ' AS am' .
              ' LEFT JOIN ' . $gTables['module'] . ' AS module ON module.id=am.moduleid' .
              " WHERE am.adminid='" . $adminid . "' AND module.name='" . $module . "' AND am.company_id = $company_id ";
   }
   $result = mysqli_query($link, $query) or die('Errore in query: ' . $query . ' Errore checkAccessRights ' . mysqli_error($link));
   if (gaz_dbi_num_rows($result) < 1) {
      return 0;
   }
   $row = gaz_dbi_fetch_array($result);
   return $row['access'];
}

function gaz_dbi_fetch_all($resource) {
   $result = array();
   while ($row = mysqli_fetch_assoc($resource)) {
      $result[] = $row;
   }
   return $result;
}

?>
