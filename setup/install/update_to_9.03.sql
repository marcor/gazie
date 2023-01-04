UPDATE `gaz_config` SET `cvalue` = '152' WHERE `id` =2;
CREATE TABLE IF NOT EXISTS `gaz_licenses` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `class` varchar(10) DEFAULT '',
  `class_descri` varchar(50) DEFAULT '',
  `sub_class` varchar(10) NOT NULL DEFAULT '',
  `description` varchar(100) DEFAULT '',
  `data` text,
  `duration` int(6) DEFAULT NULL,
  `show` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `class` (`class`),
  KEY `type` (`sub_class`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Tabella con i tipi di autorizzazioni necessarie, ad es. per la vendita/acquisto di alcune tipologie di prodotti';
INSERT INTO `gaz_licenses` (`id`, `class`, `class_descri`, `sub_class`, `description`, `data`, `duration`, `show`) VALUES
	(1, 'FS', 'Fitosanitario', 'FSUSO', 'Abilitazione all’utilizzo di fitofarmaci', '', 5, 1),
	(2, 'FS', 'Fitosanitario', 'FSVEN', 'Abilitazione alla vendita di fitofarmaci', '', 5, 1),
	(3, 'FS', 'Fitosanitario', 'FSCON', 'Abilitazione per operare come consulente fitosanitario', '', 5, 1);
CREATE TABLE IF NOT EXISTS `gaz_licenses_anagra` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `id_anagra` int(9) DEFAULT NULL,
  `id_licenses` int(9) DEFAULT NULL,
  `dataes` mediumblob COMMENT 'Criptato per contenere i documenti del cliente',
  `description` varchar(100) DEFAULT '',
  `issuing_body` varchar(100) DEFAULT '',
  `release_date` date DEFAULT NULL,
  `license identifier` varchar(60) DEFAULT '',
  `expiry_date` date DEFAULT NULL,
  `identification_document` varchar(60) DEFAULT '',
  `issue_identification_document` date DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `class` (`id_anagra`) USING BTREE,
  KEY `type` (`description`) USING BTREE,
  KEY `id_licenses` (`id_licenses`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Tabella con i tipi di autorizzazioni necessarie, ad es. per la vendita/acquisto di alcune tipologie di prodotti';
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_anagra.php'), 'custom_from_fae.php', '', '', 14, '', 7  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_client.php'), 'report_customer_group.php', '', '', 62, '', 40  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_client.php'), 'admin_customer_group.php?Insert', '', '', 61, '', 45  FROM `gaz_menu_script`;
ALTER TABLE `gaz_aziend` ADD COLUMN `descrifae_vat` INT(3) NOT NULL DEFAULT '0' COMMENT 'La percentuale serve per valorizzare i righi descrittivi sugli XML della fattura elettronica' AFTER `preeminent_vat`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `license_class` VARCHAR(10) NOT NULL DEFAULT '0' COMMENT 'Tipo di autorizzazione necessaria: colonna class della tabella gaz_licenses' AFTER `classif_amb`,
	CHANGE COLUMN `SIAN` `SIAN` INT(2) NOT NULL DEFAULT '0' COMMENT '0 non movimenta, 1 movimenta come olio, 2 movimenta come olive, 6 movimenta come fitosanitario, 7 movimenta come vino' AFTER `tempo_sospensione`;
CREATE TABLE `gaz_XXXcustomer_group` (
  `id` int(3) NOT NULL DEFAULT '0',
  `descri` varchar(50) NOT NULL DEFAULT '',
  `large_descri` text NOT NULL,
  `image` blob,
  `ref_ecommerce_customer_group` int(3) NOT NULL DEFAULT '0' COMMENT 'Riferimento al gruppo in eventuale ecommerce sincronizzato',
  `annota` varchar(50) DEFAULT NULL,
  `adminid` varchar(20) NOT NULL DEFAULT '',
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `ref_ecommerce_customer_group` (`ref_ecommerce_customer_group`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `gaz_XXXcustomer_group` (`id`, `descri`, `large_descri`, `image`, `ref_ecommerce_customer_group`, `annota`, `adminid`, `last_modified`) VALUES
	(1, 'GRUPPO 1', '', _binary '', 0, 'TEST', 'amministratore', '2022-12-24 07:26:12');
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
