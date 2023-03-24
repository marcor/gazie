UPDATE `gaz_config` SET `cvalue` = '154' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='select_regiva.php'), 'protocol_renumbering.php', '', '', 16, '', 80  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXclfoco`	ADD INDEX (`descri`);
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `tiprig` `tiprig` INT(2) NOT NULL DEFAULT '0' AFTER `id_tes`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
