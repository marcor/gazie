UPDATE `gaz_config` SET `cvalue` = '94' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD COLUMN `amm_min` VARCHAR(20) NOT NULL AFTER `fiscal_reg`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXassist` ADD `prezzo` VARCHAR(10) NULL AFTER `ore`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
