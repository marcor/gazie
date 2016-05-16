UPDATE `gaz_config` SET `cvalue` = '95' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD COLUMN `lost_cost_assets` INT(9) NOT NULL DEFAULT '0' AFTER `mas_cost_assets`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_assets.php'), 'depreciation_assets.php', '', '', 7, '', 7  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)

-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
