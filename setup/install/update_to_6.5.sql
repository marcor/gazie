UPDATE `gaz_config` SET `cvalue` = '91' WHERE `id` =2;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, '5', 'report_ragstat.php', '', '', '9', '', '9'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MAX(id) FROM `gaz_menu_module`), 'admin_ragstat.php', '', '', '12', '', '5'  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 7, 'report_destinazioni.php', '', '', '33', '', '4'  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 7, 'admin_destinazioni.php', '', '', '34', '', '5'  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, '4', 'select_situazione_contabile.php', '', '', '7', '', '7'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MAX(id) FROM `gaz_menu_module`), 'select_situazione_contabile.php', '', '', '6', '', '1'  FROM `gaz_menu_script`;
CREATE TABLE `gaz_destina` (`codice` int(9) NOT NULL AUTO_INCREMENT,`unita_locale1` varchar(50) NOT NULL DEFAULT  '',`unita_locale2` varchar(50) NOT NULL DEFAULT  '',`indspe` varchar(50) NOT NULL DEFAULT  '',`capspe` varchar(10) NOT NULL DEFAULT  '',`citspe` varchar(50) NOT NULL DEFAULT  '',`prospe` char(2) NOT NULL DEFAULT  '',`country` varchar(3) NOT NULL,`latitude` decimal(8,5) NOT NULL,`longitude` decimal(8,5) NOT NULL,`telefo` varchar(50) NOT NULL DEFAULT  '',`fax` varchar(32) NOT NULL DEFAULT  '',`cell` varchar(32) NOT NULL DEFAULT  '',`e_mail` varchar(50) NOT NULL DEFAULT  '',`annota` varchar(50) NOT NULL DEFAULT  '',`id_anagra` int(9) NOT NULL ,PRIMARY KEY (`codice`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `gaz_admin` ADD `skin` VARCHAR(60) NOT NULL DEFAULT 'default.css' AFTER `style`;
CREATE TABLE IF NOT EXISTS `gaz_menu_usage` ( `adminid` varchar(30) NOT NULL, `name` varchar(50) NOT NULL, `link` varchar(80) NOT NULL DEFAULT '', `click` int(5) DEFAULT NULL, `last_use` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`link`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXaliiva` ADD `taxstamp` INT(1) NOT NULL AFTER `aliquo`;
UPDATE `gaz_XXXaliiva` SET `taxstamp` = '1' WHERE `aliquo` <= 0.1;
CREATE TABLE `gaz_XXXragstat` ( `codice` char(15) NOT NULL, `descri` varchar(50) NOT NULL DEFAULT '', `image` blob NOT NULL, `web_url` varchar(255) NOT NULL, `ricarico` decimal(4,1) NOT NULL, `annota` varchar(50) DEFAULT NULL, `adminid` varchar(20) NOT NULL DEFAULT '', `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (codice)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `gaz_XXXartico` ADD `ragstat` CHAR(15) NOT NULL AFTER `catmer`;
ALTER TABLE `gaz_XXXartico` ADD FOREIGN KEY (ragstat)REFERENCES gaz_XXXragstat(codice);
ALTER TABLE `gaz_XXXartico` ADD `clfoco` CHAR(15) DEFAULT NULL;
ALTER TABLE `gaz_XXXartico` ADD FOREIGN KEY (clfoco) REFERENCES gaz_XXXclfoco(codice);
ALTER TABLE `gaz_XXXartico` ADD `sconto` decimal(6,3) AFTER `preve3`;
ALTER TABLE `gaz_XXXrigdoc` MODIFY COLUMN `sconto` decimal(6,3);
ALTER TABLE `gaz_XXXtesdoc` ADD `ddt_type` CHAR(1) NOT NULL AFTER `tipdoc`;
UPDATE `gaz_XXXtesdoc` SET `ddt_type` = 'T' WHERE (`tipdoc` = 'DDT' OR `tipdoc` = 'FAD');
ALTER TABLE `gaz_XXXtesdoc` ADD `id_doc_ritorno` int(9) NOT NULL DEFAULT 0 AFTER `ddt_type`;
ALTER TABLE `gaz_XXXtesdoc` ADD `data_ordine` DATE DEFAULT null AFTER `datemi`;
ALTER TABLE `gaz_XXXtesdoc` ADD `ragbol` int NOT NULL DEFAULT 0 AFTER `pagame`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)