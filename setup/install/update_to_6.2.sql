UPDATE `gaz_config` SET `cvalue` = '89' WHERE `id` =2;
DELETE FROM `gaz_menu_module` WHERE `id_module` = 10 OR `id_module` = 11;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)