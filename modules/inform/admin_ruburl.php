<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$msg = "";

if ((isset($_POST['Update'])) or (isset($_GET['Update']))) {
    $toDo = 'update';
} else {
    $toDo = 'insert';
}

if ((isset($_POST['Insert'])) or (isset($_POST['Update']))) {   //se non e' il primo accesso
   $form['ritorno'] = $_POST['ritorno'];
   $form['hidden_req'] = $_POST['hidden_req'];
   $form['id'] = intval($_POST['id']);
   $form['description'] = $_POST['description'];
	$form['val'] = $_POST['indirizzo'];

   if (isset($_POST['ins'])) {   // Se viene inviata la richiesta di conferma totale ...
      if ($msg == "") {// nessun errore
         if ($toDo == 'update') {  // modifica
            $codice = array('id',$form['id']);
				$table = 'company_config';
				$columns = array( 'description', 'var', 'val' );
				$newValue['description'] = $_POST['description'];
				$newValue['var'] = "ruburl";
				$newValue['val'] = $_POST['val'];
				if ( substr($newValue['val'],0,4)!="http" ) {
					$newValue['val'] = "http://".$newValue['val'];
				}
				tableUpdate($table, $columns, $codice, $newValue);
            header("Location: ".$form['ritorno']);
            exit;
         } else {                  // inserimento
				$table = 'company_config';
				$columns = array( 'description','var','val' );
				$newValue['description'] = $_POST['description'];
				$newValue['var'] = "ruburl";
				$newValue['val'] = $_POST['val'];
				if ( substr($newValue['val'],0,4)!="http" ) {
					$newValue['val'] = "http://".$newValue['val'];
				}
				tableInsert($table, $columns, $newValue);
            header("Location: report_ruburl.php");
            exit;
         }
      }
	}
} elseif ((!isset($_POST['Update'])) and (isset($_GET['Update']))) { //se e' il primo accesso per UPDATE
	$form['ritorno'] = $_SERVER['HTTP_REFERER'];
   $form['hidden_req']=''; 
	$rub = gaz_dbi_get_row($gTables['company_config'],'id',intval($_GET['id']));    
   $form['id'] = $rub['id'];
	$form['description'] = $rub['description'];
   $form['val'] = $rub['val'];
} elseif (!isset($_POST['Insert'])) { //se e' il primo accesso per INSERT
   $form['ritorno'] = $_SERVER['HTTP_REFERER'];
   $form['hidden_req']='';
   $form['id'] = "";
	$form['description'] = "";
   $form['val'] = "";     
	$rs_ultima_lettera = gaz_dbi_dyn_query("*", $gTables['company_config'], "var=\"ruburl\"","id desc",0);
   $ultima_lettera = gaz_dbi_fetch_array($rs_ultima_lettera);
   if ($ultima_lettera) {
      $form['id'] = intval($ultima_lettera['id']) + 1;
   } else {
		$form['id'] = 1;
   }
}

require("../../library/include/header.php");
$script_transl=HeadMain();

echo "<form method=\"POST\">";
echo "<input type=\"hidden\" name=\"".ucfirst($toDo)."\" value=\"\">\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\">\n";
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" name=\"\" value=\"".$form['id']."\">\n";
echo "<input type=\"hidden\" name=\"id\" value=\"".$form['id']."\">\n";
echo "<br><table class=\"Tsmall\">\n";
if (!empty($msg)) {
    echo "<tr><td colspan=\"6\" class=\"FacetDataTDred\">";
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
    echo $message."</td></tr>\n";
}
echo "<tr><td align=\"right\" class=\"FacetFieldCaptionTD\">ID : </td><td class=\"FacetDataTD\"> <input type=\"text\" value=\"".$form['id']."\" maxlength=\"20\" size=\"20\" name=\"id\"></td></tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">Descrizione : </td><td colspan=\"3\" class=\"FacetDataTD\"><input type=\"text\" value=\"".$form['description']."\" maxlength=\"60\" size=\"60\" name=\"description\"></td></tr>\n";
echo "<tr><td class=\"FacetFieldCaptionTD\">Indirizzo : </td><td colspan=\"3\" class=\"FacetDataTD\"><input type=\"text\" value=\"".$form['val']."\" maxlength=\"255\" size=\"70\" name=\"val\"></td></tr>\n";
echo "<tr><td colspan=\"3\" class=\"FacetFieldCaptionTD\" align=\"right\"><input type=\"submit\" accesskey=\"i\" name=\"ins\" value=\"".$script_transl['submit']." !\" /></td></tr>";
echo "</table>";
?>
</form>
</div><!-- chiude div container role main --></body>
</html>