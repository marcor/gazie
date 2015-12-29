UPDATE `gaz_config` SET `cvalue` = '92' WHERE `id` =2;
ALTER TABLE `gaz_anagra` ADD `fatt_email` BOOLEAN NOT NULL DEFAULT FALSE AFTER `e_mail`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)

-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)