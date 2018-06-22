<?php 
if (isset($_SESSION['table_prefix'])) {
    $table_prefix = substr($_SESSION['table_prefix'], 0, 12);
} elseif (isset($_GET['tp'])) {
    $table_prefix = filter_var(substr($_GET['tp'], 0, 12), FILTER_SANITIZE_MAGIC_QUOTES);
} else {
    $table_prefix = filter_var(substr($table_prefix, 0, 12), FILTER_SANITIZE_MAGIC_QUOTES);
}


function generateRandomString($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$aes_key=generateRandomString();
$result = gaz_dbi_dyn_query("*", $table_prefix.'_admin', 1);
echo 'I seguenti utenti sono stati valorizzati in automatico con una chiave di crittazione AES :<br>';
while ($row = gaz_dbi_fetch_array($result)) {
	gaz_dbi_query('UPDATE `'.$table_prefix."_admin` SET `aes_key`=HEX(AES_ENCRYPT('".$aes_key."',UNHEX(SHA2(`user_password_hash`,512)))) WHERE 1");		
	echo "utente: ".$row['user_name'].'<br>';
}
echo 'La chiave generata automaticamente e casualmente è :<b>'.$aes_key.'</b><br>';
?>
