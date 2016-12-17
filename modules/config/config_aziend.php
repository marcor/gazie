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

if ( isset($_POST["elimina"]) ) {   //se non e' il primo accesso
    echo "ciaoooo";
    /*DROP VIEW `gaz_002movimenti`;
    DROP TABLE `gaz_002agenti`, `gaz_002agenti_forn`, `gaz_002aliiva`, `gaz_002artico`, `gaz_002assets`, `gaz_002assist`, `gaz_002banapp`, 
            `gaz_002body_text`, `gaz_002cash_register`, `gaz_002catmer`, `gaz_002caucon`, `gaz_002caumag`, `gaz_002clfoco`, 
            `gaz_002company_config`, `gaz_002company_data`, `gaz_002contract`, `gaz_002contract_row`, `gaz_002effett`, `gaz_002extcon`, 
            `gaz_002fae_flux`, `gaz_002files`, `gaz_002imball`, `gaz_002instal`, `gaz_002letter`, `gaz_002lotmag`, `gaz_002movmag`, 
            `gaz_002pagame`, `gaz_002paymov`, `gaz_002portos`, `gaz_002provvigioni`, `gaz_002ragstat`, `gaz_002rigbro`, `gaz_002rigdoc`, 
            `gaz_002rigmoc`, `gaz_002rigmoi`, `gaz_002sconti_articoli`, `gaz_002sconti_raggruppamenti`, `gaz_002spediz`, `gaz_002staff`, 
            `gaz_002staff_skills`, `gaz_002tesbro`, `gaz_002tesdoc`, `gaz_002tesmov`, `gaz_002vettor`;

    DELETE FROM `gaz_aziend` WHERE ((`codice` = '2'));*/

} else {
    if ( count($_POST) > 0 ) {
        foreach ($_POST as $key => $value) {
            gaz_dbi_put_row($gTables['company_config'], 'var', $key, 'val', $value);
        }
        header("Location: config_aziend.php?ok");
    }
}
require("../../library/include/header.php");
$script_transl = HeadMain();
$result = gaz_dbi_dyn_query("*", $gTables['company_config'], "1=1", ' id ASC', 0, 1000);

?>
<div align="center" class="FacetFormHeaderFont">
    <?php echo $script_transl['title']; ?><br>
    <?php
        //print_r ( $admin_aziend );
    ?>
</div>
  <div class="container divlarge">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="pill" href="#generale">Configurazione</a></li>
            <?php
            if ( $admin_aziend["Login"]=="amministratore" ) { //&& $admin_aziend["company_id"]!=1 ) {
                echo "<li><a data-toggle=\"pill\" href=\"#elimina\">Elimina azienda</a></li>";
            }
            ?>
        </ul>
    
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
                <div class="form-group lastrow">
                    <div class="col-sm-offset-11 col-sm-1">
                        <button type="submit" class="btn btn-default">Salva</button>
                    </div>
                </div>
                </div>
                </div>
            </form>
        </div>
    
        <div id="elimina" class="tab-pane fade">
            <form class="form-horizontal" method="post"> 
                <div class="FacetDataTD">
                    <div class="divgroup">
                      <div class="form-group">
                        <div class="col-sm-5 control-label">Attenzione ...</div>
                        <div class="col-sm-7">
                            <button class="btn btn-100" name="annulla" value="true">Annulla</button>
                            <button class="btn btn-100" name="elimina" value="true">Elimina</button>
                        </div>
                      </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>
<?php
require("../../library/include/footer.php");
?>