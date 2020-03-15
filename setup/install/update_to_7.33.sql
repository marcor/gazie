UPDATE `gaz_config` SET `cvalue` = '128' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXcontract` ADD COLUMN `id_instal` INT(9) NULL COMMENT 'id riferito al bene/impianto/attrezzo' AFTER `id_agente`;
CREATE TABLE `gaz_XXXinstal_component` (
	`id` INT(9) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
	`id_parent_instal` INT(9) UNSIGNED NOT NULL COMMENT 'è l\'id del bene padre in gaz_NNNinstal',
	`id_component` INT(9) UNSIGNED NOT NULL COMMENT 'l\'id del bene figlio sempre riferito a gaz_NNNinstal',
	`quantity` INT(9) UNSIGNED NOT NULL COMMENT 'quantità di figli contenuti nel bene padre',
	`id_tesdoc` INT(9) UNSIGNED NOT NULL COMMENT 'riferimento al documento d\'acquisto',
	`adminid` VARCHAR(20) NOT NULL DEFAULT '',
	`last_modified` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),	INDEX `id_parent_instal` (`id_parent_instal`), INDEX `id_component` (`id_component`),	INDEX `id_tesdoc` (`id_tesdoc`)
) COMMENT='Tabella per permettere la gestione degli attrezzi/impianti/assets aziendali quando questi sono composti da diverse parti componenti, alla stregua di come già si fa con la distinta base degli articoli di magazzino.'
COLLATE='utf8_general_ci' ENGINE=MyISAM ROW_FORMAT=DYNAMIC;
ALTER TABLE `gaz_XXXinstal`	ADD COLUMN `image` BLOB NOT NULL DEFAULT '' COMMENT 'Immagine del bene strumentale' AFTER `descrizione`;
ALTER TABLE `gaz_XXXinstal`	ADD COLUMN `instal_type_id` INT(3) NOT NULL DEFAULT '0' COMMENT 'tipologia/categoria: riferimento alla tabella gaz_NNNinstal_type ' AFTER `id`;
ALTER TABLE `gaz_XXXinstal`	CHANGE COLUMN `clfoco` `clfoco` INT(11) NOT NULL COMMENT 'codice cliente presso il quale è installato il bene, ovvero il fornitore che lo ha fornito se installato in azienda' AFTER `codice`;
CREATE TABLE `gaz_XXXinstal_type` (
	`id` INT(3) NOT NULL AUTO_INCREMENT,
	`description` TEXT NOT NULL,
	`note` VARCHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) COMMENT='Tabella dei tipi di beni strumentali aziendali, es: immobili, impianti, macchinari, automobili, autocarri, pc desktop, notebook, smartphone, mobili, mezzi di sollevamento, impianti di condizionamento, software, ecc.'
COLLATE='utf8_general_ci' ENGINE=MyISAM ROW_FORMAT=DYNAMIC;
INSERT INTO `gaz_XXXinstal_type` (`id`, `description`, `note`) VALUES (1, 'immobili', ''), (2, 'impianti', ''), (3, 'macchinari', ''), (4, 'automobili', ''), (5, 'autocarri', ''), (6, 'pc desktop', ''), (7, 'notebook', ''), (8, 'smartphone', ''), (9, 'networking', ''), (10, 'mobili', ''),(11, 'mezzi di sollevamento', ''), (12, 'impianti di condizionamento', ''), (13, 'software', ''), (14, 'strumenti di misura', ''), (15, 'strumenti di analisi', ''), (16, 'utensili', ''), (17, 'cicli/motocicli', ''), (18, 'altri mezzi di trasporto', ''),(999, 'bene generico', '');
ALTER TABLE `gaz_XXXcontract` ADD COLUMN `id_asset` INT(9) NULL DEFAULT NULL COMMENT 'eventuale id riferito al bene ammortizzabile della contabilità (gaz_NNNassets)' AFTER `id_instal`;
ALTER TABLE `gaz_XXXinstal` ADD COLUMN `id_asset` INT(9) NULL DEFAULT NULL COMMENT 'eventuale id riferito al bene ammortizzabile della contabilità (gaz_NNNassets)' AFTER `clfoco`;
ALTER TABLE `gaz_XXXassist`	COMMENT='Tabella utilizzata dal modulo supporto (assistenze) ma implementabile anche per la gestione delle manutenzioni dei beni aziendali';
ALTER TABLE `gaz_XXXinstal`	ADD COLUMN `stato` TINYINT(1) NOT NULL COMMENT 'Stato del bene aziendale: 0=inattivo ma funzionante, 1=in funzione, 2=in riparazione, 3=rotto, 8=in vendita, 9=alienato' AFTER `note`;
ALTER TABLE `gaz_XXXassist` ADD COLUMN `adminid` VARCHAR(20) NULL DEFAULT NULL AFTER `note`, ADD COLUMN `last_modified` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `adminid`;
ALTER TABLE `gaz_XXXinstal` ADD COLUMN `adminid` VARCHAR(20) NULL DEFAULT NULL AFTER `stato`, ADD COLUMN `last_modified` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `adminid`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)