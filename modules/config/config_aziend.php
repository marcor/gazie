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

require("../../library/include/datlib.inc.php");

$admin_aziend = checkAdmin(9);

if ( count($_POST) > 0 ) {
    foreach ($_POST as $key => $value) {
        gaz_dbi_put_row($gTables['company_config'], 'var', $key, 'val', $value);
    }
    header("Location: config_aziend.php?ok");
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$result = gaz_dbi_dyn_query("*", $gTables['company_config'], "1=1", ' id ASC', 0, 1000);
?>
<div align="center" class="FacetFormHeaderFont">
    <?php echo $script_transl['title']; ?>
</div>
    <div class="container divlarge">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="pill" href="#generale">Configurazione</a></li>
            <!--<li><a data-toggle="pill" href="#email">Email</a></li>-->
        </ul>
    </div>
    <div class="tab-content divlarge divborder">
        <div id="generale" class="tab-pane fade in active">
            <form class="form-horizontal" method="post"> 
                <div class="FacetDataTD">
                    <div class="divgroup">
                    <?php if ( isset($_GET["ok"]) ) { ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <?php echo "Le modifiche sono state salvate correttamente<br/>"; ?>
                    </div>
                    <?php } ?>
                <?php
                if (gaz_dbi_num_rows($result) > 0) {
                    while ($r = gaz_dbi_fetch_array($result)) {
                    ?>
                    
                    <div class="form-group">
                        <label for="input<?php echo $r["id"];?>" class="col-sm-5 control-label"><?php echo $r["description"]; ?></label>
                        <div class="col-sm-7">
                            <input type="<?php 
								if ( strpos($r["var"],"pass")===false ) {
									echo "text";
								} else {
									echo "password";
								}	
								?>" class="form-control input-sm" id="input<?php echo $r["id"];?>" name="<?php echo $r["var"];?>" placeholder="<?php echo $r["var"];?>" value="<?php echo $r["val"]; ?>">
                        </div>
                    </div>
                    <?php
                    }
                }
                ?>                    
                <div class="form-group">
                    <div class="col-sm-offset-11 col-sm-1">
                        <button type="submit" class="btn btn-default">Salva</button>
                    </div>
                </div>
                </div>
                </div>
            </form>
        </div>
    </div>
<?php
require("../../library/include/footer.php");
?>