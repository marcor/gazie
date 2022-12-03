<?php
// Antonio Germani
echo '<br>Versione attuale di php: ' . phpversion();
if (floatval(phpversion())>=7.4){
  echo "<br>Versione di php > 7.4: OK";
}else{
  echo "<br>Versione di php > 7.4: FAILED";
}
echo "<br><br>Controllo librerie necessarie a GAzie";
if(extension_loaded('MySQLi')){
  echo "<br> MySQLi: OK";
}else{
  echo "<br> MySQLi: FAILED";
}
if(extension_loaded('intl')){
  echo "<br> intl: OK";
}else{
  echo "<br> intl: FAILED";
}
if(extension_loaded('xml')){
  echo "<br> xml: OK";
}else{
  echo "<br> xml: FAILED";
}
if(extension_loaded('gd')){
  echo "<br> gd: OK";
}else{
  echo "<br> gd: FAILED";
}
if(extension_loaded('xml')){
  echo "<br> xsl: OK";
}else{
  echo "<br> xsl: FAILED";
}
if(extension_loaded('calendar')){
  echo "<br> calendar: OK";
}else{
  echo "<br> calendar: FAILED";
}
echo "<br><br> ESTENSIONI php abilitate:<pre>";
print_r(get_loaded_extensions());
/*
echo "</pre>";
echo phpinfo();
*/
?>
