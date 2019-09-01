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
$rs = gaz_dbi_query("SELECT ".$gTables['admin_module'].".access FROM ".$gTables['admin_module']." LEFT JOIN ".$gTables['module']." ON ".$gTables['admin_module'].".moduleid=".$gTables['module'].".id WHERE `adminid`='".$admin_aziend['user_name']."' AND ".$gTables['module'].".name='shop-synchronize'");
$test=mysqli_fetch_array($rs)
?>
<div class="panel panel-info col-sm-12">
<?php
if ($test && $test['access']==3){ 
?>
<div class="box-header bg-info">
	<h4 class="box-title"><i class="glyphicon glyphicon-transfer"></i> SINCRONIZZAZIONE SHOP ONLINE</h4>
</div>
<div class="box-body">
	<h4><a href="../shop-synchronize/synchronize.php"> Procedi alla sincronizzazione del sito per lo shopping online <i class="glyphicon glyphicon-transfer"></i></a></h4>
</div>
<?php
} else {
?>
<div class="box-header bg-danger">
	<h3 class="box-title">LA FUNZIONE SINCRONIZZAZIONE SHOP FUNZIONA SOLO ATTIVANDO IL RELATIVO MODULO </h3>
</div>

<?php	
}
?>
</div>