<?php
/*
	  --------------------------------------------------------------------------
	  GAzie - Gestione Azienda
	  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
	  (http://www.devincentiis.it)
	  <http://gazie.sourceforge.net>
	  --------------------------------------------------------------------------
	  Vacation Rental è un modulo creato per GAzie da Antonio Germani, Massignano AP
	  Copyright (C) 2018-2021 - Antonio Germani, Massignano (AP)
	  https://www.lacasettabio.it
	  https://www.programmisitiweb.lacasettabio.it
	  --------------------------------------------------------------------------
	  Questo programma e` free software;   e` lecito redistribuirlo  e/o
	  modificarlo secondo i  termini della Licenza Pubblica Generica GNU
	  come e` pubblicata dalla Free Software Foundation; o la versione 2
	  della licenza o (a propria scelta) una versione successiva.

	  Questo programma  e` distribuito nella speranza  che sia utile, ma
	  SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
	  NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
	  veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

	  Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
	  Generica GNU insieme a   questo programma; in caso  contrario,  si
	  scriva   alla   Free  Software Foundation,  Inc.,   59
	  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
	  --------------------------------------------------------------------------
	  # free to use, Author name and references must be left untouched  #
	  --------------------------------------------------------------------------
*/
//upgrade database per il modulo Vacation Rental
$dbname = constant("Database");
global $table_prefix;

// da qui in poi iserire le query che saranno eseguite su ogni azienda con il modulo attivo

/*  >>> esempio di come vanno impostate le query il numero [147] rappresenta la versione dell'update di GAzie
$upgrade_db[147][]="ALTER TABLE ".$table_prefix."_XXXrental_discounts ADD `test2` TINYINT(2) NOT NULL DEFAULT '0' COMMENT 'test update 2';";
*/

$upgrade_db[148][]="INSERT INTO ".$table_prefix."_XXXcompany_config (`id`, `description`, `var`, `val`) VALUES (NULL, 'URL di ritorno da Stripe dopo avvenuto pagamento ', 'vacation_url_stripe', NULL);";
$upgrade_db[148][]="CREATE TABLE `".$dbname.$table_prefix."_XXXrental_feedback_elements` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `element` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , `facility` INT(9) NULL COMMENT 'eventuale riferimento alla struttura' , `status` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;";
$upgrade_db[148][]="CREATE TABLE `".$dbname.$table_prefix."_XXXrental_feedbacks` ( `id` INT(12) NOT NULL AUTO_INCREMENT , `reservation_id` INT(32) NOT NULL , `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `text_reply` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, `house_code` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `customer_anagra_id` INT(9) NOT NULL , `created_date` DATETIME NOT NULL, `modified_date` DATETIME NOT NULL, `status` INT(1) NOT NULL DEFAULT '0' COMMENT '0= in attesa di approvazione\r\n1=approvato\r\n2=bloccato' , PRIMARY KEY (`id`)) ENGINE = MyISAM;";
$upgrade_db[148][]="CREATE TABLE `".$dbname.$table_prefix."_XXXrental_feedback_scores` ( `id` INT(12) NOT NULL AUTO_INCREMENT , `score` TINYINT(3) NOT NULL , `feedback_id` INT(12) NOT NULL , `element_id` INT(11) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM; ";
?>
