UPDATE `gaz_config` SET `cvalue` = '127' WHERE `id` =2;
UPDATE `gaz_admin` SET `style`='default.css',`skin`='default.css' WHERE 1;
ALTER TABLE `gaz_breadcrumb` ADD COLUMN `grid_class` VARCHAR(127) NOT NULL DEFAULT '' AFTER `icon`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXtesbro`	ADD COLUMN `ref_ecommerce_id_order` VARCHAR(50) NULL DEFAULT '' COMMENT 'Identificativo ordine attribuito dall\'eventuale ecommerce collegato attraverso API' AFTER `tipdoc`, ADD INDEX `ref_ecommerce_id_order` (`ref_ecommerce_id_order`);
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `ref_ecommerce_id_product` VARCHAR(50) NULL DEFAULT '' COMMENT 'Codice di riferimento allo stesso articolo in eventuale ecommerce collegato attraverso API' AFTER `codice_fornitore`, ADD INDEX `ref_ecommerce_id_product` (`ref_ecommerce_id_product`);
ALTER TABLE `gaz_XXXcatmer`	ADD COLUMN `ref_ecommerce_id_category` VARCHAR(50) NULL DEFAULT '' COMMENT 'Codice di riferimento alla stessa categoria articoli in eventuale ecommerce collegato attraverso API' AFTER `web_url`, ADD INDEX `ref_ecommerce_id_category` (`ref_ecommerce_id_category`);
ALTER TABLE `gaz_XXXclfoco`	ADD COLUMN `ref_ecommerce_id_customer` VARCHAR(50) NULL DEFAULT '' COMMENT 'Codice di riferimento allo stesso cliente in eventuale ecommerce collegato attraverso API' AFTER `descri`, ADD INDEX `ref_ecommerce_id_customer` (`ref_ecommerce_id_customer`);
INSERT INTO `gaz_XXXcompany_config` (`description`,`var`,`val`) VALUES ('Nome della libreria di terze parti da usare per inviare sms','send_sms','');
ALTER TABLE `gaz_XXXcatmer`	ADD COLUMN `top` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'posizione di visualizzazione/pubblicazione' AFTER `ricarico`;
SET @a  = 0 ;
UPDATE `gaz_XXXartico` SET `ref_ecommerce_id_product` = @a:=@a+1 WHERE 1 ORDER BY `catmer`, `codice`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)