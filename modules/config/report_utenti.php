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
$admin_aziend=checkAdmin(9);
$company_choice = gaz_dbi_get_row($gTables['config'], 'variable', 'users_noadmin_all_company')['cvalue'];
require("../../library/include/header.php");
$script_transl = HeadMain('','','admin_utente');

?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['report']; ?></div>
<?php
$recordnav = new recordnav($gTables['admin'], $where, $limit, $passo);
$recordnav -> output();
?>
<table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
<?php
$headers_utenti = array  (
              $script_transl["user_name"] => "user_name",
              $script_transl['user_lastname'] => "Cognome",
              $script_transl['user_firstname'] => "Nome",
              $script_transl['Abilit'] => "Abilit",
              $script_transl['company'] => "",
			  'Privacy'=>'user_id',
              $script_transl['Access'] => "Access",
              $script_transl['delete'] => ""
            );
$linkHeaders = new linkHeaders($headers_utenti);
$linkHeaders -> output();
$result = gaz_dbi_dyn_query ('*', $gTables['admin'], $where, $orderby, $limit, $passo);
while ($a_row = gaz_dbi_fetch_array($result)) {
	// RESPONSABILE O INCARICATO: DIPENDE DAL LIVELLO DI ABILITAZIONE
	$ri_descr='stampa nomina INCARICATO trattamento dati personali';
	$regol_lnk='';
	$company = gaz_dbi_get_row($gTables['aziend'], 'codice', $a_row['company_id']);
	if ($a_row["Abilit"]>8){
		$company['ragso1']=$script_transl['all'];
		$ri_descr='stampa nomina RESPONSABILE trattamento dati personali';
		$regol_lnk=' _ <a title="stampa e/o edita il REGOLAMENTO per l’utilizzo e la gestione delle risorse informatiche" class="btn btn-xs btn-default" href="edit_privacy_regol.php?user_id=' . $a_row["user_id"] . '" target="_blank"><i class="glyphicon glyphicon-list"></i></a> ';
	}
	if ($company_choice>0){
		$company['ragso1']=$script_transl['all'];
	}
    echo "<tr class=\"FacetDataTD\">";
    echo "<td title=\"".$script_transl['update']."\"><a class=\"btn btn-xs btn-default\" href=\"admin_utente.php?user_name=".$a_row["user_name"]."&Update\">".$a_row["user_name"]." </a> &nbsp</td>";
    echo "<td>".$a_row["user_lastname"]." &nbsp;</td>";
    echo "<td>".$a_row["user_firstname"]." &nbsp;</td>";
    echo "<td align=\"center\">".$a_row["Abilit"]." &nbsp;</td>";
    echo "<td>".$company['ragso1']." &nbsp;</td>";
    // colonna stampa nomina trattamento dati personali 
    echo "<td align=\"center\"><a title=\"".$ri_descr."\" class=\"btn btn-xs btn-default\" href=\"stampa_nomina.php?user_id=" . $a_row["user_id"] . "\" target=\"_blank\"><i class=\"glyphicon glyphicon-eye-close\"></i></a>".
	$regol_lnk."
	</td>";
	// fine colonna privacy
    echo "<td align=\"center\">".$a_row["Access"]." &nbsp;</td>";
    echo "<td align=\"center\"><a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_utente.php?user_name=".$a_row["user_name"]."\"><i class=\"glyphicon glyphicon-remove\"></i></a></td>";
    echo "</tr>";
}
?>
</table>
<?php
require("../../library/include/footer.php");
?>