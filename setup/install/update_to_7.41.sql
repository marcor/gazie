UPDATE `gaz_config` SET `cvalue` = '136' WHERE `id` =2;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, '8', 'reconstruction_schedule.php', '', '', '8', '', '20'  FROM `gaz_menu_module`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )