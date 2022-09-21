<?php
/*
   --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-2023 - Antonio Germani, Massignano (AP) - telefono +39 340 50 11 912
  (http://www.programmisitiweb.lacasettabio.it)

  --------------------------------------------------------------------------
   --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend = checkAdmin(8);
$gForm = new GazieForm();
$genclass="active";
$feedclass="";

if (isset($_POST['addElement'])){// se è stato richiesto di inserire un nuovo elemento feedback
  $genclass="";
  $feedclass="active";
  if (strlen($_POST['newElement'])>2){// se non è vuoto posso inserire
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
if (isset($_POST['updElement']) && intval($_POST['updElement'])>0){// se è stato richiesto di modificare un elemento feedback
  $genclass="";
  $feedclass="active";
  $upd=gaz_dbi_get_row($gTables['rental_feedback_elements'], 'id', intval($_POST['updElement']));
}
if (isset($_POST['SaveupdElement']) && intval($_POST['SaveupdElement'])>0){// se è stato richiesto di salvare la modifica di un elemento feedback
  $genclass="";
  $feedclass="active";
   $table = 'rental_feedback_elements';
    $set['element']=  mysqli_real_escape_string($link,substr($_POST['newElement'],0,64));
    $set['facility']=  intval($_POST['newFacility']);
    $set['status']=  "MODIFIED";
    $columns = array('element', 'facility', 'status');
    $codice=array();
    $codice[0]="id";
    $codice[1]=intval($_POST['SaveupdElement']);
    $newValue = array('element'=>$set['element'], 'facility'=>$set['facility'],'status'=>$set['status']);
    tableUpdate($table, $columns, $codice, $newValue);
}

if (count($_POST) > 1 && !isset($_POST['addElement']) && !isset($_POST['delElement']) && !isset($_POST['updElement']) && !isset($_POST['SaveupdElement'])) {
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
                    <button type="submit" class="btn btn-success" name="updElement" value="<?php echo $feedback["id"]; ?>">
                      <i class="glyphicon glyphicon-edit"> modifica</i>
                    </button>
                  </div>
                </div>
                <?php
              }
            }
              if (isset($_POST['updElement']) && intval($_POST['updElement'])>0){
                ?>
                <div class="row">
                  <div class="form-group" >
                    <div class="row">
                      <label for="inputElement" class="col-sm-5 control-label">Modifica struttura</label>
                        <div class="col-sm-7">
                        <?php
                        $gForm->selectFromDB('artico_group', 'newFacility', 'id_artico_group', $upd['facility'], false, 0, ' - ', 'descri', '', 'col-sm-8', array('value'=>0,'descri'=>''), 'tabindex="18" style="max-width: 250px;"');
                        ?>
                        </div>
                    </div>
                    <label for="inputElement" class="col-sm-5 control-label">Modifica elemento feedback&nbsp;<i class="glyphicon glyphicon-flag" title="accetta tag lingue (<it></it>)"></i></label>
                    <div class="col-sm-7">
                      <input type="text" name="newElement" value="<?php echo $upd['element'];?>">
                      <button type="submit" class="btn btn-success" name="SaveupdElement" value="<?php echo $upd['id']; ?>">
                        <i class="glyphicon glyphicon-record"> Modifica elemento</i>
                      </button>
                    </div>
                  </div>
                </div><!-- chiude row  -->
                <?php
              }else{

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
                  <label for="inputElement" class="col-sm-5 control-label">Inserisci nuovo elemento feedback&nbsp;<i class="glyphicon glyphicon-flag" title="accetta tag lingue (<it></it>)"></i></label>
                  <div class="col-sm-7">
                    <input type="text" name="newElement">
                    <button type="submit" class="btn btn-success" name="addElement">
                      <i class="glyphicon glyphicon-plus"> Aggiungi elemento</i>
                    </button>
                  </div>
                </div>
              </div><!-- chiude row  -->
              <?php
              }

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
