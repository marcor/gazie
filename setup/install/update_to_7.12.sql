UPDATE `gaz_config` SET `cvalue` = '111' WHERE `id` =2;
ALTER TABLE `gaz_staff_work_type` ADD INDEX `id_work_type` (`id_work_type`);
ALTER TABLE `gaz_staff_work_type` ADD INDEX `descri` (`descri`);
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXtesmov`	ADD COLUMN `notess` TEXT NULL DEFAULT '' COMMENT 'Note che NON vengono stampate sui registri contabili' AFTER `descri`;
ALTER TABLE `gaz_XXXrigbro`	ADD COLUMN `id_orderman` INT(9) NULL COMMENT 'Per avere riferimenti uno a molti, e viceversa, con le produzioni (orderman)' AFTER `id_mag`;
ALTER TABLE `gaz_XXXrigbro`	ADD INDEX `id_orderman` (`id_orderman`);
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `status` `status` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id_orderman`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)