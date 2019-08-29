<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
function getDashFiles()
{
	$fileArr=[];
	foreach(glob('../*', GLOB_ONLYDIR) as $dir) {
	    if ($handle = opendir($dir)) {
			while ($file = readdir($handle)) {
				if(($file == ".")||($file == "..")||($file == "dash_order_update.php")) continue;
				if(!preg_match("/^dash_[A-Za-z0-9 _ .-]+\.php$/",$file)) continue; //filtro i nomi contenenti il suffisso dash e estensione .php
				$fileArr[] = str_replace('../', '', $dir).'/'.$file; // push sull'accumulatore con una stringa adatta alla colonna del DB
			}
		}
	}
	return $fileArr;
}
 
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/bootstrap-switch'));
?>
<script type="text/javascript">
    $(function () {
    });
</script>
<form class="form-horizontal">
    <div class="panel">
    </div><!-- chiude panel  -->
</form>
<?php
print_r(getDashFiles());
require("../../library/include/footer.php");
?>