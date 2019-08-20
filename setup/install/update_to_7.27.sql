UPDATE `gaz_config` SET `cvalue` = '122' WHERE `id` =2;
ALTER TABLE `gaz_breadcrumb` ADD COLUMN `adminid` VARCHAR(20) NOT NULL DEFAULT '' AFTER `id_bread`, ADD COLUMN `position_order` INT(2) NULL DEFAULT '0' AFTER `link`,ADD COLUMN `icon` BLOB NOT NULL AFTER `position_order`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
