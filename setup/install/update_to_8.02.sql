UPDATE `gaz_config` SET `cvalue` = '146' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico`	CHANGE COLUMN `codice_fornitore` `codice_fornitore` VARCHAR(50) NOT NULL COMMENT 'Da intendersi come "codice produttore" ovvero univoco per il medesimo prodotto, indipendentemente dal canale/fornitore utilizzato per l\'approvigionamento. I codici utilizzati dai singoli fornitori vanno eventualmente indicati su rigdoc-rigbro' AFTER `id_artico_group`;
CREATE TABLE IF NOT EXISTS `gaz_XXXartico_position` (
  `id_position` int(3) NOT NULL AUTO_INCREMENT,
  `id_warehouse` int(3) NOT NULL,
  `id_shelf` int(3) NOT NULL,
  `codart` varchar(15) NOT NULL,
  `position` varchar(50) NOT NULL DEFAULT '',
  `image` blob NOT NULL,
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
  `code` varchar(15) NOT NULL DEFAULT '',
  `image` blob NOT NULL,
  `custom_field` text COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}',
  `adminid` varchar(20) NOT NULL DEFAULT '',
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_shelf`) USING BTREE,
  UNIQUE KEY `id_shelf_id_warehouse` (`id_shelf`,`id_warehouse`),
  KEY `id_warehouse` (`id_warehouse`)
) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
