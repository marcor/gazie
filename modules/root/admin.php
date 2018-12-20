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
$company_choice = gaz_dbi_get_row($gTables['config'], 'variable', 'users_noadmin_all_company')['cvalue'];
if (!isset($_POST['hidden_req'])) {
    $form['hidden_req'] = '';
    $form['company_id'] = $admin_aziend['company_id'];
    $form['search']['company_id'] = '';
} else {
    if (isset($_POST['logout'])) {
        header("Location: logout.php");
        exit;
    }
    $form['hidden_req'] = $_POST['hidden_req'];
	if ($company_choice==1 || $admin_aziend['Abilit'] >= 8){
		$form['company_id'] = intval($_POST['company_id']);
		$form['search']['company_id'] = $_POST['search']['company_id'];
	}
}

function selectCompany($name, $val, $strSearch = '', $val_hiddenReq = '', $mesg, $class = 'FacetSelect') {
    global $gTables, $admin_aziend;
    $table = $gTables['aziend'] . ' LEFT JOIN ' . $gTables['admin_module'] . ' ON ' . $gTables['admin_module'] . '.company_id = ' . $gTables['aziend'] . '.codice';
    $where = $gTables['admin_module'] . '.adminid=\'' . $admin_aziend["user_name"] . '\' GROUP BY company_id';
    if ($val > 0 && $val < 1000) { // vengo da una modifica della precedente select case quindi non serve la ricerca
        $co_rs = gaz_dbi_dyn_query("*", $table, 'company_id = ' . $val . ' AND ' . $where, "ragso1 ASC");
        $co = gaz_dbi_fetch_array($co_rs);
        changeEnterprise(intval($val));
        echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
        echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"%%\">\n";
        echo "\t<input type=\"submit\" value=\"" . $co['ragso1'] . "\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
    } else {
        if (strlen($strSearch) >= 2) { //sto ricercando un nuovo partner
            echo "\t<select name=\"$name\" class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
            $co_rs = gaz_dbi_dyn_query("*", $table, "ragso1 LIKE '" . addslashes($strSearch) . "%' AND " . $where, "ragso1 ASC");
            if ($co_rs) {
                echo "<option value=\"0\"> ---------- </option>";
                while ($r = gaz_dbi_fetch_array($co_rs)) {
                    $selected = '';
                    if ($r['company_id'] == $val) {
                        $selected = "selected";
                    }
                    echo "\t\t <option value=\"" . $r['company_id'] . "\" $selected >" . intval($r['company_id']) . "-" . $r["ragso1"] . "</option>\n";
                }
                echo "\t </select>\n";
            } else {
                $msg = $mesg[0];
            }
        } else {
            $msg = $mesg[1];
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
        }
        echo "\t<input type=\"text\" name=\"search[$name]\" value=\"" . $strSearch . "\" maxlength=\"15\" size=\"6\" class=\"FacetInput\">\n";
        if (isset($msg)) {
            echo "<input type=\"text\" style=\"color: red; font-weight: bold;\" size=\"" . strlen($msg) . "\" disabled value=\"$msg\">";
        }
        //echo "\t<input type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
        /** ENRICO FEDELE */
        /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
        echo '<button type="submit" class="btn btn-default btn-sm" name="search_str"><i class="glyphicon glyphicon-search"></i></button>';
        /** ENRICO FEDELE */
    }
}

$checkUpd = new CheckDbAlign;
$data = $checkUpd->TestDbAlign();
if ($data) {
    // induco l'utente ad aggiornare il db      
    header("Location: ../../setup/install/install.php?tp=" . $table_prefix);
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
        if (checkAccessRights($_SESSION["user_name"], 'inform', $_SESSION['company_id']) != 0) {
            header("Location: ../../modules/inform/backup.php?internal");
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
<form method="POST" name="gaz_form">
    <input type="hidden" value="' . $form['hidden_req'] . '" name="hidden_req" />
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
        ?>
        <div class="row text-center">
            <div class="col-sm-6">
                <div class="panel panel-default company-color panel-company" >
                    <p>
                        <?php echo $script_transl['company'] ?>
                    <div class="img-containter">
                        <a href="../config/admin_aziend.php"><img class="img-circle dit-picture" src="view.php?table=aziend&value=<?php echo $form['company_id']; ?>" alt="Logo" style="max-width: 100%;" border="0" title="<?php echo $script_transl['upd_company']; ?>" ></a>
                    </div>
                    </p>
                    <p>
                        <?php
						if ($company_choice==1 || $admin_aziend['Abilit'] >= 8){
							echo $script_transl['mesg_co'][2] . ' &rArr; ';
							selectCompany('company_id', $form['company_id'], $form['search']['company_id'], $form['hidden_req'], $script_transl['mesg_co']);
                        }
						?>
                    </p>
                    <p>
                        <?php echo $script_transl['logout']; ?> &rArr; <input name="logout" type="submit" value=" Logout ">
                    </p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default panel-user" >
                    <p>
                        <?php echo ucfirst($msg) . " " . $admin_aziend['user_firstname'] . ' (ip=' . $admin_aziend['last_ip'] . ')'; ?>
                    </p>
                    <p>
                    <div class="img-containter">
                        <a href="../config/admin_utente.php?user_name=<?php echo $admin_aziend["user_name"]; ?>&Update">
                            <img class="img-circle usr-picture" src="view.php?table=admin&field=user_name&value=<?php echo $admin_aziend["user_name"] ?>" alt="<?php echo $admin_aziend['user_lastname'] . ' ' . $admin_aziend['user_firstname']; ?>" style="max-width: 100%;" title="<?php echo $script_transl['change_usr']; ?>" >
                        </a>
                    </div>
                    </p>
                    <p>
                        <?php echo $script_transl['access'] . $admin_aziend['Access'] . $script_transl['pass'] . gaz_format_date($admin_aziend['datpas']) ?> 
                    </p>    
                    <div>
						<a class="btn btn-primary" href="../config/print_privacy_regol.php" class="button"> <?php echo $script_transl['user_regol'];?></a> 
					</div>
                </div>
            </div>
        </div>

        <?php
        $schedule_view = gaz_dbi_get_row($gTables['company_config'], 'var', 'schedule_view');
        if ($admin_aziend['Abilit'] >= 8 && $schedule_view['val'] >= 1) {
            ?>
            <!-- Scadenziari -->
            <div class="row">
                <div class="col-sm-6">
                    <div class="box gaz-home-scadenze">
                        <div class="box-header">
                            <h3 class="box-title"><?php echo $script_transl['sca_scacli']; ?></h3>
                        </div>
                        <div class="box-body">
                            <table id="clienti" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="clienti_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 300px;" aria-label="Cliente"><?php echo $script_transl['sca_cliente']; ?></th>
                                        <th class="sorting" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 120px;" aria-label="Avere"><?php echo $script_transl['sca_avere']; ?></th>
                                        <th class="sorting_asc" tabindex="0" aria-controls="clienti" rowspan="1" colspan="1" style="width: 120px;" aria-sort="ascending" aria-label="Scadenza"><?php echo $script_transl['sca_scadenza']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Scadenzario clienti -->
                                    <?php
                                    $ctrl_partner = 0;
                                    $scdl = new Schedule;
                                    $m = $scdl->getScheduleEntries("0", $admin_aziend['mascli'], true);
                                    if (sizeof($scdl->Entries) > 0) {
										foreach ($scdl->Entries AS $key => $mv) {
                                            $paymov = $mv["id_tesdoc_ref"];
                                            $scdl->getStatus($paymov);
                                            $r = $scdl->Status;
                                            $status_descr = '';
                                            if ($mv['expiry'] <= date("Y-m-d")) {
                                                $stato_partita = "warning";
                                            } else {
                                                $stato_partita = "";
                                            }
                                            if ($mv["amount"] >= 0.01 && $r['sta']<>1) {
                                                echo "<tr class='odd " . $stato_partita . "' role='row'>";
                                                echo "<td>" . $mv["ragsoc"] . "</td>";
                                                echo "<td align='right'>" . gaz_format_number($mv["amount"]) . "</td>";
                                                echo "<td align='center'><span>" . $mv["expiry"] . "</span>" . gaz_format_date($mv["expiry"]) . "</td>";
                                                echo "</tr>";
                                            }
                                            $ctrl_partner = $mv["clfoco"];
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1"></th>
                                        <th rowspan="1" colspan="1"></th>
                                        <th rowspan="1" colspan="1"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Scadenzario fornitori -->
                <div class="col-sm-6">
                    <div class="box gaz-home-scadenze">
                        <div class="box-header">
                            <h3 class="box-title"><?php echo $script_transl['sca_scafor']; ?></h3>
                        </div>
                        <div class="box-body">
                            <table id="fornitori" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="fornitori_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 300px;" aria-label="Rendering engine: activate to sort column descending"><?php echo $script_transl['sca_fornitore']; ?></th>
                                        <th class="sorting" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 120px;" aria-label="Browser: activate to sort column ascending"><?php echo $script_transl['sca_dare']; ?></th>
                                        <th class="sorting_asc" tabindex="0" aria-controls="fornitori" rowspan="1" colspan="1" style="width: 120px;" aria-sort="ascending" aria-label="Platform(s): activate to sort column ascending"><?php echo $script_transl['sca_scadenza']; ?></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- Scadenzario fornitori -->
                                    <?php
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
                                                echo "<td align='center'><span>" . $mv["expiry"] . "</span>" . gaz_format_date($mv["expiry"]) . "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1"></th>
                                        <th rowspan="1" colspan="1"></th>
                                        <th rowspan="1" colspan="1"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fine scadenzari -->

            <?php
        }
        ?>
        <div class="collapse navbar-collapse"> 
            <!-- per adesso lo faccio collassare in caso di small device anche se si potrebbe fare uno switch in verticale -->
            <?php
            $result = gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' company_id="' . $form['company_id'] . '" AND adminid="' . $admin_aziend["user_name"] . '" ', ' click DESC, last_use DESC', 0, 8);
            $res_last = gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' company_id="' . $form['company_id'] . '" AND adminid="' . $admin_aziend["user_name"] . '" ', ' last_use DESC, click DESC', 0, 8);

            if (gaz_dbi_num_rows($result) > 0) {
                while ($r = gaz_dbi_fetch_array($result)) {
                    $rref = explode('-', $r['transl_ref']);
                    $rl = gaz_dbi_fetch_array($res_last);
                    $rlref = explode('-', $rl['transl_ref']);
                    switch ($rref[1]) {
                        case 'm1':
                            require '../' . $rref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                            $rref_name = $transl[$rref[0]]['title'];
                            break;
                        case 'm2':
                            require '../' . $rref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                            $rref_name = $transl[$rref[0]]['m2'][$rref[2]][0];
                            break;
                        case 'm3':
                            require '../' . $rref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                            $rref_name = $transl[$rref[0]]['m3'][$rref[2]][0];
                            break;
                        case 'sc':
                            require '../' . $rref[0] . '/lang.' . $admin_aziend['lang'] . '.php';
                            $rref_name = $strScript[$rref[2]][$rref[3]];
                            break;
                        default:
                            $rref_name = 'Nome script non trovato';
                            break;
                    }
                    switch ($rlref[1]) {
                        case 'm1':
                            require '../' . $rlref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                            $rlref_name = $transl[$rlref[0]]['title'];
                            break;
                        case 'm2':
                            require '../' . $rlref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                            $rlref_name = $transl[$rlref[0]]['m2'][$rlref[2]][0];
                            break;
                        case 'm3':
                            require '../' . $rlref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                            $rlref_name = $transl[$rlref[0]]['m3'][$rlref[2]][0];
                            break;
                        case 'sc':
                            require '../' . $rlref[0] . '/lang.' . $admin_aziend['lang'] . '.php';
                            $rlref_name = $strScript[$rlref[2]][$rlref[3]];
                            break;
                        default:
                            $rlref_name = 'Nome script non trovato';
                            break;
                    }
                    ?>
                    <div class="row">
                        <div class="col-sm-6">
                            <a href="<?php
                            if ($r["link"] != "")
                                echo '../../modules' . $r["link"];
                            else
                                echo "&nbsp;";
                            ?>" type="button" class="btn btn-default btn-full" style="background-color: #<?php echo $r["color"]; ?>; font-size: 85%; text-align: left;">
                                <span ><?php echo $r["click"] . ' click - <b>' . $rref_name . '</b>'; ?></span></a>
                        </div>
                        <div class="col-sm-6">
                            <a href="<?php
                            if ($rl["link"] != "")
                                echo '../../modules' . $rl["link"];
                            else
                                echo "&nbsp;";
                            ?>" type="button" class="btn btn-default btn-full" style="background-color: #<?php echo $rl["color"]; ?>; font-size: 85%; text-align: left;">
                                <span ><?php
                                    echo gaz_time_from(strtotime($rl["last_use"])) . ' - <b>';
                                    if (is_string($rlref_name)) {
                                        echo $rlref_name;
                                    } else {
                                        echo "Errore nello script (array)";
                                    }
                                    echo '</b>';
                                    ?></span></a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class='admin_footer' align="center">
            <div > GAzie Version: <?php echo GAZIE_VERSION; ?> Software Open Source (lic. GPL)
                <?php echo $script_transl['business'] . " " . $script_transl['proj']; ?> 
                <a target="_new" title="<?php echo $script_transl['auth']; ?>" href="http://www.devincentiis.it"> http://www.devincentiis.it</a>
            </div>
            <div>
                <a href="http://gazie.sourceforge.net" target="_new" title="<?php echo $script_transl['devel']; ?>"><img src="../../library/images/gazie.gif" height="38" border="0"></a>
                <?php
                foreach ($script_transl['strBottom'] as $value) {
                    echo "<a href=\"" . $value['href'] . "\" title=\"" . $value['title'] . "\" target=\"_NEW\" ><img src=\"../../library/images/" . $value['img'] . "\" border=\"0\" ></a>\n";
                }
                ?>

            </div>
        </div>
        <div>
            <div>
                <?php
                if (file_exists("help/" . $admin_aziend['lang'] . "/admin_help.php")) {
                    include("help/" . $admin_aziend['lang'] . "/admin_help.php");
                }
                ?>
            </div>
        </div>
    </div>
</form>
<?php
require("../../library/include/footer.php");

/* questa parte sarebbe da mettere nel footer specifico del tema (library/theme/nome_tema/footer.php) 
 * in ognuno dei quali mettere una classe contenente, oltre al costrutto, anche le varie funzioni 
 * richiamabili alla fine dagli script php e comunque presenti sui footer di TUTTU i motori di template
 */
if ($admin_aziend['Abilit'] >= 8 && $schedule_view['val'] >= 1) {
    ?>
    <script src="../../library/theme/lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../../library/theme/lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script>
        $(function () {
            $("#clienti").DataTable({
                "oLanguage": {
                    "sUrl": "../../library/theme/lte/plugins/datatables/Italian.json"
                },
                "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, "Tutti"]],
                "iDisplayLength": 5,
                "order": [2, 'asc'],
                "filter": false,
				"responsive": true,
                "stateSave": true
            });
            $('#fornitori').DataTable({
                "oLanguage": {
                    "sUrl": "../../library/theme/lte/plugins/datatables/Italian.json"
                },
                "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, "Tutti"]],
                "iDisplayLength": 5,
                "order": [2, 'asc'],
                "filter": false,
				"responsive": true,
                "stateSave": true
            });
        });
    </script>
    <?php
}
?>
