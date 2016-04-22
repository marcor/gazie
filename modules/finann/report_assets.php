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
        if ($(document).height() <= $(window).scrollTop() + $(window).height())
        {
            loadmore();
        }
    });

    function loadmore()
    {
        var val = document.getElementById("row_no").value;
        $.ajax({
            type: 'post',
            url: 'get_results.php',
            data: {
                getresult: val
            },
            success: function (response) {
                var content = document.getElementById("all_rows");
                content.innerHTML = content.innerHTML + response;

                // We increase the value by 10 because we limit the results by 10
                document.getElementById("row_no").value = Number(val) + 10;
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
        'ID' => 'id',
        $script_transl['descri'] => 'descri',
    );
    $linkHeaders = new linkHeaders($headers);
    $linkHeaders->output();
    ?>
    <div id="all_rows">
        <?php
        $result = gaz_dbi_dyn_query('*', $gTables['artico'], $where, $orderby, 0, 20);
        while ($row = gaz_dbi_fetch_array($result)) {
            echo '<p class="rows">' . $row["descri"] . " </p>\n";
        }
        ?>
        <input type="hidden" id="row_no" value="20">
    </div>   
</div><!-- chiude div container role main -->
</body>
</html>