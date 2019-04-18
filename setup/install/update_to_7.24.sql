UPDATE `gaz_config` SET `cvalue` = '120' WHERE `id` =2;
DELETE FROM `gaz_menu_script` WHERE `link`='select_regcor.php';
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
UPDATE `gaz_XXXtesmov` SET `datliq`=`datreg` WHERE `caucon`='VCO' AND `datreg` >= '2019-01-01' AND `datliq` < '2000-01-01' ;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
