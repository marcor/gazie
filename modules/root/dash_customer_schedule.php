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
if ($admin_aziend['Abilit'] >= 8 && $schedule_view['val'] >= 1) {
    ?>
    <!-- Scadenziari -->
	<div class="panel panel-default panel-user col-md-12" >
          <!--+ DC - 13/02/2019 -->
  				  <div class="wheel_load"></div>
          <!--- DC - 13/02/2019 -->
          <div class="box-header">
              <h3 class="box-title"><?php echo $script_transl['sca_scacli']; ?></h3>
          </div>
          <div class="box-body">
              <table id="clienti" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="clienti_info">
                  <thead>
                      <tr role="row">
                          <!--+ DC 07/02/2019 - th class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 300px;" aria-label="Cliente"><?php echo $script_transl['sca_cliente']; ?></th--->
                          <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 150px;" aria-label="Cliente"><?php echo $script_transl['sca_cliente']; ?></th>
                          <!--+ DC 07/02/2019 - nuova colonne Dare --->
                          <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 70px;" aria-label="Dare"><?php echo $script_transl['sca_dare']; ?></th>
                          <!--- DC 07/02/2019 - nuova colonne Dare --->
                          <!--+ DC 07/02/2019 - th class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 120px;" aria-label="Avere"><?php echo $script_transl['sca_avere']; ?></th--->
                          <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 70px;" aria-label="Avere"><?php echo $script_transl['sca_avere']; ?></th>
                          <!--+ DC 07/02/2019 - nuova colonna Saldo --->
                          <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 70px;" aria-label="Saldo"><?php echo $script_transl['sca_saldo']; ?></th>
                          <!--- DC 07/02/2019 - nuove colonna Saldo --->
                          <!--+ DC 07/02/2019 - th class="sorting_asc" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 120px;" aria-sort="ascending" aria-label="Scadenza"><?php echo $script_transl['sca_scadenza']; ?></th--->
                          <th style="cursor:pointer;cursor:hand" class="sorting_asc" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 70px;" aria-sort="ascending" aria-label="Scadenza"><?php echo $script_transl['sca_scadenza']; ?></th>
                      </tr>
                  </thead>
                  <tbody>
                      <!-- Scadenzario clienti -->
                      <?php

                      // Recupero dati come in 'select_partner_status.php' per recupero DARE/AVERE e calcolare SALDO
    									$paymov = new Schedule;
    									$paymov->setScheduledPartner($admin_aziend['mascli']);

    									$totDare = 0;
    									$totAvere = 0;
    									$totSaldo = 0;

    									// impostazioni variabili
    									$today = date("Y-m-d");
    									//$today = "2019-02-13";
    									$dateFound = "";
    									$id_tesFound = "";
    									$numdocFound = "";
    									$diffDate = 99999999;

    									if (sizeof($paymov->Partners) > 0) {
    										$anagrafica = new Anagrafica();
    										foreach ($paymov->Partners as $p) {
    											$ctrl_close_partner = false;
    											$anagrafica = new Anagrafica();
    											$prt = $anagrafica->getPartner($p);

    											$paymov->getPartnerStatus($p, date("Y") . '-' . date("m") . '-' . date("d"));
    											foreach ($paymov->PartnerStatus as $k => $v) {
    												/*$paymov->docData[$k]['id_tes'] . ' ' .
    												$paymov->docData[$k]['descri'];
    												if ($paymov->docData[$k]['numdoc'] >= 1) {
    													echo ' n.' .
    													$paymov->docData[$k]['numdoc'] . '/' .
    													$paymov->docData[$k]['seziva'] . ' del ' .
    													gaz_format_date($paymov->docData[$k]['datdoc']);
    												}*/

    												// INIZIO crezione tabella per la visualizzazione sul tootip di tutto il movimento e facccio la somma del totale movimento
    												$res_rig = gaz_dbi_dyn_query("*", $gTables['rigmoc'], 'id_tes=' . $paymov->docData[$k]['id_tes'], 'id_rig');
    												$tt = '<table><th colspan=3 >' . $paymov->docData[$k]['descri']. '<br /> N. ' . $paymov->docData[$k]['numdoc'] . ' del ' . gaz_format_date($paymov->docData[$k]['datdoc']) . '</th>';
    												//$tt = '<table><th colspan=3 >' . "Intestazione" . '</th>';
    												//$tot = 0.00;
    												while ($rr = gaz_dbi_fetch_array($res_rig)) {
    													$account = $anagrafica->getPartner($rr["codcon"]);
    													$tt .= '<tr><td>' . htmlspecialchars( $account['descri'] ) . '</td><td align=right>' . $rr['import'] . '</td><td align=right>' . $rr['darave'] . '</td></tr>';
    												}
    												$tt .= '</table>';
    												// FINE creazione tabella per il tooltip

    												foreach ($v as $ki => $vi) {
    													$ctrl_close_paymov = false;
    													$lnk = '';
    													$class_paymov = 'FacetDataTDevidenziaCL';
    													$v_op = '';
    													$cl_exp = '';
    													if ($vi['op_val'] >= 0.01) {
    														$v_op = gaz_format_number($vi['op_val']);
    													}
    													$v_cl = '';
    													if ($vi['cl_val'] >= 0.01) {
    														$v_cl = gaz_format_number($vi['cl_val']);
    														$cl_exp = gaz_format_date($vi['cl_exp']);
    													}
    													$expo = '';

    													$stato_partita = "";
    													$style_partita = "";

    													if ($vi['expo_day'] >= 1) {
    														$expo = $vi['expo_day'];
    														if ($vi['cl_val'] == $vi['op_val']) {
    															$vi['status'] = 2; // la partita ? chiusa ma ? esposta a rischio insolvenza
    															$class_paymov = 'FacetDataTDevidenziaOK';
    														}
    													} else {
    														$stato_partita = "warning";
    														if ($vi['cl_val'] == $vi['op_val']) { // chiusa e non esposta
    															continue;
    															$cl_exp = '';
    															$class_paymov = 'FacetDataTD';
    															$ctrl_close_paymov = true;
    														} elseif ($vi['status'] == 3) { // SCADUTA
    															$cl_exp = '';
    															$class_paymov = 'FacetDataTDevidenziaKO';

    															$stato_partita = "warning";
    															$style_partita = "color:red";

    														} elseif ($vi['status'] == 9) { // PAGAMENTO ANTICIPATO
    															$class_paymov = 'FacetDataTDevidenziaBL';
    															$vi['expiry'] = $vi['cl_exp'];
    														} elseif ($vi['status'] == 0) { // APERTA
    															$lnk = " &nbsp;<a title=\"Riscuoti\" class=\"btn btn-xs btn-default btn-pagamento\" href=\"../vendit/customer_payment.php?partner=" . $p . "\"><i class=\"glyphicon glyphicon-euro\"></i></a>";
    														}
    													}

    													// controlli per calcolo data da visualizzare in prossimit? di oggi
    													$datetime1 = date_create($vi['expiry']);
    													$datetime2 = date_create($today);
    													$diffDays = date_diff($datetime1, $datetime2);
    													$nGiorni=$diffDays->format('%R%a days');

    													if($nGiorni <= $diffDate) {
    														$dateFound = $vi['expiry'];
    														$id_tesFound = $paymov->docData[$k]['id_tes'];
    														$numdocFound = $paymov->docData[$k]['numdoc'];
    														$diffDate = $nGiorni;
    													}

    													// costruzione chiave partita su cui posizionarsi
    													$keyRowCli =  $paymov->docData[$k]['id_tes'] . "~" . $paymov->docData[$k]['numdoc'] . "~" . $vi['expiry'];

    													// stampa colonne
    													echo "<tr style='" . $style_partita ."' class='odd " . $stato_partita . "' role='row'>"; //*?
    													//echo "<td>" . $prt['ragso1'] . "</td>";
    													echo '<td><div class="gazie-tooltip" data-type="movcon-thumb" data-id="' . $paymov->docData[$k]['id_tes'] . '" data-title="' . str_replace("\"", "'", $tt) . '" >' . $prt['ragso1'] . "</div><span class='keyRow'>" . $keyRowCli . "</span></td>";

    													echo "<td align='right'>" . gaz_format_number($vi['cl_val']) . "</td>";

    													echo "<td align='right'>" . gaz_format_number($vi['op_val']) . "</td>";

    													echo "<td align='right'>" . gaz_format_number($vi['op_val']-$vi['cl_val']) . "</td>";

    													echo "<td class='" . $class_paymov . "' align='center'><span>" . $vi['expiry'] . "</span>" . gaz_format_date($vi['expiry']) . " &nbsp; $lnk</td>";
    													echo "</tr>";

    													$totDare += $vi['cl_val'];
    													$totAvere += $vi['op_val'];
    												}
    											}
    										}
    									}

    									$keyRowFoundCli = $id_tesFound . "~" . $numdocFound . "~" . $dateFound;

    									$totSaldo = $totAvere-$totDare;
                     ?>
                  </tbody>
                  <tfoot>
                      <tr>
                        <th rowspan="1" colspan="1"></th>
                        <!--+ DC 07/02/2019 - nuova colonna Dare --->
                        <th style="text-align:right" rowspan="1" colspan="1"><?php echo gaz_format_number($totDare); ?></th>
                        <!--- DC 07/02/2019 - nuova colonna Dare --->
                        <!--+ DC 07/02/2019 - aggiunto style su th e stampata variabile totale Avere --->
                        <th style="text-align:right" align="right" rowspan="1" colspan="1"><?php echo gaz_format_number($totAvere); ?></th>
                        <!--- DC 07/02/2019 - aggiunto style su th e stampata variabile totale Avere --->
                        <!--+ DC 07/02/2019 - nuova colonna Saldo --->
                        <th style="text-align:right" align="right" rowspan="1" colspan="1"><?php echo gaz_format_number($totSaldo); ?></th>
                        <!--- DC 07/02/2019 - nuova colonna Saldo --->
                        <th rowspan="1" colspan="1"></th>
                      </tr>
                  </tfoot>
              </table>
			</div>
      </div>
    <?php
}
?>