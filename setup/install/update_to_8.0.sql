UPDATE `gaz_config` SET `cvalue` = '109' WHERE `id` =2;
INSERT INTO `gaz_module` (`name`, `link`, `icon`, `class`, `access`, `weight`) VALUES ('wiki', 'docume_wiki.php', 'wiki.png', '', '0', 15);
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_admin_module` (`adminid`, `company_id`, `moduleid`, `access`) SELECT user_name,(CONVERT('XXX',UNSIGNED INTEGER)),(SELECT MAX(id)+1 FROM `gaz_module`),3 FROM `gaz_admin` WHERE 1;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)