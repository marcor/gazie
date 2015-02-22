<?php

/**
 * Model Root For Login
 */
class ModelRootLogin extends Model
{
	
	public function checkAdmin( $Livaut=0 )
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
}