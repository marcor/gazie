<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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

require("../../library/include/header.php");
$script_transl = HeadMain();

if (isset($_POST['hidden_req'])) { // accessi successivi allo script
    $form['hidden_req'] = $_POST["hidden_req"];
    $form['ritorno'] = $_POST['ritorno'];
} else {
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
}
?>
<form method="POST">
    <input type="hidden" value="<?php echo $form['hidden_req']; ?>" name="hidden_req" />
    <input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno" />
    <div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title']; ?></div>
    <div class="tab-content">
        <div id="lista" class="tab-pane fade in active">
            <div class="table-responsive">

                <table class="Tlarge table table-striped table-bordered table-condensed">
                    <tr>
                        <th class="FacetFieldCaptionTD">ID</th>
                        <th class="FacetFieldCaptionTD"><?php echo $script_transl['anno']; ?></th>
                        <th class="FacetFieldCaptionTD"><?php echo $script_transl['periodicita']; ?></th>            
                        <th class="FacetFieldCaptionTD"><?php echo $script_transl['trimestre_semestre']; ?></th>            
                        <th class="FacetFieldCaptionTD">File DTE</th>
                        <th class="FacetFieldCaptionTD">File DTR</th>
                        <th class="FacetFieldCaptionTD">File ZIP</th>
                            <?php
                            $result = gaz_dbi_dyn_query('*', $gTables['comunicazioni_dati_fatture'], "nome_file_ZIP LIKE '%DF_Z%'", 'anno DESC, trimestre_semestre DESC');
                            while ($row = gaz_dbi_fetch_array($result)) {
                                echo "<tr class=\"FacetDataTD\">";
                                echo "<td><a class=\"btn btn-xs btn-default\" href=\"comunicazione_dati_fatture.php?id=" . $row["id"] . "&Update\"><i class=\"glyphicon glyphicon-folder-open\"></i>&nbsp;&nbsp;" . $row["id"] . "</a> &nbsp</td>";
                                echo "<td align=\"center\">" . $row['anno'] . " &nbsp;</td>";
                                echo '<td align="center">' . $script_transl['periodicita_value'][$row['periodicita']] . ' &nbsp;</td>';
                                echo '<td align="center">' . $script_transl['trimestre_semestre_value'][$row['periodicita']][$row['trimestre_semestre']] . ' &nbsp;</td>';
                                echo "<td align=\"center\">" . $row['nome_file_DTE'] . " &nbsp;</td>";
                                echo "<td align=\"center\">" . $row['nome_file_DTR'] . " &nbsp;</td>";
                                echo '<td align="center"><a class="btn btn-xs btn-default" href="download_comunicazione_dati_fatture.php?id='.$row["id"].'">'. $row['nome_file_ZIP'] .'<i class="glyphicon glyphicon-download"></i></a> &nbsp;</td>';
                                echo "</tr>";
                            }
                            ?>
                </table>
            </div>
        </div>
    </div>

</form>
<?php
require("../../library/include/footer.php");
?>