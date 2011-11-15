<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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
//
// Verifica i parametri della chiamata.
//
if (isset($_POST["do_backup"])) {
	$create_database=$_POST["create_database"];
	$use_database=$_POST["use_database"];
	$table_selection=$_POST["table_selection"];
	$do_backup=$_POST["do_backup"];
} else {
	$create_database='';
	$use_database='';
	$table_selection='';
	$do_backup=0;
}

if ($do_backup != 1)
  {
    //
    // Mostra il modulo form e poi termina la visualizzazione.
    //
    require("../../library/include/header.php");
    echo "</head>\n";
    echo "<body>\n";
    echo "<form action=\"backup.php\" method=\"post\">";
    echo "<p><strong>Aggiunge le istruzioni seguenti:</strong></p>";
    echo "<p><input type=\"checkbox\" name=\"create_database\" value=\"1\" checked=\"checked\"> CREATE DATABASE IF NOT EXISTS $Database;</p>";
    echo "<p><input type=\"checkbox\" name=\"use_database\" value=\"1\" checked=\"checked\"> USE $Database;</p>";
    echo "<hr>";
    echo "<p><strong>Backup di:</strong></p>";
    echo "<input type=\"radio\" name=\"table_selection\" value=\"1\" checked=\"checked\"> le sole tabelle con prefisso \"$table_prefix\"</p>";
    echo "<input type=\"radio\" name=\"table_selection\" value=\"0\"> tutte le tabelle della base di dati \"$Database\"</p>";
    echo "<hr>";
    //
    echo "<input type=\"hidden\" name=\"do_backup\" value=\"1\">";
    echo "<p><input type=\"submit\" name=\"submit\" value=\"genera il file di backup\"></p>";
    echo "</form>";
    echo "</body>";
    echo "</html>";
  }
else
  {
    //
    // Esegue il backup.
    //
    // Impostazione degli header per l'opozione "save as" dello standard input che verra` generato
    header('Content-Type: text/x-sql; charset=utf-8');
    header("Content-Disposition: attachment; filename=".$Database.date("YmdHi").'.sql');
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');// per poter ripetere l'operazione di back-up pi— volte.
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Pragma: no-cache');
    }
    echo "-- GAzie SQL Dump\n";
    echo "-- version: ".$versSw."\n";
    echo "-- http://gazie.altervista.org\n";
    echo "-- Date: ".date("d-m-Y H:i:s")."\n";
    echo "-- OS: ".PHP_OS."\n";
    echo "-- Host: ".$_SERVER["HTTP_HOST"]."\n";
         $myvers=gaz_dbi_fetch_array(gaz_dbi_query('SELECT version();'));
    echo "-- MySQL: ".$myvers[0]."\n";
    echo "-- PHP: ".phpversion()."\n";
    echo "-- Browser: ".$_SERVER['HTTP_USER_AGENT']."\n";
    echo "--\n";
    echo "-- Opzioni: create_database=$create_database\n";
    echo "--          use_database=$use_database\n";
    echo "--          table_selection=$table_selection\n";
    echo "--\n";
    echo "--\n";
    echo "-- ATTENZIONE: la codifica di questo file dovrebbe essere UTF-8;\n";
    echo "--             tuttavia, puo` darsi che il risultato che si ottiene\n";
    echo "--             sia codificato in modo ®anomalo¯. Prima di utilizzare\n";
    echo "--             questo file, e` necessario controllare che le lettere\n";
    echo "--             accentate siano visualizzate correttamete. In caso\n";
    echo "--             contrario si puo` tentare di convertirlo con un programma\n";
    echo "--             come `recode' che si trova normalmente in un sistema\n";
    echo "--             GNU (ma prima di usarlo occorre fare una copia di sicurezza del file):\n";
    echo "--\n";
    echo "--             $ recode latin..utf8 file.sql\n";
    echo "--\n";
    echo "--             oppure, se non funziona:\n";
    echo "--\n";
    echo "--             $ recode utf8..latin1 file.sql\n";
    echo "--\n";
    echo "\n";
    //
    // Si imposta la codifica interna a UTF-8.
    //
    mb_internal_encoding ("UTF-8");
    //
    //
    //
    $query = "SHOW  TABLES from " . $Database;
    //lettura delle informazioni (struttura + dati) dal database:
    // ottiene tutti i nomi delle tabelle del database in uso
    $result = gaz_dbi_query ($query);// ottengo le tabelle in un unico array associativo
    //
    echo "\n";
    echo "-- Le istruzioni seguenti consentono di ricreare la base di dati\n";
    echo "-- originaria e di selezionarla prima di procedere con il recupero\n";
    echo "-- delle tabelle.\n";
    echo "--\n";
    if ($create_database == 1)
      {
        echo "CREATE DATABASE IF NOT EXISTS $Database;\n";
      }
    else
      {
        echo "-- CREATE DATABASE IF NOT EXISTS $Database;\n";
      }
    if ($use_database == 1)
      {
        echo "USE $Database;\n";
      }
    else
      {
        echo "-- USE $Database;\n";
      }
    echo "\n";
    echo "\n";
    echo "\n";
    //
    while ($a_row = gaz_dbi_fetch_array($result)) {// navigazione tra gli elementi dell'array associativo (navigazione tra ciascuna delle tabelle ottenute dalla query di cui sopra)
        list ($key , $nome_tabella) = each($a_row); // conversione di ciascun elemento dell'array associativo nelle variabili chiave e valore corrispondenti (nomi tabelle).
        //
        // Verifica che si tratti di una tabella del gruppo appartenente a questa gestione di Gazie.
        //
        if (preg_match ("/^" . $table_prefix . "_/", $nome_tabella))
          {
            //
            // Ok.
            //
            ;
          }
        else
          {
            //
            // Il prefisso del nome della tabella non coincide: si salta se sono state richieste
            // solo le tabelle della gestione in corso.
            //
            if ($table_selection == 1)
              {
                continue;
              }
          }
        //
        // creazione della struttura della tabella corrente.
        //
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
              // elimina l'ultima virgola dalla stringa (se esiste)
              $head_query_insert = preg_replace("/,$/",'', $head_query_insert);
              //
              $head_query_insert .= ") VALUES (";
              $query_insert = $head_query_insert;
              $c=0;
              while ($val = gaz_dbi_fetch_row($field_results)) {
                $c++;
                if ($c==50){ //ogni 50 righi viene riscritto l'head dell'inserimento
                   $c=0;
                   // elimina l'ultima virgola e parentesi dalla stringa (se esiste)
                   $query_insert = preg_replace("/,\($/",'', $query_insert).";\n\n";
                   //
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
                    //
                    // Premesso che inizialmente Š stata inserita l'istruzione
                    // mb_internal_encoding ("UTF-8"), si ottiene un file
                    // codificato correttamente convertendo i campi con utf8_decode(),
                    // anche se non ho capito il perch‚.
                    //
                    $query_insert .="'".addslashes(utf8_decode($val[$j]))."'";
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