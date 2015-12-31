UPDATE `gaz_config` SET `cvalue` = '92' WHERE `id` =2;
ALTER TABLE `gaz_anagra` ADD `fatt_email` BOOLEAN NOT NULL DEFAULT FALSE AFTER `e_mail`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
CREATE TABLE gaz_xxxagenti_forn (`id_agente` int(9) NOT NULL,`id_fornitore` int(9) NOT NULL,`base_percent` decimal(4,2) NOT NULL,`tipo_contratto` tinyint(1) NOT NULL,`adminid` varchar(20) NOT NULL,`last_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)