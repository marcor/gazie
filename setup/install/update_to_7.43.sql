UPDATE `gaz_config` SET `cvalue` = '137' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXrigdoc`	CHANGE COLUMN `prelis` `prelis` DECIMAL(17,8) NULL DEFAULT '0' AFTER `quanti`;
DROP IF EXISTS TABLE `gaz_XXXfornitore_magazzino`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )