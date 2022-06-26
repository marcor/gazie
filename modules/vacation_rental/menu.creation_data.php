<?php
/*  --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-20223 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)
  Ogni diritto è riservato.
  E' possibile usare questo modulo solo dietro autorizzazione dell'autore
  --------------------------------------------------------------------------
 */
$dbname = constant("Database");

//Aggiunta di tabelle aziendali specifiche del modulo Vacation Rental
$tn = array('rental_events','rental_prices','rental_ical','rental_extra','rental_discounts','rental_payments');
$idaz = 1;
if (isset($_SESSION['company_id'])) {
    $idaz = sprintf('%03d', $_SESSION['company_id']);
}
foreach ($tn as $v) {
    $gTables[$v] = $table_prefix . "_" . $idaz . $v;
}

$menu_data = array('m1' => array('link' => "docume_vacation_rental.php"),
                        'm2' => array(1 => array('link' => "report_accommodation.php", 'weight' => 1),
										2 => array('link' => "report_facility.php", 'weight' => 2),
                                        3 => array('link' => "report_booking.php", 'weight' => 3),
										4 => array('link' => "report_extra.php", 'weight' => 4),
                    5 => array('link' => "report_discount.php", 'weight' => 5),
										6 => array('link' => "settings.php", 'weight' => 6)
                                     ),
						 'm3' => array('m2' => array(1 => array(
																array('translate_key' => 1, 'link' => "admin_house.php?Insert", 'weight' => 10)
															  ),
													2 => array(
																array('translate_key' => 2, 'link' => "admin_facility.php", 'weight' => 20)
															  ),
													3 => array( array('translate_key' => 3, 'link' => "admin_booking.php?Insert&tipdoc=VOR", 'weight' => 30)
															  ),
													4 => array( array('translate_key' => 4, 'link' => "admin_extra.php?Insert", 'weight' => 40)
															  ),
                          5 => array( array('translate_key' => 5, 'link' => "admin_discount.php?Insert", 'weight' => 50)
															  )
													)
									 )
                );
$module_class='fas fa-landmark';

$update_db[]="CREATE TABLE `".$dbname."`.".$gTables['rental_events']." ( `id` INT(32) NOT NULL AUTO_INCREMENT , `title` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `start` DATE NULL DEFAULT NULL , `end` DATE NULL DEFAULT NULL , `house_code` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `id_tesbro` INT(9) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;";
$update_db[]="CREATE TABLE `".$dbname."`.".$gTables['rental_prices']." ( `id` INT(32) NOT NULL AUTO_INCREMENT , `title` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `start` DATE NULL DEFAULT NULL , `end` DATE NULL DEFAULT NULL , `house_code` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `price` DECIMAL(13,4) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;";
$update_db[]="CREATE TABLE `".$dbname."`.".$gTables['rental_ical']." ( `id` INT(2) NOT NULL AUTO_INCREMENT , `url` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `codice_alloggio` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `ical_descri` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM; ";
$update_db[]="ALTER TABLE ".$gTables['rental_events']." ADD INDEX(`house_code`);";
$update_db[]="ALTER TABLE ".$gTables['rental_events']." ADD INDEX(`start`)";
$update_db[]="ALTER TABLE ".$gTables['rental_events']." ADD `id_rigbro` INT(9) NULL DEFAULT NULL COMMENT 'riferimento al rigo di rigbro', ADD `adult` INT(2) NULL AFTER `id_tesbro`, ADD `child` INT(2) NOT NULL AFTER `adult`, ADD `Ical_sync_id` INT(2) NULL DEFAULT NULL COMMENT 'Se l\'evento è stato inserito da una importazione di un Ical, ne imposto il suo id' AFTER `child`; ";
$update_db[]="CREATE TABLE ".$gTables['rental_extra']." ( `id` INT(2) NOT NULL AUTO_INCREMENT , `mod_prezzo` INT(1) NOT NULL , `rif_alloggio` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `max_quantity` INT NOT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = MyISAM; ";
$update_db[]="ALTER TABLE ".$gTables['rental_extra']." ADD `obligatory` INT NULL DEFAULT '0' COMMENT '0=facoltativo - 1=obbligatorio', ADD `codart` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Riferimento alla tabella artico' AFTER `max_quantity`; ";
$update_db[]="INSERT INTO ".$gTables['company_config']." (`description`, `var`, `val`) VALUES ('Notti da bloccare prima e dopo ogni prenotazione', 'vacation_blockdays', '0')";
$update_db[]="INSERT INTO ".$gTables['company_config']." (`description`, `var`, `val`) VALUES ('Notti minime da prenotare ', 'vacation_minnights', '1')";
$update_db[]="INSERT INTO ".$gTables['company_config']." (`id`, `description`, `var`, `val`) VALUES (NULL, 'ID pagamento con bonifico bancario per front-end (sincronizzazione con pagamenti GAzie)', 'vacation_id_pagbon', '0'), (NULL, 'ID pagamento con carta di credito off-line per front-end (sincronizzazione con pagamenti GAzie)', 'vacation_id_pagccoff', '0'), (NULL, 'ID pagamento con carta di credito on-line per front-end (sincronizzazione con pagamenti GAzie)', 'vacation_id_pagccon', '0') ";
$update_db[]="INSERT INTO ".$gTables['company_config']." (`id`, `description`, `var`, `val`) VALUES (NULL, 'URL front-end del regolamento locazioni', 'vacation_url_rules', NULL), (NULL, 'URL front-end del regolamento sulla privacy', 'vacation_url_privacy', NULL) ";
$update_db[]="INSERT INTO ".$gTables['company_config']." (`id`, `description`, `var`, `val`) VALUES (NULL, 'Usa prezzi IVA compresa nei calendari(si / no)', 'vacation_ivac', 'no');";
$update_db[]="INSERT INTO ".$gTables['company_config']." (`id`, `description`, `var`, `val`) VALUES (NULL, 'ID del conto corrente bancario su cui ricevere i bonifici delle prenotazioni alloggi', 'vacation_ccb', '0');";
$update_db[]="CREATE TABLE `".$dbname."`.".$gTables['rental_discounts']." ( `id` INT NOT NULL AUTO_INCREMENT , `title` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL , `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL , `facility_id` INT(9) NULL , `accommodation_code` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , `min_stay` TINYINT(3) NULL , `valid_from` DATE NULL , `valid_to` DATE NULL , `value` DECIMAL(12,2) NULL , `value_adult` DECIMAL(12,2) NULL , `value_child` DECIMAL(12,2) NULL , `is_percent` TINYINT(1) NULL , `priority` INT(11) NULL , `custom_field` TEXT NULL COMMENT 'Riferimenti generici utilizzabili sui moduli. Normalmente in formato json: {\"nome_modulo\":{\"nome_variabile\":{\"valore_variabile\": {}}}} ' , `stop_further_processing` TINYINT(3) NULL , `STATUS` VARCHAR(50) NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM; ";
$update_db[]="ALTER TABLE ".$gTables['rental_discounts']." ADD `reusable` TINYINT(2) NOT NULL DEFAULT '0' COMMENT 'Se è zero, lo sconto è utilizzabile una sola volta', ADD `discount_voucher_code` VARCHAR(10) NULL DEFAULT NULL COMMENT 'Se valorizzato questo è un buono sconto' AFTER `stop_further_processing`, ADD `id_anagra` INT(9) NULL DEFAULT NULL COMMENT 'Se valorizzato lo sconto è riservato ad un solo utente' AFTER `discount_voucher_code`;";
$update_db[]="ALTER TABLE ".$gTables['rental_discounts']." ADD INDEX(`id_anagra`);";
$update_db[]="ALTER TABLE ".$gTables['rental_discounts']." ADD INDEX(`accommodation_code`);";
$update_db[]="ALTER TABLE ".$gTables['rental_discounts']." ADD INDEX(`facility_id`);";
$update_db[]="ALTER TABLE ".$gTables['artico_group']." ADD `custom_field` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `adminid`; ";
$update_db[]="INSERT INTO ".$gTables['artico']." (`codice`, `descri`, `id_artico_group`, `codice_fornitore`, `id_assets`, `ref_ecommerce_id_product`, `custom_field`, `ecomm_option_attribute`, `quality`, `ordinabile`, `movimentabile`, `good_or_service`, `lot_or_serial`, `image`, `barcode`, `unimis`, `larghezza`, `lunghezza`, `spessore`, `bending_moment`, `catmer`, `ragstat`, `preacq`, `preve1`, `preve2`, `preve3`, `preve4`, `sconto`, `web_mu`, `web_price`, `web_multiplier`, `web_public`, `depli_public`, `web_url`, `aliiva`, `retention_tax`, `last_cost`, `payroll_tax`, `scorta`, `riordino`, `uniacq`, `classif_amb`, `maintenance_period`, `durability`, `durability_mu`, `warranty_days`, `mostra_qdc`, `peso_specifico`, `volume_specifico`, `dose_massima`, `rame_metallico`, `perc_N`, `perc_P`, `perc_K`, `tempo_sospensione`, `SIAN`, `id_reg`, `pack_units`, `codcon`, `id_cost`, `annota`, `adminid`, `last_modified`, `clfoco`, `last_used`) VALUES ('TASSA-TURISTICA', 'Tassa di soggiorno turistica', '0', '', '0', '', NULL, '', '', '', '', '1', 0, '', '', 'n', NULL, NULL, NULL, NULL, '0', '', '0.00000', '0.00000', '0.00000', '0.00000', '0.00000', '0.000', '', 0, '0', '0', '0', '', '11', '0', '0.00000', '0', '0.000', '0.000', '', '0', '0', '0', NULL, '0', '0', '0.000', '0', '0.000', '0.000', NULL, NULL, NULL, '0', '0', '0', '0', '215000000', '0', NULL, '', CURRENT_TIMESTAMP, NULL, NULL) ";
$update_db[]="ALTER TABLE ".$gTables['rental_events']." ADD `voucher_id` INT(11) NULL DEFAULT NULL COMMENT 'Se valorizzato indica il Riferimento ID discounts usato' AFTER `id_rigbro`, ADD `checked_in_date` DATETIME NULL DEFAULT NULL COMMENT 'Giorno e ora dell\'effettuato check-in' AFTER `voucher_id`, ADD `checked_out_date` DATETIME NULL DEFAULT NULL COMMENT 'Giorno e ora dell\'effettuato check-out' AFTER `checked_in_date`, ADD `access_code` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `checked_out_date`,  ADD `type` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'tipo di evento' AFTER `access_code`;";
$update_db[]="CREATE TABLE `".$dbname."`.".$gTables['rental_payments']." ( `payment_id` INT(11) NOT NULL AUTO_INCREMENT , `type` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL , `item_number` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `txn_id` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `payment_gross` DECIMAL(14,5) NOT NULL , `currency_code` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `payment_status` VARCHAR(20) NOT NULL , `id_tesbro` INT(9) NOT NULL , `created` DATETIME NOT NULL , PRIMARY KEY (`payment_id`)) ENGINE = MyISAM;";
?>

