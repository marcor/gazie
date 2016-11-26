<?php
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
   //echo getcwd();
?>
<!DOCTYPE html>
<html>
  <head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <title><?php echo $admin_aziend['ragso1'];?></title>
   <link rel="shortcut icon" href="../../library/images/favicon.ico">			
   <!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../library/theme/lte/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../library/theme/lte/ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/plugins/iCheck/flat/blue.css">
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/plugins/morris/morris.css">
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/plugins/datepicker/datepicker3.css">
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/plugins/daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" href="../../library/theme/lte/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <script src="../../library/theme/lte/adminlte/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    
<?php
	if (!empty($admin_aziend['style']) && file_exists("../../library/theme/lte/skeletons/" . $admin_aziend['style'])) {
		$style = $admin_aziend['style'];
	}
	if (!empty($admin_aziend['skin']) && file_exists("../../library/theme/lte/skins/" . $admin_aziend['skin'])) {
		$skin = $admin_aziend['skin'];
	}
?>
    <!--<link href="../../library/style/<?php echo $style; ?>" rel="stylesheet" type="text/css" />-->
    <!--<link href="../../library/style/skins/<?php echo $skin; ?>" rel="stylesheet" type="text/css" />-->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="hold-transition skin-blue sidebar-mini">
    <form method="POST" name="head_form">
    <div class="wrapper">

      <header class="main-header">
        <!-- Logo -->
        <a href="../../modules/root/admin.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>G<?php echo $versSw; ?></b></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>GA</b>zie <?php echo $versSw; ?></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="<?php echo '../root/view.php?table=admin&field=Login&value=' . $admin_aziend['Login']; ?>" class="user-image" alt="User Image">
                  <span class="hidden-xs"><?php echo $admin_aziend['Nome'].' '.$admin_aziend['Cognome']; ?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="<?php echo '../root/view.php?table=admin&field=Login&value=' . $admin_aziend['Login']; ?>" class="img-circle" alt="User Image">
                    <p>
                      <?php echo $admin_aziend['Nome'].' '.$admin_aziend['Cognome']; ?>
                      <small>
                      Questo è il tuo <b><?php echo $admin_aziend['Access']; ?>°</b> accesso<br/>
                      La tua password risale al <b><?php echo gaz_format_date($admin_aziend['datpas']);?></b><br>
                      </small>
                    </p>
                  </li>
                  <!-- Menu Body -->
                  <li class="user-body">
                    <div class="col-xs-4 text-center">
                      <a href="../config/admin_aziend.php">
                        <img class="img-circle usr-picture" src="view.php?table=aziend&value=<?php echo $form['company_id']; ?>" width="70" alt="Logo" border="0" title="<?php echo $script_transl['upd_company']; ?>" >
                     </a>
                    </div>
                    <div class="col-xs-8 text-center" align="center">
                      <a href="cambia_azien.php"><?php echo $admin_aziend['ragso1']."<br>".$admin_aziend['ragso2']; ?></a> 
                      <?php //selectCompany('company_id', $form['company_id'], $form['search']['company_id'], $form['hidden_req'], $script_transl['mesg_co']); ?>
                    </div>
                    <!--<div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div>-->
                  </li>
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="../../modules/config/admin_utente.php?Login=<?php echo $admin_aziend['Login']; ?>&Update" class="btn btn-default btn-flat">Profilo</a>
                    </div>
                    <div class="pull-right">
                      <input name="logout" type="submit" value=" Logout " class="btn btn-default btn-flat">
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Control Sidebar Toggle Button -->
              <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
              </li>
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
              <img src="<?php //echo '../root/view.php?table=admin&field=Login&value=' . $admin_aziend['Login']; ?>" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p><?php //echo $admin_aziend['Nome'].' '.$admin_aziend['Cognome']; ?></p>
              <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
          </div>
          <!-- search form 
          <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
              <input type="text" name="q" class="form-control" placeholder="Search...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
          </form>-->
          <ul class="sidebar-menu">
            <!--<li class="header">MENU' PRINCIPALE</li>-->
<?php

function HeadMain($idScript = '', $jsArray = '', $alternative_transl = false, $cssArray = '') {
    global $module, $admin_aziend, $radix, $scriptname;
    if (is_array($jsArray)) {
        foreach ($jsArray as $v) {
            echo '			<script type="text/javascript" src="../../js/' . $v . '.js"></script>';
        }
    }
    if (is_array($cssArray)) {
        foreach ($cssArray as $v) {
            echo '			<link rel="stylesheet" type="text/css" href="../../modules/' . $v . '">';
        }
    }
    $result = getAccessRights($_SESSION['Login'], $_SESSION['company_id']);
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
                    require("../../modules/" . $row['name'] . "/menu.".$admin_aziend['lang'].".php");
                }
                if ($row['name'] == $module) {
                    $row['weight'] = 0;

                    if ($row['m3_link'] == $scriptname) {
                        $title_from_menu = $transl[$row['name']]['m3'][$row['m3_trkey']][0];
                    }

                    if ($ctrl_m2 != $row['m2_id'] and $ctrl_m1 != $row['m1_id']) {
                        require("../../modules/" . $row['name'] . "/lang.".$admin_aziend['lang'].".php");
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
                            'icon' => $row['icon'],
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
                    $menuArray[$row['weight']][$row['m2_weight']][$row['m3_weight']] = array('link' => '../' . $row['name'] . '/' . $row['m3_link'],
                        'icon' => '../' . $row['name'] . '/' . $row['m3_icon'],
                        'name' => $transl[$row['name']]['m3'][$row['m3_trkey']][1],
                        'title' => $transl[$row['name']]['m3'][$row['m3_trkey']][0],
                        'class' => $row['m3_class']);
                } elseif ($ctrl_m1 != $row['m1_id']) { // è il primo di menu2
                    $menuArray[$row['weight']] = array('link' => '../' . $row['name'] . '/' . $row['link'],
                        'icon' => $row['icon'],
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

        if (!empty($idScript)) {
            if (is_array($idScript)) { // $idScript dev'essere un array con index [0] per il numero di menu e index[1] per l'id dello script
                if ($idScript[0] == 2) {
                    echo '			&raquo;' . $transl[$module]['m2'][$idScript[1]][0];
                } elseif ($idScript[0] == 3) {
                    echo '			&raquo;' . $transl[$module]['m3'][$idScript[1]][0];
                }
            } elseif ($idScript > 0) {
                echo '			&raquo;' . $transl[$module]['m3'][$idScript][0];
            }
        } elseif (isset($title_from_menu)) {
            //echo '			&raquo;' . $title_from_menu;
        }
        //$tipomenu = substr($admin_aziend['style'], 0, -4);
    $i = 0;
    foreach ($menuArray as $link) {
        if ( $i==0 ) {
                    ?>
            <li class="treeview active">
                <a href="<?php echo $link['link']; ?>"><!--'.$link['link'].'-->
                <i class="<?php echo $link['icon']; ?>"></i>
                    <span><?php echo $link['name']; ?></span>
                <i class="fa fa-angle-left pull-right"></i>
                </a>
            <?php
        } else {
            ?>
            <li class="treeview">
                <a href="<?php echo $link['link'];?>">
                <i class="<?php echo $link['icon']; ?>"></i>
                    <span><?php echo $link['name']; ?></span>
                <i class="fa fa-angle-left pull-right"></i>
                </a>
            <?php
        }
        submenu($link, $i);
        echo "          </li>\n";
        $i++;
    }
?>
    </ul>
    </section>
    </aside>
</form>
    <div class="content-wrapper">
      <section class="content-header">
         <h1>
         <!--TITOLO<?php echo $script_transl['title']; ?>-->
         </h1>
         <ol class="breadcrumb">
         <!--<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Examples</a></li>
         <li class="active">User profile</li>
         -->
         <li>&nbsp;</li>
         </ol>
      </section>

<?php
    }
    if (!isset($translated_script)) {
        if ($alternative_transl) { // se e' stato passato il nome dello script sul quale mi devo basare per la traduzione
            $translated_script = $strScript[$alternative_transl . '.php'];
        } else {
            $translated_script = array($module);
        }
    }
    require("../../language/".$admin_aziend['lang']."/menu.inc.php");
    echo '<script type="text/javascript">
		 countclick = 0;
		 function chkSubmit() {
			if(countclick > 0) {
				alert("' . $strCommon['wait_al'] . '");
				document.getElementById(\'preventDuplicate\').disabled=true;
				return false;
			} else {
				var alPre = document.getElementById(\'confirmSubmit\').value.toString();
				if (alPre) {
					var conf = confirm (alPre);
					if (!conf) {
						document.getElementById(\'preventDuplicate\').disabled=true;
						return true;
					}
				}
				countclick++;
				document.getElementById(\'preventDuplicate\').hidden=true;
				return true;
			}
		 }
		 </script>
		 <!--<div class="container" role="main">-->';
         return ($strCommon + $translated_script);
}

function submenu($array, $index, $sub="") {
	if(!is_array($array)) { return ;}
	$numsub = 0;
	$submenu = '';    
   
	foreach($array as $i => $mnu) {
		if(!is_array($mnu)) {continue;}      
		$submnu = '';
		if ($numsub === 0) {
        	?>
    <ul class="treeview-menu">
            <?php
		}       
		if (count($mnu)>5) {	//	Esiste un sotto menu
            ?>
               <li>
                    <?php 
                    $sub = '<a href="'.$mnu["link"].'">Lista '.$submnu.stripslashes($mnu["name"]);
                    ?>
                    <a href="#"><?php echo $submnu.stripslashes($mnu["name"]); ?>
                    <i class="fa fa-angle-left pull-right"></i>
                    </a>                    
                <?php submenu($mnu, 1, $sub);	?>
                </li>
        <?php } else { 
        if ( $sub!="" ) {
            echo "<li>$sub</a></li>";
            $sub="";
        }
        ?>
            <li >
                    <a href="<?php echo $mnu['link']; ?>"><?php echo $submnu.stripslashes($mnu['name']); ?></a>
                    </li>
        <?php }
		$numsub++;
	}
	if ($numsub > 0) {?>
            </ul>        
    <?php }
}
?>