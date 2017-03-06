UPDATE `gaz_config` SET `cvalue` = '98' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXpagame` CHANGE `incaut` `incaut` CHAR(9) NOT NULL DEFAULT '';
ALTER TABLE `gaz_XXXpagame` ADD `pagaut` INT(9) NULL DEFAULT 0 AFTER `incaut`;
UPDATE `gaz_XXXpagame` SET `pagaut`=(SELECT `cassa_` FROM `gaz_aziend` WHERE `codice`= CONVERT('XXX',UNSIGNED INTEGER) LIMIT 1) WHERE `incaut` = 'S';
UPDATE `gaz_XXXpagame` SET `incaut`=(SELECT `cassa_` FROM `gaz_aziend` WHERE `codice`= CONVERT('XXX',UNSIGNED INTEGER) LIMIT 1) WHERE `incaut` = 'S';
ALTER TABLE `gaz_XXXpagame` CHANGE `incaut` `incaut` INT(9) NOT NULL DEFAULT 0;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
UPDATE `gaz_config` SET `cvalue` = '99' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD COLUMN `desez4` VARCHAR(50) NOT NULL DEFAULT '' AFTER `desez3`, ADD COLUMN `desez5` VARCHAR(50) NOT NULL DEFAULT '' AFTER `desez4`, ADD COLUMN `desez6` VARCHAR(50) NOT NULL DEFAULT '' AFTER `desez5`, ADD COLUMN `desez7` VARCHAR(50) NOT NULL DEFAULT '' AFTER `desez6`, ADD COLUMN `desez8` VARCHAR(50) NOT NULL DEFAULT '' AFTER `desez7`, ADD COLUMN `desez9` VARCHAR(50) NOT NULL DEFAULT 'AUTOFATTURE - REVERSE CHARGE' AFTER `desez8`;
UPDATE `gaz_config` SET `cvalue` = '100' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD COLUMN `reverse_charge_sez` INT(1) NOT NULL DEFAULT '9' AFTER `desez9`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXtesmov`	ADD COLUMN `reverse_charge_idtes` INT(9) NOT NULL AFTER `operat`;
ALTER TABLE `gaz_XXXtesmov`	ADD COLUMN `coll_dich_iva` VARCHAR(15) NOT NULL AFTER `reverse_charge_idtes`;
ALTER TABLE `gaz_XXXaliiva` ADD COLUMN `coll_dich_iva` CHAR(15) NOT NULL DEFAULT '' AFTER `tipiva`;
INSERT INTO `gaz_XXXaliiva` (`codice`, `tipiva`, `coll_dich_iva`, `aliquo`, `fae_natura`, `descri`, `status`, `annota`) SELECT MAX(`codice`)+1, 'I', 'VJ', '22', 'N6', 'REVERSE CHARGE art.17c.6 IVA al 22%','','' FROM `gaz_XXXaliiva`;
ALTER TABLE `gaz_XXXclfoco`	ADD COLUMN `coll_dich_iva` VARCHAR(15) NOT NULL DEFAULT '' AFTER `ceeave`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
