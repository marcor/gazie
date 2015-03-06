UPDATE `gaz_config` SET `cvalue` = '89' WHERE `id` =2;
DELETE FROM `gaz_menu_module` WHERE `id_module` = (SELECT id FROM `gaz_module` WHERE `name`='gazpme' LIMIT 1);
DELETE FROM `gaz_menu_module` WHERE `id_module` = (SELECT id FROM `gaz_module` WHERE `name`='gazpma' LIMIT 1);
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico` ADD `lot_or_serial` INT(1) NOT NULL AFTER `descri`;
ALTER TABLE `gaz_XXXmovmag` ADD `id_lotmag` INT(9) NOT NULL AFTER `artico`;
CREATE TABLE IF NOT EXISTS `gaz_XXXlotmag` (  `id` int(9) NOT NULL AUTO_INCREMENT,  `id_purchase` int(9) NOT NULL, `lot_or_serial` varchar(100) NOT NULL, `description` varchar(100) NOT NULL,  `id_doc` int(9) NOT NULL,  `expiry` date NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)