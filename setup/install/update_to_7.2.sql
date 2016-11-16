UPDATE `gaz_config` SET `cvalue` = '96' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_client.php'), 'select_sconti_articoli.php', '', '', 41, '', 7 FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='report_client.php'), 'select_sconti_raggruppamenti.php', '', '', 42, '', 8 FROM `gaz_menu_script`;
UPDATE `gaz_menu_script` SET `weight`=5 WHERE `link`='select_esportazione_articoli_venduti.php';
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='../magazz/report_statis.php'), 'select_analisi_fatturato_clienti.php', '', '', 43, '', 3  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='../magazz/report_statis.php'), 'select_analisi_fatturato_cliente_fornitore.php', '', '', 44, '', 4  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, (SELECT id FROM `gaz_module` WHERE `name`='suppor'), 'report_install.php', '', '', 3, '', 3  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MAX(id) FROM `gaz_menu_module`), 'admin_install.php?Insert', '', '', 3, '', 1  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT id FROM `gaz_menu_module` WHERE `link`='select_liqiva.php'), 'select_spesometro_analitico.php', '', '', 8, '', 2  FROM `gaz_menu_script`;
INSERT INTO `gaz_config` (`id`, `description`, `variable`, `cvalue`, `weight`, `show`, `last_modified`) VALUES (NULL, 'Header personalizzabile', 'header', 'header_default.php', '0', '0', '2016-11-12 19:00:00');
CREATE TABLE IF NOT EXISTS `gaz_classroom` (  `id` int(6) NOT NULL AUTO_INCREMENT, `classe` varchar(16) NOT NULL, `sezione` varchar(16) NOT NULL, `anno_scolastico` int(4) NOT NULL, `teacher` varchar(50) NOT NULL, `location` varchar(100) NOT NULL, `title_note` varchar(200) NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `gaz_student` ( `id` int(6) NOT NULL AUTO_INCREMENT, `username` varchar(50) NOT NULL DEFAULT '0', `password` varchar(64) NOT NULL DEFAULT '0', `Cognome` varchar(50) NOT NULL DEFAULT '0', `Nome` varchar(50) NOT NULL DEFAULT '0', `email` varchar(128) NOT NULL DEFAULT '0', `telephone` varchar(50) NOT NULL DEFAULT '0', `id_classroom` int(6) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
CREATE TABLE `gaz_XXXsconti_articoli` (`clfoco` int(9),`codart` varchar(15),`sconto` decimal(6,3),`prezzo_netto` decimal(14,5), primary key(`clfoco`,`codart`));
CREATE TABLE `gaz_XXXsconti_raggruppamenti` (`clfoco` int(9),`ragstat` char(15),`sconto` decimal(6,3), primary key(`clfoco`,`ragstat`));
ALTER TABLE `gaz_XXXassist` ADD `ripetizione` varchar(10) COLLATE 'utf8_general_ci' NOT NULL AFTER `prezzo`;
ALTER TABLE `gaz_XXXassist` ADD `codart` varchar(15) NOT NULL AFTER `ore`;
ALTER TABLE `gaz_XXXassist` change `ripetizione` `ripetizione` int NULL DEFAULT '1' AFTER `prezzo`, ADD `ogni` int NULL DEFAULT '365' AFTER `ripetizione`;
ALTER TABLE `gaz_XXXassist` ADD `codeart` varchar(10) COLLATE 'utf8_general_ci' NULL AFTER `prezzo`;
ALTER TABLE `gaz_XXXassist` CHANGE `ogni` `ogni` varchar(10) NULL DEFAULT 'Anni' AFTER `ripetizione`;
CREATE TABLE `gaz_XXXinstal` ( `id` int NOT NULL, `clfoco` int NOT NULL, `descrizione` varchar(255) NOT NULL, `seriale` varchar(255) NOT NULL, `datainst` date NOT NULL, `note` text NOT NULL ) COLLATE 'utf8_general_ci';
ALTER TABLE `gaz_XXXinstal` ADD `codice` int(11) NOT NULL AFTER `id`;
ALTER TABLE `gaz_XXXinstal` ADD `oggetto` varchar(100) NOT NULL AFTER `clfoco`;
ALTER TABLE `gaz_XXXassist` ADD `idinstallazione` int(11) NOT NULL AFTER `id`;
ALTER TABLE `gaz_XXXinstal` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)