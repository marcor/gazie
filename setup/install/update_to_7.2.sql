UPDATE `gaz_config` SET `cvalue` = '96' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 7, 'select_sconti_articoli.php', '', '', 41, '', 7 FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 7, 'select_sconti_raggruppamenti.php', '', '', 42, '', 8 FROM `gaz_menu_script`;
UPDATE `gaz_menu_script` SET `weight`=5 WHERE `weight`=3 and `id_menu`=9
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 9, 'select_analisi_fatturato_clienti.php', '', '', 43, '', 3  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, 9, 'select_analisi_fatturato_cliente_fornitore.php', '', '', 44, '', 4  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
CREATE TABLE `gaz_XXXsconti_articoli` (`clfoco` int(9),`codart` varchar(15),`sconto` decimal(6,3),`prezzo_netto` decimal(14,5), primary key(`clfoco`,`codart`));
CREATE TABLE `gaz_XXXsconti_raggruppamenti` (`clfoco` int(9),`ragstat` char(15),`sconto` decimal(6,3), primary key(`clfoco`,`ragstat`));
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
