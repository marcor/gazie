<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
  (http://www.devincentiis.it)
  <http://gazie.sourceforge.net>
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
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
$menu_data = array('m1' => array('link' => "docume_shop-synchronize.php"), 
                        'm2' => array(  1 => array('link' => "synchronize.php", 'weight' => 1)
                         ),
                        'm3' => array()
                );

$module_class='fas fas fa-exchange-alt';

// Li commento, ma possono servire di esempio a chi clona e personalizza questo modulo per altri ecommerce
//$update_db[]="INSERT INTO ".$gTables['company_data']." (`description`, `var`) VALUES ('URL per API login dell\'ecommerce', 'oc_api_url')";
//$update_db[]="INSERT INTO ".$gTables['company_data']." (`description`, `var`) VALUES ('Nome utente per accesso ad API ecommerce', 'oc_api_username')";
//$update_db[]="INSERT INTO ".$gTables['company_data']." (`description`, `var`) VALUES ('Chiave per accesso ad API ecommerce', 'oc_api_key')";

// valorizzo automaticamente in configurazione azienda con il nome del modulo
$update_db[]="UPDATE ".$gTables['aziend']." SET `sync_ecom_mod`='shop-synchronize' WHERE `codice`=".$user_data['company_id'];
?>