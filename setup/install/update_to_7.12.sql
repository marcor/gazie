UPDATE `gaz_config` SET `cvalue` = '111' WHERE `id` =2;
ALTER TABLE `gaz_staff_work_type` ADD INDEX `id_work_type` (`id_work_type`);
ALTER TABLE `gaz_staff_work_type` ADD INDEX `descri` (`descri`);
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_doctra.php'), 'admin_docven.php?Insert&tipdoc=CMR', '', '', 50, '', 10  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_doctra.php'), 'report_doccmr.php', '', '', 51, '', 15  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_doctra.php'), 'emissi_fatdif.php?tipodocumento=CMR', '', '', 53, '', 20  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXtesmov`	ADD COLUMN `notess` TEXT NULL DEFAULT '' COMMENT 'Note che NON vengono stampate sui registri contabili' AFTER `descri`;
ALTER TABLE `gaz_XXXrigbro`	ADD COLUMN `id_orderman` INT(9) NULL COMMENT 'Per avere riferimenti uno a molti, e viceversa, con le produzioni (orderman)' AFTER `id_mag`;
ALTER TABLE `gaz_XXXrigbro`	ADD INDEX `id_orderman` (`id_orderman`);
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `status` `status` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id_orderman`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)