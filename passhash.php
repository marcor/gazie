<?php
if (isset($_POST['password'])) {

} else {
  $_POST['password']='';
};
?>
<body>
<title>Trova l'hash della password di GAzie ver.9</title>
<h1 style="background-color: aquamarine;" >Trova l'hash della password di GAzie ver.9</h1>
<form method="post">
<input type="password" name="password" value="<?php echo $_POST['password'];?>" placeholder="password in chiaro">
<input class="btn btn-info" name="login" type="submit" value="Conferma" >

</form>
<?php
if (isset($_POST['password'])) {
  if (strlen($_POST['password']) > 3 ) {
    $psw = $_POST['password'];
    $sha256password = hash('sha256',$psw);
    $newhash=password_hash($sha256password, PASSWORD_DEFAULT, ['cost' => 10]);
    //echo '<br>Nuovo hash: '.$newhash.'<br>';
    if (password_verify($sha256password, $newhash)){
      echo '<h3 style="color: green;">GENERAZIONE NUOVI HASH RIUSCITA</h3><p>Per accedere con la <b>password inserita </b> la colonna <b>user_password_hash</b> della tabella <b>gaz_admin</b> può essere valorizzata con :<br/><b>'.$newhash.'</b></p><p>Nelle vecchie versioni di GAzie ( < 9.0) può essere usato l\'hash: <br/> <b>'.password_hash($psw, PASSWORD_DEFAULT, ['cost' => 10]).'</b></p><p>L\'hash SHA256 della stessa è:<br/><b> '.$sha256password.'</b></p>';
    } else {
      echo 'ERRORE';
    }
    $ciphertext_b64 = "";

    // $aeskey è il valore della chiave che si dovrà ritrovare nel registro globale $_SESSION['aes_key'] dopo il login di qualsiasi utente
    // qui è definita in variabile solo come esempio ma dovrebbe essere chiesta in fase di prima installazione del getionale e poi non presente in nessun luogo
    // in quanto successivamente generata onthefly al login dal decrypt del valore di aes_key del database usando come chiave $prepared_key (lunga 16 byte)
    $aeskey = "8i2;3Gt6]1JG.òç@";

// definiti in root/config_login.php
    define("AES_KEY_SALT","CK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNo");
    define("AES_KEY_IV","LQjFLCU3sAVplBC3");

    $prepared_key = openssl_pbkdf2($sha256password, AES_KEY_SALT, 16, 1000, "sha256");
    $ciphertext_b64 = base64_encode(openssl_encrypt($aeskey,"AES-128-CBC",$prepared_key,OPENSSL_RAW_DATA, AES_KEY_IV));
    $aeskey = openssl_decrypt(base64_decode($ciphertext_b64),"AES-128-CBC",$prepared_key,OPENSSL_RAW_DATA, AES_KEY_IV);
    echo "<p>Se si assume di voler usare come chiave di encrypt/decrypt dei campi da proteggere uguale a: <br/><b>".$aeskey . "</b>";
    echo "<p>consegue che nella colonna <b>aes_key</b> della tabella <b>gaz_admin</b> dovrai avere: <br/><b>".$ciphertext_b64. "</b></p>";
  }
}
?>
</body>
