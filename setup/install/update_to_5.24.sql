UPDATE `gaz_config` SET `cvalue` = '82' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXclfoco` ADD `sia_code` VARCHAR( 5 ) NOT NULL AFTER `iban` ;
ALTER TABLE `gaz_XXXeffett` ADD `protoc` INT( 9 ) NOT NULL AFTER `seziva`; 
TRUNCATE TABLE `gaz_XXXpaymov`; 
ALTER TABLE `gaz_XXXpaymov` CHANGE `id_tesdoc_ref` `id_tesdoc_ref` VARCHAR( 15 ) NOT NULL;
ALTER TABLE `gaz_XXXcaucon` ADD `pay_schedule` INT( 1 ) NOT NULL AFTER `operat`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)