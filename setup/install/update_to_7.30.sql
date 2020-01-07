UPDATE `gaz_config` SET `cvalue` = '125' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXfae_flux` CHANGE COLUMN `id_SDI` `id_SDI` VARCHAR(20) NOT NULL DEFAULT '0';
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) VALUES ('Destinazione testo descrittivo articolo (0=solo su documenti, 1=entrambi, 2=solo su web, 9=nessuno)', 'article_text', '0');
CREATE TABLE `gaz_XXXfornitore_magazzino` (
 `id` INT(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
 `id_anagr` int(9) NOT NULL,
 `codice_fornitore` VARCHAR(50) NOT NULL,
 `codice_magazzino` VARCHAR(15) NOT NULL,
 `rapporto_qta_fornitore` decimal(14,2) NOT NULL DEFAULT '1',
 `last_price` decimal(14,5) NOT NULL,
 `date_add`  DATE  NOT NULL,
 `date_update` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)
