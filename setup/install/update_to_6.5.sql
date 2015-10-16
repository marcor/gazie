UPDATE `gaz_config` SET `cvalue` = '91' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXaliiva` ADD `taxstamp` INT(1) NOT NULL AFTER `aliquo`;
UPDATE `gaz_XXXaliiva` SET `taxstamp` = '1' WHERE `aliquo` <= 0.1;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)