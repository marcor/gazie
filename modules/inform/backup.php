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

require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin(9);

if (!ini_get('safe_mode')) { //se me lo posso permettere...
    ini_set('memory_limit', '512M');
    gaz_set_time_limit(0);
}
//
// Verifica i parametri della chiamata.
//

if (isset($_POST['hidden_req'])) { // accessi successivi allo script
    $form['hidden_req'] = $_POST["hidden_req"];
    $form['ritorno'] = $_POST['ritorno'];
    $form['do_backup'] = $_POST["do_backup"];
} else {  // al primo accesso allo script
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['do_backup'] = 0;
}

if ($form['do_backup'] != 1 && isset($_GET['external'])) {
    //
    // Mostra il modulo form e poi termina la visualizzazione.
    //
    require("../../library/include/header.php");
    $script_transl = HeadMain();
    echo "<div align=\"center\" class=\"FacetFormHeaderFont\">" . $script_transl['title'];
    echo "</div>\n";
    echo "<form method=\"POST\">";
    echo "<input type=\"hidden\" name=\"do_backup\" value=\"1\">";
    echo "<input type=\"hidden\" value=\"" . $form['hidden_req'] . "\" name=\"hidden_req\" />\n";
    echo "<input type=\"hidden\" value=\"" . $form['ritorno'] . "\" name=\"ritorno\" />\n";
    echo "<table class=\"Tsmall\" align=\"center\">\n";
    echo "<tr><td colspan=\"2\"><hr></td></tr>";
    echo "<tr><td></td><td align=\"right\"><strong>" . $script_transl['sql_submit'] . "</strong></td></tr>";
    echo "<tr><td class=\"FacetFieldCaptionTD\"><input type=\"submit\" name=\"return\" value=\"" . $script_transl['return'] . "\"></td>
              <td class=\"FacetDataTD\" align=\"right\"><input type=\"submit\" id=\"preventDuplicate\" onClick=\"chkSubmit();\" name=\"submit\" value=\"&#9196;" . $script_transl['submit'] . "\"></td></tr>";
    echo "</table>\n</form>\n";
    require("../../library/include/footer.php");
} elseif (isset($_GET['internal'])){
    require("../../library/include/header.php");// posso passare l'header così ho lo spinner
    //
    // Esegue il backup.
    //
    $dump = new MySQLDump($link);
    $dump->save(DATA_DIR.'files/backups/' . $Database . '-' . date("YmdHi") . '-v' . GAZIE_VERSION . '.sql.gz');
    gaz_dbi_put_row($gTables['config'], 'variable', 'last_backup', 'cvalue', date('Y-m-d'));
    ?>
    <script type="text/javascript">
    window.location.href = '../../modules/inform/report_backup.php';
    </script>
    <?php
}else {
  if (isset($_POST['return'])) {
      header("Location: " . $form['ritorno']);
      exit;
  }
  //
  // Esegue il backup.
  //
  if (isset($_GET['external'])) {
  $dump = new MySQLDump($link);
  $dump->save(DATA_DIR.'files/backups/' . $Database . '-' . date("YmdHi") . '-v' . GAZIE_VERSION . '.sql.gz');
  gaz_dbi_put_row($gTables['config'], 'variable', 'last_backup', 'cvalue', date('Y-m-d'));
  // Impostazione degli header per l'opzione "save as"
  header("Pragma: no-cache");
  header("Expires: 0");
  header("Content-Type: application/octet-stream");
  header("Content-Length: ".filesize(DATA_DIR.'files/backups/' . $Database . '-' . date("YmdHi") . '-v' . GAZIE_VERSION . '.sql.gz'));
  header("Content-Disposition: attachment; filename=\"".$Database . '-' . date("YmdHi") . '-v' . GAZIE_VERSION . '.sql.gz'."\"");
  readfile(DATA_DIR.'files/backups/' . $Database . '-' . date("YmdHi") . '-v' . GAZIE_VERSION . '.sql.gz');
  }
}
require("../../library/include/footer.php");
?>
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
