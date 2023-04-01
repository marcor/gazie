<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
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
?>
<style>
	#loader {
		border: 12px solid #f3f3f3;
		border-radius: 50%;
		border-top: 12px solid #444444;
		width: 70px;
		height: 70px;
		animation: spin 1s linear infinite;
	}

	@keyframes spin {
		100% {
			transform: rotate(360deg);
		}
	}

	.center {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		margin: auto;
	}
</style>
<div id="loader" class="center"></div>
<?php
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
require("../../library/include/header.php");

if (isset($_POST['Recover']) && $_POST['Conferma']=="accetto") {
  $dump = new MySQLImport($link);

  if ($dump->load(DATA_DIR.'files/backups/'.$_GET["id"])){
    ?>
    <script type="text/javascript">
    window.location.href = '../../modules/inform/report_backup.php';
    </script>
    <?php
    exit;
  }else{
    echo 'Errore!';
    exit;
  }



    if (isset($_POST['Return'])){
        header("Location: report_backup.php");
        exit;
    }
}

// visualizzo la form di conferma importazione database
$script_transl=HeadMain('','','report_backup');
print "<form method=\"POST\">\n";
print "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['warning']." il database verrà eliminato e sarà sostituito con il seguente</div>\n";
print "<table class=\"Tmiddle table-striped\" align=\"center\">\n";
print "<tr><td class=\"FacetFieldCaptionTD\">".$script_transl['sure']."</td><td class=\"FacetDataTD\">".$_GET["id"]."</td></tr>";
print "<tr><td class=\"FacetFieldCaptionTD\">Scrivi \"accetto\"</td><td><input autocomplete=\"off\" name=\"Conferma\" value=\"\" /></td></tr>";
print "<tr></tr><td align=\"right\"><input type=\"submit\" name=\"Return\" value=\"".$script_transl['return']."\"></td><td align=\"right\"><input type=\"submit\" name=\"Recover\" value=\"".strtoupper($script_transl['recover'])."!\"></td></tr>";
?>
</table>
</form>
<script>
document.onreadystatechange = function() {
    if (document.readyState !== "complete") {
        document.querySelector("body").style.visibility = "hidden";
        document.querySelector("#loader").style.visibility = "visible";
    } else {
        document.querySelector("#loader").style.display = "none";
        document.querySelector("body").style.visibility = "visible";
    }
};
</script>
<?php
require("../../library/include/footer.php");
?>
