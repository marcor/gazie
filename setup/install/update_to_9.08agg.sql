UPDATE `gaz_config` SET `cvalue` = '157' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
CREATE TABLE IF NOT EXISTS `gaz_XXXpagame_distribution` (
  `id_pagdis` int NOT NULL AUTO_INCREMENT,
  `codpag` int NOT NULL DEFAULT '0' COMMENT 'Referenza alla tabella gaz_XXXpagame (molti a uno)',
  `descri` varchar(50) NOT NULL DEFAULT '' COMMENT 'La descrizione è indispensabile in assenza di data di scadenza (expiry)',
  `ratperc` decimal(4,1) NOT NULL DEFAULT (0) COMMENT 'La somma dei righi riferiti allo stesso codpag dovrebbe essere 100 se non lo è viene troncato o riempito fino a capienza',
  `from_prev` varchar(3) NULL DEFAULT NULL COMMENT 'Sono i giorni  dalla scadenza precedente o dalla data di riferimento, si può indicare anche una lettere coma nella colonna tiprat di gaz_XXXpagame. non è indispensabile e se variabile, ovvero non strettamente determinato, es. quando il limite temporale dipende dalla fine dei lavori o da altre condizione queste verranno indicate in descrizione e lasciata vuota o NULL.',
  `tippag` char(1) NOT NULL DEFAULT '0' COMMENT 'La stessa della colonna tippag di gaz_XXXpagame ma prevale su di essa se valorizzata. Consente metodi di pagamento diversi sulle diverse rate',
  `fae_mode` varchar(4) DEFAULT NULL COMMENT 'Come su gaz_XXXpagame è il valore MPXX della fattura elettronica. Consente metodi di pagamento diversi sulle diverse rate',
  `id_bank` int NOT NULL,
  `annota` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_pagdis`) USING BTREE,
  KEY `codpag` (`codpag`),
  KEY `from_prev` (`from_prev`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Il contenuto di questa tabella serve per gestire i pagamenti con scadenze e rate non costanti nell''importo e nella periodicità. Ed è legata alla tabella gaz_XXXpagame. Ogni rigo di questa tabella rappresenta una rata per il pagamento riferito con la colonna codpag e verranno passate come matrice alla funzione CalcExpiry nel file expiry_calc.php ';
ALTER TABLE `gaz_XXXpagame` CHANGE COLUMN `tippag` `tippag` CHAR(1) NOT NULL DEFAULT '' COMMENT 'C=contanti,O=bonifico,K=carte di pagamento,D=rimessa diretta,I=rapporto interbancario diretto (RID),B=Ricevuta Bancaria,T=Cambiale-Tratta,V=mediante avviso(MAV),F=finanziamento,M=misto. ATTENZIONE se F o M ci si può appoggiare alla tabella gaz_XXXcompany_data per contenere dati quali anticipo, finanziato, importo rate, numero rate, periodicità' AFTER `descri`;
ALTER TABLE `gaz_XXXcompany_data`	COMMENT='Multipurpose table. Tabella per contenere dati di qualsiasi genere, utilizzabile sulle personalizzazioni per conservare (ad es.) documenti crittografati.', ADD COLUMN `id_ref` INT(9) NULL COMMENT 'Referenza numerica alla fonte' AFTER `id`,	ADD INDEX `id_ref` (`id_ref`);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
