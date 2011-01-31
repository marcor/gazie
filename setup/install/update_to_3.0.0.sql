UPDATE `gaz_config` SET `cvalue` = '14' WHERE `id` =2;
ALTER TABLE `gaz_tesdoc` ADD `pervat` DECIMAL( 3, 1 ) NOT NULL DEFAULT '0.0' AFTER `ivaspe` ,
ADD `cauven` INT( 2 ) NOT NULL AFTER `pervat` , 
ADD `caucon` CHAR( 3 ) NOT NULL AFTER `cauven` ,
ADD `caumag` INT( 2 ) NOT NULL AFTER `caucon` ,
ADD `codage` INT( 9 ) NOT NULL AFTER `caumag` ,
ADD `id_pro` INT( 9 ) NOT NULL AFTER `codage` ,
ADD `destin` VARCHAR( 100 ) NOT NULL AFTER `listin` ,
ADD `id_des` INT( 9 ) NOT NULL AFTER `destin` ;
ALTER TABLE `gaz_rigdoc` ADD `id_mag` INT( 9 ) NOT NULL AFTER `codric` ;
ALTER TABLE `gaz_tesbro` ADD `pervat` DECIMAL( 3, 1 ) NOT NULL DEFAULT '0.0' AFTER `ivaspe` ,
ADD `cauven` INT( 2 ) NOT NULL AFTER `pervat` , 
ADD `caucon` CHAR( 3 ) NOT NULL AFTER `cauven` ,
ADD `caumag` INT( 2 ) NOT NULL AFTER `caucon` ,
ADD `codage` INT( 9 ) NOT NULL AFTER `caumag` ,
ADD `id_pro` INT( 9 ) NOT NULL AFTER `codage` ,
ADD `destin` VARCHAR( 100 ) NOT NULL AFTER `listin` ,
ADD `id_des` INT( 9 ) NOT NULL AFTER `destin` ;
ALTER TABLE `gaz_rigbro` ADD `id_mag` INT( 9 ) NOT NULL AFTER `id_doc` ;
CREATE TABLE `gaz_movmag` (
  `id_mov` int(9) NOT NULL auto_increment,
  `caumag` int(2) NOT NULL default '0',
  `datreg` date NOT NULL,
  `tipdoc` char(3) NOT NULL,
  `desdoc` varchar(50) NOT NULL,
  `datdoc` date NOT NULL,
  `clfoco` int(9) NOT NULL,
  `scochi` decimal(5,2) NOT NULL,
  `id_rif` int(9) NOT NULL,
  `artico` varchar(50) NOT NULL default '',
  `quanti` decimal(10,1) default '0.0',
  `prezzo` decimal(12,3) default '0.000',
  `scorig` decimal(4,1) default '0.0',
  `status` varchar(10) NOT NULL default '',
  `adminid` varchar(20) NOT NULL,
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_mov`,`datreg`,`artico`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `gaz_caumag` (
  `codice` int(2) NOT NULL,
  `descri` varchar(50) NOT NULL default '',
  `insdoc` tinyint(1) NOT NULL,
  `operat` tinyint(1) NOT NULL,
  `upesis` tinyint(1) NOT NULL,
  `adminid` varchar(20) NOT NULL default '',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`codice`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
UPDATE `gaz_config` SET `cvalue` = '15' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD `magazz` INT NOT NULL AFTER `regime` ;
ALTER TABLE `gaz_clfoco` ADD `destin` VARCHAR( 100 ) NOT NULL AFTER `listin` ,
ADD `id_des` INT( 9 ) NOT NULL AFTER `destin` ;
UPDATE `gaz_config` SET `cvalue` = '16' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD `conmag` INT( 1 ) NOT NULL AFTER `colore` ;
UPDATE `gaz_config` SET `cvalue` = '17' WHERE `id` =2;
ALTER TABLE `gaz_caumag` ADD `clifor` TINYINT( 1 ) NOT NULL AFTER `descri` ;
ALTER TABLE `gaz_movmag` ADD `operat` TINYINT( 1 ) NOT NULL AFTER `caumag` ;
UPDATE `gaz_config` SET `cvalue` = '18' WHERE `id` =2;
INSERT INTO `gaz_caumag` (`codice`, `descri`, `clifor`, `insdoc`, `operat`, `upesis`, `adminid`, `last_modified`) VALUES 
(1, 'SCARICO PER VENDITA', -1, 1, -1, 1, '', ''),
(2, 'CARICO PER RESO DA CLIENTE', -1, 1, 1, 1, '', ''),
(3, 'SCARICO PER C/LAVORAZIONE', 1, 1, -1, 1, '', ''),
(4, 'SCARICO PER RESO A FORNITORE', 1, 1, -1, 1, '', ''),
(5, 'CARICO PER ACQUISTO', 1, 1, 1, 1, '', '');
UPDATE `gaz_tesdoc` SET `caumag` = 1 WHERE `tipdoc` = 'FAI' OR `tipdoc` = 'FAD' OR `tipdoc` = 'DDT';
UPDATE `gaz_tesdoc` SET `caumag` = 2 WHERE `tipdoc` = 'FNC';
UPDATE `gaz_tesdoc` SET `caumag` = 3 WHERE `tipdoc` = 'DDL';
UPDATE `gaz_tesdoc` SET `caumag` = 4 WHERE `tipdoc` = 'DDR';
UPDATE `gaz_aziend` SET `conmag` = 1 WHERE `codice` = 1;
INSERT INTO `gaz_config` ( `id` , `description` , `variable` , `cvalue` , `weight` , `show` , `last_modified` )
VALUES ( '8', 'Formato pagine', 'page_format', 'gazie', '8', '0', '');
