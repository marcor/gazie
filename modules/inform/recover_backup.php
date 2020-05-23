<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin(9);

function rcopy($src, $dst) {
    if (is_dir ( $src )) {
        if ( !file_exists($dst) ) mkdir ( $dst );
        $files = scandir ( $src );
        foreach ( $files as $file )
            if ($file != "." && $file != "..")
                rcopy ( "$src/$file", "$dst/$file" );
    } else if (file_exists ( $src ))
        copy ( $src, $dst );
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

if (isset($_POST['Return'])) {
    header("Location: report_backup.php");
}
if (isset($_POST['Recover']) && $_POST['Conferma']=="accetto") {
    $mysqlDatabaseName =$Database;
    $mysqlUserName =$User;
    $mysqlPassword =$Password;
    $mysqlHostName =$Host;

    $zip = new ZipArchive;
    $res = $zip->open(DATA_DIR.'files/backups/'.$_GET["id"]);
    if ($res === TRUE) {
        mkdir ( DATA_DIR.'files/backups/tmp' );
        $zip->extractTo(DATA_DIR.'files/backups/tmp');
        $zip->close();
        $mysqlImportFilename = rtrim($_GET["id"],".gaz");
    } else {
        echo 'Errore!';
        exit;
    }

    // importare in modalità mysql o php (false = php)
    $mysqlimport = FALSE;

    // cancello il database
    gaz_dbi_query("DROP DATABASE `".$mysqlDatabaseName."`");

    if ( $mysqlimport == TRUE ) {
        $command='mysql -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;
        $output=array();
        exec($command,$output,$worked);
        switch($worked){
            case 0:
                echo 'Import file <b>' .$mysqlImportFilename .'</b> successfully imported to database <b>' .$mysqlDatabaseName .'</b>';
                unlink (DATA_DIR."files/backups/".$_GET["id"]."/".$mysqlImportFilename);
                break;
            case 1:
                echo 'There was an error during import.';
                break;
        }
            header("Location: report_backup.php");
            exit;
    } else {
        // nome del file sql da importare
        $filename = DATA_DIR."files/backups/tmp/".$mysqlImportFilename;

        // azzerro la stringa che ospiterà la query
        $templine = '';
        // leggo il file sql
        $lines = file($filename);

        foreach ($lines as $line)
        {
            // è un commmento passo al prossimo rigo
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            // Aggiungo la linea alla query
            $templine .= $line;
            // se è presente un punto e virgola eseguo la query
            if (substr(trim($line), -1, 1) == ';')
            {
                gaz_dbi_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
                $templine = '';
            }
        }
        // cancello il file temporaneo .sql e copio la cartella (rimane comunque il file compresso .gaz)
        unlink (DATA_DIR."files/backups/tmp/".$mysqlImportFilename);
        $src = DATA_DIR."files/backups/tmp/";
        $dest = "../../";
        rcopy( $src , $dest );
        deleteDirectory ( $src );

        header("Location: report_backup.php");
        exit;
    }

    if (isset($_POST['Return'])){
        header("Location: report_backup.php");
        exit;
    }
}

// visualizzo la form di conferma importazione database
require("../../library/include/header.php");
$script_transl=HeadMain('','','report_backup');
print "<form method=\"POST\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['warning']." il database verrà eliminato e sarà sostituito con il seguente</div>\n";
print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" class=\"FacetFormTABLE\" align=\"center\">\n";
print "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['sure']."</td><td class=\"FacetDataTD\">".$_GET["id"]."</td></tr>";
print "<tr><td class=\"FacetFieldCaptionTD\">Scrivi \"accetto\"</td><td><input autocomplete=\"off\" name=\"Conferma\" value=\"\" /></td></tr>";
print "<tr></tr><td align=\"right\"><input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\"></td><td align=\"right\"><input type=\"submit\" name=\"Recover\" value=\"".strtoupper($script_transl['recover'])."!\"></td></tr>";
?>
</table>
</form>
<?php
require("../../library/include/footer.php");
?>