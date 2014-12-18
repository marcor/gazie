UPDATE `gaz_config` SET `cvalue` = '87' WHERE `id` =2;
UPDATE `gaz_admin` SET `style` = 'default.css' WHERE 1;
ALTER TABLE `gaz_aziend` ADD `taxstamp_limit` DECIMAL(5,2) NOT NULL AFTER `acciva`;
UPDATE `gaz_aziend` SET `taxstamp_limit` = 77.47 WHERE 1; 
ALTER TABLE `gaz_aziend` ADD `virtual_taxstamp` TINYINT(1) NOT NULL AFTER `round_bol`;
UPDATE `gaz_aziend` SET `virtual_taxstamp` = 1 WHERE 1; 
UPDATE `gaz_aziend` SET `template` = '' WHERE 1; 
ALTER TABLE `gaz_aziend` CHANGE `alliva` `preeminent_vat` INT(2) NOT NULL DEFAULT '0';
ALTER TABLE `gaz_aziend` DROP `iva_susp`;
ALTER TABLE `gaz_aziend` CHANGE `ricbol` `taxstamp` DECIMAL(5,2) NOT NULL;
ALTER TABLE `gaz_aziend` CHANGE `ivabol` `taxstamp_vat` INT(2) NOT NULL DEFAULT '0';
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXtesdoc` ADD `taxstamp` DECIMAL(5,2) NOT NULL AFTER `stamp`;
ALTER TABLE `gaz_XXXtesbro` ADD `taxstamp` DECIMAL(5,2) NOT NULL AFTER `stamp`;
ALTER TABLE `gaz_XXXtesdoc` ADD `virtual_taxstamp` TINYINT(1) NOT NULL AFTER `taxstamp`;
ALTER TABLE `gaz_XXXtesbro` ADD `virtual_taxstamp` TINYINT(1) NOT NULL AFTER `taxstamp`;
ALTER TABLE `gaz_XXXtesdoc` CHANGE `vat_susp` `expense_vat` INT(2) NOT NULL DEFAULT '0';
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)