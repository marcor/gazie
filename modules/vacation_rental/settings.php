<?php
/*
  --------------------------------------------------------------------------
Copyright (C) - Antonio Germani Massignano (AP) https://www.lacasettabio.it - telefono +39 340 50 11 912
  --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin(8);
$gForm = new GazieForm();
$genclass="active";
$feedclass="";

if (isset($_POST['addElement'])){// se è stato richiesto di inserire un nuovo elemento feedback
  $genclass="";
  $feedclass="active";
  if (strlen($_POST['newElement']>2)){// se non è vuoto posso inserire
    $table = 'rental_feedback_elements';
    $set['element']=  mysqli_real_escape_string($link,substr($_POST['newElement'],0,64));
    $set['facility']=  intval($_POST['newFacility']);
    $set['status']=  "CREATED";
    $columns = array('element', 'facility', 'status');
    tableInsert($table, $columns, $set);
  }
}
if (isset($_POST['delElement']) && intval($_POST['delElement'])>0){// se è stato richiesto di cancellare un elemento feedback
  $genclass="";
  $feedclass="active";
  if (!gaz_dbi_get_row($gTables['rental_feedback_scores'], 'element_id', intval($_POST['delElement']))){// se l'elemento non è mai stato usato lo posso cancellare
    gaz_dbi_del_row($gTables['rental_feedback_elements'], 'id', intval($_POST['delElement']));
  }else{// altrimenti segnalo l'impossibilità
    echo 'Non posso cancellare l\'elemento perché ad esso risulta associato almeno un feedback';
  }
}
if (count($_POST) > 1 && !isset($_POST['addElement']) && !isset($_POST['delElement']) ) {
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
$feedbacks = gaz_dbi_query("SELECT * FROM ".$gTables['rental_feedback_elements']." LEFT JOIN " . $gTables['artico_group'] . " ON " . $gTables['rental_feedback_elements'] . ".facility = " . $gTables['artico_group'] . ".id_artico_group ORDER BY id ASC");

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
  <li class="<?php echo $genclass; ?>"><a data-toggle="pill" href="#generale">Configurazione</a></li>
  <li class="<?php echo $feedclass; ?>"><a data-toggle="pill" href="#feedback"><b>Recensioni</b></a></li>
  <li style="float: right;"><div class="btn btn-warning" id="upsave">Salva</div></li>
</ul>
<?php

?>
    <div class="tab-content">

      <div id="generale" class="tab-pane fade in <?php echo $genclass; ?>">
        <form method="post" id="sbmt-form">
<?php     if (isset($_GET["ok_insert"])) { ?>
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

          <div id="feedback" class="tab-pane fade in <?php echo $feedclass; ?>">
            <form method="post" id="feedback">
            <div class="row text-info bg-info">
              ELEMENTI DEI FEEDBACKS PER GLI ALLOGGI
            </div><!-- chiude row  -->
            <?php
            if (gaz_dbi_num_rows($feedbacks) > 0) {
              foreach ($feedbacks as $feedback) {
                ?>
                <div class="row">
                  <div class="form-group" >
                    <label for="existElement" class="col-sm-5 control-label"><?php echo $feedback["element"]; ?></label>
                    <?php if (intval($feedback["facility"])>0){
                      ?>
                      <span> - riservato alla struttura: <?php echo $feedback["facility"]," ",$feedback["descri"]; ?></span>
                      <?php
                    }else{
                      ?>
                      <span> - tutte le strutture</span>
                      <?php
                    }
                    ?>
                    <button type="submit" class="btn btn-success" name="delElement" value="<?php echo $feedback["id"]; ?>">
                      <i class="glyphicon glyphicon-minus"> Elimina elemento</i>
                    </button>
                  </div>
                </div>
                <?php
              }
            }
              ?>
              <div class="row">
                <div class="form-group" >
                  <div class="row">
                    <label for="inputElement" class="col-sm-5 control-label">Inserisci eventuale struttura</label>
                      <div class="col-sm-7">
 <?php
                      $gForm->selectFromDB('artico_group', 'newFacility', 'id_artico_group', 0, false, 0, ' - ', 'descri', '', 'col-sm-8', array('value'=>0,'descri'=>''), 'tabindex="18" style="max-width: 250px;"');
                      ?>
                      </div>
                  </div>
                  <label for="inputElement" class="col-sm-5 control-label">Inserisci nuovo elemento feedback</label>
                  <div class="col-sm-7">
                    <input type="text" name="newElement">
                    <button type="submit" class="btn btn-success" name="addElement">
                      <i class="glyphicon glyphicon-plus"> Aggiungi elemento</i>
                    </button>
                  </div>
                </div>
              </div><!-- chiude row  -->
              <?php
            ?>


            <?php

            ?>

            </form>
          </div><!-- chiude feedback  -->
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
