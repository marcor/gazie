<?php
class venditForm extends GAzieForm
{
    function ticketPayments($name,$val,$class='FacetSelect')
    {
        global $gTables;
        $query = 'SELECT codice,descri,tippag FROM `'.$gTables['pagame']."` WHERE tippag = 'D' OR tippag = 'C' ORDER BY tippag";
        echo "\t <select name=\"$name\" class=\"$class\">\n";
        $result = gaz_dbi_query($query);
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if($r['codice'] == $val) {
                $selected = "selected";
            }
            echo "\t\t<option value=\"".$r['codice']."\" $selected >".$r['descri']."</option>\n";
        }
        print "\t </select>\n";
    }

    function getECR_userData($login)
    {
        global $gTables;
        return gaz_dbi_get_row($gTables['cash_register'],'adminid',$login);
    }

    function getECRdata($id)
    {
        global $gTables;
        return gaz_dbi_get_row($gTables['cash_register'],'id_cash',$id);
    }
}

class Agenti
{
  function getPercent($id_agente,$articolo='')
  {
        global $gTables;
        if ($id_agente < 1) {
            return false;
        } else { // devo ricavare la percentuale associata all'articolo(prioritaria) o categoria merceologica
            $value = gaz_dbi_get_row($gTables['artico'], 'codice', $articolo);
            $rs = gaz_dbi_dyn_query($gTables['agenti'].".*,".$gTables['provvigioni'].".*", $gTables['agenti']." LEFT JOIN ".$gTables['provvigioni']." ON ".$gTables['agenti'].".id_agente = ".$gTables['provvigioni'].".id_agente",
                           $gTables['provvigioni'].".id_agente = ".$id_agente." AND ((cod_articolo = '".$articolo."' AND cod_articolo != '') OR (cod_catmer = ".intval($value['catmer'])." AND cod_articolo = ''))",'cod_articolo DESC',0,1);
            $result = gaz_dbi_fetch_array($rs);
            if ($result) {
                return $result['percentuale'];
            } else {
                $result = gaz_dbi_get_row($gTables['agenti'], 'id_agente', $id_agente);
                return $result['base_percent'];
            }
        }
    }
}

class selectAgente extends SelectBox
{
  function output()
  {
        global $gTables;
        $query = "SELECT ".$gTables['agenti'].".id_agente,".$gTables['agenti'].".id_fornitore,".$gTables['anagra'].".ragso1,".$gTables['clfoco'].".codice
                  FROM ".$gTables['agenti']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['agenti'].".id_fornitore = ".$gTables['clfoco'].".codice
                  LEFT JOIN ".$gTables['anagra']." ON ".$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id";
        SelectBox::_output($query, 'ragso1', True,'','',"id_agente",'AGENTE');
  }
}

class Compute
{
  function stampTax($value,$percent,$cents_ceil_round=5)
  {
  if ($cents_ceil_round==0) {
      $cents_ceil_round=5;
  }
  $cents=100*$value*($percent/100+$percent*$percent/10000);
  if ($cents_ceil_round<0) { // quando passo un arrotondamento negativo ritorno il valore di $percent
     return round($percent,2);
  } else {
     return round(ceil($cents/$cents_ceil_round)*$cents_ceil_round/100,2);
  }
  }
}

?>