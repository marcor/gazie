<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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

class configForm extends GAzieForm {

    function selSpecieAmmortamentoMin($nameFileXML, $name, $val) {
        $refresh = '';
        if (file_exists('../../library/include/' . $nameFileXML)) {
            $xml = simplexml_load_file('../../library/include/' . $nameFileXML);
        } else {
            exit('Failed to open: ../../library/include/' . $nameFileXML);
        }
        echo "\t <select id=\"$name\" name=\"$name\" style=\"width: 350px; font-height:0.4em;\"  >\n";
        echo "\t\t <option value=\"\">-----------------</option>\n";
        foreach ($xml->gruppo as $vg) {
            echo "\t <optgroup label=\"" . $vg->gn[0] . '-' . $vg->gd[0] . "\" >\n";
            foreach ($vg->specie as $v) {
                $selected = '';
                if ($vg->gn[0] . $v->ns[0] == $val) {
                    $selected = "selected";
                }
                echo "\t\t <option value=\"" . $vg->gn[0] . $v->ns[0] . "\" $selected >• " . $v->ns[0] . " - " . $v->ds[0] . "</option>\n";
            }
            echo "\t </optgroup>\n";
        }
        echo "\t </select>\n";
    }

    function selThemeDir($name, $val) {
        echo '<select name="' . $name . '" class="form-control input-sm">';
        foreach (glob('../../library/theme/*', GLOB_ONLYDIR) as $dir) {
            $selected = "";
            if (substr($dir,5) == $val) {
                $selected = " selected ";
            }
            echo "<option value=\"" . substr($dir,5) . "\"" . $selected . ">" . substr($dir,5) . "</option>\n";
        }
        echo "</select>\n";
    }

}

?>