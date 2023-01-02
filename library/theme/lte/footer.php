<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
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


#$admin_aziend = \GAzie\GAzie::factory()->getCheckAdmin();

require("../../modules/root/lang.".$admin_aziend['lang'].".php");

?>
</div>
</section>
<?php
// se viene visualizzata una pagina specifica non visualizzare il footer
$url = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
if ( $url!='ruburl.php' ) {
?>
    <footer class="main-footer">
        <?php
         // mostra le variabili $global e $server nella pagina
         echo "<div>";
          // solo quando verrà aggiornato KINT potremo utilizzarlo, tolto sulla 7.43
          // if ( $debug_active == true ) d($GLOBALS, $_SERVER);
         echo "</div>";
        ?>
        <div class="pull-right hidden-xs">
        <?php echo $strScript['admin.php']['auth']; ?>:  <a  target="_new" title="<?php echo $strScript['admin.php']['auth']; ?>" href="https://<?php echo $contact_link; ?>">https://<?php echo $contact_link; ?></a>
        </div>
Version <?php echo GAZIE_VERSION; ?>
    </footer>

    <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- Create the tabs -->
        <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
          <li class="active"><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
          <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
          <!-- Home tab content -->
          <div class="tab-pane active" id="control-sidebar-home-tab">

              <!--<form action="" method="post">
        <select class="changeStatus" name="changeStatus">
                <option value="0">Starting</option>
                <option value="1">Ongoing</option>
                <option value="2">Over</option>
        </select>
        <input class="projectId" type="hidden" name="projectId" value="<?php //echo $data['id'];?>"/>
               </form>-->
              <ul class="control-sidebar-menu">
                <?php
            $result   = gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' company_id="' . $admin_aziend['company_id'] . '" AND adminid="' . $admin_aziend["user_name"] . '" ', ' click DESC, last_use DESC', 0, 20);
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
                  <li>
                    <a href="<?php
                            if ($r["link"] != "")
                                echo '../../modules' . $r["link"];
                            else
                                echo "&nbsp;";
                            ?>">
                          <i class="menu-icon fa <?php echo get_rref_type( $r["link"] ); ?>" style="color:#<?php echo $r["color"]; ?>"></i>
                          <div class="menu-info">
                            <h4 class="control-sidebar-subheading">
                                <?php
                                    echo pulisci_rref_name( $rref_name );
                                ?>
                            </h4>
                            <p><?php echo $r["click"] . ' click'; ?></p>
                          </div>
                    </a>
                  </li>
                  <?php
                }
            }
            ?>
                </ul>

          </div><!-- /.tab-pane -->
          <!-- Stats tab content -->
          <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div><!-- /.tab-pane -->
          <!-- Settings tab content -->
          <div class="tab-pane" id="control-sidebar-settings-tab">
            <form method="post">
              <!--<h3 class="control-sidebar-heading">Impostazioni </h3>-->
              <?php
                printCheckbox( "Stile Fixed", "LTE_Fixed", "Attiva lo stile 'fisso'. Non puoi usare 'fisso' e 'boxed' insieme" );
                printCheckbox("Stile Boxed", "LTE_Boxed", "Attiva lo stile 'boxed'" );
                printCheckbox("Menu Ridotto", "LTE_Collapsed", "Collassa il menu principale" );
                printCheckbox("Menu Automatico", "LTE_Onhover", "Espandi automaticamente il menu" );
                printCheckbox("Sidebar Aperto", "LTE_SidebarOpen", "Mantieni la barra aperta" );
              ?>
              <div class='form-group'>
                  <a href="">Ripristina default</a>
              </div>
            </form>
          </div><!-- /.tab-pane -->
        </div>
      </aside>
      <?php
}
?>
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
    <script src="../../js/jquery.ui/jquery-ui.min.js"></script>
    <script><!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <script type="text/javascript">
        function processForm(el) {
            var checkbox = $(el);
            $.ajax( {
                type: 'POST',
                url: '../../modules/root/lte_post_config.php',
                data: { 'name': checkbox.attr('name'),
                        'val': checkbox.is(':checked'),
                        'desc': checkbox.attr('hint')
                },
                success: function(data) {
                    $('#message').html(data);
                }
            });
            //window.location.reload();
        }
        <?php
         // solo quando verrà aggiornato KINT potremo utilizzarlo, tolto sulla 7.43
         // mostra le variabili $global e $server nella pagina
         //if ( $debug_active == true ) d($GLOBALS, $_SERVER);
    ?>
    </script>
    <script src="../../js/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="../../library/theme/lte/plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <script src="../../library/theme/lte/plugins/fastclick/fastclick.min.js"></script>
    <script>
        var AdminLTEOptions = {
            sidebarExpandOnHover: <?php echo $config->getValue('LTE_Onhover'); ?>,
            enableBoxRefresh: true,
            enableBSToppltip: true
        };
    </script>

    <script src="../../library/theme/lte/adminlte/dist/js/app.js"></script>
    <script src="../../js/custom/jquery.ui.autocomplete.html.js"></script>
    <script src="../../js/custom/gz-library.js"></script>
    <script src="../../js/tinymce/tinymce.min.js"></script>
    <script src="../../js/custom/tinymce.js"></script>
    <script src="../../js/jquery.ui/datepicker-<?php echo substr($admin_aziend['lang'], 0, 2); ?>.js"></script>
    </body>
</html>
