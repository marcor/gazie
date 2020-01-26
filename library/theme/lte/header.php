<?php
/*
  -------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 Antonio De Vincentiis Montesilvano (PE)
  (http://www.devincentiis.it)
  <http://gazie.sourceforge.net>
  -------------------------------------------------------------------
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
  -------------------------------------------------------------------
 */

/*
ANTONIO DE VINCENTIIS: COMMENTO perché obbliga ad avere il file gconfig.myconf.php

// Load object autoloader
include_once("../../library/include/classes/Autoloader.php");
$GAzie = \GAzie\GAzie::factory();
if ( $GAzie->moduleLoaded() ) {
	# Prendo admin_aziend dall'oggetto
	$admin_aziend = $GAzie->getCheckAdmin();
} 
*/

$config = new UserConfig;

if ( $maintenance != FALSE ) header("Location: ../../modules/root/maintenance.php");

require("../../library/theme/lte/function.php");

if (!strstr($_SERVER["REQUEST_URI"], "login_admin") == "login_admin.php") {
    $_SESSION['lastpage'] = $_SERVER["REQUEST_URI"];
}
$menuclass = ' class="FacetMainMenu" ';
$style = 'default.css';
$skin = 'default.css';
if (isset($_POST['logout'])) {
    header("Location: logout.php");
    exit;
}

if ($scriptname != $prev_script && $scriptname != 'admin.php') { // aggiorno le statistiche solo in caso di cambio script
    $result = gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' adminid="' . $admin_aziend["user_name"] . '" AND company_id="' . $admin_aziend['company_id'] . '" AND link="' . $mod_uri . '" ', ' adminid', 0, 1);
    $value = array();
    if (gaz_dbi_num_rows($result) == 0) {
        $value['transl_ref'] = get_transl_referer($mod_uri);
        $value['adminid'] = $admin_aziend["user_name"];
        $value['company_id'] = $admin_aziend['company_id'];
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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
		<meta name="mobile-web-app-capable" content="yes">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-title" content="GAzie - Gestione AZIEndale">
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <title><?php echo gettitolo($mod_uri).' > '.$admin_aziend['ragso1']; ?></title>
        <link rel="shortcut icon" href="../../library/images/favicon.ico">			
		<link rel="icon" sizes="192x192" href="../../library/images/gaz192.png" />
		<link rel="apple-touch-icon" href="../../library/images/apple-icon-114x114-precomposed.png">
		<link rel="apple-touch-startup-image" href="../../library/images/apple-icon-114x114-precomposed.png">		
		<link rel="apple-touch-icon-precomposed" sizes="57x57" href="../../library/images/apple-icon-57x57-precomposed.png" />
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../../library/images/apple-icon-72x72-precomposed.png" />
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../../library/images/apple-icon-114x114-precomposed.png" />
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="../../library/images/apple-icon-144x144-precomposed.png" />
        <link href="../../library/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="../../library/theme/lte/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="../../library/theme/lte/ionicons/css/ionicons.min.css">
        <link rel="stylesheet" href="../../library/theme/lte/adminlte/dist/css/AdminLTE.css">
        <link rel="stylesheet" href="../../library/theme/lte/adminlte/dist/css/skins/skin-gazie.css"> <!-- _all-skins.min.css">-->
        <link href="../../js/jquery.ui/jquery-ui.css" rel="stylesheet">
		<script src="../../js/jquery/jquery.js"></script>
		

        <?php
        if (!empty($admin_aziend['style']) && file_exists("../../library/theme/lte/scheletons/" . $admin_aziend['style'])) {
            $style = $admin_aziend['style'];
        }
        if (!empty($admin_aziend['skin']) && file_exists("../../library/theme/lte/skins/" . $admin_aziend['skin'])) {
            $skin = $admin_aziend['skin'];
        }
        ?>
        <link href="../../library/theme/lte/scheletons/<?php echo $style; ?>" rel="stylesheet" type="text/css" />
        <link href="../../library/theme/lte/skins/<?php echo $skin; ?>" rel="stylesheet" type="text/css" />
        <style>
            .company-color { 
                background-color: #<?php echo $admin_aziend['colore']; ?> ; 
            }
            .dropdown-menu > li > a:hover {
                background-color: #<?php echo $admin_aziend['colore']; ?> ;
            }
            .navbar-default .navbar-nav > li > a:hover {
                background-color: #<?php echo $admin_aziend['colore']; ?>;
            }
        </style>  
    </head>
    <?php
    // imposto le opzioni del tema caricando le opzioni del database

    $val = $config->getValue('LTE_Fixed');
    if (!isset($val)) {
        $config->setDefaultValue();
        header("Location: ../../modules/root/admin.php");
    } else {
        $val = "";
    }

    if ($config->getValue('LTE_Fixed') == "true")
        $val = " fixed";
    if ($config->getValue('LTE_Boxed') == "true")
        $val = " layout-boxed";
    if ($config->getValue('LTE_Collapsed') == "true")
        $val .= " sidebar-collapse";
    if ($config->getValue('LTE_Onhover') == "true")
        $val .= " wysihtml5-supported sidebar-collapse";
    if ($config->getValue('LTE_SidebarOpen') == "true")
        $val .= " control-sidebar-open";

    echo "<body class=\"hold-transition skin-blue sidebar-mini " . $val . "\">";
    ?>
    <form method="POST" name="head_form" action="../../modules/root/admin.php">
        <div class="wrapper">
            <header class="main-header">
                <!-- Logo -->
                <a href="../../modules/root/admin.php" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini">
                        <img src="../../modules/root/view.php?table=aziend&amp;value=<?php echo $admin_aziend["company_id"]; ?>" height="30" alt="Logo" border="0" title="<?php echo $admin_aziend["ragso1"]; ?>" />
                    </span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg">
                        <img src="../../modules/root/view.php?table=aziend&amp;value=<?php echo $admin_aziend["company_id"]; ?>" height="30" alt="Logo" border="0" title="<?php echo $admin_aziend["ragso1"]; ?>" />
                        &nbsp;
<?php echo substr($admin_aziend["ragso1"], 0, 16); ?>
                    </span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                    </a>    
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">     
                            <?php
                            if ($module == "finann")
                                $fileDocs = "finean";
                            else
                                $fileDocs = $module;
                            //leggo dalla tabella admin_module se il modulo wiki è abilitato
                            $res_access_mod = gaz_dbi_dyn_query ( "*", $gTables["admin_module"], 'moduleid=16','moduleid asc');
                            $row_access_mod = gaz_dbi_fetch_array($res_access_mod);
                            if ( $row_access_mod['access'] == 0 ) {
                                //visualizzo la documentazione standard
                                echo "<li><a target=\"_new\" href=\"../../modules/" . $module . "/docume_" . $fileDocs . ".php\"><i class=\"fa fa-question\"></i></a></li>";
                            } else {
                                //visualizzo il link alla wiki
                                echo "<li><a target=\"_new\" href=\"../../modules/wiki/\"><i class=\"fa fa-question\"></i></a></li>";
                            }
                            $res_sync_mod = gaz_dbi_dyn_query ( "*", $gTables["admin_module"], 'moduleid=17','moduleid asc');
                            $row_sync_mod = gaz_dbi_fetch_array($res_sync_mod);
                            if ( $row_sync_mod['access'] != 0 ) {
                                echo "<li><a href='../../modules/shop-synchronize/synchronize.php' class='glyphicon glyphicon-transfer'></a></li>";
                            }
                            ?>
                            <!-- Messages: style can be found in dropdown.less-->
                            <li class="dropdown messages-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-star" style="color: yellow"></i>
                                    <!--<span class="label label-success">4</span>-->
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="header">Funzioni più utilizzate</li>
                                    <li>
                                        <!-- inner menu: contains the actual data -->
                                        <ul class="menu">
                                            <?php
                                            $result = gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' company_id="' . $admin_aziend['company_id'] . '" AND adminid="' . $admin_aziend["user_name"] . '" ', ' click DESC, last_use DESC', 0, 8);
                                            if (gaz_dbi_num_rows($result) > 0) {
                                                while ($r = gaz_dbi_fetch_array($result)) {
                                                    $rref = explode('-', $r['transl_ref']);

                                                    switch ($rref[1]) {
                                                        case 'm1':
                                                            require '../' . $rref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                                                            $rref_name = $transl[$rref[0]]['title'];
                                                            break;
                                                        case 'm2':
                                                            require '../' . $rref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                                                            $rref_name = $transl[$rref[0]]['m2'][$rref[2]][0];
                                                            break;
                                                        case 'm3':
                                                            require '../' . $rref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                                                            $rref_name = $transl[$rref[0]]['m3'][$rref[2]][0];
                                                            break;
                                                        case 'sc':
                                                            require '../' . $rref[0] . '/lang.' . $admin_aziend['lang'] . '.php';
                                                            $rref_name = $strScript[$rref[2]][$rref[3]];
                                                            break;
                                                        default:
                                                            $rref_name = 'Nome script non trovato';
                                                            break;
                                                    }
                                                    ?>
                                                    <li><!-- start message -->
                                                        <a href="<?php
                                                if ($r["link"] != "")
                                                    echo '../../modules' . $r["link"];
                                                else
                                                    echo "&nbsp;";
                                                ?>">
                                                            <div class="pull-left">
                                                                <i class="fa fa-archive" style="color:#<?php echo $r["color"]; ?>"></i>
                                                            </div>
                                                            <h4>
                                                    <?php echo substr($rref_name, 0, 28); ?>
                                                                <small><i class="fa fa-thumbs-o-up"></i> <?php echo $r["click"] . ' click'; ?></small>
                                                            </h4>
                                                            <p><?php echo substr($r["link"], 0, 38); ?></p>
                                                        </a>
                                                    </li>  
        <?php
    }
}
?>
                                        </ul>
                                    </li>
                                    <li class="footer"><a href="../../modules/root/admin.php">Vedi tutte</a></li>
                                </ul>
                            </li>

                            <!-- Sezione link più usati -->
                            <li class="dropdown messages-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-clock-o"></i>
                                    <!--<span class="label label-success">4</span>-->
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="header">Ultime funzioni utilizzate</li>
                                    <li>
                                        <!-- inner menu: contains the actual data -->
                                        <ul class="menu">
                                            <?php
                                            $res_last = gaz_dbi_dyn_query("*", $gTables['menu_usage'], ' company_id="' . $admin_aziend['company_id'] . '" AND adminid="' . $admin_aziend["user_name"] . '" ', ' last_use DESC, click DESC', 0, 8);
                                            if (gaz_dbi_num_rows($res_last) > 0) {
                                                while ($rl = gaz_dbi_fetch_array($res_last)) {
                                                    $rlref = explode('-', $rl['transl_ref']);
                                                    switch ($rlref[1]) {
                                                        case 'm1':
                                                            require '../' . $rlref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                                                            $rlref_name = $transl[$rlref[0]]['title'];
                                                            break;
                                                        case 'm2':
                                                            require '../' . $rlref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                                                            $rlref_name = $transl[$rlref[0]]['m2'][$rlref[2]][0];
                                                            break;
                                                        case 'm3':
                                                            require '../' . $rlref[0] . '/menu.' . $admin_aziend['lang'] . '.php';
                                                            $rlref_name = $transl[$rlref[0]]['m3'][$rlref[2]][0];
                                                            break;
                                                        case 'sc':
                                                            require '../' . $rlref[0] . '/lang.' . $admin_aziend['lang'] . '.php';
                                                            $rlref_name = $strScript[$rlref[2]][$rlref[3]];
                                                            break;
                                                        default:
                                                            $rlref_name = 'Nome script non trovato';
                                                            break;
                                                    }
                                                    ?>
                                                    <li>
                                                        <a href="<?php
                                                if ($rl["link"] != "")
                                                    echo '../../modules' . $rl["link"];
                                                else
                                                    echo "&nbsp;";
                                                    ?>">
                                                            <div class="pull-left">
                                                                <i class="fa fa-archive" style="color:#<?php echo $rl["color"]; ?>"></i>
                                                            </div>
                                                            <h4>
        <?php
        if (is_string($rlref_name)) {
            echo substr($rlref_name, 0, 28);
        } else {
            //print_r( $rlref_name);
            echo 'Nome script non trovato';
        }
        ?>
                                                                <small><i class="fa fa-clock-o"></i> <?php echo gaz_time_from(strtotime($rl["last_use"])); ?></small>
                                                            </h4>
                                                            <p><?php echo substr($rl["link"], 0, 38); ?></p>
                                                        </a>
                                                    </li>
        <?php
    }
}
?>
                                        </ul>
                                    </li>
                                    <li class="footer"><a href="../../modules/root/admin.php">Vedi tutte</a></li>
                                </ul>
                            </li>

                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="<?php echo '../root/view.php?table=admin&field=user_name&value=' . $admin_aziend["user_name"]; ?>" class="user-image" alt="User Image">
                                    <span class="hidden-xs"><?php echo $admin_aziend['user_firstname'] . ' ' . $admin_aziend['user_lastname']; ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="<?php echo '../root/view.php?table=admin&field=user_name&value=' . $admin_aziend["user_name"]; ?>" class="img-circle" alt="User Image">
                                        <p>
<?php echo $admin_aziend['user_firstname'] . ' ' . $admin_aziend['user_lastname']; ?>
                                            <small>
                                                Questo è il tuo <b><?php echo $admin_aziend['Access']; ?>°</b> accesso<br/>
                                                La tua password risale al <b><?php echo gaz_format_date($admin_aziend['datpas']); ?></b><br>
                                            </small>
                                        </p>
                                    </li>
                                    <!-- Menu Body -->
                                    <li class="user-body">
                                        <div class="col-xs-4 text-center">
                                            <a href="../config/admin_aziend.php">
                                                <img class="img-circle" src="../../modules/root/view.php?table=aziend&value=<?php echo $admin_aziend['company_id']; ?>" width="90" alt="Logo" border="0" >
                                            </a>
                                        </div>
                                        <div class="col-xs-8 text-center" align="center">
                                            <a href="../../modules/root/admin.php"><?php echo $admin_aziend['ragso1'] . "<br>" . $admin_aziend['ragso2']; ?></a> 
<?php //selectCompany('company_id', $form['company_id'], $form['search']['company_id'], $form['hidden_req'], $script_transl['mesg_co']);  ?>
                                        </div>
                                        <!--<div class="col-xs-4 text-center">
                                          <a href="#">Friends</a>
                                        </div>-->
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="../../modules/config/admin_utente.php?user_name=<?php echo $admin_aziend["user_name"]; ?>&Update" class="btn btn-default btn-flat">Profilo</a>
                                        </div>
                                        <div class="pull-right">
                                            <input name="logout" type="submit" value=" Logout " class="btn btn-default btn-flat">
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <!-- Control Sidebar Toggle Button -->
<?php
if ($admin_aziend['Abilit'] == 9) {
    echo "<li><a href=\"#\" data-toggle=\"control-sidebar\"><i class=\"fa fa-bars\"></i></a></li>";
} else {
    echo "<li></li>";
}
?>

                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <!--<div class="user-panel">
                      <div class="pull-left image">
                        <img src="<?php //echo '../root/view.php?table=admin&field=user_name&value=' . $admin_aziend["user_name"];  ?>" class="img-circle" alt="User Image">
                      </div>
                      <div class="pull-left info">
                        <p><?php //echo $admin_aziend['Nome'].' '.$admin_aziend['Cognome'];  ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                      </div>
                    </div>
                    <!-- search form--> 

                    <ul class="sidebar-menu">
                        <!--<li class="header">MENU' PRINCIPALE</li>-->
