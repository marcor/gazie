UPDATE `gaz_config` SET `cvalue` = '141' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_artico.php'), 'admin_group.php?Insert', '', '', 14, '', 2  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )