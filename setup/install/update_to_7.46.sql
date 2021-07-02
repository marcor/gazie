UPDATE `gaz_config` SET `cvalue` = '141' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_artico.php'), 'admin_group.php?Insert', '', '', 14, '', 2  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `durability` INT(4) NOT NULL DEFAULT '0' COMMENT 'Durabilità anche se solo presunta espressa in unità di misura specificata nel campo successivo' AFTER `maintenance_period`,
	ADD COLUMN `durability_mu` VARCHAR(1) NULL DEFAULT NULL COMMENT 'Unità di misura della durabilità di cui al campo precedente (H=ore,D=giorni,M=mesi) nel caso di durabilità alimenti (<=minore di,>=maggiore di)' AFTER `durability`,
	ADD COLUMN `warranty_days` INT(4) NOT NULL DEFAULT '0' COMMENT 'Durata della garanzia in giorni' AFTER `durability_mu`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )