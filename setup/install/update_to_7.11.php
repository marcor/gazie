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
	echo "Ho aggiunto una voce di menù al modulo <b>Registro di campagna</b>";
}
?>
