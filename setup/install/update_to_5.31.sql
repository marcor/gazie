UPDATE `gaz_config` SET `cvalue` = '87' WHERE `id` =2;
UPDATE `gaz_admin` SET `style` = 'default.css' WHERE 1;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)