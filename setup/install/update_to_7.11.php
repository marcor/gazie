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
	gaz_dbi_query("INSERT INTO `gaz_menu_module` SELECT MAX(id)+1 , ".$camp_mod['id'].", 'fitofarmaci.php', '', '', 6, '', 6  FROM `gaz_menu_module`");
	// cancello dal 3° livello il link spostato sul menù di 2° livello
	gaz_dbi_query("DELETE FROM `gaz_menu_script` WHERE `link` = 'update_fitofarmaci.php'");
	// aggiungo le nuove voci di menù di 3° livello
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='fitofarmaci.php'), 'admin_avv.php', '', '', 13, '', 1  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='fitofarmaci.php'), 'admin_colt.php', '', '', 14, '', 5  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='fitofarmaci.php'), 'report_fitofarmaci.php', '', '', 15, '', 10  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='fitofarmaci.php'), 'update_fitofarmaci.php', '', '', 11, '', 15  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='fitofarmaci.php'), 'update_fitofarmaci.php', '', '', 16, '', 20  FROM `gaz_menu_script`");
	echo "<p>Ho modificato il menù del modulo <b>Registro di campagna</b></p>";
}

/* CONVERSIONE VECCHI ORDINI CON NUOVI EVADIBILI PARZIALMENTE MA LA CONVERSIONE E' LIMITATA ALL'ANNO 2018
ALTER TABLE `gaz_001tesdoc`
	ADD INDEX `tipdoc` (`tipdoc`),
	ADD INDEX `numfat` (`numfat`),
	ADD INDEX `datfat` (`datfat`);

	ALTER TABLE `gaz_001effett`
	ADD INDEX `id_doc` (`id_doc`);

*/



$limit="99999999";
$passo="99999999";
$nu=0;
$result = gaz_dbi_dyn_query("*", $table_prefix.'_aziend', 1);

while ($row = gaz_dbi_fetch_array($result)) {
	$aziend_codice = sprintf("%03s", $row["codice"]);
	
	// inizio controlli presenza di alcuni indici altrimenti li creo perché senza di essi diverse query ricorsive sarebbero troppo lente in caso di tabelle con molti righi
	
	// id_tes su rigdoc
	$rk=gaz_dbi_query("SHOW KEYS FROM ". $table_prefix . "_" . $aziend_codice."rigdoc WHERE 1");
	$ex=false;	
	while ($vk = gaz_dbi_fetch_array($rk)) {
		if ($vk['Column_name'] == 'id_tes'){
			$ex=true;
		}
	}
	if (!$ex){
		gaz_dbi_query("ALTER TABLE ". $table_prefix . "_" . $aziend_codice."rigdoc	ADD INDEX `id_tes` (`id_tes`)");		
		echo "<p>Ho creato l'index <b>id_tes</b> su ". $table_prefix . "_" . $aziend_codice."rigdoc perché non esisteva</p>";
	}

	// id_doc su effett
	$rk=gaz_dbi_query("SHOW KEYS FROM ". $table_prefix . "_" . $aziend_codice."effett WHERE 1");
	$ex=false;	
	while ($vk = gaz_dbi_fetch_array($rk)) {
		if ($vk['Column_name'] == 'id_doc'){
			$ex=true;
		}
	}
	if (!$ex){
		gaz_dbi_query("ALTER TABLE ". $table_prefix . "_" . $aziend_codice."effett	ADD INDEX `id_doc` (`id_doc`)");		
		echo "<p>Ho creato l'index <b>id_doc</b> su ". $table_prefix . "_" . $aziend_codice."effett perché non esisteva</p>";
	}

	// tipdoc su tesdoc
	$rk=gaz_dbi_query("SHOW KEYS FROM ". $table_prefix . "_" . $aziend_codice."tesdoc WHERE 1");
	$ex=false;	
	while ($vk = gaz_dbi_fetch_array($rk)) {
		if ($vk['Column_name'] == 'tipdoc'){
			$ex=true;
		}
	}
	if (!$ex){
		gaz_dbi_query("ALTER TABLE ". $table_prefix . "_" . $aziend_codice."tesdoc	ADD INDEX `tipdoc` (`tipdoc`)");		
		echo "<p>Ho creato l'index <b>tipdoc</b> su ". $table_prefix . "_" . $aziend_codice."tesdoc perché non esisteva</p>";
	}

	// numfat su tesdoc
	$rk=gaz_dbi_query("SHOW KEYS FROM ". $table_prefix . "_" . $aziend_codice."tesdoc WHERE 1");
	$ex=false;	
	while ($vk = gaz_dbi_fetch_array($rk)) {
		if ($vk['Column_name'] == 'numfat'){
			$ex=true;
		}
	}
	if (!$ex){
		gaz_dbi_query("ALTER TABLE ". $table_prefix . "_" . $aziend_codice."tesdoc	ADD INDEX `numfat` (`numfat`)");		
		echo "<p>Ho creato l'index <b>numfat</b> su ". $table_prefix . "_" . $aziend_codice."tesdoc perché non esisteva</p>";
	}

	// datfat su tesdoc
	$rk=gaz_dbi_query("SHOW KEYS FROM ". $table_prefix . "_" . $aziend_codice."tesdoc WHERE 1");
	$ex=false;	
	while ($vk = gaz_dbi_fetch_array($rk)) {
		if ($vk['Column_name'] == 'datfat'){
			$ex=true;
		}
	}
	if (!$ex){
		gaz_dbi_query("ALTER TABLE ". $table_prefix . "_" . $aziend_codice."tesdoc	ADD INDEX `datfat` (`datfat`)");		
		echo "<p>Ho creato l'index <b>datfat</b> su ". $table_prefix . "_" . $aziend_codice."tesdoc perché non esisteva</p>";
	}

	// id_tes su rigmoc
	$rk=gaz_dbi_query("SHOW KEYS FROM ". $table_prefix . "_" . $aziend_codice."rigmoc WHERE 1");
	$ex=false;	
	while ($vk = gaz_dbi_fetch_array($rk)) {
		if ($vk['Column_name'] == 'id_tes'){
			$ex=true;
		}
	}
	if (!$ex){
		gaz_dbi_query("ALTER TABLE ". $table_prefix . "_" . $aziend_codice."rigmoc	ADD INDEX `id_tes` (`id_tes`)");		
		echo "<p>Ho creato l'index <b>id_tes</b> su ". $table_prefix . "_" . $aziend_codice."rigmoc perché non esisteva</p>";
	}
	// fine controlli - creazioni indici

	$rtesbro = gaz_dbi_dyn_query("*", $table_prefix . "_" . $aziend_codice."tesbro", "tipdoc='VOR'", "id_tes DESC"); 
	while ($rtb = gaz_dbi_fetch_array($rtesbro)) {
		$rrigbro = gaz_dbi_dyn_query("*", $table_prefix . "_" . $aziend_codice."rigbro", "id_tes=".$rtb["id_tes"], "id_rig DESC");
		while ($rrb = gaz_dbi_fetch_array($rrigbro)) {
			if ( $rrb["id_doc"]!=0 ) {
				$nu++;
				$res = gaz_dbi_query("UPDATE ".$table_prefix . "_" . $aziend_codice."rigdoc set id_order=".$rtb["id_tes"]." WHERE id_tes=".$rrb["id_doc"]." and codart='".$rrb["codart"]."';");
				if ( !$res ) {
					echo "errore UPDATE ".$table_prefix . "_" . $aziend_codice."rigdoc set id_order=".$rtb["id_tes"]." WHERE id_tes=".$rrb["id_doc"]." and codart='".$rrb["codart"]."';<br>";
				}
			}
		}
	}
}
echo "<p>Ho convertito <b>".$nu." righi di ordini</b> nella nuova versione evadibili parzialmente</p>";

?>
