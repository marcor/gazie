<!DOCTYPE html>
<?php
if (isset($_POST['password'])) {

} else {
  $_POST['password']='';
  $_POST['user_name']='';
  $_POST['aeskey']='JnèGCM(ùRp$9ò{-c';
}
?>
<html lang="it">

<head>
  <meta name="description" content="Utilià per trovare gli hash della password di GAzie" />
  <meta charset="utf-8">
  <title>Trova l'hash della password di GAzie</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Antonio de Vincentiis">
</head>

<body>
<script>
function makeaeskey(length) {
  let result = '';
  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!£$%&/()=?^,.-òàùè+[]@#}{;:_';
  const charactersLength = characters.length;
  let counter = 0;
  while (counter < length) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
    counter += 1;
  }
  var ak = document.getElementById('aeskey');
  ak.value = result;
  //console.log(result);
  document.getElementById('myForm').submit();
}
</script>


<h1 style="background-color: aquamarine;" >Trova l'hash della password di GAzie ver.9</h1>
<form method="post" id="myForm">
<p><input type="text" name="user_name" value="<?php echo $_POST['user_name'];?>" placeholder="nome utente"></p>
<p><input type="password" name="password" value="<?php echo $_POST['password'];?>" placeholder="password in chiaro"></p>
<p><input type="hidden" name="aeskey" id="aeskey" value="<?php echo $_POST['aeskey']; ?>" ><input type="submit" value="Conferma" ></p>
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
    // definiti anche in root/config_login.php
    define("AES_KEY_SALT","CK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNo");
    define("AES_KEY_IV","LQjFLCU3sAVplBC3");

    $prepared_key = openssl_pbkdf2($sha256password.$_POST['user_name'], AES_KEY_SALT, 16, 1000, "sha256");
    $ciphertext_b64 = base64_encode(openssl_encrypt($_POST['aeskey'],"AES-128-CBC",$prepared_key,OPENSSL_RAW_DATA, AES_KEY_IV));
    $aeskey = openssl_decrypt(base64_decode($ciphertext_b64),"AES-128-CBC",$prepared_key,OPENSSL_RAW_DATA, AES_KEY_IV);
    echo "<p>Se si assume di voler usare come chiave di encrypt/decrypt dei campi da proteggere uguale a: <br/><b>".$aeskey. '</b>  <button type="button" onclick="makeaeskey(16)">Cambia</button>';
    echo "<p>consegue che nella colonna <b>aes_key</b> della tabella <b>gaz_admin</b> dovrai avere: <br/><b>".$ciphertext_b64. "</b></p>";
  }
}
?>
</form>
</body>
</html>
