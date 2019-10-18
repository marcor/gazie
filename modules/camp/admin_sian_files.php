<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
 // IL REGISTRO DI CAMPAGNA E' UN MODULO DI ANTONIO GERMANI - MASSIGNANO AP
// >> gestione dei file .txt di upload per il SIAN <<
require("../../library/include/datlib.inc.php");
require ("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();

require("../../library/include/header.php");
$script_transl = HeadMain();
// prendo tutti i file della cartella sian

if ($handle = opendir('../../data/files/1/sian/')){
	while ($file = readdir($handle)){
		if ($file == '.' || $file == '..') {
			continue;
		}
		$files[]=$file;
	}
	closedir($handle);
}

?>
<div class="panel panel-default gaz-table-form">
    <div class="container-fluid">
		<div align="center" class="lead"><h1>Gestione dei file creati per l'upload al SIAN</h1></div>
		  <table class="col-md-12 table-bordered table-striped table-condensed cf">
		<thead class="cf">
	<tr>
	<th class="col-md-8">Nome file</th>
	<th class="col-md-3">Giorno di creazione</th>
	<th class="col-md-1">Scarica</th>
	</tr>
	</thead>
	<tbody>
	<?php 
	foreach ($files as $file){
		$data=explode("_",$file);
		$gio = substr($data[1],6,2);
		$mes = substr($data[1],4,2);
		$ann = substr($data[1],0,4);
	?>
	<tr>
	<td data-title="Code"><?php echo $file;?></td>
	<td data-title="Company"><?php echo $gio,"-",$mes,"-",$ann;?></td>
	<td data-title="Price" class="numeric"><a href="../camp/getfilesian.php?filename=<?php echo substr($file,0,-4);?>&ext=txt&company_id=1">
<i class="glyphicon glyphicon-file" title="Scarica il file appena generato"></i>
</a></td>
	</tr>
	<?php
	}
	?>
	</tbody>
	</table>
	</div>
</div>
<?php
require("../../library/include/footer.php");
?>