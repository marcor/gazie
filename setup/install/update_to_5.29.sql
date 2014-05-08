UPDATE `gaz_config` SET `cvalue` = '85' WHERE `id` =2;
ALTER TABLE `gaz_anagra` ADD `fe_cod_univoco` VARCHAR( 6 ) NOT NULL AFTER `pariva` ;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)