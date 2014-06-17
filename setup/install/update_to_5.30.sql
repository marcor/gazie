UPDATE `gaz_config` SET `cvalue` = '86' WHERE `id` =2;
ALTER TABLE `gaz_aziend` ADD `virtual_stamp_auth_prot` VARCHAR( 14 ) NOT NULL AFTER `round_bol` ,
ADD `virtual_stamp_auth_date` DATE NOT NULL AFTER `virtual_stamp_auth_prot`, ADD `causale_pagam_770` VARCHAR( 1 ) NOT NULL AFTER `virtual_stamp_auth_date`; 
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)