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
             <h3 class="box-title"><?php echo $script_transl['sca_scafor']; ?></h3>
         </div>
         <div class="box-body">
             <table id="fornitori" class="table table-bordered table-striped table-responsive dataTable" role="grid" aria-describedby="fornitori_info">
                 <thead>
                     <tr role="row">
                       <!--+ DC 07/02/2019 - th class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 300px;" aria-label="Rendering engine: activate to sort column descending"><?php echo $script_transl['sca_fornitore']; ?></th--->
                       <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 150px;" aria-label="Rendering engine: activate to sort column descending"><?php echo $script_transl['sca_fornitore']; ?></th>
                       <!--+ DC 07/02/2019 - th class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 120px;" aria-label="Browser: activate to sort column ascending"><?php echo $script_transl['sca_dare']; ?></th--->
                       <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 70px;" aria-label="Browser: activate to sort column ascending"><?php echo $script_transl['sca_dare']; ?></th>
                       <!--+ DC 07/02/2019 - nuove colonne Avere/Saldo --->
                       <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 70px;" aria-sort="ascending" aria-label="Platform(s): activate to sort column ascending"><?php echo $script_transl['sca_avere']; ?></th>
                       <th style="cursor:pointer;cursor:hand" class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 70px;" aria-sort="ascending" aria-label="Platform(s): activate to sort column ascending"><?php echo $script_transl['sca_saldo']; ?></th>
                       <!--- DC 07/02/2019 - nuove colonne Avere/Saldo --->
                       <!--+ DC 07/02/2019 - th class="sorting_asc" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 120px;" aria-sort="ascending" aria-label="Platform(s): activate to sort column ascending"><?php echo $script_transl['sca_scadenza']; ?></th--->
                       <th style="cursor:pointer;cursor:hand" class="sorting_asc" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 70px;" aria-sort="ascending" aria-label="Platform(s): activate to sort column ascending"><?php echo $script_transl['sca_scadenza']; ?></th>
                     </tr>
                 </thead>

                 <tbody>
                     <!-- Scadenzario fornitori -->
                     <?php

                     // Recupero dati come in 'select_suppliers_status.php' per recupero DARE/AVERE e calcolare SALDO
         							$paymov = new Schedule;
         							$paymov->setScheduledPartner($admin_aziend['masfor']);

         							$totDare = 0;
         							$totAvere = 0;

         							// impostazioni variabili
          							$dateFound = "";
         							$id_tesFound = "";
         							$numdocFound = "";
         							$diffDate = 99999999;
         							if (sizeof($paymov->Partners) > 0) {
         								$anagrafica = new Anagrafica();
         								foreach ($paymov->Partners as $p) {
         									$ctrl_close_partner = false;
         									$prt = $anagrafica->getPartner($p);
         									$paymov->getPartnerStatus($p, date("Y") . '-' . date("m") . '-' . date("d"));
         									foreach ($paymov->PartnerStatus as $k => $v) {
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
         													$lnk = " &nbsp;<a title=\"Paga il fornitore\" class=\"btn btn-xs btn-default btn-pagamento\" href=\"../acquis/supplier_payment.php?partner=" . $p . "\"><i class=\"glyphicon glyphicon-euro\"></i></a>";
         												}
         											}

         											// controlli per calcolo data da visualizzare in prossimit? di oggi
         											$datetime1 = date_create($vi['expiry']);
         											$datetime2 = date_create();
         											$diffDays = date_diff($datetime1, $datetime2);
         											$nGiorni=$diffDays->format('%R%a days');

         											if($nGiorni <= $diffDate) {
         												$dateFound = $vi['expiry'];
         												$id_tesFound = $paymov->docData[$k]['id_tes'];
         												$numdocFound = $paymov->docData[$k]['numdoc'];
         												$diffDate = $nGiorni;
         											}

         											$keyRowFor =  $paymov->docData[$k]['id_tes'] . "~" . $paymov->docData[$k]['numdoc'] . "~" . $vi['expiry'];
         											//echo $keyRow . "<br />";

         											//if ($vi['cl_val'] == $vi['op_val']) { // chiusa e non esposta - non stampo
         											// stampa colonne
         											echo "<tr style='" . $style_partita ."' class='odd " . $stato_partita . "' role='row'>"; //*?
         											//echo "<td>" . $prt['ragso1'] . "</td>";
         											echo '<td><div class="gazie-tooltip" data-type="movcon-thumb" data-id="' . $paymov->docData[$k]['id_tes'] . '">' . $prt['ragso1'] . "</div><span class='keyRow'>" . $keyRowFor . "</span></td>";
         											echo "<td align='right'>" . gaz_format_number($vi['op_val']) . "</td>";

         											echo "<td align='right'>" . gaz_format_number($vi['cl_val']) . "</td>";
         											echo "<td align='right'>" . gaz_format_number($vi['op_val']-$vi['cl_val']) . "</td>";

         											echo "<td class='" . $class_paymov . "' align='center'><span>" . $vi['expiry'] . "</span>" . gaz_format_date($vi['expiry']) . " &nbsp; $lnk</td>";
         											echo "</tr>";

         											$totDare += $vi['op_val'];
         											$totAvere += $vi['cl_val'];
         											//}
         										}
         									}
         								}
         							}
         							//echo "Data trovata: " . $dateFound . " - " . $id_tesFound . " / " . $numdocFound;

         							$keyRowFoundFor = $id_tesFound . "~" . $numdocFound . "~" . $dateFound;

         							$totSaldo = $totDare-$totAvere;

         							/*+ DC - 07-02-2018 - sostituito codice originale che non teneva conto dei pagamenti - vedi sopra
         							$ctrl_partner = 0;
                                       $scdl = new Schedule;
                                       $m = $scdl->getScheduleEntries("0", $admin_aziend['masfor'], true);
         							if (sizeof($scdl->Entries) > 0) {
         								foreach ($scdl->Entries AS $key => $mv) {
                                               $paymov = $mv["id_tesdoc_ref"];
                                               $scdl->getStatus($paymov);
                                               $r = $scdl->Status;
                                               if ($mv['expiry'] <= date("Y-m-d")) {
                                                   $stato_partita = "warning";
                                               } else {
                                                   $stato_partita = "";
                                               }
                                               if ($mv["amount"] >= 0.01 && $r['sta']<>1) {
                                                   echo "<tr class='odd " . $stato_partita . "' role='row'>";
                                                   echo "<td>" . $mv["ragsoc"] . "</td>";
                                                   echo "<td align='right'>" . gaz_format_number($mv["amount"]) . "</td>";

         										echo "<td align='right'>" . gaz_format_number($mv["amount"]) . "</td>";
                                                   echo "<td align='right'>" . gaz_format_number($mv["amount"]) . "</td>";

                                                   echo "<td align='center'><span>" . $mv["expiry"] . "</span>" . gaz_format_date($mv["expiry"]) . "</td>";
                                                   echo "</tr>";
                                               }
                                           }
                                       }
         							*/

                     ?>
                 </tbody>
                 <tfoot>
                     <tr>
                                      <th rowspan="1" colspan="1"></th>
                  <!--+ DC 07/02/2019 - aggiunto style su th e stampata variabile totDare --->
                                      <th style="text-align:right" rowspan="1" colspan="1"><?php echo gaz_format_number($totDare); ?></th>
                  <!--- DC 07/02/2019 - nuove colonne Avere/Saldo --->
                  <!--+ DC 07/02/2019 - nuove colonne Avere/Saldo --->
                  <th style="text-align:right" rowspan="1" colspan="1"><?php echo gaz_format_number($totAvere); ?></th>
                                      <th style="text-align:right" rowspan="1" colspan="1"><?php echo gaz_format_number($totSaldo); ?></th>
                  <!--- DC 07/02/2019 - nuove colonne Avere/Saldo --->
                                      <th rowspan="1" colspan="1"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
	</div>
    <script src="../../library/theme/lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../../library/theme/lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script>

    //*+ DC - 07/02/2018
		//modificati parametri order/filter
        //        "order": [2, 'asc'],
		//		  "filter": false,
		//*- DC - 07/02/2018
		$(function () {
			$('#fornitori').DataTable({
                "oLanguage": {
                    "sUrl": "../../library/theme/lte/plugins/datatables/Italian.json"
                },
                "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, "Tutti"]],
                "iDisplayLength": 5,
                "order": [4, 'asc'],
                "filter": true,
				"responsive": true,
                "stateSave": true
            });
        });

  //*+ DC - 07/02/2018 - nuove funzioni per gestione posizionmento su scadenzari
  function gotoPage(id,num)
	{
		var table = $(id).DataTable();
		table.page( num ).draw( false );
	}

	function searchPageOnTable(id,keyRow,lenPage)
	{
		var table = $(id).DataTable();

		var plainArray = table
			.column(0)
			.data()
			.toArray();

		var i;

		for(i= 0 ; i < plainArray.length; i++)
		{
			if(plainArray[i].split('"keyRow">')[1].replace("</span>","") == keyRow)
				break;
		}

		return Math.floor(i / lenPage)
	}

	//add stylesheet css
	//$('document').ready(function() {
		$("head").append('<link rel="stylesheet" href="./admin.css">');
	//});

	$(window).load(function(){
		// Scadenziario Fornitori
		keyRowFor = "<?php echo $keyRowFoundFor ?>";

		if(keyRowFor != ""){
			setTimeout(function(){num = searchPageOnTable('#fornitori',keyRowFor,$('#fornitori').DataTable().page.len())
				gotoPage('#fornitori',num);
				$("#fornitori").css("max-height","none");
				$("#fornitori").css("opacity","1");
				$(".wheel_load").css("display","none");
			},1000)
			}
			else
			{
				$("#fornitori").css("max-height","none");
				$("#fornitori").css("opacity","1");
				$(".wheel_load").css("display","none");
			}
		});
    //*- DC - 07/02/2018 - nuove funzioni per gestione posizionmento su scadenzari
    </script>
<?php
}
?>