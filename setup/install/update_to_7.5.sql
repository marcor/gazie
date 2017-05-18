UPDATE `gaz_config` SET `cvalue` = '102' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
UPDATE `gaz_menu_script` SET `link`='comunicazione_liquidazioni_periodiche.php' WHERE  `link`='select_spesometro_analitico.php';
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
