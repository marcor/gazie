<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$return_arr = array();
$term =  filter_var($_GET['term'],FILTER_SANITIZE_STRING);
if(strlen($term) >1) {
        $result = gaz_dbi_dyn_query("id,ragso1,citspe",$gTables['anagra'],"ragso1 LIKE '%".$term."%'",'ragso1');
        while($row = gaz_dbi_fetch_array($result)) {
            $r['id']=$row['id'];
            $r['label']=$row['ragso1'];
            $r['value']=$row['ragso1'];
            array_push($return_arr,$r);
        }
        echo json_encode($return_arr);
} else {
  return;
}
?>

