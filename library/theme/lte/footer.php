<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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
global $admin_aziend;

require("../../modules/root/lang.".$admin_aziend['lang'].".php");

function printCheckbox( $Caption, $varName, $Descrizione ) {
    global $config;
    echo "<div class='form-group'>";
    echo "<label class='control-sidebar-subheading'>";
    echo $Caption;
    if ( $config->getValue($varName)!="false" ) $val = "checked='".$config->getValue($varName)."'";
    else $val="";
    echo "<input type='checkbox' hint='".$Descrizione."' class='pull-right' name='".$varName."' ".$val." onclick='processForm(this)' />"; 
    echo "</label><p>".$Descrizione."</p></div>";
}

?>
</div>
</section>
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Version</b> <?php echo GAZIE_VERSION; ?>
        </div>
        <!--<strong>-->
        <b>GA</b>zie Version: <?php echo GAZIE_VERSION; ?> Software Open Source (lic. GPL) <?php echo $strScript['admin.php']['business'] . " " . $strScript['admin.php']['proj']; ?>
        <a  target="_new" title="<?php echo $strScript['admin.php']['auth']; ?>" href="http://www.devincentiis.it">http://www.devincentiis.it</a>
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
          <div class="tab-pane" id="control-sidebar-home-tab">
          
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
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->

    <!-- jQuery 2.1.4 -->
    <script src="../../js/jquery/jquery.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="../../js/jquery.ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
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
    </script>
    
    <!-- Bootstrap 3.3.5 -->
    <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
    <!-- Slimscroll -->
    <script src="../../library/theme/lte/adminlte/plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="../../library/theme/lte/adminlte/plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
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
  </body>
</html>