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

if (isset($styles) && is_array($styles)) {
    foreach ($styles as $v) {
        ?>
        <link href="../../library/theme/g7/<?php echo $v; ?>" rel="stylesheet" type="text/css" />
        <?php
    }
}
if ( $debug_active==true ) echo "<div>".d($GLOBALS, $_SERVER)."</div>";
?>

<!-- questo è contenuto in library/theme/g7/footer.php -->
<div class="navbar navbar-fixed-bottom">
    <div style="background:white;" > GAzie Version: <?php echo GAZIE_VERSION; ?> Software Open Source (lic. GPL)
        <?php echo $script_transl['business'] . " " . $script_transl['proj']; ?>
        <a target="_new" title="<?php echo $script_transl['auth']; ?>" href="http://www.devincentiis.it"> http://www.devincentiis.it</a>
    </div>
</div>
<script src="../../library/bootstrap/js/bootstrap.min.js"></script>
<script src="../../library/theme/g7/smartmenus-master/jquery.smartmenus.js" type="text/javascript"></script>
<script src="../../library/theme/g7/smartmenus-master/bootstrap/jquery.smartmenus.bootstrap.js" type="text/javascript"></script>
<script src="../../js/jquery.ui/jquery-ui.min.js"></script>
<script src="../../js/jquery.ui/datepicker-<?php echo substr($admin_aziend['lang'], 0, 2); ?>.js"></script>
<script src="../../js/custom/jquery.ui.autocomplete.html.js"></script>
<script src="../../js/custom/gz-library.js"></script>
<script src="../../js/tinymce/tinymce.min.js"></script>
<script src="../../js/custom/tinymce.js"></script>
</div><!-- chiude div container role main --></body>
</html>
