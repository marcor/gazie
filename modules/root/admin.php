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


require( "../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
$company_choice = gaz_dbi_get_row($gTables['config'], 'variable', 'users_noadmin_all_company')['cvalue'];
require( "../../modules/vendit/lib.function.php");
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
	$form['company_id'] = intval($_POST['company_id']);
	if ($company_choice==1 || $admin_aziend['Abilit'] >= 8){
		$form['search']['company_id'] = $_POST['search']['company_id'];
	}
}


$checkUpd = new CheckDbAlign;
$data = $checkUpd->TestDbAlign();
if ($data) {
    // induco l'utente ad aggiornare il db
    redirect( $path_root . '/setup/install/install.php?tp=' . $table_prefix);
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
            redirect( $path_root . '/modules/inform/backup.php?internal');
        }
    }
}

require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<style>
#sortable div:hover {
    cursor: move;
}
#sortable>div {
	margin: auto;
}
#sortable {
	display: flex;
	flex-wrap: wrap;
}
@media (max-width: 978px) {
	form .container, form .container #sortable .col-md-6, div .container-fluid {
		padding-left: 0px;
		padding-right: 0px;
	}
	div.panel { 
		border-left:0px;
		border-right:0px;
		border-radius:0px;
	}
	#gaz-responsive-table table{
		padding: 0px;
		overflow-wrap: anywhere;
	}
	.dataTables_wrapper > div.row > div.col-sm-12{
		padding-right: 0px;
	} 
	.row { 
		margin-right: 0px;
		padding: 0px;
	}
}
.panel { 
	padding: 0px 0px 5px 0px;
	margin: 0px 0px 5px 0px;
}

.btn-full { 
	width: 100%;
	margin-top: 2px;
}
.btn-full>span { 
	width: 100%;
	margin-top: 2px;
	white-space: normal;
}
.vertical-align {
    display: flex;
    align-items: center;
}
</style>
<script>
$(function(){
	function isMobile() {
		return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
	}
	if (!isMobile()) {
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
	}
});
</script>
<div class="container-fluid gaz-body">
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
echo '<div id="sortable" class="vertical-align">';
while ( $row = gaz_dbi_fetch_array($get_widgets) ) {
	echo '<div class="col-xs-12 col-md-6 text-center" id="position-'.$row['id_bread'].'">';
	require('../'.$row['file']);
	echo '</div>'; 
}
echo '</div>';

?>
    </div>
</form>
</div>
<?php
require('../../library/include/footer.php');
?>
