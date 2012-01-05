<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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
$msg = '';

$anagrafica = new Anagrafica();

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if ((isset($_GET['Update']) and  !isset($_GET['id_send_mail']))) {
    header("Location: ".$form['ritorno']);
    exit;
}


if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or (isset($_POST['Update']))) {   //se non e' il primo accesso
    //qui si deve fare un parsing di quanto arriva dal browser...
    $form['id_send_mail'] = intval($_POST['id_send_mail']);
    $cliente = $anagrafica->getPartner(intval($_POST['customer']));
    $form['hidden_req'] = $_POST['hidden_req'];
    foreach($_POST['search'] as $k=>$v){
       $form['search'][$k]=$v;
    }
    $form['doc_type'] = strtoupper(substr($_POST['doc_type'],0,3));
    $form['customer'] = substr($_POST['customer'],0,13);
    $form['vat_section'] = intval($_POST['vat_section']);

    // inizio rigo di input
    $form['in_status'] = $_POST['in_status'];
    $form['in_descri'] = $_POST['in_descri'];
    $form['in_unimis'] = $_POST['in_unimis'];
    $form['in_quanti'] = gaz_format_quantity($_POST['in_quanti'],0,$admin_aziend['decimal_quantity']);
    $form['in_price'] = $_POST['in_price'];
    $form['in_discount'] = $_POST['in_discount'];
    $form['in_vat_code'] = $_POST['in_vat_code'];
    $form['in_cod_revenue'] = $_POST['in_cod_revenue'];
    // fine rigo input
    $form['rows'] = array();
    $next_row = 0;
    $rows_text ='body_text';
    if (isset($_POST['rows'])) {
       foreach ($_POST['rows'] as $next_row => $value) {
            $form['rows'][$next_row]['status'] = substr($value['status'],0,30);
            $form['rows'][$next_row]['descri'] = substr($value['descri'],0,100);
            $form['rows'][$next_row]['unimis'] = substr($value['unimis'],0,3);
            $form['rows'][$next_row]['price'] = number_format(preg_replace("/\,/",'.',$value['price']),$admin_aziend['decimal_price'],'.','');
            $form['rows'][$next_row]['discount'] = floatval(preg_replace("/\,/",'.',$value['discount']));
            $form['rows'][$next_row]['quanti'] = gaz_format_quantity($value['quanti'],0,$admin_aziend['decimal_quantity']);
            $form['rows'][$next_row]['vat_code'] = intval($value['vat_code']);
            $form['rows'][$next_row]['cod_revenue'] = intval($value['cod_revenue']);
            $next_row++;
       }
    }
    // Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
       $form['conclusion_date'] = $form['conclusion_date_Y']."-".$form['conclusion_date_M']."-".$form['conclusion_date_D'];
       $utsconcl = mktime(0,0,0,$form['conclusion_date_M'],$form['conclusion_date_D'],$form['conclusion_date_Y']);
       $form['start_date'] = $form['start_date_Y']."-".$form['start_date_M']."-".$form['start_date_D'];
       $utsstart = mktime(0,0,0,$form['start_date_M'],$form['start_date_D'],$form['start_date_Y']);
       $form['last_reassessment'] = $form['last_reassessment_Y']."-".$form['last_reassessment_M']."-".$form['last_reassessment_D'];
       $utsreass = mktime(0,0,0,$form['last_reassessment_M'],$form['last_reassessment_D'],$form['last_reassessment_Y']);
       if (!checkdate($form['conclusion_date_M'],$form['conclusion_date_D'],$form['conclusion_date_Y'])) {
          $msg .= "0+";
       }
       if (!checkdate($form['start_date_M'],$form['start_date_D'],$form['start_date_Y'])) {
          $msg .= "1+";
       }
       if (!checkdate($form['last_reassessment_M'],$form['last_reassessment_D'],$form['last_reassessment_Y'])) {
          $msg .= "2+";
       }
       if ($utsconcl>$utsstart) {
          $msg .= "3+";
       }
       if ($utsstart>$utsreass) {
          $msg .= "4+";
       }
       if (empty($form["customer"])) {
          $msg .= "5+";
       }
       if (empty ($form["payment_method"])) {
          $msg .= "6+";
       }
       if (empty ($form["body_text"])) {
          $msg .= "9+";
       }
       if ($form["current_fee"] <= 0) {
          $msg .= "10+";
       }
       //controllo che i rows non abbiano descrizioni e unita' di misura vuote in presenza di quantita diverse da 0
       foreach ($form['rows'] as $i => $value) {
            if (empty($value['descri']) && $value['quanti']>0) {
                $msg .= "7+";
            }
            if (empty($value['unimis']) && $value['quanti']>0) {
                $msg .= "8+";
            }
       }
       if ($msg == "") { // nessun errore
          if (preg_match("/^id_([0-9]+)$/",$form['customer'],$match)) {
             $new_clfoco = $anagrafica->getPartnerData($match[1],1);
             $form['customer']=$anagrafica->anagra_to_clfoco($new_clfoco,$admin_aziend['mascli']);
          }
          if ($toDo == 'update') { // e' una modifica
             $old_rows = gaz_dbi_dyn_query("*", $gTables['send_mail_row'], "id_send_mail = ".$form['id_send_mail'],"id_send_mail");
             $i=0;
             $count = count($form['rows'])-1;
             while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
                   if ($i <= $count) { //se il vecchio rigo e' ancora presente nel nuovo lo modifico
                      $form['rows'][$i]['id_send_mail'] = $form['id_send_mail'];
                      send_mailRowUpdate($form['rows'][$i],array('id_row',$val_old_row['id_row']));
                   } else { //altrimenti lo elimino
                      gaz_dbi_del_row($gTables['send_mail_row'], 'id_row', $val_old_row['id_row']);
                   }
                   $i++;
             }
             //qualora i nuovi rows fossero di più dei vecchi inserisco l'eccedenza
             for ($i = $i; $i <= $count; $i++) {
                $form['rows'][$i]['id_send_mail'] = $form['id_send_mail'];
                send_mailRowUpdate($form['rows'][$i]);
             }
             bodytextUpdate(array('id_body',$form['id_body_text']),array('table_name_ref'=>'send_mail','id_ref'=>$form['id_send_mail'],'body_text'=>$form['body_text']));
             send_mailUpdate($form, array('id_send_mail',$form['id_send_mail']));
             header("Location: ".$form['ritorno']);
             exit;
          } else { // e' un'inserimento
            send_mailUpdate($form);
            //recupero l'id assegnato dall'inserimento
            $ultimo_id = gaz_dbi_last_id();
            bodytextInsert(array('table_name_ref'=>'send_mail','id_ref'=>$ultimo_id,'body_text'=>$form['body_text']));
            gaz_dbi_put_row($gTables['send_mail'], 'id_send_mail', $ultimo_id, 'id_body_text', gaz_dbi_last_id());
            //inserisco i rows
            foreach ($form['rows'] as $i=>$value) {
                  $value['id_send_mail'] = $ultimo_id;
                  send_mailRowUpdate($value);
            }
            $_SESSION['print_request']=$ultimo_id;
            header("Location: invsta_send_mail.php");
            exit;
          }
    }
  }
  // Se viene inviata la richiesta di conferma cliente
  if ($_POST['hidden_req']=='customer') {
    if (preg_match("/^id_([0-9]+)$/",$form['customer'],$match)) {
        $cliente = $anagrafica->getPartnerData($match[1],1);
    } else {
        $cliente = $anagrafica->getPartner($form['customer']);
    }
    $form['payment_method']=$cliente['codpag'];
    $form['bank']=$cliente['banapp'];
    $form['id_agente']=$cliente['id_agente'];
    $form['in_vat_code']=$cliente['aliiva'];
    $provvigione = new Agenti;
    $form['provvigione']=$provvigione->getPercent($form['id_agente']);
    $form['hidden_req']='';
  }

  // Se viene modificato l'agente ricarico la provvigione
  if ($_POST['hidden_req'] == 'AGENTE') {
     if ($form['id_agente'] > 0) {
         $provvigione = new Agenti;
         $form['provvigione']=$provvigione->getPercent($form['id_agente']);
    } else {
         $form['provvigione']=0.00;
    }
    $form['hidden_req']='';
  }

  // Se viene inviata la richiesta di conferma rigo
  if (isset($_POST['in_submit_x'])) {
    if (substr($form['in_status'],0,6) == "UPDROW"){ //se è un rigo da modificare
         $old_key = intval(substr($form['in_status'],6));
         $form['rows'][$old_key]['status'] = "UPDATE";
         $form['rows'][$old_key]['descri'] = $form['in_descri'];
         $form['rows'][$old_key]['unimis'] = $form['in_unimis'];
         $form['rows'][$old_key]['quanti'] = $form['in_quanti'];
         $form['rows'][$old_key]['codart'] = $form['in_codart'];
         $form['rows'][$old_key]['cod_revenue'] = $form['in_cod_revenue'];
         $form['rows'][$old_key]['provvigione'] = $form['in_provvigione'];
         $form['rows'][$old_key]['price'] = number_format($form['in_price'],$admin_aziend['decimal_price'],'.','');
         $form['rows'][$old_key]['discount'] = $form['in_discount'];
         $form['rows'][$old_key]['vat_code'] = $form['in_vat_code'];
         $iva_row = gaz_dbi_get_row($gTables['aliiva'],"codice",$form['in_vat_code']);
         if ($form['in_type_row'] == 0 ) {  //rigo normale
         } else {   // rigo di testo
         }
         ksort($form['rows']);
    } else { //se è un rigo da inserire
         $form['rows'][$next_row]['status'] = 'INSERT';
         $form['rows'][$next_row]['descri'] = $form['in_descri'];
         $form['rows'][$next_row]['unimis'] = $form['in_unimis'];
         $form['rows'][$next_row]['price'] = number_format($form['in_price'],$admin_aziend['decimal_price'],'.','');
         $form['rows'][$next_row]['cod_revenue'] = $form['in_cod_revenue'];
         $form['rows'][$next_row]['quanti'] = $form['in_quanti'];
         $form['rows'][$next_row]['discount'] = $form['in_discount'];
         $form['rows'][$next_row]['vat_code'] =  $form['in_vat_code'];
         $form['rows'][$next_row]['cod_revenue'] = $form['in_cod_revenue'];
    }
     // reinizializzo rigo di input tranne che tipo rigo, aliquota iva e conto ricavo
     $form['in_descri'] = "";
     $form['in_unimis'] = "";
     $form['in_price'] = 0;
     $form['in_discount'] = 0;
     $form['in_quanti'] = 0;
     // fine reinizializzo rigo input
     $next_row++;
  }

  // Se viene inviata la richiesta elimina il rigo corrispondente
  if (isset($_POST['del'])) {
    $delri= key($_POST['del']);
    array_splice($form['rows'],$delri,1);
    $next_row--;
  }

} elseif ((!isset($_POST['Update'])) and (isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $send_mail = gaz_dbi_get_row($gTables['send_mail'],"id_send_mail",intval($_GET['id_send_mail']));
    $cliente = $anagrafica->getPartner($send_mail['customer']);
    $form['hidden_req'] = '';
    $form['id_send_mail'] = $send_mail['id_send_mail'];
    $form['doc_type'] = $send_mail['doc_type'];
    $form['customer'] = $send_mail['customer'];
    $form['search']['customer']=substr($cliente['ragso1'],0,10);
    $form['vat_section'] = $send_mail['vat_section'];

    // inizio rigo di input
    $form['in_status'] = "INSERT";
    $form['in_descri'] = "";
    $form['in_unimis'] = '';
    $form['in_quanti'] = 0;
    $form['in_price'] = 0;
    $form['in_discount'] = 0;
    $form['in_vat_code'] = $admin_aziend['alliva'];
    $form['in_cod_revenue'] = $admin_aziend['impven'];
    // fine rigo input

    $form['rows'] = array();
    $next_row = 0;
    $rows_text = 'body_text';
    $rs_row = gaz_dbi_dyn_query("*", $gTables['send_mail_row'], "id_send_mail = ".intval($_GET['id_send_mail']),"id_row ASC");
    while ($row = gaz_dbi_fetch_array($rs_row)) {
           $form['rows'][$next_row]['descri'] = $row['descri'];
           $form['rows'][$next_row]['unimis'] = $row['unimis'];
           $form['rows'][$next_row]['price'] = number_format($row['price'],$admin_aziend['decimal_price'],'.','');
           $form['rows'][$next_row]['discount'] = $row['discount'];
           $form['rows'][$next_row]['quanti'] = gaz_format_quantity($row['quanti'],0,$admin_aziend['decimal_quantity']);
           $form['rows'][$next_row]['vat_code'] = $row['vat_code'];
           $form['rows'][$next_row]['cod_revenue'] = $row['cod_revenue'];
           $form['rows'][$next_row]['status'] = $row['status'];
           $next_row++;
    }
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['id_send_mail'] = '';
    $form['customer'] = '';
    if (empty($admin_aziend['pariva'])){
        $form['doc_type'] = 'VRI';
    } else {
        $form['doc_type'] = 'FAI';
    }
    $cliente['indspe'] = '';
    $form['search']['customer']='';
    $form['hidden_req'] = '';
    // inizio rigo di input
    $form['in_status'] = "INSERT";
    $form['in_descri'] = "";
    $form['in_type_row'] = 0;
    $form['in_unimis'] = "";
    $form['in_price'] = 0;
    $form['in_discount'] = 0;
    $form['in_quanti'] = 0;
    $form['in_vat_code'] = $admin_aziend['alliva'];
    $form['in_cod_revenue'] = $admin_aziend['impven'];
    // fine rigo input
}

require("../../library/include/header.php");
$script_transl = HeadMain(0,array('tiny_mce/tiny_mce',
                                  'boxover/boxover',
                                  'calendarpopup/CalendarPopup',
                                  'jquery/jquery-1.4.2.min',
                                  'jquery/ui/jquery.ui.core',
                                  'jquery/ui/jquery.ui.widget',
                                  'jquery/ui/jquery.ui.position',
                                  'jquery/ui/jquery.ui.autocomplete',
                                  'jquery/autocomplete_anagra'));
$title = ucfirst($script_transl['ins_this']);
if ($toDo=='update'){
  $title = ucfirst($script_transl['upd_this']);
}
echo "<script type=\"text/javascript\">
// Initialize TinyMCE with the new plugin and menu button
tinyMCE.init({
  mode : \"exact\",
  theme : \"advanced\",
  language : \"it\",
  forced_root_block : false,
  force_br_newlines : true,
  force_p_newlines : false,
  elements : \"".$rows_text."\",
  plugins : \"table,advlink\",
  theme_advanced_buttons1 : \"mymenubutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,|,link,unlink,code,|,formatselect,forecolor,backcolor,|,tablecontrols\",
  theme_advanced_buttons2 : \"\",
  theme_advanced_buttons3 : \"\",
  theme_advanced_toolbar_location : \"external\",
  theme_advanced_toolbar_align : \"left\",
});

var cal = new CalendarPopup();
var calName = '';
function setMultipleValues(y,m,d) {
     document.getElementById(calName+'_Y').value=y;
     document.getElementById(calName+'_M').selectedIndex=m*1-1;
     document.getElementById(calName+'_D').selectedIndex=d*1-1;
}
function setDate(name) {
  calName = name.toString();
  var year = document.getElementById(calName+'_Y').value.toString();
  var month = document.getElementById(calName+'_M').value.toString();
  var day = document.getElementById(calName+'_D').value.toString();
  var mdy = month+'/'+day+'/'+year;
  cal.setReturnFunction('setMultipleValues');
  cal.showCalendar('anchor', mdy);
}
</script>
";
echo "<form method=\"POST\" name=\"send_mail\">\n";
$gForm = new GAzieForm();
echo "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">\n";
echo "<input type=\"hidden\" value=\"".$form['id_send_mail']."\" name=\"id_send_mail\">\n";
echo "<input type=\"hidden\" value=\"".$form['id_body_text']."\" name=\"id_body_text\">\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">$title ";
$select_cliente = new selectPartner("customer");
$select_cliente->selectDocPartner('customer',$form['customer'],$form['search']['customer'],'customer',$script_transl['mesg'],$admin_aziend['mascli']);
echo ' n.'.$form['doc_number']."</div>\n";
echo "<table class=\"Tlarge\">\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['vat_section']."</td><td class=\"FacetDataTD\">\n";
$gForm->selectNumber('vat_section',$form['vat_section'],0,1,3);
echo "\t </td>\n";
if (!empty($msg)) {
    echo '<td colspan="2" class="FacetDataTDred">'.$gForm->outputErrors($msg,$script_transl['errors'])."</td>\n";
} else {
    echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['address']."</td><td>".$cliente['indspe']."<br />";
    echo "</td>\n";
}
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['conclusion_date']."</td><td class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('conclusion_date',$form['conclusion_date_D'],$form['conclusion_date_M'],$form['conclusion_date_Y']);
echo "\t</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['doc_number']."</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"doc_number\" value=\"".$form['doc_number']."\" align=\"right\" maxlength=\"9\" size=\"3\" /></td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['current_fee']."</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"current_fee\" value=\"".$form['current_fee']."\" align=\"right\" maxlength=\"9\" size=\"9\" tabindex=\"2\" /></td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['payment_method']."</td><td  class=\"FacetDataTD\">\n";
$gForm->selectFromDB('pagame','payment_method','codice',$form['payment_method'],'codice',1,' ','descri');
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['initial_fee']."</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"initial_fee\" value=\"".$form['initial_fee']."\" align=\"right\" maxlength=\"9\" size=\"3\" /></td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['bank']."</td><td  class=\"FacetDataTD\">\n";
$gForm->selectFromDB('banapp','bank','codice',$form['bank'],'codice',1,' ','descri');
echo "</td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['start_date']."</td>\n";
echo "\t<td class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('start_date',$form['start_date_D'],$form['start_date_M'],$form['start_date_Y']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['months_duration']."</td>\n";
echo "\t<td class=\"FacetDataTD\"><input type=\"text\" name=\"months_duration\" value=\"".$form['months_duration']."\" align=\"right\" maxlength=\"3\" size=\"3\" />\n";
echo "\t </td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['id_agente']."</td><td  class=\"FacetDataTD\">\n";
$select_agente = new selectAgente("id_agente");
$select_agente->addSelected($form["id_agente"]);
$select_agente->output();
echo " ".$script_transl['provvigione']."\n";
echo "\t<input type=\"text\" name=\"provvigione\" value=\"".$form['provvigione']."\" align=\"right\" maxlength=\"5\" size=\"3\" />\n";
echo "</td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['doc_type']."</td><td  class=\"FacetDataTD\">\n";
$gForm->variousSelect('doc_type',$script_transl['doc_type_value'],$form['doc_type']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['tacit_renewal']."</td><td class=\"FacetDataTD\">\n";
$gForm->selectNumber('tacit_renewal',$form['tacit_renewal'],1);
echo "\t </td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['cod_revenue']."</td><td class=\"FacetDataTD\">\n";
$select_cod_revenue = new selectconven('cod_revenue');
$select_cod_revenue->addSelected($form['cod_revenue']);
$select_cod_revenue->output(substr($form['cod_revenue'],0,1));
echo "\t </td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['last_reassessment']."</td><td class=\"FacetDataTD\">\n";
$gForm->CalendarPopup('last_reassessment',$form['last_reassessment_D'],$form['last_reassessment_M'],$form['last_reassessment_Y']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">\n".$script_transl['periodic_reassessment']."</td><td  class=\"FacetDataTD\">\n";
$gForm->selectNumber('periodic_reassessment',$form['periodic_reassessment'],1);
echo "\t </td>\n";
echo "<td class=\"FacetFieldCaptionTD\">".$script_transl['vat_code']."</td><td  class=\"FacetDataTD\">\n";
$gForm->selectFromDB('aliiva','vat_code','codice',$form['vat_code'],'codice',0,' - ','descri');
echo "</td>\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['periodicity']."</td><td  class=\"FacetDataTD\">\n";
$gForm->variousSelect('periodicity',$script_transl['periodicity_value'],$form['periodicity']);
echo "\t </td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "\t<td colspan=\"6\" align=\"center\">\n";
echo $script_transl['body_text'];
echo "</td></tr>\n";
echo "\t<td colspan=\"6\">\n";
echo "<textarea id=\"body_text\" name=\"body_text\" style=\"width:100%;height:400px;\" >".$form['body_text']."</textarea>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "<table class=\"Tlarge\">\n";
echo "<tr>\n";
echo "\t<td colspan=\"8\" align=\"center\">".$script_transl['rows_title']."</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"".$form['in_status']."\" name=\"in_status\" />\n";
echo "\t<tr class=\"FacetColumnTD\" align=\"center\">\n";
echo "\t<td colspan=\"3\">".$script_transl['descri']."</td>\n";
echo "\t<td>".$script_transl['unimis']."</td>\n";
echo "\t<td>".$script_transl['quanti']."</td>\n";
echo "\t<td>".$script_transl['price']."</td>\n";
echo "\t<td>".$script_transl['discount']."</td>\n";
echo "</tr>\n";
echo "<tr class=\"FacetColumnTD\" align=\"center\">\n";
echo "<td colspan=\"3\">\n";
echo "<input type=\"text\" value=\"".$form['in_descri']."\" maxlength=\"100\" size=\"100\" name=\"in_descri\">\n";
echo "\t </td>\n";
echo "<td>\n";
echo "<input type=\"text\" value=\"".$form['in_unimis']."\" maxlength=\"3\" size=\"3\" name=\"in_unimis\">\n";
echo "\t </td>\n";
echo "<td>\n";
echo "<input type=\"text\" style=\"text-align:right\" value=\"".$form['in_quanti']."\" maxlength=\"11\" size=\"7\" name=\"in_quanti\">\n";
echo "\t </td>\n";
echo "<td >\n";
echo "<input type=\"text\" style=\"text-align:right\" value=\"".$form['in_price']."\" maxlength=\"15\" size=\"7\" name=\"in_price\">\n";
echo "\t </td>\n";
echo "<td>\n";
echo "<input type=\"text\" style=\"text-align:right\" value=\"".$form['in_discount']."\" maxlength=\"4\" size=\"1\" name=\"in_discount\">";
echo "\t </td>\n";
echo "<td align=\"right\">\n";
echo "<input type=\"image\" name=\"in_submit\" src=\"../../library/images/vbut.gif\" title=\"".$script_transl['submit'].$script_transl['thisrow']."!\">\n";
echo "\t </td>\n";
echo "\t </tr>\n";
echo "\t<tr class=\"FacetColumnTD\">\n";
echo "<td colspan=\"7\">\n";
echo $script_transl['vat_code'].' :';
$gForm->selectFromDB('aliiva','in_vat_code','codice',$form['in_vat_code'],'codice',0,' - ','descri');
echo $script_transl['cod_revenue'].' :';
$select_cod_revenue = new selectconven("in_cod_revenue");
$select_cod_revenue -> addSelected($form['in_cod_revenue']);
$select_cod_revenue -> output(substr($form['in_cod_revenue'],0,1));
echo "\t </td>\n";
echo "\t </tr>\n";
if ($next_row>0) {
    echo "<tr class=\"FacetFieldCaptionTD\"><td colspan=\"8\">".$script_transl['insrow']." :</td></tr>\n";
    foreach ($form['rows'] as $k=>$val) {
            $nr=$k+1;
            $aliiva = gaz_dbi_get_row($gTables['aliiva'],'codice',$val['vat_code']);
            echo "<input type=\"hidden\" value=\"".$val['status']."\" name=\"rows[$k][status]\">\n";
            echo "<input type=\"hidden\" value=\"".$val['vat_code']."\" name=\"rows[$k][vat_code]\">\n";
            echo "<input type=\"hidden\" value=\"".$val['cod_revenue']."\" name=\"rows[$k][cod_revenue]\">\n";
            echo "<tr class=\"FacetFieldCaptionTD\">\n";
            echo "<td colspan=\"3\">$nr<input type=\"text\" name=\"rows[$k][descri]\" value=\"".$val['descri']."\" maxlength=\"100\" size=\"50\" />
                  ".$script_transl['cod_revenue'].": ".$val['cod_revenue']." - ".$aliiva['descri']."</td>\n";
            echo "<td><input type=\"text\" name=\"rows[$k][unimis]\" value=\"".$val['unimis']."\" maxlength=\"3\" size=\"3\" /></td>\n";
            echo "<td><input type=\"text\" style=\"text-align:right\" name=\"rows[$k][quanti]\" value=\"".$val['quanti']."\" maxlength=\"11\" size=\"7\" /></td>\n";
            echo "<td><input type=\"text\" style=\"text-align:right\" name=\"rows[$k][price]\" value=\"".$val['price']."\" maxlength=\"15\" size=\"7\" /></td>\n";
            echo "<td><input type=\"text\" style=\"text-align:right\" name=\"rows[$k][discount]\" value=\"".$val['discount']."\" maxlength=\"4\" size=\"3\" /></td>\n";
            echo "<td align=\"right\"><input type=\"image\" name=\"del[$k]\" src=\"../../library/images/xbut.gif\" title=\"".$script_transl['delete'].$script_transl['thisrow']."!\" /></td></tr>\n";
            echo "\t </tr>\n";
    }
}
echo "\t<tr class=\"FacetFieldCaptionTD\">\n";
echo '<td colspan="6" align="right"> <input type="submit" accesskey="i" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="';
echo $script_transl['submit'];
echo '" tabindex="100" >';
echo "\t </td>\n";
echo "\t </tr>\n";
echo "</table>\n";
?>
</form>
</body>
</html>