<?php
/*
	  --------------------------------------------------------------------------
	  GAzie - Gestione Azienda
	  Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
	  (http://www.devincentiis.it)
	  <http://gazie.sourceforge.net>
	  --------------------------------------------------------------------------
	 VACATION RENTAL è un modulo creato per GAzie da Antonio Germani, Massignano AP
	  Copyright (C) 2022-2023 - Antonio Germani, Massignano (AP)
	  https://www.lacasettabio.it
	  https://www.programmisitiweb.lacasettabio.it
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
	  scriva   alla   Free  Software Foundation,  Inc.,   59
	  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
	  --------------------------------------------------------------------------
	  # free to use, Author name and references must be left untouched  #
	  --------------------------------------------------------------------------
*/
require("../../modules/vacation_rental/lib.function.php");
require("../../modules/vacation_rental/lib.data.php");
if (!isset($_POST['access'])){// primo accesso
  $form['start']=date("Y-m-d");
  $form['end']=date('Y-m-d', strtotime($form['start'] . ' +90 day'));
}else{
  $form['start']=$_POST['start'];
  $form['end']=$_POST['end'];
}
?>


  <div id="generale" class="tab-pane fade in ">
    <form method="post" id="sbmt-form" enctype="multipart/form-data">
      <div class="panel panel-info col-sm-12">
        <div class="box-header bg-info">
          <h4 class="box-title"><i class="glyphicon glyphicon-blackboard"></i> Riepilogo Vacation rental</h4>
          <a class="pull-right dialog_grid" id_bread="<?php echo $grr['id_bread']; ?>" style="cursor:pointer;"><i class="glyphicon glyphicon-cog"></i></a>
        </div>
        <div class="box-body">
          <table class="Tlarge table table-striped table-bordered table-condensed">
            <tr>
              <td class="FacetFieldCaptionTD text-right">Periodo</td>
              <td class="FacetDataTD">
                dal <input type="date" name="start" value="<?php echo $form['start']; ?>" class="FacetInput" onchange="this.form.submit()">
              </td>
              <td class="FacetDataTD">
                al <input type="date" name="end" value="<?php echo $form['end']; ?>" class="FacetInput" onchange="this.form.submit()">
                <input type="hidden" value="access" maxlength="6" name="access">
              </td>
            </tr>
          </table>
          <?php
          // prendo i dati statistici
          $tot_promemo = get_total_promemo($form['start'],$form['end']);
          // prendo i check-in nei prossimi 7 giorni
          $next_check = get_next_check(date("Y-m-d"),date('Y-m-d', strtotime(date("Y-m-d") . ' + 7 day')));
          ?>
          <div class="row">
            <div class="column" style="float: left; width: 25%;">
              <table class="Tlarge table-bordered " style="width:95%;">
                <tr>
                  <th>Importo totale</th>
                </tr>
                <tr>
                  <td><?php echo "€ ",number_format($tot_promemo['totalprice_booking'], 2, '.', ''); ?></td>
                </tr>
              </table>
            </div>
            <div class="column" style="float: left; width: 25%;">
              <table class="Tlarge table-bordered " style="width:95%;">
                <tr>
                  <th>Notti periodo</th>
                </tr>
                <tr>
                  <td><?php echo $tot_promemo['tot_nights_bookable']; ?></td>
                </tr>
              </table>
            </div>
            <div class="column" style="float: left; width: 25%;">
              <table class="Tlarge table-bordered " style="width:95%;">
                <tr>
                  <th>Notti vendute</th>
                </tr>
                <tr>
                  <td><?php echo $tot_promemo['tot_nights_booked']; ?></td>
                </tr>
              </table>
            </div>
            <div class="column" style="float: left; width: 25%;">
              <table class="Tlarge table-bordered " style="width:95%;">
                <tr>
                  <th>Occupazione</th>
                </tr>
                <tr>
                  <td><?php echo number_format($tot_promemo['perc_booked'], 2, '.', ''),"%"; ?></td>
                </tr>
              </table>
            </div>
          </div>
          <div class="row">
            <table class="Tlarge table table-striped table-bordered table-condensed">
              <h5 class="box-title"><i class="glyphicon glyphicon-pushpin"></i> Nei prossimi 7 giorni </h5>
              <?php
              if (count($next_check['in']) >0){
                $keys = array_column($next_check['in'], 'start');
                array_multisort($keys, SORT_ASC, $next_check['in']);// ordino per start
                ?>

                <table class="Tlarge table table-striped table-bordered text-left">
                  <tr>
                    <th class="text-center"><i class="glyphicon glyphicon-log-in"></i>&nbsp;&nbsp;<?php echo "Check-in"; ?></th>

                  </tr>
                  <?php
                  foreach($next_check['in'] as $next_row){

                    $table = $gTables['rental_events'] ." LEFT JOIN ". $gTables['tesbro'] ." ON ". $gTables['tesbro'] .".id_tes = " . $gTables['rental_events'] . ".id_tesbro LEFT JOIN ". $gTables['clfoco'] ." ON ". $gTables['clfoco'] .".codice = " . $gTables['tesbro'] . ".clfoco LEFT JOIN ". $gTables['anagra'] ." ON ". $gTables['anagra'] .".id = " . $gTables['clfoco'] . ".id_anagra";
                    $where = $gTables['rental_events'].".id = '".$next_row['id']."'";
                    $what = $gTables['rental_events'] .".*, ". $gTables['anagra'] . ".ragso1, ".	$gTables['anagra'] .".ragso2, ". 	$gTables['tesbro'] . ".numdoc, ".	$gTables['tesbro'] . ".datemi ";
                    $result = gaz_dbi_dyn_query($what, $table, $where, "start DESC");
                    $row=gaz_dbi_fetch_array($result);
                    if (isset($row)){
                      ?>
                      <tr>
                      <td><?php echo "<b>",gaz_format_date($row['start']),"</b> ",$row['type']," ",$row['house_code'],"<b> -> </b>",$row['ragso1']," ",$row['ragso2']," prenotazione n.",$row['numdoc']," del ",gaz_format_date($row['datemi']); ?></td>
                      </tr>
                      <?php
                    }
                  }
                  ?>
                </table>

                <?php
              }
              if (count($next_check['out']) >0){
                $keys = array_column($next_check['out'], 'end');
                array_multisort($keys, SORT_ASC, $next_check['out']);// ordino per end
                ?>

                <table class="Tlarge table table-striped table-bordered text-left">
                  <tr>
                    <th class="text-center"><i class="glyphicon glyphicon-log-out"></i>&nbsp;&nbsp;<?php echo "Check-out"; ?></th>
                  </tr>
                  <?php
                  foreach($next_check['out'] as $next_row){
                    $table = $gTables['rental_events'] ." LEFT JOIN ". $gTables['tesbro'] ." ON ". $gTables['tesbro'] .".id_tes = " . $gTables['rental_events'] . ".id_tesbro LEFT JOIN ". $gTables['clfoco'] ." ON ". $gTables['clfoco'] .".codice = " . $gTables['tesbro'] . ".clfoco LEFT JOIN ". $gTables['anagra'] ." ON ". $gTables['anagra'] .".id = " . $gTables['clfoco'] . ".id_anagra";
                    $where = $gTables['rental_events'].".id = '".$next_row['id']."'";
                    $what = $gTables['rental_events'] .".*, ". $gTables['anagra'] . ".ragso1, ".	$gTables['anagra'] .".ragso2, ". 	$gTables['tesbro'] . ".numdoc, ".	$gTables['tesbro'] . ".datemi ";
                    $result = gaz_dbi_dyn_query($what, $table, $where, "end DESC");
                    $row=gaz_dbi_fetch_array($result);
                    if (isset($row)){
                      ?>
                      <tr>
                      <td><?php echo "<b>",gaz_format_date($row['end']),"</b> ",$row['type']," ",$row['house_code'],"<b> -> </b>",$row['ragso1']," ",$row['ragso2']," prenotazione n.",$row['numdoc']," del ",gaz_format_date($row['datemi']); ?></td>
                      </tr>
                      <?php
                    }
                  }
                  ?>
                </table>

                <?php
              }
              ?>

            </table>
          </div>

        </div>


      </div>
    </form>
  </div>

