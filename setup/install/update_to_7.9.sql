UPDATE `gaz_config` SET `cvalue` = '107' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
CREATE TABLE `gaz_XXXcaucon_rows` (`caucon_cod` CHAR(3) NOT NULL DEFAULT '', `clfoco_ref` INT(9) NOT NULL DEFAULT '0', `type_imp` CHAR(1) NOT NULL DEFAULT '',	`dare_avere` CHAR(1) NOT NULL DEFAULT '', `n_order` INT(3) NOT NULL DEFAULT '0') ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `gaz_XXXcaucon_rows` (`caucon_cod`,clfoco_ref,type_imp,dare_avere,n_order) SELECT `codice`,`contr1`,`tipim1`,`daav_1`,1 FROM `gaz_001caucon`;
INSERT INTO `gaz_XXXcaucon_rows` (`caucon_cod`,clfoco_ref,type_imp,dare_avere,n_order) SELECT `codice`,`contr2`,`tipim2`,`daav_2`,2 FROM `gaz_001caucon`;
INSERT INTO `gaz_XXXcaucon_rows` (`caucon_cod`,clfoco_ref,type_imp,dare_avere,n_order) SELECT `codice`,`contr3`,`tipim3`,`daav_3`,3 FROM `gaz_001caucon`;
INSERT INTO `gaz_XXXcaucon_rows` (`caucon_cod`,clfoco_ref,type_imp,dare_avere,n_order) SELECT `codice`,`contr4`,`tipim4`,`daav_4`,4 FROM `gaz_001caucon`;
INSERT INTO `gaz_XXXcaucon_rows` (`caucon_cod`,clfoco_ref,type_imp,dare_avere,n_order) SELECT `codice`,`contr5`,`tipim5`,`daav_5`,5 FROM `gaz_001caucon`;
INSERT INTO `gaz_XXXcaucon_rows` (`caucon_cod`,clfoco_ref,type_imp,dare_avere,n_order) SELECT `codice`,`contr6`,`tipim6`,`daav_6`,6 FROM `gaz_001caucon`;
ALTER TABLE `gaz_XXXcaucon` DROP COLUMN `contr1`, DROP COLUMN `tipim1`, DROP COLUMN `daav_1`, DROP COLUMN `contr2`, DROP COLUMN `tipim2`, DROP COLUMN `daav_2`, DROP COLUMN `contr3`, DROP COLUMN `tipim3`, DROP COLUMN `daav_3`, DROP COLUMN `contr4`, DROP COLUMN `tipim4`, DROP COLUMN `daav_4`, DROP COLUMN `contr5`, DROP COLUMN `tipim5`, DROP COLUMN `daav_5`, DROP COLUMN `contr6`, DROP COLUMN `tipim6`, DROP COLUMN `daav_6`;
DELETE FROM `gaz_XXXcaucon_rows` WHERE `clfoco_ref` = 0;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
UPDATE `gaz_config` SET `cvalue` = '108' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_anagra.php'), 'report_anagra.php', '', '', 10, '', 5  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_anagra.php'), 'report_municipalities.php', '', '', 11, '', 10  FROM `gaz_menu_script`;

