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

class venditCalc extends Compute
{
  function contractCalc($id_contract)
  {
    //recupero il contratto da calcolare
    global $gTables,$admin_aziend;
    $this->contract_castle=array();
    $contract = gaz_dbi_get_row($gTables['contract'],"id_contract",$id_contract);
    $this->contract_castel[$contract['vat_code']]['impcast']=$contract['current_fee'];
    
    $result = gaz_dbi_dyn_query('*', $gTables['contract_row'], $gTables['contract_row'].'.id_contract ='.$id_contract, $gTables['contract_row'].'.id_row');
    while ($row = gaz_dbi_fetch_array($result)) {
        $r_val = CalcolaImportoRigo($row['quanti'], $row['price'],array($row['discount']));
        if (!isset($this->contract_castel[$row['vat_code']])) {
            $this->contract_castel[$row['vat_code']]['impcast']=0.00;
        }
        $this->contract_castel[$row['vat_code']]['impcast']+=$r_val;
    }
    $this->add_value_to_VAT_castle($this->contract_castel,444,$admin_aziend['taxstamp_vat']);
  }    
}

class Schedule 
{
    function Schedule()
        {
            $this->target=0;
        }
    
    function setPartnerTarget($account)
        {
            $this->target=$account;
        }
    function getScheduleEntries($ob=0)
        {
            global $gTables;
            switch ($ob) {
                  case 1:
                    $orderby = "expiry DESC, codice, id_tesdoc_ref, caucon, datreg, numdoc ASC ";
                  break;
                  case 2:
                    $orderby = "ragso1, id_tesdoc_ref,caucon, datreg, numdoc ASC ";
                  break;
                  case 3:
                    $orderby = "ragso1 DESC, id_tesdoc_ref,caucon, datreg, numdoc ASC ";
                  break;
                  default:
                    $orderby = "expiry, codice, id_tesdoc_ref, caucon, datreg, numdoc ASC ";
            }
            $select = "*, ".$gTables['tesmov'].".*, ".$gTables['clfoco'].".descri AS ragsoc";
            if ($this->target==0 ) {
                $where = " 1";
            } else {
                $where = $gTables['clfoco'].".codice = ".$this->target;
            }
            $table = $gTables['paymov']." LEFT JOIN ".$gTables['rigmoc']." ON (".$gTables['paymov'].".id_rigmoc_pay = ".$gTables['rigmoc'].".id_rig OR ".$gTables['paymov'].".id_rigmoc_doc = ".$gTables['rigmoc'].".id_rig )"
                    ."LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes "
                    ."LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['clfoco'].".codice = ".$gTables['rigmoc'].".codcon "
                    ."LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra ";
    
            $this->Entries=array();
            $rs=gaz_dbi_dyn_query ($select, $table, $where, $orderby);
            while ($r = gaz_dbi_fetch_array($rs)) {
                $this->Entries[] = $r;
            }
    }
    function getStatus($id_tesdoc_ref)
    {
        global $gTables;
        $sqlquery= "SELECT SUM(amount*(id_rigmoc_doc>0)- amount*(id_rigmoc_pay>0)) AS diff_paydoc, SUM(amount*(id_rigmoc_pay>0)) AS pay, SUM(amount*(id_rigmoc_doc>0))AS doc 
            FROM ".$gTables['paymov']."
            WHERE id_tesdoc_ref = '".$id_tesdoc_ref."' GROUP BY id_tesdoc_ref";
        $rs = gaz_dbi_query($sqlquery);
        $this->Status=gaz_dbi_fetch_array($rs);
    }
}

?>