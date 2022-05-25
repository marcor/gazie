<?php
/*
  --------------------------------------------------------------------------
Copyright (C) - Antonio Germani Massignano (AP) https://www.lacasettabio.it - telefono +39 340 50 11 912
  --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin(8);

if (count($_POST) > 1) {
  $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  foreach ($_POST as $k => $v) {
    $value=filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $key=filter_var($k, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    gaz_dbi_put_row($gTables['company_config'], 'var', $key, 'val', $value);
  }
  header("Location: settings.php?ok_insert");
  exit;
}
require("../../library/include/header.php");
$script_transl = HeadMain();

$general = gaz_dbi_dyn_query("*", $gTables['company_config'], " var LIKE 'vacation%'", ' id ASC', 0, 1000);
?>
<div align="center" class="FacetFormHeaderFont">
    <?php echo $script_transl['title']; ?><br>
</div>
<div class="panel panel-default gaz-table-form div-bordered">
  <div class="container-fluid">
<?php
$address_for_fae =gaz_dbi_get_row($gTables['company_config'], 'var', 'pecsdi_address_for_fae');
if (trim($address_for_fae)==''){
  $address_for_fae=$admin_aziend['pec'];
}

?>
<ul class="nav nav-pills">
  <li class="active"><a data-toggle="pill" href="#generale">Configurazione</a></li>
  <li class=""><a data-toggle="pill" href="#email"><b>Custom <?php echo $address_for_fae; ?></b></a></li>
  <li style="float: right;"><div class="btn btn-warning" id="upsave">Salva</div></li>
</ul>
<?php

?>
    <div class="tab-content">
        <div id="generale" class="tab-pane fade in active">
        <form method="post" id="sbmt-form">
<?php        if (isset($_GET["ok_insert"])) { ?>
            <div class="alert alert-success text-center" role="alert">
                <?php echo "Le modifiche sono state salvate correttamente<br/>"; ?>
            </div>
        <?php }
        if (gaz_dbi_num_rows($general) > 0) {
          
          ?>
            <div class="row text-info bg-info">
IMPOSTAZIONI GENERALI PER TUTTI GLI ALLOGGI E TUTTE LE STRUTTURE
            </div><!-- chiude row  -->
            
          <?php
            while ($r = gaz_dbi_fetch_array($general)) {
                ?>
                <div class="row">
                  <div class="form-group" >
                    <label for="input<?php echo $r["id"]; ?>" class="col-sm-5 control-label"><?php echo $r["description"]; ?></label>
                    <div class="col-sm-7">
                        <?php
                            ?>
                            <input type="<?php
                            if (strpos($r["var"], "psw") === false) {
                                echo "text";
                            } else {
                                echo "password";
                            }
                            ?>" class="form-control input-sm" id="input<?php echo $r["id"]; ?>" name="<?php echo $r["var"]; ?>" placeholder="<?php echo $r["var"]; ?>" value="<?php echo $r["val"]; ?>">
                    </div>
                  </div>
                </div><!-- chiude row  -->
                <?php
            }
        }
        
        ?>
        <div class="row">
            <div class="form-group" >
                <label class="col-sm-5 control-label"></label>
                <div  style="float: right;">
                    <button type="submit" class="btn btn-warning">Salva</button>
                </div>
            </div>
        </div>
        </form>
    </div><!-- chiude generale  -->
    <div id="email" class="tab-pane fade">
			<div>Da utilizzare per impostazioni future.
        </div>
		</br>
    
    </div><!-- chiude email  -->
  </div><!-- chiude tab-content  -->
 </div><!-- chiude container-fluid  -->
</div><!-- chiude panel  -->
<script>

$( "#upsave" ).click(function() {
    $( "#sbmt-form" ).submit();
});
</script>
<?php
require("../../library/include/footer.php");
?>
