<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
$msg = "";

$mastrofornitori = $admin_aziend['masfor']."000000";
$inifornitori=$admin_aziend['masfor'].'000001';
$finfornitori=$admin_aziend['masfor'].'999999';

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}
if (isset($_GET['Update']) and  !isset($_GET['id_agente'])) {
    header("Location: ".$form['ritorno']);
    exit;
}

if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or (isset($_POST['Update']))) {   //se non e' il primo accesso
    //qui si dovrebbe fare un parsing di quanto arriva dal browser... o altro;-)
    $form['id_agente'] = intval($_POST['id_agente']);
    if (isset($_POST['cerca_fornitore'])){
       $form['cerca_fornitore'] = $_POST['cerca_fornitore'];
    }
    $form['id_fornitore'] = intval($_POST['id_fornitore']);
    $form['base_percent'] = floatval(preg_replace("/\,/",'.',$_POST['base_percent']));
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner($form['id_fornitore']);
    //--- variabili temporanee
    if (isset($_POST['newfornitore'])) {
            $anagrafica = new Anagrafica();
            $fornitore = $anagrafica->getPartner($form['id_fornitore']);
            $form['cerca_fornitore'] = substr($fornitore['ragso1'],0,4);
            $form['id_fornitore'] = 0;
    }
    // inizio rigo di input
    $form['in_cod_articolo'] = substr($_POST['in_cod_articolo'],0,15);
    $form['in_cod_catmer'] = intval($_POST['in_cod_catmer']);
    $form['in_percentuale'] = floatval(preg_replace("/\,/",'.',$_POST['in_percentuale']));
    $form['in_status'] = $_POST['in_status'];
    $form['cosear'] = $_POST['cosear'];
    // fine rigo input
    $form['righi'] = array();
    $next_row = 0;
    if (isset($_POST['righi'])) {
       foreach ($_POST['righi'] as $next_row => $value) {
            // inizio impedimento della duplicazione dei codici
            if ( (!empty($value['cod_articolo']) && $value['cod_articolo'] == $form['in_cod_articolo'] ) ||
                 (!empty($value['cod_catmer']) && $value['cod_catmer'] == $form['in_cod_catmer'] ) ) { //codice esistente
                   $msg = "7-8-11+";
                   //unset($_POST['in_submit_x']);
				   unset($_POST['in_submit']);
            }
            // fine controllo impedimento inserimento codici esistenti
            $form['righi'][$next_row]['id_provvigione'] = intval($value['id_provvigione']);
            $form['righi'][$next_row]['cod_articolo'] = substr($value['cod_articolo'],0,15);
            $form['righi'][$next_row]['cod_catmer'] = intval($value['cod_catmer']);
            $form['righi'][$next_row]['percentuale'] = floatval(preg_replace("/\,/",'.',$value['percentuale']));
            $form['righi'][$next_row]['status'] = substr($value['status'],0,10);
            if (isset($_POST['upd_row'])) {
               $key_up = key($_POST['upd_row']);
               if ($key_up == $next_row) {
                  $form['in_cod_articolo'] = $form['righi'][$key_up]['cod_articolo'];
                  $form['in_cod_catmer'] = $form['righi'][$key_up]['cod_catmer'];
                  $form['in_percentuale'] = $form['righi'][$key_up]['percentuale'];
                  $form['in_status'] = "UPDROW".$next_row;
                  $form['cosear'] = $form['in_cod_articolo'];
                  array_splice($form['righi'],$key_up,1);
                  $next_row--;
               }
            }
            $next_row++;
       }
    }

  // Se viene inviata la richiesta di conferma rigo
   	/** ENRICO FEDELE */
	/* Con button non funziona _x */
    //if (isset($_POST['in_submit_x'])) {
	/** ENRICO FEDELE */
   if (isset($_POST['in_submit'])) {
   if ((!empty($form['in_cod_articolo']) || $form['in_cod_catmer'] > 0) && $form['in_percentuale'] > 0) {
    if (substr($form['in_status'],0,6) == "UPDROW"){ //se � un rigo da modificare
         $old_key = intval(substr($form['in_status'],6));
         $form['righi'][$old_key]['id_provvigione'] = $form['id_provvigione'];
         $form['righi'][$old_key]['cod_articolo'] = $form['in_cod_articolo'];
         $form['righi'][$old_key]['cod_catmer'] = $form['in_cod_catmer'];
         $form['righi'][$old_key]['percentuale'] = $form['in_percentuale'];
         $form['righi'][$old_key]['status'] = "UPDATE";
         ksort($form['righi']);
    } else { //se � un rigo da inserire
         $form['righi'][$next_row]['id_provvigione'] = 0;
         $form['righi'][$next_row]['cod_articolo'] = $form['in_cod_articolo'];
         $form['righi'][$next_row]['cod_catmer'] = $form['in_cod_catmer'];
         $form['righi'][$next_row]['percentuale'] = $form['in_percentuale'];
         $form['righi'][$next_row]['status'] = "INSERT";
    }
    // reinizializzo rigo di input tranne che per il tipo rigo e aliquota iva
    $form['in_cod_articolo'] = '';
    $form['in_cod_catmer'] = 0;
    $form['in_percentuale'] = 0;
    $form['in_status'] = "INSERT";
    // fine reinizializzo rigo input
    $form['cosear'] = '';
    $next_row++;
   } else {  // dati insufficenti per aggiungere un rigo
       $msg .= "12+";
   }
  }

    // Se viene inviata la richiesta di conferma totale ...
    if (isset($_POST['ins'])) {
//       if ($form['base_percent'] <= 0.01 )
//          $msg .= "13+"; ci sono agenti che non prendono provvigioni, quindi questo controllo non va bene
       if ($form['id_fornitore'] < $inifornitori || $form['id_fornitore'] > $finfornitori) {
          $msg .= "14+";
       }
       if ($form['id_agente'] <= 0) {
          $msg .= "15+";
       }
       $fornitore_exist = gaz_dbi_get_row($gTables['agenti'],'id_fornitore',$form['id_fornitore']);
       if (!empty($fornitore_exist) && $fornitore_exist['id_agente'] != $form['id_agente']) { // il fornitore � gi� un agente (ma non ha lo stesso id)
             $msg .= "16+";
       }
       if ($toDo == 'insert') {
          $agente_exist = gaz_dbi_get_row($gTables['agenti'],'id_agente',$form['id_agente']);
          if (!empty($agente_exist)) { // esiste un agente con lo stesso codice
             $msg .= "17+";
          }
       }
       if ($msg == "") {// nessun errore
             if ($toDo == 'update') { // e' una modifica
                $old_rows = gaz_dbi_dyn_query("*", $gTables['provvigioni'], "id_agente = ".$form['id_agente'],"id_provvigione asc");
                $i=0;
                $count = count($form['righi'])-1;
                while ($val_old_row = gaz_dbi_fetch_array($old_rows)) {
                   if ($i <= $count) { //se il vecchio rigo e' ancora presente nel nuovo lo modifico
                      $form['righi'][$i]['id_agente'] = $form['id_agente'];
                      provvigioniUpdate(array('id_provvigione',$val_old_row['id_provvigione']),$form['righi'][$i]);
                   } else { //altrimenti lo elimino
                      gaz_dbi_del_row($gTables['provvigioni'], 'id_provvigione', $val_old_row['id_provvigione']);
                   }
                   $i++;
                }
                //qualora i nuovi righi fossero di pi� dei vecchi inserisco l'eccedenza
                for ($i = $i; $i <= $count; $i++) {
                    $form['righi'][$i]['id_agente'] = $form['id_agente'];
                    provvigioniInsert($form['righi'][$i]);
                }
                //modifico la testata con i nuovi dati...
                agentiUpdate(array('id_agente',$form['id_agente']),$form);
                header("Location: ".$form['ritorno']);
                exit;
             } else { // e' un'inserimento
                agentiInsert(array('id_agente',$form['id_agente']),$form);
                foreach ($form['righi'] as $i => $value) {
                   $form['righi'][$i]['id_agente'] = $form['id_agente'];
                   provvigioniInsert($form['righi'][$i]);
                }
                header("Location: ".$form['ritorno']);
                exit;
             }
          }
  }

  // Se viene inviata la richiesta elimina il rigo corrispondente
  if (isset($_POST['del'])) {
    $delri= key($_POST['del']);
    array_splice($form['righi'],$delri,1);
    $next_row--;
  }

} elseif ((!isset($_POST['Update'])) and (isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
    $form['id_agente'] = intval($_GET['id_agente']);
    $agenti = gaz_dbi_get_row($gTables['agenti'],'id_agente',$form['id_agente']);
    $anagrafica = new Anagrafica();
    $fornitore = $anagrafica->getPartner($agenti['id_fornitore']);
    $rs_rig = gaz_dbi_dyn_query("*", $gTables['provvigioni'], "id_agente = ".$form['id_agente'],"id_provvigione ASC");
    // inizio rigo di input
    $form['in_cod_articolo'] = '';
    $form['in_cod_catmer'] = 0;
    $form['in_percentuale'] = 0;
    $form['in_status'] = "INSERT";
    $form['cosear']='';
    // fine rigo input
    $form['righi'] = array();
    // ...e della testata
    $form['id_agente'] = $agenti['id_agente'];
    $form['cerca_fornitore'] = substr($fornitore['ragso1'],0,6);
    $form['id_fornitore'] = $agenti['id_fornitore'];
    $form['base_percent'] = $agenti['base_percent'];
    $next_row = 0;
    while ($rigo = gaz_dbi_fetch_array($rs_rig)) {
         $form['righi'][$next_row]['id_provvigione'] = $rigo['id_provvigione'];
         $form['righi'][$next_row]['cod_articolo'] = $rigo['cod_articolo'];
         $form['righi'][$next_row]['cod_catmer'] = $rigo['cod_catmer'];
         $form['righi'][$next_row]['percentuale'] = $rigo['percentuale'];
         $form['righi'][$next_row]['status'] = "UPDATE";
         $next_row++;
    }
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
    $form['righi'] = array();
    $next_row = 0;
    // inizio rigo di input
    $form['in_cod_articolo'] = 0;
    $form['in_cod_catmer'] = 0;
    $form['in_percentuale'] = 0;
    $form['in_status'] = "INSERT";
    // fine rigo input
    $form['cosear'] = '';
    $rs_ultimo_agente = gaz_dbi_dyn_query("id_agente", $gTables['agenti'], 1,"id_agente DESC",0,1);
    $ultimo_agente = gaz_dbi_fetch_array($rs_ultimo_agente);
    $form['id_agente'] = $ultimo_agente['id_agente']+1;
    $form['id_fornitore'] = '';
    $form['base_percent'] = 0;
    $form['cerca_fornitore'] = '';
    $form['change_pag'] = '';
}

require("../../library/include/header.php");
$script_transl = HeadMain(0, array('custom/autocomplete'));
echo "<form method=\"POST\">\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".ucfirst($script_transl[$toDo].$script_transl[1])."</div> ";
echo "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">\n";
echo "<input type=\"hidden\" name=\"ritorno\" value=\"".$form['ritorno']."\">\n";
echo "<input type=\"hidden\" value=\"".$form['id_agente']."\" name=\"id_agente\">\n";
echo "<table class=\"Tsmall\">\n";
if (!empty($msg)) {
    echo "<tr><td colspan=\"2\" class=\"FacetDataTDred\">";
    $message = "";
    $rsmsg = array_slice( explode('+',chop($msg)),0,-1);
    foreach ($rsmsg as $value){
            $message .= $script_transl['error']."! -> ";
            $rsval = explode('-',chop($value));
            foreach ($rsval as $valmsg){
                    $message .= $script_transl[$valmsg]." ";
            }
            $message .= "<br />";
    }
    echo $message."</td>\n";
}
echo "</tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">$script_transl[2] : </td><td class=\"FacetDataTD\">\n";
if ($toDo == 'update') {
echo "\t<input type=\"hidden\" name=\"id_agente\" value=\"".$form['id_agente']."\" /><div class=\"FacetDataTD\">".$form['id_agente']."<div>\n";
} else {
echo "\t<input type=\"text\" name=\"id_agente\" value=\"".$form['id_agente']."\" maxlength=\"3\" size=\"3\" class=\"FacetInput\" />\n";
}
echo "</td></tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">$script_transl[3] : </td><td class=\"FacetDataTD\">\n";
$messaggio = "";
$ric_mastro = substr($form['id_fornitore'],0,3);
if ($form['id_fornitore'] == 0) {
   $tabula =" tabindex=\"1\" ";
   if (strlen($form['cerca_fornitore']) >= 2) {
      $anagrafica = new Anagrafica();
      $fornitore = $anagrafica->queryPartners("*", "(codice between '$inifornitori' and '$finfornitori' ) and ragso1 like '".addslashes($form['cerca_fornitore'])."%'", "ragso1 asc");
      if (sizeof($fornitore) > 0) {
         $tabula="";
         echo "\t<select name=\"id_fornitore\" class=\"FacetSelect\" onchange=\"this.form.submit()\">\n";
         echo "<option value=\"000000000\"> ---------- </option>";
		 foreach ($fornitore AS $key => $row) {
           $selected = "";
           if ($row["codice"] == $form['id_fornitore']) {
               $selected = "selected";
           }
           echo "\t\t <option value=\"".$row["codice"]."\" $selected >".$row["ragso1"]."&nbsp;".$row["citspe"]."</option>\n";
         }
         echo "\t </select>\n";
      } else {
      $messaggio = "Non &egrave; stato trovato nulla";
      echo "\t<input type=\"hidden\" name=\"id_fornitore\" value=\"".$form['id_fornitore']."\">\n";
      }
   } else {
      $messaggio = "Inserire min. 2 caratteri";
      echo "\t<input type=\"hidden\" name=\"id_fornitore\" value=\"".$form['id_fornitore']."\">\n";
   }
   echo "\t<input type=\"text\" name=\"cerca_fornitore\" accesskey=\"e\" value=\"".$form['cerca_fornitore']."\" maxlength=\"15\" size=\"9\" class=\"FacetInput\">\n";
   echo $messaggio;
   //echo "\t <input type=\"image\" align=\"middle\" accesskey=\"c\" name=\"search\" src=\"../../library/images/cerbut.gif\"></td>\n";
   /** ENRICO FEDELE */
   /* Cambio l'aspetto del pulsante per renderlo bootstrap, con glyphicon */
   echo '&nbsp;<button type="submit" class="btn btn-default btn-sm" name="search" accesskey="c"><i class="glyphicon glyphicon-search"></i></button></td>';
   /** ENRICO FEDELE */
} else {
   $anagrafica = new Anagrafica();
   $fornitore = $anagrafica->getPartner($form['id_fornitore']);
   echo "<input type=\"submit\" value=\"".$fornitore['ragso1'].' '.$fornitore['ragso2']."\" name=\"newfornitore\" title=\" Modifica \">\n";
   echo "\t<input type=\"hidden\" name=\"id_fornitore\" value=\"".$form['id_fornitore']."\">\n";
}
echo "</td></tr>\n";
echo "<tr>\n";
echo "<td class=\"FacetFieldCaptionTD\">$script_transl[6] : </td><td class=\"FacetDataTD\">\n";
echo "<input type=\"text\" name=\"base_percent\" value=\"".$form['base_percent']."\" maxlength=\"5\" size=\"5\" class=\"FacetInput\">";
echo "</td></tr>\n";
echo "</table>\n";
echo "<div class=\"FacetSeparatorTD\" align=\"center\">$script_transl[10] $script_transl[7] / $script_transl[8]</div>\n";
// inizio rigo inserimento
echo "<table class=\"Tlarge table table-striped table-bordered table-condensed table-responsive\">\n";
echo "<input type=\"hidden\" value=\"".$form['in_status']."\" name=\"in_status\" />\n";
echo "<tr><td class=\"FacetColumnTD\">$script_transl[7] :\n";
$select_catmer = new selectcatmer('in_cod_catmer');
$select_catmer -> addSelected($form['in_cod_catmer']);
$select_catmer -> output();
echo "</td><td class=\"FacetColumnTD\">$script_transl[8] :\n";
$select_artico = new selectartico('in_cod_articolo');
$select_artico -> addSelected($form['in_cod_articolo']);
$select_artico -> output($form['cosear'],'C');
echo "</td><td class=\"FacetColumnTD\">$script_transl[9] : <input type=\"text\" value=\"".$form['in_percentuale']."\" maxlength=\"5\" size=\"5\" name=\"in_percentuale\">\n";
/*echo "</td><td class=\"FacetColumnTD\" align=\"right\"><input type=\"image\" name=\"in_submit\" src=\"../../library/images/vbut.gif\" tabindex=\"6\" title=\"".$script_transl['submit'].$script_transl['thisrow']."!\">\n";*/

/** ENRICO FEDELE */
/* glyph-icon */
echo '  </td>
		<td class="FacetColumnTD" align="right"> 
			<button type="submit" class="btn btn-default btn-sm" name="in_submit" title="'.$script_transl['submit'].$script_transl['thisrow'].'" tabindex="6"><i class="glyphicon glyphicon-ok"></i></button>
		</td>
	  </tr>';
	   /** ENRICO FEDELE */

echo "</td></tr>\n";
// fine rigo inserimento
echo "<tr><td colspan=\"5\"><hr></td></tr>\n";
// inizio righi gi� inseriti
foreach ($form['righi'] as $key => $value) {
        echo "<input type=\"hidden\" value=\"".$value['status']."\" name=\"righi[$key][status]\">\n";
        echo "<input type=\"hidden\" value=\"".$value['id_provvigione']."\" name=\"righi[$key][id_provvigione]\">\n";
        echo "<tr>\n";
        if  ($value['cod_catmer']>0){
            $catmer = gaz_dbi_get_row($gTables['catmer'],'codice',$value['cod_catmer']);
            /*echo "<td><input type=\"hidden\" value=\"".$value['cod_catmer']."\" name=\"righi[$key][cod_catmer]\">\n
                  <input type=\"hidden\" value=\"\" name=\"righi[$key][cod_articolo]\" />\n
                  <input class=\"FacetDataTD\" type=\"submit\" name=\"upd_row[$key]\" value=\"".$value['cod_catmer']."\" />
                  ".$catmer['descri']."</td><td></td>\n";*/
				echo '<td></td><td>
						<input type="hidden" value="'.$value['cod_catmer'].'" name="righi['.$key.'][cod_catmer]">
						<input type="hidden" value="" name="righi['.$key.'][cod_articolo]" />						  
						<button name="upd_row['.$key.']" class="btn btn-xs btn-success btn-block" type="submit">
							<i class="glyphicon glyphicon-refresh"></i>&nbsp;'.$catmer['descri'].'
						</button>
					  </td>
					  ';
        } else {
            $artico = gaz_dbi_get_row($gTables['artico'],'codice',$value['cod_articolo']);
				echo '<td>
                    <input type="text" value="'.$value['cod_articolo']." - ".$artico["descri"].'" name="art'.$key.'" class="FacetInput" disabled size="100">
                        </td>
                        <td>
						<input type="hidden" value="'.$value['cod_articolo'].'" name="righi['.$key.'][cod_articolo]">
						<input type="hidden" value="" name="righi['.$key.'][cod_catmer]" />						  
						<button name="upd_row['.$key.']" class="btn btn-xs btn-success btn-block" type="submit">
							<i class="glyphicon glyphicon-refresh"></i>&nbsp;'.$catmer['descri'].'
						</button>
					  </td>';   //FP: da formattare meglio
					  /*
            echo "<td></td><td><input type=\"hidden\" value=\"".$value['cod_articolo']."\" name=\"righi[$key][cod_articolo]\" />\n
                  <input type=\"hidden\" value=\"\" name=\"righi[$key][cod_catmer]\" />\n
                  <input class=\"FacetDataTD\" type=\"submit\" name=\"upd_row[$key]\" value=\"".$value['cod_articolo']."\" />
                  ".$artico['descri']."</td>\n";*/
        }
        echo "<td><input type=\"text\" name=\"righi[$key][percentuale]\" value=\"".$value['percentuale']."\" maxlength=\"5\" size=\"5\" class=\"FacetInput\"></td>\n";
        
		//echo "<td align=\"right\"><input type=\"image\" name=\"del[$key]\" src=\"../../library/images/xbut.gif\" title=\"".$script_transl['delete'].$script_transl['thisrow']."!\" /></td></tr>\n";

		/** ENRICO FEDELE */
		/* glyph icon */
		echo '  <td align="right">
				  <button type="submit" class="btn btn-default btn-sm" name="del['.$key.']" title="'.$script_transl['delete'].$script_transl['thisrow'].'!"><i class="glyphicon glyphicon-remove"></i></button>
				</td>
			  </tr>';
		/** ENRICO FEDELE */

}
// fine righi inseriti
if ($toDo == 'update') {
   echo '<td class="FacetFieldCaptionTD" colspan="5" align="right"><input type="submit" accesskey="m" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="Modifica"></td></tr>';
} else {
   echo '<td class="FacetFieldCaptionTD" colspan="5" align="right"><input type="submit" accesskey="i" name="ins" id="preventDuplicate" onClick="chkSubmit();" value="Inserisci"></td></tr>';
}
echo "</table>";
?>
</form>
<?php
require("../../library/include/footer.php");
?>