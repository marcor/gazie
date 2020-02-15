UPDATE `gaz_config` SET `cvalue` = '127' WHERE `id` =2;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
UPDATE `gaz_admin` SET `style`='default.css',`skin`='default.css' WHERE 1;
ALTER TABLE `gaz_breadcrumb` ADD COLUMN `grid_class` VARCHAR(127) NOT NULL DEFAULT '' AFTER `icon`;
ALTER TABLE `gaz_XXXtesbro`	ADD COLUMN `ref_ecommerce_id_order` VARCHAR(50) NULL DEFAULT '' COMMENT 'Identificativo ordine attribuito dall\'eventuale ecommerce collegato attraverso API' AFTER `tipdoc`, ADD INDEX `ref_ecommerce_id_order` (`ref_ecommerce_id_order`);
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `ref_ecommerce_id_product` VARCHAR(50) NULL DEFAULT '' COMMENT 'Codice di riferimento allo stesso articolo in eventuale ecommerce collegato attraverso API' AFTER `codice_fornitore`, ADD INDEX `ref_ecommerce_id_product` (`ref_ecommerce_id_product`);
ALTER TABLE `gaz_XXXclfoco`	ADD COLUMN `ref_ecommerce_id_customer` VARCHAR(50) NULL DEFAULT '' COMMENT 'Codice di riferimento allo stesso cliente in eventuale ecommerce collegato attraverso API' AFTER `descri`, ADD INDEX `ref_ecommerce_id_customer` (`ref_ecommerce_id_customer`);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)