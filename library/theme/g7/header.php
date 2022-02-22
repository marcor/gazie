<?php
$pdb = gaz_dbi_get_row($gTables['company_config'], 'var', 'menu_alerts_check')['val'];
$period = ($pdb == 0) ? 60 : $pdb;
require("../../library/theme/g7/function.php");
if ( isset($maintenance) && $maintenance!=FALSE && $maintenance!=$_SESSION['user_email'] ) {
	header("Location: ../../modules/root/maintenance.php");
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="content-language" content="en, it, es">
		<meta name="mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Antonio de Vincentiis http://www.devincentiis.it">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-title" content="<?php echo $admin_aziend['ragso1'];?>">
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
		<?php
        if (substr($admin_aziend['ragso1'],0,16)=='AZIENDA DI PROVA'){ // l'azienda di default prende il maialino
			$ico=base64_encode(file_get_contents( '../../library/images/favicon.ico' ));
            $ico114=base64_encode(file_get_contents( '../../library/images/logo_114x114.png' ));
        } else { // altrimenti prendo le icone create in fase di scelta del logo in configurazione azienda
            $ico=base64_encode(@file_get_contents( DATA_DIR . 'files/' . $admin_aziend['codice'] . '/favicon.ico' ));
            $ico114=base64_encode(@file_get_contents( DATA_DIR . 'files/' . $admin_aziend['codice'] . '/logo_114x114.png' ));
        }
		?>
        <link rel="icon" href="data:image/x-icon;base64,<?php echo $ico?>"  type="image/x-icon" />
		<link rel="icon" sizes="114x114" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon" />
		<link rel="apple-touch-icon" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon">
		<link rel="apple-touch-startup-image" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon">		
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon" />
        <link href="../../library/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        <link href="../../js/jquery.ui/jquery-ui.css" rel="stylesheet">
        <link href="../../library/theme/g7/smartmenus-master/bootstrap/jquery.smartmenus.bootstrap.css" rel="stylesheet" />
        <script src="../../js/jquery/jquery.js"></script>

        <?php
        // carico il css strutturale grandezza font, posizione, ecc 
        $style = 'base.css';
        if (!empty($admin_aziend['style']) && file_exists("../../library/theme/g7/scheletons/" . $admin_aziend['style'])) {
            $style = $admin_aziend['style'];
        }
        // carico i fogli di stile personalizzati nella subdir skin si imposta l'aspetto (colori, font, ecc) 
        $skin = 'base.css';
        if (!empty($admin_aziend['skin']) && file_exists("../../library/theme/g7/skins/" . $admin_aziend['skin'])) {
            $skin = $admin_aziend['skin'];
        }

        function hex_color_mod($hex, $diff) {
            $rgb = str_split($hex, 2);
            foreach ($rgb as &$hex) {
                $dec = hexdec($hex);
                if ($diff >= 0) {
                    $dec += $diff;
                } else {
                    $dec -= abs($diff);
                }
                $dec = max(0, min(255, $dec));
                $hex = str_pad(dechex($dec), 2, '0', STR_PAD_LEFT);
            }
            return '#' . implode($rgb);
        } 
        ?>
        <link href="../../library/theme/g7/scheletons/<?php echo $style; ?>" rel="stylesheet" type="text/css" />
        <link href="../../library/theme/g7/skins/<?php echo $skin; ?>" rel="stylesheet" type="text/css" />
        <style type="text/css">
            .navbar-default .navbar-collapse { 
                background-color: <?php echo hex_color_mod($admin_aziend['colore'],20); ?> ; 
            }
            .company-color { 
                background-color: #<?php echo $admin_aziend['colore']; ?> ; 
            }
            .dropdown-menu > li > a:hover {
                background-color: #<?php echo $admin_aziend['colore']; ?> ;
            }
            .navbar-default .navbar-nav > li > a:hover {
                background-color: #<?php echo $admin_aziend['colore']; ?>;
            }
			div.blink{
			  animation:blink 700ms infinite alternate;
			  padding-top:10px;
			}
			div.blink>a.btn{
			  padding:5px;
			}
			@keyframes blink {
				from { opacity:1; } to { opacity:0; }
			}   
            .ui-dialog-buttonset>button.btn.btn-confirm:first-child {
                background-color: #f9b54d;
            }       
        </style>  
        <?php

        function get_transl_referer($rlink) {
            global $gTables;
            $clink = explode('/', $rlink);
            $n1 = gaz_dbi_get_row($gTables['module'], 'link', end($clink));
            if ($n1) {
                include "../../modules/" . $clink[1] . "/menu.italian.php";
                return $clink[1] . '-m1-' . $n1['id'];
            } else {
                $n2 = gaz_dbi_get_row($gTables['menu_module'], 'link', end($clink));
                if ($n2) {
                    include "../../modules/" . $clink[1] . "/menu.italian.php";
                    return $clink[1] . '-m2-' . $n2['translate_key'];
                } else {
                    $n3 = gaz_dbi_get_row($gTables['menu_script'], 'link', end($clink));
                    if ($n3) {
                        include "../../modules/" . $clink[1] . "/menu.italian.php";
                        return $clink[1] . '-m3-' . $n3['translate_key'];
                    } else { // non l'ho trovato neanche nel m3, provo sui file di traduzione
                        include "../../modules/" . $clink[1] . "/lang.italian.php";
                        // tento di risalire allo script giusto
                        $n_scr = explode('?', end($clink));
                        if (isset($strScript[$n_scr[0]])) { // ho trovato una traduzione per lo script
                            if (isset($strScript[$n_scr[0]]['title'])) { // ho trovato una traduzione per lo script con index specifico
                                if (is_array($strScript[$n_scr[0]]['title'])) {
                                    return $clink[1] . '-sc-' . $n_scr[0] . '-title-' . array_shift(array_slice($strScript[$n_scr[0]]['title'], 0, 1));
                                } else {
                                    return $clink[1] . '-sc-' . $n_scr[0] . '-title';
                                }
                            } elseif (isset($strScript[$n_scr[0]][0])) { // ho trovato una traduzione per lo script nel primo elemento
                                if (is_array($strScript[$n_scr[0]][0])) {
                                    return $clink[1] . '-sc-' . $n_scr[0] . '-0-' . array_shift(array_slice($strScript[$n_scr[0]][0], 0, 1));
                                } else {
                                    return $clink[1] . '-sc-' . $n_scr[0] . '-0';
                                }
                            } else { // non ho trovato nulla nemmeno sui file tipo lang.english.php
                                return $clink[1] . '-none-script';
                            }
                        } else { // non c'è traduzione per questo script 
                            return $clink[1] . '-none-script_menu';
                        }
                    }
                }
            }
        }

//aggiornamento automatico della tabella gaz_menu_usage
        if ( isset($scriptname) && isset($prev_script) && $scriptname != $prev_script && $scriptname != 'admin.php') { // aggiorno le statistiche solo in caso di cambio script
            $result = @gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' adminid="' . $admin_aziend["user_name"] . '" AND company_id="' . $admin_aziend['company_id'] . '" AND link="' . $mod_uri . '" ', ' adminid', 0, 1);
            $value = array();
            if (gaz_dbi_num_rows($result) == 0) {
                $value['transl_ref'] = get_transl_referer($mod_uri);
                $value['adminid'] = @$admin_aziend["user_name"];
                $value['company_id'] = @$admin_aziend['company_id'];
                $value['link'] = $mod_uri;
                $value['click'] = 1;
                $value['color'] = pastelColors();
                $value['last_use'] = date('Y-m-d H:i:s');
                gaz_dbi_table_insert('menu_usage', $value);
            } else {
                $usage = gaz_dbi_fetch_array($result);
                gaz_dbi_put_query($gTables['menu_usage'], ' adminid="' . $admin_aziend["user_name"] . '" AND company_id="' . $admin_aziend['company_id'] . '" AND link="' . $mod_uri . '"', 'click', $usage['click'] + 1);
            }
        }

        function pastelColors() {
            $r = dechex(round(((float) rand() / (float) getrandmax()) * 127) + 127);
            $g = dechex(round(((float) rand() / (float) getrandmax()) * 127) + 127);
            $b = dechex(round(((float) rand() / (float) getrandmax()) * 127) + 127);

            return $r . $g . $b;
        }

        function HeadMain($idScript = '', $jsArray = '', $alternative_transl = false, $cssArray = '') {
            global $module, $admin_aziend, $radix, $scriptname, $debug_active;
            /* - In $idScript si deve passare l'id dell'array submenu (m2) in menu.language.php (per mettere nel tag <TITLE> )
              oltre che il nome del modulo anche quello dello script tradotto
              - In $jsArray di devono passare i nomi dei file javascript che si vogliono caricare e presenti nella directory 'js'
             */
            if (is_array($jsArray)) {
                foreach ($jsArray as $v) {
                    echo "          <!-- js caricato dallo script in esecuzione -->\n" . '        <script type="text/javascript" src="../../js/' . $v . '.js"></script>' . "\n";
                }
            }
            if (is_array($cssArray)) {
                foreach ($cssArray as $v) {
                    echo "          <!-- sytle caricato dallo script in esecuzione -->\n" . '        <link rel="stylesheet" type="text/css" href="../../modules/' . $v . '">' . "\n";
                }
            }
            $result = getAccessRights($_SESSION["user_name"], $_SESSION['company_id']);
            if (gaz_dbi_num_rows($result) > 0) {
                // creo l'array associativo per la generazione del menu con JSCookMenu
                $ctrl_m1 = 0;
                $ctrl_m2 = 0;
                $ctrl_m3 = 0;
                $menuArray = array();
                $transl = array();
                while ($row = gaz_dbi_fetch_array($result)) {
                    if ($row['access'] == 3) {
                        if ($ctrl_m1 != $row['m1_id']) {
                            require("../../modules/" . $row['name'] . "/menu." . $admin_aziend['lang'] . ".php");
                        }
                        if ($row['name'] == $module) {
                            $row['weight'] = 0;

                            if ($row['m3_link'] == $scriptname) {
                                $title_from_menu = $transl[$row['name']]['m3'][$row['m3_trkey']][0];
                            }

                            if ($ctrl_m2 != $row['m2_id'] and $ctrl_m1 != $row['m1_id']) {
                                require("../../modules/" . $row['name'] . "/lang." . $admin_aziend['lang'] . ".php");
				if (isset($strScript[$scriptname])) { // se è stato tradotto lo script lo ritorno al chiamante
                                    $translated_script = $strScript[$scriptname];
                                    if (isset($translated_script['title'])) {
                                        $title_from_menu = $translated_script['title'];
                                    }
                                }
                            }
                        }
                        if (isset($row['m3_id']) and $row['m3_id'] > 0) { // è un menu3
                            if ($ctrl_m2 != $row['m2_id'] and $ctrl_m1 != $row['m1_id']) { // è pure il primo di menu2 e menu1
                                $menuArray[$row['weight']] = array('link' => '../' . $row['name'] . '/' . $row['link'],
                                    'icon' => '../' . $row['name'] . '/' . $row['icon'],
                                    'name' => $transl[$row['name']]['name'],
                                    'title' => $transl[$row['name']]['title'],
                                    'class' => $row['class']);
                                $menuArray[$row['weight']][$row['m2_weight']] = array('link' => '../' . $row['name'] . '/' . $row['m2_link'],
                                    'icon' => '../' . $row['name'] . '/' . $row['m2_icon'],
                                    'name' => $transl[$row['name']]['m2'][$row['m2_trkey']][1],
                                    'title' => $transl[$row['name']]['m2'][$row['m2_trkey']][0],
                                    'class' => $row['m2_class']);
                            } elseif ($ctrl_m2 != $row['m2_id']) { // è solo il primo di menu2
                                $menuArray[$row['weight']][$row['m2_weight']] = array('link' => '../' . $row['name'] . '/' . $row['m2_link'],
                                    'icon' => '../' . $row['name'] . '/' . $row['m2_icon'],
                                    'name' => $transl[$row['name']]['m2'][$row['m2_trkey']][1],
                                    'title' => $transl[$row['name']]['m2'][$row['m2_trkey']][0],
                                    'class' => $row['m2_class']);
                            }
                            // if (!isset($transl[$row['name']]['m3'][$row['m3_trkey']][1])) echo $row['name'] . '/' . $row['m3_link'].'<br>'; // decommentandolo evidenzio gli script con la key indefinita
                            $menuArray[$row['weight']][$row['m2_weight']][$row['m3_weight']] = array('link' => '../' . $row['name'] . '/' . $row['m3_link'],
                                'icon' => '../' . $row['name'] . '/' . $row['m3_icon'],
                                'name' => $transl[$row['name']]['m3'][$row['m3_trkey']][1],
                                'title' => $transl[$row['name']]['m3'][$row['m3_trkey']][0],
                                'class' => $row['m3_class']);
                        } elseif ($ctrl_m1 != $row['m1_id']) { // è il primo di menu2
                            $menuArray[$row['weight']] = array('link' => '../' . $row['name'] . '/' . $row['link'],
                                'icon' => '../' . $row['name'] . '/' . $row['icon'],
                                'name' => $transl[$row['name']]['name'],
                                'title' => $transl[$row['name']]['title'],
                                'class' => $row['class']);
                            $menuArray[$row['weight']][$row['m2_weight']] = array('link' => '../' . $row['name'] . '/' . $row['m2_link'],
                                'icon' => '../' . $row['name'] . '/' . $row['m2_icon'],
                                'name' => $transl[$row['name']]['m2'][$row['m2_trkey']][1],
                                'title' => $transl[$row['name']]['m2'][$row['m2_trkey']][0],
                                'class' => $row['m2_class']);
                        } else { // non è il primo di menu2
                            $menuArray[$row['weight']][$row['m2_weight']] = array('link' => '../' . $row['name'] . '/' . $row['m2_link'],
                                'icon' => '../' . $row['name'] . '/' . $row['m2_icon'],
                                'name' => $transl[$row['name']]['m2'][$row['m2_trkey']][1],
                                'title' => $transl[$row['name']]['m2'][$row['m2_trkey']][0],
                                'class' => $row['m2_class']);
                        }
                    }
                    $ctrl_m1 = $row['m1_id'];
                    $ctrl_m2 = $row['m2_id'];
                    $ctrl_m3 = $row['m3_id'];
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
                echo "\n
    
      <title>" . $admin_aziend['ragso1'] . '» ' . $menuArray[0]['title'];
                if (!empty($idScript)) {
                    if (is_array($idScript)) { // $idScript dev'essere un array con index [0] per il numero di menu e index[1] per l'id dello script
                        if ($idScript[0] == 2) {
                            echo '» ' . $transl[$module]['m2'][$idScript[1]][0];
                        } elseif ($idScript[0] == 3) {
                            echo '» ' . $transl[$module]['m3'][$idScript[1]][0];
                        }
                    } elseif ($idScript > 0) {
                        echo '» ' . $transl[$module]['m3'][$idScript][0];
                    }
                } elseif (isset($title_from_menu)) {
                    echo '» ' . $title_from_menu;
                }
                echo '</title>';
				echo "\n</head>\n<body> \n ";
                // cambia il tipo di menu
                $tipomenu = substr($admin_aziend['style'], 0, -4);
                if (file_exists("../../library/theme/g7/header_menu_" . $tipomenu . ".php")) {
                    require("../../library/theme/g7/header_menu_" . $tipomenu . ".php");
                } else {
                    require("../../library/theme/g7/header_menu_default.php");
                }
            }
            if (!isset($translated_script)) {
                if ($alternative_transl) { // se e' stato passato il nome dello script sul quale mi devo basare per la traduzione
                    $translated_script = $strScript[$alternative_transl . '.php'];
                } else {
                    $translated_script = array($module);
                }
            }
            require("../../language/" . $admin_aziend['lang'] . "/menu.inc.php");
            echo '<script type="text/javascript">
		 countclick = 0;
		 function chkSubmit() {
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            } 
			if(countclick > 0) {
				alert("' . $strCommon['wait_al'] . '");
				document.getElementById("preventDuplicate").disabled=true;
				return false;
			} else {
				var element = document.getElementById("confirmSubmit");
				if (element) {
				   var alPre = element.value.toString();
					var conf = confirm (alPre);
					if (!conf) {
						document.getElementById("preventDuplicate").disabled=true;
						return true;
					}
				}
				countclick++;
				document.getElementById("preventDuplicate").hidden=true;
				return true;
			}
		 }
</script>
<div class="container-fluid gaz-body">';
		printDash($gTables,$module,$admin_aziend,$transl);
            return ($strCommon + $translated_script);
        } 
?>