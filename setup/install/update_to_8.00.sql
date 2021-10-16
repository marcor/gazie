UPDATE `gaz_config` SET `cvalue` = '143' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXcamp_mov_sian` CHANGE `varieta` `varieta` VARCHAR(250) NOT NULL COMMENT 'Campo descrittivo della varietà da utilizzare per il campo note nel registro telematico oli SIAN';
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )