<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2015 - Antonio De Vincentiis Montesilvano (PE)
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
if (isset($_SERVER['SCRIPT_FILENAME']) && (str_replace('\\','/',__FILE__) == $_SERVER['SCRIPT_FILENAME'])) {
    exit('Accesso diretto non consentito') ;
}

connectToDB();

session_cache_limiter('nocache');
$scriptname=basename($_SERVER['PHP_SELF']);
$direttorio = explode("/",dirname($_SERVER['PHP_SELF']));
$module = array_pop($direttorio);
$radixarr = array_diff($direttorio,array('modules',$module,''));
$radix= implode('/',$radixarr);
if(strlen($radix) > 1){
  session_name(implode($radixarr));
} else {
  session_name(_SESSION_NAME);
}
session_start();


function gaz_format_number($number=0)
{
    global $gTables;
    $currency = gaz_dbi_get_row($gTables['admin'].' LEFT JOIN '.$gTables['aziend'].' ON '.$gTables['admin'].'.enterprise_id = '.$gTables['aziend'].'.codice
                                                    LEFT JOIN '.$gTables['currencies'].' ON '.$gTables['currencies'].'.id = '.$gTables['aziend'].'.id_currency', "Login", $_SESSION["Login"]);
    return number_format(floatval($number),$currency['decimal_place'],$currency['decimal_symbol'],$currency['thousands_symbol']);
}

function gaz_format_date($date,$db=false)
{
    if ($db){
        $uts=mktime( 0,0,0,intval(substr($date,3,2)),intval(substr($date,0,2)),intval(substr($date,6,4)) );
        return date("Y-m-d",$uts);
    } else {
        $uts=mktime( 0,0,0,intval(substr($date,5,2)),intval(substr($date,8,2)),intval(substr($date,0,4)) );
        return date("d-m-Y",$uts);
    }
}

function gaz_format_datetime($date)
{
  $uts=mktime(substr($date,11,2),substr($date,14,2),substr($date,17,2),substr($date,5,2),substr($date,8,2),substr($date,0,4));
  return date("d-m-Y H:i:s",$uts);
}

function gaz_html_call_tel($tel_n)
{
    if ($tel_n!="_") {
		preg_match_all("/([\d]+)/",$tel_n,$r);
		$ret = '<a href="tel:'.implode("", $r[0]).'" >'.$tel_n."</a>\n";
	} else {
		$ret = $tel_n;
	}
    return $ret;
}

function gaz_html_ae_checkiva($paese,$pariva)
{
	$htmlpariva = "<a target=\"_blank\" href=\"http://www1.agenziaentrate.gov.it/servizi/vies/vies.htm?s=".$paese."&p=".$pariva."\">".$paese." ".$pariva."</a>";
	return $htmlpariva;	
}

function gaz_format_quantity($number,$comma=false,$decimal=false)
{
    $number =  sprintf("%.3f",preg_replace("/\,/",'.',$number)); //max 3 decimal
    if (!$decimal) { // decimal is not defined (depreceted in recursive call)
        global $gTables;
        $config = gaz_dbi_get_row($gTables['aziend'],'codice',1);
        $decimal = $config['decimal_quantity'];
    }
    if ($decimal > 3){ //float
       if ($comma == true){
          return preg_replace("/\./",',',floatval($number));
       } else {
          return floatval($number);
       }
    } else { //decimal defined
       if ($comma == true){
          return number_format($number, $decimal,',','.');
       } else {
          return number_format($number, $decimal,'.','');
       }
    }
}

function gaz_set_time_limit ($time)
{
    global $disable_set_time_limit;
    if (!$disable_set_time_limit) {
        set_time_limit ($time);
    }
}

function CalcolaImportoRigo($quantita, $prezzo, $sconto, $decimal=2)
{
   if (is_array($sconto)){
     $res = 1;
     foreach ($sconto as $val) {
          $res -= $res*$val/100;
     }
     $res = 1 - $res;
   } else {
     $res=$sconto/100;
   }
   return round($quantita * ($prezzo - $prezzo * $res),$decimal);
}

//
// La funzione table_prefix_ok() serve a determinare se il prefisso
// delle tabelle è valido, secondo lo schema di Gazie, oppure no.
// In pratica, si verifica che inizi con la stringa `gaz' e può
// continuare con lettere minuscole e cifre numeriche, fino
// a un massimo di ulteriori nove caratteri
//
function table_prefix_ok ($table_prefix)
{
  if (preg_match ("/^[g][a][z][a-z0-9]{0,9}$/", $table_prefix) == 1)
    {
      return TRUE;
    }
  else
    {
      return FALSE;
    }
}

//
// La funzione table_prefix_get() serve a estrapolare il prefisso
// del nome di una tabella di Gazie, usando le stesse regole
// della funzione table_prefix_ok() per tale individuazione.
// Il riconoscimenti si basa soprattutto sul fatto che il prefisso
// dei nomi delle tabelle non possa contenere il trattino basso.
//
// ATTENZIONE: il funzionamento corretto di questa funzione
//             è ancora da verificare e viene aggiunta solo
//             come suggerimento, in abbinamento alla funzione
//             table_prefix_ok().
//
function table_prefix_get ($table_name)
{
  $matches;
  if (preg_match ("/^([g][a][z][a-z0-9]{0,9})[_]/", $table_name, $matches) == 1)
    {
      return $matches[1];
    }
  else
    {
      return "";
    }
}

//
// Una funzione per segnalare errori fatali in modo molto semplice.
//
function message_fatal_error ($text)
{
  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//IT\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
  echo "<html>\n";
  echo "<head>\n";
  echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
  echo "<meta name=\"author\" content=\"Antonio De Vincentiis http://www.devincentiis.it\">\n";
  echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../library/style/stylesheet.css\">\n";
  echo "<link rel=\"shortcut icon\" href=\"../../library/images/favicon.ico\">\n";
  echo "<title>Fatal error</title>\n";
  echo "</head>\n";
  echo "<body>\n";
  echo "<h1>Fatal error</h1>\n";
  echo "<p><strong>$text</strong></p>\n";
  echo "</body>\n";
  echo "</html>\n";
}

class Config
{
    function Config()
    {
        global $gTables;
        $results = gaz_dbi_query ("SELECT variable, cvalue FROM " . $gTables['config']);
        while($row = gaz_dbi_fetch_object($results)) {
            $this->{$row->variable} = $row->cvalue;
        }
    }

    function getValue($variable)
    {
        return $this->{$variable};
    }

    function setValue($variable, $value)
    {
        $this->{$variable} = $value;
        //
        // TODO: Inserimento in tabella
        //
    }

} // end Config

class configTemplate
{
    function configTemplate()
    {
        global $gTables;
        $row = gaz_dbi_get_row($gTables['aziend'],'codice',$_SESSION['enterprise_id']);
        $this->template=$row['template'];
    }
}

class Anagrafica
{
    function Anagrafica ()
    {
        global $gTables;
        $this->gTables = $gTables;
        $this->partnerTables = $gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id';
    }

    function getPartner ($idClfoco)
    {
        return gaz_dbi_get_row($this->partnerTables,"codice", $idClfoco);
    }

    function getPartnerData ($idAnagra,$acc=1)
    {
        global $table_prefix;
        $rs_co = gaz_dbi_dyn_query ('codice', $this->gTables['aziend'],1);
        $partner_data = array();
        $partner = array();
        while ($co = gaz_dbi_fetch_array($rs_co)) {
              $rs_partner = gaz_dbi_query ('SELECT * FROM '.$table_prefix.sprintf('_%03d',$co['codice']).'clfoco WHERE '.
                                           ' codice BETWEEN '.$acc.'00000001 AND '.$acc.'99999999 AND id_anagra ='.$idAnagra.'  LIMIT 1');
              $r_p = gaz_dbi_fetch_array($rs_partner);
              if ($r_p) {
                 $r_p['id_aziend']=$co['codice'];
                 $partner_data[] = $r_p;
              }
        }
        if (sizeof($partner_data)==0) {  // se non ci sono tra i partner omogenei controllo su tutti
            $rs_co = gaz_dbi_dyn_query ('codice', $this->gTables['aziend'],1);
            while ($co = gaz_dbi_fetch_array($rs_co)) {
              $rs_partner = gaz_dbi_query ('SELECT * FROM '.$table_prefix.sprintf('_%03d',$co['codice']).'clfoco WHERE '.
                                           ' id_anagra ='.$idAnagra.'  LIMIT 1');
              $r_p = gaz_dbi_fetch_array($rs_partner);
              if ($r_p) {
                 $r_p['id_aziend']=$co['codice'];
                 $partner_data[] = $r_p;
              }
            }
        }
        if (sizeof($partner_data)==0) { // e' un'anagrafica isolata inserisco una tabella vuota
           $partner_data[0]=gaz_dbi_fields('clfoco');
           $partner_data[0]['last_modified']='isolated';
           $partner_data[0]['id_anagra']=$idAnagra;
        }
        foreach ($partner_data as $k=>$row) {
                 $partner[$row['last_modified']] = $row;
        }
        ksort($partner);
        $r_a=gaz_dbi_get_row($this->gTables['anagra'],'id',$idAnagra);
        $data = array_merge(array_pop($partner),$r_a);
        unset($data['codice']);
        return $data ;
    }

    function queryPartners ($select, $where=1, $orderby=2, $limit=0, $passo=1900000)
    {
        $result = gaz_dbi_dyn_query ($select, $this->partnerTables, $where, $orderby, $limit, $passo);
        $partners = array();
        while ($row = gaz_dbi_fetch_array($result)) {
            $partners[] = $row;
        }
        return $partners;
    }

    function updatePartners($codice,$newValue)
    {
        $newValue['descri'] = $newValue['ragso1'].' '.$newValue['ragso2'];
        gaz_dbi_table_update('clfoco',$codice,$newValue);
        gaz_dbi_table_update('anagra',array('id',$newValue['id_anagra']),$newValue);
    }

    function anagra_to_clfoco($v,$m)
    {
        $last_partner = gaz_dbi_dyn_query("*",$this->gTables['clfoco'],'codice BETWEEN '.$m.'000001 AND '.$m.'999999',"codice DESC",0,1);
        $last = gaz_dbi_fetch_array($last_partner);
        if ($last) {
           $v['codice']=$last['codice']+1;
        } else {
           $v['codice']= $m.'000001';
        }
        $v['descri'] = $v['ragso1'];
        if (isset($v['ragso2'])) {
           $v['descri'] .= $v['ragso2'];
        }
        gaz_dbi_table_insert('clfoco',$v);
        return $v['codice'];
    }

    function insertPartner($v)
    {
        $v['descri'] = $v['ragso1'];
        if (isset($v['ragso2'])) {
           $v['descri'] .= $v['ragso2'];
        }
        gaz_dbi_table_insert('anagra',$v);
        $v['id_anagra']=gaz_dbi_last_id();
        gaz_dbi_table_insert('clfoco',$v);
    }

    function deletePartner ($idClfoco)
    {
        global $gTables;
        gaz_dbi_del_row($gTables['clfoco'], 'codice', $idClfoco);
    }

}

//===============================================================================
// classe generica per la generazione di select box
//================================================================================
class SelectBox
{
    var $name;
    // assegno subito il nome della select box
    function SelectBox($name)
    {
        $this -> name = $name;
    }

    function setSelected($selected)
    {
        $this->selected = $selected;
        }

    function addSelected($selected)
    {
        $this->setSelected($selected);
        }

    function _output($query, $index1, $empty=False, $bridge='', $index2='', $key='codice', $refresh='')
    {
        if (!empty($refresh)){
            $refresh = "onchange=\"this.form.hidden_req.value='$refresh'; this.form.submit();\"";
        }
        echo "\t <select name=\"$this->name\" class=\"FacetSelect\" $refresh >\n";
        if ($empty) {
            echo "\t\t <option value=\"\"></option>\n";
        }
        $result = gaz_dbi_query($query);
        while ($a_row = gaz_dbi_fetch_array($result)) {
            $selected = "";
            if($a_row[$key] == $this->selected) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"".$a_row[$key]."\" $selected >";
            if (empty($index2)) {
                echo substr($a_row[$index1],0,43)."</option>\n";
            } else {
                echo substr($a_row[$index1],0,38).$bridge.substr($a_row[$index2],0,35)."</option>\n";
            }
        }
        echo "\t </select>\n";
    }
}

// classe per la generazione di select box dei clienti e fornitori (partner commerciali)
class selectPartner extends SelectBox
{
    function selectPartner($name)
    {
        global $gTables;
        $this->gTables = $gTables;
        $this->name = $name;
        $this->what="a.id AS id,pariva,codfis,a.citspe AS citta, ragso1 AS ragsoc,
                     (SELECT ".$this->gTables['clfoco'].".codice FROM ".$this->gTables['clfoco']." WHERE a.id=".$this->gTables['clfoco'].".id_anagra LIMIT 1) AS codice,
                     (SELECT ".$this->gTables['clfoco'].".status FROM ".$this->gTables['clfoco']." WHERE a.id=".$this->gTables['clfoco'].".id_anagra LIMIT 1) AS status, 0 AS codpart ";
    }

    function setWhat($m)
    {
        $this->what="a.id AS id,pariva,codfis,a.citspe AS citta, ragso1 AS ragsoc,
                     (SELECT ".$this->gTables['clfoco'].".codice FROM ".$this->gTables['clfoco']." WHERE a.id=".$this->gTables['clfoco'].".id_anagra AND ".$this->gTables['clfoco'].".codice BETWEEN ".$m."000001 AND ".$m."999999 LIMIT 1) AS codpart ,
                     (SELECT ".$this->gTables['clfoco'].".codice FROM ".$this->gTables['clfoco']." WHERE a.id=".$this->gTables['clfoco'].".id_anagra LIMIT 1) AS codice,
                     (SELECT ".$this->gTables['clfoco'].".status FROM ".$this->gTables['clfoco']." WHERE a.id=".$this->gTables['clfoco'].".id_anagra LIMIT 1) AS status ";
    }

    function queryAnagra($where=1)
    {
       $rs=gaz_dbi_dyn_query($this->what,$this->gTables['anagra'].' AS a',$where,"ragsoc ASC");
       $anagrafiche = array();
       while ($r=gaz_dbi_fetch_array($rs)) {
            $anagrafiche[] = $r;
       }
       return $anagrafiche;
    }

    function output($mastro,$cerca)
    {
        global $script_transl;
        $msg = "";
        $put_anagra='';
        $tabula = " tabindex=\"1\" ";
        if (strlen($cerca) >= 2) {
            if (is_numeric($cerca)){                      //ricerca per partita iva
              $partners = $this->queryAnagra(" pariva = ".intval($cerca));
            } elseif (is_numeric(substr($cerca,6,2))) {   //ricerca per codice fiscale
              $partners = $this->queryAnagra(" a.codfis LIKE '%".addslashes($cerca)."%'");
            } else {                                      //ricerca per ragione sociale
              $partners = $this->queryAnagra(" a.ragso1 LIKE '".addslashes($cerca)."%'");
            }
            $numclfoco = sizeof($partners);
            if ($numclfoco > 0) {
                $tabula =" ";
                echo "\t <select name=\"$this->name\" class=\"FacetSelect\">\n";
                while (list($key, $a_row) = each($partners)) {
                    $selected = "";
                    $style='';
                    if($a_row["codice"] == $this->selected) {
                        $selected = "selected";
                        if ($a_row["codice"] < 1) {
                          $put_anagra="\t<input type=\"hidden\" name=\"put_anagra\" value=\"".$a_row['id']."\">\n";
                        }
                    }
                    if($a_row["codice"]<1) {
                        $style = 'style="background:#FF0000";';
                    }
                    echo "\t\t <option value=\"".$a_row["codice"]."\" $selected $style>".$a_row["ragsoc"]."&nbsp;".$a_row["citta"]."</option>\n";
                }
                echo "\t </select>\n";
            } else {
                $msg = $script_transl['notfound']."!\n";
                echo "\t<input type=\"hidden\" name=\"$this->name\" value=\"\">\n";
            }
        } else {
                $msg = $script_transl['minins']." 2 ".$script_transl['charat']."!\n";
                echo "\t<input type=\"hidden\" name=\"$this->name\" value=\"\">\n";
        }
        echo $put_anagra;
        echo "\t<input type=\"text\" name=\"ragso1\" ".$tabula." accesskey=\"e\" value=\"".$cerca."\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
        echo $msg;
        echo "\t<input type=\"image\" align=\"middle\" accesskey=\"c\" ".$tabula." name=\"clfoco\" src=\"../../library/images/cerbut.gif\" title=\"".$script_transl['search']."\">\n";
    }

    function selectDocPartner($name,$val,$strSearch='',$val_hiddenReq='',$mesg,$m=0,$anonimo=-1,$tab=1)
    {
      /* se passo $m=-1 ottengo tutti i partner nel piano dei conti indistintamente
         passare false su $tab se non si vuole la tabulazione
      */
      global $gTables;
      $tab1 = '';
      $tab2 = '';
      $tab3 = '';
      if ($tab){
         $tab1 = ' tabindex="'.$tab.'"';
         $tab2 = ' tabindex="'.($tab+1).'"';
         $tab3 = ' tabindex="'.($tab+2).'"';
      }
      if ($val>100000000) { //vengo da una modifica della precedente select case quindi non serve la ricerca
            $partner=gaz_dbi_get_row($gTables['clfoco'].' LEFT JOIN '.$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id',"codice",$val);
            echo "\t<input type=\"submit\" value=\"&rArr;\" name=\"fantoccio\" disabled>\n";
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"".substr($partner['ragso1'],0,8)."\">\n";
            echo "\t<input type=\"submit\" tabindex=\"999\" value=\"".$partner['ragso1']."\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
      } elseif (preg_match("/^id_([0-9]+)$/",$val,$match)) { // e' stato selezionata la sola anagrafica
            $partner=gaz_dbi_get_row($gTables['anagra'],'id',$match[1]);
            echo "\t<input type=\"submit\" value=\"&rArr;\" name=\"fantoccio\" disabled>\n";
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"".substr($partner['ragso1'],0,8)."\">\n";
            echo "\t<input type=\"submit\" tabindex=\"999\" style=\"background:#FFBBBB\"; value=\"".$partner['ragso1']."\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
      } elseif ($val==$anonimo) { // e' un cliente anonimo
            echo "\t<input type=\"submit\" value=\"&rArr;\" name=\"fantoccio\" disabled>\n";
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"\">\n";
            echo "\t<input type=\"submit\" tabindex=\"999\" value=\"".$mesg[5]."\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
      } else {
         if (strlen($strSearch) >= 2) { //sto ricercando un nuovo partner
            if ($m>100){ //ho da ricercare nell'ambito di un mastro
              $this->setWhat($m);
            }
            if (is_numeric($strSearch)){                      //ricerca per partita iva
              $partner = $this->queryAnagra(" pariva = ".intval($strSearch));
            } elseif (substr($strSearch,0,1) == '@') { //ricerca conoscendo il codice cliente
			  $temp_agrafica = new Anagrafica();
			  $codicetemp = intval($m*1000000+substr($strSearch,1)); 
			  $last=$temp_agrafica->getPartner($codicetemp); 	
			  $codicecer=$last['id_anagra'];				  
			  $partner = $this->queryAnagra(" a.id = ".intval($codicecer));
              //echo "---".$m."-".$codicetemp."-".$codicecer; //debug
            } elseif (substr($strSearch,0,1) == '#') { //ricerca conoscendo il codice cliente
			  $partner = $this->queryAnagra(" a.fe_cod_univoco LIKE '%".addslashes(substr($strSearch,1))."%'");
            } elseif (is_numeric(substr($strSearch,6,2))) {   //ricerca per codice fiscale
              $partner = $this->queryAnagra(" a.codfis LIKE '%".addslashes($strSearch)."%'");
            } else {                                      //ricerca per ragione sociale
              $partner = $this->queryAnagra(" a.ragso1 LIKE '".addslashes($strSearch)."%'");
            }
            if (count($partner) > 0) {
                 echo "\t<select name=\"$name\" $tab1 class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
                 echo "<option value=\"0\"> ---------- </option>";
                 if ($anonimo>100){
                    echo "<option value=\"$anonimo\">".$mesg[5]."</option>";
                 }
                 preg_match("/^id_([0-9]+)$/",$val,$match);
                 foreach ($partner as $r) {
                       if ($r['codpart']>0){
                         $r['codice']=$r['codpart'];
                       }
                       $style='';
                       $selected = '';
                       $disabled='';
                       if($r['status']=='HIDDEN') {
                        $disabled=' disabled ';
                       }
                       if (isset($match[1]) && $match[1]==$r['id']) {
                           $selected = "selected";
                       } elseif ($r['codice']==$val && $val >0) {
                           $selected = "selected";
                       }
                       if ($m < 0 ){ // vado cercando tutti i partner del piano dei conti
                          if ($r["codice"]<1) {  // disabilito le anagrafiche presenti solo in altre aziende
                               $disabled=' disabled ';
                               $style = 'style="background:#FF6666";';
                          }
                       } elseif($r["codice"]<1) {
                           $style = 'style="background:#FF6666";';
                           $r['codice'] = 'id_'.$r['id'];
                       } elseif(substr($r["codice"],0,3)!=$m) {
                           $style = 'style="background:#FFBBBB";';
                           $r['codice'] = 'id_'.$r['id'];
                       }
                       echo "\t\t <option $style value=\"".$r['codice']."\" $selected $disabled>".$r["ragsoc"]." ".$r["citta"]."</option>\n";
                 }
                 echo "\t </select>\n";
            } else {
                 $msg = $mesg[0];
                 echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            }
         } else {
            $msg = $mesg[1];
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
         }
         echo "\t<input type=\"text\" $tab2 id=\"search_$name\" name=\"search[$name]\" value=\"".$strSearch."\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
         if (isset($msg)) {
            echo "<input type=\"text\" style=\"color: red; font-weight: bold;\" size=\"".strlen($msg)."\" disabled value=\"$msg\">\n";
         }
         echo "\t<input type=\"image\" $tab3 align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
       }
    }

    function selectAnagra($name,$val,$strSearch='',$val_hiddenReq='',$mesg,$tab=false)
    {
      global $gTables;
      $tab1 = '';
      $tab2 = '';
      $tab3 = '';
      if ($tab){
         $tab1 = ' tabindex="'.$tab.'"';
         $tab2 = ' tabindex="'.($tab+1).'"';
         $tab3 = ' tabindex="'.($tab+2).'"';
      }
      if ($val>1) { //vengo da una modifica della precedente select case quindi non serve la ricerca
            $partner=gaz_dbi_get_row($gTables['anagra'],"id",$val);
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            echo "\t<input type=\"hidden\" name=\"search[$name]\" value=\"".substr($partner['ragso1'],0,8)."\">\n";
            echo "\t<input type=\"submit\" tabindex=\"999\" value=\"".$partner['ragso1']."\" name=\"change\" onclick=\"this.form.$name.value='0'; this.form.hidden_req.value='change';\" title=\"$mesg[2]\">\n";
      } else {
         if (strlen($strSearch) >= 2) { //sto ricercando un nuovo partner
            if (is_numeric($strSearch)){                      //ricerca per partita iva
              $partner = $this->queryAnagra(" pariva = ".intval($strSearch));
            } elseif (is_numeric(substr($strSearch,6,2))) {   //ricerca per codice fiscale
              $partner = $this->queryAnagra(" a.codfis LIKE '%".addslashes($strSearch)."%'");
            } else {                                      //ricerca per ragione sociale
              $partner = $this->queryAnagra(" a.ragso1 LIKE '".addslashes($strSearch)."%'");
            }
            if (count($partner) > 0) {
                 echo "\t<select name=\"$name\" $tab1 class=\"FacetSelect\" onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\">\n";
                 echo "<option value=\"0\"> ---------- </option>";
                 foreach ($partner as $r) {
                       $style='';
                       $selected = '';
                       if ($r['codice']==$val && $val >0) {
                           $selected = "selected";
                       }
                       echo "\t\t <option $style value=\"".$r['id']."\" $selected >".$r["ragsoc"]." ".$r["citta"]."</option>\n";
                 }
                 echo "\t </select>\n";
            } else {
                 $msg = $mesg[0];
                 echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
            }
         } else {
            $msg = $mesg[1];
            echo "\t<input type=\"hidden\" name=\"$name\" value=\"$val\">\n";
         }
         echo "\t<input type=\"text\"  $tab2  name=\"search[$name]\" value=\"".$strSearch."\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
         if (isset($msg)) {
            echo "<input type=\"text\" style=\"color: red; font-weight: bold;\" size=\"".strlen($msg)."\" disabled value=\"$msg\">";
         }
         echo "\t<input type=\"image\"  $tab3  align=\"middle\" name=\"search_str\" src=\"../../library/images/cerbut.gif\">\n";
       }
    }
}

// classe per la generazione di select box degli articoli
class selectartico extends SelectBox
{
    function output($cerca,$field='C')
    {
        global $gTables,$script_transl,$script_transl;
        $msg = "";
        $tabula = " tabindex=\"4\" ";
        $opera = "%'";
        if (strlen($cerca) >= 1) {
            if ($field == 'B'){        //ricerca per codice a barre
                $field_sql = 'barcode';
            } elseif ($field == 'D') { //ricerca per descrizione
                $field_sql = 'descri';
            } else {                   //ricerca per codice (default)
                $field_sql = 'codice';
                if (substr($cerca, 0,1) == "@") {
                  $cerca = substr($cerca,1);
                  $opera = "'";
                }
            }
            $result = gaz_dbi_dyn_query("codice,descri,barcode", $gTables['artico'],$field_sql." LIKE '".addslashes($cerca).$opera,"descri DESC");
            $numclfoco = gaz_dbi_num_rows($result);
            if ($numclfoco > 0) {
                $tabula = "";
                echo "\t <select tabindex=\"4\" name=\"$this->name\" class=\"FacetSelect\">\n";
                while ($a_row = gaz_dbi_fetch_array($result)) {
                    $selected = "";
                    if($a_row["codice"] == $this->selected) {
                        $selected = "selected";
                    }
                    echo "\t\t <option value=\"".$a_row["codice"]."\" $selected >".$a_row["codice"]."-".$a_row["descri"]."</option>\n";
                }
                echo "\t </select>\n";
            } else {
                $msg = $script_transl['notfound']."!\n";
                echo "\t<input type=\"hidden\" name=\"$this->name\" value=\"\">\n";
            }
        } else {
                $msg = $script_transl['minins']." 1 ".$script_transl['charat']."!\n";
            echo "\t<input type=\"hidden\" name=\"$this->name\" value=\"\">\n";
        }
        echo "\t<input type=\"text\" name=\"cosear\" value=\"".$cerca."\" ".$tabula." maxlength=\"16\" size=\"9\" class=\"FacetInput\">\n";
        echo "<font style=\"color:#ff0000;\">$msg </font>";
        echo "\t<input type=\"image\" align=\"middle\" accesskey=\"c\" name=\"artico\" ".$tabula." src=\"../../library/images/cerbut.gif\" title=\"{$script_transl['search']}\">\n";
    }
}

// classe per la generazione di select box dei conti ricavi di vendita-costi d'acquisto
class selectconven extends SelectBox
{
    function output($mastri)
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['clfoco']."` WHERE codice LIKE '".$mastri."%' AND codice NOT LIKE '%000000' ORDER BY `codice` ASC";
        SelectBox::_output($query, 'codice', False, '-', 'descri');
    }
}

// classe per la generazione di select box dei conti ricavi di vendita-costi d'acquisto
class selectbanacc extends SelectBox
{
    function output($mastri)
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['clfoco']."` WHERE codice LIKE '".$mastri."%' AND codice > '".$mastri."000000' ORDER BY `codice` ASC";
        SelectBox::_output($query, 'codice', True, '-', 'ragso1');
    }
}

// classe per la generazione di select box banche d'appoggio
class selectbanapp extends SelectBox
{
    function output()
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['banapp'].'` ORDER BY `descri`';
        SelectBox::_output($query, 'descri', True, ' ', 'locali');
    }
}

// classe per la generazione di select box dei pagamenti
class selectpagame extends SelectBox
{
    function output($refresh='')
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['pagame'].'` ORDER BY `codice`';
        SelectBox::_output($query, 'descri', True, '', '', 'codice', $refresh);
    }
}

// classe per la generazione di select box delle aliquote iva
class selectaliiva extends SelectBox
{
    function output()
    {

        global $gTables;

        $query = 'SELECT * FROM `'.$gTables['aliiva'].'` ORDER BY `codice`';

        SelectBox::_output($query, 'descri', True);
    }
}

// classe per la generazione di select box delle categorie merceologiche
class selectcatmer extends SelectBox
{
    function output($refresh='')
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['catmer'].'` ORDER BY `codice`';
        SelectBox::_output($query, 'codice', True, '-', 'descri','codice',$refresh);
    }
}

// classe per la generazione di select box porto resa
class selectportos extends SelectBox
{
    function output()
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['portos'].'` ORDER BY `codice`';
        SelectBox::_output($query, 'codice', True, '-', 'descri');
    }
}

// classe per la generazione di select box delle spedizioni
class selectspediz extends SelectBox
{
    function output()
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['spediz'].'` ORDER BY `codice`';
        SelectBox::_output($query, 'codice', True, '-', 'descri');
    }
}

// classe per la generazione di select box imballi
class selectimball extends SelectBox
{
    function output()
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables['imball'].'` ORDER BY `codice`';
        SelectBox::_output($query, 'codice', True, '-', 'descri');
    }
}

// classe per la generazione di select box imballi, spedizioni, porto resa
class SelectValue extends SelectBox
{
    function output($table, $fieldName)
    {
        global $gTables;
        $query = 'SELECT * FROM `'.$gTables[$table].'` ORDER BY `codice`';
        $index1 = 'codice';
        $empty = True;
        $bridge = '&nbsp; ';
        $index2 = 'descri';
        echo "\t <select name=\"$this->name\" class=\"FacetSelect\" onChange=\"pulldown_menu('".$this->name."','".$fieldName."')\" style=\"width: 20px\">\n";
        if ($empty) {
            echo "\t\t <option value=\"\"></option>\n";
        }
        $result = gaz_dbi_query($query);
        while ($a_row = gaz_dbi_fetch_array($result)) {
            if ($index2 == '') {
                echo "\t\t <option value=\"\">".$a_row[$index1]."</option>\n";
            } else {
                echo "\t\t <option value=\"".$a_row[$index2]."\">&nbsp;".$a_row[$index1].$bridge.$a_row[$index2]."</option>\n";
            }
        }
        echo "\t </select>\n";
    }
}


// classe per la generazione di select box vettori
class selectvettor extends SelectBox
{
    function output()
    {
        global $gTables;
        echo "\t <select name=\"$this->name\" class=\"FacetSelect\">\n";
        echo "\t\t <option value=\"\"></option>\n";
        $result = gaz_dbi_dyn_query("*", $gTables['vettor'],1,"codice");
        while ($a_row = gaz_dbi_fetch_array($result)) {
            $selected = "";
            if($a_row["codice"] == $this->selected) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"".$a_row["codice"]."\" $selected >".substr($a_row["ragione_sociale"],0,22)."</option>\n";
        }
        echo "\t </select>\n";
    }
}

// classe per l'invio di documenti allegati ad una e-mail
class GAzieMail
{
  function sendMail($admin_data,$user,$content,$partner)
    {
        global $gTables;
        global $email_enabled;
        global $email_disclaimer;

        require_once "../../library/phpmailer/class.phpmailer.php";
        require_once "../../library/phpmailer/class.smtp.php";
        //
        // Se è possibile usare la posta elettronica, si procede.
        //
        if (!$email_enabled) {
            echo "invio e-mail <b style=\"color: #ff0000;\">disabilitato... ERROR!</b><br />mail send is <b style=\"color: #ff0000;\">disabled... ERROR!</b> ";
            return;
        }
        //
        // Si procede con la costruzione del messaggio.
        //
        // definisco il server SMTP e il mittente
        $config_mailer = gaz_dbi_get_row($gTables['company_config'],'var','mailer');
        $config_host = gaz_dbi_get_row($gTables['company_config'],'var','smtp_server');
        $config_notif = gaz_dbi_get_row($gTables['company_config'],'var','return_notification');
        $config_port = gaz_dbi_get_row($gTables['company_config'],'var','smtp_port');
        $config_secure = gaz_dbi_get_row($gTables['company_config'],'var','smtp_secure');
        $config_user = gaz_dbi_get_row($gTables['company_config'],'var','smtp_user');
        $config_pass = gaz_dbi_get_row($gTables['company_config'],'var','smtp_password');
        // se non è possibile usare ini_set allora la mail verrà trasmessa usando i
        // dati attinti su php.ini
        $body_text = gaz_dbi_get_row($gTables['body_text'],'table_name_ref','body_send_doc_email');
        $mailto = $partner['e_mail']; //recipient
        $subject = $admin_data['ragso1']." ".$admin_data['ragso2']."-Trasmissione documenti"; //subject
        $email_disclaimer = ("".$email_disclaimer != "") ? "<p>".$email_disclaimer."</p>" : "";
        // Costruisco il testo HTML dell'email
        $body_text['body_text'] .= "<h3><span style=\"color: #000000; background-color: #" . $admin_data['colore'] . ";\">Company: " . $admin_data['ragso1'] . " " . $admin_data['ragso2'] . "</span></h3>";
        $admin_data['web_url'] = trim($admin_data['web_url']);
        $body_text['body_text'] .= ( empty($admin_data['web_url']) ? "" : "<h4><span style=\"color: #000000;\">Web: <a href=\"" . $admin_data['web_url'] . "\">" . $admin_data['web_url'] . "</a></span></h4>" );
        $body_text['body_text'] .= "<address><span style=\"color: #" . $admin_data['colore'] . ";\">User: " . $user['Nome'] . " " . $user['Cognome'] . "</span><br /></address>";
        $body_text['body_text'] .= "<hr />" . $email_disclaimer;
        //
        // Inizializzo PHPMailer
        //
        $mail = new PHPMailer();
        $mail->Host = $config_host['val'];
        $mail->IsHTML();                                // Modalita' HTML
        // Imposto il server SMTP
        if ( !empty($config_port['val']) ) {
            $mail->Port = $config_port['val'];             // Imposto la porta del servizio SMTP
        }
        switch ( $config_mailer['val'] ) {
            case "smtp":
            // Invio tramite protocollo SMTP
            $mail->SMTPDebug = 2;                           // Attivo il debug
            $mail->IsSMTP();                                // Modalita' SMTP
            if (! empty($config_secure['val'])) {
                $mail->SMTPSecure = $config_secure['val']; // Invio tramite protocollo criptato
            }
            $mail->SMTPAuth = ( !empty($config_user['val']) && $config_mailer['val']=='smtp' ? TRUE : FALSE );
            if ( $mail->SMTPAuth ) {
                $mail->Username = $config_user['val'];     // Imposto username per autenticazione SMTP
                $mail->Password = $config_pass['val'];     // Imposto password per autenticazione SMTP
            }
            break;
            case "mail":
            default:
	    break;
        }
        // Imposto eventuale richiesta di notifica
        if ($config_notif['val']=='yes'){
            $mail->AddCustomHeader($mail->HeaderLine("Disposition-notification-to", $admin_data['e_mail']));
        }
        // Imposto email del mittente
        $mail->SetFrom($admin_data['e_mail'], $admin_data['ragso1']." ".$admin_data['ragso2']);
        // Imposto email del destinatario
        $mail->AddAddress($mailto);
        // Aggiungo l'email del mittente tra i destinatari in cc
        $mail->AddCC($admin_data['e_mail'], $admin_data['ragso1']." ".$admin_data['ragso2']);
        // Imposto l'oggetto dell'email
        $mail->Subject = $subject;
        // Imposto il testo HTML dell'email
        $mail->MsgHTML($body_text['body_text']);
        // Aggiungo la fattura in allegato
        $mail->AddStringAttachment($content->string, $content->name, $content->encoding, $content->mimeType);
        // Invio...
        if ( $mail->Send() ) {
            echo "invio e-mail riuscito... <strong>OK</strong><br />mail send has been successful... <strong>OK</strong>"; // or use booleans here
        } else {
            echo "<br />invio e-mail <strong style=\"color: #ff0000;\">NON riuscito... ERROR!</strong><br />mail send has<strong style=\"color: #ff0000;\"> NOT been successful... ERROR!</strong> ";
            echo "<br />mailer error: " . $mail->ErrorInfo;
        }
 }
}


// classe per la generazione dinamica dei form di amministrazione
class GAzieForm
{
  function outputErrors($idxMsg,$transl_errors)
     {
        /* In questa funzione si deve passare una striga dove il "+"
           serve a separare i diversi indici di errori e il "-" separa il riferimento
           all'errore es. "fa150-3+" dara' un risultato del genere:
               ERRORE! -> introdotto un valore negativo ¯fa150
        */
        global $script_transl;
        $message='';
        if (!empty($idxMsg)) {
           $rsmsg = array_slice( explode('+',chop($idxMsg)),0,-1);
           foreach ($rsmsg as $value){
                   $message .= $script_transl['error']."! -> ";
                   $rsval = explode('-',chop($value));
                   $k=array_pop($rsval);
                   $message .= $transl_errors[$k].' ';
                   foreach ($rsval as $valmsg){
                                 $message .= ' &raquo;'.$valmsg;
                   }
                   $message .= "<br />";
           }
        }
        return $message;
    }

  function Calendar($name,$day,$month,$year,$class='FacetSelect',$refresh='')
    {
        if (!empty($refresh)){
            $refresh = "onchange=\"this.form.hidden_req.value='$refresh'; this.form.submit();\"";
        }

        echo "\t <select name=\"".$name."_D\" id=\"".$name."_D\" class=\"$class\" $refresh>\n";
        for( $i = 1; $i <= 31; $i++ ) {
            $selected = "";
            if($i == $day) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"$i\" $selected >$i</option>\n";
        }
        echo "\t </select>\n";
        echo "\t <select name=\"".$name."_M\" id=\"".$name."_M\" class=\"$class\" $refresh>\n";
        for( $i = 1; $i <= 12; $i++ ) {
            $selected = "";
            if($i == $month) {
                $selected = "selected";
            }
            $month_name = ucwords(strftime("%B", mktime (0,0,0,$i,1,0)));
            echo "\t\t <option value=\"$i\"  $selected >$month_name</option>\n";
        }
        echo "\t </select>\n";
        echo "\t <select name=\"".$name."_Y\" id=\"".$name."_Y\" class=\"$class\" $refresh>\n";
        for( $i = $year-10; $i <= $year+10; $i++ ) {
            $selected = "";
            if($i == $year) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"$i\"  $selected >$i</option>\n";
        }
        echo "\t </select>\n";
    }


  function CalendarPopup($name,$day,$month,$year,$class='FacetSelect',$refresh='')
    {
        global $script_transl;
        if (!empty($refresh)){
            $refresh = "onchange=\"this.form.hidden_req.value='$refresh'; this.form.submit();\"";
        }

        echo "\t <select name=\"".$name."_D\" id=\"".$name."_D\" class=\"$class\" $refresh>\n";
        for( $i = 1; $i <= 31; $i++ ) {
            $selected = "";
            if($i == $day) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"$i\" $selected >$i</option>\n";
        }
        echo "\t </select>\n";
        echo "\t <select name=\"".$name."_M\" id=\"".$name."_M\" class=\"$class\" $refresh>\n";
        for( $i = 1; $i <= 12; $i++ ) {
            $selected = "";
            if($i == $month) {
                $selected = "selected";
            }
            $month_name = ucwords(strftime("%B", mktime (0,0,0,$i,1,0)));
            echo "\t\t <option value=\"$i\"  $selected >$month_name</option>\n";
        }
        echo "\t </select>\n";
        echo "\t <input type=\"text\" name=\"".$name."_Y\" id=\"".$name."_Y\" value=\"".$year."\" class=\"$class\"  maxlength=\"4\" size=\"4\" $refresh />\n ";
        echo "\t <A HREF=\"#\" onClick=\"setDate('$name'); return false;\" TITLE=\"".$script_transl['changedate']."\" NAME=\"anchor\" ID=\"anchor\">\n";
        echo "\t<img border=\"0\" src=\"../../library/images/cal.png\"></A>\n";
    }


  function variousSelect($name,$transl,$sel,$class='FacetSelect',$bridge=true,$refresh='',$maxlenght=false)
    {
        if (!empty($refresh)){
            $refresh = "onchange=\"this.form.hidden_req.value='$refresh'; this.form.submit();\"";
        }
        echo "<select name=\"$name\" id=\"$name\" class=\"$class\" $refresh>\n";
        foreach ($transl as $i=>$val) {
            if ($maxlenght){
                $val = substr($val,0,$maxlenght);
            }
            $selected='';
            if ($bridge){
                $k = $i.' -';
            } else {
                $k = '';
            }
            if ($sel == $i) {
                $selected = ' selected ';
            }
            echo "<option value=\"$i\"$selected>$k $val</option>\n";
        }
        echo "</select>\n";
    }

  function selCheckbox($name,$sel,$title='',$refresh='',$class='FacetSelect')
    {
        if (!empty($refresh)){
            $refresh = "onchange=\"this.form.hidden_req.value='$refresh'; this.form.submit();\"";
        }
        $selected='';
        if ($sel == $name) {
            $selected = ' checked ';
        }
        echo "<input type=\"checkbox\" name=\"$name\" title=\"$title\" value=\"$name\" $selected $refresh>\n";
    }

  function selectNumber($name,$val,$msg=false,$min=0,$max=1,$class='FacetSelect',$val_hiddenReq='')
    {
        global $script_transl;
        $refresh ='';
        if (!empty($val_hiddenReq)){
            $refresh = "onchange=\"this.form.hidden_req.value='$val_hiddenReq'; this.form.submit();\"";
        }
        echo "<select name=\"$name\" class=\"$class\" $refresh >\n";
        for ($i=$min; $i<=$max; $i++) {
             $selected='';
             $message=$i;
             if ($val == $i) {
                 $selected = " selected ";
             }
             if ($msg && $i==0) {
                $message = $script_transl['no'];
             }
             if ($msg && $i==1) {
                $message = $script_transl['yes'];
             }
             echo "<option value=\"$i\"$selected>$message</option>\n";
        }
        echo "</select>\n";
    }

    function selectFromDB($table,$name,$key,$val,$order=false,$empty=false,$bridge='',$key2='',$val_hiddenReq='',$class='FacetSelect',$addOption=null)
    {
        global $gTables;
        $refresh ='';
        if (!$order){
            $order = $key;
        }
        $query = 'SELECT * FROM `'.$gTables[$table].'` ORDER BY `'.$order.'`';
        if (!empty($val_hiddenReq)){
            $refresh = "onchange=\"this.form.hidden_req.value='$val_hiddenReq'; this.form.submit();\"";
        }
        echo "\t <select id=\"$name\" name=\"$name\" class=\"$class\" $refresh >\n";
        if ($empty) {
            echo "\t\t <option value=\"\"></option>\n";
        }
        $result = gaz_dbi_query($query);
        while ($r = gaz_dbi_fetch_array($result)) {
            $selected = '';
            if($r[$key] == $val) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"".$r[$key]."\" $selected >";
            if (empty($key2)) {
                echo substr($r[$key],0,43)."</option>\n";
            } else {
                echo substr($r[$key],0,28).$bridge.substr($r[$key2],0,35)."</option>\n";
            }
        }
        if ($addOption) {
            echo "\t\t <option value=\"".$addOption['value']."\"";
            if($addOption['value'] == $val) {
                echo " selected ";
            }
            echo ">".$addOption['descri']."</option>\n";
        }
        echo "\t </select>\n";
    }

    // funzione per la generazione di una select box da file XML
    function selectFromXML($nameFileXML, $name,$key,$val,$empty=false,$val_hiddenReq='',$class='FacetSelect',$addOption=null)
    {
        $refresh ='';
        if (file_exists($nameFileXML)) {
            $xml = simplexml_load_file($nameFileXML);
        } else {
            exit('Failed to open: '.$nameFileXML);
        }
        if (!empty($val_hiddenReq)){
            $refresh = "onchange=\"this.form.hidden_req.value='$val_hiddenReq'; this.form.submit();\"";
        }
        echo "\t <select id=\"$name\" name=\"$name\" class=\"$class\" $refresh >\n";
        if ($empty) {
            echo "\t\t <option value=\"\"></option>\n";
        }
        foreach ($xml->record as $v){
            $selected = '';
            if($v->field[0] == $val) {
                $selected = "selected";
            }
            echo "\t\t <option value=\"".$v->field[0]."\" $selected >&nbsp;".$v->field[0]." - ".$v->field[1]."</option>\n";
        }
        if ($addOption) {
            echo "\t\t <option value=\"".$addOption['value']."\"";
            if($addOption['value'] == $val) {
                echo " selected ";
            }
            echo ">".$addOption['descri']."</option>\n";
        }
        echo "\t </select>\n";
    }

    function selectAccount($name,$val,$type=1,$val_hiddenReq='')
    {
        global $gTables,$admin_aziend;
        $refresh ='';
        $data_color=Array(1=>"88D6FF",2=>"D6FF88",3=>"D688FF",4=>"FFD688",5=>"FF88D6",
                          6=>"88FFD6",7=>"FF88D6",8=>"88FFD6",9=>"FF88D6");
        if (!empty($val_hiddenReq)) {
            $refresh = " onchange=\"this.form.hidden_req.value='$name'; this.form.submit();\"";
        }
        if (is_array($type)) { /* per cercare tra i mastri l'array deve contenere tutti i
                                  i primi numeri che si vogliono ovvero: 1=attivo,2=passivo,3=ricavi,4=costi, ecc
                                  se si vuole cercare tra i sottoconti allora il primo elemento
                                  dell'array deve contenere il valore "SUB"
                                */
            $where='';
            $first=true;
            $sub=false;
            foreach ($type as $v) {
               if (strtoupper($v)== 'SUB') {
                  $sub=true;
                  continue;
               }
               $where .= ($first ? "" : " OR ");
               $first=false;
               if ($sub) {
                  $where.="codice BETWEEN ".intval(substr($v,0,1))."00000001 AND ".intval(substr($v,0,1))."99999999 AND codice NOT LIKE '".$admin_aziend['mascli']."%' AND codice NOT LIKE '".$admin_aziend['masfor']."%' AND codice NOT LIKE '%000000'";
               } else {
                  $where.="codice LIKE '".intval(substr($v,0,1))."__000000'";
               }
            }
        } elseif ($type>99) { // se passo il mastro
           $type = sprintf('%03d',substr($type,0,3));
           $where="codice BETWEEN ".$type."000001 AND ".$type."999999 AND codice NOT LIKE '%000000'";
        } else {
           $where="codice BETWEEN ".$type."00000001 AND ".$type."99999999 AND codice NOT LIKE '".$admin_aziend['mascli']."%' AND codice NOT LIKE '".$admin_aziend['masfor']."%' AND codice NOT LIKE '%000000'";
        }
        echo "\t<select name=\"$name\" class=\"FacetSelect\" $refresh>\n";
        echo "<option value=\"0\"> ---------- </option>";
        $result = gaz_dbi_dyn_query("codice,descri", $gTables['clfoco'],$where,"codice ASC");
        while ($r = gaz_dbi_fetch_array($result)) {
                  $selected='';
                  $v=$r["codice"];
                  $c=intval($v/100000000);
                  $selected .= " style=\"background:#".$data_color[$c]."; color:#000000;\" ";
                  if (intval($type)>99 || (is_array($type) && count($type)==1) ) {
                     $v=intval(substr($r["codice"],0,3));
                  }
                  if ($val == $v) {
                     $selected .= " selected ";
                  }
                  echo "<option value=\"".$v."\"".$selected.">".$r["codice"]." - ".$r['descri']."</option>\n";
        }
        echo "</select>\n";
    }

    function selTypeRow($name,$val,$class='FacetDataTDsmall')
    {
      global $script_transl;
      $this->variousSelect($name,$script_transl['typerow'],$val,$class,true);
    }

    function selSearchItem($name,$val,$class='FacetDataTDsmall')
    {
      global $script_transl;
      $this->variousSelect($name,$script_transl['search_item'],$val,$class,true);
    }
}

/* SEZIONE PER L'ORDINAMENTO DEI RECORD IN OUTPUT
   SONO IMPOSTATE TUTE LE VARIABILI NECESSARIE ALLA FUNZIONE gaz_dbi_dyn_query
   imposto le variabili di sessione con i valori di default*/
if (!isset ($_GET['flag_order'])) {
    $flag_order='';
    $flagorpost='';
}
if (!isset ($_GET['auxil'])) {
    $auxil = "1";
}
if (!isset ($limit)) {
    $limit = "0";
}
if (!isset ($passo)) {
    $passo = "20";
}
if (!isset ($field)) {
    $field = "2" ;
}
//flag di ordinamento ascendente e discendente
if (isset ($_GET['flag_order']) && ($_GET['flag_order'] == "DESC")) {
    $flag_order="ASC";
    $flagorpost="DESC";
} elseif (isset ($_GET['flag_order']) && ($_GET['flag_order'] <> "DESC")) {
    $flag_order="DESC";
    $flagorpost="ASC";
}
// se $PHP_SELF e' compreso nel referer (ricaricamento dalla stessa pagina), conservo tutte le variabili di
// sessione, altrimenti resetto $session['field'], $session['limit'], $session['passo'], $session['where'] e session['order']
if (!isset($_SERVER["HTTP_REFERER"])) {
    $_SERVER["HTTP_REFERER"] = "";
}
// If you only want to determine if a particular needle  occurs within haystack, use the faster and less memory intensive function strpos() instead
//if (!strstr ($_SERVER["HTTP_REFERER"],$_SERVER['PHP_SELF'])) {
if (!strpos ($_SERVER["HTTP_REFERER"],$_SERVER['PHP_SELF'])) {
     $field = "2";  // valore che indica alla gaz_dbi_dyn_query che orderby non va usato
     $flag_order = "DESC"; // per default i dati piu' recenti sono i primi
     $limit = "0";
     $passo = "20";
     $orderby = $field." ".$flag_order;
     $auxil = "1";
     $where = '1';
}
// imposto il nuovo campo per l'ordinamento
if (isset ($_GET['auxil'])) {
    $auxil=$_GET['auxil'];
}
if (isset ($_GET['field'])) {
    $field=$_GET['field'];
}
$orderby = $field.' '.$flag_order;
if (isset ($_GET['limit'])) {
    $limit=$_GET['limit'];
}
// statement where di default = 1
if (!isset ($_GET['where'])) {
    $where = "1";
} else {
    $where = $_GET['where'];
}

// classe che visualizza i pulsanti per la navigazione dei record
// input= tabella, session[where], limit e passo.
// calcola i valori da impostare sulla variabile limit per scorrere i record
// visualizza il numero totale di record e i pulsanti
class   recordnav
{
    var $table;
    var $where;
    var $limit;
    var $passo;
    var $last;
    function recordnav($table, $where, $limit, $passo)
    {
        global $limit, $passo;
        $this->table = $table;
        $this->where = $where;
        $this->limit = $limit;
        $this->passo = $passo;
        // faccio il conto totale dei record selezionati dalla query
        $this->count = gaz_dbi_record_count($table, $where);
        $this->last = $this->count-($this->count%$this->passo);
        //return $last;
    }

    function output ()
    {
        global $flagorpost;
        global $field;
        global $auxil,$script_transl;
        $first = 0;
        $next = $this->limit + $this->passo;
        $prev = $this->limit - $this->passo;
        // se e' arrivato a fondo scala imposto il fermo
        if ($prev <= 0) {
            $prev = 0;
        }
        if ($next >= $this->last) {
            $next = $this->last;
        }
        if ( ($this->count) <= $this->passo ) {
            // non visualizzo la barra di navigazione dei record
            echo "<div align=\"center\"><font class=\"FacetFormDataFont\">Num. record = $this->count</font></div>";
        } else {
            echo "<div align=\"center\"><font class=\"FacetFormDataFont\">Num. record = $this->count</font></div>";
            echo "<div align=\"center\">";
            echo "| << <a href=\"".$_SERVER['PHP_SELF']."?field=".$field."&auxil=".$auxil."&flag_order=".$flagorpost."&limit=0\" >".ucfirst($script_transl['first'])."</a> ";
            echo "| < <a href=\"".$_SERVER['PHP_SELF']."?field=".$field."&auxil=".$auxil."&flag_order=".$flagorpost."&limit=$prev\">".ucfirst($script_transl['prev'])."</a> ";
            echo "| <a href=\"".$_SERVER['PHP_SELF']."?field=".$field."&auxil=".$auxil."&flag_order=".$flagorpost."&limit=$next\">".ucfirst($script_transl['next'])."</a> > ";
            echo "| <a href=\"".$_SERVER['PHP_SELF']."?field=".$field."&auxil=".$auxil."&flag_order=".$flagorpost."&limit=$this->last\">".ucfirst($script_transl['last'])."</a> >> |";
            echo "</div>";
        }
    }
}

// classe per la creazione di headers cliccabili per l'ordinamento dei record
// accetta come parametro un array associativo composto dalle label e relativi campi del db
class linkHeaders
{
    var $headers = array(); // label e campi degli headers
    function linkHeaders($headers)
    {
        $this->headers = $headers;
        $this->align = false;
        $this->style = false;
    }
    function setAlign($align) // funzione per settare l'allineamento del testo passando un array
    {
        $this->align = $align;
    }
    function setStyle($style) // funzione per settare uno stile particolare passando un array
    {
        $this->style = $style;
    }

    function output()
    {
        global $flag_order, $script_transl, $auxil, $headers;
        $k=0; // è l'indice dell'array dei nomi di campo 
        foreach($this->headers as $header => $field) {
            $style='FacetFieldCaptionTD';
            $align='';
            if($this->align){ // ho settato i nomi dei campi del db
                $align= ' style="text-align:'.$this->align[$k].';" ';
            }
            if($this->style){ // ho settato degli stili diversi
                $style= $this->style[$k];
            }
            if ($field <> "") {
                echo "\t<th class=\"$style\" $align ><a href=\"".$_SERVER['PHP_SELF']."?field=".$field."&flag_order=".$flag_order."&auxil=".$auxil."\" title=\"".$script_transl['order'].$header."\">".$header."</a></th>\n\r";
            } else {
                echo "\t<th class=\"$style\" $align >".$header."</th>\n\r";
            }
            $k++;
        }
    }
}

function cleanMemberSession($abilit, $login, $password, $count, $enterprise_id, $table_prefix)
{
    global $gTables;
    $_SESSION["Abilit"] = true;
    $_SESSION["Login"] = $login;
    $_SESSION["Password"] = $password;
    $_SESSION["logged_in"] = true;
    $_SESSION["enterprise_id"] = $enterprise_id;
    $_SESSION["table_prefix"] = $table_prefix;
    $count++;
    //incremento il contatore d'accessi
    gaz_dbi_put_row($gTables['admin'], "Login",$login,"Access",$count);
    //modifico l'ultimo IP
    gaz_dbi_put_row($gTables['admin'], "Login",$login,'last_ip',$_SERVER['REMOTE_ADDR']);
}

function checkAdmin($Livaut=0)
{
    global $gTables,$module,$table_prefix;
    $_SESSION["logged_in"] = false;
    $_SESSION["Abilit"] = false;
    // Se utente non  loggato lo mandiamo alla pagina di login
    if ((! isset ($_SESSION["Login"])) or ($_SESSION["Login"] == "Null")) {
        $_SESSION["Login"]= "Null";
        header("Location: ../root/login_admin.php?tp=".$table_prefix);
        exit;
    }
    if (checkAccessRights($_SESSION['Login'],$module,$_SESSION['enterprise_id']) == 0) {
        // Se utente non ha il diritto di accedere al modulo, lo mostriamo
        // il messaggio di errore, ma senza obligarlo di fare un altro (inutile) login
        header("Location: ../root/access_error.php?module=".$module);
        exit;
    }
    $admin_aziend = gaz_dbi_get_row($gTables['admin'].' LEFT JOIN '.$gTables['aziend'].' ON '.$gTables['admin'].'.enterprise_id = '.$gTables['aziend'].'.codice', "Login", $_SESSION["Login"]);
    $currency=array();
    if (isset($admin_aziend['id_currency'])) {
        $currency = gaz_dbi_get_row($gTables['currencies'], "id", $admin_aziend['id_currency']);
    }
    if ($Livaut > $admin_aziend["Abilit"]) {
        header("Location: ../root/login_admin.php?tp=".$table_prefix);
        exit;
    } else {
        $_SESSION["Abilit"] = true;
    }

    if (!$admin_aziend || $admin_aziend["Password"] != $_SESSION["Password"]) {
        header("Location: ../root/login_admin.php?tp=".$table_prefix);
        exit;
    }
    $_SESSION["logged_in"] = true;
    return array_merge($admin_aziend,$currency);
}

function changeEnterprise($new_co=1)
{
    global $gTables;
    gaz_dbi_put_row($gTables['admin'],'Login',$_SESSION['Login'],'enterprise_id',$new_co);
    $_SESSION['enterprise_id']=$new_co;
}

class Compute
{
    
    function payment_taxstamp($value,$percent,$cents_ceil_round=5)
    {
        if ($cents_ceil_round==0) {
            $cents_ceil_round=5;
        }
        $cents=100*$value*($percent/100+$percent*$percent/10000);
        if ($cents_ceil_round<0) { // quando passo un arrotondamento negativo ritorno il valore di $percent
           $this->pay_taxstamp=round($percent,2);
        } else {
           $this->pay_taxstamp=round(ceil($cents/$cents_ceil_round)*$cents_ceil_round/100,2);
        }
    }

    function add_value_to_VAT_castle($vat_castle,$value=0,$vat_rate=0)
    {
        global $gTables;
        $new_castle=array();    
        $row=0;
        $this->total_imp=0;
        $this->total_vat=0;
        $this->total_exc=0;
        $this->total_isp=0; // totale degli inesigibili per split payment PA
        /* ho due metodi di calcolo del castelletto IVA:
         * 1 - quando non ho l'aliquota IVA allora uso la ventilazione
         * 2 - in presenza di aliquota IVA e quindi devo aggiungere al castelletto */

        if ($vat_rate==0){        // METODO VENTILAZIONE (per mantenere la retrocompatibilità)
            $total_imp=0;
            $decalc_imp=0;
            foreach ($vat_castle as $k=>$v) { // attraverso dell'array per calcolare i totali
                $total_imp += $v['impcast'];
                $row++;
            }
            if ($total_imp>=0.01){ // per evitare il divide by zero in caso di imponibile 0
              foreach ($vat_castle as $k=>$v) {   // riattraverso l'array del castelletto
                                                // per aggiungere proporzionalmente (ventilazione)
                $vat = gaz_dbi_get_row($gTables['aliiva'],"codice",$k);
                $new_castle[$k]['periva'] = $vat['aliquo'];
                $new_castle[$k]['tipiva'] = $vat['tipiva'];
                $new_castle[$k]['descriz'] = $vat['descri'];
                $new_castle[$k]['fae_natura'] = $vat['fae_natura'];
                $row--; 
                if ($row == 0) { // è l'ultimo rigo del castelletto
                    // aggiungo il resto
                    $new_imp = round($total_imp-$decalc_imp +($value*($total_imp-$decalc_imp)/$total_imp),2);
                } else {
                    $new_imp=round($v['impcast']+($value*$v['impcast']/$total_imp),2);
                    $decalc_imp+=$v['impcast'];
                }
                $new_castle[$k]['impcast'] = $new_imp;
                $new_castle[$k]['imponi'] = $new_imp;
                $this->total_imp+=$new_imp; // aggiungo all'accumulatore del totale
                if ($vat['aliquo'] < 0.01){ // è senza IVA
                    $this->total_exc+=$new_imp; // aggiungo all'accumulatore degli esclusi/esenti/non imponibili
                }
                $new_castle[$k]['ivacast'] = round(($new_imp*$vat['aliquo'])/ 100,2);
                if ($vat['tipiva']== 'T'){ // è un'IVA non esigibile per split payment PA
                    $this->total_isp+=$new_castle[$k]['ivacast']; // aggiungo all'accumulatore 
                }
                $this->total_vat+=$new_castle[$k]['ivacast']; // aggiungo anche l'IVA al totale
              }
            }
        } else {  // METODO DELL'AGGIUNTA DIRETTA (nuovo)
            $match=false;            
            foreach ($vat_castle as $k=>$v) { // attraverso dell'array 
                $vat = gaz_dbi_get_row($gTables['aliiva'],"codice",$k);
                $new_castle[$k]['periva'] = $vat['aliquo'];
                $new_castle[$k]['tipiva'] = $vat['tipiva'];
                $new_castle[$k]['descriz'] = $vat['descri'];
                $new_castle[$k]['fae_natura'] = $vat['fae_natura'];
                if ($k==$vat_rate) { // SE è la stessa aliquota aggiungo il nuovo valore
                    $match=true;
                    $new_imp = $v['impcast']+$value;
                    $new_castle[$k]['impcast'] = $new_imp;
                    $new_castle[$k]['imponi'] = $new_imp;
                    $new_castle[$k]['ivacast'] = round(($new_imp*$vat['aliquo'])/ 100,2);
                } else { // è una aliquota che non interessa il valore che devo aggiungere 
                    $new_castle[$k]['impcast'] = $v['impcast'];
                    $new_castle[$k]['imponi'] = $v['impcast'];
                    $new_castle[$k]['ivacast'] = round(($v['impcast']*$vat['aliquo'])/ 100,2);
                }
                if ($vat['aliquo'] < 0.01){ // è senza IVA
                    $this->total_exc+=$new_castle[$k]['impcast']; // aggiungo all'accumulatore degli esclusi/esenti/non imponibili
                }
                if ($vat['tipiva']== 'T'){ // è un'IVA non esigibile per split payment PA
                    $this->total_isp+=$new_castle[$k]['ivacast']; // aggiungo all'accumulatore 
                }
                $this->total_imp+=$new_castle[$k]['impcast']; // aggiungo all'accumulatore del totale
                $this->total_vat+=$new_castle[$k]['ivacast']; // aggiungo anche l'IVA al totale
            }
            if (!$match && $value >= 0.01) { // non ho trovato una aliquota uguale a quella del nuovo valore se > 0 
                $vat = gaz_dbi_get_row($gTables['aliiva'],"codice",$vat_rate);
                $new_castle[$vat_rate]['periva'] = $vat['aliquo'];
                $new_castle[$vat_rate]['tipiva'] = $vat['tipiva'];
                $new_castle[$vat_rate]['impcast'] = $value;
                $new_castle[$vat_rate]['imponi'] = $value;
                $new_castle[$vat_rate]['ivacast'] = round(($value*$vat['aliquo'])/ 100,2);
                $new_castle[$vat_rate]['descriz'] = $vat['descri'];
                $new_castle[$vat_rate]['fae_natura'] = $vat['fae_natura'];
                if ($vat['aliquo'] < 0.01){ // è senza IVA
                    $this->total_exc+=$new_castle[$vat_rate]['impcast']; // aggiungo all'accumulatore degli esclusi/esenti/non imponibili
                }
                if ($vat['tipiva']== 'T'){ // è un'IVA non esigibile per split payment PA
                    $this->total_isp+=$new_castle[$vat_rate]['ivacast']; // aggiungo all'accumulatore 
                }
                $this->total_imp+=$new_castle[$vat_rate]['impcast']; // aggiungo all'accumulatore del totale
                $this->total_vat+=$new_castle[$vat_rate]['ivacast']; // aggiungo anche l'IVA al totale
            }
        }
        $this->castle=$new_castle;
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
            /*
             * setta il valore del conto (piano dei conti) del partner (cliente o fornitore) 
            */
            $this->target=$account;
        }

    function setScheduledPartner($partner_type=false) // 0=TUTTI, 1=FORNITORI, 2=TUTTI
        {
            /*
             * restituisce in $this->Partners i codici dei clienti o dei fornitori
             * che hanno almeno un movimento nell'archivio dello scadenzario
            */
            global $gTables;
            if (!$partner_type) { // se NON mi è stato passato il mastro dei clienti o dei fornitori
              $partner_type='';  
            }
            $sqlquery= "SELECT ".$gTables['clfoco'].".codice 
                FROM ".$gTables['paymov']." LEFT JOIN ".$gTables['rigmoc']." ON (".$gTables['paymov'].".id_rigmoc_pay = ".$gTables['rigmoc'].".id_rig OR ".$gTables['paymov'].".id_rigmoc_doc = ".$gTables['rigmoc'].".id_rig )"
                        ."LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes "
                        ."LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['clfoco'].".codice = ".$gTables['rigmoc'].".codcon 
                WHERE ".$gTables['clfoco'].".codice  LIKE '".$partner_type."%' GROUP BY codcon ORDER BY ".$gTables['clfoco'].".descri ";
                $rs = gaz_dbi_query($sqlquery);
                $acc=array();
                while ($r = gaz_dbi_fetch_array($rs)) {
                    $acc[] = $r['codice'];
                }
                $this->Partners=$acc;               
        }
        
    function getScheduleEntries($ob=0,$masclifor)
        {
            /*
             * genera un array con tutti i movimenti di partite aperte con quattro tipi di ordinamento
             * se viene settato il partnerTarget allora prende in considerazione solo quelli relativi allo stesso 
            */
            global $gTables;
            switch ($ob) {
                  case 1:
                    $orderby = "id_tesdoc_ref, expiry DESC, codice, caucon, datreg, numdoc ASC ";
                  break;
                  case 2:
                    $orderby = "ragso1, id_tesdoc_ref,caucon, datreg, numdoc ASC ";
                  break;
                  case 3:
                    $orderby = "ragso1 DESC, id_tesdoc_ref,caucon, datreg, numdoc ASC ";
                  break;
                  default:
                    $orderby = "id_tesdoc_ref, expiry, codice,  caucon, datreg, numdoc ASC ";
            }
            $select = "*, ".$gTables['tesmov'].".*, ".$gTables['clfoco'].".descri AS ragsoc";
            if ($this->target==0 ) {
                $where = $gTables['clfoco'].".codice LIKE '$masclifor%' ";
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
	
    function getPartnerAccountingBalance($clfoco,$date=false)
    {
    /*  
     * restituisce il valore del saldo contabile di un cliente ad una data, se passata, oppure alla data di sistema
     * */
        global $gTables;
        if ( $this->target>0 && $clfoco==0 ) {
            $clfoco=$this->target;
        }
        if (!$date){
           $date = strftime("%Y-%m-%d", mktime (0,0,0,date("m"),date("d"),date("Y")));
        }
	$sqlquery= "SELECT ".$gTables['tesmov'].".datreg ,".$gTables['rigmoc'].".import, ".$gTables['rigmoc'].".darave
            FROM ".$gTables['rigmoc']." LEFT JOIN ".$gTables['tesmov'].
            " ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes
            WHERE codcon = $clfoco AND caucon <> 'CHI' AND caucon <> 'APE' OR (caucon = 'APE' AND codcon = $clfoco AND datreg IN (SELECT MIN(datreg) FROM ".$gTables['tesmov'].")) ORDER BY datreg ASC";
        $rs = gaz_dbi_query($sqlquery);
        $date_ctrl = new DateTime($date);
        $acc=0.00;
        while ($r = gaz_dbi_fetch_array($rs)) {
            $dr = new DateTime($r['datreg']);
            if ($dr<=$date_ctrl){
                if ($r['darave']=='D'){
                    $acc+=$r['import'];
                } else {
                    $acc-=$r['import'];
                }
            }
        }
	return round($acc,2);
    }
	
    function getStatus($id_tesdoc_ref)
    {
        /*
         * restituisce in $this->Satus la differenza (stato) tra apertura e chiusura di una partita
        */
        global $gTables;
        $sqlquery= "SELECT SUM(amount*(id_rigmoc_doc>0)- amount*(id_rigmoc_pay>0)) AS diff_paydoc, SUM(amount*(id_rigmoc_pay>0)) AS pay, SUM(amount*(id_rigmoc_doc>0))AS doc 
            FROM ".$gTables['paymov']."
            WHERE id_tesdoc_ref = '".$id_tesdoc_ref."' GROUP BY id_tesdoc_ref";
        $rs = gaz_dbi_query($sqlquery);
        $this->Status=gaz_dbi_fetch_array($rs);
    }
    
    function getDocumentData($id_tesdoc_ref)
    {
        /*
          restituisce i dati relativi al documento che ha aperto la partita 
        */
		if (!is_numeric($id_tesdoc_ref)){ $id_tesdoc_ref = "'".$id_tesdoc_ref."'";}
        global $gTables;
		$sqlquery= "SELECT ".$gTables['tesmov'].".* 
            FROM ".$gTables['paymov']." LEFT JOIN ".$gTables['rigmoc']." ON ".$gTables['paymov'].".id_rigmoc_doc = ".$gTables['rigmoc'].".id_rig
            LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes
            WHERE ".$gTables['paymov'].".id_rigmoc_doc > 0 AND ".$gTables['paymov'].".id_tesdoc_ref = $id_tesdoc_ref ORDER BY datreg ASC";
        $rs = gaz_dbi_query($sqlquery);
        return gaz_dbi_fetch_array($rs);
    }
    
    function getPartnerStatus($clfoco,$date=false)
    /*  
     * genera un array ($this->PartnerStatus)con i valori dell'esposizione verso un partner commerciale
     * riferito ad una data, se passata, oppure alla data di sistema
     * $this->docData verrà valorizzato con i dati relativi al documento di riferimento
     * */
    {
        global $gTables;
        $this->PartnerStatus = array();
        if ( $this->target>0 && $clfoco==0 ) {
            $clfoco=$this->target;
        }
        if (!$date){
           $date = strftime("%Y-%m-%d", mktime (0,0,0,date("m"),date("d"),date("Y")));
        }
        $sqlquery= "SELECT ".$gTables['paymov'].".*, ".$gTables['tesmov'].".* ,".$gTables['rigmoc'].".*
            FROM ".$gTables['paymov']." LEFT JOIN ".$gTables['rigmoc']." ON (".$gTables['paymov'].".id_rigmoc_pay = ".$gTables['rigmoc'].".id_rig OR ".$gTables['paymov'].".id_rigmoc_doc = ".$gTables['rigmoc'].".id_rig )"
                    ."LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['rigmoc'].".id_tes = ".$gTables['tesmov'].".id_tes "
                    ."LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['clfoco'].".codice = ".$gTables['rigmoc'].".codcon 
            WHERE ".$gTables['clfoco'].".codice  = ".$clfoco." ORDER BY id_tesdoc_ref, id_rigmoc_pay, expiry";
        $rs = gaz_dbi_query($sqlquery);
        $date_ctrl = new DateTime($date);
        $ctrl_id=0;
        $acc=array();
        while ($r = gaz_dbi_fetch_array($rs)) {
            $expo=false;
            $k=$r['id_tesdoc_ref'];
            if ( $k <> $ctrl_id){ // PARTITA DIVERSA DALLA PRECEDENTE
                $acc[$k]= array();
                $this->docData[$k]=array('id_tes'=>$r['id_tes'],'descri'=>$r['descri'],'numdoc'=>$r['numdoc'],'seziva'=>$r['seziva'],'datdoc'=>$r['datdoc']);
            }    
            $ex = new DateTime($r['expiry']);
            $interval = $date_ctrl->diff($ex);
            if ($r['id_rigmoc_doc']>0) { // APERTURE (vengono prima delle chiusure)
                $s=0;
                if($date_ctrl >= $ex  ){
                    $s=3; // SCADUTA
                }
                $acc[$k][]= array('id'=>$r['id'],'op_val'=>$r['amount'],'expiry'=>$r['expiry'],'cl_val'=>0,'cl_exp'=>'','expo_day'=>0,'status'=>$s,'op_id_rig'=>$r['id_rig'],'cl_rig_data'=>array());
            } else {                    // ATTRIBUZIONE EVENTUALI CHIUSURE ALLE APERTUTRE (in ordine di scadenza)
                if ($date_ctrl < $ex  ) { //  se è un pagamento che avverrà ma non è stato realmente effettuato , che comporta esposizione a rischio
                    $expo=true;
                }
                $v=$r['amount'];
                foreach ($acc[$k] as $ko=>$vo) { // attraverso l'array delle aperture
                    $diff=round($vo['op_val']-$vo['cl_val'],2);
                    if ($diff>=0.01 && $v>0.01) { // faccio il push sui dati del rigo
						$acc[$k][$ko]['cl_rig_data'][]=array('id_rig'=>$r['id_rig'],'descri'=>$r['descri'],'id_tes'=>$r['id_tes'],'import'=>$r['import']);
					}
                    if ($v <= $diff) { // se c'è capienza
                        $acc[$k][$ko]['cl_val'] += $v;
                        if ($expo) { // è un pagamento che avverrà ma non è stato realmente effettuato , che comporta esposizione a rischio
                            $acc[$k][$ko]['expo_day'] = $interval->format('%a');
                            $acc[$k][$ko]['cl_exp'] = $r['expiry'];
                            $expo=false;
                        } else {
                            $acc[$k][$ko]['cl_exp'] = $r['expiry'];
						}
                        $v = 0;
                    } else { // non c'è capienza
                        $acc[$k][$ko]['cl_val'] += $diff;
                        if ($expo && $diff >= 0.01) { // è un pagamento che avverrà ma non è stato realmente effettuato , che comporta esposizione a rischio
                            $acc[$k][$ko]['expo_day'] = $interval->format('%a');
                            $acc[$k][$ko]['cl_exp'] = $r['expiry'];
                        }
                        $v = round($v-$diff,2);
                    }
					if (round($acc[$k][$ko]['op_val']-$acc[$k][$ko]['cl_val'],2)<0.01) { // è chiusa
						$acc[$k][$ko]['status']=1;
					}
                }
                if (count($acc[$k])==0){ 
                    $acc[$k][]= array('id'=>$r['id'],'op_val'=>0,'expiry'=>0,'cl_val'=>$r['amount'],'cl_exp'=>$r['expiry'],'expo_day'=>0,'status'=>9,'op_id_rig'=>0,'cl_rig_data'=>array(0=>array('id_rig'=>$r['id_rig'],'descri'=>$r['descri'],'id_tes'=>$r['id_tes'])));
                }
            }
            $ctrl_id=$r['id_tesdoc_ref'];
        }
        $this->PartnerStatus=$acc;
    }

    function updatePaymov($data)
    {
        global $gTables;
        if (isset($data['id']) && !empty($data['id'])) { // se c'è l'id vuol dire che è un rigo da aggiornare
            paymovUpdate(array('id',$data['id']),$data);
        } elseif (is_numeric($data)) { /* se passo un dato numerico vuol dire che devo eliminare tutti i righi
                                        * di paymov che fanno riferimento a quell'id_rig */
            gaz_dbi_del_row($gTables['paymov'], "id_rigmoc_doc", $data);
            gaz_dbi_del_row($gTables['paymov'], "id_rigmoc_pay", $data);
        } elseif (isset($data['id_del'])) { /* se passo un id da eliminare elimino SOLO quello */
            gaz_dbi_del_row($gTables['paymov'], "id", $data['id_del']);
        } else {    // altrimenti è un nuovo rigo da inserire
            paymovInsert($data);
        }
    }
    function setRigmocEntries($id_rig) // 
    {
        global $gTables;
        $sqlquery= "SELECT * FROM ".$gTables['paymov']." WHERE id_rigmoc_pay=$id_rig OR id_rigmoc_doc=$id_rig";
        $this->RigmocEntries=array();
        $rs = gaz_dbi_query($sqlquery);
        while ($r = gaz_dbi_fetch_array($rs)) {
            $this->RigmocEntries[] = $r;
        }
    }
}


/* controllo se ho delle funzioni specifiche per il modulo corrente
     residente nella directory del module stesso, con queste caratteristiche:
     modules/nome_modulo/lib.function.php
*/

if(@file_exists('./lib.function.php') ) {
    require('./lib.function.php');
}

?>