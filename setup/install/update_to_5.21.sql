UPDATE `gaz_config` SET `cvalue` = '79' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXcontract_row` DROP PRIMARY KEY , ADD PRIMARY KEY ( `id_row` , `id_contract` );
-- STOP_WHILE( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)
