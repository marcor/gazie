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
require("../../library/include/datlib.inc.php");
$message = "";
$newpass = false;
$config = new Config;
//
// table prefix
//
if (isset($_POST['tp'])) {
    $tp=filter_var(substr($_POST['tp'],0,12),FILTER_SANITIZE_MAGIC_QUOTES);
} elseif(isset($_GET['tp'])) {
    $tp=filter_var(substr($_GET['tp'],0,12),FILTER_SANITIZE_MAGIC_QUOTES);
} else {
    $tp=filter_var(substr($table_prefix,0,12),FILTER_SANITIZE_MAGIC_QUOTES);
}
//
if (!table_prefix_ok ($tp)) {
  //
  // Ahia...
  //
  message_fatal_error ("Il prefisso delle tabelle non è valido: \"$tp\"");
  exit;
}
//
// utente predefinito.
//
if (isset($_POST['usr'])) {
    $usr=filter_var(substr($_POST['usr'],0,30),FILTER_SANITIZE_MAGIC_QUOTES);
} elseif(isset($_GET['usr'])) {
    $usr=filter_var(substr($_GET['usr'],0,30),FILTER_SANITIZE_MAGIC_QUOTES);
} else {
    $usr=filter_var(substr($default_user,0,30),FILTER_SANITIZE_MAGIC_QUOTES);
}
//
//
//
$server_lang=substr(strtoupper($local['cvalue']),0,2);
switch ($server_lang) {
    case 'IT':
        $lang = 'italian';
        break;
    case 'EN':
        $lang = 'english';
        break;
    case 'ES':
        $lang = 'spanish';
        break;
    default:
        $lang = 'italian';
        break;
}
require("./lang.".$lang.".php");
$script_transl = $strScript["login_admin.php"];

if (isset($_POST['actionflag'])) {
    $form['Login']=filter_var(substr($_POST['Login'],0,30),FILTER_SANITIZE_MAGIC_QUOTES);
    // checkUser();
    $result = gaz_dbi_get_row ($gTables['admin'], "Login", $form['Login']);
    if ($result) {
        require("../../library/include/HMAC.php");
        $crypt = new Crypt_HMAC($result["Password"], 'md5');
        $hmacPass = $crypt->hash($_COOKIE[session_name()]);
        if ($hmacPass == $_POST['Password']) {
            cleanMemberSession($result["Abilit"], $result["Login"], $result["Password"], $result["Access"], $result['enterprise_id'],$tp);
            $utspas = mktime(0,0,0, substr($result['datpas'],5,2), substr($result['datpas'],8,2), substr($result['datpas'], 0, 4));
            $utsoggi = mktime(0,0,0,date("m"),date("d"),date("Y")) - $config->getValue('giornipass') * 86400;
            if($utspas < $utsoggi) {
                $message .= $result["Nome"]." ".$result["Cognome"].$script_transl[2];
                if (! isset($_POST['Nuovapass'])) {
                    $_POST['Nuovapass'] = '';
                }
                if (! isset($_POST['Confepass'])) {
                    $_POST['Confepass'] = '';
                }
                if($_POST['Password'] != $_POST['Nuovapass'] and $_POST['Nuovapass'] == $_POST['Confepass'] and  strlen($_POST['Nuovapass']) >= $config->getValue('psw_min_length') ) {
                    gaz_dbi_put_row($gTables['admin'], "Login",$form['Login'],"datpas",date("Y-m-d H:i:s"));
                    gaz_dbi_put_row($gTables['admin'], "Login",$form['Login'],"Password",$_POST['Nuovapass']);
                    cleanMemberSession($result["Abilit"], $result["Login"], $_POST["Nuovapass"], $result["Access"], $result['enterprise_id'],$tp);
                    header("Location: ../root/admin.php");
                    exit;
                } else {
                    $message .= $script_transl[0].$config->getValue('psw_min_length').$script_transl[1];
                }
                $newpass = true;
            } else {
                if (isset($_SESSION["lastpage"]) && !empty($_SESSION["lastpage"]) && !strstr($_SESSION["lastpage"], "login_admin")=="login_admin.php") {
                    $lastpage = $_SESSION["lastpage"];
                    $_SESSION['lastpage'] = "";
                    header("Location: ".$lastpage);
                } else {
                    header("Location: ../root/admin.php");
                }
                exit;
            }
        }
    }
    if (!empty($_POST['Login']) and $newpass == false) {
        $message .= $script_transl[3];
    }
} else {
    $form['Login']='';
}
if ((isset($_SESSION['Abilit']) and isset($_SESSION["Login"])) and ($_SESSION['Abilit'] == false and $_SESSION["Login"] != 'Null')) {
    $result = gaz_dbi_get_row($gTables['admin'], "Login", $_SESSION['Login']);
    if (!empty ($result['lang'])){
          $lang = $result['lang'];
    } else {
          $lang = 'italian';
    }
    require("./lang.".$lang.".php");
    $script_transl = $strScript["login_admin.php"];
    $message .= $script_transl[4];
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//IT" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="author" content="Antonio De Vincentiis http://www.devincentiis.it">

<link rel="stylesheet" href="../../library/bootstrap/css/bootstrap.min.css" >
<link rel="shortcut icon" href="../../library/images/favicon.ico">
<link rel="stylesheet" type="text/css" href="../../library/style/default.css">
<title>Gazie - Login</title>
<noscript>
<h1><center>
ATTENTION!!! <br />Yours browser it is not qualified to execute code Javascript, in order to use GAZIE is indispensable to change such formulation!<BR />
ATTENZIONE!!!<br />Il tuo browser non &egrave; abilitato ad eseguire codice Javascript, per usare GAZIE &egrave; indispensabile cambiare tale impostazione!<br />
</h1></noscript>

<script language="JavaScript1.2" src="../../js/cookies/cookies.js"></script>
<script language="JavaScript" src="../../js/md5/md5.js"></script>
<script language="JavaScript" src="../../library/bootstrap/js/jquery.min.js"></script>
<script language="JavaScript" src="../../js/jquery/capslock.js"></script>

<script type="text/javascript">
      $(document).ready(function() {

        var poptions = {
          caps_lock_on: function() { $("#pmsg").text("Blocco maiuscole attivato! Caps lock on! Bloqueo de mayusculas!");},
          caps_lock_off: function() { $("#pmsg").text(""); }
        };

        $("#ppass").capslock(poptions);

        $("#ppass").focus();

      });
	  
	  function showPassword() {
    
    var key_attr = $('#Password').attr('type');
    
    if(key_attr != 'text') {
        
        $('.checkbox').addClass('show');
        $('#Password').attr('type', 'text');
        
    } else {
        
        $('.checkbox').removeClass('show');
        $('#Password').attr('type', 'password');
        
    }
    
}
</script>
</head>
<body background="../../library/images/sfondo.png">
<form method="post" onsubmit="document.forms[0].Password.value=hex_hmac_md5(document.forms[0].Password.value, GetCookie('<?php echo session_name(); ?>'));" action="<?php echo "login_admin.php?tp=".$tp; ?> ">
<input type="hidden" name="tp" value="<?php echo $tp; ?>" />
<div class="container">    
    <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
        <div class="panel panel-info" >
            <div class="panel-heading panel-gazie">
                <div class="panel-title"><img width="5%" src="../../library/images/gazie.gif" /> <?php echo $script_transl['log']; ?> <?php echo $server_lang; ?> <img width="5%" src="../../language/<?php echo $lang;?>/flag.png" /></div>
                <div style="color: red; float:right; font-size: 100%; position: relative; top:-10px"></div>
            </div>

            <div style="padding-top:30px" class="panel-body" >
			<?php
			if (!$message == "") {
				echo '<div id="login-alert" class="alert alert-danger col-sm-12">';
				print $message;
				echo '</div>';
			}
			?>
			<p><h4><?php echo $script_transl['welcome']; ?></h4>
                        <?php echo $script_transl['intro']; ?></p>
                        <p><?php echo $script_transl['usr_psw']; ?></p><br/>
                    <div style="margin-bottom: 25px" class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                        <input id="login-username" type="text" class="form-control" name="Login" value="<?php if (isset($_POST['Login'])) {echo $form['Login'];} else {if (isset($usr)) {echo $usr;}}; ?>" placeholder="Inserisci il Nome Utente">
                                    </div>
                                
                    <div style="margin-bottom: 25px" class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                        <input id="login-password" type="password" class="form-control" name="Password" placeholder="<?php echo $script_transl['ins_psw']; ?>">
                                    </div>
					<?php
					if ($newpass == true) {
					?>
					<?php echo $script_transl['label_new_psw']; ?>
					<div style="margin-bottom: 25px" class="input-group">
						<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
						<input id="login-password" type="password" class="form-control" name="Nuovapass" placeholder="<?php echo $script_transl['new_psw']; ?>">
					</div>

					<?php echo $script_transl['label_conf_psw']; ?>			
					<div style="margin-bottom: 25px" class="input-group">	
						<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
						<input id="login-password" type="password" class="form-control" name="Confepass" placeholder="<?php echo $script_transl['conf_psw']; ?>">
					</div>
					<?php
					}
					?>
                    <div style="margin-top:10px" class="form-group">
                        <div class="col-sm-12 controls">
                            <input style="float:right;" class="btn btn-success" name="actionflag" type="submit" value="Login" >
                        </div>
                    </div>
                </div>                     
            </div>  
        </div>
</form>
</body>
</html>
