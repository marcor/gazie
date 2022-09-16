<?php
if (isset($_POST['password'])) {

} else {
  $_POST['password']='';
};
?>
<script src='build/forge-sha256.min.js'></script>
<script>
console.log(forge_sha256('<?php echo $psw; ?>'));
</script>
<body>
<title>Trova l'hash della password di GAzie ver.9</title>
<h1>Trova l'hash della password di GAzie ver.9</h1>
<form method="post">
<input type="password" name="password" value="<?php echo $_POST['password'];?>" placeholder="password in chiaro">
<input class="btn btn-info" name="login" type="submit" value="Conferma" >

</form>
<?php
if (isset($_POST['password'])) {
  if (strlen($_POST['password']) > 3 ) {
    $psw = $_POST['password'];
    $sha256password = hash('sha256',$psw);
    //echo '<br>Password: '.$psw;
    //echo '<br>SHA256: '.$sha256password;
    $newhash=password_hash($sha256password, PASSWORD_DEFAULT, ['cost' => 10]);
    //echo '<br>Nuovo hash: '.$newhash.'<br>';
    if (password_verify($sha256password, $newhash)){
      echo '<div class="text-info bg-info"><h5>GENERAZIONE NUOVO HASH RIUSCITO</h5></div><p>Per accedere con la password scelta la colonna <b></u>user_password_hash</u></b> della tabella <b>gaz_admin</b> può essere valorizzata con :</p><p><b>'.$newhash.'</b></p><p><small>L\'hash SHA256 della stessa è:<br> '.$sha256password.'</small></p><p>Nelle vecchie versioni ( < 9.0) può essere usato l\'hash: '.password_hash($psw, PASSWORD_DEFAULT, ['cost' => 10]).'</p>';
    } else {
      echo 'ERRORE';
    }
    $ciphertext_b64 = "";
    //$plaintext = "ChiaveFieldsCrypt";
    $plaintext = "8123Gt621JG.;òç@";

// definiti in root/config_login.php
    define("AES_KEY_SALT","CK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNo");
    define("AES_KEY_IV","LQjFLCU3sAVplBC3");
    $prepared_key = openssl_pbkdf2($sha256password, AES_KEY_SALT, 16, 1000, "sha256");
    $ciphertext_b64 = base64_encode(openssl_encrypt($plaintext,"AES-128-CBC",$prepared_key,OPENSSL_RAW_DATA, AES_KEY_IV));
    $plaintext = openssl_decrypt(base64_decode($ciphertext_b64),"AES-128-CBC",$prepared_key,OPENSSL_RAW_DATA, AES_KEY_IV);
    echo "<br/>Per il seme di crypt/decript dei campi sul database uguale a : <b>".$plaintext . "</b><br/>";
    echo "<br/>nella colonna aes_key devi avere: <b>".$ciphertext_b64. "</b><br/>";
  }
}
?>
</body>
