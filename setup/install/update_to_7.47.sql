UPDATE `gaz_config` SET `cvalue` = '142' WHERE `id` =2;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, '5', 'report_wharehouse.php', '', '', '10', '', '8'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_wharehouse.php'), 'admin_wharehouse.php?Insert', '', '', 15, '', 10  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_effett.php'), 'select_effett_report.php', '', '', 60, '', 11  FROM `gaz_menu_script`;
ALTER TABLE `gaz_staff_work_type` ADD COLUMN `descri_ext` VARCHAR(255) NULL DEFAULT NULL AFTER `descri`, ADD COLUMN `inps_ref` VARCHAR(3) NULL DEFAULT NULL AFTER `descri_ext`,	ADD COLUMN `causal` VARCHAR(3) NULL DEFAULT NULL AFTER `inps_ref`;
UPDATE `gaz_staff_work_type` SET `descri_ext` = `descri` WHERE 1;
INSERT INTO gaz_staff_work_type (id_work_type, hour_year_limit, hour_month_limit, hour_week_limit, hour_day_limit, increase, inps_ref, causal, descri, descri_ext) SELECT 9, 0, 0, 0, 0, 0, inps_ref, causal, descri, descri_ext FROM `gaz_staff_absence_type` WHERE 1;
DROP TABLE `gaz_staff_absence_type`;
ALTER TABLE `gaz_staff_work_type` CHANGE COLUMN `id_work_type` `id_work_type` INT(3) NOT NULL COMMENT '1=straordinario;  2,8=altri; 9=assenze;' AFTER `id_work`;
DELETE t1 FROM gaz_module t1 INNER JOIN gaz_module t2 WHERE t1.id > t2.id AND t1.name = t2.name;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_module` WHERE `name`='humres'), 'employee_timesheet.php', '', '', '2', '', '20'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_module` WHERE `name`='humres'), 'pay_salary.php', '', '', '3', '', '30'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_module` WHERE `name`='humres'), 'report_pay_salary.php', '', '', '4', '', '40'  FROM `gaz_menu_module`;
DELETE FROM `gaz_menu_script` WHERE `link`= 'employee_timesheet.php';
DELETE FROM `gaz_menu_script` WHERE `link`= 'pay_salary.php';
DELETE FROM `gaz_menu_script` WHERE `link`= 'report_pay_salary.php';
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) VALUES ('Abilita la richiesta di scelta template per carta intestata 0=No 1=Si', 'enable_lh_print_dialog', '1');
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) VALUES ('Ritorno dopo inserimento documento 0=Nuovo inserimento 1=Report', 'after_newdoc_back_to_doclist', '0');
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )