UPDATE `gaz_config` SET `cvalue` = '92' WHERE `id` =2;
INSERT INTO `gaz_config` (`description`, `variable`, `cvalue`) VALUES ('backup to keep', 'keep_backup', '200');
INSERT INTO `gaz_config` (`description`, `variable`, `cvalue`) VALUES ('leave free space in backup', 'freespace_backup', '10');
INSERT INTO `gaz_config` (`description`, `variable`, `cvalue`) VALUES ('backup files', 'file_backup', '0');
ALTER TABLE `gaz_anagra` ADD `fatt_email` BOOLEAN NOT NULL DEFAULT FALSE AFTER `e_mail`;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, 3, 'report_agenti_forn.php', '', '', 9, '', 9  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MAX(id) FROM `gaz_menu_module`), 'admin_agenti_forn.php?Insert', '', '', 14, '', 1  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
CREATE TABLE gaz_XXXagenti_forn (`id_agente` int(9) NOT NULL,`id_fornitore` int(9) NOT NULL,`base_percent` decimal(4,2) NOT NULL,`tipo_contratto` tinyint(1) NOT NULL,`adminid` varchar(20) NOT NULL,`last_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)