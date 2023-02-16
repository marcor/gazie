UPDATE `gaz_config` SET `cvalue` = '152' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `codice_fornitore` `codice_fornitore` VARCHAR(50) NOT NULL COMMENT 'Mappatura di codart con il codice usato dal fornitore in caso di acquisto, potrebbe essere usato anche per mappare i codici clienti ad es. nella acquisizione ordini da clienti' AFTER `codart`, ADD INDEX `codice_fornitore` (`codice_fornitore`);
ALTER TABLE `gaz_XXXtesbro`	ADD INDEX `tipdoc` (`tipdoc`);
ALTER TABLE `gaz_XXXorderman`	ADD INDEX `id_tesbro` (`id_tesbro`);
ALTER TABLE `gaz_XXXtesdoc`
	ADD COLUMN `custom_field` TEXT NULL COMMENT 'Usabile per contenere le scelte dell\'utente in ambito dello specifico modulo.Normalmente in formato json: {"nome_variabile":{"valore_variabile": {}}' AFTER `status`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
