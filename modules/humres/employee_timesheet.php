<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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

function getStartToEndDate($week, $year) {
  $dto = new DateTime();
  $dto->setISODate($year, $week);
  $ret['mon'] = $dto->format('Y-m-d');
  $dto->modify('+1 days');
  $ret['tue'] = $dto->format('Y-m-d');
  $dto->modify('+1 days');
  $ret['wed'] = $dto->format('Y-m-d');
  $dto->modify('+1 days');
  $ret['thu'] = $dto->format('Y-m-d');
  $dto->modify('+1 days');
  $ret['fri'] = $dto->format('Y-m-d');
  $dto->modify('+1 days');
  $ret['sat'] = $dto->format('Y-m-d');
  $dto->modify('+1 days');
  $ret['sun'] = $dto->format('Y-m-d');
  return $ret;
}

function getWorkers($date) {
	global $gTables;
    $orderby = "id_staff ASC";
    $where = "end_date IS NULL OR end_date <= " . $date;
    $field = 'staff.id_staff, CONCAT(ana.ragso1,\' \',ana.ragso2) AS worker_descri ';
    $from = $gTables['staff'] . ' AS staff ' .
            'LEFT JOIN ' . $gTables['clfoco'] . ' AS worker ON staff.id_clfoco=worker.codice ' .
            'LEFT JOIN ' . $gTables['anagra'] . ' AS ana ON worker.id_anagra=ana.id ';
    $result = gaz_dbi_dyn_query($field, $from, $where, $orderby);
	$ret= array();
    while ($row = gaz_dbi_fetch_array($result)) {
		$ret[]=$row;
	}
	return $ret;
}

if (isset($_POST['week'])) { // accessi successivi
    $form['week'] = filter_input(INPUT_POST, 'week');
    $form['year'] = filter_input(INPUT_POST, 'year');
	if ($_POST['goto']=='next'){
		if ($form['week']<52) { // settimana successiva
			$form['week'] ++;
		} else {
			$form['year'] ++;
			$form['week'] =1;
		}
	}
	if ($_POST['goto']=='prev'){
		if ($form['week']>1) { // settimana precedente
			$form['week'] --;
		} else {
			$form['year'] --;
			$form['week'] =52;
		}
	}
} else { // al primo accesso
	$dto = new DateTime();
    $form['week'] = $dto->format("W");
    $form['year'] = $dto->format("Y");
}

$week_days = getStartToEndDate($form['week'], $form['year']);

require("../../library/include/header.php");
?>
<script type="text/javascript">
    $(function () {
        $("#prev").click(function () {
			$("#goto").val('prev');
            this.form.submit();
        });
        $("#next").click(function () {
			$("#goto").val('next');
            this.form.submit();
        });
    });
</script>
<?php
$script_transl = HeadMain();
$gForm = new humresForm();
?>
<form method="POST" id="form">
    <div class="text-center"><b><?php echo $script_transl['title']; ?></b></div>
	<div class="panel panel-info" style="max-width:650px;">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12 text-center"><button type="button" class="btn btn-xs btn-default" id="prev"><i class="glyphicon glyphicon-chevron-left"></i><?php echo ucfirst($script_transl['prev']); ?></button> <b><?php echo intval($form["week"]).'^'; ?> SETTIMANA <?php echo ' -> dal '.gaz_format_date($week_days['mon']).' al '.gaz_format_date($week_days['sun']); ?> </b> <button type="button"  class="btn btn-xs btn-default" id="next"><i class="glyphicon glyphicon-chevron-right"></i><?php echo ucfirst($script_transl['next']); ?></button></div>
			</div>
	<?php
	$workers=getWorkers($week_days['sun']);
    foreach ($workers as $k => $v) {
	?>
		<div class="row center-block">
			<div class="panel panel-default">
			<table>
                <thead>
                    <tr class="bg-success">              
                        <th class="col-xs-4">
							<?php echo $v['worker_descri']; ?>                       
						</th>
                        <th class="col-xs-1">
                            <?php echo utf8_encode(substr(strftime("%A", strtotime("01/01/2018")),0,3)). ' ' . intval(substr($week_days['mon'],8,2));  ?>
                        </th>
                        <th class="col-xs-1">
                            <?php echo utf8_encode(substr(strftime("%A", strtotime("01/02/2018")),0,3)). ' ' . intval(substr($week_days['tue'],8,2)); ?>
                        </th>
                        <th class="col-xs-1">
                            <?php echo utf8_encode(substr(strftime("%A", strtotime("01/03/2018")),0,3)). ' ' . intval(substr($week_days['wed'],8,2)); ?>
                        </th>
                        <th class="col-xs-1">
                            <?php echo utf8_encode(substr(strftime("%A", strtotime("01/04/2018")),0,3)). ' ' . intval(substr($week_days['thu'],8,2)); ?>
                        </th>
                        <th class="col-xs-1">
                            <?php echo utf8_encode(substr(strftime("%A", strtotime("01/05/2018")),0,3)). ' ' . intval(substr($week_days['fri'],8,2)); ?>
                        </th>
                        <th class="col-xs-1">
                            <?php echo utf8_encode(substr(strftime("%A", strtotime("01/06/2018")),0,3)). ' ' . intval(substr($week_days['sat'],8,2)); ?>
                        </th>
                        <th class="col-xs-1 bg-warning">
                            <?php echo utf8_encode(substr(strftime("%A", strtotime("01/07/2018")),0,3)). ' ' . intval(substr($week_days['sun'],8,2)); ?>
                        </th>
                    </tr>      
                </thead>    
                <tbody>
                    <tr>              
                        <td>
							<?php echo $script_transl['work_hou']; ?>  
                        </td>
                        <td><input class="form-control" id="ex1" maxlength="4" type="text">
                        </td>
                        <td><input class="form-control" id="ex1" maxlength="4" type="text">
                        </td>
                        <td><input class="form-control" id="ex1" maxlength="4" type="text">
                        </td>
                        <td><input class="form-control" id="ex1" maxlength="4" type="text">
                        </td>
                        <td><input class="form-control" id="ex1" maxlength="4" type="text">
                        </td>
                        <td><input class="form-control" id="ex1" maxlength="4" type="text">
                        </td>
                        <td class="bg-warning"><input class="form-control" id="ex1" maxlength="4" type="text">
                        </td>
                    </tr>      
                    <tr>              
                        <td>
							<?php echo $script_transl['work_hextra']; ?> 
                        </td>
                        <td>1
                        </td>
                        <td>2
                        </td>
                        <td>3
                        </td>
                        <td>4
                        </td>
                        <td>5
                        </td>
                        <td>6
                        </td>
                        <td class="bg-warning">7
                        </td>
                    </tr>      
                    <tr>              
                        <td>
							<?php echo $script_transl['absence_hou']; ?> 
                        </td>
                        <td>1
                        </td>
                        <td>2
                        </td>
                        <td>3
                        </td>
                        <td>4
                        </td>
                        <td>5
                        </td>
                        <td>6
                        </td>
                        <td class="bg-warning">7
                        </td>
                    </tr>      
                    <tr>              
                        <td>
							<?php echo $script_transl['absence_cau']; ?> 
                        </td>
                        <td>1
                        </td>
                        <td>2
                        </td>
                        <td>3
                        </td>
                        <td>4
                        </td>
                        <td>5
                        </td>
                        <td>6
                        </td>
                        <td class="bg-warning">7
                        </td>
                    </tr>      
                    <tr>              
                        <td>
							<?php echo $script_transl['other_cau']; ?> 
                        </td>
                        <td>1
                        </td>
                        <td>2
                        </td>
                        <td>3
                        </td>
                        <td>4
                        </td>
                        <td>5
                        </td>
                        <td>6
                        </td>
                        <td class="bg-warning">7
                        </td>
                    </tr>      
                    <tr>              
                        <td>
							<?php echo $script_transl['other_qua']; ?> 
                        </td>
                        <td>1
                        </td>
                        <td>2
                        </td>
                        <td>3
                        </td>
                        <td>4
                        </td>
                        <td>5
                        </td>
                        <td>6
                        </td>
                        <td class="bg-warning">7
                        </td>
                    </tr>      
                    <tr>              
                        <td>
							<?php echo $script_transl['note']; ?> 
                        </td>
                        <td>1
                        </td>
                        <td>2
                        </td>
                        <td>3
                        </td>
                        <td>4
                        </td>
                        <td>5
                        </td>
                        <td>6
                        </td>
                        <td class="bg-warning">7
                        </td>
                    </tr>      
				</tbody>     
            </table>
			</div><!-- chiude panel  -->
		</div>
	<?php
    }
	?>
			<div class="row">
				<div class="col-lg-4">
                <a class="btn btn-sm btn-edit">
                    <i class="glyphicon glyphicon-edit">				
					<?php
					echo $script_transl['insert'];
					?>
					</i>
                </a>
				</div>
			</div>
        </div><!-- chiude container  -->
    </div><!-- chiude panel  -->
    <input type="hidden" name="year" id="year" value="<?php echo $form["year"]; ?>">
    <input type="hidden" name="week" id="week" value="<?php echo $form["week"]; ?>">
    <input type="hidden" name="goto" id="goto">
</form>
<div id="loader-icon"><img src="../../library/images/ui-anim_basic_16x16.gif" />
</div>  
<?php
require("../../library/include/footer.php");
?>