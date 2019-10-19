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
	// aggiungo una voce al menù_module (2°livello)
	gaz_dbi_query("INSERT INTO `gaz_menu_module` SELECT MAX(id)+1 , ".$camp_mod['id'].", 'admin_sian.php', '', '', 7, '', 7  FROM `gaz_menu_module`");
	// aggiungo le nuove voci di menù di 3° livello
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='admin_sian.php'), 'rec_stocc.php', '', '', 17, '', 1  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='admin_sian.php'), 'admin_sian_files.php', '', '', 19, '', 5  FROM `gaz_menu_script`");
	gaz_dbi_query("INSERT INTO `gaz_menu_script` SELECT MAX(id)+1 , (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='admin_sian.php'), 'stabilim.php', '', '', 18, '', 10  FROM `gaz_menu_script`");
	echo "<p>Ho modificato il menù del modulo <b>Registro di campagna</b></p>";
}

$result = gaz_dbi_dyn_query("*", $table_prefix.'_aziend', 1);
while ($row = gaz_dbi_fetch_array($result)) {
	$aziend_codice = sprintf("%03s", $row["codice"]);
	// inizio controlli presenza di indici altrimenti li creo perché senza di essi le query ricorsive sarebbero troppo lente in caso di tabelle con molti righi
	$idx=array(0=>array('ref'=>'company_data','var'=>'company_data','description'=>'company_data')); // indicizzo la colonna data di registrazione dei movimenti contabili per poter avere libro giornale e partitari velocemente
	foreach($idx as $vi){
		foreach($vi as $k=>$v){
			$rk=gaz_dbi_query("SHOW KEYS FROM ". $table_prefix . "_" . $aziend_codice.$v." WHERE 1");
			$ex=false;	
			while ($vk = gaz_dbi_fetch_array($rk)) {
				if ($vk['Column_name'] == $k){
					$ex=true;
				}
			}
			if (!$ex){
				gaz_dbi_query("ALTER TABLE ". $table_prefix . "_" . $aziend_codice.$v." ADD INDEX `".$k."` (`".$k."`)");		
				echo "<p>Ho creato l'index <b>".$k."</b> su ". $table_prefix . "_" . $aziend_codice.$v." perché non esisteva</p>";
			}
		}
	}
	// fine controlli - creazioni indici mancanti
}
?>
