UPDATE `gaz_config` SET `cvalue` = '110' WHERE `id` =2;
INSERT INTO `gaz_module` (`name`, `link`, `icon`, `class`, `access`, `weight`) VALUES ('wiki', 'docume_wiki.php', 'wiki.png', '', '0', 17);
INSERT INTO `gaz_admin_module` (`adminid`, `company_id`, `moduleid`, `access`) VALUES ('amministratore', '1', '17', '3');
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)

-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)