UPDATE `gaz_config` SET `cvalue` = '143' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
UPDATE gaz_XXXpaymov SET id_tesdoc_ref = CONCAT(SUBSTRING(expiry,1,4),LPAD(CAST((id_rigmoc_pay + id_rigmoc_doc) AS CHAR) , 11, '0' )) WHERE id_tesdoc_ref LIKE '%new%'
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )