UPDATE `gaz_config` SET `cvalue` = '97' WHERE `id` =2;
CREATE TABLE IF NOT EXISTS `gaz_admin_config` ( `id` int(9) unsigned NOT NULL AUTO_INCREMENT, `adminid` varchar(20) NOT NULL DEFAULT '', `var_descri` varchar(100) NOT NULL DEFAULT '', `var_name` varchar(100) NOT NULL DEFAULT '', `var_value` text, PRIMARY KEY (`id`), KEY `adminid` (`adminid`), KEY `var_name` (`var_name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `gaz_admin_config` (`adminid`, `var_descri`, `var_name`, `var_value`) SELECT Login , 'Contenuto in HTML del testo del corpo delle email inviate dell\'utente', 'body_send_doc_email', '' FROM `gaz_admin`;
INSERT INTO `gaz_admin_config` (`adminid`, `var_descri`, `var_name`, `var_value`) SELECT Login , 'Menu/header/footer personalizzabile', 'theme', '/library/theme/g7' FROM `gaz_admin`;
DELETE FROM `gaz_config` WHERE  `variable`='theme';
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXclfoco`	CHANGE COLUMN `last_modified` `last_modified` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `adminid`;
ALTER TABLE `gaz_XXXcompany_config`	CHANGE COLUMN `val` `val` VARCHAR(2000) NULL DEFAULT NULL AFTER `var`;
INSERT INTO `gaz_XXXcompany_config` (`description`,`var`, `val`) SELECT  'Testo in HTML delle email inviate dall\'azienda', 'company_email_text', body_text FROM `gaz_XXXbody_text` WHERE table_name_ref = 'body_send_doc_email';
DELETE FROM `gaz_XXXbody_text` WHERE  `table_name_ref` = 'body_send_doc_email';
ALTER TABLE `gaz_anagra` ADD COLUMN `pec_email` VARCHAR(50) NOT NULL DEFAULT '' AFTER `e_mail`;
ALTER TABLE `gaz_anagra` CHANGE COLUMN `fe_cod_univoco` `fe_cod_univoco` VARCHAR(7) NOT NULL AFTER `pariva`;
ALTER TABLE `gaz_aziend` ADD COLUMN `pec` VARCHAR(50) NOT NULL DEFAULT '' AFTER `rea`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)