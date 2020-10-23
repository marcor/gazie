-- UPDATE `gaz_config` SET `cvalue` = '129' WHERE `id` =2; 
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
UPDATE `gaz_XXXmovmag` SET `tipdoc`='MAG' WHERE `tipdoc` = '';
ALTER TABLE `gaz_XXXstaff_skills` CHANGE COLUMN `skill_value` `skill_data` VARCHAR(100) NULL DEFAULT NULL AFTER `variable_name`, ADD COLUMN `skill_description` VARCHAR(100) NULL DEFAULT NULL AFTER `skill_data`, ADD COLUMN `skill_cost` DECIMAL(8,2) NULL DEFAULT NULL AFTER `skill_description`;
ALTER TABLE `gaz_XXXstaff_worked_hours`	ADD COLUMN `id_tes` INT(9) NULL DEFAULT NULL COMMENT 'può essere usato per link con tesbro al fine di aver un documento/resoconto del lavoro eseguito' AFTER `id_orderman`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )