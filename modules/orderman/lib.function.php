<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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

class ordermanForm extends GAzieForm {

    function get_magazz_ordinati ( $codice, $tip='AOR' ) {
        global $gTables;
    
        $column = $gTables['artico'].".codice,
            ".$gTables['artico'].".good_or_service,
            ".$gTables['rigbro'].".id_tes,
            ".$gTables['rigbro'].".codart,
            ".$gTables['rigbro'].".unimis,
            ".$gTables['rigbro'].".quanti,
            ".$gTables['tesbro'].".tipdoc";
    
        $tables = $gTables['artico']."
            INNER JOIN ".$gTables['rigbro']."
                ON ".$gTables['artico'].".codice = ".$gTables['rigbro'].".codart
            INNER JOIN ".$gTables['tesbro']."
                ON ".$gTables['rigbro'].".id_tes = ".$gTables['tesbro'].".id_tes";
    
        $where = $gTables['artico'].".good_or_service = 0
            AND ".$gTables['rigbro'].".id_doc = 0
            AND ".$gTables['artico'].".codice = '".$codice."'
            AND ".$gTables['tesbro'].".tipdoc = '".$tip."'";
    
    
        $orderby = $gTables['artico'].".codice ASC";
        $limit = "0";
        $passo = "999";
    
        $restemp = gaz_dbi_dyn_query($column, $tables, $where, $orderby, $limit, $passo);
        $totord = 0;
        while ($row = gaz_dbi_fetch_array($restemp)) {
            $totord += $row['quanti'];
        }
        return $totord;
    }

}

?>