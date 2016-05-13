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
            url: 'report_artico_scroll.php',
            data: {
                getresult: val
            },
            beforeSend: function () {
                $('#loader-icon').show();
            },
            complete: function () {
                $('#loader-icon').hide();
            },
            success: function (response) {
                $("#all_rows").append(response); //append received data into the element
                $("#row_no").val(Number(val) + <?php echo PER_PAGE; ?>);
                $('.gazie-tooltip').tooltip(
                        {html: true,
                            placement: 'auto bottom',
                            delay: {show: 50},
                            title: function () {
                                return '<span class="label">' + this.getAttribute('data-label') + '</span><img src="../root/view.php?table=artico&value=' + this.getAttribute('data-id') + '" onerror="this.src=\'../../library/images/link_break.png\'" alt="' + this.getAttribute('data-label') + '" />';
                            }
                        });
            }
        });
    }
</script>
<?php
$script_transl = HeadMain();
$gForm = new magazzForm();
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title']; ?></div>
<div class="panel panel-default">
    <div id="gaz-responsive-table"  class="container-fluid">
        <table class="table table-responsive table-striped table-condensed cf">
            <thead>
                <tr class="bg-success">              
                    <th>
                        <?php echo $script_transl["codice"]; ?>
                    </th>
                    <th>
                        <?php echo $script_transl["descri"]; ?>
                    </th>
                    <th>
                        <?php echo $script_transl["good_or_service"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["catmer"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["unimis"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["preve1"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["preacq"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["stock"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["aliiva"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["retention_tax"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["payroll_tax"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["barcode"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["clone"]; ?>
                    </th>
                    <th class="text-right">
                        <?php echo $script_transl["delete"]; ?>
                    </th>
                </tr>      
            </thead>    
            <tbody id="all_rows">
            </tbody>     
        </table>
    </div>  
</div>
<input type="hidden" id="row_no" value="0">
<div id="loader-icon"><img src="../../library/images/ui-anim_basic_16x16.gif" />
</div>  
</div><!-- chiude div container role main -->
</body>
</html>