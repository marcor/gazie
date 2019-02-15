UPDATE `gaz_config` SET `cvalue` = '118' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_NNNcompany_config` (`description`, `var`, `val`) VALUES ('Stampa Documenti di Trasporto in modalità 2xA5 (affiancati)', 'ddt_A5', '0');
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
