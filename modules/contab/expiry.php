<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$term =  filter_var(intval($_GET['term']),FILTER_SANITIZE_MAGIC_QUOTES);
$return_arr = array();
$result = gaz_dbi_dyn_query("*",$gTables['rigmoc'],"codcon=".$term,'codcon');
while($row = gaz_dbi_fetch_array($result)) {
            $r['id']=$row['codcon'];
            $r['label']=$row['darave'];
            $r['value']=$row['import'];
            array_push($return_arr,$r);
}
echo json_encode($return_arr);
?>

