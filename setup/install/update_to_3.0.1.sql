UPDATE `gaz_config` SET `cvalue` = '19' WHERE `id` =2;
UPDATE `gaz_config` SET `variable` = 'update_url' WHERE `id` =5;
UPDATE `gaz_config` SET `cvalue` = '20' WHERE `id` =2;
CREATE TABLE `gaz_letter` (
  `id_let` int(9) NOT NULL auto_increment,
  `data` date NOT NULL,
  `numero` varchar(20) NOT NULL,
  `clfoco` int(9) NOT NULL,
  `tipo` char(3) NOT NULL,
  `c_a` varchar(60) NOT NULL,
  `oggetto` varchar(60) NOT NULL,
  `corpo` text NOT NULL,
  `signature` tinyint(1) NOT NULL,
  `adminid` varchar(20) NOT NULL,
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_let`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
UPDATE `gaz_config` SET `cvalue` = '21' WHERE `id` =2;
ALTER TABLE `gaz_catmer` ADD `ricarico` DECIMAL( 4, 1 ) NOT NULL AFTER `image` ;