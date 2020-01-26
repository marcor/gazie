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

checkCustoms($_SERVER['REQUEST_URI']);

 function selectFrom($table, $name, $key, $val, $order, $bridge = '', $key2, $class = 'FacetSelect') { // Antonio Germani - crea un input select
        global $gTables;
		$acc='';
        $refresh = '';
        $query = 'SELECT * FROM `' . $gTables[$table] . '` '. ' ORDER BY `' . $order . '`';
		$acc .= "\t <select id=\"$name\" name=\"$name\" class=\"$class\" onchange=\"this.form.submit();\" > <option value=\"\"></option>\n";
        $result = gaz_dbi_query($query);
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if ($r[$key] == $val) {
                $selected = "selected";
            }
            $acc .= "\t\t <option value=\"" . $r[$key] . "\" $selected >";
            $acc .= substr($r[$key], 0, 28) . $bridge . substr($r[$key2], 0, 35) . "</option>\n";
        }       
        $acc .= "\t </select>\n";
		echo $acc;
    }

$admin_aziend = checkAdmin();

if (isset($_POST['erase'])) {
	unset($_POST);
}

if (isset($_POST['catmer'])){
 $form['catmer']=$_POST['catmer'];
 $cat = filter_input(INPUT_POST, 'catmer');
} else {
	$form['catmer']="";
	$cat="";
}


if (isset($_POST['order_by'])) { // controllo se vengo da una richiesta di ordinamento
    $rn = 0;
    $ob = filter_input(INPUT_POST, 'order_by');
    $so = filter_input(INPUT_POST, 'sort');
    $cs = filter_input(INPUT_POST, 'cosear');
} else {
    $rn = '0';
    $ob = 'last_modified';
    $so = 'DESC';
    $cs = '';
}
require("../../library/include/header.php");
?>
<script type="text/javascript">
    $(window).scroll(function ()
    {
        if ($(document).height() <= $(window).scrollTop() + $(window).height() +20 ) {
            loadmore();
        }
    });
    $(window).load(function () {
        loadmore();
    });
                                   
    function loadmore()
    {
        var rn = $("#row_no").val();
        var ob = $("#order_by").val();
        var so = $("#sort").val();
        var ca = '<?php echo $cs ?>';
        var cat = '<?php echo $cat ?>';
        $.ajax({
            type: 'post',
            url: 'report_artico_scroll.php',
            data: {
                rowno: rn,
                orderby: ob,
                sort: so,
                codart: ca,
				catmer: cat,
            },
            beforeSend: function () {
                $('#loader-icon').show();
            },
            complete: function () {
                $('#loader-icon').hide();
            },
            success: function (response) {
                $("#all_rows").append(response); //append received data into the element
                $("#row_no").val(Number(rn) + <?php echo PER_PAGE; ?>);
                $('.gazie-tooltip').tooltip(
                        {html: true,
                            placement: 'auto bottom',
                            delay: {show: 50},
                            title: function () {
                                return '<span>' + this.getAttribute('data-label') + '</span><img src="../root/view.php?table=artico&value=' + this.getAttribute('data-id') + '" onerror="this.src=\'../../library/images/link_break.png\'" alt="' + this.getAttribute('data-label') + '"  style="max-height:150px;" />';
                            }
                        });
            }
        });
    }

    $(function () {
        $('.orby').click(function () {
            var v = $(this).attr('data-order');
            var actual_ob = $("#order_by").val();
            var actual_so = $("#sort").val();
            if (v === actual_ob) { // è la stessa colonna inverto l'ordine
                if (actual_so === 'ASC') {
                    $("#sort").val('DESC');
                } else {
                    $("#sort").val('ASC');
                }
            } else { // una colonna diversa la cambio 
                $("#order_by").val(v);
            }
            $("#row_no").val(0); // quando richiedo un nuovo ordinamento devo necessariamente ricominciare da zero
            $("#form").submit();
        });
    });

</script>
<?php
$script_transl = HeadMain(0, array('custom/autocomplete'));
$gForm = new magazzForm();
?>
<form method="POST" name="myform" id="form">
    <div class="text-center"><b><?php echo $script_transl['title']; ?></b></div>
    <div class="panel panel-info col-lg-8">
        <div class="container-fluid">
        <label for="codice" class="col-lg-3 control-label"><?php echo $script_transl['codice'].'-'.$script_transl['descri']; ?></label>
		<?php
		echo ' <input type="text" class="col-lg-3" name="cosear" id="search_cosear" value="' .substr($cs, 0, 20) . '"  maxlength="16" />';
		if (strlen($cs)<1){// Antonio Germani - Se non ? stata richiesta la ricerca per singolo articolo creo un select categoria merceologica
			echo '<label class="col-lg-2 control-label">'.$script_transl['catmer'].'</label>';
			selectFrom('catmer', 'catmer','codice', $form['catmer'], 'codice', ' - ','descri','FacetSelect');
		}
		echo '<button type="submit" name="erase" title="Reset" class="btn btn-default"  style="border-radius= 85px; "> <i class="glyphicon glyphicon-remove-circle"></i></button>'

		?>
		</div>
    </div>
    <div class="panel panel-default">
        <div id="gaz-responsive-table"  class="container-fluid">
            <table class="table table-responsive table-striped table-condensed cf">
                <thead>
                    <tr class="bg-success">              
                        <th>
                            <a href="#" class="orby" data-order="codice">
                                <?php echo $script_transl["codice"]; ?>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="orby" data-order="descri">
                                <?php echo $script_transl["descri"]; ?>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="orby" data-order="good_or_service">
                                <?php echo $script_transl["good_or_service"]; ?>
                            </a>
                        </th>
                        <th class="text-right">
                            <a href="#" class="orby" data-order="catmer">
                                <?php echo $script_transl["catmer"]; ?>
                            </a>
                        </th>
                        <th class="text-right">
                            <?php echo $script_transl["unimis"]; ?>
                        </th>
                        <th class="text-right">
                            <a href="#" class="orby" data-order="preve1">
                                <?php echo $script_transl["preve1"]; ?>
                            </a>
                        </th>
                        <!--+ nuova colonna fornitore - DC - 02 feb 2018 -->
						<th>
							<a href="#" class="orby" data-order="clfoco">
								<?php echo $script_transl["clfoco"]; ?>
							</a>
                        </th>
                        <!--- nuova colonna fornitore -->
                        <th class="text-right">
                            <?php echo $script_transl["preacq"]; ?>
                        </th>
                        <th class="text-right">
                            <?php echo $script_transl["stock"]; ?>
                        </th>
                        <th class="text-center">
                            <a href="#" class="orby" data-order="aliiva">
                                <?php echo $script_transl["aliiva"]; ?>
                            </a>
                        </th>
						<th class="text-center">
                            <?php echo $script_transl["retention_tax"]; ?>
                        </th>
                        <th class="text-center">
                            <?php echo $script_transl["payroll_tax"]; ?>
                        </th>
                        <th class="text-center">
                            <?php echo $script_transl["barcode"]; ?>
                        </th>
						 <th class="text-center">
                            <?php echo $script_transl["lot"]; ?>
                        </th>
                        <th class="text-center">
                            <?php echo $script_transl["clone"]; ?>
                        </th>
                        <th class="text-center">
                            <?php echo $script_transl["delete"]; ?>
                        </th>
                    </tr>      
                </thead>    
                <tbody id="all_rows">
                </tbody>     
            </table>
        </div>  
    </div>
    <input type="hidden" name="row_no" id="row_no" value="<?php echo $rn; ?>">
    <input type="hidden" name="order_by" id="order_by" value="<?php echo $ob; ?>">
    <input type="hidden" name="sort" id="sort" value="<?php echo $so; ?>">
</form>
<div id="loader-icon"><img src="../../library/images/ui-anim_basic_16x16.gif" />
</div>  
<?php
require("../../library/include/footer.php");
?>