 <?php
//Credit: m_zolfo

/*if (isset($_SERVER['SCRIPT_FILENAME']) && (str_replace('\\', '/', __FILE__) == $_SERVER['SCRIPT_FILENAME'])) {
    exit('Accesso diretto non consentito');
}*/

// prevent direct access

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}

if($_POST["fn"] == "read" && !file_exists($_POST["filename"]))
  return;

if($_POST["fn"] == "read" && $_POST["filename"] !="")
{
	$myfile = fopen($_POST["filename"], "r") or die(""); //echo "";return;
	echo fread($myfile,filesize($_POST["filename"]));
	fclose($myfile);
}

if($_POST["fn"] == "save" && /*$_POST["value"] != "" &&*/ $_POST["filename"] !="")
{
	$myfile = fopen($_POST["filename"], "w") or die("");
	fwrite($myfile,$_POST["value"]);
	fclose($myfile);
}
?>
