UPDATE `gaz_config` SET `cvalue` = '142' WHERE `id` =2;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, '5', 'report_wharehouse.php', '', '', '10', '', '8'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_wharehouse.php'), 'admin_wharehouse.php?Insert', '', '', 15, '', 10  FROM `gaz_menu_script`;
UPDATE `gaz_config` SET `cvalue` = '142' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXcamp_artico` ADD `varieta` VARCHAR(40) NOT NULL COMMENT 'Varietà delle olive, da indicare per gli oli che in etichetta riporteranno il nome della varietà (monovarietali)' AFTER `categoria`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )