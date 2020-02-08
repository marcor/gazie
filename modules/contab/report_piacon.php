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

if (!isset($_POST['annrip'])) {
    $_POST['annrip'] = date("Y");
}
// INIZIO determinazione limiti di date
$final_date = intval($_POST['annrip']) . '1231';
$rs_last_opening = gaz_dbi_dyn_query("*", $gTables['tesmov'], "caucon = 'APE' AND datreg <= " . $final_date, "datreg DESC", 0, 1);
$last_opening = gaz_dbi_fetch_array($rs_last_opening);
if ($last_opening) {
    $date_ini = substr($last_opening['datreg'], 0, 4) . substr($last_opening['datreg'], 5, 2) . substr($last_opening['datreg'], 8, 2);
} else {
    $date_ini = '20040101';
}
// FINE determinazione limiti di date

require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<style>
.collapsible
      {
            cursor:pointer;
      } 
</style>
<div class="FacetFormHeaderFont text-center"><?php echo $script_transl['title']; ?></div>
<div class="alert alert-danger text-center" role="alert"><?php echo $script_transl['msg1']; ?></div>

<form method="POST">
	<div class="table-responsive">
    <table class="table_piacon table table-striped table-bordered table-condensed table-responsive">
        <thead>
            <tr class="tr_piacon">
                <?php
                foreach ($script_transl['header'] as $k => $v) {
                    echo '				<th class="FacetFieldCaptionTD">' . $k . '</th>';
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            echo '			<tr>
					<td colspan="8" class="FacetDataTD text-right">' . $script_transl['msg2'] . ' : <select name="annrip" class="FacetSelect" onchange="this.form.submit();">';
            for ($counter = date("Y") - 3; $counter <= date("Y"); $counter++) {
                $selected = "";
                if ($counter == $_POST['annrip']) {
                    $selected = ' selected=""';
                }
                echo '						<option value="' . $counter . '"' . $selected . '>' . $counter . '</option>';
            }
            echo '					</select>
					</td>
				</tr>';
            $where = "    (codice < " . $admin_aziend['mascli'] . "000001 OR codice > " . $admin_aziend['mascli'] . "999999)
          AND (codice < " . $admin_aziend['masfor'] . "000001 OR codice > " . $admin_aziend['masfor'] . "999999)";

            $select = " SUM(import*(darave='D')) AS dare, 
			SUM(import*(darave='A')) AS avere";

            $table = $gTables['rigmoc'] . " LEFT JOIN " . $gTables['tesmov'] . " ON " . $gTables['rigmoc'] . ".id_tes = " . $gTables['tesmov'] . ".id_tes ";

            $where2 = " AND datreg BETWEEN " . $date_ini . " AND " . $final_date . " GROUP BY codcon";

            $rs = gaz_dbi_dyn_query('codice,descri', $gTables['clfoco'], $where, 'codice');

            $collapse = 0;
            
            $css_class = array ("gaz-attivo","gaz-passivo","gaz-costi","gaz-ricavi","gaz-transitori");
            
            while ($r = gaz_dbi_fetch_array($rs)) {
                $r2 = array('dare' => 0, 'avere' => 0);
                $rs2 = gaz_dbi_dyn_query($select, $table, 'codcon=' . $r['codice'] . $where2, 'codcon');
                if ($rs2) {
                    $r2 = gaz_dbi_fetch_array($rs2);
                }
                $color_class = $css_class[substr($r["codice"],0,1)-1];
                if (substr($r["codice"], 3) == '000000') {
                    $collapse = $r["codice"];
                    echo '<tr class="collapsible" data-toggle="collapse" data-target=".' . $collapse . '">	
			<td class="'.$color_class.'">
				<a class="btn btn-xs btn-default btn-edit" href="admin_piacon.php?Update&amp;codice=' . $r["codice"] . '" title="' . $script_transl['edit_master'] . '" >
					<i class="glyphicon glyphicon-edit"></i>&nbsp;' . substr($r["codice"], 0, 3) . '
				</a>
			</td>
			<td class="'.$color_class.'"></td>
			<td class="'.$color_class.' text-danger" colspan="5"><strong><i class="glyphicon glyphicon-list"></i> ' . $r["descri"] . '</strong></td>
			<td class="'.$color_class.' text-center">
				<a class="btn btn-xs btn-default btn-elimina" href="delete_piacon.php?codice=' . $r["codice"] . '">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
			</td>
			</tr>';
                } else {
                    echo '<tr class="' . $collapse.' collapse tr_piacon" aria-expanded="false">
			<td class="noborder tr_piacon"> </td>
			<td class="'.$color_class.'">
				<a class="btn btn-xs btn-default" href="admin_piacon.php?Update&amp;codice=' . $r["codice"] . '" title="' . $script_transl['edit_account'] . '">
					<i class="glyphicon glyphicon-edit"></i>&nbsp;' . substr($r["codice"], 3) . '
				</a>
			</td>
			<td class="'.$color_class.'">' . $r["descri"] . ' </td>
			<td class="'.$color_class.' text-right">' . gaz_format_number($r2["dare"]) . ' </td>
			<td class="'.$color_class.' text-right">' . gaz_format_number($r2["avere"]) . ' </td>
			<td class="'.$color_class.' text-right">' . gaz_format_number($r2["dare"] - $r2["avere"]) . ' </td>
			<td class="'.$color_class.' text-center" title="Visualizza e stampa il paritario">
				<a class="btn btn-xs btn-default" href="select_partit.php?id=' . $r["codice"] . '" target="_blank">
					<i class="glyphicon glyphicon-check"></i>&nbsp;<i class="glyphicon glyphicon-print"></i>
				</a>
			</td>
			<td class="'.$color_class.' text-center">
				<a class="btn btn-xs btn-default btn-elimina" href="delete_piacon.php?codice=' . $r["codice"] . '">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
			</td>
			</tr>';
                }
            }
            ?>
        </tbody>
    </table></div>
</form>
<?php
require("../../library/include/footer.php");
?>