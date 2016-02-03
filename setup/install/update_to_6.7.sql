UPDATE `gaz_config` SET `cvalue` = '93' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_001effett` ADD COLUMN `cigcup` VARCHAR(40) NOT NULL AFTER `id_con`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
