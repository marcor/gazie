<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin(8);
require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<div class="panel panel-info"><h2>
<ul>
<li>Il modulo "LIMS" per GAZIE (Gestione AZIEnda) è sviluppato in base alle specifiche necessità dei laboratori. </li><li>Possono essere realizzati moduli ad-hoc per collegare gli strumenti di analisi, i dispositivi di campionamento, la sensoristica e ricordare le scadenze delle tarature/calibrature e degli accreditamenti.</li> <li>La reportistica, i rapporti di prova, i verbali di campionamento, le metodiche, le verifiche strumentali messe in essere.</li></ul>
<p class="text-info"> Se vuoi realizzare un sistema informativo su misura o per qualsiasi chiarimento puoi contattare l'autore:<br> Antonio De Vincentiis Montesilvano (PE) https://www.devincentiis.it - telefono +39 3383121161</p>
</h2></div>
</div>
<?php
require("../../library/include/footer.php");
?>
