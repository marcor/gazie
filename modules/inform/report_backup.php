<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin(9);
require("../root/lib.function.php");
$checkUpd = new CheckDbAlign;
//
// Verifica i parametri della chiamata.
//
require("../../library/include/header.php");
$script_transl = HeadMain();

if (isset($_POST['hidden_req'])) { // accessi successivi allo script
    $form['hidden_req'] = $_POST["hidden_req"];
    $form['ritorno'] = $_POST['ritorno'];
    $form['do_backup'] = $_POST["do_backup"];
    if (isset($_POST['save_config'])) { // ho chiesto la modifica della configurazione
        $nv = filter_input(INPUT_POST, 'backup_mode');
        $checkUpd->backupMode($nv); // passando un valore alla stessa funzione faccio l'update
    }
} else {
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
    $form['do_backup'] = 0;
}
$bm = $checkUpd->backupMode();
?>
<div align="center" class="FacetFormHeaderFont">
    <?php echo $script_transl['title']; ?>
</div>
<form method="POST">
    <div class="container">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="pill" href="#lista"><?php echo $script_transl['title']; ?></a></li>
            <li><a data-toggle="pill" href="#config">Configurazione</a></li>
        </ul>
    </div>

    <input type="hidden" name="do_backup" value="1">
    <input type="hidden" value="<?php echo $form['hidden_req']; ?>" name="hidden_req" />
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno" />

    <div class="tab-content">
        <div id="lista" class="tab-pane fade in active">

            <table class="Tlarge">
                <tr>
                    <th class="FacetFieldCaptionTD"><?php echo $script_transl['id']; ?></th>
                    <th class="FacetFieldCaptionTD"><?php echo $script_transl['name']; ?></th>
                    <th class="FacetFieldCaptionTD"><?php echo $script_transl['size']; ?></th>            
                    <th class="FacetFieldCaptionTD"><?php echo $script_transl['rec']; ?></th>
                    <th class="FacetFieldCaptionTD"><?php echo $script_transl['dow']; ?></th>
                    <th class="FacetFieldCaptionTD" align="center"><?php echo $script_transl['delete']; ?></th>
                </tr>
                <?php
                $interval = 0;
                $files = array();
                if ($handle = opendir('../../data/files/backups/')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && strpos($file, ".gaz")) {
                            $files[filemtime('../../data/files/backups/' . $file)] = $file;
                        }
                    }
                    closedir($handle);
                    krsort($files);
                    $reallyLastModified = end($files);
                    $index = 0;
                    foreach ($files as $file) {
                        $id = substr($file, 5, 12);
                        if ($index < 30) {
                            ?>
                            <tr><td class="FacetDataTD"><a class="btn btn-xs btn-default" href="">
                                        <?php echo $id; ?>
                                    </a></td>
                                <td class="FacetDataTD">
                                    <?php echo $file; ?>
                                </td>
                                <td class="FacetDataTD">
                                    <?php echo formatSizeUnits(filesize('../../data/files/backups/' . $file)); ?>
                                </td>
                                <td align="center" class="FacetDataTD">
                                    <a class="btn btn-xs btn-default" href=""><i class="glyphicon glyphicon-repeat"></i></a>
                                </td>
                                <td align="center" class="FacetDataTD">
                                    <a class="btn btn-xs btn-default" href=""><i class="glyphicon glyphicon-download"></i></a>
                                </td>
                                <td align="center" class="FacetDataTD">
                                    <a class="btn btn-xs btn-default" href="delete_backup.php?id=<?php echo $id ?>"><i class="glyphicon glyphicon-remove"></i></a>
                                </td>
                            </tr>
                            <?php
                            $index++;
                        } else {
                            unlink("../../data/files/backups/" . $file);
                        }
                    }
                }
                ?>
            </table>
        </div>
        <div id="config" class="tab-pane fade">
            <div class="Tlarge FacetDataTD div-config div-table">
                <div class="div-table-row">
                    <div class="div-table-col">
                        <?php
                        echo $script_transl['backup_mode'] . ': <br/>';

                        foreach ($script_transl['backup_mode_value'] as $k => $v) {
                            echo ' ' . $v . ' <input type="radio" name="backup_mode" value="' . $k . '"';
                            if ($bm == $k) {
                                echo ' checked ';
                            }
                            echo '/>';
                        }
                        ?>
                    </div>
                </div>
                <div class="div-table-row">
                    <div class="div-table-col" >
                        <input type="submit" name="save_config" value="<?php echo $script_transl['update']; ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
</div><!-- chiude div container role main --></body>
</html>