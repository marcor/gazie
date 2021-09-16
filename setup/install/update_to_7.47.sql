UPDATE `gaz_config` SET `cvalue` = '141' WHERE `id` =2;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, '5', 'report_wharehouse.php', '', '', '10', '', '1'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_wharehouse.php'), 'admin_wharehouse.php?Insert', '', '', 15, '', 10  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )