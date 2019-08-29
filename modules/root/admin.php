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

require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$company_choice = gaz_dbi_get_row($gTables['config'], 'variable', 'users_noadmin_all_company')['cvalue'];
require("../../modules/vendit/lib.function.php");
$lm = new lotmag;
$schedule_view = gaz_dbi_get_row($gTables['company_config'], 'var', 'schedule_view');


if (!isset($_POST['hidden_req'])) {
    $form['hidden_req'] = '';
    $form['company_id'] = $admin_aziend['company_id'];
    $form['search']['company_id'] = '';
} else {
    if (isset($_POST['logout'])) {
        redirect('logout.php');
        exit;
    }
    $form['hidden_req'] = $_POST['hidden_req'];
	if ($company_choice==1 || $admin_aziend['Abilit'] >= 8){
		$form['company_id'] = intval($_POST['company_id']);
		$form['search']['company_id'] = $_POST['search']['company_id'];
	}
}


$checkUpd = new CheckDbAlign;
$data = $checkUpd->TestDbAlign();
if ($data) {
    // induco l'utente ad aggiornare il db
    redirect('../../setup/install/install.php?tp=' . $table_prefix);
    exit;
}
$lastBackup = $checkUpd->testDbBackup();

//andrea backup automatico
$backupMode = $checkUpd->backupMode();
if ($backupMode == "automatic") {
    if ($checkUpd->testDbBackup(0) != date("Y-m-d")) {
        $sysdisk = $checkUpd->get_system_disk();
        $gazpath = $checkUpd->get_backup_path();
        $freespace = gaz_dbi_get_row($gTables['config'], 'variable', 'freespace_backup');
        $percspace = (disk_total_space($sysdisk) / 100) * $freespace["cvalue"];

        $files = glob($gazpath . '*.gaz');
        array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files);

        $keep = gaz_dbi_get_row($gTables['config'], 'variable', 'keep_backup');
        if (count($files) > $keep["cvalue"]) {
            if (count($files) > $keep["cvalue"] && $keep["cvalue"] > 0) {
                for ($i = 0; $i < count($files) - ($keep["cvalue"]); $i++)
                    unlink($files[$i]);
                // unlink(dirname(__FILE__) . $files[$i];
                //echo $files[$i] . "<br>";
            }
        }
        if (disk_free_space($sysdisk) < $percspace) {
            $i = 0;
            while (disk_free_space($sysdisk) < $freespace && $i < count($files)) {
                if ($i <= count($files) - 30) {
                    unlink($files[$i]);
                }
                $i++;
            }
        }
        if ($admin_aziend['Abilit'] >= 8 && checkAccessRights($_SESSION['user_name'], 'inform', $_SESSION['company_id']) != 0) {
            redirect('../../modules/inform/backup.php?internal');
        }
    }
}

// Antonio Germani - controllo scadenze articoli con lotti
$query="SELECT codice, descri FROM " . $gTables['artico'] . " WHERE  lot_or_serial = '1'";
$result = gaz_dbi_query($query);
$cod=0;
$inscad=0;
$scad=0;
$lotinscad=array();
$lotscad=array();
while ($row = gaz_dbi_fetch_array($result)) {
	$lm -> getAvailableLots($row['codice'],0);
	if (count($lm->available) > 0) {
		foreach ($lm->available as $v_lm) {
			// 1 giorno è 86400 secondi ;  3 mesi sono 15552000
			if (strtotime($v_lm['expiry'])>0 and (strtotime($v_lm['expiry'])-15552000)<=strtotime (date("Ymd")) and strtotime($v_lm['expiry']) > strtotime (date("Ymd"))) {
				$lotinscad[$inscad]['codice']=$row['codice'];
				$lotinscad[$inscad]['descri']=$row['descri'];
				$lotinscad[$inscad]['identifier']=$v_lm['identifier'];
				$lotinscad[$inscad]['expiry']=$v_lm['expiry'];
				$lotinscad[$inscad]['rest']=$v_lm['rest'];
				$inscad++;
			}
			if (strtotime($v_lm['expiry'])>0 and strtotime($v_lm['expiry']) <= strtotime (date("Ymd"))) {
				$lotscad[$scad]['codice']=$row['codice'];
				$lotscad[$scad]['descri']=$row['descri'];
				$lotscad[$scad]['identifier']=$v_lm['identifier'];
				$lotscad[$scad]['expiry']=$v_lm['expiry'];
				$lotscad[$scad]['rest']=$v_lm['rest'];
				$scad++;
			}
		}
	}
}


require("../../library/include/header.php");
$script_transl = HeadMain();
$t = strftime("%H");
if ($t > 4 && $t <= 13) {
    $msg = $script_transl['morning'];
} elseif ($t > 13 && $t <= 17) {
    $msg = $script_transl['afternoon'];
} elseif ($t > 17 && $t <= 21) {
    $msg = $script_transl['evening'];
} else {
    $msg = $script_transl['night'];
}
?>
<style>
#sortable div:hover {
    cursor: move;
}

#sortable {
	display: flex;
	flex-wrap: wrap;
}

</style>
<script>
$(function(){
  $("#sortable").sortable({
    update: function (event, ui) {
        var data = $(this).sortable('serialize');
        // POST to server using $.post or $.ajax
        $.ajax({
            data: data,
            type: 'post',
            url: './dashboard_update.php'
        });
    }
	});
  $("#sortable").disableSelection();
});
</script>
  <form method="POST" name="gaz_form">
    <input type="hidden" value="<?php echo $form['hidden_req'];?>" name="hidden_req" />
    <div class="container">

        <?php
        if ($lastBackup) {
            ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php
                if ($admin_aziend['Abilit'] > 8) {
                    echo $script_transl['errors'][4] . ' : <a href="../inform/backup.php?' . $checkUpd->backupMode() . '">BACKUP!</a>(' . $checkUpd->backupMode() . ')';
                } else {
                    echo $script_transl['errors'][4] . ' o avvisa il tuo amministratore!';
                }
                ?>
            </div>
            <?php
        }
        if (empty($admin_aziend['legrap_pf_nome']) || empty($admin_aziend['legrap_pf_cognome'])) {
            ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php
                    echo $script_transl['errors']['legrap'] ;
                ?>
            </div>
            <?php
        }
        if ($admin_aziend['sexper']=='G' && ( empty($admin_aziend['REA_ufficio']) || empty($admin_aziend['REA_socio']) || strlen($admin_aziend['REA_numero']) < 4)) {
            ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php
                    echo $script_transl['errors']['rea'] ;
                ?>
            </div>
            <?php
        }
$get_widgets = gaz_dbi_dyn_query("*", $gTables['breadcrumb'],"exec_mode=2 AND adminid='".$admin_aziend['user_name']."'", 'position_order');
echo '<div id="sortable">';
while ( $row = gaz_dbi_fetch_array($get_widgets) ) {
	echo '<div class="col-md-6 text-center" id="position-'.$row['id_bread'].'">';
	require('../'.$row['file']);
	echo '</div>'; 
}
echo '</div>';

//		 <!-- Antonio Germani - lotti in scadenza -->
		 if (count($lotinscad)>0 or count($lotscad)>0){ // visualizzo scadenzario lotti sono se sono presenti
			 ?>
                <div class="col-sm-6">
                        <div class="box-header">
                            <h3 class="box-title"><?php echo $script_transl['inscalot']; ?></h3>
                        </div>
                        <div class="box-body">
                            <table id="inscad" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="clienti_info">
                                <thead>
                                    <tr role="row">
                                        <th  tabindex="0" rowspan="1" colspan="1" style="width: 120px;"><?php echo $script_transl['cod']; ?></th>
                                        <th  tabindex="0" rowspan="1" colspan="1" style="width: 310px;"><?php echo $script_transl['des']; ?></th>
										<th  tabindex="0" rowspan="1" colspan="1" style="width: 120px;" ><?php echo $script_transl['lot']; ?></th>
                                        <th  tabindex="0" rowspan="1" colspan="1" style="width: 120px;" ><?php echo $script_transl['sca_scadenza']; ?></th>
										<th  tabindex="0" rowspan="1" colspan="1" style="width: 110px;" ><?php echo $script_transl['res']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- lotti in scadenza -->
                                    <?php
									for ($x=0; $x<count($lotinscad); $x++){
										 echo "<tr role='row'>";
										 echo "<td align='left'>" . $lotinscad[$x]['codice'] . "</td>";
										 echo "<td align='left'>" . substr($lotinscad[$x]['descri'],0,21) . "</td>";
										 echo "<td align='left'>" . $lotinscad[$x]['identifier'] . "</td>";
										 echo "<td align='left'>" . gaz_format_date($lotinscad[$x]['expiry']) . "</td>";
										 echo "<td align='left'>" . gaz_format_number($lotinscad[$x]['rest']) . "</td>";
										echo "</tr>";
									}
                                    ?>
                                </tbody>
                              </table>
                        </div>
                </div>
                <!-- Antonio Germani - lotti scaduti -->
                <div class="col-sm-6">
                    <div class="box gaz-home-scadenze">
                        <div class="box-header">
                            <h3 class="box-title"><?php echo $script_transl['scalot']; ?></h3>
                        </div>
                        <div class="box-body">
                            <table id="scad" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="fornitori_info">
                                <thead>
                                    <tr role="row">
                                        <th  class="sorting" tabindex="0" rowspan="1" colspan="1" style="width:120px;"><?php echo $script_transl['cod']; ?></th>
                                        <th  tabindex="0" rowspan="1" colspan="1" style="width: 310px;"><?php echo $script_transl['des']; ?></th>
										<th  tabindex="0" rowspan="1" colspan="1" style="width: 120px;" ><?php echo $script_transl['lot']; ?></th>
                                        <th  tabindex="0" rowspan="1" colspan="1" style="width: 120px;" ><?php echo $script_transl['sca_scadenza']; ?></th>
										<th  tabindex="0" rowspan="1" colspan="1" style="width: 110px;" ><?php echo $script_transl['res']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- lotti scaduti -->
                                    <?php
									for ($x=0; $x<count($lotscad); $x++){
										echo "<tr role='row'>";
										echo "<td align='left'>" . $lotscad[$x]['codice'] . "</td>";
										echo "<td align='left'>" . substr($lotscad[$x]['descri'],0,21) . "</td>";
										echo "<td align='left'>" . $lotscad[$x]['identifier'] . "</td>";
										echo "<td align='left'>" . gaz_format_date($lotscad[$x]['expiry']) . "</td>";
										echo "<td align='left'>" . gaz_format_number($lotscad[$x]['rest']) . "</td>";
										echo "</tr>";
									}
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fine scadenzario lotti -->
        <?php
	}
?>
        <div class='admin_footer' align="center">
            <div > GAzie Version: <?php echo GAZIE_VERSION; ?> Software Open Source (lic. GPL)
                <?php echo $script_transl['business'] . " " . $script_transl['proj']; ?>
                <a target="_new" title="<?php echo $script_transl['auth']; ?>" href="http://www.devincentiis.it"> http://www.devincentiis.it</a>
            </div>
        </div>
    </div>
</form>
<?php

/* questa parte sarebbe da mettere nel footer specifico del tema (library/theme/nome_tema/footer.php)
 * in ognuno dei quali mettere una classe contenente, oltre al costrutto, anche le varie funzioni
 * richiamabili alla fine dagli script php e comunque presenti sui footer di TUTTU i motori di template
 */
if ($admin_aziend['Abilit'] >= 8 && $schedule_view['val'] >= 1) {
    ?>
    <script src="../../library/theme/lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../../library/theme/lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script>

    //*+ DC - 07/02/2018
		//modificati parametri order/filter
        //        "order": [2, 'asc'],
		//		  "filter": false,
		//*- DC - 07/02/2018
		$(function () {
			$("#clienti").DataTable({
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
		// Scadenziario Clienti
		keyRowCli = "<?php echo $keyRowFoundCli ?>";

		if(keyRowCli != ""){
			setTimeout(function(){num = searchPageOnTable('#clienti',keyRowCli,$('#clienti').DataTable().page.len())
				gotoPage('#clienti',num);
				$("#clienti").css("max-height","none");
				$("#clienti").css("opacity","1");
				$(".wheel_load").css("display","none");
			},1000)
			}
			else
			{
				$("#clienti").css("max-height","none");
				$("#clienti").css("opacity","1");
				$(".wheel_load").css("display","none");
			}

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
require('../../library/include/footer.php');
?>
