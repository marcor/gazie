UPDATE `gaz_config` SET `cvalue` = '94' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD COLUMN `amm_min` VARCHAR(20) NOT NULL AFTER `fiscal_reg`;
ALTER TABLE `gaz_aziend` ADD COLUMN `mas_amm` INT(3) NOT NULL DEFAULT '0' AFTER `masban`;
UPDATE `gaz_aziend` SET `amm_min` = '22IV';
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_docacq.php'), 'admin_assets.php?Insert', '', '', 16, '', 4  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXassist` ADD `prezzo` VARCHAR(10) NULL AFTER `ore`;
ALTER TABLE `gaz_XXXclfoco` ADD COLUMN `id_asset` INT(9) NOT NULL AFTER `ceeave`;
CREATE TABLE `gaz_XXXassets` ( `id` INT(9) NOT NULL AUTO_INCREMENT, `id_tesmov` INT(9) NOT NULL DEFAULT '0', `id_tesdoc` INT(9) NOT NULL DEFAULT '0', `ss_amm_min` INT(2) NOT NULL DEFAULT '0', `valamm` DECIMAL(5,2) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
