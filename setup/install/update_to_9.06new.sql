UPDATE `gaz_config` SET `cvalue` = '154' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='select_regiva.php'), 'protocol_renumbering.php', '', '', 16, '', 80  FROM `gaz_menu_script`;
UPDATE `gaz_config` SET `cvalue` = JSON_MERGE_PRESERVE(cvalue, JSON_OBJECT('RPL','vendit')) WHERE variable='report_movmag_ref_doc';
UPDATE `gaz_config` SET `cvalue` = JSON_MERGE_PRESERVE(cvalue, JSON_OBJECT('VOL','vendit')) WHERE variable='report_movmag_ref_doc';
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXclfoco`	ADD INDEX (`descri`);
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `tiprig` `tiprig` INT(2) NOT NULL DEFAULT '0' AFTER `id_tes`;
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `id_doc` `id_doc` INT(9) NOT NULL DEFAULT '0' COMMENT 'può essere usato come riferimento ad un figlio anche se in rigdoc (es. se questo è il rigo di un ordine evaso) o ad un genitore (es. se questo è un task di un diagramma di Gantt anch\'esso in tesbro)' AFTER `delivery_date`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
