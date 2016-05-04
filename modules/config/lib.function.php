<?php

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
}
?>