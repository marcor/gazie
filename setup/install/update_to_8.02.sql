UPDATE `gaz_config` SET `cvalue` = '146' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico`	CHANGE COLUMN `codice_fornitore` `codice_fornitore` VARCHAR(50) NOT NULL COMMENT 'Da intendersi come "codice produttore" ovvero univoco per il medesimo prodotto, indipendentemente dal canale/fornitore utilizzato per l\'approvigionamento. I codici utilizzati dai singoli fornitori vanno eventualmente indicati su rigdoc-rigbro' AFTER `id_artico_group`;
CREATE TABLE IF NOT EXISTS `gaz_XXXartico_position` (
  `id_position` int(3) NOT NULL AUTO_INCREMENT,
  `id_warehouse` int(3) NOT NULL,
  `id_shelf` int(3) NOT NULL,
  `codart` varchar(32) NOT NULL,
  `position` varchar(50) NOT NULL DEFAULT '',
  `image` blob NULL,
  `custom_field` text COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}',
  `adminid` varchar(20) NOT NULL DEFAULT '',
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_position`),
  UNIQUE KEY `id_warehouse_id_shelf_codart` (`id_warehouse`,`id_shelf`,`codart`),
  KEY `id_warehouse` (`id_warehouse`),
  KEY `id_shelf` (`id_shelf`),
  KEY `codart` (`codart`)
) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `gaz_XXXshelves` (
  `id_shelf` int(3) NOT NULL AUTO_INCREMENT,
  `id_warehouse` int(3) DEFAULT NULL,
  `descri` varchar(50) NOT NULL DEFAULT '',
  `code` varchar(32) NOT NULL DEFAULT '',
  `image` blob NULL,
  `custom_field` text COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}',
  `adminid` varchar(20) NOT NULL DEFAULT '',
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_shelf`) USING BTREE,
  UNIQUE KEY `id_shelf_id_warehouse` (`id_shelf`,`id_warehouse`),
  KEY `id_warehouse` (`id_warehouse`)
) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
ALTER TABLE `gaz_XXXcamp_recip_stocc`	ADD COLUMN `nome` VARCHAR(20) NOT NULL AFTER `cod_silos`;
UPDATE `gaz_XXXcompany_config` SET `description`='Permetti anagrafiche senza dati fiscali' WHERE `var`='consenti_nofisc';
ALTER TABLE `gaz_XXXartico`	CHANGE COLUMN `codice` `codice` VARCHAR(32) NOT NULL DEFAULT '' FIRST;
ALTER TABLE `gaz_XXXassets`	CHANGE COLUMN `codice_artico` `codice_artico` VARCHAR(32) NOT NULL COMMENT 'verrà valorizzato con un codice articolo se si vorranno gestire i beni strumentali, e sarà lo stesso valore codice in gaz_NNNartico' AFTER `descri`;
ALTER TABLE `gaz_XXXassist`	CHANGE COLUMN `codart` `codart` VARCHAR(32) NOT NULL AFTER `ore`;
ALTER TABLE `gaz_XXXcampi` CHANGE COLUMN `codice_prodotto_usato` `codice_prodotto_usato` VARCHAR(32) NOT NULL AFTER `giorno_decadimento`;
ALTER TABLE `gaz_XXXcamp_artico` CHANGE COLUMN `codice` `codice` VARCHAR(32) NOT NULL COMMENT 'Codice articolo uguale alla tabella artico' AFTER `id_campartico`;
ALTER TABLE `gaz_XXXdistinta_base` CHANGE COLUMN `codice_composizione` `codice_composizione` VARCHAR(32) NOT NULL COMMENT 'è il codice dell\'articolo composito' COLLATE 'utf8mb4_general_ci' AFTER `id`,	CHANGE COLUMN `codice_artico_base` `codice_artico_base` VARCHAR(32) NOT NULL COMMENT 'codice dell\'articolo base' AFTER `codice_composizione`;
ALTER TABLE `gaz_XXXlotmag`	CHANGE COLUMN `codart` `codart` VARCHAR(32) NOT NULL DEFAULT '' AFTER `id`;
ALTER TABLE `gaz_XXXprovvigioni` CHANGE COLUMN `cod_articolo` `cod_articolo` VARCHAR(32) NOT NULL AFTER `id_agente`;
ALTER TABLE `gaz_XXXragstat` CHANGE COLUMN `codice` `codice` CHAR(32) NOT NULL FIRST;
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `codart` `codart` VARCHAR(32) NOT NULL DEFAULT '' AFTER `tiprig`;
ALTER TABLE `gaz_XXXrigdoc` CHANGE COLUMN `codart` `codart` VARCHAR(32) NOT NULL DEFAULT '' AFTER `tiprig`;
ALTER TABLE `gaz_XXXsconti_articoli` CHANGE COLUMN `codart` `codart` VARCHAR(32) NOT NULL AFTER `clfoco`;
ALTER TABLE `gaz_XXXsconti_raggruppamenti` CHANGE COLUMN `ragstat` `ragstat` CHAR(32) NOT NULL AFTER `clfoco`;
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) SELECT 'Attiva lo scroll automatico sull\'ultimo rigo dei documenti (0=No, 1=Si)', 'autoscroll_to_last_row', '1' FROM DUAL WHERE NOT EXISTS (SELECT `var` FROM `gaz_XXXcompany_config` WHERE `var` = 'autoscroll_to_last_row' LIMIT 1);
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) SELECT 'PEC SMTP Server', 'pec_smtp_server', '' FROM DUAL WHERE NOT EXISTS (SELECT `var` FROM `gaz_XXXcompany_config` WHERE `var` = 'pec_smtp_server' LIMIT 1);
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) SELECT 'PEC SMTP Port (25,587,465)', 'pec_smtp_port', '' FROM DUAL WHERE NOT EXISTS (SELECT `var` FROM `gaz_XXXcompany_config` WHERE `var` = 'pec_smtp_port' LIMIT 1);
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) SELECT 'PEC SMTP Secure (tls,ssl)', 'pec_smtp_secure', '' FROM DUAL WHERE NOT EXISTS (SELECT `var` FROM `gaz_XXXcompany_config` WHERE `var` = 'pec_smtp_secure' LIMIT 1);
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) SELECT 'PEC SMTP User', 'pec_smtp_usr', '' FROM DUAL WHERE NOT EXISTS (SELECT `var` FROM `gaz_XXXcompany_config` WHERE `var` = 'pec_smtp_usr' LIMIT 1);
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) SELECT 'PEC SMTP Password', 'pec_smtp_psw', '' FROM DUAL WHERE NOT EXISTS (SELECT `var` FROM `gaz_XXXcompany_config` WHERE `var` = 'pec_smtp_psw' LIMIT 1);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
