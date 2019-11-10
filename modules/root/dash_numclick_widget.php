<div class="panel panel-info col-md-12" >
    <div class="box-header company-color">
	</div>
    <div class="img-containter">
    <!-- per adesso lo faccio collassare in caso di small device anche se si potrebbe fare uno switch in verticale -->
    <?php
    $result = gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' company_id="' . $form['company_id'] . '" AND adminid="' . $admin_aziend['user_name'] . '" ', ' click DESC, last_use DESC', 0, 8);
    if (gaz_dbi_num_rows($result) > 0) {
        while ($r = gaz_dbi_fetch_array($result)) {
            $rref = explode('-', $r['transl_ref']);
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
            ?>
            <div>
                    <a href="<?php
                    if ($r["link"] != "")
                        echo '../../modules' . $r["link"];
                    else
                        echo "&nbsp;";
                    ?>" type="button" class="btn btn-default btn-full" style="background-color: #<?php echo $r["color"]; ?>; font-size: 85%; text-align: left;">
                        <span ><?php echo $r["click"] . ' click - <b>' . $rref_name . '</b>'; ?></span></a>
            </div>
            <?php
        }
    }
    ?>
	</div>
</div>