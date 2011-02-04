<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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
*/
require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();

require("../../library/include/header.php");
$script_transl=HeadMain();
?>
<table border="0" cellpadding="3" cellspacing="1" align="center" width="70%">
<tr><td class="FacetFormHeaderFont" align="center" colspan="2">
Queste routine eseguono un controllo formale sugli archivi.
</td></tr>
<tr>
<td class="FacetFormHeaderFont" align="center"><a href="error_rigmoc.php" accesskey="c">Controllo Sbilancio Movimenti Contabili</a></td>
<td class="FacetFormHeaderFont" align="center"><a href="error_protoc.php" accesskey="p">Controllo Protocolli I.V.A.</a></td>
</tr>
</table>
</body>
</html>