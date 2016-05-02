UPDATE `gaz_config` SET `cvalue` = '94' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD COLUMN `amm_min` VARCHAR(20) NOT NULL AFTER `fiscal_reg`;
ALTER TABLE `gaz_aziend` ADD COLUMN `mas_fixed_assets` INT(3) NOT NULL DEFAULT '0' AFTER `masban`;
ALTER TABLE `gaz_aziend` ADD COLUMN `mas_found_assets` INT(3) NOT NULL DEFAULT '0' AFTER `mas_fixed_assets`;
ALTER TABLE `gaz_aziend` ADD COLUMN `mas_cost_assets` INT(3) NOT NULL DEFAULT '0' AFTER `mas_found_assets`;
UPDATE `gaz_aziend` SET `amm_min` = '22IV';
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_docacq.php'), 'admin_assets.php?Insert', '', '', 16, '', 4  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, 6, 'report_assets.php', '', '', 6, '', 6  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_assets.php'), 'assets_book.php', '', '', 6, '', 5  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXassist` ADD `prezzo` VARCHAR(10) NULL AFTER `ore`;
ALTER TABLE `gaz_XXXclfoco` ADD COLUMN `id_assets` INT(9) NOT NULL AFTER `ceeave`;
CREATE TABLE IF NOT EXISTS `gaz_XXXassets` ( `id` int(9) NOT NULL AUTO_INCREMENT, `id_tes` int(9) NOT NULL DEFAULT '0', `type_mov` int(1) NOT NULL DEFAULT '0',`descri` varchar(100) NOT NULL, `unimis` varchar(3) NOT NULL, `quantity` decimal(12,3) NOT NULL, `a_value` decimal(12,2) NOT NULL, `pagame` int(2) NOT NULL DEFAULT '0', `ss_amm_min` int(2) NOT NULL DEFAULT '0', `valamm` decimal(5,2) NOT NULL DEFAULT '0.00', `acc_fixed_assets` int(9) NOT NULL DEFAULT '0', `acc_found_assets` int(9) NOT NULL DEFAULT '0', `acc_cost_assets` int(9) NOT NULL DEFAULT '0', `id_no_deduct_vat` int(2) NOT NULL DEFAULT '0', `no_deduct_vat_rate` decimal(5,2) NOT NULL DEFAULT '0.00', `acc_no_detuct_cost` int(11) NOT NULL DEFAULT '0', `no_deduct_cost_rate` decimal(5,2) NOT NULL DEFAULT '0.00', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
