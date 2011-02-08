UPDATE `gaz_config` SET `cvalue` = '71' WHERE `id` =2;
DELETE FROM `gaz_menu_script` WHERE `id` = 52;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico` DROP `esiste`,  DROP `valore`;
ALTER TABLE `gaz_XXXcaumag` DROP `upesis`;
-- STOP_WHILE( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)