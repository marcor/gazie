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
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
require("../../library/include/header.php");
?>
<script type="text/javascript">
    $(window).scroll(function ()
    {
        if ($(document).height() <= $(window).scrollTop() + $(window).height()) {
            loadmore();
        }
    });
    $(window).load(function () {
        loadmore();
    });
    function loadmore()
    {
        var val = document.getElementById("row_no").value;
        $.ajax({
            type: 'post',
            url: '../root/get_scroll_data.php',
            data: {
                getresult: val,
                table: 'artico'
            },
            beforeSend: function () {
                $('#loader-icon').show();
            },
            complete: function () {
                $('#loader-icon').hide();
            },
            success: function (response) {
                var content = document.getElementById("all_rows");
                content.innerHTML = content.innerHTML + response;
                document.getElementById("row_no").value = Number(val) + <?php echo PER_PAGE; ?>;
            }
        });
    }
</script>
<?php
$script_transl = HeadMain();
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title']; ?></div>
<?php
$headers = array(
    'ID' => 'codice',
    $script_transl['descri'] => 'descri',
);
$linkHeaders = new linkHeaders($headers);
$linkHeaders->output();
?>
<div id="all_rows">
</div>     
<input type="hidden" id="row_no" value="0">
<div id="loader-icon"><img src="../../library/images/ui-anim_basic_16x16.gif" />
</div>  
</div><!-- chiude div container role main -->
</body>
</html>