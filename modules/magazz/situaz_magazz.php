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
$admin_aziend = checkAdmin();

if ( isset($_POST["Stampa"]) ) {
    header("Location: ../../modules/magazz/stampa_situaz.php");
}

require("../../library/include/header.php");
$script_transl = Headmain();
$passo = 1000;
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title']; ?> </div>
<?php
$recordnav = new recordnav($gTables['artico'], $where, $limit, $passo);
$recordnav->output();

?>
<form method="POST">
    <table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
        <tbody>
            <?php
            // creo l'array (header => campi) per l'ordinamento dei record
            $headers_artico = array("Codice" => "codice",
                "Descrizione" => "descri",
                "UmV" => "",
                "Pezzi in stock" => "",
                "Ordinato Cliente" => "",
                "Ordinato Fornitore" => "",
                "<input type='submit' class='btn btn-default btn-xs' name='Stampa' value='&nbsp;Stampa&nbsp;' />" => "" );

            $linkHeaders = new linkHeaders($headers_artico);
            $gForm = new magazzForm();

            $result = gaz_dbi_dyn_query("*", $gTables['artico'], "good_or_service=0", $orderby, $limit, $passo);
            echo '<tr>'. $linkHeaders->output() .'</tr>';
            while ($r = gaz_dbi_fetch_array($result)) {
                $totale = 0;
                $ordinatif = $gForm->get_magazz_ordinati($r['codice'], "AOR");
                $ordinatic = $gForm->get_magazz_ordinati($r['codice'], "VOR");
                $mv = $gForm->getStockValue(false, $r['codice']);
                $magval = array_pop($mv);
                $totale = $magval['q_g']-$ordinatic+$ordinatif;
                echo '<tr class="FacetDataTD">
	   			    <td width="5%"><a class="btn btn-xs btn-success btn-block" href="admin_artico.php?codice=' . $r["codice"] . '&amp;Update">
				    <i class="glyphicon glyphicon-edit"></i>&nbsp;' . $r["codice"] . '</a></td>';
                echo '	<td width="30%">
	   				<span class="gazie-tooltip" data-type="product-thumb" data-id="' . $r["codice"] . '" data-title="' . $r['annota'] . '">' . $r["descri"] . '</span>
                    </td><td align="center" title="">'.$r['unimis'].'</td>';
                echo '<td align="right">' . floatval($magval['q_g']) . ' </td>
                    <td align="center">' . $ordinatic . ' </td>
                    <td align="center">' . $ordinatif . ' </td>
                    <td align="right">'.$totale.'</td></tr>';
            }
            echo '<tr><td class="FacetFieldCaptionTD" colspan="10" align="right">&nbsp;</td></tr>';
            ?>
        </tbody>
    </table>
</form>
<?php
require("../../library/include/footer.php");
?>