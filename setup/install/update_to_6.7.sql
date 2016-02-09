UPDATE `gaz_config` SET `cvalue` = '93' WHERE `id` =2;
ALTER TABLE `gaz_menu_usage` ADD `color` VARCHAR(6) NOT NULL DEFAULT '5cb85c' AFTER `click`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 2, 'select_docforlist.php', '', '', 36, '', 9 FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXeffett` ADD COLUMN `cigcup` VARCHAR(40) NOT NULL AFTER `id_con`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
