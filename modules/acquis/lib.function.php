<?php
class acquisForm extends GAzieForm
{
    function selectSupplier($name,$val,$strSearch='',$val_hiddenReq='',$mesg,$class='FacetSelect')
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
             $partner = $anagrafica->queryPartners("*", "codice LIKE '".$admin_aziend['masfor']."%' AND codice >".intval($admin_aziend['masfor'].'000000')."  AND ragso1 LIKE '".addslashes($strSearch)."%'","codice ASC");
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
           echo "\t<input tabindex=\"3\" type=\"image\" align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
        }
    }
}
?>