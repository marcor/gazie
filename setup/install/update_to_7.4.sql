UPDATE `gaz_config` SET `cvalue` = '98' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXpagame` CHANGE `incaut` `incaut` CHAR(9) NOT NULL DEFAULT '';
ALTER TABLE `gaz_XXXpagame` ADD `pagaut` INT(9) NULL DEFAULT 0 AFTER `incaut`;
UPDATE `gaz_XXXpagame` SET `pagaut`=(SELECT `cassa_` FROM `gaz_aziend` WHERE `codice`= CONVERT('XXX',UNSIGNED INTEGER) LIMIT 1) WHERE `incaut` = 'S';
UPDATE `gaz_XXXpagame` SET `incaut`=(SELECT `cassa_` FROM `gaz_aziend` WHERE `codice`= CONVERT('XXX',UNSIGNED INTEGER) LIMIT 1) WHERE `incaut` = 'S';
ALTER TABLE `gaz_XXXpagame` CHANGE `incaut` `incaut` INT(9) NOT NULL DEFAULT 0;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
