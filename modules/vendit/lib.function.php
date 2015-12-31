<?php
class venditForm extends GAzieForm
{
	/* Questa funzione dovrebbe essere perfettamente inutile poichè è un duplicato di quella che sta nella classe GAzieForm
	   Se questa classe estende GAZieForm, ne eredita tutto, di conseguenza è inutile ripetere codice
	*/
     /* function CalendarPopup($name, $day, $month, $year, $class = 'FacetSelect', $refresh = '') {
      global $script_transl;
      if (!empty($refresh)) {
         $refresh = "onchange=\"this.form.hidden_req.value='$refresh'; this.form.submit();\"";
      }

      echo "\t <select name=\"" . $name . "_D\" id=\"" . $name . "_D\" class=\"$class\" $refresh>\n";
      for ($i = 1; $i <= 31; $i++) {
         $selected = "";
         if ($i == $day) {
            $selected = "selected";
         }
         echo "\t\t <option value=\"$i\" $selected >$i</option>\n";
      }
      echo "\t </select>\n";
      echo "\t <select name=\"" . $name . "_M\" id=\"" . $name . "_M\" class=\"$class\" $refresh>\n";
      for ($i = 1; $i <= 12; $i++) {
         $selected = "";
         if ($i == $month) {
            $selected = "selected";
         }
         $month_name = ucwords(strftime("%B", mktime(0, 0, 0, $i, 1, 0)));
         echo "\t\t <option value=\"$i\"  $selected >$month_name</option>\n";
      }
      echo "\t </select>\n";
      echo "\t <input type=\"text\" name=\"" . $name . "_Y\" id=\"" . $name . "_Y\" value=\"" . $year . "\" class=\"$class\"  maxlength=\"4\" size=\"4\" $refresh />\n ";
      echo "\t <a class=\"btn btn-default btn-sm\" href=\"#\" onClick=\"setDate('$name'); return false;\" TITLE=\"" . $script_transl['changedate'] . "\" name=\"anchor\" id=\"anchor\">\n";
      //echo "\t<img border=\"0\" src=\"../../library/images/cal.png\"></A>\n";
      echo '<i class="glyphicon glyphicon-calendar"></i></a>';
   }*/


    function ticketPayments($name,$val,$class='FacetSelect')
    {
        global $gTables;
        $query = 'SELECT codice,descri,tippag FROM `'.$gTables['pagame']."` WHERE tippag = 'D' OR tippag = 'C' OR tippag = 'K' ORDER BY tippag";
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

    function selectCustomer($name,$val,$strSearch='',$val_hiddenReq='',$mesg,$class='FacetSelect')
    {
        global $gTables,$admin_aziend;
        $anagrafica = new Anagrafica();
        if ($val>100000000) { //vengo da una modifica della precedente select case quindi non serve la ricerca
              $partner = $anagrafica->getPartner($val);
              echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
              echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"".substr($partner['ragso1'],0,8)."\">\n";
              echo "\t<input type=\"submit\" value=\"".$partner['ragso1']." ".$partner["ragso2"]." ".$partner["citspe"]." (".$partner["codice"].")\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
        } else {
          if (strlen($strSearch) >= 2) { //sto ricercando un nuovo partner
             echo "\t<select tabindex=\"1\" name=\"$name\" class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
             echo "<option value=\"0\"> ---------- </option>";
             $partner = $anagrafica->queryPartners("*", "codice LIKE '".$admin_aziend['mascli']."%' AND codice >".intval($admin_aziend['mascli'].'000000')."  AND ragso1 LIKE '".addslashes($strSearch)."%'","codice ASC");
             if (count($partner) > 0) {
                   foreach ($partner as $r) {
                         $selected = '';
                         if ($r['codice'] == $val) {
                             $selected = "selected";
                         }
                         echo "\t\t <option value=\"".$r['codice']."\" $selected >".$r['ragso1']." ".$r["ragso2"]." ".$r["citspe"]."</option>\n";
                   }
                   echo "\t </select>\n";
              } else {
                   $msg = $mesg[0];
              }
           } else {
              $msg = $mesg[1];
              echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
           }
           echo "\t<input tabindex=\"2\" type=\"text\" id=\"search_$name\" name=\"search[$name]\" value=\"".$strSearch."\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
           if (isset($msg)) {
              echo "<input type=\"text\" style=\"color: red; font-weight: bold;\" size=\"".strlen($msg)."\" disabled value=\"$msg\">";
           }
           //echo "\t<input tabindex=\"3\" type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
		   /** ENRICO FEDELE */
		   /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
		   echo '<button type="submit" class="btn btn-default btn-sm" name="search_str" tabindex="3"><i class="glyphicon glyphicon-search"></i></button>';
		   /** ENRICO FEDELE */
        }
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
?>