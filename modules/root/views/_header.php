<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="Antonio De Vincentiis http://www.devincentiis.it">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<?php
// utiLizzo l'ultimo thema scelto dall'ultimo utente con i massimi diritti
$rsu = gaz_dbi_dyn_query('var_value', $gTables['admin'].' LEFT JOIN '. $gTables['admin_config'].' ON '. $gTables['admin'].'.user_name='. $gTables['admin_config'].'.adminid', $gTables['admin_config'].".var_name='theme'",  $gTables['admin'].'.Abilit DESC,'.$gTables['admin'].'.datacc DESC');
$u = gaz_dbi_fetch_array($rsu);

// CONTROLLO QUANTE AZIENDE HA L'INSTALLAZIONE
$rs_az = gaz_dbi_dyn_query('*', $gTables['aziend'], '1', 'codice DESC');
if (gaz_dbi_num_rows($rs_az) > 1) { // ho più aziende gestite devo usare una icona generica derivante dal tema dell'ultimo utente amministratore che è entrato  
?>
		<meta name="apple-mobile-web-app-title" content="GAzie - Gestione AZIEndale">
        <link rel="shortcut icon" href="../..<?php echo $u['var_value'];?>/images/favicon.ico">
		<link rel="apple-touch-icon"  href="../..<?php echo $u['var_value'];?>/images/apple-icon-114x114-precomposed.png">
		<link rel="apple-touch-startup-image"  href="../..<?php echo $u['var_value'];?>/images/apple-icon-114x114-precomposed.png">		
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../..<?php echo $u['var_value'];?>/images/apple-icon-114x114-precomposed.png" />
        <title>Login</title>
<?php	
} else { //ho una sola azienda, utilizzo le sue icone
	$az = gaz_dbi_fetch_array($rs_az);
?>
		<meta name="apple-mobile-web-app-title" content="<?php echo $az['ragso1'];?>">
		<?php
			$ico=base64_encode(file_get_contents('../../data/files/'.$az['codice'].'/favicon.ico'));
			$ico114=base64_encode(file_get_contents('../../data/files/'.$az['codice'].'/logo_114x114.png'));
		?>
        <link rel="icon" href="data:image/x-icon;base64,<?php echo $ico?>"  type="image/x-icon" />
		<link rel="icon" sizes="114x114" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon" />
		<link rel="apple-touch-icon" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon">
		<link rel="apple-touch-startup-image" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon">		
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="data:image/x-icon;base64,<?php echo $ico114?>"  type="image/x-icon" />

<?php	
}
?>
        <link rel="stylesheet" href="../../library/bootstrap/css/bootstrap.min.css" >
        <link rel="stylesheet" type="text/css" href="../..<?php echo $u['var_value'];?>/scheletons/default.css">
        <link rel="stylesheet" type="text/css" href="../..<?php echo $u['var_value'];?>/skins/default.css">

</head>
<body>

