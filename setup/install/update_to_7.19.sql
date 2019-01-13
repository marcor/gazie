UPDATE `gaz_config` SET `cvalue` = '117' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) VALUES ('Usa articoli composti come Kit o come Standard (KIT o STD)', 'tipo_composti', 'STD');
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
