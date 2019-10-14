UPDATE `gaz_config` SET `cvalue` = '124' WHERE `id` =2;
ALTER TABLE `gaz_anagra` ADD `id_SIAN` INT(10) NULL DEFAULT NULL COMMENT 'identificativo stabilimento assegnato dal SIAN' AFTER `fatt_email`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
CREATE TABLE IF NOT EXISTS `gaz_XXXcamp_recip_stocc` (
  `cod_silos` varchar(10) CHARACTER SET utf8 NOT NULL COMMENT 'Codice recipiente di stoccaggio olio. Deve essere identico a quello registrato al SIAN',
  `capacita` decimal(13,3) NOT NULL COMMENT 'La capacità in kg di olio del recipiente',
  `affitto` int(1) NOT NULL COMMENT '0=di proprietà 1=in affitto',
  `dop_igp` int(1) NOT NULL COMMENT '0=non classificato 1=Recipiente destinato a olio DOP o IGP'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `gaz_XXXcamp_mov_sian` (
  `or_macro_prima` int(2) NOT NULL COMMENT 'Codice origine macroarea',
  `or_macro_dopo` int(2) NOT NULL COMMENT 'Codice origine macroarea a fine operazione',
  `or_spec_prima` varchar(80) CHARACTER SET utf8 NOT NULL COMMENT 'Origine specifica',
  `or_spec_dopo` varchar(80) CHARACTER SET utf8 NOT NULL COMMENT 'Origine specifica a fine operazione',
  `id_movmag` int(9) NOT NULL COMMENT 'Movimento magazzino connesso',
  `sprem_freddo_prima` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag prima spremitura a freddo',
  `sprem_freddo_dopo` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag prima spremitura a freddo a fine operazione',
  `estr_freddo_prima` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag estratto a freddo',
  `estr_feddo_dopo` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag estratto a freddo a fine operazione',
  `bio_prima` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag da agricoltura biologica',
  `bio_dopo` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag da agricoltura biologica a fine operazione',
  `conver_prima` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag in conversione',
  `conver_dopo` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag in conversione a fine operazione',
  `etichett_prima` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag non etichettato',
  `etichett_dopo` varchar(1) CHARACTER SET utf8 NOT NULL COMMENT 'Flag non etichettato a fine operazione',
  `capac_conf` decimal(13,3) NOT NULL COMMENT 'Capacità in litri confezione',
  `categ_olio` int(2) NOT NULL COMMENT 'Codice categoria olio',
  `categ_olio_dopo` int(2) NOT NULL COMMENT 'Codice categoria olio a fine operazione',
  `recip_stocc` varchar(10) CHARACTER SET utf8 NOT NULL COMMENT 'Identificativo recipiente di stoccaggio. Deve essere identico a quello inserito nel SIAN',
  `cod_operazione` varchar(10) CHARACTER SET utf8 NOT NULL COMMENT 'Codice dell''operazione esguita',
  `stabil_dep` int(10) NOT NULL COMMENT 'Identificativo dello stabilimento o deposito assegnato dal SIAN'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `gaz_XXXcampi`
	ADD COLUMN `indirizzo` VARCHAR(50) NULL AFTER `image`,
	ADD COLUMN `provincia` VARCHAR(2) NULL AFTER `indirizzo`,
	ADD COLUMN `comune` VARCHAR(50) NULL AFTER `provincia`,
	ADD COLUMN `id_rif` INT(10) NULL DEFAULT '0' COMMENT 'Identificativo dello stabilimento/deposito assegnato dal SIAN' AFTER `id_mov`;
ALTER TABLE `gaz_XXXartico`
	ADD COLUMN `SIAN` INT(1) NOT NULL DEFAULT '0' COMMENT '0 non movimenta, 1 movimenta il SIAN come olio, 2 movimenta il SIAN come olive' AFTER `tempo_sospensione`;
CREATE TABLE IF NOT EXISTS `gaz_XXXcamp_artico` (
  `codice` varchar(15) NOT NULL COMMENT 'Codice articolo uguale alla tabella artico',
  `or_macro` int(2) NOT NULL COMMENT 'macroarea di origine',
  `or_spec` varchar(80) NOT NULL COMMENT 'origine specifica',
  `estrazione` int(1) NOT NULL COMMENT 'Flag estrazione: 0=non specificata 1=prima spremitura a freddo 2=estratto a freddo',
  `biologico` int(1) NOT NULL COMMENT 'Flag agricoltura: 0=convenzionale 1=biologica 2=in conversione',
  `etichetta` int(1) NOT NULL COMMENT 'Flag etichettatura: 0=non etichettato 1=etichettato',
  `confezione` decimal(9,3) NOT NULL COMMENT 'Capacità singola confezione. 0=sfuso',
  `categoria` int(2) NOT NULL COMMENT 'Categoria olio come codificato dal SIAN'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
