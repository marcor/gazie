<?php
/* $Id: login_admin.php,v 1.21 2011/01/01 11:07:47 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
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
if (isset ($_POST['actionflag'])) {
    // checkUser();
    $result = gaz_dbi_get_row ($gTables['admin'], "Login", $_POST['Login']);
    if (!empty ($result['lang'])){
          $lang = $result['lang'];
    } else {
          $lang = 'italian';
    }
    require("./lang.".$lang.".php");
    $script_transl = $strScript["login_admin.php"];
    if ($result) {
        require("../../library/include/HMAC.php");
        $crypt = new Crypt_HMAC($result["Password"], 'md5');
        $hmacPass = $crypt->hash($_COOKIE[session_name()]);
        if ($hmacPass == $_POST['Password']) {
            cleanMemberSession($result["Abilit"], $result["Login"], $result["Password"], $result["Access"], $result['enterprise_id']);
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
                    gaz_dbi_put_row($gTables['admin'], "Login",$_POST['Login'],"datpas",date("Y%-m%-d H:i:s"));
                    gaz_dbi_put_row($gTables['admin'], "Login",$_POST['Login'],"Password",$_POST['Nuovapass']);
                    cleanMemberSession($result["Abilit"], $result["Login"], $_POST["Nuovapass"], $result["Access"], $result['enterprise_id']);
                    header("Location: ../root/admin.php");
                    exit;
                } else {
                    $message .= $script_transl[0].$config->getValue('psw_min_length').$script_transl[1];
                }
                $newpass = true;
            } else {
                header("Location: ../root/admin.php");
                exit;
            }
        }
    }
    if (!empty($_POST['Login']) and $newpass == false) {
        $message .= $script_transl[3];
    }
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
<meta name="author" content="Antonio De Vincentiis www.devincentiis.it">
<link rel="stylesheet" type="text/css" href="../../library/style/stylesheet.css">
<link rel="shortcut icon" href="../../library/images/favicon.ico">
<title>Gazie - Login</title>
<noscript>
<h1><center>
ATTENTION!!! <br />Yours browser it is not qualified to execute code Javascript, in order to use GAZIE is indispensable to change such formulation!<BR />
ATTENZIONE!!!<br />Il tuo browser non &egrave; abilitato ad eseguire codice Javascript, per usare GAZIE &egrave; indispensabile cambiare tale impostazione!<br />
</h1></noscript>
<script language="JavaScript1.2" src="../../js/cookies/cookies.js"></script>
<script language="JavaScript" src="../../js/md5/md5.js"></script>
<script language="JavaScript" src="../../js/jquery/jquery-1.3.2.min.js"></script>
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
</script>
</head>
<body background="../../library/images/sfondo.png">
<form method="post" onsubmit="document.forms[0].Password.value=hex_hmac_md5(document.forms[0].Password.value, GetCookie('<?php echo session_name(); ?>'));" action="<?php echo "login_admin.php"; ?> ">
<div align="center" class="FacetFormHeaderFont" >Authentication</div>
<table align="center" border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE">
<!-- BEGIN Error -->
    <?php
    if (!$message == "") {
        print '<tr><td colspan="3" class="FacetDataTDred">'.$message.'</td></tr>';
    }
    ?>
<!-- END Error -->
<tr><td class="FacetDataTD" rowspan="2"><img src="../../library/images/gazie.gif" width="51"></td>
<td class="FacetFieldCaptionTD">User </td>
<td class="FacetDataTD"><input type="text" name="Login" value="<?php if (isset($_POST['Login'])) echo $_POST['Login']; ?>" maxlength="100" size="20" class="FacetInput"> </td>
</tr>
<tr>
<td class="FacetFieldCaptionTD">Password </td>
<td class="FacetDataTD"><input type="password" name="Password" value="" maxlength="32" size="20" class="FacetInput" id="ppass"><div class="FacetDataTDred" id="pmsg"></div></td>
</tr>
<?php
    if ($newpass == true) {
        echo "<tr><td class=\"FacetDataTD\"></td><td class=\"FacetFieldCaptionTD\">$script_transl[5]</td>";
        echo "<td class=\"FacetDataTD\"><input type=\"password\" name=\"Nuovapass\" value=\"\" maxlength=\"20\" size=\"20\" class=\"FacetInput\"> </td><tr>\n";
        echo "<tr><td class=\"FacetDataTD\"></td><td class=\"FacetFieldCaptionTD\">$script_transl[6]</td>";
        echo "<td class=\"FacetDataTD\"><input type=\"password\" name=\"Confepass\" value=\"\" maxlength=\"20\" size=\"20\" class=\"FacetInput\"> </td><tr>\n";
    }
?>
  <tr>
    <td colspan="3" align="center">
      <!-- BEGIN Button DoLogin --><input name="actionflag" type="submit" value="Login" ><!-- END Button DoLogin -->
    </td>
  </tr></table>
</form>
<!-- END Record Login -->
</body>
</html>

