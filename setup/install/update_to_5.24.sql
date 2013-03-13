UPDATE `gaz_config` SET `cvalue` = '82' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXclfoco` ADD `sia_code` VARCHAR( 5 ) NOT NULL AFTER `iban` ;
-- STOP_WHILE( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)
