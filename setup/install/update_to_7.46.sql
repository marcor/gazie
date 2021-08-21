UPDATE `gaz_config` SET `cvalue` = '141' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_artico.php'), 'admin_group.php?Insert', '', '', 14, '', 2  FROM `gaz_menu_script`;
ALTER TABLE `gaz_camp_uso_fitofarmaci` ADD `numero_registrazione` INT(6) NOT NULL COMMENT 'Riferito alla tabella camp_fitofarmaci' AFTER `tempo_sosp`;
ALTER TABLE `gaz_camp_avversita` CHANGE COLUMN `id_avv` `id_avv` INT(3) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `gaz_camp_colture` CHANGE COLUMN `id_colt` `id_colt` INT(3) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `gaz_camp_uso_fitofarmaci` CHANGE COLUMN `id` `id` INT(4) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `gaz_anagra` ADD `custom_field` TEXT NULL DEFAULT NULL COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}' AFTER `id_SIAN`;
ALTER TABLE `gaz_admin`
	ADD COLUMN `id_anagra` INT(9) NULL DEFAULT NULL COMMENT 'Riferimento alla tabella anagrafiche comuni (gaz_anagra)' AFTER `user_id`,
	ADD INDEX `id_anagra` (`id_anagra`);
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `durability` INT(4) NOT NULL DEFAULT '0' COMMENT 'Durabilità anche se solo presunta espressa in unità di misura specificata nel campo successivo' AFTER `maintenance_period`,
	ADD COLUMN `durability_mu` VARCHAR(1) NULL DEFAULT NULL COMMENT 'Unità di misura della durabilità di cui al campo precedente (H=ore,D=giorni,M=mesi) nel caso di durabilità alimenti (<=minore di,>=maggiore di)' AFTER `durability`,
	ADD COLUMN `warranty_days` INT(4) NOT NULL DEFAULT '0' COMMENT 'Durata della garanzia in giorni' AFTER `durability_mu`;
ALTER TABLE `gaz_XXXrigbro`
	ADD COLUMN `id_rigmoc` INT(9) NULL DEFAULT NULL COMMENT 'Riferimento ad id_rig della tabella gaz_NNNrigmoc' AFTER `id_orderman`,
	ADD INDEX `id_rigmoc` (`id_rigmoc`);  
ALTER TABLE `gaz_XXXaliiva`
	CHANGE COLUMN `annota` `annota` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Descrizione estesa e/o annotazioni' AFTER `status`;
ALTER TABLE `gaz_XXXfae_flux`
	CHANGE COLUMN `flux_completed` `n_invio` INT(1) NOT NULL DEFAULT '1' AFTER `flux_status`;
UPDATE `gaz_XXXfae_flux` SET `n_invio`= 1 WHERE `n_invio` < 1;
ALTER TABLE `gaz_XXXcampi`
	CHANGE COLUMN `codice` `codice` INT(3) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `gaz_XXXfae_flux`
	CHANGE COLUMN `flux_status` `flux_status` VARCHAR(10) NOT NULL COMMENT 'Stato del flusso verso SdI DI=da inviare, IV=inviata, PC=presa in carico, RC=consegnata, NS=scartata, MC=mancata consegna, NA=accettata(PA), NR=rifiutata(PA), AT=recapito impossibile, DT=decorrenza termini(PA)' AFTER `data`;
UPDATE `gaz_XXXfae_flux` SET `flux_status`='DI' WHERE `flux_status` LIKE '#%';    
UPDATE `gaz_XXXfae_flux` SET `flux_status`='IN' WHERE `flux_status` LIKE '@%'; 
ALTER TABLE `gaz_XXXfae_flux`
	CHANGE COLUMN `flux_descri` `flux_descri` TEXT NULL COMMENT 'Descrizione della notifica, ad esempio l\'errore causa dello scarto o del rifiuto' AFTER `progr_ret`;
ALTER TABLE `gaz_XXXartico`
	CHANGE COLUMN `preacq` `preacq` DECIMAL(14,5) NULL DEFAULT '0.00000' COMMENT 'Colonna utilizzata dal modulo ProduzionI (orderman) per indicare il costo di produzione in mancanza di movimenti di magazzino per acquisti. Sul modulo Acquisti (acquis) indica il prezzo di acquisto di un bene strumentale o servizio, e comunque di qualsiasi merce/servizio/strumento che non è presente in contabilità di magazzino (gaz_NNNmovmag).' AFTER `ragstat`;
ALTER TABLE `gaz_XXXeffett`
	ADD COLUMN `iban` VARCHAR(32) NULL DEFAULT NULL AFTER `banapp`;
ALTER TABLE `gaz_XXXmovmag`
	CHANGE COLUMN `campo_coltivazione` `campo_impianto` INT(3) NOT NULL DEFAULT '0' COMMENT 'Referenza alla colonna codice della tabella edv_001campi, è il luogo o campo di produzione' AFTER `scorig`,
	CHANGE COLUMN `id_avversita` `id_avversita` INT(3) NULL DEFAULT NULL COMMENT 'Avversità nel quaderno di campagna ma può essere usato per altri inconvenienti verificatesi nella movimentazione ' AFTER `campo_impianto`,
	CHANGE COLUMN `id_colture` `id_colture` INT(3) NULL DEFAULT NULL COMMENT 'Riferito al tipo di coltura e/o altre specifiche' AFTER `id_avversita`,
	ADD COLUMN `custom_field` TEXT NULL DEFAULT NULL COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}' AFTER `id_colture`,
    ADD COLUMN `id_wharehouse` INT(9) NULL DEFAULT NULL COMMENT 'Ref. alla tabella gaz_001wharehouse' AFTER `artico`,
	ADD INDEX (`campo_impianto`),
	ADD INDEX (`id_wharehouse`),
	ADD INDEX (`id_avversita`);
ALTER TABLE `gaz_XXXartico`
	ADD COLUMN `custom_field` TEXT NULL DEFAULT NULL COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}' AFTER `ref_ecommerce_id_product`;
CREATE TABLE IF NOT EXISTS `gaz_XXXwharehouse` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `image` blob NOT NULL,
  `web_url` varchar(255) DEFAULT NULL,
  `custom_field` text COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}',
  `note_other` varchar(50) DEFAULT NULL,
  `adminid` varchar(20) NOT NULL DEFAULT '',
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
ALTER TABLE `gaz_admin_module`
	ADD COLUMN `custom_field` TEXT NULL COMMENT 'Usabile per contenere le scelte dell\'utente in ambito dello specifico modulo.Normalmente in formato json: {"nome_variabile":{"valore_variabile": {}}' AFTER `moduleid`;
ALTER TABLE `gaz_XXXeffett`
	ADD COLUMN `id_distinta` INT(4) NULL DEFAULT NULL COMMENT 'Quando usato è il riferimento alla distinta degli effetti (normalmente contenuta in gaz_001company_data) ' AFTER `id_con`,
	ADD INDEX (`id_distinta`),
	ADD INDEX (`id_con`); 
UPDATE gaz_XXXtesbro SET initra=datemi WHERE tipdoc='PRO' AND id_orderman > 0; 
ALTER TABLE `gaz_XXXstaff_worked_hours`	COMMENT='Tabella contenente i dati per la generazione del "Registro delle presenze", ossia dei riepiloghi giornalieri delle ore/tipo di lavoro eseguito da ciascun lavoratore. Può essere scritta manualente dalla apposita interfaccia o, eventualemente, generata a fine mese dai movimenti registrati su gaz_001staff_work_movements a sua volta frutto di inserimento manuale o se collegato tramite un marcatempo a badge.',
	ADD COLUMN `id` INT(9) NOT NULL AUTO_INCREMENT FIRST,
	ADD COLUMN `custom_field` TEXT NULL DEFAULT NULL COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}' AFTER `id_tes`,
	ADD PRIMARY KEY (`id`);
CREATE TABLE IF NOT EXISTS `gaz_XXXstaff_work_movements` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `id_staff` int(3) NOT NULL DEFAULT '0',
  `start_work` datetime DEFAULT NULL,
  `end_work` datetime DEFAULT NULL,
  `id_work_type` int(3) NOT NULL DEFAULT '0' COMMENT 'Quando riferito forza la scelta automatica. Esempio ''nromale'', straordinario, notturno',
  `min_delay` decimal(3,1) NOT NULL DEFAULT '0.0' COMMENT 'Minuti di ritardo',
  `note` varchar(255) DEFAULT NULL,
  `id_orderman` int(9) DEFAULT NULL COMMENT 'sarà legato al piano dei conti per gestire le commesse (centri di costo)',
  `id_staff_worked_hours` int(9) DEFAULT NULL COMMENT 'Se maggiore di 0 il rigo è già stato usato per calcolare il valore del rigo a cui si riferisce su gaz_001_staff_worked_hours',
  `custom_field` text COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {"nome_modulo":{"nome_variabile":{"valore_variabile": {}}}}',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id_staff` (`id_staff`) USING BTREE,
  KEY `id_orderman` (`id_orderman`) USING BTREE,
  KEY `work_day` (`start_work`) USING BTREE,
  KEY `id_tes` (`id_staff_worked_hours`) USING BTREE,
  KEY `end_work` (`end_work`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Tabella contenente i singoli movimenti del lavoratore. Può essere scritta manualente dalla apposita interfaccia o, se collegato, tramite un marcatempo a badge. Offre la possibilità di indicare la produzione/commessa (id_orderman) sulla quale sta lavorando.';
    
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )