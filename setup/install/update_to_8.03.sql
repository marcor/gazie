UPDATE `gaz_config` SET `cvalue` = '146' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXcampi`	COMMENT='Tabella contenente i campi ovvero i luoghi di produzione.', ADD COLUMN `used_from_modules` VARCHAR(127) NULL COMMENT 'Se FALSE o NULL appare nella select di tutti i moduli altrimenti solo in quello/i del/i modulo/i indicati qui. Usare la virgola per separare i nomi dei moduli quando più di uno.' COLLATE 'utf8_general_ci' AFTER `codice_prodotto_usato`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
