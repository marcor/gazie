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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="author" content="Antonio De Vincentiis www.devincentiis.it">
<?php
$menuclass = ' class="FacetMainMenu" ';
$style = 'stylesheet.css';
if (!empty($admin_aziend['style'])){
    $style = $admin_aziend['style'];
}

echo '<link rel="stylesheet" type="text/css" href="../../library/style/'.$style.'">';
echo '<link rel="shortcut icon" href="../../library/images/favicon.ico">';
echo '<script type="text/javascript" src="../../js/jscookmenu/JSCookMenu.js"></script>';
echo '<link rel="stylesheet" href="../../js/jscookmenu/theme.css" type="text/css">';
echo '<script type="text/javascript" src="../../js/jscookmenu/theme.js"></script>';
function HeadMain ($idScript='',$jsArray='',$alternative_transl=false)
{
  /* - In $idScript si deve passare l'id dell'array submenu (m2) in menu.language.php (per mettere nel tag <TITLE> )
             oltre che il nome del modulo anche quello dello script tradotto
     - In $jsArray di devono passare i nomi dei file javascript che si vogliono caricare e presenti nella directory 'js'
     */
  if (is_array($jsArray)){
      foreach ($jsArray as $v){
              echo "\n<script type=\"text/javascript\" src=\"../../js/".$v.".js\"></script>";
      }
  }
  global $money,$module,$admin_aziend,$radix,$scriptname;
  $result = getAccessRights($_SESSION['Login'],$_SESSION['enterprise_id']);
  if (gaz_dbi_num_rows($result) > 0) {
     // creo l'array associativo per la generazione del menu con JSCookMenu
     $ctrl_m1=0;
     $ctrl_m2=0;
     $ctrl_m3=0;
     $menuArray = array();
     $transl = array();
     while ($row = gaz_dbi_fetch_array($result)) {
        if ($row['access'] == 3 ){
             if ($ctrl_m1!=$row['m1_id']) {
                require("../../modules/".$row['name']."/menu.".$admin_aziend['lang'].".php");
             }
             if ($row['name'] == $module) {
                 $row['weight']=0;
                 if ($row['m3_link'] == $scriptname) {
                     $title_from_menu = $transl[$row['name']]['m3'][$row['m3_trkey']][0];
                 }
                 if ($ctrl_m2 != $row['m2_id'] and $ctrl_m1 != $row['m1_id']) {
                    require("../../modules/".$row['name']."/lang.".$admin_aziend['lang'].".php");
                    if (isset($strScript[$scriptname])){ // se è stato tradotto lo script lo ritorno al chiamante
                          $translated_script=$strScript[$scriptname];
                          if (isset($translated_script['title'])) {
                             $title_from_menu = $translated_script['title'];
                          }
                    }
                }
             }
             if (isset($row['m3_id']) and $row['m3_id']>0) { // è un menu3
                if ($ctrl_m2 != $row['m2_id'] and $ctrl_m1 != $row['m1_id']) { // è pure il primo di menu2 e menu1
                    $menuArray[$row['weight']] = array('link'=>'../'.$row['name'].'/'.$row['link'],'icon'=>'../'.$row['name'].'/'.$row['icon'],'name'=>$transl[$row['name']]['name'],'title'=>$transl[$row['name']]['title'],'class'=>$row['class']);
                    $menuArray[$row['weight']][$row['m2_weight']] = array('link'=>'../'.$row['name'].'/'.$row['m2_link'],'icon'=>'../'.$row['name'].'/'.$row['m2_icon'],'name'=>$transl[$row['name']]['m2'][$row['m2_trkey']][1],'title'=>$transl[$row['name']]['m2'][$row['m2_trkey']][0],'class'=>$row['m2_class']);
                } elseif ($ctrl_m2 != $row['m2_id']) { // è solo il primo di menu2
                    $menuArray[$row['weight']][$row['m2_weight']] = array('link'=>'../'.$row['name'].'/'.$row['m2_link'],'icon'=>'../'.$row['name'].'/'.$row['m2_icon'],'name'=>$transl[$row['name']]['m2'][$row['m2_trkey']][1],'title'=>$transl[$row['name']]['m2'][$row['m2_trkey']][0],'class'=>$row['m2_class']);
                }
                $menuArray[$row['weight']][$row['m2_weight']][$row['m3_weight']] = array('link'=>'../'.$row['name'].'/'.$row['m3_link'],'icon'=>'../'.$row['name'].'/'.$row['m3_icon'],'name'=>$transl[$row['name']]['m3'][$row['m3_trkey']][1],'title'=>$transl[$row['name']]['m3'][$row['m3_trkey']][0],'class'=>$row['m3_class']);
             } elseif ($ctrl_m1!=$row['m1_id']) { // è il primo di menu2
                $menuArray[$row['weight']] = array('link'=>'../'.$row['name'].'/'.$row['link'],'icon'=>'../'.$row['name'].'/'.$row['icon'],'name'=>$transl[$row['name']]['name'],'title'=>$transl[$row['name']]['title'],'class'=>$row['class']);
                $menuArray[$row['weight']][$row['m2_weight']] = array('link'=>'../'.$row['name'].'/'.$row['m2_link'],'icon'=>'../'.$row['name'].'/'.$row['m2_icon'],'name'=>$transl[$row['name']]['m2'][$row['m2_trkey']][1],'title'=>$transl[$row['name']]['m2'][$row['m2_trkey']][0],'class'=>$row['m2_class']);
             } else { // non è il primo di menu2
                $menuArray[$row['weight']][$row['m2_weight']] = array('link'=>'../'.$row['name'].'/'.$row['m2_link'],'icon'=>'../'.$row['name'].'/'.$row['m2_icon'],'name'=>$transl[$row['name']]['m2'][$row['m2_trkey']][1],'title'=>$transl[$row['name']]['m2'][$row['m2_trkey']][0],'class'=>$row['m2_class']);
             }
        }
        $ctrl_m1=$row['m1_id'];
        $ctrl_m2=$row['m2_id'];
        $ctrl_m3=$row['m3_id'];
     }
     ksort($menuArray);
     /*   Fine creazione array per JSCookMenu.
     In $menuArray c'e' la lista del menu
     con index '0' il modulo corrente,
     è una matrice a 3 dimensioni ,
     questo serve per poter creare un array in JS
     compatibile con le specifiche di JSCookMenu,
     la funzione createGazieJSCM serve per creare un
     array con il menu corrente orizzontale , si potrebbero creare
     altre forme di menu modificando questa funzione. */
     echo "\n<title>".$admin_aziend['ragso1']."&raquo;".$menuArray[0]['title'];
     if (!empty($idScript)) {
        if (is_array($idScript)) { // $idScript dev'essere un array con index [0] per il numero di menu e index[1] per l'id dello script
            if ($idScript[0] == 2) {
                echo "&raquo;".$transl[$module]['m2'][$idScript[1]][0];
            } elseif ($idScript[0] == 3){
                echo "&raquo;".$transl[$module]['m3'][$idScript[1]][0];
            }
        } elseif ($idScript > 0) {
            echo "&raquo;".$transl[$module]['m3'][$idScript][0];
        }
     } elseif (isset($title_from_menu)) {
            echo "&raquo;".$title_from_menu;
     }
     echo "</title>\n";
     echo createGazieCookMenu($menuArray,$radix);
     echo "</head>\n";
     echo "<body>\n";
     echo '<div id="GazieMenuID"></div>';
     echo '<script type="text/javascript">';
     echo "cmDraw('GazieMenuID', myMenu, 'hbr', cmThemeGazie);\n";
     echo '</script>';
  }
  if (!isset($translated_script)){
     if ($alternative_transl){ // se e' stato passato il nome dello script sul quale mi devo basare per la traduzione
        $translated_script=$strScript[$alternative_transl.'.php'];
     } else {
        $translated_script=array($module);
     }
  }
  require("../../language/".$admin_aziend['lang']."/menu.inc.php");
  echo '<script type="text/javascript">
     countclick = 0;
     function chkSubmit() {
        if(countclick > 0) {
            alert("'.$strCommon['wait_al'].'");
            document.getElementById(\'preventDuplicate\').disabled=true;
            return false;
        } else {
            countclick++;
            document.getElementById(\'preventDuplicate\').hidden=true;
            return true;
        }
     }
     </script>';
  return ($strCommon+$translated_script);
}

function createGazieCookMenu($m,$r)
{
/*
Questa funzione  crea l'array Javascript da passare a JSCookMenu
dove sulla barra orizzontale in alto c'è il menu del modulo corrente
*/
$acc="<script type=\"text/javascript\">\n var myMenu = [";
$acc.= "['<img class=\"seq1\" src=\"".$m[0]['icon']."\" />','".$m[0]['name']."','".$m[0]['link']."','_new',null\n";
unset($m[0][0],$m[0]['link'],$m[0]['icon'],$m[0]['name'],$m[0]['title'],$m[0]['class']);
$topm=$m[0];
unset($m[0]);
foreach($m as $v1) {
    $acc .= ",['<img class=\"seq1\" src=\"".$v1['icon']."\"/><img class=\"seq2\" src=\"".$v1['icon']."\" />','".$v1['name']."','".$v1['link']."',null,null\n";
    unset($v1['link'],$v1['icon'],$v1['name'],$v1['title'],$v1['class']);
    foreach($v1 as $v2) {
        $acc .= ",[null,'".$v2['name']."','".$v2['link']."',null,null";
        unset($v2['link'],$v2['icon'],$v2['name'],$v2['title'],$v2['class']);
        foreach($v2 as $v3) {
            $acc .= ",['','".$v3['name']."','".$v3['link']."',null,null]";
        }
        $acc .= "]\n";
    }
    $acc .= "]\n";
}
$acc .= "]\n";
foreach($topm as $v2) {
        $acc .= ",_cmSplit,[null,'".$v2['name']."','".$v2['link']."',null,null";
        unset($v2['link'],$v2['icon'],$v2['name'],$v2['title'],$v2['class']);
        foreach($v2 as $v3) {
            $acc .= ",[null,'".$v3['name']."','".$v3['link']."',null,null]";
        }
        $acc .= "]\n";
}
$acc .= "];\n </script>\n";
return $acc;
}
?>