<?php 
if (isset($_SESSION['table_prefix'])) {
    $table_prefix = substr($_SESSION['table_prefix'], 0, 12);
} elseif (isset($_GET['tp'])) {
    $table_prefix = filter_var(substr($_GET['tp'], 0, 12), FILTER_SANITIZE_MAGIC_QUOTES);
} else {
    $table_prefix = filter_var(substr($table_prefix, 0, 12), FILTER_SANITIZE_MAGIC_QUOTES);
}
// se ho il modulo "camp" attivo allora aggiungo una voce al menù
$camp_mod = gaz_dbi_get_row($table_prefix.'_module','name', 'camp');

if ($camp_mod){
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_movmag.php' AND `id_module`> 6), 'calc_prod.php', '', '', 12, '', 15  FROM `gaz_menu_script`");
	// aggiungo una voce al menù_module (2°livello)
	gaz_dbi_query("INSERT INTO `gaz_menu_module` SELECT MAX(id)+1 , ".$camp_mod['id'].", 'report_fitofarmaci.php', '', '', 6, '', 6  FROM `gaz_menu_module`");
	// cancello dal 3° livello il link spostato sul menù di 2° livello
	gaz_dbi_query("DELETE FROM `gaz_menu_script` WHERE `link` = 'update_fitofarmaci.php'");
	// aggiungo le 3 nuove voci di menù di 3° livello
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_fitofarmaci.php'), 'admin_avv.php', '', '', 13, '', 1  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_fitofarmaci.php'), 'admin_colt.php', '', '', 14, '', 5  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_fitofarmaci.php'), 'admin_usofito.php', '', '', 15, '', 10  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_fitofarmaci.php'), 'update_fitofarmaci.php', '', '', 11, '', 15  FROM `gaz_menu_script`");
	echo "Ho modificato il menù del modulo <b>Registro di campagna</b>";
}
?>
