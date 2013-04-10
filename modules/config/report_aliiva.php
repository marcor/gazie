<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
         (www.facebook.com/antonio.devincentiis.9)
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
echo '<div align="center" class="FacetFormHeaderFont"><a href="admin_aliiva.php?Insert">'.$script_transl['ins_this'].'</a></div>';
echo '<div align="center" class="FacetFormHeaderFont">'.$script_transl['title'].'</div>';
$recordnav = new recordnav($gTables['aliiva'], $where, $limit, $passo);
$recordnav -> output();
echo '<table class="Tlarge">';
$headers = array  (
            $script_transl['codice']=>'codice',
            $script_transl['descri']=>'descri',
            $script_transl['type']=>'tipiva',
            $script_transl['aliquo']=>'aliquo',
            $script_transl['delete']=>''
            );
$linkHeaders = new linkHeaders($headers);
$linkHeaders -> output();
$result = gaz_dbi_dyn_query ('*', $gTables['aliiva'], $where, $orderby, $limit, $passo);
while ($a_row = gaz_dbi_fetch_array($result))
    {
    echo "<tr>";
    echo "<td class=\"FacetDataTD\"><a href=\"admin_aliiva.php?Update&codice=".$a_row["codice"]."\">".$a_row["codice"]."</a> &nbsp</td>";
    echo "<td class=\"FacetDataTD\">".$a_row["descri"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\">".$script_transl['tipiva'][$a_row["tipiva"]]."</td>";
    echo "<td class=\"FacetDataTD\">".$a_row["aliquo"]." &nbsp;</td>";
    echo "<td class=\"FacetDataTD\"><a href=\"delete_aliiva.php?codice=".$a_row["codice"]."\"><center><img src=\"../../library/images/x.gif\" alt=\"Cancella\" border=\"0\"></a></td>";
    echo "</tr>";
    }
?>
</table>
</body>
</html>