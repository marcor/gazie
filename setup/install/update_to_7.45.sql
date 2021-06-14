UPDATE `gaz_config` SET `cvalue` = '139' WHERE `id` =2;
ALTER TABLE `gaz_anagra` ADD COLUMN `legrap_id` INT(9) NOT NULL COMMENT 'id riferito all\' id_anagra del record del legale rappresentente di questa stessa tabella' AFTER `legrap_pf_cognome`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )