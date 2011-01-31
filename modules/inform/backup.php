<?php
/*$Id: backup.php,v 1.24 2011/01/01 11:07:40 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
checkAdmin(9);
if (!ini_get('safe_mode')){ //se me lo posso permettere...
    ini_set('memory_limit','128M');
    set_time_limit (120);
}

// Impostazione degli header per l'opozione "save as" dello standard input che verrà generato
header('Content-Type: text/x-sql; charset=utf-8');
header("Content-Disposition: attachment; filename=".$Database.date("YmdHi").'.sql');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');// per poter ripetere l'operazione di back-up più volte.
if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
} else {
    header('Pragma: no-cache');
}

echo "-- GAzie SQL Dump\n";
echo "-- version: ".$versSw."\n";
echo "-- http://gazie.sourceforge.net\n";
echo "-- Date: ".date("d-m-Y H:i:s")."\n";
echo "-- OS: ".PHP_OS."\n";
echo "-- Host: ".$_SERVER["HTTP_HOST"]."\n";
     $myvers=gaz_dbi_fetch_array(gaz_dbi_query('SELECT version();'));
echo "-- MySQL: ".$myvers[0]."\n";
echo "-- PHP: ".phpversion()."\n";
echo "-- Browser: ".$_SERVER['HTTP_USER_AGENT']."\n\n";

$query = "SHOW  TABLES from " . $Database;
//lettura delle informazioni (struttura + dati) dal database:
// ottiene tutti i nomi delle tabelle del database in uso
$result = gaz_dbi_query ($query);// ottengo le tabelle in un unico array associativo
echo "CREATE DATABASE IF NOT EXISTS $Database;\n\nUSE $Database;\n\n";
while ($a_row = gaz_dbi_fetch_array($result)) {// navigazione tra gli elementi dell'array associativo (navigazione tra ciascuna delle tabelle ottenute dalla query di cui sopra)
    list ($key , $nome_tabella) = each($a_row); // conversione di ciascun elemento dell'array associativo nelle variabili chiave e valore corrispondenti (nomi tabelle).
    // creazione della struttura della tabella corrente.
    echo "DROP TABLE IF EXISTS `".$nome_tabella."`;\n";
    createTable($nome_tabella);
    // riempimento della tabella corrente
    $field_results = gaz_dbi_query ("select * from " . $nome_tabella);
    $field_meta=gaz_dbi_get_fields_meta($field_results);
    if (gaz_dbi_num_rows($field_results)>0){
          echo "LOCK TABLES `".$nome_tabella."` WRITE;\n";
          $head_query_insert = "INSERT INTO `" . $nome_tabella . "` ( " ;
          for ($j = 0; $j < $field_meta['num']; $j++) {
              $head_query_insert .="`".$field_meta['data'][$j]->name."`,";
          }
          $head_query_insert = preg_replace("/,$/",'', $head_query_insert);// elimina l'ultima virgola dalla stringa(se esiste)
          $head_query_insert .= ") VALUES (";
          $query_insert = $head_query_insert;
          $c=0;
          while ($val = gaz_dbi_fetch_row($field_results)) {
            $c++;
            if ($c==50){ //ogni 50 righi viene riscritto l'head dell'inserimento
               $c=0;
               $query_insert = preg_replace("/,\($/",'', $query_insert).";\n\n";// elimina l'ultima virgola e parentesi dalla stringa(se esiste)
               echo $query_insert;
               $query_insert = $head_query_insert;
            }
            $first = True;
            for ($j = 0; $j < $field_meta['num']; $j++) {
              $query_insert .= ($first ? "" : ", ");
              $first = False;
              if ($field_meta['data'][$j]->blob && !empty($val[$j])) {
                $query_insert .= '0x'.bin2hex($val[$j]);
              } elseif ($field_meta['data'][$j]->numeric && $field_meta['data'][$j]->type != 'timestamp'){
                $query_insert .= $val[$j];
              } else {
                $query_insert .="'".addslashes($val[$j])."'";
              }
            }
            $first = True;
            $query_insert .= "),(";
          }
          $c=0;
          $query_insert = preg_replace("/,\($/",'', $query_insert).";\n";// elimina l'ultima virgola e parentesi dalla stringa(se esiste)
          echo $query_insert;
          echo "UNLOCK TABLES;\n\n";
    }
}
exit;

// Coded By Louis
// ############### FUNZIONI DI SUPPORTO ###############
function createTable($table)
{
    $results = gaz_dbi_query ("SHOW CREATE TABLE ".$table);
    $row = gaz_dbi_fetch_array($results);
    echo $row['Create Table'];
    echo ";\n\n";
}
?>
</table>
</form>
</body>
</html>