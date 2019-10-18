<?php
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();
if (isset($_GET['filename'])&&isset($_GET['ext'])&&isset($_GET['company_id'])){
	$bfn = filter_var($_GET['filename'], FILTER_SANITIZE_STRING);
	$ext = filter_var($_GET['ext'], FILTER_SANITIZE_STRING);
	$fn=$bfn.'.'.$ext;
	$ci = intval($_GET['company_id']);
	$mime=mime_content_type('../../data/files/'.$ci.'/sian/'.$fn);
	$fs=filesize('../../data/files/'.$ci.'/sian/'.$fn);
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=".$fn);
	header ('Content-length: ' .$fs);
	header("Content-Type: ".$mime);
	header("Content-Transfer-Encoding: binary");
	readfile('../../data/files/'.$ci.'/sian/'.$fn);	
}
?>