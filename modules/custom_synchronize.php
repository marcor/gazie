<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
 
/*
------------------------------------------------------------------------------------------
	**Antonio Germani**
	Questo file serve per sincronizzare un sito web con il gestionale GAzie con un solo click.
	Ad esempio, è possibile eseguire un download degli ordini dal sito web oppure aggiornare le
	quantità degli articoli e ogni altra cosa da Gazie al sito web.
	Ogni sito web ha bisogno di un codice specifico per interfacciarsi con GAzie.
------------------------------------------------------------------------------------------
*/




// INSERIRE QUì IL PROPRIO CODICE PERSONALIZZATO



 
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>