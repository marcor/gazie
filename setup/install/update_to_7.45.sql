UPDATE `gaz_config` SET `cvalue` = '140' WHERE `id` =2;
ALTER TABLE `gaz_anagra` ADD COLUMN `fiscal_rapresentative_id` INT(9) NOT NULL COMMENT 'riferito all\' id  del record di questa tabella  contenente l\'anagrafica del legale rappresentente' AFTER `legrap_pf_cognome`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )