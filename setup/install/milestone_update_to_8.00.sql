-- UPDATE `gaz_config` SET `cvalue` = '' WHERE `id` =2; ATTENZIONE!!! DA MODIFICARE quando verrà utilizzato 

ALTER TABLE `gaz_anagra` ADD COLUMN `ragso1_aes` TINYTEXT NOT NULL AFTER `ragso1`;
ALTER TABLE `gaz_anagra` ADD COLUMN `ragso2_aes` TINYTEXT NOT NULL AFTER `ragso2`;
ALTER TABLE `gaz_anagra` ADD COLUMN `sedleg_aes` TINYTEXT NOT NULL AFTER `sedleg`;
ALTER TABLE `gaz_anagra` ADD COLUMN `legrap_pf_nome_aes` TINYTEXT NOT NULL AFTER `legrap_pf_nome`;
ALTER TABLE `gaz_anagra` ADD COLUMN `legrap_pf_cognome_aes` TINYTEXT NOT NULL AFTER `legrap_pf_cognome`;
ALTER TABLE `gaz_anagra` ADD COLUMN `indspe_aes` TINYTEXT NOT NULL AFTER `indspe`;
ALTER TABLE `gaz_anagra` ADD COLUMN `telefo_aes` TINYTEXT NOT NULL AFTER `telefo`;
ALTER TABLE `gaz_anagra` ADD COLUMN `fax_aes` TINYTEXT NOT NULL AFTER `fax`;
ALTER TABLE `gaz_anagra` ADD COLUMN `cell_aes` TINYTEXT NOT NULL AFTER `cell`;
ALTER TABLE `gaz_anagra` ADD COLUMN `codfis_aes` TINYTEXT NOT NULL AFTER `codfis`;
ALTER TABLE `gaz_anagra` ADD COLUMN `pariva_aes` TINYTEXT NOT NULL AFTER `pariva`;
ALTER TABLE `gaz_anagra` ADD COLUMN `e_mail_aes` TINYTEXT NOT NULL AFTER `e_mail`;
ALTER TABLE `gaz_anagra` ADD COLUMN `pec_email_aes` TINYTEXT NOT NULL AFTER `pec_email`;
ALTER TABLE `gaz_anagra` ADD COLUMN `latitude_aes` TINYTEXT NOT NULL AFTER `latitude`;
ALTER TABLE `gaz_anagra` ADD COLUMN `longitude_aes` TINYTEXT NOT NULL AFTER `longitude`;
ALTER TABLE `gaz_anagra` ADD COLUMN `fatt_email_aes` TINYTEXT NOT NULL AFTER `fatt_email`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)

-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)