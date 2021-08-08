UPDATE `gaz_config` SET `cvalue` = '141' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_artico.php'), 'admin_group.php?Insert', '', '', 14, '', 2  FROM `gaz_menu_script`;
ALTER TABLE `gaz_camp_uso_fitofarmaci` ADD `numero_registrazione` INT(6) NOT NULL COMMENT 'Riferito alla tabella camp_fitofarmaci' AFTER `tempo_sosp`;
ALTER TABLE `gaz_camp_avversita` CHANGE COLUMN `id_avv` `id_avv` INT(3) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `gaz_camp_colture` CHANGE COLUMN `id_colt` `id_colt` INT(3) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `gaz_camp_uso_fitofarmaci` CHANGE COLUMN `id` `id` INT(4) NOT NULL AUTO_INCREMENT FIRST;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `durability` INT(4) NOT NULL DEFAULT '0' COMMENT 'Durabilità anche se solo presunta espressa in unità di misura specificata nel campo successivo' AFTER `maintenance_period`,
	ADD COLUMN `durability_mu` VARCHAR(1) NULL DEFAULT NULL COMMENT 'Unità di misura della durabilità di cui al campo precedente (H=ore,D=giorni,M=mesi) nel caso di durabilità alimenti (<=minore di,>=maggiore di)' AFTER `durability`,
	ADD COLUMN `warranty_days` INT(4) NOT NULL DEFAULT '0' COMMENT 'Durata della garanzia in giorni' AFTER `durability_mu`;
ALTER TABLE `gaz_XXXrigbro`
	ADD COLUMN `id_rigmoc` INT(9) NULL DEFAULT NULL COMMENT 'Riferimento ad id_rig della tabella gaz_NNNrigmoc' AFTER `id_orderman`,
	ADD INDEX `id_rigmoc` (`id_rigmoc`);  
ALTER TABLE `gaz_XXXaliiva`
	CHANGE COLUMN `annota` `annota` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Descrizione estesa e/o annotazioni' AFTER `status`;
ALTER TABLE `gaz_XXXfae_flux`
	CHANGE COLUMN `flux_completed` `n_invio` INT(1) NOT NULL DEFAULT '1' AFTER `flux_status`;
UPDATE `gaz_XXXfae_flux` SET `n_invio`= 1 WHERE `n_invio` < 1;
ALTER TABLE `gaz_XXXcampi`
	CHANGE COLUMN `codice` `codice` INT(3) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `gaz_XXXfae_flux`
	CHANGE COLUMN `flux_status` `flux_status` VARCHAR(10) NOT NULL COMMENT 'Stato del flusso verso SdI DI=da inviare, IV=inviata, PC=presa in carico, RC=consegnata, NS=scartata, MC=mancata consegna, NA=accettata(PA), NR=rifiutata(PA), AT=recapito impossibile, DT=decorrenza termini(PA)' AFTER `data`;
UPDATE `gaz_XXXfae_flux` SET `flux_status`='DI' WHERE `flux_status` LIKE '#%';    
UPDATE `gaz_XXXfae_flux` SET `flux_status`='IN' WHERE `flux_status` LIKE '@%';    
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )