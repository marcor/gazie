UPDATE `gaz_config` SET `cvalue` = '81' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_XXXcompany_config` SELECT MAX(id)+1, 'SMTP Port (default 25)', 'smtp_port', '25'  FROM `gaz_XXXcompany_config`;
INSERT INTO `gaz_XXXcompany_config` SELECT MAX(id)+1, 'SMTP Secure', 'smtp_secure', 'tls'  FROM `gaz_XXXcompany_config`;
INSERT INTO `gaz_XXXcompany_config` SELECT MAX(id)+1, 'SMTP Authentication (mail,smtp,sendmail,qmail)', 'smtp_auth', 'mail'  FROM `gaz_XXXcompany_config`;
INSERT INTO `gaz_XXXcompany_config` SELECT MAX(id)+1, 'SMTP Username (empty for no auth)', 'smtp_user', ''  FROM `gaz_XXXcompany_config`;
INSERT INTO `gaz_XXXcompany_config` SELECT MAX(id)+1, 'SMTP Password', 'smtp_password', ''  FROM `gaz_XXXcompany_config`;
-- STOP_WHILE( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)
