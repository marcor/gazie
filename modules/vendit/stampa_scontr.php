<?php
/* $Id: stampa_scontr.php,v 1.8 2011/01/01 11:07:53 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
require("../../library/include/header.php");
$script_transl=HeadMain();
echo "<table class=\"Tsmall\">\n";
echo "<tr><td align=\"center\"><img src=\"../../library/images/vendit.png\"></td></tr><tr>
          <td class=\"FacetFormHeaderFont\">Se stai leggendo questo messaggio &egrave;
perch&eacute; non hai ancora sostituito questo script (stampa_scontr.php) con uno
 realizzato (o fatto realizzare) per interfacciare GAzie ad una stampante fiscale/registratore di cassa.
Siccome esistono diversi modelli di stampanti fiscali con altrettanti numerosi di
 protocolli di comunicazione non ne abbiamo realizzato alcuno, lasciando questa
 incombenza agli utilizzatori di GAzie.\n
Qualora ci fosse qualche produttore e/o rivenditore di stampanti fiscali interessato
alla scrittura per la sua macchina (bastano poche righe di codice), sperando in un
ritorno commerciale sulle vendite del proprio hardware, pu&oacute; inviare il lavoro
direttamente su Sourceforge a <A HREF=\"http://sourceforge.net/tracker2/?group_id=130281&atid=717641\">
QUESTO INDIRIZZO</A>.
Non esiteremo a recepire ed evidenziare rapidamente il contributo sul sito del progetto.
</td></tr></table>\n";
?>
</form>
</body>
</html>