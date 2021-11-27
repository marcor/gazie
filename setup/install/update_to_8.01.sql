UPDATE `gaz_config` SET `cvalue` = '000' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) SELECT
'Invia i pdf dei reports in finestra modale (0=No, 1=Si)', 'pdf_reports_send_to_modal', '1' FROM DUAL
WHERE NOT EXISTS (SELECT `var` FROM `gaz_XXXcompany_config` WHERE `var` = 'pdf_reports_send_to_modal' LIMIT 1);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
