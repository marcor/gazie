<?php
/*
  --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-20223 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)
  --------------------------------------------------------------------------
  --------------------------------------------------------------------------
Copyright (C) - Antonio Germani Massignano (AP) https://www.lacasettabio.it - telefono +39 340 50 11 912
  --------------------------------------------------------------------------
   --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2023 - Antonio De Vincentiis Montesilvano (PE)
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
/////////////////////////////////////////////////

/*
Per evitare che durante gli aggiornamenti di GAzie questo file venga sovrascritto perdendo le impostazioni fatte si prega di
copiare questo stesso file e salvarlo con il nome 'personal_manual_settings.php'
In questo caso le impostazioni potranno essere fatte solo nel 'personal_manual_settings.php'
*/

/////////////////////////////////////////////////

if (count(get_included_files()) ==1 OR basename($_SERVER['PHP_SELF']) == basename(__FILE__)){// impedisce accesso diretto
  exit('Restricted Access');
}elseif (file_exists('personal_manual_settings.php') && basename(__FILE__)<>'personal_manual_settings.php'){
  include_once("personal_manual_settings.php");
}else{
  $idDB="_001";// ID azienda per stabilire a quale ID azienda del data base dovrà accedere il front-end del sito web
  $token="yourtokenword"; // inserisci una parola chiave, che verrà usata dagli script, per bloccare gli accessi diretti.
  $smtp_pass="password"; // la password e-mail smtp (con la nuova criptazione non posso più prenderla dal DB)
}
?>
