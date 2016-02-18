UPDATE `gaz_config` SET `cvalue` = '93' WHERE `id` =2;
ALTER TABLE `gaz_menu_usage` ADD `color` VARCHAR(6) NOT NULL DEFAULT '5cb85c' AFTER `click`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 2, 'select_docforlist.php', '', '', 36, '', 9 FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 7, 'print_anagrafe.php?clifor=C', '', '', 37, '', 6  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 14, 'print_anagrafe.php?clifor=F', '', '', 15, '', 3  FROM `gaz_menu_script`;
ALTER TABLE `gaz_aziend` ADD COLUMN `payroll_tax` DECIMAL(3,1) NOT NULL AFTER `ritenuta`, ADD COLUMN `c_payroll_tax` INT(9) NOT NULL DEFAULT '0' AFTER `c_ritenute`;
UPDATE `gaz_menu_script` SET `link` = 'select_suppliers_status.php' WHERE  ( `link`='select_partner_status.php' AND `translate_key` < 30 );
UPDATE `gaz_menu_script` SET `link`='select_schedule_debt.php' WHERE  ( `link`='select_schedule.php' AND `translate_key` < 30 );
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXeffett` ADD COLUMN `cigcup` VARCHAR(40) NOT NULL AFTER `id_con`;
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `good_or_service` INT(1) NULL DEFAULT NULL AFTER `descri`, ADD COLUMN `depli_public` TINYINT(1) NOT NULL NULL DEFAULT '0' AFTER `web_public`, ADD COLUMN `retention_tax` TINYINT NOT NULL DEFAULT '0' AFTER `aliiva`, ADD COLUMN `payroll_tax` TINYINT NOT NULL DEFAULT '0' AFTER `last_cost`;
ALTER TABLE `gaz_XXXbody_text`	ADD INDEX `table_name_ref` (`table_name_ref`);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
